<?php

namespace App\Services\Deployment;

use App\Enums\DeployStrategy as DeployStrategyEnum;
use App\Models\Deployment;
use App\Models\Site;

class DeploymentService
{
    public function strategyFor(Site $site): DeploymentStrategy
    {
        $strategy = $site->deploy_strategy instanceof DeployStrategyEnum ? $site->deploy_strategy : DeployStrategyEnum::from($site->deploy_strategy);

        return match ($strategy) {
            DeployStrategyEnum::ZeroDowntime => app(ZeroDowntimeStrategy::class),
            default => app(BasicStrategy::class),
        };
    }

    public function onCreated(Deployment $deployment): void
    {
        $this->strategyFor($deployment->site)->onCreated($deployment);
    }

    public function onStatusChanged(Deployment $deployment): void
    {
        $this->strategyFor($deployment->site)->onStatusChanged($deployment);
    }
}
