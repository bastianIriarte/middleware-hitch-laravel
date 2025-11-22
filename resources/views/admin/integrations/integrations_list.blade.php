@extends('layout.layout_admin')
@section('contenido')
    <div class="content-wrapper">
        <div class="page-title">
            <i class="fa fa-database me-2"></i>{{ $title }}
        </div>
        <div class="mb-4">
            <!-- Tabla de Integraciones -->
            <div class="card p-2">
                <div class="card-header">
                    <h5 class="mb-0">{{ $title_table }}</h5>
                </div>
                <div style="margin-bottom: 30px; !important"></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <div class="text-center" id="div_spinner">Cargando...<br>
                            <div class="d-flex justify-content-center mt-2">
                                <div class="spinner-border spinner-border-md" role="status">
                                    <span class="sr-only">Cargando...</span>
                                </div>
                            </div>
                        </div>
                        <table class="table table-hover" id="table_list" hidden>
                            <thead class="table-dark">
                                <tr class="font-13">
                                    <th class="text-center">ID</th>
                                    <th class="text-center">Servicio</th>
                                    <th class="text-center">Origen</th>
                                    <th class="text-center">Destino</th>
                                    <th class="text-center">Código</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Fecha</th>
                                    <th class="text-center">Última Actualización</th>
                                    {{-- <th class="text-center">Intentos</th> --}}
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($list_data as $key => $l)
                                @php
                                    $origin = $l->origin;
                                    if($resource->name == 'Boletas'){
                                        $origin = 'POS';
                                    }
                                @endphp
                                    <tr id="fila_{{ $l->id }}" class="font-13">
                                        <td class="text-center">{{ $l->id  }}</td>
                                        <td class="text-center">{{ !empty($l->service_name) ? $l->service_name : 'Sin información' }}</td>
                                        <td class="text-center">{{ !empty($origin) ? $origin : 'Sin información' }}</td>
                                        <td class="text-center">{{ !empty($l->destiny) ? $l->destiny : 'Sin información' }}</td>
                                        <td class="text-center">{{ !empty($l->code) ? $l->code : '-' }}</td>
                                        <td class="text-center">
                                                <span class="badge status-badge  {{ !empty($l->status->badge) ? $l->status->badge : '' }}">
                                                    <i class="fa {{ !empty($l->status->icon) ? $l->status->icon : '' }} me-1"></i>{{ !empty($l->status->status) ? $l->status->status : 'Estado Desconocido' }}
                                                </span>
                                        </td>
                                        <td class="text-center">
                                            {{ !empty($l->created_at) ? ordenar_fechaHoraMinutoHumano($l->created_at) : 'Sin información' }}
                                        </td>
                                        <td class="text-center">
                                            {{ !empty($l->updated_at) ? ordenar_fechaHoraMinutoHumano($l->updated_at) : ordenar_fechaHoraMinutoHumano($l->created_at) }}
                                        </td>
                                        {{-- <td class="text-center">{{ $l->attempts }}</td> --}}
                                        <td class="table-actions text-center">
                                            <button class="btn btn-sm btn-outline-info me-1 btn-view-detail" 
                                                data-id="{{ $l->id }}"
                                                data-resource-name="{{ $resource->name }}"
                                                data-service="{{ $l->service_name }}"
                                                data-origin="{{ $origin }}"
                                                data-destiny="{{ $l->destiny }}"
                                                data-badge="{{ $l->status->badge }}"
                                                data-icon="{{ $l->status->icon }}"
                                                data-status-text="{{ $l->status->status }}"
                                                data-attempts="{{ $l->attempts }}"
                                                data-created-at="{{ $l->created_at }}"
                                                data-updated-at="{{ $l->updated_at }}"
                                                data-code="{{ $l->code }}"
                                                data-message="{{ $l->message }}"
                                                data-create-body="{{ $l->create_body }}"
                                                data-request-body="{{ $l->request_body }}"
                                                data-response="{{ $l->response }}"
                                                data-entry-request-body="{{ $l->entry_request_body ?? '' }}"
                                                data-entry-response="{{ $l->entry_response ?? '' }}"
                                                data-user-created="{{ !empty($l->createdBy) ? $l->createdBy->name : 'Sin información' }}"
                                                data-user-updated="{{ !empty($l->updatedBy) ? $l->updatedBy->name : 'Sin información' }}"
                                                title="Ver detalle">
                                                <i class="fa fa-eye"></i>
                                            </button>
                                            @if ($l->status_integration_id == 4)
                                                <button class="btn btn-sm btn-warning btn-reintegrate" 
                                                    data-id="{{ $l->id }}" 
                                                    data-table="{{ $l->table_name }}" 
                                                    data-service="{{ $l->service_name }}" 
                                                    data-resource="{{ $resource->name }}"
                                                    data-action="{{ route('integrations-close', ['slug' => $resource->slug]) }}"
                                                    title="Cerrar">
                                                    <i class="fa fa-ban"></i>
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL DETALLE -->
    <div class="modal fade" id="modal_view_detail" tabindex="-1" aria-labelledby="modal_view_detailLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content" style="max-height: 95vh;">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal_view_detailLabel">
                        <i class="fa fa-info-circle me-2"></i>Detalle de Integración
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body overflow-auto" style="max-height: 70vh;">
                    <div id="detailContent">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card detail-card mb-3">
                                    <div class="card-header">
                                        <h6 class="mb-0"><i class="fa fa-info me-2"></i>Información General</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-2">
                                            <div class="col-sm-4 detail-label">ID:</div>
                                            <div class="col-sm-8" id="detail_id"></div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-sm-4 detail-label">Recurso:</div>
                                            <div class="col-sm-8" id="detail_resource"></div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-sm-4 detail-label">Servicio:</div>
                                            <div class="col-sm-8" id="detail_service"></div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-sm-4 detail-label">Origen:</div>
                                            <div class="col-sm-8" id="detail_origin"></div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-sm-4 detail-label">Destino:</div>
                                            <div class="col-sm-8" id="detail_destiny"></div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-sm-4 detail-label">Estado:</div>
                                            <div class="col-sm-8" id="detail_status"></div>
                                        </div>
                                        {{-- <div class="row mb-2">
                                            <div class="col-sm-4 detail-label">Intentos:</div>
                                            <div class="col-sm-8" id="detail_attempts"></div>
                                        </div> --}}
                                        <div class="row mb-2">
                                            <div class="col-sm-4 detail-label">Código:</div>
                                            <div class="col-sm-8" id="detail_code"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card detail-card mb-3">
                                    <div class="card-header">
                                        <h6 class="mb-0"><i class="fa fa-clock me-2"></i>Fechas</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-2">
                                            <div class="col-sm-4 detail-label">Fecha creación:</div>
                                            <div class="col-sm-8" id="detail_created_at"></div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-sm-4 detail-label">Última actualización:</div>
                                            <div class="col-sm-8" id="detail_updated_at"></div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-sm-4 detail-label">Usuario creación:</div>
                                            <div class="col-sm-8" id="detail_user_created"></div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-sm-4 detail-label">Usuario actualización:</div>
                                            <div class="col-sm-8" id="detail_user_updated"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="card detail-card mb-3">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0"><i class="fa fa-comment me-2"></i>Mensaje</h6>
                                        <button class="btn btn-xs btn-default btn-copy" data-target="detail_message" title="Copiar al portapapeles">
                                            <i class="fa fa-clipboard"></i>
                                        </button>
                                    </div>
                                    <div class="card-body">
                                        <p class="alert" id="detail_message"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            @if($resource->name == 'Boletas')
                                <div class="col-md-6">
                                    <div class="card detail-card mb-3">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0"><i class="fa fa-arrow-up me-2"></i>Datos de Solicitud (Entrada de Mercadería)</h6>
                                            <button class="btn btn-xs btn-default btn-copy" data-target="detail_entry_request_body" title="Copiar al portapapeles">
                                                <i class="fa fa-clipboard"></i>
                                            </button>
                                        </div>
                                        <div class="card-body">
                                            <textarea readonly class="bg-dark text-light p-2 rounded small" id="detail_entry_request_body" style="width: 100%; height: 250px;"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card detail-card mb-3">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0"><i class="fa fa-arrow-down me-2"></i>Datos de Respuesta  (Entrada de Mercadería)</h6>
                                            <button class="btn btn-xs btn-default btn-copy" data-target="detail_entry_response" title="Copiar al portapapeles">
                                                <i class="fa fa-clipboard"></i>
                                            </button>
                                        </div>
                                        <div class="card-body">
                                            <textarea readonly class="bg-dark text-light p-2 rounded small" id="detail_entry_response"  style="width: 100%; height: 250px;"></textarea>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <div class="col-md-6">
                                <div class="card detail-card mb-3">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0"><i class="fa fa-arrow-up me-2"></i>Datos de Solicitud</h6>
                                        <button class="btn btn-xs btn-default btn-copy" data-target="detail_request_body" title="Copiar al portapapeles">
                                            <i class="fa fa-clipboard"></i>
                                        </button>
                                    </div>
                                    <div class="card-body">
                                        <textarea readonly class="bg-dark text-light p-2 rounded small" id="detail_request_body" style="width: 100%; height: 250px;"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card detail-card mb-3">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0"><i class="fa fa-arrow-down me-2"></i>Datos de Respuesta</h6>
                                        <button class="btn btn-xs btn-default btn-copy" data-target="detail_response" title="Copiar al portapapeles">
                                            <i class="fa fa-clipboard"></i>
                                        </button>
                                    </div>
                                    <div class="card-body">
                                        <textarea readonly class="bg-dark text-light p-2 rounded small" id="detail_response"  style="width: 100%; height: 250px;"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card detail-card mb-3">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0"><i class="fa fa-code me-2"></i>Cuerpo de Creación</h6>
                                        <button class="btn btn-xs btn-default btn-copy" data-target="detail_create_body" title="Copiar al portapapeles">
                                            <i class="fa fa-clipboard"></i>
                                        </button>
                                    </div>
                                    <div class="card-body">
                                        <textarea readonly class="bg-dark text-light p-2 rounded small" id="detail_create_body"  style="width: 100%; height: 250px;"></textarea>
                                    </div>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    {{-- <button type="button" class="btn btn-primary" onclick="downloadLog()">
                        <i class="fa fa-download me-2"></i>Descargar Log
                    </button> --}}
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL CERRAR -->
    <div class="modal fade" id="modal_reintegrate" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form method="POST" action="">
                @csrf
                <input type="hidden" name="id_modal" id="reintegrate_id">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fa fa-ban-circle me-2"></i>Confirmar Cerrar Integración</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <p>¿Estás seguro de que deseas marcar como cerrado este registro?</p>
                        <div class="alert alert-info">
                            <strong>ID:</strong> <span id="reintegrate_id_show"></span><br>
                            <strong>Recurso:</strong> <span id="reintegrate_resource_show"></span><br>
                            <strong>Servicio:</strong> <span id="reintegrate_service_show"></span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button id="confirmReintegrateButton" type="submit" class="btn btn-warning">
                            <i class="fa fa-ban me-1"></i>Confirmar
                        </button>
                        <button id="cancelReintegrateButton" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('js_content')
    <script nonce="{{ app('csp_nonce') }}">
        setTimeout(function() {
            $("#div_spinner").html('')
            $('#table_list').DataTable({
                responsive: true,
                "bLengthChange": false,
                "language": {
                    "url":  `{{ asset(ASSETS_JS_ADMIN) }}/Spanish.json`
                },
                order: [
                    [0, 'desc']
                ],
            });
            $("#div_25").removeClass('table-responsive');
            $("#table_list").attr('hidden', false);
        }, 400);
    </script>

    <script nonce="{{ app('csp_nonce') }}">
        $(document).ready(function () {
            // Modal Ver Detalle
            document.querySelectorAll('.btn-view-detail').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    // Datos básicos
                    document.getElementById('detail_id').textContent = this.dataset.id;
                    document.getElementById('detail_service').textContent = this.dataset.service;
                    document.getElementById('detail_resource').textContent = this.dataset.resourceName;
                    document.getElementById('detail_origin').textContent = this.dataset.origin;
                    document.getElementById('detail_destiny').textContent = this.dataset.destiny;
                    // document.getElementById('detail_attempts').textContent = this.dataset.attempts;
                    document.getElementById('detail_code').textContent = this.dataset.code;
                    document.getElementById('detail_user_created').textContent = this.dataset.userCreated;
                    document.getElementById('detail_user_updated').textContent = this.dataset.userUpdated;

                    const messageElement = document.getElementById('detail_message');
                    messageElement.textContent = this.dataset.message;
                    messageElement.className = 'alert ' + (this.dataset.badge || 'alert-secondary');

                    
                    // Estado con badge
                    const badgeClass  = this.dataset.badge || '';
                    const iconClass   = this.dataset.icon  || '';
                    const statusLabel = this.dataset.statusText || '—';

                    document.getElementById('detail_status').innerHTML = 
                        `<span class="badge status-badge ${badgeClass}">
                            <i class="fa ${iconClass} me-1"></i>${statusLabel}
                        </span>`;

                    
                    // Fechas formateadas
                    document.getElementById('detail_created_at').textContent = formatDateTime(this.dataset.createdAt);
                    document.getElementById('detail_updated_at').textContent = formatDateTime(this.dataset.updatedAt);
                    
                    // Datos JSON formateados
                    try {
                        document.getElementById('detail_create_body').textContent = JSON.stringify(JSON.parse(this.dataset.createBody), null, 2);
                        document.getElementById('detail_request_body').textContent = JSON.stringify(JSON.parse(this.dataset.requestBody), null, 2);
                        document.getElementById('detail_response').textContent = JSON.stringify(JSON.parse(this.dataset.response), null, 2);
                     
                        @if($resource->name == 'Boletas')
                            document.getElementById('detail_entry_request_body').textContent = JSON.stringify(JSON.parse(this.dataset.entryRequestBody), null, 2);
                            document.getElementById('detail_entry_response').textContent = JSON.stringify(JSON.parse(this.dataset.entryResponse), null, 2);
                        @endif
                    } catch (e) {
                        document.getElementById('detail_create_body').textContent = this.dataset.createBody;
                        document.getElementById('detail_request_body').textContent = this.dataset.requestBody;
                        document.getElementById('detail_response').textContent = this.dataset.response;

                        @if($resource->name == 'Boletas')
                            document.getElementById('detail_entry_request_body').textContent = this.dataset.entryRequestBody;
                            document.getElementById('detail_entry_response').textContent = this.dataset.entryResponse;
                        @endif
                    }
                    
                    new bootstrap.Modal(document.getElementById('modal_view_detail')).show();
                });
            });

            // Función para formatear fecha y hora
            function formatDateTime(dateTimeString) {
                if (!dateTimeString) return 'Sin información';
                const date = new Date(dateTimeString);
                return date.toLocaleString('es-CL', {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                });
            }

            // Modal Reintegrar
            document.querySelectorAll('.btn-reintegrate').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const id = this.dataset.id;
                    const service = this.dataset.service;
                    const resource = this.dataset.resource;
                    // const table = this.dataset.table;

                    document.getElementById('reintegrate_id').value = id;
                    document.getElementById('reintegrate_id_show').textContent = id;
                    document.getElementById('reintegrate_service_show').textContent = service;
                    document.getElementById('reintegrate_resource_show').textContent = resource;
                    // document.getElementById('table_name').value = table;
                    document.querySelector('#modal_reintegrate form').setAttribute('action', this.dataset.action);

                    new bootstrap.Modal(document.getElementById('modal_reintegrate')).show();
                });
            });

            $('#modal_reintegrate form').on('submit', function (e) {
                console.log('submit')
                e.preventDefault();
                let integrationId = $('#reintegrate_id').val();

                if (!integrationId || parseInt(integrationId) <= 0) {
                    showToast('ID de integración no válido. Intenta nuevamente.', 'error');
                    return;
                }

                $('#confirmReintegrateButton').text('Validando...');
                $('#confirmReintegrateButton .loading-spinner').removeClass('d-none');
                $('#confirmReintegrateButton').prop('disabled', true);
                $('#cancelReintegrateButton').addClass('d-none');

                setTimeout(() => {
                    document.querySelector('#modal_reintegrate form').submit();
                }, 500);
            });

            document.querySelectorAll('.btn-copy').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const targetId = this.dataset.target;
                    copyToClipboard(targetId);
                });
            });


            // Función para descargar log (ejemplo)
            function downloadLog() {
                const integrationId = document.getElementById('detail_id').textContent;
                // Aquí implementarías la lógica para descargar el log
                console.log(`Descargando log para integración ${integrationId}`);
                // Ejemplo: window.location.href = `/integrations/${integrationId}/download-log`;
            }

            // Función para copiar contenido de cualquier elemento al portapapeles (mejor compatibilidad)

            // debuguear para verificar que existe clipboard, solo funciona en contextos "seguros" (https o localhost)
            // console.log(navigator.clipboard);

            function copyToClipboard(elementId) {
                // Selecciona el textarea por ID
                const textArea = document.getElementById(elementId);
                
                // Verifica si el elemento existe
                if (textArea) {
                    // Crea un rango de selección
                    textArea.removeAttribute('disabled'); // Habilita temporalmente para seleccionar el contenido
                    textArea.select();
                    textArea.setSelectionRange(0, 99999); // Selección para móviles
                    
                    // Copia el contenido al portapapeles
                    navigator.clipboard.writeText(textArea.value)
                        .then(() => {
                            alert('¡Copiado al portapapeles!');
                        })
                        .catch((err) => {
                            console.error('Error al copiar:', err);
                        });
                    textArea.setAttribute('disabled', true); // Vuelve a deshabilitar el textarea
                } else {
                    console.error('No se encontró el elemento:', elementId);
                }
            }
        });
    </script>
@endsection