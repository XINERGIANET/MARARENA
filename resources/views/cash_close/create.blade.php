@extends('layouts.app')

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/css/jquery-ui.min.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/css/hope-ui.min.css') }}" />

<style>
.tables-row {
  display: flex;
  gap: 2rem;
  flex-wrap: wrap;
}

</style>

@endsection


@section('header')
<h2>Registro de Cierre de Caja</h2>
<p>Ingresar Cierre de Caja</p>
@endsection


@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <!-- <button class="btn btn-primary mb-4" id="btn-print">Imprimir</button> -->
                    <form class="mb-4" id="date-form">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Fecha</label>
                                    <input type="date" class="form-control" name="date" id="date"
                                        value="{{ request()->date ? request()->date : now()->format('Y-m-d') }}">
                                </div>
                            </div>
                        </div>
                    </form>
                          
                    <form action="{{ route('cash_close.store') }}" id="store-cash-close-form">
                        <label for="monto" class="form-label">Monto: </label>
                        <div class="input-group mb-3" style="width: 22rem;">
                            <input type="number" class="form-control form-control-sm" placeholder="Ingrese un monto" name="amount" id="amount" value="{{ $monto ?? '' }}" step="0.01">
                            <button type="submit" class="btn btn-primary" >Guardar</button>
                        </div>
                    </form>
                    
                    <!-- <label class="d-block my-4">Turno: {{ $shift == 0 ? 'Mañana' : 'Tarde' }}</label> -->
                    
                    <div class="tables-row">

                        <div class="table-responsive">              
                            <table class="table table-bordered table-sm w-auto" id="cash-table">
                                <thead>
                                    <tr>
                                        <th colspan="2" class="text-center">Ventas de cafetería</th>
                                    </tr>
                                    <tr>
                                        <th>Método de pago</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- VENTAS DIRECTAS (no se renderizan si es delivery) --}}
                                        @foreach ($ventas_payment_methods as $payment_method)
                                        <tr>
                                            <td>Ventas | {{ ucfirst(strtolower($payment_method->name)) }}</td>
                                            <td align="right">{{ number_format($payment_method->total, 2) }}</td>
                                        </tr>
                                        @endforeach
                                        <tr>
                                            <td><strong>TOTAL</strong></td>
                                            <td align="right"><strong>{{ number_format($total_ventas, 2) }}</strong></td>
                                        </tr>

                                    @php
                                        // Consolidado por método: Ventas + Inicial + Pendientes
                                        $totales_por_metodo = [];

                                        // Ventas directas (si aplica)
                                        foreach ($ventas_payment_methods as $pm) {
                                            $nombre = ucfirst(strtolower($pm->nombre));
                                            $totales_por_metodo[$nombre] = ($totales_por_metodo[$nombre] ?? 0) + $pm->total;
                                        }

                                        // Gran total
                                        $gran_total = array_sum($totales_por_metodo);
                                    @endphp

                                    <!-- <tr>
                                        <td class="fw-bold" colspan="2">Efectivo = {{ number_format($efectivo, 2) }}</td>
                                    </tr> -->
                                </tbody>
                            </table>

                        </div>
                    
                    </div>
                    

                    <button class="btn me-2 mt-3 btn-success" id="btnGuardar">Imprimir</button>

                    <button type="button" class="btn me-2 mt-3 btn-danger" id="btnPDF">
                        <i class="fas fa-file-pdf"></i> PDF
                    </button>
                  
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
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

            async imprimirEn(nombreImpresora) {
                const payload = {
                    operaciones: this.operaciones,
                    nombreImpresora,
                    serial: this.serial,
                };
                const response = await fetch(this.ruta + "/imprimir", {
                    method: "POST",
                    // headers: {
                    //    'Content-Type': 'application/json; charset=utf-8'
                    // },
                    body: JSON.stringify(payload),
                });
                return await response.json();
            }
        }
        return ConectorPlugin;
    })();
