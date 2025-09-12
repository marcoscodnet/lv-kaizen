@extends('layouts.app')
@section('headSection')



@endsection

@section('content')
    <div class="card mb-3">
        <div class="card-header">
            <div class="row flex-between-end">
                <div class="col-auto align-self-center">
                    <h5 class="mb-0" data-anchor="data-anchor"><i class="fa fa-cash-register" aria-hidden="true"></i><span class="ms-2">Crear Concepto</span></h5>
                </div>
                <div class="col-auto ms-auto">

                </div>
            </div>
        </div>
        <div class="card-body bg-body-tertiary">
            <form role="form" action="{{ route('conceptos.store') }}" method="post" >
                {{ csrf_field() }}
                <div class="tab-content">
                    <div class="box-body">

                        @include('includes.messages')
                        <div class="row">
                            <div class="col-12 col-lg-6">
                                <div class="form-group">
                                    <label for="nombre">Nombre</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Nombre" value="{{ old('nombre') }}">
                                </div>
                            </div>
                            <div class="col-12 col-lg-3">
                                <div class="form-group">
                                    <label for="tipo">Tipo</label>

                                    <select name="tipo" id="tipo" class="form-control" required>
                                        <option value="">
                                            Seleccionar...
                                        </option>
                                        @foreach (config('tipos') as $key => $label)
                                            <option value="{{ $key }}" {{ old('tipo') == $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>


                                </div>
                            </div>
                        </div>
                        <div class="row">

                            <div class="col-12 col-lg-3">
                                <div class="form-check mt-4">
                                    <input type="hidden" name="ticket" value="0">
                                    <input class="form-check-input" type="checkbox" id="ticket" name="ticket" value="1"
                                        {{ old('ticket', $obj->ticket ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="ticket">
                                        Ticket
                                    </label>
                                </div>

                            </div>
                            <div class="col-12 col-lg-3">
                                <div class="form-check mt-4">
                                    <input type="hidden" name="referencia" value="0">
                                    <input class="form-check-input" type="checkbox" id="referencia" name="referencia" value="1"
                                        {{ old('referencia', $obj->referencia ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="referencia">
                                        Referencia
                                    </label>
                                </div>

                            </div>
                            <div class="col-12 col-lg-3">
                                <div class="form-check mt-4">
                                    <input type="hidden" name="credito" value="0">
                                    <input class="form-check-input" type="checkbox" id="credito" name="credito" value="1"
                                        {{ old('credito', $obj->credito ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="credito">
                                        Credito
                                    </label>
                                </div>

                            </div>
                            <div class="col-12 col-lg-3">
                                <div class="form-check mt-4">
                                    <input type="hidden" name="activo" value="0">
                                    <input class="form-check-input" type="checkbox" id="activo" name="activo" value="1"
                                        {{ old('activo', $obj->activo ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="activo">
                                        Activo
                                    </label>
                                </div>

                            </div>
                        </div>

                        <div class="row" style="margin-top: 10px;">
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">Guardar</button>
                                <a href='{{ route('conceptos.index') }}' class="btn btn-warning">Volver</a>
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

    <script src="{{ asset('assets/js/confirm-exit.js') }}"></script>
    <!-- page script -->

@endsection
