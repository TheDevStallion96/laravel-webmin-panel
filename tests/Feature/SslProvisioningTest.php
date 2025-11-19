<?php

use App\Models\Site;
use App\Models\Domain;
use App\Services\WebServer\NginxService;
use Illuminate\Filesystem\Filesystem;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

test('provisioning certificate writes files and updates vhost', function () {
    $user = \App\Models\User::factory()->create(['is_admin' => true]);
    $site = Site::factory()->create(['created_by' => $user->id, 'status' => 'active']);
    actingAs($user);
    $domain = Domain::create([
        'site_id' => $site->id,
        'hostname' => 'example.test',
        'is_primary' => true,
        'https_forced' => false,
    ]);

    post(route('sites.certificates.provision', $site))->assertRedirect();
    $site->refresh();
    $cert = $site->sslCertificates()->first();
    expect($cert)->not()->toBeNull();
    $fs = new Filesystem();
    expect($fs->exists($cert->path_cert))->toBeTrue();
    expect($fs->exists($cert->path_key))->toBeTrue();

    // toggle https forced
    post(route('sites.domains.https', [$site, $domain]))->assertRedirect();
    $nginxConf = storage_path('app/panel/configs/'.$site->slug.'/nginx.conf');
    expect($fs->exists($nginxConf))->toBeTrue();
    $contents = $fs->get($nginxConf);
    expect($contents)->toContain('ssl_certificate');
    expect($contents)->toContain('listen 443 ssl;');
    expect($contents)->toContain('return 301 https://');
});
