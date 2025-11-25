@extends('layout.layout_tailwind')

@section('title', 'Estadísticas de Archivos')

@section('contenido')

    <div class="space-y-10">

        <!-- Title -->
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Estadísticas de Archivos</h1>
            <p class="text-gray-500 mt-1">Resumen general del comportamiento de los archivos procesados.</p>
        </div>

        <!-- Navigation Tabs -->
        <x-navigation />


        <!-- Filtros -->
        <div class="bg-white shadow-md border rounded-xl">
            <div class="px-6 py-4 border-b">
                <h5 class="text-lg font-semibold text-gray-800">Filtros</h5>
            </div>

            <form method="GET" action="{{ route('file-management.stats') }}" class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    <!-- Empresa -->
                    <div class="col-span-1 md:col-span-1">
                        <label class="block text-sm font-semibold text-gray-700">Empresa</label>
                        <select name="company_id"
                            class="mt-2 w-full rounded-lg border-gray-300 focus:ring-primary-500 focus:border-primary-500">
                            <option value="">Todas las empresas</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->id }}"
                                    {{ request('company_id') == $company->id ? 'selected' : '' }}>
                                    {{ $company->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Tipo de archivo -->
                    <div class="col-span-1 md:col-span-1">
                        <label class="block text-sm font-semibold text-gray-700">Tipo de Archivo</label>
                        <select name="file_type_id"
                            class="mt-2 w-full rounded-lg border-gray-300 focus:ring-primary-500 focus:border-primary-500">
                            <option value="">Todos los tipos</option>
                            @foreach ($fileTypes as $type)
                                <option value="{{ $type->id }}"
                                    {{ request('file_type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-span-1 flex items-end">
                        <button type="submit"
                            class="w-full px-5 py-2 rounded-lg bg-primary-500 text-white font-semibold hover:bg-primary-600">
                            Filtrar
                        </button>
                    </div>

                </div>
            </form>
        </div>


        <!-- Estadísticas -->
        <div class="grid grid-cols-2 md:grid-cols-6 gap-4">

            <x-stat icon="database" color="blue" :value="$stats['total']">
                Total
            </x-stat>

            <x-stat icon="inbox" color="purple" :value="$stats['received']">
                Recibidos
            </x-stat>

            <x-stat icon="loader" color="yellow" :value="$stats['processing']">
                Procesando
            </x-stat>

            <x-stat icon="check-circle" color="green" :value="$stats['uploaded']">
                Subidos
            </x-stat>

            <x-stat icon="x-circle" color="rose" :value="$stats['failed']">
                Fallidos
            </x-stat>

            <x-stat icon="alert-triangle" color="red" :value="$stats['total_errors']">
                Errores
            </x-stat>

        </div>


        <!-- Logs recientes -->
        <div class="bg-white shadow-md border rounded-xl">

            <div class="px-6 py-4 border-b">
                <h5 class="text-lg font-semibold text-gray-800">Archivos Recientes</h5>
            </div>

            <div class="overflow-x-auto p-4">
                <table class="min-w-full text-sm text-left">
                    <thead>
                        <tr class="bg-gray-100 text-gray-600 uppercase text-xs">
                            <th class="px-4 py-3">ID</th>
                            <th class="px-4 py-3">Empresa</th>
                            <th class="px-4 py-3">Tipo</th>
                            <th class="px-4 py-3">Archivo</th>
                            <th class="px-4 py-3">Estado</th>
                            <th class="px-4 py-3">Registros</th>
                            <th class="px-4 py-3">Fecha</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y">

                        @forelse($recentLogs as $log)
                            <tr class="hover:bg-gray-50">

                                <td class="px-4 py-3">{{ $log->id }}</td>

                                <td class="px-4 py-3">
                                    <span class="font-semibold text-gray-900">{{ $log->company->code }}</span>
                                </td>

                                <td class="px-4 py-3">{{ $log->fileType->name }}</td>

                                <td class="px-4 py-3">{{ Str::limit($log->original_filename, 40) }}</td>

                                <td class="px-4 py-3">

                                    @switch($log->status)
                                        @case('uploaded')
                                            <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs">Subido</span>
                                        @break

                                        @case('failed')
                                            <span class="px-2 py-1 bg-red-100 text-red-700 rounded text-xs">Fallido</span>
                                        @break

                                        @case('processing')
                                            <span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded text-xs">Procesando</span>
                                        @break

                                        @default
                                            <span
                                                class="px-2 py-1 bg-gray-200 text-gray-700 rounded text-xs">{{ $log->status }}</span>
                                    @endswitch

                                </td>

                                <td class="px-4 py-3">{{ number_format($log->records_count) }}</td>

                                <td class="px-4 py-3 text-xs">{{ $log->created_at->format('d/m/Y H:i') }}</td>

                            </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-6 text-gray-500">No hay archivos recientes</td>
                                </tr>
                            @endforelse

                        </tbody>
                    </table>
                </div>

            </div>

        </div>

    @endsection
