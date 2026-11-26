<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ErrorLog extends Model
{
    protected $table = 'error_logs';
    protected $primaryKey = 'ErrorLogID';
    public $timestamps = false;

    protected static bool $isRecording = false;

    protected $fillable = [
        'UserID',
        'Username',
        'Level',
        'ExceptionClass',
        'Message',
        'File',
        'Line',
        'Url',
        'Method',
        'IpAddress',
        'StackTrace',
        'CreatedAt',
    ];

    protected function casts(): array
    {
        return [
            'CreatedAt' => 'datetime',
        ];
    }

    public static function recordException(\Throwable $e): void
    {
        if (self::$isRecording) {
            return;
        }

        self::$isRecording = true;

        try {
            $req = app()->runningInConsole() ? null : request();
            $user = Auth::check() ? Auth::user() : null;
            $level = $e instanceof HttpExceptionInterface ? 'HTTP Error' : 'Error';

            self::create([
                'UserID' => $user?->UserID,
                'Username' => $user?->Username,
                'Level' => $level,
                'ExceptionClass' => get_class($e),
                'Message' => $e->getMessage() ?: class_basename($e),
                'File' => str_replace(base_path() . DIRECTORY_SEPARATOR, '', $e->getFile()),
                'Line' => $e->getLine(),
                'Url' => $req?->fullUrl(),
                'Method' => $req?->method(),
                'IpAddress' => $req?->ip(),
                'StackTrace' => substr($e->getTraceAsString(), 0, 15000),
                'CreatedAt' => now(),
            ]);
        } catch (\Throwable) {
            // Prevent infinite recursion if database is unavailable
        } finally {
            self::$isRecording = false;
        }
    }
}