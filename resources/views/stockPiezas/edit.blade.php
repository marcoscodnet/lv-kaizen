@extends('layouts.app')
@section('headSection')

    <link rel="stylesheet" href="{{ asset('bower_components/select2/dist/css/select2.min.css') }}">

@endsection

@section('content')
    <div class="card mb-3">
        <div class="card-header">
            <div class="row flex-between-end">
                <div class="col-auto align-self-center">
                    <h5 class="mb-0" data-anchor="data-anchor"><i class="fa fa-toolbox" aria-hidden="true"></i><span class="ms-2">Editar stock pieza</span></h5>
                </div>
                <div class="col-auto ms-auto">

                </div>
            </div>
        </div>
        <div class="card-body bg-body-tertiary">
            <form role="form" action="{{ route('stockPiezas.update',$stockPieza->id) }}" method="post" >
                {{ csrf_field() }}
                {{ method_field('PUT') }}
                <div class="tab-content">
                    <div class="box-body">

                        @include('includes.messages')



                        <div class="row">
                            <div class="col-12 col-lg-9">
                                <div class="form-group">
                                    <label for="pieza_id">Pieza</label>
                                    <select name="pieza_id" id="pieza_id" class="form-control js-example-basic-single" disabled>

                                        @foreach($piezas as $piezaId => $pieza)
                                            <option value="{{ $piezaId }}" {{ old('pieza_id', $stockPieza->pieza_id) == $piezaId ? 'selected' : '' }}>{{ $pieza }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                        </div>


                        <div class="row">
                            <div class="col-12 col-lg-3">
                                <div class="form-group">
                                    <label for="cantidad">Cantidad</label>
                                    <input type="number" class="form-control" id="cantidad" name="cantidad" placeholder="Cantidad" value="@if (old('cantidad')){{ old('cantidad') }}@else{{ $stockPieza->cantidad }}@endif">
                                </div>
                            </div>

                            <div class="col-12 col-lg-3">
                                <div class="form-group">
                                    <label for="costo">Costo</label>
                                    <input type="number" step="0.01" class="form-control" id="costo" name="costo" placeholder="Costo" value="@if (old('costo')){{ old('costo') }}@else{{ $stockPieza->costo }}@endif">
                                </div>
                            </div>
                            <div class="col-12 col-lg-3">
                                <div class="form-group">
                                    <label for="precio_minimo">$ mínimo</label>
                                    <input type="number" step="0.01" class="form-control" id="precio_minimo" name="precio_minimo" placeholder="$ mínimo" value="@if (old('precio_minimo')){{ old('precio_minimo') }}@else{{ $stockPieza->precio_minimo }}@endif">
                                </div>
                            </div>


                        </div>

                        <div class="row">

                            <div class="col-12 col-lg-4">
                                <div class="form-group">
                                    <label for="sucursal_id">Sucursal</label>
                                    <select name="sucursal_id" class="form-control js-example-basic-single" required>

                                        @foreach($sucursals as $sucursalId => $sucursal)
                                            <option value="{{ $sucursalId }}" {{ old('sucursal_id', $stockPieza->sucursal_id) == $sucursalId ? 'selected' : '' }}>{{ $sucursal }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-lg-4">
                                <div class="form-group">
                                    <label for="proveedor">Proveedor</label>
                                    <select name="proveedor_id" id="proveedor_id" class="form-control js-example-basic-single">
                                        <option value="">
                                            Seleccionar...
                                        </option>

                                        @foreach($proveedors as $proveedorId => $proveedor)
                                            <option value="{{ $proveedorId }}" {{ old('proveedor_id', $stockPieza->proveedor_id) == $proveedorId ? 'selected' : '' }}>{{ $proveedor }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">

                            <div class="col-12 col-lg-4">
                                <div class="form-group">
                                    <label for="ingreso_visible">F. Ingreso</label>

                                    <input type="date" class="form-control" id="ingreso" name="ingreso"  value="@if (old('ingreso')){{ old('ingreso') }}@else{{ ($stockPieza->ingreso)?date('Y-m-d', strtotime($stockPieza->ingreso)):'' }}@endif" readonly>
                                </div>
                            </div>
                            <div class="col-12 col-lg-4">
                                <div class="form-group">
                                    <div class="form-group">
                                        <label for="remito">Remito</label>
                                        <input type="text" class="form-control" id="remito" name="remito" placeholder="Remito" value="@if (old('remito')){{ old('remito') }}@else{{ $stockPieza->remito }}@endif" >
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row" style="margin-top: 10px;">
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">Guardar</button>
                                <a href='{{ route('stockPiezas.index') }}' class="btn btn-warning">Volver</a>
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

    <!-- Select2 -->
    <script src="{{ asset('bower_components/select2/dist/js/select2.min.js') }}"></script>
    <script src="{{ asset('bower_components/select2/dist/js/i18n/es.js') }}"></script>

    <script src="{{ asset('assets/js/confirm-exit.js') }}"></script>
    <!-- page script -->
    <script>
        $(document).ready(function () {

            $('.js-example-basic-single').select2({
                language: 'es'});
        });
    </script>

@endsection
