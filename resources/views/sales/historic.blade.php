@extends('layouts.app')


@section('header')
<h2>Histórico de ventas</h2>
<p>Lista total de ventas</p>
@endsection

@section('nav')
<ul class="nav justify-content-center py-1">
	<li class="nav-item">
		<a class="nav-link bg-primary text-white rounded" href="{{ route('sales.historic') }}">Histórico</a>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="{{ route('sales.deleted') }}">Anuladas</a>
	</li>
    <li class="nav-item">
		<a class="nav-link" href="{{ route('sales.porProducto') }}">Por producto</a>
	</li>
</ul>
@endsection

@section('content')
<div class="container-fluid content-inner mt-n5 py-0">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">

                <div class="card-body border-bottom">
                    <form action="" id="formFilter">
                        <div class="row d-flex">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Fecha inicial</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" value="{{ request()->start_date ? request()->start_date : '' }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Fecha final</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" value="{{ request()->end_date ? request()->end_date : '' }}">
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label">N° Comprobante</label>
                                <input type="text" id="number" name="number" class="form-control" value="{{ request('number') }}">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Cliente</label>
                                <input type="hidden" id="client_id" name="client_id" value="{{ request()->client_id ?? '' }}">                          
                                <input type="text" id="search-client" class="form-control" value="{{ request()->client_name ?? '' }}">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Producto</label>
                                <input type="text" class="form-control" id="product_name" name="product_name" value="{{ request()->product_name ?? '' }}">
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label">Tipo de comprobante</label>
                                <select name="voucher_type" id="voucher_type" class="form-select">
                                    <option value="">Todos</option>
                                    <option value="Ticket" {{ request('voucher_type') == 'Ticket' ? 'selected' : '' }}>Ticket</option>
                                    <option value="Boleta" {{ request('voucher_type') == 'Boleta' ? 'selected' : '' }}>Boleta</option>
                                    <option value="Factura" {{ request('voucher_type') == 'Factura' ? 'selected' : '' }}>Factura</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Método de pago</label>
                                <select name="payment_method_id" class="form-select">
                                    <option value="">Todos</option>
                                    @foreach($paymentMethod as $method)
                                        <option value="{{ $method->id }}" {{ request('payment_method_id') == $method->id ? 'selected' : '' }}>
                                            {{ $method->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Tipo de venta</label>
                                <select name="type_sale" class="form-select">
                                    <option value="">Todas</option>
                                    <option value="0" {{ request('type_sale') === '0' ? 'selected' : '' }}>Punto de venta</option>
                                    <option value="1" {{ request('type_sale') === '1' ? 'selected' : '' }}>Cafetería</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Tipo de turno</label>
                                <select name="shift" class="form-select">
                                    <option value="">Elegir turno</option>
                                    <option value="0" {{ request('shift') === '0' ? 'selected' : '' }}>Mañana</option>
                                    <option value="1" {{ request('shift') === '1' ? 'selected' : '' }}>Tarde</option>
                                </select>
                            </div>
                            <div class="col d-flex align-items-end mt-4">
                                <div class=" w-50s me-2">
                                    <button type="submit" class="btn btn-primary w-100" id="btnFiltrar">Filtrar</button>
                                </div>
                                <div class="d-none w-50s me-2">
                                    <button type="button" class="btn btn-success w-100" id="btnExcel">
                                        <i class="bi bi-file-earmark-excel"></i> Excel
                                    </button>
                                </div>
                                <div class="d-none w-50s me-2">
                                     <button type="button" class="btn btn-danger w-100" id="btnPDF">
                                        <i class="bi bi-file-earmark-pdf"></i> PDF
                                    </button>
                                </div>
                                <div class=" w-50s me-2">
                                    <a href="{{ route('sales.historic') }}" class="btn btn-warning w-100" id="btnLimpiar">Limpiar</a>
                                </div>
                            </div>
                            <div class="col-12 mt-4">
                                <div class="d-flex justify-content-end">
                                    <div>
                                        <h5>
                                            <strong>Total vendido: S/ {{ number_format($total, 2, '.', ',') }}</strong>
                                        </h5>
                                        <!-- <h6>
                                            Total pagado: S/ {{ number_format($total_pagos, 2, '.', ',') }}
                                        </h6> -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>


                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>N° comprobante</th>
                                    <th>Linea de Venta</th>
                                    <th>Tipo de Venta</th>
                                    <th>Cliente</th>
                                    <th>Fecha</th>
                                    <th>Total</th>
                                    <th>Saldo</th>
                                    <th>Fecha entrega</th>
                                    <th>Comprobante</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($anticipadas as $anticipada)
                                <tr>
                                    <td>{{ $anticipada->number ? $anticipada->number : "N/A" }}</td>
                                    <td>
                                        @if($anticipada->type_sale === 0)
                                            Punto de venta
                                        @elseif($anticipada->type_sale === 1)
                                            Cafetería
                                        @endif
                                    </td>
                                    <td>
                                        @if($anticipada->type_status === 0)
                                            Directa
                                        @elseif($anticipada->type_status === 1)
                                            Anticipada
                                        @elseif($anticipada->type_status === 2)
                                            Delivery
                                        @endif
                                    </td>
                                    <td>{{ $anticipada->client_name ?? "varios" }}</td>
                                    <td>{{ optional($anticipada->date)->format('d/m/Y') ?? 'N/A' }}</td>
                                    <td>{{ $anticipada->total }}</td>
                                    <td>{{ $anticipada->saldo() }}</td>
                                    <td>{{ optional($anticipada->delivery_date)->format('d/m/Y') ?? "N/A" }}</td>
                                    <td>
                                    {{--                                         
                                    @if($anticipada->voucher_type == 'Boleta' || $anticipada->voucher_type == 'Factura')
                                        <a href="{{ route('sales.pdf', $anticipada) }}" target="_blank"
                                            class="btn btn-info btn-sm"
                                            style="--bs-btn-padding-x: .5rem; --bs-btn-font-size: .75rem;"
                                            title="Ver PDF detallado">
                                            <i class="bi bi-file-earmark-pdf"></i>
                                        </a>
                                        @else
                                        <a href="{{ route('sales.pdf_detallado', $anticipada) }}" target="_blank"
                                            class="btn btn-info btn-sm"
                                            style="--bs-btn-padding-x: .5rem; --bs-btn-font-size: .75rem;"
                                            title="Ver PDF detallado">
                                            <i class="bi bi-file-earmark-pdf"></i>
                                        </a>
                                        @endif
                                     --}}
                                        @if($anticipada->voucher_type == 'Boleta' || $anticipada->voucher_type == 'Factura')                      
                                            <a href="{{ config('apisunat.url').'/documents/'.$anticipada->voucher_id.'/getPDF/A4/'.$anticipada->voucher_file }}"
                                                target="_blank" class="btn btn-primary btn-sm btn-icon" title="Ver Comprobante">
                                                A4
                                            </a>
                                            <a href="{{ route('sales.getVoucherData', ['voucher_id' => $anticipada->voucher_id, 'type' => 'xml']) }}" target="_blank"
                                                class="btn btn-primary btn-sm btn-icon" title="Descargar XML">
                                                XML
                                            </a>
                                            <a href="{{ route('sales.getVoucherData', ['voucher_id' => $anticipada->voucher_id, 'type' => 'cdr']) }}" target="_blank"
                                                class="btn btn-primary btn-sm btn-icon" title="Descargar CDR">
                                                CDR
                                            </a>
                                        @else
                                            <button type="button" class="btn btn-secondary btn-sm btn-icon" disabled title="No disponible para {{ $anticipada->voucher_type }}">
                                                A4
                                            </button>
                                            <button type="button" class="btn btn-secondary btn-sm btn-icon" disabled title="No disponible para {{ $anticipada->voucher_type }}">
                                                XML
                                            </button>
                                            <button type="button" class="btn btn-secondary btn-sm btn-icon" disabled title="No disponible para {{ $anticipada->voucher_type }}">
                                                CDR
                                            </button>
                                        @endif
                                    </td>
                                    <td>
                                        <button type="button"
                                            class="btn btn-warning btn-sm btn-icon btn-print-sale"
                                            data-sale-id="{{ $anticipada->id }}"
                                            title="Imprimir directo en ticketera">
                                            <i class="bi bi-printer"></i>
                                        </button>
                                        <button type="button"
                                            class="btn btn-info btn-sm btn-icon btn-preview-ticket-pdf"
                                            data-sale-id="{{ $anticipada->id }}"
                                            title="Ver ticket PDF">
                                            <i class="bi bi-file-earmark-pdf"></i>
                                        </button>
                                        <button type="button" class="btn btn-primary btn-sm open-details-modal"
                                            data-bs-venta_id="{{ $anticipada->id }}"
                                            style="--bs-btn-padding-x: .5rem; --bs-btn-font-size: .75rem;">
                                            <i class="bi bi-list-task"></i>
                                        </button>
                                        @if(auth()->user()->rol->name == 'Administrator' || auth()->user()->rol->name == 'Master')
                                        <button type="button" class="btn btn-danger btn-sm btn-icon btn-anular-venta"
                                            data-sale-id="{{ $anticipada->id }}"
                                            title="Eliminar venta">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        @endif
                                    </td>   
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-3">
                        {{ $anticipadas->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Spinner de carga -->
<div id="global-spinner" class="d-flex justify-content-center align-items-center spinner-hidden" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255, 255, 255, 0.8); z-index: 1050">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Cargando...</span>
    </div>
</div>

<!-- Modal detalles -->
<div class="modal fade" id="ModalDetalle" tabindex="-1" aria-labelledby="Productos" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Productos</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-responsive table-striped">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Precio</th>
                            <th>Cantidad</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody id="modal-productos">
                        <tr>
                            <th colspan="4">No hay productos</th>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-end">Total:</th>
                            <th id="modal-total">S/1000</th>
                        </tr>
                    </tfoot>
                </table>

                <label for="direccion-input" class="form-label">
                    Dirección
                </label>
                <input type="text" readonly id="modal-direccion" name="direccion-input" class="form-control">

                <label for="direccion-input" class="form-label mt-3">
                    Referencia
                </label>
                <input type="text" readonly id="modal-referencia" name="direccion-input" class="form-control">

                <label for="direccion-input" class="form-label mt-3">
                    Observación
                </label>
                <input type="text" readonly id="modal-observacion" name="direccion-input" class="form-control">

                <input hidden type="number" id="modal-detalle-sale_id" name="sale_id" class="form-control">


                <!-- <form id="form-foto" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input hidden type="number" id="modal-detalle-sale_id" name="sale_id" class="form-control">
                     <input type="file" id="foto-input" name="foto" class="form-control mt-4" accept="image/*">
                    <div class="text-end mt-1">
                        <button type="submit" class="btn btn-primary mt-2">Subir foto</button>
                        <a href="" id="ver-foto-link" target="_blank" class="btn btn-link text-end" style="text-decoration: underline;" disabled>Ver foto</a>
                    </div>
                </form> -->

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal previsualización de ticket PDF -->
<div class="modal fade" id="printPreviewModal" tabindex="-1" aria-labelledby="printPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="printPreviewModalLabel">Vista previa del ticket (PDF)</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-0" style="height: 75vh;">
                <iframe id="print-preview-iframe" src="about:blank" style="width: 100%; height: 100%; border: 0;"></iframe>
            </div>
            <div class="modal-footer">
                <a id="open-ticket-pdf-new-tab" href="#" target="_blank" class="btn btn-outline-primary">Abrir PDF en pestaña nueva</a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal pagos -->
<div class="modal fade" id="ModalPago" tabindex="-1" aria-labelledby="Pagos" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Productos</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Pago</th>
                            <th>Método</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody id="modal-pagos">
                        <tr>
                            <th colspan="3">No hay pagos</th>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="2" class="text-end">Saldo:</th>
                            <th id="modal-saldo">S/1000</th>
                        </tr>
                    </tfoot>
                </table>

                <div class="row mt-3">
                    <div class="col-6">
                        <input hidden type="number" id="modal-pago-sale_id" name="sale_id-input" class="form-control">

                        <label for="monto-input" class="form-label">
                            Monto
                        </label>
                        <input type="text" readonly id="modal-monto" name="monto-input" class="form-control">
                    </div>
                    <div class="col-6">
                        <label for="metodo-input" class="form-label">
                            Método de pago
                        </label>
                        <input type="text" readonly id="modal-metodo" name="metodo-input" class="form-control">
                    </div>
                </div>

                <button type="button" id="guardar-pago" class="btn btn-success mt-2 float-end">Guardar</button>



            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<div id="global-spinner" class="d-flex justify-content-center align-items-center spinner-hidden"
    style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255, 255, 255, 0.8); z-index: 1050;">
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

    .numeric-keypad {
        max-width: 300px;
        margin: 0 auto;
    }

    .num-btn {
        padding: 10px 0;
    }

    .swal-confirm-btn {
        background-color: #dc3545 !important; /* rojo Bootstrap */
        color: #fff !important;
        border: none;
        border-radius: 6px;
        padding: 8px 20px;
        margin-right: 10px;
        font-weight: 500;
    }

    .swal-cancel-btn {
        background-color: #6c757d !important; /* gris Bootstrap */
        color: #fff !important;
        border: none;
        border-radius: 6px;
        padding: 8px 20px;
        font-weight: 500;
    }
</style>
@endsection

@section('scripts')

<script>
    $(document).on('click', '.btn-anular-venta', function () {
        const sale_id = $(this).data('sale-id');

        Swal.fire({
            title: '¿Anular venta?',
            text: "Esta acción restaurará el stock asociado.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, anular',
            cancelButtonText: 'Cancelar',
            customClass: {
                title: 'text-dark',
                htmlContainer: 'text-dark',
                confirmButton: 'swal-confirm-btn',
                cancelButton: 'swal-cancel-btn'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                $('#global-spinner').removeClass('spinner-hidden').addClass('spinner-visible');

                $.ajax({
                    url: "{{ route('sales.anular') }}?sale_id=" + sale_id,
                    method: 'GET',
                    complete: function () {
                        // ✅ Siempre se ejecuta, haya sido éxito o error
                        location.reload(); // 🔁 Recargar la página completa
                    }
                });
            }
        });
    });



    document.addEventListener('DOMContentLoaded', function() {

        const spinner = document.getElementById('global-spinner');

        //mostrar detalle
        const buttons_detalle = document.querySelectorAll('.open-details-modal');
        buttons_detalle.forEach(button => {
            button.addEventListener('click', function() {
                const sale_id = this.getAttribute('data-bs-venta_id');
                const tabla = document.getElementById('modal-productos');
                tabla.innerHTML = '';
                document.getElementById('modal-total').textContent = 'S/0.00';
                // Mostrar el spinner
                spinner.classList.remove('spinner-hidden');
                spinner.classList.add('spinner-visible');

                const direccion = this.getAttribute('data-bs-address');
                const referencia = this.getAttribute('data-bs-reference');
                const observacion = this.getAttribute('data-bs-observation');


                document.getElementById('modal-direccion').value = direccion ?? ''
                document.getElementById('modal-referencia').value = referencia ?? ''
                document.getElementById('modal-observacion').value = observacion ?? ''
                document.getElementById('modal-detalle-sale_id').value = sale_id

                $.ajax({
                    url: "{{ route('sales.details') }}?sale_id=" + sale_id,
                    method: 'GET',
                    success: function(response) {
                        let total = 0;

                        if (response.productos.length === 0) {
                            const fila = `
                                <tr>
                                    <th colspan="4">No hay productos</th>
                                </tr>
                            `;
                            tabla.innerHTML = fila; // Agrega la fila directamente
                        } else {
                            response.productos.forEach(producto => {
                                const fila = `
                                    <tr>
                                        <td>${producto.nombre}</td>
                                        <td>${producto.precio}</td>
                                        <td>${producto.cantidad}</td>
                                        <td>S/${producto.subtotal.toFixed(2)}</td>
                                    </tr>
                                `;
                                tabla.innerHTML += fila;
                                total += producto.subtotal;
                            });
                        }

                        //imprimier el total
                        document.getElementById('modal-total').textContent = `S/${total.toFixed(2)}`;

                        //ocultar spinner de carga
                        spinner.classList.remove('spinner-visible');
                        spinner.classList.add('spinner-hidden');

                        //mostrar el modal
                        const modal = new bootstrap.Modal(document.getElementById('ModalDetalle'));
                        modal.show();

                    },
                    error: function(xhr) {
                        // Ocultar el spinner de carga
                        spinner.classList.remove('spinner-visible');
                        spinner.classList.add('spinner-hidden');

                        ToastError.fire({
                            text: 'Ocurrió un error al listar los detalles'
                        });
                    }
                });
            });
        });

        //mostrar pagos
        const buttons_pago = document.querySelectorAll('.open-payments-modal');
        buttons_pago.forEach(button => {
            button.addEventListener('click', function() {
                const sale_id = this.getAttribute('data-bs-venta_id');
                const saldo = this.getAttribute('data-bs-saldo');
                const tabla = document.getElementById('modal-pagos');
                document.getElementById('modal-sale_id').value = sale_id;
                tabla.innerHTML = '';
                document.getElementById('modal-saldo').textContent = `S/${saldo}`;
                // Mostrar el spinner
                spinner.classList.remove('spinner-hidden');
                spinner.classList.add('spinner-visible');

                $.ajax({
                    url: "{{ route('payment.listar') }}?sale_id=" + sale_id,
                    method: 'GET',
                    success: function(response) {
                        let total = 0;

                        if (response.payments.length === 0) {
                            const fila = `
                                <tr>
                                    <th colspan="3">No hay pagos</th>
                                </tr>
                            `;
                            tabla.innerHTML = fila; // Agrega la fila directamente
                        } else {
                            response.payments.forEach(payment => {
                                const fila = `
                                    <tr>
                                        <td>${payment.monto}</td>
                                        <td>${payment.metodo_pago}</td>
                                        <td>${payment.fecha}</td>
                                    </tr>
                                `;
                                tabla.innerHTML += fila;
                            });
                        }

                        //ocultar spinner de carga
                        spinner.classList.remove('spinner-visible');
                        spinner.classList.add('spinner-hidden');

                        //mostrar el modal
                        const modal = new bootstrap.Modal(document.getElementById('ModalPago'));
                        modal.show();

                    },
                    error: function(xhr) {
                        // Ocultar el spinner de carga
                        spinner.classList.remove('spinner-visible');
                        spinner.classList.add('spinner-hidden');

                        ToastError.fire({
                            text: 'Ocurrió un error al listar los pagos'
                        });
                    }
                });
            });
        });

    });

    document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formFilter');
    const buttonFiltrar = document.getElementById('btnFiltrar');
    const spinner = document.getElementById('global-spinner');

    let clickedFiltrar = false;

    spinner.classList.remove('spinner-visible');
    spinner.classList.add('spinner-hidden');

    buttonFiltrar.addEventListener('click', function () {
        clickedFiltrar = true;
    });

    form.addEventListener('submit', function () {
        if (clickedFiltrar) {
            spinner.classList.remove('spinner-hidden');
            spinner.classList.add('spinner-visible');
        }

        clickedFiltrar = false;
    });
});
</script>
<script>
    const ConectorPluginV3 = (() => {

        /**
         * Una clase para interactuar con el plugin v3
         *
         * @date 2022-09-28
         * @author parzibyte
         * @see https://parzibyte.me/blog
         */

        class Operacion {
            constructor(nombre, argumentos) {
                this.nombre = nombre;
                this.argumentos = argumentos;
            }
        }

        class ConectorPlugin {

            static URL_PLUGIN_POR_DEFECTO = "http://localhost:8000";
            static Operacion = Operacion;
            static TAMAÑO_IMAGEN_NORMAL = 0;
            static TAMAÑO_IMAGEN_DOBLE_ANCHO = 1;
            static TAMAÑO_IMAGEN_DOBLE_ALTO = 2;
            static TAMAÑO_IMAGEN_DOBLE_ANCHO_Y_ALTO = 3;
            static TAMAÑO_IMAGEN_DOBLE_ANCHO_Y_ALTO = 3;
            static ALINEACION_IZQUIERDA = 0;
            static ALINEACION_CENTRO = 1;
            static ALINEACION_DERECHA = 2;
            static RECUPERACION_QR_BAJA = 0;
            static RECUPERACION_QR_MEDIA = 1;
            static RECUPERACION_QR_ALTA = 2;
            static RECUPERACION_QR_MEJOR = 3;


            constructor(ruta, serial) {
                if (!ruta) ruta = ConectorPlugin.URL_PLUGIN_POR_DEFECTO;
                if (!serial) serial = "";
                this.ruta = ruta;
                this.serial = serial;
                this.operaciones = [];
                return this;
            }

            CargarImagenLocalEImprimir(ruta, tamaño, maximoAncho) {
                this.operaciones.push(new ConectorPlugin.Operacion("CargarImagenLocalEImprimir", Array.from(arguments)));
                return this;
            }
            Corte(lineas) {
                this.operaciones.push(new ConectorPlugin.Operacion("Corte", Array.from(arguments)));
                return this;
            }
            CorteParcial() {
                this.operaciones.push(new ConectorPlugin.Operacion("CorteParcial", Array.from(arguments)));
                return this;
            }
            DefinirCaracterPersonalizado(caracterRemplazo, matriz) {
                this.operaciones.push(new ConectorPlugin.Operacion("DefinirCaracterPersonalizado", Array.from(arguments)));
                return this;
            }
            DescargarImagenDeInternetEImprimir(urlImagen, tamaño, maximoAncho) {
                this.operaciones.push(new ConectorPlugin.Operacion("DescargarImagenDeInternetEImprimir", Array.from(arguments)));
                return this;
            }
            DeshabilitarCaracteresPersonalizados() {
                this.operaciones.push(new ConectorPlugin.Operacion("DeshabilitarCaracteresPersonalizados", Array.from(arguments)));
                return this;
            }
            DeshabilitarElModoDeCaracteresChinos() {

                this.operaciones.push(new ConectorPlugin.Operacion("DeshabilitarElModoDeCaracteresChinos", Array.from(arguments)));
                return this;
            }
            EscribirTexto(texto) {
                this.operaciones.push(new ConectorPlugin.Operacion("EscribirTexto", Array.from(arguments)));
                return this;
            }
            EstablecerAlineacion(alineacion) {
                this.operaciones.push(new ConectorPlugin.Operacion("EstablecerAlineacion", Array.from(arguments)));
                return this;
            }
            EstablecerEnfatizado(enfatizado) {
                this.operaciones.push(new ConectorPlugin.Operacion("EstablecerEnfatizado", Array.from(arguments)));
                return this;
            }
            EstablecerFuente(fuente) {
                this.operaciones.push(new ConectorPlugin.Operacion("EstablecerFuente", Array.from(arguments)));
                return this;
            }
            EstablecerImpresionAlReves(alReves) {
                this.operaciones.push(new ConectorPlugin.Operacion("EstablecerImpresionAlReves", Array.from(arguments)));
                return this;
            }
            EstablecerImpresionBlancoYNegroInversa(invertir) {
                this.operaciones.push(new ConectorPlugin.Operacion("EstablecerImpresionBlancoYNegroInversa", Array.from(arguments)));
                return this;
            }
            EstablecerRotacionDe90Grados(rotar) {
                this.operaciones.push(new ConectorPlugin.Operacion("EstablecerRotacionDe90Grados", Array.from(arguments)));
                return this;
            }
            EstablecerSubrayado(subrayado) {
                this.operaciones.push(new ConectorPlugin.Operacion("EstablecerSubrayado", Array.from(arguments)));
                return this;
            }
            EstablecerTamañoFuente(multiplicadorAncho, multiplicadorAlto) {
                this.operaciones.push(new ConectorPlugin.Operacion("EstablecerTamañoFuente", Array.from(arguments)));
                return this;
            }
            Feed(lineas) {
                this.operaciones.push(new ConectorPlugin.Operacion("Feed", Array.from(arguments)));
                return this;
            }
            HabilitarCaracteresPersonalizados() {
                this.operaciones.push(new ConectorPlugin.Operacion("HabilitarCaracteresPersonalizados", Array.from(arguments)));
                return this;
            }
            HabilitarElModoDeCaracteresChinos() {
                this.operaciones.push(new ConectorPlugin.Operacion("HabilitarElModoDeCaracteresChinos", Array.from(arguments)));
                return this;
            }
            ImprimirCodigoDeBarrasCodabar(contenido, alto, ancho, tamañoImagen) {

                this.operaciones.push(new ConectorPlugin.Operacion("ImprimirCodigoDeBarrasCodabar", Array.from(arguments)));
                return this;
            }

            ImprimirCodigoDeBarrasCode128(contenido, alto, ancho, tamañoImagen) {
                this.operaciones.push(new ConectorPlugin.Operacion("ImprimirCodigoDeBarrasCode128", Array.from(arguments)));
                return this;
            }
            ImprimirCodigoDeBarrasCode39(contenido, incluirSumaDeVerificacion, modoAsciiCompleto, alto, ancho, tamañoImagen) {
                this.operaciones.push(new ConectorPlugin.Operacion("ImprimirCodigoDeBarrasCode39", Array.from(arguments)));
                return this;
            }

            ImprimirCodigoDeBarrasCode93(contenido, alto, ancho, tamañoImagen) {
                this.operaciones.push(new ConectorPlugin.Operacion("ImprimirCodigoDeBarrasCode93", Array.from(arguments)));
                return this;
            }

            ImprimirCodigoDeBarrasEan(contenido, alto, ancho, tamañoImagen) {
                this.operaciones.push(new ConectorPlugin.Operacion("ImprimirCodigoDeBarrasEan", Array.from(arguments)));
                return this;
            }
            ImprimirCodigoDeBarrasEan8(contenido, alto, ancho, tamañoImagen) {
                this.operaciones.push(new ConectorPlugin.Operacion("ImprimirCodigoDeBarrasEan8", Array.from(arguments)));
                return this;
            }
            ImprimirCodigoDeBarrasPdf417(contenido, nivelSeguridad, alto, ancho, tamañoImagen) {
                this.operaciones.push(new ConectorPlugin.Operacion("ImprimirCodigoDeBarrasPdf417", Array.from(arguments)));
                return this;
            }
            ImprimirCodigoDeBarrasTwoOfFiveITF(contenido, intercalado, alto, ancho, tamañoImagen) {
                this.operaciones.push(new ConectorPlugin.Operacion("ImprimirCodigoDeBarrasTwoOfFiveITF", Array.from(arguments)));
                return this;
            }
            ImprimirCodigoDeBarrasUpcA(contenido, alto, ancho, tamañoImagen) {
                this.operaciones.push(new ConectorPlugin.Operacion("ImprimirCodigoDeBarrasUpcA", Array.from(arguments)));
                return this;
            }
            ImprimirCodigoDeBarrasUpcE(contenido, alto, ancho, tamañoImagen) {
                this.operaciones.push(new ConectorPlugin.Operacion("ImprimirCodigoDeBarrasUpcE", Array.from(arguments)));
                return this;
            }
            ImprimirCodigoQr(contenido, anchoMaximo, nivelRecuperacion, tamañoImagen) {
                this.operaciones.push(new ConectorPlugin.Operacion("ImprimirCodigoQr", Array.from(arguments)));
                return this;
            }
            ImprimirImagenEnBase64(imagenCodificadaEnBase64, tamaño, maximoAncho) {
                this.operaciones.push(new ConectorPlugin.Operacion("ImprimirImagenEnBase64", Array.from(arguments)));
                return this;
            }

            Iniciar() {
                this.operaciones.push(new ConectorPlugin.Operacion("Iniciar", Array.from(arguments)));
                return this;
            }

            Pulso(pin, tiempoEncendido, tiempoApagado) {
                this.operaciones.push(new ConectorPlugin.Operacion("Pulso", Array.from(arguments)));
                return this;
            }

            TextoSegunPaginaDeCodigos(numeroPagina, pagina, texto) {
                this.operaciones.push(new ConectorPlugin.Operacion("TextoSegunPaginaDeCodigos", Array.from(arguments)));
                return this;
            }


            static async obtenerImpresoras(ruta) {
                if (ruta) ConectorPlugin.URL_PLUGIN_POR_DEFECTO = ruta;
                const response = await fetch(ConectorPlugin.URL_PLUGIN_POR_DEFECTO + "/impresoras");
                return await response.json();
            }

            static async obtenerImpresorasRemotas(ruta, rutaRemota) {
                if (ruta) ConectorPlugin.URL_PLUGIN_POR_DEFECTO = ruta;
                const response = await fetch(ConectorPlugin.URL_PLUGIN_POR_DEFECTO + "/reenviar?host=" + rutaRemota);
                return await response.json();
            }


            async imprimirEnImpresoraRemota(nombreImpresora, rutaRemota) {
                const payload = {
                    operaciones: this.operaciones,
                    nombreImpresora,
                    serial: this.serial,
                };
                const response = await fetch(this.ruta + "/reenviar?host=" + rutaRemota, {
                    method: "POST",
                    body: JSON.stringify(payload),
                });
                return await response.json();
            }

            // CAMBIO: generar un json e imprimirlo en consola
            async imprimirEn(nombreImpresora, response) {
                
                const idVenta  = response.id;
                const sede = response.headquarter.nombre;
                
                const payload = {
                    operaciones: this.operaciones,
                    nombreImpresora,
                    serial: this.serial,
                    idVenta: idVenta,
                    sede: sede
                };
                            

                try {
                    const response = await fetch('{{-- route("printer.save-job") --}}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: JSON.stringify(payload)
                    });
                    const data = await response.json();
                    if (data.status) {
                        alert('Archivo guardado: ' + data.file);
                    } else {
                        alert('Error al guardar el archivo');
                    }
                } catch (error) {
                    console.error('Error al enviar el JSON:', error);
                    alert('Error al enviar el JSON');
                }

                console.log(payload);
            }
        }
        return ConectorPlugin;
    })();
