<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
        }

        .duplicado {
            margin-bottom: 20px;
        }

        .linea-corte {
            border: none;
            border-top: 2px dashed #000;
            margin: 30px 0;
        }

        .header {
            display: table;
            width: 100%;
            table-layout: fixed;
        }

        .left-section {
            display: table-cell;
            width: 55%;
            vertical-align: middle;
            text-align: center;
            border: 1px solid #000;
            border-right: none;
            padding: 5px;
            box-sizing: border-box;
            height: 100px;
        }

        .right-section {
            display: table-cell;
            width: 45%;
            text-align: left;
            border: 1px solid #000;
            border-left: none;
            padding: 5px;
            box-sizing: border-box;
            height: 100px;
        }

        .logo {
            width: 380px;
        }

        .datos-sucursal,
        .remito-info {
            font-size: 14px;
            line-height: 1.6;
            margin-left: 10%;
            margin-top: 5%;
        }

        .fecha-info {
            font-size: 14px;
            line-height: 1.6;
            text-align: right;
            margin-right: 10%;
        }

        .row2 {
            display: table;
            width: 100%;
            table-layout: fixed;
            border: 1px solid #000;
            border-collapse: separate;
            border-spacing: 10px 0;
            padding: 10px;
            box-sizing: border-box;
        }

        .cell {
            display: table-cell;
            vertical-align: middle;
            padding: 10px 15px;
        }

        .left-section2 {
            width: 60%;
            text-align: left;
        }

        .right-section2 {
            width: 40%;
            text-align: right;
        }

        .title {
            font-size: 20px;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid black;
            padding: 8px;
        }

        th {
            background-color: #dddddd;
            text-align: center;
        }
    </style>
</head>
<body>

@for ($i = 0; $i < 2; $i++)
    <div class="duplicado">
        <div class="header">
            <div class="left-section">
                <img src="{{ public_path('/images/logo_pieza_izq.jpg') }}" alt="KAIZEN Logo" class="logo">
            </div>

            <div class="right-section" style="text-align: left; margin-left: 250px;">
                <img src="{{ public_path('/images/logo_pieza_der.jpg') }}" alt="KAIZEN Logo" style="width: 280px; margin-top: 15px;">
                <div class="remito-info">
                    N 0001 - {{ $remito ?? '7005' }}
                </div>
                <div class="fecha-info">
                    {{ date('d/m/Y', strtotime($fecha)) }}
                </div>
            </div>
        </div>

        <div class="row2">
            <div class="cell left-section2">
                {{ $destino }}
            </div>
            <div class="cell right-section2">
                Vendedor: {{ $vendedor }}
            </div>
        </div>

        <div class="content">
            <table>
                <tr style="background-color: #999999;">
                    <th>Código</th><th>Pieza</th><th>Suc. Origen</th><th>Cant. Pedida</th><th>Monto a Cobrar</th>
                </tr>
                @php $total = 0; @endphp
                @foreach($piezaVentapiezas as $piezaVentapieza)
                    @php $total += $piezaVentapieza->precio; @endphp
                    <tr>
                        <td>{{ $piezaVentapieza->pieza->codigo }}</td>
                        <td>{{ $piezaVentapieza->pieza->descripcion }}</td>
                        <td>{{ $piezaVentapieza->sucursal->nombre }}</td>
                        <td style="text-align: center">{{ $piezaVentapieza->cantidad }}</td>
                        <td style="text-align: right">${{ number_format($piezaVentapieza->precio, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="4" style="text-align: right; font-weight: bold;">TOTAL:</td>
                    <td style="text-align: right; font-weight: bold;">${{ number_format($total, 2, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        <div class="content" style="margin-top: 10px; margin-bottom: 10px; margin-left: 5px;">
            {{ $descripcion }}
        </div>
    </div>

    @if ($i == 0)
        <hr class="linea-corte">
    @endif
@endfor

</body>
</html>
