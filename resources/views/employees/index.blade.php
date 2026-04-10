@extends('layouts.app')

@section('nav')
    <ul class="nav justify-content-center">
        <li class="nav-item" style="margin: 0 10px 5px 10px;">
            <!-- Margen personalizado: 0 arriba, 20px a los lados, 5px abajo -->
            <a class="nav-link btn btn-primary active" href="{{ route('employees.create') }}">Registro</a>
        </li>
        <li class="nav-item" style="margin: 0 10px 5px 10px;">
            <!-- Margen personalizado: 0 arriba, 20px a los lados, 5px abajo -->
            <a class="nav-link btn btn-secondary" href="{{ route('employees.index') }}">Historico</a>
        </li>
    </ul>
@endsection

@section('header')
    <h1>Lista Empleados</h1>
    <p>Listado de empleados</p>
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
                                <th>Nombre</th>
                                <th>Documento</th>
                                <th>F. nacimiento</th>
                                <th>Teléfono</th>
                                <th>Dirección</th>
                                <th>PIN</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($employees as $employee)
                                <tr>
                                    <td>{{ ($employees->currentPage() - 1) * $employees->perPage() + $loop->iteration }}
                                    </td>
                                    <td>{{ $employee->name }} {{ $employee->last_name }}</td>
                                    <td>{{ $employee->document }}</td>
                                    <td>{{ $employee->birth_date->format('d/m/Y') }}</td>
                                    <td>{{ $employee->phone }}</td>
                                    <td>{{ $employee->address }}</td>
                                    <td>{{ $employee->pin }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-warning edit-employee-btn" data-bs-toggle="modal"
                                            data-bs-target="#editModal" data-id="{{ $employee->id }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>

                                        <button class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                            data-bs-target="#deleteModal" onclick="setDeleteModal({{ $employee->id }})">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">No hay colaboradores registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center mt-3">
                    {{ $employees->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Editar -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="editCollaboratorForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Editar Colaborador</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_nombre" class="form-label">Nombres</label>
                            <input type="text" class="form-control" id="edit_nombre" name="name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_apellido" class="form-label">Apellidos</label>
                            <input type="text" class="form-control" id="edit_apellido" name="last_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_documento" class="form-label">Documento</label>
                            <input type="number" class="form-control" id="edit_documento" name="document" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_nacimiento" class="form-label">F. nacimiento</label>
                            <input type="date" class="form-control" id="edit_nacimiento" name="birth_date" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_telefono" class="form-label">Teléfono</label>
                            <input type="text" class="form-control" id="edit_telefono" name="phone" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="edit_direccion" class="form-label">Dirección</label>
                            <input type="text" class="form-control" id="edit_direccion" name="address" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="edit_pin" class="form-label">PIN</label>
                            <input type="text" class="form-control" id="edit_pin" name="pin" maxlength="4"
                                pattern="\d{4}" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="saveBtn">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Eliminar -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="deleteCollaboratorForm" method="POST"
                    action="{{ route('employees.destroy', $employee->id) }}">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header">
                        <h5 class="modal-title">Eliminar Colaborador</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>¿Estás seguro de que deseas eliminar este colaborador?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Eliminar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        $(document).ready(function() {
            let currentEmployeeId = null;
            $('.edit-employee-btn').on('click', function() {
                currentEmployeeId = $(this).data('id');

                $.ajax({
                    url: "{{ route('employees.edit', ':id') }}".replace(':id', currentEmployeeId),
                    method: 'GET',
                    success: function(response) {
                        if (response.status) {
                            $('#edit_nombre').val(response.data.name);
                            $('#edit_apellido').val(response.data.last_name);
                            $('#edit_documento').val(response.data.document);
                            $('#edit_nacimiento').val(response.data.birth_date.substring(0,
                                10));
                            $('#edit_telefono').val(response.data.phone);
                            $('#edit_direccion').val(response.data.address);
                            $('#edit_pin').val(response.data.pin);

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
            $('#editCollaboratorForm').on('submit', function(e) {
                e.preventDefault();

                const formData = $(this).serialize();

                $.ajax({
                    url: "{{ route('employees.update', ':id') }}".replace(':id',
                        currentEmployeeId),
                    method: 'PUT',
                    data: formData,
                    success: function(response) {
                        if (response.status) {
                            ToastMessage.fire({
                                icon: 'success',
                                text: response.message
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            ToastMessage.fire({
                                icon: 'error',
                                text: response.message
                            });
                        }
                    },
                    error: function(xhr) {
                        console.error('Error:', xhr);
                        let errorMessage = 'Error al actualizar el colaborador.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        ToastMessage.fire({
                            icon: 'error',
                            text: errorMessage
                        });
                    },
                    complete: function() {
                        // Ocultar loading
                        saveBtn.prop('disabled', false);
                        spinner.addClass('d-none');
                    }
                });

            });
            // Limpiar modales al cerrar
            $('#editModal').on('hidden.bs.modal', function() {
                $('#editTableForm')[0].reset();
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
