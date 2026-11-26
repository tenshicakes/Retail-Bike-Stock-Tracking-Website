<x-layout>

    <div id="accountsPageWrapper" data-current-user-id="{{ Auth::id() }}" data-csrf="{{ csrf_token() }}">

        <div id="customAlert" class="alert alert-success alert-dismissible fade show position-fixed top-0 end-0 m-4 shadow-lg d-none" style="z-index: 9999; max-width: 400px;" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill fs-3 me-3" id="alertIcon"></i>
                <div>
                    <strong id="alertTitle" class="d-block fs-6">Success!</strong>
                    <span id="alertMessage" class="small">Operation completed successfully.</span>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>

        <div class="modal fade" id="deleteConfirmModal" data-bs-backdrop="static" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title fw-bold text-danger"><i class="bi bi-trash3 me-2"></i>Delete Selected Accounts</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <p class="mb-0">Are you sure you want to delete <span id="deleteConfirmCount" class="fw-bold">0</span> selected account(s)? This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger fw-bold" id="btnConfirmDelete">Delete</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reset 2FA Confirmation Modal -->
        <div class="modal fade" id="reset2faConfirmModal" data-bs-backdrop="static" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title fw-bold text-warning-emphasis">
                            <i class="bi bi-shield-slash me-2"></i>Reset Two-Factor Authentication
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <p class="mb-2">
                            Are you sure you want to reset 2FA for <strong id="reset2faUsername"></strong>?
                        </p>
                        <p class="small text-muted mb-0">
                            Their Microsoft Authenticator link will be removed, and they will be able to log in with their password until they set up 2FA again.
                        </p>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-warning fw-bold" id="btnConfirmReset2fa">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset 2FA
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 rounded-4 mb-4">
            <div class="card-body p-4 bg-white rounded-4">
                <h1 class="pb-4 fw-bold">User Management</h1>
                <div class="row g-3 align-items-end">
                    <div class="col-12">
                        <label class="form-label fw-bold text-muted">Search Username</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                            <input type="text" id="txtSearch" class="form-control border-start-0 bg-light" placeholder="Type to search email/username in real-time..." value="{{ request('search') }}">
                            <button type="button" id="btnResetSearch" class="btn btn-light border fw-bold text-danger">
                                <i class="bi bi-x-circle me-1"></i> Clear
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 px-4 py-3 border-bottom bg-light">
                <h6 class="m-0 fw-bold text-muted mode-title" id="modeTitle">Accounts Management</h6>

                <div class="d-flex gap-2 flex-wrap justify-content-end mode-actions">
                    <button class="btn btn-danger fw-bold rounded-pill shadow-sm btn-activate-mode" data-mode="Delete">
                        <i class="bi bi-person-x me-1"></i> Delete Account
                    </button>
                    <button class="btn fw-bold rounded-pill shadow-sm btn-stock-in-action" id="btnAddAccount">
                        <i class="bi bi-person-plus me-1"></i> Add Account
                    </button>
                    <button class="btn btn-dark fw-bold rounded-pill shadow-sm btn-activate-mode" id="btnEditAccount" data-mode="Edit">
                        <i class="bi bi-pencil-square me-1"></i> Edit Account
                    </button>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="selection-col d-none" style="width: 60px;">
                                    <input type="checkbox" id="selectAllAccounts" class="form-check-input" title="Select all">
                                </th>
                                <th style="width: 110px;">User ID</th>
                                <th>Username (Email)</th>
                                <th style="width: 160px;">Role</th>
                                <th style="width: 140px;">2FA Status</th>
                                <th class="text-end pe-4" style="width: 160px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($accounts as $account)
                                @php
                                    $isCurrentUser = (int) $account->UserID === (int) Auth::id();
                                    $has2fa = $account->hasTwoFactorEnabled();
                                @endphp
                                <tr data-id="{{ $account->UserID }}" data-username="{{ $account->Username }}" data-role="{{ $account->Role }}">
                                    <td class="selection-col d-none">
                                        <input type="checkbox"
                                               class="form-check-input account-checkbox"
                                               value="{{ $account->UserID }}"
                                               data-is-self="{{ $isCurrentUser ? '1' : '0' }}">
                                    </td>
                                    <td class="fw-bold text-muted">#{{ $account->UserID }}</td>
                                    <td class="fw-semibold">
                                        {{ $account->Username }}
                                        @if($isCurrentUser)
                                            <span class="badge bg-primary-subtle text-primary border ms-1">You</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="role-cell">
                                            {{ $account->Role }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($has2fa)
                                            <span class="badge bg-success-subtle text-success border px-2 py-1">
                                                <i class="bi bi-shield-fill-check me-1"></i>Enabled
                                            </span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary border px-2 py-1">
                                                Disabled
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        @if($isCurrentUser)
                                            <span class="badge bg-light text-muted border px-2 py-1" title="Manage your own 2FA in Profile & Settings">
                                                <i class="bi bi-person-lock me-1"></i>Manage in Profile
                                            </span>
                                        @else
                                            <button type="button"
                                                    class="btn btn-sm {{ $has2fa ? 'btn-outline-warning text-dark fw-semibold' : 'btn-outline-secondary' }} btn-reset-2fa"
                                                    data-id="{{ $account->UserID }}"
                                                    data-username="{{ $account->Username }}"
                                                    {{ $has2fa ? '' : 'disabled' }}
                                                    title="{{ $has2fa ? 'Reset 2FA for this account' : '2FA is not enabled for this user' }}">
                                                <i class="bi bi-shield-slash me-1"></i> Reset 2FA
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No accounts found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-dark text-white p-3 d-flex justify-content-between align-items-center flex-wrap gap-2 d-none" id="selectionFooter" style="position: sticky; bottom: 0; z-index: 1000;">
                <div class="selection-summary">
                    <span class="fw-bold fs-5" id="selectedCount">0</span> <span id="selectionUnitLabel">accounts</span> selected for <span id="currentModeLabel" class="text-info">Action</span>
                </div>
                <div class="d-flex gap-2 flex-wrap justify-content-end selection-actions">
                    <button class="btn btn-light fw-bold" id="btnCancelSelection">Cancel Mode</button>
                    <button class="btn btn-info fw-bold text-white" id="btnConfirmSelection">Confirm</button>
                </div>
            </div>

            @if($accounts->hasPages())
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 px-4 py-3 border-top bg-white">
                    <div class="small text-muted fw-semibold">
                        Showing {{ $accounts->firstItem() }} to {{ $accounts->lastItem() }} of {{ $accounts->total() }} results
                    </div>
                    <div>
                        {{ $accounts->links() }}
                    </div>
                </div>
            @endif
        </div>

        <div class="modal fade" id="accountModal" data-bs-backdrop="static" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title fw-bold" id="accountModalTitle">Add Account</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <form id="frmAccount">
                        <div class="modal-body p-4">
                            <input type="hidden" id="accountId" name="accountId">

                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted">Email Address (Username)</label>
                                <input type="email" class="form-control" id="accountUsername" name="Username" placeholder="name@example.com" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted">Password</label>
                                <input type="password" class="form-control" id="accountPassword" name="Password" minlength="8" placeholder="Minimum 8 characters" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted">Confirm Password</label>
                                <input type="password" class="form-control" id="accountPasswordConfirmation" name="Password_confirmation" minlength="8" placeholder="Confirm password" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted">Role</label>
                                <select class="form-select" id="accountRole" name="Role" required>
                                    <option value="">Select Role</option>
                                    <option value="Administrator">Administrator</option>
                                    <option value="Owner">Owner</option>
                                    <option value="Staff">Staff</option>
                                    <option value="Mechanic">Mechanic</option>
                                </select>
                                <div class="form-text text-warning-emphasis fw-semibold d-none mt-1" id="selfRoleNotice">
                                    <i class="bi bi-lock-fill me-1"></i>Your own Administrator role is locked to prevent losing admin access.
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary fw-bold" id="btnSubmitAccount">
                                <i class="bi bi-check-circle-fill me-1"></i> Save Account
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

@push('scripts')
@vite('resources/js/accounts-page.js')
@endpush

</x-layout>