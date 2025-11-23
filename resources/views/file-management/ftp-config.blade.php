@extends('layout.layout_admin')

@section('title', 'Configuración FTP - ' . $company->name)

@section('contenido')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Configuración FTP</h1>
            <p class="lead">Empresa: <strong>{{ $company->name }}</strong> ({{ $company->code }})</p>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <a href="{{ route('file-management.companies') }}" class="btn btn-outline-secondary">
                <i class="fa fa-arrow-left"></i> Volver a Empresas
            </a>
        </div>
    </div>

    @if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5>Configuración de Servidor FTP</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('file-management.ftp-save') }}" method="POST" id="ftpConfigForm">
                        @csrf
                        <input type="hidden" name="company_id" value="{{ $company->id }}">

                        <div class="mb-3">
                            <label for="protocol" class="form-label">Protocolo *</label>
                            <select class="form-select" id="protocol" name="protocol" required>
                                <option value="ftp" {{ old('protocol', $ftpConfig->protocol ?? 'ftp') == 'ftp' ? 'selected' : '' }}>FTP</option>
                                <option value="sftp" {{ old('protocol', $ftpConfig->protocol ?? 'ftp') == 'sftp' ? 'selected' : '' }}>SFTP (SSH)</option>
                            </select>
                            <small class="form-text text-muted">FTP usa puerto 21, SFTP usa puerto 22</small>
                        </div>

                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="host" class="form-label">Host / IP *</label>
                                    <input type="text" class="form-control" id="host" name="host"
                                           value="{{ old('host', $ftpConfig->host ?? '') }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="port" class="form-label">Puerto *</label>
                                    <input type="number" class="form-control" id="port" name="port"
                                           value="{{ old('port', $ftpConfig->port ?? 21) }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="username" class="form-label">Usuario *</label>
                                    <input type="text" class="form-control" id="username" name="username"
                                           value="{{ old('username', $ftpConfig->username ?? '') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password" class="form-label">Contraseña *</label>
                                    <input type="password" class="form-control" id="password" name="password"
                                           placeholder="{{ $ftpConfig ? '****** (no cambiar si vacío)' : '' }}"
                                           {{ $ftpConfig ? '' : 'required' }}>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="root_path" class="form-label">Ruta Raíz</label>
                            <input type="text" class="form-control" id="root_path" name="root_path"
                                   value="{{ old('root_path', $ftpConfig->root_path ?? '/') }}"
                                   placeholder="/">
                            <small class="form-text text-muted">Directorio base donde se subirán los archivos</small>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="timeout" class="form-label">Timeout (segundos)</label>
                                    <input type="number" class="form-control" id="timeout" name="timeout"
                                           value="{{ old('timeout', $ftpConfig->timeout ?? 30) }}">
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">Opciones</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="ssl" name="ssl" value="1"
                                               {{ old('ssl', $ftpConfig->ssl ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="ssl">
                                            Usar SSL/TLS
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="passive" name="passive" value="1"
                                               {{ old('passive', $ftpConfig->passive ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="passive">
                                            Modo Pasivo
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="status" name="status" value="1"
                                               {{ old('status', $ftpConfig->status ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="status">
                                            Habilitado
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save"></i> Guardar Configuración
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Información</h5>
                </div>
                <div class="card-body">
                    <h6>Estado Actual</h6>
                    <p>
                        @if($ftpConfig)
                            @if($ftpConfig->status)
                                <span class="badge bg-success">FTP Configurado y Activo</span>
                            @else
                                <span class="badge bg-warning">FTP Configurado pero Deshabilitado</span>
                            @endif
                        @else
                            <span class="badge bg-secondary">Sin Configurar</span>
                        @endif
                    </p>

                    <hr>

                    <h6>Última Actualización</h6>
                    <p>
                        @if($ftpConfig && $ftpConfig->updated_at)
                            {{ $ftpConfig->updated_at->format('d/m/Y H:i') }}
                        @else
                            <em>Nunca</em>
                        @endif
                    </p>

                    @if($ftpConfig)
                    <hr>
                    <div class="d-grid">
                        <a href="{{ route('file-management.test-ftp', $company->id) }}"
                           class="btn btn-success">
                            <i class="fa fa-check-circle"></i> Probar Conexión
                        </a>
                    </div>
                    @endif
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5>Ayuda</h5>
                </div>
                <div class="card-body">
                    <small>
                        <strong>Host:</strong> Dirección IP o dominio del servidor FTP.<br>
                        <strong>Puerto:</strong> Usualmente 21 para FTP estándar.<br>
                        <strong>SSL/TLS:</strong> Activar para conexión segura (FTPS).<br>
                        <strong>Modo Pasivo:</strong> Recomendado para servidores detrás de firewall.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js_content')
<script nonce="{{ app('csp_nonce') }}">
    document.addEventListener('DOMContentLoaded', function() {
        const protocolSelect = document.getElementById('protocol');
        const portInput = document.getElementById('port');
        const sslCheckbox = document.getElementById('ssl');
        const passiveCheckbox = document.getElementById('passive');

        // Cambiar puerto automáticamente al cambiar protocolo
        protocolSelect.addEventListener('change', function() {
            const protocol = this.value;

            if (protocol === 'sftp') {
                portInput.value = 22;
                // SFTP no usa SSL ni modo pasivo de la misma forma que FTP
                sslCheckbox.checked = false;
                passiveCheckbox.checked = false;
                sslCheckbox.disabled = true;
                passiveCheckbox.disabled = true;
            } else {
                portInput.value = 21;
                sslCheckbox.disabled = false;
                passiveCheckbox.disabled = false;
                passiveCheckbox.checked = true;
            }
        });

        // Inicializar estado al cargar
        if (protocolSelect.value === 'sftp') {
            sslCheckbox.disabled = true;
            passiveCheckbox.disabled = true;
        }
    });
</script>
@endsection
