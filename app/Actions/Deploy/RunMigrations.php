<?php

namespace App\Actions\Deploy;

use App\Models\Site;

class RunMigrations implements DeployActionInterface
{
    public function name(): string { return 'Run Migrations'; }

    public function run(Site $site, string $releasePath, DeploymentLogger $logger): bool
    {
        $logger->line('Running artisan migrate (simulated)');
        @file_put_contents($releasePath.'/.migrated', 'yes');
        return true;
    }
}
