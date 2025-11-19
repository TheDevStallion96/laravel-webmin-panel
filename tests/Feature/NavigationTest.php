<?php

use App\Models\User;

it('shows Sites link in primary navigation for authenticated user', function () {
    $user = User::factory()->viewer()->create();
    $this->actingAs($user)
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('Sites');
});

it('shows Sites link in responsive navigation for authenticated user', function () {
    $user = User::factory()->developer()->create();
    $this->actingAs($user)
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('Sites');
});
