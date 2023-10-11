<?php


namespace Customer;


use Illuminate\Support\ServiceProvider;

class CustomerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/api.php');

        $this->loadRoutesFrom(__DIR__ . '/customize.php');

        $this->loadRoutesFrom(__DIR__ . '/search.php');
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
