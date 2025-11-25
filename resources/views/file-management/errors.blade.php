@extends('layout.layout_tailwind')

@section('title', 'Errores de Archivos')

@section('contenido')

    <div class="space-y-10">

        <!-- Title -->
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Errores de Archivos</h1>
            <p class="text-gray-500 mt-1">Listado general de errores generados por los archivos procesados.</p>
        </div>

        <!-- Navigation Tabs -->
        <x-navigation />

        <!-- Filtros -->
        <div class="bg-white shadow-md border rounded-xl">
            <div class="px-6 py-4 border-b">
                <h5 class="text-lg font-semibold text-gray-800">Filtros</h5>
            </div>

            <form method="GET" action="{{ route('file-management.errors') }}" class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-6">

                    <!-- Empresa -->
                    <div>
                        <label for="company_id" class="block text-sm font-semibold text-gray-700">
                            Empresa
                        </label>
                        <select id="company_id" name="company_id"
                            class="mt-2 w-full rounded-lg border-gray-300 focus:ring-primary-500 focus:border-primary-500">
                            <option value="">Todas</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->id }}"
                                    {{ request('company_id') == $company->id ? 'selected' : '' }}>
                                    {{ $company->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Tipo de archivo -->
                    <div>
                        <label for="file_type_id" class="block text-sm font-semibold text-gray-700">
                            Tipo de Archivo
                        </label>
                        <select id="file_type_id" name="file_type_id"
                            class="mt-2 w-full rounded-lg border-gray-300 focus:ring-primary-500 focus:border-primary-500">
                            <option value="">Todos</option>
                            @foreach ($fileTypes as $type)
                                <option value="{{ $type->id }}"
                                    {{ request('file_type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Severidad -->
                    <div>
                        <label for="severity" class="block text-sm font-semibold text-gray-700">
                            Severidad
                        </label>
                        <select id="severity" name="severity"
                            class="mt-2 w-full rounded-lg border-gray-300 focus:ring-primary-500 focus:border-primary-500">
                            <option value="">Todas</option>
                            <option value="low" {{ request('severity') == 'low' ? 'selected' : '' }}>Baja</option>
                            <option value="medium" {{ request('severity') == 'medium' ? 'selected' : '' }}>Media</option>
                            <option value="high" {{ request('severity') == 'high' ? 'selected' : '' }}>Alta</option>
                            <option value="critical" {{ request('severity') == 'critical' ? 'selected' : '' }}>Crítica
                            </option>
                        </select>
                    </div>

                    <!-- Fecha desde -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Desde</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}"
                            class="mt-2 w-full rounded-lg border-gray-300 focus:ring-primary-500 focus:border-primary-500">
                    </div>

                    <!-- Fecha hasta -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Hasta</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}"
                            class="mt-2 w-full rounded-lg border-gray-300 focus:ring-primary-500 focus:border-primary-500">
                    </div>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit"
                        class="px-5 py-2 rounded-lg bg-primary-500 text-white font-semibold hover:bg-primary-600">
                        Filtrar
                    </button>

                    <a href="{{ route('file-management.errors') }}"
                        class="px-5 py-2 rounded-lg bg-gray-200 text-gray-800 font-semibold hover:bg-gray-300">
                        Limpiar
                    </a>
                </div>

            </form>
        </div>

        <!-- Tabla -->
        <div class="bg-white shadow-md border rounded-xl">
            <div class="px-6 py-4 border-b">
                <h5 class="text-lg font-semibold text-gray-800">
                    Errores Registrados ({{ $errorsData->total() }} registros)
                </h5>
            </div>

            <div class="overflow-x-auto p-4">
                <table class="min-w-full text-sm text-left">
                    <thead>
                        <tr class="bg-gray-100 text-gray-600 uppercase text-xs">
                            <th class="px-4 py-3">ID</th>
                            <th class="px-4 py-3">Empresa</th>
                            <th class="px-4 py-3">Tipo</th>
                            <th class="px-4 py-3">Tipo Error</th>
                            <th class="px-4 py-3">Mensaje</th>
                            <th class="px-4 py-3">Línea</th>
                            <th class="px-4 py-3">Severidad</th>
                            <th class="px-4 py-3">Fecha</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y">

                        @forelse($errorsData as $error)
                            <tr class="hover:bg-gray-50">

                                <td class="px-4 py-3 font-semibold">#{{ $error->id }}</td>

                                <td class="px-4 py-3">
                                    <strong
                                        class="text-gray-900">{{ isset($error->company->code) ? $error->company->code : '' }}</strong><br>
                                    <span
                                        class="text-xs text-gray-500">{{ isset($error->company->name) ? $error->company->name : '' }}</span>
                                </td>

                                <td class="px-4 py-3">{{ isset($error->fileType->name) ? $error->fileType->name : '-' }}
                                </td>

                                <td class="px-4 py-3">
                                    <code
                                        class="text-primary-600">{{ isset($error->error_type) ? $error->error_type : '-' }}</code>
                                </td>

                                <td class="px-4 py-3">
                                    <span title="{{ $error->error_message }}">
                                        {{ $error->error_message }}
                                    </span>

                                    @if ($error->error_details)
                                        <br>
                                        <span class="text-xs text-gray-500">
                                            {{ $error->error_details }}
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-3">
                                    @if ($error->line_number)
                                        <span class="px-2 py-1 bg-gray-200 text-gray-700 text-xs rounded">
                                            {{ $error->line_number }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3">
                                    @switch($error->severity)
                                        @case('critical')
                                            <span class="px-2 py-1 bg-red-100 text-red-700 text-xs rounded">Crítica</span>
                                        @break

                                        @case('high')
                                            <span class="px-2 py-1 bg-yellow-100 text-yellow-700 text-xs rounded">Alta</span>
                                        @break

                                        @case('medium')
                                            <span class="px-2 py-1 bg-blue-100 text-blue-700 text-xs rounded">Media</span>
                                        @break

                                        @default
                                            <span class="px-2 py-1 bg-gray-200 text-gray-700 text-xs rounded">Baja</span>
                                    @endswitch
                                </td>

                                <td class="px-4 py-3 text-xs">
                                    {{ $error->created_at->format('d/m/Y H:i') }}
                                </td>

                            </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-6 text-gray-500">
                                        No se encontraron errores
                                    </td>
                                </tr>
                            @endforelse

                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4">
                    {{ $errorsData->links() }}
                </div>

            </div>
        </div>

    @endsection
