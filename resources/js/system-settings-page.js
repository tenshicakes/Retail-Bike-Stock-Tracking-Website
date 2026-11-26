$(document).ready(function () {
    const wrapper = $('#systemSettingsWrapper');
    const csrfToken = wrapper.data('csrf') || $('meta[name="csrf-token"]').attr('content');

    const nameInput = $('#systemNameInput');
    const logoInput = $('#systemLogoInput');

    // Real-time typing preview
    nameInput.on('input', function () {
        const val = $(this).val().trim() || "Alvin's Bike Repair Shop";
        $('#previewSidebarName').text(val);
        $('#previewLoginName').text(val);
    });

    // Real-time image file preview before upload
    logoInput.on('change', function () {
        const file = this.files && this.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function (e) {
            $('#previewSidebarLogo').attr('src', e.target.result);
            $('#previewLoginLogo').attr('src', e.target.result);
        };
        reader.readAsDataURL(file);
    });

    // AJAX Form Submit
    $('#frmSystemSettings').on('submit', function (e) {
        e.preventDefault();

        const btn = $('#btnSaveSystemSettings');
        btn.prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i> Saving...');

        const formData = new FormData(this);
        formData.append('_token', csrfToken);

        $.ajax({
            url: '/dashboard/system-settings',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (res) {
                // Instantly sync live sidebar, previews, and browser tab title without reload
                $('#sidebarBrandName').text(res.system_name);
                $('#sidebarBrandLogo').attr('src', res.logo_url);
                $('#previewSidebarName').text(res.system_name);
                $('#previewLoginName').text(res.system_name);
                $('#previewSidebarLogo').attr('src', res.logo_url);
                $('#previewLoginLogo').attr('src', res.logo_url);
                document.title = `Dashboard - ${res.system_name}`;

                logoInput.val('');

                if (res.is_custom_logo) {
                    $('#btnResetDefaultLogo').removeClass('d-none');
                }

                if (window.showToast) {
                    window.showToast(res.message, 'success');
                }
            },
            error: function (xhr) {
                let message = xhr.responseJSON?.message || 'Failed to update system settings.';
                const errors = xhr.responseJSON?.errors;
                if (errors) {
                    message = Object.values(errors).flat().join(' ');
                }
                if (window.showToast) {
                    window.showToast(message, 'error');
                }
            },
            complete: function () {
                btn.prop('disabled', false).html('<i class="bi bi-check-circle-fill me-1"></i> Save Changes');
            }
        });
    });

    // AJAX Reset to Default Logo
    $('#btnResetDefaultLogo').on('click', function () {
        if (!confirm('Reset the system logo back to the default JFM logo?')) return;

        const btn = $(this);
        btn.prop('disabled', true);

        $.ajax({
            url: '/dashboard/system-settings/reset-logo',
            method: 'POST',
            data: { _token: csrfToken },
            success: function (res) {
                $('#sidebarBrandLogo').attr('src', res.logo_url);
                $('#previewSidebarLogo').attr('src', res.logo_url);
                $('#previewLoginLogo').attr('src', res.logo_url);
                logoInput.val('');
                btn.addClass('d-none');

                if (window.showToast) {
                    window.showToast(res.message, 'success');
                }
            },
            error: function () {
                if (window.showToast) {
                    window.showToast('Failed to reset system logo.', 'error');
                }
            },
            complete: function () {
                btn.prop('disabled', false);
            }
        });
    });
});