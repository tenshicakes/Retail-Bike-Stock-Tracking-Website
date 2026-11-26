@props(['accounts'])

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
                <th class="text-end pe-4" style="width: 175px;">Actions</th>
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

@if($accounts->hasPages())
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 px-4 py-3 border-top bg-white accounts-pagination-container">
        <div class="small text-muted fw-semibold">
            Showing {{ $accounts->firstItem() }} to {{ $accounts->lastItem() }} of {{ $accounts->total() }} results
        </div>
        <div>
            {{ $accounts->links() }}
        </div>
    </div>
@endif