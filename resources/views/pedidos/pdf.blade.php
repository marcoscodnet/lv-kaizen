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
    <h2 style="margin-top: 10px;">Listado de Pedidos</h2>
</div>

<!-- Filtros aplicados -->
<p><strong>Filtros aplicados:</strong></p>
<p>
    @if(!empty($busqueda))
        <strong>Búsqueda:</strong> {{ $busqueda }}<br>
    @endif

</p>

<table>
    <thead>
    <tr>
        <th>Fecha</th>
        <th>Código</th>
        <th>Nueva</th>
        <th>Observaciones</th>
        <th>Estado</th>

    </tr>
    </thead>
    <tbody>
    @foreach($pedidos as $p)
        <tr>
            <td>{{ $p->fecha ? date('d/m/Y', strtotime($p->fecha)) : '—' }}</td>
            <td>{{ $p->pieza_codigo }}</td>
            <td>{{ $p->nueva }}</td>
            <td>{{ $p->observacion }}</td>
            <td>{{ $p->estado }}</td>

        </tr>
    @endforeach
    </tbody>
</table>

</body>
</html>
