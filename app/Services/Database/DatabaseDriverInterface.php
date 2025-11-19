<?php

namespace App\Services\Database;

interface DatabaseDriverInterface
{
    public function createDatabase(string $name): bool;
    public function dropDatabase(string $name): bool;
    public function createUser(string $username, string $password): bool;
    public function grant(string $username, string $database): bool;
    public function revoke(string $username, string $database): bool;
    public function dump(string $database, string $path): bool;
    public function restore(string $database, string $path): bool;
}
