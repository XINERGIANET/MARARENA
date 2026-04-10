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
<h2>Histórico de compras</h2>
<p>Listado de todas las compras realizadas</p>
@endsection

@section('content')
<div class="container-fluid content-inner mt-n5 py-0">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body p-3">
                    <div class="header-title w-100">
                        <!-- Historial de Facturas -->
                        <div class="row">
                            <!-- Fila de filtros y botones en la misma línea -->
                            <div class="col-12">
                                <form action="" method="GET">
                                    <div class="row align-items-end g-3">
                                        <!-- Fecha inicial -->
                                        <div class="col-md-2">
                                            <label for="start_date" class="form-label small">Fecha Inicial</label>
                                            <input type="date" class="form-control" name="start_date" id="start_date"
                                                value="{{ request()->start_date ? request()->start_date : '' }}">
                                        </div>
                                        <!-- Fecha final -->
                                        <div class="col-md-2">
                                            <label for="end_date" class="form-label small">Fecha Final</label>
                                            <input type="date" class="form-control" name="end_date" id="end_date"
                                                value="{{ request()->end_date ? request()->end_date : '' }}">
                                        </div>
                                        <!-- Proveedor -->

                                        <div class="col-md-3">
                                            <label class="form-label">Proveedor</label>
                                            <input type="text" id="search-supplier" class="form-control" value="{{ request()->supplier_name ?? '' }}" placeholder="Todos los proveedores">
                                            <input type="hidden" id="supplier_id" name="supplier_id" value="{{ request()->supplier_id ?? '' }}">
                                        </div>

                                        <div class="col-md-3">
                                            <label for="search-product" class="form-label">Producto</label>
                                            <input type="text" class="form-control" id="search-product" placeholder="Todos los productos" value="{{ request()->product_name ?? '' }}">
                                            <input hidden type="number" id="product_id" name="product_id" placeholder="" value="{{ request()->product_id ?? '' }}">
                                        </div>


                                        <!-- Botones -->
                                        <div class="col-md-8">
                                            <div class="d-flex gap-2">
                                                <button class="btn btn-primary" type="submit">
                                                    <i class="fas fa-filter"></i> Filtrar
                                                </button>
                                                <div class="w-50s me-2">
                                                    <a href="{{ route('purchases.index') }}"
                                                        class="btn btn-warning w-100" id="btnLimpiar">
                                                        Limpiar
                                                    </a>
                                                </div>
                                                <!-- <button class="btn btn-danger" type="button" id="pdfBtn">
                                                    <i class="fas fa-file-pdf"></i> PDF
                                                </button>
                                                <button class="btn btn-danger" type="button" id="pdfBtnGeneral">
                                                    <i class="fas fa-file-pdf"></i> PDF (general)
                                                </button>

                                                <button class="btn btn-success" type="button" id="pdfBtnProduct">
                                                    <i class="fas fa-file-pdf"></i> PDF Producto
                                                </button>
                                                <button class="btn btn-info" type="button" id="pdfBtnAllProducts">
                                                    <i class="fas fa-file-pdf"></i> PDF Todos los Productos
                                                </button> -->

                                                <div class="btn-group d-none">
                                                    <button type="button" class="btn btn-danger btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" style="--bs-btn-padding-x: .5rem; --bs-btn-font-size: .75rem;">
                                                        <i class="fas fa-file-pdf"></i> INFORMES PDF
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li>
                                                            <a class="dropdown-item btn-pdf" href="#">
                                                                <i class="bi bi-file-earmark-text"></i> DETALLE POR PROOVEDOR
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item btn-pdf-general" href="#">
                                                                <i class="bi bi-file-earmark-text"></i> DETALLE TOTAL POR PROOVEDOR
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item btn-producto" href="#">
                                                                <i class="bi bi-file-earmark-text"></i> DETALLE POR PRODUCTO
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item btn-producto-todo" href="#">
                                                                <i class="bi bi-file-earmark-text"></i> DETALLE TOTAL POR PRODUCTO
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>

                                                <button class="btn btn-success d-none" type="button" id="excelBtn">
                                                    <i class="fas fa-file-excel"></i> Excel
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <!-- Fila del total - abajo a la derecha -->
                            <div class="col-12 my-4">
                                <div class="d-flex justify-content-end">
                                    <div>
                                        <h4>
                                            <strong>TOTAL: S/ {{ number_format($total, 2, '.', ',') }}</strong>
                                        </h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- <h4 class="mt-3">Historial de Facturas</h4> -->
                        <div class="table-responsive">
                            <table class="table table-striped" id="invoiceHistoryTable">
                                <thead>
                                    <tr>
                                        <th>Proveedor</th>
                                        <th>Fecha</th>
                                        <th>Método de Pago</th>
                                        <th>Total</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($purchases->count())
                                    @foreach ($purchases as $purchase)
                                    <tr>
                                        <td>{{ $purchase->supplier->company_name ?? 'Sin proveedor' }}</td>
                                        <td>{{ $purchase->date->format('d/m/Y') }}</td>
                                        <td>{{ $purchase->payment_method->name ?? 'Sin método de pago' }}</td>
                                        <td>{{ number_format($purchase->total, 2) }}</td>
                                        <td>{{ $purchase->deleted == 0 ? 'Activo' : 'Anulado' }}</td>
                                        <td>
                                            <button class="btn btn-primary btn-sm btn-icon btn-show"
                                                data-id="{{ $purchase->id }}"
                                                title="Ver Detalle">
                                                <i class="bi bi-eye-fill"></i>
                                            </button>

                                            @if($purchase->deleted == 0)
                                            <button class="btn btn-warning btn-sm btn-icon btn-edit"
                                                data-id="{{ $purchase->id }}"
                                                title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </button>

                                            <button class="btn btn-danger btn-sm btn-icon btn-eliminar"
                                                data-id="{{ $purchase->id }}"
                                                data-bs-toggle="modal"
                                                data-bs-target="#eliminarModal"
                                                title="Eliminar">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                    @else
                                    <tr>
                                        <td colspan="6" class="text-center">Sin Registros</td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center mt-3">
                            {{ $purchases->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para mostrar detalles -->
<div class="modal fade" id="showModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalle de la compra</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Precio Unitario (S/)</th>
                                <th>Subtotal (S/)</th>
                            </tr>
                        </thead>
                        <tbody id="tbl-items"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Compra</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Aquí puedes incluir el formulario de edición -->
                <form id="editForm">
                    @csrf
                    @method('PUT')
                    <!-- Fila 1: N° Comprobante y Proveedor -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="editInvoiceNumber" class="form-label">N° Comprobante</label>
                            <input type="text" class="form-control" id="editInvoiceNumber" name="invoice_number">
                        </div>
                        <div class="col-md-6">
                            <label for="editSupplier" class="form-label">Proveedor</label>
                            <select class="form-control" id="editSupplier" name="supplier_id">
                                <option value="">Seleccionar proveedor</option>
                                @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->company_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <!-- Fila 2: Fecha, Método de Pago y Total -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="editDate" class="form-label">Fecha</label>
                            <input type="date" class="form-control" id="editDate" name="date" required>
                        </div>
                        <div class="col-md-4">
                            <label for="editPaymentMethod" class="form-label">Método de Pago</label>
                            <select class="form-control" id="editPaymentMethod" name="payment_method_id" required>
                                <option value="">Seleccionar método de pago</option>
                                @foreach ($paymentMethods as $method)
                                <option value="{{ $method->id }}">{{ $method->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="editTotal" class="form-label">Total</label>
                            <input type="number" class="form-control" id="editTotal" name="total" step="0.01" disabled>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade modal-lg" id="eliminarModal" tabindex="-1" aria-labelledby="eliminarModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header text-white">
                <h5 class="modal-title" id="eliminarModalLabel">Confirmar Eliminación</h5>
                <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas anular esta compra?</p>
            </div>
            <div class="modal-footer">
                <form id="formEliminar" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="global-spinner" class="d-flex justify-content-center align-items-center spinner-hidden"
    style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255, 255, 255, 0.8); z-index: 1000;">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Cargando...</span>
    </div>
</div>

<style>
    .spinner-hidden {
        display: none !important;
    }

    .spinner-visible {
        display: flex !important;
    }
</style>

<!-- Script para manejar la solicitud AJAX -->


@endsection

@section('scripts')
<script>
    
    var suppliers = @json($suppliers);
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

    var products = @json($products);
    $('#search-product').autocomplete({
        source: function(request, response) {
            var results = [];
            if (products && products.length) {
                for (var i = 0; i < products.length; i++) {
                    var product = products[i];
                    if (product && product.name &&
                        product.name.toLowerCase().indexOf(request.term.toLowerCase()) !== -1) {
                        results.push({
                            label: product.name,
                            value: product.name,
                            id: product.id
                        });
                    }
                }
            }
            response(results.slice(0, 15));
        },
        select: function(event, ui) {
            if (ui.item && ui.item.id) {
                $('#product_id').val(ui.item.id);
            }
        },
        change: function(event, ui) {
            if (!ui.item) {
                $('#product_id').val('');
            }
        }
    });

    // Limpiar cuando se borra el texto
    $('#search-product').on('input', function() {
        if ($(this).val() === '') {
            $('#product_id').val('');
        }
    });
</script>
<script>

    document.addEventListener('DOMContentLoaded', function() {
        const spinner = document.getElementById('global-spinner');
        const editForm = document.getElementById('editForm');

    });

    document.addEventListener('DOMContentLoaded', function() {
        const eliminarModal = document.getElementById('eliminarModal');
        eliminarModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');

            // Ruta al controlador que actualiza el estado
            const form = document.getElementById('formEliminar');
            form.action = '{{ route("purchases.destroy", ":id") }}'.replace(':id', id);
        });
    });

    $(document).ready(function() {
        // Cuando se hace clic en el botón "Ver Detalle"
        $('.btn-show').click(function() {

            var id = $(this).data('id');
            $('#tbl-items').html('');

            $.ajax({
                url: '{{ route("purchases.show", "") }}/' + id,
                method: 'GET',
                success: function(data) {
                    var html = '';

                    // Construir las filas de la tabla con los detalles
                    data.details.forEach(function(detail) {
                        html += `
                            <tr>
                                <td>${detail.product.name}</td>
                                <td>${detail.quantity}</td>
                                <td>${detail.unit_price}</td>
                                <td>${detail.subtotal}</td>
                            </tr>
                        `;
                    });

                    // Insertar las filas en la tabla
                    $('#tbl-items').html(html);

                    // Mostrar el modal
                    $('#showModal').modal('show');
                },
                error: function(xhr) {
                    ToastError.fire({
                        text: "Error al cargar detalles"
                    });
                    console.error('Error al cargar los detalles:', xhr.responseText);
                }
            });
        });
    });

    // Manejar el clic en el botón "Editar"
    $(document).on('click', '.btn-edit', function() {
        var id = $(this).data('id');

        $('#editForm').data('id', id);

        // Construimos la URL dinámicamente usando la ruta Laravel
        var url = '{{ route("purchases.edit", ":id") }}'.replace(':id', id);

        $.ajax({
            url: url,
            method: 'GET',
            success: function(data) {
                const registro = data.registro;

                $('#editInvoiceNumber').val(registro.invoice_number);
                $('#editSupplier').val(registro.supplier_id);
                $('#editDate').val(registro.date.split('T')[0]);
                $('#editTotal').val(registro.total);
                $('#editPaymentMethod').val(registro.payment_method_id);
            },
            error: function(xhr) {
                console.error(xhr.responseText);
                ToastError.fire({
                    text: 'No se pudo cargar los datos'
                });
            }
        });

        $('#editModal').modal('show');
    });

    // Manejar el envío del formulario de edición
    $('#editForm').submit(function(e) {
        e.preventDefault();

        var id = $(this).data('id');

        var url = '{{ route("purchases.update", ":id") }}'.replace(':id', id);

        var token = $('input[name="_token"]').val();

        var formData = {
            invoice_number: $('#editInvoiceNumber').val(),
            supplier_id: $('#editSupplier').val(),
            date: $('#editDate').val(),
            payment_method_id: $('#editPaymentMethod').val(),
            _token: token,
            _method: 'PUT'
        };

        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            success: function(response) {
                $('#editModal').modal('hide');
                ToastMessage.fire({
                    icon: 'success',
                    text: 'Registro actualizado correctamente.'
                });
                location.reload();
            },
            error: function(xhr) {
                ToastError.fire({
                    text: 'No se pudo actualizar el registro'
                });
            }
        });
    });

    $(document).on('click', '.btn-pdf', function() {
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        const supplierId = document.getElementById('supplier_id').value;

        // Usar la nueva ruta
        let pdfUrl = '{{ route("purchases.pdf") }}';
        const params = new URLSearchParams();

        if (startDate) params.append('start_date', startDate);
        if (endDate) params.append('end_date', endDate);
        if (supplierId) params.append('supplier_id', supplierId);

        if (params.toString()) {
            pdfUrl += '?' + params.toString();
        }

        console.log('URL generada:', pdfUrl);

        // Crear un enlace temporal para forzar la descarga
        const link = document.createElement('a');
        link.href = pdfUrl;
        link.download = 'reporte_compras' + '.pdf';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });

    $(document).on('click', '.btn-pdf-general', function() {
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        const supplierId = document.getElementById('supplier_id').value;

        // Usar la nueva ruta
        let pdfUrl = '{{ route("purchases.pdfGeneral") }}';
        const params = new URLSearchParams();

        if (startDate) params.append('start_date', startDate);
        if (endDate) params.append('end_date', endDate);
        if (supplierId) params.append('supplier_id', supplierId);
        if (params.toString()) {
            pdfUrl += '?' + params.toString();
        }

        console.log('URL generada:', pdfUrl);

        // Crear un enlace temporal para forzar la descarga
        const link = document.createElement('a');
        link.href = pdfUrl;
        link.download = 'reporte_compras_general' + '.pdf';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        // Alternativa: abrir en nueva ventana
        // window.open(pdfUrl, '_blank');
    });

    $(document).on('click', '.btn-producto', function() {
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        const supplierId = document.getElementById('supplier_id').value;
        const productId = document.getElementById('product_id').value;

        // Validar que hay un producto seleccionado
        if (!productId) {
            ToastError.fire({
                text: 'Seleccione un Producto a filtrar'
            });
            return;
        }

        let pdfUrl = '{{ route("purchases.pdfProduct") }}';
        const params = new URLSearchParams();

        if (startDate) params.append('start_date', startDate);
        if (endDate) params.append('end_date', endDate);
        if (supplierId) params.append('supplier_id', supplierId);
        params.append('product_id', productId);

        if (params.toString()) {
            pdfUrl += '?' + params.toString();
        }

        const productName = document.getElementById('search-product').value || 'producto';
        const link = document.createElement('a');
        link.href = pdfUrl;
        link.download = `reporte_${productName.toLowerCase().replace(/\s+/g, '_')}.pdf`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });


    $(document).on('click', '.btn-producto-todo', function() {
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        const supplierId = document.getElementById('supplier_id').value;

        let pdfUrl = '{{ route("purchases.pdfAllProducts") }}';
        const params = new URLSearchParams();

        if (startDate) params.append('start_date', startDate);
        if (endDate) params.append('end_date', endDate);
        if (supplierId) params.append('supplier_id', supplierId);

        if (params.toString()) {
            pdfUrl += '?' + params.toString();
        }

        const link = document.createElement('a');
        link.href = pdfUrl;
        link.download = 'reporte_todos_los_productos_compras.pdf';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });

    // NUEVO: Botón PDF Todos los Productos (agrupados por producto)
    document.getElementById('excelBtn').addEventListener('click', function() {
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;

        let excelUrl = '{{ route("purchases.excel") }}';
        const params = new URLSearchParams();

        if (startDate) params.append('start_date', startDate);
        if (endDate) params.append('end_date', endDate);

        if (params.toString()) {
            excelUrl += '?' + params.toString();
        }

        const link = document.createElement('a');
        link.href = excelUrl;
        link.download = 'reporte_compras.xlsx';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });
</script>
@endsection