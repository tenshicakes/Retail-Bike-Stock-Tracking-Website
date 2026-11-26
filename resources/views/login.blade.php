<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Retail Bike Stock Tracking System</title>

    @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>
<body class="bg-light">

    @php
        $isTwoFactorActive = (bool) ($twoFactorPending ?? false);
        $isSignupActive = !$isTwoFactorActive && (session('active_tab') === 'signup' || $errors->signup->any());
        $isLockedOut = ($lockoutRemaining ?? 0) > 0;
    @endphp

    <div class="container-fluid min-vh-100 d-flex flex-column p-0">

        <div class="row g-0 flex-grow-1">
            
            <div class="col-lg-6 bg-white d-flex flex-column align-items-center justify-content-center p-4 p-md-5 text-center">
                <img src="{{ asset('images/JFM.png') }}" alt="Logo" class="logo mb-4">  
                <h1 class="display-5 display-md-4 fw-bold text-dark">Alvin's Bike Repair Shop</h1>
                <p class="text-muted mb-0 fs-6">Retail Bike Stock Tracking System</p>
            </div>

            <div class="col-lg-6 login-right-side d-flex align-items-center justify-content-center p-3 p-sm-4 p-md-5">
                
                <div class="glass-login-box p-4 p-sm-5 shadow-lg my-auto">

                    @if($isTwoFactorActive)
                        <!-- ==================== TWO-FACTOR AUTHENTICATION CHALLENGE ==================== -->
                        <div id="twoFactorSection">
                            <div class="text-center mb-3">
                                <span class="badge bg-info text-dark px-3 py-2 rounded-pill fw-bold mb-2">
                                    2-Step Verification
                                </span>
                                <h3 class="text-white fw-bold fs-3 mb-1">Authenticator Code</h3>
                                <p class="text-white-50 small mb-0">
                                    Verifying sign-in for <strong class="text-white">{{ $twoFactorEmail }}</strong>
                                </p>
                            </div>

                            <div class="alert alert-info py-2 px-3 small mb-3" role="alert">
                                Open <strong>Microsoft Authenticator</strong> on your mobile device and enter the 6-digit verification code.
                            </div>

                            @if($errors->has('two_factor_error'))
                                <div class="alert alert-danger py-2 px-3 small fw-semibold mb-3" role="alert">
                                    {{ $errors->first('two_factor_error') }}
                                </div>
                            @endif

                            <form method="POST" action="{{ route('login.2fa.verify') }}" id="twoFactorForm">
                                @csrf
                                <div class="mb-4">
                                    <label for="two_factor_code" class="form-label text-white fw-semibold">6-Digit Verification Code</label>
                                    <input
                                        type="text"
                                        inputmode="numeric"
                                        pattern="[0-9]{6}"
                                        maxlength="6"
                                        autocomplete="one-time-code"
                                        class="form-control bg-white text-dark py-2 text-center fw-bold fs-3"
                                        style="letter-spacing: 0.45em;"
                                        id="two_factor_code"
                                        name="code"
                                        placeholder="000000"
                                        required
                                        autofocus
                                    >
                                </div>

                                <div class="d-grid gap-2 button-container">
                                    <button type="submit" class="btn btn-info fw-bold text-white py-2 shadow-sm fs-5">
                                        Verify &amp; Login
                                    </button>
                                </div>
                            </form>

                            <form method="POST" action="{{ route('login.2fa.cancel') }}" class="mt-3 text-center">
                                @csrf
                                <button type="submit" class="btn btn-link text-white-50 text-decoration-none small p-0">
                                    &larr; Use a different account
                                </button>
                            </form>
                        </div>
                    @else
                        <!-- Mode Switcher Tabs -->
                        <div class="auth-switcher d-grid grid-cols-2 mb-4" style="display: grid; grid-template-columns: 1fr 1fr; gap: 4px;">
                            <button type="button" id="tabLoginBtn" class="auth-tab-btn py-2 {{ !$isSignupActive ? 'active' : '' }}">
                                Login
                            </button>
                            <button type="button" id="tabSignupBtn" class="auth-tab-btn py-2 {{ $isSignupActive ? 'active' : '' }}">
                                Sign Up
                            </button>
                        </div>

                        <!-- ==================== LOGIN SECTION ==================== -->
                        <div id="loginSection" class="{{ $isSignupActive ? 'd-none' : '' }}">
                            <h3 class="text-white text-center mb-4 fw-bold fs-3">Login your account</h3>

                            @if(session('signup_success'))
                                <div class="alert alert-success py-2 px-3 small fw-semibold mb-3" role="alert">
                                    {{ session('signup_success') }}
                                </div>
                            @endif

                            <!-- Persistent Lockout Alert & Live Countdown -->
                            <div id="lockoutAlert" class="alert alert-danger py-2 px-3 small fw-semibold mb-3 {{ $isLockedOut ? '' : 'd-none' }}" role="alert">
                                Too many failed login attempts. Please wait 
                                <span id="lockoutTimer" class="fw-bold text-decoration-underline">{{ $lockoutRemaining }}</span> 
                                seconds before trying again.
                            </div>

                            @if($errors->has('login_error') && !$isLockedOut)
                                <div class="alert alert-danger py-2 px-3 small fw-semibold mb-3" role="alert">
                                    {{ $errors->first('login_error') }}
                                </div>
                            @endif
                            
                            <form method="POST" action="/login" id="loginForm">
                                @csrf
                                <div class="mb-3">
                                    <label for="login_email" class="form-label text-white fw-semibold fs-5">Email Address</label>
                                    <input 
                                        type="email" 
                                        class="form-control bg-white text-dark py-2" 
                                        id="login_email" 
                                        name="email" 
                                        placeholder="name@gmail.com"
                                        value="{{ !$isSignupActive ? old('email') : '' }}"
                                        {{ $isLockedOut ? 'disabled' : '' }}
                                        required 
                                        autofocus
                                    >
                                </div>
                                
                                <div class="mb-4">
                                    <label for="login_password" class="form-label text-white fw-semibold fs-5">Password</label>
                                    <input 
                                        type="password" 
                                        class="form-control bg-white text-dark py-2" 
                                        id="login_password" 
                                        name="password" 
                                        minlength="8"
                                        placeholder="Minimum 8 characters"
                                        {{ $isLockedOut ? 'disabled' : '' }}
                                        required
                                    >
                                </div>
                                
                                <div class="d-grid mt-4 button-container">
                                    <button type="submit" id="btnLoginSubmit" class="btn btn-info fw-bold text-white py-2 shadow-sm fs-4" {{ $isLockedOut ? 'disabled' : '' }}>
                                        {{ $isLockedOut ? "Locked ({$lockoutRemaining}s)" : 'Login' }}
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- ==================== SIGN UP SECTION ==================== -->
                        <div id="signupSection" class="{{ $isSignupActive ? '' : 'd-none' }}">
                            <h3 class="text-white text-center mb-1 fw-bold fs-3">Create an account</h3>
                            <p class="text-white-50 text-center small mb-4">Choose your role and complete the registration details</p>

                            @if($errors->signup->any())
                                <div class="alert alert-danger py-2 px-3 small fw-semibold mb-3" role="alert">
                                    <ul class="mb-0 ps-3">
                                        @foreach($errors->signup->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('register') }}" id="signupForm">
                                @csrf
                                <div class="mb-3">
                                    <label for="signup_email" class="form-label text-white fw-semibold">Email Address</label>
                                    <input 
                                        type="email" 
                                        class="form-control bg-white text-dark py-2" 
                                        id="signup_email" 
                                        name="email" 
                                        placeholder="name@gmail.com"
                                        value="{{ $isSignupActive ? old('email') : '' }}"
                                        required
                                    >
                                </div>

                                <div class="mb-3">
                                    <label for="signup_role" class="form-label text-white fw-semibold">Role</label>
                                    <select class="form-select bg-white text-dark py-2" id="signup_role" name="role" required>
                                        <option value="" disabled {{ !old('role') ? 'selected' : '' }}>-- Select Role --</option>
                                        <option value="Staff" {{ old('role') === 'Staff' ? 'selected' : '' }}>Staff</option>
                                        <option value="Mechanic" {{ old('role') === 'Mechanic' ? 'selected' : '' }}>Mechanic</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="signup_password" class="form-label text-white fw-semibold">Password</label>
                                    <input 
                                        type="password" 
                                        class="form-control bg-white text-dark py-2" 
                                        id="signup_password" 
                                        name="password" 
                                        minlength="8"
                                        placeholder="At least 8 characters"
                                        required
                                    >
                                </div>

                                <div class="mb-3">
                                    <label for="signup_password_confirmation" class="form-label text-white fw-semibold">Confirm Password</label>
                                    <input 
                                        type="password" 
                                        class="form-control bg-white text-dark py-2" 
                                        id="signup_password_confirmation" 
                                        name="password_confirmation" 
                                        minlength="8"
                                        placeholder="Re-enter password"
                                        required
                                    >
                                </div>

                                <!-- Math CAPTCHA Challenge -->
                                <div class="captcha-box p-3 mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label for="captcha_answer" class="form-label text-white fw-semibold m-0">
                                            Verify CAPTCHA: 
                                            <span class="badge bg-info text-dark fs-6 ms-1" id="captchaEquation">
                                                {{ $captchaNum1 }} &times; {{ $captchaNum2 }} = ?
                                            </span>
                                        </label>
                                        <button type="button" id="btnRefreshCaptcha" class="btn btn-sm btn-outline-light" title="New Math Problem">
                                            Refresh
                                        </button>
                                    </div>
                                    <input 
                                        type="number" 
                                        class="form-control bg-white text-dark py-2" 
                                        id="captcha_answer" 
                                        name="captcha_answer" 
                                        placeholder="Enter product answer"
                                        required
                                    >
                                </div>

                                <div class="d-grid mt-3 button-container">
                                    <button type="submit" class="btn btn-info fw-bold text-white py-2 shadow-sm fs-5">
                                        Register
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif

                </div>

            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Strip non-digits on the 6-digit 2FA input
            const twoFactorInput = document.getElementById('two_factor_code');
            if (twoFactorInput) {
                twoFactorInput.addEventListener('input', function () {
                    this.value = this.value.replace(/\D/g, '').slice(0, 6);
                });
            }

            // 1. Login / Sign Up Tab Switcher
            const tabLoginBtn = document.getElementById('tabLoginBtn');
            const tabSignupBtn = document.getElementById('tabSignupBtn');
            const loginSection = document.getElementById('loginSection');
            const signupSection = document.getElementById('signupSection');

            if (tabLoginBtn && tabSignupBtn) {
                tabLoginBtn.addEventListener('click', function () {
                    tabLoginBtn.classList.add('active');
                    tabSignupBtn.classList.remove('active');
                    loginSection.classList.remove('d-none');
                    signupSection.classList.add('d-none');
                });

                tabSignupBtn.addEventListener('click', function () {
                    tabSignupBtn.classList.add('active');
                    tabLoginBtn.classList.remove('active');
                    signupSection.classList.remove('d-none');
                    loginSection.classList.add('d-none');
                });
            }

            // 2. Persistent 20-Second Lockout Countdown
            let remainingSeconds = {{ (int) ($lockoutRemaining ?? 0) }};
            const lockoutAlert = document.getElementById('lockoutAlert');
            const lockoutTimer = document.getElementById('lockoutTimer');
            const loginEmail = document.getElementById('login_email');
            const loginPassword = document.getElementById('login_password');
            const btnLoginSubmit = document.getElementById('btnLoginSubmit');

            if (remainingSeconds > 0 && lockoutAlert && btnLoginSubmit) {
                const interval = setInterval(function () {
                    remainingSeconds--;

                    if (remainingSeconds > 0) {
                        lockoutTimer.textContent = remainingSeconds;
                        btnLoginSubmit.textContent = `Locked (${remainingSeconds}s)`;
                    } else {
                        clearInterval(interval);
                        lockoutAlert.classList.add('d-none');
                        if (loginEmail) loginEmail.disabled = false;
                        if (loginPassword) loginPassword.disabled = false;
                        btnLoginSubmit.disabled = false;
                        btnLoginSubmit.textContent = 'Login';
                        if (loginEmail) loginEmail.focus();
                    }
                }, 1000);
            }

            // 3. Manual CAPTCHA Refresh Button
            const btnRefreshCaptcha = document.getElementById('btnRefreshCaptcha');
            const captchaEquation = document.getElementById('captchaEquation');
            const captchaInput = document.getElementById('captcha_answer');

            if (btnRefreshCaptcha) {
                btnRefreshCaptcha.addEventListener('click', function () {
                    fetch('{{ route("captcha.refresh") }}', {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(res => res.json())
                    .then(data => {
                        captchaEquation.innerHTML = `${data.num1} &times; ${data.num2} = ?`;
                        captchaInput.value = '';
                        captchaInput.focus();
                    });
                });
            }
        });
    </script>
</body>
</html>