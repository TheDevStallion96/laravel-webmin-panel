<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $site_id
 * @property string $hostname
 * @property bool $is_primary
 * @property bool $https_forced
 */
class Domain extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'site_id', 'hostname', 'is_primary', 'https_forced',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'https_forced' => 'boolean',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
