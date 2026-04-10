<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de {{ $tipo === 'egreso' ? 'Egresos' : 'Compras' }} por Productos</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            color: #333;
            font-size: 18px;
        }
        .header h2 {
            margin: 5px 0;
            color: #666;
            font-size: 14px;
            font-weight: normal;
        }
        .filters {
            background-color: #f5f5f5;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .filters strong {
            color: #333;
        }
        .product-section {
            margin-bottom: 25px;
            border: 1px solid #ddd;
            border-radius: 5px;
            overflow: hidden;
        }
        .product-header {
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            font-weight: bold;
            font-size: 14px;
        }
        .product-summary {
            background-color: #f9f9f9;
            padding: 8px 10px;
            font-weight: bold;
            border-bottom: 1px solid #ddd;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }
        .details-table th {
            background-color: #2196F3;
            color: white;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
        }
        .details-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #eee;
            font-size: 11px;
        }
        .details-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .total-general {
            background-color: #333;
            color: white;
            padding: 15px;
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin-top: 20px;
            border-radius: 5px;
        }
        .no-data {
            text-align: center;
            padding: 40px;
            color: #666;
            font-style: italic;
        }
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>REPORTE DE {{ strtoupper($tipo === 'egreso' ? 'Egresos' : 'Compras') }} POR PRODUCTOS</h1>
        <h2>Resumen Detallado de {{ $tipo === 'egreso' ? 'Egresos' : 'Compras' }}</h2>
        <p>Generado el {{ date('d/m/Y H:i:s') }}</p>
    </div>

    <div class="filters">
        <strong>Filtros aplicados:</strong><br>
        @if($startDate)
            <strong>Fecha desde:</strong> {{ date('d/m/Y', strtotime($startDate)) }} |
        @endif
        @if($endDate)
            <strong>Fecha hasta:</strong> {{ date('d/m/Y', strtotime($endDate)) }} |
        @endif
        @if(isset($filters['supplier_id']) && $filters['supplier_id'])
            <strong>Proveedor:</strong> {{ $filters['supplier_name'] ?? 'ID: ' . $filters['supplier_id'] }}
        @endif
        @if(!$startDate && !$endDate && !isset($filters['supplier_id']))
            Sin filtros específicos - Todos los {{ $tipo === 'egreso' ? 'egresos' : 'compras' }}
        @endif
    </div>

    @if(isset($message))
        <div class="no-data">
            <h3>{{ $message }}</h3>
        </div>
    @elseif(empty($productsSummary))
        <div class="no-data">
            <h3>No hay datos para mostrar</h3>
        </div>
    @else
        @foreach($productsSummary as $productId => $product)
            <div class="product-section">
                <div class="product-header">
                    {{ $product['name'] }}
                </div>
                
                <div class="product-summary">
                    Cantidad Total: <strong>{{ number_format($product['total_quantity'], 2) }}</strong> | 
                    Subtotal: <strong>S/ {{ number_format($product['total_subtotal'], 2) }}</strong> 
                </div>
                @if(!empty($product['details']))
                    <table class="details-table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Proveedor</th>
                                <th>N° Factura</th>
                                <th class="text-right">Cantidad</th>
                                <th class="text-right">Precio Unit.</th>
                                <th class="text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($product['details'] as $detail)
                                <tr>
                                    <td>{{ date('d/m/Y', strtotime($detail['purchase_date'])) }}</td>
                                    <td>{{ $detail['supplier'] }}</td>
                                    <td class="text-center">{{ $detail['invoice_number'] }}</td>
                                    <td class="text-right">{{ number_format($detail['quantity'], 2) }}</td>
                                    <td class="text-right">S/ {{ number_format($detail['unit_price'], 2) }}</td>
                                    <td class="text-right">S/ {{ number_format($detail['subtotal'], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        @endforeach

        <div class="total-general">
            TOTAL GENERAL: S/ {{ number_format($totalGeneral, 2) }}
        </div>
    @endif
</body>
</html>