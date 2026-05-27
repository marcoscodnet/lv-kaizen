@extends('layouts.app')
@section('headSection')
    <link rel="stylesheet" href="{{ asset('bower_components/datatables.net-bs/css/dataTables.bootstrap.css') }}">
    <link rel="stylesheet" href="{{ asset('bower_components/select2/dist/css/select2.min.css') }}">
@endsection

@section('content')
    <div class="card mb-3">
        <div class="card-header">
            <div class="row flex-between-center">
                <div class="col-auto">
                    <h5 class="mb-0"><i class="fa fa-check-double" aria-hidden="true"></i><span class="ms-2">Auditoría de pagos</span></h5>
                </div>
                <div class="col-auto">
                    @can('autorizacion-crear')
                        <button type="button" class="btn btn-success btn-sm" id="btnAutorizarLote" disabled>
                            <i class="fa fa-check"></i> Autorizar seleccionados (<span id="contadorSel">0</span>)
                        </button>
                    @endcan
                </div>
            </div>
            @include('includes.messages')
        </div>

        <div class="card-body pt-0">
            <div class="row">
                <div class="col-md-2">
                    <label for="fechaDesde">Desde:</label>
                    <input type="date" id="fechaDesde" class="form-control">
                </div>
                <div class="col-md-2">
                    <label for="fechaHasta">Hasta:</label>
                    <input type="date" id="fechaHasta" class="form-control">
                </div>
                <div class="col-md-3">
                    <label for="filtroEntidad">Entidad:</label>
                    <select id="filtroEntidad" class="form-control js-example-basic-single">
                        @foreach($entidads as $id => $nombre)
                            <option value="{{ $id }}">{{ $nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="filtroEstado">Estado:</label>
                    <select id="filtroEstado" class="form-control">
                        <option value="pendiente" selected>Pendientes</option>
                        <option value="autorizado">Autorizados</option>
                        <option value="">Todos</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="card-body pt-0">
            <table id="tablaAuditoria" class="table table-bordered table-striped table-hover fs-10 mb-0">
                <thead class="bg-200">
                <tr>
                    <th scope="col"><input type="checkbox" id="checkTodos"></th>
                    <th scope="col">Fecha</th>
                    <th scope="col">Origen</th>
                    <th scope="col">Cliente</th>
                    <th scope="col">Entidad</th>
                    <th scope="col">Monto</th>
                    <th scope="col">Acreditado</th>
                    <th scope="col">Estado</th>
                    <th scope="col">Autorizó</th>
                    <th scope="col">Comprobante</th>
                    <th scope="col">Acciones</th>
                </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    {{-- Authorize modal --}}
    <div class="modal fade" id="autorizarModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Autorizar pago</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="autPagoId">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Entidad</label>
                            <input type="text" id="autEntidad" class="form-control" readonly>
                        </div>
                        <div class="col-md-6">
                            <label>Monto declarado</label>
                            <input type="text" id="autMonto" class="form-control" readonly>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Acreditado <span class="text-danger">*</span></label>
                            <input type="text" id="autPagado" class="form-control formato-numero">
                        </div>
                        <div class="col-md-6">
                            <label>Fecha contadora</label>
                            <input type="date" id="autContadora" class="form-control">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12">
                            <label>Observaciones</label>
                            <textarea id="autObservaciones" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <label>Comprobante</label>
                            <div id="autComprobanteWrapper" class="border rounded p-2 text-center bg-light">
                                <span id="autSinComprobante" class="text-muted">Sin comprobante</span>
                                <img id="autComprobanteImg" src="" style="display:none; max-width:100%; max-height:400px;">
                                <a id="autComprobantePdf" href="#" target="_blank" style="display:none;" class="btn btn-outline-primary btn-sm">
                                    <i class="fa fa-file-pdf"></i> Ver PDF
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" id="btnConfirmarAutorizar">Autorizar</button>
                    <button type="button" class="btn btn-warning" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footerSection')
    <script src="{{ asset('bower_components/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('bower_components/bootstrap/dist/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('bower_components/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
    <script src="{{ asset('bower_components/select2/dist/js/select2.min.js') }}"></script>
    <script src="{{ asset('bower_components/select2/dist/js/i18n/es.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.11.2/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/autonumeric@4.10.5/dist/autoNumeric.min.js"></script>

    <script>
        var CSRF = '{{ csrf_token() }}';
        var puedeAutorizar = {{ auth()->user()->can('autorizacion-crear') ? 'true' : 'false' }};
        var puedeDesautorizar = {{ auth()->user()->can('autorizacion-eliminar') ? 'true' : 'false' }};
        var seleccionados = [];

        function fmt(n) {
            if (n === null || n === '') return '';
            return '$' + parseFloat(n).toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        $(document).ready(function () {
            $('.js-example-basic-single').select2({ language: 'es' });

            var autPagadoAN = new AutoNumeric('#autPagado', {
                digitGroupSeparator: '.', decimalCharacter: ',', decimalPlaces: 2
            });

            var tabla = $('#tablaAuditoria').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                scrollX: true,
                ajax: {
                    url: "{{ route('auditoria.dataTable') }}",
                    type: "POST",
                    data: function (d) {
                        d._token = CSRF;
                        d.estado = $('#filtroEstado').val();
                        d.entidad_id = $('#filtroEntidad').val();
                        d.fecha_desde = $('#fechaDesde').val();
                        d.fecha_hasta = $('#fechaHasta').val();
                    }
                },
                columns: [
                    {
                        data: 'id', orderable: false, searchable: false,
                        render: function (data, type, row) {
                            if (row.estado === 'Pendiente' && puedeAutorizar) {
                                return '<input type="checkbox" class="check-pago" value="' + data + '">';
                            }
                            return '';
                        }
                    },
                    { data: 'fecha', render: function (d) { return d ? moment(d).format('DD/MM/YYYY') : ''; } },
                    { data: 'origen' },
                    { data: 'cliente' },
                    { data: 'entidad_nombre' },
                    { data: 'monto', render: fmt },
                    { data: 'pagado', render: fmt },
                    {
                        data: 'estado',
                        render: function (d) {
                            var cls = d === 'Autorizado' ? 'success' : 'warning';
                            return '<span class="badge bg-' + cls + '">' + d + '</span>';
                        }
                    },
                    { data: 'autorizado_por' },
                    {
                        data: 'comprobante_path', orderable: false, searchable: false,
                        render: function (data) {
                            if (!data) return '<span class="text-muted">—</span>';
                            var url = "{{ asset('') }}" + data;
                            return '<a href="' + url + '" target="_blank" class="btn btn-link p-0"><i class="fas fa-file-image text-500"></i></a>';
                        }
                    },
                    {
                        data: 'id', orderable: false, searchable: false,
                        render: function (data, type, row) {
                            var html = '<div>';
                            if (row.estado === 'Pendiente' && puedeAutorizar) {
                                html += '<a href="#" class="btn btn-link p-0 btn-autorizar" data-id="' + data + '" title="Autorizar"><span class="text-success fas fa-check"></span></a>';
                            }
                            if (row.estado === 'Autorizado' && puedeDesautorizar) {
                                html += '<a href="#" class="btn btn-link p-0 btn-desautorizar" data-id="' + data + '" title="Desautorizar"><span class="text-danger fas fa-undo"></span></a>';
                            }
                            html += '</div>';
                            return html;
                        }
                    }
                ],
                order: [[1, 'desc']],
                language: { url: "{{ asset('bower_components/datatables.net/lang/es-AR.json') }}" }
            });

            $('#filtroEstado, #filtroEntidad, #fechaDesde, #fechaHasta').on('change', function () {
                seleccionados = [];
                actualizarContador();
                tabla.ajax.reload();
            });

            // Selection (batch)
            $('#tablaAuditoria tbody').on('change', '.check-pago', function () {
                var id = $(this).val();
                if (this.checked) {
                    if (!seleccionados.includes(id)) seleccionados.push(id);
                } else {
                    seleccionados = seleccionados.filter(function (x) { return x != id; });
                }
                actualizarContador();
            });

            $('#checkTodos').on('change', function () {
                var checked = this.checked;
                $('.check-pago').each(function () {
                    this.checked = checked;
                    $(this).trigger('change');
                });
            });

            function actualizarContador() {
                $('#contadorSel').text(seleccionados.length);
                $('#btnAutorizarLote').prop('disabled', seleccionados.length === 0);
            }

            // Open authorize modal (single)
            $('#tablaAuditoria tbody').on('click', '.btn-autorizar', function (e) {
                e.preventDefault();
                var id = $(this).data('id');
                $.get("{{ url('auditoria') }}/" + id + "/datos", function (p) {
                    $('#autPagoId').val(p.id);
                    $('#autEntidad').val(p.entidad);
                    $('#autMonto').val(fmt(p.monto));
                    autPagadoAN.set(p.pagado || p.monto);
                    $('#autContadora').val(p.contadora || '');
                    $('#autObservaciones').val('');

                    // Show proof
                    $('#autComprobanteImg, #autComprobantePdf, #autSinComprobante').hide();
                    if (p.comprobante) {
                        if (p.comprobante.toLowerCase().endsWith('.pdf')) {
                            $('#autComprobantePdf').attr('href', p.comprobante).show();
                        } else {
                            $('#autComprobanteImg').attr('src', p.comprobante).show();
                        }
                    } else {
                        $('#autSinComprobante').show();
                    }

                    $('#autorizarModal').modal('show');
                });
            });

            // Confirm single authorization
            $('#btnConfirmarAutorizar').on('click', function () {
                var id = $('#autPagoId').val();
                $.ajax({
                    url: "{{ url('auditoria') }}/" + id + "/autorizar",
                    type: "POST",
                    data: {
                        _token: CSRF,
                        pagado: autPagadoAN.getNumericString(),
                        contadora: $('#autContadora').val(),
                        observaciones: $('#autObservaciones').val()
                    },
                    success: function (r) {
                        $('#autorizarModal').modal('hide');
                        tabla.ajax.reload();
                    },
                    error: function (xhr) {
                        alert(xhr.responseJSON ? xhr.responseJSON.error : 'Error al autorizar');
                    }
                });
            });

            // Batch authorize
            $('#btnAutorizarLote').on('click', function () {
                if (seleccionados.length === 0) return;
                if (!confirm('¿Autorizar ' + seleccionados.length + ' pago(s)? Se tomará el monto declarado como acreditado.')) return;

                $.ajax({
                    url: "{{ route('auditoria.autorizarLote') }}",
                    type: "POST",
                    data: { _token: CSRF, ids: seleccionados },
                    success: function (r) {
                        seleccionados = [];
                        actualizarContador();
                        tabla.ajax.reload();
                    },
                    error: function (xhr) {
                        alert(xhr.responseJSON ? xhr.responseJSON.error : 'Error al autorizar en lote');
                    }
                });
            });

            // Deauthorize
            $('#tablaAuditoria tbody').on('click', '.btn-desautorizar', function (e) {
                e.preventDefault();
                if (!confirm('¿Desautorizar este pago? Se limpiará el acreditado y se revertirá el movimiento.')) return;
                var id = $(this).data('id');
                $.ajax({
                    url: "{{ url('auditoria') }}/" + id + "/desautorizar",
                    type: "POST",
                    data: { _token: CSRF, _method: 'DELETE' },
                    success: function (r) {
                        tabla.ajax.reload();
                    },
                    error: function (xhr) {
                        alert(xhr.responseJSON ? xhr.responseJSON.error : 'Error al desautorizar');
                    }
                });
            });
        });
    </script>
@endsection
