@extends('layouts.app')
@section('headSection')

@endsection

@section('content')
    <div class="card mb-3">
        <div class="card-header">
            <div class="row flex-between-end">
                <div class="col-auto align-self-center">
                    <h5 class="mb-0" data-anchor="data-anchor">
                        <i class="fa fa-shopping-cart" aria-hidden="true"></i>
                        <span class="ms-2">Ver venta unidad</span>
                    </h5>
                </div>
            </div>
        </div>
        <div class="card-body bg-body-tertiary">
            <form id="formVenta" role="form" action="{{ route('ventas.update',$venta->id) }}" method="post" enctype="multipart/form-data">
                {{ csrf_field() }}
                {{ method_field('PUT') }}

                <div class="tab-content">
                    <div class="box-body">
                        @include('includes.messages')

                        {{-- Datos de la unidad --}}
                        <div class="row">
                            <div class="col-lg-9">
                                <div class="form-group">
                                    <label for="producto">Producto</label>

                                    <input type="text" class="form-control" id="producto" name="producto"
                                           value="{{ isset($venta->unidad->producto) ? $venta->unidad->producto->tipounidad->nombre : '' }} {{ isset($venta->unidad->producto) ? $venta->unidad->producto->marca->nombre : '' }} {{ isset($venta->unidad->producto) ? $venta->unidad->producto->modelo->nombre : '' }} {{ isset($venta->unidad->producto) ? $venta->unidad->producto->color->nombre : '' }}"
                                           readonly disabled>
                                </div>
                            </div>

                        </div>

                        <div class="row">

                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label for="motor">Motor</label>
                                    <input type="text" class="form-control" id="motor" name="motor" value="{{ $venta->unidad->motor }}" readonly disabled>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label for="cuadro">Cuadro</label>
                                    <input type="text" class="form-control" id="cuadro" name="cuadro" value="{{ $venta->unidad->cuadro }}" readonly disabled>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label for="precio">Importe sugerido</label>
                                    <input type="text" class="form-control" id="precio" name="precio"
                                           value="{{ isset($venta->unidad->producto) ? $venta->unidad->producto->precio : '' }}" readonly disabled>
                                </div>
                            </div>
                        </div>

                        {{-- Fecha, vendedor y sucursal --}}
                        <div class="row">
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label for="fecha">Fecha</label>
                                    @php
                                        $fechaValor = old('fecha')
                                            ? \Carbon\Carbon::parse(old('fecha'))->format('d/m/Y H:i:s')
                                            : ($venta->fecha ? \Carbon\Carbon::parse($venta->fecha)->format('d/m/Y H:i:s') : '');
                                    @endphp
                                    <input type="text" class="form-control" id="fecha" name="fecha"
                                           value="{{ $fechaValor }}" readonly required disabled>

                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label for="user_id">Vendedor</label>
                                    <select name="user_id" id="user_id" class="form-control js-example-basic-single" required disabled>
                                        @foreach($users as $userId => $user)
                                            <option value="{{ $userId }}" {{ old('user_id', $venta->user_id) == $userId ? 'selected' : '' }}>
                                                {{ $user }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label for="sucursal_id">Sucursal</label>
                                    <select id="sucursal_id" name="sucursal_id" class="form-control js-example-basic-single" required disabled>
                                        <option value="">Seleccione...</option>
                                        @foreach($sucursals as $sucursalId => $sucursal)
                                            <option value="{{ $sucursalId }}" {{ old('sucursal_id', $venta->sucursal_id) == $sucursalId ? 'selected' : '' }}>
                                                {{ $sucursal }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Cliente --}}
                        <div class="row">
                            <div class="col-lg-9">
                                <div class="form-group d-flex align-items-end gap-2">
                                    <div class="flex-grow-1">
                                        <label for="cliente_id">Cliente</label>
                                        <select name="cliente_id" id="cliente_id" class="form-control js-example-basic-single" required disabled>
                                            @if(old('cliente_id'))
                                                {{-- Mostrar cliente seleccionado por old() --}}
                                                <option value="{{ old('cliente_id') }}" selected>
                                                    {{ old('cliente_nombre', '') }}
                                                </option>
                                            @elseif(isset($venta) && $venta->cliente)
                                                {{-- Mostrar cliente existente en la venta --}}
                                                <option value="{{ $venta->cliente_id }}" selected>
                                                    {{ $venta->cliente->full_name_phone }}
                                                </option>
                                            @endif
                                        </select>
                                    </div>

                                </div>

                            </div>


                        </div>

                        <div class="row">
                            <div class="col-lg-3">
                                <div class="form-group d-flex align-items-end gap-2">
                                    <div class="flex-grow-1">
                                        <label for="forma">Forma de pago</label>
                                        <select name="forma" id="forma" class="form-control" required disabled>
                                        <option value="">
                                            Seleccionar...
                                        </option>
                                        @foreach (config('formas') as $key => $label)
                                            <option value="{{ $key }}" {{ old('forma', $venta->forma ?? '') == $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                        </select>
                                    </div>

                                </div>
                            </div>


                        </div>
<p></p>
                        <div class="row">
                            <div class="col-lg-12">
                                <div id="cuerpoVenta">




                                    @foreach($venta->pagos as $i => $pago)
                                        <div class="card p-3 mb-3 pago-item">
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <label>Entidad</label>
                                                    <select name="entidad_id[]" class="form-control js-example-basic-single" required disabled>
                                                        <option value="">Seleccione...</option>
                                                        @foreach($entidads as $entidadId => $entidad)
                                                            <option value="{{ $entidadId }}"
                                                                {{ old('entidad_id.'.$i, $pago->entidad_id) == $entidadId ? 'selected' : '' }}>
                                                                {{ $entidad }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-2">
                                                    <label>Importe</label>
                                                    <input type="number" name="monto[]" class="form-control"
                                                           value="{{ old('monto.'.$i, $pago->monto) }}" required disabled>
                                                </div>
                                                <div class="col-md-2">
                                                    <label id="fechaPago">Fecha Pago</label>
                                                    <input type="date" name="fecha_pago[]" class="form-control"
                                                           value="{{ old('fecha_pago.'.$i, $pago->fecha ? date('Y-m-d', strtotime($pago->fecha)) : '') }}" required disabled>
                                                </div>
                                                <div class="col-md-2">
                                                    <label>Acreditado</label>
                                                    <input type="number" name="pagado[]" class="form-control"
                                                           value="{{ old('pagado.'.$i, $pago->pagado) }}" disabled>
                                                </div>
                                                <div class="col-md-2">
                                                    <label>Fecha Contadora</label>
                                                    <input type="date" name="contadora[]" class="form-control"
                                                           value="{{ old('contadora.'.$i, $pago->contadora ? date('Y-m-d', strtotime($pago->contadora)) : '') }}" disabled>
                                                </div>
                                            </div>

                                            <div class="row mt-2">
                                                <div class="col-5">
                                                    <label>Observaciones vendedor</label>
                                                    <textarea name="detalle[]" class="form-control" rows="2" disabled>{{ old('detalle.'.$i, $pago->detalle) }}</textarea>
                                                </div>
                                                <div class="col-5">
                                                    <label>Observaciones</label>
                                                    <textarea name="observaciones[]" class="form-control" rows="2" disabled>{{ old('observaciones.'.$i, $pago->observacion) }}</textarea>
                                                </div>

                                            </div>
                                        </div>
                                    @endforeach


                                </div>


                            </div>


                        </div>
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label>Importe total</label>
                                <input type="text" id="totalMonto" name="totalMonto" class="form-control" value="0" readonly disabled>
                            </div>
                            <div class="col-md-3">
                                <label>Importe Acreditado</label>
                                <input type="text" id="totalAcreditado" name="totalAcreditado" class="form-control" value="0" readonly disabled>
                            </div>
                        </div>

                        {{-- Botones --}}
                        <div class="row mt-3">
                            <div class="form-group">

                                <a href='{{ route('ventas.index') }}' class="btn btn-warning">Volver</a>
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
