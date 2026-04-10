@extends('layouts.app')

@section('nav')
    <ul class="nav justify-content-center">
        <li class="nav-item" style="margin: 0 10px 5px 10px;">
            <!-- Margen personalizado: 0 arriba, 20px a los lados, 5px abajo -->
            <a class="nav-link btn btn-primary active" href="{{ route('roles.create') }}">Registro</a>
        </li>
        <li class="nav-item" style="margin: 0 10px 5px 10px;">
            <!-- Margen personalizado: 0 arriba, 20px a los lados, 5px abajo -->
            <a class="nav-link btn btn-secondary" href="{{ route('roles.index') }}">Historico</a>
        </li>
    </ul>
@endsection

@section('header')
    <h1>Lista Roles</h1>
    <p>Listado de roles</p>
@endsection

@section('content')
    <div class="container-fluid content-inner mt-n5 py-0">
        <!-- Card que contiene el formulario y la tabla -->
        <div class="card shadow">
            <!-- Cuerpo del Card -->
            <div class="card-body">
                <!-- Tabla de Registros -->
                <div class="table-responsive mt-4">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($roles as $rol)
                                <tr>
                                    <td>{{ $rol->name }}</td>
                                    <td>{{ $rol->description }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-warning edit-rol-btn" data-bs-toggle="modal"
                                            data-bs-target="#editModal" data-id="{{ $rol->id }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger delete-rol-btn" data-bs-toggle="modal"
                                            data-bs-target="#deleteModal" data-id="{{ $rol->id }}"
                                            data-name="{{ $rol->name }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center">No hay roles registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center mt-3">
                    {{ $roles->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>

    <!--Modal de edicion de rol-->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="editRolForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Editar rol</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body row">
                        <div class="col-12 mb-3">
                            <label for="edit_name_rol" class="form-label">Nombre de rol</label>
                            <input type="text" class="form-control" id="edit_name_rol" name="name_rol" required>
                            <div class="invalid-feedback" id="edit_name_error"></div>
                        </div>
                    </div>
                    <div class="modal-body row">
                        <div class="col-12 mb-3">
                            <label for="edit_description_rol" class="form-label">Descripción de rol</label>
                            <input type="text" class="form-control" id="edit_description_rol" name="description_rol"
                                required>
                            <div class="invalid-feedback" id="edit_description_error"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="editSaveBtn">
                            <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                            Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!--Modal de eliminacion de rol-->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Eliminar rol</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que deseas eliminar el rol <strong id="delete_rol_name"></strong>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                        Eliminar
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            let currentRolId = null;

            $('.edit-rol-btn').on('click', function() {
                currentRolId = $(this).data('id');

                $('#edit_name_rol').val('');
                $('#edit_description_rol').val('');
                $('#editModal').modal('show');

                $.ajax({
                    url: "{{ route('roles.edit', ':id') }}".replace(':id', currentRolId),
                    type: 'GET',
                    success: function(response) {
                        if (response.status) {
                            $('#edit_name_rol').val(response.data.name);
                            $('#edit_description_rol').val(response.data.description);
                        } else {
                            ToastMessage.fire({
                                icon: 'error',
                                text: response.message
                            })
                        }
                    },
                    error: function(xhr) {
                        console.error('Error:', xhr);
                        ToastMessage.fire({
                            icon: 'error',
                            text: 'Error al cargar los datos de rol'
                        });
                    }
                })
            })
            $('#editRolForm').on('submit', function(e) {
                e.preventDefault();

                const saveBtn = $('#editSaveBtn');
                const spinner = saveBtn.find('.spinner-border');

                saveBtn.prop('disabled', true);
                spinner.removeClass('d-none');

                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').text('');

                $.ajax({
                    url: "{{ route('roles.update', ':id') }}".replace(':id',
                        currentRolId),
                    type: 'PUT',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        name: $('#edit_name_rol').val(),
                        description: $('#edit_description_rol').val()
                    },
                    success: function(response) {
                        if (response.status) {
                            $('#editModal').modal('hide');
                            ToastMessage.fire({
                                icon: 'success',
                                text: response.message
                            });

                            // Recargar la página después de 1 segundo
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        } else {
                            ToastMessage.fire({
                                icon: 'error',
                                text: response.message
                            });
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            // Errores de validación
                            const errors = xhr.responseJSON.errors;

                            if (errors.name) {
                                $('#edit_name_rol').addClass('is-invalid');
                                $('#edit_name_error').text(errors.name[0]);
                            }
                        } else {
                            console.error('Error:', xhr);
                            ToastMessage.fire({
                                icon: 'error',
                                text: 'Error al actualizar rol'
                            });
                        }
                    },
                    complete: function() {
                        // Ocultar loading
                        saveBtn.prop('disabled', false);
                        spinner.addClass('d-none');
                    }
                });
            });
            $('.delete-rol-btn').on('click', function() {
                currentRolId = $(this).data('id');
                const rolName = $(this).data('name');

                $('#delete_rol_name').text(rolName);
                $('#deleteModal').modal('show');
            });

            $('#confirmDeleteBtn').on('click', function() {
                const deleteBtn = $(this);
                const spinner = deleteBtn.find('.spinner-border');

                // Mostrar loading
                deleteBtn.prop('disabled', true);
                spinner.removeClass('d-none');

                $.ajax({
                    url: "{{ route('roles.destroy', ':id') }}".replace(':id',
                        currentRolId),
                    type: 'DELETE',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        $('#deleteModal').modal('hide');

                        if (response.status) {
                            ToastMessage.fire({
                                icon: 'success',
                                text: response.message
                            });

                            // Recargar la página después de 1 segundo
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        } else {
                            ToastMessage.fire({
                                icon: 'error',
                                text: response.message
                            });
                        }
                    },
                    error: function(xhr) {
                        console.error('Error:', xhr);
                        const message = xhr.responseJSON?.message ||
                            'Error al eliminar el rol';
                        ToastMessage.fire({
                            icon: 'error',
                            text: message
                        });
                    },
                    complete: function() {
                        // Ocultar loading
                        deleteBtn.prop('disabled', false);
                        spinner.addClass('d-none');
                    }
                });
            });

            // Limpiar modales al cerrar
            $('#editModal').on('hidden.bs.modal', function() {
                $('#editRolForm')[0].reset();
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').text('');
                currentCategoryId = null;
            });

            $('#deleteModal').on('hidden.bs.modal', function() {
                currentCategoryId = null;
            });
        })
    </script>
@endsection
