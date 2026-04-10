@extends('layouts.app')

@section('nav')
<ul class="nav justify-content-center">
    <li class="nav-item" style="margin: 0 10px 5px 10px;">
        <a class="nav-link btn btn-primary active" href="{{ route('purchases.create') }}">Registro</a>
    </li>
    <li class="nav-item" style="margin: 0 10px 5px 10px;">
        <a class="nav-link btn btn-secondary" href="{{ route('purchases.index') }}">Histórico</a>
    </li>
</ul>
@endsection

@section('header')
<h1>Compras</h1>
<p>Registro de compras</p>
@endsection


@section('content')
@include('components.spinner')
<div class="container-fluid content-inner mt-n5 py-0">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body p-3">
                    <div class="header-title w-100">
                        <form id="purchaseForm">
                            @csrf
                            <p><strong>Movimientos</strong></p>
                            <div class="mb-2 row">
                                <label class="col-sm-3 col-form-label text-start">Proveedor:</label>
                                <div class="col-sm-5">
                                    <input type="text" id="search-supplier" class="form-control" placeholder="Buscar proveedor...">
                                    <input type="hidden" id="supplier_id" name="supplier_id">
                                </div>
                                <div class="col-sm-2 mb-3">
                                    <a class="btn btn-primary" id="addProvider" data-bs-toggle="modal" data-bs-target="#providerModal">
                                        <i class="bi bi-plus-lg"></i>
                                    </a>
                                </div>
                            </div>

                            <div class="row" style="display: none;">
                                <label class="col-sm-3 col-form-label text-start">Buscar Producto:</label>
                                <div class="col-sm-4 position-relative">
                                    <input type="text" style="display: block;" class="form-control border-dark" id="search-product" name="search-product" placeholder="Buscar producto...">
                                </div>
                                <div class="col-sm-1 d-flex align-items-center ps-0">
                                    <i class="bi bi-info-circle"
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="right"
                                    data-bs-title="Esto agregará un producto a la tabla de detalles."></i>
                                </div>
                       
                            </div>


                            <hr style="border: none; border-top: 2px solid #888; margin: 20px 0;">

                            <p><strong>Detalle Compra</strong></p>

                            <div class="mb-4 row" style="display: none;">
                                <label class="col-sm-3 col-form-label text-start">Tipo de Comprobante</label>
                                <div class="col-sm-3">
                                    <select class="form-select" id="voucherType" name="voucher_type">
                                        <option value="">Seleccione</option>
                                        <option value="1">Factura</option>
                                        <option value="2">Boleta</option>
                                        <option value="3">Nota de Venta</option>
                                        <option value="4">Otro</option>
                                    </select>
                                </div>
                                <label class="col-sm-3 col-form-label text-start">N° Comprobante (*)</label>
                                <div class="col-sm-3">
                                    <input type="text" class="form-control border-dark" id="invoiceNumber" name="invoice_number" >
                                </div>
                            </div>

                            <div class="mb-4 row">
                                <label class="col-sm-3 col-form-label text-start">Método de Pago</label>
                                <div class="col-sm-3">
                                    <select class="form-control border-dark" id="paymentMethod" name="payment_method_id" required>
                                        <option value="">Seleccione un método</option>
                                        @foreach ($paymentMethods as $method)
                                        <option value="{{ $method->id }}">{{ $method->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <label class="col-sm-3 col-form-label text-start">Fecha de Compra</label>
                                <div class="col-sm-3">
                                    <input type="date" class="form-control border-dark" id="purchaseDate" name="date" required>
                                </div>
                            </div>


                            <hr style="border: none; border-top: 2px solid #888; margin: 20px 0;">

                            <div class="col-12 mb-3">
                                <p><strong>Filtro Búsqueda</strong></p>
                                
                                <div class="row align-items-end">
                                    <!-- Búsqueda al inicio -->
                                    <div class="col-md-4">
                                        <label class="form-label">Producto</label>
                                        <div class="input-group">
                                            <input type="text"
                                                class="form-control"
                                                id="busquedaProducto"
                                                placeholder="Buscar producto...">
                                        </div>
                                    </div>
                                    <div class="col-md-1 d-flex align-items-center ps-0" style="height: 38px;">
                                        <i class="bi bi-info-circle"
                                            data-bs-toggle="tooltip"
                                            data-bs-placement="right"
                                            data-bs-title="Esto agregará un producto a la tabla de detalles."></i>
                                    </div>
                                    
                                    <!-- Espacio en el medio -->
                                    <div class="col-md-3">
                                    </div>
                                    
                                    <!-- Total y botón al final -->
                                    <div class="col-md-4 text-end">
                                        <div class="mb-2">
                                            <strong>Total: S/ <span id="totalAmount">0.00</span></strong>
                                        </div>
                                        <button type="submit" class="btn btn-primary" id="savePurchase">
                                            Guardar Compra
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Tabla de productos agregados -->
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped" id="purchaseTable">
                                    <thead class="table">
                                        <tr>
                                            <th>Producto</th>
                                            <th>Precio Unitario</th>
                                            <th>Cantidad</th>
                                            <th>Subtotal</th>
                                            <th>Accion</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="providerModal" tabindex="-1" aria-labelledby="providerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="providerModalLabel">Agregar Proveedor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="providerForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="document" class="form-label">RUC/DNI</label>
                                <input type="number" class="form-control" id="document" name="document" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="company_name" class="form-label">Razón Social</label>
                                <input type="text" class="form-control" id="company_name" name="company_name" required>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn btn-primary" id="saveSupplier">
                    <i class="fas fa-save"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>


<style>

    .cantidad-input {
        width: 100px;
    }

    /* Limita la altura del menú y añade scroll vertical */
    .ui-autocomplete {
        max-height: 200px;
        /* ajusta la altura a tu gusto */
        overflow-y: auto;
        /* habilita scroll vertical */
        overflow-x: hidden;
        /* evita scroll horizontal */
        /* opcional: para que no tape otros elementos */
        z-index: 1000;
    }

    /* Opcional: mejorar visibilidad de cada ítem */
    .ui-menu-item-wrapper {
        white-space: nowrap;
        padding: 4px 8px;
    }
</style>
@endsection
@section('scripts')

<script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>

<script>

    function collectTableData() {
        const products = [];
        
        $('#purchaseTable tbody tr').each(function() {
            const row = $(this);
            const productId = row.data('product-id');
            const quantity = parseFloat(row.find('.quantity').val()) || 0;
            const price = parseFloat(row.find('.price').val()) || 0;
            const subtotal = parseFloat(row.find('.subtotal').val()) || 0;

            if (quantity > 0) {
                const productData = {
                    product_id: productId,
                    quantity: quantity,
                    price: price,
                    subtotal: subtotal
                };

                // Si es un producto nuevo (ID negativo), agregar datos adicionales
                // if (productId < 0) {
                //     productData.category_id = row.data('category-id');
                //     productData.nombre = row.data('nombre');
                // }

                products.push(productData);
            }
        });

        return products;
    }


    var suppliers = @json($suppliers);
    var newproducts = @json($products);
    var selectedProducts = [];

    $('#search-product').autocomplete({
        source: function(request, response) {
            const term = request.term.toLowerCase();

            const results = newproducts
                .filter(p => p.name.toLowerCase().includes(term))
                .map(p => ({
                    label: p.name,
                    value: p.name,
                    id: p.id,
                }));

            response(results);
        },
        appendTo: '.container-fluid',
        select: function(event, ui) {
            $('#producto_id').val(ui.item.id);
            handleProductClickSelect(ui.item.id);
            $(this).val('');
            return false;
        }
    }).autocomplete("instance")._renderItem = function(ul, item) {
        return $("<li>")
            .append(`<div class="d-flex justify-content-between"><span>${item.label}</span></div>`)
            .appendTo(ul);
    };

    $('#busquedaProducto').autocomplete({
        source: function(request, response) {
            const term = request.term.toLowerCase();

            const results = newproducts
                .filter(p => p.name.toLowerCase().includes(term))
                .map(p => ({
                    label: p.name,
                    value: p.name,
                    id: p.id,
                }));

            response(results);
        },
        appendTo: '.container-fluid',
        select: function(event, ui) {
            $('#producto_id').val(ui.item.id);
            handleProductClickSelect(ui.item.id);
            $(this).val(''); 
            return false;
        }
    }).autocomplete("instance")._renderItem = function(ul, item) {
        return $("<li>")
            .append(`<div class="d-flex justify-content-between"><span>${item.label}</span></div>`)
            .appendTo(ul);
    };

    function handleProductClickSelect(productId) {
        // Buscar el producto en la lista
        const selectedProduct = newproducts.find(p => p.id === productId);

        if (!selectedProduct) return;

        // Verificar si ya existe en la tabla
        const existingRow = $(`#purchaseTable tr[data-product-id="${productId}"]`);

        if (existingRow.length > 0) {
            // Si existe, incrementar cantidad
            const quantityInput = existingRow.find('.quantity');
            const currentQty = parseInt(quantityInput.val()) || 0;
            quantityInput.val(currentQty + 1);
        } else {
            // Si no existe, agregar nueva fila
            const newRow = `
                <tr data-product-id="${productId}">
                    <td>${selectedProduct.name}</td>
                    <td><input type="number" class="form-control text-end unit_price" disabled></td>
                    <td><input type="number" class="form-control text-end quantity" min="0.001" step="0.001"></td>
                    <td><input type="number" class="form-control text-end subtotal" min="0.001" step="0.001"></td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm delete-row">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            $('#purchaseTable tbody').append(newRow);
            attachEventsToRows();
        }

        // Limpiar campo de búsqueda
        $('#search-product').val('');
        $('#busquedaProducto').val('');
    }

    $('#search-supplier').autocomplete({
            source: function(request, response) {
                var matches = $.grep(suppliers, function(item) {
                    return item.company_name.toLowerCase()
                        .includes(request.term.toLowerCase());
                });
                matches = matches.slice(0, 10);
                var results = $.map(matches, function(item) {
                    return {
                        label: item.company_name,
                        value: item.company_name,
                        id: item.id
                    };
                });
                response(results);
            },
            select: function(event, ui) {
                $('#supplier_id').val(ui.item.id); // Guardar el ID en campo oculto
                //cargarProductosProveedor(ui.item.id); no hay productos por proveedor
            },
            appendTo: '.container-fluid'
        })
        .autocomplete("instance")._renderItem = function(ul, item) {
            return $("<li>")
                .append(`<div class="d-flex justify-content-between"><span>${item.label}</span></div>`)
                .appendTo(ul);
        };

    // function cargarProductosProveedor(supplierId) {
    //     $.ajax({
    //         url: `{{ url('/proveedor') }}/${supplierId}/productos`,
    //         method: 'GET',
    //         success: function(productos) {
    //             let tbody = $('#purchaseTable tbody');
    //             tbody.empty();

    //             productos.forEach((producto, index) => {
    //                 tbody.append(`
    //                     <tr data-product-id="${producto.id}" data-type="Producto">
    //                         <td>${producto.category.nombre}</td>
    //                         <td>${producto.nombre}</td>
    //                         <td><input type="number" class="form-control text-end price" disabled></td>
    //                         <td><input type="number" class="form-control text-end quantity" min="0.01" step="0.01"></td>
    //                         <td><input type="number" class="form-control text-end subtotal" min="0.01" step="0.01"></td>
    //                         <td>
    //                             <button type="button" class="btn btn-danger btn-sm delete-row">
    //                                 <i class="fas fa-trash-alt"></i>
    //                             </button>
    //                         </td>
    //                     </tr>
    //                 `);
    //             });

    //             attachEventsToRows(); // Vuelve a asociar eventos
    //         }
    //     });
    // }

    function attachEventsToRows() {
        $('#purchaseTable').on('input', '.quantity, .subtotal', function() {
            const row = $(this).closest('tr');
            const quantity = parseFloat(row.find('.quantity').val()) || 0;
            const subtotal = parseFloat(row.find('.subtotal').val()) || 0;
            const priceField = row.find('.unit_price');

            // Calcular precio unitario basado en cantidad y subtotal
            if (quantity > 0 && subtotal > 0) {
                const unitPrice = (subtotal / quantity).toFixed(2);
                priceField.val(unitPrice);
            } else {
                priceField.val('');
            }

            // Actualizar total general
            updateTotal();
        });
    }


    $('#purchaseForm').on('submit', function(e) {
        e.preventDefault();

        let productsCart = [];
        let suppliesCart = [];

        $('#purchaseTable tbody tr').each(function() {
            let row = $(this);
            let productId = row.data('product-id');
            let quantity = parseFloat(row.find('.quantity').val());
            let subtotal = parseFloat(row.find('.subtotal').val());
            let unit_price = parseFloat(row.find('.unit_price').val());

            if (productId && quantity >= 0.01 && subtotal >= 0 && unit_price >= 0) {
                const item = {
                    product_id: productId,
                    quantity: quantity,
                    unit_price: unit_price,
                    subtotal: subtotal,
                };

                productsCart.push(item);
                
            }
        });

        if (productsCart.length === 0) {
            spinner.classList.add('spinner-hidden');
            spinner.classList.remove('spinner-visible');

            ToastError.fire({
                icon: 'warning',
                text: 'Debe agregar al menos un producto'
            });

            return;
        }

        // Mostrar spinner
        spinner.classList.remove('spinner-hidden');
        spinner.classList.add('spinner-visible');

        // Preparar los datos para enviar
        let data = {
            _token: $('input[name="_token"]').val(),
            supplier_id: $('#supplier_id').val(),
            voucher_type: $('#voucherType').val(),
            invoice_number: $('#invoiceNumber').val(),
            payment_method_id: $('#paymentMethod').val(),
            total: $('#totalAmount').text(),
            date: $('#purchaseDate').val(),
            products: JSON.stringify(productsCart)
        };

        // Debug: mostrar los datos que se van a enviar
        console.log("Datos a enviar:", data);
        console.log("Carrito:", productsCart);

        // Enviar los datos mediante AJAX
        $.ajax({
            url: '{{ route('purchases.store') }}',
            method: 'POST',
            data: data,
            success: function(response) {
                spinner.classList.add('spinner-hidden');
                spinner.classList.remove('spinner-visible');

                if (response.status) {
                    ToastMessage.fire({
                        icon: 'success',
                        text: response.message || 'Operación exitosa'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    // Error del backend
                    ToastError.fire({
                        text: response.error || 'Ocurrió un error'
                    });
                }
            },

           error: function(xhr, status, error) {
            spinner.classList.add('spinner-hidden');
            spinner.classList.remove('spinner-visible');

            console.log("Error en la petición:");
            console.log("Products enviados:", productsCart);
            console.log("Supplies enviados:", suppliesCart);
            console.log("XHR Response:", xhr);
            console.log("XHR Status:", status);
            console.log("XHR Error:", error);

            let mensaje = 'Ocurrió un error al procesar la compra';

            if (xhr.responseJSON) {
                if (xhr.responseJSON.error) {
                    mensaje = xhr.responseJSON.error;
                } else if (xhr.responseJSON.message) {
                    mensaje = xhr.responseJSON.message;
                }
            } else if (xhr.responseText) {
                mensaje = xhr.responseText;
            }

            ToastError.fire({
                text: mensaje
            });
        }

        });
    });

    function updateTotal() {
        let total = 0;

        $('#purchaseTable tbody tr').each(function() {
            let subtotal = parseFloat($(this).find('.subtotal').val()) || 0;
            total += subtotal;
        });

        $('#totalAmount').text(total.toFixed(2));
    }

    document.getElementById('saveSupplier').addEventListener('click', function() {
        var docum = document.getElementById('document').value.trim();
        var companyName = document.getElementById('company_name').value.trim();

        if (docum === "" || companyName === "") {
            alert("Los campos son obligatorios");
            return;
        }

        var data = {
            document: docum,
            company_name: companyName
        };

        var saveBtn = this;
        var originalText = saveBtn.innerHTML;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
        saveBtn.disabled = true;

        fetch('{{ route('suppliers.saveSupplier') }}', {
                    method: 'POST', // o el método que necesites
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(data),
                })
            .then(response => response.json())
            .then(data => {
                console.log('Respuesta:', data);

                if (data.success) {
                    // Mostrar mensaje de éxito
                    ToastMessage.fire({
                        icon: 'success',
                        text: data.message || 'Operación exitosa' // Corregido: usar data.message en lugar de response.message
                    }).then(() => {
                        console.log(data.supplier);
                        suppliers.push(data.supplier);
                    });

                    // Cerrar modal
                    // const modal = document.getElementById('providerModal');
                    // const bsModal = bootstrap.Modal.getInstance(modal);
                    
                    //limpiar y esconder
                    document.getElementById('document').value = "";
                    document.getElementById('company_name').value = "";
                    $('#providerModal').modal('hide');
                    

                } else {
                    ToastError.fire({
                        text: data.message || 'Error al agregar el proveedor'
                    });
                }
            })
            .catch(error => {
                console.error('Error completo:', error);
                alert('Error: ' + error.message);
            })
            .finally(() => {
                // Restaurar estado del botón
                saveBtn.innerHTML = originalText;
                saveBtn.disabled = false;
            });
    });

    // Evento para eliminar fila
    $('#purchaseTable').on('click', '.delete-row', function () {
        $(this).closest('tr').remove();
        updateTotal(); // actualizar total
    });

</script>
@endsection