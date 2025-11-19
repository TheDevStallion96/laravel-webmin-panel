<?php

namespace App\Http\Controllers;

use App\Http\Requests\DomainStoreRequest;
use App\Models\Domain;
use App\Models\Site;
use App\Services\WebServer\NginxService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DomainsController extends Controller
{
    public function index(Request $request, Site $site): View
    {
        $domains = $site->domains()->orderByDesc('is_primary')->orderBy('hostname')->get();
        $certificate = $site->sslCertificates()->latest()->first();
        return view('domains.index', compact('site','domains','certificate'));
    }

    public function store(DomainStoreRequest $request, Site $site): RedirectResponse
    {
        $data = $request->validated();
        $data['site_id'] = $site->id;
        $domain = Domain::create($data);
        if ($domain->is_primary) {
            $site->domains()->where('id','!=',$domain->id)->update(['is_primary' => false]);
        }
        return redirect()->route('sites.domains.index', $site)->with('status','domain-created');
    }

    public function destroy(Request $request, Site $site, Domain $domain): RedirectResponse
    {
        $this->authorize('delete', $domain);
        $domain->delete();
        return redirect()->route('sites.domains.index', $site)->with('status','domain-deleted');
    }

    public function makePrimary(Request $request, Site $site, Domain $domain): RedirectResponse
    {
        $this->authorize('update', $domain);
        $site->domains()->update(['is_primary' => false]);
        $domain->update(['is_primary' => true]);
        return redirect()->route('sites.domains.index', $site)->with('status','domain-primary-updated');
    }

    public function toggleHttpsForced(Request $request, Site $site, Domain $domain, NginxService $nginx): RedirectResponse
    {
        $this->authorize('update', $domain);
        $domain->update(['https_forced' => ! $domain->https_forced]);
        // Re-write vhost if cert exists
        $certificate = $site->sslCertificates()->latest()->first();
        $httpsForced = $site->domains()->where('https_forced', true)->exists();
        if ($certificate) {
            $nginx->writeSiteVhost($site, $site->domains()->get(), false, false, $certificate, $httpsForced);
        }
        return redirect()->route('sites.domains.index', $site)->with('status','domain-https-forced-toggled');
    }
}
