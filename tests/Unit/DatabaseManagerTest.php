<?php

use App\Services\Database\DatabaseManager;
use App\Services\Database\MySqlService;
use App\Services\Database\PostgresService;

it('returns correct driver for engine', function () {
    $mgr = new DatabaseManager();
    expect($mgr->driver('mysql'))->toBeInstanceOf(MySqlService::class);
    expect($mgr->driver('pgsql'))->toBeInstanceOf(PostgresService::class);
    expect(fn()=> $mgr->driver('sqlite'))
        ->toThrow(InvalidArgumentException::class);
});
