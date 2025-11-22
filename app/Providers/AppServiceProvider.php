<?php

namespace App\Providers;

use App\Services\SapServiceLayerService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(SapServiceLayerService::class, function ($app) {
            return new SapServiceLayerService();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // 🔹 Aumentar los límites globalmente
        if (function_exists('set_time_limit')) {
            @set_time_limit(0);
        }
        @ini_set('max_execution_time', 0);
        @ini_set('memory_limit', '-1');
        // Compartir recursos activos y visibles para el usuario con todas las vistas
        View::composer('*', function ($view) {
            $resources = DB::table('resources')
                ->where('status', true)
                ->where('show_user', true)
                ->get();

            $view->with('sidenavResources', $resources);
        });
    }
}
