@extends('layouts.app')

@section('nav')
<style>
    .card-mesa.borde-naranja {
        border: 6px solid #ffa500 !important;
    }

    .card-mesa.borde-rojo {
        border: 6px solid red !important;
    }

    .card-mesa.borde-verde {
        border: 6px solid green !important;
    }

    /* Estilos para campos de delivery */
    #camposDelivery {
        background-color: #f8f9fa;
        padding: 15px;
        margin-top: 10px;
        border-radius: 5px;
        transition: all 0.3s ease;
    }

    #camposDelivery .form-label {
        font-weight: 600;
    }
</style>
@endsection

@section('header')
<h2>Punto de Venta Cafetería</h2>
<p>Lista de mesas</p>
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
            <button class="btn btn-success btn-lg mb-3" onclick="
            @if($mesa_directa->status === 'Libre')
                abrirMesa('{{ $mesa_directa->id }}', event)
            @else
                verPedido('{{ $mesa_directa->id }}', event)
            @endif
            "> <i class="bi bi-plus"></i> Venta directa</button>
            <div class="row g-4">
                @foreach($mesas as $mesa)
                <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                    <div class="card shadow border-0 rounded-4 text-center h-100 card-mesa" id="mesa-card-{{ $mesa->id }}" data-mesa-id="{{ $mesa->id }}" data-opened-at="{{ $mesa->opened_at }}">
                        <div class="card-body d-flex flex-column justify-content-between ">
                            <h5 class="card-title mb-3 fw-bold">{{ $mesa->name }}</h5>

                            <span id="estado-mesa-{{ $mesa->id }}" class="badge mb-2 {{ $mesa->status == 'Libre' ? 'bg-success' : 'bg-danger' }} fs-5">
                                {{ ucfirst($mesa->status) }}
                            </span>

                            <div id="acciones-mesa-{{ $mesa->id }}">
                                @if($mesa->status === 'Libre')
                                <button class="btn btn-primary rounded-pill" onclick="abrirMesa('{{ $mesa->id }}', event)">
                                    Abrir Mesa
                                </button>
                                @else
                                <div class="d-grid gap-2">
                                    <button class="btn btn-warning rounded-pill" onclick="verPedido('{{ $mesa->id }}', event)">
                                        Ver Pedido
                                    </button>
                                    <button class="btn btn-danger rounded-pill" onclick="cerrarMesa('{{ $mesa->id }}', event)">
                                        Cancelar Venta <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                                @if($mesa->opened_at)
                                <div class="mt-2 text-muted small">
                                    Tiempo: <span id="contador-{{ $mesa->id }}">--:--</span>
                                </div>
                                @endif
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Modal para Abrir Mesa -->
<div class="modal fade" id="abrirMesaModal" tabindex="-1" aria-labelledby="abrirMesaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="abrirMesaModalLabel">Abrir Mesa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <!-- Seleccionar Productos -->
                    <div class="form-group">
                        <label for="producto_id" class="col-sm-3 col-form-label text-start"><strong>Producto</strong></label>
                        <div class="col-md-12">
                            <input hidden type="number" class="form-control" name="producto_id" id="producto_id">
                            <input type="text" class="form-control" name="name" id="search-product" placeholder="Buscar Producto">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 col-form-label text-start"><strong>Categorías</strong></label>
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
                            <tbody></tbody>
                        </table>
                    </div>
                    <div class="row justify-content-end mb-3">
                        <div class="col-md-5 text-end">
                            <h5><strong>TOTAL: S/ <span id="totalAmount" name="total">0.00</span></strong></h5>
                            <input hidden type="number" step="0.01" name="total" id="totalAmountInput" value="0">
                            <button class="btn me-2 mt-3 btn-warning" type="button" onclick="confirmOrder()">Confirmar</button>
                            <button class="btn me-2 mt-3 btn-secondary" type="button" onclick="preaccount()">Precuenta</button>
                            <button class="btn me-2 mt-3 btn-success" type="button" onclick="abrirModalCobro()">Cobrar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Cobro -->
<div class="modal fade" id="modalCobro" tabindex="-1" aria-labelledby="modalCobroLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Registrar Cobro</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body px-4 py-3">
                <form id="formCobro">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <!-- Selección de Comprobante -->
                            <div class="mb-3">
                                <label class="mb-2"><strong>Tipo de Comprobante</strong></label>
                                <div class="btn-group d-flex justify-content-start mb-4">
                                    <button type="button" class="btn btn-outline-primary me-1" id="btn-boleta"
                                        onclick="selectVoucherType('boleta', this)">Boleta</button>
                                    <button type="button" class="btn btn-outline-success me-1" id="btn-factura"
                                        onclick="selectVoucherType('factura', this)">Factura</button>
                                    <button type="button" class="btn btn-outline-info me-1" id="btn-ticket"
                                        onclick="selectVoucherType('ticket', this)">Ticket</button>
                                </div>
                                <input type="hidden" name="voucher_type" id="voucher_type" value="">
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
                            <!-- Documento y Cliente -->
                            <div class="mb-3">
                                <label class="col-sm-4 col-form-label text-start"><strong>Documento</strong></label>
                                <div class="input-group">
                                    <input type="text" class="form-control form-control-xs" id="document"
                                        name="document" maxlength="11" onkeypress="isNumber(event)">
                                    <button type="button" class="btn btn-primary btn-xs"
                                        onclick="searchAPI('#document','#name','#address')"><i
                                            class="bi bi-search"></i></button>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><strong>Cliente</strong></label>
                                <input type="text" class="form-control" id="name" name="client">
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><strong>Observación</strong></label>
                                <textarea class="form-control" id="observacion" name="observacion" rows="2" placeholder="Observaciones adicionales (opcional)"></textarea>
                            </div>
                            <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">
                            <input hidden type="number" name="type_sale" id="type_sale" value="1">
                            <input hidden type="number" name="status" id="status" value="1">
                            <input hidden type="number" name="type_status" id="type_status" value="0">
                        </div>

                        <div class="col-md-6">
                            <!-- Métodos de pago -->
                            <div class="mb-3">
                                <label class="mb-2"><strong>Método de Pago</strong></label>
                                <div class="d-flex flex-wrap">
                                    @foreach ($pms as $index => $method)
                                    @php
                                    $colorClass = $colors[$index % count($colors)];
                                    @endphp
                                    <button
                                        type="button"
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

                            <!-- Campos por método de pago -->
                            @foreach ($pms as $method)
                            <div class="mb-3 d-none align-items-center gap-3" id="campos-{{ $method->name }}">
                                <label class="form-label mb-0">
                                    <strong>{{ strtoupper(Str::limit($method->name, 4, '.')) }}</strong>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">S/</span>
                                    <input type="text" class="form-control" placeholder="Ingrese Monto"
                                        name="monto[{{ $method->id }}]" onkeypress="isDecimal(event)" oninput="calcularSaldo()">
                                </div>
                            </div>
                            @endforeach

                            <!-- Checkbox para Delivery -->
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="checkDelivery" onchange="toggleDelivery()">
                                    <label class="form-check-label" for="checkDelivery">
                                        <strong>Delivery</strong>
                                    </label>
                                </div>
                            </div>

                            <!-- Campos de Delivery (inicialmente ocultos) -->
                            <div id="camposDelivery" class="d-none">
                                <div class="mb-3">
                                    <label class="form-label"><strong>Fecha de Entrega</strong></label>
                                    <input type="date" class="form-control" name="delivery_date" id="delivery_date">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label"><strong>Hora de Entrega</strong></label>
                                    <input type="text" class="form-control" name="delivery_hour" id="delivery_hour" placeholder="Ej: 14:30 o 2:30 PM">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label"><strong>Dirección de Entrega</strong></label>
                                    <textarea class="form-control" name="delivery_address" id="delivery_address" rows="2" placeholder="Ingrese la dirección completa de entrega"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer mt-4">
                        <!-- Mostrar el total y saldo -->
                        <div class="mb-2 text-end w-100">
                            <h5><strong>TOTAL: S/ <span id="totalAmountModal">0.00</span></strong></h5>
                            <h6><strong>SALDO: S/ <span id="saldoAmount" class="text-danger">0.00</span></strong></h6>
                        </div>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Finalizar Venta</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        @foreach($mesas as $mesa)
        @if($mesa->status === 'Ocupado' && $mesa->opened_at)
        try {
            iniciarContadorMesa("{{ $mesa->id }}", "{{ $mesa->opened_at }}");
        } catch (error) {
            console.error('Error al iniciar contador para mesa {{ $mesa->id }}:', error);
        }
        @endif
        @endforeach
    });
