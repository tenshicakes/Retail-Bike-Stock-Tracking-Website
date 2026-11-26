<?php

namespace App\Http\Controllers;

use App\Models\AccessLog;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class SystemSettingsController extends Controller
{
    public function index()
    {
        $settings = SystemSetting::current();

        return view('dashboard.system-settings', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'system_name' => ['required', 'string', 'max:120'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
        ], [
            'system_name.required' => 'System name is required.',
            'system_name.max' => 'System name cannot exceed 120 characters.',
            'logo.image' => 'The uploaded file must be a valid image.',
            'logo.mimes' => 'Logo must be a PNG, JPG, JPEG, or WEBP file.',
            'logo.max' => 'Logo file size must not exceed 2 MB.',
        ]);

        $settings = SystemSetting::current();
        $oldName = $settings->system_name;
        $settings->system_name = trim($validated['system_name']);

        if ($request->hasFile('logo')) {
            $brandingDir = public_path('images/branding');
            File::ensureDirectoryExists($brandingDir);

            // Delete previous custom logo if one exists (never delete default JFM.png)
            if (! empty($settings->logo_path) && str_starts_with($settings->logo_path, 'images/branding/')) {
                $oldFullPath = public_path($settings->logo_path);
                if (File::exists($oldFullPath)) {
                    File::delete($oldFullPath);
                }
            }

            $file = $request->file('logo');
            $filename = 'logo_' . now()->format('YmdHis') . '_' . bin2hex(random_bytes(3)) . '.' . $file->getClientOriginalExtension();
            $file->move($brandingDir, $filename);

            $settings->logo_path = 'images/branding/' . $filename;
        }

        $settings->save();
        SystemSetting::clearCache();

        $user = Auth::user();
        AccessLog::record(
            'System Settings Updated',
            'Success',
            $user?->Username,
            $user?->UserID,
            "Updated system branding (Name: '{$oldName}' -> '{$settings->system_name}'" . ($request->hasFile('logo') ? ', new logo uploaded' : '') . ').',
            $request
        );

        return response()->json([
            'status' => 'success',
            'message' => 'System settings saved and synced across the application!',
            'system_name' => $settings->system_name,
            'logo_url' => $settings->logoUrl(),
            'is_custom_logo' => ! empty($settings->logo_path),
        ]);
    }

    public function resetLogo(Request $request)
    {
        $settings = SystemSetting::current();

        if (! empty($settings->logo_path) && str_starts_with($settings->logo_path, 'images/branding/')) {
            $oldFullPath = public_path($settings->logo_path);
            if (File::exists($oldFullPath)) {
                File::delete($oldFullPath);
            }
        }

        $settings->logo_path = null;
        $settings->save();
        SystemSetting::clearCache();

        $user = Auth::user();
        AccessLog::record(
            'System Settings Updated',
            'Success',
            $user?->Username,
            $user?->UserID,
            'Reset system logo to default (JFM.png).',
            $request
        );

        return response()->json([
            'status' => 'success',
            'message' => 'System logo reset to default.',
            'logo_url' => $settings->logoUrl(),
        ]);
    }
}