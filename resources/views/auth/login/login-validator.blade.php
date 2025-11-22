@section('js_content')
    <script nonce="{{ app('csp_nonce') }}">
        $(document).ready(function() {
            $('#username').keyup(function() {
                validateField($('#username').val(), 'username')
            });
            $('#password').keyup(function() {
                validateField($('#password').val(), 'password');
            });

            function validateField(texto, id, msg = 'Campo Obligatorio') {
                if ($("#" + id)) {
                    if (texto !== '') {
                        texto = texto.trim();
                        if (texto.length > 0) {
                            $("#" + id).css('border-color', '');
                            $("#invalid_" + id).html('');
                            return 1;
                        }
                        $("#" + id).css('border-color', 'red');
                        $("#invalid_" + id).html(msg);
                        return 0;
                    } else {
                        $("#" + id).css('border-color', 'red');
                        $("#invalid_" + id).text(msg);
                        return 0;
                    }
                }
            }


            $("#form").submit(function(e) {
                e.preventDefault();
                let username = validateField($('#username').val(), 'username')
                let password = validateField($("#password").val(), 'password');
                if (username == 1 && password == 1) {
                    $("#login-status").html(``);
                    $("#btn-submit").html(
                        `<span class="spinner-border spinner-border-sm" id="sign_spinner"></span> Validando...`
                    );
                    $("#btn-submit").attr('disabled');
                    setTimeout(function() {
                        document.getElementById("form").submit();
                    }, 400);
                } else {
                    username == 0 ? $("#username").focus() : $("#password").focus();
                    $("#login-status").html(
                        `<div class="msg-error alert alert-danger py-2 px-3 mb-3 fs-14 text-center">
                        <i class="fa fa-circle-exclamation me-2"></i> Usuario y contraseña son obligatorios
                     </div>`
                    );
                }
            });
        });
    </script>
@endsection
