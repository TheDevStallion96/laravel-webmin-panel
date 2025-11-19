<?php

namespace App\Actions\Sites;

use App\Models\Site;
use App\Services\FilesystemService;

class ProvisionSite
{
    public function __construct(
        private FilesystemService $fs = new FilesystemService(),
        private WriteEnv $writeEnv = new WriteEnv(),
    ) {}

    /**
        * Provision a local directory structure for the site and write .env
        */
    public function handle(Site $site): void
    {
        // For MVP we provision under storage/sites/{slug}
        $base = storage_path('sites/'. $site->slug);
        $this->fs->ensureDirectory($base);
        $this->fs->ensureDirectory($base . '/' . $site->public_dir);
        $this->writeEnv->write($site, $base);
    }
}
