@extends('layouts.app')

@section('headSection')



    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('bower_components/datatables.net-bs/css/dataTables.bootstrap.css') }}">


@endsection


@section('content')
    <div class="card mb-3">

    <div class="card-header">
        <div class="row flex-between-end">
            <div class="col-auto align-self-center">
                <h5 class="mb-0" data-anchor="data-anchor"><i class="fa fa-user" aria-hidden="true"></i>Usuarios</h5>
            </div>
            <div class="col-auto ms-auto">
                <div class="nav nav-pills nav-pills-falcon flex-grow-1" role="tablist">
                    <a class='pull-right btn btn-success' href="{{ route('users.create') }}">Nuevo</a>
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

                    <th class="text-900 sort"></th>
                    <th class="text-900 sort">Nombre</th>

                    <th class="text-900 sort">E-mail</th>
                    <th class="text-900 sort">Roles</th>
                    <th>Acciones</th>

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
                    }
                },
                columns: [
                    { data: 'image', name: 'image', render: function(data, type, row) {
                            if (data) {
                                return '<img src="{{ url('images/') }}/' + data + '" class="img-circle" width="50px">';
                            } else {
                                return '<img src="{{ url('images/user.png') }}" class="img-circle">';
                            }
                        } },
                    { data: 'name', name: 'name' },

                    { data: 'email', name: 'email' },
                    { data: 'roles', name: 'roles', render: function(data, type, row) {
                            var rolesHtml = '';
                            if (data && data.length > 0) {
                                data.forEach(function(role) {
                                    rolesHtml += '<label class="badge badge-success">' + role.name + '</label>';
                                });
                            }
                            return rolesHtml;
                        } },
                    // Actions column
                    {
                        "data": "id",
                        "orderable": false,
                        "searchable": false,
                        "render": function(data, type, row) {
                            // Construir HTML para las acciones
                            var actionsHtml = '';

                            // Agregar enlace de edición si el usuario tiene permiso
                            @can('usuario-editar')
                                actionsHtml += '<a href="{{ route("users.edit", ":id") }}" alt="Editar" title="Editar" style="margin-right: 5px;"><i class="fas fa-edit"></i></a>'.replace(':id', row.id);
                            @endcan


                            // Agregar formulario de eliminación si el usuario tiene permiso
                            @can('usuario-eliminar')
                                actionsHtml += '<form id="delete-form-' + row.id + '" method="post" action="{{ route('users.destroy', '') }}/' + row.id + '" style="display: none">';
                            actionsHtml += '{{ csrf_field() }}';
                            actionsHtml += '{{ method_field('DELETE') }}';
                            actionsHtml += '</form>';
                            actionsHtml += '<a href="" onclick="if(confirm(\'Está seguro?\')) {event.preventDefault(); document.getElementById(\'delete-form-' + row.id + '\').submit();} else {event.preventDefault();}" alt="Eliminar" title="Eliminar" ><i class="fas fa-trash"></i></a>';
                            @endcan

                                return actionsHtml;

                        },
                    }
                ],
                "language": {
                    "url": "{{ asset('bower_components/datatables.net/lang/es-AR.json') }}"
                }
            });
        });

    </script>
@endsection
