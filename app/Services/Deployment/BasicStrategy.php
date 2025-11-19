<?php

namespace App\Services\Deployment;

use App\Models\Deployment;

class BasicStrategy implements DeploymentStrategy
{
    public function onCreated(Deployment $deployment): void
    {
        activity()->onSite($deployment->site_id)->action('deployment.strategy.basic.created')->meta(['deployment_id' => $deployment->id])->log();
    }

    public function onStatusChanged(Deployment $deployment): void
    {
        activity()->onSite($deployment->site_id)->action('deployment.strategy.basic.status')->meta([
            'deployment_id' => $deployment->id,
            'status' => (string) ($deployment->status?->value ?? $deployment->status),
        ])->log();
    }
}
