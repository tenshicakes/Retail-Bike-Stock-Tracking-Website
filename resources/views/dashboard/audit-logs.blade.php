<x-layout>
    <div id="auditLogsWrapper" data-csrf="{{ csrf_token() }}">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <div>
                <h2 class="fw-bold text-dark m-0">System Audit Logs</h2>
                <small class="text-muted">Administrator-only security access history and runtime system error monitor</small>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card home-stat-card border-0 rounded-4 p-3 bg-white h-100">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 p-3 rounded-3 text-dark me-3">
                            <i class="bi bi-shield-lock fs-3"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1 text-uppercase fw-bold" style="font-size: 0.78rem;">Total Access Events</h6>
                            <h3 class="fw-bolder mb-0">{{ $totalAccessCount }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card home-stat-card border-0 rounded-4 p-3 bg-white h-100">
                    <div class="d-flex align-items-center">
                        <div class="bg-warning bg-opacity-10 p-3 rounded-3 text-dark me-3">
                            <i class="bi bi-exclamation-diamond fs-3"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1 text-uppercase fw-bold" style="font-size: 0.78rem;">Failed / Blocked Attempts</h6>
                            <h3 class="fw-bolder mb-0">{{ $blockedOrFailedCount }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card home-stat-card border-0 rounded-4 p-3 bg-white h-100">
                    <div class="d-flex align-items-center">
                        <div class="bg-danger bg-opacity-10 p-3 rounded-3 text-dark me-3">
                            <i class="bi bi-bug fs-3"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1 text-uppercase fw-bold" style="font-size: 0.78rem;">System Errors Logged</h6>
                            <h3 class="fw-bolder mb-0">{{ $totalErrorCount }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab Navigation -->
        <ul class="nav nav-pills gap-2 mb-3" id="auditTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold rounded-pill px-4" id="tab-access-btn" data-bs-toggle="pill" data-bs-target="#pane-access" type="button" role="tab">
                    <i class="bi bi-person-check me-1"></i> Access Logs
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold rounded-pill px-4" id="tab-error-btn" data-bs-toggle="pill" data-bs-target="#pane-error" type="button" role="tab">
                    <i class="bi bi-bug-fill me-1"></i> Error Logs
                </button>
            </li>
        </ul>

        <div class="tab-content">
            <!-- ==================== TAB 1: ACCESS LOGS ==================== -->
            <div class="tab-pane fade show active" id="pane-access" role="tabpanel">
                <div class="card shadow-sm border-0 rounded-4 mb-4">
                    <div class="card-body p-4 bg-white rounded-4">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-muted">Search Email, IP, or Details</label>
                                <input type="text" id="txtAccessSearch" class="form-control bg-light" placeholder="Type to filter access logs...">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold text-muted">Event Type</label>
                                <select id="cboAccessEvent" class="form-select">
                                    <option value="">All Events</option>
                                    @foreach($eventTypes as $ev)
                                        <option value="{{ $ev }}">{{ $ev }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold text-muted">Status</label>
                                <select id="cboAccessStatus" class="form-select">
                                    <option value="">All Statuses</option>
                                    <option value="Success">Success</option>
                                    <option value="Failed">Failed</option>
                                    <option value="Blocked">Blocked</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex gap-2">
                                <button type="button" id="btnResetAccess" class="btn btn-light border fw-bold text-danger w-100">
                                    <i class="bi bi-x-circle me-1"></i> Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 px-4 py-3 border-bottom bg-light">
                        <h6 class="m-0 fw-bold text-muted">Authentication &amp; Security Events</h6>
                        <div class="d-flex gap-2">
                            <button type="button" id="btnRefreshAccess" class="btn btn-sm btn-outline-dark fw-semibold rounded-pill px-3">
                                <i class="bi bi-arrow-clockwise me-1"></i> Refresh
                            </button>
                            <button type="button" id="btnClearAccess" class="btn btn-sm btn-outline-danger fw-semibold rounded-pill px-3">
                                <i class="bi bi-trash3 me-1"></i> Clear Access Logs
                            </button>
                        </div>
                    </div>

                    <div class="card-body p-0" id="accessLogTableContainer">
                        <x-access-log-table :accessLogs="$accessLogs" />
                    </div>
                </div>
            </div>

            <!-- ==================== TAB 2: ERROR LOGS ==================== -->
            <div class="tab-pane fade" id="pane-error" role="tabpanel">
                <div class="card shadow-sm border-0 rounded-4 mb-4">
                    <div class="card-body p-4 bg-white rounded-4">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-muted">Search Message, Exception, File, or URL</label>
                                <input type="text" id="txtErrorSearch" class="form-control bg-light" placeholder="Type to search system errors...">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-muted">Severity Level</label>
                                <select id="cboErrorLevel" class="form-select">
                                    <option value="">All Levels</option>
                                    <option value="Error">Error</option>
                                    <option value="HTTP Error">HTTP Error</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="button" id="btnResetError" class="btn btn-light border fw-bold text-danger w-100">
                                    <i class="bi bi-x-circle me-1"></i> Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 px-4 py-3 border-bottom bg-light">
                        <h6 class="m-0 fw-bold text-muted">Captured Application &amp; Server Errors</h6>
                        <div class="d-flex gap-2">
                            <button type="button" id="btnRefreshError" class="btn btn-sm btn-outline-dark fw-semibold rounded-pill px-3">
                                <i class="bi bi-arrow-clockwise me-1"></i> Refresh
                            </button>
                            <button type="button" id="btnClearError" class="btn btn-sm btn-outline-danger fw-semibold rounded-pill px-3">
                                <i class="bi bi-trash3 me-1"></i> Clear Error Logs
                            </button>
                        </div>
                    </div>

                    <div class="card-body p-0" id="errorLogTableContainer">
                        <x-error-log-table :errorLogs="$errorLogs" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Stack Trace Modal -->
        <div class="modal fade" id="errorTraceModal" tabindex="-1">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-bug-fill text-danger me-2"></i>Error #<span id="traceModalId"></span> Details
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-3 mb-3 small">
                            <div class="col-md-6"><strong>Exception:</strong> <span id="traceModalException" class="font-monospace"></span></div>
                            <div class="col-md-6"><strong>Timestamp:</strong> <span id="traceModalDate"></span></div>
                            <div class="col-md-6"><strong>Location:</strong> <span id="traceModalLocation" class="font-monospace"></span></div>
                            <div class="col-md-6"><strong>User / IP:</strong> <span id="traceModalUser"></span></div>
                            <div class="col-12"><strong>Request:</strong> <span id="traceModalUrl" class="font-monospace"></span></div>
                        </div>

                        <div class="alert alert-danger fw-semibold small mb-3" id="traceModalMessage"></div>

                        <label class="form-label fw-bold text-muted small text-uppercase">Stack Trace</label>
                        <pre class="bg-dark text-light p-3 rounded-3 small mb-0" style="max-height: 360px; overflow-y: auto; white-space: pre-wrap;" id="traceModalStack"></pre>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

    </div>

@push('scripts')
@vite('resources/js/audit-logs-page.js')
@endpush
</x-layout>