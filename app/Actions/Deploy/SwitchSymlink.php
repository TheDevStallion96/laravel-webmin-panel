<?php

namespace App\Actions\Deploy;

use App\Models\Site;

class SwitchSymlink implements DeployActionInterface
{
    public function name(): string { return 'Switch Symlink'; }

    public function run(Site $site, string $releasePath, DeploymentLogger $logger): bool
    {
        $base = storage_path('app/panel/releases/'.$site->slug);
        $current = $base.'/current';
        // Remove existing symlink if present
        if (is_link($current) || file_exists($current)) {
            @unlink($current);
        }
        @symlink($releasePath, $current);
        $logger->line('Switched current symlink to '.basename($releasePath));
        return true;
    }
}
