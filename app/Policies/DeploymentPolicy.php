<?php

namespace App\Policies;

use App\Models\Deployment;
use App\Models\Site;
use App\Models\User;

class DeploymentPolicy
{
    public function view(User $user, Deployment $deployment): bool
    {
        return $user->isAdmin() || ($deployment->site && $deployment->site->created_by === $user->id);
    }

    public function create(User $user, Site $site): bool
    {
        return $user->isAdmin() || $site->created_by === $user->id;
    }

    public function cancel(User $user, Deployment $deployment): bool
    {
        return $user->isAdmin() || ($deployment->site && $deployment->site->created_by === $user->id);
    }
}
