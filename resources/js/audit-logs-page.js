$(document).ready(function () {
    const csrfToken =
        $('#auditLogsWrapper').data('csrf') ||
        $('meta[name="csrf-token"]').attr('content');

    // --- ACCESS LOGS AJAX ---
    let accessTimer;

    function fetchAccessLogs(pageUrl = '/dashboard/audit-logs') {
        const parsedUrl = new URL(pageUrl, window.location.origin);
        const params = new URLSearchParams();
        params.set('tab', 'access');

        const search = $('#txtAccessSearch').val().trim();
        const eventType = $('#cboAccessEvent').val();
        const status = $('#cboAccessStatus').val();
        const page = parsedUrl.searchParams.get('access_page');

        if (search) params.set('access_search', search);
        if (eventType) params.set('access_event', eventType);
        if (status) params.set('access_status', status);
        if (page) params.set('access_page', page);

        $.ajax({
            url: `/dashboard/audit-logs?${params.toString()}`,
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function (html) {
                $('#accessLogTableContainer').html(html);
            },
            error: function () {
                if (window.showToast) window.showToast('Failed to load access logs.', 'error');
            }
        });
    }

    $('#txtAccessSearch').on('keyup input', function () {
        clearTimeout(accessTimer);
        accessTimer = setTimeout(() => fetchAccessLogs(), 250);
    });

    $('#cboAccessEvent, #cboAccessStatus').on('change', function () {
        fetchAccessLogs();
    });

    $('#btnResetAccess').on('click', function () {
        $('#txtAccessSearch').val('');
        $('#cboAccessEvent').val('');
        $('#cboAccessStatus').val('');
        fetchAccessLogs();
    });

    $('#btnRefreshAccess').on('click', function () {
        fetchAccessLogs();
        if (window.showToast) window.showToast('Access logs refreshed.', 'success');
    });

    $(document).on('click', '.access-pagination-container a', function (e) {
        e.preventDefault();
        const href = $(this).attr('href');
        if (href) fetchAccessLogs(href);
    });

    $('#btnClearAccess').on('click', function () {
        if (!confirm('Clear all access log entries? This action cannot be undone.')) return;

        $.ajax({
            url: '/dashboard/audit-logs/access',
            method: 'POST',
            data: { _token: csrfToken, _method: 'DELETE' },
            success: function (res) {
                if (window.showToast) window.showToast(res.message, 'success');
                fetchAccessLogs();
            },
            error: function () {
                if (window.showToast) window.showToast('Failed to clear access logs.', 'error');
            }
        });
    });

    // --- ERROR LOGS AJAX ---
    let errorTimer;

    function fetchErrorLogs(pageUrl = '/dashboard/audit-logs') {
        const parsedUrl = new URL(pageUrl, window.location.origin);
        const params = new URLSearchParams();
        params.set('tab', 'error');

        const search = $('#txtErrorSearch').val().trim();
        const level = $('#cboErrorLevel').val();
        const page = parsedUrl.searchParams.get('error_page');

        if (search) params.set('error_search', search);
        if (level) params.set('error_level', level);
        if (page) params.set('error_page', page);

        $.ajax({
            url: `/dashboard/audit-logs?${params.toString()}`,
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function (html) {
                $('#errorLogTableContainer').html(html);
            },
            error: function () {
                if (window.showToast) window.showToast('Failed to load error logs.', 'error');
            }
        });
    }

    $('#txtErrorSearch').on('keyup input', function () {
        clearTimeout(errorTimer);
        errorTimer = setTimeout(() => fetchErrorLogs(), 250);
    });

    $('#cboErrorLevel').on('change', function () {
        fetchErrorLogs();
    });

    $('#btnResetError').on('click', function () {
        $('#txtErrorSearch').val('');
        $('#cboErrorLevel').val('');
        fetchErrorLogs();
    });

    $('#btnRefreshError').on('click', function () {
        fetchErrorLogs();
        if (window.showToast) window.showToast('Error logs refreshed.', 'success');
    });

    $(document).on('click', '.error-pagination-container a', function (e) {
        e.preventDefault();
        const href = $(this).attr('href');
        if (href) fetchErrorLogs(href);
    });

    $('#btnClearError').on('click', function () {
        if (!confirm('Clear all system error log entries? This action cannot be undone.')) return;

        $.ajax({
            url: '/dashboard/audit-logs/error',
            method: 'POST',
            data: { _token: csrfToken, _method: 'DELETE' },
            success: function (res) {
                if (window.showToast) window.showToast(res.message, 'success');
                fetchErrorLogs();
            },
            error: function () {
                if (window.showToast) window.showToast('Failed to clear error logs.', 'error');
            }
        });
    });

    // --- INSPECT ERROR STACK TRACE MODAL ---
    $(document).on('click', '.btn-view-trace', function () {
        const btn = $(this);
        $('#traceModalId').text(btn.data('id'));
        $('#traceModalException').text(btn.data('exception') || 'Exception');
        $('#traceModalDate').text(btn.data('date') || '');
        $('#traceModalLocation').text(btn.data('location') || 'N/A');
        $('#traceModalUser').text(btn.data('user') || 'Guest');
        $('#traceModalUrl').text(btn.data('url') || 'N/A');
        $('#traceModalMessage').text(btn.data('message') || '');
        $('#traceModalStack').text(btn.data('trace') || 'No stack trace captured.');

        new bootstrap.Modal(document.getElementById('errorTraceModal')).show();
    });
});