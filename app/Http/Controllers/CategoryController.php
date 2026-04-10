<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\SaleLines;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        $categories = Category::with('sale_line')->where('deleted', 0)->paginate(10);
        return view('categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create(): View
    {
        $sls = SaleLines::where('deleted', 0)->get();
        return view('categories.create',compact('sls'));
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
            'name' => 'required|string|max:255|unique:categories,name,NULL,id,deleted,0',
            'sale_line_id' => 'required|int',
        ]);

        // Crear la categoría
        $category = Category::create([
            'name' => $validated['name'],
            'sale_line_id' => $validated['sale_line_id'],
        ]);

        // Redirigir con mensaje de éxito
        return redirect()->route('categories.index')
            ->with('success', 'Categoría creada correctamente.');
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
            $category = Category::where('id', $id)
                ->where('deleted', 0)
                ->first();

            if (!$category) {
                return response()->json([
                    'status' => false,
                    'message' => 'Categoría no encontrada'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Categoría encontrada',
                'data' => $category
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error al obtener la categoría: ' . $e->getMessage()
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
            $category = Category::where('id', $id)
                ->where('deleted', 0)
                ->first();

            if (!$category) {
                return response()->json([
                    'status' => false,
                    'message' => 'Categoría no encontrada'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Datos de la categoría para edición',
                'data' => $category
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
            // Buscar la categoría
            $category = Category::where('id', $id)
                ->where('deleted', 0)
                ->first();

            if (!$category) {
                return response()->json([
                    'status' => false,
                    'message' => 'Categoría no encontrada'
                ], 404);
            }

            // Validar el campo 'name' requerido y único (excluyendo el registro actual)
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:categories,name,' . $id . ',id,deleted,0',
            ]);

            // Actualizar la categoría
            $category->update([
                'name' => $validated['name'],
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Categoría actualizada correctamente',
                'data' => $category->fresh()
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
                'message' => 'Error al actualizar la categoría: ' . $e->getMessage()
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
            $category = Category::where('id', $id)
                ->where('deleted', 0)
                ->first();

            if (!$category) {
                return response()->json([
                    'status' => false,
                    'message' => 'Categoría no encontrada'
                ], 404);
            }

            // Verificar si la categoría está siendo usada en productos
            $isUsedInProducts = DB::table('products')
                ->where('category_id', $id)
                ->where('deleted', 0)
                ->exists();

            if ($isUsedInProducts) {
                return response()->json([
                    'status' => false,
                    'message' => 'No se puede eliminar la categoría porque está siendo utilizada en productos'
                ], 400);
            }

            // Soft delete - marcar como eliminado
            $category->update(['deleted' => 1]);

            return response()->json([
                'status' => true,
                'message' => 'Categoría eliminada correctamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error al eliminar la categoría: ' . $e->getMessage()
            ], 500);
        }
    }
}
