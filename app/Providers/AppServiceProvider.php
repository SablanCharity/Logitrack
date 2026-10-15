<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
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
        // Automatically share $classroomId with all views to prevent undefined variable errors
        View::composer('*', function ($view) {
            if (!array_key_exists('classroomId', $view->getData())) {
                $view->with('classroomId', null);
            }
        });
    }
}