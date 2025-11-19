<?php

namespace App\Services\Deployment;

use App\Models\Deployment;

class ZeroDowntimeStrategy implements DeploymentStrategy
{
    public function onCreated(Deployment $deployment): void
    {
        activity()->onSite($deployment->site_id)->action('deployment.strategy.zd.prepare')->meta(['deployment_id' => $deployment->id])->log();
    }

    public function onStatusChanged(Deployment $deployment): void
    {
        activity()->onSite($deployment->site_id)->action('deployment.strategy.zd.status')->meta([
            'deployment_id' => $deployment->id,
            'status' => (string) ($deployment->status?->value ?? $deployment->status),
        ])->log();
    }
}
