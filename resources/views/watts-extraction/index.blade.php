@extends('layout.layout_tailwind')

@section('title', 'Extracciones Watts')

@section('contenido')

    <div class="space-y-10">

        <!-- Header -->
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Extracciones Watts</h1>
            <p class="text-gray-500 mt-1">Ejecuta extracciones desde el ERP hacia Watts</p>
        </div>

        <!-- Navigation Tabs -->
        <x-navigation />
        
        <!-- Estado del API -->
        <div class="bg-white rounded-xl border shadow">
            <div class="flex justify-between items-center px-6 py-4 border-b">
                <h2 class="text-lg font-semibold text-gray-700">Estado del API Watts</h2>

                <button onclick="checkApiHealth()"
                    class="px-3 py-1 text-sm border rounded-lg text-primary-700 hover:bg-primary-50">
                    <i data-lucide="refresh-cw" class="w-4 h-4 inline"></i> Verificar
                </button>
            </div>

            <div class="p-6" id="apiHealthStatus">
                @if ($apiHealth['success'])
                    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                        <i data-lucide="check-circle" class="w-5 h-5 inline mr-1"></i>
                        <strong>API Conectado</strong> – {{ $apiHealth['message'] }}
                    </div>
                @else
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                        <i data-lucide="x-circle" class="w-5 h-5 inline mr-1"></i>
                        <strong>API No Disponible</strong> – {{ $apiHealth['message'] }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Alerts -->
        @if (session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                <i data-lucide="check-circle" class="w-4 h-4 inline"></i>
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                <i data-lucide="alert-triangle" class="w-4 h-4 inline"></i>
                {{ session('error') }}
            </div>
        @endif

        <!-- Configuración -->
        <div class="bg-white border shadow rounded-xl">
            <div class="px-6 py-4 border-b font-semibold text-gray-700">
                Configuración de Extracción
            </div>

            <div class="p-6">
                <form id="extractionConfigForm" class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <div>
                        <label class="text-sm font-medium text-gray-600">Fecha Inicio (Sell Out)</label>
                        <input type="date" id="start_date" name="start_date" value="{{ $defaultConfig['startDate'] }}"
                            class="mt-1 w-full rounded-lg border-gray-300 focus:ring-primary-500">
                        <p class="text-xs text-gray-400 mt-1">Solo aplica para Sell Out</p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-600">Fecha Fin (Sell Out)</label>
                        <input type="date" id="end_date" name="end_date" value="{{ $defaultConfig['endDate'] }}"
                            class="mt-1 w-full rounded-lg border-gray-300 focus:ring-primary-500">
                        <p class="text-xs text-gray-400 mt-1">Solo aplica para Sell Out</p>
                    </div>

                </form>
            </div>
        </div>

        <!-- Extracciones Individuales -->
        <div class="bg-white border shadow rounded-xl">
            <div class="px-6 py-4 border-b font-semibold text-gray-700">
                Extracciones Individuales
            </div>

            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

                <!-- CARD COMPONENT -->
                @php
                    $cards = [
                        [
                            'title' => 'Clientes',
                            'icon' => 'users',
                            'color' => 'text-primary-600',
                            'type' => 'customers',
                            'desc' => 'Extrae maestro de clientes desde el ERP',
                        ],
                        [
                            'title' => 'Productos',
                            'icon' => 'package',
                            'color' => 'text-green-600',
                            'type' => 'products',
                            'desc' => 'Extrae maestro de productos desde el ERP',
                        ],
                        [
                            'title' => 'Vendedores',
                            'icon' => 'user',
                            'color' => 'text-blue-600',
                            'type' => 'vendors',
                            'desc' => 'Extrae maestro de vendedores desde el ERP',
                        ],
                        [
                            'title' => 'Sell Out',
                            'icon' => 'chart-line',
                            'color' => 'text-yellow-600',
                            'type' => 'sellout',
                            'desc' => 'Extrae ventas dentro del rango seleccionado',
                        ],
                    ];
                @endphp

                @foreach ($cards as $c)
                    <div class="border rounded-xl p-6 shadow-sm text-center space-y-3 hover:shadow-md transition">
                        <i data-lucide="{{ $c['icon'] }}" class="w-10 h-10 mx-auto {{ $c['color'] }}"></i>

                        <h3 class="font-semibold text-gray-800 text-lg">{{ $c['title'] }}</h3>

                        <p class="text-gray-500 text-sm">{{ $c['desc'] }}</p>

                        <button onclick="executeExtraction('{{ $c['type'] }}')"
                            class="w-full py-2 rounded-lg bg-primary-600 text-white hover:bg-primary-700">
                            <i data-lucide="play" class="w-4 h-4 inline mr-1"></i> Ejecutar
                        </button>
                    </div>
                @endforeach

            </div>
        </div>

        <!-- Extracción Completa -->
        <div class="bg-white border shadow rounded-xl">
            <div class="px-6 py-4 bg-primary-600 text-white rounded-t-xl font-semibold">
                <i data-lucide="rocket" class="w-5 h-5 inline mr-1"></i>
                Extracción Completa
            </div>

            <div class="p-6">
                <p class="text-gray-600 mb-4">
                    Ejecuta todas las extracciones: Clientes, Productos, Vendedores y Sell Out
                </p>

                <button onclick="executeAllExtractions()"
                    class="px-6 py-3 rounded-lg bg-primary-600 text-white hover:bg-primary-700">
                    <i data-lucide="play-circle" class="w-5 h-5 inline mr-1"></i>
                    Ejecutar Todas
                </button>
            </div>
        </div>

        <!-- Info -->
        <div class="bg-blue-50 border border-blue-200 text-blue-700 px-6 py-5 rounded-xl">
            <h4 class="font-semibold mb-3 flex items-center gap-2">
                <i data-lucide="info"></i> Información Importante
            </h4>

            <ul class="space-y-1 text-sm">
                <li>• Las extracciones se ejecutan en segundo plano mediante queues</li>
                <li>• El proceso puede tomar varios minutos</li>
                <li>• Las fechas aplican solo al Sell Out</li>
                <li>• Los archivos generados se envían automáticamente al FTP</li>
                <li>• Puedes ver los logs en:
                    <a href="{{ route('file-management.logs') }}" class="text-primary-600 font-medium hover:underline">
                        Logs de Archivos
                    </a>
                </li>
            </ul>
        </div>

    </div>

    <!-- Formularios ocultos -->
    <form id="executionForm" action="{{ route('watts-extraction.execute') }}" method="POST" class="hidden">
        @csrf
        <input type="hidden" id="execution_type" name="extraction_type">
        <input type="hidden" id="execution_start_date" name="start_date">
        <input type="hidden" id="execution_end_date" name="end_date">
    </form>

    <form id="executeAllForm" action="{{ route('watts-extraction.execute-all') }}" method="POST" class="hidden">
        @csrf
        <input type="hidden" id="all_start_date" name="start_date">
        <input type="hidden" id="all_end_date" name="end_date">js
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
            statusDiv.innerHTML =
                '<div class="alert alert-info mb-0"><i class="fa fa-spinner fa-spin"></i> Verificando conexión...</div>';

            fetch('{{ route('watts-extraction.check-health') }}')
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
@endsection-
