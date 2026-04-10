<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Compras - {{ $product->name }}</title>
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
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            color: #333;
            font-size: 20px;
        }
        .header h2 {
            margin: 5px 0;
            color: #4CAF50;
            font-size: 16px;
            font-weight: bold;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .filters {
            background-color: #f5f5f5;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            border-left: 4px solid #4CAF50;
        }
        .filters strong {
            color: #333;
        }
        .summary-box {
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            text-align: center;
        }
        .summary-box h3 {
            margin: 0 0 15px 0;
            font-size: 18px;
        }
        /* Cambio principal: usar tabla en lugar de flexbox */
        .summary-stats {
            width: 100%;
            border-collapse: separate;
            border-spacing: 10px;
        }
        .summary-stats td {
            background: rgba(255,255,255,0.2);
            padding: 10px 15px;
            border-radius: 5px;
            text-align: center;
            vertical-align: top;
            width: 25%;
        }
        .stat-label {
            font-size: 11px;
            opacity: 0.9;
            display: block;
            margin-bottom: 5px;
        }
        .stat-value {
            font-size: 16px;
            font-weight: bold;
            display: block;
        }
        .details-section {
            margin-top: 30px;
        }
        .section-title {
            background-color: #2196F3;
            color: white;
            padding: 12px;
            margin: 0 0 15px 0;
            border-radius: 5px;
            font-weight: bold;
            font-size: 14px;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            border-radius: 5px;
            overflow: hidden;
        }
        .details-table th {
            background-color: #2196F3;
            color: white;
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
        }
        .details-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #eee;
            font-size: 11px;
        }
        .details-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .details-table tr:hover {
            background-color: #f0f8ff;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .no-data {
            text-align: center;
            padding: 60px 20px;
            color: #666;
            background-color: #f9f9f9;
            border-radius: 10px;
            border: 2px dashed #ddd;
        }
        .no-data h3 {
            margin-top: 0;
            color: #999;
        }
        .no-data p {
            font-style: italic;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            color: #666;
            font-size: 10px;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>REPORTE DE COMPRAS POR PRODUCTO</h1>
        <h2>{{ $product->name }}</h2>
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
            Sin filtros específicos - Todos los registros
        @endif
    </div>

    @if(isset($productData['message']))
        <div class="no-data">
            <h3>{{ $productData['message'] }}</h3>
            <p>No hay compras registradas para este producto en el periodo seleccionado.</p>
        </div>
    @elseif(empty($productData['details']))
        <div class="no-data">
            <h3>Sin movimientos registrados</h3>
            <p>No se encontraron compras para este producto con los filtros aplicados.</p>
        </div>
    @else
        <div style="background-color: #4CAF50; color: white; padding: 20px; text-align: center; margin-bottom: 25px;">
            <h3 style="margin: 0;">{{ $product->nombre }}</h3>
            <p style="font-size: 16px; margin: 10px 0; font-weight: bold;">
                Cantidad Total: {{ number_format($productData['total_quantity'], 2) }}
            </p>
        </div>

        <div class="details-section">
            <div class="section-title">
                    Detalle de {{ $tipo === 'egreso' ? 'Egresos' : 'Compras' }} ({{ count($productData['details']) }} registros)
            </div>

            <table class="details-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Proveedor</th>
                        <th class="text-center">N° Factura</th>
                        <th class="text-right">Cantidad</th>
                        <th class="text-right">Precio Unitario</th>
                        <th class="text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($productData['details'] as $detail)
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
                <tfoot>
                    <tr style="background-color: #333; color: white; font-weight: bold;">
                        <td colspan="3" class="text-right"><strong>TOTALES:</strong></td>
                        <td class="text-right">{{ number_format($productData['total_quantity'], 2) }}</td>
                        <td class="text-right">---</td>
                        <td class="text-right">S/ {{ number_format($productData['total_subtotal'], 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif

    <div class="footer">
        Reporte generado automáticamente por el sistema de gestión de compras
    </div>
</body>
</html>