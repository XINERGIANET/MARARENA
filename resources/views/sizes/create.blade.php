@extends('layouts.app')

@section('nav')
<ul class="nav justify-content-center">
    <li class="nav-item" style="margin: 0 10px 5px 10px;"> <!-- Margen personalizado: 0 arriba, 20px a los lados, 5px abajo -->
        <a class="nav-link btn btn-primary active" href="{{ route('sizes.create') }}">Registro</a>
    </li>
    <li class="nav-item" style="margin: 0 10px 5px 10px;"> <!-- Margen personalizado: 0 arriba, 20px a los lados, 5px abajo -->
        <a class="nav-link btn btn-secondary" href="{{ route('sizes.index') }}">Historico</a>
    </li>
</ul>
@endsection

@section('header')
<h1>Registro Tallas</h1>
<p>Registrar un nueva talla</p>
@endsection

@section('content')
<div class="container-fluid content-inner mt-n5 py-0">
    <div class="card shadow">
        <div class="card-body">
            <form id="formRegistro" action="{{ route('sizes.store') }}" method="POST">
                @csrf
                <!-- Campo Nombre -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="name" class="form-label">Talla</label>
                    </div>
                    <div class="col-md-9">
                        <input type="text" class="form-control" id="name" name="name" required>
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