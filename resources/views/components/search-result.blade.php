@props(['products', 'showStockOut' => true])

<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th class="selection-col d-none text-center" style="width: 45px;">
                    <input type="checkbox" id="selectAllProducts" class="form-check-input">
                </th>
                <th style="width: 80px;">ID</th>
                <th>Product Name</th>
                <th>Category</th>
                <th>Sub-category</th>
                <th>Price</th>
                <th>Stocks</th>
                <th class="action-col text-end pe-4" style="width: 140px;">Quick Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $product)
                <tr data-id="{{ $product->ProductID }}" 
                    data-name="{{ $product->ProductName }}" 
                    data-category="{{ $product->Category }}" 
                    data-subcategory="{{ $product->SubCategory }}" 
                    data-price="{{ $product->Price }}" 
                    data-stocks="{{ $product->Stocks }}">
                    
                    <td class="selection-col d-none text-center">
                        <input type="checkbox" class="form-check-input product-checkbox" value="{{ $product->ProductID }}">
                    </td>

                    <td class="fw-bold text-muted">#{{ $product->ProductID }}</td>

                    <td class="fw-bold std-ps ps-4">{{ $product->ProductName }}</td>

                    <td>
                        <span class="badge bg-secondary-subtle text-dark border">{{ $product->Category ?? 'Uncategorized' }}</span>
                    </td>

                    <td>
                        <span class="badge bg-light text-secondary border">{{ $product->SubCategory ?? 'N/A' }}</span>
                    </td>

                    <td class="fw-semibold text-success">₱{{ number_format($product->Price, 2) }}</td>

                    <td>
                        <span class="badge {{ $product->Stocks > 5 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} border px-2 py-1">
                            {{ $product->Stocks }}
                        </span>
                    </td>

                    <td class="action-col text-end pe-4">
                        <div class="btn-group btn-group-sm" role="group" style="gap: 0.35rem;">
                            <button class="btn btn-outline-success btn-quick-stock" data-id="{{ $product->ProductID }}" data-action="Stock-In" title="Quick Stock In (+1)">
                                <i class="bi bi-plus-lg"></i>
                            </button>
                            @if($showStockOut)
                                <button class="btn btn-outline-danger btn-quick-stock" data-id="{{ $product->ProductID }}" data-action="Stock-Out" title="Quick Stock Out (-1)">
                                    <i class="bi bi-dash-lg"></i>
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center py-4 text-muted">
                        <i class="bi bi-box-seam fs-2 d-block mb-2"></i>
                        No products found matching your search criteria.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($products->hasPages())
    <div class="p-3 border-top pagination-container">
        {{ $products->links() }}
    </div>
@endif