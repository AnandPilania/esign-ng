<?php


namespace Viettel;


use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ViettelServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/viettel.php');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
    }
}
