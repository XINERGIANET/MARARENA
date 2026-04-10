@extends('layouts.app')

@section('nav')
<ul class="nav justify-content-center">
    <li class="nav-item" style="margin: 0 10px 5px 10px;"> <!-- Margen personalizado: 0 arriba, 20px a los lados, 5px abajo -->
        <a class="nav-link btn btn-primary active" href="{{ route('products.create') }}">Registro</a>
    </li>
    <li class="nav-item" style="margin: 0 10px 5px 10px;"> <!-- Margen personalizado: 0 arriba, 20px a los lados, 5px abajo -->
        <a class="nav-link btn btn-secondary" href="{{ route('products.index') }}">Historico</a>
    </li>
</ul>
@endsection

@section('header')
<h1>Registro Producto</h1>
<p>Registrar un nuevo producto</p>
@endsection

@section('content')
<div class="container-fluid content-inner mt-n5 py-0">
    <div class="card shadow">
        <div class="card-body">
            <form id="formRegistro" action="{{ route('products.store') }}" method="POST">
                @csrf
                <!-- Nombre del producto (1 fila completa) -->
                <div class="row mb-3">
                    <div class="col-md-2">
                        <label for="name" class="form-label">Producto</label>
                    </div>
                    <div class="col-md-10">
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                </div>

                <!-- Categoría y Precio de venta (2 por fila) -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="sale_line_id" class="form-label">Linea de Venta</label>
                        <select class="form-control" id="sale_line_id" name="sale_line_id" required>
                            <option value="">Seleccione una línea</option>
                            @foreach ($sls as $sl)
                            <option value="{{ $sl->id }}" data-name="{{ $sl->name }}">{{ $sl->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="category_id" class="form-label">Categoría</label>
                        <select class="form-control" id="category_id" name="category_id" required>
                            <option value="">Seleccione una categoría</option>
                            @foreach ($categories as $category)
                            <option value="{{ $category->id }}" data-sale-line="{{ $category->sale_line_id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Línea de venta y Talla (2 por fila) -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="unit_price" class="form-label">Precio de venta</label>
                        <input type="number" step="0.01" class="form-control" id="unit_price" name="unit_price" required>
                    </div>
                    <div class="col-md-6 d-none" id="size_col">
                        <label for="size_id" class="form-label">Talla</label>
                        <select class="form-control" id="size_id" name="size_id">
                            <option value="">Seleccione una talla</option>
                            @foreach ($sizes as $size)
                            <option value="{{ $size->id }}">{{ $size->name }}</option>
                            @endforeach
                        </select>
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

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const saleLine = document.getElementById('sale_line_id');
    const sizeCol = document.getElementById('size_col');
    saleLine.addEventListener('change', function() {
        const selected = saleLine.options[saleLine.selectedIndex];
        // Puedes cambiar "Ropa" por el nombre exacto de la línea que corresponde a ropa
        if (selected.dataset.name === 'Ropa') {
            sizeCol.classList.remove('d-none');
        } else {
            sizeCol.classList.add('d-none');
            document.getElementById('size_id').value = '';
        }
    });

    const categorySelect = document.getElementById('category_id');
    const allOptions = Array.from(categorySelect.options).slice(1); // Excluye el "Seleccione una categoría"

    saleLine.addEventListener('change', function() {
        const selectedSaleLineId = saleLine.value;
        // Limpia las opciones actuales, dejando solo la primera
        categorySelect.innerHTML = '<option value="">Seleccione una categoría</option>';
        // Filtra y agrega las opciones que coinciden
        allOptions.forEach(option => {
            if (!selectedSaleLineId || option.dataset.saleLine === selectedSaleLineId) {
                categorySelect.appendChild(option);
            }
        });
        categorySelect.value = '';
    });
});
</script>
@endsection