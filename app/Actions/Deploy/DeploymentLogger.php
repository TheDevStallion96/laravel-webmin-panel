<?php

namespace App\Actions\Deploy;

use Illuminate\Support\Carbon;

class DeploymentLogger
{
    public function __construct(public string $logPath)
    {
        $dir = dirname($this->logPath);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
    }

    public function line(string $message): void
    {
        $ts = Carbon::now()->toDateTimeString();
        @file_put_contents($this->logPath, "[$ts] $message\n", FILE_APPEND);
    }
}
