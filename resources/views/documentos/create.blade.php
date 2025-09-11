@extends('layouts.app')
@section('headSection')
@endsection

@section('content')
    <div class="card mb-3">
        <div class="card-header">
            <div class="row flex-between-end">
                <div class="col-auto align-self-center">
                    <h5 class="mb-0" data-anchor="data-anchor"><i class="fa fa-file" aria-hidden="true"></i><span class="ms-2">Crear Documento</span></h5>
                </div>
                <div class="col-auto ms-auto">

                </div>
            </div>
        </div>
        <div class="card-body bg-body-tertiary">
            <form role="form" action="{{ route('documentos.store') }}" method="post" enctype="multipart/form-data">
                {{ csrf_field() }}
                <div class="tab-content">
                    <div class="box-body">

                        @include('includes.messages')
                        <div class="row">
                            <!-- Nombre -->
                            <div class="col-12 col-lg-4">
                                <div class="form-group">
                                    <label for="nombre">Nombre</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Nombre" value="{{ old('nombre') }}">
                                </div>
                            </div>
                            <!-- Orden -->
                            <div class="col-12 col-lg-2">
                                <div class="form-group">
                                    <label for="orden">Orden</label>
                                    <input type="number" class="form-control" id="orden" name="orden" placeholder="Orden" value="{{ old('orden') }}">
                                </div>
                            </div>
                            <!-- Habilitado -->
                            <div class="col-12 col-lg-2">
                                <div class="form-check mt-4">
                                    <input type="hidden" name="habilitado" value="0">
                                    <input class="form-check-input" type="checkbox" id="habilitado" name="habilitado" value="1"
                                        {{ old('habilitado', $obj->habilitado ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="habilitado">
                                        Habilitado
                                    </label>
                                </div>
                            </div>




                        </div>
                        <div class="row">
                            <!-- Archivo -->
                            <div class="col-12 col-lg-8 mt-3">
                                <div class="form-group">
                                    <label for="path">Archivo</label>
                                    <input type="file" class="form-control" id="path" name="path">
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
    <script src="{{ asset('bower_components/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('bower_components/bootstrap/dist/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('bower_components/jquery-slimscroll/jquery.slimscroll.min.js') }}"></script>
    <script src="{{ asset('bower_components/fastclick/lib/fastclick.js') }}"></script>
    <script src="{{ asset('assets/js/confirm-exit.js') }}"></script>
@endsection
