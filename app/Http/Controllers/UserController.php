<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $users = User::with('rol')->where('deleted', 0)->paginate(10);
        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        $roles = Rol::where('deleted', 0)->get();
        return view('users.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validar el campo 'name' requerido y único
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|max:255',
            'password' => 'required|string|max:255',
            'rol_id' => 'required|integer|exists:roles,id',
            'shift' => 'nullable|integer'
        ]);

        // Crear el registro
        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']), // Encriptar la contraseña
            'rol_id' => $validated['rol_id'],
            'deleted' => 0, // Por defecto, el usuario está activo
            'shift' => 0,// $validated['shift'],
        ]);

        return redirect()->route('users.index')
            ->with('success', 'Usuario creado exitosamente.');
    }

    public function setTurno(Request $request)
    {
        $request->validate([
            'shift' => 'required|string|max:50',
        ]);

        $authUser = Auth::user();
        $user = User::find($authUser->id);
        $user->shift = $request->input('shift');
        $user->save();

        // Elimina la variable de sesión para que el modal no vuelva a aparecer
        $request->session()->forget('show_turno_modal');

        return response()->json(['success' => true]);
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
            $user = User::with('rol')
                ->where('id', $id)
                ->where('deleted', 0)
                ->first();
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'error' => 'Usuario no encontrado'
                ], 404);
            }

            $roles = Rol::with('users')->where('deleted', 0)->get();

            return response()->json([
                'status' => true,
                'message' => 'Datos de usuario para edicion',
                'data' => [
                    'user' => $user,
                    'roles' => $roles
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Error al obtener datos para edicion' . $e->getMessage()
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
            // Reglas de validación
            $rules = [
                'name' => 'required|string|max:255',
                'email' => "required|string|max:255", // único excepto el actual
                'rol_id' => 'required|exists:roles,id',
                'shift' => 'nullable|integer',
            ];

            // Si quiere cambiar contraseña, la validamos
            if ($request->filled('new_pass')) {
                $rules['new_pass'] = 'required|min:4';
            }

            $validated = $request->validate($rules);

            $user = User::findOrFail($id);

            $user->name = $validated['name'];
            $user->email = $validated['email'];
            $user->rol_id = $validated['rol_id'];
            $user->shift = 0;//$validated['shift'];

            // Solo actualiza contraseña si fue enviada
            if (!empty($validated['new_pass'])) {
                $user->password =  Hash::make($validated['new_pass']);
            }

            $user->save();

            return response()->json([
                'status' => true,
                'message' => 'Usuario actualizado correctamente',
                'user' => $user
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Error al actualizar usuario: ' . $e->getMessage()
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
            $user = User::where('id', $id)
                ->where('deleted', 0)
                ->first();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Usuario no encontrado'
                ], 404);
            }

            // Soft delete - marcar como eliminado
            $user->update(['deleted' => 1]);

            return response()->json([
                'status' => true,
                'message' => 'Usuario eliminado correctamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error al eliminar el usuario: ' . $e->getMessage()
            ], 500);
        }
    }
}
