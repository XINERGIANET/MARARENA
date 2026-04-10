@extends('layouts.app')

@section('styles')
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

    /* Estilos para las nuevas columnas */
    .table td {
        vertical-align: middle;
    }

    .table .foto-column {
        width: 80px;
        text-align: center;
    }

    .table .medios-pago-column {
        max-width: 200px;
    }

    .table .productos-column {
        padding-right: 0.2rem;
    }

    .table .estado-column {
        width: 120px;
        text-align: center;
    }

    .table .acciones-column {
        width: 180px;
        text-align: center;
    }

    .btn-group .btn {
        margin-right: 2px;
    }

    .btn-group .btn:last-child {
        margin-right: 0;
    }

    .img-thumbnail {
        transition: transform 0.2s;
    }

    .img-thumbnail:hover {
        transform: scale(1.1);
    }

    /* Estilos para medios de pago */
    .payment-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 2px 0;
    }

    .payment-item .badge {
        font-size: 0.7rem;
        white-space: nowrap;
    }

    .payment-item small {
        font-weight: 500;
        margin-left: 5px;
    }
</style>

@endsection

@section('header')
<h2>Ventas por entregar</h2>
<p>Lista de ventas por entregar</p>
@endsection

@section('content')
@php
$colors = ['btn-outline-primary', 'btn-outline-success', 'btn-outline-info', 'btn-outline-warning', 'btn-outline-danger', 'btn-outline-dark'];
@endphp
<div class="container-fluid content-inner mt-n5 py-0">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">

                <div class="card-body border-bottom">
                    <form action="" id="fromFilter">
                        <div class="row d-flex">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Número de comprobante</label>
                                    <input type="text" class="form-control" name="number" value="{{ request()->number ? request()->number : '' }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Fecha de entrega inicial</label>
                                    <input type="date" class="form-control" name="start_date" value="{{ request()->start_date ? request()->start_date : '' }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Fecha de entrega final</label>
                                    <input type="date" class="form-control" name="end_date" value="{{ request()->end_date ? request()->end_date : '' }}">
                                </div>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <div class="mb-3 w-50s me-2">
                                    <button type="submit" class="btn btn-primary w-100" id="btnFiltrar">Filtrar</button>
                                </div>
                                <div class="mb-3 w-50s me-2">
                                    <a href="{{ route('sales.anticipated') }}" class="btn btn-warning w-100" id="">Limpiar</a>
                                </div>

                            </div>
                        </div>

                    </form>
                </div>

                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Número</th>
                                    <th>Total (S/)</th>
                                    <th>Saldo (S/)</th>
                                    <th class="medios-pago-column">Métodos de Pago</th>
                                    <th class="estado-column">Estado</th>
                                    <th class="productos-column">Productos</th>
                                    <th class="observacion-column">Observación</th>
                                    <th class="foto-column">Foto</th>
                                    <th>Fecha entrega</th>
                                    <th>Hora entrega</th>
                                    <!-- <th>Sede</th> -->
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($anticipadas as $anticipada)
                                <tr id="fila-venta-{{ $anticipada->id }}">
                                    <td class="number-column">
                                        {{ $anticipada->number }}
                                    </td>
                                    <td>S/ {{ number_format($anticipada->total, 2) }}</td>
                                    <td id="saldo-venta-{{ $anticipada->id }}">S/ {{ number_format($anticipada->saldo(), 2) }}</td>

                                    <!-- Medios de Pago -->
                                    <td class=" medios-pago-column">
                                        @if($anticipada->payments && $anticipada->payments->count() > 0)
                                        @foreach($anticipada->payments as $payment)
                                        <div class="payment-item mb-1">
                                            <span class="">{{ $payment->payment_method->name ?? 'N/A' }}</span>
                                            <small class="text-muted">S/ {{ number_format($payment->subtotal, 2) }}</small>
                                        </div>
                                        @endforeach
                                        @else
                                        <span class="text-muted">Sin pagos</span>
                                        @endif
                                    </td>

                                    <!-- Estado de Entrega -->
                                    <td class=" estado-column">
                                        @if($anticipada->status == 0)
                                            @if($anticipada->saldo() == 0)
                                            <span class="badge bg-primary">Por entregar</span>
                                            @else
                                            <span class="badge bg-danger">Por pagar</span>
                                            @endif
                                        @elseif($anticipada->status == 1)
                                            <span class="badge bg-info">Entregado</span>
                                        @endif
                                    </td>

                                    <!-- Productos -->
                                    <td class=" productos-column">
                                        @if($anticipada->details && $anticipada->details->count() > 0)
                                        <div class="small">
                                            @foreach($anticipada->details as $detail)
                                            <div class="mb-1">
                                                <strong>{{ $detail->quantity }}x</strong> {{ $detail->product->name ?? 'Producto' }}
                                            </div>
                                            @endforeach
                                        </div>
                                        @else
                                        <span class="text-muted">Sin productos</span>
                                        @endif
                                    </td>

                                    <td>
                                        <div class="text-center">
                                            @php
                                            $observacion = $anticipada->observation ?? 'Sin observación';
                                            // Si la observación es muy larga (más de 50 caracteres), agregar saltos de línea
                                            if (strlen($observacion) > 50) {
                                            $observacion = wordwrap($observacion, 50, '<br>', true);
                                            }
                                            @endphp
                                            {!! $observacion !!}
                                        </div>
                                    </td>

                                    <!-- Foto -->
                                    <td class="foto-column">
                                        @if($anticipada->foto)
                                        <img src="../storage/app/public/{{ $anticipada->foto }}"
                                            alt="Foto de pedido"
                                            class="img-thumbnail foto-pedido"
                                            style="width: 60px; height: 60px; object-fit: cover; cursor: pointer;"
                                            onclick="window.open(this.src, '_blank')"
                                            onerror="tryAlternativeImagePath(this, '{{ $anticipada->foto }}');">
                                        <span class="text-muted imagen-error" style="display: none;">Imagen no disponible</span>
                                        @else
                                        <span class="text-muted">Sin foto</span>
                                        @endif
                                    </td>
                                    <td>{{ optional($anticipada->delivery_date)->format('d/m/Y') ?? '-' }} </td>
                                    <td>{{ $anticipada->delivery_hour ?? '-' }}</td>
                                    <td class="acciones-column">
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-primary btn-sm open-details-modal"
                                                data-bs-venta_id="{{ $anticipada->id }}"
                                                data-bs-cliente="{{ $anticipada->client->nombre ?? $anticipada->client_name ?? 'Varios' }}"
                                                data-bs-telefono="{{ $anticipada->phone }}"
                                                data-bs-fecha="{{ optional($anticipada->date)->format('d/m/Y') ?? '' }}"
                                                data-bs-fecha_entrega="{{ optional($anticipada->delivery_date)->format('Y-m-d') ?? '' }}"
                                                data-bs-hora_entrega="{{ $anticipada->delivery_hour }}"
                                                data-bs-address="{{ $anticipada->address }}"
                                                data-bs-reference="{{ $anticipada->reference }}"
                                                data-bs-foto="{{ $anticipada->foto }}"
                                                data-bs-observation="{{ $anticipada->observation }}"
                                                style="--bs-btn-padding-x: .5rem; --bs-btn-font-size: .75rem;"
                                                title="Ver detalles">
                                                <i class="bi bi-list-task"></i>
                                            </button>

                                            {{-- 
                                            @if($anticipada)
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
                                            @endif

                                            <!-- Nuevo botón de impresión -->
                                            <button type="button" class="btn btn-secondary btn-sm btn-imprimir"
                                                data-bs-venta_id="{{ $anticipada->id }}"
                                                style="--bs-btn-padding-x: .5rem; --bs-btn-font-size: .75rem;"
                                                title="Imprimir ticket">
                                                <i class="bi bi-printer"></i>
                                            </button>
                                            --}}
                                            
                                            @if($anticipada->voucher_type == 'Boleta' || $anticipada->voucher_type == 'Factura')
                                            <button type="button" class="btn btn-success btn-sm open-payments-modal" disabled
                                                style="--bs-btn-padding-x: .5rem; --bs-btn-font-size: .75rem;"
                                                title="Gestionar pagos">
                                                <i class="bi bi-currency-dollar"></i>
                                            </button>
                                            @else
                                            <button type="button" class="btn btn-success btn-sm open-payments-modal"
                                                data-bs-venta_id="{{ $anticipada->id }}"
                                                data-bs-saldo="{{ $anticipada->saldo() }}"
                                                data-bs-doc-type="{{ strlen($anticipada->client->ruc_dni ?? '') == 8 ? 'DNI' : (strlen($anticipada->client->ruc_dni ?? '') == 11 ? 'RUC' : 'Otro') }}"
                                                data-bs-doc-number="{{ $anticipada->client->ruc_dni ?? '' }}"
                                                style="--bs-btn-padding-x: .5rem; --bs-btn-font-size: .75rem;"
                                                title="Gestionar pagos">
                                                <i class="bi bi-currency-dollar"></i>
                                            </button>
                                            @endif

                                            @if($anticipada->voucher_type == 'Boleta' || $anticipada->voucher_type == 'Factura')
                                            <a href="{{ config('apisunat.url').'/documents/'.$anticipada->voucher_id.'/getPDF/A4/'.$anticipada->voucher_file }}"
                                                target="_blank" class="btn btn-primary btn-sm btn-icon" title="Ver Comprobante">
                                                A4
                                            </a>
                                            @else
                                            <button type="button" class="btn btn-secondary btn-sm btn-icon" disabled title="No disponible para {{ $anticipada->voucher_type }}">
                                                A4
                                            </button>
                                            @endif

                                            @if($anticipada->foto)
                                            <a target="_blank" class="btn btn-warning btn-sm"
                                                href="../storage/app/public/{{ $anticipada->foto }}"
                                                style="--bs-btn-padding-x: .5rem; --bs-btn-font-size: .75rem;"
                                                title="Ver foto completa">
                                                <i class="bi bi-image"></i>
                                            </a>
                                            @endif

                                            @if($anticipada->status == 0)
                                            <button type="button"
                                                class="btn btn-info btn-sm btn-entrega"
                                                id="entrega-venta-{{ $anticipada->id }}"
                                                data-bs-venta_id="{{ $anticipada->id }}"
                                                style="--bs-btn-padding-x: .5rem; --bs-btn-font-size: .75rem;"
                                                title="Confirmar entrega"
                                                {{ $anticipada->saldo() != 0 ? 'disabled title="El saldo debe ser 0 para entregar"' : '' }}>
                                                <i class="bi bi-box-seam"></i>
                                            </button>
                                            @endif

                                            <form class="form-delete-sale" action="{{ route('sales.destroy', $anticipada->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-danger btn-icon btn-delete-sale" data-id="{{ $anticipada->id }}" title="Eliminar">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Entrega producto -->
