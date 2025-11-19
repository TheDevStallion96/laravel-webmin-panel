<?php

namespace Tests\Fakes;

use App\Services\Database\DatabaseDriverInterface;

class FakeDatabaseDriver implements DatabaseDriverInterface
{
    public array $calls = [];

    protected function record(string $method, array $args = []): bool
    {
        $this->calls[] = [$method, $args];
        return true; // always succeed
    }

    public function createDatabase(string $name): bool { return $this->record(__FUNCTION__, [$name]); }
    public function dropDatabase(string $name): bool { return $this->record(__FUNCTION__, [$name]); }
    public function createUser(string $username, string $password): bool { return $this->record(__FUNCTION__, [$username,$password]); }
    public function grant(string $username, string $database): bool { return $this->record(__FUNCTION__, [$username,$database]); }
    public function revoke(string $username, string $database): bool { return $this->record(__FUNCTION__, [$username,$database]); }
    public function dump(string $database, string $path): bool { return $this->record(__FUNCTION__, [$database,$path]); }
    public function restore(string $database, string $path): bool { return $this->record(__FUNCTION__, [$database,$path]); }
}
