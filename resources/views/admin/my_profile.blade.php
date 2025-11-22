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
                            <a class="btn btn-info" href="{{ route('change-password') }}">
                                <i class="fa fa-unlock"></i> Cambiar contraseña
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
                                    <h5 class="text-center text-uppercase fs-14" style="margin: 0 auto;">
                                        <b><?= isset($title_form) ? $title_form : 'Mi perfil' ?></b>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('profile-post') }}" method="POST" id="form">
                                        {{ csrf_field() }}
                                        <div class="row">
                                            <div class="col-md-6 col-xl-3">
                                                <div class="form-group">
                                                    <label for="username">Usuario <span class="text-danger"></span></label>
                                                    <input type="text" id="username" class="form-control" disabled
                                                        value="{{ !empty(auth()->user()->username) ? auth()->user()->username : '' }}">
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-xl-3">
                                                <div class="form-group">
                                                    <label for="profile">Perfil <span class="text-danger"></span></label>
                                                    <input type="text" id="profile" class="form-control" disabled
                                                        value="{{ !empty(auth()->user()->profile->profile) ? auth()->user()->profile->profile : '' }}">
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-xl-3">
                                                <div class="form-group">
                                                    <label for="rut">Rut <span class="text-danger"></span></label>
                                                    <input type="text" id="rut" name="rut" placeholder="Ingrese rut..." 
                                                        value="{{ !empty(auth()->user()->rut) ? formateaRut(auth()->user()->rut) : '' }}"
                                                        class="form-control" maxlength="12">
                                                    <small id="invalid_rut" class="text-danger"></small>
                                                </div>
                                            </div>

                                            <div class="col-md-6 col-xl-3">
                                                <div class="form-group">
                                                    <label for="name">Nombre <span class="text-danger">*</span></label>
                                                    <input type="text" id="name" name="name" class="form-control"
                                                        placeholder="Ingrese Nombre..."
                                                        value="{{ !empty(auth()->user()->name) ? auth()->user()->name : '' }}">
                                                    <small id="invalid_name" class="text-danger"></small>
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-xl-3">
                                                <div class="form-group">
                                                    <label for="email">Correo Electrónico <span
                                                            class="text-danger">*</span></label>
                                                    <input type="text" id="email" name="email" class="form-control"
                                                        value="{{ !empty(auth()->user()->email) ? auth()->user()->email : '' }}"
                                                        placeholder="Ingrese correo...">
                                                    <small id="invalid_email" class="text-danger"></small>
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-xl-3">
                                                <div class="form-group">
                                                    <label class="form-control-label" for="menu_type">MENÚ LATERAL <span
                                                            class="text-danger">*</span></label>
                                                    <select class="form-control" name="menu_type" id="menu_type">
                                                        <option value=""
                                                            {{ auth()->user()->menu_type == '' ? 'selected' : '' }}>
                                                            Expandido</option>
                                                        <option value="sidebar-collapse"
                                                            {{ auth()->user()->menu_type == 'sidebar-collapse' ? 'selected' : '' }}>
                                                            Colapsado</option>
                                                    </select>
                                                    <small id="invalid_menu_type" class="text-danger"></small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <hr class="mt-2 mb-3">
                                            <div class="d-flex flex-wrap justify-content-center align-items-center">
                                                <button class="btn btn-md btn-dark mt-3 mt-sm-0" type="submit"
                                                    id="btn_submit">
                                                    <i class="fa fa-save"></i> Modificar perfil
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
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
    @include('validator-rut')
    @include('validator')
    <script nonce="{{ app('csp_nonce') }}">
        $(document).ready(function() {
            validateField($('#rut').val(), 'rut', 'rut', false);
            validateField($('#name').val(), 'name', 'names', true);
            validateEmail($('#email').val(), 'email');
        });

        $('#rut').keyup(function() {
            validateField($('#rut').val(), 'rut', 'rut', false);
        });

        $('#name').keyup(function() {
            validateField($('#name').val(), 'name', 'names', true);
        });

        $('#email').keyup(function() {
            validateEmail($('#email').val(), 'email', 'Ingrese un Correo Válido')
        });

      

        $("#form").submit(function(e) {
            e.preventDefault();
            let rut = validateField($('#rut').val(), 'rut', 'rut', false);
            let name = validateField($('#name').val(), 'name', 'names', true);
            let email = validateEmail($('#email').val(), 'email');
            if (rut == 1 && name == 1 && email == 1) {
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