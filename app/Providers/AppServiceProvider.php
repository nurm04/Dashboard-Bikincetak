<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\ResetPassword;

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
        Vite::prefetch(concurrency: 3);

        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            
            if ($notifiable->role === 'customer') {
                $frontendUrl = env('APP_URL_NEXTJS', 'http://localhost:3000');
                return "{$frontendUrl}/reset-password/{$token}?email={$notifiable->getEmailForPasswordReset()}";
            }

            return url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));
        });
    }
}
