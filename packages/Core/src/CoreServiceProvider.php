<?php

namespace Core;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(255);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $models = array(
            'eCAdmin',
            'eCUser',
            'eCRole',
            'eCCompany',
            'eCAgencies'
        );
        foreach ($models as $model) {
            $this->app->bind("Core\Repositories\\{$model}\\{$model}Repository",
                "Core\Repositories\\{$model}\\{$model}RepositoryEloquent");
        }
    }
}
