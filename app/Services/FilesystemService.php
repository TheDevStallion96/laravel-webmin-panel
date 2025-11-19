<?php

namespace App\Services;

use Illuminate\Filesystem\Filesystem;

class FilesystemService
{
    public function __construct(
        protected Filesystem $fs = new Filesystem,
        protected bool $dryRun = false,
    ) {}

    public function setDryRun(bool $dry): void
    {
        $this->dryRun = $dry;
    }

    public function ensureDirectory(string $path): void
    {
        if ($this->dryRun) return;
        if (!$this->fs->isDirectory($path)) {
            $this->fs->makeDirectory($path, 0755, true);
        }
    }

    public function writeFile(string $path, string $contents): void
    {
        if ($this->dryRun) return;
        $dir = dirname($path);
        $this->ensureDirectory($dir);
        $this->fs->put($path, $contents);
    }
}
