<?php

use App\Jobs\RunDeployment;
use App\Models\Site;
use App\Models\Deployment;
use App\Enums\DeployStrategy;
use Illuminate\Support\Carbon;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;
use function Pest\Laravel\get;

test('deployment creates log release and symlink then rollback switches release', function () {
    $user = \App\Models\User::factory()->admin()->create();
    $site = Site::factory()->create([
        'created_by' => $user->id,
        'repo_url' => 'git@github.com:example/repo.git',
        'deploy_strategy' => DeployStrategy::ZeroDowntime->value,
    ]);
    actingAs($user);

    // Run deployment via controller
    post(route('sites.deploy.run', $site))->assertRedirect(route('sites.deploy.history', $site));
    // Process queued job synchronously (jobs use database queue normally; here we just invoke directly)
    $deployment = $site->deployments()->latest('id')->first();
    (new RunDeployment($deployment->id))->handle();
    $deployment->refresh();
    expect($deployment->status)->toBeInstanceOf(\App\Enums\DeploymentStatus::class)
        ->and($deployment->status->value)->toBe('completed');
    expect(is_file($deployment->log_path))->toBeTrue();

    $base = storage_path('app/panel/releases/'.$site->slug);
    $current = $base.'/current';
    expect(is_link($current))->toBeTrue();
    $firstTarget = readlink($current);

    // Second deployment
    post(route('sites.deploy.run', $site))->assertRedirect();
    $second = $site->deployments()->latest('id')->first();
    (new RunDeployment($second->id))->handle();
    $second->refresh();
    expect($second->status)->toBeInstanceOf(\App\Enums\DeploymentStatus::class)
        ->and($second->status->value)->toBe('completed');
    $newTarget = readlink($current);
    expect($newTarget)->not()->toBe($firstTarget);

    // Rollback to first
    post(route('sites.deploy.rollback', [$site, $deployment]))->assertRedirect(route('sites.deploy.history', $site));
    $rolledTarget = readlink($current);
    expect($rolledTarget)->toBe($firstTarget);
});
