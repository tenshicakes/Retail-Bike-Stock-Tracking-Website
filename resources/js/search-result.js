$(document).ready(function () {
    let currentMode = null;
    let selectedItems = {};
    let modalItems = [];
    let modalCurrentPage = 1;
    const modalItemsPerPage = 5;

    let categoriesList = $("#pageData").data("categories") || [];
    let categoryMap = $("#pageData").data("category-map") || {};

    function loadGridSubcategories(catElem) {
        let idx = catElem.data("index");
        let selectedCat = catElem.val();
        let subElem = $(`#grid_sub_${idx}`);
        let currentSub = subElem.attr("data-selected") || subElem.val();

        let subcategories = categoryMap[selectedCat] || [];
        let opts = "";

        subcategories.forEach((sub) => {
            let sel = sub === currentSub ? "selected" : "";
            opts += `<option value="${sub}" ${sel}>${sub}</option>`;
        });

        subElem.html(opts);
    }

    function showAlert(title, message, type = "success") {
        let alertBox = $("#customAlert");
        alertBox
            .removeClass("alert-success alert-danger d-none")
            .addClass(type === "success" ? "alert-success" : "alert-danger");
        $("#alertTitle").text(title);
        $("#alertMessage").text(message);
        $("#alertIcon").attr(
            "class",
            type === "success"
                ? "bi bi-check-circle-fill fs-3 me-3"
                : "bi bi-exclamation-triangle-fill fs-3 me-3",
        );
        alertBox.addClass("show");
        setTimeout(() => alertBox.addClass("d-none"), 4000);
    }

    // AJAX Search & Cascading Filter
    let searchTimer;
    function performSearch(pageUrl = "/api/products/search") {
        $.ajax({
            url: pageUrl,
            data: {
                search: $("#txtSearch").val(),
                category: $("#cboCategory").val(),
                subcategory: $("#cboSubCategory").val(),
            },
            success: function (html) {
                $("#tableContainer").html(html);
                if (currentMode) reapplySelectionState();
            },
        });
    }

    $("#txtSearch").on("keyup input", function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => performSearch(), 300);
    });

    function handleProductFilterChange() {
        if ($("#cboCategory").val() || $("#cboSubCategory").val()) {
            performSearch();
        }
    }

    $("#cboCategory").change(function () {
        let cat = $(this).val();
        let cboSub = $("#cboSubCategory");

        if (!cat) {
            cboSub
                .html('<option value="">All Subcategories</option>')
                .prop("disabled", true);
            handleProductFilterChange();
            return;
        }

        cboSub
            .prop("disabled", false)
            .html('<option value="">Loading...</option>');
        $.ajax({
            url: "/api/subcategories",
            data: { category: cat },
            success: function (data) {
                let options = '<option value="">All Subcategories</option>';
                data.forEach(
                    (sub) =>
                        (options += `<option value="${sub}">${sub}</option>`),
                );
                cboSub.html(options);
                handleProductFilterChange();
            },
        });
    });

    $("#cboSubCategory").change(function () {
        handleProductFilterChange();
    });

    $("#btnResetFilters").click(function () {
        $("#txtSearch").val("");
        $("#cboCategory").val("");
        $("#cboSubCategory")
            .html('<option value="">All Subcategories</option>')
            .prop("disabled", true);
        performSearch();
    });

    $(document).on("click", ".pagination-container a", function (e) {
        e.preventDefault();
        performSearch($(this).attr("href"));
    });

    // Activate Mode Handler
    $(".btn-activate-mode").click(function () {
        currentMode = $(this).data("mode");
        activateSelectionMode();
    });

    $("#btnCancelSelection").click(deactivateSelectionMode);

    function activateSelectionMode() {
        $(".action-col").addClass("d-none");
        $(".selection-col").removeClass("d-none");
        $(".std-ps").removeClass("ps-4");
        $("#selectionFooter").removeClass("d-none");
        $("#modeTitle")
            .text(`Mode Active: ${currentMode}`)
            .addClass("text-info");
        $("#currentModeLabel").text(`${currentMode} Mode`);
        reapplySelectionState();
        updateSelectionCount();
    }

    function deactivateSelectionMode() {
        currentMode = null;
        selectedItems = {};
        modalItems = [];
        modalCurrentPage = 1;
        $(".product-checkbox").prop("checked", false);
        $("#selectAllProducts").prop("checked", false);
        $(".selection-col").addClass("d-none");
        $(".action-col").removeClass("d-none");
        $(".std-ps").addClass("ps-4");
        $("#selectionFooter").addClass("d-none");
        $("#modeTitle").text("Products Management").removeClass("text-info");
    }

    function reapplySelectionState() {
        $(".action-col").addClass("d-none");
        $(".selection-col").removeClass("d-none");
        $(".std-ps").removeClass("ps-4");
        $(".product-checkbox").each(function () {
            if (selectedItems[$(this).val()]) $(this).prop("checked", true);
        });
    }

    // Select All Checkbox Handler
    $(document).on("change", "#selectAllProducts", function () {
        let isChecked = this.checked;
        $(".product-checkbox").each(function () {
            $(this).prop("checked", isChecked).trigger("change");
        });
    });

    $(document).on("change", ".product-checkbox", function () {
        let tr = $(this).closest("tr");
        let id = $(this).val();

        if (this.checked) {
            selectedItems[id] = {
                id: id,
                name: tr.data("name"),
                category: tr.data("category"),
                subcategory: tr.data("subcategory"),
                price: tr.data("price"),
                stocks: tr.data("stocks"),
            };
        } else {
            delete selectedItems[id];
        }
        updateSelectionCount();
    });

    function updateSelectionCount() {
        $("#selectedCount").text(Object.keys(selectedItems).length);
    }

    // Confirm Mode Selection Button
    $("#btnConfirmSelection").click(function () {
        if (Object.keys(selectedItems).length === 0) {
            return alert("Please select at least one product.");
        }

        if (currentMode === "Edit") {
            openEditModal();
        } else if (currentMode === "Stock-In" || currentMode === "Stock-Out") {
            openStockModal();
        }
    });

    // Populate Grid Modal for Editing
    function openEditModal() {
        let html = "";
        Object.values(selectedItems).forEach((item, idx) => {
            let categoryOptions = categoriesList
                .map(
                    (c) =>
                        `<option value="${c}" ${c === item.category ? "selected" : ""}>${c}</option>`,
                )
                .join("");

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

        $("#editModalBody").html(html);

        $(".grid-cbo-category").each(function () {
            loadGridSubcategories($(this));
        });

        new bootstrap.Modal(document.getElementById("editModal")).show();
    }

    $(document).on("change", ".grid-cbo-category", function () {
        loadGridSubcategories($(this));
    });

    function loadGridSubcategories(catElem) {
        let idx = catElem.data("index");
        let catVal = catElem.val();
        let subElem = $(`#grid_sub_${idx}`);
        let currentSub = subElem.val();

        $.ajax({
            url: "/api/subcategories",
            data: { category: catVal },
            success: function (data) {
                let opts = "";
                data.forEach((sub) => {
                    let sel = sub === currentSub ? "selected" : "";
                    opts += `<option value="${sub}" ${sel}>${sub}</option>`;
                });
                subElem.html(opts);
            },
        });
    }

    $("#frmBulkEdit").submit(function (e) {
        e.preventDefault();
        let btn = $("#btnSaveBulkEdit")
            .prop("disabled", true)
            .text("Saving...");

        $.ajax({
            url: "/dashboard/products/bulk-update",
            method: "POST",
            data: $(this).serialize(),
            headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
            success: function (res) {
                btn.prop("disabled", false).html(
                    '<i class="bi bi-check-circle-fill me-1"></i> Save Changes',
                );
                bootstrap.Modal.getInstance(
                    document.getElementById("editModal"),
                ).hide();
                deactivateSelectionMode();
                performSearch();
                showAlert("Update Successful", res.message, "success");
            },
            error: function () {
                btn.prop("disabled", false).html(
                    '<i class="bi bi-check-circle-fill me-1"></i> Save Changes',
                );
                showAlert(
                    "Update Failed",
                    "An error occurred while saving product changes.",
                    "error",
                );
            },
        });
    });

    function sanitizeQuantity(rawValue, maxAllowed = null) {
        let parsed = parseInt(String(rawValue).replace(/[^0-9]/g, ""), 10);
        if (!Number.isInteger(parsed) || parsed < 1) parsed = 1;
        if (maxAllowed !== null && parsed > maxAllowed) parsed = maxAllowed;
        return parsed;
    }

    function renderStockModalTable() {
        let start = (modalCurrentPage - 1) * modalItemsPerPage;
        let end = start + modalItemsPerPage;
        let paginatedItems = modalItems.slice(start, end);

        let html = "";
        paginatedItems.forEach((item, index) => {
            let globalIndex = start + index;
            let maxQty =
                currentMode === "Stock-Out" ? Number(item.stock || 0) : null;
            let safeQty = sanitizeQuantity(item.quantity, maxQty);

            html += `
                        <tr data-id="${item.id}">
                            <td class="fw-bold">${item.name}</td>
                            <td class="text-success">₱${parseFloat(item.price).toFixed(2)}</td>
                            <td style="width: 170px;">
                                <div class="input-group input-group-sm">
                                    <button class="btn btn-outline-secondary btn-qty-minus" data-idx="${globalIndex}">-</button>
                                    <input type="number" class="form-control text-center qty-input" data-idx="${globalIndex}" value="${safeQty}" min="1" max="${maxQty ?? ""}" inputmode="numeric">
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
            $("#btnSubmitStock").prop("disabled", true);
        } else {
            $("#btnSubmitStock").prop("disabled", false);
        }

        $("#stockModalBody").html(html);

        let totalPages = Math.ceil(modalItems.length / modalItemsPerPage) || 1;
        $("#stockModalPageCurrent").text(modalCurrentPage);
        $("#stockModalPageTotal").text(totalPages);
        $("#stockModalPrevBtn").prop("disabled", modalCurrentPage === 1);
        $("#stockModalNextBtn").prop(
            "disabled",
            modalCurrentPage === totalPages,
        );
    }

    $("#stockModalBody").on("click", ".btn-qty-minus", function () {
        let idx = $(this).data("idx");
        if (modalItems[idx].quantity > 1) {
            modalItems[idx].quantity--;
            renderStockModalTable();
        }
    });

    $("#stockModalBody").on("click", ".btn-qty-plus", function () {
        let idx = $(this).data("idx");
        modalItems[idx].quantity++;
        renderStockModalTable();
    });

    $("#stockModalBody").on("change", ".qty-input", function () {
        let idx = $(this).data("idx");
        let maxAllowed =
            currentMode === "Stock-Out"
                ? Number(modalItems[idx].stock || 0)
                : null;
        modalItems[idx].quantity = sanitizeQuantity($(this).val(), maxAllowed);
        renderStockModalTable();
    });

    $("#stockModalBody").on("click", ".btn-remove-item", function () {
        let idx = $(this).data("idx");
        let removedId = modalItems[idx].id;

        modalItems.splice(idx, 1);
        delete selectedItems[removedId];
        updateSelectionCount();

        $(`.product-checkbox[value='${removedId}']`).prop("checked", false);

        let totalPages = Math.ceil(modalItems.length / modalItemsPerPage) || 1;
        if (modalCurrentPage > totalPages) modalCurrentPage = totalPages;

        renderStockModalTable();
    });

    $("#stockModalPrevBtn").click(function () {
        if (modalCurrentPage > 1) {
            modalCurrentPage--;
            renderStockModalTable();
        }
    });

    $("#stockModalNextBtn").click(function () {
        let totalPages = Math.ceil(modalItems.length / modalItemsPerPage) || 1;
        if (modalCurrentPage < totalPages) {
            modalCurrentPage++;
            renderStockModalTable();
        }
    });

    function openStockModal() {
        $("#stockModalTitle").text(`Process Transaction: ${currentMode}`);
        $("#stockLogDescription").val("");

        modalItems = Object.values(selectedItems).map((item) => ({
            id: item.id,
            name: item.name,
            price: Number(item.price || 0),
            stock: Number(item.stocks || 0),
            quantity: 1,
        }));

        modalCurrentPage = 1;
        renderStockModalTable();

        new bootstrap.Modal(document.getElementById("stockModal")).show();
    }

    $("#btnSubmitStock").click(function () {
        let items = [];
        let hasInvalidQty = false;

        modalItems = modalItems.map((item) => {
            let maxAllowed =
                currentMode === "Stock-Out" ? Number(item.stock || 0) : null;
            let safeQty = sanitizeQuantity(item.quantity, maxAllowed);
            if (safeQty !== Number(item.quantity) || safeQty < 1) {
                hasInvalidQty = true;
            }
            return { ...item, quantity: safeQty };
        });

        if (hasInvalidQty) {
            showAlert(
                "Invalid Quantity",
                currentMode === "Stock-Out"
                    ? "Stock-out quantity cannot exceed current stock and must be a positive number."
                    : "Quantity must be a positive number.",
                "error",
            );
            renderStockModalTable();
            return;
        }

        items = modalItems.map((item) => ({
            id: item.id,
            quantity: item.quantity,
            price: item.price,
        }));

        $.ajax({
            url: "/dashboard/process-stock",
            method: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                actionType: currentMode,
                description: $("#stockLogDescription").val(),
                items: items,
            },
            success: function (res) {
                bootstrap.Modal.getInstance(
                    document.getElementById("stockModal"),
                ).hide();
                deactivateSelectionMode();
                performSearch();
                showAlert(
                    "Transaction Successful",
                    res.message || "Stock processed successfully!",
                    "success",
                );
            },
            error: function (xhr) {
                showAlert(
                    "Transaction Failed",
                    xhr.responseJSON?.message ||
                        "Unable to process the stock transaction.",
                    "error",
                );
            },
        });
    });

    // Inline Quick Stock
    $(document).on("click", ".btn-quick-stock", function () {
        let id = $(this).data("id");
        let action = $(this).data("action");
        let tr = $(this).closest("tr");

        selectedItems = {};
        selectedItems[id] = {
            id: id,
            name: tr.data("name"),
            category: tr.data("category"),
            subcategory: tr.data("subcategory"),
            price: tr.data("price"),
            stocks: tr.data("stocks"),
        };

        currentMode = action;
        openStockModal();
    });

    if ($("#frmAddProduct").length) {
        function populateAddProductSubcategories(selectedCategory = "") {
            let subSelect = $("#addProductSubCategory");
            let subcategories = categoryMap[selectedCategory] || [];

            if (!selectedCategory) {
                subSelect.html('<option value="">Select Subcategory</option>');
                subSelect.prop("disabled", true);
                return;
            }

            let options = '<option value="">Select Subcategory</option>';
            subcategories.forEach((sub) => {
                options += `<option value="${sub}">${sub}</option>`;
            });

            subSelect.html(options).prop("disabled", false);
        }

        $("#btnOpenAddProductModal").on("click", function () {
            $("#frmAddProduct")[0].reset();
            $("#addProductCategory").val("");
            populateAddProductSubcategories();
            new bootstrap.Modal(
                document.getElementById("addProductModal"),
            ).show();
        });

        $("#addProductCategory").on("change", function () {
            populateAddProductSubcategories($(this).val());
        });

        $("#frmAddProduct").on("submit", function (e) {
            e.preventDefault();

            let btn = $("#btnSubmitAddProduct")
                .prop("disabled", true)
                .html('<i class="bi bi-arrow-repeat me-1"></i> Adding...');

            $.ajax({
                url: "/dashboard/products",
                method: "POST",
                data: $(this).serialize(),
                headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                success: function (res) {
                    btn.prop("disabled", false).html(
                        '<i class="bi bi-check-circle-fill me-1"></i> Add Product',
                    );
                    bootstrap.Modal.getInstance(
                        document.getElementById("addProductModal"),
                    ).hide();
                    $("#frmAddProduct")[0].reset();
                    $("#addProductCategory").val("");
                    populateAddProductSubcategories();
                    performSearch();
                    showAlert(
                        "Product Added",
                        res.message || "Product created successfully.",
                        "success",
                    );
                },
                error: function (xhr) {
                    btn.prop("disabled", false).html(
                        '<i class="bi bi-check-circle-fill me-1"></i> Add Product',
                    );
                    let message =
                        xhr.responseJSON?.message ||
                        "Unable to create the product.";
                    let errors = xhr.responseJSON?.errors;
                    if (errors) {
                        message = Object.values(errors).flat().join(" ");
                    }
                    showAlert("Add Product Failed", message, "error");
                },
            });
        });
    }
});
