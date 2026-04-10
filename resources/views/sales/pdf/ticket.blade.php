<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket Venta #{{ $sale->id }}</title>
    <style>
        @page {
            margin: 2px 2px 2px 2px;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #000;
            margin: 0;
            padding: 1px;
            line-height: 1.15;
        }
        .center { text-align: center; }
        .line { border-top: 1px dashed #000; margin: 3px 0; }
        .row {
            width: 100%;
            border-collapse: collapse;
        }
        .row td {
            vertical-align: top;
            padding: 0;
        }
        .right { text-align: right; }
        .bold { font-weight: 700; }
        .small { font-size: 9px; }
        .mt-4 { margin-top: 2px; }
        .mt-6 { margin-top: 3px; }
        .mb-4 { margin-bottom: 2px; }
    </style>
</head>
<body>
    @php
        $subtotal = round(((float) $sale->total) / 1.18, 2);
        $igv = round(((float) $sale->total) - $subtotal, 2);
        $cliente = $sale->client->business_name ?? $sale->client->contact_name ?? $sale->client_name ?? 'Varios';
        $documento = $sale->client->document ?? 'N/A';
        $cajero = $sale->user->name ?? $sale->user->email ?? 'N/A';
        // $sede = config('app.name', 'MARARENA');
    @endphp

    <div class="center bold">MARARENA</div>
    <div class="center small">RUC 20606515627</div>
    {{-- <div class="center small">{{ $sede }}</div> --}}
    <div class="line"></div>

    <table class="row">
        <tr><td>Comprobante:</td><td class="right">{{ $sale->voucher_type ?? 'Ticket' }}</td></tr>
        <tr><td>Número:</td><td class="right">{{ $sale->number ?? ('VENTA-' . $sale->id) }}</td></tr>
        <tr><td>Fecha:</td><td class="right">{{ optional($sale->date)->format('d/m/Y H:i') }}</td></tr>
        <tr><td>Cliente:</td><td class="right">{{ $cliente }}</td></tr>
        <tr><td>Doc:</td><td class="right">{{ $documento }}</td></tr>
        <tr><td>Cajero:</td><td class="right">{{ $cajero }}</td></tr>
    </table>

    <div class="line"></div>
    <table class="row small">
        <tr>
            <td class="bold" style="width: 48%;">Producto</td>
            <td class="right bold" style="width: 14%;">Cant</td>
            <td class="right bold" style="width: 18%;">P.U.</td>
            <td class="right bold" style="width: 20%;">Subt</td>
        </tr>
        @foreach($sale->details as $detail)
            @php
                $nombre = $detail->product->name ?? 'Producto';
                $cantidad = (float) $detail->quantity;
                $precio = (float) $detail->unit_price;
                $sub = (float) $detail->subtotal;
            @endphp
            <tr>
                <td>{{ \Illuminate\Support\Str::limit($nombre, 26) }}</td>
                <td class="right">{{ number_format($cantidad, 2) }}</td>
                <td class="right">{{ number_format($precio, 2) }}</td>
                <td class="right">{{ number_format($sub, 2) }}</td>
            </tr>
        @endforeach
    </table>

    <div class="line"></div>
    <table class="row">
        <tr><td>OP. Gravada:</td><td class="right">S/{{ number_format($subtotal, 2) }}</td></tr>
        <tr><td>IGV (18%):</td><td class="right">S/{{ number_format($igv, 2) }}</td></tr>
        <tr><td class="bold">TOTAL:</td><td class="right bold">S/{{ number_format((float) $sale->total, 2) }}</td></tr>
    </table>

    @if($sale->payments->count() > 0)
        <div class="line"></div>
        <div class="bold mb-4">Pagos</div>
        <table class="row small">
            @foreach($sale->payments as $payment)
                @php
                    $metodo = $payment->payment_method->name ?? $payment->payment_method->nombre ?? 'Método';
                @endphp
                <tr>
                    <td>{{ $metodo }}</td>
                    <td class="right">S/{{ number_format((float) $payment->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    @if(!empty($sale->observation))
        <div class="line"></div>
        <div class="bold">Observación</div>
        <div class="small mt-4">{{ $sale->observation }}</div>
    @endif

    <div class="line"></div>
    <div class="center small mt-6">Gracias por su compra</div>
</body>
</html>
