<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AccessLog extends Model
{
    protected $table = 'access_logs';
    protected $primaryKey = 'AccessLogID';
    public $timestamps = false;

    protected $fillable = [
        'UserID',
        'Username',
        'EventType',
        'Status',
        'IpAddress',
        'UserAgent',
        'Details',
        'CreatedAt',
    ];

    protected function casts(): array
    {
        return [
            'CreatedAt' => 'datetime',
        ];
    }

    public static function record(
        string $eventType,
        string $status,
        ?string $username = null,
        ?int $userId = null,
        ?string $details = null,
        ?Request $request = null
    ): void {
        try {
            $req = $request ?? request();

            self::create([
                'UserID' => $userId,
                'Username' => $username,
                'EventType' => $eventType,
                'Status' => $status,
                'IpAddress' => $req?->ip(),
                'UserAgent' => substr((string) ($req?->userAgent() ?? ''), 0, 500),
                'Details' => $details,
                'CreatedAt' => now(),
            ]);
        } catch (\Throwable) {
            // Silently ignore logging failures so auth flows are never disrupted
        }
    }
}