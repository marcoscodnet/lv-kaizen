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
            display: flex;
            justify-content: space-between;
            align-items: flex-start; /* Alineación superior */
            padding: 20px;
            border-bottom: 1px solid #ccc;
            box-sizing: border-box;
        }

        .left-section,
        .right-section {
            width: 49%;
            box-sizing: border-box;
        }

        .left-section {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .logo {
            width: 100px;
            margin-bottom: 10px;
            display: block;
        }

        .datos-sucursal {
            font-size: 12px;
            line-height: 1.5;
        }

        .right-section {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            justify-content: flex-start;
        }

        .remito-info {
            text-align: right;
            font-size: 14px;
            line-height: 1.6;
        }

        .title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
<div class="header">
    <!-- Izquierda: Logo + Sucursal -->
    <div class="left-section">
        <img src="{{ public_path('/images/logo_kaisen.png') }}" alt="KAIZEN Logo" class="logo">
        <div class="datos-sucursal">
            <strong>Destino:</strong> {{ $sucursal->nombre ?? 'Sucursal 1' }}<br>
            {{ $sucursal->direccion ?? 'Calle Falsa 123' }}<br>
            Tel.: {{ $sucursal->telefono ?? '0221-4692220' }}<br>
            <span style="font-size: 10px">I.V.A. RESPONSABLE INSCRIPTO</span>
        </div>
    </div>

    <!-- Derecha: Título REMITO + Datos -->
    <div class="right-section">
        <div class="remito-info">
            <div class="title">REMITO</div>
            <strong>Fecha:</strong> {{ $fecha ?? '15/05/2025' }} <br>
            <strong>Remito Nº:</strong> {{ $numeroRemito ?? '7005' }}
        </div>
    </div>
</div>
</body>
</html>
