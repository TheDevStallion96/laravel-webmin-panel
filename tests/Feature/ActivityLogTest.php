<?php

use App\Models\ActivityLog;
use App\Models\Deployment;
use App\Enums\DeploymentStatus;
use App\Models\Site;
use App\Models\User;

it('logs activity via fluent interface', function () {
    $user = User::factory()->create();
    $site = Site::factory()->create();

    $log = activity()
        ->byUser($user)
        ->onSite($site)
        ->action('site.created')
        ->meta(['foo' => 'bar'])
        ->log();

    expect($log)->toBeInstanceOf(ActivityLog::class);
    expect($log->action)->toBe('site.created');
    expect($log->user_id)->toBe($user->id);
    expect($log->site_id)->toBe($site->id);
    expect($log->meta)->toMatchArray(['foo' => 'bar']);
});

it('logs activity without explicit user and site context', function () {
    $log = activity()
        ->action('system.maintenance')
        ->meta(['ok' => true])
        ->log();

    expect($log->user_id)->toBeNull();
    expect($log->site_id)->toBeNull();
    expect($log->action)->toBe('system.maintenance');
});

it('throws when action missing', function () {
    activity()->log();
})->throws(InvalidArgumentException::class);

it('creating a site records an activity (manual example)', function () {
    $site = Site::factory()->create();

    $log = activity()->onSite($site)->action('site.created')->log();

    expect($log->site_id)->toBe($site->id);
    expect($log->action)->toBe('site.created');
});

it('deployment observer logs lifecycle and strategy hooks', function () {
    $site = Site::factory()->create(['deploy_strategy' => \App\Enums\DeployStrategy::ZeroDowntime->value]);
    $deployment = Deployment::factory()->for($site)->create(['status' => DeploymentStatus::Pending->value]);

    expect(ActivityLog::query()->where('action', 'deployment.created')->where('site_id', $site->id)->exists())->toBeTrue();
    expect(ActivityLog::query()->where('action', 'deployment.strategy.zd.prepare')->where('site_id', $site->id)->exists())->toBeTrue();

    $deployment->update(['status' => DeploymentStatus::Completed->value]);
    expect(ActivityLog::query()->where('action', 'deployment.status_changed')->where('site_id', $site->id)->exists())->toBeTrue();
});
