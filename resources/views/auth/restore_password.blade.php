@extends('layout.layout_no_login')
@section('contenido')
    <div class="card-body p-sm-6">
        <div class="text-center mb-4">
            <h4 class="mb-1"><b>Restablecimiento de Contraseña</b></h4>
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
        <div id="password-alert" class="alert alert-danger">
            <ul style="list-style-type: circle;padding: 8px;">
                <li>La contraseña debe tener al menos 8 caracteres.</li>
                <li>La contraseña debe tener al menos una letra mayúscula.</li>
                <li>La contraseña debe tener al menos un número.</li>
                <li>La contraseña debe tener al menos un carácter especial.</li>
            </ul>
        </div>
        <div class="row">
            @php
                $route = route('restore-password-post');
                $btn_login = route('login'); 
            @endphp
            <form action="{{ $route }}" method="POST" id="form">
                {{ csrf_field() }}
                <input type="hidden" name="token" value="{{ $token }}">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="password">Nueva Contraseña <span class="text-danger">*</span></label>
                        <div class="password-toggle">
                            <input class="form-control prepended-form-control" id="password" name="password"
                                type="password" placeholder="Ingrese nueva contraseña...">
                            <label class="password-toggle-btn">
                                <input class="custom-control-input" type="checkbox"><i
                                    class="czi-eye password-toggle-indicator"></i><span class="sr-only">Show
                                    password</span>
                            </label>
                            <small id="invalid_password" class="text-danger ml-2"></small>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="password_confirm">Confirmar Contraseña <span class="text-danger">*</span></label>
                        <div class="password-toggle">
                            <input class="form-control prepended-form-control" id="password_confirm" name="password_confirm"
                                type="password" placeholder="Confirme nueva contraseña...">
                            <label class="password-toggle-btn">
                                <input class="custom-control-input" type="checkbox"><i
                                    class="czi-eye password-toggle-indicator"></i><span class="sr-only">Show
                                    password</span>
                            </label>
                            <small id="invalid_password_confirm" class="text-danger ml-2"></small>
                        </div>
                    </div>
                </div>
                <div class="col-xl-12">
                    <div class="d-grid mb-3">
                        <button class="btn btn-primary w-100 mt-2 p-2" type="submit" id="btn-submit">
                            <i class="fa fa-save"></i> Cambiar contraseña
                        </button>
                    </div>
                    <p class="mb-0 text-center">
                        ¿Recordaste tu contraseña?<br>
                        <a href="{{ $btn_login }}" class="text-center">Inicia Sesión Aquí</a>
                    </p>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('js_content')
    @include('validator-password')
    <script nonce="{{ app('csp_nonce') }}">
        $('#password').keyup(function() {
            validateFieldsPassword($('#password').val(), 'password');
        });
        $('#password_confirm').keyup(function() {
            validateFieldsPassword($('#password_confirm').val(), 'password_confirm');
        });

        $("#form").submit(function(e) {
            e.preventDefault();
            let password = validateFieldsPassword($('#password').val(), 'password');
            let password_confirm = validateFieldsPassword($('#password_confirm').val(), 'password_confirm');
            if (password == 1 && password_confirm == 1) {
                $("#login-status").html(``);
                $("#btn-submit").html(
                    `<span class="spinner-border spinner-border-sm" id="sign_spinner"></span> Validando...`
                );
                $("#btn-submit").attr('disabled', true);
                setTimeout(function() {
                    document.getElementById("form").submit();
                }, 400);
            } else {
                toastr["error"](
                    `Se encontraron 1 o más Campos con Problemas. Corrija e Intente nuevamente`,
                    "Error de Validación"
                )
            }
        });
    </script>
@endsection
