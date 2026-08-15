@props(['logs'])

<div class="d-flex justify-content-between align-items-center px-4 py-3 border-bottom bg-light">
    <div>
        <h6 class="m-0 fw-bold text-muted" id="selectionStatus">Standard Mode</h6>
    </div>
    <div>
        <button class="btn btn-dark fw-bold rounded-pill shadow-sm" id="btnBatchExport">
            <i class="bi bi-file-earmark-pdf-fill me-1"></i> Batch Export PDF
        </button>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-hover table-borderless align-middle mb-0">
        <thead class="table-light text-muted" style="font-size: 0.85rem;">
            <tr>
                <th class="ps-4 selection-col d-none">SELECT</th>
                <th class="std-ps">LOG ID</th>
                <th>DATE</th>
                <th>ACTION</th>
                <th>PRODUCT</th>
                <th>QTY</th>
                <th>TOTAL PRICE</th>
                <th class="pe-4 text-end action-col">EXPORT</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
                <tr data-id="{{ $log->LogID }}" data-name="{{ $log->product->ProductName }}" data-action="{{ $log->ActionType }}" data-qty="{{ $log->Quantity }}" data-total="{{ $log->TotalPrice }}">
                    
                    <td class="ps-4 selection-col d-none">
                        <input class="form-check-input log-checkbox fs-5" type="checkbox" value="{{ $log->LogID }}">
                    </td>
                    
                    <td class="ps-4 fw-bold text-muted std-ps">#{{ $log->LogID }}</td>
                    <td>{{ \Carbon\Carbon::parse($log->LogDate)->format('M d, Y h:i A') }}</td>
                    <td>
                        <span class="badge {{ $log->ActionType == 'Stock-In' ? 'bg-success' : 'bg-danger' }} bg-opacity-10 {{ $log->ActionType == 'Stock-In' ? 'text-success' : 'text-danger' }} px-2 py-1 rounded">
                            {{ $log->ActionType }}
                        </span>
                    </td>
                    <td class="fw-bold">{{ $log->product->ProductName }}</td>
                    <td>{{ $log->Quantity }}</td>
                    <td class="fw-semibold">₱{{ number_format($log->TotalPrice, 2) }}</td>
                    
                    <td class="pe-4 text-end action-col">
                        <form method="POST" action="/dashboard/logs/export" class="m-0">
                            @csrf
                            <input type="hidden" name="log_ids[]" value="{{ $log->LogID }}">
                            <button type="submit" class="btn btn-sm btn-outline-dark" title="Export this row">
                                <i class="bi bi-file-pdf"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center py-5">No logs found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="px-4 py-3 border-top bg-white">
    {{ $logs->links() }}
</div>

<div class="bg-dark text-white p-3 d-flex justify-content-between align-items-center d-none" id="selectionFooter" style="position: sticky; bottom: 0; z-index: 1000;">
    <div><span class="fw-bold fs-5" id="selectedCountDisplay">0</span> logs selected for PDF Export</div>
    <div class="d-flex gap-2">
        <button class="btn btn-light fw-bold" id="btnCancelSelection">Cancel</button>
        <button class="btn btn-info fw-bold text-white" id="btnConfirmSelection">Review & Generate</button>
    </div>
</div>

