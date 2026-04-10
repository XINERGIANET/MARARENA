@extends('layouts.app')

@section('nav')
<ul class="nav justify-content-center">
    <li class="nav-item" style="margin: 0 10px 5px 10px;"> <!-- Margen personalizado: 0 arriba, 20px a los lados, 5px abajo -->
        <a class="nav-link btn btn-primary active" href="{{ route('roles.create') }}">Registro</a>
    </li>
    <li class="nav-item" style="margin: 0 10px 5px 10px;"> <!-- Margen personalizado: 0 arriba, 20px a los lados, 5px abajo -->
        <a class="nav-link btn btn-secondary" href="{{ route('roles.index') }}">Historico</a>
    </li>
</ul>
@endsection

@section('header')
<h1>Registro Roles</h1>
<p>Registrar un nuevo rol</p>
@endsection

@section('content')
<div class="container-fluid content-inner mt-n5 py-0">
    <div class="card shadow">
        <div class="card-body">
            <form id="formRegistro" action="{{ route('roles.store') }}" method="POST">
                @csrf
                <!-- Campo Nombre -->
                <div class="row mb-3 align-items-center">
                    <div class="col-md-6">
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <label for="name" class="form-label mb-0">Rol</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" placeholder="Nombre del Rol"
                                    id="name" name="name" required>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <label for="description" class="form-label mb-0">Descripción</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" placeholder="Descripción del Rol" id="description" name="description"
                                    required>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Botones -->
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                Guardar
                            </button>
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection
