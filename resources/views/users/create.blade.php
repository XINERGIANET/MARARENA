@extends('layouts.app')

@section('nav')
    <ul class="nav justify-content-center">
        <li class="nav-item" style="margin: 0 10px 5px 10px;">
            <!-- Margen personalizado: 0 arriba, 20px a los lados, 5px abajo -->
            <a class="nav-link btn btn-primary active" href="{{ route('users.create') }}">Registro</a>
        </li>
        <li class="nav-item" style="margin: 0 10px 5px 10px;">
            <!-- Margen personalizado: 0 arriba, 20px a los lados, 5px abajo -->
            <a class="nav-link btn btn-secondary" href="{{ route('users.index') }}">Historico</a>
        </li>
    </ul>
@endsection

@section('header')
    <h1>Registro Usuario</h1>
    <p>Registrar un nuevo usuario</p>
@endsection

@section('content')
    <div class="container-fluid content-inner mt-n5 py-0">
        <div class="card shadow">
            <div class="card-body">
                <form id="formRegistro" action="{{ route('users.store') }}" method="POST" autocomplete="off">
                    @csrf
                    <!-- Campo Nombre -->
                    <div class="row mb-3 align-items-center">
                        <div class="col-md-6">
                            <div class="row align-items-center">
                                <div class="col-md-4">
                                    <label for="name" class="form-label mb-0">Usuario</label>
                                </div>
                                <div class="col-md-8">
                                    <input type="text" class="form-control" placeholder="Nombre de Usuario"
                                        id="name" name="name" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row align-items-center">
                                <div class="col-md-4">
                                    <label for="rol_id" class="form-label mb-0">Rol</label>
                                </div>
                                <div class="col-md-8">
                                    <select class="form-control" id="rol_id" name="rol_id" required>
                                        <option value="">Seleccione un Rol</option>
                                        @foreach ($roles as $rol)
                                            <option value="{{ $rol->id }}">{{ $rol->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3 align-items-center">
                        <div class="col-md-6">
                            <div class="row align-items-center">
                                <div class="col-md-4">
                                    <label for="email" class="form-label mb-0">Email</label>
                                </div>
                                <div class="col-md-8">
                                    <input type="text" class="form-control" placeholder="Email" id="email"
                                        name="email" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row align-items-center">
                                <div class="col-md-4">
                                    <label for="password" class="form-label mb-0">Contraseña</label>
                                </div>
                                <div class="col-md-8">
                                    <input type="password" class="form-control" placeholder="Contraseña" id="password"
                                        name="password" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- <div class="row mb-3 align-items-center">
                        <div class="col-md-6">
                            <div class="row align-items-center">
                                <div class="col-md-4">
                                    <label for="shift" class="form-label mb-0">Turno</label>
                                </div>
                                <div class="col-md-8">
                                    <select class="form-control" name="shift" id="shift">
                                        <option value="">Seleccione un turno</option>
                                        <option value="0">Mañana</option>
                                        <option value="1">Tarde</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div> -->
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
