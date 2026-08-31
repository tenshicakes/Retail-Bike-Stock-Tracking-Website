 $(document).ready(function () {
            let selectedItems = {};
            let currentMode = null;

            function showAlert(title, message, type = 'success') {
                let alertBox = $('#customAlert');
                alertBox.removeClass('alert-success alert-danger d-none').addClass(type === 'success' ? 'alert-success' : 'alert-danger');
                $('#alertTitle').text(title);
                $('#alertMessage').text(message);
                $('#alertIcon').attr('class', type === 'success' ? 'bi bi-check-circle-fill fs-3 me-3' : 'bi bi-exclamation-triangle-fill fs-3 me-3');
                alertBox.addClass('show');
                setTimeout(() => alertBox.addClass('d-none'), 4000);
            }

            function updateSelectionCount() {
                $('#selectedCount').text(Object.keys(selectedItems).length);
            }

            function activateSelectionMode() {
                $('.selection-col').removeClass('d-none');
                $('#selectionFooter').removeClass('d-none');
                $('#modeTitle').text(`Mode Active: ${currentMode}`).addClass('text-info');
                $('#currentModeLabel').text(`${currentMode} Mode`);
                updateSelectionCount();
            }

            function deactivateSelectionMode() {
                currentMode = null;
                selectedItems = {};
                $('.account-checkbox').prop('checked', false);
                $('#selectAllAccounts').prop('checked', false);
                $('.selection-col').addClass('d-none');
                $('#selectionFooter').addClass('d-none');
                $('#modeTitle').text('Accounts Management').removeClass('text-info');
                updateSelectionCount();
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

            $('.btn-activate-mode').click(function () {
                currentMode = $(this).data('mode');
                activateSelectionMode();
            });

            $('#btnCancelSelection').click(deactivateSelectionMode);

            $('#selectAllAccounts').change(function () {
                let checked = this.checked;
                $('.account-checkbox').each(function () {
                    $(this).prop('checked', checked).trigger('change');
                });
            });

            $(document).on('change', '.account-checkbox', function () {
                let id = $(this).val();
                if (this.checked) {
                    selectedItems[id] = { id: id };
                } else {
                    delete selectedItems[id];
                }
                updateSelectionCount();
            });

            $('#btnEditAccount').click(function () {
                currentMode = 'Edit';
                activateSelectionMode();
            });

            $('#btnAddAccount').click(function () {
                $('#accountModalTitle').text('Add Account');
                $('#frmAccount').attr('data-mode', 'create');
                $('#frmAccount')[0].reset();
                $('#accountId').val('');
                $('#btnSubmitAccount').html('<i class="bi bi-check-circle-fill me-1"></i> Save Account');
                new bootstrap.Modal(document.getElementById('accountModal')).show();
            });

            $('#frmAccount').submit(function (e) {
                e.preventDefault();

                let mode = $(this).attr('data-mode') || 'create';
                let payload = {
                    _token: '{{ csrf_token() }}',
                    Username: $('#accountUsername').val(),
                    Password: $('#accountPassword').val(),
                    Password_confirmation: $('#accountPasswordConfirmation').val(),
                    Role: $('#accountRole').val()
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
                    let id = $('#accountId').val();
                    ajaxConfig.url = '/dashboard/accounts/' + id;
                    ajaxConfig.data = {
                        ...payload,
                        _method: 'PUT'
                    };
                }

                $.ajax(ajaxConfig);
            });

            $('#btnConfirmSelection').click(function () {
                if (Object.keys(selectedItems).length === 0) {
                    showAlert('Selection Required', 'Please select at least one account.', 'error');
                    return;
                }

                if (currentMode === 'Edit') {
                    if (Object.keys(selectedItems).length > 1) {
                        showAlert('Only One Account', 'Please select only one account to edit.', 'error');
                        return;
                    }

                    let id = Object.keys(selectedItems)[0];
                    let row = $(`tr[data-id='${id}']`);

                    $('#accountModalTitle').text('Edit Account');
                    $('#frmAccount').attr('data-mode', 'edit');
                    $('#accountId').val(id);
                    $('#accountUsername').val(row.data('username'));
                    $('#accountPassword').val('');
                    $('#accountPasswordConfirmation').val('');
                    $('#accountRole').val(row.data('role'));
                    $('#btnSubmitAccount').html('<i class="bi bi-check-circle-fill me-1"></i> Update Account');

                    bootstrap.Modal.getInstance(document.getElementById('accountModal'))?.hide();
                    new bootstrap.Modal(document.getElementById('accountModal')).show();
                    return;
                }

                if (currentMode === 'Delete') {
                    $('#deleteConfirmCount').text(Object.keys(selectedItems).length);
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
                        _token: '{{ csrf_token() }}',
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