<?php


namespace Report;


use Illuminate\Support\ServiceProvider;

class ReportProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/views', 'report');
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
