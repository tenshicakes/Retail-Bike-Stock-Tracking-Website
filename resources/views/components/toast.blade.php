<div class="toast-container position-fixed bottom-0 end-0 p-4" style="z-index: 1100;">
    <div id="systemToast" class="toast align-items-center text-white border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body fw-bold d-flex align-items-center" style="font-size: 0.95rem;">
                <i id="toastIcon" class="bi bi-check-circle-fill fs-5 me-2"></i>
                <span id="toastMessage">Transaction Successful!</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-3 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<script>
    // Global function you can call from anywhere in your app
    window.showToast = function(message, type = 'success') {
        const toastEl = document.getElementById('systemToast');
        const toastMessage = document.getElementById('toastMessage');
        const toastIcon = document.getElementById('toastIcon');

        // Reset styling
        toastEl.classList.remove('bg-success', 'bg-danger', 'bg-warning', 'bg-info');
        toastIcon.className = 'fs-5 me-2'; // Reset icon classes

        // Apply specific styles based on type
        if (type === 'success') {
            toastEl.classList.add('bg-success');
            toastIcon.classList.add('bi', 'bi-check-circle-fill');
        } else if (type === 'error') {
            toastEl.classList.add('bg-danger');
            toastIcon.classList.add('bi', 'bi-exclamation-triangle-fill');
        } else {
            toastEl.classList.add('bg-info');
            toastIcon.classList.add('bi', 'bi-info-circle-fill');
        }

        // Set message and show
        toastMessage.textContent = message;
        const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
        toast.show();
    }
</script>