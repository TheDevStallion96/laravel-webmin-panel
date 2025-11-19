<?php

namespace App\Policies;

use App\Models\Site;
use App\Models\User;

class SitePolicy
{
    public function viewAny(User $user): bool
    {
        return (bool) $user; // any authenticated user
    }

    public function view(User $user, Site $site): bool
    {
        // Admin or creator can view
        return $user->isAdmin() || $site->created_by === $user->id;
    }

    public function create(User $user): bool
    {
        // Any authenticated user can create their own site (route gates may further restrict)
        return (bool) $user;
    }

    public function update(User $user, Site $site): bool
    {
        // Admin can update any; owners can update their own
        return $user->isAdmin() || $site->created_by === $user->id;
    }

    public function delete(User $user, Site $site): bool
    {
        // Admin can delete any; owners can delete their own
        return $user->isAdmin() || $site->created_by === $user->id;
    }
}
