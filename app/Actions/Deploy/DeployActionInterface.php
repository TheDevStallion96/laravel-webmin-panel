<?php

namespace App\Actions\Deploy;

use App\Models\Site;

interface DeployActionInterface
{
    /**
     * Execute the deployment step.
     * Return true on success, false on failure.
     */
    public function run(Site $site, string $releasePath, DeploymentLogger $logger): bool;

    /** Human readable step name. */
    public function name(): string;
}
