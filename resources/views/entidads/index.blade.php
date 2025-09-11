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
                    <h5 class="fs-9 mb-0 text-nowrap py-2 py-xl-0"><i class="fa fa-university" aria-hidden="true"></i><span class="ms-2">Entidades</span></h5>
                </div>
                <div class="col-8 col-sm-auto text-end ps-2">

                    <div id="table-customers-replace-element">
                        <a class="btn btn-falcon-default btn-sm d-inline-flex align-items-center" href="{{ route('entidads.create') }}">
                            <span class="fas fa-plus"></span>
                            <span class="d-none d-sm-inline-block ms-2">Nueva</span>
                        </a>

                    </div>
                </div>




            </div>
            @include('includes.messages')
        </div>
        <div class="card-body pt-0">
            <div class="tab-content table-responsive">
                <table id="example1" class="table table-striped table-hover table-sm nowrap w-100">
                    <thead class="bg-200">
                    <tr>


                        <th scope="col">Nombre</th>
                        <th scope="col">Activa</th>


                        <th scope="col">Acciones</th>

                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($entidads as $entidad)
                        <tr>

                            <td>{{ $entidad->nombre }}</td>
                            <td>{{ $entidad->activa?'SI':'NO' }}</td>
                            <td class="text-end"><div>

                                    {{-- Botón Ver (lupa) --}}
                                    @can('entidad-ver')
                                        <a class="btn btn-link p-0"
                                           href="{{ route('entidads.show', $entidad->id) }}"
                                           alt="Ver"
                                           title="Ver"
                                           data-bs-toggle="tooltip"
                                           data-bs-placement="top">
                                            <span class="text-500 fas fa-search"></span>
                                        </a>
                                    @endcan

                                    @can('entidad-editar')<a class="btn btn-link p-0" href="{{ route('entidads.edit',$entidad->id) }}" alt="Editar" title="Editar" data-bs-toggle="tooltip" data-bs-placement="top"><span class="text-500 fas fa-edit"></span></a>@endcan

                                @can('entidad-eliminar')
                                    <form id="delete-form-{{ $entidad->id }}" method="post" action="{{ route('entidads.destroy',$entidad->id) }}" style="display: none">
                                        {{ csrf_field() }}
                                        {{ method_field('DELETE') }}
                                    </form>

                                <a href="" onclick="
                        if(confirm('Está seguro?'))
                        {
                        event.preventDefault();
                        document.getElementById('delete-form-{{ $entidad->id }}').submit();
                        }
                        else{
                        event.preventDefault();
                        }" alt="Eliminar" title="Eliminar" class="btn btn-link p-0" data-bs-toggle="tooltip" data-bs-placement="top"><span class="text-500 fas fa-trash-alt"></span></a>@endcan





                            </div></td>

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
