<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - {{ $systemSettings->system_name }}</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>
<body class="bg-light">
    
    <div class="d-flex min-vh-100">
        
        <aside class="sidebar d-flex flex-column p-3 text-white shadow-lg" id="sidebar">
            
            <div class="d-flex align-items-center justify-content-between mb-4 mt-2">
              <div class="d-flex align-items-center">
                  <img src="{{ $systemSettings->logoUrl() }}" alt="Logo" id="sidebarBrandLogo" class="brand-logo rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                  <h5 class="mb-0 ms-3 fw-bold sidebar-text" id="sidebarBrandName">{{ $systemSettings->system_name }}</h5>
              </div>

              <button class="btn text-white fs-4 mobile-close-btn p-0 border-0" id="sidebarClose">
                  <i class="bi bi-x-lg"></i>
              </button>
          </div>

            <ul class="nav flex-column gap-2 mb-auto">
                <li class="nav-item">
                    <a href="/dashboard/home" class="nav-link sidebar-link d-flex align-items-center {{ request()->is('dashboard/home') ? 'active' : '' }}">
                        <i class="bi bi-house-door fs-5"></i>
                        <span class="ms-3 sidebar-text">Home</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/dashboard/products" class="nav-link sidebar-link d-flex align-items-center {{ request()->is('dashboard/products') ? 'active' : '' }}">
                        <i class="bi bi-bicycle fs-5"></i>
                        <span class="ms-3 sidebar-text">Products</span>
                    </a>
                </li>
                @if(Auth::check() && Auth::user()->canAccessPage('lowstock'))
                    <li class="nav-item">
                        <a href="/dashboard/lowstock" class="nav-link sidebar-link d-flex align-items-center {{ request()->is('dashboard/lowstock') ? 'active' : '' }}">
                            <i class="bi bi-exclamation-triangle fs-5"></i>
                            <span class="ms-3 sidebar-text">Low Stock</span>
                        </a>
                    </li>
                @endif
                @if(Auth::check() && Auth::user()->canAccessPage('nostock'))
                    <li class="nav-item">
                        <a href="/dashboard/nostock" class="nav-link sidebar-link d-flex align-items-center {{ request()->is('dashboard/nostock') ? 'active' : '' }}">
                            <i class="bi bi-x-circle fs-5"></i>
                            <span class="ms-3 sidebar-text">No Stock</span>
                        </a>
                    </li>
                @endif
                @if(Auth::check() && Auth::user()->canAccessPage('logs'))
                    <li class="nav-item">
                        <a href="/dashboard/logs" class="nav-link sidebar-link d-flex align-items-center {{ request()->is('dashboard/logs') ? 'active' : '' }}">
                            <i class="bi bi-journal-text fs-5"></i>
                            <span class="ms-3 sidebar-text">Logs</span>
                        </a>
                    </li>
                @endif
                @if(Auth::check() && Auth::user()->canAccessPage('accounts'))
                    <li class="nav-item">
                        <a href="/dashboard/accounts" class="nav-link sidebar-link d-flex align-items-center {{ request()->is('dashboard/accounts') ? 'active' : '' }}">
                            <i class="bi bi-people fs-5"></i>
                            <span class="ms-3 sidebar-text">Accounts</span>
                        </a>
                    </li>
                @endif
                @if(Auth::check() && Auth::user()->canAccessPage('backup'))
                    <li class="nav-item">
                        <a href="/dashboard/backup" class="nav-link sidebar-link d-flex align-items-center {{ request()->is('dashboard/backup') ? 'active' : '' }}">
                            <i class="bi bi-database-down fs-5"></i>
                            <span class="ms-3 sidebar-text">System Backup</span>
                        </a>
                    </li>
                @endif

        
                @if(Auth::check() && Auth::user()->canAccessPage('audit-logs'))
                    <li class="nav-item">
                        <a href="/dashboard/audit-logs" class="nav-link sidebar-link d-flex align-items-center {{ request()->is('dashboard/audit-logs') ? 'active' : '' }}">
                            <i class="bi bi-shield-check fs-5"></i>
                            <span class="ms-3 sidebar-text">Audit Logs</span>
                        </a>
                    </li>
                @endif

                @if(Auth::check() && Auth::user()->canAccessPage('system-settings'))
                  <li class="nav-item">
                      <a href="/dashboard/system-settings" class="nav-link sidebar-link d-flex align-items-center {{ request()->is('dashboard/system-settings') ? 'active' : '' }}">
                          <i class="bi bi-sliders fs-5"></i>
                          <span class="ms-3 sidebar-text">System Settings</span>
                      </a>
                  </li>
              @endif

                <li class="nav-item">
                    <a href="/dashboard/profile" class="nav-link sidebar-link d-flex align-items-center {{ request()->is('dashboard/profile') ? 'active' : '' }}">
                        <i class="bi bi-person-gear fs-5"></i>
                        <span class="ms-3 sidebar-text">Profile & Settings</span>
                    </a>
                </li>
            </ul>

            <hr class="text-white-50">
            <a href="/logout" class="nav-link sidebar-link d-flex align-items-center text-danger fw-bold">
                <i class="bi bi-box-arrow-left fs-5 text-white"></i>
                <span class="ms-3 sidebar-text text-white">Logout</span>
            </a>
            
        </aside>

        <main class="grow d-flex flex-column w-100 overflow-auto">
            
            <!-- TOP NAVBAR WITH USER & ROLE INDICATOR -->
            <header class="bg-white shadow-sm px-4 py-2 d-flex align-items-center justify-content-between">
                <button class="btn btn-light border-0" id="sidebarToggle">
                    <i class="bi bi-list fs-4"></i>
                </button>

                @if(Auth::check())
                    @php
                        $currentUser = Auth::user();
                        $avatarUrl = $currentUser->profilePictureUrl();
                        $roleUpper = strtoupper(trim((string) $currentUser->Role));
                        $roleBadgeClass = match($roleUpper) {
                            'ADMINISTRATOR' => 'bg-danger text-white',
                            'OWNER' => 'bg-dark text-white',
                            'MECHANIC' => 'bg-info text-dark',
                            default => 'bg-primary text-white',
                        };
                    @endphp

                    <div class="dropdown">
                        <button class="btn btn-light border rounded-pill px-3 py-1 d-flex align-items-center gap-2 shadow-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="{{ $avatarUrl ?? '' }}"
                                 alt="Avatar"
                                 id="navbarAvatarImg"
                                 class="rounded-circle {{ !empty($avatarUrl) ? '' : 'd-none' }}"
                                 style="width: 32px; height: 32px; object-fit: cover;">

                            <span class="rounded-circle d-inline-flex align-items-center justify-content-center text-white fw-bold {{ !empty($avatarUrl) ? 'd-none' : '' }}"
                                  id="navbarAvatarInitial"
                                  style="width: 32px; height: 32px; background-color: rgb(12, 17, 44); font-size: 0.85rem;">
                                {{ $currentUser->initial() }}
                            </span>

                            <div class="text-start d-none d-sm-block lh-sm">
                                <div class="fw-bold text-dark small" id="navbarUserEmail">{{ $currentUser->Username }}</div>
                                <span class="badge {{ $roleBadgeClass }}" style="font-size: 0.68rem;">
                                    {{ $currentUser->Role }}
                                </span>
                            </div>
                        </button>

                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 mt-2">
                            <li class="px-3 py-2 d-sm-none border-bottom">
                                <div class="fw-bold small text-dark" id="navbarUserEmailMobile">{{ $currentUser->Username }}</div>
                                <span class="badge {{ $roleBadgeClass }} mt-1">{{ $currentUser->Role }}</span>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="/dashboard/profile">
                                    <i class="bi bi-person-gear text-primary"></i>
                                    <span>Profile & Settings</span>
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2 py-2 text-danger fw-semibold" href="/logout">
                                    <i class="bi bi-box-arrow-left"></i>
                                    <span>Logout</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                @endif
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

            // Open sidebar 
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

    @stack('scripts')
    <x-toast />
</body>
</html>