<?php

namespace App\Services;

use App\Models\Site;
use App\Models\Domain;
use App\Models\SslCertificate;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Carbon;

class SslCertificateService
{
    public function __construct(private Filesystem $fs = new Filesystem()) {}

    /**
     * Provision a Let's Encrypt certificate (simulated) for the primary domain.
     * Writes placeholder cert/key files and records expiry.
     */
    public function provisionLetsEncrypt(Site $site, Domain $primaryDomain): SslCertificate
    {
        $dir = storage_path('app/panel/certs/'.$site->slug);
        if (! $this->fs->isDirectory($dir)) {
            $this->fs->makeDirectory($dir, 0755, true);
        }
        $certPath = $dir.'/fullchain.pem';
        $keyPath = $dir.'/privkey.pem';
        // Simulate certbot output
        $this->fs->put($certPath, "---BEGIN CERTIFICATE---\nFAKE-LE-CERT for {$primaryDomain->hostname}\n---END CERTIFICATE---");
        $this->fs->put($keyPath, "---BEGIN PRIVATE KEY---\nFAKE-KEY {$primaryDomain->hostname}\n---END PRIVATE KEY---");
        $expires = Carbon::now()->addDays(90);
        $cert = SslCertificate::create([
            'site_id' => $site->id,
            'type' => 'letsencrypt',
            'common_name' => $primaryDomain->hostname,
            'expires_at' => $expires,
            'path_cert' => $certPath,
            'path_key' => $keyPath,
            'last_renewed_at' => Carbon::now(),
            'status' => 'active',
        ]);
        if (function_exists('activity')) {
            activity()->onSite($site)->action('ssl.provisioned')->meta([
                'domain' => $primaryDomain->hostname,
                'expires_at' => $expires->toDateTimeString(),
            ])->log();
        }
        return $cert;
    }
}
