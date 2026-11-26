@props(['accessLogs'])

<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-light text-muted" style="font-size: 0.85rem;">
            <tr>
                <th class="ps-4" style="width: 90px;">ID</th>
                <th style="width: 175px;">Timestamp</th>
                <th>User / Attempted Email</th>
                <th style="width: 175px;">Event Type</th>
                <th style="width: 115px;">Status</th>
                <th style="width: 140px;">IP Address</th>
                <th>Details</th>
            </tr>
        </thead>
        <tbody>
            @forelse($accessLogs as $log)
                @php
                    $statusBadge = match($log->Status) {
                        'Success' => 'bg-success-subtle text-success border',
                        'Blocked' => 'bg-dark text-white',
                        default => 'bg-danger-subtle text-danger border',
                    };
                @endphp
                <tr>
                    <td class="ps-4 fw-bold text-muted">#{{ $log->AccessLogID }}</td>
                    <td class="small text-muted">{{ $log->CreatedAt?->format('M d, Y h:i:s A') }}</td>
                    <td class="fw-semibold">
                        {{ $log->Username ?? 'Guest / Unknown' }}
                        @if($log->UserID)
                            <span class="text-muted small">(ID #{{ $log->UserID }})</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge bg-light text-dark border">{{ $log->EventType }}</span>
                    </td>
                    <td>
                        <span class="badge {{ $statusBadge }} px-2 py-1">{{ $log->Status }}</span>
                    </td>
                    <td class="font-monospace small text-muted">{{ $log->IpAddress ?? 'N/A' }}</td>
                    <td class="small text-muted">{{ $log->Details ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-5">No access logs found matching your criteria.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($accessLogs->hasPages())
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 px-4 py-3 border-top bg-white access-pagination-container">
        <div class="small text-muted fw-semibold">
            Showing {{ $accessLogs->firstItem() }} to {{ $accessLogs->lastItem() }} of {{ $accessLogs->total() }} entries
        </div>
        <div>
            {{ $accessLogs->links() }}
        </div>
    </div>
@endif