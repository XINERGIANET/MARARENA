<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\CashClose;
use App\Models\Product;
use App\Models\SaleDetail;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class CashCloseController extends Controller
{

    
    public function index(Request $request)
    {
        // 
    }


    public function create(Request $request)
    {
        $date = $request->date ? $request->date : now()->toDateString();
        $shift = auth()->user()->shift;

        // Monto de apertura de caja para la fecha/turno/sede
        $monto = CashClose::where('deleted', 0)
            ->where('date', $date)
            ->where('shift', $shift)
            ->where('user_id', auth()->id())
            ->value('amount');


        $ventas_payment_methods = PaymentMethod::select('id', 'name')
            ->where('deleted', 0)
            ->get()
            ->map(function ($method) use ($date, $shift) {
                $total = Payment::where('deleted', 0)
                    ->where('payment_method_id', $method->id)
                    ->where('date', $date)
                    ->where('user_id', auth()->id())
                    ->where('shift', $shift)
                    ->whereHas('sale', function ($q) {
                        $q->where('deleted', 0);
                    })
                    ->sum('subtotal');

                $method->total = $total;
                return $method;
            });

        $total_ventas = $ventas_payment_methods->sum('total');
       
        $efectivo = Payment::where('deleted', 0)
            ->where('date', $date)
            ->where('user_id', auth()->id())
            ->where('shift', $shift)
            ->whereHas('payment_method', function ($q) {
                $q->whereRaw('UPPER(name) like "%EFECTIVO%"');
            })
            ->whereHas('sale', function ($q) {
                $q->where('deleted', 0);
            })
            ->sum('subtotal');

        // detalle acumulado de productos
        // $details = Product::selectRaw('products.name as product,
        //     COALESCE(SUM(sale_details.quantity), 0) AS quantity,
        //     COALESCE(SUM(sale_details.subtotal), 0) AS subtotal')
        //     ->leftJoin('sale_details', function ($join) {
        //         $join->on('products.id', '=', 'sale_details.product_id')
        //             ->where('sale_details.deleted', '=', 0)
        //             ->whereIn('sale_details.sale_id', function ($query) {
        //                 $query->select('sales.id')
        //                     ->from('sales')
        //                     ->where('sales.shift', 0)
        //                     ->whereRaw('DATE(sales.date) = ?', ['2025-10-13'])
        //                     ->where('sales.user_id', 2)
        //                     ->where('sales.deleted', 0);
        //             });
        //     })
        //     ->where('products.deleted', 0)
        //     ->groupBy('products.name')
        //     ->orderBy('products.name')
        //     ->get();

        $sales = Sale::with(['sale_details.product']) // asume relación sale_details y sale_details->product
            ->where('deleted', 0)
            ->whereDate('date', $date)
            ->where('shift', $shift)
            ->get()
            ->map(function ($sale) {
                return [
                    'id' => $sale->id,
                    'number' => $sale->number ?? null,
                    'date' => $sale->date,
                    'total' => (float) $sale->total,
                    'type_sale' => (int) $sale->type_sale,
                    'details_count' => $sale->sale_details->sum('quantity'),
                    'details' => $sale->sale_details->map(function ($d) {
                        return [
                            'id' => $d->id,
                            'product_id' => $d->product_id,
                            'product_name' => optional($d->product)->name ?? ($d->product_name ?? null),
                            'quantity' => (float) $d->quantity,
                            'subtotal' => (float) $d->subtotal,
                            'unit_price' => (float) ($d->product_price ?? $d->unit_price ?? 0),
                        ];
                    })->values()
                ];
            });

        // Mapear los keys 0/1 a etiquetas legibles si prefieres
        $labels = [
            0 => 'ropa',
            1 => 'cafeteria'
        ];

        $grouped_sales = $sales->groupBy('type_sale')->mapWithKeys(function ($group, $type) use ($labels) {
            $key = $labels[(int)$type] ?? "type_{$type}";
            $salesCollection = $group->values();

            // total de ventas (suma campo total) para la línea
            $salesTotal = (float) $salesCollection->sum('total');

            // total de detalles (suma subtotal de todos los detalles) para la línea
            $detailsTotal = (float) $salesCollection->flatMap(function ($sale) {
                return $sale['details'] ?? [];
            })->sum('subtotal');

            return [$key => [
                'sales' => $salesCollection,
                'totals' => [
                    'sales_total' => $salesTotal,
                    'details_total' => $detailsTotal,
                ]
            ]];
        });

        return view('cash_close.create', compact(
            'efectivo',
            'ventas_payment_methods',
            'total_ventas',
            'date',
            'monto',
            'shift',
            // 'details',
            'grouped_sales'
        ));
    }

    
    public function store(Request $request)
    {
        try {

            $date = $request->date;
            $amount = $request->amount;
            $shift = auth()->user()->shift;
            $user_id = auth()->user()->id;

            $cierre = CashClose::updateOrCreate(
                [
                    'date' => $date,
                    'shift' => $shift,
                    'user_id' => $user_id,
                    'deleted' => 0,
                ],
                [
                    'amount' => $amount,
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
    public function show($id)
    {

    }

    public function edit($id)
    {}

    public function update(Request $request, $id)
    {}

    public function destroy($id)
    {}



    public function pdf(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'nullable|exists:users,id',
                'turno' => 'nullable|numeric|in:0,1',
                'headquarter_id' => 'nullable|exists:headquarters,id',
                'tabla' => 'required',
                'date' => 'required|date',
                'monto' => 'nullable|numeric'
            ]);

            $user_id = $request->user_id ?? auth()->user()->id;
            $user = User::find($user_id)->name;
            $shift = auth()->user()->shift;
            if ($shift === 0) {
                $turno = 'mañana';
            } else {
                $turno = 'tarde';
            }
            $tabla = $request->tabla;
            // $details = array_map(fn($d) => is_object($d) ? $d : (object)$d, $request->details);
            $fecha = $request->date;
            $monto = $request->monto ?? "No registrado";
            $efectivo = $request->efectivo ?? 0;
            $diferencia = $monto - $efectivo;

            $sales = Sale::with(['sale_details.product']) // asume relación sale_details y sale_details->product
                ->where('deleted', 0)
                ->whereDate('date', $fecha)
                ->where('shift', $shift)
                ->get()
                ->map(function ($sale) {
                    return [
                        'id' => $sale->id,
                        'number' => $sale->number ?? null,
                        'date' => $sale->date,
                        'total' => (float) $sale->total,
                        'type_sale' => (int) $sale->type_sale,
                        'details_count' => $sale->sale_details->sum('quantity'),
                        'details' => $sale->sale_details->map(function ($d) {
                            return [
                                'id' => $d->id,
                                'product_id' => $d->product_id,
                                'product_name' => optional($d->product)->name ?? ($d->product_name ?? null),
                                'quantity' => (float) $d->quantity,
                                'subtotal' => (float) $d->subtotal,
                                'unit_price' => (float) ($d->product_price ?? $d->unit_price ?? 0),
                            ];
                        })->values()
                    ];
                });

            // Mapear los keys 0/1 a etiquetas legibles si prefieres
            $labels = [
                0 => 'ropa',
                1 => 'cafeteria'
            ];

            $grouped_sales = $sales->groupBy('type_sale')->mapWithKeys(function ($group, $type) use ($labels) {
                $key = $labels[(int)$type] ?? "type_{$type}";
                $salesCollection = $group->values();

                // total de ventas (suma campo total) para la línea
                $salesTotal = (float) $salesCollection->sum('total');

                // total de detalles (suma subtotal de todos los detalles) para la línea
                $detailsTotal = (float) $salesCollection->flatMap(function ($sale) {
                    return $sale['details'] ?? [];
                })->sum('subtotal');

                return [$key => [
                    'sales' => $salesCollection,
                    'totals' => [
                        'sales_total' => $salesTotal,
                        'details_total' => $detailsTotal,
                    ]
                ]];
            });
    
            $pdf = Pdf::loadView('cash_close.pdf', compact(
                'user', 
                'turno', 
                'tabla', 
                'fecha', 
                'monto',
                'efectivo',
                'diferencia',
                // 'details',
                'grouped_sales'
            ));
            
            return $pdf->download('Cierre.pdf');
        } catch (\Throwable $e) {
            Log::error('Error generando PDF: ' . $e->getMessage());
            return response('Error generando PDF: ' . $e->getMessage(), 500);
        }
    }
}
