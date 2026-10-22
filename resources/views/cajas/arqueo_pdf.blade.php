<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }
    h2 { margin: 0 0 6px; }
    .cabecera p { margin: 2px 0; }
    h3 { margin: 16px 0 0; padding: 5px 6px; font-size: 12px; }
    h3.acreditados { background: #d4edda; }
    h3.pendientes  { background: #fff3cd; }
    h3.egresos     { background: #f8d7da; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #999; padding: 4px; }
    th { background: #eee; text-align: left; }
    td.monto, th.monto { text-align: right; white-space: nowrap; }
    tr.subtotal td { font-weight: bold; background: #f5f5f5; }
    td.vacio { font-style: italic; color: #777; text-align: center; }
    table.resumen { width: 55%; margin-top: 18px; margin-left: auto; }
    table.resumen td { border: none; border-bottom: 1px solid #ddd; }
    table.resumen tr.saldo td { font-weight: bold; font-size: 12px; border-top: 2px solid #333; }
    table.resumen tr.nota td { color: #8a6d00; }
</style>

<div class="cabecera">
    <h2>Arqueo Caja #{{ $caja->id }}</h2>
    <p>Sucursal: {{ $caja->sucursal->nombre }}</p>
    <p>Apertura: {{ $caja->apertura->format('d/m/Y H:i:s') }}</p>
    <p>Usuario: {{ $caja->user->name }}</p>
    <p>Estado: {{ ucfirst($caja->estado) }}</p>
    <p>Monto Inicial: ${{ number_format($caja->inicial, 2, ',', '.') }}</p>
    @if($caja->estado === 'Cerrada')
        <p>Monto Final: ${{ number_format($caja->final, 2, ',', '.') }}</p>
    @endif
</div>

@foreach($secciones['bloques'] as $bloque)
    <h3 class="{{ $bloque['corto'] }}">{{ $bloque['titulo'] }}</h3>
    <table>
        <thead>
        <tr>
            <th>Fecha</th>
            <th>Concepto</th>
            <th>Referencia</th>
            <th>Forma de pago</th>
            <th>Origen</th>
            <th class="monto">Monto</th>
        </tr>
        </thead>
        <tbody>
        @forelse($bloque['movimientos'] as $mov)
            <tr>
                <td>{{ $mov->fecha->format('d/m/Y H:i') }}</td>
                <td>{{ optional($mov->concepto)->nombre ?? '-' }}</td>
                <td>{{ $mov->referencia ?: '-' }}</td>
                <td>{{ optional($mov->entidad)->nombre ?? '-' }}</td>
                <td>{{ $mov->origen }}</td>
                <td class="monto">${{ number_format($mov->monto, 2, ',', '.') }}</td>
            </tr>
        @empty
            <tr><td colspan="6" class="vacio">Sin movimientos</td></tr>
        @endforelse
        <tr class="subtotal">
            <td colspan="5" style="text-align:right">Subtotal {{ $bloque['corto'] }}</td>
            <td class="monto">${{ number_format($bloque['total'], 2, ',', '.') }}</td>
        </tr>
        </tbody>
    </table>
@endforeach

<table class="resumen">
    <tr>
        <td>Ingresos acreditados</td>
        <td class="monto">${{ number_format($secciones['totales']['acreditados'], 2, ',', '.') }}</td>
    </tr>
    <tr>
        <td>Egresos</td>
        <td class="monto">-${{ number_format($secciones['totales']['egresos'], 2, ',', '.') }}</td>
    </tr>
    <tr class="saldo">
        <td>Saldo (acreditado − egresos)</td>
        <td class="monto">${{ number_format($secciones['totales']['saldo'], 2, ',', '.') }}</td>
    </tr>
    <tr class="nota">
        <td>Pendiente de acreditar (no suma al saldo)</td>
        <td class="monto">${{ number_format($secciones['totales']['pendientes'], 2, ',', '.') }}</td>
    </tr>
</table>
