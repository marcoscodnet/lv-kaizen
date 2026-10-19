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
                <div class="col-auto">
                    @can('senia-crear')
                        <a href="{{ route('senias.create') }}" class="btn btn-falcon-default btn-sm">
                            <span class="fas fa-plus"></span><span class="ms-1">Registrar seña</span>
                        </a>
                    @endcan
                </div>
            </div>
            <p class="text-muted small mb-0 mt-1">
                Una seña en efectivo queda disponible en el momento. Si se pagó con transferencia, tarjeta u otra forma que
                requiere autorización, queda <strong>pendiente de autorización</strong> hasta que Auditoría la autorice.
                Las disponibles se aplican como pago al cobrar una moto, artículos o un servicio del mismo cliente, por lo acreditado.
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
                    <label for="buscar" class="form-label">Cliente o detalle</label>
                    <input type="text" name="buscar" id="buscar" class="form-control" placeholder="Nombre, documento o qué se señó" value="{{ request('buscar') }}">
                </div>
                <div class="col-12 col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover fs-10">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Seña de</th>
                        <th>Sucursal</th>
                        <th>Pagos</th>
                        <th class="text-end">Cobrado</th>
                        <th class="text-end">Acreditado</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($senias as $s)
                        @php
                            $acreditado = $s->acreditado();
                            $destino = $s->aplicadaA();
                        @endphp
                        <tr>
                            <td>{{ $s->id }}</td>
                            <td class="text-nowrap">{{ $s->fecha->format('d/m/Y H:i') }}</td>
                            <td>{{ optional($s->cliente)->nombre }} <span class="text-muted">({{ optional($s->cliente)->documento }})</span></td>
                            <td>{{ $s->detalle ?: '-' }}</td>
                            <td>{{ optional($s->sucursal)->nombre }}</td>
                            <td class="small">
                                @foreach($s->pagos as $p)
                                    <div class="text-nowrap">
                                        {{ optional($p->entidad)->nombre }}: ${{ number_format($p->monto, 2, ',', '.') }}
                                        @if(optional($p->entidad)->autorizacion)
                                            @if($p->autorizacion)
                                                <span class="badge bg-success">autorizado</span>
                                            @else
                                                <span class="badge bg-warning text-dark">a autorizar</span>
                                            @endif
                                        @endif
                                    </div>
                                @endforeach
                                @foreach($s->movimientosCaja->unique('caja_id') as $m)
                                    <a href="{{ route('cajas.show', $m->caja_id) }}">caja #{{ $m->caja_id }}</a>
                                @endforeach
                            </td>
                            <td class="text-end text-nowrap">${{ number_format($s->monto, 2, ',', '.') }}</td>
                            <td class="text-end text-nowrap">${{ number_format($acreditado, 2, ',', '.') }}</td>
                            <td>
                                <span class="badge bg-{{ $s->colorEstado() }}">{{ $s->estadoVisible() }}</span>
                                @if($destino)
                                    <div class="small"><a href="{{ $destino['url'] }}">{{ $destino['texto'] }}</a></div>
                                @endif
                                @if($s->estado === \App\Models\Senia::DEVUELTA)
                                    <div class="small text-muted">
                                        ${{ number_format($s->devolucion_monto, 2, ',', '.') }} con {{ optional($s->devolucionEntidad)->nombre }}
                                    </div>
                                @endif
                                @if($s->fecha_baja)
                                    <div class="text-muted small">{{ $s->fecha_baja->format('d/m/Y') }}</div>
                                @endif
                                @if($s->observacion)
                                    <div class="text-muted small">{{ $s->observacion }}</div>
                                @endif
                            </td>
                            <td class="text-nowrap">
                                @if($s->esVigente())
                                    @can('senia-baja')
                                        @if($s->estaAutorizada())
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-devolver"
                                                    data-bs-toggle="modal" data-bs-target="#modalDevolver"
                                                    data-url="{{ route('senias.devolver', $s->id) }}"
                                                    data-senia="{{ $s->id }}"
                                                    data-cliente="{{ optional($s->cliente)->nombre }}"
                                                    data-monto="{{ number_format($acreditado, 2, '.', '') }}">
                                                Devolver
                                            </button>
                                        @endif
                                        <form action="{{ route('senias.retener', $s->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="observacion" class="obs-baja">
                                            <button type="submit" class="btn btn-sm btn-outline-secondary btn-baja"
                                                    data-mensaje="¿Retener la seña #{{ $s->id }} de {{ optional($s->cliente)->nombre }}? No se devuelve la plata y no se mueve caja ni cuenta.">
                                                Retener
                                            </button>
                                        </form>
                                        <form action="{{ route('senias.destroy', $s->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-dark btn-anular"
                                                    data-mensaje="¿Anular la seña #{{ $s->id }}? Usalo solo si se cargó por error: se borran sus pagos y los movimientos de caja y cuenta.">
                                                Anular
                                            </button>
                                        </form>
                                    @endcan
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="text-center text-muted">No hay señas para mostrar.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            {{ $senias->links('pagination::bootstrap-4') }}
        </div>
    </div>

    {{-- Devolución: se elige con qué forma de pago --}}
    <div class="modal fade" id="modalDevolver" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" id="formDevolver" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Devolver seña #<span id="devSenia"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">Cliente: <strong id="devCliente"></strong></p>
                    <div class="mb-2">
                        <label class="form-label" for="devEntidad">Se devuelve con</label>
                        <select name="entidad_id" id="devEntidad" class="form-control" required>
                            <option value="">Seleccionar...</option>
                            @foreach($entidadsDevolucion as $e)
                                <option value="{{ $e->id }}" data-tangible="{{ $e->tangible }}" data-cuenta="{{ $e->cuenta }}">{{ $e->nombre }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted" id="devAyuda"></small>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="devMonto">Importe</label>
                        <input type="number" step="0.01" min="0.01" name="monto" id="devMonto" class="form-control" required>
                        <small class="text-muted">Como máximo, lo acreditado.</small>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="devObs">Motivo (opcional)</label>
                        <input type="text" name="observacion" id="devObs" class="form-control" maxlength="255">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Devolver</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('footerSection')
    <script>
        $(function () {
            // Retener: confirmación + motivo opcional
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

            $('.btn-anular').on('click', function (e) {
                if (!confirm($(this).data('mensaje'))) {
                    e.preventDefault();
                }
            });

            $('.btn-devolver').on('click', function () {
                $('#formDevolver').attr('action', $(this).data('url'));
                $('#devSenia').text($(this).data('senia'));
                $('#devCliente').text($(this).data('cliente'));
                $('#devMonto').val($(this).data('monto')).attr('max', $(this).data('monto'));
                $('#devEntidad').val('').trigger('change');
                $('#devObs').val('');
            });

            $('#devEntidad').on('change', function () {
                var $op = $(this).find(':selected');
                var txt = '';
                if ($op.val()) {
                    if ($op.data('tangible') == 1) {
                        txt = 'Sale como egreso de la caja abierta de hoy.';
                    } else if ($op.data('cuenta') == 1) {
                        txt = 'Sale como egreso de la cuenta de ' + $op.text().trim() + '.';
                    } else {
                        txt = 'Queda registrada; no mueve caja ni cuenta.';
                    }
                }
                $('#devAyuda').text(txt);
            });
        });
    </script>
@endsection