<div class="modal fade" id="modalConfirmarEntrega" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center">
            <div class="modal-header">
                <h5 class="modal-title">Confirmación de entrega</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p>¿Productos entregados?</p>
                <input type="hidden" id="venta-id-entregar">
            </div>
            <div class="modal-footer d-flex justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                <button type="button" class="btn btn-success" id="confirmar-entrega">Sí</button>
            </div>
        </div>
    </div>
</div>


<!-- Modal detalles -->
<div class="modal fade" id="ModalDetalle" tabindex="-1" aria-labelledby="Productos" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Detalles de la Venta</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Información de la venta -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5 class="mb-3">Información General</h5>

                        <div class="mb-3">
                            <label for="modal-cliente" class="form-label">Cliente</label>
                            <input type="text" id="modal-cliente" class="form-control" readonly>
                        </div>

                        <div class="mb-3">
                            <label for="modal-telefono" class="form-label">Teléfono</label>
                            <input type="text" id="modal-telefono" class="form-control">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <h5 class="mb-3">Información de Entrega</h5>

                        <div class="mb-3">
                            <label for="modal-fecha_entrega" class="form-label">Fecha de Entrega</label>
                            <input type="date" id="modal-fecha_entrega" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label for="modal-hora_entrega" class="form-label">Hora de Entrega</label>
                            <input type="text" id="modal-hora_entrega" class="form-control" placeholder="Ej: 14:30">
                        </div>

                        <div class="mb-3">
                            <label for="modal-direccion" class="form-label">Dirección</label>
                            <input type="text" id="modal-direccion" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label for="modal-referencia" class="form-label">Referencia</label>
                            <input type="text" id="modal-referencia" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label for="modal-observacion" class="form-label">Observación</label>
                            <textarea id="modal-observacion" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Productos -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Productos</h5>
                        <button type="button" class="btn btn-primary btn-sm" id="btn-agregar-producto">
                            <i class="bi bi-plus"></i> Agregar Producto
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Precio</th>
                                    <th>Cantidad</th>
                                    <th>Subtotal</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="modal-productos">
                                <tr>
                                    <th colspan="5" class="text-center">No hay productos</th>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-end">Total:</th>
                                    <th id="modal-total" class="text-primary">S/0.00</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Modal para agregar producto -->
                <div class="modal fade" id="ModalAgregarProducto" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Agregar Producto</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Producto</label>
                                    <input hidden type="number" class="form-control" name="producto_id" id="producto_id">
                                    <input type="text" class="form-control" name="name" id="search-product"
                                        placeholder="Buscar Producto">
                                    {{--
                                        <select id="producto-select" class="form-select">
                                        <option value="">Seleccionar producto</option>
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}" data-precio="{{ $product->precio ?? 0 }}">
                                    {{ $product->nombre }}
                                    </option>
                                    @endforeach
                                    </select>
                                    --}}
                                </div>
                                <div class="mb-3">
                                    <label for="producto-precio" class="form-label">Precio</label>
                                    <input type="number" id="producto-precio" class="form-control" step="0.01" min="0">
                                </div>
                                <div class="mb-3">
                                    <label for="producto-cantidad" class="form-label">Cantidad</label>
                                    <input type="number" id="producto-cantidad" class="form-control" min="1" value="1">
                                </div>
                                <div class="mb-3">
                                    <label for="producto-subtotal" class="form-label">Subtotal</label>
                                    <input type="number" id="producto-subtotal" class="form-control" readonly>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="button" class="btn btn-primary" id="btn-confirmar-producto">Agregar</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gestión de foto -->
                <div class="mb-4">
                    <h5 class="mb-3">Foto del Pedido</h5>
                    <form id="form-foto" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input hidden type="number" id="modal-detalle-sale_id" name="sale_id" class="form-control">
                        <div class="row">
                            <div class="col-md-8">
                                <input type="file" id="foto-input" name="foto" class="form-control" accept="image/*">
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary me-2">Subir foto</button>
                                <a href="" id="ver-foto-link" target="_blank" class="btn btn-outline-info" disabled>Ver foto</a>
                            </div>
                        </div>
                    </form>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="btn-guardar-detalles">
                    <span id="btn-guardar-text">Guardar Cambios</span>
                    <span id="btn-guardar-spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal pagos -->
