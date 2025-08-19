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
                    <h5 class="fs-9 mb-0 text-nowrap py-2 py-xl-0"><i class="fa fa-money-bill" aria-hidden="true"></i><span class="ms-2">Ventas</span></h5>
                </div>
                <div class="col-8 col-sm-auto text-end ps-2">

                    <div id="table-customers-replace-element">
                        <a class="btn btn-falcon-default btn-sm d-inline-flex align-items-center" href="{{ route('ventas.create') }}">
                            <span class="fas fa-plus"></span>
                            <span class="d-none d-sm-inline-block ms-2">Registrar</span>
                        </a>

                    </div>
                </div>
            </div>
            @include('includes.messages')
        </div>
        <div class="card-body pt-0">
            <div class="tab-content">
                <table id="example1" class="table table-bordered table-striped fs-10 mb-0">
                    <thead class="bg-200">
                    <tr>

                        <th scope="col">Fecha</th>
                        <th scope="col">Cliente</th>
                        <th scope="col">Nro. motor</th>
                        <th scope="col">Modelo</th>
                        <th scope="col">Vendedor</th>
                        <th scope="col">Sucursal</th>
                        <th scope="col">Estado</th>
                        <th scope="col">Pago</th>
                        <th class="text-end" scope="col">Acciones</th>

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

    <!-- daterangepicker -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.11.2/moment.min.js"></script>

    <!-- page script -->
    <!-- page script -->
    <script>
        $(document).ready(function() {
            $('#example1').DataTable({
                "processing": true, // Activar la indicación de procesamiento
                "serverSide": true, // Habilitar el procesamiento del lado del servidor
                "autoWidth": false, // Desactiva el ajuste automático del anchos
                responsive: true,
                scrollX: true,
                paging : true,
                "ajax": {
                    "url": "{{ route('ventas.dataTable') }}",
                    "type": "POST",
                    "data": function (d) {
                        d._token = '{{ csrf_token() }}'; // Agrega el token CSRF si estás usando Laravel
                        // Agrega otros parámetros si es necesario
                        // d.otroParametro = valor;
                    }
                },
                columns: [

                    {
                        data: 'fecha',
                        name: 'fecha',
                        render: function(data) {
                            // Verificar si el dato es válido
                            if (data) {

                                return moment(data).format('DD/MM/YYYY');
                            }
                            // Si no hay datos, retornar un valor por defecto o una cadena vacía
                            return '';
                        }
                    },
                    { data: 'cliente', name: 'cliente' },
                    { data: 'motor', name: 'motor' },
                    { data: 'modelo', name: 'modelo' },
                    { data: 'usuario_nombre', name: 'usuario_nombre' },
                    { data: 'sucursal_nombre', name: 'sucursal_nombre' },
                    { data: 'autorizacion', name: 'autorizacion' },
                    { data: 'forma', name: 'forma' },

                    // Actions column
                    {
                        "data": "id",
                        "orderable": false,
                        "searchable": false,
                        "render": function(data, type, row) {
                            // Construir HTML para las acciones
                            var actionsHtml = '<div>';

                            // Agregar enlace de edición si el usuario tiene permiso
                            @can('venta-pieza-editar')
                                actionsHtml += '<a href="{{ route("ventas.edit", ":id") }}" class="btn btn-link p-0" alt="Editar" title="Editar" data-bs-toggle="tooltip" data-bs-placement="top" style="margin-right: 5px;"><span class="text-500 fas fa-edit"></span></a>'.replace(':id', row.id);
                            @endcan

                            actionsHtml += '<a href="{{ route("ventas.pdf") }}?venta_id=' + row.id + '" alt="Descargar PDF" title="Descargar PDF" target="_blank" style="margin-right: 5px;" class="btn btn-link p-0"><span class="fas fa-file-pdf text-500"></span></a>';

                            // Agregar formulario de eliminación si el venta_ tiene permiso
                            @can('venta-eliminar')
                                actionsHtml += '<form id="delete-form-' + row.id + '" method="post" action="{{ route('ventas.destroy', '') }}/' + row.id + '" style="display: none">';
                            actionsHtml += '{{ csrf_field() }}';
                            actionsHtml += '{{ method_field('DELETE') }}';
                            actionsHtml += '</form>';
                            actionsHtml += '<a href="" onclick="if(confirm(\'Está seguro?\')) {event.preventDefault(); document.getElementById(\'delete-form-' + row.id + '\').submit();} else {event.preventDefault();}" class="btn btn-link p-0 ms-2" data-bs-toggle="tooltip" data-bs-placement="top" title="Eliminar"><span class="text-500 fas fa-trash-alt"></span></a>';
                            @endcan
                                actionsHtml += '</div>';
                                return actionsHtml;

                        },
                        // Aquí agregamos la clase al <td> de la columna de acciones
                        "class": "text-end"
                    }
                ],
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
        });

    </script>
@endsection
