<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Services\WebServer\NginxService;
use App\Services\PHP\FpmService;
use Illuminate\Http\Request;

class WebServerController extends Controller
{
    public function rebuild(Request $request, Site $site, NginxService $nginx, FpmService $fpm)
    {
        // Authorization: ensure user can manage this site
        $this->authorize('update', $site);

        $dry = (bool)$request->query('dry');
        $withDiff = (bool)$request->query('diff');

        // Domains relation may not be eager loaded; attempt ->domains if relation exists else fall back to primary domain slug
        $domains = method_exists($site, 'domains') ? $site->domains()->get() : [$site->slug.'.local'];

        if ($dry) {
            $vhost = $nginx->writeSiteVhost($site, $domains, true, $withDiff);
            $pool = $fpm->writePool($site, true, $withDiff);
            return response()->json([
                'dry' => true,
                'vhost' => $vhost,
                'pool' => $pool,
            ]);
        }

        $nginx->writeSiteVhost($site, $domains, false);
        $fpm->writePool($site, false);

        // Reload services; failure does not abort write but is reported
        $nginxReloaded = $nginx->testConfig() && $nginx->reload();
        $fpmReloaded = $fpm->reload($site->php_version);

        if (function_exists('activity')) {
            activity()->onSite($site)->action('webserver.rebuilt')->meta([
                'nginx_reloaded' => $nginxReloaded,
                'fpm_reloaded' => $fpmReloaded,
            ])->log();
        }

        return redirect()->route('sites.show', $site)->with('status', 'webserver-rebuilt');
    }
}
