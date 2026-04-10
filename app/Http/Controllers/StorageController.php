<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Storage;
use Illuminate\Http\Request;

class StorageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $tipo = $request->get('tipo');
        $searchTerm = $request->input('search');

        // Filtrar productos por línea de venta
        $products = Product::with(['category.sale_line'])
            ->whereHas('category', function ($q) use ($tipo) {
                $q->whereHas('sale_line', function ($q2) use ($tipo) {
                    $q2->whereRaw('LOWER(name) = ?', [strtolower($tipo)]);
                });
            })
            ->where('deleted', 0)
            ->when($searchTerm, function ($query, $searchTerm) {
                return $query->where('name', 'like', "%{$searchTerm}%");
            })
            ->get();

        // Si es una petición AJAX, devolver JSON
        if ($request->ajax()) {
            return response()->json([
                'status' => true,
                'products' => $products
            ]);
        }

        return view('storages.index', [
            'products' => $products,
            'tipo' => $tipo
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        return view('payment_methods.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:0',
        ]);

        // Actualizar la cantidad directamente en el producto
        $product = Product::where('id', $request->product_id)->where('deleted', 0)->first();
        if ($product) {
            $product->quantity = $request->quantity;
            $product->save();
            return response()->json(['success' => true, 'message' => 'Stock actualizado correctamente.']);
        } else {
            return response()->json(['success' => false, 'message' => 'Producto no encontrado.'], 404);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
