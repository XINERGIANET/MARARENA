@extends('layouts.app')

@section('header')
<h1>Indicadores de Gestión</h1>
<p>Reportes Variados</p>
@endsection

@section('content')
<div class="container-fluid content-inner mt-n5 py-0" style="height:100vh; position:relative; padding: 0; width: 100%; max-width: 95%; margin: 0 auto;">
    <div class="card shadow h-100" style="height: 100%; width: 100%;">
        <div class="card-body p-0 h-100" style="position:relative; height: 110%; margin: 0;">
            
            <iframe
                title="Tambo_KPI"
                src="https://app.powerbi.com/view?r=eyJrIjoiZTk2ZjYzZDEtYzZmYS00NDM4LWJmNWItMGQ0YjI5YTUwMTZiIiwidCI6IjhjNTMxOGIzLTFiMWYtNDY1Ni1hNGU4LTc0MjE3MzE5NWZjMiJ9"
                style="border:0; width:100%; height:100%; margin: 0;"
                allowfullscreen
            ></iframe>

            <!-- Capa que oculta el footer completo (logo, paginación, zoom) -->
            <div style="position:absolute; bottom:0; left:0; width:100%; height:65px; background:#fff;"></div>
        </div>
    </div>
</div>
@endsection