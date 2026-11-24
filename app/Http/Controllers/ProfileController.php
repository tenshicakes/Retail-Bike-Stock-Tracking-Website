<?php

namespace App\Http\Controllers;

use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Personal inventory transaction stats for this user
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
}