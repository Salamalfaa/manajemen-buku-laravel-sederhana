<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
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
        RateLimiter::for('password-reset-request', function (Request $request): Limit|array {
            $email = mb_strtolower(trim((string) $request->input('email')));
            $key = $email !== '' ? hash('sha256', $email) : $request->ip();

            return [
                Limit::perMinute(5)->by('password-reset-request:'.$key),
                Limit::perMinute(120)->by('password-reset-request-ip:'.$request->ip()),
            ];
        });

        RateLimiter::for('password-reset-status', function (Request $request): Limit|array {
            return [
                Limit::perMinute(60)->by('password-reset-status:'.$request->session()->getId()),
                Limit::perMinute(300)->by('password-reset-status-ip:'.$request->ip()),
            ];
        });

        RateLimiter::for('password-reset-link', function (Request $request): Limit|array {
            $token = (string) $request->route('token');

            return [
                Limit::perMinute(12)->by('password-reset-link:'.hash('sha256', $token)),
                Limit::perMinute(120)->by('password-reset-link-ip:'.$request->ip()),
            ];
        });

        RateLimiter::for('password-reset-completion', function (Request $request): Limit|array {
            $email = mb_strtolower(trim((string) $request->input('email')));
            $key = $email !== '' ? hash('sha256', $email) : $request->ip();

            return [
                Limit::perMinute(5)->by('password-reset-completion:'.$key),
                Limit::perMinute(120)->by('password-reset-completion-ip:'.$request->ip()),
            ];
        });
    }
}
