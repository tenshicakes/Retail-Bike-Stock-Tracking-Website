<?php

namespace App\Http\Controllers;

use App\Models\AccessLog;
use App\Models\ErrorLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditLogsController extends Controller
{
    public function index(Request $request)
    {
        // 1. Access Logs Query
        $accessQuery = AccessLog::query()->latest('CreatedAt');

        if ($request->filled('access_search')) {
            $term = trim((string) $request->access_search);
            $accessQuery->where(function ($q) use ($term) {
                $q->where('Username', 'like', "%{$term}%")
                  ->orWhere('IpAddress', 'like', "%{$term}%")
                  ->orWhere('Details', 'like', "%{$term}%");
            });
        }

        if ($request->filled('access_status')) {
            $accessQuery->where('Status', $request->access_status);
        }

        if ($request->filled('access_event')) {
            $accessQuery->where('EventType', $request->access_event);
        }

        // 2. Error Logs Query
        $errorQuery = ErrorLog::query()->latest('CreatedAt');

        if ($request->filled('error_search')) {
            $term = trim((string) $request->error_search);
            $errorQuery->where(function ($q) use ($term) {
                $q->where('Message', 'like', "%{$term}%")
                  ->orWhere('ExceptionClass', 'like', "%{$term}%")
                  ->orWhere('File', 'like', "%{$term}%")
                  ->orWhere('Url', 'like', "%{$term}%")
                  ->orWhere('Username', 'like', "%{$term}%");
            });
        }

        if ($request->filled('error_level')) {
            $errorQuery->where('Level', $request->error_level);
        }

        // AJAX Tab-Specific Partial Responses
        if ($request->ajax()) {
            $tab = $request->input('tab', 'access');

            if ($tab === 'error') {
                $errorLogs = $errorQuery->paginate(8, ['*'], 'error_page')->withQueryString();
                return view('components.error-log-table', compact('errorLogs'))->render();
            }

            $accessLogs = $accessQuery->paginate(8, ['*'], 'access_page')->withQueryString();
            return view('components.access-log-table', compact('accessLogs'))->render();
        }

        $accessLogs = $accessQuery->paginate(8, ['*'], 'access_page')->withQueryString();
        $errorLogs = $errorQuery->paginate(8, ['*'], 'error_page')->withQueryString();

        $totalAccessCount = AccessLog::count();
        $blockedOrFailedCount = AccessLog::whereIn('Status', ['Failed', 'Blocked'])->count();
        $totalErrorCount = ErrorLog::count();

        $eventTypes = AccessLog::select('EventType')
            ->distinct()
            ->whereNotNull('EventType')
            ->orderBy('EventType')
            ->pluck('EventType');

        return view('dashboard.audit-logs', compact(
            'accessLogs',
            'errorLogs',
            'totalAccessCount',
            'blockedOrFailedCount',
            'totalErrorCount',
            'eventTypes'
        ));
    }

    public function clearAccessLogs(Request $request)
    {
        AccessLog::truncate();

        $user = Auth::user();
        AccessLog::record(
            'Audit Cleared',
            'Success',
            $user?->Username,
            $user?->UserID,
            'Administrator cleared all Access Logs.',
            $request
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Access logs cleared successfully.',
        ]);
    }

    public function clearErrorLogs(Request $request)
    {
        ErrorLog::truncate();

        $user = Auth::user();
        AccessLog::record(
            'Audit Cleared',
            'Success',
            $user?->Username,
            $user?->UserID,
            'Administrator cleared all System Error Logs.',
            $request
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Error logs cleared successfully.',
        ]);
    }
}