@extends('layout.layout_admin')

@section('title', 'Errores de Archivos')

@section('contenido')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Errores de Archivos</h1>
        </div>
    </div>

    <!-- Menú de navegación -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="btn-group" role="group">
                <a href="{{ route('file-management.index') }}" class="btn btn-outline-primary">Dashboard</a>
                <a href="{{ route('file-management.companies') }}" class="btn btn-outline-primary">Empresas & FTP</a>
                <a href="{{ route('file-management.logs') }}" class="btn btn-outline-primary">Logs de Archivos</a>
                <a href="{{ route('file-management.errors') }}" class="btn btn-primary active">Errores</a>
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
                    <form method="GET" action="{{ route('file-management.errors') }}">
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
                                    <label for="severity" class="form-label">Severidad</label>
                                    <select class="form-select" id="severity" name="severity">
                                        <option value="">Todas</option>
                                        <option value="low" {{ request('severity') == 'low' ? 'selected' : '' }}>Baja</option>
                                        <option value="medium" {{ request('severity') == 'medium' ? 'selected' : '' }}>Media</option>
                                        <option value="high" {{ request('severity') == 'high' ? 'selected' : '' }}>Alta</option>
                                        <option value="critical" {{ request('severity') == 'critical' ? 'selected' : '' }}>Crítica</option>
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
                                <a href="{{ route('file-management.errors') }}" class="btn btn-secondary">
                                    <i class="fa fa-times"></i> Limpiar
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Errores -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Errores Registrados ({{ $errorsData->total() }} registros)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Empresa</th>
                                    <th>Tipo</th>
                                    <th>Tipo de Error</th>
                                    <th>Mensaje</th>
                                    <th>Línea</th>
                                    <th>Severidad</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($errorsData as $error)
                                <tr>
                                    <td>{{ $error->id }}</td>
                                    <td>
                                        <strong>{{ $error->company->code }}</strong><br>
                                        <small>{{ $error->company->name }}</small>
                                    </td>
                                    <td>{{ $error->fileType->name }}</td>
                                    <td><code>{{ $error->error_type }}</code></td>
                                    <td>
                                        <span title="{{ $error->error_message }}">
                                            {{ Str::limit($error->error_message, 50) }}
                                        </span>
                                        @if($error->error_details)
                                            <br><small class="text-muted">{{ Str::limit($error->error_details, 40) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($error->line_number)
                                            <span class="badge bg-secondary">{{ $error->line_number }}</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($error->severity === 'critical')
                                            <span class="badge bg-danger">Crítica</span>
                                        @elseif($error->severity === 'high')
                                            <span class="badge bg-warning text-dark">Alta</span>
                                        @elseif($error->severity === 'medium')
                                            <span class="badge bg-info">Media</span>
                                        @else
                                            <span class="badge bg-secondary">Baja</span>
                                        @endif
                                    </td>
                                    <td>{{ $error->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center">No se encontraron errores</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $errorsData->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
