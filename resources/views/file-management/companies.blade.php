@extends('layout.layout_admin')

@section('title', 'Empresas y Configuración FTP')

@section('contenido')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Empresas y Configuración FTP</h1>
        </div>
    </div>

    <!-- Menú de navegación -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="btn-group" role="group">
                <a href="{{ route('file-management.index') }}" class="btn btn-outline-primary">Dashboard</a>
                <a href="{{ route('file-management.companies') }}" class="btn btn-primary active">Empresas & FTP</a>
                <a href="{{ route('file-management.logs') }}" class="btn btn-outline-primary">Logs de Archivos</a>
                <a href="{{ route('file-management.errors') }}" class="btn btn-outline-primary">Errores</a>
                <a href="{{ route('file-management.stats') }}" class="btn btn-outline-primary">Estadísticas</a>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Tabla de Empresas -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Empresas Registradas</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Código</th>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Teléfono</th>
                                    <th>Estado</th>
                                    <th>Archivos</th>
                                    <th>Errores</th>
                                    <th>FTP</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($companies as $company)
                                <tr>
                                    <td>{{ $company->id }}</td>
                                    <td><strong>{{ $company->code }}</strong></td>
                                    <td>{{ $company->name }}</td>
                                    <td>{{ $company->email }}</td>
                                    <td>{{ $company->phone }}</td>
                                    <td>
                                        @if($company->status)
                                            <span class="badge bg-success">Activa</span>
                                        @else
                                            <span class="badge bg-secondary">Inactiva</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $company->file_logs_count }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-danger">{{ $company->file_errors_count }}</span>
                                    </td>
                                    <td>
                                        @if($company->ftpConfig)
                                            @if($company->ftpConfig->status)
                                                <span class="badge bg-success">Configurado</span>
                                            @else
                                                <span class="badge bg-warning">Deshabilitado</span>
                                            @endif
                                        @else
                                            <span class="badge bg-secondary">Sin configurar</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('file-management.ftp-config', $company->id) }}"
                                               class="btn btn-sm btn-primary" title="Configurar FTP">
                                                <i class="fa fa-cog"></i> FTP
                                            </a>
                                            @if($company->ftpConfig)
                                            <a href="{{ route('file-management.test-ftp', $company->id) }}"
                                               class="btn btn-sm btn-success" title="Probar conexión FTP">
                                                <i class="fa fa-check"></i> Test
                                            </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center">No hay empresas registradas</td>
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
