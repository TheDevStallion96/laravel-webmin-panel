<?php

use App\Actions\Sites\WriteEnv;

test('write env renders key value lines', function () {
    $writer = new WriteEnv();
    $env = $writer->render([
        'app_env' => 'production',
        'app_debug' => false,
        'json' => ['a' => 1],
    ]);

    expect($env)->toContain("APP_ENV=production")
        ->and($env)->toContain("APP_DEBUG=false")
        ->and($env)->toContain('JSON={"a":1}');
});
