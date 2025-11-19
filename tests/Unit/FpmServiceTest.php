<?php

use App\Models\Site;
use App\Services\PHP\FpmService;
use App\Services\TemplateRenderer;
use Tests\Fakes\FakeShell;
use Illuminate\Filesystem\Filesystem;

it('writes php-fpm pool config', function () {
    $site = new Site([
        'php_version' => '8.2',
        'root_path' => '/var/www/example',
        'slug' => 'example',
    ]);
    $shell = new FakeShell();
    $service = new FpmService(new TemplateRenderer(), $shell, new Filesystem());
    $config = $service->writePool($site);
    expect($config)->toContain('['.$site->slug.']')
        ->and($config)->toContain('php'.$site->php_version.'-fpm-'.$site->slug.'.sock');
});
