<?php

namespace App\Actions\Deploy;

use App\Models\Site;

class ComposerInstall implements DeployActionInterface
{
    public function name(): string { return 'Composer Install'; }

    public function run(Site $site, string $releasePath, DeploymentLogger $logger): bool
    {
        $logger->line('Running composer install (simulated)');
        @file_put_contents($releasePath.'/.composer', 'installed');
        return true;
    }
}
