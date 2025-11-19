<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TokenController;
use App\Http\Controllers\SitesController;
use App\Http\Controllers\SitesPauseController;
use App\Http\Controllers\SitesResumeController;
use App\Http\Controllers\WebServerController;
use App\Http\Controllers\DatabasesController;
use App\Http\Controllers\DomainsController;
use App\Http\Controllers\CertificatesController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Token management UI
    Route::get('/tokens', [TokenController::class, 'index'])->name('tokens.index');
    Route::post('/tokens', [TokenController::class, 'store'])->name('tokens.store');
    Route::delete('/tokens/{id}', [TokenController::class, 'destroy'])->name('tokens.destroy');

    // Admin-only routes (server management)
    Route::middleware('can:manage-server')->group(function () {
        Route::get('/admin/servers', function () {
            return response()->view('welcome');
        })->name('admin.servers.index');
    });

    // Sites routes per Phase 3
    Route::get('/sites', [SitesController::class, 'index'])->middleware('can:view-site')->name('sites.index');
    Route::get('/sites/create', [SitesController::class, 'create'])->middleware('can:manage-site')->name('sites.create');
    Route::post('/sites', [SitesController::class, 'store'])->middleware('can:manage-site')->name('sites.store');
    Route::get('/sites/{site}', [SitesController::class, 'show'])->middleware('can:view-site')->name('sites.show');
    Route::put('/sites/{site}', [SitesController::class, 'update'])->middleware('can:manage-site')->name('sites.update');
    Route::patch('/sites/{site}', [SitesController::class, 'update'])->middleware('can:manage-site')->name('sites.update.patch');
    Route::delete('/sites/{site}', [SitesController::class, 'destroy'])->middleware('can:manage-site')->name('sites.destroy');
    Route::post('/sites/{site}/pause', [SitesPauseController::class, 'store'])->middleware('can:manage-site')->name('sites.pause');
    Route::post('/sites/{site}/resume', [SitesResumeController::class, 'store'])->middleware('can:manage-site')->name('sites.resume');
    // Web server rebuild (nginx + php-fpm configs) with optional dry-run & diff (?dry=1&diff=1)
    Route::post('/sites/{site}/webserver/rebuild', [WebServerController::class, 'rebuild'])->middleware('can:manage-site')->name('sites.webserver.rebuild');
    // Databases management Phase 5
    Route::get('/sites/{site}/databases', [DatabasesController::class, 'index'])->middleware('can:manage-site')->name('sites.databases.index');
    Route::post('/sites/{site}/databases', [DatabasesController::class, 'store'])->middleware('can:manage-site')->name('sites.databases.store');
    Route::delete('/sites/{site}/databases/{database}', [DatabasesController::class, 'destroy'])->middleware('can:manage-site')->name('sites.databases.destroy');

    // Domains & SSL Phase 6
    Route::get('/sites/{site}/domains', [DomainsController::class, 'index'])->middleware('can:manage-site')->name('sites.domains.index');
    Route::post('/sites/{site}/domains', [DomainsController::class, 'store'])->middleware('can:manage-site')->name('sites.domains.store');
    Route::delete('/sites/{site}/domains/{domain}', [DomainsController::class, 'destroy'])->middleware('can:manage-site')->name('sites.domains.destroy');
    Route::post('/sites/{site}/domains/{domain}/primary', [DomainsController::class, 'makePrimary'])->middleware('can:manage-site')->name('sites.domains.primary');
    Route::post('/sites/{site}/domains/{domain}/https', [DomainsController::class, 'toggleHttpsForced'])->middleware('can:manage-site')->name('sites.domains.https');
    Route::post('/sites/{site}/certificates/provision', [CertificatesController::class, 'provisionLetsEncrypt'])->middleware('can:manage-site')->name('sites.certificates.provision');

    // Deployments Phase 7
    Route::get('/sites/{site}/deploy/settings', [\App\Http\Controllers\DeploymentController::class, 'settings'])->middleware('can:manage-site')->name('sites.deploy.settings');
    Route::post('/sites/{site}/deploy/run', [\App\Http\Controllers\DeploymentController::class, 'run'])->middleware('can:manage-site')->name('sites.deploy.run');
    Route::get('/sites/{site}/deploy/history', [\App\Http\Controllers\DeploymentController::class, 'history'])->middleware('can:view-site')->name('sites.deploy.history');
    Route::get('/sites/{site}/deploy/logs/{deployment}', [\App\Http\Controllers\DeploymentController::class, 'logs'])->middleware('can:view-site')->name('sites.deploy.logs');
    Route::post('/sites/{site}/deploy/rollback/{deployment}', [\App\Http\Controllers\DeploymentController::class, 'rollback'])->middleware('can:manage-site')->name('sites.deploy.rollback');
});

require __DIR__.'/auth.php';