<div class="modal fade" id="ModalPago" tabindex="-1" aria-labelledby="Pagos" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Pagos</h1>
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

                <div id="metodos-pago-container" class="d-none">
                    <input type="hidden" id="modal-sale_id" value="">

                    <div class="d-flex flex-wrap mb-3">
                        @foreach ($paymentMethod as $index => $method)
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


                    @foreach ($paymentMethod as $method)
                    <div class="d-flex align-items-center mb-4 d-none" id="campos-{{ $method->name }}">
                        <label class="mb-2 me-3"><strong>{{ strlen($method->name) > 4 ? strtoupper(substr($method->nombre, 0, 4) . '.') : strtoupper($method->nombre) }}</strong></label>
                        <input hidden type="number" name="medio_pago_id" value="{{ $method->id }}">
                        <div class="input-group me-2">
                            <span class="input-group-text">S/</span>
                            <input type="text" class="form-control" placeholder="Ingrese Monto"
                                name="monto[{{ $method->id }}]"
                                onkeypress="isDecimal(event)"
                                oninput="calcularVueltoEfectivo('{{ $method->name }}', '{{ $method->id }}', this)">
                        </div>
                        @if(strtolower($method->name) === 'efectivo')
                        <div class="input-group me-2">
                            <input type="text" class="form-control" placeholder="0.00" style="width: 150px;"
                                id="vuelto-efectivo" readonly>
                        </div>
                        @endif
                    </div>
                    @endforeach

                    <button class="btn btn-success mt-3 mb-4" type="button" id="agregar-pago">Agregar pago restante</button>
                </div>

                <div class="mb-4" id="comprobante-container">
                    <input type="hidden" name="comprobante" id="comprobante" value="boleta">
                    <!-- Botones de comprobante -->
                    <div class="btn-group w-100 mb-3 gap-1" role="group" aria-label="Tipo de comprobante">
                        <button type="button" class="btn btn-outline-primary active" id="btn-boleta"
                            onclick="seleccionarComprobante('boleta', event)">Boleta</button>
                        <button type="button" class="btn btn-outline-success" id="btn-factura"
                            onclick="seleccionarComprobante('factura', event)">Factura</button>
                        <button type="button" class="btn btn-outline-info" id="btn-ticket"
                            onclick="seleccionarComprobante('ticket', event)">Ticket</button>
                    </div>
                </div>
                <!-- Inputs de Cliente y Observación -->
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label"><strong>DNI / RUC</strong></label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="document" name="document" maxlength="11"
                                onkeypress="isNumber(event)" required>
                            <button class="btn btn-primary" type="button"
                                onclick="searchAPI('#document','#client','#direccion')">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label mb-1"><strong>Cliente</strong></label>
                        <input type="text" class="form-control" id="client" name="client" autocomplete="off" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label mb-1"><strong>Observación</strong></label>
                        <input type="text" id="observacion" name="observacion" class="form-control" autocomplete="off">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="guardar-venta">Guardar Venta</button>
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

