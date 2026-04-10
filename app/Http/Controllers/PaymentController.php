<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class PaymentController extends Controller
{

    public function index(Request $request)
    {
        $payments = Payment::with('usuario', 'paymentMethod')
            ->where('estado', 0)
            ->orderBy('fecha', 'desc')
            ->paginate(20);

        return view('payments.index', compact('payments'));
    }


    public function listar(Request $request)
    {
        try {
            $sale_id = $request->sale_id;

            $payments = Payment::where('sale_id', '=',  $sale_id)
                ->orderBy('date')
                ->with('payment_method')
                ->get()
                ->map(function ($payment) {
                    return [
                        'monto' => $payment->subtotal,
                        'fecha' => Carbon::parse($payment->date)->format('Y-m-d'),
                        'metodo_pago' => $payment->payment_method->name,
                    ];
                });

            return response()->json([
                'status' => true,
                'payments' => $payments,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'error' => 'Error al listar pagos: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $sale_id = $request->sale_id;
            $monto = $request->monto;
            $metodo_pago_id = $request->metodo_pago;
            $fecha = $request->fecha;

            $payment = Payment::create([
                'sale_id' => $sale_id,
                'estado' => 0,
                'fecha' => $fecha,
                'monto' => $monto,
                'payment_method_id' => $metodo_pago_id,
            ]);


            $payments = Payment::where('sale_id', '=',  $sale_id)
                ->orderBy('fecha')
                ->with('paymentMethod')
                ->get()
                ->map(function ($payment) {
                    return [
                        'monto' => $payment->monto,
                        'fecha' => $payment->fecha,
                        'metodo_pago' => $payment->paymentMethod->nombre,
                    ];
                });


            return response()->json([
                'status' => true,
                'payments' => $payments,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'error' => 'Error al guardar pago: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function cashClose(Request $request)
    {
        $date = $request->date ? $request->date : now()->toDateString();

        // ¿El usuario es delivery?
        $isDelivery = auth()->user()->hasRole('delivery');

        // Delivery puede consultar cualquier turno (default: mañana=0). Otros: su turno actual.
        if ($isDelivery) {
            $turno = $request->turno !== null ? (int) $request->turno : 0;
        } else {
            $turno = auth()->user()->turno;
        }

        // Sede: delivery no filtra por sede; otros sí
        $sede = $isDelivery ? null : auth()->user()->sede_id;

        // Monto de apertura de caja para la fecha/turno/sede
        $monto = CashClose::where('estado', 0)
            ->where('fecha', $date)
            ->where('turno', $turno)
            ->where('headquarter_id', $sede)
            ->value('monto');

        // Tipos de venta (según si es delivery o no)
        $venta_directa    = $isDelivery ? 2 : 0;
        $venta_anticipada = $isDelivery ? 3 : 1;

        // =========================
        // VENTAS DIRECTAS (totales por método)
        // =========================
        $ventas_payment_methods = PaymentMethod::select('id', 'nombre')
            ->where('estado', 0)
            ->get()
            ->map(function ($method) use ($date, $turno, $sede, $venta_directa, $isDelivery) {
                $total = Payment::where('estado', 0)
                    ->where('payment_method_id', $method->id)
                    ->where('fecha', $date)
                    ->where('user_id', auth()->id())
                    ->where('turno', $turno)
                    ->whereHas('sale', function ($q) use ($sede, $venta_directa, $isDelivery) {
                        $q->where('type_sale', $venta_directa)
                            ->where('estado', 0);
                        if (!$isDelivery) {
                            $q->where('headquarter_id', $sede);
                        }
                    })
                    ->sum('monto');

                $method->total = $total;
                return $method;
            });

        $total_ventas = $ventas_payment_methods->sum('total');

        // =========================
        // ANTICIPADAS (totales por método - todas)
        // =========================
        $anticipadas_payment_methods = PaymentMethod::select('id', 'nombre')
            ->where('estado', 0)
            ->get()
            ->map(function ($method) use ($date, $turno, $sede, $venta_anticipada, $isDelivery) {
                $total = Payment::where('estado', 0)
                    ->where('payment_method_id', $method->id)
                    ->where('fecha', $date)
                    ->where('user_id', auth()->id())
                    ->where('turno', $turno)
                    ->whereHas('sale', function ($q) use ($sede, $venta_anticipada, $isDelivery) {
                        $q->where('type_sale', $venta_anticipada)
                            ->where('estado', 0);
                        if (!$isDelivery) {
                            $q->where('headquarter_id', $sede);
                        }
                    })
                    ->sum('monto');

                $method->total = $total;
                return $method;
            });

        $total_anticipadas = $anticipadas_payment_methods->sum('total');

        // =====================================================
        // ANTICIPADAS DIVIDIDAS: PAGO INICIAL vs PAGOS PENDIENTES
        // =====================================================

        // 1) Pago inicial: es el primer pago (más antiguo) de la venta
        $anticipadas_inicial_payment_methods = PaymentMethod::select('id', 'nombre')
            ->where('estado', 0)
            ->get()
            ->map(function ($method) use ($date, $turno, $sede, $venta_anticipada, $isDelivery) {
                $total = Payment::where('estado', 0)
                    ->where('payment_method_id', $method->id)
                    ->where('fecha', $date)
                    ->where('user_id', auth()->id())
                    ->where('turno', $turno)
                    ->whereHas('sale', function ($q) use ($sede, $venta_anticipada, $isDelivery) {
                        $q->where('type_sale', $venta_anticipada)
                            ->where('estado', 0);
                        if (!$isDelivery) {
                            $q->where('headquarter_id', $sede);
                        }
                    })
                    // Este pago debe ser el PRIMERO de su venta
                    ->whereRaw("
                    payments.id = (
                        SELECT p2.id
                        FROM payments AS p2
                        WHERE p2.sale_id = payments.sale_id
                          AND p2.estado = 0
                        ORDER BY p2.fecha ASC, p2.created_at ASC, p2.id ASC
                        LIMIT 1
                    )
                ")
                    ->sum('monto');

                $method->total = $total;
                return $method;
            });

        $total_anticipadas_iniciales = $anticipadas_inicial_payment_methods->sum('total');

        // 2) Pagos pendientes: cualquier pago de la venta que NO sea el primero
        $anticipadas_pendiente_payment_methods = PaymentMethod::select('id', 'nombre')
            ->where('estado', 0)
            ->get()
            ->map(function ($method) use ($date, $turno, $sede, $venta_anticipada, $isDelivery) {
                $total = Payment::where('estado', 0)
                    ->where('payment_method_id', $method->id)
                    ->where('fecha', $date)
                    ->where('user_id', auth()->id())
                    ->where('turno', $turno)
                    ->whereHas('sale', function ($q) use ($sede, $venta_anticipada, $isDelivery) {
                        $q->where('type_sale', $venta_anticipada)
                            ->where('estado', 0);
                        if (!$isDelivery) {
                            $q->where('headquarter_id', $sede);
                        }
                    })
                    // Este pago NO es el primero de su venta
                    ->whereRaw("
                    payments.id <> (
                        SELECT p2.id
                        FROM payments AS p2
                        WHERE p2.sale_id = payments.sale_id
                          AND p2.estado = 0
                        ORDER BY p2.fecha ASC, p2.created_at ASC, p2.id ASC
                        LIMIT 1
                    )
                ")
                    ->sum('monto');

                $method->total = $total;
                return $method;
            });

        $total_anticipadas_pendientes = $anticipadas_pendiente_payment_methods->sum('total');

        // =====================================================
        // EFECTIVO (directas + anticipadas) - solo método efectivo (id=2)
        // =====================================================
        $efectivo = Payment::where('estado', 0)
            ->where('payment_method_id', 2) // id del método "Efectivo"
            ->where('fecha', $date)
            ->where('user_id', auth()->id())
            ->where('turno', $turno)
            ->whereHas('sale', function ($q) use ($sede, $venta_directa, $venta_anticipada, $isDelivery) {
                $q->whereIn('type_sale', [$venta_directa, $venta_anticipada])
                    ->where('estado', 0);
                if (!$isDelivery) {
                    $q->where('headquarter_id', $sede);
                }
            })
            ->sum('monto');

        // =====================================================
        // GASTOS
        // =====================================================
        $gastos = Expense::with('details')
            ->where('estado', 0)
            ->where('date', $date)
            ->where('turno', $turno)
            ->where('sede_id', $sede)
            ->get()
            ->flatMap(fn($e) => $e->details)
            ->sum('subtotal');

        // Delivery no descuenta gastos
        if ($isDelivery) {
            $gastos = 0;
        }

        $saldo = $efectivo - $gastos;

        return view('cashClose.index', compact(
            'efectivo',
            'saldo',
            'ventas_payment_methods',
            'anticipadas_payment_methods',
            'total_ventas',
            'total_anticipadas',
            'date',
            'monto',
            'turno',
            'isDelivery',
            // Nuevos arreglos/totales de anticipadas divididas
            'anticipadas_inicial_payment_methods',
            'anticipadas_pendiente_payment_methods',
            'total_anticipadas_iniciales',
            'total_anticipadas_pendientes'
        ));
    }


    public function cashCloseHistory(Request $request)
    {
        // Filtros del formulario
        $date   = $request->input('date', now()->toDateString());
        $turno  = $request->input('turno');
        $sede   = $request->input('headquarter_id');
        $userId = $request->input('user_id');

        // Catálogos para selects
        $sedes = Headquarters::where('estado', 0)->orderBy('nombre')->get();

        $usuarios = Usuario::orderBy('nombre')
            ->when($sede, function ($q) use ($sede) {
                $q->where(function ($qq) use ($sede) {
                    $qq->where('sede_id', $sede)
                        ->orWhereNull('sede_id');
                })
                    ->where('rol_id', '!=', 5);
            }, function ($q) {
                $q->where('rol_id', 5);
            })
            ->get();

        $todosLosUsuarios = Usuario::orderBy('nombre')->get();

        // IDs de tipo de venta (ajusta si en tu app son otros)
        $VENTA_DIRECTA    = 0;
        $VENTA_ANTICIPADA = 1;

        // Ventas directas por método
        $ventas_payment_methods = PaymentMethod::select('id', 'nombre')
            ->where('estado', 0)
            ->get()
            ->map(function ($method) use ($date, $turno, $sede, $userId, $VENTA_DIRECTA) {
                $total = Payment::where('estado', 0)
                    ->where('payment_method_id', $method->id)
                    ->whereDate('fecha', $date)
                    ->when($turno !== null && $turno !== '', fn($q) => $q->where('turno', (int)$turno))
                    ->when($userId, fn($q) => $q->where('user_id', $userId))
                    ->whereHas('sale', function ($q) use ($VENTA_DIRECTA, $sede) {
                        $q->where('type_sale', $VENTA_DIRECTA)
                            ->where('estado', 0)
                            ->when($sede, fn($qq) => $qq->where('headquarter_id', $sede));
                    })
                    ->sum('monto');
                $method->total = $total;
                return $method;
            });

        $total_ventas = $ventas_payment_methods->sum('total');

        // Anticipadas por método
        $anticipadas_payment_methods = PaymentMethod::select('id', 'nombre')
            ->where('estado', 0)
            ->get()
            ->map(function ($method) use ($date, $turno, $sede, $userId, $VENTA_ANTICIPADA) {
                $total = Payment::where('estado', 0)
                    ->where('payment_method_id', $method->id)
                    ->whereDate('fecha', $date)
                    ->when($turno !== null && $turno !== '', fn($q) => $q->where('turno', (int)$turno))
                    ->when($userId, fn($q) => $q->where('user_id', $userId))
                    ->whereHas('sale', function ($q) use ($VENTA_ANTICIPADA, $sede) {
                        $q->where('type_sale', $VENTA_ANTICIPADA)
                            ->where('estado', 0)
                            ->when($sede, fn($qq) => $qq->where('headquarter_id', $sede));
                    })
                    ->sum('monto');
                $method->total = $total;
                return $method;
            });

        $total_anticipadas = $anticipadas_payment_methods->sum('total');

        // Efectivo (método 2) sumando directas + anticipadas
        $efectivo = Payment::where('estado', 0)
            ->where('payment_method_id', 2)
            ->whereDate('fecha', $date)
            ->when($turno !== null && $turno !== '', fn($q) => $q->where('turno', (int)$turno))
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->whereHas('sale', function ($q) use ($VENTA_DIRECTA, $VENTA_ANTICIPADA, $sede) {
                $q->whereIn('type_sale', [$VENTA_DIRECTA, $VENTA_ANTICIPADA])
                    ->where('estado', 0)
                    ->when($sede, fn($qq) => $qq->where('headquarter_id', $sede));
            })
            ->sum('monto');

        // Gastos del día/turno/sede
        $gastos = Expense::with('details')
            ->where('estado', 0)
            ->whereDate('date', $date)
            ->when($turno !== null && $turno !== '', fn($q) => $q->where('turno', (int)$turno))
            ->when($sede, fn($q) => $q->where('sede_id', $sede))
            ->get()
            ->flatMap->details
            ->sum('subtotal');

        $saldo = $efectivo - $gastos;

        // Monto registrado (si manejas por sede/usuario)
        $monto = CashClose::where('estado', 0)
            ->whereDate('fecha', $date)
            ->when($turno !== null && $turno !== '', fn($q) => $q->where('turno', (int)$turno))
            ->when($sede,   fn($q) => $q->where('headquarter_id', $sede))
            ->when($userId, fn($q) => $q->where('usuario_id', $userId))
            ->value('monto');

        return view('cashClose.historico', compact(
            'date',
            'turno',
            'sede',
            'userId',
            'sedes',
            'usuarios',
            'ventas_payment_methods',
            'anticipadas_payment_methods',
            'total_ventas',
            'total_anticipadas',
            'efectivo',
            'gastos',
            'saldo',
            'monto',
            'todosLosUsuarios'
        ));
    }


    public function storeCashClose(Request $request)
    {
        try {
            $isDelivery = auth()->user()->hasRole('delivery');

            // Delivery no puede guardar cierres de caja
            if ($isDelivery) {
                return response()->json([
                    'status' => false,
                    'error' => 'Los usuarios delivery no pueden guardar cierres de caja.'
                ], 403);
            }

            $fecha = $request->fecha;
            $monto = $request->monto;
            $turno = auth()->user()->turno; // Usuario normal siempre usa su turno
            $usuario_id = auth()->user()->id;
            $headquarter_id = auth()->user()->sede_id;

            $cierre = CashClose::updateOrCreate(
                [
                    'fecha' => $fecha,
                    'turno' => $turno,
                    'headquarter_id' => $headquarter_id,
                ],
                [
                    'usuario_id' => $usuario_id,
                    'monto' => $monto,
                    'estado' => 0,
                ]
            );

            return response()->json([
                'status' => true,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'error' => 'Error al guardar cierre: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function cashClosePDF(Request $request)
    {
        try {
            Log::info('cashClosePDF recibe:', $request->all());
            $request->validate([
                'user_id' => 'nullable|exists:users,id',
                'turno' => 'nullable|numeric|in:0,1',
                'headquarter_id' => 'nullable|exists:headquarters,id',
                'tabla' => 'required',
                'date' => 'required|date',
                'monto' => 'nullable|numeric'
            ]);

            $user_id = $request->user_id ?? auth()->user()->id;
            $user = Usuario::find($user_id)->nombre;
            $turn = $request->turno ?? auth()->user()->turno;
            if ($turn === 0) {
                $turno = 'mañana';
            } else {
                $turno = 'tarde';
            }
            $headquarter_id = $request->headquarter_id ?? auth()->user()->sede_id;
            $headquarter = Headquarters::find($headquarter_id)->nombre;
            $tabla = $request->tabla;
            $fecha = $request->date;
            $monto = $request->monto ?? "No registrado";

            $pdf = Pdf::loadView('cashClose.pdf', compact('user', 'turno', 'headquarter', 'tabla', 'fecha', 'monto'));

            // Descargar el archivo PDF
            return $pdf->download('Cierre.pdf');
        } catch (\Throwable $e) {
            Log::error('Error generando PDF: ' . $e->getMessage());
            return response('Error generando PDF: ' . $e->getMessage(), 500);
        }
    }
}
