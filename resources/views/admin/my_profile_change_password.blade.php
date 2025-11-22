@extends('layout.layout_admin')
@section('contenido')
    <!-- PAGE-HEADER -->
    <div class="page-header d-flex align-items-center justify-content-between border-bottom mb-4">
        <h1 class="page-title">{{ $title }}</h1>
        <div>
            <ol class="breadcrumb">
                <li class="breadcrumb-item active" aria-current="page">{{ $title }}</li>
            </ol>
        </div>
    </div>
    <!-- PAGE-HEADER END -->
    <!-- CONTAINER -->
    <div class="main-container container-fluid">
        <div class="col-xl-12">
            <div class="card p-0">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-xl-10 col-8"></div>
                        <div class="col-xl-2 col-4">
                            <a class="btn btn-info" href="{{ route('profile') }}">
                                <i class="fa fa-user"></i> Mi Perfil
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row ">
            <div class="col-md-12">
                <div class="main-container container-fluid">
                    <!-- Start::row-1 -->
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card custom-card p-4">
                                <div class="card-header bg-light">
                                    <h5 class="text-center text-uppercase fs-14" style="margin: 0 auto;"><b>Cambio de
                                            contraseña</b></h5>
                                </div>
                                <div id="password-alert" class="alert alert-danger mt-2">
                                    <ul style="list-style-type: circle;padding: 8px;">
                                        <li>La contraseña debe tener al menos 8 caracteres.</li>
                                        <li>La contraseña debe tener al menos una letra mayúscula.</li>
                                        <li>La contraseña debe tener al menos un número.</li>
                                        <li>La contraseña debe tener al menos un carácter especial.</li>
                                    </ul>
                                </div>
                                <!-- form start -->
                                <form action="{{ route('change-password-post') }}" method="post" id="form">
                                    {{ csrf_field() }}
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-lg-4 col-md-6">
                                                <div class="form-group">
                                                    <label for="password_current">Contraseña Actual <span
                                                            class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <input type="password" id="password_current" name="password_current"
                                                            class="form-control" placeholder="Ingrese contraseña actual...">
                                                    </div>
                                                    <small id="invalid_password_current" class="text-danger"></small>
                                                </div>
                                            </div>
                                            <div class="col-lg-4 col-md-6">
                                                <div class="form-group">
                                                    <label for="password">Nueva Contraseña <span
                                                            class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <input type="password" id="password" name="password"
                                                            class="form-control" placeholder="Ingrese Nueva contraseña...">
                                                    </div>
                                                    <small id="invalid_password" class="text-danger"></small>
                                                </div>
                                            </div>
                                            <div class="col-lg-4 col-md-6">
                                                <div class="form-group">
                                                    <label for="password_confirm">Confirmar Contraseña <span
                                                            class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <input type="password" id="password_confirm" name="password_confirm"
                                                            class="form-control" placeholder="Confirme Nueva contraseña...">
                                                    </div>
                                                    <small id="invalid_password_confirm" class="text-danger"></small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <hr class="mt-2 mb-3">
                                            <div class="d-flex flex-wrap justify-content-center align-items-center">
                                                <button class="btn btn-md btn-dark mt-3 mt-sm-0" type="submit"
                                                    id="btn_submit">
                                                    <i class="fa fa-save"></i> Cambiar contraseña
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <!-- /.content -->
                </div>
            </div>
        </div>
    </div>
    <!-- CONTAINER CLOSED -->

    <!--app-content closed-->
@endsection


@section('js_content')
@include('validator')
@include('validator-password')
    <script nonce="{{ app('csp_nonce') }}">
		$('#password_current').keyup(function() {
			validateField($('#password_current').val(), 'password_current');
		});
		$('#password').keyup(function() {
			validateFieldsPassword($('#password').val(), 'password');
		});
		$('#password_confirm').keyup(function() {
			validateFieldsPassword($('#password_confirm').val(), 'password_confirm');
		});

        $("#form").submit(function(e) {
            e.preventDefault();
            let password_current = validateField($("#password_current").val(), 'password_current');
			let password = validateFieldsPassword($("#password").val(), 'password');
			let password_confirm = validateFieldsPassword($("#password_confirm").val(), 'password_confirm');
            if (password_current == 1 && password == 1 && password_confirm == 1) {
                $("#btn_submit").attr('disabled', true);
                $("#btn_submit").html(
                    `<span class="spinner-border spinner-border-sm" id="sign_spinner"></span> Validando...`);
                setTimeout(function() {
                    document.getElementById("form").submit();
                }, 400);
            } else {
                toastr["error"](`Se encontraron 1 o más Campos con Problemas. Corrija e Intente nuevamente`,
                    "Error de Validación")
            }
        });
    </script>
@endsection