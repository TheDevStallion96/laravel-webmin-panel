<?php

namespace App\Services\Database;

use App\Support\Shell;

class DatabaseManager
{
    public function __construct(private Shell $shell = new Shell()) {}

    public function driver(string $engine): DatabaseDriverInterface
    {
        return match ($engine) {
            'mysql' => new MySqlService($this->shell),
            'pgsql' => new PostgresService($this->shell),
            default => throw new \InvalidArgumentException('Unsupported engine: '.$engine),
        };
    }
}
