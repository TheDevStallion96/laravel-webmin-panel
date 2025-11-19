<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Site;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use InvalidArgumentException;

/**
 * Service for recording activity logs with a fluent API.
 */
class ActivityLogService
{
    protected ?int $siteId = null;
    protected ?int $userId = null;
    protected ?string $action = null;
    protected array $meta = [];
    protected ?string $ip = null;

    /**
     * Bind the log to a site context.
     *
     * @param  int|Site|null  $site
     */
    public function onSite($site): self
    {
        if ($site instanceof Site) {
            $this->siteId = $site->getKey();
        } elseif (is_numeric($site)) {
            $this->siteId = (int) $site;
        } elseif ($site === null) {
            $this->siteId = null;
        } else {
            throw new InvalidArgumentException('Invalid site value');
        }

        return $this;
    }

    /**
     * Set the user performing the action.
     *
     * @param  int|User|Authenticatable|null  $user
     */
    public function byUser($user): self
    {
        if ($user instanceof User || $user instanceof Authenticatable) {
            $this->userId = $user->getAuthIdentifier();
        } elseif (is_numeric($user)) {
            $this->userId = (int) $user;
        } elseif ($user === null) {
            $this->userId = null;
        } else {
            throw new InvalidArgumentException('Invalid user value');
        }

        return $this;
    }

    /**
     * Set the action name (required).
     */
    public function action(string $action): self
    {
        $this->action = $action;
        return $this;
    }

    /**
     * Set metadata payload.
     */
    public function meta(array $meta): self
    {
        $this->meta = $meta;
        return $this;
    }

    /**
     * Set IP address.
     */
    public function withIp(?string $ip): self
    {
        $this->ip = $ip;
        return $this;
    }

    /**
     * Persist the activity log.
     */
    public function log(): ActivityLog
    {
        if (! $this->action) {
            throw new InvalidArgumentException('Activity action is required.');
        }

        $userId = $this->userId ?? optional(Auth::user())->getAuthIdentifier();
        $ip = $this->ip ?? (request()->server('REMOTE_ADDR') ?? null);

        return ActivityLog::create([
            'user_id' => $userId,
            'site_id' => $this->siteId,
            'action' => $this->action,
            'meta' => $this->meta,
            'ip' => $ip,
        ]);
    }
}
