<?php

namespace App\Http\Controllers;

use App\Enums\SiteStatus;
use App\Models\Site;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class SitesResumeController extends Controller
{
    public function store(Site $site): RedirectResponse
    {
        Gate::authorize('manage-site');
        $this->authorize('update', $site);
        $site->status = SiteStatus::Active;
        $site->save();
        // Explicit activity entry for resume action distinct from generic update observer
        activity()->onSite($site)->action('site.resumed')->meta(['id' => $site->id])->log();
        return redirect()->route('sites.show', $site)->with('status', 'site-resumed');
    }
}
