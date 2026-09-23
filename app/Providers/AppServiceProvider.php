<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
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

        /*
         * Public student forms. Fluent's users are university students who often
         * share one network IP, so limits are keyed mainly by the applicant's
         * e-mail (one person), with a much higher per-IP ceiling as the abuse
         * backstop. Every limiter uses its own named key, so one route can never
         * use up another route's allowance (the old unnamed throttle shared one
         * per-IP counter across routes).
         */
        $emailKey = fn (Request $request) => sha1(Str::lower(trim((string) $request->input('email'))));

        // Student application: 5/min and 20/day per e-mail; 30/min and 500/day per network.
        RateLimiter::for('apply-submit', fn (Request $request) => [
            Limit::perMinute(5)->by('apply:email:' . $emailKey($request)),
            Limit::perDay(20)->by('apply:email-day:' . $emailKey($request)),
            Limit::perMinute(30)->by('apply:ip:' . $request->ip()),
            Limit::perDay(500)->by('apply:ip-day:' . $request->ip()),
        ]);

        // Login code request / verification: per-network burst ceilings only.
        // The per-e-mail limits (3 codes / 10 min, 10 wrong codes / 10 min, 5 tries per
        // code) live in StudentLoginService and apply whether or not the e-mail exists.
        RateLimiter::for('login-code', fn (Request $request) => Limit::perMinute(30)->by('login-code:ip:' . $request->ip()));
        RateLimiter::for('login-verify', fn (Request $request) => Limit::perMinute(60)->by('login-verify:ip:' . $request->ip()));

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
