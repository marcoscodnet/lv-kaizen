<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Servicios</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            table-layout: fixed;
        }

        table th, table td {
            border: 1px solid #333;
            padding: 5px 6px;
            text-align: left;
            word-wrap: break-word;
        }

        table th {
            background: #f0f0f0;
        }

        h2 {
            margin-bottom: 5px;
            text-align: center;
        }

        .filtros {
            margin-bottom: 10px;
            font-size: 13px;
        }

        .filtros strong {
            display: inline-block;
            width: 120px;
        }
    </style>
</head>

<body>
<!-- Cabecera con imagen -->
<div style="text-align: center; margin-bottom: 20px;">
    <img src="{{ public_path('images/logo_kaisen.png') }}" width="180">
    <h2 style="margin-top: 10px;">Listado de Venta de Piezas</h2>
</div>


{{-- ============================ --}}
{{--        FILTROS APLICADOS     --}}
{{-- ============================ --}}

<div class="filtros">
    <p><strong>Vendedor:</strong> {{ $userNombre }}</p>

    <p><strong>Fecha desde:</strong> {{ $fechaDesde ? date('d/m/Y', strtotime($fechaDesde)) : '—' }}</p>
    <p><strong>Fecha hasta:</strong> {{ $fechaHasta ? date('d/m/Y', strtotime($fechaHasta)) : '—' }}</p>
    <p><strong>Búsqueda:</strong> {{ $busqueda ?: '—' }}</p>
</div>

{{-- ============================ --}}
{{--        TABLA PRINCIPAL       --}}
{{-- ============================ --}}
<table>
    <thead>
    <tr>
        <th>Fecha</th>
        <th>Cliente</th>
        <th>Pedido</th>
        <th>Destino</th>
        <th>Monto</th>
        <th>Sucursal</th>
        <th>Vendedor</th>
        <th>Piezas</th>
    </tr>
    </thead>

    <tbody>
    @forelse ($piezas as $p)
        <tr>
            <td>{{ $p->fecha ? date('d/m/Y', strtotime($p->fecha)) : '—' }}</td>
            <td>{{ $p->cliente }}</td>
            <td>{{ $p->pedido }}</td>
            <td>{{ $p->destino }}</td>
            <td>{{ $p->precio_total }}</td>

            <td>{{ $p->sucursal_nombre }}</td>
            <td>{{ $p->usuario_nombre }}</td>
            <td>{{ $p->piezas_codigos }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="8" style="text-align: center; padding: 20px;">
                No se encontraron ventas con los filtros aplicados.
            </td>
        </tr>
    @endforelse
    </tbody>
</table>

</body>
</html>


