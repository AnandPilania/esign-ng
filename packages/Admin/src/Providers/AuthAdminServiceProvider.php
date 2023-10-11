<?php

namespace Admin\Providers;


use Admin\Policies\CompanyPolicy;
use Core\Models\eCCompany;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthAdminServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        eCCompany::class => CompanyPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
