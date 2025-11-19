<?php

use App\Services\Database\PostgresService;
use Tests\Fakes\FakeShell;

it('runs postgres lifecycle commands successfully', function () {
    $shell = new FakeShell();
    $driver = new PostgresService($shell);
    expect($driver->createDatabase('test_db'))->toBeTrue();
    expect($driver->createUser('user1','secret'))->toBeTrue();
    expect($driver->grant('user1','test_db'))->toBeTrue();
    expect($driver->revoke('user1','test_db'))->toBeTrue();
    expect($driver->dropDatabase('test_db'))->toBeTrue();
});
