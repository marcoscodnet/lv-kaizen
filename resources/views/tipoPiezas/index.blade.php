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
                    <h5 class="fs-9 mb-0 text-nowrap py-2 py-xl-0"><i class="fa fa-cogs" aria-hidden="true"></i><span class="ms-2">Tipos de Piezas</span></h5>
                </div>
                <div class="col-8 col-sm-auto text-end ps-2">

                    <div id="table-customers-replace-element">
                        <a class="btn btn-falcon-default btn-sm d-inline-flex align-items-center" href="{{ route('tipoPiezas.create') }}">
                            <span class="fas fa-plus"></span>
                            <span class="d-none d-sm-inline-block ms-2">Nuevo</span>
                        </a>

                    </div>
                </div>
            </div>
            @include('includes.messages')
        </div>
        <div class="card-body pt-0">
            <div class="tab-content table-responsive">
                <table id="example1" class="table table-bordered table-striped table-hover fs-10 mb-0">
                    <thead class="bg-200">
                    <tr>


                        <th scope="col">Nombre</th>



                        <th scope="col">Acciones</th>

                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($tipoPiezas as $tipoPieza)
                        <tr>

                            <td>{{ $tipoPieza->nombre }}</td>

                            <td ><div>
                                    {{-- Botón Ver (lupa) --}}
                                    @can('tipo-pieza-ver')
                                        <a class="btn btn-link p-0"
                                           href="{{ route('tipoPiezas.show', $tipoPieza->id) }}"
                                           alt="Ver"
                                           title="Ver"
                                           data-bs-toggle="tooltip"
                                           data-bs-placement="top">
                                            <span class="text-500 fas fa-search"></span>
                                        </a>
                                    @endcan

                                    @can('tipo-pieza-editar')<a href="{{ route('tipoPiezas.edit',$tipoPieza->id) }}" class="btn btn-link p-0" alt="Editar" title="Editar" data-bs-toggle="tooltip" data-bs-placement="top"><span class="text-500 fas fa-edit"></span></a>@endcan

                                @can('tipo-pieza-eliminar')
                                    <form id="delete-form-{{ $tipoPieza->id }}" method="post" action="{{ route('tipoPiezas.destroy',$tipoPieza->id) }}" style="display: none">
                                        {{ csrf_field() }}
                                        {{ method_field('DELETE') }}
                                    </form>

                                <a href="" onclick="
                        if(confirm('Está seguro?'))
                        {
                        event.preventDefault();
                        document.getElementById('delete-form-{{ $tipoPieza->id }}').submit();
                        }
                        else{
                        event.preventDefault();
                        }" class="btn btn-link p-0" data-bs-toggle="tooltip" data-bs-placement="top" title="Eliminar"><span class="text-500 fas fa-trash-alt"></span></a>@endcan
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
