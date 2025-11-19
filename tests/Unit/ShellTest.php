<?php

use App\Support\Shell;

it('rejects disallowed command', function () {
    $shell = new Shell(['echo']);
    $shell->run('echo', ['hello']);
    expect(fn() => $shell->run('nginx', ['-t']))->toThrow(RuntimeException::class);
});
