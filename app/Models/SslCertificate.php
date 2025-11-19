<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $site_id
 * @property string $type
 * @property string $common_name
 * @property \Illuminate\Support\Carbon $expires_at
 * @property string $path_cert
 * @property string $path_key
 * @property \Illuminate\Support\Carbon|null $last_renewed_at
 * @property string $status
 */
class SslCertificate extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'site_id', 'type', 'common_name', 'expires_at', 'path_cert', 'path_key', 'last_renewed_at', 'status',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'last_renewed_at' => 'datetime',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
