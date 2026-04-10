@extends('layouts.app')

@section('header')
    <h1>Almacén {{ ucfirst($tipo) }}</h1>
    <p>Listado de productos para {{ ucfirst($tipo) }}</p>
@endsection

@section('content')
    <div class="container-fluid content-inner mt-n5 py-0">
        <div class="card shadow">
            <div class="card-body">
                <!-- Campo de búsqueda -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control" id="searchStorage"
                                placeholder="Buscar producto en almacén...">
                        </div>
                    </div>
                </div>
                <div class="table-responsive mt-4">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>N°</th>
                                <th>Producto</th>
                                <th>Categoría</th>
                                <th>Precio</th>
                                <th>Stock</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($products as $product)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $product->name }}</td>
                                    <td>{{ $product->category->name ?? '-' }}</td>
                                    <td>{{ $product->unit_price }}</td>
                                    <td>{{ $product->quantity }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                            data-bs-target="#stockModal" data-id="{{ $product->id }}"
                                            data-name="{{ $product->name }}" data-quantity="{{ $product->quantity }}">
                                            <i class="bi bi-plus-circle"></i> Modificar Stock
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">No hay productos registrados para esta categoría.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

<!-- Modal para modificar stock -->
<div class="modal fade" id="stockModal" tabindex="-1" aria-labelledby="stockModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formRegistro" action="{{ route('storages.store') }}" method="POST">
                @csrf
                <input type="hidden" name="tipo" value="{{ $tipo }}">
                <div class="modal-header">
                    <h5 class="modal-title" id="stockModalLabel">Modificar Stock</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="modal_product_id" name="product_id">
                    <div class="mb-3">
                        <label for="modal_product_name" class="form-label">Producto</label>
                        <input type="text" class="form-control" id="modal_product_name" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="modal_quantity" class="form-label">Stock</label>
                        <input type="number" class="form-control" id="modal_quantity" name="quantity" min="0"
                            required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

@section('scripts')
    <script>
        const tipo = "{{ $tipo }}";

// Modal de stock
var stockModal = document.getElementById('stockModal');
stockModal.addEventListener('show.bs.modal', function(event) {
    var button = event.relatedTarget;
    var productId = button.getAttribute('data-id');
    var productName = button.getAttribute('data-name');
    var quantity = button.getAttribute('data-quantity');

    var modalProductId = stockModal.querySelector('#modal_product_id');
    var modalProductName = stockModal.querySelector('#modal_product_name');
    var modalQuantity = stockModal.querySelector('#modal_quantity');

    modalProductId.value = productId;
    if (modalProductName) modalProductName.value = productName;
    modalQuantity.value = quantity;
});

// Guardar stock
document.getElementById('formRegistro').addEventListener('submit', function(e) {
    e.preventDefault();
    var form = this;
    var formData = new FormData(form);
    fetch(form.action, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            var modal = bootstrap.Modal.getInstance(stockModal);
            modal.hide();
            location.reload();
        } else {
            alert('Error al actualizar el stock');
        }
    })
    .catch(() => alert('Error al actualizar el stock'));
});

// Búsqueda en tiempo real
let searchTimeout;
$('#searchStorage').on('keyup', function() {
    clearTimeout(searchTimeout);
    const searchTerm = $(this).val();

    searchTimeout = setTimeout(function() {
        $.ajax({
            url: "{{ route('storages.index') }}",
            type: 'GET',
            data: {
                tipo: tipo,
                search: searchTerm
            },
            success: function(response) {
                const tbody = $('table tbody');
                tbody.empty();

                if (response.products.length > 0) {
                    response.products.forEach(function(product, index) {
                        const categoryName = product.category ? product.category.name : '-';
                        
                        const row = `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${product.name}</td>
                                <td>${categoryName}</td>
                                <td>${product.unit_price}</td>
                                <td>${product.quantity}</td>
                                <td>
                                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" 
                                            data-bs-target="#stockModal" data-id="${product.id}" 
                                            data-name="${product.name}" data-quantity="${product.quantity}">
                                        <i class="bi bi-plus-circle"></i> Modificar Stock
                                    </button>
                                </td>
                            </tr>
                        `;
                        tbody.append(row);
                    });
                } else {
                    tbody.append('<tr><td colspan="6" class="text-center">No hay productos registrados para esta categoría.</td></tr>');
                }
            },
            error: function(xhr) {
                console.error('Error:', xhr);
                alert('Error al buscar productos');
            }
        });
    }, 200);
});
    </script>
@endsection
