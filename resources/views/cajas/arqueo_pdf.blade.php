<h2>Arqueo Caja #{{ $caja->id }}</h2>
<p>Sucursal: {{ $caja->sucursal->nombre }}</p>
<p>Apertura: {{ $caja->apertura->format('d/m/Y H:i') }}</p>
<p>Usuario: {{ $caja->user->name }}</p>
<p>Estado: {{ ucfirst($caja->estado) }}</p>
<p>Monto Inicial: ${{ number_format($caja->inicial,2) }}</p>
@if($caja->estado === 'cerrada')
    <p>Monto Final: ${{ number_format($caja->final,2) }}</p>
@endif

<table border="1" width="100%" cellpadding="5" style="border-collapse: collapse;">
    <thead>
    <tr>
        <th>Fecha</th>
        <th>Concepto</th>
        <th>Entidad</th>
        <th>Tipo</th>
        <th>Monto</th>
        <th>Acreditado</th>
    </tr>
    </thead>
    <tbody>
    @foreach($caja->movimientos as $mov)
        <tr>
            <td>{{ $mov->fecha->format('d/m/Y H:i') }}</td>
            <td>{{ optional($mov->concepto)->nombre ?? '-' }}</td>
            <td>{{ optional($mov->entidad)->nombre ?? '-' }}</td>
            <td>{{ ucfirst($mov->tipo) }}</td>
            <td>${{ number_format($mov->monto,2) }}</td>
            <td>{{ $mov->tipo === 'Ingreso' ? ($mov->acreditado ? 'Sí' : 'No') : '-' }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<p>
    <strong>Total Ingresos Acreditados:</strong> ${{ number_format($caja->movimientos->where('tipo','Ingreso')->where('acreditado',true)->sum('monto'),2) }}<br>
    <strong>Total Egresos:</strong> ${{ number_format($caja->movimientos->where('tipo','Egreso')->sum('monto'),2) }}<br>
    <strong>Saldo Actual:</strong> ${{ number_format($caja->movimientos->where('tipo','Ingreso')->where('acreditado',true)->sum('monto') - $caja->movimientos->where('tipo','Egreso')->sum('monto'),2) }}
</p>
