<?php

namespace App\Services\PHP;

use App\Models\Site;
use App\Services\TemplateRenderer;
use App\Support\Shell;
use Illuminate\Filesystem\Filesystem;

class FpmService
{
    public function __construct(
        private TemplateRenderer $renderer = new TemplateRenderer(),
        private Shell $shell = new Shell(),
        private Filesystem $fs = new Filesystem(),
    ) {}

    public function ensureVersionInstalled(string $version): bool
    {
        // Basic heuristic: run `php -v` and check output contains version prefix
        $res = $this->shell->run('php', ['-v']);
        return str_contains($res['output'].$res['error'], $version);
    }

    public function writePool(Site $site, bool $dryRun = false, bool $withDiff = false): string|array
    {
        $stub = $this->stubPath('supervisor/php-fpm-pool.conf.stub');
        $pool = $this->renderer->render($stub, [
            'php_version' => $site->php_version,
            'site_slug' => $site->slug,
            'root_path' => $site->root_path,
        ]);
        $dir = dirname(__DIR__, 3).'/storage/app/panel/configs/'.$site->slug;
        $path = $dir.'/php-fpm-pool.conf';
        $existing = $this->fs->exists($path) ? $this->fs->get($path) : '';
        $changed = $existing !== $pool;
        if ($dryRun) {
            $result = [
                'config' => $pool,
                'changed' => $changed,
            ];
            if ($withDiff) {
                $result['diff'] = $this->diff($existing, $pool);
                if (function_exists('activity')) {
                    activity()->onSite($site)->action('phpfpm.pool.diff')->meta([
                        'changed' => $changed,
                    ])->log();
                }
            }
            return $result;
        }
        if (!$this->fs->isDirectory($dir)) {
            $this->fs->makeDirectory($dir, 0755, true);
        }
        if ($changed) {
            $this->fs->put($path, $pool);
            if (function_exists('activity')) {
                activity()->onSite($site)->action('phpfpm.pool.written')->meta([
                    'path' => $path,
                ])->log();
            }
        }
        return $pool;
    }

    public function reload(string $version): bool
    {
        // Attempt reload via systemctl (phpX.Y-fpm service naming may differ; use generic reload nginx if missing)
        $service = 'php'.$version.'-fpm';
        $res = $this->shell->run('systemctl', ['reload', $service]);
        return $res['exit_code'] === 0;
    }

    private function stubPath(string $rel): string
    {
        $root = dirname(__DIR__, 3);
        return $root.'/resources/stubs/'.$rel;
    }

    private function diff(string $old, string $new): string
    {
        $oldLines = $old === '' ? [] : explode("\n", $old);
        $newLines = explode("\n", $new);
        $out = [];
        $length = max(count($oldLines), count($newLines));
        for ($i = 0; $i < $length; $i++) {
            $o = $oldLines[$i] ?? null;
            $n = $newLines[$i] ?? null;
            if ($o === $n) {
                if ($n !== null) $out[] = ' '.$n;
            } else {
                if ($o !== null) $out[] = '-'.$o;
                if ($n !== null) $out[] = '+'.$n;
            }
        }
        return implode("\n", $out);
    }
}
