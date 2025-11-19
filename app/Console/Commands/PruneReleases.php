<?php

namespace App\Console\Commands;

use App\Enums\DeploymentStatus;
use App\Models\Site;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PruneReleases extends Command
{
    protected $signature = 'deploy:prune-releases {--keep=}';
    protected $description = 'Prune old release directories, keeping only the last N successful releases per site';

    public function handle(): int
    {
        $keep = (int) ($this->option('keep') ?? config('deploy.keep_releases', 5));
        if ($keep < 1) $keep = 1;

        $pruned = 0;
        foreach (Site::query()->cursor() as $site) {
            $base = storage_path('app/panel/releases/'.$site->slug);
            if (!is_dir($base)) continue;
            $current = $base.'/current';
            $currentTarget = is_link($current) ? readlink($current) : null;

            $completed = $site->deployments()
                ->where('status', DeploymentStatus::Completed->value)
                ->whereNotNull('finished_at')
                ->orderByDesc('finished_at')
                ->get();

            $toDelete = $completed->slice($keep); // everything after first N
            foreach ($toDelete as $deployment) {
                $path = $deployment->release_path ?? ($base.'/'.($deployment->release_name ?? ''));
                if (!$path || !is_dir($path)) continue;
                if ($currentTarget && realpath($path) === realpath($currentTarget)) {
                    $this->line("Skipping in-use release: {$path}");
                    continue;
                }
                $this->line("Deleting old release: {$path}");
                try {
                    File::deleteDirectory($path);
                    $pruned++;
                } catch (\Throwable $e) {
                    $this->error('Failed to delete '.$path.' : '.$e->getMessage());
                }
            }
        }
        $this->info("Pruned {$pruned} release directories.");
        return self::SUCCESS;
    }
}
