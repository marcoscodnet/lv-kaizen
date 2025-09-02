@extends('layouts.app')
@section('headSection')



@endsection

@section('content')
    <div class="card mb-3">
        <div class="card-header">
            <div class="row flex-between-end">
                <div class="col-auto align-self-center">
                    <h5 class="mb-0" data-anchor="data-anchor"><i class="fa fa-truck" aria-hidden="true"></i><span class="ms-2">Ver pedido</span></h5>
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

                            <div class="col-lg-offset-3 col-lg-3 col-md-2">
                                <div class="form-group">
                                    <label for="pieza_id">Pieza</label>
                                    <select name="pieza_id" id="pieza_id" class="form-control js-example-basic-single" required disabled>

                                        @foreach($piezas as $piezaId => $pieza)
                                            <option value="{{ $piezaId }}" {{ old('pieza_id', $pedido->pieza_id) == $piezaId ? 'selected' : '' }}>{{ $pieza }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            {{-- Nueva pieza --}}
                            <div class="col-lg-3 col-md-2">
                                <div class="form-group">
                                    <label for="nombre">
                                        Nueva pieza
                                        <input type="checkbox" id="nueva_pieza_check" name="usar_nueva_pieza"
                                               style="margin-left: 5px;"
                                            {{ old('nombre', $pedido->nombre) ? 'checked' : '' }} disabled>
                                    </label>
                                    <input type="text" class="form-control" id="nombre" name="nombre"
                                           placeholder="Ingrese nueva pieza"
                                           value="{{ old('nombre', $pedido->nombre) }}"
                                        {{ old('nombre', $pedido->nombre) ? '' : 'disabled' }} disabled>
                                </div>
                            </div>
                            <div class="col-lg-offset-3 col-lg-3 col-md-2">
                                <div class="form-group">
                                    <label for="fecha">Fecha</label>
                                    <input type="date" class="form-control" id="fecha" name="fecha"  value="@if (old('fecha')){{ old('fecha') }}@else{{ ($pedido->fecha)?date('Y-m-d', strtotime($pedido->fecha)):'' }}@endif" required disabled>
                                </div>
                            </div>


                        </div>

                        <div class="row">
                            {{-- Cantidad --}}
                            <div class="col-lg-offset-3 col-lg-2 col-md-3">
                                <div class="form-group">
                                    <label for="cantidad">Cantidad</label>
                                    <input type="number" class="form-control" id="cantidad" name="cantidad" placeholder="Cantidad"
                                           value="{{ old('cantidad', $pedido->cantidad) }}" required disabled>
                                </div>
                            </div>

                            {{-- Estado --}}
                            <div class="col-lg-offset-3 col-lg-2 col-md-3">
                                <div class="form-group">
                                    <label for="estado">Estado</label>
                                    <select name="estado" id="estado" class="form-control" required disabled>
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
                            <div class="col-lg-offset-3 col-lg-2 col-md-3">
                                <div class="form-group">
                                    <label for="minimo">$ Mínimo</label>
                                    <input type="number" step="0.01" class="form-control" id="minimo" name="minimo" placeholder="$ Mínimo"
                                           value="{{ old('minimo', $pedido->minimo) }}" disabled>
                                </div>
                            </div>

                            {{-- Seña --}}
                            <div class="col-lg-offset-3 col-lg-2 col-md-3">
                                <div class="form-group">
                                    <label for="senia">Seña</label>
                                    <input type="number" step="0.01" class="form-control" id="senia" name="senia" placeholder="Seña"
                                           value="{{ old('senia', $pedido->senia) }}" disabled>
                                </div>
                            </div>
                        </div>

                        {{-- Observaciones --}}
                        <div class="row">
                            <div class="col-lg-offset-3 col-lg-9 col-md-2">
                                <div class="form-group">
                                    <label for="observacion" class="col-md-12">Observaciones</label>
                                    <textarea id="observacion" name="observacion" class="form-control" rows="3" disabled>{{ old('observacion', $pedido->observacion) }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row" style="margin-top: 10px;">
                            <div class="form-group">

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



@endsection
