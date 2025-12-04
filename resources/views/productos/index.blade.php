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
                    <h5 class="fs-9 mb-0 text-nowrap py-2 py-xl-0"><i class="fa fa-box" aria-hidden="true"></i><span class="ms-2">Productos</span></h5>
                </div>
                <div class="col-8 col-sm-auto text-end ps-2">

                    <div id="table-customers-replace-element">
                        <a class="btn btn-falcon-default btn-sm d-inline-flex align-items-center" href="{{ route('productos.create') }}">
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
                        <label for="filtroDiscontinuo">Discontinuos:</label>
                        <select id="filtroDiscontinuo" class="form-control">
                            <option value="-1">Todos</option>
                            <option value="2">No</option>
                            <option value="1">Si</option>
                        </select>

                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="filtroStockMinimo">Debajo del mínimo:</label>
                        <select id="filtroStockMinimo" class="form-control">
                            <option value="0">Todos</option>
                            <option value="1">Sí</option>
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


                        <th scope="col">Tipo</th>
                        <th scope="col">Marca</th>
                        <th scope="col">Modelo</th>
                        <th scope="col">Color</th>
                        <th scope="col">$ Sugerido</th>
                        <th scope="col">Stock mín.</th>
                        <th scope="col">Stock Actual</th>
                        <th scope="col">Discontinuo</th>


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

    <!-- page script -->
    <!-- page script -->
    <script>
        $(document).ready(function() {
            var table = $('#example1').DataTable({
                "processing": true, // Activar la indicación de procesamiento
                "serverSide": true, // Habilitar el procesamiento del lado del servidor
                "autoWidth": false, // Desactiva el ajuste automático del anchos
                responsive: true,
                scrollX: true,
                paging : true,
                "ajax": {
                    "url": "{{ route('productos.dataTable') }}",
                    "type": "POST",
                    "data": function (d) {
                        d._token = '{{ csrf_token() }}'; // Agrega el token CSRF si estás usando Laravel
                        d.discontinuo = $('#filtroDiscontinuo').val();
                        d.filtroStockMinimo = $('#filtroStockMinimo').val();
                        // Agrega otros parámetros si es necesario
                        // d.otroParametro = valor;
                    }
                },
                columns: [

                    { data: 'tipo_unidad_nombre', name: 'tipo_unidad_nombre' },


                    { data: 'marca_nombre', name: 'marca_nombre' },
                    { data: 'modelo_nombre', name: 'modelo_nombre' },
                    { data: 'color_nombre', name: 'color_nombre' },
                    {
                        data: 'precio',
                        name: 'precio',
                        render: function (data, type, row) {
                            if (type === 'display') {
                                let valor = (data === null || data === '') ? '' :
                                    parseFloat(data).toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

                                // Si el usuario tiene permiso se genera el input
                                @can('producto-editar')
                                    return '<input type="text" class="form-control form-control-sm input-precio" ' +
                                    'data-id="' + row.id + '" ' +
                                    'value="' + valor + '">';
                                @else
                                    return valor;
                                @endcan
                            }
                            return data;
                        }
                    },



                    { data: 'minimo', name: 'minimo' },
                    { data: 'stock_actual', name: 'stock_actual' }, // <-- Nueva columna
                    { data: 'discontinuo', name: 'discontinuo' },
                    // Actions column
                    {
                        "data": "id",
                        "orderable": false,
                        "searchable": false,
                        "render": function(data, type, row) {
                            // Construir HTML para las acciones
                            var actionsHtml = '<div>';
                            @can('producto-ver')

                                actionsHtml += '<a href="{{ route("productos.show", ":id") }}" class="btn btn-link p-0" alt="Ver" title="Ver" data-bs-toggle="tooltip" data-bs-placement="top"><span class="text-500 fas fa-search"></span></a>'.replace(':id', row.id);
                            @endcan
                            // Agregar enlace de edición si el usuario tiene permiso
                            @can('producto-editar')
                                actionsHtml += '<a href="{{ route("productos.edit", ":id") }}" class="btn btn-link p-0" alt="Editar" title="Editar" data-bs-toggle="tooltip" data-bs-placement="top"><span class="text-500 fas fa-edit"></span></a>'.replace(':id', row.id);
                            @endcan


                            // Agregar formulario de eliminación si el producto tiene permiso
                            @can('producto-eliminar')
                                actionsHtml += '<form id="delete-form-' + row.id + '" method="post" action="{{ route('productos.destroy', '') }}/' + row.id + '" style="display: none">';
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
                    data.filtroDiscontinuo = $('#filtroDiscontinuo').val();
                    data.filtroStockMinimo = $('#filtroStockMinimo').val();

                },
                stateLoadParams: function (settings, data) {
                    if (data.filtroDiscontinuo) {
                        $('#filtroDiscontinuo').val(data.filtroDiscontinuo).trigger('change');
                    }
                    if (data.filtroStockMinimo) {
                        $('#filtroStockMinimo').val(data.filtroStockMinimo).trigger('change');
                    }

                },
                initComplete: function () {
                    // Eliminar las clases 'form-control' y 'input-sm', y agregar 'form-select' (para Bootstrap 5)
                    $('select[name="example1_length"]').removeClass('form-control');
                    $('input[type="search"]').removeClass('form-control');
                    $('input[type="search"]').css('width', '70%');
                }
            });
            $('#filtroDiscontinuo').change(function() {
                table.ajax.reload(); // Recargar la tabla cuando cambie el filtro de período
            });
            $('#filtroStockMinimo').change(function() {
                table.ajax.reload();
            });

        });
        // Evento blur para guardar automáticamente
        $(document).on('blur', '.input-precio', function() {
            let input = $(this);
            let id = input.data('id');
            let valor = input.val().replace(/\./g, '').replace(',', '.'); // convertir formato a float

            $.ajax({
                url: "{{ route('productos.updatePrecio') }}", // ruta en Laravel
                type: "POST",
                data: {
                    _token: '{{ csrf_token() }}',
                    id: id,
                    precio: valor
                },
                success: function(response) {
                    if (response.success) {
                        input.css('border-color', 'green');
                    } else {
                        input.css('border-color', 'red');
                        alert(response.message || "Error al guardar.");
                    }
                },
                error: function() {
                    input.css('border-color', 'red');
                    alert("Error en la comunicación con el servidor.");
                }
            });
        });

        function exportarExcel() {
            let discontinuo = $('#filtroDiscontinuo').val();
            let stockMinimo = $('#filtroStockMinimo').val();
            let busqueda = $('#example1_filter input').val(); // <-- esto captura la búsqueda
            let url = "{{ route('productos.exportarXLS') }}"
                + "?discontinuo=" + discontinuo
                + "&stockMinimo=" + stockMinimo
                + "&search=" + encodeURIComponent(busqueda); // <-- pasar búsqueda

            window.location.href = url;
        }

        function exportarPDF() {
            let discontinuo = $('#filtroDiscontinuo').val();
            let stockMinimo = $('#filtroStockMinimo').val();
            let busqueda = $('#example1_filter input').val(); // <-- esto captura la búsqueda

            let url = "{{ route('productos.exportarPDF') }}"
                + "?discontinuo=" + discontinuo
                + "&stockMinimo=" + stockMinimo
                + "&search=" + encodeURIComponent(busqueda); // <-- pasar búsqueda

            window.location.href = url;
        }

    </script>
@endsection
