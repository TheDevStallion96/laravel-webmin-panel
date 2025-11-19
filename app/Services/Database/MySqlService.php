<?php

namespace App\Services\Database;

use App\Support\Shell;

class MySqlService implements DatabaseDriverInterface
{
    public function __construct(private Shell $shell = new Shell()) {}

    private function runSql(string $sql): bool
    {
        $res = $this->shell->run('mysql', ['-e', $sql]);
        return $res['exit_code'] === 0;
    }

    public function createDatabase(string $name): bool
    {
        return $this->runSql("CREATE DATABASE IF NOT EXISTS `{$name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    }
    public function dropDatabase(string $name): bool
    {
        return $this->runSql("DROP DATABASE IF EXISTS `{$name}`");
    }
    public function createUser(string $username, string $password): bool
    {
        return $this->runSql("CREATE USER IF NOT EXISTS '{$username}'@'%' IDENTIFIED BY '{$password}'");
    }
    public function grant(string $username, string $database): bool
    {
        return $this->runSql("GRANT ALL PRIVILEGES ON `{$database}`.* TO '{$username}'@'%'".
            "; FLUSH PRIVILEGES");
    }
    public function revoke(string $username, string $database): bool
    {
        return $this->runSql("REVOKE ALL PRIVILEGES ON `{$database}`.* FROM '{$username}'@'%'; FLUSH PRIVILEGES");
    }
    public function dump(string $database, string $path): bool
    {
        $res = $this->shell->run('mysqldump', [$database, '>', $path]); // simplified
        return $res['exit_code'] === 0;
    }
    public function restore(string $database, string $path): bool
    {
        $res = $this->shell->run('mysql', [$database, '<', $path]);
        return $res['exit_code'] === 0;
    }
}
