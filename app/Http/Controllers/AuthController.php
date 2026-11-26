<?php

namespace App\Http\Controllers;

use App\Models\AccessLog;
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
        $captcha = $this->generateCaptcha();

        $lockoutKey = 'login_lockout_' . $request->ip();
        $lockoutRemaining = 0;

        if (Cache::has($lockoutKey)) {
            $unlockTimestamp = (int) Cache::get($lockoutKey);
            $lockoutRemaining = max(0, $unlockTimestamp - now()->timestamp);

            if ($lockoutRemaining === 0) {
                Cache::forget($lockoutKey);
            }
        }

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

    public function authenticate(Request $request)
    {
        $ip = $request->ip();
        $lockoutKey = "login_lockout_{$ip}";
        $attemptsKey = "login_attempts_{$ip}";
        $attemptedEmail = trim((string) $request->input('email'));

        if (Cache::has($lockoutKey)) {
            $remaining = max(1, (int) Cache::get($lockoutKey) - now()->timestamp);

            AccessLog::record(
                'Lockout',
                'Blocked',
                $attemptedEmail ?: null,
                null,
                "Login blocked during active 20s IP lockout ({$remaining}s remaining).",
                $request
            );

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
            return $this->handleFailedLogin(
                $request,
                $attemptsKey,
                $lockoutKey,
                $validator->errors()->first(),
                $attemptedEmail ?: null
            );
        }

        $account = Account::where('Username', $attemptedEmail)->first();

        if (! $account || ! Hash::check($request->password, $account->Password)) {
            return $this->handleFailedLogin(
                $request,
                $attemptsKey,
                $lockoutKey,
                'The provided email or password does not match our records.',
                $attemptedEmail,
                $account?->UserID
            );
        }

        Cache::forget($attemptsKey);
        Cache::forget($lockoutKey);

        if ($account->hasTwoFactorEnabled()) {
            $request->session()->put('2fa:user:id', $account->UserID);
            $request->session()->put('2fa:expires_at', now()->addMinutes(5)->timestamp);

            AccessLog::record(
                '2FA Challenge',
                'Success',
                $account->Username,
                $account->UserID,
                'Password verified; awaiting 6-digit authenticator code.',
                $request
            );

            return redirect()->route('login');
        }

        Auth::login($account);
        $request->session()->regenerate();

        AccessLog::record(
            'Login Success',
            'Success',
            $account->Username,
            $account->UserID,
            "Logged in as {$account->Role}.",
            $request
        );

        return redirect()->intended('/dashboard/home');
    }

    public function verifyTwoFactor(Request $request)
    {
        $ip = $request->ip();
        $lockoutKey = "login_lockout_{$ip}";
        $attemptsKey = "login_attempts_{$ip}";

        if (Cache::has($lockoutKey)) {
            $remaining = max(1, (int) Cache::get($lockoutKey) - now()->timestamp);

            AccessLog::record(
                'Lockout',
                'Blocked',
                null,
                null,
                "2FA verification blocked during active IP lockout ({$remaining}s remaining).",
                $request
            );

            return redirect()->route('login')
                ->withErrors(['lockout' => "Too many failed attempts. Please wait {$remaining} seconds before trying again."]);
        }

        $userId = $request->session()->get('2fa:user:id');
        $expiresAt = (int) $request->session()->get('2fa:expires_at', 0);

        if (! $userId || now()->timestamp > $expiresAt) {
            $request->session()->forget(['2fa:user:id', '2fa:expires_at']);

            AccessLog::record(
                '2FA Failed',
                'Failed',
                null,
                $userId ? (int) $userId : null,
                'Two-factor verification session expired.',
                $request
            );

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
            AccessLog::record(
                '2FA Failed',
                'Failed',
                $account->Username,
                $account->UserID,
                'Malformed 2FA code submitted.',
                $request
            );

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

                AccessLog::record(
                    'Lockout',
                    'Blocked',
                    $account->Username,
                    $account->UserID,
                    'IP locked out for 20 seconds after 5 failed 2FA attempts.',
                    $request
                );

                return redirect()->route('login')
                    ->withErrors(['lockout' => 'Too many failed verification attempts. Please wait 20 seconds before trying again.']);
            }

            Cache::put($attemptsKey, $attempts, now()->addMinutes(5));
            $remainingAttempts = 5 - $attempts;

            AccessLog::record(
                '2FA Failed',
                'Failed',
                $account->Username,
                $account->UserID,
                "Invalid 6-digit authenticator code (Attempt {$attempts}/5).",
                $request
            );

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

        AccessLog::record(
            'Login Success',
            'Success',
            $account->Username,
            $account->UserID,
            "Logged in with 2FA verified as {$account->Role}.",
            $request
        );

        return redirect()->intended('/dashboard/home');
    }

    public function cancelTwoFactor(Request $request)
    {
        $userId = $request->session()->get('2fa:user:id');
        $account = $userId ? Account::find($userId) : null;

        if ($account) {
            AccessLog::record(
                '2FA Cancelled',
                'Failed',
                $account->Username,
                $account->UserID,
                'User cancelled 2FA verification challenge.',
                $request
            );
        }

        $request->session()->forget(['2fa:user:id', '2fa:expires_at']);

        return redirect()->route('login');
    }

    public function register(Request $request)
    {
        $attemptedEmail = trim((string) $request->input('email'));

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
            AccessLog::record(
                'Registration Failed',
                'Failed',
                $attemptedEmail ?: null,
                null,
                'Registration validation failed: ' . $validator->errors()->first(),
                $request
            );

            return back()
                ->withErrors($validator, 'signup')
                ->withInput($request->except(['password', 'password_confirmation', 'captcha_answer']))
                ->with('active_tab', 'signup');
        }

        $expectedAnswer = (int) session('captcha_answer');
        if ((int) $request->captcha_answer !== $expectedAnswer) {
            AccessLog::record(
                'Registration Failed',
                'Failed',
                $attemptedEmail,
                null,
                'Incorrect math CAPTCHA answer during registration.',
                $request
            );

            return back()
                ->withErrors(['captcha_answer' => 'Incorrect CAPTCHA answer. A new math problem has been generated.'], 'signup')
                ->withInput($request->except(['password', 'password_confirmation', 'captcha_answer']))
                ->with('active_tab', 'signup');
        }

        $newAccount = Account::create([
            'Username' => $attemptedEmail,
            'Password' => Hash::make($request->password),
            'Role' => $request->role,
        ]);

        AccessLog::record(
            'Registration',
            'Success',
            $newAccount->Username,
            $newAccount->UserID,
            "New account registered with role {$newAccount->Role}.",
            $request
        );

        session()->forget(['captcha_num1', 'captcha_num2', 'captcha_answer']);

        return redirect('/')
            ->with('signup_success', "Account created successfully with {$request->role} access! You can now log in.");
    }

    public function refreshCaptcha()
    {
        $captcha = $this->generateCaptcha();

        return response()->json([
            'num1' => $captcha['num1'],
            'num2' => $captcha['num2'],
        ]);
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            AccessLog::record(
                'Logout',
                'Success',
                $user->Username,
                $user->UserID,
                "User logged out ({$user->Role}).",
                $request
            );
        }

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

    private function handleFailedLogin(
        Request $request,
        string $attemptsKey,
        string $lockoutKey,
        string $errorMessage,
        ?string $attemptedEmail = null,
        ?int $userId = null
    ) {
        $attempts = (int) Cache::get($attemptsKey, 0) + 1;

        if ($attempts >= 5) {
            $unlockTimestamp = now()->addSeconds(20)->timestamp;
            Cache::put($lockoutKey, $unlockTimestamp, 20);
            Cache::forget($attemptsKey);

            AccessLog::record(
                'Lockout',
                'Blocked',
                $attemptedEmail,
                $userId,
                'IP locked out for 20 seconds after 5 failed login attempts.',
                $request
            );

            return back()
                ->withErrors(['lockout' => 'Too many failed login attempts. Please wait 20 seconds before trying again.'])
                ->onlyInput('email');
        }

        Cache::put($attemptsKey, $attempts, now()->addMinutes(5));
        $remainingAttempts = 5 - $attempts;

        AccessLog::record(
            'Login Failed',
            'Failed',
            $attemptedEmail,
            $userId,
            "Failed login attempt ({$attempts}/5): {$errorMessage}",
            $request
        );

        return back()
            ->withErrors([
                'login_error' => "{$errorMessage} ({$remainingAttempts} attempt(s) remaining before lockout.)",
            ])
            ->onlyInput('email');
    }
}