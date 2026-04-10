<?php

namespace App\Http\Controllers;

use App\Models\Size;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class SizeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        $sizes = Size::where('deleted', 0)->paginate(10);
        return view('sizes.index', compact('sizes'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create(): View
    {
        return view('sizes.create');
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
            'name' => 'required|string|max:255|unique:sizes,name,NULL,id,deleted,0',
        ]);

        // Crear la talla
        $size = Size::create([
            'name' => $validated['name'],
        ]);

        // Redirigir con mensaje de éxito
        return redirect()->route('sizes.index')
            ->with('success', 'Talla registrada correctamente.');
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
            $size = Size::where('id', $id)
                ->where('deleted', 0)
                ->first();

            if (!$size) {
                return response()->json([
                    'status' => false,
                    'message' => 'Talla no encontrada'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Talla encontrada',
                'data' => $size
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error al obtener la talla: ' . $e->getMessage()
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
            $size = Size::where('id', $id)
                ->where('deleted', 0)
                ->first();

            if (!$size) {
                return response()->json([
                    'status' => false,
                    'message' => 'Talla no encontrada'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Datos de la talla para edición',
                'data' => $size
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
            // Buscar la talla
            $size = Size::where('id', $id)
                ->where('deleted', 0)
                ->first();

            if (!$size) {
                return response()->json([
                    'status' => false,
                    'message' => 'Talla no encontrada'
                ], 404);
            }

            // Validar el campo 'name' requerido y único (excluyendo el registro actual)
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:sizes,name,' . $id . ',id,deleted,0',
            ]);

            // Actualizar la talla
            $size->update([
                'name' => $validated['name'],
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Talla actualizada correctamente',
                'data' => $size->fresh()
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
                'message' => 'Error al actualizar la talla: ' . $e->getMessage()
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
            $size = Size::where('id', $id)
                ->where('deleted', 0)
                ->first();

            if (!$size) {
                return response()->json([
                    'status' => false,
                    'message' => 'Talla no encontrada'
                ], 404);
            }

            // Verificar si la talla está siendo usada en productos
            $isUsedInProducts = DB::table('products')
                ->where('size_id', $id)
                ->where('deleted', 0)
                ->exists();

            if ($isUsedInProducts) {
                return response()->json([
                    'status' => false,
                    'message' => 'No se puede eliminar la talla porque está siendo utilizada en productos'
                ], 400);
            }

            // Soft delete - marcar como eliminado
            $size->update(['deleted' => 1]);

            return response()->json([
                'status' => true,
                'message' => 'Talla eliminada correctamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error al eliminar la talla: ' . $e->getMessage()
            ], 500);
        }
    }
}
