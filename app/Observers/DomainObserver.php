<?php

namespace App\Observers;

use App\Models\Domain;

class DomainObserver
{
    public function created(Domain $domain): void
    {
        activity()->onSite($domain->site_id)->action('domain.created')->meta([
            'hostname' => $domain->hostname,
            'is_primary' => $domain->is_primary,
        ])->log();
    }

    public function updated(Domain $domain): void
    {
        activity()->onSite($domain->site_id)->action('domain.updated')->meta([
            'changes' => $domain->getChanges(),
        ])->log();
    }

    public function deleted(Domain $domain): void
    {
        activity()->onSite($domain->site_id)->action('domain.deleted')->meta([
            'hostname' => $domain->hostname,
        ])->log();
    }
}
