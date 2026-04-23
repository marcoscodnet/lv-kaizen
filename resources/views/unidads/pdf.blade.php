<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; table-layout: fixed;}
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #222; padding: 5px; word-wrap: break-word;}
        th { background: #f0f0f0; }
        h3 { margin: 0; padding: 0; }
    </style>
</head>
<body>

<!-- Cabecera con imagen -->
<div style="text-align: center; margin-bottom: 20px;">
    <img src="{{ public_path('images/logo_kaisen.png') }}" width="180">
    <h2 style="margin-top: 10px;">Listado de Unidades</h2>
</div>


{{-- ============================ --}}
{{--        FILTROS APLICADOS     --}}
{{-- ============================ --}}

<div class="filtros">

    <p><strong>Marca:</strong> {{ $marcaNombre }}</p>
    <p><strong>Fecha desde:</strong> {{ $fechaDesde ? date('d/m/Y', strtotime($fechaDesde)) : '—' }}</p>
    <p><strong>Fecha hasta:</strong> {{ $fechaHasta ? date('d/m/Y', strtotime($fechaHasta)) : '—' }}</p>
    <p><strong>Búsqueda:</strong> {{ $busqueda ?: '—' }}</p>
</div>

<table>
    <thead>
    <tr>
        <th>Tipo</th>
        <th>Marca</th>
        <th>Modelo</th>
        <th>Color</th>
        <th>Sucursal</th>
        <th>Ingreso</th>
        <th>Año</th>
        <th>Envío</th>
        <th>Motor</th>
        <th>Cuadro</th>
    </tr>
    </thead>
    <tbody>
    @foreach($unidads as $p)
        <tr>
            <td>{{ $p->tipo_unidad_nombre }}</td>
            <td>{{ $p->marca_nombre }}</td>
            <td>{{ $p->modelo_nombre }}</td>
            <td>{{ $p->color_nombre }}</td>
            <td>{{ $p->sucursal_nombre }}</td>
            <td>{{ $p->ingreso ? date('d/m/Y', strtotime($p->ingreso)) : '—' }}</td>
            <td>{{ $p->year }}</td>
            <td>{{ $p->envio }}</td>
            <td>{{ $p->motor }}</td>
            <td>{{ $p->cuadro }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

</body>
</html>
