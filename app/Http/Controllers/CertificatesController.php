<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Services\SslCertificateService;
use App\Services\WebServer\NginxService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CertificatesController extends Controller
{
    public function provisionLetsEncrypt(Request $request, Site $site, SslCertificateService $provisioner, NginxService $nginx): RedirectResponse
    {
        // Ensure primary domain exists
        $primary = $site->domains()->where('is_primary', true)->first();
        if (!$primary) {
            return redirect()->route('sites.domains.index', $site)->with('status','no-primary-domain');
        }
        // Simulate provisioning
        $cert = $provisioner->provisionLetsEncrypt($site, $primary);
        // Re-write vhost including SSL
        $httpsForced = $site->domains()->where('https_forced', true)->exists();
        $nginx->writeSiteVhost($site, $site->domains()->get(), false, false, $cert, $httpsForced);
        return redirect()->route('sites.domains.index', $site)->with('status','certificate-provisioned');
    }
}