<div class="modal fade" id="exportModal" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold">Review Invoice Export</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <form method="POST" action="/dashboard/logs/export" id="exportForm">
                @csrf
                <div class="modal-body p-4">
                    <div class="table-responsive border rounded mb-3">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Action</th>
                                    <th>Product Name</th>
                                    <th>Qty</th>
                                    <th>Total</th>
                                    <th class="text-end">Remove</th>
                                </tr>
                            </thead>
                            <tbody id="exportModalBody">
                                </tbody>
                        </table>
                    </div>
                    <div id="exportHiddenInputs"></div> </div>
                <div class="modal-header bg-light justify-content-end gap-2">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-dark fw-bold" id="btnSubmitExport"><i class="bi bi-printer-fill me-1"></i> Generate PDF</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="module">
    $(document).ready(function() {
        let selectedLogs = JSON.parse(sessionStorage.getItem('bikeShop_exportLogs') || '{}');

        function restoreCheckboxes() {
            $('.log-checkbox').each(function() {
                $(this).prop('checked', !!selectedLogs[$(this).val()]);
            });
        }

        function updateFooter() {
            $('#selectedCountDisplay').text(Object.keys(selectedLogs).length);
        }

        function activateSelectionMode() {
            $('.action-col').addClass('d-none');
            $('.selection-col').removeClass('d-none');
            $('.std-ps').removeClass('ps-4');
            $('#selectionFooter').removeClass('d-none');
            $('#selectionStatus').text('Selection Mode').addClass('text-info');
            restoreCheckboxes();
            updateFooter();
        }

        function deactivateSelectionMode() {
            selectedLogs = {};
            sessionStorage.removeItem('bikeShop_exportLogs');
            $('.log-checkbox').prop('checked', false);
            $('.selection-col').addClass('d-none');
            $('.action-col').removeClass('d-none');
            $('.std-ps').addClass('ps-4');
            $('#selectionFooter').addClass('d-none');
            $('#selectionStatus').text('Standard Mode').removeClass('text-info');
        }

        if (Object.keys(selectedLogs).length > 0) {
            activateSelectionMode();
        }

        $('#btnBatchExport').click(activateSelectionMode);
        $('#btnCancelSelection').click(deactivateSelectionMode);

        $('.log-checkbox').change(function() {
            let tr = $(this).closest('tr');
            let id = $(this).val();

            if (this.checked) {
                selectedLogs[id] = {
                    id: id,
                    action: tr.data('action'),
                    name: tr.data('name'),
                    qty: tr.data('qty'),
                    total: tr.data('total')
                };
            } else {
                delete selectedLogs[id];
            }

            sessionStorage.setItem('bikeShop_exportLogs', JSON.stringify(selectedLogs));
            updateFooter();
        });

        $('#btnConfirmSelection').click(function() {
            if (Object.keys(selectedLogs).length === 0) return alert('Select at least one log.');

            let html = '';
            let inputs = '';

            Object.values(selectedLogs).forEach(log => {
                html += `
                    <tr>
                        <td><span class="badge ${log.action === 'Stock-In' ? 'bg-success' : 'bg-danger'}">${log.action}</span></td>
                        <td class="fw-bold">${log.name}</td>
                        <td>${log.qty}</td>
                        <td>₱${parseFloat(log.total).toFixed(2)}</td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-light text-danger btn-remove-log" data-id="${log.id}"><i class="bi bi-trash3-fill"></i></button>
                        </td>
                    </tr>`;
                inputs += `<input type="hidden" name="log_ids[]" id="hidden_log_${log.id}" value="${log.id}">`;
            });

            $('#exportModalBody').html(html);
            $('#exportHiddenInputs').html(inputs);
            new bootstrap.Modal(document.getElementById('exportModal')).show();
        });

        $('#exportModalBody').on('click', '.btn-remove-log', function() {
            let id = $(this).data('id');
            delete selectedLogs[id];
            sessionStorage.setItem('bikeShop_exportLogs', JSON.stringify(selectedLogs));
            $(this).closest('tr').remove();
            $(`#hidden_log_${id}`).remove();
            $(`.log-checkbox[value='${id}']`).prop('checked', false);
            updateFooter();

            if (Object.keys(selectedLogs).length === 0) {
                bootstrap.Modal.getInstance(document.getElementById('exportModal')).hide();
            }
        });

        $('#exportForm').submit(function() {
            setTimeout(deactivateSelectionMode, 500);
            bootstrap.Modal.getInstance(document.getElementById('exportModal')).hide();
        });
    });
</script>