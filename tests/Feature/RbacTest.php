<?php

use App\Enums\Role;
use App\Models\Site;
use App\Models\User;
use Illuminate\Support\Str;

test('viewer can view sites but cannot mutate or access admin', function () {
    $viewer = User::factory()->viewer()->create();
    $creator = User::factory()->developer()->create();
    $site = Site::factory()->create(['created_by' => $creator->id]);

    // can view list and show
    $this->actingAs($viewer)
        ->get('/sites')
        ->assertStatus(200);

    $this->actingAs($viewer)
        ->get('/sites/'.$site->id)
        ->assertStatus(200);

    // cannot create/update/delete
    $this->actingAs($viewer)
        ->post('/sites')
        ->assertForbidden();

    $this->actingAs($viewer)
        ->patch('/sites/'.$site->id)
        ->assertForbidden();

    $this->actingAs($viewer)
        ->delete('/sites/'.$site->id)
        ->assertForbidden();

    // cannot access admin
    $this->actingAs($viewer)
        ->get('/admin/servers')
        ->assertForbidden();
});

test('developer can manage own sites but not admin endpoints', function () {
    $developer = User::factory()->developer()->create();
    $other = User::factory()->developer()->create();
    $ownSite = Site::factory()->create(['created_by' => $developer->id]);
    $otherSite = Site::factory()->create(['created_by' => $other->id]);

    // can view
    $this->actingAs($developer)
        ->get('/sites')
        ->assertOk();

    // can create (authorized gate and policy)
    $this->actingAs($developer)
        ->post('/sites')
        ->assertNoContent(201);

    // can update/delete own
    $this->actingAs($developer)
        ->patch('/sites/'.$ownSite->id)
        ->assertNoContent();

    $this->actingAs($developer)
        ->delete('/sites/'.$ownSite->id)
        ->assertNoContent();

    // cannot update/delete others
    $this->actingAs($developer)
        ->patch('/sites/'.$otherSite->id)
        ->assertForbidden();

    $this->actingAs($developer)
        ->delete('/sites/'.$otherSite->id)
        ->assertForbidden();

    // cannot access admin endpoints
    $this->actingAs($developer)
        ->get('/admin/servers')
        ->assertForbidden();
});

test('admin can do everything', function () {
    $admin = User::factory()->admin()->create();
    $site = Site::factory()->create();

    $this->actingAs($admin)
        ->get('/sites')
        ->assertOk();

    $this->actingAs($admin)
        ->post('/sites')
        ->assertNoContent(201);

    $this->actingAs($admin)
        ->patch('/sites/'.$site->id)
        ->assertNoContent();

    $this->actingAs($admin)
        ->delete('/sites/'.$site->id)
        ->assertNoContent();

    $this->actingAs($admin)
        ->get('/admin/servers')
        ->assertOk();
});

test('api tokens authenticate and enforce abilities', function () {
    $dev = User::factory()->developer()->create();

    // create token with only view-site
    $viewToken = $dev->createToken('view', ['view-site'])->plainTextToken;
    $manageToken = $dev->createToken('manage', ['manage-site','view-site'])->plainTextToken;

    // status endpoint requires view-site
    $this->withHeader('Authorization', 'Bearer '.$viewToken)
        ->getJson('/api/status')
        ->assertOk();

    // manage action should be forbidden with only view-site
    $this->withHeader('Authorization', 'Bearer '.$viewToken)
        ->postJson('/api/manage-site-action')
        ->assertForbidden();

    // manage with proper ability succeeds
    $this->withHeader('Authorization', 'Bearer '.$manageToken)
        ->postJson('/api/manage-site-action')
        ->assertOk();
});
