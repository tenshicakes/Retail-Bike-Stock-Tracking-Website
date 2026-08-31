<x-layout>

    <div id="pageData"
     data-categories='@json($categories)'
     data-category-map='@json($categoryMap)'
     class="d-none">
    </div>

    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-body p-4 bg-white rounded-4">
            <div class="row g-3">
                <div class="col-12">
                    <h1 class="pb-4 fw-bold">No Stocks</h1>
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
            <h6 class="m-0 fw-bold text-muted mode-title" id="modeTitle">No Stock Products</h6>

            <div class="d-flex gap-2 flex-wrap justify-content-end mode-actions">
                <button class="btn fw-bold rounded-pill shadow-sm btn-activate-mode btn-stock-in-action" data-mode="Stock-In">
                    <i class="bi bi-box-arrow-in-down me-1"></i> Stock-in
                </button>
            </div>
        </div>

        <div class="card-body p-0" id="tableContainer">
            <x-search-result :products="$products" :show-stock-out="false" />
        </div>

        <div class="bg-dark text-white p-3 d-flex justify-content-between align-items-center flex-wrap gap-2 d-none" id="selectionFooter" style="position: sticky; bottom: 0; z-index: 1000;">
            <div class="selection-summary"><span class="fw-bold fs-5" id="selectedCount">0</span> items selected for <span id="currentModeLabel" class="text-info">Action</span></div>
            <div class="d-flex gap-2 flex-wrap justify-content-end selection-actions">
                <button class="btn btn-light fw-bold" id="btnCancelSelection">Cancel Mode</button>
                <button class="btn btn-info fw-bold text-white" id="btnConfirmSelection">Review & Process</button>
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

@push('scripts')
@vite('resources/js/nostock-page.js')
@endpush

</x-layout>
