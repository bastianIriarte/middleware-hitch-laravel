@extends('layout.layout_tailwind')

@section('title', 'Logs de Archivos')

@section('contenido')

<div class="space-y-10">

    <!-- Título -->
    <div>
        <h1 class="text-3xl font-bold text-gray-800">Logs de Archivos</h1>
        <p class="text-gray-500 mt-1">Historial detallado de archivos procesados y sus estados.</p>
    </div>

    <!-- Tabs -->
    <div class="flex flex-wrap gap-3 border-b pb-2">

        <a href="{{ route('file-management.index') }}"
           class="px-4 py-2 rounded-lg text-sm font-semibold
           {{ request()->routeIs('file-management.index') ? 'bg-primary-500 text-white' : 'text-primary-700 hover:bg-primary-50' }}">
            Dashboard
        </a>

        <a href="{{ route('file-management.companies') }}"
           class="px-4 py-2 rounded-lg text-sm font-semibold
           {{ request()->routeIs('file-management.companies') ? 'bg-primary-500 text-white' : 'text-primary-700 hover:bg-primary-50' }}">
            Empresas & FTP
        </a>

        <a href="{{ route('file-management.logs') }}"
           class="px-4 py-2 rounded-lg text-sm font-semibold
           {{ request()->routeIs('file-management.logs') ? 'bg-primary-500 text-white' : 'text-primary-700 hover:bg-primary-50' }}">
            Logs de Archivos
        </a>

        <a href="{{ route('file-management.errors') }}"
           class="px-4 py-2 rounded-lg text-sm font-semibold
           {{ request()->routeIs('file-management.errors') ? 'bg-primary-500 text-white' : 'text-primary-700 hover:bg-primary-50' }}">
            Errores
        </a>

        <a href="{{ route('file-management.stats') }}"
           class="px-4 py-2 rounded-lg text-sm font-semibold
           {{ request()->routeIs('file-management.stats') ? 'bg-primary-500 text-white' : 'text-primary-700 hover:bg-primary-50' }}">
            Estadísticas
        </a>
    </div>

    <!-- Filtros -->
    <div class="bg-white border rounded-xl shadow-md">
        <div class="px-6 py-4 border-b">
            <h5 class="text-lg font-semibold text-gray-800">Filtros</h5>
        </div>

        <div class="p-6">
            <form method="GET" action="{{ route('file-management.logs') }}" class="space-y-6">

                <div class="grid grid-cols-1 md:grid-cols-5 gap-6">

                    <!-- Empresa -->
                    <div>
                        <label for="company_id" class="block text-sm font-semibold text-gray-700">Empresa</label>
                        <select id="company_id" name="company_id"
                                class="mt-2 w-full rounded-lg border-gray-300 focus:ring-primary-500 focus:border-primary-500">
                            <option value="">Todas</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}"
                                    {{ request('company_id') == $company->id ? 'selected' : '' }}>
                                    {{ $company->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Tipo de archivo -->
                    <div>
                        <label for="file_type_id" class="block text-sm font-semibold text-gray-700">Tipo de Archivo</label>
                        <select id="file_type_id" name="file_type_id"
                                class="mt-2 w-full rounded-lg border-gray-300 focus:ring-primary-500 focus:border-primary-500">
                            <option value="">Todos</option>
                            @foreach($fileTypes as $type)
                                <option value="{{ $type->id }}"
                                    {{ request('file_type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Estado -->
                    <div>
                        <label for="status" class="block text-sm font-semibold text-gray-700">Estado</label>
                        <select id="status" name="status"
                                class="mt-2 w-full rounded-lg border-gray-300 focus:ring-primary-500 focus:border-primary-500">
                            <option value="">Todos</option>
                            <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>Recibido</option>
                            <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Procesando</option>
                            <option value="uploaded" {{ request('status') == 'uploaded' ? 'selected' : '' }}>Subido</option>
                            <option value="failed"  {{ request('status') == 'failed' ? 'selected' : '' }}>Fallido</option>
                        </select>
                    </div>

                    <!-- Fecha desde -->
                    <div>
                        <label for="date_from" class="block text-sm font-semibold text-gray-700">Desde</label>
                        <input type="date" id="date_from" name="date_from"
                               value="{{ request('date_from') }}"
                               class="mt-2 w-full rounded-lg border-gray-300 focus:ring-primary-500 focus:border-primary-500">
                    </div>

                    <!-- Fecha hasta -->
                    <div>
                        <label for="date_to" class="block text-sm font-semibold text-gray-700">Hasta</label>
                        <input type="date" id="date_to" name="date_to"
                               value="{{ request('date_to') }}"
                               class="mt-2 w-full rounded-lg border-gray-300 focus:ring-primary-500 focus:border-primary-500">
                    </div>
                </div>

                <!-- Botones -->
                <div class="flex gap-3">
                    <button type="submit"
                        class="px-5 py-2 rounded-lg bg-primary-500 text-white font-semibold hover:bg-primary-600">
                        Filtrar
                    </button>

                    <a href="{{ route('file-management.logs') }}"
                       class="px-5 py-2 rounded-lg bg-gray-200 text-gray-800 font-semibold hover:bg-gray-300">
                        Limpiar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Logs -->
    <div class="bg-white border rounded-xl shadow-md">
        <div class="px-6 py-4 border-b">
            <h5 class="text-lg font-semibold text-gray-800">Logs de Archivos ({{ $logs->total() }} registros)</h5>
        </div>

        <div class="p-4 overflow-x-auto">
            <table class="min-w-full text-sm text-left">
                <thead>
                    <tr class="bg-gray-100 text-gray-600 uppercase text-xs">
                        <th class="px-4 py-3">ID</th>
                        <th class="px-4 py-3">Empresa</th>
                        <th class="px-4 py-3">Tipo</th>
                        <th class="px-4 py-3">Archivo Original</th>
                        <th class="px-4 py-3">Tamaño</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3">Errores</th>
                        <th class="px-4 py-3">Recibido</th>
                        <th class="px-4 py-3">Subido</th>
                        <th class="px-4 py-3">Acciones</th>
                    </tr>
                </thead>

                <tbody class="divide-y">

                    @forelse($logs as $log)
                    <tr class="hover:bg-gray-50">

                        <td class="px-4 py-3">{{ $log->id }}</td>

                        <td class="px-4 py-3">
                            <strong class="text-gray-900">{{ $log->company->code }}</strong>
                            <br>
                            <span class="text-gray-600 text-xs">{{ $log->company->name }}</span>
                        </td>

                        <td class="px-4 py-3">{{ $log->fileType->name }}</td>

                        <td class="px-4 py-3">
                            <span title="{{ $log->original_filename }}">
                                {{ Str::limit($log->original_filename, 30) }}
                            </span>
                        </td>

                        <td class="px-4 py-3">{{ number_format($log->file_size / 1024, 2) }} KB</td>

                        <td class="px-4 py-3">
                            @switch($log->status)
                                @case('uploaded')
                                    <span class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded">Subido</span>
                                    @break
                                @case('failed')
                                    <span class="px-2 py-1 text-xs bg-red-100 text-red-700 rounded">Fallido</span>
                                    @break
                                @case('processing')
                                    <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded">Procesando</span>
                                    @break
                                @case('received')
                                    <span class="px-2 py-1 text-xs bg-blue-100 text-blue-700 rounded">Recibido</span>
                                    @break
                                @default
                                    <span class="px-2 py-1 text-xs bg-gray-200 text-gray-700 rounded">{{ $log->status }}</span>
                            @endswitch
                        </td>

                        <td class="px-4 py-3">
                            @if($log->errors->count() > 0)
                                <span class="px-2 py-1 text-xs bg-red-100 text-red-700 rounded">
                                    {{ $log->errors->count() }}
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded">0</span>
                            @endif
                        </td>

                        <td class="px-4 py-3">
                            {{ $log->received_at ? $log->received_at->format('d/m/Y H:i') : '-' }}
                        </td>

                        <td class="px-4 py-3">
                            {{ $log->uploaded_at ? $log->uploaded_at->format('d/m/Y H:i') : '-' }}
                        </td>

                        <td class="px-4 py-3">
                            <a href="{{ route('file-management.log-detail', $log->id) }}"
                               class="px-3 py-1 text-xs bg-primary-500 text-white rounded hover:bg-primary-600">
                                Ver
                            </a>
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-6 text-gray-500">
                            No se encontraron logs
                        </td>
                    </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

        <!-- Paginación -->
        <div class="px-6 py-4">
            {{ $logs->links() }}
        </div>

    </div>

</div>

@endsection
