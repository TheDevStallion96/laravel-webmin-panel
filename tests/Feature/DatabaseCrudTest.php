<?php

use App\Models\Site;
use App\Models\User;
use App\Services\Database\DatabaseManager;
use App\Services\Database\DatabaseDriverInterface;
use Illuminate\Support\Facades\Crypt;

class FakeDriver implements DatabaseDriverInterface {
    public array $calls = [];
    public function createDatabase(string $name): bool { $this->calls[] = ['createDatabase',$name]; return true; }
    public function dropDatabase(string $name): bool { $this->calls[] = ['dropDatabase',$name]; return true; }
    public function createUser(string $username, string $password): bool { $this->calls[] = ['createUser',$username]; return true; }
    public function grant(string $username, string $database): bool { $this->calls[] = ['grant',$username,$database]; return true; }
    public function revoke(string $username, string $database): bool { $this->calls[] = ['revoke',$username,$database]; return true; }
    public function dump(string $database, string $path): bool { return true; }
    public function restore(string $database, string $path): bool { return true; }
}

it('can create and delete a database for a site', function () {
    $admin = User::factory()->admin()->create();
    $site = Site::factory()->create(['created_by' => $admin->id]);
    $this->actingAs($admin);

    // Swap manager with fake drivers
    $fake = new FakeDriver();
    $this->instance(DatabaseManager::class, new class($fake) extends DatabaseManager {
        public function __construct(private $fake) {}
        public function driver(string $engine): DatabaseDriverInterface { return $this->fake; }
    });

    $payload = ['engine' => 'mysql', 'name' => 'app_db'];
    $this->post(route('sites.databases.store', $site), $payload)
        ->assertRedirect(route('sites.databases.index', $site));
    $this->assertDatabaseHas('databases', ['site_id' => $site->id, 'name' => 'app_db']);
    $site->refresh();
    expect($site->environment['db_database'])->toBe('app_db')
        ->and(file_get_contents(storage_path('sites/'.$site->slug.'/.env')))->toContain('DB_DATABASE=app_db');

    $db = $site->databases()->where('name','app_db')->first();
    $this->delete(route('sites.databases.destroy', [$site,$db]))
        ->assertRedirect(route('sites.databases.index', $site));
    $this->assertDatabaseMissing('databases', ['id' => $db->id]);
});
