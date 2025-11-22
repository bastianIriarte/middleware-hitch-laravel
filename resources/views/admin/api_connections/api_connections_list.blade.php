@extends('layout.layout_admin')
@section('contenido')
    <div class="content-wrapper">
        <div class="page-title">
            <i class="fa fa-plug me-2"></i>{{ $title }}
        </div>

        <div class="card p-3">

            @if (session()->has('danger_message'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {!! session()->get('danger_message') !!}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- Nav tabs --}}
            <ul class="nav nav-tabs" id="integrationTabs" role="tablist">
                @foreach ($integrations as $key => $integration)
                    @if ((!empty($integration['data']) && $integration['data']->status) || empty($integration['data']))
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $loop->first ? 'active' : '' }}" id="{{ strtolower($key) }}-tab"
                                data-bs-toggle="tab" data-bs-target="#{{ strtolower($key) }}" type="button"
                                role="tab">{{ $key }}</button>
                        </li>
                    @endif
                @endforeach
            </ul>

            {{-- Tab content --}}
            <div class="tab-content p-3" id="integrationTabsContent">
                @foreach ($integrations as $key => $integration)
                    @if ((!empty($integration['data']) && $integration['data']->status) || empty($integration['data']))
                        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="{{ strtolower($key) }}"
                            role="tabpanel">

                            <form id="{{ strtolower($key) }}Form" method="POST"
                                action="{{ route('api-connections-update') }}">
                                @csrf
                                <input type="hidden" name="integration" value="{{ $key }}" />
                                <div class="row g-3">
                                    @foreach ($integration['fields'] as $field => $meta)
                                        <div class="col-md-6">
                                            <label for="{{ strtolower($key . '_' . $field) }}" class="form-label">
                                                {{ $meta['label'] }} <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <input type="{{ $meta['type'] }}" class="form-control"
                                                    id="{{ strtolower($key . '_' . $field) }}"
                                                    name="{{ strtolower($field) }}"
                                                    value="{{ !empty($integration['data']->$field) ? $integration['data']->$field : '' }}"
                                                    placeholder="{{ $meta['placeholder'] }}">
                                                @if (in_array($field, ['password', 'api_key']))
                                                    <button class="btn btn-outline-secondary toggle-password" type="button"
                                                        data-target="{{ strtolower($key . '_' . $field) }}">
                                                        <i class="fa fa-eye"></i>
                                                    </button>
                                                @endif
                                            </div>
                                            <div class="invalid-feedback" id="{{ strtolower($key . '_' . $field) }}_error">
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="mt-3 text-end">
                                    <button type="button" class="btn btn-primary saveBtn"
                                        data-form="{{ strtolower($key) }}Form">
                                        <i class="fa fa-save me-1"></i> Guardar Configuración {{ $key }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
@endsection

@section('js_content')
    <script nonce="{{ app('csp_nonce') }}">
        // Toggle show/hide password
        $(document).on('click', '.toggle-password', function() {
            let targetId = $(this).data('target');
            let input = $(`#${targetId}`);
            let icon = $(this).find('i');

            if (input.attr('type') === 'password') {
                input.attr('type', 'text');
                icon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                input.attr('type', 'password');
                icon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });

        $(document).ready(function() {
            function validateField(id, validator, message) {
                const input = $(`#${id}`);
                const errorDiv = $(`#${id}_error`);
                const value = input.val().trim();
                if (!validator(value)) {
                    input.addClass('is-invalid');
                    errorDiv.text(message);
                    return false;
                }
                input.removeClass('is-invalid');
                errorDiv.text('');
                return true;
            }

            $('.saveBtn').on('click', function() {
                let formId = $(this).data('form');
                let valid = true;

                $(`#${formId} input`).each(function() {
                    let id = $(this).attr('id');
                    let val = $(this).val().trim();

                    // Reglas mínimas: required para text, min 4 para password/apikey
                    if ($(this).attr('type') === 'text' && val.length === 0) {
                        valid &= validateField(id, v => v.length > 0, 'Campo requerido.');
                    }
                    if ($(this).attr('type') === 'password') {
                        if (val.length === 0) {
                            valid = false;
                            $(this).addClass('is-invalid');
                            $(`#${id}_error`).text('Campo requerido.');
                        } else if (val.length < 4) {
                            valid = false;
                            $(this).addClass('is-invalid');
                            $(`#${id}_error`).text('Mínimo 4 caracteres.');
                        } else {
                            $(this).removeClass('is-invalid');
                            $(`#${id}_error`).text('');
                        }
                    }

                });

                if (!valid) return;
                $(this).prop('disabled', true).find('i').toggleClass('fa-save fa-spinner fa-spin');
                $(`#${formId}`).submit();
            });
        });
    </script>
@endsection
