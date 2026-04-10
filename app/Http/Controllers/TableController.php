<?php

namespace App\Http\Controllers;

use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class TableController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        $tables = Table::where('deleted', 0)->paginate(10);
        return view('tables.index', compact('tables'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create(): View
    {
        return view('tables.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        // Validar el campo 'name' requerido y único
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:tables,name,NULL,id,deleted,0',
        ]);

        // Crear la tabla
        $tables = Table::create([
            'name' => $validated['name'],
        ]);

        // Redirigir con mensaje de éxito
        return redirect()->route('tables.index')
            ->with('success', 'Tabla creada correctamente.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id): JsonResponse
    {
        try {
            $table = Table::where('id', $id)
                ->where('deleted', 0)
                ->first();

            if (!$table) {
                return response()->json([
                    'status' => false,
                    'message' => 'Mesa no encontrada'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Mesa encontrada',
                'data' => $table
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error al obtener la mesa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit($id): JsonResponse
    {
        try {
            $table = Table::where('id', $id)
                ->where('deleted', 0)
                ->first();

            if (!$table) {
                return response()->json([
                    'status' => false,
                    'message' => 'Mesa no encontrada'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Datos de la mesa para edición',
                'data' => $table
            ], 200);

        } catch (\Exception $e) {
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
    public function update(Request $request, $id): JsonResponse
    {
        try {
            // Buscar la mesa
            $table = Table::where('id', $id)
                ->where('deleted', 0)
                ->first();

            if (!$table) {
                return response()->json([
                    'status' => false,
                    'message' => 'Mesa no encontrada'
                ], 404);
            }

            // Validar el campo 'name' requerido y único (excluyendo el registro actual)
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:tables,name,' . $id . ',id,deleted,0',
            ]);

            // Actualizar la mesa
            $table->update([
                'name' => $validated['name'],
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Mesa actualizada correctamente',
                'data' => $table->fresh()
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Errores de validación',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error al actualizar la mesa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        try {
            $table = Table::where('id', $id)
                ->where('deleted', 0)
                ->first();

            if (!$table) {
                return response()->json([
                    'status' => false,
                    'message' => 'Mesa no encontrada'
                ], 404);
            }

            // Verificar si la mesa está siendo usada en órdenes
            $isUsedInOrders = DB::table('orders')
                ->where('table_id', $id)
                ->where('deleted', 0)
                ->exists();

            if ($isUsedInOrders) {
                return response()->json([
                    'status' => false,
                    'message' => 'No se puede eliminar la mesa porque tiene órdenes asociadas'
                ], 400);
            }

            // Soft delete - marcar como eliminado
            $table->update(['deleted' => 1]);

            return response()->json([
                'status' => true,
                'message' => 'Mesa eliminada correctamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error al eliminar la mesa: ' . $e->getMessage()
            ], 500);
        }
    }
}
