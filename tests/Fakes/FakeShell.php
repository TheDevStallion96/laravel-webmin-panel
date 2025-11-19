<?php

namespace Tests\Fakes;

use App\Support\Shell;

class FakeShell extends Shell
{
    public array $calls = [];

    public function __construct()
    {
        parent::__construct(['echo']);
    }

    public function run(string $command, array $args = []): array
    {
        $this->calls[] = [$command, $args];
        return [
            'command' => $command.' '.implode(' ', $args),
            'exit_code' => 0,
            'output' => 'ok',
            'error' => '',
        ];
    }
}