@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>
<script>
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


    // ============= FUNCIÓN AUXILIAR =============
    function obtenerNombreImpresora(tipoComprobante) {
        switch (tipoComprobante) {
            case 'ticket':
            case 'boleta':
            case 'factura':
                return "Ticketera";
            default:
                return "Ticketera";
        }
    }

    var serial = "{{ config('printer.serial') }}";


    // Función para imprimir venta anticipada específica
    $(document).on('click', '.btn-imprimir', function() {
        const saleId = $(this).attr('data-bs-venta_id');

        if (!saleId) {
            ToastError.fire({
                text: 'Error: No se pudo obtener el ID de la venta'
            });
            return;
        }

        imprimirTicketVentaSimple(saleId);

    });

    
    function imprimirVenta(saleId) {
        $.ajax({
            url: "{{-- route('anticipated_print') --}}",
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
                            argumentos: ["RUC 20611061618\n"]
                        },
                        {
                            nombre: "EscribirTexto",
                            argumentos: ["AV. JOSE BALTA NRO. 054 P.J. CHINO ZAMORA CHICLAYO\n"]
                        },
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
                    // ...existing code...

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
                        },
                        {
                            nombre: 'EscribirTexto',
                            argumentos: [`FECHA ENTREGA: ${venta.fecha_entrega}\n`]
                        },
                        {
                            nombre: 'EscribirTexto',
                            argumentos: [`HORA ENTREGA: ${venta.hora_entrega || 'No especificada'}\n`]
                        },
                        {
                            nombre: 'EscribirTexto',
                            argumentos: [`CLIENTE: ${venta.cliente}\n`]
                        },
                        {
                            nombre: 'EscribirTexto',
                            argumentos: [`TELEFONO: ${venta.telefono || '000000000'}\n`]
                        },
                        {
                            nombre: 'EscribirTexto',
                            argumentos: ['----------------------------------------\n']
                        },
                        {
                            nombre: 'EscribirTexto',
                            argumentos: ['INFORMACION DE PAGOS:\n']
                        },
                        {
                            nombre: 'EscribirTexto',
                            argumentos: [`TOTAL VENTA: S/${venta.total}\n`]
                        },
                        {
                            nombre: 'EscribirTexto',
                            argumentos: [`TOTAL PAGADO: S/${(venta.total - venta.saldo).toFixed(2)}\n`]
                        },
                        {
                            nombre: 'EscribirTexto',
                            argumentos: [`SALDO PENDIENTE: S/${venta.saldo}\n`]
                        },
                        {
                            nombre: 'EscribirTexto',
                            argumentos: ['----------------------------------------\n']
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

    function imprimirTicketVentaSimple(saleId) {
        $.ajax({
            url: "{{-- route('anticipated_print') --}}",
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
                        },
                        {
                            nombre: 'EscribirTexto',
                            argumentos: [`FECHA ENTREGA: ${venta.fecha_entrega}\n`]
                        },
                        {
                            nombre: 'EscribirTexto',
                            argumentos: [`HORA ENTREGA: ${venta.hora_entrega || 'No especificada'}\n`]
                        },
                        {
                            nombre: 'EscribirTexto',
                            argumentos: [`CLIENTE: ${venta.cliente}\n`]
                        },
                        {
                            nombre: 'EscribirTexto',
                            argumentos: [`TELEFONO: ${venta.telefono || '000000000'}\n`]
                        },
                        {
                            nombre: "EstablecerEnfatizado",
                            argumentos: [true]
                        },
                        {
                            nombre: "EstablecerEnfatizado",
                            argumentos: [false]
                        },
                        {
                            nombre: 'EscribirTexto',
                            argumentos: ['----------------------------------------\n']
                        },
                        {
                            nombre: 'EscribirTexto',
                            argumentos: ['INFORMACION DE PAGOS:\n']
                        },
                        {
                            nombre: 'EscribirTexto',
                            argumentos: [`TOTAL VENTA: S/${venta.total}\n`]
                        },
                        {
                            nombre: 'EscribirTexto',
                            argumentos: [`TOTAL PAGADO: S/${(venta.total - venta.saldo).toFixed(2)}\n`]
                        },
                        {
                            nombre: 'EscribirTexto',
                            argumentos: [`SALDO PENDIENTE: S/${venta.saldo}\n`]
                        },
                        {
                            nombre: 'EscribirTexto',
                            argumentos: ['----------------------------------------\n']
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
                    const http = await fetch('http://localhost:8000/imprimir', {
                        method: 'POST',
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
<script>
    let tipoDocumentoCliente = '';
    let numeroDocumentoCliente = '';

    // Función para probar rutas alternativas de imágenes
    function tryAlternativeImagePath(img, fotoPath) {
        const alternativePaths = [
            '{{ url("storage") }}/' + fotoPath,
            '{{ asset("storage") }}/' + fotoPath,
            '{{ url("") }}/storage/' + fotoPath,
            '../storage/' + fotoPath,
            './storage/' + fotoPath,
            'storage/app/public/' + fotoPath
        ];

        let currentIndex = img.dataset.currentIndex ? parseInt(img.dataset.currentIndex) : 0;

        if (currentIndex < alternativePaths.length) {
            img.dataset.currentIndex = currentIndex + 1;
            img.src = alternativePaths[currentIndex];
        } else {
            // Si ninguna ruta funciona, ocultar imagen y mostrar mensaje
            img.style.display = 'none';
            const errorSpan = img.nextElementSibling;
            if (errorSpan) {
                errorSpan.style.display = 'inline';
            }
        }
    }

    function isNumber(evt) {
        evt = evt || window.event;
        var charCode = evt.which || evt.keyCode;

        // Solo permite números (0–9)
        if (charCode < 48 || charCode > 57) {
            evt.preventDefault();
            return false;
        }

        return true;
    }

    document.getElementById('guardar-venta').addEventListener('click', function() {
        const ventaId = document.getElementById('modal-sale_id')?.value;
        const comprobante = document.getElementById('comprobante').value;
        const documentValue = document.getElementById('document')?.value.trim();
        const clientValue = document.getElementById('client')?.value.trim();
        const observacionValue = document.getElementById('observacion')?.value.trim();

        if (!ventaId) {
            ToastError.fire({ text: 'No se ha definido el ID de venta anticipada' });
            return;
        }
        if (!comprobante) {
            ToastError.fire({ text: 'Seleccione un tipo de comprobante' });
            return;
        }

        if (comprobante === "factura" && !documentValue) {
            ToastError.fire({ text: 'Debe ingresar un RUC valido' });
            return;
        }

        var pdfTemplateUrl;
        // if (comprobante === "ticket") {
        //     pdfTemplateUrl ={{-- @json(route('sales.pdf_detallado', ['sale' => ':id']))  --}} ;
        // } else {
        //     pdfTemplateUrl ={{-- @json(route('sales.pdf', ['sale' => ':id'])) --}};
        // }

        const spinner = document.getElementById('global-spinner');
        spinner.classList.remove('spinner-hidden');
        spinner.classList.add('spinner-visible');

        fetch(`{{ route('sales.generar_comprobante') }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                sale_id: ventaId,
                tipo_comprobante: comprobante,
                document: documentValue,
                client: clientValue,
                observacion: observacionValue
            })
        })
        .then(res => res.json())
        .then(async data => {
            spinner.classList.add('spinner-hidden');
            spinner.classList.remove('spinner-visible');

            if (data.status) {
                ToastMessage.fire({ text: 'Comprobante generado correctamente.' });
                if (ventaId) {
                    const pdfUrl = pdfTemplateUrl.replace(':id', ventaId);
                    // window.open(pdfUrl, '_blank');
                }
                try {
                    imprimirVenta(ventaId);
                } catch (error) {
                    ToastError.fire({ text: 'Error al imprimir el comprobante.' });
                }
                setTimeout(() => location.reload(), 1000);
            } else {
                ToastError.fire({ text: data.message || 'No se pudo generar el comprobante.' });
            }
        })
        .catch(error => {
            spinner.classList.add('spinner-hidden');
            spinner.classList.remove('spinner-visible');
            ToastError.fire({ text: 'Error al generar el comprobante.' });
        });
    });

    function seleccionarComprobante(comprobante, event) {
        const parent = event.target.closest('.btn-group');
        Array.from(parent.children).forEach(child => {
            child.classList.remove('active');
        });
        event.target.classList.add('active');
        document.getElementById('comprobante').value = comprobante;
    }


    function seleccionarMedioPago(medio_id, event) {
        const btn = event.target;
        btn.classList.toggle('active');

        const campos = btn.dataset.campos;
        const camposDiv = document.getElementById(campos);
        const inputMonto = document.querySelector(`#${campos} input[name="monto[${medio_id}]"]`);

        camposDiv.classList.toggle('d-none');

        if (!btn.classList.contains('active')) {
            btn.blur();
        }
        revisarUnicoMetodoSeleccionado();
    }

    function revisarUnicoMetodoSeleccionado() {
        const botonesActivos = document.querySelectorAll('[id^="btn-"].active[data-campos]');
        const saldoTexto = document.getElementById('modal-saldo')?.textContent || 'S/0.00';
        const saldo = parseFloat(saldoTexto.replace('S/', '')) || 0;

        const campoVuelto = document.getElementById('vuelto-efectivo');

        if (botonesActivos.length === 1) {
            const unicoBoton = botonesActivos[0];
            const camposId = unicoBoton.dataset.campos;
            const input = document.querySelector(`#${camposId} input[name^="monto["]`);

            if (input && input.value.trim() === '') {
                input.value = saldo.toFixed(2);
            }

            calcularVueltoTotal();

        } else {
            botonesActivos.forEach(boton => {
                const camposId = boton.dataset.campos;
                const input = document.querySelector(`#${camposId} input[name^="monto["]`);
                if (input) input.value = '';
            });

            if (campoVuelto) {
                campoVuelto.value = '0.00';
            }
        }
    }

    function isDecimal(evt) {
        evt = evt || window.event;
        var charCode = evt.which || evt.keyCode;
        if ((charCode >= 48 && charCode <= 57) || charCode === 46) {
            var input = evt.target || evt.srcElement;
            if (charCode === 46 && input.value.includes('.')) {
                evt.preventDefault();
                return false;
            }
            return true;
        } else {
            evt.preventDefault();
            return false;
        }
    }

    function calcularVueltoEfectivo(nombreMetodo, idMetodo, inputElement) {
        calcularVueltoTotal();
    }

    function calcularVueltoTotal() {
        const botonesActivos = document.querySelectorAll('[id^="btn-"].active[data-campos]');
        const saldoTexto = document.getElementById('modal-saldo')?.textContent || 'S/0.00';
        const totalVenta = parseFloat(saldoTexto.replace('S/', '')) || 0;
        const campoVuelto = document.getElementById('vuelto-efectivo');

        if (!campoVuelto) return;

        let totalPagado = 0;
        let hayEfectivo = false;

        botonesActivos.forEach(boton => {
            const camposId = boton.dataset.campos;
            const nombreMetodo = camposId.replace('campos-', '');
            const inputMonto = document.querySelector(`#${camposId} input[name^="monto["]`);
            const monto = parseFloat(inputMonto?.value) || 0;

            if (nombreMetodo.toLowerCase() === 'efectivo') {
                hayEfectivo = true;
            }

            totalPagado += monto;
        });

        if (hayEfectivo && totalPagado > totalVenta) {
            const vuelto = totalPagado - totalVenta;
            campoVuelto.value = vuelto.toFixed(2);
        } else {
            campoVuelto.value = '0.00';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('agregar-pago').addEventListener('click', function() {
            const saleInput = document.getElementById('modal-sale_id');
            if (!saleInput) {
                ToastError.fire({
                    text: 'No se encontró el ID de la venta.'
                });
                return;
            }

            const sale_id = saleInput.value;
            const saldoTexto = document.getElementById('modal-saldo')?.textContent || 'S/0.00';
            let saldoRestante = parseFloat(saldoTexto.replace('S/', '').trim()) || 0;

            const botonesActivos = document.querySelectorAll('[id^="btn-"].active[data-campos]');

            if (botonesActivos.length !== 1) {
                ToastError.fire({
                    text: 'Selecciona un único método de pago.'
                });
                return;
            }

            const boton = botonesActivos[0];
            const camposId = boton.dataset.campos;
            const metodo = camposId.replace('campos-', '');
            const input = document.querySelector(`#${camposId} input[name^="monto["]`);
            const monto = parseFloat(input?.value) || 0;

            if (monto !== saldoRestante) {
                ToastError.fire({
                    text: 'El monto debe ser igual al saldo pendiente.'
                });
                return;
            }

            if (!monto || monto <= 0) {
                ToastError.fire({
                    text: 'Ingresa un monto válido para el pago.'
                });
                return;
            }

            const spinner = document.getElementById('global-spinner');

            spinner.classList.remove('spinner-hidden');
            spinner.classList.add('spinner-visible');

            fetch("{{ route('sales.registrar_pago') }}", {
                    method: "POST",
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        sale_id: sale_id,
                        monto: monto,
                        metodo: metodo,
                    })
                })
                .then(res => res.json())
                .then(data => {
                    spinner.classList.add('spinner-hidden');
                    spinner.classList.remove('spinner-visible');

                    if (data.status) {
                        ToastMessage.fire({
                            text: 'Pago registrado correctamente.'
                        });

                        document.getElementById('modal-saldo').textContent = `S/${data.nuevo_saldo.toFixed(2)}`;

                        boton.classList.remove('active');
                        const camposDiv = document.getElementById(camposId);
                        camposDiv.classList.add('d-none');
                        input.value = '';

                        const vuelto = document.getElementById('vuelto-efectivo');
                        if (vuelto) vuelto.value = '0.00';

                        if (data.nuevo_saldo <= 0) {
                            document.getElementById('metodos-pago-container').classList.add('d-none');
                            document.getElementById('comprobante-container').classList.remove('d-none');
                            document.getElementById('guardar-venta').style.display = 'inline-block'; // <-- asegúrate de tener esta línea

                            const entregaBtn = document.getElementById(`entrega-venta-${sale_id}`);
                            if (entregaBtn) {
                                entregaBtn.disabled = false;
                            }
                        }

                        // Recargar tabla de pagos
                        cargarPagosVenta(sale_id);

                        const filaVenta = document.getElementById(`fila-venta-${sale_id}`);
                        if (filaVenta) {
                            const celdas = filaVenta.getElementsByTagName('td');
                            if (celdas.length >= 3) {
                                celdas[2].textContent = data.nuevo_saldo.toFixed(2);

                                if (data.nuevo_saldo <= 0) {
                                    const btns = filaVenta.querySelectorAll('.open-payments-modal');
                                    btns.forEach(btn => {
                                        btn.setAttribute('disabled', true);
                                    });
                                }
                            }
                        }
                    } else {
                        ToastError.fire({
                            text: data.message || 'Error al registrar el pago.'
                        });
                    }
                })
                .catch(() => {
                    spinner.classList.add('spinner-hidden');
                    spinner.classList.remove('spinner-visible');
                    ToastError.fire({
                        text: 'Error de conexión al registrar el pago.'
                    });
                });
        });

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

                // Obtener datos del botón
                const cliente = this.getAttribute('data-bs-cliente');
                const telefono = this.getAttribute('data-bs-telefono');
                const fechaEntrega = this.getAttribute('data-bs-fecha_entrega');
                const horaEntrega = this.getAttribute('data-bs-hora_entrega');
                const direccion = this.getAttribute('data-bs-address');
                const referencia = this.getAttribute('data-bs-reference');
                const observacion = this.getAttribute('data-bs-observation');
                const ruta_foto = this.getAttribute('data-bs-foto');

                // Prellenar campos con datos básicos
                document.getElementById('modal-cliente').value = cliente || '';
                document.getElementById('modal-telefono').value = telefono || '';


                document.getElementById('modal-fecha_entrega').value = fechaEntrega || '';
                document.getElementById('modal-hora_entrega').value = horaEntrega || '';
                document.getElementById('modal-direccion').value = direccion || '';
                document.getElementById('modal-referencia').value = referencia || '';
                document.getElementById('modal-observacion').value = observacion || '';
                document.getElementById('modal-detalle-sale_id').value = sale_id;

                // Manejar enlace de foto
                const link = document.getElementById('ver-foto-link');
                if (ruta_foto && ruta_foto !== '') {
                    link.href = '{{ url("storage/app/public") }}/' + ruta_foto;
                    link.removeAttribute('disabled');
                    link.classList.remove('ver-foto-disabled');
                    link.style.textDecoration = 'underline';
                    link.style.pointerEvents = 'auto';
                } else {
                    link.href = '';
                    link.setAttribute('disabled', true);
                    link.classList.add('ver-foto-disabled');
                    link.style.textDecoration = 'none';
                    link.style.pointerEvents = 'none';
                }

                // Hacer request para obtener datos completos
                $.ajax({
                    url: "{{ route('sales.details') }}?sale_id=" + sale_id,
                    method: 'GET',
                    success: function(response) {
                        let total = 0;

                        // Actualizar campos con datos del servidor si están disponibles
                        if (response.venta) {
                            document.getElementById('modal-cliente').value = response.venta.cliente || '';
                            document.getElementById('modal-telefono').value = response.venta.telefono || '';
                            document.getElementById('modal-fecha_entrega').value = response.venta.fecha_entrega || '';
                            document.getElementById('modal-hora_entrega').value = response.venta.hora_entrega || '';
                            document.getElementById('modal-direccion').value = response.venta.direccion || '';
                            document.getElementById('modal-referencia').value = response.venta.referencia || '';
                            document.getElementById('modal-observacion').value = response.venta.observacion || '';
                        }

                        // Cargar productos
                        if (response.productos.length === 0) {
                            const fila = `
                                <tr>
                                    <th colspan="5" class="text-center">No hay productos</th>
                                </tr>
                            `;
                            tabla.innerHTML = fila;
                        } else {
                            response.productos.forEach(producto => {
                                const fila = `
                                    <tr data-product-id="${producto.id || 'temp-' + Date.now()}">
                                        <td>${producto.nombre}</td>
                                        <td>
                                            <input type="number" class="form-control form-control-sm producto-precio" 
                                                   value="${producto.precio.toFixed(2)}" step="0.01" min="0" 
                                                   data-product-id="${producto.id || 'temp-' + Date.now()}">
                                        </td>
                                        <td>
                                            <input type="number" class="form-control form-control-sm producto-cantidad" 
                                                   value="${producto.cantidad}" min="1" 
                                                   data-product-id="${producto.id || 'temp-' + Date.now()}">
                                        </td>
                                        <td class="producto-subtotal">S/${producto.subtotal.toFixed(2)}</td>
                                        <td>
                                            <button type="button" class="btn btn-danger btn-sm btn-eliminar-producto" 
                                                    data-product-id="${producto.id || 'temp-' + Date.now()}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                `;
                                tabla.innerHTML += fila;
                                total += producto.subtotal;
                            });
                        }

                        // Actualizar total
                        document.getElementById('modal-total').textContent = `S/${total.toFixed(2)}`;

                        // Ocultar spinner y mostrar modal
                        spinner.classList.remove('spinner-visible');
                        spinner.classList.add('spinner-hidden');

                        // Sacar elementos de fecha de venta y hoy
                        // const fechaVentaStr = response.venta.fecha;
                        // const [fecha, hora] = fechaVentaStr.split(' ');
                        // const [dia, mes, anio] = fecha.split('/');
                        // const [h, m, s] = hora.split(':');
                        // const fechaVenta = new Date(anio, mes - 1, dia, h, m, s);
                        // const ahora = new Date();
                        // const diffMs = Math.abs(ahora - fechaVenta);
                        // const diffMin = diffMs / 60000;

                        // const btnTexto = document.getElementById('btn-guardar-text');



                        // if (diffMin > 30) { //30min
                        //     // Desactivar botón y cambiar cursor
                        //     btnTexto.closest('button').disabled = true;
                        //     btnTexto.closest('button').style.cursor = 'not-allowed';
                        //     btnTexto.closest('button').title = 'Las ediciones solo se pueden realizar hasta 30 minutos después de guardada la venta';
                        // }else{
                        //     btnTexto.closest('button').disabled = false;
                        //     btnTexto.closest('button').style.cursor = 'auto';
                        //     btnTexto.closest('button').title = '';
                        // }
                        
                        

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

        // Variables globales para manejo de productos
        let productosEditados = [];
        let totalVenta = 0;

        // Función para calcular total
        function calcularTotal() {
            totalVenta = 0;
            const subtotales = document.querySelectorAll('.producto-subtotal');
            subtotales.forEach(subtotal => {
                const valor = parseFloat(subtotal.textContent.replace('S/', '')) || 0;
                totalVenta += valor;
            });
            document.getElementById('modal-total').textContent = `S/${totalVenta.toFixed(2)}`;
        }

        // Event listener para cambios en precio
        $(document).on('input', '.producto-precio', function() {
            const precio = parseFloat($(this).val()) || 0;
            const fila = $(this).closest('tr');
            const cantidad = parseFloat(fila.find('.producto-cantidad').val()) || 0;
            const subtotal = precio * cantidad;

            fila.find('.producto-subtotal').text(`S/${subtotal.toFixed(2)}`);
            calcularTotal();
        });

        // Event listener para cambios en cantidad
        $(document).on('input', '.producto-cantidad', function() {
            const cantidad = parseFloat($(this).val()) || 0;
            const fila = $(this).closest('tr');
            const precio = parseFloat(fila.find('.producto-precio').val()) || 0;
            const subtotal = precio * cantidad;

            fila.find('.producto-subtotal').text(`S/${subtotal.toFixed(2)}`);
            calcularTotal();
        });

        // Event listener para eliminar producto
        $(document).on('click', '.btn-eliminar-producto', function() {
            $(this).closest('tr').remove();
            calcularTotal();

            // Si no hay productos, mostrar mensaje
            const tabla = document.getElementById('modal-productos');
            if (tabla.children.length === 0) {
                tabla.innerHTML = '<tr><th colspan="5" class="text-center">No hay productos</th></tr>';
            }
        });

        // Event listener para abrir modal de agregar producto
        document.getElementById('btn-agregar-producto').addEventListener('click', function() {
            // document.getElementById('producto-select').value = '';
            document.getElementById('producto-precio').value = '';
            document.getElementById('producto-cantidad').value = '1';
            document.getElementById('producto-subtotal').value = '';

            const modal = new bootstrap.Modal(document.getElementById('ModalAgregarProducto'));
            modal.show();
        });

        // Event listeners para recalcular subtotal en modal agregar
        document.getElementById('producto-precio').addEventListener('input', function() {
            const precio = parseFloat(this.value) || 0;
            const cantidad = parseFloat(document.getElementById('producto-cantidad').value) || 1;
            const subtotal = precio * cantidad;
            document.getElementById('producto-subtotal').value = subtotal.toFixed(2);
        });

        document.getElementById('producto-cantidad').addEventListener('input', function() {
            const cantidad = parseFloat(this.value) || 1;
            const precio = parseFloat(document.getElementById('producto-precio').value) || 0;
            const subtotal = precio * cantidad;
            document.getElementById('producto-subtotal').value = subtotal.toFixed(2);
        });

        // Event listener para confirmar agregar producto
        document.getElementById('btn-confirmar-producto').addEventListener('click', function() {
            // const productoSelect = document.getElementById('producto-select');
            //const productoId = document.getElementById('producto_id');
            const productId = document.getElementById('producto_id').value;
            const productName = document.getElementById('search-product').value;
            const precio = parseFloat(document.getElementById('producto-precio').value) || 0;
            const cantidad = parseFloat(document.getElementById('producto-cantidad').value) || 1;
            const subtotal = precio * cantidad;

            if (!productId) {
                ToastError.fire({
                    text: 'Por favor seleccione un producto'
                });
                return;
            }

            if (precio <= 0) {
                ToastError.fire({
                    text: 'El precio debe ser mayor a 0'
                });
                return;
            }

            if (cantidad <= 0) {
                ToastError.fire({
                    text: 'La cantidad debe ser mayor a 0'
                });
                return;
            }

            // Verificar si el producto ya existe
            const existingProduct = document.querySelector(`tr[data-product-id="${productId}"]`);
            if (existingProduct) {
                ToastError.fire({
                    text: 'El producto ya está en la lista'
                });
                return;
            }

            // Agregar producto a la tabla
            const tabla = document.getElementById('modal-productos');

            // Remover mensaje de "No hay productos" si existe
            const noProductsRow = tabla.querySelector('tr th[colspan="5"]');
            if (noProductsRow) {
                tabla.innerHTML = '';
            }

            const fila = `
                <tr data-product-id="${productId}">
                    <td>${productName}</td>
                    <td>
                        <input type="number" class="form-control form-control-sm producto-precio" 
                               value="${precio.toFixed(2)}" step="0.01" min="0" 
                               data-product-id="${productId}">
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm producto-cantidad" 
                               value="${cantidad}" min="1" 
                               data-product-id="${productId}">
                    </td>
                    <td class="producto-subtotal">S/${subtotal.toFixed(2)}</td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm btn-eliminar-producto" 
                                data-product-id="${productId}">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `;

            tabla.innerHTML += fila;
            calcularTotal();

            // Cerrar modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('ModalAgregarProducto'));
            modal.hide();

            ToastMessage.fire({
                text: 'Producto agregado correctamente'
            });

            document.getElementById('producto_id').value = '';
            document.getElementById('search-product').value = '';
        });

        // Manejador para guardar cambios en el modal de detalles
        document.getElementById('btn-guardar-detalles').addEventListener('click', function() {
            const sale_id = document.getElementById('modal-detalle-sale_id').value;
            const btnGuardar = document.getElementById('btn-guardar-detalles');
            const btnTexto = document.getElementById('btn-guardar-text');
            const btnSpinner = document.getElementById('btn-guardar-spinner');

            if (!sale_id) {
                ToastError.fire({
                    text: 'No se encontró el ID de la venta'
                });
                return;
            }

            // Deshabilitar botón y mostrar spinner
            btnGuardar.disabled = true;
            btnTexto.classList.add('d-none');
            btnSpinner.classList.remove('d-none');

            // Recalcular total antes de enviar
            calcularTotal();

            // Recopilar datos del formulario
            const datos = {
                sale_id: sale_id,
                telefono: document.getElementById('modal-telefono').value,
                fecha_entrega: document.getElementById('modal-fecha_entrega').value,
                hora_entrega: document.getElementById('modal-hora_entrega').value,
                direccion: document.getElementById('modal-direccion').value,
                referencia: document.getElementById('modal-referencia').value,
                observacion: document.getElementById('modal-observacion').value,
                productos: []
            };

            // Debug: Log de los datos que se van a enviar
            console.log('Datos a enviar:', datos);

            // Recopilar productos editados
            const filasProductos = document.querySelectorAll('#modal-productos tr[data-product-id]');
            let totalCalculado = 0;
            filasProductos.forEach(fila => {
                const productId = fila.getAttribute('data-product-id');
                const nombre = fila.querySelector('td:first-child').textContent;
                const precio = parseFloat(fila.querySelector('.producto-precio').value) || 0;
                const cantidad = parseFloat(fila.querySelector('.producto-cantidad').value) || 0;
                const subtotal = precio * cantidad;
                totalCalculado += subtotal;

                datos.productos.push({
                    id: productId,
                    nombre: nombre,
                    precio: precio,
                    cantidad: cantidad,
                    subtotal: subtotal
                });
            });

            datos.total = totalCalculado;

            // Debug: Log del total calculado
            console.log('Total calculado:', totalCalculado);
            console.log('Total variable global:', totalVenta);

            // Mostrar spinner principal
            spinner.classList.remove('spinner-hidden');
            spinner.classList.add('spinner-visible');

            // Realizar petición AJAX para actualizar
            $.ajax({
                url: "{{ route('sales.updateDetails') }}",
                method: 'POST',
                data: datos,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    // Ocultar spinners
                    spinner.classList.remove('spinner-visible');
                    spinner.classList.add('spinner-hidden');

                    // Restaurar botón
                    btnGuardar.disabled = false;
                    btnTexto.classList.remove('d-none');
                    btnSpinner.classList.add('d-none');

                    if (response.success) {
                        ToastMessage.fire({
                            text: 'Detalles actualizados correctamente'
                        });

                        // Cerrar modal
                        const modal = bootstrap.Modal.getInstance(document.getElementById('ModalDetalle'));
                        modal.hide();

                        // Actualizar la tabla principal si es necesario
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    } else {
                        ToastError.fire({
                            text: response.message || 'Error al actualizar los detalles'
                        });
                    }
                },
                error: function(xhr) {
                    // Ocultar spinners
                    spinner.classList.remove('spinner-visible');
                    spinner.classList.add('spinner-hidden');

                    // Restaurar botón
                    btnGuardar.disabled = false;
                    btnTexto.classList.remove('d-none');
                    btnSpinner.classList.add('d-none');

                    let errorMessage = 'Error al actualizar los detalles';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = Object.values(xhr.responseJSON.errors).flat();
                        errorMessage = errors.join(', ');
                    }

                    ToastError.fire({
                        text: errorMessage
                    });
                }
            });
        });

        //mostrar pagos
        const buttons_pago = document.querySelectorAll('.open-payments-modal');
        buttons_pago.forEach(button => {
            button.addEventListener('click', function() {
                tipoDocumentoCliente = this.getAttribute('data-bs-doc-type') || '';
                numeroDocumentoCliente = this.getAttribute('data-bs-doc-number') || '';

                const sale_id = this.getAttribute('data-bs-venta_id');
                const saldoTexto = document.getElementById(`saldo-venta-${sale_id}`)?.textContent || '0.00';
                const saldo = parseFloat(saldoTexto.replace('S/', '').trim()) || 0;

                const tabla = document.getElementById('modal-pagos');

                document.getElementById('modal-sale_id').value = sale_id;

                const containerMetodos = document.getElementById('metodos-pago-container');
                const containerComprobante = document.getElementById('comprobante-container');

                if (containerMetodos) {
                    if (saldo > 0) {
                        containerMetodos.classList.remove('d-none');
                        containerComprobante.classList.add('d-none');
                        document.getElementById('guardar-venta').style.display = 'none';
                    } else {
                        containerMetodos.classList.add('d-none');
                        containerComprobante.classList.remove('d-none');
                        document.getElementById('guardar-venta').style.display = 'inline-block';
                    }
                }

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

        document.getElementById('form-foto').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            // Ocultar el spinner de carga
            spinner.classList.add('spinner-visible');
            spinner.classList.remove('spinner-hidden');

            $.ajax({
                url: "{{ route('sales.subirFoto') }}",
                data: formData,
                processData: false,
                contentType: false,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(response) {
                    spinner.classList.remove('spinner-visible');
                    spinner.classList.add('spinner-hidden');
                    document.getElementById('ver-foto-link').href = '{{ url("storage/app/public") }}/' + response.path;
                    document.getElementById('ver-foto-link').removeAttribute('disabled');
                    document.getElementById('ver-foto-link').style.textDecoration = 'underline'
                    document.getElementById('ver-foto-link').style.pointerEvents = 'auto';
                    document.getElementById('ver-foto-link').classList.remove('ver-foto-disabled');
                    ToastMessage.fire({
                        text: 'Foto guardada'
                    });

                    const venta_id = document.getElementById('modal-detalle-sale_id').value;
                    // Busca el botón con ese data-bs-venta_id y actualiza su data-bs-foto
                    const btn = document.querySelector(`.open-details-modal[data-bs-venta_id="${venta_id}"]`);
                    if (btn) {
                        btn.setAttribute('data-bs-foto', response.path);
                    }

                    // Actualizar también la imagen en la tabla principal
                    const filaVenta = document.getElementById(`fila-venta-${venta_id}`);
                    if (filaVenta) {
                        const fotoColumn = filaVenta.querySelector('.foto-column');
                        if (fotoColumn) {
                            // Crear nueva imagen
                            const nuevaImagenUrl = '{{ url("storage/app/public") }}/' + response.path;
                            fotoColumn.innerHTML = `
                                <img src="${nuevaImagenUrl}"
                                    alt="Foto de pedido"
                                    class="img-thumbnail foto-pedido"
                                    style="width: 60px; height: 60px; object-fit: cover; cursor: pointer;"
                                    onclick="window.open(this.src, '_blank')"
                                    onerror="tryAlternativeImagePath(this, '${response.path}');">
                                <span class="text-muted imagen-error" style="display: none;">Imagen no disponible</span>
                            `;
                        }
                    }

                    document.getElementById('foto-input').value = '';


                },
                error: function(xhr) {
                    // Ocultar el spinner de carga
                    spinner.classList.remove('spinner-visible');
                    spinner.classList.add('spinner-hidden');

                    ToastError.fire({
                        text: 'Ocurrió un error al guardar la foto'
                    });
                }
            });
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('fromFilter');
        const buttonFiltrar = document.getElementById('btnFiltrar');
        const spinner = document.getElementById('global-spinner');

        let clickedFiltrar = false;

        spinner.classList.remove('spinner-visible');
        spinner.classList.add('spinner-hidden');

        buttonFiltrar.addEventListener('click', function() {
            clickedFiltrar = true;
        });

        form.addEventListener('submit', function() {
            if (clickedFiltrar) {
                spinner.classList.remove('spinner-hidden');
                spinner.classList.add('spinner-visible');
            }

            clickedFiltrar = false;
        });
    });

    function cargarPagosVenta(sale_id) {
        const tabla = document.getElementById('modal-pagos');
        tabla.innerHTML = '<tr><td colspan="3">Cargando pagos...</td></tr>';

        fetch(`{{ route('payment.listar') }}?sale_id=${sale_id}`)
            .then(res => res.json())
            .then(data => {
                if (data.payments.length === 0) {
                    tabla.innerHTML = `<tr><td colspan="3">No hay pagos</td></tr>`;
                } else {
                    tabla.innerHTML = '';
                    data.payments.forEach(payment => {
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
            })
            .catch(() => {
                tabla.innerHTML = `<tr><td colspan="3">Error al cargar pagos</td></tr>`;
            });
    }

    document.querySelectorAll('.btn-entrega').forEach(button => {
        button.addEventListener('click', function() {
            const ventaId = this.getAttribute('data-bs-venta_id');
            document.getElementById('venta-id-entregar').value = ventaId;

            const modal = new bootstrap.Modal(document.getElementById('modalConfirmarEntrega'));
            modal.show();
        });
    });

    document.getElementById('confirmar-entrega').addEventListener('click', function() {
        const ventaId = document.getElementById('venta-id-entregar').value;
        const spinner = document.getElementById('global-spinner');
        spinner.classList.remove('spinner-hidden');
        spinner.classList.add('spinner-visible');

        fetch(`{{ url('/sales/entregar/') }}/${ventaId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(res => res.json())
            .then(data => {
                spinner.classList.add('spinner-hidden');
                spinner.classList.remove('spinner-visible');

                if (data.status) {
                    ToastMessage.fire({
                        text: 'Entrega registrada con éxito.'
                    });

                    const botonEntrega = document.getElementById(`entrega-venta-${ventaId}`);
                    if (botonEntrega) {
                        botonEntrega.classList.add('d-none');
                    }

                    const modal = bootstrap.Modal.getInstance(document.getElementById('modalConfirmarEntrega'));
                    modal.hide();
                } else {
                    ToastError.fire({
                        text: 'Error al actualizar la entrega.'
                    });
                }
            })
            .catch(() => {
                spinner.classList.add('spinner-hidden');
                ToastError.fire({
                    text: 'Error de red al confirmar entrega.'
                });
            });
    });

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

    $(document).on('submit', '.form-delete-sale', function(e) {
        e.preventDefault();
        const form = $(this);
        const saleId = form.find('button[type="submit"]').data('id') || form.closest('tr').attr('id').replace('fila-venta-', '');

        ToastConfirm.fire({
            text: '¿Desea eliminar esta venta anticipada?',
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: form.serialize(),
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        // Elimina la fila visualmente si todo OK
                        $('#fila-venta-' + saleId).remove();
                        ToastMessage.fire({
                            icon: 'success',
                            text: response.message || 'Venta eliminada correctamente.'
                        });
                    },
                    error: function(xhr) {
                        ToastError.fire({
                            text: 'No se pudo eliminar la venta.'
                        });
                    }
                });
            }
        });
    });

    const productsSelect = @json($products);
    $('#search-product').autocomplete({
        source: function(request, response) {

            $('#producto_id').val('');
            const term = request.term.toLowerCase();

            const results = productsSelect
                .filter(p => p.name.toLowerCase().includes(term))
                .map(p => {
                    const stock = p.quantity || 0;
                    return {
                        label: `${p.name} (${stock})`,
                        value: p.name,
                        id: p.id,
                        stock: stock
                    };
                });

            response(results);
        },
        appendTo: '#ModalAgregarProducto',
        select: function(event, ui) {
            $('#producto_id').val(ui.item.id);
            // handleProductClickSelect(ui.item.id, ui.item.value);
            $(this).val(ui.item.value);
            return false;
        }
    }).autocomplete("instance")._renderItem = function(ul, item) {
        return $("<li>")
            .append(`<div class="d-flex justify-content-between"><span>${item.label}</span></div>`)
            .appendTo(ul);
    };
</script>
@endsection