@extends('layout.layout_tailwind')

@section('title', $title ?? 'Usuarios')

@section('contenido')

    <div class=" max-w-7xl mx-auto" x-data="{
        // URLs backend
        storeUrl: '{{ route('user-store') }}',
        updateUrlTemplate: '{{ route('user-update', ':id') }}',
        deleteUrl: '{{ route('user-delete') }}',
    
        // Estado UI
        showUserModal: false,
        showDeleteModal: false,
        isEdit: false,
    
        // Form usuario
        form: {
            id: '',
            rut: '',
            name: '',
            email: '',
            api_access: '1',
            status: '1',
        },
    
        // Datos para eliminación
        deleteUser: {
            id: '',
            name: '',
            email: '',
        },
    
        openCreate() {
            this.isEdit = false;
            this.form = { id: '', rut: '', name: '', email: '', api_access: '1', status: '1' };
            this.showUserModal = true;
        },
    
        openEdit(user) {
            this.isEdit = true;
            this.form = {
                id: user.id,
                rut: user.rut ?? '',
                name: user.name ?? '',
                email: user.email ?? '',
                api_access: String(user.api_access ?? '0'),
                status: String(user.status ?? '0'),
            };
            this.showUserModal = true;
        },
    
        openDelete(user) {
            this.deleteUser = {
                id: user.id,
                name: user.name ?? 'Sin información',
                email: user.email ?? 'Sin información',
            };
            this.showDeleteModal = true;
        },
    
        userFormAction() {
            if (this.isEdit) {
                return this.updateUrlTemplate.replace(':id', this.form.id);
            }
            return this.storeUrl;
        }
    }">

        <!-- Header -->
        <div class="flex items-center justify-between gap-4 mb-12">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">{{ $title ?? 'Usuarios' }}</h1>
                <p class="text-gray-500 mt-1">Gestiona usuarios, accesos API y estados.</p>
            </div>
            <div class="flex items-center gap-3">
                <button type="button" @click="openCreate()"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary-600 text-white hover:bg-primary-700 shadow-sm">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Nuevo Usuario</span>
                </button>
            </div>
        </div>
        <!-- Navigation Tabs -->
        <x-navigation />
        <!-- Mensajes de error globales -->
        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl">
                <strong>Corrige los siguientes errores:</strong>
                <ul class="mt-2 text-sm list-disc ml-6">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Tabla de usuarios -->
        <section class="bg-white shadow-md rounded-xl border border-gray-200">
            <header class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h5 class="text-lg font-semibold text-gray-800">
                    {{ $title_table ?? 'Listado de Usuarios' }}
                </h5>
                <p class="text-xs text-gray-400">
                    Total: {{ count($list_data) }} usuarios
                </p>
            </header>

            <div class="p-4 overflow-x-auto">
                <table class="min-w-full text-sm text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-100 text-gray-600 uppercase text-xs">
                            <th class="px-4 py-3 text-center">ID</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap">RUT</th>
                            <th class="px-4 py-3 whitespace-nowrap">Información Usuario</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap">Acceso API</th>
                            <th class="px-4 py-3 text-center">Estado</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap">Fecha creación</th>
                            <th class="px-4 py-3 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($list_data as $l)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-center text-xs text-gray-500">
                                    {{ $l->id }}
                                </td>

                                <td class="px-4 py-3 text-center">
                                    @php $rutRaw = !empty($l->rut) ? $l->rut : ''; @endphp
                                    <span class="hidden">{{ $rutRaw }}</span>
                                    <span class="text-sm text-gray-800">
                                        {{ !empty($l->rut) ? formateaRut($l->rut) : '-' }}
                                    </span>
                                </td>

                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-xs font-semibold">
                                            {{ !empty($l->username) ? strtoupper(substr($l->username, 0, 2)) : 'S/N' }}
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-sm font-semibold text-gray-900">
                                                {{ !empty($l->name) ? $l->name : 'Sin información' }}
                                            </span>
                                            <span class="text-xs text-gray-500">
                                                {{ !empty($l->email) ? $l->email : 'Sin información' }}
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-4 py-3 text-center">
                                    @if ($l->api_access)
                                        <span class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded">
                                            SI
                                        </span>
                                    @else
                                        <span class="px-2 py-1 text-xs bg-red-100 text-red-700 rounded">
                                            NO
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-center">
                                    @if ($l->status)
                                        <span class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded">
                                            Activo
                                        </span>
                                    @else
                                        <span class="px-2 py-1 text-xs bg-gray-200 text-gray-700 rounded">
                                            Inactivo
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-center text-xs text-gray-500 whitespace-nowrap">
                                    {{ !empty($l->created_at) ? ordenar_fechaHoraHumano($l->created_at) : 'Sin información' }}
                                </td>

                                <td class="px-4 py-3 text-center">
                                    <div class="flex justify-center space-x-2">
                                        <button type="button"
                                            class="px-3 py-1 text-xs bg-primary-500 text-white rounded hover:bg-primary-600 inline-flex items-center gap-1"
                                            @click="openEdit({
                                            id: {{ $l->id }},
                                            rut: @js($rutRaw),
                                            name: @js($l->name),
                                            email: @js($l->email),
                                            api_access: {{ $l->api_access ? 1 : 0 }},
                                            status: {{ $l->status ? 1 : 0 }},
                                        })"
                                            title="Editar">
                                            <i data-lucide="edit" class="w-3 h-3"></i>
                                            <span>Editar</span>
                                        </button>

                                        <button type="button"
                                            class="px-3 py-1 text-xs bg-red-500 text-white rounded hover:bg-red-600 inline-flex items-center gap-1"
                                            @click="openDelete({
                                            id: {{ $l->id }},
                                            name: @js($l->name),
                                            email: @js($l->email),
                                        })"
                                            title="Eliminar">
                                            <i data-lucide="trash-2" class="w-3 h-3"></i>
                                            <span>Eliminar</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-6 text-gray-500">
                                    No hay usuarios registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Modal Crear / Editar Usuario -->
        <div x-show="showUserModal" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40  mt-[-20px]" x-transition>
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden"
                @click.away="showUserModal = false">
                <div class="px-6 py-4 border-b flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                        <i data-lucide="user"></i>
                        <span x-text="isEdit ? 'Editar Usuario' : 'Crear Nuevo Usuario'"></span>
                    </h2>
                    <button type="button" class="text-gray-400 hover:text-gray-600" @click="showUserModal = false">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form method="POST" :action="userFormAction()" class="px-6 py-5 space-y-5" x-ref="userForm">
                    @csrf

                    <input type="hidden" name="user_id" x-model="form.id">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <div class="space-y-2">
                            <label for="rut" class="block text-sm font-medium text-gray-700">
                                RUT <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="rut" id="rut" x-model="form.rut"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white"
                                placeholder="Ingrese rut...">
                            <small id="invalid_rut" class="text-xs text-red-500"></small>
                        </div>

                        <div class="space-y-2">
                            <label for="name" class="block text-sm font-medium text-gray-700">
                                Nombre completo <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" id="name" x-model="form.name" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white"
                                placeholder="Ingrese nombre completo...">
                        </div>

                        <div class="space-y-2">
                            <label for="email" class="block text-sm font-medium text-gray-700">
                                Correo electrónico <span class="text-red-500">*</span>
                            </label>
                            <input type="email" name="email" id="email" x-model="form.email" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white"
                                placeholder="Ingrese correo electrónico...">
                        </div>

                        <div class="space-y-2">
                            <label for="api_access" class="block text-sm font-medium text-gray-700">
                                Acceso API <span class="text-red-500">*</span>
                            </label>
                            <select name="api_access" id="api_access" x-model="form.api_access"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white">
                                <option value="1">SI</option>
                                <option value="0">NO</option>
                            </select>
                            <small id="invalid_api_access" class="text-xs text-red-500"></small>
                        </div>

                        <div class="space-y-2 md:col-span-2">
                            <label for="status" class="block text-sm font-medium text-gray-700">
                                Estado <span class="text-red-500">*</span>
                            </label>
                            <select name="status" id="status" x-model="form.status" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                    </div>

                    {{-- Contraseña solo para creación --}}
                    <template x-if="!isEdit">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            <div class="space-y-2" x-data="{ show: false }">
                                <label for="password" class="block text-sm font-medium text-gray-700">
                                    Contraseña <span class="text-red-500">*</span>
                                </label>
                                <div
                                    class="flex rounded-lg border border-gray-300 focus-within:ring-2 focus-within:ring-primary-500 focus-within:border-primary-500 bg-white overflow-hidden">
                                    <input :type="show ? 'text' : 'password'" name="password" id="password"
                                        class="flex-1 px-3 py-2 text-sm outline-none border-0 bg-transparent"
                                        placeholder="Ingrese contraseña..." minlength="8" required>
                                    <button type="button" class="px-3 text-gray-500 hover:text-gray-700 bg-transparent"
                                        @click="show = !show">
                                        <i data-lucide="eye" x-show="!show" class="w-4 h-4"></i>
                                        <i data-lucide="eye-off" x-show="show" class="w-4 h-4"></i>
                                    </button>
                                </div>
                                <p class="text-xs text-gray-400">Mínimo 8 caracteres.</p>
                            </div>

                            <div class="space-y-2" x-data="{ show: false }">
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                                    Confirmar contraseña <span class="text-red-500">*</span>
                                </label>
                                <div
                                    class="flex rounded-lg border border-gray-300 focus-within:ring-2 focus-within:ring-primary-500 focus-within:border-primary-500 bg-white overflow-hidden">
                                    <input :type="show ? 'text' : 'password'" name="password_confirmation"
                                        id="password_confirmation"
                                        class="flex-1 px-3 py-2 text-sm outline-none border-0 bg-transparent"
                                        placeholder="Confirme contraseña..." minlength="8" required>
                                    <button type="button" class="px-3 text-gray-500 hover:text-gray-700 bg-transparent"
                                        @click="show = !show">
                                        <i data-lucide="eye" x-show="!show" class="w-4 h-4"></i>
                                        <i data-lucide="eye-off" x-show="show" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </div>

                        </div>
                    </template>

                    <div class="flex items-center justify-end gap-3 mt-6 border-t pt-4">
                        <button type="button" class="px-4 py-2 rounded-lg border text-gray-700 hover:bg-gray-100"
                            @click="showUserModal = false">
                            Cancelar
                        </button>
                        <button type="button"
                            class="px-4 py-2 rounded-lg bg-primary-600 text-white hover:bg-primary-700 flex items-center gap-2"
                            @click="$refs.userForm.submit()">
                            <i data-lucide="save" class="w-4 h-4"></i>
                            <span>Guardar Usuario</span>
                        </button>
                    </div>

                </form>
            </div>
        </div>

        <!-- Modal Confirmar Eliminación -->
        <div x-show="showDeleteModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
            x-transition>
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden"
                @click.away="showDeleteModal = false">
                <div class="px-6 py-4 border-b flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                        <i data-lucide="trash-2"></i>
                        Confirmar eliminación
                    </h2>
                    <button type="button" class="text-gray-400 hover:text-gray-600" @click="showDeleteModal = false">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form method="POST" :action="deleteUrl" class="px-6 py-5 space-y-4" x-ref="deleteForm">
                    @csrf
                    <input type="hidden" name="id_modal" x-model="deleteUser.id">

                    <p class="text-sm text-gray-700">
                        ¿Estás seguro de que deseas eliminar este usuario?
                    </p>

                    <div
                        class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-3 py-2 rounded-lg text-sm flex gap-2">
                        <i data-lucide="alert-triangle" class="w-4 h-4 mt-0.5"></i>
                        <span>Esta acción no se puede deshacer.</span>
                    </div>

                    <div class="bg-blue-50 border border-blue-200 text-blue-800 px-3 py-2 rounded-lg text-sm">
                        <div><strong>Nombre:</strong> <span x-text="deleteUser.name"></span></div>
                        <div><strong>Email:</strong> <span x-text="deleteUser.email"></span></div>
                    </div>

                    <div class="flex items-center justify-end gap-3 mt-4 border-t pt-4">
                        <button type="button" class="px-4 py-2 rounded-lg border text-gray-700 hover:bg-gray-100"
                            @click="showDeleteModal = false">
                            Cancelar
                        </button>
                        <button type="button"
                            class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 flex items-center gap-2"
                            @click="$refs.deleteForm.submit()">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                            <span>Eliminar Usuario</span>
                        </button>
                    </div>

                </form>
            </div>
        </div>

    </div>

@endsection
