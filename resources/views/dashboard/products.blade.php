<x-layout>

    <div id="pageData" 
     data-categories='@json($categories)' 
     data-category-map='@json($categoryMap)' 
     class="d-none">
    </div>

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

    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-body p-4 bg-white rounded-4">
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label fw-bold text-muted">Search Product Name</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" id="txtSearch" class="form-control border-start-0 bg-light" placeholder="Type to search product name in real-time...">
                    </div>
                </div>

                <div class="col-md-5">
                    <label class="form-label fw-bold text-muted">Filter Category</label>
                    <select id="cboCategory" class="form-select">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-5">
                    <label class="form-label fw-bold text-muted">Filter Subcategory</label>
                    <select id="cboSubCategory" class="form-select" disabled>
                        <option value="">All Subcategories</option>
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" id="btnResetFilters" class="btn btn-light border fw-bold text-danger w-100">
                        <i class="bi bi-x-circle me-1"></i> Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
        
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 px-4 py-3 border-bottom bg-light">
            <h6 class="m-0 fw-bold text-muted mode-title" id="modeTitle">Products Management</h6>
            
            <div class="d-flex gap-2 flex-wrap justify-content-end mode-actions">
                <button class="btn fw-bold rounded-pill shadow-sm btn-activate-mode btn-stock-in-action" data-mode="Stock-In">
                    <i class="bi bi-box-arrow-in-down me-1"></i> Stock-in
                </button>
                <button class="btn fw-bold rounded-pill shadow-sm btn-activate-mode btn-stock-out-action" data-mode="Stock-Out">
                    <i class="bi bi-box-arrow-up me-1"></i> Stock-out
                </button>
                @if(Auth::check() && Auth::user()->canEditProducts())
                    <button class="btn btn-dark fw-bold rounded-pill shadow-sm btn-activate-mode" data-mode="Edit">
                        <i class="bi bi-pencil-square me-1"></i> Edit Products
                    </button>
                @endif
            </div>
        </div>

        <div class="card-body p-0" id="tableContainer">
            <x-search-result :products="$products" />
        </div>

        <div class="bg-dark text-white p-3 d-flex justify-content-between align-items-center flex-wrap gap-2 d-none" id="selectionFooter" style="position: sticky; bottom: 0; z-index: 1000;">
            <div class="selection-summary"><span class="fw-bold fs-5" id="selectedCount">0</span> items selected for <span id="currentModeLabel" class="text-info">Action</span></div>
            <div class="d-flex gap-2 flex-wrap justify-content-end selection-actions">
                <button class="btn btn-light fw-bold" id="btnCancelSelection">Cancel Mode</button>
                <button class="btn btn-info fw-bold text-white" id="btnConfirmSelection">Review & Process</button>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editModal" data-bs-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>Edit Selected Products</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="frmBulkEdit">
                    <div class="modal-body p-4">
                        <div class="table-responsive border rounded">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 80px;">ID</th>
                                        <th>Product Name</th>
                                        <th style="width: 220px;">Category</th>
                                        <th style="width: 220px;">SubCategory</th>
                                        <th style="width: 140px;">Price (₱)</th>
                                    </tr>
                                </thead>
                                <tbody id="editModalBody">
                                    </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-dark fw-bold" id="btnSaveBulkEdit">
                            <i class="bi bi-check-circle-fill me-1"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="stockModal" data-bs-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold" id="stockModalTitle">Process Transaction</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label class="form-label fw-bold">Log Description (Optional)</label>
                        <input type="text" class="form-control" id="stockLogDescription" placeholder="e.g., Weekly supplier delivery, Inventory correction...">
                    </div>

                    <h6 class="fw-bold text-muted mb-3">Selected Items</h6>
                    <div class="table-responsive border rounded">
                        <table class="table table-hover align-middle mb-0" id="stockModalTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Item Name</th>
                                    <th>Unit Price</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-end">Remove</th>
                                </tr>
                            </thead>
                            <tbody id="stockModalBody"></tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <button class="btn btn-sm btn-outline-secondary" id="stockModalPrevBtn" disabled>Previous</button>
                        <span class="text-muted small">Page <span id="stockModalPageCurrent">1</span> of <span id="stockModalPageTotal">1</span></span>
                        <button class="btn btn-sm btn-outline-secondary" id="stockModalNextBtn" disabled>Next</button>
                    </div>
                </div>
                <div class="modal-footer bg-light justify-content-end gap-2">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary fw-bold" id="btnSubmitStock">Submit Transaction</button>
                </div>
            </div>
        </div>
    </div>

    <script type="module">
        $(document).ready(function() {
            let currentMode = null;
            let selectedItems = {};
            let modalItems = [];
            let modalCurrentPage = 1;
            const modalItemsPerPage = 5;
            
            let categoriesList = $('#pageData').data('categories') || [];
            let categoryMap = $('#pageData').data('category-map') || {};

            function loadGridSubcategories(catElem) {
        let idx = catElem.data('index');
        let selectedCat = catElem.val();
        let subElem = $(`#grid_sub_${idx}`);
        let currentSub = subElem.attr('data-selected') || subElem.val();

        let subcategories = categoryMap[selectedCat] || [];
        let opts = '';

        subcategories.forEach(sub => {
        let sel = (sub === currentSub) ? 'selected' : '';
        opts += `<option value="${sub}" ${sel}>${sub}</option>`;
    });

    subElem.html(opts);
}

            function showAlert(title, message, type = 'success') {
                let alertBox = $('#customAlert');
                alertBox.removeClass('alert-success alert-danger d-none').addClass(type === 'success' ? 'alert-success' : 'alert-danger');
                $('#alertTitle').text(title);
                $('#alertMessage').text(message);
                $('#alertIcon').attr('class', type === 'success' ? 'bi bi-check-circle-fill fs-3 me-3' : 'bi bi-exclamation-triangle-fill fs-3 me-3');
                alertBox.addClass('show');
                setTimeout(() => alertBox.addClass('d-none'), 4000);
            }

            // AJAX Search & Cascading Filter
            let searchTimer;
            function performSearch(pageUrl = '/api/products/search') {
                $.ajax({
                    url: pageUrl,
                    data: { 
                        search: $('#txtSearch').val(), 
                        category: $('#cboCategory').val(), 
                        subcategory: $('#cboSubCategory').val() 
                    },
                    success: function(html) {
                        $('#tableContainer').html(html);
                        if (currentMode) reapplySelectionState();
                    }
                });
            }

            $('#txtSearch').on('keyup input', function() {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => performSearch(), 300);
            });

            function handleProductFilterChange() {
                if ($('#cboCategory').val() || $('#cboSubCategory').val()) {
                    performSearch();
                }
            }

            $('#cboCategory').change(function() {
                let cat = $(this).val();
                let cboSub = $('#cboSubCategory');

                if (!cat) {
                    cboSub.html('<option value="">All Subcategories</option>').prop('disabled', true);
                    handleProductFilterChange();
                    return;
                }

                cboSub.prop('disabled', false).html('<option value="">Loading...</option>');
                $.ajax({
                    url: '/api/subcategories',
                    data: { category: cat },
                    success: function(data) {
                        let options = '<option value="">All Subcategories</option>';
                        data.forEach(sub => options += `<option value="${sub}">${sub}</option>`);
                        cboSub.html(options);
                        handleProductFilterChange();
                    }
                });
            });

            $('#cboSubCategory').change(function() {
                handleProductFilterChange();
            });

            $('#btnResetFilters').click(function() {
                $('#txtSearch').val('');
                $('#cboCategory').val('');
                $('#cboSubCategory').html('<option value="">All Subcategories</option>').prop('disabled', true);
                performSearch();
            });

            $(document).on('click', '.pagination-container a', function(e) {
                e.preventDefault();
                performSearch($(this).attr('href'));
            });

            // Activate Mode Handler
            $('.btn-activate-mode').click(function() {
                currentMode = $(this).data('mode');
                activateSelectionMode();
            });

            $('#btnCancelSelection').click(deactivateSelectionMode);

            function activateSelectionMode() {
                $('.action-col').addClass('d-none');
                $('.selection-col').removeClass('d-none');
                $('.std-ps').removeClass('ps-4');
                $('#selectionFooter').removeClass('d-none');
                $('#modeTitle').text(`Mode Active: ${currentMode}`).addClass('text-info');
                $('#currentModeLabel').text(`${currentMode} Mode`);
                reapplySelectionState();
                updateSelectionCount();
            }

            function deactivateSelectionMode() {
                currentMode = null;
                selectedItems = {};
                modalItems = [];
                modalCurrentPage = 1;
                $('.product-checkbox').prop('checked', false);
                $('#selectAllProducts').prop('checked', false);
                $('.selection-col').addClass('d-none');
                $('.action-col').removeClass('d-none');
                $('.std-ps').addClass('ps-4');
                $('#selectionFooter').addClass('d-none');
                $('#modeTitle').text('Products Management').removeClass('text-info');
            }

            function reapplySelectionState() {
                $('.action-col').addClass('d-none');
                $('.selection-col').removeClass('d-none');
                $('.std-ps').removeClass('ps-4');
                $('.product-checkbox').each(function() {
                    if (selectedItems[$(this).val()]) $(this).prop('checked', true);
                });
            }

            // Select All Checkbox Handler
            $(document).on('change', '#selectAllProducts', function() {
                let isChecked = this.checked;
                $('.product-checkbox').each(function() {
                    $(this).prop('checked', isChecked).trigger('change');
                });
            });

            $(document).on('change', '.product-checkbox', function() {
                let tr = $(this).closest('tr');
                let id = $(this).val();

                if (this.checked) {
                    selectedItems[id] = {
                        id: id,
                        name: tr.data('name'),
                        category: tr.data('category'),
                        subcategory: tr.data('subcategory'),
                        price: tr.data('price'),
                        stocks: tr.data('stocks')
                    };
                } else {
                    delete selectedItems[id];
                }
                updateSelectionCount();
            });

            function updateSelectionCount() {
                $('#selectedCount').text(Object.keys(selectedItems).length);
            }

            // Confirm Mode Selection Button
            $('#btnConfirmSelection').click(function() {
                if (Object.keys(selectedItems).length === 0) {
                    return alert('Please select at least one product.');
                }

                if (currentMode === 'Edit') {
                    openEditModal();
                } else if (currentMode === 'Stock-In' || currentMode === 'Stock-Out') {
                    openStockModal();
                }
            });

            // Populate Grid Modal for Editing
            function openEditModal() {
                let html = '';
                Object.values(selectedItems).forEach((item, idx) => {
                    let categoryOptions = categoriesList.map(c => `<option value="${c}" ${c === item.category ? 'selected' : ''}>${c}</option>`).join('');

                    html += `
                        <tr data-row-id="${item.id}">
                            <td class="fw-bold text-muted">#${item.id}
                                <input type="hidden" name="products[${idx}][ProductID]" value="${item.id}">
                            </td>
                            <td>
                                <input type="text" name="products[${idx}][ProductName]" class="form-control form-control-sm fw-bold" value="${item.name}" required>
                            </td>
                            <td>
                                <select name="products[${idx}][Category]" class="form-select form-control-sm grid-cbo-category" data-index="${idx}" required>
                                    ${categoryOptions}
                                </select>
                            </td>
                            <td>
                                <select name="products[${idx}][SubCategory]" class="form-select form-control-sm grid-cbo-subcategory" id="grid_sub_${idx}" required>
                                    <option value="${item.subcategory}" selected>${item.subcategory}</option>
                                </select>
                            </td>
                            <td>
                                <input type="number" step="0.01" name="products[${idx}][Price]" class="form-control form-control-sm" value="${parseFloat(item.price).toFixed(2)}" required>
                            </td>
                        </tr>`;
                });

                $('#editModalBody').html(html);
                
                $('.grid-cbo-category').each(function() {
                    loadGridSubcategories($(this));
                });

                new bootstrap.Modal(document.getElementById('editModal')).show();
            }

            $(document).on('change', '.grid-cbo-category', function() {
                loadGridSubcategories($(this));
            });

            function loadGridSubcategories(catElem) {
                let idx = catElem.data('index');
                let catVal = catElem.val();
                let subElem = $(`#grid_sub_${idx}`);
                let currentSub = subElem.val();

                $.ajax({
                    url: '/api/subcategories',
                    data: { category: catVal },
                    success: function(data) {
                        let opts = '';
                        data.forEach(sub => {
                            let sel = (sub === currentSub) ? 'selected' : '';
                            opts += `<option value="${sub}" ${sel}>${sub}</option>`;
                        });
                        subElem.html(opts);
                    }
                });
            }

            $('#frmBulkEdit').submit(function(e) {
                e.preventDefault();
                let btn = $('#btnSaveBulkEdit').prop('disabled', true).text('Saving...');

                $.ajax({
                    url: '/dashboard/products/bulk-update',
                    method: 'POST',
                    data: $(this).serialize(),
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    success: function(res) {
                        btn.prop('disabled', false).html('<i class="bi bi-check-circle-fill me-1"></i> Save Changes');
                        bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
                        deactivateSelectionMode();
                        performSearch();
                        showAlert('Update Successful', res.message, 'success');
                    },
                    error: function() {
                        btn.prop('disabled', false).html('<i class="bi bi-check-circle-fill me-1"></i> Save Changes');
                        showAlert('Update Failed', 'An error occurred while saving product changes.', 'error');
                    }
                });
            });

            function sanitizeQuantity(rawValue, maxAllowed = null) {
                let parsed = parseInt(String(rawValue).replace(/[^0-9]/g, ''), 10);
                if (!Number.isInteger(parsed) || parsed < 1) parsed = 1;
                if (maxAllowed !== null && parsed > maxAllowed) parsed = maxAllowed;
                return parsed;
            }

            function renderStockModalTable() {
                let start = (modalCurrentPage - 1) * modalItemsPerPage;
                let end = start + modalItemsPerPage;
                let paginatedItems = modalItems.slice(start, end);

                let html = '';
                paginatedItems.forEach((item, index) => {
                    let globalIndex = start + index;
                    let maxQty = currentMode === 'Stock-Out' ? Number(item.stock || 0) : null;
                    let safeQty = sanitizeQuantity(item.quantity, maxQty);

                    html += `
                        <tr data-id="${item.id}">
                            <td class="fw-bold">${item.name}</td>
                            <td class="text-success">₱${parseFloat(item.price).toFixed(2)}</td>
                            <td style="width: 170px;">
                                <div class="input-group input-group-sm">
                                    <button class="btn btn-outline-secondary btn-qty-minus" data-idx="${globalIndex}">-</button>
                                    <input type="number" class="form-control text-center qty-input" data-idx="${globalIndex}" value="${safeQty}" min="1" max="${maxQty ?? ''}" inputmode="numeric">
                                    <button class="btn btn-outline-secondary btn-qty-plus" data-idx="${globalIndex}">+</button>
                                </div>
                            </td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-light text-danger btn-remove-item" data-idx="${globalIndex}">
                                    <i class="bi bi-trash3-fill"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                });

                if (modalItems.length === 0) {
                    html = `<tr><td colspan="4" class="text-center text-muted">No items remaining.</td></tr>`;
                    $('#btnSubmitStock').prop('disabled', true);
                } else {
                    $('#btnSubmitStock').prop('disabled', false);
                }

                $('#stockModalBody').html(html);

                let totalPages = Math.ceil(modalItems.length / modalItemsPerPage) || 1;
                $('#stockModalPageCurrent').text(modalCurrentPage);
                $('#stockModalPageTotal').text(totalPages);
                $('#stockModalPrevBtn').prop('disabled', modalCurrentPage === 1);
                $('#stockModalNextBtn').prop('disabled', modalCurrentPage === totalPages);
            }

            $('#stockModalBody').on('click', '.btn-qty-minus', function() {
                let idx = $(this).data('idx');
                if (modalItems[idx].quantity > 1) {
                    modalItems[idx].quantity--;
                    renderStockModalTable();
                }
            });

            $('#stockModalBody').on('click', '.btn-qty-plus', function() {
                let idx = $(this).data('idx');
                modalItems[idx].quantity++;
                renderStockModalTable();
            });

            $('#stockModalBody').on('change', '.qty-input', function() {
                let idx = $(this).data('idx');
                let maxAllowed = currentMode === 'Stock-Out' ? Number(modalItems[idx].stock || 0) : null;
                modalItems[idx].quantity = sanitizeQuantity($(this).val(), maxAllowed);
                renderStockModalTable();
            });

            $('#stockModalBody').on('click', '.btn-remove-item', function() {
                let idx = $(this).data('idx');
                let removedId = modalItems[idx].id;

                modalItems.splice(idx, 1);
                delete selectedItems[removedId];
                updateSelectionCount();

                $(`.product-checkbox[value='${removedId}']`).prop('checked', false);

                let totalPages = Math.ceil(modalItems.length / modalItemsPerPage) || 1;
                if (modalCurrentPage > totalPages) modalCurrentPage = totalPages;

                renderStockModalTable();
            });

            $('#stockModalPrevBtn').click(function() {
                if (modalCurrentPage > 1) {
                    modalCurrentPage--;
                    renderStockModalTable();
                }
            });

            $('#stockModalNextBtn').click(function() {
                let totalPages = Math.ceil(modalItems.length / modalItemsPerPage) || 1;
                if (modalCurrentPage < totalPages) {
                    modalCurrentPage++;
                    renderStockModalTable();
                }
            });

            function openStockModal() {
                $('#stockModalTitle').text(`Process Transaction: ${currentMode}`);
                $('#stockLogDescription').val('');

                modalItems = Object.values(selectedItems).map(item => ({
                    id: item.id,
                    name: item.name,
                    price: Number(item.price || 0),
                    stock: Number(item.stocks || 0),
                    quantity: 1
                }));

                modalCurrentPage = 1;
                renderStockModalTable();

                new bootstrap.Modal(document.getElementById('stockModal')).show();
            }

            $('#btnSubmitStock').click(function() {
                let items = [];
                let hasInvalidQty = false;

                modalItems = modalItems.map(item => {
                    let maxAllowed = currentMode === 'Stock-Out' ? Number(item.stock || 0) : null;
                    let safeQty = sanitizeQuantity(item.quantity, maxAllowed);
                    if (safeQty !== Number(item.quantity) || safeQty < 1) {
                        hasInvalidQty = true;
                    }
                    return { ...item, quantity: safeQty };
                });

                if (hasInvalidQty) {
                    showAlert('Invalid Quantity', currentMode === 'Stock-Out' ? 'Stock-out quantity cannot exceed current stock and must be a positive number.' : 'Quantity must be a positive number.', 'error');
                    renderStockModalTable();
                    return;
                }

                items = modalItems.map(item => ({
                    id: item.id,
                    quantity: item.quantity,
                    price: item.price
                }));

                $.ajax({
                    url: '/dashboard/process-stock',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        actionType: currentMode,
                        description: $('#stockLogDescription').val(),
                        items: items
                    },
                    success: function(res) {
                        bootstrap.Modal.getInstance(document.getElementById('stockModal')).hide();
                        deactivateSelectionMode();
                        performSearch();
                        showAlert('Transaction Successful', res.message || 'Stock processed successfully!', 'success');
                    },
                    error: function(xhr) {
                        showAlert('Transaction Failed', xhr.responseJSON?.message || 'Unable to process the stock transaction.', 'error');
                    }
                });
            });

            // Inline Quick Stock
            $(document).on('click', '.btn-quick-stock', function() {
                let id = $(this).data('id');
                let action = $(this).data('action');
                let tr = $(this).closest('tr');

                selectedItems = {};
                selectedItems[id] = {
                    id: id,
                    name: tr.data('name'),
                    category: tr.data('category'),
                    subcategory: tr.data('subcategory'),
                    price: tr.data('price'),
                    stocks: tr.data('stocks')
                };

                currentMode = action;
                openStockModal();
            });
        });
    </script>

</x-layout>