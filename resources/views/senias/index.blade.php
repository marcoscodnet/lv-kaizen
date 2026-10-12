@extends('layouts.app')

@section('content')
    <div class="card mb-3">
        <div class="card-header">
            <div class="row flex-between-center">
                <div class="col-auto d-flex align-items-center pe-0">
                    <h5 class="fs-9 mb-0 text-nowrap py-2 py-xl-0">
                        <i class="fas fa-hand-holding-usd"></i>
                        <span class="ms-2">Señas</span>
                    </h5>
                </div>
            </div>
            <p class="text-muted small mb-0 mt-1">
                Las señas se registran desde la caja (Registrar Movimiento, concepto "Seña") y se aplican como pago al hacer la venta al mismo cliente.
            </p>
            @include('includes.messages')
        </div>

        <div class="card-body pt-0">
            <form method="GET" action="{{ route('senias.index') }}" class="row g-2 align-items-end mb-3">
                <div class="col-12 col-md-3">
                    <label for="estado" class="form-label">Estado</label>
                    <select name="estado" id="estado" class="form-control">
                        @foreach($estados as $e)
                            <option value="{{ $e }}" {{ $estado === $e ? 'selected' : '' }}>{{ $e }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <label for="sucursal_id" class="form-label">Sucursal</label>
                    <select name="sucursal_id" id="sucursal_id" class="form-control">
                        @foreach($sucursals as $id => $nombre)
                            <option value="{{ $id }}" {{ (string) request('sucursal_id') === (string) $id ? 'selected' : '' }}>{{ $nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label for="buscar" class="form-label">Cliente</label>
                    <input type="text" name="buscar" id="buscar" class="form-control" placeholder="Nombre o documento" value="{{ request('buscar') }}">
                </div>
                <div class="col-12 col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover fs-10">
                    <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Sucursal</th>
                        <th>Forma de pago</th>
                        <th class="text-end">Monto</th>
                        <th>Estado</th>
                        <th>Detalle</th>
                        <th>Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($senias as $s)
                        <tr>
                            <td class="text-nowrap">{{ $s->fecha->format('d/m/Y H:i') }}</td>
                            <td>{{ optional($s->cliente)->nombre }} <span class="text-muted">({{ optional($s->cliente)->documento }})</span></td>
                            <td>{{ optional($s->sucursal)->nombre }}</td>
                            <td>{{ optional(optional($s->movimientoCaja)->entidad)->nombre ?? '-' }}</td>
                            <td class="text-end text-nowrap">${{ number_format($s->monto, 2, ',', '.') }}</td>
                            <td>
                                @php
                                    $color = [
                                        'Pendiente' => 'warning', 'Aplicada' => 'success',
                                        'Devuelta' => 'secondary', 'Retenida' => 'info',
                                    ][$s->estado] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $color }}">{{ $s->estado }}</span>
                                @if($s->estado === 'Aplicada' && $s->venta_id)
                                    <a href="{{ route('ventas.show', $s->venta_id) }}" class="ms-1">venta #{{ $s->venta_id }}</a>
                                @endif
                                @if($s->fecha_baja)
                                    <div class="text-muted small">{{ $s->fecha_baja->format('d/m/Y') }}</div>
                                @endif
                            </td>
                            <td>
                                {{ $s->observacion ?: '-' }}
                                @if($s->movimientoCaja)
                                    <div class="small">
                                        <a href="{{ route('cajas.show', $s->movimientoCaja->caja_id) }}">caja #{{ $s->movimientoCaja->caja_id }}</a>
                                    </div>
                                @endif
                            </td>
                            <td class="text-nowrap">
                                @if($s->esPendiente())
                                    @can('caja-movimiento-registrar')
                                        <form action="{{ route('senias.devolver', $s->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="observacion" class="obs-baja">
                                            <button type="submit" class="btn btn-sm btn-outline-danger btn-baja"
                                                    data-mensaje="¿Devolver ${{ number_format($s->monto, 2, ',', '.') }} a {{ optional($s->cliente)->nombre }}? Se registra un egreso en la caja abierta de hoy.">
                                                Devolver
                                            </button>
                                        </form>
                                        <form action="{{ route('senias.retener', $s->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="observacion" class="obs-baja">
                                            <button type="submit" class="btn btn-sm btn-outline-secondary btn-baja"
                                                    data-mensaje="¿Retener la seña de {{ optional($s->cliente)->nombre }}? No se devuelve la plata y no se mueve la caja.">
                                                Retener
                                            </button>
                                        </form>
                                    @endcan
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted">No hay señas para mostrar.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            {{ $senias->links('pagination::bootstrap-4') }}
        </div>
    </div>
@endsection

@section('footerSection')
    <script>
        $(function () {
            // Confirmación + motivo opcional antes de dar de baja una seña
            $('.btn-baja').on('click', function (e) {
                if (!confirm($(this).data('mensaje'))) {
                    e.preventDefault();
                    return;
                }
                var motivo = window.prompt('Motivo (opcional):', '');
                if (motivo === null) {
                    e.preventDefault();
                    return;
                }
                $(this).closest('form').find('.obs-baja').val(motivo);
            });
        });
    </script>
@endsection
