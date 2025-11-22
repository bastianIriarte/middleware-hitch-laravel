@section('js_content')
    @include('validator-no-login')
    @include('validator-password')
    <script nonce="{{ app('csp_nonce') }}">
        $(document).ready(function() {
            $('#first_name').keyup(function() {
                validateField($('#first_name').val(), 'first_name', 'names')
            });
            $('#last_name').keyup(function() {
                validateField($('#last_name').val(), 'last_name', 'names')
            });

            $('#email').keyup(function() {
                validateEmail($('#email').val(), 'email', 'Ingrese correo electrónico válido');
            });

            $('#password').keyup(function() {
                validateFieldsPassword($('#password').val(), 'password');
            });
            $('#password_confirm').keyup(function() {
                validateFieldsPassword($('#password_confirm').val(), 'password_confirm');
            });

            $("#form").submit(function(e) {
                e.preventDefault();
                let first_name = validateField($('#first_name').val(), 'first_name', 'names');
                let last_name = validateField($('#last_name').val(), 'last_name', 'names');
                let email = validateEmail($('#email').val(), 'email', 'Ingrese correo electrónico válido');
                let password = validateFieldsPassword($('#password').val(), 'password');
                let password_confirm = validateFieldsPassword($('#password_confirm').val(),
                    'password_confirm');
                if (first_name == 1 && last_name == 1 && email == 1 && password == 1 &&
                    password_confirm == 1) {
                    $("#btn_submit").html(
                        `<span class="spinner-border spinner-border-sm" id="sign_spinner"></span> Validando...`
                    );
                    setTimeout(function() {
                        document.getElementById("form").submit();
                    }, 400);
                } else {
                    toastr["error"](
                        `Se encontraron 1 o más Campos con Problemas. Corrija e Intente nuevamente`,
                        "Error de Validación")
                }
            });


        });
    </script>
@endsection
