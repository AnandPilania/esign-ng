<?php


namespace Admin;


use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AdminServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/admin.php');
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
