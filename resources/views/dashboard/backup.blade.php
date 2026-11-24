<x-layout>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteBackupModal" data-bs-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold text-danger"><i class="bi bi-trash3 me-2"></i>Delete Backup File</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="mb-0">Are you sure you want to permanently delete <strong id="deleteBackupFilename"></strong> from the server?</p>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger fw-bold" id="btnConfirmDeleteBackup">Delete File</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h1 class="fw-bold text-dark m-0">System Backup & Restore</h1>
            <p class="text-muted mb-0">Export full or selective <code>.sql</code> backups of <strong>{{ $databaseName }}</strong> and manage server snapshots.</p>
        </div>

        <button type="button" class="btn btn-dark btn-lg fw-bold rounded-pill shadow-sm px-4" id="btnFullBackup">
            <i class="bi bi-cloud-arrow-down-fill me-2"></i> Download Full SQL Backup
        </button>
    </div>

    <!-- 1. DATABASE SUMMARY CARDS -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 rounded-4 home-stat-card h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted fw-bold small text-uppercase">Active Database</span>
                        <i class="bi bi-database-check fs-4 text-primary"></i>
                    </div>
                    <h4 class="fw-bold text-dark mb-1">{{ $databaseName }}</h4>
                    <span class="text-muted small">{{ count($allTables) }} total tables detected</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 rounded-4 home-stat-card h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted fw-bold small text-uppercase">Core Records</span>
                        <i class="bi bi-table fs-4 text-info"></i>
                    </div>
                    <h4 class="fw-bold text-dark mb-1">{{ number_format($accountsCount + $productsCount + $logsCount) }}</h4>
                    <span class="text-muted small">{{ $accountsCount }} Accounts &bull; {{ $productsCount }} Products &bull; {{ $logsCount }} Logs</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 rounded-4 home-stat-card h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted fw-bold small text-uppercase">Saved Snapshots</span>
                        <i class="bi bi-archive fs-4 text-dark"></i>
                    </div>
                    <h4 class="fw-bold text-dark mb-1">{{ count($backups) }}</h4>
                    <span class="text-muted small">Stored in <code>storage/app/backups</code></span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 rounded-4 home-stat-card h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted fw-bold small text-uppercase">Last Backup</span>
                        <i class="bi bi-clock-history fs-4 text-secondary"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-1 mt-2">{{ $lastBackupTime }}</h6>
                    <span class="text-muted small">Administrator exclusive</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. SELECTIVE TABLE EXPORT & DATABASE RESTORE -->
    <div class="row g-4 mb-4">
        
        <!-- Selective Table Export -->
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-header bg-white border-bottom px-4 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="m-0 fw-bold text-dark"><i class="bi bi-list-check me-2 text-primary"></i>Selective Table Export</h5>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-secondary" id="btnSelectAllTables">Select All</button>
                        <button type="button" class="btn btn-outline-secondary" id="btnCoreTablesOnly">Core Only</button>
                    </div>
                </div>
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <p class="text-muted small mb-3">
                            Choose specific database tables to export into a partial <code>.sql</code> file (useful for exporting inventory and logs without account credentials).
                        </p>

                        <form id="frmSelectiveBackup">
                            @csrf
                            <div class="border rounded-3 p-3 bg-light mb-3" style="max-height: 230px; overflow-y: auto;">
                                @foreach($allTables as $table)
                                    @php
                                        $isCore = in_array($table, ['accounts', 'products', 'logs'], true);
                                    @endphp
                                    <div class="form-check d-flex justify-content-between align-items-center py-1 border-bottom border-light-subtle">
                                        <div>
                                            <input class="form-check-input table-export-checkbox {{ $isCore ? 'core-table' : '' }}"
                                                   type="checkbox"
                                                   name="tables[]"
                                                   value="{{ $table }}"
                                                   id="tbl_{{ $table }}"
                                                   {{ in_array($table, ['products', 'logs'], true) ? 'checked' : '' }}>
                                            <label class="form-check-label fw-semibold ms-1" for="tbl_{{ $table }}">
                                                {{ $table }}
                                            </label>
                                            @if($isCore)
                                                <span class="badge bg-primary-subtle text-primary border ms-1" style="font-size: 0.7rem;">Core</span>
                                            @endif
                                        </div>
                                        <span class="badge bg-white text-dark border">{{ number_format($tableStats[$table] ?? 0) }} rows</span>
                                    </div>
                                @endforeach
                            </div>
                        </form>
                    </div>

                    <div class="d-grid">
                        <button type="button" class="btn btn-primary fw-bold" id="btnSelectiveBackup">
                            <i class="bi bi-file-earmark-code me-1"></i> Export Selected Tables (.sql)
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Database Restore -->
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-header bg-white border-bottom px-4 py-3">
                    <h5 class="m-0 fw-bold text-dark"><i class="bi bi-arrow-counterclockwise me-2 text-danger"></i>Restore Database from SQL</h5>
                </div>
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="alert alert-warning border-warning-subtle small mb-3" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                            <strong>Caution:</strong> Restoring a <code>.sql</code> file will drop and overwrite existing tables included in that backup file. Download a fresh full backup before restoring.
                        </div>

                        <form id="frmRestoreDatabase" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label for="sql_file" class="form-label fw-bold text-muted">Select Backup File (<code>.sql</code>)</label>
                                <input type="file" class="form-control" id="sql_file" name="sql_file" accept=".sql,.txt" required>
                                <div class="invalid-feedback" id="errSqlFile"></div>
                            </div>

                            <div class="mb-3">
                                <label for="admin_password" class="form-label fw-bold text-muted">Confirm Administrator Password</label>
                                <input type="password" class="form-control" id="admin_password" name="admin_password" placeholder="Enter your password to authorize restore" required>
                                <div class="invalid-feedback" id="errAdminPassword"></div>
                            </div>
                        </form>
                    </div>

                    <div class="d-grid">
                        <button type="button" class="btn btn-danger fw-bold" id="btnRestoreDatabase">
                            <i class="bi bi-upload me-1"></i> Restore Database
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- 3. SAVED BACKUP HISTORY TABLE -->
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <div class="card-header bg-light border-bottom px-4 py-3 d-flex justify-content-between align-items-center">
            <h5 class="m-0 fw-bold text-dark"><i class="bi bi-clock-history me-2 text-primary"></i>Saved Server Backups</h5>
            <span class="badge bg-dark rounded-pill px-3 py-2">{{ count($backups) }} file(s)</span>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Backup Filename</th>
                            <th style="width: 150px;">Type</th>
                            <th style="width: 130px;">File Size</th>
                            <th style="width: 210px;">Created Date</th>
                            <th class="text-end pe-4" style="width: 200px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($backups as $backup)
                            <tr>
                                <td class="ps-4 fw-bold text-dark">
                                    <i class="bi bi-filetype-sql text-primary fs-5 me-2"></i>{{ $backup['filename'] }}
                                </td>
                                <td>
                                    <span class="badge {{ $backup['type'] === 'Full Backup' ? 'bg-primary-subtle text-primary border' : 'bg-info-subtle text-dark border' }}">
                                        {{ $backup['type'] }}
                                    </span>
                                </td>
                                <td class="fw-semibold text-muted">{{ $backup['size'] }}</td>
                                <td class="text-muted">{{ $backup['created_at'] }}</td>
                                <td class="text-end pe-4">
                                    <div class="d-inline-flex gap-2">
                                        <a href="{{ route('backup.download', ['filename' => $backup['filename']]) }}"
                                           class="btn btn-sm btn-outline-dark fw-semibold">
                                            <i class="bi bi-download me-1"></i> Download
                                        </a>
                                        <button type="button"
                                                class="btn btn-sm btn-outline-danger fw-semibold btn-delete-backup"
                                                data-filename="{{ $backup['filename'] }}">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-database-slash fs-2 d-block mb-2"></i>
                                    No backup files generated yet. Click <strong>Download Full SQL Backup</strong> above to create your first snapshot.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@push('scripts')
