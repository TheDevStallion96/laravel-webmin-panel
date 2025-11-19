<?php

namespace App\Actions\Deploy;

use App\Models\Site;

class NpmBuild implements DeployActionInterface
{
    public function name(): string { return 'NPM Build'; }

    public function run(Site $site, string $releasePath, DeploymentLogger $logger): bool
    {
        $logger->line('Running npm ci + build (simulated)');
        @file_put_contents($releasePath.'/.npm', 'built');
        return true;
    }
}
