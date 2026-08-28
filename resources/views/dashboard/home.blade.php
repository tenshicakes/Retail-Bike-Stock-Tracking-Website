<x-layout>
    
    <div class="mb-4">
        <h1 class="fw-bolder text-dark display-5 mb-0" id="digitalClock" style="letter-spacing: -1px;">
            00:00:00 AM
        </h1>
        <h5 class="text-muted fw-semibold" id="currentDate">
            {{ now()->format('F j, Y l') }}
        </h5>
    </div>

    <div class="row g-4 mb-5">
        
        <div class="col-md-4">
            <div class="card home-stat-card border-0 h-100 rounded-4 p-3 bg-white">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 p-3 rounded-3 text-black me-3">
                        <i class="bi bi-bicycle fs-2"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 text-uppercase fw-bold" style="font-size: 0.8rem;">Products Listed</h6>
                        <h2 class="fw-bolder mb-0">{{ $totalProducts }}</h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card home-stat-card border-0 h-100 rounded-4 p-3 bg-white">
                <div class="d-flex align-items-center">
                    <div class="bg-warning bg-opacity-10 p-3 rounded-3 text-black me-3">
                        <i class="bi bi-exclamation-triangle-fill fs-2"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 text-uppercase fw-bold" style="font-size: 0.8rem;">Low Stocks</h6>
                        <h2 class="fw-bolder mb-0">{{ $lowStocks }}</h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card home-stat-card border-0 h-100 rounded-4 p-3 bg-white">
                <div class="d-flex align-items-center">
                    <div class="bg-danger bg-opacity-10 p-3 rounded-3 text-black me-3">
                        <i class="bi bi-x-octagon-fill fs-2"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 text-uppercase fw-bold" style="font-size: 0.8rem;">No Stocks</h6>
                        <h2 class="fw-bolder mb-0">{{ $noStocks }}</h2>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        
        <div class="card-header bg-white border-bottom-0 pt-4 pb-3 px-4 d-flex justify-content-between align-items-center">
            <h4 class="fw-bold m-0">All Products</h4>
        </div>

        <div class="card-body p-0">
            <x-product-table :products="$products" />
        </div>
    </div>


    @push('scripts')
    <script>
        function updateClock() {
            const now = new Date();
            let hours = now.getHours();
            let minutes = now.getMinutes();
            let seconds = now.getSeconds();
            const ampm = hours >= 12 ? 'PM' : 'AM';
            
            hours = hours % 12;
            hours = hours ? hours : 12; // the hour '0' should be '12'
            
            // Format numbers with leading zeros if needed
            hours = hours < 10 ? '0' + hours : hours;
            minutes = minutes < 10 ? '0' + minutes : minutes;
            seconds = seconds < 10 ? '0' + seconds : seconds;
            
            const timeString = `${hours}:${minutes}:${seconds} ${ampm}`;
            document.getElementById('digitalClock').textContent = timeString;
        }
        
        // Update the clock immediately, then every 1000ms (1 second)
        updateClock();
        setInterval(updateClock, 1000);
    </script>
    @endpush

</x-layout>