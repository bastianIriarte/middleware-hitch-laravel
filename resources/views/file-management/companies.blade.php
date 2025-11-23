@extends('layout.layout_tailwind')

@section('title', 'Empresas y Configuración FTP')

@section('contenido')

<div class="space-y-10">

    <!-- Título -->
    <div>
        <h1 class="text-3xl font-bold text-gray-800">Empresas y Configuración FTP</h1>
        <p class="text-gray-500 mt-1">Gestión de empresas, carga de archivos y parámetros de conexión.</p>
    </div>

    <!-- Tabs -->
    <div class="flex flex-wrap gap-3 border-b pb-2">

        <a href="{{ route('file-management.index') }}"
           class="px-4 py-2 rounded-lg text-sm font-semibold 
           {{ request()->routeIs('file-management.index') ? 'bg-primary-500 text-white shadow' : 'text-primary-700 hover:bg-primary-50' }}">
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

    </div>

    <!-- Alertas -->
    @if(session('success'))
        <div class="p-4 text-green-800 bg-green-100 border border-green-300 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 text-red-800 bg-red-100 border border-red-300 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    <!-- Tabla de Empresas -->
    <div class="bg-white shadow-md rounded-xl border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h5 class="text-lg font-semibold text-gray-800">Empresas Registradas</h5>
        </div>

        <div class="p-4 overflow-x-auto">
            <table class="min-w-full text-sm text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 text-gray-600 uppercase text-xs">
                        <th class="px-4 py-3">ID</th>
                        <th class="px-4 py-3">Código</th>
                        <th class="px-4 py-3">Nombre</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3">Teléfono</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3">Archivos</th>
                        <th class="px-4 py-3">Errores</th>
                        <th class="px-4 py-3">FTP</th>
                        <th class="px-4 py-3 text-center">Acciones</th>
                    </tr>
                </thead>

                <tbody class="divide-y">

                    @forelse($companies as $company)
                    <tr class="hover:bg-gray-50">

                        <td class="px-4 py-3">{{ $company->id }}</td>
                        <td class="px-4 py-3 font-semibold text-gray-900">{{ $company->code }}</td>
                        <td class="px-4 py-3">{{ $company->name }}</td>
                        <td class="px-4 py-3">{{ $company->email }}</td>
                        <td class="px-4 py-3">{{ $company->phone }}</td>

                        <td class="px-4 py-3">
                            @if($company->status)
                                <span class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded">Activa</span>
                            @else
                                <span class="px-2 py-1 text-xs bg-gray-200 text-gray-700 rounded">Inactiva</span>
                            @endif
                        </td>

                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs bg-blue-100 text-blue-700 rounded">
                                {{ $company->file_logs_count }}
                            </span>
                        </td>

                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs bg-red-100 text-red-700 rounded">
                                {{ $company->file_errors_count }}
                            </span>
                        </td>

                        <td class="px-4 py-3">
                            @if($company->ftpConfig)
                                @if($company->ftpConfig->status)
                                    <span class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded">Configurado</span>
                                @else
                                    <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-700 rounded">Deshabilitado</span>
                                @endif
                            @else
                                <span class="px-2 py-1 text-xs bg-gray-200 text-gray-700 rounded">Sin configurar</span>
                            @endif
                        </td>

                        <td class="px-4 py-3 text-center">
                            <div class="flex justify-center space-x-2">

                                <!-- Configurar FTP -->
                                <a href="{{ route('file-management.ftp-config', $company->id) }}"
                                   class="px-3 py-1 text-xs bg-primary-500 text-white rounded hover:bg-primary-600">
                                    FTP
                                </a>

                                <!-- Test FTP -->
                                @if($company->ftpConfig)
                                <a href="{{ route('file-management.test-ftp', $company->id) }}"
                                   class="px-3 py-1 text-xs bg-green-500 text-white rounded hover:bg-green-600">
                                    Test
                                </a>
                                @endif

                            </div>
                        </td>

                    </tr>
                    @empty

                    <tr>
                        <td colspan="10" class="text-center py-6 text-gray-500">
                            No hay empresas registradas
                        </td>
                    </tr>

                    @endforelse

                </tbody>
            </table>
        </div>
    </div>

</div>

@endsection
