<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int|null $user_id
 * @property int|null $site_id
 * @property string $action
 * @property array $meta
 * @property string|null $ip
 */
class ActivityLog extends Model
{
    use HasFactory;

    public $timestamps = false; // created_at only

    protected $fillable = [
        'user_id', 'site_id', 'action', 'meta', 'ip', 'created_at',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
