<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Movimientos</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        table th, table td {
            border: 1px solid #333;
            padding: 5px 6px;
            text-align: left;
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

<h2>Listado de Movimientos</h2>

{{-- ============================ --}}
{{--        FILTROS APLICADOS     --}}
{{-- ============================ --}}

<div class="filtros">
    <p><strong>Usuario:</strong> {{ $usuarioFiltrado }}</p>
    <p><strong>Búsqueda:</strong> {{ $busqueda ?: '—' }}</p>
</div>

{{-- ============================ --}}
{{--        TABLA PRINCIPAL       --}}
{{-- ============================ --}}
<table>
    <thead>
    <tr>

        <th>Usuario</th>
        <th>Origen</th>
        <th>Destino</th>
        <th>Fecha</th>
        <th>Cuadros</th>
        <th>Motores</th>
    </tr>
    </thead>

    <tbody>
    @forelse ($movimientos as $m)
        <tr>

            <td>{{ $m['usuario_nombre'] }}</td>
            <td>{{ $m['origen_nombre'] }}</td>
            <td>{{ $m['destino_nombre'] }}</td>
            <td>{{ $m['fecha'] }}</td>
            <td>{{ $m['cuadros'] }}</td>
            <td>{{ $m['motores'] }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="7" style="text-align: center; padding: 20px;">
                No se encontraron movimientos con los filtros aplicados.
            </td>
        </tr>
    @endforelse
    </tbody>
</table>

</body>
</html>
