<?php

namespace App\Observers;

use App\Models\Site;

class SiteObserver
{
    public function created(Site $site): void
    {
        activity()->onSite($site)->action('site.created')->meta(['id' => $site->id])->log();
    }

    public function updated(Site $site): void
    {
        activity()->onSite($site)->action('site.updated')->meta([
            'changes' => $site->getChanges(),
        ])->log();
    }

    public function deleted(Site $site): void
    {
        // The model has been deleted, so avoid setting a foreign key to a non-existent site
        activity()->onSite(null)->action('site.deleted')->meta(['id' => $site->id])->log();
    }
}
