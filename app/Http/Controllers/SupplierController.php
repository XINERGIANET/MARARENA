<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Supplier;
use Illuminate\Support\Facades\Validator;


class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $suppliers = Supplier::where('deleted', 0)->paginate(10);
        return view('suppliers.index', compact('suppliers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('suppliers.create');
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
            'company_name' => 'required|string|max:255',
            'document' => 'required|string|max:20|unique:suppliers,document',
            'commercial_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:15',
        ]);

        Supplier::create([
            'company_name' => $request->company_name,
            'document' => $request->document,
            'commercial_name' => $request->commercial_name,
            'phone' => $request->phone,
            'deleted' => 0, // Por defecto, el proveedor está activo
        ]);

        return redirect()->route('suppliers.index')->with('success', 'Proveedor registrado correctamente.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $supplier = Supplier::findOrFail($id);
        return view('suppliers.show', compact('supplier'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $supplier = Supplier::findOrFail($id);
        return view('suppliers.edit', compact('supplier'));
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
        $request->validate([
            'company_name' => 'required|string|max:255',
            'document' => 'required|string|max:20|unique:suppliers,document,' . $id,
            'commercial_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:15',
        ]);

        $supplier = Supplier::findOrFail($id);

        $supplier->update([
            'company_name' => $request->company_name,
            'document' => $request->document,
            'commercial_name' => $request->commercial_name,
            'phone' => $request->phone,
        ]);

        return redirect()->route('suppliers.index')->with('success', 'Proveedor actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $supplier = Supplier::findOrFail($id);

        $supplier->update(['deleted' => 1]);

        return redirect()->route('suppliers.index')->with('success', 'Proveedor eliminado correctamente.');
    }

    public function save_ajax(Request $request)
    {   
        $validatedData = $request->validate([
            'company_name' => 'required|string|max:255',
            'document' => 'required|string|max:20|unique:suppliers,document',
            'commercial_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:15',
        ]);

        $proveedor = Supplier::create(array_merge($validatedData, ['estado' => 0]));

        return response()->json(['success' => true, 'supplier' => $proveedor, 'message' => 'Proveedor guardado correctamente.']);
    }

    public function search(Request $request)
    {
        $query = $request->input('query'); // Obtener el término de búsqueda

        // Buscar clientes que coincidan con el término
        $clients = Supplier::where('company_name', 'LIKE', "%{$query}%")
            ->orWhere('commercial_name', 'LIKE', "%{$query}%")
            ->select('id', 'company_name', 'commercial_name')
            ->limit(10)
            ->get();

        return response()->json($clients); // Devolver resultados en JSON
    }
}
