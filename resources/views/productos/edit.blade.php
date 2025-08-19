@extends('layouts.app')
@section('headSection')

    <link rel="stylesheet" href="{{ asset('bower_components/select2/dist/css/select2.min.css') }}">

@endsection

@section('content')
    <div class="card mb-3">
        <div class="card-header">
            <div class="row flex-between-end">
                <div class="col-auto align-self-center">
                    <h5 class="mb-0" data-anchor="data-anchor"><i class="fa fa-box" aria-hidden="true"></i><span class="ms-2">Editar producto</span></h5>
                </div>
                <div class="col-auto ms-auto">

                </div>
            </div>
        </div>
        <div class="card-body bg-body-tertiary">
            <form role="form" action="{{ route('productos.update',$producto->id) }}" method="post" >
                {{ csrf_field() }}
                {{ method_field('PUT') }}
                <div class="tab-content">
                    <div class="box-body">

                        @include('includes.messages')
                        <div class="row">
                            <div class="col-lg-offset-3 col-lg-6 col-md-2">
                                <div class="form-group">
                                    <label for="tipo_unidad_id">Tipo de Unidad</label>
                                    <select name="tipo_unidad_id" class="form-control js-example-basic-single" required>

                                        @foreach($tipoUnidads as $tipoUnidadId => $tipoUnidad)
                                            <option value="{{ $tipoUnidadId }}" {{ old('tipo_unidad_id', $producto->tipo_unidad_id) == $tipoUnidadId ? 'selected' : '' }}>{{ $tipoUnidad }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-offset-3 col-lg-6 col-md-2">
                                <div class="form-group">
                                    <label for="marca_id">Marca</label>
                                    <select name="marca_id" class="form-control js-example-basic-single" required>

                                        @foreach($marcas as $marcaId => $marca)
                                            <option value="{{ $marcaId }}" {{ old('marca_id', $producto->marca_id) == $marcaId ? 'selected' : '' }}>{{ $marca }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-offset-3 col-lg-6 col-md-2">
                                <div class="form-group">
                                    <label for="modelo_id">Modelo</label>
                                    <select name="modelo_id" class="form-control js-example-basic-single" required>

                                        @foreach($modelos as $modeloId => $modelo)
                                            <option value="{{ $modeloId }}" {{ old('modelo_id', $producto->modelo_id) == $modeloId ? 'selected' : '' }}>{{ $modelo }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-offset-3 col-lg-6 col-md-2">
                                <div class="form-group">
                                    <label for="color_id">Color</label>
                                    <select name="color_id" class="form-control js-example-basic-single" required>

                                        @foreach($colors as $colorId => $color)
                                            <option value="{{ $colorId }}" {{ old('color_id', $producto->color_id) == $colorId ? 'selected' : '' }}>{{ $color }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>


                        </div>

                        <div class="row">
                            <div class="col-lg-offset-3 col-lg-3 col-md-3">
                                <div class="form-group">
                                    <label for="precio">$ sugerido</label>
                                    <input type="number" step="0.01" class="form-control" id="precio" name="precio" placeholder="$ sugerido" value="@if (old('precio')){{ old('precio') }}@else{{ $producto->precio }}@endif">
                                </div>
                            </div>
                            <div class="col-lg-offset-3 col-lg-3 col-md-3">
                                <div class="form-group">
                                    <label for="minimo">Stock mínimo</label>
                                    <input type="number" class="form-control" id="minimo" name="minimo" placeholder="Stock mínimo" value="@if (old('minimo')){{ old('minimo') }}@else{{ $producto->minimo }}@endif">
                                </div>
                            </div>
                            <div class="col-lg-offset-3 col-lg-6 col-md-1">
                                <div class="form-check mt-4">
                                    <input type="hidden" name="discontinuo" value="0">
                                    <input class="form-check-input" type="checkbox" id="discontinuo" name="discontinuo" value="1"
                                        {{ old('discontinuo', $producto->discontinuo ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="discontinuo">
                                        Discontinuo
                                    </label>
                                </div>

                            </div>
                        </div>
                        <div class="row" style="margin-top: 10px;">
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">Guardar</button>
                                <a href='{{ route('productos.index') }}' class="btn btn-warning">Volver</a>
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

    <!-- Select2 -->
    <script src="{{ asset('bower_components/select2/dist/js/select2.min.js') }}"></script>

    <!-- Inputmask -->
    <script src="{{ asset('bower_components/inputmask/dist/min/jquery.inputmask.bundle.min.js') }}"></script>


    <script src="{{ asset('assets/js/confirm-exit.js') }}"></script>


    <!-- page script -->
    <script>
        $(document).ready(function () {
            $('#cuil').inputmask('99-99999999-9', { placeholder: 'XX-XXXXXXXX-X' });
            $('.js-example-basic-single').select2();



        });

    </script>


@endsection
