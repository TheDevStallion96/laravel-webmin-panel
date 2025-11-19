<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

/**
 * @property int $id
 * @property int $site_id
 * @property string $engine
 * @property string $name
 * @property string $username
 * @property string $host
 * @property int $port
 * @property string $password_encrypted
 */
class Database extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'site_id', 'engine', 'name', 'username', 'host', 'port', 'password_encrypted',
    ];

    protected $casts = [
        'port' => 'integer',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Decrypt the stored password.
     */
    public function getPasswordAttribute(): ?string
    {
        try {
            return Crypt::decryptString($this->password_encrypted);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
