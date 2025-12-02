@extends('layouts.app')
@section('headSection')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('bower_components/datatables.net-bs/css/dataTables.bootstrap.css') }}">


@endsection

@section('content')
    <div class="card mb-3">
        <div class="card-header">

            <div class="row flex-between-center">
                <div class="col-4 col-sm-auto d-flex align-items-center pe-0">
                    <h5 class="fs-9 mb-0 text-nowrap py-2 py-xl-0"><i class="fa fa-cogs" aria-hidden="true"></i><span class="ms-2">Piezas</span></h5>
                </div>
                <div class="col-8 col-sm-auto text-end ps-2">

                    <div id="table-customers-replace-element">
                        <a class="btn btn-falcon-default btn-sm d-inline-flex align-items-center" href="{{ route('piezas.create') }}">
                            <span class="fas fa-plus"></span>
                            <span class="d-none d-sm-inline-block ms-2">Nueva</span>
                        </a>
                        <a class="btn btn-falcon-default btn-sm d-inline-flex align-items-center ms-2" href="{{ route('piezas.masivo') }}">
                            <span class="fas fa-layer-group"></span>
                            <span class="d-none d-sm-inline-block ms-2">Carga masiva</span>
                        </a>

                    </div>
                </div>
            </div>
            @include('includes.messages')
        </div>
        <div class="card-body pt-0">
            <div class="row">


                <div class="col-md-2">
                    <div class="form-group">
                        <label for="filtroSucursal">Sucursal:</label>
                        <select name="filtroSucursal" id="filtroSucursal" class="form-control js-example-basic-single">
                            @foreach($sucursals as $sucursalId => $sucursal)
                                <option value="{{ $sucursalId }}">{{ $sucursal }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group">
                        <label for="filtroUbicacion">Ubicación:</label>
                        <select name="filtroUbicacion" id="filtroUbicacion" class="form-control js-example-basic-single">
                            <option value="">Todas</option>
                        </select>
                    </div>
                </div>





            </div>
            <!-- /.form-group -->
        </div>
        <div class="card-body pt-0">
            <div class="tab-content table-responsive">
                <table id="example1" class="table table-bordered table-striped table-hover fs-10 mb-0">
                    <thead class="bg-200">
                    <tr>


                        <th scope="col">Código</th>
                        <th scope="col">Descripción</th>
                        <th scope="col">Tipo</th>
                        <th scope="col">Stock mín.</th>
                        <th scope="col">Stock actual</th>
                        <th scope="col">Sucursal</th>
                        <th scope="col">Ubicación</th>
                        <th scope="col">Observaciones</th>

                        <th scope="col">Acciones</th>

                    </tr>
                    </thead>
                    <tbody>

                    </tbody>

                </table>
            </div>
        </div>


    </div>
    <!-- /.content-wrapper -->
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

    <script>
        var ubicacionesUrl = "{{ url('/ubicaciones') }}";
    </script>

    <!-- page script -->
    <!-- page script -->
    <script>
        $(document).ready(function() {


            var table =  $('#example1').DataTable({
                "processing": true, // Activar la indicación de procesamiento
                "serverSide": true, // Habilitar el procesamiento del lado del servidor
                "autoWidth": false, // Desactiva el ajuste automático del anchos
                responsive: true,
                scrollX: true,
                paging : true,
                "ajax": {
                    "url": "{{ route('piezas.dataTable') }}",
                    "type": "POST",
                    "data": function (d) {
                        d._token = '{{ csrf_token() }}'; // Agrega el token CSRF si estás usando Laravel
                        d.sucursal_id = $('#filtroSucursal').val();
                        d.ubicacion_id = $('#filtroUbicacion').val();
                        // Agrega otros parámetros si es necesario
                        // d.otroParametro = valor;
                    },
                    "error": function(xhr, error, thrown) {
                        if (xhr.status === 401) {
                            // Usuario no autenticado, redirigir al login
                            window.location.href = "{{ route('login') }}";
                        } else {
                            console.error("Error al cargar los datos:", error);
                        }
                    }
                },
                columns: [

                    { data: 'codigo', name: 'codigo' },
                    { data: 'descripcion', name: 'descripcion' },
                    { data: 'tipo_pieza', name: 'tipo_pieza' },
                    { data: 'stock_minimo', name: 'stock_minimo' },
                    { data: 'stock_actual', name: 'stock_actual' },
                    { data: 'sucursal_nombre', name: 'sucursal_nombre' },
                    { data: 'ubicacion_nombre', name: 'ubicacion_nombre' },
                    { data: 'observaciones', name: 'observaciones' },
                    // Actions column
                    {
                        "data": "id",
                        "orderable": false,
                        "searchable": false,
                        "render": function(data, type, row) {
                            // Construir HTML para las acciones
                            var actionsHtml = '<div>';

                            @can('pieza-ver')

                                actionsHtml += '<a href="{{ route("piezas.show", ":id") }}" class="btn btn-link p-0" alt="Ver" title="Ver" data-bs-toggle="tooltip" data-bs-placement="top"><span class="text-500 fas fa-search"></span></a>'.replace(':id', row.id);
                            @endcan
                            // Agregar enlace de edición si el usuario tiene permiso
                            @can('pieza-editar')
                                actionsHtml += '<a href="{{ route("piezas.edit", ":id") }}" class="btn btn-link p-0" alt="Editar" title="Editar" data-bs-toggle="tooltip" data-bs-placement="top"><span class="text-500 fas fa-edit"></span></a>'.replace(':id', row.id);
                            @endcan
                                @can('pieza-modificar-descripcion')
                                actionsHtml += '<a href="{{ route("piezas.edit", ":id") }}" class="btn btn-link p-0" alt="Editar" title="Editar" data-bs-toggle="tooltip" data-bs-placement="top"><span class="text-500 fas fa-edit"></span></a>'.replace(':id', row.id);
                            @endcan

                            // Agregar formulario de eliminación si el pieza tiene permiso
                            @can('pieza-eliminar')
                                actionsHtml += '<form id="delete-form-' + row.id + '" method="post" action="{{ route('piezas.destroy', '') }}/' + row.id + '" style="display: none">';
                            actionsHtml += '{{ csrf_field() }}';
                            actionsHtml += '{{ method_field('DELETE') }}';
                            actionsHtml += '</form>';
                            actionsHtml += '<a href="" onclick="if(confirm(\'Está seguro?\')) {event.preventDefault(); document.getElementById(\'delete-form-' + row.id + '\').submit();} else {event.preventDefault();}" class="btn btn-link p-0" data-bs-toggle="tooltip" data-bs-placement="top" title="Eliminar"><span class="text-500 fas fa-trash-alt"></span></a>';
                            @endcan
                                actionsHtml += '</div>';
                                return actionsHtml;

                        },

                    }
                ],
                "language": {
                    "url": "{{ asset('bower_components/datatables.net/lang/es-AR.json') }}"
                },
                stateSave: true,
                stateSaveParams: function (settings, data) {

                    data.filtroSucursal = $('#filtroSucursal').val();
                    data.filtroUbicacion = $('#filtroUbicacion').val();

                },
                stateLoadParams: function (settings, data) {

                    if (data.filtroSucursal) {
                        $('#filtroSucursal').val(data.filtroSucursal).trigger('change');
                    }

                    if (data.filtroUbicacion) {
                        $('#filtroUbicacion').val(data.filtroUbicacion).trigger('change');
                    }

                },
                initComplete: function () {
                    // Eliminar las clases 'form-control' y 'input-sm', y agregar 'form-select' (para Bootstrap 5)
                    $('select[name="example1_length"]').removeClass('form-control');
                    $('input[type="search"]').removeClass('form-control');
                    $('input[type="search"]').css('width', '70%');
                }
            });

            $('#filtroSucursal').change(function() {
                cargarUbicaciones($(this).val());
                table.ajax.reload();
            });

            $('#filtroUbicacion').change(function() {
                table.ajax.reload();
            });

        });
        function cargarUbicaciones(sucursalId) {
            $('#filtroUbicacion').html('<option value="">Todas</option>');

            if (!sucursalId) {
                return;
            }

            $.get(ubicacionesUrl + '/' + sucursalId, function(data) {
                data.forEach(function(ubicacion) {
                    $('#filtroUbicacion').append(
                        '<option value="'+ ubicacion.id +'">'+ ubicacion.nombre +'</option>'
                    );
                });
            });
        }


    </script>
@endsection
