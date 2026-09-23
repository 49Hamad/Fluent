<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Development / staging safety net: never e-mail real students by mistake.
        if (! $this->app->isProduction() && filled($to = config('fluent.dev_mail_to'))) {
            Mail::alwaysTo($to);
        }

        // «شاركنا تحديًا»: protection against automated / repeated submissions.
        RateLimiter::for('challenge-submit', fn (Request $request) => [
            Limit::perMinute(5)->by('min:' . $request->ip()),
            Limit::perDay(20)->by('day:' . $request->ip()),
        ]);

        // Student portal workflow — per signed-in student and per action.
        $student = fn (Request $request) => 'student:' . (auth('student')->id() ?? $request->ip()) . ':' . $request->route()?->getName();
        RateLimiter::for('portal-action', fn (Request $request) => Limit::perMinute(10)->by($student($request)));
        RateLimiter::for('portal-receipt', fn (Request $request) => [
            Limit::perMinute(5)->by('m:' . $student($request)),
            Limit::perDay(30)->by('d:' . $student($request)),
        ]);
        RateLimiter::for('portal-download', fn (Request $request) => Limit::perMinute(30)->by($student($request)));
    }
}
