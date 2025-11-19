<?php

namespace App\Http\Controllers;

use App\Enums\SiteStatus;
use App\Models\Site;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class SitesPauseController extends Controller
{
    public function store(Site $site): RedirectResponse
    {
        Gate::authorize('manage-site');
        $this->authorize('update', $site);
        $site->status = SiteStatus::Paused;
        $site->save();
        // Explicit activity entry for pause action distinct from generic update observer
        activity()->onSite($site)->action('site.paused')->meta(['id' => $site->id])->log();
        return redirect()->route('sites.show', $site)->with('status', 'site-paused');
    }
}
