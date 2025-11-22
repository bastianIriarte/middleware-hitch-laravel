@extends('layout.layout_admin')

@section('title', 'Logs de Archivos')

@section('contenido')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Logs de Archivos</h1>
        </div>
    </div>

    <!-- Menú de navegación -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="btn-group" role="group">
                <a href="{{ route('file-management.index') }}" class="btn btn-outline-primary">Dashboard</a>
                <a href="{{ route('file-management.companies') }}" class="btn btn-outline-primary">Empresas & FTP</a>
                <a href="{{ route('file-management.logs') }}" class="btn btn-primary active">Logs de Archivos</a>
                <a href="{{ route('file-management.errors') }}" class="btn btn-outline-primary">Errores</a>
                <a href="{{ route('file-management.stats') }}" class="btn btn-outline-primary">Estadísticas</a>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Filtros</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('file-management.logs') }}">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="company_id" class="form-label">Empresa</label>
                                    <select class="form-select" id="company_id" name="company_id">
                                        <option value="">Todas</option>
                                        @foreach($companies as $company)
                                            <option value="{{ $company->id }}"
                                                {{ request('company_id') == $company->id ? 'selected' : '' }}>
                                                {{ $company->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="file_type_id" class="form-label">Tipo de Archivo</label>
                                    <select class="form-select" id="file_type_id" name="file_type_id">
                                        <option value="">Todos</option>
                                        @foreach($fileTypes as $type)
                                            <option value="{{ $type->id }}"
                                                {{ request('file_type_id') == $type->id ? 'selected' : '' }}>
                                                {{ $type->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Estado</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="">Todos</option>
                                        <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>Recibido</option>
                                        <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Procesando</option>
                                        <option value="uploaded" {{ request('status') == 'uploaded' ? 'selected' : '' }}>Subido</option>
                                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Fallido</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label for="date_from" class="form-label">Desde</label>
                                    <input type="date" class="form-control" id="date_from" name="date_from"
                                           value="{{ request('date_from') }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label for="date_to" class="form-label">Hasta</label>
                                    <input type="date" class="form-control" id="date_to" name="date_to"
                                           value="{{ request('date_to') }}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-filter"></i> Filtrar
                                </button>
                                <a href="{{ route('file-management.logs') }}" class="btn btn-secondary">
                                    <i class="fa fa-times"></i> Limpiar
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Logs -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Logs de Archivos ({{ $logs->total() }} registros)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Empresa</th>
                                    <th>Tipo</th>
                                    <th>Archivo Original</th>
                                    <th>Tamaño</th>
                                    <th>Estado</th>
                                    <th>Registros</th>
                                    <th>Rechazados</th>
                                    <th>Errores</th>
                                    <th>Recibido</th>
                                    <th>Subido</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $log)
                                <tr>
                                    <td>{{ $log->id }}</td>
                                    <td>
                                        <strong>{{ $log->company->code }}</strong><br>
                                        <small>{{ $log->company->name }}</small>
                                    </td>
                                    <td>{{ $log->fileType->name }}</td>
                                    <td>
                                        <span title="{{ $log->original_filename }}">
                                            {{ Str::limit($log->original_filename, 30) }}
                                        </span>
                                    </td>
                                    <td>{{ number_format($log->file_size / 1024, 2) }} KB</td>
                                    <td>
                                        @if($log->status === 'uploaded')
                                            <span class="badge bg-success">Subido</span>
                                        @elseif($log->status === 'failed')
                                            <span class="badge bg-danger">Fallido</span>
                                        @elseif($log->status === 'processing')
                                            <span class="badge bg-warning text-dark">Procesando</span>
                                        @elseif($log->status === 'received')
                                            <span class="badge bg-info">Recibido</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $log->status }}</span>
                                        @endif
                                    </td>
                                    <td>{{ number_format($log->records_count) }}</td>
                                    <td>
                                        @if($log->rejected_count > 0)
                                            <span class="badge bg-warning text-dark">{{ number_format($log->rejected_count) }}</span>
                                        @else
                                            {{ number_format($log->rejected_count) }}
                                        @endif
                                    </td>
                                    <td>
                                        @if($log->errors->count() > 0)
                                            <span class="badge bg-danger">{{ $log->errors->count() }}</span>
                                        @else
                                            <span class="badge bg-success">0</span>
                                        @endif
                                    </td>
                                    <td>{{ $log->received_at ? $log->received_at->format('d/m/Y H:i') : '-' }}</td>
                                    <td>{{ $log->uploaded_at ? $log->uploaded_at->format('d/m/Y H:i') : '-' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="11" class="text-center">No se encontraron logs</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $logs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
