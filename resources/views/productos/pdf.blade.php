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
    <h2 style="margin-top: 10px;">Listado de Productos</h2>
</div>

<!-- Filtros aplicados -->
<p><strong>Filtros aplicados:</strong></p>
<p>
    <strong>Discontinuos:</strong> {{ $discontinuoNombre }} <br>
    <strong>Debajo del mínimo:</strong> {{ $minimoNombre }}<br>
    @if(!empty($busqueda))
        <strong>Búsqueda:</strong> {{ $busqueda }}<br>
    @endif

</p>

<table>
    <thead>
    <tr>
        <th>Tipo</th>
        <th>Marca</th>
        <th>Modelo</th>
        <th>Color</th>
        <th>$ sugerido</th>
        <th>Stock mín.</th>
        <th>Stock Actual</th>
        <th>Discontinuo</th>
    </tr>
    </thead>
    <tbody>
    @foreach($productos as $p)
        <tr>
            <td>{{ $p->tipo_unidad_nombre }}</td>
            <td>{{ $p->marca_nombre }}</td>
            <td>{{ $p->modelo_nombre }}</td>
            <td>{{ $p->color_nombre }}</td>
            <td>{{ $p->precio }}</td>
            <td>{{ $p->minimo }}</td>
            <td>{{ $p->stock_actual }}</td>
            <td>{{ $p->discontinuo }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

</body>
</html>
