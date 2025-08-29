@extends('layouts.app')
@section('headSection')



@endsection

@section('content')
    <div class="card mb-3">
        <div class="card-header">
            <div class="row flex-between-end">
                <div class="col-auto align-self-center">
                    <h5 class="mb-0" data-anchor="data-anchor"><i class="fa fa-file" aria-hidden="true"></i><span class="ms-2">Editar Documento</span></h5>
                </div>
                <div class="col-auto ms-auto">

                </div>
            </div>
        </div>
        <div class="card-body bg-body-tertiary">
            <form role="form" action="{{ route('documentos.update',$documento->id) }}" method="post" >
                {{ csrf_field() }}
                {{ method_field('PUT') }}
                <div class="tab-content">
                    <div class="box-body">

                        @include('includes.messages')
                        <div class="row">
                            <div class="col-lg-offset-3 col-lg-4 col-md-3">
                                <div class="form-group">
                                    <label for="nombre">Nombre</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Nombre" value="@if (old('nombre')){{ old('nombre') }}@else{{ $documento->nombre }}@endif">
                                </div>
                            </div>
                            <!-- Orden -->
                            <div class="col-lg-offset-3 col-lg-2 col-md-3">
                                <div class="form-group">
                                    <label for="orden">Orden</label>
                                    <input type="number" class="form-control" id="orden" name="orden" placeholder="Orden" value="@if (old('orden')){{ old('orden') }}@else{{ $documento->orden }}@endif">
                                </div>
                            </div>
                            <div class="col-lg-offset-3 col-lg-2 col-md-1">
                                <div class="form-check mt-4">
                                    <input type="hidden" name="habilitado" value="0">
                                    <input class="form-check-input" type="checkbox" id="habilitado" name="habilitado" value="1"
                                        {{ old('habilitado', $documento->habilitado ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="habilitado">
                                        Habilitado
                                    </label>
                                </div>

                            </div>
                        </div>
                        <div class="row">
                            <!-- Archivo -->
                            <div class="col-lg-offset-3 col-lg-8 col-md-3 mt-3">
                                <div class="form-group">
                                    <label for="path">Archivo</label>
                                    <input type="file" class="form-control" id="path" name="path">

                                    @if($documento->path)
                                        <p class="mt-2">
                                            Archivo actual: <a href="{{ asset($documento->path) }}" target="_blank">Ver documento</a>
                                        </p>
                                    @endif
                                </div>
                            </div>

                        </div>
                        <div class="row" style="margin-top: 10px;">
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">Guardar</button>
                                <a href='{{ route('documentos.index') }}' class="btn btn-warning">Volver</a>
                            </div>
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </div>


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

    <script src="{{ asset('assets/js/confirm-exit.js') }}"></script>
    <!-- page script -->

@endsection
