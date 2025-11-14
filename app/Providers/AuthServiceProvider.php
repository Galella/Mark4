<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        \App\Models\User::class => \App\Policies\UserPolicy::class,
        \App\Models\Office::class => \App\Policies\OfficePolicy::class,
        \App\Models\Outlet::class => \App\Policies\OutletPolicy::class,
        \App\Models\OutletType::class => \App\Policies\OutletTypePolicy::class,
        \App\Models\IncomeTarget::class => \App\Policies\IncomeTargetPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        //
    }
}
