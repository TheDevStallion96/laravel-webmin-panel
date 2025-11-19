<?php

namespace App\Observers;

use App\Enums\DeploymentStatus;
use App\Models\Deployment;
use App\Services\Deployment\DeploymentService;

class DeploymentObserver
{
    public function created(Deployment $deployment): void
    {
        activity()->onSite($deployment->site_id)->byUser($deployment->user_id)
            ->action('deployment.created')->meta([
                'id' => $deployment->id,
                'branch' => $deployment->branch,
                'commit' => $deployment->commit_hash,
            ])->log();

        // Strategy hook
        app(DeploymentService::class)->onCreated($deployment);
    }

    public function updated(Deployment $deployment): void
    {
        if ($deployment->wasChanged('status')) {
            $status = $deployment->status instanceof DeploymentStatus ? $deployment->status->value : $deployment->status;
            activity()->onSite($deployment->site_id)->byUser($deployment->user_id)
                ->action('deployment.status_changed')->meta([
                    'id' => $deployment->id,
                    'status' => $status,
                ])->log();

            app(DeploymentService::class)->onStatusChanged($deployment);
        }
    }

    public function deleted(Deployment $deployment): void
    {
        activity()->onSite($deployment->site_id)->byUser($deployment->user_id)
            ->action('deployment.deleted')->meta(['id' => $deployment->id])->log();
    }
}
