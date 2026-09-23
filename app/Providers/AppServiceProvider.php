<?php

namespace App\Providers;

use Illuminate\Support\Facades\Mail;
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
    }
}
