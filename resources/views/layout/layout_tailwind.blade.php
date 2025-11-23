<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - {{ env('APP_NAME') }}</title>

    <link rel="shortcut icon" href="{{ asset(URL_LOGO_FAVICON) }}">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Alpine.js (ANTES para que x-data funcione bien) -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Custom Tailwind Config -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f5f7ff',
                            100: '#ebf0ff',
                            200: '#d6e0ff',
                            300: '#b3c7ff',
                            400: '#8ca8ff',
                            500: '#667eea',
                            600: '#5568d3',
                            700: '#4451b8',
                            800: '#343d94',
                            900: '#252d6b',
                        },
                        secondary: {
                            500: '#764ba2',
                            600: '#6a4391',
                        }
                    }
                }
            }
        }
    </script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }

        * { transition: all 0.2s ease; }

        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #667eea; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #5568d3; }

        .gradient-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    </style>
</head>

<body class="bg-gray-50">

    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">

                <!-- Logo -->
                <div class="flex items-center space-x-4">
                    <button id="sidebarToggle" class="lg:hidden p-2 rounded-lg hover:bg-gray-100">
                        <i data-lucide="menu" class="w-6 h-6 text-gray-600"></i>
                    </button>

                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-3">
                        <div class="w-10 h-10 gradient-primary rounded-lg flex items-center justify-center">
                            <i data-lucide="plug" class="w-6 h-6 text-white"></i>
                        </div>

                        <span class="text-xl font-bold bg-gradient-to-r from-primary-500 to-secondary-500 bg-clip-text text-transparent hidden sm:block">
                            Middleware Hitch
                        </span>
                    </a>
                </div>

                <!-- Right Actions -->
                <div class="flex items-center space-x-3">

                    <!-- Refresh -->
                    <button onclick="location.reload()" class="p-2 rounded-lg hover:bg-gray-100" title="Actualizar">
                        <i data-lucide="refresh-cw" class="w-5 h-5 text-gray-600"></i>
                    </button>

                    <!-- User Menu -->
                    <div class="relative" x-data="{ open: false }">

                        <button @click="open = !open" class="flex items-center p-2 rounded-lg hover:bg-gray-100">
                            <div class="w-8 h-8 bg-gradient-to-br from-primary-500 to-secondary-500 rounded-full flex items-center justify-center">
                                <i data-lucide="user" class="w-4 h-4 text-white"></i>
                            </div>
                        </button>

                        <!-- Dropdown -->
                        <div
                            x-show="open"
                            @click.away="open = false"
                            x-transition
                            class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1"
                        >
                            <a href="{{ route('logout') }}"
                               class="flex items-center space-x-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                <i data-lucide="log-out" class="w-4 h-4"></i>
                                <span>Cerrar Sesión</span>
                            </a>
                        </div>

                    </div>

                </div>

            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @yield('contenido')
    </main>

    <!-- Lucide Init -->
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            lucide.createIcons();
        });
    </script>

    @stack('scripts')
</body>
</html>
