<?php

namespace App\Services\Database;

use App\Support\Shell;

class PostgresService implements DatabaseDriverInterface
{
    public function __construct(private Shell $shell = new Shell()) {}

    private function runSql(string $sql): bool
    {
        $res = $this->shell->run('psql', ['-c', $sql]);
        return $res['exit_code'] === 0;
    }

    public function createDatabase(string $name): bool
    {
        return $this->runSql("CREATE DATABASE \"{$name}\" WITH ENCODING 'UTF8'");
    }
    public function dropDatabase(string $name): bool
    {
        return $this->runSql("DROP DATABASE IF EXISTS \"{$name}\"");
    }
    public function createUser(string $username, string $password): bool
    {
        return $this->runSql("CREATE ROLE \"{$username}\" LOGIN PASSWORD '{$password}'");
    }
    public function grant(string $username, string $database): bool
    {
        return $this->runSql("GRANT ALL PRIVILEGES ON DATABASE \"{$database}\" TO \"{$username}\"");
    }
    public function revoke(string $username, string $database): bool
    {
        return $this->runSql("REVOKE ALL PRIVILEGES ON DATABASE \"{$database}\" FROM \"{$username}\"");
    }
    public function dump(string $database, string $path): bool
    {
        $res = $this->shell->run('pg_dump', [$database, '>', $path]);
        return $res['exit_code'] === 0;
    }
    public function restore(string $database, string $path): bool
    {
        $res = $this->shell->run('psql', [$database, '<', $path]);
        return $res['exit_code'] === 0;
    }
}
