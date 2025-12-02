@extends('layouts.app')
@section('headSection')

    <link rel="stylesheet" href="{{ asset('bower_components/select2/dist/css/select2.min.css') }}">

@endsection

@section('content')
    <div class="card mb-3">
        <div class="card-header">
            <div class="row flex-between-end">
                <div class="col-auto align-self-center">
                    <h5 class="mb-0">
                        <i class="fa fa-cogs"></i>
                        <span class="ms-2">Ver pieza</span>
                    </h5>
                </div>
            </div>
        </div>

        <div class="card-body bg-body-tertiary">

            {{-- ❌ SIN FORMULARIO, SOLO LECTURA --}}
            <div class="box-body">

                @include('includes.messages')

                {{-- Datos generales --}}
                <div class="row">
                    <div class="col-lg-2">
                        <label>Tipo</label>
                        <select class="form-control" disabled>
                            @foreach($tipos as $tipoId => $tipo)
                                <option value="{{ $tipoId }}" {{ $pieza->tipo_pieza_id == $tipoId ? 'selected' : '' }}>
                                    {{ $tipo }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-lg-3">
                        <label>Código</label>
                        <input disabled class="form-control" value="{{ $pieza->codigo }}">
                    </div>

                    <div class="col-12 col-lg-5">
                        <label>Descripción</label>
                        <input disabled class="form-control" value="{{ $pieza->descripcion }}">
                    </div>
                </div>

                {{-- Foto --}}
                <div class="row mt-3">
                    <div class="col-12 col-lg-6">
                        <label>Foto de la pieza</label>
                        <div>
                            @if($pieza->foto)
                                <img src="{{ asset('images/'.$pieza->foto) }}"
                                     style="max-width:320px;border:1px solid #ccc;">
                            @else
                                <p>No hay foto disponible</p>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Observaciones --}}
                <div class="row mt-3">
                    <div class="col-12 col-lg-9">
                        <label>Observaciones</label>
                        <textarea disabled class="form-control" rows="3">
                            {{ $pieza->observaciones }}
                        </textarea>
                    </div>
                </div>

                {{-- UBICACIONES DE LA PIEZA --}}
                <div class="card mt-4">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">Ubicaciones</h5>
                    </div>
                    <div class="card-body">

                        @if($pieza->ubicacions->isEmpty())
                            <p>No hay ubicaciones asociadas.</p>
                        @else
                            <table class="table">
                                <thead>
                                <tr>
                                    <th>Sucursal</th>
                                    <th>Ubicación</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($pieza->ubicacions as $ubi)
                                    <tr>
                                        <td>{{ $ubi->sucursal->nombre }}</td>
                                        <td>{{ $ubi->nombre }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        @endif

                    </div>
                </div>

                {{-- STOCK --}}
                <div class="card mt-4">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">Stock</h5>
                    </div>
                    <div class="card-body">

                        <div class="row">
                            <div class="col-12 col-lg-3">
                                <label>Costo</label>
                                <input disabled class="form-control" value="{{ $pieza->costo }}">
                            </div>

                            <div class="col-12 col-lg-3">
                                <label>$ mínimo</label>
                                <input disabled class="form-control" value="{{ $pieza->precio_minimo }}">
                            </div>

                            <div class="col-12 col-lg-2">
                                <label>Stock mínimo</label>
                                <input disabled class="form-control" value="{{ $pieza->sctock_minimo }}">
                            </div>

                            <div class="col-12 col-lg-2">
                                <label>Stock actual</label>
                                <input disabled class="form-control" value="{{ $pieza->stock_actual }}">
                            </div>
                        </div>

                        <table class="table mt-3">
                            <thead>
                            <tr>
                                <th>Sucursal</th>
                                <th>Stock</th>
                            </tr>
                            </thead>

                            <tbody>
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

                {{-- Botón volver --}}
                <div class="row mt-3">
                    <div class="form-group">
                        <a href="{{ route('piezas.index') }}" class="btn btn-warning">Volver</a>
                    </div>
                </div>

            </div>

        </div>
    </div>
@endsection

@section('footerSection')

@endsection
