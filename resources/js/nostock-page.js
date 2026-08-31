 $(document).ready(function() {
            let currentMode = null;
            let selectedItems = {};
            let modalItems = [];
            let modalCurrentPage = 1;
            const modalItemsPerPage = 5;

            function sanitizeQuantity(rawValue) {
                let parsed = parseInt(String(rawValue).replace(/[^0-9]/g, ''), 10);
                if (!Number.isInteger(parsed) || parsed < 1) parsed = 1;
                return parsed;
            }

            function performSearch(pageUrl = '/api/nostock/search') {
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

            let searchTimer;
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
                $('#modeTitle').text('No Stock Products').removeClass('text-info');
            }

            function reapplySelectionState() {
                $('.action-col').addClass('d-none');
                $('.selection-col').removeClass('d-none');
                $('.std-ps').removeClass('ps-4');
                $('.product-checkbox').each(function() {
                    if (selectedItems[$(this).val()]) $(this).prop('checked', true);
                });
            }

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

            $('#btnConfirmSelection').click(function() {
                if (Object.keys(selectedItems).length === 0) {
                    return alert('Please select at least one product.');
                }

                if (currentMode === 'Stock-In') {
                    openStockModal();
                }
            });

            function renderStockModalTable() {
                let start = (modalCurrentPage - 1) * modalItemsPerPage;
                let end = start + modalItemsPerPage;
                let paginatedItems = modalItems.slice(start, end);

                let html = '';
                paginatedItems.forEach((item, index) => {
                    let globalIndex = start + index;
                    let safeQty = sanitizeQuantity(item.quantity);

                    html += `
                        <tr data-id="${item.id}">
                            <td class="fw-bold">${item.name}</td>
                            <td class="text-success">₱${parseFloat(item.price).toFixed(2)}</td>
                            <td style="width: 170px;">
                                <div class="input-group input-group-sm">
                                    <button class="btn btn-outline-secondary btn-qty-minus" data-idx="${globalIndex}">-</button>
                                    <input type="number" class="form-control text-center qty-input" data-idx="${globalIndex}" value="${safeQty}" min="1" inputmode="numeric">
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
                modalItems[idx].quantity = sanitizeQuantity($(this).val());
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
                    let safeQty = sanitizeQuantity(item.quantity);
                    if (safeQty !== Number(item.quantity) || safeQty < 1) {
                        hasInvalidQty = true;
                    }
                    return { ...item, quantity: safeQty };
                });

                if (hasInvalidQty) {
                    alert('Quantity must be a positive number.');
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
                        window.showToast ? window.showToast(res.message || 'Stock processed successfully!', 'success') : alert(res.message || 'Stock processed successfully!');
                    },
                    error: function(xhr) {
                        alert(xhr.responseJSON?.message || 'Unable to process the stock transaction.');
                    }
                });
            });

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