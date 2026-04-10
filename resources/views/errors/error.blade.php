@extends('layouts.app')

@section('header')
<h2>Sistema Temporalmente No Disponible</h2>
<p>Estamos trabajando para resolver el inconveniente</p>
@endsection

@section('content')
<style>
    .maintenance-container {
        min-height: 60vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .maintenance-icon {
        font-size: 4rem;
        color: #ffc107;
        margin-bottom: 1.5rem;
    }
    
    .maintenance-card {
        max-width: 600px;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
</style>

<div class="container-fluid content-inner mt-n5 py-0">
    <div class="maintenance-container">
        <div class="text-center">
            <div class="card maintenance-card">
                <div class="card-body p-5">
                    <div class="maintenance-icon">
                        <i class="fas fa-tools"></i>
                    </div>
                    
                    <h3 class="card-title mb-3">¡Ups! Algo salió mal</h3>
                    
                    <p class="text-muted mb-4">
                        {{ $message ?? 'Estamos experimentando problemas técnicos temporales. Nuestro equipo ya está trabajando en solucionarlo.' }}
                    </p>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>¿Qué puedes hacer?</strong>
                        <ul class="list-unstyled mb-0 mt-2">
                            <li>• Espera unos minutos e intenta nuevamente</li>
                            <li>• Actualiza la página</li>
                            <li>• Contacta al soporte si el problema persiste</li>
                        </ul>
                    </div>
                    
                    <div class="mt-4">
                        <button onclick="window.location.reload()" class="btn btn-warning me-2">
                            <i class="fas fa-sync-alt me-2"></i>
                            Reintentar
                        </button>
                        
                        <a href="{{ url('/') }}" class="btn btn-primary">
                            <i class="fas fa-home me-2"></i>
                            Ir al Inicio
                        </a>
                    </div>
                    
                    <div class="mt-3">
                        <small class="text-muted">
                            Código de error: {{ date('YmdHis') }}-{{ Str::random(6) }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Auto reload cada 60 segundos (opcional)
    setTimeout(() => {
        console.log('Auto recargando página...');
        // window.location.reload();
    }, 60000);
</script>
@endsection