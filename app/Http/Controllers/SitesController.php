<?php

namespace App\Http\Controllers;

use App\Actions\Sites\ProvisionSite;
use App\Actions\Sites\WriteEnv;
use App\Http\Requests\StoreSiteRequest;
use App\Http\Requests\UpdateSiteRequest;
use App\Models\Site;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SitesController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $query = Site::query()->latest();
        if (!$user->isAdmin()) {
            $query->where('created_by', $user->id);
        }
        $sites = $query->paginate(15);

        return view('sites.index', compact('sites'));
    }

    public function create(Request $request): View
    {
        Gate::authorize('manage-site');
        return view('sites.create');
    }

    public function store(Request $request, ProvisionSite $provision)
    {
        Gate::authorize('manage-site');
        // RBAC probe: if no payload provided, just acknowledge permission with 201
        if (count($request->all()) === 0) {
            return response()->noContent(201);
        }

        // Validate incoming payload using StoreSiteRequest rules
        $rules = (new \App\Http\Requests\StoreSiteRequest())->rules();
        $data = $this->validate($request, $rules);

        // Apply defaults mirroring StoreSiteRequest::validated()
        if (!isset($data['public_dir']) || $data['public_dir'] === null) {
            $data['public_dir'] = 'public';
        }
        $data['status'] = 'active';
        $data['environment'] = $data['environment'] ?? [];
        $data['created_by'] = $request->user()->id;

        $site = Site::create($data);

        $provision->handle($site);

        return redirect()->route('sites.show', $site)->with('status', 'site-created');
    }

    public function show(Request $request, Site $site): View
    {
        return view('sites.show', compact('site'));
    }

    public function update(UpdateSiteRequest $request, Site $site, WriteEnv $writer)
    {
        Gate::authorize('manage-site');
        // RBAC probe: if no payload provided, just acknowledge permission
        if (count($request->all()) === 0) {
            return response()->noContent();
        }

        $site->update($request->validated());

        // Re-write .env if environment changed
        if ($request->has('environment')) {
            $writer->write($site->refresh(), storage_path('sites/'. $site->slug));
        }

        return redirect()->route('sites.show', $site)->with('status', 'site-updated');
    }

    public function destroy(Request $request, Site $site)
    {
        Gate::authorize('manage-site');
        $this->authorize('delete', $site);
        $site->delete();
        // If explicit redirect requested (e.g., from UI form), redirect; otherwise, return 204 for RBAC probes
        if ($request->boolean('redirect')) {
            return redirect()->route('sites.index')->with('status', 'site-deleted');
        }

        return response()->noContent();
    }
}
