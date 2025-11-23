@extends('layout.layout_tailwind')

@section('title', "Detalle del Log #$log->id")

@section('contenido')

<div class="space-y-10">

    <!-- Header Principal -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Detalle del Log #{{ $log->id }}</h1>
            <p class="text-gray-500 mt-1">
                {{ $log->company->name }} • {{ $log->fileType->name }}
            </p>
        </div>

        <a href="{{ route('file-management.logs') }}"
           class="px-4 py-2 border rounded-lg hover:bg-gray-50 text-gray-600">
            ← Volver
        </a>
    </div>

    <!-- Estado -->
    <div class="bg-white border rounded-xl px-6 py-4">

        @switch($log->status)

            @case('uploaded')
                <div class="flex items-center gap-3 text-green-700">
                    <span class="w-3 h-3 bg-green-500 rounded-full"></span>
                    <span class="font-medium">Archivo subido correctamente</span>
                </div>
                @break

            @case('failed')
                <div class="flex items-center gap-3 text-red-700">
                    <span class="w-3 h-3 bg-red-500 rounded-full"></span>
                    <span class="font-medium">Error en el procesamiento</span>
                </div>
                @break

            @case('processing')
                <div class="flex items-center gap-3 text-yellow-700">
                    <span class="w-3 h-3 bg-yellow-400 rounded-full animate-pulse"></span>
                    <span class="font-medium">Procesando archivo…</span>
                </div>
                @break

            @default
                <div class="flex items-center gap-3 text-blue-700">
                    <span class="w-3 h-3 bg-blue-500 rounded-full"></span>
                    <span class="font-medium">Archivo recibido</span>
                </div>
        @endswitch

    </div>


    <!-- Información General + Archivo -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Panel -->
        <div class="bg-white border rounded-xl p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Información General</h2>

            <div class="space-y-5">

                <div>
                    <p class="text-sm text-gray-500">ID del Log</p>
                    <p class="font-medium text-gray-800">#{{ $log->id }}</p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">Empresa</p>
                    <p class="font-medium">{{ $log->company->code }}</p>
                    <p class="text-gray-500 text-sm">{{ $log->company->name }}</p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">Tipo de Archivo</p>
                    <p class="font-medium">{{ $log->fileType->name }}</p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">Creado el</p>
                    <p class="font-medium">{{ $log->created_at->format('d/m/Y H:i:s') }}</p>
                    <p class="text-gray-500 text-sm">{{ $log->created_at->diffForHumans() }}</p>
                </div>

            </div>
        </div>

        <!-- Panel archivo -->
        <div class="bg-white border rounded-xl p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Archivo</h2>

            <div class="space-y-5">

                <div>
                    <p class="text-sm text-gray-500">Nombre original</p>
                    <p class="font-medium break-all">{{ $log->original_filename }}</p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">Nombre almacenado</p>
                    <p class="font-medium">{{ $log->stored_filename ?? 'No almacenado' }}</p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">Ruta</p>
                    <p class="text-gray-600 break-all">{{ $log->file_path ?? 'No disponible' }}</p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">Tamaño</p>
                    @if($log->file_size)
                        <p class="font-medium">{{ number_format($log->file_size / 1024, 2) }} KB</p>
                    @else
                        <p class="text-gray-500">No disponible</p>
                    @endif
                </div>

            </div>
        </div>

    </div>

    <!-- Timeline -->
    <div class="bg-white border rounded-xl p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-6">Timeline del Proceso</h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <!-- Step -->
            <x-step :checked="$log->received_at" title="Recibido"
                    :date="$log->received_at" icon="inbox" />

            <x-step :checked="$log->status !== 'received'"
                    :loading="$log->status === 'processing'"
                    :date="$log->updated_at"
                    title="Procesado"
                    icon="cog" />

            <x-step :checked="$log->status === 'uploaded'"
                    :failed="$log->status === 'failed'"
                    :date="$log->uploaded_at"
                    title="{{ $log->status === 'failed' ? 'Fallido' : 'Subido' }}"
                    icon="{{ $log->status === 'failed' ? 'x-circle' : 'check-circle' }}" />

        </div>
    </div>


    <!-- Errores -->
    <div class="bg-white border rounded-xl p-6">

        <h2 class="text-lg font-semibold text-gray-800 mb-6">
            Errores Asociados
            <span class="ml-3 text-sm bg-red-100 text-red-700 px-2 py-1 rounded">
                {{ $log->errors->count() }}
            </span>
        </h2>

        @if($log->errors->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-100 text-gray-600 text-xs uppercase">
                        <tr>
                            <th class="px-4 py-2">ID</th>
                            <th class="px-4 py-2">Tipo</th>
                            <th class="px-4 py-2">Mensaje</th>
                            <th class="px-4 py-2">Línea</th>
                            <th class="px-4 py-2">Severidad</th>
                            <th class="px-4 py-2">Fecha</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y">
                        @foreach($log->errors as $error)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 font-medium">#{{ $error->id }}</td>
                            <td class="px-4 py-2">{{ $error->error_type }}</td>
                            <td class="px-4 py-2">{{ Str::limit($error->error_message, 50) }}</td>
                            <td class="px-4 py-2">{{ $error->line_number ?? '-' }}</td>
                            <td class="px-4 py-2">
                                <x-badges.severity :level="$error->severity" />
                            </td>
                            <td class="px-4 py-2 text-xs">
                                {{ $error->created_at->format('d/m/Y H:i') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>

        @else

            <div class="text-center py-10">
                <i data-lucide="check-circle" class="w-12 h-12 text-green-500 mx-auto"></i>
                <h4 class="text-green-600 text-xl mt-2">Sin errores</h4>
                <p class="text-gray-500">Este archivo se procesó correctamente.</p>
            </div>

        @endif

    </div>

</div>

@endsection
