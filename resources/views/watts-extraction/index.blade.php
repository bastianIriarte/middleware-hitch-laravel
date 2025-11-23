@extends('layout.layout_admin')

@section('title', 'Extracciones Watts')

@section('contenido')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Extracciones Watts</h1>
            <p class="text-muted">Ejecuta las extracciones de datos desde el ERP hacia Watts</p>
        </div>
    </div>

    <!-- Estado del API -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Estado del API Watts</h5>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="checkApiHealth()">
                        <i class="fa fa-refresh"></i> Verificar
                    </button>
                </div>
                <div class="card-body">
                    <div id="apiHealthStatus">
                        @if($apiHealth['success'])
                            <div class="alert alert-success mb-0">
                                <i class="fa fa-check-circle"></i>
                                <strong>API Conectado</strong> - {{ $apiHealth['message'] }}
                            </div>
                        @else
                            <div class="alert alert-danger mb-0">
                                <i class="fa fa-times-circle"></i>
                                <strong>API No Disponible</strong> - {{ $apiHealth['message'] }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fa fa-check-circle"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fa fa-exclamation-triangle"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Configuración de Fechas -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Configuración de Extracción</h5>
                </div>
                <div class="card-body">
                    <form id="extractionConfigForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="start_date" class="form-label">Fecha Inicio (Sell Out)</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date"
                                           value="{{ $defaultConfig['startDate'] }}">
                                    <small class="form-text text-muted">Solo aplica para Sell Out</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="end_date" class="form-label">Fecha Fin (Sell Out)</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date"
                                           value="{{ $defaultConfig['endDate'] }}">
                                    <small class="form-text text-muted">Solo aplica para Sell Out</small>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Botones de Extracción Individual -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Extracciones Individuales</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <!-- Clientes -->
                        <div class="col-md-6 col-lg-3">
                            <div class="card text-center h-100">
                                <div class="card-body">
                                    <i class="fa fa-users fa-3x text-primary mb-3"></i>
                                    <h5 class="card-title">Clientes</h5>
                                    <p class="card-text small text-muted">Extrae maestro de clientes desde el ERP</p>
                                    <button type="button" class="btn btn-primary w-100" onclick="executeExtraction('customers')">
                                        <i class="fa fa-play"></i> Ejecutar
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Productos -->
                        <div class="col-md-6 col-lg-3">
                            <div class="card text-center h-100">
                                <div class="card-body">
                                    <i class="fa fa-cube fa-3x text-success mb-3"></i>
                                    <h5 class="card-title">Productos</h5>
                                    <p class="card-text small text-muted">Extrae maestro de productos desde el ERP</p>
                                    <button type="button" class="btn btn-success w-100" onclick="executeExtraction('products')">
                                        <i class="fa fa-play"></i> Ejecutar
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Vendedores -->
                        <div class="col-md-6 col-lg-3">
                            <div class="card text-center h-100">
                                <div class="card-body">
                                    <i class="fa fa-user-tie fa-3x text-info mb-3"></i>
                                    <h5 class="card-title">Vendedores</h5>
                                    <p class="card-text small text-muted">Extrae maestro de vendedores desde el ERP</p>
                                    <button type="button" class="btn btn-info w-100" onclick="executeExtraction('vendors')">
                                        <i class="fa fa-play"></i> Ejecutar
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Sell Out -->
                        <div class="col-md-6 col-lg-3">
                            <div class="card text-center h-100">
                                <div class="card-body">
                                    <i class="fa fa-chart-line fa-3x text-warning mb-3"></i>
                                    <h5 class="card-title">Sell Out</h5>
                                    <p class="card-text small text-muted">Extrae ventas en el rango de fechas seleccionado</p>
                                    <button type="button" class="btn btn-warning w-100" onclick="executeExtraction('sellout')">
                                        <i class="fa fa-play"></i> Ejecutar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Extracción Completa -->
    <div class="row">
        <div class="col-12">
            <div class="card border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fa fa-rocket"></i> Extracción Completa
                    </h5>
                </div>
                <div class="card-body">
                    <p class="mb-3">Ejecuta todas las extracciones en secuencia (Clientes, Productos, Vendedores y Sell Out)</p>
                    <button type="button" class="btn btn-lg btn-primary" onclick="executeAllExtractions()">
                        <i class="fa fa-play-circle"></i> Ejecutar Todas las Extracciones
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Información -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="alert alert-info">
                <h6><i class="fa fa-info-circle"></i> Información Importante</h6>
                <ul class="mb-0">
                    <li>Las extracciones se ejecutan en segundo plano mediante colas (queues)</li>
                    <li>El proceso puede tomar varios minutos dependiendo de la cantidad de datos</li>
                    <li>Las fechas solo aplican para la extracción de Sell Out</li>
                    <li>Los archivos generados se envían automáticamente al servidor FTP configurado</li>
                    <li>Puedes ver el progreso y errores en la sección de <a href="{{ route('file-management.logs') }}">Logs de Archivos</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Formularios ocultos para enviar las extracciones -->
<form id="executionForm" action="{{ route('watts-extraction.execute') }}" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="extraction_type" id="execution_type">
    <input type="hidden" name="start_date" id="execution_start_date">
    <input type="hidden" name="end_date" id="execution_end_date">
</form>

<form id="executeAllForm" action="{{ route('watts-extraction.execute-all') }}" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="start_date" id="all_start_date">
    <input type="hidden" name="end_date" id="all_end_date">
</form>
@endsection

@section('js_content')
<script nonce="{{ app('csp_nonce') }}">
    function executeExtraction(type) {
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;

        document.getElementById('execution_type').value = type;
        document.getElementById('execution_start_date').value = startDate;
        document.getElementById('execution_end_date').value = endDate;

        if (confirm(`¿Estás seguro de ejecutar la extracción de ${getExtractionName(type)}?`)) {
            document.getElementById('executionForm').submit();
        }
    }

    function executeAllExtractions() {
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;

        document.getElementById('all_start_date').value = startDate;
        document.getElementById('all_end_date').value = endDate;

        if (confirm('¿Estás seguro de ejecutar TODAS las extracciones? Este proceso puede tomar varios minutos.')) {
            document.getElementById('executeAllForm').submit();
        }
    }

    function getExtractionName(type) {
        const names = {
            'customers': 'Clientes',
            'products': 'Productos',
            'vendors': 'Vendedores',
            'sellout': 'Sell Out',
            'all': 'Todas'
        };
        return names[type] || type;
    }

    function checkApiHealth() {
        const statusDiv = document.getElementById('apiHealthStatus');
        statusDiv.innerHTML = '<div class="alert alert-info mb-0"><i class="fa fa-spinner fa-spin"></i> Verificando conexión...</div>';

        fetch('{{ route("watts-extraction.check-health") }}')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    statusDiv.innerHTML = `
                        <div class="alert alert-success mb-0">
                            <i class="fa fa-check-circle"></i>
                            <strong>API Conectado</strong> - ${data.message}
                        </div>
                    `;
                } else {
                    statusDiv.innerHTML = `
                        <div class="alert alert-danger mb-0">
                            <i class="fa fa-times-circle"></i>
                            <strong>API No Disponible</strong> - ${data.message}
                        </div>
                    `;
                }
            })
            .catch(error => {
                statusDiv.innerHTML = `
                    <div class="alert alert-danger mb-0">
                        <i class="fa fa-times-circle"></i>
                        <strong>Error</strong> - No se pudo verificar el estado del API
                    </div>
                `;
            });
    }
</script>
@endsection
