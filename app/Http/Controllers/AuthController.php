<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use PragmaRX\Google2FA\Google2FA;

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

        // Check if a user has passed password check and is awaiting 2FA verification
        $twoFactorPending = false;
        $twoFactorEmail = null;

        if ($request->session()->has('2fa:user:id')) {
            $expiresAt = (int) $request->session()->get('2fa:expires_at', 0);
            $pendingUser = Account::find($request->session()->get('2fa:user:id'));

            if ($pendingUser && $pendingUser->hasTwoFactorEnabled() && now()->timestamp <= $expiresAt) {
                $twoFactorPending = true;
                $twoFactorEmail = $pendingUser->Username;
            } else {
                $request->session()->forget(['2fa:user:id', '2fa:expires_at']);
            }
        }

        return view('login', [
            'captchaNum1' => $captcha['num1'],
            'captchaNum2' => $captcha['num2'],
            'lockoutRemaining' => $lockoutRemaining,
            'twoFactorPending' => $twoFactorPending,
            'twoFactorEmail' => $twoFactorEmail,
        ]);
    }

    // Login Handler with 5-Attempt / 20-Second Persistent Lockout + 2FA Check
    public function authenticate(Request $request)
    {
        $ip = $request->ip();
        $lockoutKey = "login_lockout_{$ip}";
        $attemptsKey = "login_attempts_{$ip}";

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

        $account = Account::where('Username', trim($request->email))->first();

        if (! $account || ! Hash::check($request->password, $account->Password)) {
            return $this->handleFailedLogin(
                $request,
                $attemptsKey,
                $lockoutKey,
                'The provided email or password does not match our records.'
            );
        }

        Cache::forget($attemptsKey);
        Cache::forget($lockoutKey);

        // If 2FA is enabled on this account (all roles), require 6-digit Microsoft Authenticator code first
        if ($account->hasTwoFactorEnabled()) {
            $request->session()->put('2fa:user:id', $account->UserID);
            $request->session()->put('2fa:expires_at', now()->addMinutes(5)->timestamp);

            return redirect()->route('login');
        }

        Auth::login($account);
        $request->session()->regenerate();

        return redirect()->intended('/dashboard/home');
    }

    // Verify 6-Digit Microsoft Authenticator Code on Login
    public function verifyTwoFactor(Request $request)
    {
        $ip = $request->ip();
        $lockoutKey = "login_lockout_{$ip}";
        $attemptsKey = "login_attempts_{$ip}";

        if (Cache::has($lockoutKey)) {
            $remaining = max(1, (int) Cache::get($lockoutKey) - now()->timestamp);

            return redirect()->route('login')
                ->withErrors(['lockout' => "Too many failed attempts. Please wait {$remaining} seconds before trying again."]);
        }

        $userId = $request->session()->get('2fa:user:id');
        $expiresAt = (int) $request->session()->get('2fa:expires_at', 0);

        if (! $userId || now()->timestamp > $expiresAt) {
            $request->session()->forget(['2fa:user:id', '2fa:expires_at']);

            return redirect()->route('login')
                ->withErrors(['login_error' => 'Your two-factor verification session expired. Please log in again.']);
        }

        $account = Account::find($userId);

        if (! $account || ! $account->hasTwoFactorEnabled()) {
            $request->session()->forget(['2fa:user:id', '2fa:expires_at']);

            return redirect()->route('login')
                ->withErrors(['login_error' => 'Two-factor authentication is no longer active for this account. Please log in again.']);
        }

        $cleanCode = preg_replace('/\s+/', '', (string) $request->input('code'));
        $request->merge(['code' => $cleanCode]);

        $validator = Validator::make($request->all(), [
            'code' => ['required', 'digits:6'],
        ], [
            'code.required' => 'Please enter the 6-digit verification code from Microsoft Authenticator.',
            'code.digits' => 'The verification code must be exactly 6 digits.',
        ]);

        if ($validator->fails()) {
            return redirect()->route('login')
                ->withErrors(['two_factor_error' => $validator->errors()->first()]);
        }

        $google2fa = new Google2FA();
        $isValid = $google2fa->verifyKey((string) $account->two_factor_secret, $cleanCode);

        if (! $isValid) {
            $attempts = (int) Cache::get($attemptsKey, 0) + 1;

            if ($attempts >= 5) {
                $unlockTimestamp = now()->addSeconds(20)->timestamp;
                Cache::put($lockoutKey, $unlockTimestamp, 20);
                Cache::forget($attemptsKey);
                $request->session()->forget(['2fa:user:id', '2fa:expires_at']);

                return redirect()->route('login')
                    ->withErrors(['lockout' => 'Too many failed verification attempts. Please wait 20 seconds before trying again.']);
            }

            Cache::put($attemptsKey, $attempts, now()->addMinutes(5));
            $remainingAttempts = 5 - $attempts;

            return redirect()->route('login')
                ->withErrors([
                    'two_factor_error' => "Invalid 6-digit authenticator code. ({$remainingAttempts} attempt(s) remaining before lockout.)",
                ]);
        }

        Cache::forget($attemptsKey);
        Cache::forget($lockoutKey);
        $request->session()->forget(['2fa:user:id', '2fa:expires_at']);

        Auth::login($account);
        $request->session()->regenerate();

        return redirect()->intended('/dashboard/home');
    }

    // Cancel pending 2FA challenge and return to email/password login
    public function cancelTwoFactor(Request $request)
    {
        $request->session()->forget(['2fa:user:id', '2fa:expires_at']);

        return redirect()->route('login');
    }

    // Sign-Up Handler with Role Selection ('Staff' or 'Mechanic') & Multiplication CAPTCHA
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