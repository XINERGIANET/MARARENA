<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\PaymentMethod;
use App\Models\Supplier;
use App\Models\Product;
use App\Exports\PurchasesExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $suppliers = Supplier::where('deleted', 0)->get();
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        $supplier_id = $request->supplier_id;
        if($supplier_id){
            $supplier_name = Supplier::find($supplier_id)->company_name;
            $request->merge(['supplier_name' => $supplier_name]);
        }
        
        $productId = $request->product_id;
        if($productId){
            $product_name = Product::find($productId)->name;
            $request->merge(['product_name' => $product_name]);
        }

        // COMPRAS
        $query = Purchase::with('purchase_details','payment_method')
            ->when($start_date, fn($q) => $q->whereDate('date', '>=', $start_date))
            ->when($end_date, fn($q) => $q->whereDate('date', '<=', $end_date))
            ->when($supplier_id, fn($q) => $q->where('supplier_id', $supplier_id))
            ->when($productId, function($q) use ($productId){
                $q->whereHas('purchase_details', function ($q2) use ($productId){
                    $q2->where('product_id','=', $productId);
                });
            })
            ->orderBy('date', 'desc');

        $purchases = $query->paginate(30);

        $allPurchases = $query->get();

        $total = $allPurchases->where('deleted',0)
            ->sum(function ($purchase){
                return $purchase->total;
            });

        $paymentMethods = PaymentMethod::where('deleted', 0)->get();

        $products = Product::where('deleted', 0)
            ->get();

        return view('purchases.index', [
            'tipo' => 'compra',
            'purchases' => $purchases,
            'total' => $total,
            'suppliers' => $suppliers,
            'paymentMethods' => $paymentMethods,
            'products' => $products
        ]);
        
    }

    public function create(Request $request)
    {
        $paymentMethods = PaymentMethod::where('deleted', 0)->get();
        $suppliers = Supplier::select('id', 'company_name')->where('deleted', 0)->get();
        $products = Product::where('deleted', 0)
            ->get();

        return view('purchases.create', compact('paymentMethods', 'suppliers', 'products'));

    }

    public function store(Request $request)
    {
        $details = json_decode($request->input('products'), true);

        $validator = Validator::make(array_merge($request->all(), ['details' => $details]), [
            'voucher_type'           => 'nullable|numeric|min:1',
            'invoice_number'         => 'nullable|string',
            'payment_method_id'      => 'nullable|exists:payment_methods,id',
            'date'                   => 'required|date',
            'supplier_id'            => 'nullable|exists:suppliers,id',
            'details'                => 'required|array|min:1',
            'details.*.quantity'     => 'required|numeric|min:0.01',
            'details.*.unit_price'   => 'required|numeric|min:0',
            'details.*.subtotal'     => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'error'  => $validator->errors()->first(),
            ], 400);
        }

        DB::beginTransaction();

        try {
            $processedDetails = [];

            foreach ($details as $detail) {
                if ($detail['quantity'] <= 0) continue;

                $productId = $detail['product_id'];


                $processedDetails[] = [
                    'product_id'  => $productId,
                    'quantity'    => $detail['quantity'],
                    'unit_price'  => $detail['unit_price'],
                    'subtotal'    => $detail['subtotal'],
                ];
            }

            if (empty($processedDetails)) {
                throw new \Exception('No hay detalles válidos para procesar');
            }

            $compra = Purchase::create([
                'voucher_type'   => $request->voucher_type,
                'invoice_number'     => $request->invoice_number,
                'payment_method_id'  => $request->payment_method_id,
                'date'               => $request->date,
                'supplier_id'        => $request->supplier_id,
            ]);

            foreach ($processedDetails as $detail) {
                $compra->purchase_details()->create($detail);
            }


            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Compra registrada correctamente.',
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'error'  => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }


    public function show(Request $request, $id)
    {
        $registro = Purchase::with('purchase_details.product')->findOrFail($id);

        return response()->json([
            'status'  => true,
            'details' => $registro->purchase_details,
        ]);
    }

    public function edit($id)
    {
        $registro = Purchase::with(['purchase_details', 'supplier'])->findOrFail($id);

        return response()->json([
            'status'   => true,
            'registro' => $registro
        ]);
    }

    public function update(Request $request, $id)
    {

        $registro = Purchase::findOrFail($id);
        $registro->update($request->all());

        return response()->json([
            'status'  => true,
            'message' => 'Compra actualizada correctamente.',
        ]);
    }

    public function destroy($id)
    {
        $purchase = Purchase::findOrFail($id);
        $purchase->deleted = 1;
        $purchase->save();

        return redirect()->route('purchases.index')->with('success', 'Compra anulada correctamente.');
    }

    public function buscarSuppliers(Request $request)
    {
        $query = $request->input('query');

        $suppliers = Supplier::where(function($q) use ($query) {
            $q->where('commercial_name', 'like', "%{$query}%")
            ->orWhere('document', 'like', "%{$query}%")
            ->orWhere('company_name', 'like', "%{$query}%");
        })
        ->where('deleted', 0)
        ->limit(10)
        ->get();
        
        return response()->json($suppliers);
    }

    public function buscarProducts(Request $request)
    {
        $query = $request->input('query');

        $products = Product::where('name', 'like', "%{$query}%")
            ->where('deleted', 0)
            ->limit(10)
            ->get();
        
        return response()->json($products);
    }

    public function searchPurchase(Request $request){
        $query = $request->input('query');

        //busca en la cedena "num company_name fecha", se puede optimizar si en producción busca muy lento
        $purchases = DB::select(
            "SELECT p.id,concat_ws(' ',invoice_number,company_name,p.date) AS 'name'
            FROM purchases p INNER JOIN suppliers s ON p.supplier_id = s.id
            WHERE CONCAT_WS(' ',invoice_number,company_name,p.date) LIKE ?
            ORDER BY p.date desc",
            ["%{$query}%"]
        );

        return response()->json($purchases);
    }

    
    public function pdf(Request $request)
    {
        try {
            $start_date = $request->start_date;
            $end_date = $request->end_date;
            $supplier_id = $request->supplier_id;

            $supplierName = '';
            $totalGeneral = 0;

            $tipo = 'compra';
            $query = Purchase::with(['purchase_details.product', 'supplier', 'payment_method'])
                ->where('deleted', 0) 
                ->when($start_date, fn($q) => $q->whereDate('date', '>=', $start_date))
                ->when($end_date, fn($q) => $q->whereDate('date', '<=', $end_date))
                ->when($supplier_id, fn($q) => $q->where('supplier_id', $supplier_id))
                ->orderBy('date', 'desc');


            $records = $query->get();

            foreach ($records as $purchase) {
                $totalGeneral += $purchase->total;
            }

            if ($supplier_id && $records->count() > 0) {
                $supplier = $records->first()->supplier ?? null;
                $supplierName = $supplier ? $supplier->company_name : '';
            }

            $title = 'Reporte de Compras';
                $subtitle = 'Listado de compras registradas';
            

            $data = [
                'tipo' => $tipo,
                'purchases' => $records,
                'totalGeneral' => $totalGeneral,
                'title' => $title,
                'subtitle' => $subtitle,
                'filters' => [
                    'start_date' => $start_date,
                    'end_date' => $end_date,
                    'supplier_id' => $supplier_id,
                    'supplier_name' => $supplierName
                ]
            ];

            $pdf = Pdf::loadView('purchases.pdf.pdf', $data)->setPaper('A4', 'portrait');
            $filename = 'reporte_' . $tipo . '_' . date('Y-m-d_H-i-s') . '.pdf';

            return response($pdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        } catch (\Exception $e) {
            Log::error('Error generating PDF: ' . $e->getMessage());
            return response('Error: ' . $e->getMessage(), 500);
        }
    }

    public function pdf_general(Request $request)
    {
        try {
            $tipo = 'compra';
            $start_date = $request->start_date;
            $end_date = $request->end_date;
            $supplier_id = $request->supplier_id;

            $totalGeneral = 0;
            $supplierName = '';
            $purchases = collect();

            $purchases = DB::table('purchases')
                ->join('purchase_details', 'purchases.id', '=', 'purchase_details.purchase_id')
                ->leftJoin('suppliers', 'purchases.supplier_id', '=', 'suppliers.id')
                ->select(
                    DB::raw("COALESCE(suppliers.company_name, 'Sin proveedor') as company_name"),
                    DB::raw('SUM(purchase_details.subtotal) as total')
                )
                ->where('purchases.deleted', 0)
                ->when($start_date, fn($q) => $q->whereDate('purchases.date', '>=', $start_date))
                ->when($end_date, fn($q) => $q->whereDate('purchases.date', '<=', $end_date))
                ->when($supplier_id, fn($q) => $q->where('purchases.supplier_id', $supplier_id))
                ->groupBy('suppliers.company_name')
                ->get();
            

            // Calcular total
            $totalGeneral = $purchases->sum('total');

            if ($supplier_id && $purchases->count() > 0) {
                $supplier = Supplier::find($supplier_id);
                $supplierName = $supplier ? $supplier->company_name : '';
            }

            $data = [
                'tipo' => $tipo,
                'purchases' => $purchases,
                'totalGeneral' => $totalGeneral,
                'filters' => [
                    'start_date' => $start_date,
                    'end_date' => $end_date,
                    'supplier_id' => $supplier_id,
                    'supplier_name' => $supplierName
                ]
            ];

            $pdf = Pdf::loadView('purchases.pdf.pdf_general', $data)->setPaper('A4', 'portrait');
            $filename = 'reporte_general_compras' . '_' . date('Y-m-d_H-i-s') . '.pdf';

            return response($pdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        } catch (\Exception $e) {
            Log::error('Error generating PDF: ' . $e->getMessage());
            return response('Error: ' . $e->getMessage(), 500);
        }
    }

    public function generatePDFProduct(Request $request)
    {
        try {
            $startDate = $request->start_date;
            $endDate = $request->end_date;
            $supplierId = $request->supplier_id;
            $productId = $request->product_id;
            $tipo = 'compra';

            if (!$productId) {
                return response()->json(['error' => 'ID de producto requerido'], 400);
            }

            $product = Product::find($productId);
            if (!$product) {
                return response()->json(['error' => 'Producto no encontrado'], 404);
            }

            $query = Purchase::with(['supplier', 'purchase_details.product'])->where('deleted', 0);

            // Filtros
            if ($startDate) $query->whereDate('date', '>=', $startDate);
            if ($endDate)   $query->whereDate('date', '<=', $endDate);
            if ($supplierId) $query->where('supplier_id', $supplierId);

            $records = $query->get();

            $productData = $this->getProductSummary($records, $productId);

            if (empty($productData['details'])) {
                $productData = [
                    'total_quantity' => 0,
                    'total_subtotal' => 0,
                    'details' => [],
                    'message' => 'No hay registros para este producto en el período seleccionado'
                ];
            }

            $data = [
                'productData' => $productData,
                'product' => $product,
                'tipo' => $tipo,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'filters' => $request->all()
            ];

            Log::info('PDF Product generado', [
                'tipo' => $tipo,
                'product_id' => $productId,
                'nombre' => $product->nombre,
                'detalles' => count($productData['details'])
            ]);

            $pdf = PDF::loadView('purchases.pdf.pdf_product', $data)->setPaper('A4', 'portrait');
            $filename = 'reporte_' . $tipo . '_' . strtolower(str_replace(' ', '_', $product->nombre)) . '_' . date('Y-m-d') . '.pdf';

            return response($pdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Content-Length' => strlen($pdf->output()),
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error generating PDF Product: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'error' => 'Error al generar PDF: ' . $e->getMessage()
            ], 500);
        }
    }


    public function generatePDFAllProducts(Request $request)
    {
        try {
            $tipo = 'compra'; 

            $startDate = $request->start_date;
            $endDate = $request->end_date;
            $supplierId = $request->supplier_id;

            $query =  Purchase::with(['supplier', 'purchase_details.product'])
            ->where('deleted', 0);

            if ($startDate) {
                $query->whereDate('date', '>=', $startDate);
            }
            if ($endDate) {
                $query->whereDate('date', '<=', $endDate);
            }
            if ($supplierId) {
                $query->where('supplier_id', $supplierId);
            }

            $records = $query->get();

            $allProductsSummary = $this->getAllProductsSummary($records);

            if (empty($allProductsSummary)) {
                $allProductsSummary = [];
                $totalGeneral = 0;
                $message = 'No hay compras registradas en el período seleccionado';
            } else {
                $totalGeneral = array_sum(array_column($allProductsSummary, 'total_subtotal'));
                $message = null;
            }

            $data = [
                'productsSummary' => $allProductsSummary,
                'totalGeneral' => $totalGeneral,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'filters' => $request->all(),
                'message' => $message,
                'tipo' => $tipo, 
            ];

            $pdf = PDF::loadView('purchases.pdf.pdf_all_products', $data);
            $pdf->setPaper('A4', 'portrait');

            $filename = 'reporte_todos_los_productos_' . $tipo . '_' . date('Y-m-d') . '.pdf';

            return response($pdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Content-Length' => strlen($pdf->output()),
            ]);

        } catch (\Exception $e) {
            Log::error('Error generating PDF All Products: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'error' => 'Error al generar PDF: ' . $e->getMessage()
            ], 500);
        }
    }

    // Método auxiliar corregido para obtener resumen de un producto específico
    private function getProductSummary($purchases, $productId)
    {
        $totalQuantity = 0;
        $totalSubtotal = 0;
        $details = [];

        foreach ($purchases as $purchase) {
            if ($purchase->purchase_details && $purchase->purchase_details->count() > 0) {
                foreach ($purchase->purchase_details as $detail) {

                    $detailProductId = $detail->product_id ?? null;
                    
                    if ($detailProductId == $productId) {
                        $totalQuantity += $detail->quantity ?? 0;
                        $totalSubtotal += $detail->subtotal ?? 0;
                        
                        $details[] = [
                            'purchase_date' => $purchase->date,
                            'supplier' => $purchase->supplier->company_name ?? 'Sin proveedor',
                            'invoice_number' => $purchase->invoice_number ?? '---',
                            'quantity' => $detail->quantity ?? 0,
                            'unit_price' => $detail->unit_price ?? 0,
                            'subtotal' => $detail->subtotal ?? 0
                        ];
                    }
                }
            }
        }

        return [
            'total_quantity' => $totalQuantity,
            'total_subtotal' => $totalSubtotal,
            'details' => $details
        ];
    }

    // Método auxiliar corregido para obtener resumen de todos los productos
    private function getAllProductsSummary($purchases)
    {
        $productsSummary = [];

        foreach ($purchases as $purchase) {
            if ($purchase->purchase_details && $purchase->purchase_details->count() > 0) {
                foreach ($purchase->purchase_details as $detail) {

                    $product = $detail->product ?? null;
                    $productName = $product ? $product->name : 'Producto sin nombre';
                    $productId = $detail->product_id ?? 'unknown';

                    // Validar que tenemos un ID válido
                    if (!$productId || $productId === 'unknown') {
                        continue;
                    }

                    if (!isset($productsSummary[$productId])) {
                        $productsSummary[$productId] = [
                            'name' => $productName,
                            'total_quantity' => 0,
                            'total_subtotal' => 0,
                            'details' => []
                        ];
                    }

                    $productsSummary[$productId]['total_quantity'] += $detail->quantity ?? 0;
                    $productsSummary[$productId]['total_subtotal'] += $detail->subtotal ?? 0;
                    
                    $productsSummary[$productId]['details'][] = [
                        'purchase_date' => $purchase->date,
                        'supplier' => $purchase->supplier->company_name ?? 'Sin proveedor',
                        'invoice_number' => $purchase->invoice_number ?? '---',
                        'quantity' => $detail->quantity ?? 0,
                        'unit_price' => $detail->unit_price ?? 0,
                        'subtotal' => $detail->subtotal ?? 0
                    ];
                }
            }
        }

        // Ordenar por nombre de producto
        uasort($productsSummary, function($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        return $productsSummary;
    }

    public function excel(Request $request)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        try {
            return Excel::download(new PurchasesExport($start_date, $end_date), 'Compras.xlsx');
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error' => 'Error al generar Excel: ' . $e->getMessage(),
            ], 500);
        }
    }
}
