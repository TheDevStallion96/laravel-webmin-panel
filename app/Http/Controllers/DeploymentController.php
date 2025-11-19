<?php

namespace App\Http\Controllers;

use App\Jobs\RunDeployment;
use App\Models\Deployment;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class DeploymentController extends Controller
{
    public function settings(Site $site): View
    {
        $deployments = $site->deployments()->latest('id')->limit(5)->get();
        return view('deploy.settings', compact('site','deployments'));
    }

    public function run(Request $request, Site $site)
    {
        $this->authorize('update', $site); // manage-site gate in routes
        // Create deployment record
        $deployment = Deployment::create([
            'site_id' => $site->id,
            'commit_hash' => 'pending',
            'branch' => $site->default_branch,
            'status' => 'pending',
            'log_path' => '',
            'user_id' => $request->user()->id ?? null,
        ]);
        RunDeployment::dispatch($deployment->id);
        return Redirect::route('sites.deploy.history', $site)->with('status', 'deployment-started');
    }

    public function history(Site $site): View
    {
        $deployments = $site->deployments()->latest('id')->paginate(20);
        return view('deploy.history', compact('site','deployments'));
    }

    public function logs(Site $site, Deployment $deployment): View
    {
        if ($deployment->site_id !== $site->id) abort(404);
        $tail = (int) request()->query('tail', 0);
        $ansi = (bool) request()->query('ansi', false);
        $contents = '';
        if (is_file($deployment->log_path)) {
            if ($tail > 0) {
                $contents = $this->tailFile($deployment->log_path, $tail);
            } else {
                $contents = file_get_contents($deployment->log_path);
            }
        }
        $contents_html = $ansi ? ansi_to_html($contents) : e($contents);
        return view('deploy.logs', [
            'site' => $site,
            'deployment' => $deployment,
            'contents' => $contents,
            'contents_html' => $contents_html,
            'tail' => $tail,
            'ansi' => $ansi,
        ]);
    }

    public function rollback(Request $request, Site $site, Deployment $deployment)
    {
        $this->authorize('update', $site);
        if ($deployment->site_id !== $site->id) abort(404);
        $statusValue = $deployment->status instanceof \App\Enums\DeploymentStatus ? $deployment->status->value : (string) $deployment->status;
        if ($statusValue !== 'completed') {
            return redirect()->route('sites.deploy.history', $site)->with('status', 'rollback-not-completed');
        }
        $base = storage_path('app/panel/releases/'.$site->slug);
        $targetRelease = $this->inferReleaseFromLog($deployment->log_path);
        $releasePath = $base.'/'.$targetRelease;
        $current = $base.'/current';
        if (is_dir($releasePath)) {
            if (is_link($current) || file_exists($current)) @unlink($current);
            @symlink($releasePath, $current);
            if (function_exists('activity')) {
                activity()->onSite($site)->action('deployment.rollback')->meta([
                    'to_deployment_id' => $deployment->id,
                    'release' => $targetRelease,
                ])->log();
            }
            return redirect()->route('sites.deploy.history', $site)->with('status', 'rollback-success');
        }
        return redirect()->route('sites.deploy.history', $site)->with('status', 'rollback-missing-release');
    }

    private function inferReleaseFromLog(string $logPath): string
    {
        $name = basename($logPath, '.log');
        return $name; // timestamp used as release name
    }

    private function tailFile(string $path, int $lines): string
    {
        // Efficiently read last N lines
        $f = fopen($path, 'r');
        if (!$f) return '';
        $buffer = '';
        $pos = -1;
        $lineCount = 0;
        $stat = fstat($f);
        $size = $stat['size'] ?? 0;
        if ($size === 0) { fclose($f); return ''; }
        while ($lineCount < $lines && -$pos < $size) {
            fseek($f, $pos, SEEK_END);
            $char = fgetc($f);
            $buffer = $char.$buffer;
            if ($char === "\n") $lineCount++;
            $pos--;
        }
        fclose($f);
        return $buffer;
    }
}
