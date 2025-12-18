@extends('layouts.app')
@section('headSection')



@endsection

@section('content')
    <div class="card mb-3">
        <div class="card-header">
            <div class="row flex-between-end">
                <div class="col-auto align-self-center">
                    <h5 class="mb-0" data-anchor="data-anchor"><i class="fa fa-exchange-alt" aria-hidden="true"></i><span class="ms-2">Ver movimiento de piezas</span></h5>
                </div>
                <div class="col-auto ms-auto">

                </div>
            </div>
        </div>
        <div class="card-body bg-body-tertiary">
            <form role="form" action="{{ route('movimientoPiezas.store') }}" method="post" >
                {{ csrf_field() }}
                <div class="tab-content">
                    <div class="box-body">

                        @include('includes.messages')
                        <div class="row">
                            <div class="col-12 col-lg-3">
                                <div class="form-group">
                                    <label for="user_id">Vendedor</label>
                                    <select name="user_id" class="form-control js-example-basic-single" required disabled>

                                        @foreach($users as $userId => $user)
                                            <option value="{{ $userId }}"
                                                {{ old('user_id', $movimiento->user_id) == $userId ? 'selected' : '' }}>
                                                {{ $user }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-lg-3">
                                <div class="form-group">
                                    <label for="fecha">Fecha</label>
                                    <input type="date" class="form-control" id="fecha" name="fecha"  value="@if (old('fecha')){{ old('fecha') }}@else{{ ($movimiento->fecha)?date('Y-m-d', strtotime($movimiento->fecha)):'' }}@endif" required disabled>
                                </div>
                            </div>
                        </div>
                        <div class="row">

                            <div class="col-12 col-lg-3">
                                <div class="form-group">
                                    <label for="sucursal_origen_id">Origen</label>
                                    <select name="sucursal_origen_id" class="form-control js-example-basic-single" required disabled>

                                        @foreach($origens as $sucursalId => $sucursal)
                                            <option value="{{ $sucursalId }}" {{ old('sucursal_origen_id', $movimiento->sucursal_origen_id) == $sucursalId ? 'selected' : '' }}>{{ $sucursal }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-lg-3">
                                <div class="form-group">
                                    <label for="sucursal_destino_id">Destino</label>
                                    <select name="sucursal_destino_id" class="form-control js-example-basic-single" required disabled>

                                        @foreach($destinos as $sucursalId => $sucursal)
                                            <option value="{{ $sucursalId }}" {{ old('sucursal_destino_id', $movimiento->sucursal_destino_id) == $sucursalId ? 'selected' : '' }}>{{ $sucursal }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                        </div>

                        <div class="form-group">


                            <table class="table">
                                <thead>

                                <th>Código</th>
                                <th>Pieza</th>
                                <th>Cantidad</th>




                                </thead>

                                <tbody id="cuerpoProducto">



                                @foreach($movimiento->piezaMovimientos as $piezaMovimiento)

                                    <tr>
                                        <td>
                                            {{$piezaMovimiento->pieza->codigo}}
                                        </td>
                                        <td>
                                            {{$piezaMovimiento->pieza->descripcion}}
                                        </td>
                                        <td>
                                            {{$piezaMovimiento->cantidad }}
                                        </td>

                                    </tr>
                                @endforeach
                                </tbody>




                            </table>
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

                                        <textarea id="observaciones" name="observaciones" class="form-control" rows="3" disabled>
                                            @if (old('observaciones')){{ old('observaciones') }}@else{{ $movimiento->observaciones }}@endif
                                        </textarea>

                                </div>
                            </div>

                        </div>

                        <div class="row" style="margin-top: 10px;">
                            <div class="form-group">

                                <a href='{{ route('movimientoPiezas.index') }}' class="btn btn-warning">Volver</a>
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
