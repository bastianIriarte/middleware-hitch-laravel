@extends('layout.layout_admin')
@section('contenido')
    <div class="content-wrapper">
        <div class="page-title">
            <i class="fa fa-tachometer me-2"></i>Dashboard
        </div>
        <!-- Card de Bienvenida -->
        <div class="col-12 mb-4">
            <div class="stats-card d-flex align-items-center" style="background: #f0f4f7;">
                <div class="stats-icon info me-3" style="font-size: 2.5rem;">
                    <i class="fa fa-smile-o"></i>
                </div>
                <div>
                    <div class="stats-number" style="font-size: 1.5rem;">
                        ¡Hola {{ auth()->user()->name }}!
                    </div>
                    <div class="stats-label" style="font-size: 1rem;">
                        Bienvenido/a al panel de integraciones.
                    </div>
                </div>
            </div>
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
                            <table class="table table-hover align-middle" id="table_list" hidden>
                                <thead class="table-dark">
                                    <tr class="text-center">
                                        <th class="text-center"><i class="fa fa-folder me-1"></i>Recurso</th>
                                        <th class="text-center"><i class="fa fa-clock-o me-1"></i>Pendientes</th>
                                        <th class="text-center"><i class="fa fa-spinner me-1"></i>En Curso</th>
                                        <th class="text-center"><i class="fa fa-check-circle me-1"></i>Completadas</th>
                                        <th class="text-center"><i class="fa fa-exclamation-circle me-1"></i>Fallidas</th>
                                        <th class="text-center"><i class="fa fa-cogs me-1"></i>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($resources as $key => $l)
                                        <tr id="fila_{{ $l->id }}">
                                            <td class="text-center fw-semibold">{{ $l->name ?? 'Sin información' }}</td>

                                            <td class="text-center">
                                                <span class="badge bg-warning">
                                                    <i class="fa fa-clock-o me-1"></i>{{ $l->counts->pending ?? 0 }}
                                                </span>
                                            </td>

                                            <td class="text-center">
                                                <span class="badge bg-primary">
                                                    <i class="fa fa-spinner me-1"></i>{{ $l->counts->in_progress ?? 0 }}
                                                </span>
                                            </td>

                                            <td class="text-center">
                                                <span class="badge bg-success">
                                                    <i class="fa fa-check-circle me-1"></i>{{ $l->counts->success ?? 0 }}
                                                </span>
                                            </td>

                                            <td class="text-center">
                                                <span class="badge bg-danger">
                                                    <i class="fa fa-exclamation-circle me-1"></i>{{ $l->counts->failed ?? 0 }}
                                                </span>
                                            </td>

                                            <td class="text-center">
                                                <a class="btn btn-sm btn-outline-dark" href="{{ route('integrations', ['slug' => $l->slug]) }}">
                                                    <i class="fa fa-share me-1"></i> Ver Detalle
                                                </a>
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
                    [0, 'asc']
                ],
            });
            $("#div_25").removeClass('table-responsive');
            $("#table_list").attr('hidden', false);
        }, 400);
    </script>
@endsection
