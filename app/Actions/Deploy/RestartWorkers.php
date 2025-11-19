<?php

namespace App\Actions\Deploy;

use App\Models\Site;

class RestartWorkers implements DeployActionInterface
{
    public function name(): string { return 'Restart Workers'; }

    public function run(Site $site, string $releasePath, DeploymentLogger $logger): bool
    {
        $logger->line('Restarting queue workers (simulated)');
        @file_put_contents($releasePath.'/.workers_restarted', 'yes');
        return true;
    }
}
