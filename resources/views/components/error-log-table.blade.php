@props(['errorLogs'])

<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-light text-muted" style="font-size: 0.85rem;">
            <tr>
                <th class="ps-4" style="width: 90px;">ID</th>
                <th style="width: 175px;">Timestamp</th>
                <th style="width: 120px;">Level</th>
                <th>Error Message &amp; Location</th>
                <th style="width: 160px;">User</th>
                <th class="text-end pe-4" style="width: 130px;">Trace</th>
            </tr>
        </thead>
        <tbody>
            @forelse($errorLogs as $err)
                @php
                    $levelBadge = $err->Level === 'HTTP Error'
                        ? 'bg-warning-subtle text-warning-emphasis border'
                        : 'bg-danger-subtle text-danger border';
                @endphp
                <tr>
                    <td class="ps-4 fw-bold text-muted">#{{ $err->ErrorLogID }}</td>
                    <td class="small text-muted">{{ $err->CreatedAt?->format('M d, Y h:i:s A') }}</td>
                    <td>
                        <span class="badge {{ $levelBadge }} px-2 py-1">{{ $err->Level }}</span>
                    </td>
                    <td>
                        <div class="fw-bold text-dark text-truncate" style="max-width: 420px;" title="{{ $err->Message }}">
                            {{ $err->Message }}
                        </div>
                        <div class="small text-muted font-monospace">
                            {{ class_basename($err->ExceptionClass ?? 'Exception') }}
                            @if($err->File)
                                &bull; {{ $err->File }}:{{ $err->Line }}
                            @endif
                        </div>
                    </td>
                    <td class="small">{{ $err->Username ?? 'Guest' }}</td>
                    <td class="text-end pe-4">
                        <button type="button"
                                class="btn btn-sm btn-outline-dark btn-view-trace"
                                data-id="{{ $err->ErrorLogID }}"
                                data-level="{{ $err->Level }}"
                                data-exception="{{ $err->ExceptionClass }}"
                                data-message="{{ $err->Message }}"
                                data-location="{{ $err->File }}:{{ $err->Line }}"
                                data-url="{{ $err->Method }} {{ $err->Url }}"
                                data-user="{{ $err->Username ?? 'Guest' }} ({{ $err->IpAddress ?? 'N/A' }})"
                                data-date="{{ $err->CreatedAt?->format('M d, Y h:i:s A') }}"
                                data-trace="{{ $err->StackTrace }}">
                            <i class="bi bi-code-slash me-1"></i> Inspect
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-5">No system error logs recorded.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($errorLogs->hasPages())
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 px-4 py-3 border-top bg-white error-pagination-container">
        <div class="small text-muted fw-semibold">
            Showing {{ $errorLogs->firstItem() }} to {{ $errorLogs->lastItem() }} of {{ $errorLogs->total() }} errors
        </div>
        <div>
            {{ $errorLogs->links() }}
        </div>
    </div>
@endif