<?php

namespace App\Actions\Sites;

use App\Models\Site;
use App\Services\FilesystemService;

class WriteEnv
{
    public function __construct(private FilesystemService $fs = new FilesystemService()) {}

    public function render(array $environment): string
    {
        $lines = [];
        foreach ($environment as $key => $value) {
            if (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            } elseif (is_array($value)) {
                $value = json_encode($value);
            }
            $lines[] = sprintf('%s=%s', strtoupper($key), $value);
        }
        return implode("\n", $lines) . "\n";
    }

    public function write(Site $site, string $basePath): void
    {
        $env = $this->render($site->environment ?? []);
        $this->fs->writeFile(rtrim($basePath, '/'). '/.env', $env);
    }
}
