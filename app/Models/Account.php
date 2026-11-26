<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\File;

/**
 * @property int $UserID
 * @property string $Username
 * @property string $Password
 * @property string $Role
 * @property string|null $profile_picture
 * @property string|null $two_factor_secret
 * @property \Illuminate\Support\Carbon|null $two_factor_confirmed_at
 */
class Account extends Authenticatable
{
    use Notifiable;

    protected $table = 'accounts';
    protected $primaryKey = 'UserID';

    protected $fillable = [
        'Username',
        'Password',
        'Role',
        'profile_picture',
        'two_factor_secret',
        'two_factor_confirmed_at',
    ];

    protected $hidden = [
        'Password',
        'two_factor_secret',
    ];

    protected function casts(): array
    {
        return [
            'two_factor_secret' => 'encrypted',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function hasProfilePicture(): bool
    {
        return ! empty($this->profile_picture) && File::exists(public_path($this->profile_picture));
    }

    public function profilePictureUrl(): ?string
    {
        if ($this->hasProfilePicture()) {
            return asset($this->profile_picture);
        }

        return null;
    }

    public function initial(): string
    {
        return strtoupper(substr((string) $this->Username, 0, 1));
    }

    public function hasTwoFactorEnabled(): bool
    {
        return ! empty($this->two_factor_secret) && ! is_null($this->two_factor_confirmed_at);
    }

    public function hasRole(array|string $roles): bool
    {
        $roles = (array) $roles;

        return in_array(
            strtoupper(trim((string) $this->Role)),
            array_map(fn ($role) => strtoupper(trim((string) $role)), $roles),
            true
        );
    }

    public function canAccessPage(string $page): bool
    {
        $role = strtoupper(trim((string) $this->Role));

        return match ($page) {
            'home', 'products' => true,
            'accounts', 'backup', 'audit-logs' => $role === 'ADMINISTRATOR',
            'lowstock', 'nostock', 'system-settings' => in_array($role, ['ADMINISTRATOR', 'OWNER'], true),
            'logs' => in_array($role, ['ADMINISTRATOR', 'OWNER', 'STAFF'], true),
            default => true,
        };
    }

    public function canEditProducts(): bool
    {
        return $this->hasRole(['Administrator', 'Owner']);
    }

    public function getAuthPassword()
    {
        return $this->Password;
    }
}