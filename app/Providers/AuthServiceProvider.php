<?php

namespace App\Providers;

use App\Models\Deployment;
use App\Models\Domain;
use App\Models\Site;
use App\Policies\DeploymentPolicy;
use App\Policies\DomainPolicy;
use App\Policies\SitePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Site::class => SitePolicy::class,
        Domain::class => DomainPolicy::class,
        Deployment::class => DeploymentPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        // Gates
        Gate::define('manage-server', function ($user) {
            return $user->isAdmin();
        });

        Gate::define('manage-site', function ($user) {
            return $user->isAdmin() || $user->isDeveloper();
        });

        Gate::define('view-site', function ($user) {
            return (bool) $user; // all authenticated roles
        });
    }
}
