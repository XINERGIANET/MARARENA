<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>
        REPORTE DE {{ strtoupper($tipo === 'egreso' ? 'COMPRAS' : 'EGRESOS') }}
    </title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 15px;
            line-height: 1.3;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #333;
        }
        
        .filters {
            background-color: #f8f9fa;
            padding: 8px;
            margin-bottom: 15px;
            border-radius: 3px;
            border: 1px solid #ddd;
        }
        
        .purchase-block {
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            overflow: hidden;
        }
        
        .purchase-header {
            background-color: #007bff;
            color: white;
            padding: 10px;
            font-weight: bold;
            font-size: 12px;
        }
        
        .supplier-info {
            background-color: #f1f3f4;
            padding: 8px 10px;
            border-bottom: 1px solid #ddd;
        }
        
        .details-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .details-table th {
            background-color: #6c757d;
            color: white;
            padding: 6px;
            text-align: left;
            font-size: 10px;
        }
        
        .details-table td {
            padding: 5px 6px;
            border-bottom: 1px solid #eee;
        }
        
        .details-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .purchase-total {
            background-color: #e9ecef;
            padding: 8px 10px;
            text-align: right;
            font-weight: bold;
            color: #495057;
        }
        
        .grand-total {
            margin-top: 20px;
            text-align: center;
            background-color: #28a745;
            color: white;
            padding: 15px;
            font-size: 16px;
            font-weight: bold;
            border-radius: 5px;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #6c757d;
            font-style: italic;
        }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>REPORTE GENERAL DE {{ strtoupper($tipo === 'egreso' ? 'EGRESOS' : 'COMPRAS') }}</h1>
        <p>Generado el: {{ date('d/m/Y H:i:s') }}</p>
    </div>

    <div class="filters">
        <strong>Filtros aplicados:</strong>
        @if($filters['start_date'] || $filters['end_date'] || $filters['supplier_name'])
            @if($filters['start_date'])
                Desde: {{ date('d/m/Y', strtotime($filters['start_date'])) }}
            @endif
            @if($filters['end_date'])
                Hasta: {{ date('d/m/Y', strtotime($filters['end_date'])) }}
            @endif
            @if($filters['supplier_name'])
                | Proveedor: {{ $filters['supplier_name'] }}
            @endif
       @else
            Todos los {{ $tipo === 'egreso' ? 'egresos' : 'compras' }}
        @endif
    </div>

    @if($purchases->count() > 0)
            <div class="purchase-block">

                
                <!-- Detalles de la compra -->
                @if($purchases->count() > 0)
                    <table class="details-table">
                        <thead>
                            <tr>
                                <th>Proveedor</th>
                                <th style="width: 80px;">Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchases as $purchase)
                                <tr>
                                    <td>{{ $purchase->company_name }}</td>
                                    <td>S/ {{ number_format($purchase->total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div style="padding: 10px; color: #6c757d; font-style: italic;">
                        Sin compras registrados
                    </div>
                @endif
                

            </div>

        <!-- Total general -->
        <div class="grand-total">
            TOTAL GENERAL DE {{ strtoupper($tipo === 'egreso' ? 'EGRESOS' : 'COMPRAS') }}: S/ {{ number_format($totalGeneral, 2) }}
        </div>
    @else
        <div class="no-data">
            <h3>No se encontraron {{ $tipo === 'egreso' ? 'egresos' : 'compras' }}</h3>
            <p>No hay {{ $tipo === 'egreso' ? 'egresos' : 'compras' }} que coincidan con los filtros aplicados.</p>
        </div>
    @endif
</body>
</html>