<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
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
        // كل صلاحيات النظام بتتحقق من فئة المستخدم — والمطور معاه كل حاجة
        \Illuminate\Support\Facades\Gate::before(function ($user, string $ability) {
            if (method_exists($user, 'isDeveloper') && $user->isDeveloper()) {
                return true;
            }
            if (\App\Support\Permissions::exists($ability)) {
                return $user->hasPermission($ability);
            }
            return null;
        });

        // @developer ... @enddeveloper
        \Illuminate\Support\Facades\Blade::if('developer', function () {
            return auth()->check() && auth()->user()->isDeveloper();
        });
    }
}
