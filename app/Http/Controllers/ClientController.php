<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Obtener solo los productos activos (estado = 0)
        $clients = Client::where('deleted', 0)->paginate(15);
        return view('clients.index', compact('clients'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('clients.create');
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
            'business_name' => 'required|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'commercial_name' => 'nullable|string|max:255',
            'document' => 'required|string|max:20',
            'phone' => 'required|string|max:15',
            'address' => 'required|string|max:255',
            'department' => 'required|string|max:100',
            'province' => 'required|string|max:100',
            'district' => 'required|string|max:100',
        ]);

        Client::create([
            'business_name' => $request->business_name,
            'contact_name' => $request->contact_name,
            'commercial_name' => $request->commercial_name,
            'document' => $request->document,
            'phone' => $request->phone,
            'address' => $request->address,
            'department' => $request->department,
            'province' => $request->province,
            'district' => $request->district,
            'deleted' => 0,
        ]);

        return redirect()->route('clients.index')->with('success', 'Cliente registrado correctamente.');
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit($id)
    {
        try {
            $client = Client::where('id', $id)
                ->where('deleted', 0)
                ->first();

            if (!$client) {
                return response()->json(['error' => 'Empleado no encontrado'], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Datos del cliente para edición',
                'data' => $client
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al obtener datos del cliente: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Error al obtener los datos para edición: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {

        try {
            $request->validate([
                'business_name' => 'required|string|max:255',
                'contact_name' => 'nullable|string|max:255',
                'commercial_name' => 'nullable|string|max:255',
                'document' => 'required|string|max:20',
                'phone' => 'required|string|max:15',
                'address' => 'required|string|max:255',
                'department' => 'required|string|max:100',
                'province' => 'required|string|max:100',
                'district' => 'required|string|max:100',
            ]);

            $client = Client::where('id', $id)
                ->where('deleted', 0)
                ->first();
            if (!$client) {
                return response()->json([
                    'status' => false,
                    'message' => 'Cliente no encontrado'
                ], 404);
            }
            $client->update($request->all());
            return response()->json([
                'status' => true,
                'message' => 'Cliente actualizado correctamente'
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error al actualizar el cliente: " . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Error al actualizar el cliente: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $client = Client::where('id', $id)
                ->where('deleted', 0)
                ->first();
            if (!$client) {
                return response()->json([
                    'status' => false,
                    'message' => "Cliente no encontrado"
                ], 404);
            }
            $client->update(['deleted' => 1]);

            return response()->json([
                'status' => true,
                'message' => 'Cliente eliminado correctamente'
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error al eliminar el cliente: " . $e->getMessage());
        }
    }

    public function search(Request $request)
    {
        $query = $request->input('query'); // Obtener el término de búsqueda

        // Buscar clientes que coincidan con el término
        $clients = Client::where('business_name', 'LIKE', "%{$query}%")
            ->orWhere('commercial_name', 'LIKE', "%{$query}%")
            ->orWhere('contact_name', 'LIKE', "%{$query}%")
            ->select('id', 'business_name', 'contact_name')
            ->limit(10)
            ->get();

        return response()->json($clients); // Devolver resultados en JSON
    }
}
