<?php

use App\Models\Site;
use App\Models\User;
use Illuminate\Filesystem\Filesystem;

it('returns dry-run diff for webserver rebuild', function () {
    $user = User::factory()->admin()->create();
    $site = Site::factory()->create();
    $this->actingAs($user);

    $response = $this->post(route('sites.webserver.rebuild', [$site, 'dry' => 1, 'diff' => 1]));
    $response->assertOk();
    $json = $response->json();
    expect($json['dry'])->toBeTrue()
        ->and($json['vhost']['config'])->toBeString()
        ->and($json['vhost']['changed'])->toBeTrue()
        ->and(isset($json['vhost']['diff']))->toBeTrue()
        ->and($json['pool']['config'])->toBeString()
        ->and($json['pool']['changed'])->toBeTrue()
        ->and(isset($json['pool']['diff']))->toBeTrue();
});

it('writes configs and reloads on rebuild without dry-run', function () {
    $user = User::factory()->admin()->create();
    $site = Site::factory()->create();
    $this->actingAs($user);

    $response = $this->post(route('sites.webserver.rebuild', $site));
    $response->assertRedirect(route('sites.show', $site));
    $response->assertSessionHas('status', 'webserver-rebuilt');

    $fs = new Filesystem();
    $base = dirname(__DIR__, 2).'/storage/app/panel/configs/'.$site->slug;
    expect($fs->exists($base.'/nginx.conf'))->toBeTrue();
    expect($fs->exists($base.'/php-fpm-pool.conf'))->toBeTrue();
});
