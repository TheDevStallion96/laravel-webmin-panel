<?php

namespace App\Services\Deployment;

use App\Models\Deployment;

interface DeploymentStrategy
{
    public function onCreated(Deployment $deployment): void;
    public function onStatusChanged(Deployment $deployment): void;
}
