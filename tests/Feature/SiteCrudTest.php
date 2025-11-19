<?php

use App\Enums\SiteStatus;
use App\Models\Site;
use App\Models\User;

test('developer can create pause resume and delete site', function () {
    $dev = User::factory()->developer()->create();

    // Create
    $payload = [
        'name' => 'My Site',
        'slug' => 'my-site',
        'root_path' => '/var/www/my-site',
        'public_dir' => 'public',
        'php_version' => '8.2',
        'repo_url' => 'https://example.com/repo.git',
        'default_branch' => 'main',
        'environment' => ['APP_ENV' => 'production'],
        'deploy_strategy' => 'basic',
    ];

    $this->actingAs($dev)
        ->post('/sites', $payload)
        ->assertRedirect();

    $site = Site::where('slug', 'my-site')->firstOrFail();
    expect($site->status)->toBe(SiteStatus::Active);

    // .env file written
    $envPath = storage_path('sites/'.$site->slug.'/.env');
    expect(file_exists($envPath))->toBeTrue();
    expect(file_get_contents($envPath))->toContain('APP_ENV=production');

    // Update environment and ensure .env rewrites
    $this->actingAs($dev)
        ->patch('/sites/'.$site->id, [
            'environment' => ['APP_ENV' => 'staging', 'NEW_KEY' => 'value'],
        ])
        ->assertRedirect();
    $updatedEnv = file_get_contents($envPath);
    expect($updatedEnv)->toContain('APP_ENV=staging')
        ->and($updatedEnv)->toContain('NEW_KEY=value');

    // Pause
    $this->actingAs($dev)
        ->post('/sites/'.$site->id.'/pause')
        ->assertRedirect();
    expect($site->refresh()->status)->toBe(SiteStatus::Paused);

    // Resume
    $this->actingAs($dev)
        ->post('/sites/'.$site->id.'/resume')
        ->assertRedirect();
    expect($site->refresh()->status)->toBe(SiteStatus::Active);

    // Delete
    $this->actingAs($dev)
        ->delete('/sites/'.$site->id.'?redirect=1')
        ->assertRedirect('/sites');

    expect(Site::whereKey($site->id)->exists())->toBeFalse();
});

test('root path must be unique across sites', function () {
    $dev = User::factory()->developer()->create();
    $payload = [
        'name' => 'Site One',
        'slug' => 'site-one',
        'root_path' => '/var/www/shared-path',
        'public_dir' => 'public',
        'php_version' => '8.2',
        'default_branch' => 'main',
        'environment' => ['APP_ENV' => 'production'],
        'deploy_strategy' => 'basic',
    ];
    $this->actingAs($dev)->post('/sites', $payload)->assertRedirect();

    // Attempt second site with same root_path
    $payload['name'] = 'Site Two';
    $payload['slug'] = 'site-two';
    $this->actingAs($dev)->post('/sites', $payload)->assertSessionHasErrors(['root_path']);
});
