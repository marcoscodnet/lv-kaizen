@extends('layouts.app')
@section('headSection')

    <link rel="stylesheet" href="{{ asset('bower_components/select2/dist/css/select2.min.css') }}">

@endsection

@section('content')
    <div class="card mb-3">
        <div class="card-header">
            <div class="row flex-between-end">
                <div class="col-auto align-self-center">
                    <h5 class="mb-0" data-anchor="data-anchor"><i class="fa fa-motorcycle" aria-hidden="true"></i><span class="ms-2">Crear unidad</span></h5>
                </div>
                <div class="col-auto ms-auto">

                </div>
            </div>
        </div>
        <div class="card-body bg-body-tertiary">
            <form role="form" action="{{ route('unidads.store') }}" method="post" >
                {{ csrf_field() }}
                <div class="tab-content">
                    <div class="box-body">

                        @include('includes.messages')
                        <div class="row">
                            <div class="col-lg-offset-3 col-lg-6 col-md-2">
                                <div class="form-group">
                                    <label for="producto_id">Producto</label>
                                    <select name="producto_id" class="form-control js-example-basic-single" required>

                                        @foreach($productos as $productoId => $producto)
                                            <option value="{{ $productoId }}" {{ old('producto_id') == $productoId ? 'selected' : '' }}>{{ $producto }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-offset-3 col-lg-4 col-md-2">
                                <div class="form-group">
                                    <label for="sucursal_id">Sucursal</label>
                                    <select name="sucursal_id" class="form-control js-example-basic-single" required>

                                        @foreach($sucursals as $sucursalId => $sucursal)
                                            <option value="{{ $sucursalId }}" {{ old('sucursal_id') == $sucursalId ? 'selected' : '' }}>{{ $sucursal }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>


                        <div class="row">
                            <div class="col-lg-offset-3 col-lg-3 col-md-3">
                                <div class="form-group">
                                    <label for="motor">Nro. motor</label>
                                    <input type="text" class="form-control" id="motor" name="motor" placeholder="Nro. motor" value="{{ old('motor') }}" required>
                                </div>
                            </div>
                            <div class="col-lg-offset-3 col-lg-3 col-md-3">
                                <div class="form-group">
                                    <label for="cuadro">Nro. cuadro</label>
                                    <input type="text" class="form-control" id="cuadro" name="cuadro" placeholder="Nro. cuadro" value="{{ old('cuadro') }}" required>
                                </div>
                            </div>
                            <div class="col-lg-offset-3 col-lg-3 col-md-3">
                                <div class="form-group">
                                    <label for="patente">Patente</label>
                                    <input type="text" class="form-control" id="patente" name="patente" placeholder="Patente" value="{{ old('patente') }}">
                                </div>
                            </div>


                        </div>

                        <div class="row">
                            <div class="col-lg-offset-3 col-lg-3 col-md-3">
                                <div class="form-group">
                                    <label for="ingreso">Ingreso</label>
                                    <input type="date" class="form-control" id="ingreso" name="ingreso"  value="{{ old('ingreso') }}">
                                </div>
                            </div>
                            <div class="col-lg-offset-3 col-lg-2 col-md-3">
                                <div class="form-group">
                                    <label for="remito">Nro. remito ingreso</label>
                                    <input type="text" class="form-control" id="remito" name="remito" placeholder="Nro. remito ingreso" value="{{ old('remito') }}">
                                </div>
                            </div>
                            <div class="col-lg-offset-3 col-lg-2 col-md-3">
                                <div class="form-group">
                                    <label for="year">Año/Modelo</label>
                                    <input type="text" class="form-control" id="year" name="year" placeholder="Año/Modelo" value="{{ old('year') }}">
                                </div>
                            </div>
                            <div class="col-lg-offset-3 col-lg-2 col-md-3">
                                <div class="form-group">
                                    <label for="envio">Nro. Envío</label>
                                    <input type="text" class="form-control" id="envio" name="envio" placeholder="Nro. Envío" value="{{ old('envio') }}">
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


                                    <!-- Fila 2: Área de texto -->

                                    <textarea id="observaciones" name="observaciones" class="form-control" rows="3">
                                        @if (old('observaciones')){{ old('observaciones') }}@endif
                                    </textarea>

                                </div>
                            </div>

                        </div>

                        <div class="row" style="margin-top: 10px;">
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">Guardar</button>
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

            $('.js-example-basic-single').select2();




        });

    </script>


@endsection
