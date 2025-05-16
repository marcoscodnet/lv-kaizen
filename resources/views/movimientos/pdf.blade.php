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

        .header {
            display: table;
            width: 100%;
            table-layout: fixed;
        }

        .left-section{
            display: table-cell;
            width: 50%;
            vertical-align: middle;
            text-align: center;
            border: 1px solid #000;
            padding: 15px;
            box-sizing: border-box;
            height: 150px;
        }

        .right-section {
            display: table-cell;
            width: 50%;
            text-align: left;
            border: 1px solid #000;
            padding: 15px;
            box-sizing: border-box;
            height: 150px;
        }

        .logo {
            width: 300px;
            margin-bottom: 10px;
        }

        .datos-sucursal,
        .remito-info {
            font-size: 14px;
            line-height: 1.6;
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
            background-color: #dddddd; /* gris claro */
            text-align: center;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9; /* opcional para filas intercaladas */
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 80px;

            background: white;
            padding: 10px 20px;
            box-sizing: border-box;
            font-size: 12px;
            display: flex;
            justify-content: space-around;
            align-items: center;
        }

        .footer div {



            padding-top: 5px;
        }

        .watermark {
            position: absolute;
            top: 50%;
            left: -40px; /* margen izquierdo */
            transform: translateY(-50%) rotate(-90deg);
            transform-origin: left center;
            font-size: 20px;
            font-weight: bold;
            color: rgba(0, 0, 0, 0.1);
            user-select: none;
            pointer-events: none;
            white-space: nowrap;
        }


    </style>
</head>
<body>
<div class="watermark">ORIGINAL</div>

<div class="header">

    <div class="left-section">
        <img src="{{ public_path('/images/logo_kaisen.png') }}" alt="KAIZEN Logo" class="logo">
        <div class="datos-sucursal">
            {{ $origen->direccion ?? 'Sucursal 1' }}<br>
            {{ $origen->localidad->nombre ?? 'Calle Falsa 123' }}<br>
            Tel.: {{ $origen->telefono ?? '0221-4692220' }}<br>
            <span style="font-size: 8px">I.V.A. RESPONSABLE INSCRIPTO</span>
        </div>
    </div>


    <div class="right-section" style="text-align: left;margin-left: 250px;margin-top: 0px;">

        <div class="remito-info">
            <div class="title" style="font-weight: bold;">REMITO<span style="font-size: 8px;margin-left: 10px;">DOCUMENTO NO VALIDO COMO FACTURA</span></div>
            <strong>Nº:</strong> {{ $remito ?? '7005' }} <br>
            <strong>Fecha:</strong> {{ date('d/m/Y', strtotime($fecha)) }}

        </div>
    </div>
</div>

<div class="content" style="border: 1px solid #000;">
    <div class="content" style="margin-top: 10px;margin-left: 5px;">
        <span style="display: inline-block;width: 100px;">Sucursal Destino:</span> <span style="display: inline-block; border-bottom: 1px solid #ccc;width: 585px;">{{$destino->nombre}}</span>
    </div>
    <div class="content" style="margin-top: 10px;margin-left: 5px;">
        <span style="display: inline-block;width: 60px;">Dirección:</span> <span style="display: inline-block; border-bottom: 1px solid #ccc;width: 325px;">{{$destino->direccion}}</span>
        <span style="display: inline-block;width: 10px;"></span>
        <span style="display: inline-block;width: 60px;">Localidad:</span> <span style="display: inline-block; border-bottom: 1px solid #ccc;width: 220px;">{{$destino->localidad->nombre}}</span>
    </div>
</div>
<div class="content" style="border: 1px solid #000;">
    <div class="content" style="margin-top: 10px;margin-bottom: 10px;margin-left: 5px;">
        Remitimos a ud. lo siguiente:
    </div>


</div>
<div class="content">


    <table>
        <tr style="background-color: #999999;">
            <th>Código</th><th>Producto</th><th>Motor</th><th>Cuadro</th>
        </tr>
        @foreach($unidades as $unidadMovimiento)
            <tr>
                <td>{{$unidadMovimiento->unidad->id}}</td><td>{{$unidadMovimiento->unidad->producto->tipoUnidad->nombre}} - {{$unidadMovimiento->unidad->producto->marca->nombre}} - {{$unidadMovimiento->unidad->producto->modelo->nombre}} - {{$unidadMovimiento->unidad->producto->color->nombre}}</td>
                <td>{{$unidadMovimiento->unidad->motor }}</td><td>{{$unidadMovimiento->unidad->cuadro}}</td>
            </tr>
        @endforeach
    </table>
</div>

