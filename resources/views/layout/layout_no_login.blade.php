<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ isset($title) ? $title . ' |' : '' }} {{ env('APP_NAME') }}</title>
    <link rel="shortcut icon" href="{{ asset(URL_LOGO_FAVICON) }}">
    <link id="style" href="{{ asset(ASSETS_LIBS_ADMIN) }}/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset(ASSETS_ADMIN) }}/family/css/login.css" rel="stylesheet">
    <link href="{{ asset(ASSETS_CSS_ADMIN) }}/icons.css" rel="stylesheet">
</head>

<body>
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-xl-8 col-lg-10">
                <div class="login-container" style="margin: 0 auto;">
                    <div class="row g-0">
                        @if (ALERT_MANTENIMIENTO)
                            <div class="col-12">
                                <div class="alert alert-warning m-3">
                                    <i class="fa fa-info-circle me-2"></i>
                                    La aplicación está en mantenimiento. <b>{{ Str::upper(APP_NAME) }}</b>
                                </div>
                            </div>
                        @endif

                        <!-- Panel izquierdo -->
                        <div class="col-md-5 login-left">
                            <div class="text-center">
                                <div class="integration-icon">
                                    <i class="fa fa-plug"></i>
                                </div>
                                <h1>Middleware HITCH</h1>
                                <p>Gestiona y monitorea todas tus integraciones desde una sola plataforma</p>

                                <ul class="feature-list">
                                    <li><i class="fa fa-check"></i> Monitoreo en tiempo real</li>
                                    <li><i class="fa fa-check"></i> Reintegración automática</li>
                                    <li><i class="fa fa-check"></i> Logs detallados</li>
                                    <li><i class="fa fa-check"></i> Alertas inteligentes</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Panel derecho - Formulario -->
                        <div class="col-md-7 login-right">
                            <div class="login-form">
                                <div class="login-header">
                                    <img src="{{ asset(URL_LOGO) }}" alt="logo" width="120"
                                        style="margin-left: -20px;">
                                </div>

                                <!-- Mensajes de alerta -->
                                <div id="alertContainer"></div>

                                @yield('contenido')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="toast-container position-fixed bottom-0 end-0 p-3"></div>

    <script src="{{ asset(ASSETS_JS_ADMIN) }}/jquery-3.6.0.min.js"></script>
    <script src="{{ asset(ASSETS_LIBS_ADMIN) }}/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script nonce="{{ app('csp_nonce') }}">
        function showToast(message, type = 'info') {
            const toastContainer = document.querySelector('.toast-container');
            const toast = document.createElement('div');
            toast.className = toast align-items-center text-white bg-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} border-0;
            toast.setAttribute('role', 'alert');
            toast.innerHTML = 
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fa fa-${type === 'success' ? 'check' : type === 'error' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            ;
            toastContainer.appendChild(toast);
            new bootstrap.Toast(toast).show();
            toast.addEventListener('hidden.bs.toast', () => toast.remove());
        }

        @if (session()->has('success_message'))
            showToast({!! session()->get('success_message') !!}, 'success')
        @endif

        @if (session()->has('warning_message'))
            showToast({!! session()->get('warning_message') !!}, 'warning')
        @endif

        @if (session()->has('danger_message'))
            showToast({!! session()->get('danger_message') !!}, 'error')
        @endif

        @if ($errors->any())
            @php
                $errorMessages = implode('<br>', $errors->all());
                $error = 'Se encontraron los siguientes errores: <br>' . $errorMessages;
            @endphp
            showToast({!! $error !!}, 'error')
        @endif
    </script>
    @yield('js_content')
</body>

</html>