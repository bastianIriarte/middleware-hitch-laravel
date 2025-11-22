@extends('layout.layout_admin')

@section('title', 'Estadísticas de Archivos')

@section('contenido')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Estadísticas de Archivos</h1>
        </div>
    </div>

    <!-- Menú de navegación -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="btn-group" role="group">
                <a href="{{ route('file-management.index') }}" class="btn btn-outline-primary">Dashboard</a>
                <a href="{{ route('file-management.companies') }}" class="btn btn-outline-primary">Empresas & FTP</a>
                <a href="{{ route('file-management.logs') }}" class="btn btn-outline-primary">Logs de Archivos</a>
                <a href="{{ route('file-management.errors') }}" class="btn btn-outline-primary">Errores</a>
                <a href="{{ route('file-management.stats') }}" class="btn btn-primary active">Estadísticas</a>
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
                    <form method="GET" action="{{ route('file-management.stats') }}">
                        <div class="row">
                            <div class="col-md-5">
                                <div class="mb-3">
                                    <label for="company_id" class="form-label">Empresa</label>
                                    <select class="form-select" id="company_id" name="company_id">
                                        <option value="">Todas las empresas</option>
                                        @foreach($companies as $company)
                                            <option value="{{ $company->id }}"
                                                {{ request('company_id') == $company->id ? 'selected' : '' }}>
                                                {{ $company->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="mb-3">
                                    <label for="file_type_id" class="form-label">Tipo de Archivo</label>
                                    <select class="form-select" id="file_type_id" name="file_type_id">
                                        <option value="">Todos los tipos</option>
                                        @foreach($fileTypes as $type)
                                            <option value="{{ $type->id }}"
                                                {{ request('file_type_id') == $type->id ? 'selected' : '' }}>
                                                {{ $type->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fa fa-filter"></i> Filtrar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card text-white bg-info">
                <div class="card-body text-center">
                    <h6 class="card-title">Total</h6>
                    <p class="display-5 mb-0">{{ number_format($stats['total']) }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-white bg-secondary">
                <div class="card-body text-center">
                    <h6 class="card-title">Recibidos</h6>
                    <p class="display-5 mb-0">{{ number_format($stats['received']) }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-white bg-warning">
                <div class="card-body text-center">
                    <h6 class="card-title">Procesando</h6>
                    <p class="display-5 mb-0">{{ number_format($stats['processing']) }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-white bg-success">
                <div class="card-body text-center">
                    <h6 class="card-title">Subidos</h6>
                    <p class="display-5 mb-0">{{ number_format($stats['uploaded']) }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-white bg-danger">
                <div class="card-body text-center">
                    <h6 class="card-title">Fallidos</h6>
                    <p class="display-5 mb-0">{{ number_format($stats['failed']) }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <h6 class="card-title text-danger">Errores</h6>
                    <p class="display-5 mb-0 text-danger">{{ number_format($stats['total_errors']) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Logs Recientes -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Archivos Recientes</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Empresa</th>
                                    <th>Tipo</th>
                                    <th>Archivo</th>
                                    <th>Estado</th>
                                    <th>Registros</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentLogs as $log)
                                <tr>
                                    <td>{{ $log->id }}</td>
                                    <td>{{ $log->company->code }}</td>
                                    <td>{{ $log->fileType->name }}</td>
                                    <td>{{ Str::limit($log->original_filename, 40) }}</td>
                                    <td>
                                        @if($log->status === 'uploaded')
                                            <span class="badge bg-success">Subido</span>
                                        @elseif($log->status === 'failed')
                                            <span class="badge bg-danger">Fallido</span>
                                        @elseif($log->status === 'processing')
                                            <span class="badge bg-warning text-dark">Procesando</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $log->status }}</span>
                                        @endif
                                    </td>
                                    <td>{{ number_format($log->records_count) }}</td>
                                    <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">No hay archivos recientes</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
