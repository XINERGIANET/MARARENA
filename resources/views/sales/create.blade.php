@extends('layouts.app')

@section('header')
<h1>Ventas</h1>
<p>Registrar una nueva venta</p>
@endsection

@section('content')
@php
$colors = ['btn-outline-primary', 'btn-outline-success', 'btn-outline-info', 'btn-outline-warning', 'btn-outline-danger', 'btn-outline-dark'];
@endphp
<div class="container-fluid content-inner mt-n5 py-0">
    <!-- Card que contiene el formulario y la tabla -->
    <div class="card shadow">
        <!-- Cuerpo del Card -->
        <div class="card-body">
            <form action="{{ route('sales.store') }}" id="saveSale" method="POST" autocomplete="off">
                @csrf
                <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">
                <input hidden type="number" name="type_sale" value="0">
                <div class="row">
                    <div class="col-xl-4 col-lg-12 order-2 order-lg-1 mt-4 mt-lg-0">
                        <div class="btn-group d-flex justify-content-start mb-4">
                            <button type="button" class="btn btn-outline-primary me-1" id="btn-boleta">Boleta</button>
                            <button type="button" class="btn btn-outline-success me-1" id="btn-factura">Factura</button>
                            <button type="button" class="btn btn-outline-info me-1" id="btn-ticket">Ticket</button>
                        </div>
                        <div class="mb-2 row">
                            <label class="col-sm-4 col-form-label text-start"><strong>Documento</strong></label>
                            <div class="col-sm-8">
                                <div class="input-group">
                                    <input type="text" class="form-control form-control-xs" id="document"
                                        name="document" maxlength="11" onkeypress="isNumber(event)">
                                    <button type="button" class="btn btn-primary btn-xs"
                                        onclick="searchAPI('#document','#name','#address')"><i
                                            class="bi bi-search"></i></button>
                                </div>
                            </div>
                        </div>
                        <div class="mb-2 row">
                            <label class="col-form-label text-start"><strong>Cliente</strong></label>
                            <div class="col-sm-12">
                                <input type="text" class="form-control form-control-sm" id="client" name="client" >
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><strong>Empleado</strong></label>
                            <select class="form-control" name="employee_id" id="employee_id">
                                <option value="">Seleccione un empleado</option>
                                @foreach ($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->name }} {{ $employee->last_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="d-flex mb-3">
                            <div class="form-check me-4">
                                <input class="form-check-input" type="checkbox" value="1" id="anticipada" name="anticipada">
                                <label class="form-check-label" for="anticipada">
                                    Por Entregar
                                </label>
                            </div>
                        </div>

                        <input hidden type="number" name="type_sale" id="type_sale" value="0">
                        <input hidden type="number" name="status" id="status" value="1">
                        <input hidden type="number" name="type_status" id="type_status" value="0">

                        <div id="grupo-fecha-entrega" style="display: none;">
                            <label for="fecha_entrega" class="mb-2"><strong>Fecha de entrega</strong></label>
                            <input type="date" id="fecha_entrega" name="fecha_entrega"
                                class="form-control form-control-sm mb-4"
                                onkeydown="return false;" onpaste="return false;">
                            
                            <label for="hora_entrega" class="form-label"><strong>Hora de entrega:</strong></label>
                            <input type="text" class="form-control form-control-sm mb-4" id="hora_entrega" name="hora_entrega">

                        </div>
                        <label class="mb-2"><strong>Teléfono</strong></label>
                        <input type="text" id="telefono" name="telefono"
                            class="form-control form-control-sm mb-4">
                        <label class="mb-2"><strong>Dirección</strong></label>
                        <input type="text" id="direccion" name="direccion"
                            class="form-control form-control-sm mb-4">
                        <label class="mb-2"><strong>Referencia</strong></label>
                        <input type="text" id="referencia" name="referencia"
                            class="form-control form-control-sm mb-4">
                        
                        <label class="mb-2"><strong>Observación</strong></label>
                        <input type="text" id="observacion" name="observacion"
                            class="form-control form-control-sm ">
                        <div class="d-flex flex-column mb-5 mt-3">
                            <label class="mb-2"><strong>Método de Pago</strong></label>
                            <div class="d-flex flex-wrap">
                                @foreach ($pms as $index => $method)
                                @php
                                $colorClass = $colors[$index % count($colors)];
                                @endphp
                                <button type="button"
                                    id="btn-{{ $method->id }}"
                                    class="btn {{ $colorClass }} me-2 mb-2"
                                    data-campos="campos-{{ $method->name }}"
                                    data-id="{{ $method->id }}"
                                    onclick="seleccionarMedioPago('{{ $method->id }}', event)">
                                    {{ strtoupper($method->name) }}
                                </button>
                                @endforeach
                            </div>
                        </div>
                        <!-- HTML actualizado - Solo mostrar vuelto para Efectivo -->
                        @foreach ($pms as $method)
                        <div class="d-flex align-items-center mb-4 d-none" id="campos-{{ $method->name }}">
                            <label class="mb-2 me-3"><strong>{{ strlen($method->name) > 4 ? strtoupper(substr($method->name, 0, 4) . '.') : strtoupper($method->name) }}</strong></label>
                            <input hidden type="number" name="medio_pago_id" value="{{ $method->id }}">
                            <div class="input-group me-2">
                                <span class="input-group-text">S/</span>
                                <input type="text" class="form-control" placeholder="Ingrese Monto"
                                    name="monto[{{ $method->id }}]"
                                    onkeypress="isDecimal(event)"
                                    oninput="calcularVueltoEfectivo('{{ $method->name }}', '{{ $method->id }}', this)">
                            </div>
                            <!-- Campo de vuelto - SOLO para efectivo -->
                            @if(strtolower($method->name) === 'efectivo')
                            <div class="input-group me-2">
                                <input type="text" class="form-control" placeholder="0.00" style="width: 150px;"
                                    id="vuelto-efectivo" readonly>
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    <div class="col-xl-8 col-lg-12 order-1 order-lg-2">
                        <!-- Seleccionar Productos -->
                        <div class="form-group">
                            <label class="col-sm-3 col-form-label text-start">Producto:</label>
                            <div class="col-md-12">
                                <input type="text" id="search-product" class="form-control" placeholder="Buscar Producto...">
                                <input type="hidden" id="product_id" name="product_id">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="producto_id"
                                class="col-sm-3 col-form-label text-start"><strong>Categorías</strong></label>
                            <div class="mb-3">
                                @foreach ($pc as $category)
                                <button class="btn btn-outline-primary btn-sm m-1" type="button"
                                    onclick="handleCategoryClick('{{ $category->id }}')">
                                    {{ $category->name }}
                                </button>
                                @endforeach
                            </div>
                        </div>
                        <div id="product-container"></div>
                        <div class="table-responsive mt-4">
                            <table class="table table-bordered table-striped text-xs">
                                <thead>
                                    <tr class="text-center">
                                        <th>N°</th>
                                        <th>Producto</th>
                                        <th>Cantidad</th>
                                        <th>Precio</th>
                                        <th>Subtotal</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- Botón guardar: SIEMPRE al final -->
                    <div class="col-12 order-3 mt-4 text-end">
                        <h5><strong>TOTAL: S/ <span id="totalAmount" name="total">0.00</span></strong></h5>
                        <h6><strong>SALDO: S/ <span id="saldoAmount">0.00</span></strong></h6>
                        <input hidden type="number" step="0.01" name="total" id="totalAmountInput" value="0">
                        <button class="btn btn-success mt-3" type="button" id="btnSaveSale">Guardar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        if ($('#anticipada').is(':checked')) {
            $('#grupo-fecha-entrega').show();
        }

        $('#anticipada').change(function() {
            if ($(this).is(':checked')) {
                $('#grupo-fecha-entrega').show();
                $('#fecha_entrega').val('');
                $('#hora_entrega').val('');
                $('#fecha_entrega').prop('required', true);
                $('#hora_entrega').prop('required', true);
            } else {
                $('#grupo-fecha-entrega').hide();
                $('#fecha_entrega').prop('required', false);
                $('#hora_entrega').prop('required', false);
            }
        });
    });
