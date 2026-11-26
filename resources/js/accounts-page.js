$(document).ready(function () {
    const wrapper = $('#accountsPageWrapper');
    const currentUserId = String(wrapper.data('current-user-id') || '');
    const csrfToken = wrapper.data('csrf') || $('meta[name="csrf-token"]').attr('content');

    let selectedItems = {};
    let currentMode = null;

    function showAlert(title, message, type = 'success') {
        let alertBox = $('#customAlert');
        alertBox
            .removeClass('alert-success alert-danger d-none')
            .addClass(type === 'success' ? 'alert-success' : 'alert-danger');
        $('#alertTitle').text(title);
        $('#alertMessage').text(message);
        $('#alertIcon').attr(
            'class',
            type === 'success'
                ? 'bi bi-check-circle-fill fs-3 me-3'
                : 'bi bi-exclamation-triangle-fill fs-3 me-3'
        );
        alertBox.addClass('show');
        setTimeout(() => alertBox.addClass('d-none'), 4000);
    }

    function updateSelectionCount() {
        const count = Object.keys(selectedItems).length;
        $('#selectedCount').text(count);
        $('#selectionUnitLabel').text(count === 1 ? 'account' : 'accounts');
    }

    function resetCheckboxes() {
        selectedItems = {};
        $('.account-checkbox').prop('checked', false).prop('disabled', false);
        $('#selectAllAccounts').prop('checked', false);
        updateSelectionCount();
    }

    function activateSelectionMode(mode) {
        currentMode = mode;
        resetCheckboxes();

        $('.selection-col').removeClass('d-none');
        $('#selectionFooter').removeClass('d-none');
        $('#modeTitle').text(`Mode Active: ${currentMode}`).addClass('text-info');

        if (currentMode === 'Edit') {
            // Single-select only: hide Select All checkbox in Edit mode
            $('#selectAllAccounts').addClass('d-none');
            $('#currentModeLabel').text('Edit Mode (Select 1 account)');
        } else if (currentMode === 'Delete') {
            // Multi-select allowed in Delete mode, but disable self-checkbox
            $('#selectAllAccounts').removeClass('d-none');
            $('.account-checkbox[data-is-self="1"]').prop('disabled', true);
            $('#currentModeLabel').text('Delete Mode');
        }
    }

    function deactivateSelectionMode() {
        currentMode = null;
        resetCheckboxes();
        $('#selectAllAccounts').removeClass('d-none');
        $('.selection-col').addClass('d-none');
        $('#selectionFooter').addClass('d-none');
        $('#modeTitle').text('Accounts Management').removeClass('text-info');
    }

    let searchTimer;

    function applySearchQuery() {
        const value = $('#txtSearch').val().trim();
        const params = new URLSearchParams(window.location.search);

        if (value) {
            params.set('search', value);
        } else {
            params.delete('search');
        }

        const queryString = params.toString();
        window.location.href = `${window.location.pathname}${queryString ? '?' + queryString : ''}`;
    }

    $('#txtSearch').on('keyup input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => applySearchQuery(), 300);
    });

    $('#btnResetSearch').click(function () {
        $('#txtSearch').val('');
        applySearchQuery();
    });

    // Unified mode activation (resets checkboxes cleanly when switching modes)
    $('.btn-activate-mode').click(function () {
        const mode = $(this).data('mode');
        activateSelectionMode(mode);
    });

    $('#btnCancelSelection').click(deactivateSelectionMode);

    $('#selectAllAccounts').change(function () {
        if (currentMode !== 'Delete') {
            this.checked = false;
            return;
        }

        let checked = this.checked;
        $('.account-checkbox:not(:disabled)').each(function () {
            $(this).prop('checked', checked).trigger('change');
        });
    });

    $(document).on('change', '.account-checkbox', function () {
        let id = String($(this).val());

        if (currentMode === 'Edit') {
            if (this.checked) {
                // Enforce single selection: uncheck all other boxes immediately
                $('.account-checkbox').not(this).prop('checked', false);
                selectedItems = { [id]: { id: id } };
            } else {
                selectedItems = {};
            }
            updateSelectionCount();
            return;
        }

        if (this.checked) {
            selectedItems[id] = { id: id };
        } else {
            delete selectedItems[id];
        }
        updateSelectionCount();
    });

    $('#btnAddAccount').click(function () {
        $('#accountModalTitle').text('Add Account');
        $('#frmAccount').attr('data-mode', 'create');
        $('#frmAccount')[0].reset();
        $('#accountId').val('');
        $('#accountPassword, #accountPasswordConfirmation').prop('required', true);
        $('#accountRole').prop('disabled', false);
        $('#selfRoleNotice').addClass('d-none');
        $('#btnSubmitAccount').html('<i class="bi bi-check-circle-fill me-1"></i> Save Account');
        new bootstrap.Modal(document.getElementById('accountModal')).show();
    });

    $('#frmAccount').submit(function (e) {
        e.preventDefault();

        let mode = $(this).attr('data-mode') || 'create';
        let id = String($('#accountId').val() || '');
        let selectedRole = $('#accountRole').val();

        // Guard against self-role downgrade on the client side as well
        if (mode === 'edit' && id === currentUserId) {
            selectedRole = 'Administrator';
        }

        let payload = {
            _token: csrfToken,
            Username: $('#accountUsername').val(),
            Password: $('#accountPassword').val(),
            Password_confirmation: $('#accountPasswordConfirmation').val(),
            Role: selectedRole
        };

        let url = '/dashboard/accounts';
        let ajaxConfig = {
            url: url,
            method: 'POST',
            data: payload,
            success: function (res) {
                bootstrap.Modal.getInstance(document.getElementById('accountModal')).hide();
                showAlert('Success', res.message || 'Account saved successfully.', 'success');
                setTimeout(() => window.location.reload(), 700);
            },
            error: function (xhr) {
                let message = xhr.responseJSON?.message || 'Please check the account details and try again.';
                let errors = xhr.responseJSON?.errors;
                if (errors) {
                    message = Object.values(errors).flat().join(' ');
                }
                showAlert('Failed', message, 'error');
            }
        };

        if (mode === 'edit') {
            ajaxConfig.url = '/dashboard/accounts/' + id;
            ajaxConfig.data = {
                ...payload,
                _method: 'PUT'
            };
        }

        $.ajax(ajaxConfig);
    });

    $('#btnConfirmSelection').click(function () {
        const selectedIds = Object.keys(selectedItems);

        if (selectedIds.length === 0) {
            showAlert('Selection Required', 'Please select at least one account.', 'error');
            return;
        }

        if (currentMode === 'Edit') {
            if (selectedIds.length > 1) {
                showAlert('Only One Account', 'Please select only one account to edit.', 'error');
                return;
            }

            let id = selectedIds[0];
            let row = $(`tr[data-id='${id}']`);
            let isEditingSelf = String(id) === currentUserId;

            $('#accountModalTitle').text(isEditingSelf ? 'Edit Your Account' : 'Edit Account');
            $('#frmAccount').attr('data-mode', 'edit');
            $('#accountId').val(id);
            $('#accountUsername').val(row.data('username'));
            $('#accountPassword').val('').prop('required', false);
            $('#accountPasswordConfirmation').val('').prop('required', false);
            $('#accountRole').val(row.data('role'));

            // Lock Role dropdown when editing your own Administrator account
            if (isEditingSelf) {
                $('#accountRole').prop('disabled', true);
                $('#selfRoleNotice').removeClass('d-none');
            } else {
                $('#accountRole').prop('disabled', false);
                $('#selfRoleNotice').addClass('d-none');
            }

            $('#btnSubmitAccount').html('<i class="bi bi-check-circle-fill me-1"></i> Update Account');

            bootstrap.Modal.getInstance(document.getElementById('accountModal'))?.hide();
            new bootstrap.Modal(document.getElementById('accountModal')).show();
            return;
        }

        if (currentMode === 'Delete') {
            $('#deleteConfirmCount').text(selectedIds.length);
            new bootstrap.Modal(document.getElementById('deleteConfirmModal')).show();
            return;
        }

        showAlert('Invalid Mode', 'This mode does not support confirmation yet.', 'error');
    });

    $('#btnConfirmDelete').click(function () {
        $.ajax({
            url: '/dashboard/accounts',
            method: 'POST',
            data: {
                _token: csrfToken,
                _method: 'DELETE',
                ids: Object.keys(selectedItems)
            },
            success: function (res) {
                bootstrap.Modal.getInstance(document.getElementById('deleteConfirmModal'))?.hide();
                showAlert('Deleted', res.message || 'Account deleted successfully.', 'success');
                deactivateSelectionMode();
                setTimeout(() => window.location.reload(), 700);
            },
            error: function (xhr) {
                let message = xhr.responseJSON?.message || 'Unable to delete the selected account(s).';
                showAlert('Delete Failed', message, 'error');
            }
        });
    });
});

