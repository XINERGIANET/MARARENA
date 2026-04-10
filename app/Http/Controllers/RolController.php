<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RolController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $roles = Rol::where('deleted', 0)->paginate(10);
        return view('roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        return view('roles.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        // Validar el campo 'name' requerido y único
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:255',
        ]);

        // Crear el método de pago
        $tables = Rol::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
        ]);

        // Redirigir con mensaje de éxito
        return redirect()->route('roles.index')
            ->with('success', 'Rol creado correctamente.');
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
            $rol = Rol::where('id', $id)
                ->where('deleted', 0)
                ->first();
            if (!$rol) {
                return response()->json([
                    'status' => false,
                    'message' => 'Rol no encontrado'
                ], 404);
            }
            return response()->json([
                'status' => true,
                'message' => "Datos de rol para editar",
                'data' => $rol
            ], 200);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status' => false,
                'message' => "Error al obtener los datos"
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
            $rol = Rol::where('id', $id)
                ->where('deleted', 0)
                ->first();

            if (!$rol) {
                return response()->json([
                    'status' => false,
                    'message' => 'Rol no encontrado'
                ], 404);
            }

            $rol->update($request->all());
            return response()->json([
                'status' => true,
                'message' => 'Rol actualizado correctamente'
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error al actualizar el rol". $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => "Error al actualizar el rol"
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
            $rol = Rol::where("id", $id)
                ->where('deleted', 0)
                ->first();

            if(!$rol){
                return response()->json([
                    'status' => false,
                    'message' => 'Rol no encontrado'
                ], 404);
            }

            $isUsedInUsers = DB::table('users')
                ->where('rol_id', $id)
                ->where('deleted', 0)
                ->exists();
            if ($isUsedInUsers) {
                return response()->json([
                    'status'=> false,
                    'message' => 'No se puede eliminar el rol porque esta siendo usado en usuarios'
                ], 400);
            }
            $rol->update(['deleted' => 1]);
            return response()->json([
                'status' => true,
                'message' => 'Rol eliminado correctamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error al eliminar el rol: ' . $e->getMessage()
            ], 500);
        }
    }
}
