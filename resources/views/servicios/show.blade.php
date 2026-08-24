@extends('layouts.app')
@section('headSection')
    <link rel="stylesheet" href="{{ asset('bower_components/select2/dist/css/select2.min.css') }}">
@endsection

@section('content')
    <div class="card mb-3">
        <div class="card-header">
            <div class="row flex-between-end">
                <div class="col-auto align-self-center">
                    <h5 class="mb-0" data-anchor="data-anchor">
                        <i class="fa fa-shopping-cart" aria-hidden="true"></i>
                        <span class="ms-2">Ver servicio</span>
                    </h5>
                </div>
            </div>
        </div>
        <div class="card-body bg-body-tertiary">
            <form id="formVenta" role="form" action="{{ route('servicios.update',$servicio->id) }}" method="post" enctype="multipart/form-data">
                {{ csrf_field() }}
                {{ method_field('PUT') }}

                <div class="tab-content">
                    <div class="box-body">
                        @include('includes.messages')

                        {{-- Datos de la unidad --}}


                        <div class="row">
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label for="venta">F. Venta</label>
                                    <input type="date" class="form-control" id="venta" name="venta"  value="@if (old('venta')){{ old('venta') }}@else{{ (optional($servicio)->venta)?date('Y-m-d', strtotime($servicio->venta)):'' }}@endif" disabled>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="modelo">Modelo</label>
                                    <input type="text" class="form-control" id="modelo" name="modelo"
                                           value="{{ old('modelo', $servicio->modelo) }}" disabled>

                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label for="motor">Motor</label>
                                    <input type="text" class="form-control" id="motor" name="motor" value="{{ old('motor', $servicio->motor) }}" disabled>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label for="chasis">Chasis</label>
                                    <input type="text" class="form-control" id="chasis" name="chasis" value="{{ old('chasis', $servicio->chasis) }}" disabled>
                                </div>
                            </div>
                            <div class="col-lg-1">
                                <div class="form-group">
                                    <label for="year">Año</label>
                                    <input type="text" class="form-control" id="year" name="year"
                                           value="{{ old('year', $servicio->year) }}" disabled>
                                </div>
                            </div>
                        </div>

                        {{-- Cliente --}}
                        <div class="row">
                            <div class="col-lg-9">
                                <div class="form-group d-flex align-items-end gap-2">
                                    <div class="flex-grow-1">
                                        <label for="cliente_id">Cliente</label>
                                        <select name="cliente_id" id="cliente_id" class="form-control js-example-basic-single" disabled>
                                            @if(old('cliente_id'))
                                                {{-- Mostrar cliente seleccionado por old() --}}
                                                <option value="{{ old('cliente_id') }}" selected>
                                                    {{ old('cliente_nombre', '') }}
                                                </option>
                                            @elseif(isset($servicio) && $servicio->cliente)
                                                {{-- Mostrar cliente existente en la servicio --}}
                                                <option value="{{ $servicio->cliente_id }}" selected>
                                                    {{ $servicio->cliente->full_name_phone }}
                                                </option>
                                            @endif
                                        </select>
                                    </div>

                                </div>

                            </div>


                        </div>


                        {{-- =================================== --}}
                        {{-- Sección: Estado General del Vehículo --}}
                        {{-- =================================== --}}
                        <div class="card mt-4">
                            <div class="card-header bg-secondary text-white">
                                <h5 class="mb-0">Estado General del Vehículo</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-2">
                                        <div class="form-group">
                                            <label for="kilometros">Kilómetros</label>
                                            <input type="text" class="form-control" id="kilometros" name="kilometros"
                                                   value="{{ old('kilometros',$servicio->kilometros)}}" disabled>
                                        </div>
                                    </div>
                                    <div class="col-lg-7">
                                        <div class="form-group">
                                            <label for="observacion">Observaciones</label>
                                            <textarea class="form-control" id="observacion" name="observacion" rows="3" disabled>{{ old('observacion',$servicio->observacion) }}</textarea>
                                        </div>
                                    </div>

                                </div>


                            </div>
                        </div>

                        {{-- =================================== --}}
                        {{-- Sección:servicio --}}
                        {{-- =================================== --}}
                        <div class="card mt-4">
                            <div class="card-header bg-secondary text-white">
                                <h5 class="mb-0">Servicio</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-3">
                                        <label for="sucursal_id">Sucursal</label>
                                        <select id="sucursal_id" name="sucursal_id" class="form-control js-example-basic-single" disabled>
                                            <option value="">Seleccione...</option>
                                            @foreach($sucursals as $sucursalId => $sucursal)
                                                <option value="{{ $sucursalId }}" {{ old('sucursal_id', $servicio->sucursal_id) == $sucursalId ? 'selected' : '' }}>
                                                    {{ $sucursal }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-lg-3">
                                        <label for="tipo_servicio_id">Tipo</label>
                                        <select id="tipo_servicio_id" name="tipo_servicio_id" class="form-control js-example-basic-single" disabled>
                                            <option value="">Seleccione...</option>
                                            @foreach($tipos as $tipoId => $tipo)
                                                <option value="{{ $tipoId }}" {{ old('tipo_servicio_id',$servicio->tipo_servicio_id) == $tipoId ? 'selected' : '' }}>
                                                    {{ $tipo }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="ingreso">F. Ingreso</label>
                                            <input type="text" class="form-control" id="ingreso" name="ingreso"
                                                   value="@if (old('ingreso')){{ old('ingreso') }}@else{{ (optional($servicio)->ingreso)?date('d/m/Y H:i:s', strtotime($servicio->ingreso)):'' }}@endif" disabled>
                                        </div>
                                    </div>

                                </div>
                                <div class="row">

                                    <div class="col-lg-5">
                                        <div class="form-group">
                                            <label for="descripcion">Descripciones y pedidos del cliente</label>
                                            <textarea class="form-control" id="descripcion" name="descripcion" rows="3" disabled>{{ old('descripcion',$servicio->descripcion) }}</textarea>
                                        </div>
                                    </div>

                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="diagnostico">Diagnóstico y reparación realizada</label>
                                            <textarea class="form-control" id="diagnostico" name="diagnostico" rows="3" disabled>{{ old('diagnostico',$servicio->diagnostico) }}</textarea>
                                        </div>
                                    </div>

                                </div>

                                <div class="row">

                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="instrumentos">Instrumentos de medición utilizados</label>
                                            <textarea class="form-control" id="instrumentos" name="instrumentos" rows="3" disabled>{{ old('instrumentos',$servicio->instrumentos) }}</textarea>
                                        </div>
                                    </div>

                                </div>

                                {{-- Repuestos asignados a esta orden (ventas de pieza con destino Taller) --}}
                                @php
                                    $repuestosAsignados = collect();
                                    foreach(($servicio->ventaPiezas ?? []) as $vp){
                                        foreach($vp->piezas as $pvp){
                                            $repuestosAsignados->push($pvp);
                                        }
                                    }
                                    $totalRepuestos = $repuestosAsignados->sum(function($p){ return (float)$p->precio * (float)$p->cantidad; });
                                    // Mirror edit(): keep stored cost when closed, live sum while open.
                                    $costoRepuestos = $servicio->pagado ? (float)$servicio->costo_repuestos : (float)$totalRepuestos;
                                    $totalServicio  = (float)($servicio->mano_de_obra ?? 0) + $costoRepuestos + (float)($servicio->insumos ?? 0);
                                @endphp
                                <div class="row">
                                    <div class="col-lg-9">
                                        <div class="form-group">
                                            <label>Repuestos utilizados</label>
                                            <table class="table table-sm table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>Código</th>
                                                        <th>Descripción</th>
                                                        <th>Sucursal</th>
                                                        <th>Cantidad</th>
                                                        <th>Precio</th>
                                                        <th>Subtotal</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($repuestosAsignados as $pvp)
                                                        <tr>
                                                            <td>{{ optional($pvp->pieza)->codigo ?? '-' }}</td>
                                                            <td>{{ optional($pvp->pieza)->descripcion ?? '-' }}</td>
                                                            <td>{{ optional($pvp->sucursal)->nombre ?? '-' }}</td>
                                                            <td>{{ $pvp->cantidad }}</td>
                                                            <td>${{ number_format((float)$pvp->precio, 2, ',', '.') }}</td>
                                                            <td>${{ number_format((float)$pvp->precio * (float)$pvp->cantidad, 2, ',', '.') }}</td>
                                                        </tr>
                                                    @empty
                                                        <tr><td colspan="6" class="text-center text-muted">Sin repuestos asignados a esta orden.</td></tr>
                                                    @endforelse
                                                </tbody>
                                                @if($repuestosAsignados->isNotEmpty())
                                                    <tfoot>
                                                        <tr>
                                                            <th colspan="5" class="text-end">Total repuestos</th>
                                                            <th>${{ number_format($totalRepuestos, 2, ',', '.') }}</th>
                                                        </tr>
                                                    </tfoot>
                                                @endif
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-3">
                                        <label for="mecanicos">Mecánicos</label>
                                        <input type="text" class="form-control" id="mecanicos" name="mecanicos"
                                               value="{{ old('mecanicos',$servicio->mecanicos) }}" disabled>
                                    </div>
                                    <div class="col-lg-2">
                                        <label for="tiempo">Tiempo de mano de obra</label>
                                        <input type="text" class="form-control" id="tiempo" name="tiempo"
                                               value="{{ old('tiempo',$servicio->tiempo) }}" disabled>
                                    </div>
                                    <div class="col-lg-2">
                                        <div class="form-group">
                                            <label for="entrega">F. compromiso entrega</label>
                                            <input type="date" class="form-control" id="entrega" name="entrega"
                                                   value="@if (old('entrega')){{ old('entrega') }}@else{{ (optional($servicio)->entrega)?date('Y-m-d', strtotime($servicio->entrega)):'' }}@endif" disabled>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-2">
                                        <div class="form-group">
                                            <label for="mano_de_obra">Mano de obra</label>
                                            <input type="text" class="form-control formato-numero" id="mano_de_obra" name="mano_de_obra"
                                                   value="{{ old('mano_de_obra', $servicio->mano_de_obra) }}" disabled>
                                        </div>
                                    </div>
                                    <div class="col-lg-2">
                                        <div class="form-group">
                                            <label for="costo_repuestos">Repuestos</label>
                                            <input type="text" class="form-control formato-numero" id="costo_repuestos" name="costo_repuestos"
                                                   value="{{ old('costo_repuestos', $costoRepuestos) }}" disabled>
                                        </div>
                                    </div>
                                    <div class="col-lg-2">
                                        <div class="form-group">
                                            <label for="insumos">Insumos</label>
                                            <input type="text" class="form-control formato-numero" id="insumos" name="insumos"
                                                   value="{{ old('insumos', $servicio->insumos ?? 0) }}" disabled>
                                        </div>
                                    </div>
                                    <div class="col-lg-2">
                                        <div class="form-group">
                                            <label for="total">Total</label>
                                            <input type="text" class="form-control formato-numero" id="total" name="total"
                                                   value="{{ old('total', $totalServicio) }}" disabled>
                                        </div>
                                    </div>
                                    <div class="col-lg-2">
                                        <div class="form-check mt-4">
                                            <input type="hidden" name="pagado" value="0">
                                            <input class="form-check-input" type="checkbox" id="pagado" name="pagado" value="1"
                                                {{ old('pagado', $servicio->pagado ?? false) ? 'checked' : '' }} disabled>
                                            <label class="form-check-label" for="pagado">
                                                Pagado
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        {{-- ================================= --}}
                        {{-- Sección: Cobro (solo lectura) --}}
                        {{-- ================================= --}}
                        <div class="card mt-4">
                            <div class="card-header bg-secondary text-white">
                                <h5 class="mb-0">Cobro</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="forma">Condición de venta</label>
                                            <input type="text" class="form-control" id="forma" name="forma" value="{{ $servicio->forma }}" disabled>
                                        </div>
                                    </div>
                                </div>
                                <table class="table table-sm mt-2">
                                    <thead>
                                        <th>Forma de pago</th>
                                        <th>Importe</th>
                                        <th>Acreditado</th>
                                        <th>Fecha Pago</th>
                                        <th>Comprobantes</th>
                                    </thead>
                                    <tbody>
                                        @forelse($servicio->pagos as $pago)
                                            <tr>
                                                <td>{{ optional($pago->entidad)->nombre }}</td>
                                                <td>{{ $pago->monto }}</td>
                                                <td>
                                                    @if($pago->pagado !== null)
                                                        {{ $pago->pagado }}
                                                        @if(optional($pago->entidad)->acreditaAutomatico())
                                                            <span class="badge bg-success">automático</span>
                                                        @endif
                                                    @else
                                                        <span class="badge bg-warning text-dark">Pendiente</span>
                                                    @endif
                                                </td>
                                                <td>{{ $pago->fecha ? \Carbon\Carbon::parse($pago->fecha)->format('d/m/Y') : '' }}</td>
                                                <td>
                                                    @if($pago->comprobantes && $pago->comprobantes->count())
                                                        @foreach($pago->comprobantes as $comp)
                                                            @php $ext = strtolower(pathinfo($comp->path, PATHINFO_EXTENSION)); @endphp
                                                            <a href="{{ asset($comp->path) }}" target="_blank" class="me-1">
                                                                @if(in_array($ext, ['jpg','jpeg','png']))
                                                                    <img src="{{ asset($comp->path) }}" style="max-width:80px; max-height:60px;">
                                                                @else
                                                                    <span class="badge bg-secondary">📄 Ver PDF</span>
                                                                @endif
                                                            </a>
                                                        @endforeach
                                                    @else
                                                        —
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="5" class="text-muted">Sin pagos registrados.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>

                                {{-- Control informativo: acreditado vs total del servicio. No bloquea nada. --}}
                                @include('includes.aviso_acreditacion', [
                                    'pagos'    => $servicio->pagos,
                                    'sugerido' => $totalServicio,
                                    'etiqueta' => 'Servicio',
                                ])
                            </div>
                        </div>

                        {{-- Botones --}}
                        <div class="row mt-3">
                            <div class="form-group">

                                <a href='{{ route('servicios.index') }}' class="btn btn-warning">Volver</a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>



@endsection

@section('footerSection')
    <script src="{{ asset('bower_components/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('bower_components/bootstrap/dist/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('bower_components/select2/dist/js/select2.min.js') }}"></script>
    <script src="{{ asset('bower_components/select2/dist/js/i18n/es.js') }}"></script>
    <script src="{{ asset('bower_components/select2/dist/js/i18n/es.js') }}"></script>


    <script src="{{ asset('bower_components/inputmask/dist/min/jquery.inputmask.bundle.min.js') }}"></script>

    <script src="{{ asset('assets/js/combo-provincia-localidad.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/autonumeric@4.10.5/dist/autoNumeric.min.js"></script>
    <script>
        $(document).ready(function () {
            new AutoNumeric.multiple('.formato-numero', {
                digitGroupSeparator: '.',
                decimalCharacter: ',',
                decimalPlaces: 2,
                unformatOnSubmit: true
            });
        });
    </script>


@endsection
