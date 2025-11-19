<?php

use App\Enums\DeploymentStatus;
use App\Models\ActivityLog;
use App\Models\Backup;
use App\Models\Database as DbModel;
use App\Models\Deployment;
use App\Models\Domain;
use App\Models\QueueWorker;
use App\Models\ScheduledTask;
use App\Models\Site;
use App\Models\SslCertificate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('tests site relationships', function () {
    $site = Site::factory()->create();
    $domain = Domain::factory()->for($site)->create();
    $db = DbModel::factory()->for($site)->create();
    $cert = SslCertificate::factory()->for($site)->create();
    $deployment = Deployment::factory()->for($site)->create();
    $backup = Backup::factory()->for($site)->create();
    $worker = QueueWorker::factory()->for($site)->create();
    $task = ScheduledTask::factory()->for($site)->create();
    $activity = ActivityLog::factory()->create(['site_id' => $site->id]);

    expect($site->domains)->toHaveCount(1);
    expect($site->databases)->toHaveCount(1);
    expect($site->sslCertificates)->toHaveCount(1);
    expect($site->deployments)->toHaveCount(1);
    expect($site->backups)->toHaveCount(1);
    expect($site->queueWorkers)->toHaveCount(1);
    expect($site->scheduledTasks)->toHaveCount(1);
    expect($site->activityLogs->count())->toBeGreaterThan(0);

    expect($domain->site->is($site))->toBeTrue();
    expect($db->site->is($site))->toBeTrue();
    expect($cert->site->is($site))->toBeTrue();
    expect($deployment->site->is($site))->toBeTrue();
    expect($backup->site->is($site))->toBeTrue();
    expect($worker->site->is($site))->toBeTrue();
    expect($task->site->is($site))->toBeTrue();
    expect($activity->site->is($site))->toBeTrue();
});

it('tests deployment belongs to user', function () {
    $user = User::factory()->create();
    $deployment = Deployment::factory()->for(Site::factory())->for($user)->create();
    expect($deployment->user->is($user))->toBeTrue();
});

it('tests casts are correct', function () {
    $site = Site::factory()->create(['environment' => ['APP_ENV' => 'local']]);
    $domain = Domain::factory()->create(['is_primary' => true]);
    $cert = SslCertificate::factory()->create();
    $backup = Backup::factory()->create(['size_bytes' => 123]);

    expect($site->environment)->toBeArray();
    expect($domain->is_primary)->toBeTrue();
    expect($cert->expires_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
    expect($backup->size_bytes)->toBeInt();
});

it('factories generate valid records and states', function () {
    $site = Site::factory()->create();
    $primary = Domain::factory()->for($site)->primary()->create();
    $completed = Deployment::factory()->for($site)->create(['status' => DeploymentStatus::Completed->value]);

    expect($primary->is_primary)->toBeTrue();
    expect($completed->status)->toBeInstanceOf(DeploymentStatus::class);
    expect($completed->status->value)->toBe('completed');
});
