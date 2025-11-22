@extends('layout.layout_no_login')
@section('contenido')
    <div class="card-body p-sm-6">
        <div class="text-center mb-4">
            <h4 class="mb-1"><b>{{ $title }}</b></h4>
            <hr>
            <h6 class="alert alert-primary text-center"><i class="fa fa-info-circle"></i>
                Esto debido a Inactividad o Inicio de Sesión desde otro dispositivo
            </h6>
        </div>
        <div class="row">
            <div class="col-xl-12">
                <div class="d-grid mb-3">
                    <a class="btn btn-primary w-100 mt-2 p-2" href="{{ route('login') }}">
                        <i class="fa fa-sign-in"></i> Iniciar sesión
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
