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
                <h5 class="fs-9 mb-0 text-nowrap py-2 py-xl-0"><i class="fa fa-user" aria-hidden="true"></i><span class="ms-2">Usuarios</span></h5>
            </div>
            <div class="col-8 col-sm-auto text-end ps-2">

                <div id="table-customers-replace-element">
                    <a class="btn btn-falcon-default btn-sm d-inline-flex align-items-center" href="{{ route('users.create') }}">
                        <span class="fas fa-plus"></span>
                        <span class="d-none d-sm-inline-block ms-2">Nuevo</span>
                    </a>

                </div>
            </div>
        </div>
        @include('includes.messages')
    </div>
    <div class="card-body pt-0">
        <div class="tab-content">
            <table id="example1" class="table table-bordered table-striped table-hover fs-10 mb-0">
                <thead class="bg-200">
                <tr>

                    <th scope="col"></th>
                    <th scope="col">Nombre</th>

                    <th scope="col">E-mail</th>
                    <th scope="col">Roles</th>
                    <th scope="col">Acciones</th>

                </tr>
                </thead>
                <tbody>

                </tbody>

            </table>
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
                    "url": "{{ route('users.dataTable') }}",
                    "type": "POST",
                    "data": function (d) {
                        d._token = '{{ csrf_token() }}'; // Agrega el token CSRF si estás usando Laravel
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
                    { data: 'image', name: 'image', render: function(data, type, row) {
                            if (data) {
                                return '<img src="{{ url('images/') }}/' + data + '" class="img-circle" width="50px">';
                            } else {
                                return '<img src="{{ url('images/user.png') }}" class="img-circle" width="50px">';
                            }
                        } },
                    { data: 'name', name: 'name' },

                    { data: 'email', name: 'email' },
                    { data: 'roles', name: 'roles', render: function(data, type, row) {
                            var rolesHtml = '';
                            if (data && data.length > 0) {
                                data.forEach(function(role) {
                                    rolesHtml += '<label class="badge badge-success" style="color: #5d6776;background-color:#e6e8ec ;">' + role.name + '</label>';
                                });
                            }
                            return '<div style="text-align: center;">' + rolesHtml + '</div>';
                        } },
                    // Actions column
                    {
                        "data": "id",
                        "orderable": false,
                        "searchable": false,
                        "render": function(data, type, row) {
                            // Construir HTML para las acciones
                            var actionsHtml = '<div>';

                            // Agregar enlace de edición si el usuario tiene permiso
                            @can('usuario-editar')
                                actionsHtml += '<a href="{{ route("users.edit", ":id") }}" class="btn btn-link p-0" alt="Editar" title="Editar" data-bs-toggle="tooltip" data-bs-placement="top"><span class="text-500 fas fa-edit"></span></a>'.replace(':id', row.id);
                            @endcan


                            // Agregar formulario de eliminación si el usuario tiene permiso
                            @can('usuario-eliminar')
                                actionsHtml += '<form id="delete-form-' + row.id + '" method="post" action="{{ route('users.destroy', '') }}/' + row.id + '" style="display: none">';
                            actionsHtml += '{{ csrf_field() }}';
                            actionsHtml += '{{ method_field('DELETE') }}';
                            actionsHtml += '</form>';
                            actionsHtml += '<a href="" onclick="if(confirm(\'Está seguro?\')) {event.preventDefault(); document.getElementById(\'delete-form-' + row.id + '\').submit();} else {event.preventDefault();}" class="btn btn-link p-0" data-bs-toggle="tooltip" data-bs-placement="top" title="Eliminar"><span class="text-500 fas fa-trash-alt"></span></a>';
                            @endcan
                                actionsHtml += '</div>';
                                return actionsHtml;

                        },
                        "class": "text-end"
                    }
                ],
                "language": {
                    "url": "{{ asset('bower_components/datatables.net/lang/es-AR.json') }}"
                },
                stateSave: true,
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
