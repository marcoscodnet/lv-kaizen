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
                    <h5 class="fs-9 mb-0 text-nowrap py-2 py-xl-0"><i class="fa fa-wrench" aria-hidden="true"></i><span class="ms-2">Servicios</span></h5>
                </div>
                <div class="col-8 col-sm-auto text-end ps-2">

                    <div id="table-customers-replace-element">
                        <a class="btn btn-falcon-default btn-sm d-inline-flex align-items-center" href="{{ route('servicios.unidads') }}">
                            <span class="fas fa-plus"></span>
                            <span class="d-none d-sm-inline-block ms-2">Registrar</span>
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
                        <label for="fechaDesde">Desde:</label>
                        <input type="date" id="fechaDesde" class="form-control">
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group">
                        <label for="fechaHasta">Hasta:</label>
                        <input type="date" id="fechaHasta" class="form-control">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="filtroSucursal">Sucursales:</label>
                        <select name="filtroSucursal" id="filtroSucursal" class="form-control js-example-basic-single" required>

                            @foreach($sucursals as $sucursalId => $sucursal)
                                <option value="{{ $sucursalId }}">
                                    {{ $sucursal }}
                                </option>
                            @endforeach
                        </select>

                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group">
                        <label for="filtroUsuario">Vendedores:</label>
                        <select name="filtroUsuario" id="filtroUsuario" class="form-control js-example-basic-single" required>

                            @foreach($users as $userId => $user)
                                <option value="{{ $userId }}">
                                    {{ $user }}
                                </option>
                            @endforeach
                        </select>

                    </div>
                </div>


            </div>
            <!-- /.form-group -->
        </div>
        <div class="card-body pt-0">
            <div class="tab-content">
                <table id="example1" class="table table-bordered table-striped fs-10 mb-0">
                    <thead class="bg-200">
                    <tr>
                        <th scope="col">Nro.</th>
                        <th scope="col">Fecha</th>
                        <th scope="col">Nro. motor</th>
                        <th scope="col">Modelo</th>
                        <th scope="col">Chasis</th>
                        <th scope="col">Cliente</th>
                        <th scope="col">Técnico</th>
                        <th scope="col">Monto</th>
                        <th scope="col">Servicio</th>
                        <th scope="col">Cerrado</th>
                        <th scope="col">Sucursal</th>
                        <th scope="col">Vendedor</th>
                        <th scope="col">Acciones</th>

                    </tr>
                    </thead>
                    <tbody>

                    </tbody>

                </table>
                <div id="totales-servicios" class="mt-3 fs-10"></div>
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

    <!-- daterangepicker -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.11.2/moment.min.js"></script>

    <!-- Select2 -->
    <script src="{{ asset('bower_components/select2/dist/js/select2.min.js') }}"></script>
    <script src="{{ asset('bower_components/select2/dist/js/i18n/es.js') }}"></script>

    <!-- page script -->
    <!-- page script -->
    <script>
        $(document).ready(function() {
            $('.js-example-basic-single').select2({
                language: 'es'});
            var table =  $('#example1').DataTable({
                "processing": true, // Activar la indicación de procesamiento
                "serverSide": true, // Habilitar el procesamiento del lado del servidor
                "autoWidth": false, // Desactiva el ajuste automático del anchos
                responsive: true,
                scrollX: true,
                paging : true,
                "ajax": {
                    "url": "{{ route('servicios.dataTable') }}",
                    "type": "POST",
                    "data": function (d) {
                        d._token = '{{ csrf_token() }}'; // Agrega el token CSRF si estás usando Laravel
                        d.sucursal_id = $('#filtroSucursal').val();
                        d.user_id = $('#filtroUsuario').val();
                        d.fecha_desde = $('#fechaDesde').val();
                        d.fecha_hasta = $('#fechaHasta').val();
                    },
                    "error": function(xhr, error, thrown) {
                        if (xhr.status === 401) {
                            // Usuario no autenticado, redirigir al login
                            window.location.href = "{{ route('login') }}";
                        } else {
                            console.error("Error al cargar los datos:", error);
                        }
                    },
                    "dataSrc": function(json) {
                        // Forzar que los totales sean números
                        let totalServicios = Number(json.totales.totalServicios) || 0;
                        let totalServiciosImporte = Number(json.totales.totalServiciosImporte) || 0;

                        let formatter = new Intl.NumberFormat('es-AR'); // o 'es-ES' según prefieras

                        $('#totales-servicios').html(`
                                                <div>
                                                    <strong>Total de servicios realizadas:</strong> ${formatter.format(totalServicios)} <br>

                                                    <strong>Importe total:</strong> $${formatter.format(totalServiciosImporte)}
                                                </div>
                                            `);


                        return json.data;
                    }
                },
                columns: [
                    { data: 'nro', name: 'nro' },
                    {

                        data: 'carga',
                        name: 'carga',
                        render: function(data) {
                            // Verificar si el dato es válido
                            if (data) {

                                return moment(data).format('DD/MM/YYYY HH:mm:ss');
                            }
                            // Si no hay datos, retornar un valor por defecto o una cadena vacía
                            return '';
                        }
                    },

                    { data: 'motor', name: 'motor' },
                    { data: 'modelo', name: 'modelo' },
                    { data: 'chasis', name: 'chasis' },
                    { data: 'cliente', name: 'cliente' },
                    { data: 'mecanicos', name: 'mecanicos' },
                    { data: 'monto', name: 'monto' },
                    { data: 'tipo_servicio', name: 'tipo_servicio' },
                    { data: 'pagado', name: 'pagado' },
                    { data: 'sucursal_nombre', name: 'sucursal_nombre' },
                    { data: 'usuario_nombre', name: 'usuario_nombre' },


                    // Actions column
                    {
                        "data": "id",
                        "orderable": false,
                        "searchable": false,
                        "render": function(data, type, row) {
                            // Construir HTML para las acciones
                            var actionsHtml = '<div>';
                            @can('servicio-ver')

                                actionsHtml += '<a href="{{ route("servicios.show", ":id") }}" class="btn btn-link p-0" alt="Ver" title="Ver" data-bs-toggle="tooltip" data-bs-placement="top"><span class="text-500 fas fa-search"></span></a>'.replace(':id', row.id);
                            @endcan
                            // Agregar enlace de edición si el usuario tiene permiso
                            @can('servicio-editar')
                                actionsHtml += '<a href="{{ route("servicios.edit", ":id") }}" class="btn btn-link p-0" alt="Editar" title="Editar" data-bs-toggle="tooltip" data-bs-placement="top" style="margin-right: 5px;"><span class="text-500 fas fa-edit"></span></a>'.replace(':id', row.id);
                            @endcan

                            actionsHtml += '<a href="{{ route("servicios.pdf") }}?servicio_id=' + row.id + '" alt="Descargar PDF" title="Descargar PDF" target="_blank" style="margin-right: 5px;" class="btn btn-link p-0"><span class="fas fa-file-pdf text-500"></span></a>';


                            // Agregar formulario de eliminación si el servicio_ tiene permiso
                            @can('servicio-eliminar')
                                actionsHtml += '<form id="delete-form-' + row.id + '" method="post" action="{{ route('servicios.destroy', '') }}/' + row.id + '" style="display: none">';
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
                order: [[0, 'desc']],
                "language": {
                    "url": "{{ asset('bower_components/datatables.net/lang/es-AR.json') }}"
                },
                initComplete: function () {
                    // Eliminar las clases 'form-control' y 'input-sm', y agregar 'form-select' (para Bootstrap 5)
                    $('select[name="example1_length"]').removeClass('form-control');
                    $('input[type="search"]').removeClass('form-control');
                    $('input[type="search"]').css('width', '70%');
                }
            });

            $('#filtroSucursal,#filtroUsuario,#fechaDesde, #fechaHasta').change(function() {
                table.ajax.reload();
            });
        });


    </script>
@endsection
