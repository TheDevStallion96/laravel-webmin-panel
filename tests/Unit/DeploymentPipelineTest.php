<?php

use App\Jobs\RunDeployment;
use App\Models\Deployment;
use App\Models\Site;
use App\Enums\DeployStrategy;
use App\Enums\DeploymentStatus;
use App\Actions\Deploy\DeployActionInterface;
use App\Actions\Deploy\DeploymentLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

class FakeAction implements DeployActionInterface
{
    public static array $order = [];
    public bool $shouldFail;
    public string $label;
    public function __construct(string $label, bool $shouldFail = false) { $this->label = $label; $this->shouldFail = $shouldFail; }
    public function name(): string { return $this->label; }
    public function run(App\Models\Site $site, string $releasePath, DeploymentLogger $logger): bool
    {
        self::$order[] = $this->label;
        $logger->line('FAKE step '. $this->label);
        return ! $this->shouldFail;
    }
}

it('runs actions in order and marks deployment completed', function () {
    $site = Site::factory()->create(['deploy_strategy' => DeployStrategy::ZeroDowntime->value]);
    $deployment = Deployment::create([
        'site_id' => $site->id,
        'commit_hash' => 'pending',
        'branch' => $site->default_branch,
        'status' => DeploymentStatus::Pending->value,
        'log_path' => '',
    ]);
    $job = new RunDeployment($deployment->id);
    $job->actionClasses = [new FakeAction('A'), new FakeAction('B'), new FakeAction('C')];
    $job->handle();
    $deployment->refresh();
    expect($deployment->status)->toBeInstanceOf(DeploymentStatus::class)
        ->and($deployment->status->value)->toBe(DeploymentStatus::Completed->value);
    expect(FakeAction::$order)->toEqual(['A','B','C']);
});

it('stops pipeline on failure and marks deployment failed', function () {
    FakeAction::$order = [];
    $site = Site::factory()->create();
    $deployment = Deployment::create([
        'site_id' => $site->id,
        'commit_hash' => 'pending',
        'branch' => $site->default_branch,
        'status' => DeploymentStatus::Pending->value,
        'log_path' => '',
    ]);
    $job = new RunDeployment($deployment->id);
    $job->actionClasses = [new FakeAction('A'), new FakeAction('B_FAIL', true), new FakeAction('C')];
    $job->handle();
    $deployment->refresh();
    expect($deployment->status)->toBeInstanceOf(DeploymentStatus::class)
        ->and($deployment->status->value)->toBe(DeploymentStatus::Failed->value);
    expect(FakeAction::$order)->toEqual(['A','B_FAIL']);
});
