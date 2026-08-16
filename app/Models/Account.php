<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property int $UserID
 * @property string $Username
 * @property string $Password
 * @property string $Role
 */
class Account extends Authenticatable
{
    use Notifiable;

    // 1. Point to your custom table
    protected $table = 'accounts';

    // 2. Point to your custom Primary Key
    protected $primaryKey = 'UserID';

    // 3. Allow mass assignment for these columns
    protected $fillable = [
        'Username',
        'Password',
        'Role',
    ];

    // 4. Hide the password from arrays/JSON for security
    protected $hidden = [
        'Password',
    ];

    public function hasRole(array|string $roles): bool
    {
        $roles = (array) $roles;

        return in_array(strtoupper(trim((string) $this->Role)), array_map(fn ($role) => strtoupper(trim((string) $role)), $roles), true);
    }

    public function canAccessPage(string $page): bool
    {
        $role = strtoupper(trim((string) $this->Role));

        return match ($page) {
            'home', 'products' => true,
            'accounts' => $role === 'ADMINISTRATOR',
            'lowstock', 'nostock' => in_array($role, ['ADMINISTRATOR', 'OWNER'], true),
            'logs' => in_array($role, ['ADMINISTRATOR', 'OWNER', 'STAFF'], true),
            default => true,
        };
    }

    public function canEditProducts(): bool
    {
        return $this->hasRole(['Administrator', 'Owner']);
    }

    // 5. Tell Laravel's Auth system to use 'Password' instead of its default 'password' column
    public function getAuthPassword()
    {
        return $this->Password;
    }
}