<script nonce="{{ app('csp_nonce') }}">
    function validateField(value_data, id, tipo = 'texto', require = true, msg = "Campo Obligatorio") {
        if ($('#' + id)) {
            if (value_data !== '') {
                switch (tipo) {
                    case 'names':
                        value_data = notNumber(value_data, id);
                        value_data = value_data.trim();
                        if (value_data.length < 3) {
                            $("#" + id).css('border-color', 'red');
                            $("#invalid_" + id).html('El largo Mínimo de 3 Caracteres');
                            return 0;
                        } else {
                            if (value_data.length <= 254) {
                                $("#" + id).css('border-color', '');
                                $("#invalid_" + id).html('');
                                return 1;
                            } else {
                                $("#" + id).css('border-color', 'red');
                                $("#invalid_" + id).html('Supera largo máximo permitido');
                                return 0;
                            }
                        }
                        break;
                    case 'mobile':
                        let cel = checkNumero(value_data);
                        $("#" + id).val(cel)
                        $("#" + id).val(soloNumeros(cel))
                        let celval = formatCelular(cel);

                        if (celval == false || $("#" + id).val().length > 12) {
                            $("#" + id).css('border-color', 'red');
                            $("#invalid_" + id).html('N° Incorrecto. Ej: +5691234XXXX');
                            return 0;
                        } else {
                            $("#" + id).css('border-color', '');
                            $("#invalid_" + id).html('');
                            return 1;
                        }
                        break;

                    case 'phone':
                        $("#" + id).val(soloNumeros(value_data))
                        if (value_data.length == 8) {
                            $("#" + id).css('border-color', '');
                            $("#invalid_" + id).html('');
                            return 1;
                        } else {
                            $("#" + id).css('border-color', 'red');
                            $("#invalid_" + id).html('N° Incorrecto. Ej: 22531XXXX');
                            return 0;
                        }
                        break;
                    case 'status':
                        if (value_data == '1' || value_data == '0' || value_data == 1 || value_data == 0) {
                            $("#" + id).css('border-color', '');
                            $("#invalid_" + id).html('');
                            return 1;
                        } else {
                            $('#' + id).css('border-color', 'red');
                            $('#invalid_' + id).html('Seleccione opción Válida');
                            return 0;
                        }
                        break;
                    case 'select':
                        if (value_data != null) {
                            if (value_data.length > 0) {
                                $("#" + id).css('border-color', '');
                                $("#invalid_" + id).html('');
                                return 1;
                            } else {
                                $("#" + id).css('border-color', 'red');
                                $("#invalid_" + id).html(msg);
                                return 0;
                            }
                        } else {
                            $("#" + id).css('border-color', 'red');
                            $("#invalid_" + id).html(msg);
                            return 0;
                        }
                        break;
                    default:
                        value_data = value_data.trim();
                        if (value_data.length > 0) {
                            $("#" + id).css('border-color', '');
                            $("#invalid_" + id).html('');
                            return 1;
                        } else {
                            $("#" + id).css('border-color', 'red');
                            $("#invalid_" + id).html(msg);
                            return 0;
                        }
                        break;
                }

            } else {
                if (require == false) {
                    $("#" + id).css('border-color', '');
                    $("#invalid_" + id).html('');
                    return 1;
                } else {
                    $("#" + id).css('border-color', 'red');
                    $("#invalid_" + id).html(msg);
                    return 0;
                }
            }
        } else {
            toastr["error"](`No existe ID de Campo ${id}`, "ERROR DE VALIDACIÓN")
            return 0;
        }
    }

    function validateEmail(correo, id, msg = 'Campo Obligatorio', require = true) {
        if ($("#" + id)) {
            if (correo !== '') {
                if (IsEmail(correo)) {
                    $("#" + id).css('border-color', '');
                    $("#invalid_" + id).html('');
                    return 1;
                } else {
                    $("#" + id).css('border-color', 'red');
                    $("#invalid_" + id).html(msg);
                    return 0;
                }
            } else {
                if (require == false) {
                    $("#" + id).css('border-color', '');
                    $("#invalid_" + id).html('');
                    return 1;
                } else {
                    $("#" + id).css('border-color', 'red');
                    $("#invalid_" + id).html('Campo Obligatorio');
                    return 0;
                }

            }
        }
    }

    function IsEmail(email) {
        let regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
        if (!regex.test(email)) {
            return false;
        } else {
            return true;
        }
    }

    function soloNumeros(celular) {
        celular = celular.replace(/[^0-9+]/g, '');
        return celular
    }

    function formatCelular(phone) {
        phone = phone.split(' ').join('');
        if (!(/\+569\d{8}/.test(phone))) {
            return false
        }
        return true;
    }

    function checkNumero(numero) {
        if (numero.length == 0) {
            return numero;
        } else if (numero.length < 4) {
            numero = '+569';
        }

        let string = numero;
        if (!~string.indexOf("+569")) {
            string = "+569" + string;
        }
        numero = string;
        return numero;
    }

    function notNumber(string, id) {
        string = string.replace(/[^a-zA-ZñÑáÁéÉíÍóÓúÚ\s]/g, '');
        $("#" + id).val(string)
        return string

    }

    function isValidDate(dateString) {
        // Se crea un objeto Date con la fecha ingresada
        var dateObj = new Date(dateString);

        // Se verifica si es una fecha válida y si el año no es NaN
        if (Object.prototype.toString.call(dateObj) === "[object Date]" && !isNaN(dateObj.getFullYear())) {
            return true;
        } else {
            return false;
        }
    }
</script>