<x-layout>

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

    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-body p-4 bg-white rounded-4">
            <h1 class="pb-4 fw-bold">User Management</h1>
            <div class="row g-3 align-items-end">
                <div class="col-12">
                    <label class="form-label fw-bold text-muted">Search Username</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" id="txtSearch" class="form-control border-start-0 bg-light" placeholder="Type to search username in real-time..." value="{{ request('search') }}">
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
                                <input type="checkbox" id="selectAllAccounts" class="form-check-input">
                            </th>
                            <th style="width: 120px;">User ID</th>
                            <th>Username</th>
                            <th style="width: 220px;">Role</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($accounts as $account)
                            <tr data-id="{{ $account->UserID }}" data-username="{{ $account->Username }}" data-role="{{ $account->Role }}">
                                <td class="selection-col d-none">
                                    <input type="checkbox" class="form-check-input account-checkbox" value="{{ $account->UserID }}">
                                </td>
                                <td class="fw-bold text-muted">#{{ $account->UserID }}</td>
                                <td class="fw-semibold">{{ $account->Username }}</td>
                                <td>
                                    <span class="role-cell">
                                        {{ $account->Role }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No accounts found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-dark text-white p-3 d-flex justify-content-between align-items-center flex-wrap gap-2 d-none" id="selectionFooter" style="position: sticky; bottom: 0; z-index: 1000;">
            <div class="selection-summary"><span class="fw-bold fs-5" id="selectedCount">0</span> accounts selected for <span id="currentModeLabel" class="text-info">Action</span></div>
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
                            <label class="form-label fw-bold text-muted">Username</label>
                            <input type="text" class="form-control" id="accountUsername" name="Username" placeholder="Enter username" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-muted">Password</label>
                            <input type="password" class="form-control" id="accountPassword" name="Password" placeholder="Enter password" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-muted">Confirm Password</label>
                            <input type="password" class="form-control" id="accountPasswordConfirmation" name="Password_confirmation" placeholder="Confirm password" required>
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

@push('scripts')
@vite('resources/js/accounts-page.js')
@endpush

</x-layout>
