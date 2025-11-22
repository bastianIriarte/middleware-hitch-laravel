@extends('layout.layout_admin')
@section('contenido')
    <div class="content-wrapper">
        <div class="page-title">
            <i class="fa fa-users me-2"></i>{{ $title }}
        </div>
        <div class="mb-4">
            <!-- Filtros -->
            <div class="row mb-4">
                <div class="col-md-12 text-end mb-3">
                    <!-- Quick Actions -->
                    <button class="btn quick-action-btn" id="createUserBtn">
                        <i class="fa fa-plus me-2"></i>Nuevo Usuario
                    </button>
                </div>
                {{-- <div class="col-md-3">
                    <select class="form-select" id="filterStatus">
                        <option value="">Todos los estados</option>
                        <option value="active">Activo</option>
                        <option value="inactive">Inactivo</option>
                        <option value="pending">Pendiente</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="filterRole">
                        <option value="">Todos los roles</option>
                        <option value="admin">Administrador</option>
                        <option value="moderator">Moderador</option>
                        <option value="user">Usuario</option>
                    </select>
                </div> --}}
            </div>
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
                                    <th class="text-center">Id</th>
                                    <th class="text-center text-nowrap">Rut</th>
                                    <th class="text-center text-nowrap">Información Usuario</th>
                                    <th class="text-center">Acceso Api</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center" width="2%">Fecha Creación</th>
                                    <th class="text-center">Acciones</th>

                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($list_data as $key => $l)
                                    <tr id="fila_{{ $l->id }}" class="font-13">
                                        <td class="text-center">{{ $l->id }}</td>
                                        <td class="text-center">
                                            <small hidden style="font-size: 1px">
                                                {{ !empty($l->rut) ? $l->rut : '-' }}
                                            </small>
                                            {{ !empty($l->rut) ? formateaRut($l->rut) : '-' }}
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="user-table-avatar me-3">
                                                    {{ !empty($l->username) ? strtoupper(substr($l->username, 0, 2)) : 'S/N' }}
                                                </div>
                                                <div>
                                                    <div class="fw-semibold">{{ !empty($l->name) ? $l->name : 'Sin información' }}</div>
                                                    <small class="text-muted">{{ !empty($l->email) ? $l->email : 'Sin información' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center font-15">
                                            @if ($l->api_access == true)
                                                <span class='badge bg-success'>SI</span>
                                            @else
                                                <span class='badge bg-danger'>NO</span>
                                            @endif
                                        </td>
                                        <td class="text-center font-15">
                                            @if ($l->status == true)
                                                <span class='badge bg-success'>Activo</span>
                                            @else
                                                <span class='badge bg-danger'>Inactivo</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            {{ !empty($l->created_at) ? ordenar_fechaHoraHumano($l->created_at) : 'Sin información' }}
                                        </td>
                                        <td class="table-actions text-center">
                                            <button class="btn btn-sm btn-outline-primary me-1 btn-edit-user" data-user-id="{{ $l->id }}" title="Editar">
                                                <i class="fa fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger btn-delete-user" 
                                                data-user-id="{{ $l->id }}"
                                                data-user-name="{{ $l->name }}"
                                                data-user-email="{{ $l->email }}" 
                                                title="Eliminar">
                                                <i class="fa fa-trash"></i>
                                            </button>

                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Paginación -->
            <nav aria-label="Paginación de usuarios" class="mt-4">
                <ul class="pagination justify-content-center" id="usersPagination">
                    <!-- La paginación se generará dinámicamente -->
                </ul>
            </nav>
        </div>
    </div>
</div>
<!-- Modal de Creación/Edición de Usuario -->
<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userModalLabel">
                    <i class="fa fa-user-plus me-2"></i>Crear Nuevo Usuario
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="userForm" method="POST" action="#">
                    @csrf
                    <input type="hidden" name="user_id" id="userId">

                    <div class="row g-3">
    
                        <div class="col-md-6 p-1">
                            <div class="form-group">
                                <label for="rut" class="">RUT <span class="text-danger">*</span></label>
                                <input class="form-control" id="rut" name="rut" type="text" placeholder="Ingrese rut...">
                                <small id="invalid_rut" class="text-danger ml-2"></small>
                            </div>
                        </div>

                        <div class="col-md-6 p-1">
                            <div class="form-group">
                                <label for="firstName" class="form-label">NOMBRE COMPLETO <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="firstName" name="name" required>
                                <div class="invalid-feedback">Por favor ingresa el nombre.</div>
                            </div>
                        </div>

                        <div class="col-md-6 p-1">
                            <div class="form-group">
                                <label for="email" class="">CORREO ELECTRÓNICO <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" required>
                                <div class="invalid-feedback">Por favor ingresa un email válido.</div>
                            </div>
                        </div>

                        <div class="col-md-6 p-1">
                            <div class="form-group">
                                <label for="api_access" class="">ACCESO API <span class="text-danger">*</span></label>
                                <select class="form-control" name="api_access" id="api_access">
                                    <option value="1">SI</option>
                                    <option value="0">NO</option>
                                </select>
                                <small id="invalid_api_access" class="text-danger"></small>
                            </div>
                        </div>

                        <div class="col-md-6 p-1">
                            <div class="form-group">
                                <label for="status" class="form-label">ESTADO <span class="text-danger">*</span></label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="">Seleccionar estado</option>
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                                <div class="invalid-feedback">Por favor selecciona un estado.</div>
                            </div>
                        </div>
                    </div>

                    {{-- Campos de contraseña --}}
                    <div class="row" id="passwordFields">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password" class="form-label">Contraseña <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="password" name="password">
                                    <button class="btn btn-outline-secondary" type="button" id="togglePasswordBtn">
                                        <i class="fa fa-eye" id="passwordToggleIcon"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback">La contraseña debe tener al menos 8 caracteres.</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="confirmPassword" class="form-label">Confirmar Contraseña <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="confirmPassword" name="password_confirmation">
                                    <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPasswordBtn">
                                        <i class="fa fa-eye" id="confirmPasswordToggleIcon"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback">Las contraseñas no coinciden.</div>
                            </div>
                        </div>
                    </div>
                </form>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="cancelUserBtn">Cancelar</button>
                <button type="button" class="btn btn-primary" id="saveUserBtn">
                    <span class="loading-spinner spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
                    <span class="btn-text">Guardar Usuario</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmación de Eliminación -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('user-delete') }}">
            @csrf
            <input type="hidden" name="id_modal" id="deleteUserId">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">
                        <i class="fa fa-trash me-2"></i>Confirmar Eliminación
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que deseas eliminar este usuario?</p>
                    <div class="alert alert-warning">
                        <i class="fa fa-exclamation-triangle me-2"></i>
                        <strong>Advertencia:</strong> Esta acción no se puede deshacer.
                    </div>
                    <div class="alert alert-info">
                        <strong>Nombre:</strong> <span id="deleteUserName"></span><br>
                        <strong>Email:</strong> <span id="deleteUserEmail"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="cancelDeleteBtn">Cancelar</button>
                    <button type="submit" class="btn btn-danger" id="confirmDeleteBtn">
                        <span class="loading-spinner spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
                        <span class="btn-text">Eliminar Usuario</span>
                    </button>
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
                    [0, 'asc']
                ],
            });
            $("#div_25").removeClass('table-responsive');
            $("#table_list").attr('hidden', false);
        }, 400);
    </script>

    <script nonce="{{ app('csp_nonce') }}">
        $(document).ready(function () {

            // ELIMINAR USUARIO
            $(document).on('click', '.btn-delete-user', function () {
                let userId = $(this).data('user-id');
                let userEmail = $(this).data('user-email') ?? 'Sin información';
                let userName = $(this).data('user-name') ?? 'Sin información';

                if (userId > 0) {
                    $('#deleteUserEmail').text(userEmail);
                    $('#deleteUserName').text(userName);
                    $('#deleteUserId').val(userId);
                    $('#deleteModal').modal('show');
                } else {
                    showToast('Ha ocurrido un error al eliminar el registro. Recargue e intente nuevamente.', 'error');
                }
            });

            $('#deleteModal form').on('submit', function (e) {
                e.preventDefault();
                let userId = $('#deleteUserId').val();

                if (!userId || parseInt(userId) <= 0) {
                    showToast('ID de usuario no válido. Intenta nuevamente.', 'error');
                    return;
                }

                $('#confirmDeleteBtn .btn-text').text('Eliminando...');
                $('#confirmDeleteBtn .loading-spinner').removeClass('d-none');
                $('#confirmDeleteBtn').prop('disabled', true);
                $('#cancelDeleteBtn').addClass('d-none');

                setTimeout(() => {
                    document.querySelector('#deleteModal form').submit();
                }, 500);
            });

            // Mostrar/Ocultar contraseña
            $('#togglePasswordBtn').on('click', function () {
                const input = $('#password');
                const icon = $('#passwordToggleIcon');
                input.attr('type', input.attr('type') === 'password' ? 'text' : 'password');
                icon.toggleClass('fa-eye fa-eye-slash');
            });

            $('#toggleConfirmPasswordBtn').on('click', function () {
                const input = $('#confirmPassword');
                const icon = $('#confirmPasswordToggleIcon');
                input.attr('type', input.attr('type') === 'password' ? 'text' : 'password');
                icon.toggleClass('fa-eye fa-eye-slash');
            });

            // Crear nuevo usuario
            $('#createUserBtn').on('click', function () {
                $('#userModalLabel').html('<i class="fa fa-user-plus me-2"></i>Crear Nuevo Usuario');
                $('#userForm').attr('action', '{{ route('user-store') }}');
                $('#userForm')[0].reset();
                $('#userForm').removeClass('was-validated');

                $('#userId').val('');
                $('#passwordFields').show();
                $('#userModal').modal('show');
            });

            // Editar usuario
            $('.btn-edit-user').on('click', function () {
                const userId = $(this).data('user-id');
                const row = $(`#fila_${userId}`);

                const rutRaw = row.find('td:nth-child(2) small').text().trim();
                const name = row.find('td:nth-child(3) .fw-semibold').text().trim();
                const email = row.find('td:nth-child(3) .text-muted').text().trim();
                const apiAccess = row.find('td:nth-child(4) .badge').text().trim().toLowerCase() === 'si' ? '1' : '0';
                const status = row.find('td:nth-child(5) .badge').hasClass('bg-success') ? '1' : '0';

                $('#userModalLabel').html('<i class="fa fa-edit me-2"></i>Editar Usuario');
                $('#userForm').removeClass('was-validated');

                $('#userId').val(userId);
                $('#rut').val(rutRaw);
                $('#firstName').val(name);
                $('#email').val(email);
                $('#api_access').val(apiAccess);
                $('#status').val(status);
                $('#password').val('');
                $('#confirmPassword').val('');
                $('#passwordFields').hide();

                const updateRoute = '{{ route('user-update', ':id') }}'.replace(':id', userId);
                $('#userForm').attr('action', updateRoute);

                $('#userModal').modal('show');
            });

            // Validar y guardar
            $('#saveUserBtn').on('click', function () {
                let form = $('#userForm')[0];
                let isValid = form.checkValidity();
                let password = $('#password').val();
                let confirmPassword = $('#confirmPassword').val();
                let isEdit = $('#userId').val() !== '';

                if (!isEdit) {
                    if (password.length < 8) {
                        $('#password').addClass('is-invalid');
                        isValid = false;
                    } else {
                        $('#password').removeClass('is-invalid');
                    }

                    if (password !== confirmPassword) {
                        $('#confirmPassword').addClass('is-invalid');
                        isValid = false;
                    } else {
                        $('#confirmPassword').removeClass('is-invalid');
                    }
                }

                if (!isValid) {
                    form.classList.add('was-validated');
                    return;
                }

                $('#saveUserBtn .btn-text').text('Guardando...');
                $('#saveUserBtn .loading-spinner').removeClass('d-none');
                $('#saveUserBtn').prop('disabled', true);
                $('#cancelUserBtn').addClass('d-none');

                setTimeout(() => {
                    $('#userForm').submit();
                }, 500);
            });

        });
        </script>


@endsection