@extends('layouts.app')

@section('nav')
<ul class="nav justify-content-center">
    <li class="nav-item" style="margin: 0 10px 5px 10px;"> <!-- Margen personalizado: 0 arriba, 20px a los lados, 5px abajo -->
        <a class="nav-link btn btn-primary active" href="{{ route('employees.create') }}">Registro</a>
    </li>
    <li class="nav-item" style="margin: 0 10px 5px 10px;"> <!-- Margen personalizado: 0 arriba, 20px a los lados, 5px abajo -->
        <a class="nav-link btn btn-secondary" href="{{ route('employees.index') }}">Historico</a>
    </li>
</ul>
@endsection

@section('header')
<h1>Registro Empleado</h1>
<p>Registrar un nuevo empleado</p>
@endsection


@section('content')
<div class="container-fluid content-inner mt-n5 py-0">
    <div class="card shadow">
        <div class="card-body">
            <form id="formRegistro" action="{{ route('employees.store') }}" method="POST">
                @csrf
                <div class="row align-items-center">
                    <!-- Nombres -->
                    <div class="col-md-6 mb-3">
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <label for="name" class="form-label mb-0">Nombres</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" placeholder="Ingrese el nombre" id="name" name="name" required>
                            </div>
                        </div>
                    </div>
                    <!-- Apellidos -->
                    <div class="col-md-6 mb-3">
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <label for="last_name" class="form-label mb-0">Apellidos</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" placeholder="Ingrese el apellido" id="last_name" name="last_name" required>
                            </div>
                        </div>
                    </div>
                    <!-- DNI -->
                    <div class="col-md-6 mb-3">
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <label for="document" class="form-label mb-0">DNI</label>
                            </div>
                            <div class="col-md-8">
                                <input type="number" class="form-control" placeholder="Ingrese el DNI" id="document" name="document" required>
                            </div>
                        </div>
                    </div>
                    <!-- Fecha de nacimiento -->
                    <div class="col-md-6 mb-3">
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <label for="birth_date" class="form-label mb-0">F. nacimiento</label>
                            </div>
                            <div class="col-md-8">
                                <input type="date" class="form-control" placeholder="Ingrese la fecha de nacimiento" id="birth_date" name="birth_date" required>
                            </div>
                        </div>
                    </div>
                    <!-- Teléfono -->
                    <div class="col-md-6 mb-3">
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <label for="phone" class="form-label mb-0">Teléfono</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" placeholder="Ingrese el teléfono" id="phone" name="phone" required>
                            </div>
                        </div>
                    </div>
                    <!-- Dirección -->
                    <div class="col-md-6 mb-3">
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <label for="address" class="form-label mb-0">Dirección</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" placeholder="Ingrese la dirección" id="address" name="address" required>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <label for="address" class="form-label mb-0">PIN</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" placeholder="Ingrese el PIN" id="pin" name="pin" required maxlength="4" pattern="\d{4}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botón de Guardar (alineado a la derecha) -->
                <div class="row mb-3">
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection