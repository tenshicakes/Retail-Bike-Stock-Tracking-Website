<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function showLogin(Request $request)
    {
        // Generate a fresh 1-20 multiplication CAPTCHA pair on every page load/reload
        $captcha = $this->generateCaptcha();

        // Check if this IP is currently locked out
        $lockoutKey = 'login_lockout_' . $request->ip();
        $lockoutRemaining = 0;

        if (Cache::has($lockoutKey)) {
            $unlockTimestamp = (int) Cache::get($lockoutKey);
            $lockoutRemaining = max(0, $unlockTimestamp - now()->timestamp);

            if ($lockoutRemaining === 0) {
                Cache::forget($lockoutKey);
            }
        }

        return view('login', [
            'captchaNum1' => $captcha['num1'],
            'captchaNum2' => $captcha['num2'],
            'lockoutRemaining' => $lockoutRemaining,
        ]);
    }

    // Login Handler with 5-Attempt / 20-Second Persistent Lockout
    public function authenticate(Request $request)
    {
        $ip = $request->ip();
        $lockoutKey = "login_lockout_{$ip}";
        $attemptsKey = "login_attempts_{$ip}";

        // Block login attempts immediately if still inside the 20-second lockout window
        if (Cache::has($lockoutKey)) {
            $remaining = max(1, (int) Cache::get($lockoutKey) - now()->timestamp);

            return back()
                ->withErrors(['lockout' => "Too many failed login attempts. Please wait {$remaining} seconds before trying again."])
                ->onlyInput('email');
        }

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:8',
        ], [
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address (e.g., name@gmail.com).',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters long.',
        ]);

        if ($validator->fails()) {
            return $this->handleFailedLogin($request, $attemptsKey, $lockoutKey, $validator->errors()->first());
        }

        $credentials = [
            'Username' => trim($request->email),
            'password' => $request->password,
        ];

        if (Auth::attempt($credentials)) {
            Cache::forget($attemptsKey);
            Cache::forget($lockoutKey);

            $request->session()->regenerate();

            return redirect()->intended('/dashboard/home');
        }

        return $this->handleFailedLogin(
            $request,
            $attemptsKey,
            $lockoutKey,
            'The provided email or password does not match our records.'
        );
    }

    // Sign-Up Handler with Multiplication CAPTCHA & Default 'Staff' Role
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:accounts,Username',
            'role' => 'required|in:Staff,Mechanic',
            'password' => 'required|string|min:8|confirmed',
            'captcha_answer' => 'required|numeric',
        ], [
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address (e.g., name@gmail.com).',
            'email.unique' => 'This email address is already registered.',
            'role.required' => 'Please select a role.',
            'role.in' => 'Selected role must be either Staff or Mechanic.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters long.',
            'password.confirmed' => 'Password confirmation does not match.',
            'captcha_answer.required' => 'Please solve the math CAPTCHA.',
            'captcha_answer.numeric' => 'CAPTCHA answer must be a valid number.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator, 'signup')
                ->withInput($request->except(['password', 'password_confirmation', 'captcha_answer']))
                ->with('active_tab', 'signup');
        }

        // Verify CAPTCHA answer against session
        $expectedAnswer = (int) session('captcha_answer');
        if ((int) $request->captcha_answer !== $expectedAnswer) {
            return back()
                ->withErrors(['captcha_answer' => 'Incorrect CAPTCHA answer. A new math problem has been generated.'], 'signup')
                ->withInput($request->except(['password', 'password_confirmation', 'captcha_answer']))
                ->with('active_tab', 'signup');
        }

        Account::create([
            'Username' => trim($request->email),
            'Password' => Hash::make($request->password),
            'Role' => $request->role,
        ]);

        session()->forget(['captcha_num1', 'captcha_num2', 'captcha_answer']);

        return redirect('/')
            ->with('signup_success', "Account created successfully with {$request->role} access! You can now log in.");
    }

    // AJAX endpoint to manually refresh CAPTCHA numbers
    public function refreshCaptcha()
    {
        $captcha = $this->generateCaptcha();

        return response()->json([
            'num1' => $captcha['num1'],
            'num2' => $captcha['num2'],
        ]);
    }

    // Logout Handler
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    private function generateCaptcha(): array
    {
        $num1 = random_int(1, 20);
        $num2 = random_int(1, 20);

        session([
            'captcha_num1' => $num1,
            'captcha_num2' => $num2,
            'captcha_answer' => $num1 * $num2,
        ]);

        return ['num1' => $num1, 'num2' => $num2];
    }

    private function handleFailedLogin(Request $request, string $attemptsKey, string $lockoutKey, string $errorMessage)
    {
        $attempts = (int) Cache::get($attemptsKey, 0) + 1;

        if ($attempts >= 5) {
            $unlockTimestamp = now()->addSeconds(20)->timestamp;
            Cache::put($lockoutKey, $unlockTimestamp, 20);
            Cache::forget($attemptsKey);

            return back()
                ->withErrors(['lockout' => 'Too many failed login attempts. Please wait 20 seconds before trying again.'])
                ->onlyInput('email');
        }

        Cache::put($attemptsKey, $attempts, now()->addMinutes(5));
        $remainingAttempts = 5 - $attempts;

        return back()
            ->withErrors([
                'login_error' => "{$errorMessage} ({$remainingAttempts} attempt(s) remaining before lockout.)",
            ])
            ->onlyInput('email');
    }
}