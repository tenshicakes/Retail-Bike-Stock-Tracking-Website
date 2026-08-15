<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

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

    // 5. Tell Laravel's Auth system to use 'Password' instead of its default 'password' column
    public function getAuthPassword()
    {
        return $this->Password;
    }
}