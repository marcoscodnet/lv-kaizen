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
                    <h5 class="mb-0" data-anchor="data-anchor"><i class="fa fa-building" aria-hidden="true"></i>Sucursales</h5>
                </div>
                <div class="col-auto ms-auto">
                    <div class="nav nav-pills nav-pills-falcon flex-grow-1" role="tablist">
                        <a class='pull-right btn btn-success' href="{{ route('sucursals.create') }}">Nueva</a>
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


                        <th class="text-900 sort">Nombre</th>
                        <th class="text-900 sort">E-mail</th>
                        <th class="text-900 sort">Teléfono</th>
                        <th class="text-900 sort">Localidad</th>


                        <th>Acciones</th>

                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($sucursals as $sucursal)
                        <tr>

                            <td>{{ $sucursal->nombre }}</td>
                            <td>{{ $sucursal->email }}</td>
                            <td>{{ $sucursal->telefono }}</td>
                            <td>{{ $sucursal->localidad }}</td>
                            <td>@can('sucursal-editar')<a href="{{ route('sucursals.edit',$sucursal->id) }}" alt="Editar" title="Editar" style="margin-right: 5px;"><i class="fas fa-edit"></i></a>@endcan

                                @can('rol-eliminar')
                                    <form id="delete-form-{{ $sucursal->id }}" method="post" action="{{ route('sucursals.destroy',$sucursal->id) }}" style="display: none">
                                        {{ csrf_field() }}
                                        {{ method_field('DELETE') }}
                                    </form>

                                <a href="" onclick="
                        if(confirm('Está seguro?'))
                        {
                        event.preventDefault();
                        document.getElementById('delete-form-{{ $sucursal->id }}').submit();
                        }
                        else{
                        event.preventDefault();
                        }" alt="Eliminar" title="Eliminar"><i class="fas fa-trash"></i></a>@endcan
                            </td>

                        </tr>
                    @endforeach
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


    <!-- page script -->
    <script>
        $(document).ready(function() {
            $('#example1').DataTable({
                "autoWidth": false, // Desactiva el ajuste automático del anchos
                responsive: true,
                scrollX: true,
                "language": {
                    "url": "{{ asset('bower_components/datatables.net/lang/es-AR.json') }}"
                }
            });
        });

    </script>
@endsection