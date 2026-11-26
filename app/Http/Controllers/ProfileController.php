<?php

namespace App\Http\Controllers;

use App\Models\AccessLog;
use App\Models\Log;
use App\Models\SystemSetting;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Google2FA;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $totalTransactions = Log::where('UserID', $user->UserID)->count();
        $totalStockIns = Log::where('UserID', $user->UserID)
            ->where('ActionType', 'Stock-In')
            ->sum('Quantity');
        $totalStockOuts = Log::where('UserID', $user->UserID)
            ->where('ActionType', 'Stock-Out')
            ->sum('Quantity');

        return view('dashboard.profile', compact(
            'user',
            'totalTransactions',
            'totalStockIns',
            'totalStockOuts'
        ));
    }

    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
        ], [
            'avatar.required' => 'Please choose an image file to upload.',
            'avatar.image' => 'The selected file must be a valid image.',
            'avatar.mimes' => 'Profile picture must be a PNG, JPG, JPEG, or WEBP file.',
            'avatar.max' => 'Profile picture must not exceed 2 MB.',
        ]);

        $user = Auth::user();
        $avatarsDir = public_path('images/avatars');
        File::ensureDirectoryExists($avatarsDir);

        // Delete previous custom avatar if present
        if (! empty($user->profile_picture) && str_starts_with($user->profile_picture, 'images/avatars/')) {
            $oldFullPath = public_path($user->profile_picture);
            if (File::exists($oldFullPath)) {
                File::delete($oldFullPath);
            }
        }

        $file = $request->file('avatar');
        $filename = 'avatar_' . $user->UserID . '_' . now()->format('YmdHis') . '_' . bin2hex(random_bytes(3)) . '.' . $file->getClientOriginalExtension();
        $file->move($avatarsDir, $filename);

        $user->profile_picture = 'images/avatars/' . $filename;
        $user->save();

        AccessLog::record(
            'Profile Picture Updated',
            'Success',
            $user->Username,
            $user->UserID,
            'Uploaded a new profile picture.',
            $request
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Profile picture updated successfully!',
            'avatar_url' => $user->profilePictureUrl(),
            'initial' => $user->initial(),
        ]);
    }

    public function removeAvatar(Request $request)
    {
        $user = Auth::user();

        if (! empty($user->profile_picture) && str_starts_with($user->profile_picture, 'images/avatars/')) {
            $oldFullPath = public_path($user->profile_picture);
            if (File::exists($oldFullPath)) {
                File::delete($oldFullPath);
            }
        }

        $user->profile_picture = null;
        $user->save();

        AccessLog::record(
            'Profile Picture Removed',
            'Success',
            $user->Username,
            $user->UserID,
            'Removed profile picture and reverted to default initial badge.',
            $request
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Profile picture removed.',
            'initial' => $user->initial(),
        ]);
    }

    public function updateInfo(Request $request)
    {
        $user = Auth::user();

        $request->merge([
            'Username' => trim((string) $request->Username),
        ]);

        $validated = $request->validate([
            'Username' => [
                'required',
                'string',
                'email',
                'max:120',
                'unique:accounts,Username,' . $user->UserID . ',UserID',
            ],
        ], [
            'Username.required' => 'Email address is required.',
            'Username.email' => 'Please enter a valid email address (e.g., name@gmail.com).',
            'Username.unique' => 'This email address is already taken by another account.',
        ]);

        $user->Username = $validated['Username'];
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Profile email updated successfully!',
            'username' => $user->Username,
            'initial' => $user->initial(),
        ]);
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ], [
            'current_password.required' => 'Please enter your current password.',
            'password.required' => 'Please enter a new password.',
            'password.min' => 'New password must be at least 8 characters long.',
            'password.confirmed' => 'New password confirmation does not match.',
            'password.different' => 'New password must be different from your current password.',
        ]);

        if (! Hash::check($validated['current_password'], $user->Password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password you entered is incorrect.'],
            ]);
        }

        $user->Password = Hash::make($validated['password']);
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Password changed successfully!',
        ]);
    }

    public function logoutOtherSessions(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'confirm_session_password' => ['required', 'string'],
        ], [
            'confirm_session_password.required' => 'Please enter your password to confirm.',
        ]);

        if (! Hash::check($request->confirm_session_password, $user->Password)) {
            throw ValidationException::withMessages([
                'confirm_session_password' => ['Incorrect password. Could not sign out other devices.'],
            ]);
        }

        if (Schema::hasTable('sessions')) {
            DB::table('sessions')
                ->where('user_id', $user->UserID)
                ->where('id', '!=', $request->session()->getId())
                ->delete();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Signed out from all other browser sessions.',
        ]);
    }

    public function setupTwoFactor(Request $request)
    {
        $user = Auth::user();

        if ($user->hasTwoFactorEnabled()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Two-factor authentication is already enabled on your account.',
            ], 422);
        }

        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();

        $request->session()->put('two_factor_setup_secret', $secret);

        $otpAuthUrl = $google2fa->getQRCodeUrl(
            SystemSetting::current()->system_name,
            $user->Username,
            $secret
        );

        $renderer = new ImageRenderer(
            new RendererStyle(210, 1),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $qrSvg = $writer->writeString($otpAuthUrl);

        return response()->json([
            'status' => 'success',
            'qr_svg' => $qrSvg,
            'secret' => $secret,
        ]);
    }

    public function enableTwoFactor(Request $request)
    {
        $user = Auth::user();

        if ($user->hasTwoFactorEnabled()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Two-factor authentication is already enabled.',
            ], 422);
        }

        $secret = (string) $request->session()->get('two_factor_setup_secret', '');

        if ($secret === '') {
            return response()->json([
                'status' => 'error',
                'message' => '2FA setup session expired. Click "Set Up 2FA" to generate a new QR code.',
            ], 422);
        }

        $cleanCode = preg_replace('/\s+/', '', (string) $request->input('code'));
        $request->merge(['code' => $cleanCode]);

        $request->validate([
            'code' => ['required', 'digits:6'],
        ], [
            'code.required' => 'Please enter the 6-digit verification code from Microsoft Authenticator.',
            'code.digits' => 'The verification code must be 6 digits.',
        ]);

        $google2fa = new Google2FA();

        if (! $google2fa->verifyKey($secret, $cleanCode)) {
            throw ValidationException::withMessages([
                'code' => ['Invalid 6-digit code. Make sure your phone time is synced and enter the current code shown in Microsoft Authenticator.'],
            ]);
        }

        $user->two_factor_secret = $secret;
        $user->two_factor_confirmed_at = now();
        $user->save();

        $request->session()->forget('two_factor_setup_secret');

        return response()->json([
            'status' => 'success',
            'message' => 'Two-Factor Authentication (Microsoft Authenticator) is now enabled!',
        ]);
    }

    public function disableTwoFactor(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'disable_2fa_password' => ['required', 'string'],
        ], [
            'disable_2fa_password.required' => 'Please enter your current password to disable 2FA.',
        ]);

        if (! Hash::check($request->disable_2fa_password, $user->Password)) {
            throw ValidationException::withMessages([
                'disable_2fa_password' => ['Incorrect password. Two-Factor Authentication remains enabled.'],
            ]);
        }

        $user->two_factor_secret = null;
        $user->two_factor_confirmed_at = null;
        $user->save();

        $request->session()->forget('two_factor_setup_secret');

        return response()->json([
            'status' => 'success',
            'message' => 'Two-Factor Authentication has been disabled.',
        ]);
    }
}