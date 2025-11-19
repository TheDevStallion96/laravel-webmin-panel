<?php

use App\Models\Site;
use App\Services\WebServer\NginxService;
use App\Services\TemplateRenderer;
use Tests\Fakes\FakeShell;
use Illuminate\Filesystem\Filesystem;

it('writes nginx config with domains and is idempotent', function () {
    $site = new Site([
        'php_version' => '8.2',
        'root_path' => '/var/www/example',
        'public_dir' => 'public',
        'slug' => 'example',
    ]);
    $domains = collect([(object)['hostname' => 'example.com'], (object)['hostname' => 'www.example.com']]);
    $shell = new FakeShell();
    $service = new NginxService(new TemplateRenderer(), $shell, new Filesystem());
    $config1 = $service->writeSiteVhost($site, $domains);
    $config2 = $service->writeSiteVhost($site, $domains); // second write should not duplicate side effects
    expect($config1)->toBeString()->and($config1)->toContain('example.com www.example.com');
    expect($config2)->toBe($config1);
});
