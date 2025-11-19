<?php

namespace App\Support;

use Symfony\Component\Process\Process;
use Illuminate\Support\Str;

interface ShellInterface
{
    public function run(string $command, array $args = []): array;
    public function executed(): array;
}

class Shell implements ShellInterface
{
    protected array $allowList;
    protected int $timeout;
    protected ?string $runAs;
    protected array $executed = [];

    public function __construct(?array $allowList = null, int $timeout = 120, ?string $runAs = null)
    {
        if ($allowList === null) {
            // Fallback if config() helper not available or throws
            if (function_exists('config')) {
                try {
                    $allowList = config('shell.allow', []);
                } catch (\Throwable $e) {
                    $allowList = [];
                }
            } else {
                $allowList = [];
            }
        }
        $this->allowList = $allowList;
        $this->timeout = $timeout;
        $this->runAs = $runAs;
    }

    public function run(string $command, array $args = []): array
    {
        $base = Str::before($command.' ', ' ');
        if (!in_array($base, $this->allowList, true)) {
            throw new \RuntimeException("Command '{$base}' not allowed");
        }

        $full = $command;
        foreach ($args as $arg) {
            $full .= ' '.escapeshellarg($arg);
        }
        if ($this->runAs) {
            $full = 'sudo -u '.escapeshellarg($this->runAs).' '.$full;
        }

        $process = Process::fromShellCommandline($full);
        $process->setTimeout($this->timeout);
        $process->run();

        $result = [
            'command' => $full,
            'exit_code' => $process->getExitCode(),
            'output' => $process->getOutput(),
            'error' => $process->getErrorOutput(),
        ];
        $this->executed[] = $result;
        $this->log($result);
        return $result;
    }

    public function executed(): array
    {
        return $this->executed;
    }

    protected function log(array $result): void
    {
        $dir = storage_path('logs/shell');
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $line = date('c')." | {$result['exit_code']} | {$result['command']}\n";
        @file_put_contents($dir.'/'.date('Y-m-d').'.log', $line, FILE_APPEND);
    }
}
