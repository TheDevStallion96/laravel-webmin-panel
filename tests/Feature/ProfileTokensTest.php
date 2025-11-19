<?php

use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

test('user can create and revoke token via profile page', function () {
    $user = User::factory()->create();
    actingAs($user);

    // View profile page contains token form
    get(route('profile.edit'))
        ->assertOk()
        ->assertSee('API Tokens');

    // Create token
    post(route('tokens.store'), [
        'name' => 'Test Token',
        'abilities' => $user->allowedTokenAbilities(),
    ])->assertRedirect(route('tokens.index'));

    // Redirect goes to tokens.index, but profile should reflect afterwards
    get(route('profile.edit'))
        ->assertOk()
        ->assertSee('Test Token');

    $token = $user->tokens()->first();
    expect($token)->not()->toBeNull();

    // Revoke
    post(route('tokens.destroy', $token->id), ['_method' => 'DELETE'])
        ->assertRedirect(route('tokens.index'));

    get(route('profile.edit'))
        ->assertOk()
        ->assertDontSee('Test Token');
});
