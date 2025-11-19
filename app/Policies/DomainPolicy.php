<?php

namespace App\Policies;

use App\Models\Domain;
use App\Models\Site;
use App\Models\User;

class DomainPolicy
{
    public function view(User $user, Domain $domain): bool
    {
        return $user->isAdmin() || $domain->site && $domain->site->created_by === $user->id;
    }

    public function create(User $user, Site $site): bool
    {
        return $user->isAdmin() || $site->created_by === $user->id;
    }

    public function update(User $user, Domain $domain): bool
    {
        return $user->isAdmin() || ($domain->site && $domain->site->created_by === $user->id);
    }

    public function delete(User $user, Domain $domain): bool
    {
        return $user->isAdmin() || ($domain->site && $domain->site->created_by === $user->id);
    }
}