<div class="footer">
    <div class="content" style="margin-top: 10px;margin-left: 5px;">
        <span style="display: inline-block;width: 60px;">Firma:</span> <span style="display: inline-block; border-bottom: 1px solid #ccc;width: 325px;"></span>
        <span style="display: inline-block;width: 10px;"></span>
        <span style="display: inline-block;width: 90px;">Recibido por:</span> <span style="display: inline-block; border-bottom: 1px solid #ccc;width: 190px;"></span>
    </div>
    <div class="content" style="margin-top: 10px;margin-left: 5px;">
        <span style="display: inline-block;width: 90px;">Entregado por:</span> <span style="display: inline-block; border-bottom: 1px solid #ccc;width: 295px;"></span>
        <span style="display: inline-block;width: 10px;"></span>
        <span style="display: inline-block;width: 60px;">DNI:</span> <span style="display: inline-block; border-bottom: 1px solid #ccc;width: 220px;"></span>
    </div>
</div>
<div style="page-break-after: always;"></div>
<div class="watermark">DUPLICADO</div>
<div class="header">

    <div class="left-section">
        <img src="{{ public_path('/images/logo_kaisen.png') }}" alt="KAIZEN Logo" class="logo">
        <div class="datos-sucursal">
            {{ $origen->direccion ?? 'Sucursal 1' }}<br>
            {{ $origen->localidad->nombre ?? 'Calle Falsa 123' }}<br>
            Tel.: {{ $origen->telefono ?? '0221-4692220' }}<br>
            <span style="font-size: 8px">I.V.A. RESPONSABLE INSCRIPTO</span>
        </div>
    </div>


    <div class="right-section" style="text-align: left;margin-left: 250px;margin-top: 0px;">

        <div class="remito-info">
            <div class="title" style="font-weight: bold;">REMITO<span style="font-size: 8px;margin-left: 10px;">DOCUMENTO NO VALIDO COMO FACTURA</span></div>
            <strong>Nº:</strong> {{ $remito ?? '7005' }} <br>
            <strong>Fecha:</strong> {{ date('d/m/Y', strtotime($fecha)) }}

        </div>
    </div>
</div>

<div class="content" style="border: 1px solid #000;">
    <div class="content" style="margin-top: 10px;margin-left: 5px;">
        <span style="display: inline-block;width: 100px;">Sucursal Destino:</span> <span style="display: inline-block; border-bottom: 1px solid #ccc;width: 585px;">{{$destino->nombre}}</span>
    </div>
    <div class="content" style="margin-top: 10px;margin-left: 5px;">
        <span style="display: inline-block;width: 60px;">Dirección:</span> <span style="display: inline-block; border-bottom: 1px solid #ccc;width: 325px;">{{$destino->direccion}}</span>
        <span style="display: inline-block;width: 10px;"></span>
        <span style="display: inline-block;width: 60px;">Localidad:</span> <span style="display: inline-block; border-bottom: 1px solid #ccc;width: 220px;">{{$destino->localidad->nombre}}</span>
    </div>
</div>
<div class="content" style="border: 1px solid #000;">
    <div class="content" style="margin-top: 10px;margin-bottom: 10px;margin-left: 5px;">
        Remitimos a ud. lo siguiente:
    </div>


</div>
<div class="content">


    <table>
        <tr style="background-color: #999999;">
            <th>Código</th><th>Producto</th><th>Motor</th><th>Cuadro</th>
        </tr>
        @foreach($unidades as $unidadMovimiento)
            <tr>
                <td>{{$unidadMovimiento->unidad->id}}</td><td>{{$unidadMovimiento->unidad->producto->tipoUnidad->nombre}} - {{$unidadMovimiento->unidad->producto->marca->nombre}} - {{$unidadMovimiento->unidad->producto->modelo->nombre}} - {{$unidadMovimiento->unidad->producto->color->nombre}}</td>
                <td>{{$unidadMovimiento->unidad->motor }}</td><td>{{$unidadMovimiento->unidad->cuadro}}</td>
            </tr>
        @endforeach
    </table>
</div>

<div class="footer">
    <div class="content" style="margin-top: 10px;margin-left: 5px;">
        <span style="display: inline-block;width: 60px;">Firma:</span> <span style="display: inline-block; border-bottom: 1px solid #ccc;width: 325px;"></span>
        <span style="display: inline-block;width: 10px;"></span>
        <span style="display: inline-block;width: 90px;">Recibido por:</span> <span style="display: inline-block; border-bottom: 1px solid #ccc;width: 190px;"></span>
    </div>
    <div class="content" style="margin-top: 10px;margin-left: 5px;">
        <span style="display: inline-block;width: 90px;">Entregado por:</span> <span style="display: inline-block; border-bottom: 1px solid #ccc;width: 295px;"></span>
        <span style="display: inline-block;width: 10px;"></span>
        <span style="display: inline-block;width: 60px;">DNI:</span> <span style="display: inline-block; border-bottom: 1px solid #ccc;width: 220px;"></span>
    </div>
</div>
</body>
</html>
