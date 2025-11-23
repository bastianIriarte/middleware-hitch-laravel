@props(['active' => 'dashboard'])

<nav class="flex space-x-2 bg-white rounded-xl p-2 shadow-sm border border-gray-200">
    <a href="{{ route('file-management.index') }}" 
       class="flex items-center space-x-2 px-4 py-2.5 rounded-lg {{ $active === 'dashboard' ? 'gradient-primary text-white shadow-lg' : 'text-gray-700 hover:bg-gray-100' }} font-medium transition-colors">
        <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
        <span>Dashboard</span>
    </a>
    <a href="{{ route('file-management.companies') }}" 
       class="flex items-center space-x-2 px-4 py-2.5 rounded-lg {{ $active === 'companies' ? 'gradient-primary text-white shadow-lg' : 'text-gray-700 hover:bg-gray-100' }} font-medium transition-colors">
        <i data-lucide="building-2" class="w-4 h-4"></i>
        <span>Empresas & FTP</span>
    </a>
    <a href="{{ route('file-management.logs') }}" 
       class="flex items-center space-x-2 px-4 py-2.5 rounded-lg {{ $active === 'logs' ? 'gradient-primary text-white shadow-lg' : 'text-gray-700 hover:bg-gray-100' }} font-medium transition-colors">
        <i data-lucide="list" class="w-4 h-4"></i>
        <span>Logs</span>
    </a>
    <a href="{{ route('file-management.errors') }}" 
       class="flex items-center space-x-2 px-4 py-2.5 rounded-lg {{ $active === 'errors' ? 'gradient-primary text-white shadow-lg' : 'text-gray-700 hover:bg-gray-100' }} font-medium transition-colors">
        <i data-lucide="bug" class="w-4 h-4"></i>
        <span>Errores</span>
    </a>
    <a href="{{ route('file-management.stats') }}" 
       class="flex items-center space-x-2 px-4 py-2.5 rounded-lg {{ $active === 'stats' ? 'gradient-primary text-white shadow-lg' : 'text-gray-700 hover:bg-gray-100' }} font-medium transition-colors">
        <i data-lucide="bar-chart-3" class="w-4 h-4"></i>
        <span>Estadísticas</span>
    </a>
</nav>
