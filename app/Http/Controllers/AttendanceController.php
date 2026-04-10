<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('attendance.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $employee_id = $request->employee_id;

        $last_attendance = Attendance::where('employee_id', $employee_id)
            // ->where('deleted',0)
            ->orderBy('start','desc')
            ->first();

        if (!$last_attendance || ($last_attendance && $last_attendance->finish)) {
            // No hay asistencia previa o la última ya fue cerrada, registrar nueva entrada
            Attendance::create([
                'employee_id' => $employee_id,
                'start' => now(),
            ]);
        } else {
            // Hay asistencia previa sin cerrar, registrar salida
            $last_attendance->finish = now();
            $last_attendance->save();
        }
            

        return response()->json([
            'status' => true,
            'message' => 'Asistencia registrada correctamente',
        ]);
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

    public function check(Request $request)
    {
        $request->validate([
            'pin' => 'required|string|size:4',
        ]);

        $pin = $request->pin;

        $employee = Employee::where('pin',$pin)
        ->where('deleted',0)
        ->first();

        $type = null;
        $start = null;
        $employee_id = null;


        if ($employee){

            $employee_id = $employee->id;

            $last_attendance = Attendance::where('employee_id', $employee->id)
                // ->where('deleted',0)
                ->orderBy('start','desc')
                ->first();
            
            if ($last_attendance){
                if ($last_attendance->finish){ // si ya acabó, entonces se registra nva entrada
                    $type = 'entrada';
                } else {
                    $type = 'salida';
                    $start = $last_attendance->start;
                }
            } else {
                $type = 'entrada';
            }
        }

        return response()->json([
            'status' => true,
            'employee'=> $employee,
            'type'=> $type,
            'start'=> $start,
            'employee_id'=> $employee_id,
        ]);
    }
}
