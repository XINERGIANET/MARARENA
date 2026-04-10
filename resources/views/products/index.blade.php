@extends('layouts.app')

@section('nav')
    <ul class="nav justify-content-center">
        <li class="nav-item" style="margin: 0 10px 5px 10px;">
            <!-- Margen personalizado: 0 arriba, 20px a los lados, 5px abajo -->
            <a class="nav-link btn btn-primary active" href="{{ route('products.create') }}">Registro</a>
        </li>
        <li class="nav-item" style="margin: 0 10px 5px 10px;">
            <!-- Margen personalizado: 0 arriba, 20px a los lados, 5px abajo -->
            <a class="nav-link btn btn-secondary" href="{{ route('products.index') }}">Historico</a>
        </li>
    </ul>
@endsection

@section('header')
    <h1>Lista Productos</h1>
    <p>Listado de productos</p>
@endsection

@section('content')
    <div class="container-fluid content-inner mt-n5 py-0">
        <!-- Card que contiene el formulario y la tabla -->
        <div class="card shadow">
            <!-- Cuerpo del Card -->
            <div class="card-body">

                <!-- Campo de búsqueda -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control" id="searchProduct"
                                placeholder="Buscar producto por nombre...">
                        </div>
                    </div>
                </div>
                <!-- Tabla de Registros -->
                <div class="table-responsive mt-4">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>N°</th>
                                <th>Producto</th>
                                <th>Categoría</th>
                                <th>Precio de Venta</th>
                                <th>Linea de Venta</th>
                                <th>Talla</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($products as $product)
                                <tr>
                                    <td>{{ ($products->currentPage() - 1) * $products->perPage() + $loop->iteration }}</td>
                                    <td>{{ $product->name }}</td>
                                    <td>{{ $product->category->name ?? 'Sin categoria' }}</td>
                                    <td>{{ $product->unit_price }}</td>
                                    <td>{{ $product->category->sale_line->name ?? 'Sin linea de Venta' }}</td>
                                    <td>{{ $product->size->name ?? 'Sin Talla' }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-warning edit-product-btn"
                                            data-id="{{ $product->id }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger delete-product-btn"
                                            data-id="{{ $product->id }}" data-name="{{ $product->name }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">No hay productos registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center mt-3">
                    {{ $products->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Editar -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="editProductForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Editar Producto</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_name" class="form-label">Nombre del Producto</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                            <div class="invalid-feedback" id="edit_name_error"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_unit_price" class="form-label">Precio Unitario</label>
                            <input type="number" step="0.01" class="form-control" id="edit_unit_price" name="unit_price"
                                required>
                            <div class="invalid-feedback" id="edit_unit_price_error"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_quantity" class="form-label">Cantidad</label>
                            <input type="number" class="form-control" id="edit_quantity" name="quantity" required>
                            <div class="invalid-feedback" id="edit_quantity_error"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_category_id" class="form-label">Categoría</label>
                            <select class="form-control" id="edit_category_id" name="category_id" required>
                                <option value="">Seleccione una categoría</option>
                            </select>
                            <div class="invalid-feedback" id="edit_category_id_error"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_sales_line_name" class="form-label">Línea de Venta</label>
                            <input type="text" class="form-control" id="edit_sales_line_name" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_size_id" class="form-label">Talla</label>
                            <select class="form-control" id="edit_size_id" name="size_id">
                                <option value="">Sin talla</option>
                            </select>
                            <div class="invalid-feedback" id="edit_size_id_error"></div>
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
                    <h5 class="modal-title">Eliminar Producto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que deseas eliminar el producto <strong id="delete_product_name"></strong>?</p>
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
            let currentProductId = null;

            // Editar producto
            $('.edit-product-btn').on('click', function() {
                currentProductId = $(this).data('id');

                // Mostrar modal y limpiar campos
                $('#editProductForm')[0].reset();
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').text('');
                $('#editModal').modal('show');

                // Obtener datos del producto
                $.ajax({
                    url: "{{ route('products.edit', ':id') }}".replace(':id', currentProductId),
                    type: 'GET',
                    success: function(response) {
                        if (response.status) {
                            const product = response.data.product;
                            const categories = response.data.categories;
                            const sizes = response.data.sizes;

                            // Llenar campos del producto
                            $('#edit_name').val(product.name);
                            $('#edit_unit_price').val(product.unit_price);
                            $('#edit_quantity').val(product.quantity);

                            // Llenar select de categorías
                            $('#edit_category_id').empty().append(
                                '<option value="">Seleccione una categoría</option>');
                            categories.forEach(function(category) {
                                const selected = category.id == product.category_id ?
                                    'selected' : '';
                                $('#edit_category_id').append(
                                    `<option value="${category.id}" ${selected}>${category.name}</option>`
                                );
                            });

                            // Mostrar la línea de venta correspondiente a la categoría actual
                            const selectedCategory = categories.find(cat => cat.id == product
                                .category_id);
                            const saleLineName = selectedCategory && selectedCategory
                                .sale_line ? selectedCategory.sale_line.name : '';
                            $('#edit_sales_line_name').val(saleLineName);

                            // Al cambiar la categoría, actualizar el campo de línea de venta
                            $('#edit_category_id').off('change').on('change', function() {
                                const selectedId = $(this).val();
                                const selectedCat = categories.find(cat => cat.id ==
                                    selectedId);
                                const saleLineName = selectedCat && selectedCat
                                    .sale_line ? selectedCat.sale_line.name : '';
                                $('#edit_sales_line_name').val(saleLineName);
                            });

                            // Llenar select de tallas
                            $('#edit_size_id').empty().append(
                                '<option value="">Sin talla</option>');
                            sizes.forEach(function(size) {
                                const selected = size.id == product.size_id ?
                                    'selected' : '';
                                $('#edit_size_id').append(
                                    `<option value="${size.id}" ${selected}>${size.name}</option>`
                                );
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
                        ToastMessage.fire({
                            icon: 'error',
                            text: 'Error al cargar los datos del producto'
                        });
                    }
                });
            });

            // Guardar cambios del producto
            $('#editProductForm').on('submit', function(e) {
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
                    url: "{{ route('products.update', ':id') }}".replace(':id', currentProductId),
                    type: 'PUT',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        name: $('#edit_name').val(),
                        unit_price: $('#edit_unit_price').val(),
                        quantity: $('#edit_quantity').val(),
                        category_id: $('#edit_category_id').val(),
                        size_id: $('#edit_size_id').val() || null
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

                            Object.keys(errors).forEach(function(field) {
                                $(`#edit_${field}`).addClass('is-invalid');
                                $(`#edit_${field}_error`).text(errors[field][0]);
                            });
                        } else {
                            console.error('Error:', xhr);
                            ToastMessage.fire({
                                icon: 'error',
                                text: 'Error al actualizar el producto'
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

            // Eliminar producto
            $('.delete-product-btn').on('click', function() {
                currentProductId = $(this).data('id');
                const productName = $(this).data('name');

                $('#delete_product_name').text(productName);
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
                    url: "{{ route('products.destroy', ':id') }}".replace(':id', currentProductId),
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
                            'Error al eliminar el producto';
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
                $('#editProductForm')[0].reset();
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').text('');
                currentProductId = null;
            });

            $('#deleteModal').on('hidden.bs.modal', function() {
                currentProductId = null;
            });
            // Búsqueda en tiempo real
            let searchTimeout;
            $('#searchProduct').on('keyup', function() {
                clearTimeout(searchTimeout);
                const searchTerm = $(this).val();

                searchTimeout = setTimeout(function() {
                    $.ajax({
                        url: "{{ route('products.index') }}",
                        type: 'GET',
                        data: {
                            search: searchTerm
                        },
                        success: function(response) {
                            const tbody = $('table tbody');
                            tbody.empty();

                            if (response.products.data.length > 0) {
                                response.products.data.forEach(function(product,
                                index) {
                                    const rowNumber = (response.products
                                            .current_page - 1) * response
                                        .products.per_page + index + 1;
                                    const categoryName = product.category ?
                                        product.category.name : 'Sin categoria';
                                    const saleLineName = product.category &&
                                        product.category.sale_line ? product
                                        .category.sale_line.name :
                                        'Sin linea de Venta';
                                    const sizeName = product.size ? product.size
                                        .name : 'Sin Talla';

                                    const row = `
                            <tr>
                                <td>${rowNumber}</td>
                                <td>${product.name}</td>
                                <td>${categoryName}</td>
                                <td>${product.unit_price}</td>
                                <td>${saleLineName}</td>
                                <td>${sizeName}</td>
                                <td>
                                    <button class="btn btn-sm btn-warning edit-product-btn" data-id="${product.id}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-product-btn" data-id="${product.id}" data-name="${product.name}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                                    tbody.append(row);
                                });

                                bindProductButtons();
                            } else {
                                tbody.append(
                                    '<tr><td colspan="7" class="text-center">No hay productos registrados.</td></tr>'
                                    );
                            }

                            updatePagination(response.products);
                        },
                        error: function(xhr) {
                            console.error('Error:', xhr);
                            ToastMessage.fire({
                                icon: 'error',
                                text: 'Error al buscar productos'
                            });
                        }
                    });
                }, 200);
            });

            // Función para actualizar la paginación
            function updatePagination(products) {
                const paginationDiv = $('.d-flex.justify-content-center.mt-3');
                paginationDiv.empty();

                if (products.last_page > 1) {
                    let paginationHtml = '<nav><ul class="pagination">';

                    if (products.current_page > 1) {
                        paginationHtml +=
                            `<li class="page-item"><a class="page-link" href="#" data-page="${products.current_page - 1}">Anterior</a></li>`;
                    }

                    for (let i = 1; i <= products.last_page; i++) {
                        const active = i === products.current_page ? 'active' : '';
                        paginationHtml +=
                            `<li class="page-item ${active}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
                    }

                    if (products.current_page < products.last_page) {
                        paginationHtml +=
                            `<li class="page-item"><a class="page-link" href="#" data-page="${products.current_page + 1}">Siguiente</a></li>`;
                    }

                    paginationHtml += '</ul></nav>';
                    paginationDiv.html(paginationHtml);

                    $('.page-link').on('click', function(e) {
                        e.preventDefault();
                        const page = $(this).data('page');
                        const searchTerm = $('#searchProduct').val();
                        loadProducts(page, searchTerm);
                    });
                }
            }

            // Función para cargar productos con paginación
            function loadProducts(page, searchTerm = '') {
                $.ajax({
                    url: "{{ route('products.index') }}",
                    type: 'GET',
                    data: {
                        page: page,
                        search: searchTerm
                    },
                    success: function(response) {
                        const tbody = $('table tbody');
                        tbody.empty();

                        if (response.products.data.length > 0) {
                            response.products.data.forEach(function(product, index) {
                                const rowNumber = (response.products.current_page - 1) *
                                    response.products.per_page + index + 1;
                                const categoryName = product.category ? product.category.name :
                                    'Sin categoria';
                                const saleLineName = product.category && product.category
                                    .sale_line ? product.category.sale_line.name :
                                    'Sin linea de Venta';
                                const sizeName = product.size ? product.size.name : 'Sin Talla';

                                const row = `
                        <tr>
                            <td>${rowNumber}</td>
                            <td>${product.name}</td>
                            <td>${categoryName}</td>
                            <td>${product.unit_price}</td>
                            <td>${saleLineName}</td>
                            <td>${sizeName}</td>
                            <td>
                                <button class="btn btn-sm btn-warning edit-product-btn" data-id="${product.id}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-danger delete-product-btn" data-id="${product.id}" data-name="${product.name}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                                tbody.append(row);
                            });

                            bindProductButtons();
                        } else {
                            tbody.append(
                                '<tr><td colspan="7" class="text-center">No hay productos registrados.</td></tr>'
                                );
                        }

                        updatePagination(response.products);
                    }
                });
            }

            // Función para re-bindear los eventos de los botones
            function bindProductButtons() {
                $('.edit-product-btn').off('click').on('click', function() {
                    currentProductId = $(this).data('id');
                    $('#editProductForm')[0].reset();
                    $('.is-invalid').removeClass('is-invalid');
                    $('.invalid-feedback').text('');
                    $('#editModal').modal('show');

                    $.ajax({
                        url: "{{ route('products.edit', ':id') }}".replace(':id',
                            currentProductId),
                        type: 'GET',
                        success: function(response) {
                            if (response.status) {
                                const product = response.data.product;
                                const categories = response.data.categories;
                                const sizes = response.data.sizes;

                                $('#edit_name').val(product.name);
                                $('#edit_unit_price').val(product.unit_price);
                                $('#edit_quantity').val(product.quantity);

                                $('#edit_category_id').empty().append(
                                    '<option value="">Seleccione una categoría</option>');
                                categories.forEach(function(category) {
                                    const selected = category.id == product
                                        .category_id ? 'selected' : '';
                                    $('#edit_category_id').append(
                                        `<option value="${category.id}" ${selected}>${category.name}</option>`
                                        );
                                });

                                const selectedCategory = categories.find(cat => cat.id ==
                                    product.category_id);
                                const saleLineName = selectedCategory && selectedCategory
                                    .sale_line ? selectedCategory.sale_line.name : '';
                                $('#edit_sales_line_name').val(saleLineName);

                                $('#edit_category_id').off('change').on('change', function() {
                                    const selectedId = $(this).val();
                                    const selectedCat = categories.find(cat => cat.id ==
                                        selectedId);
                                    const saleLineName = selectedCat && selectedCat
                                        .sale_line ? selectedCat.sale_line.name : '';
                                    $('#edit_sales_line_name').val(saleLineName);
                                });

                                $('#edit_size_id').empty().append(
                                    '<option value="">Sin talla</option>');
                                sizes.forEach(function(size) {
                                    const selected = size.id == product.size_id ?
                                        'selected' : '';
                                    $('#edit_size_id').append(
                                        `<option value="${size.id}" ${selected}>${size.name}</option>`
                                        );
                                });
                            }
                        }
                    });
                });

                $('.delete-product-btn').off('click').on('click', function() {
                    currentProductId = $(this).data('id');
                    const productName = $(this).data('name');
                    $('#delete_product_name').text(productName);
                    $('#deleteModal').modal('show');
                });
            }
        });
    </script>
@endsection
