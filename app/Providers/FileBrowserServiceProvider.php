<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider;

class FileBrowserServiceProvider extends RouteServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Route::prefix('file-browser')
            ->as('file-browser.')
            ->namespace($this->namespace)
            ->group(base_path('routes/file-browser.php'));
    }
}
