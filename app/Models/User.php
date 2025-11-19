<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Enums\Role;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => Role::class,
        ];
    }

    public function isAdmin(): bool
    {
        // Support legacy boolean column in addition to enum role
        return ($this->role === Role::Admin) || (bool) $this->getAttribute('is_admin');
    }

    public function isDeveloper(): bool
    {
        return $this->role === Role::Developer;
    }

    public function isViewer(): bool
    {
        return $this->role === Role::Viewer;
    }

    /**
     * Abilities a user is allowed to assign to their API tokens based on role.
     *
     * @return string[]
     */
    public function allowedTokenAbilities(): array
    {
        if ($this->isAdmin()) {
            return ['manage-server', 'manage-site', 'view-site'];
        }

        if ($this->isDeveloper()) {
            return ['manage-site', 'view-site'];
        }

        return ['view-site'];
    }
}