// Per-Row Administrator 2FA Reset
    let accountIdToReset2fa = null;

    $(document).on('click', '.btn-reset-2fa', function () {
        const targetId = String($(this).data('id') || '');
        if (targetId === currentUserId) {
            showAlert('Action Not Allowed', 'Manage your own 2FA in Profile & Settings.', 'error');
            return;
        }

        accountIdToReset2fa = targetId;
        $('#reset2faUsername').text($(this).data('username'));
        new bootstrap.Modal(document.getElementById('reset2faConfirmModal')).show();
    });

    $('#btnConfirmReset2fa').on('click', function () {
        if (!accountIdToReset2fa) return;

        const btn = $(this);
        btn.prop('disabled', true).text('Resetting...');

        $.ajax({
            url: `/dashboard/accounts/${accountIdToReset2fa}/reset-2fa`,
            method: 'POST',
            data: { _token: csrfToken },
            success: function (res) {
                bootstrap.Modal.getInstance(document.getElementById('reset2faConfirmModal'))?.hide();
                btn.prop('disabled', false).html('<i class="bi bi-arrow-counterclockwise me-1"></i> Reset 2FA');
                showAlert('2FA Reset', res.message || 'Two-Factor Authentication has been reset.', 'success');
                setTimeout(() => window.location.reload(), 700);
            },
            error: function (xhr) {
                bootstrap.Modal.getInstance(document.getElementById('reset2faConfirmModal'))?.hide();
                btn.prop('disabled', false).html('<i class="bi bi-arrow-counterclockwise me-1"></i> Reset 2FA');
                const message = xhr.responseJSON?.message || 'Unable to reset 2FA for this account.';
                showAlert('Reset Failed', message, 'error');
            }
        });
    });