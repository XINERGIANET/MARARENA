@extends('layouts.app')

@section('nav')
<ul class="nav justify-content-center">
    <li class="nav-item" style="margin: 0 10px 5px 10px;"> <!-- Margen personalizado: 0 arriba, 20px a los lados, 5px abajo -->
        <a class="nav-link btn btn-primary active" href="{{ route('sizes.create') }}">Registro</a>
    </li>
    <li class="nav-item" style="margin: 0 10px 5px 10px;"> <!-- Margen personalizado: 0 arriba, 20px a los lados, 5px abajo -->
        <a class="nav-link btn btn-secondary" href="{{ route('sizes.index') }}">Historico</a>
    </li>
</ul>
@endsection

@section('header')
<h1>Lista Tallas</h1>
<p>Listado de tallas</p>
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
                            <th>Talla</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sizes as $size)
                        <tr>
                            <td>{{ ($sizes->currentPage() - 1) * $sizes->perPage() + $loop->iteration }}</td>
                            <td>{{ $size->name }}</td>
                            <td>
                                <button class="btn btn-sm btn-warning edit-size-btn" 
                                    data-id="{{ $size->id }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-danger delete-size-btn" 
                                    data-id="{{ $size->id }}" 
                                    data-name="{{ $size->name }}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center">No hay tallas registradas.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center mt-3">
                {{ $sizes->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="editSizeForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Editar Talla</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row">
                    <div class="col-12 mb-3">
                        <label for="edit_name" class="form-label">Nombre de la Talla</label>
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
                <h5 class="modal-title">Eliminar Talla</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas eliminar la talla <strong id="delete_size_name"></strong>?</p>
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
    let currentSizeId = null;

    // Editar talla
    $('.edit-size-btn').on('click', function() {
        currentSizeId = $(this).data('id');
        
        // Mostrar modal y limpiar campo
        $('#edit_name').val('');
        $('#editModal').modal('show');
        
        // Obtener datos de la talla
        $.ajax({
            url: "{{ route('sizes.edit', ':id') }}".replace(':id', currentSizeId),
            type: 'GET',
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
                    text: 'Error al cargar los datos de la talla'
                });
            }
        });
    });

    // Guardar cambios de la talla
    $('#editSizeForm').on('submit', function(e) {
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
            url: "{{ route('sizes.update', ':id') }}".replace(':id', currentSizeId),
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
                        text: 'Error al actualizar la talla'
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

    // Eliminar talla
    $('.delete-size-btn').on('click', function() {
        currentSizeId = $(this).data('id');
        const sizeName = $(this).data('name');
        
        $('#delete_size_name').text(sizeName);
        $('#deleteModal').modal('show');
    });

    // Confirmar eliminación
    $('#confirmDeleteBtn').on('click', function() {
        const deleteBtn = $(this);
        const spinner = deleteBtn.find('.spinner-border');
        
        // Mostrar loading
        deleteBtn.prop('disabled', true);
        spinner.removeClass('d-none');
        
        $.ajax({
            url: "{{ route('sizes.destroy', ':id') }}".replace(':id', currentSizeId),
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
                const message = xhr.responseJSON?.message || 'Error al eliminar la talla';
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
        $('#editSizeForm')[0].reset();
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        currentSizeId = null;
    });

    $('#deleteModal').on('hidden.bs.modal', function() {
        currentSizeId = null;
    });
});
</script>
@endsection