</script>
<script>
    var serial = "{{ config('printer.serial') }}";

    var state = {
        table_id: null
    };

    function abrirModalCobro() {
        // Sincronizar total en el modal
        const total = $('#totalAmount').text();
        $('#totalAmountModal').text(total);

        // Inicializar el observador del total
        inicializarObservadorTotal();

        // Calcular saldo inicial
        setTimeout(() => {
            calcularSaldo();
        }, 100);

        const modal = new bootstrap.Modal(document.getElementById('modalCobro'));
        modal.show();
    }

    // Restaura la UI de la mesa cuando se libera (para evitar duplicación)
    function restoreMesaUI(mesaId) {
        const estadoSpan = document.getElementById(`estado-mesa-${mesaId}`);
        if (estadoSpan) {
            estadoSpan.textContent = 'Libre';
            estadoSpan.classList.remove('bg-danger');
            estadoSpan.classList.add('bg-success');
        }

        const accionesDiv = document.getElementById(`acciones-mesa-${mesaId}`);
        if (accionesDiv) {
            accionesDiv.innerHTML = `
                <button class="btn btn-primary rounded-pill" onclick="abrirMesa('${mesaId}', event)">
                    Abrir Mesa
                </button>
            `;
        }

        const contador = document.getElementById(`contador-${mesaId}`);
        if (contador) contador.remove();

        // Limpiar timer específico de la mesa
        if (mesaTimers[mesaId]) {
            clearInterval(mesaTimers[mesaId]);
            delete mesaTimers[mesaId];
        }

        const card = document.getElementById(`mesa-card-${mesaId}`);
        if (card) {
            // Remover todas las clases de borde específicamente
            card.classList.remove('borde-verde', 'borde-naranja', 'borde-rojo');

            // Remover cualquier estilo inline de border que pueda existir
            card.style.removeProperty('border');
            card.style.removeProperty('border-color');
            card.style.removeProperty('border-width');
            card.style.removeProperty('border-style');

            // Restaurar completamente las clases originales
            card.className = 'card shadow border-0 rounded-4 text-center h-100 card-mesa';

            // Verificación adicional después de un pequeño delay
            setTimeout(() => {
                if (card.classList.contains('borde-verde') ||
                    card.classList.contains('borde-naranja') ||
                    card.classList.contains('borde-rojo')) {
                    console.warn('Borde persistente detectado en mesa', mesaId, '- forzando limpieza');
                    card.classList.remove('borde-verde', 'borde-naranja', 'borde-rojo');
                    card.style.border = 'none !important';
                }
            }, 100);

            console.log('Mesa', mesaId, 'restaurada correctamente - clases:', card.className);
        }
    }

    let openedMesaId = null;
    let timerInterval;
    let selectedProducts = [];
    const mesaTimers = {}; // Asegúrate de declarar esto en el scope global

    function seleccionarComprobante(comprobante, event) {
        const parent = event.target.closest('.btn-group');
        Array.from(parent.children).forEach(child => {
            child.classList.remove('active');
        });

        event.target.classList.add('active');
        document.getElementById('voucher_type').value = comprobante;
    }

    const totalAmountSpan = document.getElementById('totalAmount');

    // Asegurar que el observador se configure después de que el modal esté abierto
    function inicializarObservadorTotal() {
        const totalAmountElement = document.getElementById('totalAmount');
        if (totalAmountElement && !totalAmountElement.hasObserver) {
            const observer = new MutationObserver(() => {
                // Sincronizar total en el modal cuando cambie
                const total = $('#totalAmount').text();
                $('#totalAmountModal').text(total);
                calcularSaldo();
            });

            observer.observe(totalAmountElement, {
                childList: true, // Cambios en los nodos hijos (texto)
                characterData: true, // Cambios en texto directo
                subtree: true // Observar todo el subtree
            });

            totalAmountElement.hasObserver = true;
            console.log('Observador del total inicializado');
        }
    }

    function seleccionarMedioPago(medio_id, event) {
        const btn = event.target;
        const camposId = btn.dataset.campos;
        const camposElement = document.getElementById(camposId);
        const totalActual = parseFloat($('#totalAmount').text()) || 0;

        btn.classList.toggle('active');
        btn.classList.toggle('btn-success');

        if (btn.classList.contains('active')) {
            camposElement.classList.remove('d-none');
            camposElement.classList.add('d-flex', 'align-items-center');
        } else {
            camposElement.classList.add('d-none');
            camposElement.classList.remove('d-flex', 'align-items-center');
            const input = camposElement.querySelector('input[name^="monto["]');
            if (input) input.value = '';
        }

        const activos = document.querySelectorAll('[id^="btn-"].active');

        if (activos.length === 1) {
            // Solo un método activo, asignar total
            const id = activos[0].dataset.id;
            const campoUnico = document.querySelector(`#campos-${activos[0].dataset.campos.split('-')[1]}`);
            const inputUnico = campoUnico?.querySelector(`input[name="monto[${id}]"]`);
            if (inputUnico) inputUnico.value = totalActual.toFixed(2);

        } else {
            // Más de uno activo, limpiar todos los inputs
            document.querySelectorAll('[id^="campos-"] input[name^="monto["]').forEach(input => {
                input.value = '0.00';
            });
        }

        calcularSaldo();
    }

    function selectVoucherType(type, button) {
        // Remover clases activas de todos los botones
        document.querySelectorAll('#btn-boleta, #btn-factura, #btn-ticket').forEach(btn => {
            // Resetear a clases outline
            btn.classList.remove('btn-primary', 'btn-success', 'btn-info');
            if (btn.id === 'btn-boleta') {
                btn.classList.add('btn-outline-primary');
            } else if (btn.id === 'btn-factura') {
                btn.classList.add('btn-outline-success');
            } else if (btn.id === 'btn-ticket') {
                btn.classList.add('btn-outline-info');
            }
        });

        // Activar el botón seleccionado
        button.classList.remove('btn-outline-primary', 'btn-outline-success', 'btn-outline-info');
        if (type === 'boleta') {
            button.classList.add('btn-primary');
        } else if (type === 'factura') {
            button.classList.add('btn-success');
        } else if (type === 'ticket') {
            button.classList.add('btn-info');
        }

        // Establecer el valor en el campo oculto con la primera letra en mayúscula (como espera el backend)
        const voucherValue = type.charAt(0).toUpperCase() + type.slice(1);
        document.getElementById('voucher_type').value = voucherValue;

        console.log('Tipo de comprobante seleccionado:', voucherValue);
    }

    function isDecimal(evt) {
        evt = evt || window.event;
        var charCode = evt.which || evt.keyCode;

        // Solo permite números y un solo punto decimal
        if ((charCode >= 48 && charCode <= 57) || charCode === 46) {
            const input = evt.target || evt.srcElement;
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

    function iniciarContadorMesa(id, openedAtStr) {
        const openedAt = new Date(openedAtStr);
        const span = document.getElementById(`contador-${id}`);
        const card = span ? span.closest('.card-mesa') : null;
        if (!span) return;

        // Limpiar timer anterior si existe
        if (mesaTimers[id]) {
            clearInterval(mesaTimers[id]);
            delete mesaTimers[id];
        }

        // Guardar referencia del timer
        mesaTimers[id] = setInterval(() => {
            const now = new Date();
            const diff = Math.floor((now - openedAt) / 1000);
            const min = String(Math.floor(diff / 60)).padStart(2, '0');
            const sec = String(diff % 60).padStart(2, '0');
            span.textContent = `${min}:${sec}`;
            // Si pasan más de 20 minutos, pinta de naranja
            if (card) {
                if (diff >= 3600) {
                    card.classList.add('borde-rojo');
                    card.classList.remove('borde-naranja');
                    card.classList.remove('borde-verde');
                } else if (diff >= 1200) {
                    card.classList.remove('borde-rojo');
                    card.classList.add('borde-naranja');
                    card.classList.remove('borde-verde');
                } else {
                    card.classList.remove('borde-rojo');
                    card.classList.remove('borde-naranja');
                    card.classList.add('borde-verde');
                }
            }
        }, 1000);
    }

    // ADAPTACIÓN COMPLETA DEL ENVÍO DE COBRO PARA TU MODAL

    function calcularSaldo() {
        const total = parseFloat($('#totalAmount').text()) || 0;
        let totalPagado = 0;

        // Sumar todos los montos de pago visibles
        document.querySelectorAll('input[name^="monto["]').forEach(input => {
            const container = input.closest('.d-flex, .mb-3, .mb-4');
            if (container && !container.classList.contains('d-none') && container.style.display !== 'none') {
                totalPagado += parseFloat(input.value) || 0;
            }
        });

        const saldo = total - totalPagado;
        const saldoElement = $('#saldoAmount');

        if (saldoElement.length) {
            if (total === 0) {
                saldoElement.text('0.00');
                saldoElement.removeClass('text-danger text-success');
            } else {
                saldoElement.text(Math.abs(saldo).toFixed(2));

                // Cambiar color según el saldo
                if (saldo > 0.01) {
                    saldoElement.removeClass('text-success').addClass('text-danger'); // Debe dinero
                } else if (saldo < -0.01) {
                    saldoElement.removeClass('text-danger').addClass('text-warning'); // Sobra dinero (vuelto)
                } else {
                    saldoElement.removeClass('text-danger').addClass('text-success'); // Exacto
                }
            }
        }

        console.log('Cálculo saldo - Total:', total, 'Pagado:', totalPagado, 'Saldo:', saldo);
        return parseFloat(saldo) || 0;
    }

    document.getElementById('formCobro').addEventListener('submit', function(e) {
        e.preventDefault();

        const botonesMedioPago = document.querySelectorAll('.d-flex.flex-wrap button');
        const metodoPagoSeleccionado = Array.from(botonesMedioPago).some(btn => btn.classList.contains('active'));
        const comprobante = document.getElementById('voucher_type').value;

        if (!comprobante) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Debe seleccionar un tipo de comprobante.'
            });
            $('#global-spinner').removeClass('spinner-visible').addClass('spinner-hidden');
            return;
        }

        if (!metodoPagoSeleccionado) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Debe seleccionar al menos un método de pago.'
            });
            $('#global-spinner').removeClass('spinner-visible').addClass('spinner-hidden');
            return;
        }

        // Obtener valores actuales de type_status y status
        const typeStatus = document.getElementById('type_status').value;
        const status = document.getElementById('status').value;
        const checkDeliveryEl = document.getElementById('checkDelivery');

        // Validar saldos según el tipo de venta
        const saldoActual = calcularSaldo();
        const saldoElement = document.getElementById('saldoAmount');

        // Para ventas directas (type_status=0, status=1) NO permitir saldos
        // Para delivery (type_status=2) NO permitir saldos NUNCA (sin importar el status)
        if (saldoElement && saldoActual < 0 && saldoElement.classList.contains('text-warning')) {
            ToastMessage.fire({
                icon: 'error',
                text: 'El saldo no puede ser negativo.'
            });
            $('#global-spinner').removeClass('spinner-visible').addClass('spinner-hidden');
            return;
        }

        if ((typeStatus == '0' && status == '1') || typeStatus == '2' || (checkDeliveryEl && checkDeliveryEl.checked)) {
            if (saldoActual > 0.01) {
                const tipoVenta = (checkDeliveryEl && checkDeliveryEl.checked) ? 'delivery' : 'venta directa';
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: `Para ${tipoVenta} debe cancelar el monto completo antes de registrar la venta. El saldo actual es: S/ ${saldoActual.toFixed(2)}`
                });
                $('#global-spinner').removeClass('spinner-visible').addClass('spinner-hidden');
                return;
            }
        }

        // Validar campos de delivery si está activado
        if (checkDeliveryEl && checkDeliveryEl.checked) {
            const deliveryDate = document.getElementById('delivery_date').value;
            const deliveryHour = document.getElementById('delivery_hour').value;
            const deliveryAddress = document.getElementById('delivery_address').value;

            if (!deliveryDate || !deliveryHour || !deliveryAddress.trim()) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Debe completar todos los campos de delivery: fecha, hora y dirección de entrega.'
                });
                $('#global-spinner').removeClass('spinner-visible').addClass('spinner-hidden');
                return;
            }
        }

        // Crear FormData ANTES de usarlo
        const form = this;
        const formData = new FormData(form);

        // Datos básicos
        formData.append('products', JSON.stringify(selectedProducts));

        const totalElement = document.getElementById('totalAmountInput');
        const totalValue = totalElement ? totalElement.value : '0';
        formData.append('total', totalValue);

        formData.append('voucher_type', comprobante);
        formData.append('restaurant', 1);

        // Agregar documento del cliente
        const documentElement = document.getElementById('document');
        const documentValue = documentElement ? documentElement.value || '' : '';
        formData.append('document', documentValue);

        const employeeElement = document.getElementById('employee_id');
        const employeeValue = employeeElement ? employeeElement.value || '' : '';
        formData.append('employee_id', employeeValue);


        // Agregar nombre del cliente si existe
        const nameElement = document.getElementById('name');
        const nameValue = nameElement ? nameElement.value || '' : '';
        formData.append('client_name', nameValue);

        // Agregar dirección del cliente si existe
        const addressElement = document.getElementById('address');
        const addressValue = addressElement ? addressElement.value || '' : '';
        formData.append('client_address', addressValue);

        // Agregar datos de delivery si está activado
        if (checkDeliveryEl && checkDeliveryEl.checked) {
            const deliveryDate = document.getElementById('delivery_date');
            const deliveryHour = document.getElementById('delivery_hour');
            const deliveryAddress = document.getElementById('delivery_address');

            formData.append('fecha_entrega', deliveryDate ? deliveryDate.value : '');
            formData.append('hora_entrega', deliveryHour ? deliveryHour.value : '');
            formData.append('direccion', deliveryAddress ? deliveryAddress.value : '');
        }

        formData.append('table_id', state.table_id);

        if (openedMesaId) formData.append('mesa_id', openedMesaId);

        const resetFormulario = () => {
            selectedProducts = [];
            addProductToTable();
            document.getElementById('formCobro').reset();
            document.getElementById('totalAmount').textContent = '0.00';
            document.getElementById('totalAmountInput').value = 0;
            document.getElementById('voucher_type').value = '';

            // Limpiar variables globales
            currentOrderId = null;
            const mesaIdToReset = openedMesaId;
            openedMesaId = null;

            // Limpiar borde de la mesa si existe
            if (mesaIdToReset) {
                const card = document.getElementById(`mesa-card-${mesaIdToReset}`);
                if (card) {
                    card.classList.remove('borde-verde', 'borde-naranja', 'borde-rojo');
                    console.log('Borde removido de mesa:', mesaIdToReset, 'durante resetFormulario');
                }
            }

            // Resetear botones de comprobante
            document.querySelectorAll('#btn-boleta, #btn-factura, #btn-ticket').forEach(btn => {
                btn.classList.remove('btn-primary', 'btn-success', 'btn-info');
                if (btn.id === 'btn-boleta') {
                    btn.classList.add('btn-outline-primary');
                } else if (btn.id === 'btn-factura') {
                    btn.classList.add('btn-outline-success');
                } else if (btn.id === 'btn-ticket') {
                    btn.classList.add('btn-outline-info');
                }
            });

            // Resetear métodos de pago
            document.querySelectorAll('[id^="btn-"].active').forEach(btn => {
                btn.classList.remove('active', 'btn-success');
                const campos = btn.dataset.campos;
                $(`#${campos}`).addClass('d-none').removeClass('d-flex');
                $(`#${campos} input[type="text"]`).val('');
            });

            // Resetear campos de delivery
            const checkDeliveryReset = document.getElementById('checkDelivery');
            const camposDeliveryReset = document.getElementById('camposDelivery');
            if (checkDeliveryReset) {
                checkDeliveryReset.checked = false;
            }
            if (camposDeliveryReset) {
                camposDeliveryReset.classList.add('d-none');
            }

            // Limpiar campos de delivery
            const deliveryDateReset = document.getElementById('delivery_date');
            const deliveryHourReset = document.getElementById('delivery_hour');
            const deliveryAddressReset = document.getElementById('delivery_address');
            if (deliveryDateReset) deliveryDateReset.value = '';
            if (deliveryHourReset) deliveryHourReset.value = '';
            if (deliveryAddressReset) deliveryAddressReset.value = '';

            // Restaurar valores de status
            const typeStatusReset = document.getElementById('type_status');
            const statusReset = document.getElementById('status');
            if (typeStatusReset) typeStatusReset.value = 0;
            if (statusReset) statusReset.value = 1;

            console.log('Formulario reseteado completamente para mesa:', mesaIdToReset);
        };


        $.ajax({
            url: "{{ route('sales.store') }}",
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}', // Incluye el token CSRF
            },
            data: formData,
            processData: false,
            contentType: false,
            success: async function(response){
                console.log('Respuesta venta:', response);
                if (response.status) {
                    // Limpiar timers antes de cerrar modales
                    if (openedMesaId && mesaTimers[openedMesaId]) {
                        clearInterval(mesaTimers[openedMesaId]);
                        delete mesaTimers[openedMesaId];
                        console.log('Timer limpiado para mesa:', openedMesaId);
                    }

                    $('#modalCobro').modal('hide');
                    $('#abrirMesaModal').modal('hide');
                    clearInterval(timerInterval);

                    ToastMessage.fire({
                        text: 'Venta registrada correctamente'
                    });

                    if (response.ticket_pdf_url) {
                        try {
                            await printTicketRawWithQz(response.venta.id, {
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

                    // Cerrar mesa y restaurar UI
                    cerrarMesaFrom(openedMesaId);
                    resetFormulario();
                } else {
                    if (typeof ToastError !== 'undefined') {
                        ToastError.fire({
                            text: response.message || 'Error al registrar venta'
                        });
                    } else {
                        alert(response.message || 'Error al registrar venta');
                    }
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

    let clientSearchTimeout = null;
    $('#search-product').autocomplete({
        source: function(request, response) {
            clearTimeout(clientSearchTimeout);
            clientSearchTimeout = setTimeout(function() {
                let currentTerm = $('#search-product').val();
                // Solo buscar si hay al menos una letra
                if (currentTerm && currentTerm.length > 0) {
                    $.ajax({
                        url: "{{ route('products.searchrs') }}",
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
            }, 500);
        },
        appendTo: '#abrirMesaModal',
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

    function abrirMesa(mesaId) {

        state.table_id = mesaId;

        fetch(`{{ url('/mesas/abrir') }}/${mesaId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
            })
            .then(res => {
                if (!res.ok) throw new Error("Error al abrir mesa");
                return res.json();
            })
            .then(data => {
                console.log('Respuesta del servidor abrirMesa:', data);

                // ✅ ACTUALIZAR ESTADO
                const estadoSpan = document.getElementById(`estado-mesa-${mesaId}`);
                if (estadoSpan) {
                    estadoSpan.textContent = 'Ocupada';
                    estadoSpan.classList.remove('bg-success');
                    estadoSpan.classList.add('bg-danger');
                }

                // ✅ LIMPIAR BORDE AL ABRIR MESA
                const mesaCard = document.getElementById(`mesa-card-${mesaId}`);
                if (mesaCard) {
                    mesaCard.classList.remove('borde-verde', 'borde-naranja', 'borde-rojo');
                    console.log('Borde limpio al abrir mesa:', mesaId);
                }

                // ✅ REEMPLAZAR ACCIONES
                const accionesDiv = document.getElementById(`acciones-mesa-${mesaId}`);
                if (accionesDiv) {
                    accionesDiv.innerHTML = `
                    <div class="d-grid gap-2">
                        <button class="btn btn-warning rounded-pill" onclick="verPedido(${mesaId})">
                            Ver Pedido
                        </button>
                        <button class="btn btn-danger rounded-pill" onclick="cerrarMesa(${mesaId})">
                            Cancelar Venta <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div class="mt-2 text-muted small">
                        Tiempo: <span id="contador-${mesaId}">--:--</span>
                    </div>
                `;
                }

                // ✅ CONTADOR Y COLOR DINÁMICO
                const openedAt = new Date(data.opened_at);
                const contadorEl = document.getElementById(`contador-${mesaId}`);
                const card = document.getElementById(`mesa-card-${mesaId}`);

                if (contadorEl && card) {
                    const intervalId = setInterval(() => {
                        const now = new Date();
                        const diff = Math.floor((now - openedAt) / 1000);
                        const min = String(Math.floor(diff / 60)).padStart(2, '0');
                        const sec = String(diff % 60).padStart(2, '0');
                        contadorEl.textContent = `${min}:${sec}`;

                        // Cambiar borde por tiempo
                        if (diff >= 3600) {
                            card.classList.add('borde-rojo');
                            card.classList.remove('borde-naranja', 'borde-verde');
                        } else if (diff >= 1200) {
                            card.classList.add('borde-naranja');
                            card.classList.remove('borde-rojo', 'borde-verde');
                        } else {
                            card.classList.add('borde-verde');
                            card.classList.remove('borde-naranja', 'borde-rojo');
                        }
                    }, 1000);

                    mesaTimers[mesaId] = intervalId; // Guardar para limpiar luego
                }

                // ✅ LIMPIAR INFO DE PEDIDO PREVIO
                selectedProducts = [];
                addProductToTable();
                currentOrderId = null;
                openedMesaId = null;
                $('#document').val('');
                $('#client').val('');
                $('#observacion').val('');
                $('#totalAmount').text('0.00');
                $('#totalAmountInput').val('0');
                document.querySelectorAll("input[name^='monto']").forEach(el => el.value = '');

                // ✅ GUARDAR INFO ACTUAL
                openedMesaId = mesaId;
                currentOrderId = data.order_id;

                // ✅ CARGAR PRODUCTOS EXISTENTES SI HAY UN PEDIDO
                if (data.order_id && data.productos && data.productos.length > 0) {
                    console.log('Cargando productos existentes:', data.productos);
                    selectedProducts = data.productos.map(p => ({
                        id: p.id || p.product_id,
                        nombre: p.nombre || p.name,
                        precio: toNum(p.precio || p.product_price || p.unit_price, 2),
                        cantidad: toNum(p.cantidad || p.quantity, 3),
                        stock: p.stock || p.quantity_available || 9999
                    }));
                    addProductToTable();
                    console.log('selectedProducts después de cargar:', selectedProducts);
                }

                $('#abrirMesaModal').modal('show');
            })
            .catch(error => {
                console.error(error);
                alert("No se pudo abrir la mesa.");
            });
    }

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
                    ToastError.fire({
                        text: response.message || 'No se encontró información'
                    });
                }
                Swal.close();
            },
            error: function(xhr) {
                ToastError.fire({
                    text: 'Error al consultar SUNAT/RENIEC'
                });
                Swal.close();
            }
        });
    }

    // Variables globales para manejo de productos
    let productTableCounter = 0;
    let productTableBody = null; // será asignado cuando el DOM esté listo dentro del modal

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
        // Delegate state changes to agregarProductoClick to avoid double-inserts.
        const idStr = String(productId);
        const idx = selectedProducts.findIndex(p => String(p.id) === idStr);

        if (idx > -1) {
            // Incrementar cantidad sin validar stock
            agregarProductoClick({
                id: productId,
                precio: unitPrice,
                nombre: productName,
                stock: stock
            });
        } else {
            // Add new product via agregarProductoClick (it will update selectedProducts and re-render)
            agregarProductoClick({
                id: productId,
                precio: unitPrice,
                nombre: productName,
                stock: stock
            });
        }

        // Limpiar campos de búsqueda como antes
        $('#search-product').val('');
        $('#product_id').val('');
    }

    
    function addProductToTable(productId, productName, unitPrice, stock) {
        // Asignar productTableBody si no está asignado
        if (!productTableBody) {
            productTableBody = document.querySelector('#abrirMesaModal table tbody') || document.querySelector('tbody');
        }

        // Si no se pasan argumentos, re-renderizar la tabla desde selectedProducts
        if (typeof productId === 'undefined') {
            productTableBody.innerHTML = '';
            productTableCounter = 0;

            if (!Array.isArray(selectedProducts) || selectedProducts.length === 0) {
                updateTotal();
                return;
            }

            selectedProducts.forEach(p => {
                productTableCounter++;
                const id = p.id;
                const name = p.nombre || p.name || '';
                const precio = parseFloat(p.precio || p.unit_price || 0).toFixed(2);
                const cantidad = (typeof p.cantidad !== 'undefined') ? p.cantidad : 1;

                const row = document.createElement('tr');
                row.setAttribute('data-product-id', id);
                row.innerHTML = `
                    <td class="text-center">${productTableCounter}</td>
                    <td>${name}</td>
                    <td class="text-center">
                        <div class="input-group" style="width: 120px; margin: 0 auto;">
                        <input id="quantity-${productTableCounter}" type="number" class="form-control form-control-sm text-center quantity-input" 
                            value="${cantidad}" min="1"
                            onchange="updateQuantity(this, ${precio}); updateSubtotal(${productTableCounter - 1});"
                            name="products[${id}][cantidad]">
                        </div>
                        <input type="hidden" name="products[${id}][id]" value="${id}">
                        <input type="hidden" name="products[${id}][precio]" value="${precio}">
                    </td>
                    <td class="text-center">S/ ${parseFloat(precio).toFixed(2)}</td>
                    <td class="text-center subtotal subtotal-container">S/ ${(parseFloat(precio) * parseFloat(cantidad)).toFixed(2)} ${ p.confirmado === 1 ? '<i class="bi bi-check2-square" title="Confirmado"></i></button>' : ''}</td>
                    <td class="text-center">
                        <button type="button" class="btn btn-danger btn-sm" onclick="removeProduct(this)">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                `;

                productTableBody.appendChild(row);
            });

            updateTotal();
            return;
        }

        // Comportamiento anterior: agregar una fila individual
        productTableCounter++;

        const row = document.createElement('tr');
        row.setAttribute('data-product-id', productId);

        row.innerHTML = `
            <td class="text-center">${productTableCounter}</td>
            <td>${productName}</td>
            <td class="text-center">
                <div class="input-group" style="width: 120px; margin: 0 auto;">
                    <input id="quantity-${productTableCounter}" type="number" class="form-control form-control-sm text-center quantity-input" 
                        value="1" min="1"
                        onchange="updateQuantity(this, ${unitPrice}); updateSubtotal(${productTableCounter - 1});"
                        name="products[${productId}][cantidad]">
                </div>
                <input type="hidden" name="products[${productId}][id]" value="${productId}">
                <input type="hidden" name="products[${productId}][precio]" value="${unitPrice}">
            </td>
            <td class="text-center">S/ ${parseFloat(unitPrice).toFixed(2)}</td>
            <td class="text-center subtotal subtotal-container">S/ ${parseFloat(unitPrice).toFixed(2)}</td>
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
        if (!confirm('¿Está seguro de eliminar este producto?')) return;

        const row = button.closest('tr');
        const productId = row.getAttribute('data-product-id');

        // Actualizar el array selectedProducts
        const productIndex = selectedProducts.findIndex(p => String(p.id) === String(productId));
        if (productIndex > -1) {
            selectedProducts.splice(productIndex, 1);
        }

        // Si hay orden activa, primero eliminar en backend
        if (currentOrderId) {
            eliminarProductoDelPedido(productId)
                .then(() => {
                    row.remove();
                    updateTotal();
                    renumberRows();
                    // Programar envío de la tabla completa actualizada
                    scheduleEnviarTabla();
                })
                .catch(err => {
                    console.error('No se pudo eliminar producto en backend:', err);
                    alert('No se pudo eliminar el producto en el servidor. Intente de nuevo.');
                });
        } else {
            // Si no hay orden en backend, solo actualizar UI
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

    const ToastSuccess = Swal.mixin({
        toast: true,
        position: 'top-end',
        icon: 'success',
        showConfirmButton: false,
        timer: 2000,
        timerProgressBar: true
    });

    let debounceTimers = {};

    function updateSubtotal(index) {
        const input = document.getElementById(`quantity-${index + 1}`); // +1 porque los IDs empiezan en 1
        const raw = parseFloat(input.value);
        const newQuantity = Number.isFinite(raw) ? toNum(raw, 3) : 0;

        // Verificar que el índice sea válido
        if (index >= 0 && index < selectedProducts.length) {
            const item = selectedProducts[index];
            item.cantidad = newQuantity;

            // Programar envío de la tabla completa después de editar cantidad
            if (debounceTimers[index]) clearTimeout(debounceTimers[index]);
            debounceTimers[index] = setTimeout(() => {
                scheduleEnviarTabla();
            }, 800);

            updateSubtotalDisplay(index);
            updateTotal();
        }
    }


    // Nueva función para actualizar solo el display del subtotal
    function updateSubtotalDisplay(index) {
        const row = document.querySelector(`#quantity-${index + 1}`).closest('tr'); // +1 porque los IDs empiezan en 1
        const subtotalCell = row.cells[4]; // La celda del subtotal (índice 4)

        // Verificar que el índice sea válido
        if (index >= 0 && index < selectedProducts.length) {
            const precio = parseFloat(selectedProducts[index].precio) || 0;
            const cantidad = parseFloat(selectedProducts[index].cantidad);
            // Si cantidad es NaN (input vacío o inválido), mostrar 0
            const subtotal = (!isNaN(cantidad) ? precio * cantidad : 0).toFixed(2);
            subtotalCell.textContent = `S/ ${subtotal}`;
        }
    }

    // Debounce global para enviar la tabla completa de productos al backend
    let sendTableTimer = null;
    const SEND_TABLE_DELAY = 1500; // ms

    function scheduleEnviarTabla() {
        if (sendTableTimer) clearTimeout(sendTableTimer);
        sendTableTimer = setTimeout(() => {
            enviarTablaProductosAlPedido();
        }, SEND_TABLE_DELAY);
    }

    function agregarProductoClick(producto) {
        // Actualiza el array selectedProducts sin validar stock
        const idStr = String(producto.id);
        const idx = selectedProducts.findIndex(p => String(p.id) === idStr);

        if (idx > -1) {
            const current = Number(selectedProducts[idx].cantidad) || 0;
            selectedProducts[idx].cantidad = toNum(current + 1, 3);
        } else {
            selectedProducts.push({
                id: producto.id,
                nombre: producto.nombre ?? null,
                precio: toNum(producto.precio, 2),
                cantidad: 1,
                stock: producto.stock
            });
        }

        // Re-renderizar tabla desde selectedProducts
        addProductToTable();
        scheduleEnviarTabla();
    }

    async function enviarTablaProductosAlPedido() {
        if (!currentOrderId) {
            console.warn('No hay order id al intentar enviar la tabla de productos');
            return;
        }
        // En este endpoint el backend espera un solo producto por request con campos: product_id, quantity, product_price
        // Hacemos un POST por cada producto para sincronizar el pedido en el servidor (modo overwrite)
        if (!Array.isArray(selectedProducts) || selectedProducts.length === 0) {
            console.log('selectedProducts vacío, no se envía nada.');
            sendTableTimer = null;
            return;
        }

        console.log('Sincronizando', selectedProducts.length, 'productos al pedido', currentOrderId);

        try {
            for (const p of selectedProducts) {
                const body = {
                    product_id: p.id,
                    quantity: p.cantidad,
                    product_price: p.precio,
                    nombre: p.nombre ?? null,
                    sumar: false // overwrite: el backend hará updateOrCreate
                };

                console.log('Enviando producto al servidor:', body);

                const res = await fetch(`{{ url('/orders') }}/${currentOrderId}/addproducts`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(body)
                });

                if (!res.ok) {
                    const text = await res.text();
                    console.error('Error al sincronizar producto', p.id, 'status:', res.status, text);
                    // No abortamos: seguimos intentando con los demás productos
                } else {
                    const data = await res.json().catch(() => null);
                    console.log('Respuesta servidor para producto', p.id, data);
                }
            }
        } catch (err) {
            console.error('Error de red al sincronizar productos:', err);
        } finally {
            sendTableTimer = null;
        }
    }

    function eliminarProductoDelPedido(productId) {
        if (!currentOrderId) {
            console.error("No hay orden activa");
            return Promise.reject(new Error("No hay orden activa"));
        }

        console.log("Eliminando producto:", productId, "de la orden:", currentOrderId);

        return fetch(`{{ url('/orders') }}/${currentOrderId}/removeproduct`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    product_id: productId
                })
            })
            .then(response => {
                console.log('Status:', response.status);
                console.log('Status Text:', response.statusText);

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                return response.json();
            })
            .then(data => {
                console.log('Respuesta al eliminar producto:', data);
                if (data.success) {
                    console.log('Producto eliminado exitosamente del backend');
                } else {
                    console.error('Error del servidor:', data.message);
                    alert("Error al eliminar el producto: " + (data.message || "Error desconocido"));
                }
            })
            .catch(error => {
                console.error('Error al eliminar producto:', error);
                alert("Error de conexión al eliminar el producto. Verifique su conexión.");
            });
    }

    const toNum = (v, dec = null) => {
        const n = parseFloat(v);
        if (!Number.isFinite(n)) return 0;
        return dec === null ? n : +n.toFixed(dec);
    };

    function verPedido(mesaId) {
        state.table_id = mesaId;

        fetch(`{{ url('/mesas/pedido') }}/${mesaId}`)
            .then(res => {
                if (!res.ok) throw new Error("Error al obtener el pedido.");
                return res.json();
            })
            .then(data => {
                if (!data.success) {
                    if (data.message == 'No hay pedido abierto para esta mesa') {
                        abrirMesa(mesaId);
                    } else {
                        alert(data.message);
                        return;
                    }
                }

                selectedProducts = (data.productos || []).map(p => ({
                    ...p,
                    cantidad: toNum(p.cantidad, 3),
                    precio: toNum(p.precio, 2),
                    confirmado: toNum(p.confirmado),
                }));
                currentOrderId = data.order_id;
                openedMesaId = mesaId;
                addProductToTable();
                $('#abrirMesaModal').modal('show');
            })
            .catch(err => {
                console.error('Error al cargar pedido:', err);
                alert("Error al cargar el pedido.");
            });
    }

    // Vuelve a tener la función updateTotal simple
    function updateTotal() {
        let total = 0;
        selectedProducts.forEach(p => {
            total += (parseFloat(p.precio) || 0) * (parseFloat(p.cantidad) || 0);
        });
        document.getElementById('totalAmount').textContent = total.toFixed(2);
        document.getElementById('totalAmountInput').value = total.toFixed(2);
    }

    function cerrarMesaFrom(mesaId) {
        fetch(`{{ url('/mesas') }}/${mesaId}/cerrar`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    console.log('Mesa cerrada exitosamente desde backend:', mesaId);

                    Swal.fire({
                        icon: 'success',
                        title: 'Mesa liberada',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2000
                    });

                    // Restaurar UI usando helper (esto debería quitar el borde)
                    restoreMesaUI(mesaId);

                } else {
                    console.error('Error al cerrar mesa desde backend:', data.message);
                    Swal.fire('Error', data.message || 'No se pudo cerrar la mesa.', 'error');
                }
            })
            .catch(err => {
                console.error('Error al cerrar la mesa:', err);
                Swal.fire('Error', 'Error inesperado al cerrar la mesa.', 'error');
            });
    }

    function cerrarMesa(mesaId) {
        Swal.fire({
            title: '¿Liberar mesa?',
            text: 'Esto eliminará el pedido y liberará la mesa.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, liberar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#d33',
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`{{ url('/mesas') }}/${mesaId}/cerrar`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Mesa liberada',
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 2000
                            });


                            // Restaurar UI usando helper
                            restoreMesaUI(mesaId);


                        } else {
                            Swal.fire('Error', data.message || 'No se pudo cerrar la mesa.', 'error');
                        }
                    })
                    .catch(err => {
                        console.error('Error al cerrar la mesa:', err);
                        Swal.fire('Error', 'Error inesperado al cerrar la mesa.', 'error');
                    });
            }
        });

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

    let currentProductIndex = -1;

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


    function confirmOrder(showModal = true) {
        var order_id = currentOrderId;
        var order_id = currentOrderId;
        $.ajax({
            url: "{{ route('orders.confirm') }}",
            method: 'post',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: {
                order_id
            },
            success: async function(response) {
                if (response.status) {
                    var table = response.table;
                    var details = response.details;
                    mostrarIconoConfirmado();

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
                            }, {
                                nombre: 'Feed',
                                argumentos: [2]
                            },
                            {
                                nombre: 'EscribirTexto',
                                argumentos: ['PREPARACION\n']
                            },
                            {
                                nombre: "EstablecerAlineacion",
                                argumentos: [0]
                            },
                            {
                                nombre: 'Feed',
                                argumentos: [2]
                            },
                            {
                                nombre: 'EscribirTexto',
                                argumentos: ['----------------------------------------\n']
                            },
                            {
                                nombre: 'EscribirTexto',
                                argumentos: [`FECHA: ${(new Date()).toLocaleDateString('es-PE')} ${(new Date()).toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit' })}\n`]
                            },
                            {
                                nombre: 'TextoSegunPaginaDeCodigos',
                                argumentos: [
                                    2,
                                    'cp850',
                                    `MESA: ${table}\n`
                                ]
                            }, {
                                nombre: 'Feed',
                                argumentos: [2]
                            },
                            {
                                nombre: 'EscribirTexto',
                                argumentos: ['----------------------------------------\n']
                            }, {
                                nombre: 'Feed',
                                argumentos: [2]
                            },
                            {
                                nombre: 'EstablecerEnfatizado',
                                argumentos: [true]
                            }
                        ]
                    };

                    details.forEach(function(order) {
                        opts.operaciones.push({
                            nombre: 'TextoSegunPaginaDeCodigos',
                            argumentos: [
                                2,
                                'cp850',
                                `${order.quantity}    ${order.product.name}\n`
                            ]
                        }, );
                    });

                    opts.operaciones.push({
                        nombre: 'Feed',
                        argumentos: [2]
                    }, {
                        nombre: 'Corte',
                        argumentos: [1]
                    });

                    try {
                        // IP de la PC que tiene la impresora (cámbiala por la tuya)
                        const IP_PC_IMPRESORA = '192.168.18.46';

                        let url;
                        let headers = {
                            'Content-Type': 'application/json; charset=utf-8'
                        };

                        // Verificar si estamos en Android o PC
                        let esAndroid = false;
                        try {
                            const platformResponse = await fetch('http://localhost:8000/version', {
                                timeout: 3000 // Timeout de 3 segundos
                            });
                            const platformData = await platformResponse.json();
                            esAndroid = platformData.plataforma === "Puente";
                            console.log('Plataforma detectada:', esAndroid ? 'Android' : 'PC');
                        } catch (error) {
                            console.log('No se pudo detectar la plataforma, asumiendo PC');
                            esAndroid = false;
                        }

                        if (esAndroid) {
                            // Método Android con reenvío usando x-reenviar-a
                            url = 'http://localhost:8000';
                            headers['x-reenviar-a'] = `http://${IP_PC_IMPRESORA}:8000/imprimir`;
                            console.log('Usando método Android con reenvío');

                            // Enviar solicitud Android
                            const http = await fetch(url, {
                                method: 'POST',
                                body: JSON.stringify(opts),
                                headers: headers
                            });

                            const res = await http.json();

                            if (res.ok) {
                                console.log('Impresión Android exitosa');
                                if (typeof ToastMessage !== 'undefined') {
                                    ToastMessage.fire({
                                        text: 'Documento enviado a impresión correctamente (Android)'
                                    });
                                }
                            } else {
                                throw new Error(res.message || 'Error en impresión Android');
                            }

                        } else {
                            // Método PC: intentar local primero, si falla usar reenvío
                            let impresionExitosa = false;

                            try {
                                console.log('Intentando impresión local...');
                                // Intentar impresión local directa
                                const localResponse = await fetch('http://localhost:8000/imprimir', {
                                    method: 'POST',
                                    body: JSON.stringify(opts),
                                    headers: {
                                        'Content-Type': 'application/json; charset=utf-8'
                                    }
                                });

                                const localRes = await localResponse.json();

                                if (localRes.ok) {
                                    console.log('Impresión local exitosa');
                                    if (typeof ToastMessage !== 'undefined') {
                                        ToastMessage.fire({
                                            text: 'Documento enviado a impresión correctamente (Local)'
                                        });
                                    }
                                    impresionExitosa = true;
                                } else {
                                    throw new Error('Impresión local falló: ' + localRes.message);
                                }

                            } catch (errorLocal) {
                                console.log('Error en impresión local:', errorLocal.message);
                                console.log('Intentando impresión remota...');

                                try {
                                    // Usar el método de reenvío remoto
                                    const rutaRemota = `http://${IP_PC_IMPRESORA}:8000/imprimir`;
                                    const payload = {
                                        operaciones: opts.operaciones,
                                        nombreImpresora: opts.nombreImpresora,
                                        serial: opts.serial,
                                    };

                                    const remoteResponse = await fetch('http://localhost:8000/reenviar?host=' + rutaRemota, {
                                        method: 'POST',
                                        body: JSON.stringify(payload),
                                        headers: {
                                            'Content-Type': 'application/json; charset=utf-8'
                                        }
                                    });

                                    const remoteRes = await remoteResponse.json();

                                    if (remoteRes.ok) {
                                        console.log('Impresión remota exitosa');
                                        if (typeof ToastMessage !== 'undefined') {
                                            ToastMessage.fire({
                                                text: 'Documento enviado a impresión correctamente (Remoto)'
                                            });
                                        }
                                        impresionExitosa = true;
                                    } else {
                                        throw new Error('Impresión remota falló: ' + remoteRes.message);
                                    }

                                } catch (errorRemoto) {
                                    console.log('Error en impresión remota:', errorRemoto.message);
                                    throw new Error('Falló tanto la impresión local como la remota');
                                }
                            }

                            if (!impresionExitosa) {
                                throw new Error('No se pudo completar la impresión');
                            }
                        }

                    } catch (error) {
                        console.error('Error en el proceso de impresión:', error);

                        // Mostrar error específico según el tipo
                        let errorMessage = 'Error desconocido';

                        if (error.name === 'TypeError' && error.message.includes('fetch')) {
                            errorMessage = 'No se pudo conectar con el servicio de impresión. Verifica que esté funcionando.';
                        } else if (error.message.includes('timeout')) {
                            errorMessage = 'Timeout: El servicio de impresión no responde.';
                        } else if (error.message.includes('HTTP Error')) {
                            errorMessage = `Error de servidor: ${error.message}`;
                        } else {
                            errorMessage = error.message;
                        }

                        if (typeof ToastError !== 'undefined') {
                            ToastError.fire({
                                text: `Error al imprimir: ${errorMessage}`
                            });
                        }
                    }


                } else {
                    //ToastError.fire({ text: response.error });
                }
            },
            error: function(err) {
                console.log('Ocurrió un error');
            }
        });
    }

    function preaccount(showModal = true) {
        var order_id = currentOrderId;
        $.ajax({
            url: '{{ route("orders.preaccount") }}',
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: {
                order_id
            },
            success: async function(response) {
                if (response.status) {
                    var table = response.table;
                    var details = response.details;
                    var subtotal = response.subtotal;

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
                                nombre: 'Feed',
                                argumentos: [2]
                            },
                            {
                                nombre: 'EscribirTexto',
                                argumentos: ['PRECUENTA\n']
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
                                argumentos: [`FECHA: ${(new Date()).toLocaleDateString('es-PE')} ${(new Date()).toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit' })}\n`]
                            },
                            {
                                nombre: 'TextoSegunPaginaDeCodigos',
                                argumentos: [
                                    2,
                                    'cp850',
                                    `MESA: ${table}\n`
                                ]
                            },
                            {
                                nombre: 'Feed',
                                argumentos: [2]
                            },
                            {
                                nombre: 'EscribirTexto',
                                argumentos: ['----------------------------------------\n']
                            },
                            {
                                nombre: 'Feed',
                                argumentos: [2]
                            },
                            {
                                nombre: 'EstablecerEnfatizado',
                                argumentos: [true]
                            }
                        ]
                    };

                    details.forEach(function(order) {
                        opts.operaciones.push({
                            nombre: 'TextoSegunPaginaDeCodigos',
                            argumentos: [
                                2,
                                'cp850',
                                `${order.quantity}    ${order.product.name}     ${(order.product_price * order.quantity).toFixed(2)}\n`
                            ]
                        }, );
                    });

                    opts.operaciones.push({
                        nombre: 'Feed',
                        argumentos: [2]
                    }, {
                        nombre: 'EscribirTexto',
                        argumentos: ['----------------------------------------\n']
                    }, {
                        nombre: 'Feed',
                        argumentos: [2]
                    }, {
                        nombre: 'EscribirTexto',
                        argumentos: [`Subtotal: S/${(subtotal).toFixed(2)}`]
                    }, {
                        nombre: 'Feed',
                        argumentos: [2]
                    }, {
                        nombre: 'Corte',
                        argumentos: [1]
                    });

                    try {
                        // IP de la PC que tiene la impresora (cámbiala por la tuya)
                        const IP_PC_IMPRESORA = '192.168.18.46';

                        let url;
                        let headers = {
                            'Content-Type': 'application/json; charset=utf-8'
                        };

                        // Verificar si estamos en Android o PC
                        let esAndroid = false;
                        try {
                            const platformResponse = await fetch('http://localhost:8000/version', {
                                timeout: 3000 // Timeout de 3 segundos
                            });
                            const platformData = await platformResponse.json();
                            esAndroid = platformData.plataforma === "Puente";
                            console.log('Plataforma detectada:', esAndroid ? 'Android' : 'PC');
                        } catch (error) {
                            console.log('No se pudo detectar la plataforma, asumiendo PC');
                            esAndroid = false;
                        }

                        if (esAndroid) {
                            // Método Android con reenvío usando x-reenviar-a
                            url = 'http://localhost:8000';
                            headers['x-reenviar-a'] = `http://${IP_PC_IMPRESORA}:8000/imprimir`;
                            console.log('Usando método Android con reenvío');

                            // Enviar solicitud Android
                            const http = await fetch(url, {
                                method: 'POST',
                                body: JSON.stringify(opts),
                                headers: headers
                            });

                            const res = await http.json();

                            if (res.ok) {
                                console.log('Impresión Android exitosa');
                                if (typeof ToastMessage !== 'undefined') {
                                    ToastMessage.fire({
                                        text: 'Documento enviado a impresión correctamente (Android)'
                                    });
                                }
                            } else {
                                throw new Error(res.message || 'Error en impresión Android');
                            }

                        } else {
                            // Método PC: intentar local primero, si falla usar reenvío
                            let impresionExitosa = false;

                            try {
                                console.log('Intentando impresión local...');
                                // Intentar impresión local directa
                                const localResponse = await fetch('http://localhost:8000/imprimir', {
                                    method: 'POST',
                                    body: JSON.stringify(opts),
                                    headers: {
                                        'Content-Type': 'application/json; charset=utf-8'
                                    }
                                });

                                const localRes = await localResponse.json();

                                if (localRes.ok) {
                                    console.log('Impresión local exitosa');
                                    if (typeof ToastMessage !== 'undefined') {
                                        ToastMessage.fire({
                                            text: 'Documento enviado a impresión correctamente (Local)'
                                        });
                                    }
                                    impresionExitosa = true;
                                } else {
                                    throw new Error('Impresión local falló: ' + localRes.message);
                                }

                            } catch (errorLocal) {
                                console.log('Error en impresión local:', errorLocal.message);
                                console.log('Intentando impresión remota...');

                                try {
                                    // Usar el método de reenvío remoto
                                    const rutaRemota = `http://${IP_PC_IMPRESORA}:8000/imprimir`;
                                    const payload = {
                                        operaciones: opts.operaciones,
                                        nombreImpresora: opts.nombreImpresora,
                                        serial: opts.serial,
                                    };

                                    const remoteResponse = await fetch('http://localhost:8000/reenviar?host=' + rutaRemota, {
                                        method: 'POST',
                                        body: JSON.stringify(payload),
                                        headers: {
                                            'Content-Type': 'application/json; charset=utf-8'
                                        }
                                    });

                                    const remoteRes = await remoteResponse.json();

                                    if (remoteRes.ok) {
                                        console.log('Impresión remota exitosa');
                                        if (typeof ToastMessage !== 'undefined') {
                                            ToastMessage.fire({
                                                text: 'Documento enviado a impresión correctamente (Remoto)'
                                            });
                                        }
                                        impresionExitosa = true;
                                    } else {
                                        throw new Error('Impresión remota falló: ' + remoteRes.message);
                                    }

                                } catch (errorRemoto) {
                                    console.log('Error en impresión remota:', errorRemoto.message);
                                    throw new Error('Falló tanto la impresión local como la remota');
                                }
                            }

                            if (!impresionExitosa) {
                                throw new Error('No se pudo completar la impresión');
                            }
                        }

                    } catch (error) {
                        console.error('Error en el proceso de impresión:', error);

                        // Mostrar error específico según el tipo
                        let errorMessage = 'Error desconocido';

                        if (error.name === 'TypeError' && error.message.includes('fetch')) {
                            errorMessage = 'No se pudo conectar con el servicio de impresión. Verifica que esté funcionando.';
                        } else if (error.message.includes('timeout')) {
                            errorMessage = 'Timeout: El servicio de impresión no responde.';
                        } else if (error.message.includes('HTTP Error')) {
                            errorMessage = `Error de servidor: ${error.message}`;
                        } else {
                            errorMessage = error.message;
                        }

                        if (typeof ToastError !== 'undefined') {
                            ToastError.fire({
                                text: `Error al imprimir: ${errorMessage}`
                            });
                        }
                    }


                } else {
                    //ToastError.fire({ text: response.error });
                }
            },
            error: function(err) {
                console.log('Ocurrió un error');
            }
        });
    }

    function mostrarIconoConfirmado() {
        // Selecciona todos los elementos con la clase 'subtotal-container'
        const elementos = document.querySelectorAll('.subtotal-container');
        elementos.forEach(el => {
            // Solo agrega el icono si no existe ya en el elemento
            if (!el.querySelector('.bi-check2-square')) {
                el.innerHTML += '\n<i class="bi bi-check2-square" title="Confirmado"></i>';
            }
        });
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
                            argumentos: [`000${venta.type_sale} - ${venta.tipo || 'N/A'} - ${venta.tp || 'N/A'}}\n`]
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
    function toggleDelivery() {
        const checkDelivery = document.getElementById('checkDelivery');
        const camposDelivery = document.getElementById('camposDelivery');
        const typeStatus = document.getElementById('type_status');
        const status = document.getElementById('status');

        if (!checkDelivery || !camposDelivery) {
            console.error('Elementos de delivery no encontrados');
            return;
        }

        if (checkDelivery.checked) {
            // Mostrar campos de delivery
            camposDelivery.classList.remove('d-none');

            // Cambiar valores para delivery: type_status = 2, status = 0
            if (typeStatus) typeStatus.value = 2;
            if (status) status.value = 0;

            // Establecer fecha actual como valor por defecto
            const deliveryDate = document.getElementById('delivery_date');
            const deliveryHour = document.getElementById('delivery_hour');

            if (deliveryDate && !deliveryDate.value) {
                const hoy = new Date();
                const fechaFormateada = hoy.toISOString().split('T')[0];
                deliveryDate.value = fechaFormateada;
            }

            console.log('Delivery activado - type_status: 2, status: 0');
        } else {
            // Ocultar campos de delivery
            camposDelivery.classList.add('d-none');

            // Restaurar valores para venta directa: type_status = 0, status = 1
            if (typeStatus) typeStatus.value = 0;
            if (status) status.value = 1;

            // Limpiar campos de delivery
            const deliveryDateClear = document.getElementById('delivery_date');
            const deliveryHourClear = document.getElementById('delivery_hour');
            const deliveryAddressClear = document.getElementById('delivery_address');
            
            if (deliveryDateClear) deliveryDateClear.value = '';
            if (deliveryHourClear) deliveryHourClear.value = '';
            if (deliveryAddressClear) deliveryAddressClear.value = '';

            console.log('Delivery desactivado - type_status: 0, status: 1');
        }
    }
</script>
@endsection
