<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

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
        $listRoutes = ['currency', 'product', 'product_prices'];

        foreach ($listRoutes as $route) {
            $routeFile = base_path("routes/{$route}.php");

            // Verificar si el archivo de rutas existe
            if (file_exists($routeFile)) {
                Route::middleware('api')
                    ->prefix('api')
                    ->group($routeFile);
            }
        }

    }
}
