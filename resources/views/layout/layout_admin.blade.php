<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - {{ env('APP_NAME') }}</title>
    <link rel="shortcut icon" href="{{ asset(URL_LOGO_FAVICON) }}">
    <link id="style" href="{{ asset(ASSETS_LIBS_ADMIN) }}/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset(ASSETS_ADMIN) }}/family/css/style.css" rel="stylesheet">
    <link href="{{ asset(ASSETS_CSS_ADMIN) }}/icons.css" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset(ASSETS_CSS_ADMIN) }}/jquery.dataTables.min.css">
    <link rel="stylesheet" href="{{ asset(ASSETS_CSS_ADMIN) }}/dataTables.bootstrap5.min.css">
</head>

<body>
    <!-- Header -->
    <div class="header">
        <div class="header-content">
            <div class="d-flex align-items-center">
                <button class="btn btn-header menu-toggle me-3" id="btnToggleSidebar">
                    <i class="fa fa-bars"></i>
                </button>
                <div class="logo">
                    <i class="fa fa-plug"></i>
                    <span class="d-none d-md-block">Middleware Hitch</span>
                </div>
            </div>

            <div class="header-actions">
                <button class="btn btn-header" id="btnRefreshDashboard" title="Actualizar">
                    <i class="fa fa-refresh"></i>
                </button>
                <div class="user-menu">
                    <div class="user-avatar" id="btnUserMenu">
                        <i class="fa fa-user"></i>
                    </div>
                    <div class="dropdown-menu dropdown-menu-end" id="userDropdown" style="margin-left: -110px;">
                        {{-- <a class="dropdown-item" href="#"><i class="fa fa-user me-2"></i>Perfil</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#"><i class="fa fa-cog me-2"></i>Configuración</a>
                        <div class="dropdown-divider"></div> --}}
                         @php
                            $logout = route('logout');
                        @endphp
                        <a class="dropdown-item" href="{{ $logout }}">
                            <i class="fa fa-sign-out me-2"></i>
                            Cerrar Sesión
                        </a>

                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('layout.sidenav_admin')

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <div class="content-wrapper">
            @yield('contenido')
        </div>
    </div>

    <!-- Footer -->
    <div class="footer" id="footer">
        <div class="footer-left">
            <div class="designed-by">
                <span>Diseñado por</span>
                <span><strong><a href="https://hitch.cl" target="_blank">Hitch.cl</a></strong></span>
            </div>
        </div>
        <div class="footer-right">
            <span>© 2025 Middleware Hitch</span>
            <span class="version-badge">v2.1.4</span>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container position-fixed top-0 end-0 p-3"></div>

    <!-- Bootstrap JS -->
    <script src="{{ asset(ASSETS_JS_ADMIN) }}/jquery-3.6.0.min.js"></script>
    <script src="{{ asset(ASSETS_JS_ADMIN) }}/jquery.dataTables.min.js"></script>
    <script src="{{ asset(ASSETS_JS_ADMIN) }}/dataTables.bootstrap5.min.js"></script>
    <script src="{{ asset(ASSETS_LIBS_ADMIN) }}/bootstrap/js/bootstrap.bundle.min.js"></script>

    @yield('js_content')

    <!-- Secure inline JS -->
    <script nonce="{{ app('csp_nonce') }}">
        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('sidebarOverlay')?.addEventListener('click', closeSidebar);
            // Event listeners
            document.getElementById('btnToggleSidebar')?.addEventListener('click', toggleSidebar);
            document.getElementById('btnRefreshDashboard')?.addEventListener('click', refreshDashboard);
            document.getElementById('btnUserMenu')?.addEventListener('click', toggleUserMenu);
            document.getElementById('btnLogout')?.addEventListener('click', function (e) {
                e.preventDefault();
                logout();
            });

            // Cerrar dropdown al hacer clic fuera
            document.addEventListener('click', function (event) {
                const userMenu = document.querySelector('.user-menu');
                const dropdown = document.getElementById('userDropdown');
                if (!userMenu.contains(event.target)) {
                    dropdown.classList.remove('show');
                }
            });
        });

        let sidebarOpen = window.innerWidth >= 992;

        function showToast(message, type = 'info') {
            const toastContainer = document.querySelector('.toast-container');
            const toast = document.createElement('div');
            toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} border-0`;
            toast.setAttribute('role', 'alert');
            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fa fa-${type === 'success' ? 'check' : type === 'error' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            `;
            toastContainer.appendChild(toast);
            new bootstrap.Toast(toast).show();
            toast.addEventListener('hidden.bs.toast', () => toast.remove());
        }

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const mainContent = document.getElementById('mainContent');

            sidebarOpen = !sidebarOpen;

            if (sidebarOpen) {
                sidebar.classList.add('active');
                overlay.classList.add('active');
                if (window.innerWidth >= 992) {
                    mainContent.classList.add('sidebar-open');
                }
            } else {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
                mainContent.classList.remove('sidebar-open');
            }
        }

        function closeSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const mainContent = document.getElementById('mainContent');

            sidebarOpen = false;
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
            mainContent.classList.remove('sidebar-open');
        }

        function toggleUserMenu() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('show');
        }


        function refreshDashboard() {
            window.location.reload();
        }

        function showNotifications() {
            showToast('Panel de notificaciones', 'info');
        }

        @if (session()->has('success_message'))
            showToast(`{!! session()->get('success_message') !!}`, 'success')
        @endif

        @if (session()->has('warning_message'))
            showToast(`{!! session()->get('warning_message') !!}`, 'warning')
        @endif

        @if (session()->has('danger_message'))
            showToast(`{!! session()->get('danger_message') !!}`, 'error')
        @endif

        @if ($errors->any())
            @php
                $errorMessages = implode('<br>', $errors->all()); // Usar <br> para separar los mensajes de error
                $error = 'Se encontraron los siguientes errores: <br>' . $errorMessages;
            @endphp
            showToast(`{!! $error !!}`, 'error')
        @endif
    </script>
</body>

</html>
