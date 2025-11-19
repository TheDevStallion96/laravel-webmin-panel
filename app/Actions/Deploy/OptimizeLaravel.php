<?php

namespace App\Actions\Deploy;

use App\Models\Site;

class OptimizeLaravel implements DeployActionInterface
{
    public function name(): string { return 'Optimize Laravel'; }

    public function run(Site $site, string $releasePath, DeploymentLogger $logger): bool
    {
        $logger->line('Clearing and optimizing caches (simulated)');
        @file_put_contents($releasePath.'/.optimized', 'ok');
        return true;
    }
}
