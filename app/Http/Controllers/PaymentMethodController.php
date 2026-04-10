<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class PaymentMethodController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        $pms = PaymentMethod::where('deleted', 0)->paginate(10);
        return view('payment_methods.index', compact('pms'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create(): View
    {
        return view('payment_methods.create');
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
            'name' => 'required|string|max:255|unique:payment_methods,name',
        ]);

        // Crear el método de pago
        $payment_methods = PaymentMethod::create([
            'name' => $validated['name'],
        ]);

        // Redirigir con mensaje de éxito
        return redirect()->route('payment_methods.index')
            ->with('success', 'Método de pago registrado correctamente.');
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
            $paymentMethod = PaymentMethod::where('id', $id)
                ->where('deleted', 0)
                ->first();

            if (!$paymentMethod) {
                return response()->json([
                    'status' => false,
                    'message' => 'Método de pago no encontrado'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Método de pago encontrado',
                'data' => $paymentMethod
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error al obtener el método de pago: ' . $e->getMessage()
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
            $paymentMethod = PaymentMethod::where('id', $id)
                ->where('deleted', 0)
                ->first();

            if (!$paymentMethod) {
                return response()->json([
                    'status' => false,
                    'message' => 'Método de pago no encontrado'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Datos del método de pago para edición',
                'data' => $paymentMethod
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
            // Buscar el método de pago
            $paymentMethod = PaymentMethod::where('id', $id)
                ->where('deleted', 0)
                ->first();

            if (!$paymentMethod) {
                return response()->json([
                    'status' => false,
                    'message' => 'Método de pago no encontrado'
                ], 404);
            }

            // Validar el campo 'name' requerido y único (excluyendo el registro actual)
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:payment_methods,name,' . $id . ',id,deleted,0',
            ]);

            // Actualizar el método de pago
            $paymentMethod->update([
                'name' => $validated['name'],
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Método de pago actualizado correctamente',
                'data' => $paymentMethod->fresh()
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
                'message' => 'Error al actualizar el método de pago: ' . $e->getMessage()
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
            $paymentMethod = PaymentMethod::where('id', $id)
                ->where('deleted', 0)
                ->first();

            if (!$paymentMethod) {
                return response()->json([
                    'status' => false,
                    'message' => 'Método de pago no encontrado'
                ], 404);
            }

            // Verificar si el método de pago está siendo usado en ventas
            $isUsedInSales = DB::table('payments')
                ->where('payment_method_id', $id)
                ->where('deleted', 0)
                ->exists();

            if ($isUsedInSales) {
                return response()->json([
                    'status' => false,
                    'message' => 'No se puede eliminar el método de pago porque está siendo utilizado en ventas'
                ], 400);
            }

            // Soft delete - marcar como eliminado
            $paymentMethod->update(['deleted' => 1]);

            return response()->json([
                'status' => true,
                'message' => 'Método de pago eliminado correctamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error al eliminar el método de pago: ' . $e->getMessage()
            ], 500);
        }
    }
}
