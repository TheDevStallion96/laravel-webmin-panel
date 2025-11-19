<?php

namespace App\Actions\Deploy;

use App\Models\Site;
use Illuminate\Support\Str;

class GitCloneOrPull implements DeployActionInterface
{
    public function name(): string { return 'Git Clone/Pull'; }

    public function run(Site $site, string $releasePath, DeploymentLogger $logger): bool
    {
        // Simulate git clone/pull; record pseudo commit hash
        $commit = Str::random(10);
        $logger->line("Cloning/pulling repo {$site->repo_url} branch {$site->default_branch} -> commit {$commit}");
        // Write a placeholder file
        @mkdir($releasePath, 0755, true);
        @file_put_contents($releasePath.'/.commit', $commit);
        return true;
    }
}
