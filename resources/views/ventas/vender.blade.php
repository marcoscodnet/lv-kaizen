@extends('layouts.app')
@section('headSection')

    <link rel="stylesheet" href="{{ asset('bower_components/select2/dist/css/select2.min.css') }}">

@endsection

@section('content')
    <div class="card mb-3">
        <div class="card-header">
            <div class="row flex-between-end">
                <div class="col-auto align-self-center">
                    <h5 class="mb-0" data-anchor="data-anchor"><i class="fa fa-shopping-cart" aria-hidden="true"></i><span class="ms-2">Vender unidad</span></h5>
                </div>
                <div class="col-auto ms-auto">

                </div>
            </div>
        </div>
        <div class="card-body bg-body-tertiary">
            <form role="form" action="{{ route('ventas.store') }}" method="post" >
                {{ csrf_field() }}
                <div class="tab-content">
                    <div class="box-body">

                        @include('includes.messages')
                        <div class="row">

                            <div class="col-lg-offset-3 col-lg-5 col-md-6">
                                <div class="form-group">
                                    <label for="producto">Producto</label>
                                    <input type="hidden" id="unidad_id" name="unidad_id" value="{{ $unidad->id }}" >
                                    <input type="text" class="form-control" id="producto" name="producto"  value="{{ isset($unidad->producto) ? $unidad->producto->tipounidad->nombre : '' }} {{ isset($unidad->producto) ? $unidad->producto->marca->nombre : '' }} {{ isset($unidad->producto) ? $unidad->producto->modelo->nombre : '' }} {{ isset($unidad->producto) ? $unidad->producto->color->nombre : '' }}" readonly>
                                </div>
                            </div>

                            <div class="col-lg-offset-3 col-lg-3 col-md-3">
                                <div class="form-group">
                                    <label for="motor">Motor</label>
                                    <input type="text" class="form-control" id="motor" name="motor"  value="{{ $unidad->motor }}" readonly>
                                </div>
                            </div>

                            <div class="col-lg-offset-3 col-lg-3 col-md-3">
                                <div class="form-group">
                                    <label for="cuadro">Cuadro</label>
                                    <input type="text" class="form-control" id="cuadro" name="cuadro"  value="{{ $unidad->cuadro }}" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-offset-3 col-lg-3 col-md-3">
                                <div class="form-group">
                                    <label for="fecha">Fecha</label>
                                    <input type="date" class="form-control" id="fecha" name="fecha"  value="{{ now()->format('Y-m-d') }}" readonly required>
                                </div>
                            </div>
                            <div class="col-lg-offset-3 col-lg-3 col-md-2">
                                <div class="form-group">
                                    <label for="user_id">Vendedor</label>
                                    <select name="user_id" id="user_id"  class="form-control js-example-basic-single" required>

                                        @foreach($users as $userId => $user)
                                            <option value="{{ $userId }}"
                                                {{ old('user_id', auth()->id()) == $userId ? 'selected' : '' }}>
                                                {{ $user }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-offset-3 col-lg-3 col-md-2">
                                <div class="form-group">
                                    <label for="sucursal_id">Sucrusal</label>
                                    <select id="sucursal_id" name="sucursal_id" class="form-control js-example-basic-single" required>
                                        <option value="">Seleccione...</option>
                                        @foreach($sucursals as $sucursalId => $sucursal)
                                            <option value="{{ $sucursalId }}" {{ old('sucursal_id') == $sucursalId ? 'selected' : '' }}>
                                                {{ $sucursal }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                        </div>



                        <div class="row">
                            <div class="col-lg-offset-3 col-lg-6 col-md-2">
                                <div class="form-group">
                                    <label for="cliente_id">Cliente</label>
                                    <select name="cliente_id" id="cliente_id"class="form-control js-example-basic-single" required>

                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-offset-3 col-lg-3 col-md-3">
                                <div class="form-group">
                                    <label for="precio">Importe sugerido</label>
                                    <input type="text" class="form-control" id="precio" name="precio"  value="{{ isset($unidad->producto) ? $unidad->producto->precio : '' }}" readonly>
                                </div>
                            </div>

                        </div>




                        <div class="row" style="margin-top: 10px;">
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">Guardar</button>
                                <a href='{{ route('ventas.unidads') }}' class="btn btn-warning">Volver</a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- /.content-wrapper -->
@endsection
<style>

</style>
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
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        $(document).ready(function () {
            $('.js-example-basic-single').select2();
            $('#cliente_id').select2({

                    minimumInputLength: 3,
                    ajax: {
                        url: '{{ route("cliente.search") }}',
                        type: "get",
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return {
                                _token: CSRF_TOKEN,
                                search: params.term // search term
                            };
                        },
                        processResults: function (response) {
                            return {
                                results: response
                            };
                        },
                        cache: true
                    }

                }
            )
        });
    </script>



@endsection
