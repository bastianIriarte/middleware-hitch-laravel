    <div class="flex flex-wrap gap-3 border-b pb-2">

        <a href="{{ route('dashboard') }}"
            class="px-4 py-2 rounded-lg text-sm font-semibold 
                 @if (request()->routeIs('file-management.index') || request()->routeIs('dashboard')) bg-primary-500 text-white shadow
                 @else
                     text-primary-700 hover:bg-primary-50 @endif">
            Dashboard
        </a>

        <a href="{{ route('file-management.companies') }}"
            class="px-4 py-2 rounded-lg text-sm font-semibold 
                 {{ request()->routeIs('file-management.companies') ? 'bg-primary-500 text-white shadow' : 'text-primary-700 hover:bg-primary-50' }}">
            Empresas & FTP
        </a>

        <a href="{{ route('file-management.logs') }}"
            class="px-4 py-2 rounded-lg text-sm font-semibold 
                 {{ request()->routeIs('file-management.logs') ? 'bg-primary-500 text-white shadow' : 'text-primary-700 hover:bg-primary-50' }}">
            Logs de Archivos
        </a>

        <a href="{{ route('file-management.errors') }}"
            class="px-4 py-2 rounded-lg text-sm font-semibold 
                 {{ request()->routeIs('file-management.errors') ? 'bg-primary-500 text-white shadow' : 'text-primary-700 hover:bg-primary-50' }}">
            Errores
        </a>

        <a href="{{ route('file-management.stats') }}"
            class="px-4 py-2 rounded-lg text-sm font-semibold 
                 {{ request()->routeIs('file-management.stats') ? 'bg-primary-500 text-white shadow' : 'text-primary-700 hover:bg-primary-50' }}">
            Estadísticas
        </a>
        <a href="{{ route('watts-extraction.index') }}"
            class="px-4 py-2 rounded-lg text-sm font-semibold 
                 {{ request()->routeIs('watts-extraction.index') ? 'bg-primary-500 text-white shadow' : 'text-primary-700 hover:bg-primary-50' }}">
            Extraer data </a>
        <a href="{{ route('users') }}"
            class="px-4 py-2 rounded-lg text-sm font-semibold 
                 {{ request()->routeIs('users') ? 'bg-primary-500 text-white shadow' : 'text-primary-700 hover:bg-primary-50' }}">
           Usuarios </a>
    </div>
