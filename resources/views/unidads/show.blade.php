@extends('layouts.app')
@section('headSection')



@endsection

@section('content')
    <div class="card mb-3">
        <div class="card-header">
            <div class="row flex-between-end">
                <div class="col-auto align-self-center">
                    <h5 class="mb-0" data-anchor="data-anchor"><i class="fa fa-motorcycle" aria-hidden="true"></i><span class="ms-2">Ver unidad</span></h5>
                </div>
                <div class="col-auto ms-auto">

                </div>
            </div>
        </div>
        <div class="card-body bg-body-tertiary">
            <form role="form" action="{{ route('unidads.update',$unidad->id) }}" method="post" >
                {{ csrf_field() }}
                {{ method_field('PUT') }}
                <div class="tab-content">
                    <div class="box-body">

                        @include('includes.messages')
                        <div class="row">
                            <div class="col-lg-offset-3 col-lg-6 col-md-2">
                                <div class="form-group">
                                    <label for="producto_id">Producto</label>
                                    <select name="producto_id" class="form-control js-example-basic-single" required disabled>

                                        @foreach($productos as $productoId => $producto)
                                            <option value="{{ $productoId }}" {{ old('producto_id', $unidad->producto_id) == $productoId ? 'selected' : '' }}>{{ $producto }}</option>

                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-offset-3 col-lg-4 col-md-2">
                                <div class="form-group">
                                    <label for="sucursal_id">Sucursal</label>
                                    <select name="sucursal_id" class="form-control js-example-basic-single" required disabled>

                                        @foreach($sucursals as $sucursalId => $sucursal)
                                            <option value="{{ $sucursalId }}" {{ old('sucursal_id', $unidad->sucursal_id) == $sucursalId ? 'selected' : '' }}>{{ $sucursal }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>


                        <div class="row">
                            <div class="col-lg-offset-3 col-lg-3 col-md-3">
                                <div class="form-group">
                                    <label for="motor">Nro. motor</label>
                                    <input type="text" class="form-control" id="motor" name="motor" placeholder="Nro. motor" value="@if (old('motor')){{ old('motor') }}@else{{ $unidad->motor }}@endif" required disabled>
                                </div>
                            </div>
                            <div class="col-lg-offset-3 col-lg-3 col-md-3">
                                <div class="form-group">
                                    <label for="cuadro">Nro. cuadro</label>
                                    <input type="text" class="form-control" id="cuadro" name="cuadro" placeholder="Nro. cuadro" value="@if (old('cuadro')){{ old('cuadro') }}@else{{ $unidad->cuadro }}@endif" required disabled>
                                </div>
                            </div>
                            <div class="col-lg-offset-3 col-lg-3 col-md-3">
                                <div class="form-group">
                                    <label for="patente">Patente</label>
                                    <input type="text" class="form-control" id="patente" name="patente" placeholder="Patente" value="@if (old('patente')){{ old('patente') }}@else{{ $unidad->patente }}@endif" disabled>
                                </div>
                            </div>


                        </div>

                        <div class="row">
                            <div class="col-lg-offset-3 col-lg-3 col-md-3">
                                <div class="form-group">
                                    <label for="ingreso">Ingreso</label>
                                    <input type="date" class="form-control" id="ingreso" name="ingreso"  value="@if (old('ingreso')){{ old('ingreso') }}@else{{ ($unidad->ingreso)?date('Y-m-d', strtotime($unidad->ingreso)):'' }}@endif" disabled>
                                </div>
                            </div>
                            <div class="col-lg-offset-3 col-lg-2 col-md-3">
                                <div class="form-group">
                                    <label for="remito">Nro. remito ingreso</label>
                                    <input type="text" class="form-control" id="remito" name="remito" placeholder="Nro. remito ingreso" value="@if (old('remito')){{ old('remito') }}@else{{ $unidad->remito }}@endif" disabled>
                                </div>
                            </div>
                            <div class="col-lg-offset-3 col-lg-2 col-md-3">
                                <div class="form-group">
                                    <label for="year">Año/Modelo</label>
                                    <input type="text" class="form-control" id="year" name="year" placeholder="Año/Modelo" value="@if (old('year')){{ old('year') }}@else{{ $unidad->year }}@endif" disabled>
                                </div>
                            </div>
                            <div class="col-lg-offset-3 col-lg-2 col-md-3">
                                <div class="form-group">
                                    <label for="envio">Nro. Envío</label>
                                    <input type="text" class="form-control" id="envio" name="envio" placeholder="Nro. Envío" value="@if (old('envio')){{ old('envio') }}@else{{ $unidad->envio }}@endif" disabled>
                                </div>
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-lg-offset-3 col-lg-9 col-md-2">
                                <div class="form-group">

                                    <label for="observaciones" class="col-md-12">Observaciones</label>
                                </div>
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-lg-offset-3 col-lg-9 col-md-2">
                                <div class="form-group">

                                    <textarea id="observaciones" name="observaciones" class="form-control" rows="3" disabled>@if (old('observaciones')){{ old('observaciones') }}@else{{ $unidad->observaciones }}@endif</textarea>
                                </div>
                            </div>

                        </div>



                        <div class="row" style="margin-top: 10px;">
                            <div class="form-group">

                                <a href='{{ route('unidads.index') }}' class="btn btn-warning">Volver</a>
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



@endsection
