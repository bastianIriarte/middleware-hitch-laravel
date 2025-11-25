@extends('layout.layout_tailwind')

@section('title', 'Configurar FTP – ' . $company->name)

@section('contenido')

<div class="space-y-10 max-w-6xl mx-auto">

    <!-- Header -->
    <div class="space-y-1">
        <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Configuración FTP</h1>
        <p class="text-gray-500">Empresa: <strong>{{ $company->name }}</strong> ({{ $company->code }})</p>
    </div>

    <!-- Back -->
    <div>
        <a href="{{ route('file-management.companies') }}"
           class="inline-flex items-center gap-2 px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-100">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Volver a Empresas
        </a>
    </div>

    <!-- Errores -->
    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl">
            <strong>Corrige los siguientes errores:</strong>
            <ul class="mt-2 text-sm list-disc ml-6">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <!-- FORMULARIO -->
        <div class="lg:col-span-2">
            <div class="bg-white border shadow rounded-2xl overflow-hidden">
                <header class="px-6 py-4 border-b bg-gray-50 text-lg font-semibold text-gray-700">
                    Configuración de Servidor FTP
                </header>

                <div class="p-6 space-y-6">
                    <form action="{{ route('file-management.ftp-save') }}" method="POST" id="ftpConfigForm">
                        @csrf
                        <input type="hidden" name="company_id" value="{{ $company->id }}">

                        <div class="space-y-2">
                            <label for="protocol" class="block text-sm font-medium text-gray-700">Protocolo *</label>
                            <select id="protocol" name="protocol"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white"
                                required>
                                <option value="ftp" {{ old('protocol', $ftpConfig->protocol ?? 'ftp') == 'ftp' ? 'selected' : '' }}>FTP</option>
                                <option value="sftp" {{ old('protocol', $ftpConfig->protocol ?? 'ftp') == 'sftp' ? 'selected' : '' }}>SFTP (SSH)</option>
                            </select>
                            <p class="text-xs text-gray-400">FTP usa puerto 21, SFTP usa 22</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label for="host" class="block text-sm font-medium text-gray-700">Host / IP *</label>
                                <input type="text" name="host" id="host"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white"
                                    value="{{ old('host', $ftpConfig->host ?? '') }}" required>
                            </div>

                            <div class="space-y-2">
                                <label for="port" class="block text-sm font-medium text-gray-700">Puerto *</label>
                                <input type="number" name="port" id="port"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white"
                                    value="{{ old('port', $ftpConfig->port ?? 21) }}" required>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label for="username" class="block text-sm font-medium text-gray-700">Usuario *</label>
                                <input type="text" name="username" id="username"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white"
                                    value="{{ old('username', $ftpConfig->username ?? '') }}" required>
                            </div>

                            <div class="space-y-2">
                                <label for="password" class="block text-sm font-medium text-gray-700">Contraseña *</label>
                                <input type="password" name="password" id="password"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white"
                                    placeholder="{{ $ftpConfig ? '****** (dejar vacío para mantener)' : '' }}"
                                    {{ $ftpConfig ? '' : 'required' }}>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label for="root_path" class="block text-sm font-medium text-gray-700">Ruta Raíz</label>
                            <input type="text" name="root_path" id="root_path"
                                value="{{ old('root_path', $ftpConfig->root_path ?? '/') }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white">
                            <p class="text-xs text-gray-400">Ej: /carpeta/archivos</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label for="timeout" class="block text-sm font-medium text-gray-700">Timeout (segundos)</label>
                                <input type="number" name="timeout" id="timeout"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white"
                                    value="{{ old('timeout', $ftpConfig->timeout ?? 30) }}">
                            </div>

                            <div class="space-y-2">
                                <label class="text-sm font-medium text-gray-700">Opciones</label>

                                <label class="flex items-center gap-2">
                                    <input type="checkbox" id="ssl" name="ssl" value="1"
                                           class="rounded border-gray-300"
                                           {{ old('ssl', $ftpConfig->ssl ?? false) ? 'checked' : '' }}>
                                    <span class="text-sm">Usar SSL/TLS</span>
                                </label>

                                <label class="flex items-center gap-2">
                                    <input type="checkbox" id="passive" name="passive" value="1"
                                           class="rounded border-gray-300"
                                           {{ old('passive', $ftpConfig->passive ?? true) ? 'checked' : '' }}>
                                    <span class="text-sm">Modo Pasivo</span>
                                </label>

                                <label class="flex items-center gap-2">
                                    <input type="checkbox" id="status" name="status" value="1"
                                           class="rounded border-gray-300"
                                           {{ old('status', $ftpConfig->status ?? true) ? 'checked' : '' }}>
                                    <span class="text-sm">Habilitado</span>
                                </label>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit"
                                class="w-full py-3 bg-primary-600 text-white rounded-lg hover:bg-primary-700 flex items-center justify-center gap-2">
                                <i data-lucide="save" class="w-5 h-5"></i>
                                Guardar Configuración
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        <!-- PANEL LATERAL -->
        <div class="space-y-6">

            <!-- Estado -->
            <div class="bg-white border shadow rounded-2xl p-6">
                <h3 class="text-lg font-semibold text-gray-700 mb-3">Estado Actual</h3>

                @if($ftpConfig)
                    @if($ftpConfig->status)
                        <span class="px-3 py-1 text-sm rounded bg-green-100 text-green-700">FTP Activo</span>
                    @else
                        <span class="px-3 py-1 text-sm rounded bg-yellow-100 text-yellow-700">FTP Deshabilitado</span>
                    @endif
                @else
                    <span class="px-3 py-1 text-sm rounded bg-gray-200 text-gray-700">Sin Configurar</span>
                @endif

                <div class="mt-6">
                    <p class="text-sm text-gray-600">Última actualización:</p>
                    <p class="text-gray-800 font-medium mt-1">
                        @if($ftpConfig && $ftpConfig->updated_at)
                            {{ $ftpConfig->updated_at->format('d/m/Y H:i') }}
                        @else
                            <em>Nunca</em>
                        @endif
                    </p>
                </div>

                @if($ftpConfig)
                <div class="mt-6">
                    <a href="{{ route('file-management.test-ftp', $company->id) }}"
                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        <i data-lucide="check-circle" class="w-5 h-5"></i>
                        Probar Conexión
                    </a>
                </div>
                @endif
            </div>

            <!-- Ayuda -->
            <div class="bg-white border shadow rounded-2xl p-6">
                <h3 class="text-lg font-semibold text-gray-700 mb-3">Ayuda</h3>

                <ul class="text-sm text-gray-600 space-y-1">
                    <li><strong>Host:</strong> IP o dominio del servidor</li>
                    <li><strong>Puerto:</strong> Usualmente 21 (FTP)</li>
                    <li><strong>SSL/TLS:</strong> Para FTPS</li>
                    <li><strong>Modo Pasivo:</strong> Recomendado detrás de firewalls</li>
                </ul>
            </div>
        </div>

    </div>
</div>

@endsection

@section('js_content')
<script nonce="{{ app('csp_nonce') }}">
    document.addEventListener('DOMContentLoaded', function() {

        const protocol = document.getElementById('protocol');
        const port = document.getElementById('port');
        const ssl = document.getElementById('ssl');
        const passive = document.getElementById('passive');

        function updateProtocolUI() {
            if (protocol.value === 'sftp') {
                port.value = 22;
                ssl.checked = false;
                passive.checked = false;

                ssl.disabled = true;
                passive.disabled = true;
            } else {
                port.value = 21;
                ssl.disabled = false;
                passive.disabled = false;
                passive.checked = true;
            }
        }

        protocol.addEventListener('change', updateProtocolUI);
        updateProtocolUI();
    });
</script>
@endsection
