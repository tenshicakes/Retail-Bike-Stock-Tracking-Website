<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Retail Bike Stock Tracking System</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>
<body class="bg-light">
    
    <div class="d-flex min-vh-100">
        
        <aside class="sidebar d-flex flex-column p-3 text-white shadow-lg" id="sidebar">
            
            <div class="d-flex align-items-center justify-content-between mb-4 mt-2">
                <div class="d-flex align-items-center">
                    <img src="{{ asset('images/JFM.png') }}" alt="Logo" class="brand-logo rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                    <h5 class="mb-0 ms-3 fw-bold sidebar-text">Alvin's Bike Repair Shop</h5>
                </div>
                
                <button class="btn text-white fs-4 mobile-close-btn p-0 border-0" id="sidebarClose">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <ul class="nav flex-column gap-2 mb-auto">
                <li class="nav-item">
                    <a href="/dashboard/home" class="nav-link sidebar-link d-flex align-items-center">
                        <i class="bi bi-house-door fs-5"></i>
                        <span class="ms-3 sidebar-text">Home</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/dashboard/products" class="nav-link sidebar-link d-flex align-items-center">
                        <i class="bi bi-bicycle fs-5"></i>
                        <span class="ms-3 sidebar-text">Products</span>
                    </a>
                </li>
                @if(Auth::check() && Auth::user()->canAccessPage('lowstock'))
                    <li class="nav-item">
                        <a href="/dashboard/lowstock" class="nav-link sidebar-link d-flex align-items-center">
                            <i class="bi bi-exclamation-triangle fs-5 "></i>
                            <span class="ms-3 sidebar-text">Low Stock</span>
                        </a>
                    </li>
                @endif
                @if(Auth::check() && Auth::user()->canAccessPage('nostock'))
                    <li class="nav-item">
                        <a href="/dashboard/nostock" class="nav-link sidebar-link d-flex align-items-center">
                            <i class="bi bi-x-circle fs-5 "></i>
                            <span class="ms-3 sidebar-text">No Stock</span>
                        </a>
                    </li>
                @endif
                @if(Auth::check() && Auth::user()->canAccessPage('logs'))
                    <li class="nav-item">
                        <a href="/dashboard/logs" class="nav-link sidebar-link d-flex align-items-center">
                            <i class="bi bi-journal-text fs-5"></i>
                            <span class="ms-3 sidebar-text">Logs</span>
                        </a>
                    </li>
                @endif
                @if(Auth::check() && Auth::user()->canAccessPage('accounts'))
                    <li class="nav-item">
                        <a href="/dashboard/accounts" class="nav-link sidebar-link d-flex align-items-center">
                            <i class="bi bi-people fs-5"></i>
                            <span class="ms-3 sidebar-text">Accounts</span>
                        </a>
                    </li>
                @endif
            </ul>

            <hr class="text-white-50">
            <a href="/logout" class="nav-link sidebar-link d-flex align-items-center text-danger fw-bold">
                <i class="bi bi-box-arrow-left fs-5 text-white"></i>
                <span class="ms-3 sidebar-text text-white">Logout</span>
            </a>
            
        </aside>

        <main class="flex-grow-1 d-flex flex-column w-100 overflow-auto">
            
            <header class="bg-white shadow-sm p-3 d-flex align-items-center">
                <button class="btn btn-light border-0" id="sidebarToggle">
                    <i class="bi bi-list fs-4"></i>
                </button>
            </header>

            <div class="p-4">
                {{ $slot }}
            </div>
            
        </main>
    </div>

    <script type="module">
        $(document).ready(function() {
            const currentPath = window.location.pathname;
            if (currentPath !== '/dashboard/home') {
                sessionStorage.removeItem('bikeShop_home_selected');
                sessionStorage.removeItem('bikeShop_home_action');
            }

            if (currentPath !== '/dashboard/logs') {
                sessionStorage.removeItem('bikeShop_exportLogs');
            }

            // Open sidebar (or toggle on desktop)
            $('#sidebarToggle').click(function() {
                if ($(window).width() <= 768) {
                    $('#sidebar').addClass('mobile-show'); // Slide in
                } else {
                    $('#sidebar').toggleClass('collapsed'); // Shrink
                }
            });

            // Close sidebar (Mobile only)
            $('#sidebarClose').click(function() {
                $('#sidebar').removeClass('mobile-show'); // Slide out
            });
        });
    </script>
    <x-toast />
</body>
</html>