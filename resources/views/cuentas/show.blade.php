@extends('layouts.app')

@section('headSection')
    <link rel="stylesheet" href="{{ asset('bower_components/datatables.net-bs/css/dataTables.bootstrap.css') }}">
@endsection

@section('content')
    <div class="card mb-3">
        <div class="card-header">
            <div class="row flex-between-center">
                <div class="col-4 col-sm-auto d-flex align-items-center pe-0">
                    <h5 class="fs-9 mb-0 text-nowrap py-2 py-xl-0">
                        <i class="fas fa-university"></i>
                        <span class="ms-2">{{ $entidad->nombre }}</span>
                    </h5>
                </div>
                <div class="col-8 col-sm-auto text-end ps-2">
                    <a href="{{ route('cuentas.index') }}" class="btn btn-falcon-default btn-sm">
                        <span class="fas fa-arrow-left"></span>
                        <span class="d-none d-sm-inline-block ms-2">Volver</span>
                    </a>
                    @can('cuenta-crear')
                        @php
                            $tieneSaldoInicial = \App\Models\MovimientoCuenta::where('entidad_id', $entidad->id)
                                ->where('concepto', 'Saldo inicial')->exists();
                        @endphp
                        @if(!$tieneSaldoInicial)
                            <button class="btn btn-falcon-warning btn-sm ms-2"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalSaldoInicial">
                                <span class="fas fa-coins"></span>
                                <span class="d-none d-sm-inline-block ms-2">Saldo inicial</span>
                            </button>
                        @endif
                        @can('cuenta-crear')
                            <button class="btn btn-falcon-success btn-sm ms-2"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalMovimiento">
                                <span class="fas fa-plus"></span>
                                <span class="d-none d-sm-inline-block ms-2">Nuevo movimiento</span>
                            </button>

                            <button class="btn btn-falcon-primary btn-sm ms-2"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalTransferencia">
                                <span class="fas fa-exchange-alt"></span>
                                <span class="d-none d-sm-inline-block ms-2">Transferencia</span>
                            </button>
                        @endcan
                    @endcan
                </div>
            </div>
            @include('includes.messages')
        </div>

        <div class="card-body pt-0">

            {{-- Summary cards --}}
            <div class="row text-center mb-4 mt-3">
                <div class="col-md-4">
                    <div class="card bg-success text-white p-2">
                        <strong>Ingresos</strong>
                        <h4 id="cardIngresos">$0,00</h4>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-danger text-white p-2">
                        <strong>Egresos</strong>
                        <h4 id="cardEgresos">$0,00</h4>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-info text-white p-2">
                        <strong>Saldo</strong>
                        <h4 id="cardSaldo">$0,00</h4>
                    </div>
                </div>
            </div>

            {{-- Filters --}}
            <div class="row mb-3">
                <div class="col-md-3">
                    <label>Desde:</label>
                    <input type="date" id="fechaDesde" class="form-control">
                </div>
                <div class="col-md-3">
                    <label>Hasta:</label>
                    <input type="date" id="fechaHasta" class="form-control">
                </div>
            </div>

            {{-- DataTable --}}
            <div class="tab-content">
                <table id="tablaCuenta" class="table table-bordered table-striped table-hover fs-10 mb-0">
                    <thead class="bg-200">
                    <tr>
                        <th>Fecha</th>
                        <th>Concepto</th>
                        <th>Tipo</th>
                        <th>Monto</th>
                        <th>Origen</th>
                        <th>Usuario</th>
                        <th>Observación</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal: saldo inicial --}}
    @can('cuenta-crear')
        @if(!$tieneSaldoInicial)
            <div class="modal fade" id="modalSaldoInicial" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="POST" action="{{ route('cuentas.saldoInicial', $entidad->id) }}">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title">Saldo inicial — {{ $entidad->nombre }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Fecha <span class="text-danger">*</span></label>
                                    <input type="date" name="fecha" class="form-control form-control-sm"
                                           value="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Monto <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" name="monto"
                                           class="form-control form-control-sm" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Observación</label>
                                    <textarea name="observacion" class="form-control form-control-sm" rows="2"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-warning btn-sm">
                                    <i class="fas fa-save"></i> Guardar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        {{-- Modal: nuevo movimiento manual --}}
        <div class="modal fade" id="modalMovimiento" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="{{ route('cuentas.store', $entidad->id) }}">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Nuevo movimiento manual</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Tipo <span class="text-danger">*</span></label>
                                <select name="tipo" class="form-select form-select-sm" required>
                                    <option value="Ingreso">Ingreso</option>
                                    <option value="Egreso">Egreso</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Fecha <span class="text-danger">*</span></label>
                                <input type="date" name="fecha" class="form-control form-control-sm"
                                       value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Monto <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="monto"
                                       class="form-control form-control-sm" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Concepto <span class="text-danger">*</span></label>
                                <input type="text" name="concepto" class="form-control form-control-sm" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Observación</label>
                                <textarea name="observacion" class="form-control form-control-sm" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-success btn-sm">
                                <i class="fas fa-save"></i> Guardar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @can('cuenta-crear')
            <div class="modal fade" id="modalTransferencia" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="POST" action="{{ route('cuentas.transferencia', $entidad->id) }}">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title">
                                    <i class="fas fa-exchange-alt me-2"></i>
                                    Transferencia desde {{ $entidad->nombre }}
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Cuenta destino <span class="text-danger">*</span></label>
                                    <select name="entidad_destino_id" class="form-select form-select-sm" required>
                                        <option value="">Seleccionar...</option>
                                        @foreach(\App\Models\Entidad::where('tangible', 0)
                                            ->where('activa', 1)
                                            ->where('id', '!=', $entidad->id)
                                            ->orderBy('nombre')
                                            ->get() as $e)
                                            <option value="{{ $e->id }}">{{ $e->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Fecha <span class="text-danger">*</span></label>
                                    <input type="date" name="fecha"
                                           class="form-control form-control-sm"
                                           value="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Monto <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" name="monto"
                                           class="form-control form-control-sm" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Observación</label>
                                    <textarea name="observacion"
                                              class="form-control form-control-sm"
                                              rows="2"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary btn-sm"
                                        data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fas fa-exchange-alt"></i> Transferir
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endcan
    @endcan

@endsection

@section('footerSection')
    <script src="{{ asset('bower_components/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>

    <script>
        var entidadId = {{ $entidad->id }};

        var formatter = new Intl.NumberFormat('es-AR', { minimumFractionDigits: 2 });

        function formatMoney(v) {
            return '$' + formatter.format(parseFloat(v) || 0);
        }

        $(document).ready(function() {

            var table = $('#tablaCuenta').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                responsive: true,
                ajax: {
                    url: '{{ route("cuentas.data", $entidad->id) }}',
                    type: 'POST',
                    data: function(d) {
                        d._token     = '{{ csrf_token() }}';
                        d.fecha_desde = $('#fechaDesde').val();
                        d.fecha_hasta = $('#fechaHasta').val();
                    },
                    error: function(xhr) {
                        if (xhr.status === 401) {
                            window.location.href = '{{ route("login") }}';
                        }
                    },
                    dataSrc: function(json) {
                        var t = json.totales;
                        $('#cardIngresos').text(formatMoney(t.ingresos));
                        $('#cardEgresos').text(formatMoney(t.egresos));
                        $('#cardSaldo').text(formatMoney(t.saldo));
                        return json.data;
                    }
                },
                columns: [
                    {
                        data: 'fecha',
                        render: function(data) {
                            return data ? data.substring(0, 10).split('-').reverse().join('/') : '-';
                        }
                    },
                    { data: 'concepto', defaultContent: '-' },
                    {
                        data: 'tipo',
                        render: function(data) {
                            var cls = data === 'Ingreso' ? 'success' : 'danger';
                            return '<span class="badge bg-' + cls + '">' + data + '</span>';
                        }
                    },
                    {
                        data: 'monto',
                        render: function(data) { return formatMoney(data); }
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            if (row.venta_id)
                                return '<span class="badge bg-primary">Venta #' + row.venta_id + '</span>';
                            if (row.venta_pieza_id)
                                return '<span class="badge bg-secondary">Pieza #' + row.venta_pieza_id + '</span>';
                            if (row.servicio_id)
                                return '<span class="badge bg-info text-dark">Servicio #' + row.servicio_id + '</span>';
                            if (row.transferencia_id)
                                return '<span class="badge bg-warning text-dark"><i class="fas fa-exchange-alt"></i> Transferencia</span>';
                            return '<span class="badge bg-dark">Manual</span>';
                        }
                    },
                    { data: 'usuario_nombre', defaultContent: '-' },
                    { data: 'observacion',    defaultContent: '-' },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            // Only allow deletion of manual movements
                            if (row.venta_id || row.venta_pieza_id || row.servicio_id) {
                                return '-';
                            }
                            @can('cuenta-eliminar')
                                return '<form id="delete-cuenta-' + row.id + '" method="POST" '
                                + 'action="{{ url("cuentas/movimiento") }}/' + row.id + '" '
                                + 'style="display:inline">'
                                + '<input type="hidden" name="_token" value="{{ csrf_token() }}">'
                                + '<input type="hidden" name="_method" value="DELETE">'
                                + '<a href="" onclick="if(confirm(\'¿Eliminar este movimiento?\')) '
                                + '{ event.preventDefault(); document.getElementById(\'delete-cuenta-' + row.id + '\').submit(); } '
                                + 'else { event.preventDefault(); }" '
                                + 'class="btn btn-link p-0" title="Eliminar">'
                                + '<span class="text-500 fas fa-trash-alt"></span></a>'
                                + '</form>';
                            @endcan
                                return '-';
                        }
                    },
                ],
                order: [[0, 'desc']],
                language: {
                    url: "{{ asset('bower_components/datatables.net/lang/es-AR.json') }}"
                }
            });

            $('#fechaDesde, #fechaHasta').change(function() {
                table.ajax.reload();
            });
        });
    </script>
@endsection
