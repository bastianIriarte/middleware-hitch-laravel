@extends('layout.layout_admin')

@section('title', 'Gestión de Archivos')

@section('contenido')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Gestión de Archivos</h1>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">Empresas</h5>
                    <p class="card-text display-4">{{ $stats['active_companies'] }}</p>
                    <small>{{ $stats['total_companies'] }} total</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h5 class="card-title">Tipos de Archivo</h5>
                    <p class="card-text display-4">{{ $stats['active_file_types'] }}</p>
                    <small>{{ $stats['total_file_types'] }} total</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">Archivos Subidos</h5>
                    <p class="card-text display-4">{{ $stats['files_uploaded'] }}</p>
                    <small>{{ $stats['total_files'] }} total</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-danger">
                <div class="card-body">
                    <h5 class="card-title">Errores</h5>
                    <p class="card-text display-4">{{ $stats['total_errors'] }}</p>
                    <small>{{ $stats['files_failed'] }} archivos fallidos</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Menú de navegación -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="btn-group" role="group">
                <a href="{{ route('file-management.index') }}" class="btn btn-primary active">Dashboard</a>
                <a href="{{ route('file-management.companies') }}" class="btn btn-outline-primary">Empresas & FTP</a>
                <a href="{{ route('file-management.logs') }}" class="btn btn-outline-primary">Logs de Archivos</a>
                <a href="{{ route('file-management.errors') }}" class="btn btn-outline-primary">Errores</a>
                <a href="{{ route('file-management.stats') }}" class="btn btn-outline-primary">Estadísticas</a>
            </div>
        </div>
    </div>

    <!-- Logs Recientes -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Logs Recientes</h5>
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
                                    <th>Rechazados</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentLogs as $log)
                                <tr>
                                    <td>{{ $log->id }}</td>
                                    <td>{{ $log->company->name }}</td>
                                    <td>{{ $log->fileType->name }}</td>
                                    <td>{{ $log->original_filename }}</td>
                                    <td>
                                        @if($log->status === 'uploaded')
                                            <span class="badge bg-success">Subido</span>
                                        @elseif($log->status === 'failed')
                                            <span class="badge bg-danger">Fallido</span>
                                        @elseif($log->status === 'processing')
                                            <span class="badge bg-warning">Procesando</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $log->status }}</span>
                                        @endif
                                    </td>
                                    <td>{{ number_format($log->records_count) }}</td>
                                    <td>{{ number_format($log->rejected_count) }}</td>
                                    <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center">No hay logs recientes</td>
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
