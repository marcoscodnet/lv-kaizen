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

                                    <div class="col-lg-5">
                                        <div class="form-group">
                                            <label for="repuestos">Repuestos utilizados</label>
                                            <textarea class="form-control" id="repuestos" name="repuestos" rows="3" disabled>{{ old('repuestos',$servicio->repuestos) }}</textarea>
                                        </div>
                                    </div>

                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="instrumentos">Instrumentos de medición utilizados</label>
                                            <textarea class="form-control" id="instrumentos" name="instrumentos" rows="3" disabled>{{ old('instrumentos',$servicio->instrumentos) }}</textarea>
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
                                    <div class="col-lg-2">
                                        <div class="form-group">
                                            <label for="monto">Precio</label>
                                            <input type="number" class="form-control" id="monto" name="monto"
                                                   value="{{ old('monto',$servicio->monto) }}" disabled>
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

    <script>

    </script>


@endsection
