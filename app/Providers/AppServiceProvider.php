<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\{Site, Domain, Deployment};
use App\Observers\{SiteObserver, DomainObserver, DeploymentObserver};

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
        Site::observe(SiteObserver::class);
        Domain::observe(DomainObserver::class);
        Deployment::observe(DeploymentObserver::class);

        // Share authenticated user and role to all views
        View::composer('*', function ($view) {
            $user = Auth::user();
            $view->with('authUser', $user);
            $view->with('authRole', $user?->role?->value);
        });
    }
}
