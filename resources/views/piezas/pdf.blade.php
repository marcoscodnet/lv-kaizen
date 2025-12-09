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
    <h2 style="margin-top: 10px;">Listado de Piezas</h2>
</div>

<!-- Filtros aplicados -->
<p><strong>Filtros aplicados:</strong></p>
<p>
    <strong>Sucursal:</strong> {{ $sucursalNombre }} <br>
    <strong>Ubicación:</strong> {{ $ubicacionNombre }}<br>
    <strong>Tipo:</strong> {{ $tipoNombre }}<br>
    @if(!empty($busqueda))
        <strong>Búsqueda:</strong> {{ $busqueda }}<br>
    @endif

</p>

<table>
    <thead>
    <tr>
        <th>Código</th>
        <th>Descripción</th>
        <th>Tipo</th>
        <th>Stock Mínimo</th>
        <th>Stock Actual</th>
        <th>Sucursal</th>
        <th>Ubicación</th>
        <th>Observaciones</th>
    </tr>
    </thead>
    <tbody>
    @foreach($piezas as $p)
        <tr>
            <td>{{ $p->codigo }}</td>
            <td>{{ $p->descripcion }}</td>
            <td>{{ $p->tipo_pieza }}</td>
            <td>{{ $p->stock_minimo }}</td>
            <td>{{ $p->stock_actual }}</td>
            <td>{{ $p->sucursal_nombre }}</td>
            <td>{{ $p->ubicacion_nombre }}</td>
            <td>{{ $p->observaciones }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

</body>
</html>
