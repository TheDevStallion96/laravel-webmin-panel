<?php

namespace App\Jobs;

use App\Actions\Deploy\DeploymentLogger;
use App\Actions\Deploy\DeployActionInterface;
use App\Actions\Deploy\GitCloneOrPull;
use App\Actions\Deploy\ComposerInstall;
use App\Actions\Deploy\NpmBuild;
use App\Actions\Deploy\RunMigrations;
use App\Actions\Deploy\OptimizeLaravel;
use App\Actions\Deploy\SwitchSymlink;
use App\Actions\Deploy\RestartWorkers;
use App\Enums\DeploymentStatus;
use App\Enums\DeployStrategy;
use App\Models\Deployment;
use App\Models\Site;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RunDeployment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $deploymentId;
    /** @var class-string<DeployActionInterface>[] */
    public array $actionClasses = [
        GitCloneOrPull::class,
        ComposerInstall::class,
        NpmBuild::class,
        RunMigrations::class,
        OptimizeLaravel::class,
        SwitchSymlink::class,
        RestartWorkers::class,
    ];

    public function __construct(int $deploymentId)
    {
        $this->deploymentId = $deploymentId;
    }

    public function handle(): void
    {
        $deployment = Deployment::find($this->deploymentId);
        if (!$deployment) return;
        $site = Site::find($deployment->site_id);
        if (!$site) return;

        // Acquire lock to prevent concurrent deployments for same site
        $lock = Cache::lock('deploy:site:'.$site->id, 300);
        if (!$lock->get()) {
            Log::warning('Deployment skipped due to lock', ['site_id' => $site->id]);
            return;
        }

        $deployment->status = DeploymentStatus::InProgress->value;
        $deployment->started_at = Carbon::now();
        $deployment->save();

        $timestamp = Carbon::now()->format('YmdHis').'-'.substr(uniqid('', true), -4);
        $base = storage_path('app/panel/releases/'.$site->slug);
        $releasePath = $base.'/'.$timestamp;
        if (!is_dir($base)) @mkdir($base, 0755, true);

        $logDir = storage_path('app/panel/deployments/'.$site->slug);
        if (!is_dir($logDir)) @mkdir($logDir, 0755, true);
        $logPath = $logDir.'/'.$timestamp.'.log';
        $logger = new DeploymentLogger($logPath);
        $strategy = $site->deploy_strategy instanceof DeployStrategy ? $site->deploy_strategy->value : (string) $site->deploy_strategy;
        $logger->line('Starting deployment '.$timestamp.' strategy='.$strategy);

        $success = true;
        foreach ($this->actionClasses as $class) {
            /** @var DeployActionInterface $action */
            $action = is_string($class) ? app($class) : $class;
            // Skip symlink action if basic strategy and not last release creation desired
            if ((is_string($class) && $class === SwitchSymlink::class) || (!is_string($class) && $action instanceof SwitchSymlink)) {
                if ($site->deploy_strategy !== DeployStrategy::ZeroDowntime) {
                    continue;
                }
            }
            $logger->line('STEP start: '.$action->name());
            $ok = $action->run($site, $releasePath, $logger);
            if ($ok) {
                $logger->line('STEP success: '.$action->name());
            } else {
                $logger->line('STEP failed: '.$action->name());
                $success = false;
                break;
            }
        }

        $deployment->log_path = $logPath;
        $deployment->finished_at = Carbon::now();
        $deployment->status = $success ? DeploymentStatus::Completed->value : DeploymentStatus::Failed->value;
        $deployment->commit_hash = $this->extractCommitHash($releasePath);
        $deployment->save();

        if (function_exists('activity')) {
            activity()->onSite($site)->action('deployment.'.($success ? 'completed' : 'failed'))->meta([
                'deployment_id' => $deployment->id,
                'commit' => $deployment->commit_hash,
                'status' => $deployment->status,
            ])->log();
        }
        $lock->release();
    }

    private function extractCommitHash(string $releasePath): string
    {
        $file = $releasePath.'/.commit';
        if (is_file($file)) return trim(@file_get_contents($file));
        return 'unknown';
    }
}
