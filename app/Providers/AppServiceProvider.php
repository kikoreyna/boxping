<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

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
        $basePath = rtrim((string) parse_url((string) config('app.url'), PHP_URL_PATH), '/');

        if ($basePath === '') {
            return;
        }

        Livewire::setScriptRoute(
            fn ($handle) => Route::get($basePath.'/livewire/livewire.js', $handle),
        );

        Livewire::setUpdateRoute(
            fn ($handle) => Route::post($basePath.'/livewire/update', $handle),
        );
    }
}