<script type="module">
    $(document).ready(function() {
        let filenameToDelete = null;
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteBackupModal'));

        // Helper to trigger backup creation and immediate browser download
        function runBackup(payload, buttonElem, originalHtml) {
            buttonElem.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Generating SQL...');

            $.ajax({
                url: '{{ route("backup.create") }}',
                method: 'POST',
                data: payload,
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                success: function(res) {
                    buttonElem.prop('disabled', false).html(originalHtml);

                    if (window.showToast) {
                        window.showToast(res.message, 'success');
                    }

                    // Trigger file download immediately, then reload to refresh the history list
                    window.location.href = res.download_url;
                    setTimeout(() => window.location.reload(), 1200);
                },
                error: function(xhr) {
                    buttonElem.prop('disabled', false).html(originalHtml);
                    const msg = xhr.responseJSON?.message || 'Failed to generate SQL backup.';
                    if (window.showToast) {
                        window.showToast(msg, 'error');
                    } else {
                        alert(msg);
                    }
                }
            });
        }

        // 1. One-Click Full SQL Backup
        $('#btnFullBackup').on('click', function() {
            runBackup(
                { mode: 'full' },
                $(this),
                '<i class="bi bi-cloud-arrow-down-fill me-2"></i> Download Full SQL Backup'
            );
        });

        // 2. Selective Table Checkboxes & Export
        $('#btnSelectAllTables').on('click', function() {
            $('.table-export-checkbox').prop('checked', true);
        });

        $('#btnCoreTablesOnly').on('click', function() {
            $('.table-export-checkbox').prop('checked', false);
            $('.table-export-checkbox.core-table').prop('checked', true);
        });

        $('#btnSelectiveBackup').on('click', function() {
            const selectedTables = $('.table-export-checkbox:checked').map(function() {
                return $(this).val();
            }).get();

            if (selectedTables.length === 0) {
                if (window.showToast) {
                    window.showToast('Please select at least one table to export.', 'error');
                } else {
                    alert('Please select at least one table to export.');
                }
                return;
            }

            runBackup(
                { mode: 'selective', tables: selectedTables },
                $(this),
                '<i class="bi bi-file-earmark-code me-1"></i> Export Selected Tables (.sql)'
            );
        });

        // 3. Delete Saved Backup File
        $(document).on('click', '.btn-delete-backup', function() {
            filenameToDelete = $(this).data('filename');
            $('#deleteBackupFilename').text(filenameToDelete);
            deleteModal.show();
        });

        $('#btnConfirmDeleteBackup').on('click', function() {
            if (!filenameToDelete) return;
            const btn = $(this).prop('disabled', true).text('Deleting...');

            $.ajax({
                url: `/dashboard/backup/${encodeURIComponent(filenameToDelete)}`,
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                success: function(res) {
                    deleteModal.hide();
                    btn.prop('disabled', false).text('Delete File');
                    if (window.showToast) {
                        window.showToast(res.message, 'success');
                    }
                    setTimeout(() => window.location.reload(), 600);
                },
                error: function() {
                    deleteModal.hide();
                    btn.prop('disabled', false).text('Delete File');
                    if (window.showToast) {
                        window.showToast('Could not delete backup file.', 'error');
                    }
                }
            });
        });

        // 4. Restore Database from Uploaded .SQL
        $('#btnRestoreDatabase').on('click', function() {
            const form = $('#frmRestoreDatabase')[0];
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            const btn = $(this);
            $('#sql_file, #admin_password').removeClass('is-invalid');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Restoring Database...');

            const formData = new FormData(form);

            $.ajax({
                url: '{{ route("backup.restore") }}',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    btn.prop('disabled', false).html('<i class="bi bi-upload me-1"></i> Restore Database');
                    form.reset();
                    if (window.showToast) {
                        window.showToast(res.message, 'success');
                    }
                    setTimeout(() => window.location.reload(), 1000);
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('<i class="bi bi-upload me-1"></i> Restore Database');
                    const errors = xhr.responseJSON?.errors || {};

                    if (errors.sql_file) {
                        $('#sql_file').addClass('is-invalid');
                        $('#errSqlFile').text(errors.sql_file[0]);
                    }
                    if (errors.admin_password) {
                        $('#admin_password').addClass('is-invalid');
                        $('#errAdminPassword').text(errors.admin_password[0]);
                    }
                    if (!errors.sql_file && !errors.admin_password) {
                        const msg = xhr.responseJSON?.message || 'Database restore failed.';
                        if (window.showToast) {
                            window.showToast(msg, 'error');
                        } else {
                            alert(msg);
                        }
                    }
                }
            });
        });
    });
</script>
@endpush

</x-layout>