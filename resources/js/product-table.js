$(document).ready(function () {
    const homeSelectionKey = "bikeShop_home_selected";
    const homeActionKey = "bikeShop_home_action";

    // State Management
    let currentAction = sessionStorage.getItem(homeActionKey) || null;
    let selectedItems = JSON.parse(
        sessionStorage.getItem(homeSelectionKey) || "{}",
    );

    // Modal Pagination State
    let modalItems = [];
    let modalCurrentPage = 1;
    const modalItemsPerPage = 5;

    if (currentAction) {
        activateSelectionMode(currentAction);
        restoreCheckboxes();
    }

    // --- BUTTON TRIGGERS ---
    $("#btnBatchStockIn").click(() => activateSelectionMode("Stock-In"));
    $("#btnBatchStockOut").click(() => activateSelectionMode("Stock-Out"));
    $("#btnCancelSelection").click(() => deactivateSelectionMode());

    // Inline Single Item Shortcut
    $(document).on("click", ".inline-action", function () {
        let action = $(this).data("action");
        let tr = $(this).closest("tr");

        // Clear previous batch and select only this one
        selectedItems = {};
        selectedItems[tr.data("id")] = {
            id: tr.data("id"),
            name: tr.data("name"),
            price: tr.data("price"),
            stock: Number(tr.data("stocks") || 0),
        };

        currentAction = action;
        openModal();
    });

    // --- CHECKBOX LOGIC (Cross-Page Memory) ---
    $(".item-checkbox").change(function () {
        let tr = $(this).closest("tr");
        let id = tr.data("id");

        if (this.checked) {
            selectedItems[id] = {
                id: id,
                name: tr.data("name"),
                price: tr.data("price"),
                stock: Number(tr.data("stocks") || 0),
            };
        } else {
            delete selectedItems[id];
        }

        sessionStorage.setItem(homeSelectionKey, JSON.stringify(selectedItems));

        updateFooterCount();
    });

    // --- UI FUNCTIONS ---
    function activateSelectionMode(action) {
        currentAction = action;
        sessionStorage.setItem(homeActionKey, action);

        $("#selectionStatus")
            .text(`Selection Mode: ${action}`)
            .removeClass("text-muted")
            .addClass("text-" + (action === "Stock-In" ? "success" : "danger"));
        $(".action-col").addClass("d-none"); // Hide inline buttons
        $(".selection-col").removeClass("d-none"); // Show checkboxes
        $(".std-ps").removeClass("ps-4");
        $("#selectionFooter").removeClass("d-none");
        $("#currentActionDisplay").text(action);
        updateFooterCount();
    }

    function deactivateSelectionMode() {
        currentAction = null;
        selectedItems = {};
        sessionStorage.removeItem(homeActionKey);
        sessionStorage.removeItem(homeSelectionKey);

        $(".item-checkbox").prop("checked", false);
        $("#selectionStatus")
            .text("Standard Mode")
            .addClass("text-muted")
            .removeClass("text-success text-danger");
        $(".selection-col").addClass("d-none");
        $(".action-col").removeClass("d-none");
        $(".std-ps").addClass("ps-4");
        $("#selectionFooter").addClass("d-none");
    }

    function restoreCheckboxes() {
        $(".item-checkbox").each(function () {
            if (selectedItems[$(this).val()]) {
                $(this).prop("checked", true);
            }
        });
    }

    function updateFooterCount() {
        sessionStorage.setItem(homeSelectionKey, JSON.stringify(selectedItems));
        $("#selectedCountDisplay").text(Object.keys(selectedItems).length);
    }

    // --- MODAL LOGIC ---
    $("#btnConfirmSelection").click(function () {
        if (Object.keys(selectedItems).length === 0) {
            alert("Please select at least one item.");
            return;
        }
        openModal();
    });

    function openModal() {
        $("#modalTitle").text(`Process Transaction: ${currentAction}`);
        $("#logDescription").val("");

        // Convert object dictionary to array and add default quantity
        modalItems = Object.values(selectedItems).map((item) => ({
            ...item,
            quantity: 1,
        }));
        modalCurrentPage = 1;
        renderModalTable();

        let modal = new bootstrap.Modal(
            document.getElementById("transactionModal"),
        );
        modal.show();
    }

    // Render paginated items inside modal
    function sanitizeQuantity(rawValue, maxAllowed = null) {
        let parsed = parseInt(String(rawValue).replace(/[^0-9]/g, ""), 10);
        if (!Number.isInteger(parsed) || parsed < 1) parsed = 1;
        if (maxAllowed !== null && parsed > maxAllowed) parsed = maxAllowed;
        return parsed;
    }

    function renderModalTable() {
        let start = (modalCurrentPage - 1) * modalItemsPerPage;
        let end = start + modalItemsPerPage;
        let paginatedItems = modalItems.slice(start, end);

        let html = "";
        paginatedItems.forEach((item, index) => {
            let globalIndex = start + index;
            let maxQty =
                currentAction === "Stock-Out" ? Number(item.stock || 0) : null;
            let safeQty = sanitizeQuantity(item.quantity, maxQty);
            html += `
                    <tr>
                        <td class="fw-bold">${item.name}</td>
                        <td class="text-success">₱${parseFloat(item.price).toFixed(2)}</td>
                        <td style="width: 150px;">
                            <div class="input-group input-group-sm">
                                <button type="button" class="btn btn-outline-secondary btn-qty-minus" data-idx="${globalIndex}">-</button>
                                <input type="number" class="form-control text-center qty-input" data-idx="${globalIndex}" value="${safeQty}" min="1" max="${maxQty ?? ""}" inputmode="numeric">
                                <button type="button" class="btn btn-outline-secondary btn-qty-plus" data-idx="${globalIndex}">+</button>
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
            $("#btnSubmitTransaction").prop("disabled", true);
        } else {
            $("#btnSubmitTransaction").prop("disabled", false);
        }

        $("#modalTableBody").html(html);

        // Update Pagination UI
        let totalPages = Math.ceil(modalItems.length / modalItemsPerPage) || 1;
        $("#modalPageCurrent").text(modalCurrentPage);
        $("#modalPageTotal").text(totalPages);
        $("#modalPrevBtn").prop("disabled", modalCurrentPage === 1);
        $("#modalNextBtn").prop("disabled", modalCurrentPage === totalPages);
    }

    // Modal Table Actions (Event Delegation)
    $("#modalTableBody").on("click", ".btn-qty-minus", function () {
        let idx = $(this).data("idx");
        if (modalItems[idx].quantity > 1) {
            modalItems[idx].quantity--;
            renderModalTable();
        }
    });

    $("#modalTableBody").on("click", ".btn-qty-plus", function () {
        let idx = $(this).data("idx");
        modalItems[idx].quantity++;
        renderModalTable();
    });

    $("#modalTableBody").on("change", ".qty-input", function () {
        let idx = $(this).data("idx");
        let maxAllowed =
            currentAction === "Stock-Out"
                ? Number(modalItems[idx].stock || 0)
                : null;
        modalItems[idx].quantity = sanitizeQuantity($(this).val(), maxAllowed);
        renderModalTable();
    });

    $("#modalTableBody").on("click", ".btn-remove-item", function () {
        let idx = $(this).data("idx");
        let removedId = modalItems[idx].id;

        // Remove from modal array
        modalItems.splice(idx, 1);

        // Remove from current selection state
        delete selectedItems[removedId];
        updateFooterCount();

        // Uncheck box in main UI if visible
        $(`.item-checkbox[value='${removedId}']`).prop("checked", false);

        // Adjust pagination if we deleted the last item on a page
        let totalPages = Math.ceil(modalItems.length / modalItemsPerPage) || 1;
        if (modalCurrentPage > totalPages) modalCurrentPage = totalPages;

        renderModalTable();
    });

    // Modal Pagination Buttons
    $("#modalPrevBtn").click(() => {
        modalCurrentPage--;
        renderModalTable();
    });
    $("#modalNextBtn").click(() => {
        modalCurrentPage++;
        renderModalTable();
    });

    // --- SUBMIT TO BACKEND VIA AJAX ---
    $("#btnSubmitTransaction").click(function () {
        let btn = $(this);
        let hasInvalidQty = false;

        modalItems = modalItems.map((item) => {
            let maxAllowed =
                currentAction === "Stock-Out" ? Number(item.stock || 0) : null;
            let safeQty = sanitizeQuantity(item.quantity, maxAllowed);
            if (safeQty !== Number(item.quantity) || safeQty < 1) {
                hasInvalidQty = true;
            }
            return { ...item, quantity: safeQty };
        });

        if (hasInvalidQty) {
            window.showToast(
                "Quantity must be a positive number" +
                    (currentAction === "Stock-Out"
                        ? " and cannot exceed current stock."
                        : "."),
                "error",
            );
            renderModalTable();
            btn.prop("disabled", false).text("Submit Transaction");
            return;
        }

        btn.prop("disabled", true).text("Processing...");

        $.ajax({
            url: "/dashboard/process-stock",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                actionType: currentAction,
                description: $("#logDescription").val(),
                items: modalItems,
            },
            success: function (response) {
                window.showToast(response.message, "success");
                deactivateSelectionMode();

                // Delay the reload by 1.5 seconds so they can see the popup
                setTimeout(() => {
                    location.reload();
                }, 1500);
            },
            error: function (xhr) {
                window.showToast("Error: " + xhr.responseJSON.message, "error");
                btn.prop("disabled", false).text("Submit Transaction");
            },
        });
    });
});
