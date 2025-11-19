<?php

use App\Services\Database\DatabaseManager;
use App\Services\Database\MySqlService;
use App\Services\Database\PostgresService;
use App\Support\Shell;

it('resolves mysql driver', function () {
    $manager = new DatabaseManager(new Shell(['mysql']));
    expect($manager->driver('mysql'))->toBeInstanceOf(MySqlService::class);
});

it('resolves pgsql driver', function () {
    $manager = new DatabaseManager(new Shell(['psql']));
    expect($manager->driver('pgsql'))->toBeInstanceOf(PostgresService::class);
});

it('throws for unsupported engine', function () {
    $manager = new DatabaseManager(new Shell(['echo']));
    $manager->driver('sqlite');
})->throws(InvalidArgumentException::class);
