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
                    <h5 class="fs-9 mb-0 text-nowrap py-2 py-xl-0"><i class="fa fa-exchange-alt" aria-hidden="true"></i><span class="ms-2">Movimientos de piezas</span></h5>
                </div>
                <div class="col-8 col-sm-auto text-end ps-2">

                    <div id="table-customers-replace-element">
                        <a class="btn btn-falcon-default btn-sm d-inline-flex align-items-center" href="{{ route('movimientoPiezas.create') }}">
                            <span class="fas fa-plus"></span>
                            <span class="d-none d-sm-inline-block ms-2">Nuevo</span>
                        </a>
                        <a class="btn btn-falcon-default btn-sm d-inline-flex align-items-center ms-2"
                           href="#"
                           onclick="exportarExcel()">
                            <span class="fas fa-file-excel"></span>
                            <span class="d-none d-sm-inline-block ms-2">Excel</span>
                        </a>

                        <a class="btn btn-falcon-default btn-sm d-inline-flex align-items-center ms-2"
                           href="#"
                           onclick="exportarPDF()">
                            <span class="fas fa-file-pdf"></span>
                            <span class="d-none d-sm-inline-block ms-2">PDF</span>
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
                        <label for="filtroUsuario">Usuarios:</label>
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
            <div class="tab-content table-responsive">
                <table id="example1" class="table table-bordered table-hover fs-10 mb-0">
                    <thead class="bg-200">
                    <tr>


                        <th scope="col">Usuario</th>

                        <th scope="col">Origen</th>
                        <th scope="col">Destino</th>

                        <th scope="col">Envío</th>
                        <th>Piezas</th>
                        <th scope="col">Estado</th>
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

    <!-- daterangepicker -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.11.2/moment.min.js"></script>

    <!-- Select2 -->
    <script src="{{ asset('bower_components/select2/dist/js/select2.min.js') }}"></script>
    <script src="{{ asset('bower_components/select2/dist/js/i18n/es.js') }}"></script>

    <!-- page script -->
    <script>
        const USER_SUCURSAL_ID = {{ auth()->user()->sucursal_id }};
        const ACEPTAR_URL = "{{ url('/movimientoPiezas') }}";
        const ES_ADMIN = @json(auth()->user()->hasRole('Administrador'));
        $(document).ready(function() {
            $('.js-example-basic-single').select2({
                language: 'es'});
            var table = $('#example1').DataTable({
                stripeClasses: [],
                "processing": true, // Activar la indicación de procesamiento
                "serverSide": true, // Habilitar el procesamiento del lado del servidor
                "autoWidth": false, // Desactiva el ajuste automático del anchos
                responsive: true,
                scrollX: true,
                paging : true,

                "ajax": {
                    "url": "{{ route('movimientoPiezas.dataTable') }}",
                    "type": "POST",
                    "data": function (d) {
                        d._token = '{{ csrf_token() }}'; // Agrega el token CSRF si estás usando Laravel
                        d.user_id = $('#filtroUsuario').val();
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

                    { data: 'usuario_nombre', name: 'usuario_nombre' },



                    { data: 'origen_nombre', name: 'origen_nombre' },
                    { data: 'destino_nombre', name: 'destino_nombre' },

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

                    { data: 'piezas', name: 'piezas' , orderable: false},
                    { data: 'estado_texto', name: 'estado_texto' },
                    // Actions column
                    {
                        "data": "id",
                        "orderable": false,
                        "searchable": false,
                        "render": function(data, type, row) {
                            // Construir HTML para las acciones
                            var actionsHtml = '<div>';

                            @can('pieza-movimiento-ver')

                                actionsHtml += '<a href="{{ route("movimientoPiezas.show", ":id") }}" class="btn btn-link p-0" alt="Ver" title="Ver" data-bs-toggle="tooltip" data-bs-placement="top"><span class="text-500 fas fa-search"></span></a>'.replace(':id', row.id);
                            @endcan
                                @can('imprimir-remito')
                                actionsHtml += '<a href="{{ route("movimientoPiezas.pdf") }}?movimientoPieza_id=' + row.id + '" alt="Descargar PDF" title="Descargar PDF" target="_blank"  class="btn btn-link p-0"><span class="fas fa-file-pdf text-500"></span></a>';

                            @endcan
                            // ============================
                            // BOTÓN ACEPTAR ✔️
                            // ============================

                            @can('pieza-movimiento-aceptar')
                            if (
                                row.estado &&
                                row.estado.toLowerCase().trim() === 'pendiente' &&
                                (
                                    Number(row.sucursal_destino_id) === Number(USER_SUCURSAL_ID)
                                    || ES_ADMIN
                                )
                            ) {
                                actionsHtml += `
                                            <form method="POST"
                                                  action="${ACEPTAR_URL}/${row.id}/aceptar"
                                                  style="display:inline"
                                                  onsubmit="return confirm('¿Aceptar movimiento?')">
                                                @csrf
                                                <button class="btn btn-link p-0" title="Aceptar">
                                                    <span class="fas fa-check text-500"></span>
                                                </button>
                            </form>`;
                            }
                            @endcan


                            // Agregar formulario de eliminación si el movimiento tiene permiso
                            @can('pieza-movimiento-eliminar')
                                actionsHtml += '<form id="delete-form-' + row.id + '" method="post" action="{{ route('movimientoPiezas.destroy', '') }}/' + row.id + '" style="display: none">';
                            actionsHtml += '{{ csrf_field() }}';
                            actionsHtml += '{{ method_field('DELETE') }}';
                            actionsHtml += '</form>';
                            actionsHtml += '<a href="" onclick="if(confirm(\'Está seguro?\')) {event.preventDefault(); document.getElementById(\'delete-form-' + row.id + '\').submit();} else {event.preventDefault();}" class="btn btn-link p-0" data-bs-toggle="tooltip" data-bs-placement="top" title="Eliminar"><span class="text-500 fas fa-trash-alt"></span></a>';
                            @endcan
                                actionsHtml += '</div>';
                                return actionsHtml;

                        }

                    },
                    { data: 'id', name: 'id', visible: false }, // Columna oculta para ordenar
                ],
                order: [[5, 'desc']], // Ordenar por la columna oculta 'id' descendente
                "language": {
                    "url": "{{ asset('bower_components/datatables.net/lang/es-AR.json') }}"
                },

                rowCallback: function (row, data) {
                    if (data.estado === 'Pendiente') {
                        $('td', row).css('background-color', '#f8d7da');
                    }
                },
                stateSave: true,
                // Guardar y restaurar el filtro externo
                stateSaveParams: function (settings, data) {
                    data.filtroUsuario = $('#filtroUsuario').val();
                },
                stateLoadParams: function (settings, data) {
                    if (data.filtroUsuario) {
                        $('#filtroUsuario').val(data.filtroUsuario).trigger('change');
                    }
                },
                initComplete: function () {
                    // Eliminar las clases 'form-control' y 'input-sm', y agregar 'form-select' (para Bootstrap 5)
                    $('select[name="example1_length"]').removeClass('form-control');
                    $('input[type="search"]').removeClass('form-control');
                    $('input[type="search"]').css('width', '70%');
                }
            });
            $('#filtroUsuario').change(function() {
                table.ajax.reload(); // Recargar la tabla cuando cambie el filtro de período
            });
        });




        function exportarExcel() {
            let usuario = $('#filtroUsuario').val();

            let busqueda = $('#example1_filter input').val(); // <-- esto captura la búsqueda
            let url = "{{ route('movimientoPiezas.exportarXLS') }}"
                + "?user_id=" + usuario

                + "&search=" + encodeURIComponent(busqueda); // <-- pasar búsqueda

            window.location.href = url;
        }

        function exportarPDF() {
            let usuario = $('#filtroUsuario').val();

            let busqueda = $('#example1_filter input').val(); // <-- esto captura la búsqueda

            let url = "{{ route('movimientoPiezas.exportarPDF') }}"
                + "?user_id=" + usuario

                + "&search=" + encodeURIComponent(busqueda); // <-- pasar búsqueda

            window.location.href = url;
        }
    </script>
@endsection
