@extends('layouts.app')

@section('nav')
    <ul class="nav justify-content-center">
        <li class="nav-item" style="margin: 0 10px 5px 10px;">
            <!-- Margen personalizado: 0 arriba, 20px a los lados, 5px abajo -->
            <a class="nav-link btn btn-primary active" href="{{ route('payment_methods.create') }}">Registro</a>
        </li>
        <li class="nav-item" style="margin: 0 10px 5px 10px;">
            <!-- Margen personalizado: 0 arriba, 20px a los lados, 5px abajo -->
            <a class="nav-link btn btn-secondary" href="{{ route('payment_methods.index') }}">Historico</a>
        </li>
    </ul>
@endsection

@section('header')
    <h1>Lista Métodos de Pago</h1>
    <p>Listado de métodos de pago</p>
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
                                <th>N°</th>
                                <th>Método de Pago</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pms as $method)
                                <tr>
                                    <td>{{ ($pms->currentPage() - 1) * $pms->perPage() + $loop->iteration }}</td>
                                    <td>{{ $method->name }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-warning edit-method-btn"
                                            data-id="{{ $method->id }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger delete-method-btn"
                                            data-id="{{ $method->id }}" data-name="{{ $method->name }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center">No hay métodos de pago registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center mt-3">
                    {{ $pms->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Editar -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="editMethodForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Editar Método de Pago</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body row">
                        <div class="col-12 mb-3">
                            <label for="edit_name" class="form-label">Nombre del Método</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                            <div class="invalid-feedback" id="edit_name_error"></div>
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

    <!-- Modal Eliminar -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Eliminar Método de Pago</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que deseas eliminar el método de pago <strong id="delete_method_name"></strong>?</p>
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
            let methodId = null;

            //al presionar el boton editar
            $('.edit-method-btn').on('click', function() {
                methodId = $(this).data('id');


                // Mostrar modal y limpiar campo
                $('#edit_name').val('');
                $('#editModal').modal('show');

                //Cargar los datos
                $.ajax({
                    url: "{{ route('payment_methods.edit', ':id') }}".replace(':id', methodId),
                    method: 'GET',
                    success: function(response) {
                        if (response.status) {
                            $('#edit_name').val(response.data.name);
                        } else {
                            ToastMessage.fire({
                                icon: 'error',
                                text: response.message
                            });
                        }
                    },
                    error: function(xhr) {
                        console.error('Error:', xhr);
                        ToastMessage.fire({
                            icon: 'error',
                            text: 'Error al cargar los datos de la mesa'
                        });
                    }
                });
            });

            // Guardar cambios al editar
            $('#editMethodForm').on('submit', function(e) {
                e.preventDefault();
                const saveBtn = $('#editSaveBtn');
                const spinner = saveBtn.find('.spinner-border');

                // Mostrar loading
                saveBtn.prop('disabled', true);
                spinner.removeClass('d-none');

                // Limpiar errores previos
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').text('');

                $.ajax({
                    url: "{{ route('payment_methods.update', ':id') }}".replace(':id', methodId),
                    type: 'PUT',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        name: $('#edit_name').val()
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
                                $('#edit_name').addClass('is-invalid');
                                $('#edit_name_error').text(errors.name[0]);
                            }
                        } else {
                            console.error('Error:', xhr);
                            ToastMessage.fire({
                                icon: 'error',
                                text: 'Error al actualizar la mesa'
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

            //Eliminar el metodo de pago
            $('.delete-method-btn').on('click', function() {
                methodId = $(this).data('id');
                const methodName = $(this).data('name');

                $('#delete_method_name').text(methodName);
                $('#deleteModal').modal('show');
            });

            //Confirmar eliminacion
            $('#confirmDeleteBtn').on('click', function() {
                const deleteBtn = $(this);
                const spinner = deleteBtn.find('.spinner-border');

                // Mostrar loading
                deleteBtn.prop('disabled', true);
                spinner.removeClass('d-none');

                $.ajax({
                    url: "{{ route('payment_methods.destroy', ':id') }}".replace(':id', methodId),
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
                            'Error al eliminar la mesa';
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
                $('#editMethodForm')[0].reset();
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').text('');
                currentTableId = null;
            });

            $('#deleteModal').on('hidden.bs.modal', function() {
                currentTableId = null;
            });
        });
    </script>
@endsection
