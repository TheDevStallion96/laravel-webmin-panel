<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Validation\Rule;

class SiteController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Site::class);
        return JsonResource::collection(Site::query()->latest()->paginate());
    }

    public function store(Request $request)
    {
        $this->authorize('create', Site::class);
        $validated = $request->validate([
            'name' => ['required','string','max:255'],
            'slug' => ['required','string','max:255','unique:sites,slug'],
            'root_path' => ['required','string'],
            'public_dir' => ['required','string'],
            'php_version' => ['required','string'],
            'repo_url' => ['nullable','string'],
            'default_branch' => ['required','string'],
            'status' => ['required',Rule::in(['active','paused','error'])],
            'environment' => ['array'],
            'deploy_strategy' => ['required',Rule::in(['basic','zero_downtime'])],
        ]);

        $validated['created_by'] = $request->user()->id;

        $site = Site::create($validated);
        return JsonResource::make($site);
    }

    public function show(Site $site)
    {
        $this->authorize('view', $site);
        return JsonResource::make($site);
    }

    public function update(Request $request, Site $site)
    {
        $this->authorize('update', $site);
        $validated = $request->validate([
            'name' => ['sometimes','string','max:255'],
            'slug' => ['sometimes','string','max:255',Rule::unique('sites','slug')->ignore($site->id)],
            'root_path' => ['sometimes','string'],
            'public_dir' => ['sometimes','string'],
            'php_version' => ['sometimes','string'],
            'repo_url' => ['nullable','string'],
            'default_branch' => ['sometimes','string'],
            'status' => ['sometimes',Rule::in(['active','paused','error'])],
            'environment' => ['sometimes','array'],
            'deploy_strategy' => ['sometimes',Rule::in(['basic','zero_downtime'])],
        ]);

        $site->update($validated);
        return JsonResource::make($site->refresh());
    }

    public function destroy(Site $site)
    {
        $this->authorize('delete', $site);
        $site->delete();
        return response()->json(['deleted' => true]);
    }
}
