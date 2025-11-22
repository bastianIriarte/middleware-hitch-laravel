<script nonce="{{ app('csp_nonce') }}">
    function validateFieldsPassword(texto, id, msg = 'Campo Obligatorio') {
        const passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_@]).{8,}$/;
        let messages = [];

        if ($("#" + id)) {
            if (texto !== '') {
                if (id == 'password' || id == 'password_confirm') {
                    if (id == 'password' && !passwordPattern.test(texto)) {
                        $("#" + id).css('border-color', 'red');
                        if (texto.length < 8) messages.push('<li>La contraseña debe tener al menos 8 caracteres.</li>');
                        if (!/[A-Z]/.test(texto)) messages.push(
                            '<li>La contraseña debe tener al menos una letra mayúscula.</li>');
                        if (!/[a-z]/.test(texto)) messages.push(
                            '<li>La contraseña debe tener al menos una letra minúscula.</li>');
                        if (!/\d/.test(texto)) messages.push('<li>La contraseña debe tener al menos un número.</li>');
                        if (!/[\W_]/.test(texto)) messages.push(
                            '<li>La contraseña debe tener al menos un carácter especial.</li>');
                        $("#invalid_" + id).html(`Contraseña inválida`);
                        showPasswordAlert(messages);
                        return 0;
                    } else {
                        if (id == 'password_confirm') {
                            if ($('#password').val() !== texto) {
                                $("#" + id).css('border-color', 'red');
                                $("#invalid_" + id).text('Las contraseñas no coinciden');
                                return 0;
                            } else {
                                $("#" + id).css('border-color', '');
                                $("#invalid_" + id).text('');
                                return 1;
                            }
                        } else {
                            if (id == 'password') {
                                if ($('#password_confirm').val() !== texto) {
                                    $("#" + id).css('border-color', '');
                                    $("#invalid_" + id).text('');
                                    $("#password_confirm").css('border-color', 'red');
                                    $("#invalid_password_confirm").text('Las contraseñas no coinciden');
                                    hidePasswordAlert();
                                    return 0;
                                } else {
                                    $("#" + id).css('border-color', '');
                                    $("#invalid_" + id).text('');
                                    $("#password_confirm").css('border-color', '');
                                    $("#invalid_password_confirm").text('');
                                    hidePasswordAlert();
                                    return 1;
                                }
                            }
                        }
                    }
                } else {
                    $("#" + id).css('border-color', '');
                    $("#invalid_" + id).text('');
                    hidePasswordAlert();
                    return 1;
                }
            } else {
                $("#" + id).css('border-color', 'red');
                $("#invalid_" + id).text(msg);
                // hidePasswordAlert();
                return 0;
            }
        }
    }

    function showPasswordAlert(messages) {
            $("#password-alert").html(`<ul style="list-style-type: circle;padding: 8px;">${messages.join('')}</ul>`).show();
        }

        function hidePasswordAlert() {
            $("#password-alert").hide();
        }
</script>
