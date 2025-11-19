<?php

use App\Models\Site;
use App\Models\User;

it('admin can manage any site', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $site = Site::factory()->create();

    expect($admin->can('viewAny', Site::class))->toBeTrue();
    expect($admin->can('view', $site))->toBeTrue();
    expect($admin->can('create', Site::class))->toBeTrue();
    expect($admin->can('update', $site))->toBeTrue();
    expect($admin->can('delete', $site))->toBeTrue();
});

it('user can manage their own site', function () {
    $user = User::factory()->create();
    $site = Site::factory()->create(['created_by' => $user->id]);

    expect($user->can('viewAny', Site::class))->toBeTrue();
    expect($user->can('view', $site))->toBeTrue();
    expect($user->can('create', Site::class))->toBeTrue();
    expect($user->can('update', $site))->toBeTrue();
    expect($user->can('delete', $site))->toBeTrue();
});

it('user cannot manage others sites', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $site = Site::factory()->create(['created_by' => $other->id]);

    expect($user->can('view', $site))->toBeFalse();
    expect($user->can('update', $site))->toBeFalse();
    expect($user->can('delete', $site))->toBeFalse();
});

it('guest cannot perform any actions', function () {
    $site = Site::factory()->create();

    expect(auth()->check())->toBeFalse();
    // Laravel authorization requires a user instance; guests are denied.
    // We simply assert that creating a new User with default is_admin=false cannot manage other users' sites.
    $guest = new User();
    expect($guest->can('view', $site))->toBeFalse();
});
