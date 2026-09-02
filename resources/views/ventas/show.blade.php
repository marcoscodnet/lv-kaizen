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
                                    @php
                                        // Precio de lista del producto, solo como referencia
                                        $precioSugerido = isset($venta->unidad->producto)
                                            ? (float) $venta->unidad->producto->precio
                                            : 0;

                                        // Lo que se cobró efectivamente por la moto. Si la venta es
                                        // vieja y no lo tiene, cae al precio de lista.
                                        $precioUnidad = $venta->monto ?: $precioSugerido;

                                        // Lo que hay que cobrar por toda la operación: la moto más los conceptos.
                                        // Es el número contra el que se controla lo acreditado.
                                        $totalACobrar = (float) $precioUnidad + $venta->total_articulos;
                                    @endphp
                                    <label for="precio">Importe sugerido</label>
                                    <input type="text" class="form-control" id="precio" name="precio"
                                           value="{{ $precioSugerido }}" readonly disabled>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label for="monto_unidad" class="fw-bold">Importe moto</label>
                                    <input type="text" class="form-control fw-bold" id="monto_unidad" name="monto_unidad"
                                           value="{{ number_format((float) $precioUnidad, 2, ',', '.') }}" readonly disabled>
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
                                        <label for="forma">Condición de venta</label>
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
                                        @php
                                            // Pagos de entidades sin autorización (efectivo y similares) no pasan
                                            // por auditoría: se acreditan solos y no llevan fecha de contadora.
                                            $autoAcreditado = $pago->entidad && $pago->entidad->acreditaAutomatico();
                                        @endphp
                                        <div class="card p-3 mb-3 pago-item">
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <label>Forma de pago</label>
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
                                                    <input type="text" name="monto[]" class="form-control formato-numero"
                                                           value="{{ old('monto.'.$i, $pago->monto) }}" required disabled>
                                                </div>
                                                <div class="col-md-2">
                                                    <label id="fechaPago">Fecha Pago</label>
                                                    <input type="date" name="fecha_pago[]" class="form-control"
                                                           value="{{ old('fecha_pago.'.$i, $pago->fecha ? date('Y-m-d', strtotime($pago->fecha)) : '') }}" required disabled>
                                                </div>
                                                <div class="col-md-2">
                                                    <label>Acreditado</label>
                                                    <input type="text" name="pagado[]" class="form-control formato-numero"
                                                           value="{{ old('pagado.'.$i, $pago->pagado) }}" disabled>
                                                </div>
                                                @if(!$autoAcreditado)
                                                    <div class="col-md-2">
                                                        <label>Fecha Contadora</label>
                                                        <input type="date" name="contadora[]" class="form-control"
                                                               value="{{ old('contadora.'.$i, $pago->contadora ? date('Y-m-d', strtotime($pago->contadora)) : '') }}" disabled>
                                                    </div>
                                                @else
                                                    <div class="col-md-2 d-flex align-items-end">
                                                        <span class="badge bg-success">Acreditado automático</span>
                                                    </div>
                                                @endif
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

                                            <div class="row mt-2">
                                                <div class="col-12">
                                                    <label>Comprobantes</label>
                                                    @if($pago->comprobantes && $pago->comprobantes->count())
                                                        <div class="d-flex flex-wrap gap-2">
                                                            @foreach($pago->comprobantes as $comp)
                                                                @php $ext = strtolower(pathinfo($comp->path, PATHINFO_EXTENSION)); @endphp
                                                                <a href="{{ asset($comp->path) }}" target="_blank" class="border rounded p-1 text-center" style="text-decoration:none;">
                                                                    @if(in_array($ext, ['jpg','jpeg','png']))
                                                                        <img src="{{ asset($comp->path) }}" style="max-width:120px; max-height:90px; display:block;">
                                                                    @else
                                                                        <span class="badge bg-secondary">📄 Ver PDF</span>
                                                                    @endif
                                                                </a>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <p class="text-muted mb-0">Sin comprobantes.</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach


                                </div>


                            </div>


                        </div>
                        {{-- Conceptos que se cobraron junto con la moto --}}
                        @include('includes.articulos_venta', [
                                'items'          => optional($venta->ventaArticulos)->piezas,
                                'precioUnidad'   => (float) $precioUnidad,
                                'precioSugerido' => $precioSugerido,
                                'sucursalId'     => $venta->sucursal_id,
                                'sucursalNombre' => $sucursals[$venta->sucursal_id] ?? '',
                                'soloLectura'    => true,
                            ])

                        <div class="row mb-3 mt-3">
                            <div class="col-md-3">
                                <label>Importe total</label>
                                <input type="text" id="totalMonto" name="totalMonto" class="form-control formato-numero" value="0" readonly disabled>
                            </div>
                            <div class="col-md-3">
                                <label>Importe Acreditado</label>
                                <input type="text" id="totalAcreditado" name="totalAcreditado" class="form-control formato-numero" value="0" readonly disabled>
                            </div>
                            <div class="col-md-3">
                                <label class="fw-bold">Importe a cobrar</label>
                                <input type="text" id="totalACobrar" name="totalACobrar" class="form-control fw-bold"
                                       value="{{ number_format($totalACobrar, 2, ',', '.') }}" readonly disabled>
                            </div>
                        </div>

                        {{-- Control informativo: acreditado vs importe a cobrar. No bloquea la operación. --}}
                        @include('includes.aviso_acreditacion', [
                            'pagos'    => $venta->pagos,
                            'sugerido' => $totalACobrar,
                            'etiqueta' => 'A cobrar',
                            'totales'  => false,
                        ])

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
    <script src="https://cdn.jsdelivr.net/npm/autonumeric@4.10.5/dist/autoNumeric.min.js"></script>
<script>
    function actualizarTotales() {
        let totalMonto = 0;
        let totalAcreditado = 0;

        $('input[name="monto[]"]').each(function() {
            let val = $(this).val()
                .replace(/\./g,'')
                .replace(',','.');
            val = parseFloat(val);
            if (!isNaN(val)) totalMonto += val;
        });

        $('input[name="pagado[]"]').each(function() {
            let val = $(this).val()
                .replace(/\./g,'')
                .replace(',','.');
            val = parseFloat(val);
            if (!isNaN(val)) totalAcreditado += val;
        });

        // VOLVER a formato-numero usando AutoNumeric
        AutoNumeric.getAutoNumericElement('#totalMonto').set(totalMonto);
        AutoNumeric.getAutoNumericElement('#totalAcreditado').set(totalAcreditado);
    }

    $(document).ready(function () {
        new AutoNumeric.multiple('.formato-numero', {
            digitGroupSeparator: '.',
            decimalCharacter: ',',
            decimalPlaces: 2,
            unformatOnSubmit: true
        });
        actualizarTotales();
    })

</script>


@endsection
