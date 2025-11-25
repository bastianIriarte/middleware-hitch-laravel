@extends('layout.layout_tailwind')

@section('title', 'Gestión de Archivos')

@section('contenido')

    <div class="space-y-10">

        <!-- Título -->
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Gestión de Archivos</h1>
            <p class="text-gray-500 mt-1">Resumen general del estado de los archivos procesados</p>
        </div>

        <!-- Estadísticas -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

            <!-- Empresas -->
            <div class="bg-gradient-to-br from-primary-500 to-secondary-600 text-white p-6 rounded-xl shadow-md">
                <h5 class="text-lg font-semibold">Empresas</h5>
                <p class="text-4xl font-extrabold mt-3">{{ $stats['active_companies'] }}</p>
                <p class="text-sm opacity-80">{{ $stats['total_companies'] }} total</p>
            </div>

            <!-- Tipos de archivo -->
            <div class="bg-blue-500 text-white p-6 rounded-xl shadow-md">
                <h5 class="text-lg font-semibold">Tipos de Archivo</h5>
                <p class="text-4xl font-extrabold mt-3">{{ $stats['active_file_types'] }}</p>
                <p class="text-sm opacity-80">{{ $stats['total_file_types'] }} total</p>
            </div>

            <!-- Archivos subidos -->
            <div class="bg-green-500 text-white p-6 rounded-xl shadow-md">
                <h5 class="text-lg font-semibold">Archivos Subidos</h5>
                <p class="text-4xl font-extrabold mt-3">{{ $stats['files_uploaded'] }}</p>
                <p class="text-sm opacity-80">{{ $stats['total_files'] }} total</p>
            </div>

            <!-- Errores -->
            <div class="bg-red-500 text-white p-6 rounded-xl shadow-md">
                <h5 class="text-lg font-semibold">Errores</h5>
                <p class="text-4xl font-extrabold mt-3">{{ $stats['total_errors'] }}</p>
                <p class="text-sm opacity-80">{{ $stats['files_failed'] }} archivos fallidos</p>
            </div>
        </div>

        <!-- Tabs de navegación -->
        <x-navigation />

        <!-- Logs Recientes -->
        <div class="bg-white shadow-md rounded-xl border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h5 class="text-lg font-semibold text-gray-800">Logs Recientes</h5>
            </div>

            <div class="p-4 overflow-x-auto">
                <table class="min-w-full text-sm text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-100 text-gray-600 uppercase text-xs">
                            <th class="px-4 py-3">ID</th>
                            <th class="px-4 py-3">Empresa</th>
                            <th class="px-4 py-3">Tipo</th>
                            <th class="px-4 py-3">Archivo</th>
                            <th class="px-4 py-3">Estado</th>
                            <th class="px-4 py-3">Registros</th>
                            <th class="px-4 py-3">Rechazados</th>
                            <th class="px-4 py-3">Fecha</th>
                            <th class="px-4 py-3">Acciones</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y">

                        @forelse($recentLogs as $log)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">{{ $log->id }}</td>
                                <td class="px-4 py-3">{{ $log->company->name }}</td>
                                <td class="px-4 py-3">{{ $log->fileType->name }}</td>
                                <td class="px-4 py-3">{{ Str::limit($log->original_filename, 40) }}</td>

                                <td class="px-4 py-3">
                                    @switch($log->status)
                                        @case('uploaded')
                                            <span class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded">Subido</span>
                                        @break

                                        @case('failed')
                                            <span class="px-2 py-1 text-xs bg-red-100 text-red-700 rounded">Fallido</span>
                                        @break

                                        @case('processing')
                                            <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-700 rounded">Procesando</span>
                                        @break

                                        @default
                                            <span
                                                class="px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded">{{ $log->status }}</span>
                                    @endswitch
                                </td>

                                <td class="px-4 py-3">{{ number_format($log->records_count ?? 0) }}</td>
                                <td class="px-4 py-3">{{ number_format($log->rejected_count ?? 0) }}</td>
                                <td class="px-4 py-3">{{ $log->created_at->format('d/m/Y H:i') }}</td>

                                <td class="px-4 py-3">
                                    <a href="{{ route('file-management.log-detail', $log->id) }}"
                                        class="text-primary-600 hover:text-primary-800 font-semibold text-xs">
                                        Ver
                                    </a>
                                </td>
                            </tr>

                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-6 text-gray-500">
                                        No hay logs recientes
                                    </td>
                                </tr>
                            @endforelse

                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    @endsection
