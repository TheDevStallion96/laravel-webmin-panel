<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DomainController extends Controller
{
    public function index()
    {
        return JsonResource::collection(Domain::query()->latest()->paginate());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'site_id' => ['required','exists:sites,id'],
            'hostname' => ['required','string','unique:domains,hostname'],
            'is_primary' => ['boolean'],
            'https_forced' => ['boolean'],
        ]);

        $site = Site::findOrFail($validated['site_id']);
        $this->authorize('create', [Domain::class, $site]);

        $domain = Domain::create($validated);
        return JsonResource::make($domain);
    }

    public function show(Domain $domain)
    {
        $this->authorize('view', $domain);
        return JsonResource::make($domain);
    }

    public function update(Request $request, Domain $domain)
    {
        $this->authorize('update', $domain);
        $validated = $request->validate([
            'hostname' => ['sometimes','string','unique:domains,hostname,'.$domain->id],
            'is_primary' => ['sometimes','boolean'],
            'https_forced' => ['sometimes','boolean'],
        ]);

        $domain->update($validated);
        return JsonResource::make($domain->refresh());
    }

    public function destroy(Domain $domain)
    {
        $this->authorize('delete', $domain);
        $domain->delete();
        return response()->json(['deleted' => true]);
    }
}
