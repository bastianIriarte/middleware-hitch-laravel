@extends('layout.layout_no_login')
@section('contenido')
    <div class="card-body p-sm-6">
        <div class="text-center mb-4">
            <h4 class="mb-1"><b>{{ $title }}</b></h4>
            <p>Completa el formulario de registro para poder gestionar tus rutinas</p>
            <hr>
        </div>
        <div class="login-status" id="login-status">
            @if (session()->has('danger_message'))
                <div class="msg-error alert alert-danger py-2 px-3 mb-3 fs-14 text-center">
                    <i class="fa fa-circle-exclamation me-2"></i>
                    {!! session()->get('danger_message') !!}
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
            <form action="{{ route('register-post') }}" method="POST" id="form">
                {{ csrf_field() }}
                <div class="col-sm-12">
                    <div class="mb-3">
                        <label class="mb-2 fw-500">Nombre <span class="text-danger ms-1">*</span></label>
                        <input class="form-control ms-0" name="first_name" id="first_name" type="text"
                            value="{{ old('first_name') }}" placeholder="Ingrese nombre...">
                        <small id="invalid_first_name" class="text-danger ml-2"></small>
                    </div>
                </div>
                <div class="col-sm-12">
                    <div class="mb-3">
                        <label class="mb-2 fw-500">Apellido <span class="text-danger ms-1">*</span></label>
                        <input class="form-control ms-0" name="last_name" id="last_name" type="text"
                            value="{{ old('last_name') }}" placeholder="Ingrese apellido...">
                        <small id="invalid_last_name" class="text-danger ml-2"></small>
                    </div>
                </div>
                <div class="col-sm-12">
                    <div class="mb-3">
                        <label class="mb-2 fw-500">Correo electrónico <span class="text-danger ms-1">*</span></label>
                        <input class="form-control ms-0" name="email" id="email" type="email"
                            value="{{ old('email') }}" placeholder="Ingrese correo electrónico...">
                        <small id="invalid_email" class="text-danger ml-2"></small>
                    </div>
                </div>
                <div class="col-sm-12">
                    <div class="mb-3">
                        <label class="mb-2 fw-500">Contraseña <span class="text-danger ms-1">*</span></label>
                        <div>
                            <input type="password" class="form-control" id="password" name="password"
                                placeholder="Ingrese contraseña...">
                            <small id="invalid_password" class="text-danger ml-2"></small>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12">
                    <div class="mb-3">
                        <label class="mb-2 fw-500">Confirmar Contraseña <span class="text-danger ms-1">*</span></label>
                        <div>
                            <input type="password" class="form-control" id="password_confirm"
                                name="password_confirm" placeholder="Confirme contraseña...">
                            <small id="invalid_password_confirm" class="text-danger ml-2"></small>
                        </div>
                    </div>
                </div>
                <div class="col-xl-12">
                    <div class="d-grid mb-3">
                        <button class="btn btn-primary w-100 mt-2 p-2" type="submit" id="btn_submit">
                            <i class="fa fa-save"></i> Registrar
                        </button>
                    </div>
                </div>
                <div class="text-center">
                    <hr>
                    <p class="m-0">
                        ¿Ya tienes cuenta? <br><a href="{{ route('login') }}" class="mt-4">Inicia sesión aquí</a>
                    </p>
                </div>
            </form>
        </div>
    </div>
@endsection

@extends('auth.register.register-validator')
