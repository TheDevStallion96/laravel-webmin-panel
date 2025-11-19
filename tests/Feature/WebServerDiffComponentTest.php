<?php

use App\Models\Site;
use App\Models\User;

test('site show view contains webserver rebuild controls for manager', function () {
    $admin = User::factory()->admin()->create();
    $site = Site::factory()->create(['created_by' => $admin->id]);
    $this->actingAs($admin)
        ->get(route('sites.show', $site))
        ->assertOk()
        ->assertSee('Web Server Config')
        ->assertSee('Dry-run Diff')
        ->assertSee('Rebuild');
});

test('viewer cannot see rebuild controls', function () {
    $viewer = User::factory()->viewer()->create();
    $site = Site::factory()->create(['created_by' => $viewer->id]);
    $this->actingAs($viewer)
        ->get(route('sites.show', $site))
        ->assertOk()
        ->assertDontSeeText('Web Server Config')
        ->assertDontSeeText('Dry-run Diff');
});