</script>

<script>
    var serial = '{{ config('printer.serial') }}';
    const ticketPdfTemplateUrl = @json(route('sales.ticket_pdf', ['sale' => '__SALE_ID__']));

    $(document).on('click', '.btn-print-sale', async function() {
        var saleId = $(this).data('sale-id');
        const pdfUrl = ticketPdfTemplateUrl.replace('__SALE_ID__', saleId);
        try {
            await printTicketRawWithQz(saleId, {
                printerName: 'Ticketera',
                pdfUrl: pdfUrl,
                fallbackOpen: true
            });
            ToastMessage.fire({
                text: 'Ticket enviado a ticketera (QZ Tray).'
            });
        } catch (error) {
            console.error('Error al imprimir ticket histórico con QZ:', error);
            ToastError.fire({
                text: 'No se pudo imprimir en ticketera. Se abrió el PDF para impresión manual.'
            });
        }
    });

    $(document).on('click', '.btn-preview-ticket-pdf', function() {
        var saleId = $(this).data('sale-id');
        abrirVistaPreviaTicket(saleId);
    });

    function abrirVistaPreviaTicket(saleId) {
        const pdfUrl = ticketPdfTemplateUrl.replace('__SALE_ID__', saleId);
        const iframe = document.getElementById('print-preview-iframe');
        const openInNewTabBtn = document.getElementById('open-ticket-pdf-new-tab');
        const modalElement = document.getElementById('printPreviewModal');

        iframe.src = pdfUrl;
        openInNewTabBtn.href = pdfUrl;

        const modalInstance = bootstrap.Modal.getOrCreateInstance(modalElement);
        modalInstance.show();
    }

    async function intentarImpresion(response, voucherTypeFormatted, totalVenta) {
        try {
            const tipoComprobante = response.voucher_type;
            const esAnticipada = response.type_sale == 1 || response.type_sale == 3;
            const esDelivery = response.type_sale == 2 || response.type_sale == 3;

            // Configuración de impresión
            const IP_COMPUTADORA_REMOTA = "192.168.18.46";
            const PUERTO_REMOTO = "8000";
            const URL_REMOTA = `http://${IP_COMPUTADORA_REMOTA}:${PUERTO_REMOTO}`;

            const licence = serial;
            const conector = new ConectorPluginV3(ConectorPluginV3.URL_PLUGIN_POR_DEFECTO, licence);
            await conector.Iniciar();

            // Usar los productos desde la respuesta del servidor (más confiable)
            const productosParaImprimir = response.details;

            // Crear el documento de impresión
            let impresionTexto = crearDocumentoImpresion(
                conector,
                response,
                voucherTypeFormatted,
                productosParaImprimir,
                totalVenta,
                tipoComprobante,
                esAnticipada,
                esDelivery
            );

            // Intentar imprimir
            await ejecutarImpresion(conector, impresionTexto, tipoComprobante, voucherTypeFormatted, URL_REMOTA, response);

        } catch (error) {
            console.error('Error en impresión:', error);
            ToastMessage.fire({
                icon: 'warning',
                text: `Venta guardada correctamente, pero error en impresión: ${error.message}`
            });
        }
    }

    async function ejecutarImpresion(conector, impresionTexto, tipoComprobante, voucherTypeFormatted, URL_REMOTA, response) {
        const nombreImpresora = 'ticketera';

        try {
            // PASO 1: Intentar impresión local
            console.log('Intentando impresión local...');
            conector.imprimirEn(nombreImpresora, response);
            // let resultado = await conector.imprimirEn(nombreImpresora);

            // if (resultado && resultado.ok) {
            //     console.log('Impresión local exitosa');
            //     ToastMessage.fire({
            //         icon: 'success',
            //         text: `${voucherTypeFormatted} impreso localmente`
            //     });
            //     return;
            // }

            // // PASO 2: Intentar impresión remota
            // console.log('Impresión local falló, intentando remota...');
            // const urlRemotaCompleta = `${URL_REMOTA}/imprimir`;
            // resultado = await conector.imprimirEnImpresoraRemota(nombreImpresora, urlRemotaCompleta);

            // if (resultado && resultado.ok) {
            //     console.log('Impresión remota exitosa');
            //     ToastMessage.fire({
            //         icon: 'success',
            //         text: `${voucherTypeFormatted} impreso remotamente`
            //     });
            //     return;
            // }

            // // PASO 3: Ambas fallaron
            // const mensajeError = resultado && resultado.message ? resultado.message : "No se pudo conectar con ninguna impresora";
            // ToastMessage.fire({
            //     icon: 'warning',
            //     text: `Error al imprimir: ${mensajeError}`
            // });

        } catch (error) {
            console.error('Error en ejecutarImpresion:', error);
            ToastMessage.fire({
                icon: 'warning',
                text: `Error en impresión: ${error.message}`
            });
        }
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

    function agregarProductosImpresion(impresionTexto, productos) {
        const ID_PAN_VARIOS = 238;

        // console.log("🖨️ === PRODUCTOS PARA IMPRIMIR ===");
        // productos.forEach((prod, index) => {
        //     console.log(`Producto ${index}:`, prod);
        // });
        // console.log("================================");

        // Separar Pan Varios de otros productos
        const productosPanVarios = productos.filter(prod => parseInt(prod.product_id) === ID_PAN_VARIOS);
        const otrosProductos = productos.filter(prod => parseInt(prod.product_id) !== ID_PAN_VARIOS);

        

        // 1. PROCESAR PAN VARIOS (agrupado)
        if (productosPanVarios.length > 0) {
            let totalCantidadPan = 0;
            let totalPrecioPan = 0;
            let descuentoPan = 0;

            productosPanVarios.forEach(prod => {
                const precioUnitario = parseFloat(prod.unit_price) || 0;
                const cantidad = parseFloat(prod.quantity) || 0;
                const subtotal = precioUnitario * cantidad;

                totalCantidadPan += cantidad;
                totalPrecioPan += subtotal;

                if (prod.descuento) {
                    descuentoPan = parseFloat(prod.descuento) || 0;
                }
            });

            impresionTexto = impresionTexto
                .EscribirTexto("PAN VARIOS\n")
                .EscribirTexto(
                    `1     S/${totalPrecioPan.toFixed(2)}     S/${descuentoPan.toFixed(2)}     S/${totalPrecioPan.toFixed(2)}\n`
                );
        }

        // 2. PROCESAR OTROS PRODUCTOS (individualmente)
        otrosProductos.forEach(prod => {
            
            const cantidad = parseInt(prod.quantity) || 0;
            const precioUnitario = parseFloat(prod.unit_price) || 0;
            const descuento = parseFloat(prod.descuento) || 0;
            const subtotal = precioUnitario * cantidad;

            // console.log(`🖨️ IMPRIMIENDO - ${prod.nombre}:`, {
            //     datos_originales: {
            //         precio: prod.precio,
            //         cantidad: prod.cantidad
            //     },
            //     datos_procesados: {
            //         precioUnitario,
            //         cantidad,
            //         subtotal
            //     },
            //     linea_impresion: `${cantidad}     S/${precioUnitario.toFixed(2)}     S/${descuento.toFixed(2)}     S/${subtotal.toFixed(2)}`
            // });

            console.log(`${prod.product.nombre}\n`)

            impresionTexto = impresionTexto
                .EscribirTexto(`${prod.product.nombre}\n`)
                .EscribirTexto(
                    `${cantidad}     S/${precioUnitario.toFixed(2)}     S/${descuento.toFixed(2)}     S/${subtotal.toFixed(2)}\n`
                );
        });

        return impresionTexto;
    }

    function crearPieDocumento(impresionTexto, tipoComprobante, totalVenta, esAnticipada, esDelivery) {
        let textoValidez = "";
        let textoFinal = "";

        switch (tipoComprobante) {
            case 'ticket':
                textoValidez = "NO VÁLIDO COMO DOCUMENTO CONTABLE";
                textoFinal = "PUEDE CANJEARLO POR UNA BOLETA O FACTURA";
                break;
            case 'boleta':
                textoValidez = "DOCUMENTO VÁLIDO";
                textoFinal = "PUEDE CANJEARLO POR UNA FACTURA";
                break;
            case 'factura':
                textoValidez = "DOCUMENTO CONTABLE VÁLIDO";
                textoFinal = "GRACIAS POR SU COMPRA";
                break;
        }
        const textoMonto = convertirMontoALetras(totalVenta);

        return impresionTexto
            .Feed(1)
            .EstablecerAlineacion(2)
            .EstablecerEnfatizado(true)
            .EscribirTexto(`TOTAL: S/${totalVenta.toFixed(2)}\n`)
            .Feed(1)
            .EstablecerAlineacion(1) // Alineación izquierda para el texto en letras
            .EstablecerEnfatizado(false)
            .EscribirTexto(`SON: ${textoMonto}\n`)
            .Feed(1)
            .EstablecerAlineacion(1)
            .EstablecerEnfatizado(true)
            .TextoSegunPaginaDeCodigos(2, "cp850", `${textoValidez}\n`)
            .EscribirTexto(`${textoFinal}\n`)
            .EscribirTexto(esAnticipada ? "Recuerde recoger su pedido en la fecha acordada\n" : "")
            .EscribirTexto(esDelivery ? "Pedido será entregado a domicilio\n" : "")
            .TextoSegunPaginaDeCodigos(2, "cp850", "Elaborado por Xinergia de Corporación XPANDE\n")
            .Pulso(48, 60, 120)
            .Corte(1);
    }

    function crearDocumentoImpresion(conector, response, voucherTypeFormatted, productos, totalVenta, tipoComprobante, esAnticipada, esDelivery) {
        // Crear encabezado
        let tipoDocumentoCompleto = voucherTypeFormatted;
        if (esAnticipada) tipoDocumentoCompleto += " - ANTICIPADA";
        if (esDelivery) tipoDocumentoCompleto += " - DELIVERY";
        
        
     

        // Obtener datos del cliente - usando los IDs correctos
        const numeroVenta = response.id || 'N/A';

        // Obtener datos del cliente
        const nombreCliente = response.client?.nombre || 'N/A';
        const documentoCliente = response.client?.ruc_dni || 'N/A';

        // Obtener métodos de pago seleccionados - CORREGIDO
        let metodos_pago = [];
        if (response.payments && response.payments.length > 0) {
            metodos_pago = response.payments.map(payment => ({
                nombre: payment.payment_method.nombre,
                monto: payment.monto
            }));
        } else {
            // fallback: obtener de los botones activos como antes
            // ...código existente...
        }

        // Para la fecha, intentar múltiples fuentes
        let fechaVenta;
        if (response.fecha) {
            fechaVenta = response.fecha;
        } else {
            // Si no hay fecha en la respuesta, usar la fecha actual
            const now = new Date();
            const day = String(now.getDate()).padStart(2, '0');
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const year = now.getFullYear();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            fechaVenta = `${day}/${month}/${year} ${hours}:${minutes}`;
        }

        let impresionTexto = conector
            .EstablecerAlineacion(1)
            .EscribirTexto("MARARENA\n")
            .EstablecerAlineacion(0)
            .EscribirTexto(`Nro.: ${numeroVenta}\n`)
            .EscribirTexto(`Tipo: ${tipoDocumentoCompleto}\n`)
            .EscribirTexto(`Cliente: ${nombreCliente}\n`)
            .EscribirTexto(`Nro Doc: ${documentoCliente}\n`)
            .EscribirTexto(`Fecha: ${fechaVenta}\n`)
            .EscribirTexto("--------------------------------\n");

        // ...en la función crearDocumentoImpresion...
        if (metodos_pago.length > 0) {
            impresionTexto = impresionTexto.EscribirTexto("Formas de Pago:\n");
            metodos_pago.forEach((metodoPago) => {
                impresionTexto = impresionTexto.EscribirTexto(`${metodoPago.nombre}: S/ ${parseFloat(metodoPago.monto).toFixed(2)}\n`);
                if (metodoPago.nombre.toLowerCase() === 'EFECTIVO' && metodoPago.vuelto) {
                    impresionTexto = impresionTexto.EscribirTexto(`Vuelto: S/ ${parseFloat(metodoPago.vuelto).toFixed(2)}\n`);
                }
            });
        }

        impresionTexto = impresionTexto
            .EscribirTexto("--------------------------------\n")
            .EscribirTexto(esAnticipada ? "VENTA ANTICIPADA\n" : "")
            .EscribirTexto(esDelivery ? "SERVICIO DELIVERY\n" : "")
            .Feed(1)
            .EstablecerEnfatizado(true)
            .EscribirTexto("Cant.    Precio    Dcto    Subtotal\n")
            .EstablecerEnfatizado(false);

        // Agregar productos
        impresionTexto = agregarProductosImpresion(impresionTexto, productos);

        // Crear pie
        impresionTexto = crearPieDocumento(impresionTexto, tipoComprobante, totalVenta, esAnticipada, esDelivery);

        return impresionTexto;
    }

    document.getElementById('btnExcel').addEventListener('click', function() {
        const form = document.getElementById('formFilter');
        const formData = new FormData(form);

        // Construir la query string con todos los campos del formulario
        const params = new URLSearchParams(formData).toString();

        // Ruta a la que quieres enviar los datos (ajusta según tu ruta)
        const url = '{{-- route("sales.excel") --}}' + '?' + params;

        // Redirigir para descargar el Excel (GET)
        window.open(url, '_blank');

    });

    document.getElementById('btnPDF').addEventListener('click', function() {
        const form = document.getElementById('formFilter');
        const formData = new FormData(form);

        // Construir la query string con todos los campos del formulario
        const params = new URLSearchParams(formData).toString();

        // Ruta a la que quieres enviar los datos (ajusta según tu ruta)
        const url = '{{-- route("sales.pdfReport") --}}' + '?' + params;

        // Redirigir para descargar el PDF (GET)
        window.open(url, '_blank');

    });

    let clientSearchTimeout = null;
    $('#search-client').autocomplete({
        source: function(request, response) {
            clearTimeout(clientSearchTimeout);
            clientSearchTimeout = setTimeout(function() {
                let currentTerm = $('#search-client').val();
                // Solo buscar si hay al menos una letra
                if (currentTerm && currentTerm.length > 0) {
                    $.ajax({
                        url: '{{ route('clients.search') }}',
                        method: 'get',
                        data: {
                            query: currentTerm
                        },
                        success: function(data) {
                            response($.map(data, function(item) {
                                return {
                                    label: item.business_name ? item.business_name : item.contact_name,
                                    value: item.business_name ? item.business_name : item.contact_name,
                                    id: item.id,
                                };
                            }));
                        }
                    });
                } else {
                    // Si no hay letras, limpia el autocomplete
                    response([]);
                }
            }, 750);
        },
        appendTo: '.container-fluid',
        select: function(event, ui) {
            $('#client_id').val(ui.item.id);
        },
    }).autocomplete("instance")._renderItem = function(ul, item) {
        return $("<li>")
            .append(`<div class="d-flex justify-content-between"><span>${item.label}</span></div>`)
            .appendTo(ul);
    };

    $('#search-client').on('input', function() {
        $('#client_id').val('');
    });

    function imprimirVenta(saleId) {
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
                            } else {
                                throw new Error('Impresión remota falló: ' + remoteRes.message);
                            }
                        } catch (errorRemoto) {
                            console.error('Error al imprimir boleta/factura:', errorRemoto);
                            ToastError.fire({
                                text: 'Error al imprimir la boleta/factura: ' + errorRemoto.message
                            });
                            return;
                        } 
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
                        } else {
                            throw new Error('Impresión remota falló: ' + remoteRes.message);
                        }
                    } catch (errorRemoto) {
                        console.error('Error al imprimir ticket:', errorRemoto);
                        ToastError.fire({
                            text: 'Error al imprimir el ticket: ' + errorRemoto.message
                        });
                    } 
                }
            },
            error: function(xhr, status, error) {
                console.log('Error en la solicitud:', error);
                ToastError.fire({
                    text: 'Error al obtener datos para impresión'
                });
            }
        });
    }

</script>

<style>
    .spinner-hidden {
        display: none !important;
    }

    .spinner-visible {
        display: flex !important;
        z-index: 2000 !important;
    }
    .ver-foto-disabled {
        color: #aaa !important;
        pointer-events: none;
        text-decoration: none !important;
        cursor: not-allowed;
    }
</style>
@endsection
