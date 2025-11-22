<script nonce="{{ app('csp_nonce') }}">
    function validateField(value_data, id, type_data = 'texto', require = true, msg = "Campo Obligatorio") {
        if ($('#' + id)) {
            if (value_data !== '') {
                switch (type_data) {
                    case 'text_min':
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
                    case 'text_min_description':
                        value_data = value_data.trim();
                        if (value_data.length < 3) {
                            $("#" + id).css('border-color', 'red');
                            $("#invalid_" + id).html('El largo Mínimo de 3 Caracteres');
                            return 0;
                        } else {
                            $("#" + id).css('border-color', '');
                            $("#invalid_" + id).html('');
                            return 1;
                        }
                        break;
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
                    case 'profile_names':
                        value_data = notNumber(value_data, id);
                        value_data = value_data.trim();
                        if (value_data.length < 3) {
                            $("#" + id).css('border-color', 'red');
                            $("#invalid_" + id).html('El largo Mínimo de 3 Caracteres');
                            return 0;
                        } else {
                            if (value_data.length <= 60) {
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
                    case 'money':
                        value_data = value_data.trim();
                        value_data = formateaMoneda(value_data);
                        $("#" + id).val(value_data)
                        if (value_data.length < 2) {
                            $("#" + id).css('border-color', 'red');
                            $("#invalid_" + id).html('El valor mínimo debe ser 0');
                            return 0;
                        } else {
                            if (value_data.length <= 11) {
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
                    case 'money_min':
                        value_data = value_data.trim();
                        value_data = formateaMoneda(value_data);
                        $("#" + id).val(value_data)
                        if (value_data.length < 2) {
                            $("#" + id).css('border-color', 'red');
                            $("#invalid_" + id).html('El valor mínimo debe ser 1');
                            return 0;

                        } else {
                            if (value_data.length <= 11) {
                                value_data = soloNumeros(value_data)
                                if (value_data < 1) {
                                    $("#" + id).css('border-color', 'red');
                                    $("#invalid_" + id).html('El valor mínimo debe ser 1');
                                    return 0;
                                }
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
                    case 'number':
                        value_data = formatNumber(value_data)
                        $("#" + id).val(value_data)
                        if (value_data != '') {
                            if (value_data.length >= 1 && value_data.length <= 11) {
                                $("#" + id).css('border-color', '');
                                $("#invalid_" + id).html('');
                                return 1;
                            } else {
                                $("#" + id).css('border-color', 'red');
                                $("#invalid_" + id).html('Supera largo máximo permitido');
                                return 0;
                            }
                        } else {
                            $("#" + id).css('border-color', 'red');
                            $("#invalid_" + id).html(msg);
                            return 0;
                        }
                        break;
                    case 'only_number':
                        value_data = soloNumeros(value_data)
                        $("#" + id).val(value_data)
                        if (value_data != '') {
                            if (value_data.length >= 1 && value_data.length <= 11) {
                                $("#" + id).css('border-color', '');
                                $("#invalid_" + id).html('');
                                return 1;
                            } else {
                                $("#" + id).css('border-color', 'red');
                                $("#invalid_" + id).html('Supera largo máximo permitido');
                                return 0;
                            }
                        } else {
                            $("#" + id).css('border-color', 'red');
                            $("#invalid_" + id).html(msg);
                            return 0;
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
                    case 'rut':
                        let valRut = Rut(value_data, id);
                        if (valRut == false) {
                            $("#" + id).css('border-color', 'red');
                            $("#invalid_" + id).html('Formato de Rut Inválido');
                            return 0;
                        } else {
                            value_data = value_data.replaceAll('.', '')
                            value_data = value_data.replace('-', '')
                            if (value_data == '111111111' || value_data == '222222222' || value_data == '333333333' || value_data == '444444444' || value_data == '555555555' || value_data == '666666666' || value_data == '777777777' || value_data == '888888888' || value_data == '999999999') {
                                $("#" + id).css('border-color', 'red');
                                $("#invalid_" + id).html('Rut Inválido');
                                return 0;
                            } else {
                                $("#" + id).css('border-color', '');
                                $("#invalid_" + id).html('');
                                return 1;
                            }
                        }
                        break;
                    case 'rut_validation':
                        let valRutV = Rut(value_data, id);
                        if (valRutV == false) {
                            $("#" + id).css('border-color', 'red');
                            $("#invalid_" + id).html('Formato de Rut Inválido');
                            return 0;
                        } else {
                            value_data = value_data.replaceAll('.', '')
                            value_data = value_data.replace('-', '')
                            if (value_data == '222222222' || value_data == '333333333' || value_data == '444444444' || value_data == '555555555' || value_data == '666666666' || value_data == '777777777' || value_data == '888888888' || value_data == '999999999') {
                                $("#" + id).css('border-color', 'red');
                                $("#invalid_" + id).html('Rut Inválido');
                                return 0;
                            } else {
                                $("#" + id).css('border-color', '');
                                $("#invalid_" + id).html('');
                                return 1;
                            }
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
                    case 'select_avance':
                        if (require == false) {
                            $("#" + id).selectpicker('setStyle', 'border-danger', 'remove');
                            $("#" + id).selectpicker('refresh');
                            $("#invalid_" + id).text('');
                            return 1;
                        } else {
                            if (value_data != '' && parseInt(value_data) > 0) {
                                $("#" + id).selectpicker('setStyle', 'border-danger', 'remove');
                                $("#" + id).selectpicker('refresh');
                                $("#invalid_" + id).text('');
                                return 1;
                            } else {
                                $("#" + id).selectpicker('setStyle', 'border-danger', 'remove');
                                $("#" + id).selectpicker('setStyle', 'border-danger', 'add');
                                $("#" + id).selectpicker('refresh');
                                $("#invalid_" + id).text(msg);
                                return 0;
                            }
                        }
                        break;
                    case 'url':
                        if (value_data.length >= 0) {
                            if (urlVal(value_data)) {
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
                    case 'date':
                        if (!isValidDate(value_data)) {
                            $("#" + id).css('border-color', 'red');
                            $("#invalid_" + id).html(msg);
                            return 0;
                        } else {
                            $("#" + id).css('border-color', '');
                            $("#invalid_" + id).html('');
                            return 1;
                        }
                        break;
                    case 'checkbox':
                        let respuesta = cuentaCheckbox(1, msg);
                        return respuesta
                        break;
                    case 'file':
                        let img = document.getElementById(id);
                        let error = 0;
                        let archivos_cnt = 0;

                        $.each(img.files, function(i, obj) {
                            //console.log(obj.size);
                            archivos_cnt++;
                            let tamanio = obj.size;
                            console.log(tamanio);
                            if (tamanio > 5000000) {
                                error++;
                                toastr["error"](`Tamaño de Archivo Excede los 5MB permitido`, "Error al Cargar Archivo")
                                $("#" + id).css('border-color', 'red');
                                $("#invalid_" + id).html(msg);

                            }
                        })
                        console.log(error);
                        if (error == 0) {
                            if (archivos_cnt > 5) {
                                toastr["error"](`El Comentario Acepta un máximo de 5 Archivos. Corrija e Intente de Nuevo`, "Error al Cargar Archivo")
                                $('#' + id).val('');
                                return 0;
                            } else {
                                $("#" + id).css('border-color', '');
                                $("#invalid_" + id).html('');
                                return 1;
                            }
                        } else {

                            $('#' + id).val('');
                            return 0;
                        }
                        //});
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

    function quitarEstilos(id) {
        $("#" + id).css('border-color', '');
        $("#invalid_" + id).html('');
    }


    function validateEmail(email_value, id, msg = 'Campo Obligatorio', require = true) {
        if ($("#" + id)) {
            if (email_value !== '') {
                if (IsEmail(email_value)) {
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

    function formatNumber(costo) {
        costo = costo.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ".")
        costo = "" + costo;
        return costo;
    }

    function formateaMoneda(costo) {
        if (costo == null) {
            costo = "0";
        }
        costo = costo.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ".")
        costo = "$" + costo;
        return costo;
    }

    function resetform() {
        $("form select").each(function() {
            this.selectedIndex = 0
        });
        $("form input[type=text] ,form input[type=date] ,form input[type=email] , form textarea").each(function() {
            this.value = ''
        });
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

    function cuentaCheckbox(cant_esperada = 1, msg = 'Debe seleccionar al menos 1') {
        let contador = 0;
        $(".checkbox").each(function() {
            if ($(this).is(":checked")) {
                contador++;
            }
        });
        if (parseInt(contador) < parseInt(cant_esperada)) {
            if ($("#msg_chk")) {
                $("#msg_chk").attr('hidden', false);
            }
            return 0;
        } else {
            $("#msg_chk").attr('hidden', true);
            return 1;
        }
    }

    function validarCheckbox(cant_esperada, groupID, msg = 'Debe seleccionar al menos 1') {
        let contador = 0;
        $(`#${groupID} .checkbox`).each(function() {
            if ($(this).is(":checked")) {
                contador++;
            }
        });

        const mensajeErrorID = `${groupID}_msg_chk`;
        if (parseInt(contador) < parseInt(cant_esperada)) {
            $(`#${mensajeErrorID}`).attr('hidden', false);
            return false;
        } else {
            $(`#${mensajeErrorID}`).attr('hidden', true);
            return true;
        }
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
    // return this.optional(b)||/^(?:(?:(?:https?|ftp):)?\/\/)(?:\S+(?::\S*)?@)?(?:(?!(?:10|127)(?:\.\d{1,3}){3})(?!(?:169\.254|192\.168)(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)(?:\.(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)*(?:\.(?:[a-z\u00a1-\uffff]{2,})).?)(?::\d{2,5})?(?:[\/?#]\S*)?$/i.test(a)}

   

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