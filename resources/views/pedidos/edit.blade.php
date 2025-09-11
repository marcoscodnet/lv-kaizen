@extends('layouts.app')
@section('headSection')

    <link rel="stylesheet" href="{{ asset('bower_components/select2/dist/css/select2.min.css') }}">

@endsection

@section('content')
    <div class="card mb-3">
        <div class="card-header">
            <div class="row flex-between-end">
                <div class="col-auto align-self-center">
                    <h5 class="mb-0" data-anchor="data-anchor"><i class="fa fa-truck" aria-hidden="true"></i><span class="ms-2">Editar pedido</span></h5>
                </div>
                <div class="col-auto ms-auto">

                </div>
            </div>
        </div>
        <div class="card-body bg-body-tertiary">
            <form role="form" action="{{ route('pedidos.update',$pedido->id) }}" method="post" >
                {{ csrf_field() }}
                {{ method_field('PUT') }}
                <div class="tab-content">
                    <div class="box-body">

                        @include('includes.messages')
                        <div class="row">

                            <div class="col-12 col-lg-3">
                                <div class="form-group">
                                    <label for="pieza_id">Pieza</label>
                                    <select name="pieza_id" id="pieza_id" class="form-control js-example-basic-single" required>

                                        @foreach($piezas as $piezaId => $pieza)
                                            <option value="{{ $piezaId }}" {{ old('pieza_id', $pedido->pieza_id) == $piezaId ? 'selected' : '' }}>{{ $pieza }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            {{-- Nueva pieza --}}
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label for="nombre">
                                        Nueva pieza
                                        <input type="checkbox" id="nueva_pieza_check" name="usar_nueva_pieza"
                                               style="margin-left: 5px;"
                                            {{ old('nombre', $pedido->nombre) ? 'checked' : '' }}>
                                    </label>
                                    <input type="text" class="form-control" id="nombre" name="nombre"
                                           placeholder="Ingrese nueva pieza"
                                           value="{{ old('nombre', $pedido->nombre) }}"
                                        {{ old('nombre', $pedido->nombre) ? '' : 'disabled' }}>
                                </div>
                            </div>
                            <div class="col-12 col-lg-3">
                                <div class="form-group">
                                    <label for="fecha">Fecha</label>
                                    <input type="date" class="form-control" id="fecha" name="fecha"  value="@if (old('fecha')){{ old('fecha') }}@else{{ ($pedido->fecha)?date('Y-m-d', strtotime($pedido->fecha)):'' }}@endif" required>
                                </div>
                            </div>


                        </div>

                        <div class="row">
                            {{-- Cantidad --}}
                            <div class="col-12 col-lg-2">
                                <div class="form-group">
                                    <label for="cantidad">Cantidad</label>
                                    <input type="number" class="form-control" id="cantidad" name="cantidad" placeholder="Cantidad"
                                           value="{{ old('cantidad', $pedido->cantidad) }}" required>
                                </div>
                            </div>

                            {{-- Estado --}}
                            <div class="col-12 col-lg-2">
                                <div class="form-group">
                                    <label for="estado">Estado</label>
                                    <select name="estado" id="estado" class="form-control" required>
                                        <option value="">Seleccionar...</option>
                                        @foreach (config('estados') as $key => $label)
                                            <option value="{{ $key }}" {{ old('estado', $pedido->estado) == $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Mínimo --}}
                            <div class="col-12 col-lg-2">
                                <div class="form-group">
                                    <label for="minimo">$ Mínimo</label>
                                    <input type="number" step="0.01" class="form-control" id="minimo" name="minimo" placeholder="$ Mínimo"
                                           value="{{ old('minimo', $pedido->minimo) }}">
                                </div>
                            </div>

                            {{-- Seña --}}
                            <div class="col-12 col-lg-2">
                                <div class="form-group">
                                    <label for="senia">Seña</label>
                                    <input type="number" step="0.01" class="form-control" id="senia" name="senia" placeholder="Seña"
                                           value="{{ old('senia', $pedido->senia) }}">
                                </div>
                            </div>
                        </div>

                        {{-- Observaciones --}}
                        <div class="row">
                            <div class="col-12 col-lg-9">
                                <div class="form-group">
                                    <label for="observacion" class="col-md-12">Observaciones</label>
                                    <textarea id="observacion" name="observacion" class="form-control" rows="3">{{ old('observacion', $pedido->observacion) }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row" style="margin-top: 10px;">
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">Guardar</button>
                                <a href='{{ route('pedidos.index') }}' class="btn btn-warning">Volver</a>
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
    <script src="{{ asset('bower_components/select2/dist/js/i18n/es.js') }}"></script>
    <!-- Inputmask -->
    <script src="{{ asset('bower_components/inputmask/dist/min/jquery.inputmask.bundle.min.js') }}"></script>


    <script src="{{ asset('assets/js/confirm-exit.js') }}"></script>


    <!-- page script -->
    <script>
        $(document).ready(function () {
            $('.js-example-basic-single').select2({
                language: 'es'}
            );
            $('#nueva_pieza_check').on('change', function () {
                if ($(this).is(':checked')) {
                    $('#nombre').prop('disabled', false);

                    // deshabilitar el select2 y limpiar selección
                    $('#pieza_id').prop('disabled', true).val(null).trigger('change');

                } else {
                    $('#nombre').prop('disabled', true);

                    // habilitar el select2
                    $('#pieza_id').prop('disabled', false).trigger('change');
                }
            });
        });

    </script>


@endsection