</script>
@endsection

@section('scripts')
<script>
    var serial = "{{ config('printer.serial') }}";
    
    $(document).ready(function() {
        $('#anticipada').change(function() {
            const isChecked = $(this).is(':checked');
            if (isChecked) {
                // Si está marcado: type_sale = 1, status = 0, type_status = 1
                $('#type_sale').val(0);
                $('#status').val(0);
                $('#type_status').val(1);
                console.log('Venta anticipada activada: type_sale=1, status=0, type_status=1');
            } else {
                // Si no está marcado: type_sale = 0, status = 1, type_status = 0
                $('#type_sale').val(0);
                $('#status').val(1);
                $('#type_status').val(0);
                console.log('Venta normal: type_sale=0, status=1, type_status=0');
            }
        });

        // Inicializar valores por defecto (checkbox desmarcado)
        $('#type_sale').val(0);
        $('#status').val(1);
        $('#type_status').val(0);

        // Configurar botones de tipo de comprobante
        $('#btn-boleta').click(function() {
            selectVoucherType('Boleta', this);
        });

        $('#btn-factura').click(function() {
            selectVoucherType('Factura', this);
        });

        $('#btn-ticket').click(function() {
            selectVoucherType('Ticket', this);
        });

        // Seleccionar Boleta por defecto
        selectVoucherType('Ticket', document.getElementById('btn-ticket'));
    });

    let clientSearchTimeout = null;
    $('#search-product').autocomplete({
        source: function(request, response) {
            clearTimeout(clientSearchTimeout);
            clientSearchTimeout = setTimeout(function() {
                let currentTerm = $('#search-product').val();
                // Solo buscar si hay al menos una letra
                if (currentTerm && currentTerm.length > 0) {
                    $.ajax({
                        url: "{{ route('products.searchpv') }}",
                        method: 'GET',
                        data: {
                            query: currentTerm
                        },
                        success: function(data) {
                            response($.map(data, function(item) {
                                return {
                                    label: item.name + ' - Stock: ' + (item.quantity || 0) + ' - S/ ' + parseFloat(item.unit_price || 0).toFixed(2),
                                    value: item.name,
                                    id: item.id,
                                    name: item.name,
                                    unit_price: item.unit_price,
                                    quantity: item.quantity || 0
                                };
                            }));
                        }
                    });
                } else {
                    // Si no hay letras, limpia el autocomplete
                    response([]);
                }
            }, 900);
        },
        appendTo: '.container-fluid',
        select: function(event, ui) {
            // Agregar producto directamente a la tabla cuando se selecciona
            if (ui.item.quantity > 0) {
                handleProductClick(ui.item.id, ui.item.name, ui.item.unit_price, ui.item.quantity);
                // Limpiar el campo de búsqueda
                $('#search-product').val('');
                $('#product_id').val('');
            } else {
                alert('Este producto no tiene stock disponible.');
                $('#search-product').val('');
            }
            return false; // Previene que se llene el input con el valor
        },
    }).autocomplete("instance")._renderItem = function(ul, item) {
        const stockClass = item.quantity > 0 ? 'text-success' : 'text-danger';
        const stockText = item.quantity > 0 ? 'Disponible' : 'Sin Stock';
        return $("<li>")
            .append(`<div class="d-flex justify-content-between">
                        <span>${item.name}</span>
                        <small class="${stockClass}">${stockText}</small>
                     </div>`)
            .appendTo(ul);
    };

    // Variables globales para manejo de productos
    let productTableCounter = 0;
    const productTableBody = document.querySelector('tbody');

    function handleCategoryClick(categoryId) {
        const productContainer = document.getElementById('product-container');

        // Mostrar loader mientras carga
        productContainer.innerHTML = '<div class="text-center p-3"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div></div>';

        // Hacer petición AJAX para obtener productos de la categoría
        $.ajax({
            url: "{{ route('sales.getProductsByCategory', '') }}/" + categoryId,
            method: 'GET',
            success: function(products) {
                // Limpiar contenedor
                productContainer.innerHTML = '';

                if (products && products.length > 0) {
                    // Obtener nombre de la categoría del botón
                    const categoryButton = document.querySelector(`button[onclick="handleCategoryClick(${categoryId})"]`);
                    const categoryName = categoryButton ? categoryButton.textContent.trim() : 'Categoría';

                    // Crear título de la categoría
                    const categoryTitle = document.createElement('h6');
                    categoryTitle.className = 'mt-3 mb-2 text-primary';
                    categoryTitle.innerHTML = `<strong>Productos de ${categoryName}:</strong>`;
                    productContainer.appendChild(categoryTitle);

                    // Crear contenedor para los productos
                    const productsDiv = document.createElement('div');
                    productsDiv.className = 'd-flex flex-wrap gap-2'; // Cambia aquí

                    products.forEach(producto => {
                        const productCol = document.createElement('div');

                        const productElement = document.createElement('button');
                        productElement.className = "btn btn-outline-success btn-sm";
                        productElement.type = "button";

                        // Mostrar nombre del producto con stock y precio
                        const stock = producto.quantity || 0;
                        const precio = parseFloat(producto.unit_price || 0).toFixed(2);

                        productElement.innerHTML = `
                            <div class="text-start">
                                <div class="fw-bold">${producto.name.toUpperCase()} (${stock})</div>
                            </div>
                        `;

                        productElement.onclick = function() {
                            handleProductClick(producto.id, producto.name, producto.unit_price, stock);
                        };

                        productCol.appendChild(productElement);
                        productsDiv.appendChild(productCol);
                    });

                    productContainer.appendChild(productsDiv);
                } else {
                    // Mostrar mensaje si no hay productos
                    const noProductsMsg = document.createElement('div');
                    noProductsMsg.className = 'alert alert-info mt-3';
                    noProductsMsg.textContent = 'No hay productos disponibles en esta categoría.';
                    productContainer.appendChild(noProductsMsg);
                }

                // Resaltar categoría seleccionada
                document.querySelectorAll('button[onclick*="handleCategoryClick"]').forEach(btn => {
                    btn.className = 'btn btn-outline-primary btn-sm m-1';
                });

                const selectedButton = document.querySelector(`button[onclick="handleCategoryClick(${categoryId})"]`);
                if (selectedButton) {
                    selectedButton.className = 'btn btn-primary btn-sm m-1';
                }
            },
            error: function() {
                productContainer.innerHTML = '<div class="alert alert-danger mt-3">Error al cargar los productos. Por favor, intente nuevamente.</div>';
            }
        });
    }

    function handleProductClick(productId, productName, unitPrice, stock) {
        // Verificar si el producto ya está en la tabla
        const existingRow = document.querySelector(`tr[data-product-id="${productId}"]`);

        if (existingRow) {
            // Si ya existe, incrementar cantidad
            const quantityInput = existingRow.querySelector('.quantity-input');
            const currentQuantity = parseInt(quantityInput.value);

            const newQuantity = currentQuantity + 1;
            quantityInput.value = newQuantity;
            updateRowSubtotal(existingRow, unitPrice, newQuantity);
            updateTotal();
        } else {
            // Agregar nueva fila
            addProductToTable(productId, productName, unitPrice, stock);
        }
    }

    function addProductToTable(productId, productName, unitPrice, stock) {
        productTableCounter++;

        const row = document.createElement('tr');
        row.setAttribute('data-product-id', productId);

        row.innerHTML = `
            <td class="text-center">${productTableCounter}</td>
            <td>${productName}</td>
            <td class="text-center">
                <div class="input-group" style="width: 120px; margin: 0 auto;">
            <input type="number" class="form-control form-control-sm text-center quantity-input" 
                value="1" min="1"
                onchange="updateQuantity(this, ${unitPrice})"
                name="products[${productId}][cantidad]">
                </div>
        <input type="hidden" name="products[${productId}][id]" value="${productId}">
        <input type="hidden" name="products[${productId}][precio]" value="${unitPrice}">
            </td>
            <td class="text-center">S/ ${parseFloat(unitPrice).toFixed(2)}</td>
            <td class="text-center subtotal">S/ ${parseFloat(unitPrice).toFixed(2)}</td>
            <td class="text-center">
                <button type="button" class="btn btn-danger btn-sm" onclick="removeProduct(this)">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;

        productTableBody.appendChild(row);
        updateTotal();
    }

    function updateQuantity(input, unitPrice) {
        let value = parseInt(input.value);

        // Solo validar que sea un número positivo
        if (isNaN(value) || value < 1) {
            value = 1;
        }

        input.value = value;
        const row = input.closest('tr');
        updateRowSubtotal(row, unitPrice, value);
        updateTotal();
    }

    function updateRowSubtotal(row, unitPrice, quantity) {
        const subtotal = unitPrice * quantity;
        const subtotalCell = row.querySelector('.subtotal');
        subtotalCell.textContent = `S/ ${subtotal.toFixed(2)}`;
    }

    function removeProduct(button) {
        if (confirm('¿Está seguro de eliminar este producto?')) {
            const row = button.closest('tr');
            row.remove();
            updateTotal();
            renumberRows();
        }
    }

    function renumberRows() {
        const rows = productTableBody.querySelectorAll('tr');
        rows.forEach((row, index) => {
            row.querySelector('td:first-child').textContent = index + 1;
        });
        productTableCounter = rows.length;
    }

    function updateTotal() {
        const subtotalCells = document.querySelectorAll('.subtotal');
        let total = 0;

        subtotalCells.forEach(cell => {
            const value = parseFloat(cell.textContent.replace('S/ ', '')) || 0;
            total += value;
        });

        document.getElementById('totalAmount').textContent = total.toFixed(2);
        document.getElementById('totalAmountInput').value = total.toFixed(2);

        // Si el total es 0, resetear métodos de pago
        if (total === 0) {
            resetPaymentMethods();
        } else {
            // Si hay un método de pago seleccionado, actualizar el monto automáticamente
            const metodoPagoActivo = document.querySelector('[id^="campos-"]:not(.d-none)');
            if (metodoPagoActivo) {
                const montoInput = metodoPagoActivo.querySelector('input[name^="monto["]');
                if (montoInput) {
                    const montoActual = parseFloat(montoInput.value) || 0;
                    // Solo actualizar si el campo está vacío o si era igual al total anterior
                    if (montoActual === 0 || Math.abs(montoActual - parseFloat(document.getElementById('totalAmountInput').getAttribute('data-previous-total') || 0)) < 0.01) {
                        montoInput.value = total.toFixed(2);
                    }

                    // Trigger del evento para recalcular vuelto si es efectivo
                    const event = new Event('input', {
                        bubbles: true
                    });
                    montoInput.dispatchEvent(event);
                }
            }
        }

        // Guardar el total actual para la próxima comparación
        document.getElementById('totalAmountInput').setAttribute('data-previous-total', total.toFixed(2));

        // Actualizar saldo
        calcularSaldo();
    }

    function calcularSaldo() {
        const total = parseFloat(document.getElementById('totalAmountInput').value) || 0;
        let totalPagado = 0;

        // Sumar todos los montos de pago visibles
        document.querySelectorAll('input[name^="monto["]').forEach(input => {
            const container = input.closest('.d-flex, .mb-3, .mb-4');
            if (container && !container.classList.contains('d-none') && container.style.display !== 'none') {
                totalPagado += parseFloat(input.value) || 0;
            }
        });

        const saldo = total - totalPagado;
        const saldoElement = document.getElementById('saldoAmount');

        if (saldoElement) {
            if (total === 0) {
                saldoElement.textContent = '0.00';
                saldoElement.className = '';
            } else {
                saldoElement.textContent = (saldo).toFixed(2);

                // Cambiar color según el saldo
                if (saldo > 0.01) {
                    saldoElement.className = 'text-danger'; // Debe dinero
                } else if (saldo < -0.01) {
                    saldoElement.className = 'text-warning'; // Sobra dinero (vuelto)
                } else {
                    saldoElement.className = 'text-success'; // Exacto
                }
            }
        }

        return saldo;
    }

    function resetPaymentMethods() {
        // Limpiar todos los campos de monto
        const botonesActivos = document.querySelectorAll('[id^="btn-"].active');
        botonesActivos.forEach(boton => {
            boton.classList.remove('active');
            const camposId = boton.dataset.campos;
            $(`#${camposId}`).addClass('d-none');
            $(`#${camposId} input[type="text"]`).val('');
        });
        
        // Limpiar vuelto
        const campoVuelto = document.getElementById('vuelto-efectivo');
        if (campoVuelto) {
            campoVuelto.value = '0.00';
        }
        
        calcularSaldo();
    }

    // Función mejorada para calcular vuelto en efectivo
    function calcularVueltoEfectivo(nombreMetodo, idMetodo, inputElement) {
        // Solo calcular para efectivo
        if (nombreMetodo.toLowerCase() !== 'efectivo') {
            calcularSaldo();
            return;
        }

        const botonesActivos = document.querySelectorAll('[id^="btn-"].active[data-campos]');
        const totalVenta = parseFloat($('#totalAmount').text()) || 0;
        const campoVuelto = document.getElementById('vuelto-efectivo');

        if (!campoVuelto) {
            calcularSaldo();
            return;
        }

        let totalPagado = 0;
        let montoEfectivo = parseFloat(inputElement.value) || 0;

        // Calcular total pagado con todos los métodos activos
        botonesActivos.forEach(boton => {
            const camposId = boton.dataset.campos;
            const inputMonto = document.querySelector(`#${camposId} input[name^="monto["]`);
            const monto = parseFloat(inputMonto?.value) || 0;
            totalPagado += monto;
        });

        // Calcular vuelto solo si se paga más del total
        if (totalPagado > totalVenta) {
            const vueltoCalculado = totalPagado - totalVenta;
            
            // Validar que el vuelto no exceda el efectivo ingresado
            if (vueltoCalculado <= montoEfectivo) {
                campoVuelto.value = vueltoCalculado.toFixed(2);
                
                // Guardar el monto real de efectivo (lo que realmente se queda)
                const montoEfectivoReal = montoEfectivo - vueltoCalculado;
                inputElement.setAttribute('data-monto-real', montoEfectivoReal.toFixed(2));
            } else {
                // El vuelto excede el efectivo - resetear
                campoVuelto.value = '0.00';
                inputElement.removeAttribute('data-monto-real');
            }
        } else {
            campoVuelto.value = '0.00';
            inputElement.removeAttribute('data-monto-real');
        }

        calcularSaldo();
    }

    // Funciones auxiliares para validación
    function isNumber(event) {
        const charCode = (event.which) ? event.which : event.keyCode;
        // Permitir números (48-57), backspace (8), delete (46), tab (9)
        if (charCode < 48 || charCode > 57) {
            if (charCode !== 8 && charCode !== 46 && charCode !== 9) {
                event.preventDefault();
                return false;
            }
        }
        return true;
    }

    function isDecimal(event) {
        const charCode = (event.which) ? event.which : event.keyCode;
        const input = event.target;
        const value = input.value;

        // Permitir números (48-57), punto decimal (46), backspace (8), delete (46), tab (9)
        if ((charCode < 48 || charCode > 57) && charCode !== 46) {
            if (charCode !== 8 && charCode !== 9) {
                event.preventDefault();
                return false;
            }
        }

        // Permitir solo un punto decimal
        if (charCode === 46 && value.indexOf('.') !== -1) {
            event.preventDefault();
            return false;
        }

        return true;
    }

    function seleccionarMedioPago(medio_id, event) {
        const btn = event.target;
        const total = parseFloat(document.getElementById('totalAmountInput').value) || 0;
        const campos = btn.dataset.campos;

        btn.classList.toggle('active');

        // Mostrar u ocultar el contenedor
        const $campo = $(`#${campos}`);
        const $input = $campo.find(`input[name="monto[${medio_id}]"]`);

        if (btn.classList.contains('active')) {
            $campo.removeClass('d-none');
        } else {
            $campo.addClass('d-none');
            $input.val(''); 
        }

        // Verificar cuántos métodos están activos
        const botonesActivos = document.querySelectorAll('[id^="btn-"].active');
        const totalActivos = botonesActivos.length;

        if (totalActivos === 1) {
            const btnActivo = botonesActivos[0];
            const medioActivoId = btnActivo.dataset.id;
            const campoActivo = document.querySelector(`#campos-${btnActivo.dataset.campos?.split('-')[1]}`);
            const inputActivo = campoActivo?.querySelector(`input[name="monto[${medioActivoId}]"]`);
            if (inputActivo) {
                inputActivo.value = total.toFixed(2);
            }
        } else {
            document.querySelectorAll('[id^="campos-"]:not(.d-none)').forEach(div => {
                const input = div.querySelector('input[name^="monto["]');
                if (input) input.value = '0.00';
            });
        }

        calcularSaldo();
    }

    function actualizarMontosMetodosPago() {
        const contenedor = document.querySelector('.d-flex.flex-wrap');
        const botonesActivos = contenedor.querySelectorAll('.btn.active');

        if (botonesActivos.length === 1) {
            const botonActivo = botonesActivos[0];
            const camposId = botonActivo.dataset.campos;
            const idActivo = botonActivo.dataset.id;
            const total = $('#totalAmount').text().trim();

            $(`#${camposId} input[name="monto[${idActivo}]"]`).val(total);
        } else {
            botonesActivos.forEach(boton => {
                const camposId = boton.dataset.campos;
                $(`#${camposId} input[type="text"]`).val('');
            });
        }

        calcularSaldo();
    }

    // Función para buscar por API (placeholder)
    function searchAPI(docEl, nameEl, addressEl) {
        var doc = $(docEl).val();

        $(nameEl).val('');
        $(addressEl).val('');
        $('#client').val('');

        if (doc.length != 8 && doc.length != 11) {
            return;
        }

        Swal.showLoading();

        $.ajax({
            url: "{{ url('sunat/consultar') }}?doc=" + doc,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    var data = response.data;
                    if (doc.length === 8) {
                        var fullName = `${data.nombre} ${data.apellido_paterno} ${data.apellido_materno}`;
                        $(nameEl).val(fullName);
                        $(addressEl).val(data.domicilio?.direccion || '');
                        $('#client').val(fullName);
                    } else {
                        $(nameEl).val(data.nombre);
                        $(addressEl).val(data.domicilio?.direccion || '');
                        $('#client').val(data.nombre);
                    }
                } else {
                    ToastError.fire({ text: response.message || 'No se encontró información' });
                }
                Swal.close();
            },
            error: function(xhr) {
                ToastError.fire({ text: 'Error al consultar SUNAT/RENIEC' });
                Swal.close();
            }
        });
    }

    // Función para seleccionar tipo de comprobante
    function selectVoucherType(type, button) {
        // Remover clases activas de todos los botones
        document.querySelectorAll('#btn-boleta, #btn-factura, #btn-ticket').forEach(btn => {
            btn.className = btn.className.replace('btn-primary', 'btn-outline-primary')
                .replace('btn-success', 'btn-outline-success')
                .replace('btn-info', 'btn-outline-info');
        });

        // Activar el botón seleccionado
        if (type === 'Boleta') {
            button.className = 'btn btn-primary me-1';
        } else if (type === 'Factura') {
            button.className = 'btn btn-success me-1';
        } else if (type === 'Ticket') {
            button.className = 'btn btn-info me-1';
        }

        // Crear campo hidden para voucher_type si no existe
        let voucherTypeInput = document.querySelector('input[name="voucher_type"]');
        if (!voucherTypeInput) {
            voucherTypeInput = document.createElement('input');
            voucherTypeInput.type = 'hidden';
            voucherTypeInput.name = 'voucher_type';
            // Preferir el formulario real; usar fallback a cualquier formulario en la página
            const formEl = document.getElementById('saveSale') || document.querySelector('form');
            if (formEl) {
                formEl.appendChild(voucherTypeInput);
            } else {
                // No encontramos un lugar lógico donde añadirlo; adjuntar al body como último recurso
                console.warn('No se encontró el formulario para añadir voucher_type; se adjunta al body');
                document.body.appendChild(voucherTypeInput);
            }
        }
        voucherTypeInput.value = type;

        console.log('Tipo de comprobante seleccionado:', type);
    }

    function convertirMontoALetras(monto) {
        const [entero, decimal] = monto.toFixed(2).split('.');
        const parteEntera = parseInt(entero);
        const centavos = parseInt(decimal);

        let resultado = '';

        if (parteEntera === 0) {
            resultado = 'cero soles';
        } else if (parteEntera === 1) {
            resultado = 'un sol';
        } else if (parteEntera < 1000) {
            resultado = numeroALetras(parteEntera) + ' soles';
        } else {
            // Para miles
            const miles = Math.floor(parteEntera / 1000);
            const resto = parteEntera % 1000;

            if (miles === 1) {
                resultado = 'mil';
            } else {
                resultado = numeroALetras(miles) + ' mil';
            }

            if (resto > 0) {
                resultado += ' ' + numeroALetras(resto);
            }

            resultado += ' soles';
        }

        // Agregar centavos
        if (centavos > 0) {
            resultado += ' con ' + numeroALetras(centavos) + ' céntimos';
        }

        return resultado.toUpperCase();
    }

    function numeroALetras(num) {
        const unidades = ['', 'uno', 'dos', 'tres', 'cuatro', 'cinco', 'seis', 'siete', 'ocho', 'nueve'];
        const decenas = ['', '', 'veinte', 'treinta', 'cuarenta', 'cincuenta', 'sesenta', 'setenta', 'ochenta', 'noventa'];
        const especiales = ['diez', 'once', 'doce', 'trece', 'catorce', 'quince', 'dieciséis', 'diecisiete', 'dieciocho', 'diecinueve'];
        const centenas = ['', 'ciento', 'doscientos', 'trescientos', 'cuatrocientos', 'quinientos', 'seiscientos', 'setecientos', 'ochocientos', 'novecientos'];

        if (num === 0) return 'cero';
        if (num === 100) return 'cien';

        let resultado = '';

        // Centenas
        if (num >= 100) {
            resultado += centenas[Math.floor(num / 100)] + ' ';
            num %= 100;
        }

        // Decenas y unidades
        if (num >= 20) {
            resultado += decenas[Math.floor(num / 10)];
            if (num % 10 !== 0) {
                resultado += ' y ' + unidades[num % 10];
            }
        } else if (num >= 10) {
            resultado += especiales[num - 10];
        } else if (num > 0) {
            resultado += unidades[num];
        }

        return resultado.trim();
    }

    // --- ENVÍO AJAX DEL FORMULARIO DE VENTA ---
    $('#btnSaveSale').on('click', function(e) {
        e.preventDefault();

        // Validar productos
        const productRows = document.querySelectorAll('tbody tr');
        if (productRows.length === 0) {
            ToastMessage.fire({
                icon: 'error',
                text: 'Debe seleccionar al menos un producto.'
            });
            return;
        }

        // Validar método de pago
        const metodoPagoActivo = document.querySelector('[id^="campos-"]:not(.d-none)');
        if (!metodoPagoActivo) {
            ToastMessage.fire({
                icon: 'error',
                text: 'Debe seleccionar al menos un método de pago.'
            });
            return;
        }

        // Validar monto de pago
        const montoInput = metodoPagoActivo.querySelector('input[name^="monto["]');
        const montoValue = parseFloat(montoInput.value) || 0;
        if (montoValue <= 0) {
            ToastMessage.fire({
                icon: 'error',
                text: 'Debe ingresar un monto válido para el método de pago seleccionado.'
            });
            return;
        }

        // Validar RUC si es factura
        const esAnticipada = document.getElementById('anticipada').checked;
        const voucherType = document.querySelector('input[name="voucher_type"]').value.toLowerCase();
        const documentValue = document.getElementById('document').value.trim();
        if (voucherType === 'factura' && (!documentValue || documentValue.length !== 11)) {
            ToastError.fire({
                text: 'Debe ingresar un RUC válido de 11 dígitos.'
            });
            return;
        }

        const botonesActivos = document.querySelectorAll('[id^="btn-"].active[data-campos]');

        let totalPagado = 0;
        let hayEfectivo = false;
        let montoEfectivo = 0;
        let metodosNoEfectivo = 0;


        const saldo = parseFloat(document.getElementById('saldoAmount').textContent) || 0;
        const saldoElement = document.getElementById('saldoAmount');
     
        if (saldoElement && saldo < 0 && saldoElement.classList.contains('text-warning')) {
            ToastMessage.fire({
                icon: 'error',
                text: 'El saldo no puede ser negativo.'
            });
            return;
        }

         if (!esAnticipada) {
            // VENTA DIRECTA: Debe estar completamente pagada
            
            // Si solo hay métodos no efectivo, el pago debe ser exacto
            if (!hayEfectivo && (saldo) > 0.01) {
                ToastMessage.fire({
                    icon: 'error',
                    text: saldo > 0 
                        ? `Falta pagar S/ ${saldo.toFixed(2)}. Sin efectivo debe pagar el monto exacto.`
                        : `Exceso de S/ ${Math.abs(saldo).toFixed(2)}. Sin efectivo no puede pagar de más.`
                });
                return;
            }
            
            // Si hay efectivo, validar que el vuelto no exceda el efectivo ingresado
            if (hayEfectivo && saldo < 0) {
                const vuelto = (saldo);
                if (vuelto > montoEfectivo) {
                    ToastMessage.fire({
                        icon: 'error',
                        text: `El vuelto (S/ ${vuelto.toFixed(2)}) excede el efectivo recibido (S/ ${montoEfectivo.toFixed(2)})`
                    });
                    return;
                }
            }
            
            // Si hay efectivo pero falta dinero
            if (hayEfectivo && saldo > 0.01) {
                ToastMessage.fire({
                    icon: 'error',
                    text: `Para venta directa debe pagar el monto completo. Falta: S/ ${saldo.toFixed(2)}`
                });
                return;
            }
            
        } else {
            // VENTA ANTICIPADA: Puede tener saldo pendiente
            
            // Para boleta/factura debe pagar completo
            if ((tipoComprobante === 'boleta' || tipoComprobante === 'factura') && saldo > 0) {
                ToastMessage.fire({
                    icon: 'error',
                    text: `Para ventas anticipadas con ${tipoComprobante} debe pagar el monto completo. Falta: S/ ${saldo.toFixed(2)}`
                });
                return;
            }
            
            // No puede exceder el total sin efectivo
            if (!hayEfectivo && saldo < 0) {
                ToastMessage.fire({
                    icon: 'error',
                    text: `Sin efectivo no puede pagar más del total. Exceso: S/ ${Math.abs(saldo).toFixed(2)}`
                });
                return;
            }
            
            // Si hay efectivo y exceso, validar vuelto
            if (hayEfectivo && saldo < 0) {
                const vuelto = Math.abs(saldo);
                if (vuelto > montoEfectivo) {
                    ToastMessage.fire({
                        icon: 'error',
                        text: `El vuelto (S/ ${vuelto.toFixed(2)}) excede el efectivo recibido (S/ ${montoEfectivo.toFixed(2)})`
                    });
                    return;
                }
            }
        }

        // Preparar datos de productos
        const productsData = [];
        productRows.forEach(row => {
            const productId = row.getAttribute('data-product-id');
            const quantityInput = row.querySelector('.quantity-input');
            const priceInput = row.querySelector('input[name*="[precio]"]');
            if (productId && quantityInput && priceInput) {
                productsData.push({
                    id: productId,
                    cantidad: quantityInput.value,
                    precio: priceInput.value
                });
            }
        });

        // Preparar FormData
        const form = $(this).closest('form')[0];
        if (!form.checkValidity()) {
            form.reportValidity(); // Muestra los avisos nativos del navegador
            return;
        }
        const formData = new FormData(form);
        formData.set('products', JSON.stringify(productsData));
        formData.set('voucher_type', voucherType.charAt(0).toUpperCase() + voucherType.slice(1));

        
        botonesActivos.forEach(boton => {
            const camposId = boton.dataset.campos;
            const idMetodo = boton.dataset.id;
            const inputMonto = document.querySelector(`#${camposId} input[name^="monto["]`);
            
            if (inputMonto) {
                const montoReal = inputMonto.getAttribute('data-monto-real');
                const montoFinal = montoReal || inputMonto.value;
                
                if (parseFloat(montoFinal) > 0) {
                    formData.append(`monto[${idMetodo}]`, montoFinal);
                }
            }
        });

        // Si anticipada está chequeado
        if (document.getElementById('anticipada').checked) {
            formData.set('anticipada', 'on');
        }

    // Enviar AJAX
        $.ajax({
            url: $(form).attr('action'),
            method: $(form).attr('method'),
            data: formData,
            processData: false,
            contentType: false,
            success: async function(response) {
                if (response.status) {
                    ToastMessage.fire({
                        icon: 'success',
                        text: 'Venta registrada correctamente.'
                    });
                    if (response.ticket_pdf_url) {
                        try {
                            await printTicketRawWithQz(response.sale_id, {
                                printerName: 'Ticketera',
                                pdfUrl: response.ticket_pdf_url,
                                fallbackOpen: true
                            });
                            ToastMessage.fire({
                                text: 'Ticket enviado a ticketera (QZ Tray).'
                            });
                        } catch (printError) {
                            console.error('Error QZ al imprimir ticket:', printError);
                            ToastError.fire({
                                text: 'No se pudo imprimir en ticketera. Se abrió el PDF para impresión manual.'
                            });
                        }
                    }
                    document.getElementById('btn-boleta').classList.remove('active');
                    document.getElementById('btn-factura').classList.remove('active');
                    document.getElementById('btn-ticket').classList.add('active');
                    // Cambiar comprobante a ticket
                    document.querySelector('input[name="voucher_type"]').value = 'Ticket';
                    location.reload();

                } else {
                    ToastError.fire({
                        text: 'No se pudo registrar la venta'
                    });
                }
            },
            error: function(xhr) {
                console.error('Error AJAX:', xhr.status, xhr.responseText);
                let msg = 'Error al registrar venta';
                try {
                    const json = JSON.parse(xhr.responseText);
                    if (json && json.error) msg = json.error;
                } catch (e) {
                    // ignore parse error
                }
                ToastError.fire({
                    text: msg
                });
            }
        });
    });

    // Función para resetear todo el formulario y UI después de una venta exitosa
    function resetFormulario() {
        // Limpiar tabla de productos
        document.querySelectorAll('tbody tr').forEach(tr => tr.remove());
        productTableCounter = 0;
        renumberRows();

        // Limpiar contenedor de productos
        const productContainer = document.getElementById('product-container');
        if (productContainer) productContainer.innerHTML = '';

        // Limpiar campos visibles del formulario
        ['document','client','telefono','direccion','referencia','observacion','hora_entrega','search-product'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = '';
        });
        const fecha = document.getElementById('fecha_entrega'); if (fecha) fecha.value = '';

        // Resetear inputs ocultos y totales
        const typeSale = document.getElementById('type_sale'); if (typeSale) typeSale.value = 0;
        const status = document.getElementById('status'); if (status) status.value = 1;
        const typeStatus = document.getElementById('type_status'); if (typeStatus) typeStatus.value = 0;

        const totalDisplay = document.getElementById('totalAmount'); if (totalDisplay) totalDisplay.textContent = '0.00';
        const totalInput = document.getElementById('totalAmountInput'); if (totalInput) { totalInput.value = '0.00'; totalInput.removeAttribute('data-previous-total'); }

        // Limpiar y ocultar métodos de pago
        resetPaymentMethods();

        // Desmarcar anticipada
        const antic = document.getElementById('anticipada');
        if (antic) antic.checked = false;
        antic.dispatchEvent(new Event('change'));

        // Resetear tipo de comprobante a Ticket
        let voucher = document.querySelector('input[name="voucher_type"]');
        if (!voucher) {
            voucher = document.createElement('input');
            voucher.type = 'hidden';
            voucher.name = 'voucher_type';
            const formEl = document.getElementById('saveSale') || document.querySelector('form');
            if (formEl) formEl.appendChild(voucher);
            else document.body.appendChild(voucher);
        }
        voucher.value = 'Ticket';

        // Resetear botones de categoría y de medio de pago
        document.querySelectorAll('button[onclick*="handleCategoryClick"]').forEach(btn => btn.className = 'btn btn-outline-primary btn-sm m-1');
        document.querySelectorAll('[data-id]').forEach(btn => btn.classList.remove('btn-success'));


        // Resetear saldo
        const saldo = document.getElementById('saldoAmount'); if (saldo) { saldo.textContent = '0.00'; saldo.className = ''; }

        // Resetear hidden product_id
        const pid = document.getElementById('product_id'); if (pid) pid.value = '';

        // Resetear estilos de botones de comprobante
        const btnBoleta = document.getElementById('btn-boleta'); if (btnBoleta) btnBoleta.className = 'btn btn-outline-primary me-1';
        const btnFactura = document.getElementById('btn-factura'); if (btnFactura) btnFactura.className = 'btn btn-outline-success me-1';
        const btnTicket = document.getElementById('btn-ticket'); if (btnTicket) btnTicket.className = 'btn btn-info me-1';

    }

    async function imprimirVenta(saleId) {
        ajaxImpresion(saleId);
        ajaxImpresion(saleId);
    }

    async function ajaxImpresion(saleId){
        $.ajax({
            url: "{{ route('anticipated_print') }}",
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: {
                sale_id: saleId
            },
            success: async function(response) {
                if (!response.status) {
                    ToastError.fire({
                        text: response.error || 'Error al obtener datos de la venta'
                    });
                    return;
                }

                const data = response;
                const venta = data.venta;
                const productos = data.productos;
                const pagos = data.pagos;
                const voucherType = (venta.voucher_type || '').toLowerCase();

                // Formato especial para boleta/factura
                if (voucherType === 'boleta' || voucherType === 'factura') {
                    // Calcular OP. GRAVADA e IGV
                    let opGravada = 0;
                    let igv = 0;
                    let total = 0;
                    let productosLineas = [];

                    productos.forEach(function(producto) {
                        const cantidad = parseFloat(producto.cantidad) || 0;
                        const precio = parseFloat(producto.precio) || 0;
                        const subtotal = parseFloat(producto.subtotal) || (cantidad * precio);
                        opGravada += subtotal;
                        productosLineas.push({
                            nombre: producto.nombre,
                            cantidad: cantidad,
                            precio: precio,
                            subtotal: subtotal
                        });
                    });

                    let opGravadaSinIGV = opGravada / 1.18;
                    igv = opGravada - opGravadaSinIGV;
                    total = opGravada;

                    let operaciones = [{
                            nombre: "Iniciar",
                            argumentos: []
                        },
                        {
                            nombre: "EstablecerAlineacion",
                            argumentos: [1]
                        },
                        {
                            nombre: "EstablecerEnfatizado",
                            argumentos: [true]
                        },
                        {
                            nombre: "EscribirTexto",
                            argumentos: ["MARARENA\n"]
                        },
                        {
                            nombre: "EstablecerEnfatizado",
                            argumentos: [false]
                        },
                        {
                            nombre: "EscribirTexto",
                            argumentos: ["RUC 20606515627\n"]
                        },
                        // {
                        //     nombre: "EscribirTexto",
                        //     argumentos: ["AV. JOSE BALTA NRO. 054 P.J. CHINO ZAMORA CHICLAYO\n"]
                        // },
                        {
                            nombre: "EscribirTexto",
                            argumentos: ["CHICLAYO LAMBAYEQUE\n"]
                        },
                        {
                            nombre: "EscribirTexto",
                            argumentos: ["=================================================\n"]
                        },
                        {
                            nombre: "EstablecerEnfatizado",
                            argumentos: [true]
                        },
                        {
                            nombre: "EscribirTexto",
                            argumentos: [voucherType === 'boleta' ? "BOLETA DE VENTA ELECTRÓNICA\n" : "FACTURA ELECTRÓNICA\n"]
                        },
                        {
                            nombre: "EstablecerEnfatizado",
                            argumentos: [false]
                        },
                        {
                            nombre: "EscribirTexto",
                            argumentos: [`${venta.number || ''}\n`]
                        },
                        {
                            nombre: "EscribirTexto",
                            argumentos: [
                                voucherType === 'factura' ?
                                `RAZON SOCIAL: ${venta.cliente || 'CLIENTE VARIOS'}\n` :
                                `NOMBRE: ${venta.cliente || 'CLIENTE VARIOS'}\n`
                            ]
                        },
                        {
                            nombre: "EscribirTexto",
                            argumentos: [
                                voucherType === 'factura' ?
                                `RUC: ${venta.document || '00000000000'}\n` :
                                `DNI: ${venta.document || '00000000'}\n`
                            ]
                        },
                        {
                            nombre: "EscribirTexto",
                            argumentos: [`EMISION: ${data.now || ''}\n`]
                        },
                        {
                            nombre: "EscribirTexto",
                            argumentos: ["MONEDA:  SOL (PEN)\n"]
                        },
                        {
                            nombre: "EscribirTexto",
                            argumentos: ["METODOS DE PAGO\n"]
                        }
                    ];

                    // Agregar métodos de pago
                    if (pagos && pagos.length > 0) {
                        pagos.forEach(function(pago) {
                            operaciones.push({
                                nombre: 'EscribirTexto',
                                argumentos: [`${pago.metodo_pago}: S/${parseFloat(pago.monto).toFixed(2)}\n`]
                            });
                        });
                    }

                    // Agregar productos
                    operaciones.push({
                        nombre: "EscribirTexto",
                        argumentos: ["------------------------------------------------\n"]
                    }, {
                        nombre: 'EscribirTexto',
                        argumentos: ['CODIGO DESCRIPCION   CANT   P.UNIT   P.TOTAL\n']
                    }, {
                        nombre: "EscribirTexto",
                        argumentos: ["-------------------------------------------------\n"]
                    });

                    productosLineas.forEach(function(prod) {
                        // Divide el nombre en líneas de máximo 20 caracteres
                        let nombre = prod.nombre;
                        let lineas = [];
                        while (nombre.length > 20) {
                            lineas.push(nombre.substring(0, 20));
                            nombre = nombre.substring(20);
                        }
                        if (nombre.length > 0) lineas.push(nombre);

                        // Imprime la primera línea con las columnas
                        let cantidad = prod.cantidad.toFixed(2).padStart(5);
                        let precio = prod.precio.toFixed(2).padStart(8);
                        let subtotal = prod.subtotal.toFixed(2).padStart(8);
                        operaciones.push({
                            nombre: 'EscribirTexto',
                            argumentos: [lineas[0].padEnd(20) + cantidad + precio + subtotal + '\n']
                        });

                        // Imprime las siguientes líneas solo con el nombre
                        for (let i = 1; i < lineas.length; i++) {
                            operaciones.push({
                                nombre: 'EscribirTexto',
                                argumentos: [lineas[i] + '\n']
                            });
                        }
                    });

                    // Totales
                    operaciones.push({
                        nombre: "EscribirTexto",
                        argumentos: ["------------------------------------------------\n"]
                    }, {
                        nombre: "EscribirTexto",
                        argumentos: ["OP. GRAVADA   : S/ " + opGravadaSinIGV.toFixed(2) + "\n"]
                    }, {
                        nombre: "EscribirTexto",
                        argumentos: ["IGV           : S/ " + igv.toFixed(2) + "\n"]
                    }, {
                        nombre: "EscribirTexto",
                        argumentos: ["IMPORTE TOTAL : S/ " + total.toFixed(2) + "\n"]
                    }, {
                        nombre: "EscribirTexto",
                        argumentos: ["SON: " + convertirMontoALetras(total) + "\n"]
                    });

                    // Información adicional
                    operaciones.push({
                        nombre: "EscribirTexto",
                        argumentos: ["\nINFORMACION ADICIONAL:\n"]
                    });

                    // Agrega dirección si existe
                    if (venta.direccion) {
                        operaciones.push({
                            nombre: "EscribirTexto",
                            argumentos: [`DIRECCION: ${venta.direccion}\n`]
                        });
                    }

                    // Agrega referencia si existe
                    if (venta.referencia) {
                        operaciones.push({
                            nombre: "EscribirTexto",
                            argumentos: [`REFERENCIA: ${venta.referencia}\n`]
                        });
                    }

                    // Agrega teléfono si existe
                    if (venta.telefono) {
                        operaciones.push({
                            nombre: "EscribirTexto",
                            argumentos: [`TELEFONO: ${venta.telefono}\n`]
                        });
                    }

                    // Agrega usuario si existe
                    if (venta.user_id) {
                        operaciones.push({
                            nombre: "EscribirTexto",
                            argumentos: [`USUARIO: ${venta.user_id}\n`]
                        });
                    }

                    // Agrega fecha de entrega si existe
                    if (venta.fecha_entrega) {
                        operaciones.push({
                            nombre: "EscribirTexto",
                            argumentos: [`FECHA ENTREGA: ${venta.fecha_entrega}\n`]
                        });
                    }

                    // Agrega hora de entrega si existe
                    if (venta.hora_entrega) {
                        operaciones.push({
                            nombre: "EscribirTexto",
                            argumentos: [`HORA ENTREGA: ${venta.hora_entrega}\n`]
                        });
                    }

                    // Agrega observación si existe
                    if (venta.observacion) {
                        operaciones.push({
                            nombre: "EscribirTexto",
                            argumentos: [`OBSERVACION: ${venta.observacion}\n`]
                        });
                    }

                    // Footer
                    operaciones.push({
                        nombre: "Feed",
                        argumentos: [2]
                    }, {
                        nombre: "EstablecerAlineacion",
                        argumentos: [1]
                    }, {
                        nombre: "EscribirTexto",
                        argumentos: ["Gracias por su preferencia\n"]
                    }, {
                        nombre: "EscribirTexto",
                        argumentos: ["Implementado por xinergia.net\n"]
                    }, {
                        nombre: "EscribirTexto",
                        argumentos: [`IMPRESION: ${data.now}\n`]
                    }, {
                        nombre: "Feed",
                        argumentos: [1]
                    }, {
                        nombre: "Corte",
                        argumentos: [1]
                    });

                    // IMPRESIÓN DE BOLETA/FACTURA
                    try {
                        // Intentar impresión local primero
                        const http = await fetch('http://localhost:8000/imprimir', {
                            method: 'POST',
                            // headers: {
                            //     'Content-Type': 'application/json'
                            // },
                            body: JSON.stringify({
                                serial: serial,
                                nombreImpresora: 'Ticketera',
                                operaciones: operaciones
                            })
                        });

                        const res = await http.json();
                        if (!res.ok) {
                            throw new Error(res.message || 'Error al imprimir localmente');
                        } else {
                            ToastMessage.fire({
                                text: 'Comprobante impreso correctamente'
                            });
                            location.reload();
                        }
                    } catch (error) {
                        console.log('Error en impresión local, intentando remota:', error.message);

                        // Si falla local, intentar impresión remota
                        try {
                            const rutaRemota = `http://192.168.18.46:8000/imprimir`;
                            const payload = {
                                operaciones: operaciones,
                                nombreImpresora: 'Ticketera',
                                serial: serial,
                            };

                            const remoteResponse = await fetch('http://localhost:8000/reenviar?host=' + rutaRemota, {
                                method: 'POST',
                                body: JSON.stringify(payload),
                                // headers: {
                                //     'Content-Type': 'application/json; charset=utf-8'
                                // }
                            });

                            const remoteRes = await remoteResponse.json();
                            if (remoteRes.ok) {
                                ToastMessage.fire({
                                    text: 'Comprobante impreso correctamente (Remoto)'
                                });
                                location.reload();
                            } else {
                                throw new Error('Impresión remota falló: ' + remoteRes.message);
                            }
                        } catch (errorRemoto) {
                            console.error('Error al imprimir boleta/factura:', errorRemoto);
                            ToastError.fire({
                                text: 'Error al imprimir la boleta/factura: ' + errorRemoto.message
                            });
                            return;
                        } finally {
                            location.reload();
                        }
                    } finally {
                        location.reload();
                    }

                    // Si llegó aquí, la impresión fue exitosa, terminar función
                    return;
                }

                // FORMATO ORIGINAL PARA TICKET (solo si NO es boleta/factura)
                const opts = {
                    serial: serial,
                    nombreImpresora: 'Ticketera',
                    operaciones: [{
                            nombre: 'Iniciar',
                            argumentos: []
                        },
                        {
                            nombre: "EstablecerAlineacion",
                            argumentos: [1]
                        },
                        {
                            nombre: 'EscribirTexto',
                            argumentos: ['MARARENA\n']
                        },
                        {
                            nombre: 'EscribirTexto',
                            argumentos: ['----------------------------------------\n']
                        },
                        {
                            nombre: 'EscribirTexto',
                            argumentos: [`000${venta.type_sale} - ${venta.tipo || 'N/A'}\n`]
                        },
                        {
                            nombre: "EstablecerAlineacion",
                            argumentos: [0]
                        },
                        {
                            nombre: 'EscribirTexto',
                            argumentos: ['----------------------------------------\n']
                        },
                        {
                            nombre: 'EscribirTexto',
                            argumentos: [`NUMERO: ${venta.number || 'N/A'}\n`]
                        },
                        {
                            nombre: 'EscribirTexto',
                            argumentos: [`USUARIO: ${venta.user_id || 'Usuario'}\n`]
                        },
                        {
                            nombre: 'EscribirTexto',
                            argumentos: [`FECHA VENTA: ${venta.fecha}\n`]
                        }
                    ]
                };

                // Métodos de pago
                if (pagos && pagos.length > 0) {
                    opts.operaciones.push({
                        nombre: 'EscribirTexto',
                        argumentos: ['METODOS DE PAGO:\n']
                    });
                    pagos.forEach(function(pago) {
                        opts.operaciones.push({
                            nombre: 'EscribirTexto',
                            argumentos: [`${pago.metodo_pago}: S/${pago.monto}\n`]
                        });
                    });
                    opts.operaciones.push({
                        nombre: 'EscribirTexto',
                        argumentos: ['----------------------------------------\n']
                    });
                }

                // Productos
                opts.operaciones.push({
                    nombre: 'EscribirTexto',
                    argumentos: ['PRODUCTOS:\n']
                });
                opts.operaciones.push({
                    nombre: 'EscribirTexto',
                    argumentos: ['CANT PRODUCTO        P.U     TOTAL\n']
                });
                opts.operaciones.push({
                    nombre: 'EscribirTexto',
                    argumentos: ['----------------------------------------\n']
                });

                productos.forEach(function(producto) {
                    const cant = producto.cantidad.toString().padEnd(4);
                    const precio = `S/${parseFloat(producto.precio).toFixed(2)}`.padStart(8);
                    const total = `S/${parseFloat(producto.subtotal).toFixed(2)}`.padStart(8);

                    if (producto.nombre.length > 15) {
                        opts.operaciones.push({
                            nombre: 'EscribirTexto',
                            argumentos: [`${cant} ${producto.nombre}\n`]
                        });
                        opts.operaciones.push({
                            nombre: 'EscribirTexto',
                            argumentos: [`${' '.repeat(19)} ${precio} ${total}\n`]
                        });
                    } else {
                        const nombre = producto.nombre.padEnd(15);
                        opts.operaciones.push({
                            nombre: 'EscribirTexto',
                            argumentos: [`${cant} ${nombre} ${precio} ${total}\n`]
                        });
                    }
                });

                // Footer del ticket
                opts.operaciones.push({
                    nombre: 'EscribirTexto',
                    argumentos: ['----------------------------------------\n']
                });
                opts.operaciones.push({
                    nombre: "EstablecerAlineacion",
                    argumentos: [2]
                });
                opts.operaciones.push({
                    nombre: 'EscribirTexto',
                    argumentos: [`TOTAL: S/${parseFloat(venta.total).toFixed(2)}\n`]
                });
                opts.operaciones.push({
                    nombre: "EstablecerAlineacion",
                    argumentos: [0]
                });
                opts.operaciones.push({
                    nombre: 'EscribirTexto',
                    argumentos: ['----------------------------------------\n']
                });
                opts.operaciones.push({
                    nombre: "EstablecerAlineacion",
                    argumentos: [1]
                });
                opts.operaciones.push({
                    nombre: 'EscribirTexto',
                    argumentos: ['INFORMACION ADICIONAL\n']
                });

                if (venta.direccion) {
                    opts.operaciones.push({
                        nombre: 'EscribirTexto',
                        argumentos: [`DIRECCION: ${venta.direccion}\n`]
                    });
                }
                if (venta.referencia) {
                    opts.operaciones.push({
                        nombre: 'EscribirTexto',
                        argumentos: [`REFERENCIA: ${venta.referencia}\n`]
                    });
                }
                if (venta.observacion) {
                    opts.operaciones.push({
                        nombre: 'EscribirTexto',
                        argumentos: [`OBSERVACION: ${venta.observacion}\n`]
                    });
                }

                opts.operaciones.push({
                    nombre: "EstablecerAlineacion",
                    argumentos: [1]
                }, {
                    nombre: 'EscribirTexto',
                    argumentos: ['----------------------------------------\n']
                }, {
                    nombre: "EstablecerAlineacion",
                    argumentos: [0]
                }, {
                    nombre: 'Feed',
                    argumentos: [2]
                }, {
                    nombre: "EstablecerAlineacion",
                    argumentos: [1]
                }, {
                    nombre: 'EscribirTexto',
                    argumentos: ['Gracias por su preferencia\n']
                }, {
                    nombre: 'EscribirTexto',
                    argumentos: ['Implementado por xinergia.net\n']
                }, {
                    nombre: 'EscribirTexto',
                    argumentos: [`IMPRESION: ${data.now}\n`]
                }, {
                    nombre: 'Feed',
                    argumentos: [1]
                }, {
                    nombre: 'Corte',
                    argumentos: [1]
                });

                // IMPRESIÓN DEL TICKET
                try {
                    // Intentar impresión local primero
                    const http = await fetch('http://localhost:8000/imprimir', {
                        method: 'POST',
                        /* headers: {
                            'Content-Type': 'application/json'
                        }, */
                        body: JSON.stringify({
                            serial: serial,
                            nombreImpresora: 'Ticketera',
                            operaciones: opts.operaciones
                        })
                    });

                    const res = await http.json();
                    if (!res.ok) {
                        throw new Error(res.message || 'Error al imprimir localmente');
                    } else {
                        ToastMessage.fire({
                            text: 'Ticket impreso correctamente'
                        });
                        location.reload();
                    }
                } catch (error) {
                    console.log('Error en impresión local, intentando remota:', error.message);

                    // Si falla local, intentar impresión remota
                    try {
                        const rutaRemota = `http://192.168.18.46:8000/imprimir`;
                        const payload = {
                            operaciones: opts.operaciones,
                            nombreImpresora: 'Ticketera',
                            serial: serial,
                        };

                        const remoteResponse = await fetch('http://localhost:8000/reenviar?host=' + rutaRemota, {
                            method: 'POST',
                            body: JSON.stringify(payload),
                            /* headers: {
                                'Content-Type': 'application/json; charset=utf-8'
                            } */
                        });

                        const remoteRes = await remoteResponse.json();
                        if (remoteRes.ok) {
                            ToastMessage.fire({
                                text: 'Ticket impreso correctamente (Remoto)'
                            });
                            location.reload();
                        } else {
                            throw new Error('Impresión remota falló: ' + remoteRes.message);
                        }
                    } catch (errorRemoto) {
                        console.error('Error al imprimir ticket:', errorRemoto);
                        ToastError.fire({
                            text: 'Error al imprimir el ticket: ' + errorRemoto.message
                        });
                    } finally {
                        location.reload();
                    }
                } finally {
                    location.reload();
                }
            },
            error: function(xhr, status, error) {
                console.log('Error en la solicitud:', error);
                ToastError.fire({
                    text: 'Error al obtener datos para impresión'
                });
            },
            complete: function() {
                location.reload();
            }
        });
   
    }

</script>
@endsection