</script>
<script>
    var serial = '{{ config('printer.serial') }}';
    var saldoEfectivo = {{ $efectivo ?? 0 }};
    var turno = {{ $turno ?? 0 }};
    var isDelivery = false;
    // var details = {{-- @json($details); --}}
    var sales = @json($grouped_sales);
    console.log(sales);

    // Función de impresión reutilizable
    async function imprimirCierreCaja() {
        try{
            const IP_COMPUTADORA_REMOTA = "192.168.18.46"; // Cambiar por la IP de tu computadora con impresora
            const PUERTO_REMOTO = "8000";
            const URL_REMOTA = `http://${IP_COMPUTADORA_REMOTA}:${PUERTO_REMOTO}`;
            
            // Parámetros para ConectorPluginV3
            const licence = serial;
            const conector = new ConectorPluginV3(ConectorPluginV3.URL_PLUGIN_POR_DEFECTO, licence);
            await conector.Iniciar();
            

            // Función para crear encabezado común
            const crearEncabezado = (conector, fecha) => {
                const ahora = new Date();
                const fechaFormateada = ahora.getFullYear() + '-' +
                    String(ahora.getMonth() + 1).padStart(2, '0') + '-' +
                    String(ahora.getDate()).padStart(2, '0') + ' ' +
                    String(ahora.getHours()).padStart(2, '0') + ':' +
                    String(ahora.getMinutes()).padStart(2, '0');

                return conector
                    .EstablecerTamañoFuente(1, 1) // Aumentar tamaño de fuente 2x ancho y 2x alto
                    .EstablecerAlineacion(1)
                    .EstablecerEnfatizado(true)
                    .EscribirTexto("Mararena - Cierre de caja\n")
                    .EstablecerAlineacion(0)
                    .EscribirTexto(`Fecha: ${fecha}\n`)
                    .EscribirTexto(`Usuario: {{ auth()->user()->email }}\n`)
                    // .EscribirTexto(`Turno: ${turno == 0 ? "Mañana" : "Tarde"}\n`)
                    .EscribirTexto(`F. impresion: ${fechaFormateada}\n`)
                    .EscribirTexto(`\n`)
                    .Feed(1)
                    .EscribirTexto("Met.: Total\n")
                    .EscribirTexto(`\n`);
            };

            // Función para agregar productos
            const agregarMontos = (impresionTexto) => {
                document.querySelectorAll('table.table-bordered tbody tr').forEach(row => {
                    const celdas = row.querySelectorAll('td');
                    if (celdas.length > 0) {
                        let linea = '';
                        celdas.forEach(td => {
                            linea += td.innerText.trim() + '  '; // separador entre columnas
                        });
                        
                        const primerValor = linea.split(" ")[0];
                        
                        // Activar negrita para líneas importantes
                        if (primerValor === "TOTAL" || linea.includes("Efectivo") || primerValor === "Efectivo") {
                            impresionTexto = impresionTexto.EstablecerEnfatizado(true);
                        } else {
                            impresionTexto = impresionTexto.EstablecerEnfatizado(true);
                        }
                        
                        impresionTexto = impresionTexto.EscribirTexto(linea.slice(0, -1).trim() + '\n');

                        if (primerValor === "TOTAL") {
                            impresionTexto = impresionTexto.EscribirTexto("--------------------------------\n");
                        }
                    }
                });
                impresionTexto = impresionTexto.EscribirTexto('\n');
                return impresionTexto;
            };

            // Función para crear pie de documento
            const crearPie = (impresionTexto, efectivo) => {
                let textoValidez = "Efectivo = VEN + ANT - EGR";
                let real = parseFloat(document.getElementById('amount').value) || 0;
                let diferencia = real - efectivo;
                let textoFinal = "";
                return impresionTexto
                    .Feed(1)
                    .EstablecerTamañoFuente(1, 1) // Aumentar tamaño de fuente 2x ancho y 2x alto
                    .EstablecerAlineacion(1)
                    .EstablecerEnfatizado(true)
                    .EscribirTexto(`Real     = S/${real.toFixed(2)}\n`)
                    .EscribirTexto(`Diferen. = S/${diferencia.toFixed(2)}\n`)
                    .EscribirTexto("--------------------------------\n");
                    // .TextoSegunPaginaDeCodigos(2, "cp850", "Elaborado por Xinergia de Corporación XPANDE\n")
                    // .Pulso(48, 60, 120)
                    // .Corte(1);
            };

            // const crearDetalles = (impresionTexto, details) => {
            //     details = details || window.details || [];
            //     if (!Array.isArray(details)) details = Object.values(details);

            //     // Cabecera de columnas (opcional)
            //     impresionTexto = impresionTexto
            //         .EstablecerAlineacion(0)
            //         .EstablecerEnfatizado(true)
            //         .EscribirTexto("Producto                     Cant  Subtotal\n")
            //         .EscribirTexto("------------------------------------------\n")
            //         .EstablecerEnfatizado(false);

            //     // Por cada detalle imprimir nombre, cantidad y subtotal
            //     details.forEach(d => {
            //         const qty = Number(d.quantity ?? d.qty ?? 0);
            //         if (qty >= 1) {
            //             const name = (d.product_name || d.product || d.name || "").toString().slice(0, 25).padEnd(25, ' ');
            //             const subtotal = Number(d.subtotal ?? d.total ?? 0);
            //             const line = `${name} ${String(qty).padStart(4, ' ')}  ${subtotal.toFixed(2).padStart(7, ' ')}`;
            //             impresionTexto = impresionTexto.EscribirTexto(line + '\n');
            //         }
            //     });

            //     return impresionTexto
            //         .Feed(1)
            //         .EstablecerTamañoFuente(1, 1) // Aumentar tamaño de fuente 2x ancho y 2x alto
            //         .EstablecerAlineacion(1)
            //         .EstablecerEnfatizado(true)
            //         .TextoSegunPaginaDeCodigos(2, "cp850", "Elaborado por Xinergia de Corporación XPANDE\n")
            //         .Pulso(48, 60, 120)
            //         .Corte(1);
            // };


            const crearDetallesPorLinea = (impresionTexto, sales) => {
                sales = sales || window.sales || {};
                const humanize = s => String(s).replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());

                // Normalizar y preservar totales si vienen en la estructura
                let grouped = {};

                if (Array.isArray(sales)) {
                    const salesArr = sales;
                    const salesTotal = salesArr.reduce((acc, s) => acc + (Number(s.total) || 0), 0);
                    grouped['Ventas'] = { sales: salesArr, totals: { sales_total: salesTotal } };
                } else {
                    Object.entries(sales).forEach(([k, v]) => {
                        // caso: v = { sales: [...], totals: {...} } (como lo arma el controller)
                        if (v && Array.isArray(v.sales)) {
                            grouped[k] = {
                                sales: v.sales,
                                totals: v.totals || {
                                    sales_total: (v.sales.reduce((acc, s) => acc + (Number(s.total) || 0), 0))
                                }
                            };
                        } else if (Array.isArray(v)) {
                            const arr = v;
                            grouped[k] = {
                                sales: arr,
                                totals: { sales_total: arr.reduce((acc, s) => acc + (Number(s.total) || 0), 0) }
                            };
                        } else {
                            // otros formatos: convertir a array y calcular totales
                            const arr = Array.isArray(Object.values(v)) ? Object.values(v) : [];
                            grouped[k] = {
                                sales: arr,
                                totals: { sales_total: arr.reduce((acc, s) => acc + (Number(s.total) || 0), 0) }
                            };
                        }
                    });
                }

                // Imprimir por línea: nombre de línea = S/total
                Object.entries(grouped).forEach(([lineName, data]) => {
                    const totalLinea = Number(data.totals?.sales_total || 0).toFixed(2);
                    impresionTexto = impresionTexto
                        .EstablecerAlineacion(0)
                        .EstablecerEnfatizado(true)
                        .EscribirTexto(`${humanize(lineName)} = S/${totalLinea}\n`)
                        .EstablecerEnfatizado(false);

                    (data.sales || []).forEach(venta => {
                        const num = venta.number || venta.id || 'N/A';
                        impresionTexto = impresionTexto.EscribirTexto(`${num}\n`);

                        (venta.details || []).forEach(det => {
                            const prod = String(det.product_name || det.product || det.producto || '').slice(0, 30).trim();
                            const qty = Number(det.quantity ?? det.cantidad ?? 0);
                            const sub = Number(det.subtotal ?? det.total ?? 0).toFixed(2);
                            impresionTexto = impresionTexto.EscribirTexto(`  ${prod} - ${qty} - S/${sub}\n`);
                        });

                        // separación entre ventas
                        impresionTexto = impresionTexto.EscribirTexto('\n');
                    });

                    // separador entre líneas
                    impresionTexto = impresionTexto.EscribirTexto('-----------------------------------------------\n');
                });

                return impresionTexto
                    .Feed(1)
                    .EstablecerTamañoFuente(1, 1)
                    .EstablecerAlineacion(1)
                    .EstablecerEnfatizado(true)
                    .TextoSegunPaginaDeCodigos(2, "cp850", "Elaborado por Xinergia de Corporación XPANDE\n")
                    .Pulso(48, 60, 120)
                    .Corte(1);
            };

            // Función para obtener nombre de impresora según tipo
            const obtenerImpresora = () => {
                return "Ticketera";
            };

            const imprimirDocumentoAutomatico = async (impresionTexto) => {
                const nombreImpresora = obtenerImpresora();
                const tipoDocumentoTexto = "cierre de caja"

                let resultado = null;
                let impresionLocal = false;
                let impresionRemota = false;

                try {
                    // PASO 1: Intentar impresión local primero
                    console.log('Intentando impresión local...');
                    resultado = await conector.imprimirEn(nombreImpresora);
                    
                    if (resultado && resultado.ok) {
                        impresionLocal = true;
                        console.log('Impresión local exitosa');
                        ToastMessage.fire({
                            text:` - ${tipoDocumentoTexto} impreso/a localmente`
                        })
                        return; // Salir si la impresión local fue exitosa
                    } else {
                        console.log('Impresión local falló, intentando remota...');
                    }
                } catch (errorLocal) {
                    console.log('Error en impresión local:', errorLocal.message);
                }

                try {
                    // PASO 2: Si falla la impresión local, intentar remota
                    const urlRemotaCompleta = `${URL_REMOTA}/imprimir`;
                    resultado = await conector.imprimirEnImpresoraRemota(nombreImpresora, urlRemotaCompleta);
                    
                    if (resultado && resultado.ok) {
                        impresionRemota = true;
                        console.log('Impresión remota exitosa');
                        ToastMessage.fire({
                            text: ` - ${tipoDocumentoTexto} impreso/a remotamente (fallback)`
                        });
                        return; // Salir si la impresión remota fue exitosa
                    } else {
                        console.log('Impresión remota también falló');
                    }
                } catch (errorRemoto) {
                    console.log('Error en impresión remota:', errorRemoto.message);
                }

                // PASO 3: Si ambas fallaron, mostrar error
                const mensajeError = resultado && resultado.message ? resultado.message : "No se pudo conectar con ninguna impresora";
                ToastMessage.fire({
                    icon: 'warning',
                    text: ` - Error al imprimir ${tipoDocumentoTexto}: ${mensajeError}. Se intentó impresión local y remota.`
                });
            };

            // Procesar según tipo de comprobante
            let impresionTexto;
            let tipoDocumentoCompleto = "cierre de caja";
            let tipoComprobante = "wincha";
            let fecha = document.getElementById('date').value;

            // Crear documento según tipo
            if (tipoComprobante === 'wincha') {
                impresionTexto = crearEncabezado(conector, fecha);
                impresionTexto = agregarMontos(impresionTexto);
                // impresionTexto = crearPie(impresionTexto, saldoEfectivo);
                // impresionTexto = crearDetalles(impresionTexto, details);
                impresionTexto = crearDetallesPorLinea(impresionTexto, sales);
                await imprimirDocumentoAutomatico(impresionTexto);
            }
            else {
                // Tipo de comprobante no reconocido
                ToastMessage.fire({
                    text: ' - Venta registrada sin impresión (tipo no reconocido)'
                })
            }
        } catch (error) {
            console.error('Error al imprimir:', error);
            ToastMessage.fire({
                icon: 'error',
                text: 'Error al imprimir: ' + error.message
            });
        }
    }

    // Event listener para el botón imprimir
    document.getElementById('btnGuardar').addEventListener('click', async (event) => {
        event.preventDefault();
        await imprimirCierreCaja();
    });

    // Formulario de guardar cierre de caja
    $('#store-cash-close-form').on('submit', function(e) {
        e.preventDefault();
        
        spinner.classList.remove('spinner-visible');
        spinner.classList.add('spinner-hidden');

        let amount = $('#amount').val();
        let date = $('#date').val();

        $.ajax({
            url: "{{ route('cash_close.store') }}",
            method: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                amount: amount,
                date: date
            },
            success: function(response) {
                if(response.status) {
                    ToastMessage.fire({ text: 'Cierre guardado correctamente' });
                    $(`.table-responsive`).removeClass('d-none');
                    // Ejecutar impresión automáticamente después de guardar
                    setTimeout(() => {
                        imprimirCierreCaja();
                    }, 500);
                } else {
                    ToastMessage.fire({ icon: 'error', text: response.error || 'Error al guardar cierre' });
                }
            },
            error: function(xhr) {
                ToastMessage.fire({ icon: 'error', text: 'Error al guardar cierre' });
            }
        }).always(function() {
            spinner.classList.add('spinner-hidden');
            spinner.classList.remove('spinner-visible');
        });
    });

    $('#date').on('change', function() {
        $('#date-form').submit();
    });

    document.getElementById('btnPDF').addEventListener('click', function() {
        // Obtener fecha y monto
        const fecha = document.getElementById('date').value;
        const monto = parseFloat(document.getElementById('amount').value) || 0;
    
        // Obtener datos de la tabla
        const rows = Array.from(document.querySelectorAll('#cash-table tbody tr'));
        const tabla = rows.map(row => {
            return Array.from(row.querySelectorAll('td')).map(td => td.innerText.trim());
        });
    
        // Enviar los datos por POST a una ruta que genere el PDF
        fetch("{{ route('cash_close.pdf') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({
                date: fecha,
                monto: monto,
                efectivo: saldoEfectivo, // Agregar el total del sistema
                tabla: tabla,
                // details: details,
            })
        })
        .then(response => response.blob())
        .then(blob => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'Cierre.pdf';
            document.body.appendChild(a);
            a.click();
            a.remove();
            window.URL.revokeObjectURL(url);
        })
        .catch(error => {
            console.log(error);
            ToastMessage.fire({ icon: 'error', text: 'Error al generar PDF' });
        });
    });

</script>
@endsection