<x-layout>

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h1 class="fw-bold text-dark m-0">Profile & Settings</h1>
            <p class="text-muted mb-0">Manage your account credentials, security preferences, and view your role permissions.</p>
        </div>
    </div>

    <div class="row g-4">
        
        <!-- LEFT COLUMN: USER CARD, ACTIVITY STATS & ROLE PERMISSIONS -->
        <div class="col-lg-4">
            
            <!-- Profile Summary Card -->
            <div class="card shadow-sm border-0 rounded-4 mb-4">
                <div class="card-body p-4 text-center">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center text-white fw-bold mb-3 shadow-sm"
                         id="profileAvatarInitial"
                         style="width: 80px; height: 80px; font-size: 2rem; background-color: rgb(12, 17, 44);">
                        {{ strtoupper(substr($user->Username, 0, 1)) }}
                    </div>

                    <h5 class="fw-bold text-dark mb-1 text-break" id="profileDisplayEmail">{{ $user->Username }}</h5>
                    
                    <div class="mb-3">
                        <span class="badge bg-primary px-3 py-2 rounded-pill">{{ $user->Role }}</span>
                    </div>

                    <div class="border-top pt-3 text-start small">
                        <div class="d-flex justify-content-between py-1">
                            <span class="text-muted fw-semibold">User ID:</span>
                            <span class="fw-bold text-dark">#{{ $user->UserID }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-1">
                            <span class="text-muted fw-semibold">2FA Protection:</span>
                            @if($user->hasTwoFactorEnabled())
                                <span class="badge bg-success-subtle text-success border">Enabled</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border">Disabled</span>
                            @endif
                        </div>
                        <div class="d-flex justify-content-between py-1">
                            <span class="text-muted fw-semibold">Member Since:</span>
                            <span class="fw-bold text-dark">
                                {{ $user->created_at ? $user->created_at->format('M d, Y') : 'N/A' }}
                            </span>
                        </div>
                    </div>

                    <!-- Personal Inventory Activity Stats -->
                    <div class="border-top mt-3 pt-3">
                        <h6 class="text-muted fw-bold small text-uppercase mb-3">Your Inventory Activity</h6>
                        <div class="row g-2 text-center">
                            <div class="col-4">
                                <div class="p-2 bg-light rounded-3 border">
                                    <div class="fw-bold fs-5 text-dark">{{ $totalTransactions }}</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">Logs</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 bg-light rounded-3 border">
                                    <div class="fw-bold fs-5 text-primary">+{{ $totalStockIns }}</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">Stock-In</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 bg-light rounded-3 border">
                                    <div class="fw-bold fs-5 text-secondary">-{{ $totalStockOuts }}</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">Stock-Out</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Role Access & Permissions Card -->
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-white border-bottom px-4 py-3">
                    <h6 class="m-0 fw-bold text-dark"><i class="bi bi-shield-check me-2 text-primary"></i>Role Access & Permissions</h6>
                </div>
                <div class="card-body p-4">
                    <ul class="list-unstyled mb-0 d-flex flex-column gap-2 small">
                        <li class="d-flex justify-content-between align-items-center">
                            <span>Dashboard & Products View</span>
                            <span class="badge bg-success-subtle text-success border">Allowed</span>
                        </li>
                        <li class="d-flex justify-content-between align-items-center">
                            <span>Batch Stock-In / Stock-Out</span>
                            <span class="badge bg-success-subtle text-success border">Allowed</span>
                        </li>
                        <li class="d-flex justify-content-between align-items-center">
                            <span>Edit Product Details</span>
                            @if($user->hasRole(['Administrator', 'Owner', 'Mechanic']))
                                <span class="badge bg-success-subtle text-success border">Allowed</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border">Restricted</span>
                            @endif
                        </li>
                        <li class="d-flex justify-content-between align-items-center">
                            <span>Low Stock & No Stock Alerts</span>
                            @if($user->canAccessPage('lowstock'))
                                <span class="badge bg-success-subtle text-success border">Allowed</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border">Restricted</span>
                            @endif
                        </li>
                        <li class="d-flex justify-content-between align-items-center">
                            <span>Activity Logs & PDF Export</span>
                            @if($user->canAccessPage('logs'))
                                <span class="badge bg-success-subtle text-success border">Allowed</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border">Restricted</span>
                            @endif
                        </li>
                        <li class="d-flex justify-content-between align-items-center">
                            <span>Account Management</span>
                            @if($user->canAccessPage('accounts'))
                                <span class="badge bg-success-subtle text-success border">Allowed</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border">Restricted</span>
                            @endif
                        </li>
                    </ul>
                </div>
            </div>

        </div>

        <!-- RIGHT COLUMN: ACCOUNT SETTINGS, PASSWORD & 2FA -->
        <div class="col-lg-8">
            
            <!-- 1. User Information Settings -->
            <div class="card shadow-sm border-0 rounded-4 mb-4">
                <div class="card-header bg-white border-bottom px-4 py-3">
                    <h5 class="m-0 fw-bold text-dark"><i class="bi bi-person-vcard me-2 text-primary"></i>Account Information</h5>
                </div>
                <div class="card-body p-4">
                    <form id="frmUpdateProfileInfo">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-7">
                                <label for="profileUsername" class="form-label fw-bold text-muted">Email Address (Login Username)</label>
                                <input type="email" class="form-control" id="profileUsername" name="Username" value="{{ $user->Username }}" placeholder="name@gmail.com" required>
                                <div class="invalid-feedback" id="errUsername"></div>
                            </div>

                            <div class="col-md-5">
                                <label class="form-label fw-bold text-muted">Assigned Role</label>
                                <input type="text" class="form-control bg-light" value="{{ $user->Role }}" disabled readonly>
                                <div class="form-text">You cannot change your role.</div>
                            </div>
                        </div>

                        <div class="mt-4 d-flex justify-content-end">
                            <button type="submit" class="btn btn-dark fw-bold px-4" id="btnSaveInfo">
                                <i class="bi bi-check-circle-fill me-1"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- 2. Security & Password Settings -->
            <div class="card shadow-sm border-0 rounded-4 mb-4">
                <div class="card-header bg-white border-bottom px-4 py-3">
                    <h5 class="m-0 fw-bold text-dark"><i class="bi bi-key me-2 text-primary"></i>Change Password</h5>
                </div>
                <div class="card-body p-4">
                    <form id="frmUpdatePassword">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="current_password" class="form-label fw-bold text-muted">Current Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="current_password" name="current_password" placeholder="Enter your current password" required>
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="#current_password">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <div class="invalid-feedback" id="errCurrentPassword"></div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="new_password" class="form-label fw-bold text-muted">New Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="new_password" name="password" minlength="8" placeholder="Minimum 8 characters" required>
                                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="#new_password">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <div class="invalid-feedback" id="errNewPassword"></div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="new_password_confirmation" class="form-label fw-bold text-muted">Confirm New Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="new_password_confirmation" name="password_confirmation" minlength="8" placeholder="Re-enter new password" required>
                                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="#new_password_confirmation">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary fw-bold px-4" id="btnSavePassword">
                                <i class="bi bi-shield-lock-fill me-1"></i> Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- 3. Two-Factor Authentication (Microsoft Authenticator) -->
            <div class="card shadow-sm border-0 rounded-4 mb-4">
                <div class="card-header bg-white border-bottom px-4 py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="m-0 fw-bold text-dark">
                        <i class="bi bi-phone-vibrate me-2 text-primary"></i>Two-Factor Authentication (2FA)
                    </h5>
                    @if($user->hasTwoFactorEnabled())
                        <span class="badge bg-success-subtle text-success border px-3 py-2 rounded-pill">
                            <i class="bi bi-shield-fill-check me-1"></i> Enabled
                        </span>
                    @else
                        <span class="badge bg-secondary-subtle text-secondary border px-3 py-2 rounded-pill">
                            Disabled
                        </span>
                    @endif
                </div>

                <div class="card-body p-4">
                    @if(! $user->hasTwoFactorEnabled())
                        <!-- Intro State Before Generating QR Code -->
                        <div id="twoFactorIntro">
                            <p class="text-muted mb-3">
                                Protect your account with <strong>Microsoft Authenticator</strong>. When enabled, you will be required to enter a 6-digit verification code from the app on every login attempt.
                            </p>
                            <button type="button" class="btn btn-dark fw-bold px-4" id="btnStart2faSetup">
                                <i class="bi bi-qr-code-scan me-2"></i> Enable Two-Factor Authentication
                            </button>
                        </div>

                        <!-- Step-by-Step QR Code & Initial 6-Digit Verification -->
                        <div id="twoFactorSetupContainer" class="d-none">
                            <div class="row g-4 align-items-center">
                                <div class="col-md-5 text-center">
                                    <div class="p-3 bg-white border rounded-4 shadow-sm d-inline-block" id="twoFactorQrBox">
                                        <!-- SVG QR code injected here -->
                                    </div>
                                    <div class="mt-2 small text-muted">
                                        Manual Setup Key:
                                        <div class="fw-bold text-dark font-monospace user-select-all mt-1" id="twoFactorManualSecret"></div>
                                    </div>
                                </div>

                                <div class="col-md-7">
                                    <h6 class="fw-bold text-dark mb-2">1. Scan QR Code in Microsoft Authenticator</h6>
                                    <p class="small text-muted mb-3">
                                        Open <strong>Microsoft Authenticator</strong> on your phone, tap <strong>+</strong> (Add account) &rarr; <strong>Other account (Google, Facebook, etc.)</strong>, and scan the QR code.
                                    </p>

                                    <h6 class="fw-bold text-dark mb-2">2. Verify Initial 6-Digit Code</h6>
                                    <form id="frmEnable2fa">
                                        @csrf
                                        <div class="mb-3">
                                            <input
                                                type="text"
                                                inputmode="numeric"
                                                maxlength="6"
                                                class="form-control form-control-lg text-center fw-bold font-monospace"
                                                style="letter-spacing: 0.35em;"
                                                id="setup_2fa_code"
                                                name="code"
                                                placeholder="000000"
                                                required
                                            >
                                            <div class="invalid-feedback" id="errSetup2faCode"></div>
                                        </div>

                                        <div class="d-flex gap-2 flex-wrap">
                                            <button type="submit" class="btn btn-primary fw-bold px-4" id="btnConfirmEnable2fa">
                                                <i class="bi bi-check-circle-fill me-1"></i> Verify &amp; Enable 2FA
                                            </button>
                                            <button type="button" class="btn btn-light border fw-semibold" id="btnCancel2faSetup">
                                                Cancel
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- Enabled State with Password-Confirmed Disable Form -->
                        <div class="alert alert-success border-success-subtle d-flex align-items-center gap-3 mb-4" role="alert">
                            <i class="bi bi-shield-lock-fill fs-3"></i>
                            <div>
                                <div class="fw-bold">Microsoft Authenticator is active on this account.</div>
                                <div class="small">Every login attempt will require a 6-digit verification code from your authenticator app.</div>
                            </div>
                        </div>

                        <form id="frmDisable2fa" class="border-top pt-3">
                            @csrf
                            <label for="disable_2fa_password" class="form-label fw-bold text-muted">
                                Disable Two-Factor Authentication
                            </label>
                            <p class="small text-muted mb-2">
                                Enter your current password below to turn off 2FA verification for your account.
                            </p>
                            <div class="row g-2 align-items-start">
                                <div class="col-md-7">
                                    <input
                                        type="password"
                                        class="form-control"
                                        id="disable_2fa_password"
                                        name="disable_2fa_password"
                                        placeholder="Enter current password"
                                        required
                                    >
                                    <div class="invalid-feedback" id="errDisable2faPassword"></div>
                                </div>
                                <div class="col-md-5 d-grid">
                                    <button type="submit" class="btn btn-outline-danger fw-bold" id="btnDisable2fa">
                                        <i class="bi bi-shield-x me-1"></i> Disable 2FA
                                    </button>
                                </div>
                            </div>
                        </form>
                    @endif
                </div>
            </div>

        </div>
    </div>

