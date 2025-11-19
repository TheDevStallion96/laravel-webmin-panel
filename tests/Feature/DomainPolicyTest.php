<?php

use App\Models\Domain;
use App\Models\Site;
use App\Models\User;

it('user can manage domains on their sites', function () {
    $user = User::factory()->create();
    $site = Site::factory()->create(['created_by' => $user->id]);
    $domain = Domain::factory()->for($site)->create();

    expect($user->can('view', $domain))->toBeTrue();
    expect($user->can('update', $domain))->toBeTrue();
    expect($user->can('delete', $domain))->toBeTrue();
    expect($user->can('create', [Domain::class, $site]))->toBeTrue();
});

it('user cannot manage domains on other sites', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $site = Site::factory()->create(['created_by' => $other->id]);
    $domain = Domain::factory()->for($site)->create();

    expect($user->can('view', $domain))->toBeFalse();
    expect($user->can('update', $domain))->toBeFalse();
    expect($user->can('delete', $domain))->toBeFalse();
    expect($user->can('create', [Domain::class, $site]))->toBeFalse();
});

it('admin can manage all domains', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $site = Site::factory()->create();
    $domain = Domain::factory()->for($site)->create();

    expect($admin->can('view', $domain))->toBeTrue();
    expect($admin->can('update', $domain))->toBeTrue();
    expect($admin->can('delete', $domain))->toBeTrue();
    expect($admin->can('create', [Domain::class, $site]))->toBeTrue();
});
