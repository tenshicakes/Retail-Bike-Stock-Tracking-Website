<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Show the login page
    public function showLogin()
    {
        return view('login');
    }

    // Process the login attempt
    public function authenticate(Request $request)
    {
        // 1. Validate the form inputs
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // 2. Attempt to log in
        // Note: Even though your DB column is 'Password', Laravel's attempt() 
        // method requires the array key for the password to be lowercase 'password'.
        $credentials = [
            'Username' => $request->username,
            'password' => $request->password 
        ];

        if (Auth::attempt($credentials)) {
            // 3. Success! Regenerate the session to prevent fixation attacks
            $request->session()->regenerate();

            // Redirect to the dashboard home
            return redirect()->intended('/dashboard/home');
        }

        // 4. Failure! Send them back with an error
        return back()->withErrors([
            'username' => 'The provided credentials do not match our records.',
        ])->onlyInput('username');
    }

    // Process the logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
