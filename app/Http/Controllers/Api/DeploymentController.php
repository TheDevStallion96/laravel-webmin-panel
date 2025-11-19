<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Deployment;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Validation\Rule;

class DeploymentController extends Controller
{
    public function index()
    {
        return JsonResource::collection(Deployment::query()->latest()->paginate());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'site_id' => ['required','exists:sites,id'],
            'commit_hash' => ['required','string'],
            'branch' => ['required','string'],
            'status' => ['required', Rule::in(['pending','in_progress','completed','failed'])],
            'started_at' => ['nullable','date'],
            'finished_at' => ['nullable','date'],
            'log_path' => ['nullable','string'],
            'user_id' => ['nullable','exists:users,id'],
        ]);

        $site = Site::findOrFail($validated['site_id']);
        $this->authorize('create', [Deployment::class, $site]);

        $deployment = Deployment::create($validated);
        return JsonResource::make($deployment);
    }

    public function show(Deployment $deployment)
    {
        $this->authorize('view', $deployment);
        return JsonResource::make($deployment);
    }

    public function update(Request $request, Deployment $deployment)
    {
        $this->authorize('update', [\App\Models\Domain::class, $deployment->site]); // reuse site update rule

        $validated = $request->validate([
            'commit_hash' => ['sometimes','string'],
            'branch' => ['sometimes','string'],
            'status' => ['sometimes', Rule::in(['pending','in_progress','completed','failed'])],
            'started_at' => ['nullable','date'],
            'finished_at' => ['nullable','date'],
            'log_path' => ['nullable','string'],
            'user_id' => ['nullable','exists:users,id'],
        ]);

        $deployment->update($validated);
        return JsonResource::make($deployment->refresh());
    }

    public function destroy(Deployment $deployment)
    {
        $this->authorize('cancel', $deployment);
        $deployment->delete();
        return response()->json(['deleted' => true]);
    }
}
