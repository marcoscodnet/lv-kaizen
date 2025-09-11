@extends('layouts.app')
@section('headSection')

    <link rel="stylesheet" href="{{ asset('bower_components/select2/dist/css/select2.min.css') }}">

@endsection

@section('content')
    <div class="card mb-3">
        <div class="card-header">
            <div class="row flex-between-end">
                <div class="col-auto align-self-center">
                    <h5 class="mb-0" data-anchor="data-anchor"><i class="fa fa-cogs" aria-hidden="true"></i><span class="ms-2">Ver pieza</span></h5>
                </div>
                <div class="col-auto ms-auto">

                </div>
            </div>
        </div>
        <div class="card-body bg-body-tertiary">
            <form role="form" action="{{ route('piezas.update',$pieza->id) }}" method="post" >
                {{ csrf_field() }}
                {{ method_field('PUT') }}
                <div class="tab-content">
                    <div class="box-body">

                        @include('includes.messages')


                        <div class="row">
                            <div class="col-lg-2">
                                <label for="tipo_pieza_id">Tipo</label>
                                <select id="tipo_pieza_id" name="tipo_pieza_id" class="form-control js-example-basic-single" disabled>
                                    <option value="">Seleccione...</option>
                                    @foreach($tipos as $tipoId => $tipo)
                                        <option value="{{ $tipoId }}" {{ old('tipo_pieza_id',$pieza->tipo_pieza_id) == $tipoId ? 'selected' : '' }}>
                                            {{ $tipo }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-lg-3">
                                <div class="form-group">
                                    <label for="codigo">Código</label>
                                    <input type="text" class="form-control" id="codigo" name="codigo" placeholder="Codigo" value="@if (old('codigo')){{ old('codigo') }}@else{{ $pieza->codigo }}@endif" disabled>
                                </div>
                            </div>
                            <div class="col-12 col-lg-5">
                                <div class="form-group">
                                    <label for="descripcion">Descripción</label>
                                    <input type="text" class="form-control" id="descripcion" name="descripcion" placeholder="Descripcion" value="@if (old('descripcion')){{ old('descripcion') }}@else{{ $pieza->descripcion }}@endif" disabled>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12 col-lg-6">
                                <div class="form-group">
                                    <label>Foto de la pieza</label>
                                    <div>
                                        @if($pieza->foto)
                                            <img src="{{ asset('images/'.$pieza->foto) }}"
                                                 alt="Foto de la pieza"
                                                 style="max-width:320px; border:1px solid #ccc;">
                                        @else
                                            <p>No hay foto disponible</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 col-lg-9">
                                <div class="form-group">

                                    <label for="observaciones" class="col-md-12">Observaciones</label>
                                </div>
                            </div>

                        </div>

                        <div class="row">
                            <div class="col-12 col-lg-9">
                                <div class="form-group">


                                    <!-- Fila 2: Área de texto -->

                                    <textarea id="observaciones" name="observaciones" class="form-control" rows="3" disabled>@if (old('observaciones')){{ old('observaciones') }}@else{{ $pieza->observaciones }}@endif</textarea>

                                </div>
                            </div>

                        </div>
                        <div class="card mt-4">
                            <div class="card-header bg-secondary text-white">
                                <h5 class="mb-0">Stock</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12 col-lg-3">
                                        <div class="form-group">
                                            <label for="costo">Costo</label>
                                            <input type="number" step="0.01" class="form-control" id="costo" name="costo" placeholder="Costo" value="@if (old('costo')){{ old('costo') }}@else{{ $pieza->costo }}@endif" disabled>
                                        </div>
                                    </div>
                                    <div class="col-12 col-lg-3">
                                        <div class="form-group">
                                            <label for="precio_minimo">$ mínimo</label>
                                            <input type="number" step="0.01" class="form-control" id="precio_minimo" name="precio_minimo" placeholder="$ mínimo" value="@if (old('precio_minimo')){{ old('precio_minimo') }}@else{{ $pieza->precio_minimo }}@endif" disabled>
                                        </div>
                                    </div>
                                    <div class="col-12 col-lg-2">
                                        <div class="form-group">
                                            <label for="stock_minimo">Stock mínimo</label>
                                            <input type="number" class="form-control" id="stock_minimo" name="stock_minimo" placeholder="Stock mínimo" value="@if (old('sctock_minimo')){{ old('sctock_minimo') }}@else{{ $pieza->sctock_minimo }}@endif" disabled>
                                        </div>
                                    </div>


                                    <div class="col-12 col-lg-2">
                                        <div class="form-group">
                                            <label for="stock_actual">Stock actual</label>
                                            <input type="number" class="form-control" id="stock_actual" name="stock_actual" placeholder="Stock actual" value="@if (old('stock_actual')){{ old('stock_actual') }}@else{{ $pieza->stock_actual }}@endif" disabled>
                                        </div>
                                    </div>

                                </div>
                                <div class="form-group">


                                    <table class="table">
                                        <thead>

                                        <th>Sucursal</th>
                                        <th>Stock</th>




                                        </thead>

                                        <tbody id="cuerpoProducto">



                                        @foreach($stocksPorSucursal as $stock)
                                            <tr>
                                                <td>{{ $stock->sucursal->nombre }}</td>
                                                <td>{{ $stock->total_cantidad }}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>




                                    </table>
                                </div>


                            </div>
                        </div>
                        <div class="row" style="margin-top: 10px;">
                            <div class="form-group">

                                <a href='{{ route('piezas.index') }}' class="btn btn-warning">Volver</a>
                            </div>
                        </div>
                    </div>
                </div>



            </form>
        </div>
    </div>


@endsection
@section('footerSection')


@endsection
