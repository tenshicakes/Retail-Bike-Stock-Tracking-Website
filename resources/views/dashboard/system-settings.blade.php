<x-layout>
    <div id="systemSettingsWrapper" data-csrf="{{ csrf_token() }}" data-default-logo="{{ asset('images/JFM.png') }}">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <div>
                <h2 class="fw-bold text-dark m-0">System Settings</h2>
                <small class="text-muted">Customize the system name and brand logo across the dashboard, login screen, and PDF invoices</small>
            </div>
        </div>

        <div class="row g-4">
            <!-- Left: Form Card -->
            <div class="col-12 col-lg-7">
                <div class="card shadow-sm border-0 rounded-4 h-100">
                    <div class="card-header bg-light border-bottom px-4 py-3">
                        <h6 class="m-0 fw-bold text-muted"><i class="bi bi-sliders me-2"></i>Branding &amp; Identity Configuration</h6>
                    </div>

                    <form id="frmSystemSettings" enctype="multipart/form-data">
                        <div class="card-body p-4">

                            <div class="mb-4">
                                <label for="systemNameInput" class="form-label fw-bold text-muted">System / Shop Name</label>
                                <input type="text"
                                       class="form-control form-control-lg bg-light"
                                       id="systemNameInput"
                                       name="system_name"
                                       maxlength="120"
                                       value="{{ $settings->system_name }}"
                                       placeholder="Enter system or shop name"
                                       required>
                                <div class="form-text">Displayed on the sidebar navigation, login &amp; registration screen, browser title, and PDF receipts.</div>
                            </div>

                            <div class="mb-4">
                                <label for="systemLogoInput" class="form-label fw-bold text-muted">System Logo</label>
                                <input type="file"
                                       class="form-control bg-light"
                                       id="systemLogoInput"
                                       name="logo"
                                       accept="image/png,image/jpeg,image/webp">
                                <div class="form-text">Accepted formats: PNG, JPG, JPEG, WEBP (Max 2 MB). Square images work best for the circular sidebar badge.</div>
                            </div>

                            <div class="d-flex flex-wrap gap-2 pt-2">
                                <button type="submit" class="btn btn-dark fw-bold rounded-pill px-4 shadow-sm" id="btnSaveSystemSettings">
                                    <i class="bi bi-check-circle-fill me-1"></i> Save Changes
                                </button>

                                <button type="button"
                                        class="btn btn-outline-danger fw-semibold rounded-pill px-4 {{ empty($settings->logo_path) ? 'd-none' : '' }}"
                                        id="btnResetDefaultLogo">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset to Default Logo
                                </button>
                            </div>

                        </div>
                    </form>
                </div>
            </div>

            <!-- Right: Live Sync Preview Card -->
            <div class="col-12 col-lg-5">
                <div class="card shadow-sm border-0 rounded-4 h-100">
                    <div class="card-header bg-light border-bottom px-4 py-3">
                        <h6 class="m-0 fw-bold text-muted"><i class="bi bi-eye me-2"></i>Live Branding Preview</h6>
                    </div>

                    <div class="card-body p-4 d-flex flex-column justify-content-center gap-4">
                        <!-- Sidebar Preview -->
                        <div>
                            <span class="badge bg-secondary-subtle text-secondary border mb-2">Sidebar Header Preview</span>
                            <div class="p-3 rounded-3 text-white d-flex align-items-center shadow-sm" style="background-color: rgb(12, 17, 44);">
                                <img src="{{ $settings->logoUrl() }}" alt="Sidebar Logo Preview" id="previewSidebarLogo" class="rounded-circle" style="width: 42px; height: 42px; object-fit: cover;">
                                <h6 class="mb-0 ms-3 fw-bold text-white" id="previewSidebarName">{{ $settings->system_name }}</h6>
                            </div>
                        </div>

                        <!-- Login Screen Preview -->
                        <div>
                            <span class="badge bg-secondary-subtle text-secondary border mb-2">Login / Register Screen Preview</span>
                            <div class="p-4 rounded-3 border bg-white text-center shadow-sm">
                                <img src="{{ $settings->logoUrl() }}" alt="Login Logo Preview" id="previewLoginLogo" class="mb-3 rounded-circle" style="width: 84px; height: 84px; object-fit: cover;">
                                <h5 class="fw-bold text-dark mb-1" id="previewLoginName">{{ $settings->system_name }}</h5>
                                <small class="text-muted">Retail Bike Stock Tracking System</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

@push('scripts')
@vite('resources/js/system-settings-page.js')
@endpush
</x-layout>