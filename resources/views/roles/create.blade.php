@extends('layouts.app')
@section('headSection')



@endsection

@section('content')
    <div class="card mb-3">
        <div class="card-header">
            <div class="row flex-between-end">
                <div class="col-auto align-self-center">
                    <h5 class="mb-0" data-anchor="data-anchor"><i class="fa fa-user-plus" aria-hidden="true"></i><span class="ms-2">Crear rol</span></h5>
                </div>
                <div class="col-auto ms-auto">

                </div>
            </div>
        </div>
        <div class="card-body bg-body-tertiary">
            <form role="form" action="{{ route('roles.store') }}" method="post" >
                {{ csrf_field() }}
                <div class="tab-content">
                    @include('includes.messages')
                    <div class="tab-pane preview-tab-pane active" role="tabpanel" aria-labelledby="tab-dom-b5247297-25bf-45ab-80de-5d87d5130cfa" id="dom-b5247297-25bf-45ab-80de-5d87d5130cfa">
                        <div class="mb-3">
                            <label class="form-label" for="nombre">Nombre</label>
                            <input class="form-control" id="nombre"  type="text" name="name" placeholder="Nombre" value="{{ old('name') }}">
                        </div>
                        <div class="mb-3">
                            <strong>Permisos:</strong>
                            <div class="row">
                            @foreach($permission as $value)
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <label>
                                                <input type="checkbox" name="permission[]" value="{{ $value->id }}" class="name">
                                                {{ $value->name }}
                                            </label>
                                        </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">Guardar</button>
                            <a href='{{ route('roles.index') }}' class="btn btn-warning">Volver</a>
                        </div>
                    </div>
                </div>
                </div>
            </form>
        </div>
    </div>

    <!-- /.content-wrapper -->
@endsection
@section('footerSection')
    <!-- jQuery 3 -->
    <script src="{{ asset('bower_components/jquery/dist/jquery.min.js') }}"></script>
    <!-- Bootstrap 3.3.7 -->
    <script src="{{ asset('bower_components/bootstrap/dist/js/bootstrap.min.js') }}"></script>
    <!-- SlimScroll -->
    <script src="{{ asset('bower_components/jquery-slimscroll/jquery.slimscroll.min.js') }}"></script>
    <!-- FastClick -->
    <script src="{{ asset('bower_components/fastclick/lib/fastclick.js') }}"></script>
    <!-- FastClick -->
    <script src="{{ asset('bower_components/fastclick/lib/fastclick.js') }}"></script>
    <!-- AdminLTE App -->
    <script src="{{ asset('dist/js/adminlte.min.js') }}"></script>
    <!-- AdminLTE for demo purposes -->
    <script src="{{ asset('dist/js/demo.js') }}"></script>
    <!-- page script -->

@endsection
