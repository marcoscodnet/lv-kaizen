@extends('layouts.app')
@section('headSection')

    <link rel="stylesheet" href="{{ asset('bower_components/select2/dist/css/select2.min.css') }}">

@endsection

@section('content')
    <div class="card mb-3">
        <div class="card-header">
            <div class="row flex-between-end">
                <div class="col-auto align-self-center">
                    <h5 class="mb-0" data-anchor="data-anchor"><i class="fa fa-toolbox" aria-hidden="true"></i><span class="ms-2">Crear stock pieza</span></h5>
                </div>
                <div class="col-auto ms-auto">

                </div>
            </div>
        </div>
        <div class="card-body bg-body-tertiary">
            <form role="form" action="{{ route('stockPiezas.store') }}" method="post" >
                {{ csrf_field() }}
                <div class="tab-content">
                    <div class="box-body">

                        @include('includes.messages')
                        <div class="row">
                            <div class="col-lg-offset-3 col-lg-9 col-md-2">
                                <div class="form-group">
                                    <label for="pieza_id">Pieza</label>
                                    <select name="pieza_id" id="pieza_id" class="form-control js-example-basic-single" required>

                                        @foreach($piezas as $piezaId => $pieza)
                                            <option value="{{ $piezaId }}" {{ old('pieza_id') == $piezaId ? 'selected' : '' }}>{{ $pieza }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                        </div>


                        <div class="row">
                            <div class="col-lg-offset-3 col-lg-3 col-md-3">
                                <div class="form-group">
                                    <label for="cantidad">Cantidad</label>
                                    <input type="number" class="form-control" id="cantidad" name="cantidad" placeholder="Cantidad" value="{{ old('cantidad') }}">
                                </div>
                            </div>

                            <div class="col-lg-offset-3 col-lg-3 col-md-3">
                                <div class="form-group">
                                    <label for="costo">Costo</label>
                                    <input type="number" step="0.01" class="form-control" id="costo" name="costo" placeholder="Costo" value="{{ old('costo') }}">
                                </div>
                            </div>
                            <div class="col-lg-offset-3 col-lg-3 col-md-3">
                                <div class="form-group">
                                    <label for="precio_minimo">$ mínimo</label>
                                    <input type="number" step="0.01" class="form-control" id="precio_minimo" name="precio_minimo" placeholder="$ mínimo" value="{{ old('precio_minimo') }}">
                                </div>
                            </div>


                        </div>

                        <div class="row">

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
                            <div class="col-lg-offset-3 col-lg-4 col-md-2">
                                <div class="form-group">
                                    <label for="proveedor">Proveedor</label>
                                    <select name="proveedor" id="proveedor" class="form-control" required>
                                        <option value="">
                                            Seleccionar...
                                        </option>
                                        @foreach (config('proveedores') as $key => $label)
                                            <option value="{{ $key }}" {{ old('proveedor', $stockPieza->proveedor ?? '') == $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        @php
                            $hoy = date('Y-m-d');
                            $hoy_formateado = date('d/m/Y');
                        @endphp
                        <div class="row">

                            <div class="col-lg-offset-3 col-lg-4 col-md-2">
                                <div class="form-group">
                                    <label for="ingreso_visible">F. Ingreso</label>
                                    <input type="text" class="form-control" id="ingreso_visible" value="{{ $hoy_formateado }}" readonly>
                                    <input type="hidden" name="ingreso" value="{{ $hoy }}">
                                </div>
                            </div>
                            <div class="col-lg-offset-3 col-lg-4 col-md-2">
                                <div class="form-group">
                                    <div class="form-group">
                                        <label for="remito">Remito</label>
                                        <input type="text" class="form-control" id="remito" name="remito" placeholder="Remito" value="{{ old('remito') }}" >
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

    <script src="{{ asset('assets/js/confirm-exit.js') }}"></script>
    <!-- page script -->
    <script>
        const piezaUrlTemplate = @json(route('api.piezas.getDatos', ['id' => 'PIEZA_ID']));
        $(document).ready(function () {

            $('.js-example-basic-single').select2();
            $('#pieza_id').on('change', function () {
                var piezaId = $(this).val();

                if (piezaId) {
                    let url = piezaUrlTemplate.replace('PIEZA_ID', piezaId);

                        $.ajax({
                        url: url,
                        type: 'GET',
                        success: function (data) {
                            $('#costo').val(data.costo);
                            $('#precio_minimo').val(data.precio_minimo);
                        },
                        error: function () {
                            $('#costo').val('');
                            $('#precio_minimo').val('');
                        }
                    });
                }
            });
        });

    </script>

@endsection
