<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #222; padding: 5px; }
        th { background: #f0f0f0; }
        h3 { margin: 0; padding: 0; }
    </style>
</head>
<body>

<!-- Cabecera con imagen -->
<div style="text-align: center; margin-bottom: 20px;">
    <img src="{{ public_path('images/logo_kaisen.png') }}" width="180">
    <h2 style="margin-top: 10px;">Listado de Clientes</h2>
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
        <th>Nombre</th>
        <th>Documento</th>
        <th>Teléfono</th>
        <th>Celular</th>
        <th>Localidad</th>
        <th>Provincia</th>
        <th>Nacimiento</th>
        <th>E-mail</th>
    </tr>
    </thead>
    <tbody>
    @foreach($clientes as $p)
        <tr>
            <td>{{ $p->nombre }}</td>
            <td>{{ $p->documento }}</td>
            <td>{{ $p->particular }}</td>
            <td>{{ $p->celular }}</td>
            <td>{{ $p->localidad_nombre }}</td>
            <td>{{ $p->provincia_nombre }}</td>
            <td>{{ $p->nacimiento ? date('d/m/Y', strtotime($p->nacimiento)) : '—' }}</td>
            <td>{{ $p->email }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

</body>
</html>
