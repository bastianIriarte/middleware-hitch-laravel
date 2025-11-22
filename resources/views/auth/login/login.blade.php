@extends('layout.layout_no_login')
@section('contenido')
    <div class="card-body p-sm-6">
        <div class="text-center mb-4">
            <h4 class="mb-1"><b>Inicio de Sesión</b></h4>
            <p>Inicia sesión para gestionar tus integraciones.</p>
            <hr>
            <!-- Credenciales de demo -->
            {{-- <div class="demo-credentials">
                <h6><i class="fa fa-info-circle me-2"></i>Credenciales de Demo</h6>
                <div class="credential-item">
                    <strong>Admin:</strong> root@hitch.cl / admin
                </div>
                <div class="credential-item">
                    <strong>Usuario:</strong> user@hitch.cl / user123
                </div>
            </div> --}}
        </div>
        <div class="login-status" id="login-status">
            @if (session()->has('danger_message'))
                <div class="msg-error alert alert-danger py-2 px-3 mb-3 fs-14 text-center">
                    <i class="fa fa-circle-exclamation me-2"></i>
                    {!! session()->get('danger_message') !!}
                </div>
            @endif
        </div>
        <div class="row">
            <form action="{{ route('login-post') }}" method="POST" id="form">
                {{ csrf_field() }}
                <div class="col-sm-12">
                    <div class="mb-3">
                        <label class="mb-2 fw-500">Correo electrónico<span class="text-danger ms-1">*</span></label>
                        <input class="form-control ms-0" name="username" id="username" type="email"
                            value="{{ old('username') }}" placeholder="Ingrese correo electrónico...">
                    </div>
                </div>
                <div class="col-sm-12">
                    <div class="mb-3">
                        <label class="mb-2 fw-500">Contraseña<span class="text-danger ms-1">*</span></label>
                        <div>
                            <input type="password" class="form-control" id="password" name="password"
                                placeholder="Ingrese contraseña...">
                        </div>
                    </div>
                </div>

                <div class="col-xl-12">
                    <div class="d-grid mb-3">
                        <button class="btn btn-primary w-100 mt-2 p-2" type="submit" id="btn-submit">
                            <i class="fa fa-sign-in"></i> Iniciar sesión
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
@extends('auth.login.login-validator')
