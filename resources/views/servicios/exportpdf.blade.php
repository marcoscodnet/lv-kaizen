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

<h2>Listado de Servicios</h2>

{{-- ============================ --}}
{{--        FILTROS APLICADOS     --}}
{{-- ============================ --}}

<div class="filtros">
    <p><strong>Usuario:</strong> {{ $usuarioFiltrado }}</p>
    <p><strong>Sucursal:</strong> {{ $sucursalNombre }}</p>
    <p><strong>Búsqueda:</strong> {{ $busqueda ?: '—' }}</p>
</div>

{{-- ============================ --}}
{{--        TABLA PRINCIPAL       --}}
{{-- ============================ --}}
<table>
    <thead>
    <tr>

        <th>Nro.</th>
        <th>Fecha</th>
        <th>Nro. motor</th>
        <th>Modelo</th>
        <th>Cgasis</th>
        <th>Cliente</th>
        <th>Técnico</th>
        <th>Monto</th>
        <th>Servicio</th>
        <th>Cerrado</th>
        <th>Sucursal</th>
        <th>Vendedor</th>
    </tr>
    </thead>

    <tbody>
    @forelse ($servicios as $p)
        <tr>
            <td>{{ $p->id }}</td>
            <td>{{ $p->carga ? date('d/m/Y', strtotime($p->carga)) : '—' }}</td>
            <td>{{ $p->motor }}</td>
            <td>{{ $p->chasis }}</td>
            <td>{{ $p->cliente }}</td>
            <td>{{ $p->mecanicos }}</td>
            <td>{{ $p->monto }}</td>

            <td>{{ $p->tipo_servicio }}</td>
            <td>{{ $p->pagado }}</td>
            <td>{{ $p->sucursal_nombre }}</td>
            <td>{{ $p->usuario_nombre }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="12" style="text-align: center; padding: 20px;">
                No se encontraron servicios con los filtros aplicados.
            </td>
        </tr>
    @endforelse
    </tbody>
</table>

</body>
</html>
