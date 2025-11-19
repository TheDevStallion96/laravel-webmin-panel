<?php

namespace App\Services\WebServer;

use App\Models\Site;
use App\Services\TemplateRenderer;
use App\Support\Shell;
use Illuminate\Filesystem\Filesystem;

class NginxService
{
    public function __construct(
        private TemplateRenderer $renderer = new TemplateRenderer,
        private Shell $shell = new Shell,
        private Filesystem $fs = new Filesystem,
    ) {}

    public function writeSiteVhost(Site $site, $domains, bool $dryRun = false, bool $withDiff = false, $certificate = null, bool $httpsForced = false): string|array
    {
        $serverNames = collect($domains)->map(fn ($d) => $d->hostname ?? $d)->implode(' ');
        $stub = $this->stubPath('nginx/site.conf.stub');
        $sslListens = '';
        $sslBlock = '';
        $redirectBlock = '';
        if ($certificate) {
            // listen on 443 if we have a cert
            $sslListens = 'listen 443 ssl;';
            $sslBlock = 'ssl_certificate '.$certificate->path_cert.";\n    ssl_certificate_key ".$certificate->path_key.';';
        }
        if ($httpsForced && $certificate) {
            // simple scheme check redirect
            $redirectBlock = 'if ($scheme = http) { return 301 https://$host$request_uri; }';
        }
        $config = $this->renderer->render($stub, [
            'server_names' => $serverNames ?: $site->slug.'.local',
            'root_path' => $site->root_path,
            'public_dir' => $site->public_dir,
            'php_version' => $site->php_version,
            'site_slug' => $site->slug,
            'ssl_listens' => $sslListens,
            'ssl_block' => $sslBlock,
            'redirect_block' => $redirectBlock,
        ]);
        $dir = dirname(__DIR__, 3).'/storage/app/panel/configs/'.$site->slug;
        $path = $dir.'/nginx.conf';
        $existing = $this->fs->exists($path) ? $this->fs->get($path) : '';
        $changed = $existing !== $config;
        if ($dryRun) {
            $result = [
                'config' => $config,
                'changed' => $changed,
            ];
            if ($withDiff) {
                $result['diff'] = $this->diff($existing, $config);
                if (function_exists('activity')) {
                    activity()->onSite($site)->action('nginx.vhost.diff')->meta([
                        'changed' => $changed,
                    ])->log();
                }
            }

            return $result;
        }
        if (! $this->fs->isDirectory($dir)) {
            $this->fs->makeDirectory($dir, 0755, true);
        }
        if ($changed) {
            $this->fs->put($path, $config);
            if (function_exists('activity')) {
                activity()->onSite($site)->action('nginx.vhost.written')->meta([
                    'path' => $path,
                ])->log();
            }
        }

        return $config;
    }

    public function testConfig(): bool
    {
        $res = $this->shell->run('nginx', ['-t']);

        return $res['exit_code'] === 0;
    }

    public function reload(): bool
    {
        $res = $this->shell->run('systemctl', ['reload', 'nginx']);

        return $res['exit_code'] === 0;
    }

    private function stubPath(string $rel): string
    {
        $root = dirname(__DIR__, 3); // project root

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
                if ($n !== null) {
                    $out[] = ' '.$n;
                }
            } else {
                if ($o !== null) {
                    $out[] = '-'.$o;
                }
                if ($n !== null) {
                    $out[] = '+'.$n;
                }
            }
        }

        return implode("\n", $out);
    }
}
