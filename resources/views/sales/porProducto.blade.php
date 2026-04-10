@extends('layouts.app')


@section('header')
<h2>Ventas por producto</h2>
<p>Lista total de ventas acumuladas por producto</p>
@endsection

@section('nav')
<ul class="nav justify-content-center py-1">
	<li class="nav-item">
		<a class="nav-link" href="{{ route('sales.historic') }}">Histórico</a>
	</li>
	<li class="nav-item">
		<a class="nav-link" href="{{ route('sales.deleted') }}">Anuladas</a>
	</li>
    <li class="nav-item">
		<a class="nav-link bg-primary text-white rounded" href="{{ route('sales.porProducto') }}">Por producto</a>
	</li>
</ul>
@endsection

@section('content')
<div class="container-fluid content-inner mt-n5 py-0">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">

                <div class="card-body border-bottom">
                    <form action="" id="formFilter">
                        <div class="row d-flex">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Fecha inicial</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" value="{{ request()->start_date ? request()->start_date : '' }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Fecha final</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" value="{{ request()->end_date ? request()->end_date : '' }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Tipo de venta</label>
                                <select name="type_sale" class="form-select">
                                    <option value="">Todas</option>
                                    <option value="0" {{ request('type_sale') === '0' ? 'selected' : '' }}>Ropa</option>
                                    <option value="1" {{ request('type_sale') === '1' ? 'selected' : '' }}>Cafetería</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Productos</label>

                                <select name="product_id" id="product_id" class="form-select" style="width:100%">
                                    <option value="">{{ __('Todos') }}</option>
                                    @foreach($products as $p)
                                        @php
                                            $saleLine = strtolower(optional(optional($p->category)->sale_line)->name ?? '');
                                        @endphp
                                        <option value="{{ $p->id }}"
                                                data-sale-line="{{ $saleLine }}"
                                                {{ (string) request('product_id') === (string) $p->id ? 'selected' : '' }}>
                                            {{ $p->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col d-flex align-items-end mt-4">
                                <div class=" w-50s me-2">
                                    <button type="submit" class="btn btn-primary w-100" id="btnFiltrar">Filtrar</button>
                                </div>
                                <div class=" w-50s me-2">
                                    <a href="{{ route('sales.porProducto') }}" class="btn btn-warning w-100" id="btnLimpiar">Limpiar</a>
                                </div>
                            </div>
                            <div class="col-12 mt-4">
                                <div class="d-flex justify-content-end">
                                    <div>
                                        <h5>
                                            <strong>Total: S/ {{ number_format($total, 2, '.', ',') }}</strong>
                                        </h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>


                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Monto</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($consulta as $row)
                                <tr>
                                    <td>{{ $row->product_name }}</td>
                                    <td>{{ $row->total_quantity }}</td>
                                    <td>{{ $row->total_amount }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div id="global-spinner" class="d-flex justify-content-center align-items-center spinner-hidden"
    style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255, 255, 255, 0.8); z-index: 1050;">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Cargando...</span>
    </div>
</div>
<style>
    .spinner-hidden {
        display: none !important;
    }

    .spinner-visible {
        display: flex !important;
    }

    .numeric-keypad {
        max-width: 300px;
        margin: 0 auto;
    }

    .num-btn {
        padding: 10px 0;
    }

    .swal-confirm-btn {
        background-color: #dc3545 !important; /* rojo Bootstrap */
        color: #fff !important;
        border: none;
        border-radius: 6px;
        padding: 8px 20px;
        margin-right: 10px;
        font-weight: 500;
    }

    .swal-cancel-btn {
        background-color: #6c757d !important; /* gris Bootstrap */
        color: #fff !important;
        border: none;
        border-radius: 6px;
        padding: 8px 20px;
        font-weight: 500;
    }
</style>
@endsection

@section('scripts')

<script>
    var products = @json($products);

    (function(){
        function lc(s){ return s ? String(s).toLowerCase().trim() : ''; }

        function optionFragment(list, currentVal){
            const frag = document.createDocumentFragment();
            const all = document.createElement('option');
            all.value = '';
            all.text = 'Todos';
            frag.appendChild(all);

            list.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.id;
                opt.text = p.name;
                const saleLineName = (p.category && p.category.sale_line && p.category.sale_line.name)
                    ? p.category.sale_line.name
                    : (p.category && p.category.sale_line) ? p.category.sale_line : '';
                opt.setAttribute('data-sale-line', lc(saleLineName));
                if (String(currentVal) === String(p.id)) opt.selected = true;
                frag.appendChild(opt);
            });

            return frag;
        }

        $(function(){
            $('#product_id').select2({
                placeholder: 'Todos',
                allowClear: true,
                width: 'resolve',
                theme: 'bootstrap-5',
            });

            function refreshOptions(){
                const typeText = $('select[name="type_sale"] option:selected').text() || '';
                let needle = lc(typeText);
                // si la opción es "Todas" o vacío, mostrar todos los productos
                if (!needle || needle === 'todas') needle = '';

                const current = $('#product_id').val();

                const filtered = products.filter(p => {
                    const saleLineName = (p.category && p.category.sale_line && p.category.sale_line.name)
                        ? p.category.sale_line.name
                        : (p.category && p.category.sale_line) ? p.category.sale_line : '';
                    return !needle || lc(saleLineName) === needle;
                });

                const $sel = $('#product_id');
                $sel[0].innerHTML = '';
                $sel[0].appendChild(optionFragment(filtered, current));
                $sel.trigger('change');
            }

            refreshOptions();
            $('select[name="type_sale"]').on('change', refreshOptions);
        });
    })();
</script>

<style>
    .spinner-hidden {
        display: none !important;
    }

    .spinner-visible {
        display: flex !important;
        z-index: 2000 !important;
    }
    .ver-foto-disabled {
        color: #aaa !important;
        pointer-events: none;
        text-decoration: none !important;
        cursor: not-allowed;
    }
</style>
@endsection