@push('scripts')
<script type="module">
    $(document).ready(function() {
        // Show / Hide Password Toggle
        $('.toggle-password').on('click', function() {
            const target = $($(this).data('target'));
            const icon = $(this).find('i');
            if (target.attr('type') === 'password') {
                target.attr('type', 'text');
                icon.removeClass('bi-eye').addClass('bi-eye-slash');
            } else {
                target.attr('type', 'password');
                icon.removeClass('bi-eye-slash').addClass('bi-eye');
            }
        });

        // 1. Update Account Email Info
        $('#frmUpdateProfileInfo').on('submit', function(e) {
            e.preventDefault();
            const btn = $('#btnSaveInfo');
            $('#profileUsername').removeClass('is-invalid');
            btn.prop('disabled', true).text('Saving...');

            $.ajax({
                url: '{{ route("profile.updateInfo") }}',
                method: 'PUT',
                data: $(this).serialize(),
                success: function(res) {
                    btn.prop('disabled', false).html('<i class="bi bi-check-circle-fill me-1"></i> Save Changes');
                    $('#profileDisplayEmail, #navbarUserEmail, #navbarUserEmailMobile').text(res.username);
                    $('#profileAvatarInitial').text(res.username.charAt(0).toUpperCase());

                    if (window.showToast) {
                        window.showToast(res.message, 'success');
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('<i class="bi bi-check-circle-fill me-1"></i> Save Changes');
                    const errors = xhr.responseJSON?.errors || {};
                    if (errors.Username) {
                        $('#profileUsername').addClass('is-invalid');
                        $('#errUsername').text(errors.Username[0]);
                    } else if (window.showToast) {
                        window.showToast('Failed to update profile.', 'error');
                    }
                }
            });
        });

        // 2. Update Password
        $('#frmUpdatePassword').on('submit', function(e) {
            e.preventDefault();
            const btn = $('#btnSavePassword');
            $('#current_password, #new_password').removeClass('is-invalid');
            btn.prop('disabled', true).text('Updating...');

            $.ajax({
                url: '{{ route("profile.updatePassword") }}',
                method: 'PUT',
                data: $(this).serialize(),
                success: function(res) {
                    btn.prop('disabled', false).html('<i class="bi bi-shield-lock-fill me-1"></i> Update Password');
                    $('#frmUpdatePassword')[0].reset();
                    if (window.showToast) {
                        window.showToast(res.message, 'success');
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('<i class="bi bi-shield-lock-fill me-1"></i> Update Password');
                    const errors = xhr.responseJSON?.errors || {};
                    if (errors.current_password) {
                        $('#current_password').addClass('is-invalid');
                        $('#errCurrentPassword').text(errors.current_password[0]);
                    }
                    if (errors.password) {
                        $('#new_password').addClass('is-invalid');
                        $('#errNewPassword').text(errors.password[0]);
                    }
                }
            });
        });

        // 3. Start 2FA Setup (Generate QR Code)
        $('#btnStart2faSetup').on('click', function() {
            const btn = $(this);
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Generating QR...');

            $.ajax({
                url: '{{ route("profile.2fa.setup") }}',
                method: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    btn.prop('disabled', false).html('<i class="bi bi-qr-code-scan me-2"></i> Enable Two-Factor Authentication');
                    $('#twoFactorQrBox').html(res.qr_svg);
                    $('#twoFactorManualSecret').text(res.secret);
                    $('#twoFactorIntro').addClass('d-none');
                    $('#twoFactorSetupContainer').removeClass('d-none');
                    $('#setup_2fa_code').val('').focus();
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('<i class="bi bi-qr-code-scan me-2"></i> Enable Two-Factor Authentication');
                    const msg = xhr.responseJSON?.message || 'Unable to generate 2FA QR code.';
                    if (window.showToast) {
                        window.showToast(msg, 'error');
                    }
                }
            });
        });

        $('#btnCancel2faSetup').on('click', function() {
            $('#twoFactorSetupContainer').addClass('d-none');
            $('#twoFactorIntro').removeClass('d-none');
            $('#setup_2fa_code').removeClass('is-invalid').val('');
        });

        $('#setup_2fa_code').on('input', function() {
            this.value = this.value.replace(/\D/g, '').slice(0, 6);
        });

        // 4. Verify Initial 6-Digit Code & Enable 2FA
        $('#frmEnable2fa').on('submit', function(e) {
            e.preventDefault();
            const btn = $('#btnConfirmEnable2fa');
            $('#setup_2fa_code').removeClass('is-invalid');
            btn.prop('disabled', true).text('Verifying...');

            $.ajax({
                url: '{{ route("profile.2fa.enable") }}',
                method: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    if (window.showToast) {
                        window.showToast(res.message, 'success');
                    }
                    setTimeout(() => window.location.reload(), 700);
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('<i class="bi bi-check-circle-fill me-1"></i> Verify &amp; Enable 2FA');
                    const errors = xhr.responseJSON?.errors || {};
                    const msg = errors.code ? errors.code[0] : (xhr.responseJSON?.message || 'Invalid verification code.');
                    $('#setup_2fa_code').addClass('is-invalid');
                    $('#errSetup2faCode').text(msg);
                }
            });
        });

        // 5. Disable 2FA
        $('#frmDisable2fa').on('submit', function(e) {
            e.preventDefault();
            const btn = $('#btnDisable2fa');
            $('#disable_2fa_password').removeClass('is-invalid');
            btn.prop('disabled', true).text('Disabling...');

            $.ajax({
                url: '{{ route("profile.2fa.disable") }}',
                method: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    if (window.showToast) {
                        window.showToast(res.message, 'success');
                    }
                    setTimeout(() => window.location.reload(), 700);
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('<i class="bi bi-shield-x me-1"></i> Disable 2FA');
                    const errors = xhr.responseJSON?.errors || {};
                    const msg = errors.disable_2fa_password
                        ? errors.disable_2fa_password[0]
                        : (xhr.responseJSON?.message || 'Could not disable 2FA.');
                    $('#disable_2fa_password').addClass('is-invalid');
                    $('#errDisable2faPassword').text(msg);
                }
            });
        });
    });
</script>
@endpush

</x-layout>