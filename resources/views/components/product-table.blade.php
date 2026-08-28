@props(['products'])

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 px-4 py-3 border-bottom bg-light">
    <div>
        <h6 class="m-0 fw-bold text-muted" id="selectionStatus">Standard Mode</h6>
    </div>
    <div class="d-flex gap-2 flex-wrap justify-content-end mode-actions">
        <button class="btn btn-success fw-bold  rounded-pill shadow-sm" id="btnBatchStockIn">
            <i class="bi bi-box-arrow-in-down me-1 "></i> Stock-in
        </button>
        <button class="btn btn-danger fw-bold rounded-pill shadow-sm" id="btnBatchStockOut">
            <i class="bi bi-box-arrow-up me-1"></i> Stock-out
        </button>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-hover align-middle mb-0" id="mainProductTable">
        <thead class="table-light">
            <tr>
                <th class="ps-4 selection-col d-none">Select</th>
                <th>ID</th>
                <th>Product Name</th>
                <th>Price</th>
                <th>Stocks</th>
                <th class="pe-4 text-end action-col" style="width: 140px;">Quick Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $product)
                <tr data-id="{{ $product->ProductID }}" data-name="{{ $product->ProductName }}" data-price="{{ $product->Price }}" data-stocks="{{ $product->Stocks }}">
                    <td class="ps-4 selection-col d-none">
                        <input class="form-check-input item-checkbox fs-5" type="checkbox" value="{{ $product->ProductID }}">
                    </td>
                    
                    <td class="ps-4 fw-bold text-muted std-ps">#{{ $product->ProductID }}</td>
                    <td>
                        <div class="d-flex align-items-center">
                            <span class="fw-bold">{{ $product->ProductName }}</span>
                        </div>
                    </td>
                    <td class="fw-semibold" style="color: var(--price-blue);">₱{{ number_format($product->Price, 2) }}</td>
                    <td>
                        @if($product->Stocks == 0)
                            <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill">0 - No Stock</span>
                        @else
                            <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">{{ $product->Stocks }} in stock</span>
                        @endif
                    </td>
                    
                    <td class="pe-4 text-end action-col">
                        <div class="d-inline-flex gap-1">
                            <button class="btn btn-sm btn-outline-success inline-action btn-stock-in-action" data-action="Stock-In" title="Stock In">
                                <i class="bi bi-plus-lg fw-bold"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger inline-action btn-stock-out-action" data-action="Stock-Out" title="Stock Out">
                                <i class="bi bi-dash-lg fw-bold"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center py-5">No products found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="px-4 py-3 border-top bg-white">
    {{ $products->links() }}
</div>

<div class="bg-dark text-white p-3 d-flex justify-content-between align-items-center d-none" id="selectionFooter" style="position: sticky; bottom: 0; z-index: 1000;">
    <div>
        <span class="fw-bold fs-5" id="selectedCountDisplay">0</span> items selected for <span id="currentActionDisplay" class="text-info">Action</span>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-light fw-bold" id="btnCancelSelection">Cancel</button>
        <button class="btn btn-info fw-bold text-white" id="btnConfirmSelection">Confirm & Proceed</button>
    </div>
</div>


<div class="modal fade" id="transactionModal" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold" id="modalTitle">Process Transaction</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                
                <div class="mb-4">
                    <label class="form-label fw-bold">Log Description (Optional)</label>
                    <input type="text" class="form-control" id="logDescription" placeholder="e.g., Weekly supplier delivery, Inventory correction...">
                </div>

                <h6 class="fw-bold text-muted mb-3">Selected Items</h6>
                <div class="table-responsive border rounded">
                    <table class="table table-hover align-middle mb-0" id="modalTable">
                        <thead class="table-light">
                            <tr>
                                <th>Item Name</th>
                                <th>Unit Price</th>
                                <th class="text-center">Quantity</th>
                                <th class="text-end">Remove</th>
                            </tr>
                     </thead>
                        <tbody id="modalTableBody">
                            </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <button class="btn btn-sm btn-outline-secondary" id="modalPrevBtn" disabled>Previous</button>
                    <span class="text-muted small">Page <span id="modalPageCurrent">1</span> of <span id="modalPageTotal">1</span></span>
                    <button class="btn btn-sm btn-outline-secondary" id="modalNextBtn" disabled>Next</button>
                </div>

            </div>
            <div class="modal-header bg-light justify-content-end gap-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary fw-bold" id="btnSubmitTransaction">Submit Transaction</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
@vite('resources/js/product-table.js')
@endpush
