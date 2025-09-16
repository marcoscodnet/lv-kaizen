@extends('layouts.app')
@section('headSection')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('bower_components/datatables.net-bs/css/dataTables.bootstrap.css') }}">
    <link rel="stylesheet" href="{{ asset('bower_components/select2/dist/css/select2.min.css') }}">
@endsection

@section('content')
    <div class="card mb-3">
        <div class="card-header">
            <div class="row flex-between-center">
                <div class="col-4 col-sm-auto d-flex align-items-center pe-0">
                    <h5 class="fs-9 mb-0 text-nowrap py-2 py-xl-0">
                        <i class="fas fa-cash-register"></i>
                        <span class="ms-2">Cajas</span>
                    </h5>
                </div>
                <div class="col-8 col-sm-auto text-end ps-2">
                    <div id="table-cajas-replace-element">
                        @can('caja-abrir')
                            <a class="btn btn-falcon-default btn-sm d-inline-flex align-items-center" href="{{ route('cajas.abrir') }}">
                                <span class="fas fa-plus"></span>
                                <span class="d-none d-sm-inline-block ms-2">Abrir Caja</span>
                            </a>
                        @endcan
                    </div>
                </div>
            </div>
            @include('includes.messages')
        </div>

        <div class="card-body pt-0">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="fechaDesde">Desde:</label>
                        <input type="date" id="fechaDesde" class="form-control">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="fechaHasta">Hasta:</label>
                        <input type="date" id="fechaHasta" class="form-control">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="filtroSucursal">Sucursales:</label>
                        <select name="filtroSucursal" id="filtroSucursal" class="form-control js-example-basic-single">
                            @foreach($sucursals as $sucursalId => $sucursal)
                                <option value="{{ $sucursalId }}">{{ $sucursal }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body pt-0">
            <div class="tab-content table-responsive">
                <table id="cajasTable" class="table table-bordered table-striped table-hover fs-10 mb-0">
                    <thead class="bg-200">
                    <tr>

                        <th>Fecha Apertura</th>
                        <th>Sucursal</th>
                        <th>Usuario</th>
                        <th>Monto Inicial</th>
                        <th>Monto Final</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>

                <div id="totales-cajas" class="mt-3 fs-10"></div>
            </div>
        </div>
    </div>
@endsection

@section('footerSection')
    <!-- jQuery 3 -->
    <script src="{{ asset('bower_components/jquery/dist/jquery.min.js') }}"></script>
    <!-- Bootstrap 3.3.7 -->
    <script src="{{ asset('bower_components/bootstrap/dist/js/bootstrap.min.js') }}"></script>
    <!-- DataTables -->
    <script src="{{ asset('bower_components/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
    <!-- SlimScroll -->
    <script src="{{ asset('bower_components/jquery-slimscroll/jquery.slimscroll.min.js') }}"></script>
    <!-- FastClick -->
    <script src="{{ asset('bower_components/fastclick/lib/fastclick.js') }}"></script>

    <!-- daterangepicker -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.11.2/moment.min.js"></script>

    <!-- Select2 -->
    <script src="{{ asset('bower_components/select2/dist/js/select2.min.js') }}"></script>
    <script src="{{ asset('bower_components/select2/dist/js/i18n/es.js') }}"></script>

    <script>
        $(document).ready(function() {
            $('.js-example-basic-single').select2({ language: 'es' });

            var table = $('#cajasTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                autoWidth: false,
                ajax: {
                    url: "{{ route('cajas.dataTable') }}",
                    type: "POST",
                    data: function(d) {
                        d._token = '{{ csrf_token() }}';
                        d.sucursal_id = $('#filtroSucursal').val();
                        d.fecha_desde = $('#fechaDesde').val();
                        d.fecha_hasta = $('#fechaHasta').val();
                    },
                    dataSrc: function(json) {
                        // Totales
                        $('#totales-cajas').html(`
                                <div>
                                    <strong>Total Inicial:</strong> $${json.totales.totalInicial} <br>
                                    <strong>Total Final:</strong> $${json.totales.totalFinal}
                                </div>
                            `);
                        return json.data;
                    }
                },
                columns: [

                    { data: 'apertura', name: 'apertura',
                        render: function(data) {
                            return data ? moment(data).format('DD/MM/YYYY HH:mm:ss') : '';
                        }
                    },
                    { data: 'sucursal_nombre', name: 'sucursal_nombre' },
                    { data: 'usuario_nombre', name: 'usuario_nombre' },
                    { data: 'inicial', name: 'inicial',
                        render: $.fn.dataTable.render.number( ',', '.', 2, '$' )
                    },
                    { data: 'final', name: 'final',
                        render: $.fn.dataTable.render.number( ',', '.', 2, '$' )
                    },
                    { data: 'estado', name: 'estado' },
                    {
                        data: 'id',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            var actionsHtml = '<div>';
                            @can('caja-ver')
                                actionsHtml += '<a href="{{ route("cajas.show", ":id") }}" class="btn btn-link p-0" title="Ver"><i class="fas fa-search text-500"></i></a>'.replace(':id', row.id);
                            @endcan
                            @can('caja-arqueo')
                                actionsHtml += '<a href="{{ route("cajas.arqueo", ":id") }}" class="btn btn-link p-0" title="Arqueo"><i class="fas fa-chart-line text-500"></i></a>'.replace(':id', row.id);
                            @endcan
                                @can('caja-cerrar')
                            if(row.estado === 'Abierta') {
                                actionsHtml += '<form id="cerrar-form-' + row.id + '" method="post" action="{{ route("cajas.cerrar", "") }}/' + row.id + '" style="display:none;">{{ csrf_field() }}</form>';
                                actionsHtml += '<a href="#" onclick="event.preventDefault(); if(confirm(\'Cerrar caja?\')) { document.getElementById(\'cerrar-form-' + row.id + '\').submit(); }" class="btn btn-link p-0" title="Cerrar"><i class="fas fa-lock text-500"></i></a>';
                            }
                            @endcan
                                actionsHtml += '</div>';
                            return actionsHtml;
                        }
                    }
                ],
                order: [[1,'desc']],
                language: { url: "{{ asset('bower_components/datatables.net/lang/es-AR.json') }}" },
                stateSave: true
            });

            $('#filtroSucursal,#fechaDesde,#fechaHasta').change(function() {
                table.ajax.reload();
            });
        });
    </script>
@endsection

