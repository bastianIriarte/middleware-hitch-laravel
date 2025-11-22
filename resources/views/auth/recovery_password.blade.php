@extends('layout.layout_no_login')
@section('contenido')
    <div class="card-body p-sm-6">
        <div class="text-center mb-4">
            <h4 class="mb-1"><b>Restablecer Contraseña</b></h4>
            <p>Seleccione un método de recuperación para recibir indicaciones de restablecimiento.</p>
            <hr>
        </div>
        <div class="login-status" id="login-status">
            @if (session()->has('warning_message') || session()->has('danger_message'))
                <div class="msg-error alert alert-danger py-2 px-3 mb-3 fs-14 text-center">
                    <i class="fa fa-circle-exclamation me-2"></i>
                    {!! session()->get('warning_message') !!}{!! session()->get('danger_message') !!}
                </div>
            @endif
        </div>
        <div class="row">
            <form action="{{ route('recovery-password-post') }}" method="POST" id="form">
                {{ csrf_field() }}
                <div class="col-sm-12">
                    <div class="mb-3">
                        <label class="mb-2 fw-500">Método de recuperación<span class="text-danger ms-1">*</span></label>
                        <select class="form-control ms-0" id="recuperation_method" name="recuperation_method">
                            <option value="" disabled selected>--Seleccione--</option>
                            <option value="email">Email</option>
                            <option value="rut">Rut</option>
                        </select>
                        <small class="text-danger" id="invalid_recuperation_method"></small>
                    </div>
                </div>

                <div id="email_field" class="col-sm-12" style="display: none;">
                    <div class="mb-3">
                        <label class="mb-2 fw-500">Correo electrónico<span class="text-danger ms-1">*</span></label>
                        <input class="form-control ms-0" name="email" id="email" type="email"
                            value="{{ old('email') }}" placeholder="Ingrese correo electrónico...">
                        <small class="text-danger" id="invalid_email"></small>
                    </div>
                </div>

                <div id="rut_field" class="col-sm-12" style="display: none;">
                    <div class="mb-3">
                        <label class="mb-2 fw-500">Rut<span class="text-danger ms-1">*</span></label>
                        <input class="form-control ms-0" name="rut" id="rut" type="text"
                            value="{{ old('rut') }}" placeholder="Ingrese su rut..." maxlength="12">
                        <small class="text-danger" id="invalid_rut"></small>
                    </div>
                </div>

                <div class="col-xl-12">
                    <div class="d-grid mb-3">
                        <button class="btn btn-primary w-100 mt-2 p-2" type="submit" id="btn-submit">
                            <i class="fa fa-paper-plane"></i> Restablecer contraseña
                        </button>
                    </div>
                    <p class="mb-0 text-center">
                        ¿Recordaste tu contraseña?<br>
                        <a href="{{ route('login') }}" class="text-center">Inicia Sesión Aquí</a>
                    </p>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('js_content')
    @include('validator-rut')
    @include('validator-no-login')

    <script nonce="{{ app('csp_nonce') }}">
        // Muestra u oculta los campos según el método de recuperación seleccionado
        $('#recuperation_method').change(function() {
            var method = $(this).val();
            if (method === 'email') {
                $('#email_field').show();
                $('#rut_field').hide();
            } else if (method === 'rut') {
                $('#email_field').hide();
                $('#rut_field').show();
            } else {
                $('#email_field').hide();
                $('#rut_field').hide();
            }
        });
        // Valida el correo electrónico solo si el método de recuperación es 'email'
        $('#email').keyup(function() {
            if ($('#recuperation_method').val() === 'email') {
                validateEmail($('#email').val(), 'email', 'Ingrese un Correo Válido');
            }
        });

        // Valida el rut solo si el método de recuperación es 'rut'
        $('#rut').keyup(function() {
            if ($('#recuperation_method').val() === 'rut') {
                validateField($('#rut').val(), 'rut', 'rut', 'Ingrese un Rut Válido');
            }
        });

        $("#form").submit(function(e) {
            e.preventDefault();

            var method = $('#recuperation_method').val();
            var isValid = true;

            // Validación basada en el método de recuperación
            if (method === 'email') {
                // Validar correo electrónico
                let email = validateEmail($('#email').val(), 'email', 'Ingrese un Correo Válido');
                if (email == 0) {
                    $("#email").focus();
                    $("#login-status").html(
                        `<div class="msg-error alert alert-danger py-2 px-3 mb-3 fs-14 text-center">
                            <i class="fa fa-circle-exclamation me-2"></i> Correo electrónico obligatorio
                        </div>`
                    );
                    isValid = false;
                }
            } else if (method === 'rut') {
                // Validar rut
                let rut = validateField($('#rut').val(), 'rut', 'rut', 'Ingrese un Rut Válido');
                if (rut == 0) {
                    $("#rut").focus();
                    $("#login-status").html(
                        `<div class="msg-error alert alert-danger py-2 px-3 mb-3 fs-14 text-center">
                            <i class="fa fa-circle-exclamation me-2"></i> Rut obligatorio
                        </div>`
                    );
                    isValid = false;
                }
            } else {
                isValid = false;
            }

            if (isValid) {
                $("#login-status").html(``);
                $("#btn-submit").html(
                    `<span class="spinner-border spinner-border-sm" id="sign_spinner"></span> Validando...`
                );
                $("#btn-submit").attr('disabled', 'disabled');
                setTimeout(function() {
                    document.getElementById("form").submit();
                }, 400);
            } else {
                $('#recuperation_method').focus();
                $("#login-status").html(
                    `<div class="msg-error alert alert-danger py-2 px-3 mb-3 fs-14 text-center">
                            <i class="fa fa-circle-exclamation me-2"></i> Método de recuperación obligatorio
                        </div>`
                );
            }
        });
    </script>
@endsection
