<?php

namespace Tests\Fakes;

use App\Services\Database\DatabaseManager;
use App\Services\Database\DatabaseDriverInterface;
use App\Support\Shell;

class FakeDatabaseManager extends DatabaseManager
{
    public FakeDatabaseDriver $driver;

    public function __construct()
    {
        $this->driver = new FakeDatabaseDriver();
        parent::__construct(new Shell(['echo'])); // minimal shell
    }

    public function driver(string $engine): DatabaseDriverInterface
    {
        // Ignore engine, always return fake driver to avoid executing real commands
        return $this->driver;
    }
}
