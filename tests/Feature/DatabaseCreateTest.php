<?php

use App\Models\Site;
use App\Services\Database\DatabaseManager;
use Tests\Fakes\FakeDatabaseManager;
use Illuminate\Support\Facades\Storage;

test('creating a database updates site environment and persists record', function () {
    $this->app->instance(DatabaseManager::class, new FakeDatabaseManager());

    $user = \App\Models\User::factory()->developer()->create();
    $site = Site::factory()->create(['created_by' => $user->id]);
    $this->actingAs($user);

    $response = $this->post(route('sites.databases.store', $site), [
        'engine' => 'mysql',
        'name' => 'app_test_db',
    ]);

    $response->assertRedirect(route('sites.databases.index', $site));
    $response->assertSessionHas('status', 'database-created');

    $this->assertDatabaseHas('databases', [
        'site_id' => $site->id,
        'name' => 'app_test_db',
        'engine' => 'mysql',
    ]);

    $site->refresh();
    expect($site->environment['db_database'])->toBe('app_test_db');
    expect($site->environment['db_connection'])->toBe('mysql');
    expect($site->environment['db_username'])->not()->toBeEmpty();
    expect($site->environment['db_password'])->not()->toBeEmpty();

    // .env file should be written containing DB_DATABASE entry
    $envPath = storage_path('sites/'.$site->slug.'/.env');
    expect(is_file($envPath))->toBeTrue();
    $contents = file_get_contents($envPath);
    expect($contents)->toContain('DB_DATABASE=app_test_db');
});
