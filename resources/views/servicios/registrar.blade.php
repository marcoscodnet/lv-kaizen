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
                        <i class="fa fa-wrench" aria-hidden="true"></i>
                        <span class="ms-2">Registrar servicio</span>
                    </h5>
                </div>
            </div>
        </div>
        <div class="card-body bg-body-tertiary">
            <form id="formVenta" role="form" action="{{ route('servicios.store') }}" method="post">
                {{ csrf_field() }}

                <div class="tab-content">
                    <div class="box-body">
                        @include('includes.messages')

                        {{-- Datos de la unidad --}}
                        <div class="row">
                            <div class="col-lg-9">
                                <div class="form-group">
                                    <label for="unidad">Unidad</label>

                                    <input type="text" class="form-control" id="unidad" name="unidad"
                                           value="{{ isset(optional(optional($venta)->unidad)->producto) ? optional(optional($venta)->unidad)->producto->tipounidad->nombre : '' }} {{ isset(optional(optional($venta)->unidad)->producto) ? optional(optional($venta)->unidad)->producto->marca->nombre : '' }} {{ isset(optional(optional($venta)->unidad)->producto) ? optional(optional($venta)->unidad)->producto->modelo->nombre : '' }} {{ isset(optional(optional($venta)->unidad)->producto) ? optional(optional($venta)->unidad)->producto->color->nombre : '' }}"
                                           readonly>
                                </div>
                            </div>

                        </div>

                        <div class="row">
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label for="venta">F. Venta</label>
                                    <input type="date" class="form-control" id="fecha" name="fecha"  value="@if (old('fecha')){{ old('fecha') }}@else{{ (optional($venta)->fecha)?date('Y-m-d', strtotime($venta->fecha)):'' }}@endif">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="modelo">Modelo</label>
                                    <input type="text" class="form-control" id="modelo" name="modelo"
                                           value="{{ old('modelo', optional(optional($venta)->unidad)->producto->modelo->nombre ?? '') }}" required>

                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label for="motor">Motor</label>
                                    <input type="text" class="form-control" id="motor" name="motor" value="@if (old('motor')){{ old('motor') }}@else{{ optional(optional($venta)->unidad)->motor }}@endif" required>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label for="chasis">Chasis</label>
                                    <input type="text" class="form-control" id="chasis" name="chasis" value="@if (old('chasis')){{ old('chasis') }}@else{{ optional(optional($venta)->unidad)->cuadro }}@endif" required>
                                </div>
                            </div>
                            <div class="col-lg-1">
                                <div class="form-group">
                                    <label for="year">Año</label>
                                    <input type="text" class="form-control" id="year" name="year"
                                           value="@if (old('year')){{ old('year') }}@else{{ optional(optional($venta)->unidad)->year }}@endif" required>
                                </div>
                            </div>
                        </div>

                        {{-- Cliente --}}
                        <div class="row">
                            <div class="col-lg-9">
                                <div class="form-group d-flex align-items-end gap-2">
                                    <div class="flex-grow-1">
                                        <label for="cliente_id">Cliente</label>
                                        <select name="cliente_id" id="cliente_id" class="form-control js-example-basic-single" required>
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
                                    <button type="button" class="btn btn-success" id="btnNuevoCliente" data-bs-toggle="modal" data-bs-target="#nuevoClienteModal">
                                        <i class="fa fa-check"></i>
                                    </button>
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
                                                   value="{{ old('kilometros')}}">
                                        </div>
                                    </div>
                                    <div class="col-lg-7">
                                        <div class="form-group">
                                            <label for="observacion">Observaciones</label>
                                            <textarea class="form-control" id="observacion" name="observacion" rows="3">{{ old('observacion') }}</textarea>
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
                                        <select id="sucursal_id" name="sucursal_id" class="form-control js-example-basic-single" required>
                                            <option value="">Seleccione...</option>
                                            @foreach($sucursals as $sucursalId => $sucursal)
                                                <option value="{{ $sucursalId }}" {{ old('sucursal_id', auth()->user()->sucursal_id) == $sucursalId ? 'selected' : '' }}>
                                                    {{ $sucursal }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-lg-3">
                                        <label for="tipo_servicio_id">Tipo</label>
                                        <select id="tipo_servicio_id" name="tipo_servicio_id" class="form-control js-example-basic-single" required>
                                            <option value="">Seleccione...</option>
                                            @foreach($tipos as $tipoId => $tipo)
                                                <option value="{{ $tipoId }}" {{ old('tipo_servicio_id') == $tipoId ? 'selected' : '' }}>
                                                    {{ $tipo }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label for="ingreso">F. Ingreso</label>
                                            <input type="text" class="form-control" id="ingreso" name="ingreso"
                                                   value="{{ now()->format('d/m/Y H:i:s') }}" required>
                                        </div>
                                    </div>

                                </div>
                                <div class="row">

                                    <div class="col-lg-5">
                                        <div class="form-group">
                                            <label for="descripcion">Descripciones y pedidos del cliente</label>
                                            <textarea class="form-control" id="descripcion" name="descripcion" rows="3">{{ old('descripcion') }}</textarea>
                                        </div>
                                    </div>

                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="diagnostico">Diagnóstico y reparación realizada</label>
                                            <textarea class="form-control" id="diagnostico" name="diagnostico" rows="3">{{ old('diagnostico') }}</textarea>
                                        </div>
                                    </div>

                                </div>

                                <div class="row">

                                    <div class="col-lg-5">
                                        <div class="form-group">
                                            <label for="repuestos">Repuestos utilizados</label>
                                            <textarea class="form-control" id="repuestos" name="repuestos" rows="3">{{ old('repuestos') }}</textarea>
                                        </div>
                                    </div>

                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="instrumentos">Instrumentos de medición utilizados</label>
                                            <textarea class="form-control" id="instrumentos" name="instrumentos" rows="3">{{ old('instrumentos') }}</textarea>
                                        </div>
                                    </div>

                                </div>
                                <div class="row">
                                    <div class="col-lg-3">
                                        <label for="mecanicos">Mecánicos</label>
                                        <input type="text" class="form-control" id="mecanicos" name="mecanicos"
                                               value="{{ old('mecanicos') }}" required>
                                    </div>
                                    <div class="col-lg-2">
                                        <label for="tiempo">Tiempo de mano de obra</label>
                                        <input type="text" class="form-control" id="tiempo" name="tiempo"
                                               value="{{ old('tiempo') }}" required>
                                    </div>
                                    <div class="col-lg-2">
                                        <div class="form-group">
                                            <label for="entrega">F. compromiso entrega</label>
                                            <input type="date" class="form-control" id="entrega" name="entrega"
                                                   value="{{ old('entrega') }}" required>
                                        </div>
                                    </div>
                                    <div class="col-lg-2">
                                        <div class="form-group">
                                            <label for="monto">Precio</label>
                                            <input type="number" class="form-control" id="monto" name="monto"
                                                   value="{{ old('monto') }}" required>
                                        </div>
                                    </div>
                                    <div class="col-lg-2">
                                        <div class="form-check mt-4">
                                            <input type="hidden" name="pagado" value="0">
                                            <input class="form-check-input" type="checkbox" id="pagado" name="pagado" value="1"
                                                {{ old('pagado', $obj->pagado ?? false) ? 'checked' : '' }}>
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
                                <button type="submit" class="btn btn-primary">Guardar</button>
                                <a href='{{ route('servicios.unidads') }}' class="btn btn-warning">Volver</a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Nuevo Cliente -->
    <div class="modal fade" id="nuevoClienteModal" tabindex="-1" aria-labelledby="nuevoClienteLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form id="formNuevoCliente">
                @csrf
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="nuevoClienteLabel">Nuevo Cliente</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <!-- Nombre -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Nombre</label>
                                <input type="text" name="nombre" id="nombre" class="form-control" required>
                                <input type="hidden" name="cliente_id" id="cliente_id_hidden">
                            </div>

                            <!-- Documento -->
                            <div class="col-md-2 mb-3">
                                <label class="form-label">Documento</label>
                                <input type="text" name="documento" id="documento" class="form-control" required>
                            </div>

                            <!-- CUIL -->
                            <div class="col-md-3 mb-3">
                                <label class="form-label">CUIL</label>
                                <input type="text" name="cuil" id="cuil" placeholder="XX-XXXXXXXX-X" class="form-control">
                            </div>

                            <!-- Nacimiento -->
                            <div class="col-md-3 mb-3">
                                <label class="form-label">F. Nacimiento</label>
                                <input type="date" name="nacimiento" id="nacimiento" class="form-control" required>
                            </div>

                            <!-- Particular -->
                            <div class="col-md-1 mb-3">
                                <label class="form-label">Área</label>
                                <input type="text" name="particular_area" id="particular_area" class="form-control" required>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="form-label">Particular</label>
                                <input type="text" name="particular" id="particular" class="form-control" required>
                            </div>

                            <!-- Celular -->
                            <div class="col-md-1 mb-3">
                                <label class="form-label">Área</label>
                                <input type="text" name="celular_area" id="celular_area" class="form-control" required>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="form-label">Celular</label>
                                <input type="text" name="celular" id="celular" class="form-control" required>
                            </div>
                            <!-- Email -->
                            <div class="col-md-5 mb-3">
                                <label class="form-label">E-mail</label>
                                <input type="email" name="email" id="email" class="form-control" required>
                            </div>
                            <!-- Dirección -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Calle</label>
                                <input type="text" name="calle" id="calle" class="form-control" required>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="form-label">Nro</label>
                                <input type="text" name="nro" id="nro" class="form-control" required>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="form-label">CP</label>
                                <input type="text" name="cp" id="cp" class="form-control" required>
                            </div>

                                @include('includes.select-provincia-localidad')



                            <!-- Nacionalidad -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Nacionalidad</label>
                                <input type="text" name="nacionalidad" id="nacionalidad" class="form-control" required>
                            </div>

                            <!-- Estado Civil -->
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Estado Civil</label>
                                <select name="estado_civil" id="estado_civil" class="form-control" required>
                                    <option value="">
                                        Seleccionar...
                                    </option>
                                    @foreach (config('civiles') as $key => $label)
                                        <option value="{{ $key }}" {{ old('estado_civil', $cliente->estado_civil ?? '') == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-offset-3 col-lg-3 col-md-3" id="conyuge-container" style="display: none;">
                                <div class="form-group">
                                    <label for="conyuge">Cónyuge</label>
                                    <input type="text" class="form-control" id="conyuge" name="conyuge" placeholder="Cónyuge" value="{{ old('conyuge') }}" required>
                                </div>
                            </div>
                            <!-- Cómo llegó -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Cómo llegó?</label>
                                <select name="llego" id="llego" class="form-control" required>
                                    <option value="">
                                        Seleccionar...
                                    </option>
                                    @foreach (config('llego') as $key => $label)
                                        <option value="{{ $key }}" {{ old('llego', $cliente->llego ?? '') == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- IVA -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Condición IVA</label>
                                <select name="iva" id="iva" class="form-control" required>
                                    <option value="">
                                        Seleccionar...
                                    </option>
                                    @foreach (config('iva') as $key => $label)
                                        <option value="{{ $key }}" {{ old('iva', $cliente->iva ?? '') == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">

                        <button type="submit" class="btn btn-primary">Guardar</button>
                        <button type="button" class="btn btn-warning" data-bs-dismiss="modal">Cancelar</button>
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

    <script src="{{ asset('assets/js/combo-provincia-localidad-modal.js') }}"></script>

    <script>



        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        var localidadUrl = "{{ url('localidads') }}";

        $(document).ready(function () {

            // Inicializar Select2 básico
            $('.js-example-basic-single').each(function () {
                if ($(this).hasClass("select2-hidden-accessible")) {
                    $(this).select2('destroy'); // destruir si ya estaba inicializado
                }
                $(this).select2({ width: '100%'});
            });

            $('#cliente_id').select2({
                minimumInputLength: 3,
                language: 'es',
                ajax: {
                    url: '{{ route("cliente.search") }}',
                    type: "get",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return { search: params.term };
                    },
                    processResults: function (response) {
                        // response viene en formato [{id:..., text:...}, ...]
                        // Agregar opción "nuevo cliente" al final
                        response.push({ id: 'nuevo', text: '✅ Nuevo cliente' });
                        return { results: response };
                    },
                    cache: true
                }
            });

// Manejo de selección
            $('#cliente_id').on('select2:select', function (e) {
                var clienteId = e.params.data.id;

                if (!clienteId || clienteId === 'nuevo') {
                    $('#nuevoClienteLabel').text('Nuevo Cliente');
                    $('#formNuevoCliente')[0].reset();
                    // Nuevo cliente: abrir modal vacío
                    $('#formNuevoCliente')[0].reset();
                    $('#formNuevoCliente').data('confirmed', false);
                    $('#nuevoClienteModal').modal({
                        backdrop: 'static',
                        keyboard: false
                    }).modal('show');
                    return;
                }else {
                    $('#nuevoClienteLabel').text('Verificar Cliente');
                }

                if (clienteId) {
                    // Cliente existente: traer datos para verificar
                    $.ajax({
                        url: '{{ url("clientes") }}/' + clienteId + '/json',
                        type: 'GET',
                        success: function (cliente) {
                            // Fecha de nacimiento en YYYY-MM-DD
                            let nacimiento = cliente.nacimiento ? cliente.nacimiento.split(' ')[0] : '';
                            $('#formNuevoCliente #nacimiento').val(nacimiento);

                            // Datos del cliente
                            $('#formNuevoCliente #cliente_id_hidden').val(cliente.id);

                            $('#formNuevoCliente #nombre').val(cliente.nombre);
                            $('#formNuevoCliente #documento').val(cliente.documento);
                            $('#formNuevoCliente #cuil').val(cliente.cuil);
                            $('#formNuevoCliente #particular_area').val(cliente.particular_area);
                            $('#formNuevoCliente #particular').val(cliente.particular);
                            $('#formNuevoCliente #celular_area').val(cliente.celular_area);
                            $('#formNuevoCliente #celular').val(cliente.celular);
                            $('#formNuevoCliente #email').val(cliente.email);
                            $('#formNuevoCliente #calle').val(cliente.calle);
                            $('#formNuevoCliente #nro').val(cliente.nro);
                            $('#formNuevoCliente #cp').val(cliente.cp);
                            $('#formNuevoCliente #nacionalidad').val(cliente.nacionalidad);
                            $('#formNuevoCliente #estado_civil').val(cliente.estado_civil).trigger('change');
                            $('#formNuevoCliente #llego').val(cliente.llego);
                            $('#formNuevoCliente #iva').val(cliente.iva);
                            $('#formNuevoCliente #conyuge').val(cliente.conyuge);

                            // Inicializar select2 dentro del modal
                            $('#provincia_id, #localidad').select2({
                                theme: 'bootstrap-5',
                                dropdownParent: $('#nuevoClienteModal')
                            });

                            // Traer provincia desde localidad_id
                            $.ajax({
                                url: '{{ url("localidads/info") }}/' + cliente.localidad_id, // endpoint que devuelve {id, nombre, provincia_id}
                                type: 'GET',
                                success: function(localidad) {
                                    // Setear provincia y localidad usando tu JS de Select2
                                    $('#provincia_id')
                                        .val(localidad.provincia_id)
                                        .data('old-localidad', localidad.id)
                                        .trigger('change');

                                    // Abrir modal
                                    $('#formNuevoCliente').data('confirmed', false);
                                    $('#nuevoClienteModal').modal({ backdrop: 'static', keyboard: false }).modal('show');
                                },
                                error: function() {
                                    alert('No se pudo cargar la provincia de la localidad.');
                                }
                            });
                        },
                        error: function () {
                            alert('No se pudieron cargar los datos del cliente.');
                        }
                    });

                }
            });







            $('#btnNuevoCliente').on('click', function() {
                var clienteId = $('#cliente_id').val();

                if (!clienteId || clienteId === 'nuevo') {
                    // Nuevo cliente
                    $('#nuevoClienteLabel').text('Nuevo Cliente');
                    $('#formNuevoCliente')[0].reset();
                    $('#formNuevoCliente').data('confirmed', false);
                    $('#nuevoClienteModal').modal({
                        backdrop: 'static',
                        keyboard: false
                    }).modal('show');
                    return;
                }

                // Cliente existente
                $('#nuevoClienteLabel').text('Verificar Cliente');

                $.ajax({
                    url: '{{ url("clientes") }}/' + clienteId + '/json',
                    type: 'GET',
                    success: function (cliente) {
                        // Fecha de nacimiento en YYYY-MM-DD
                        let nacimiento = cliente.nacimiento ? cliente.nacimiento.split(' ')[0] : '';
                        $('#formNuevoCliente #nacimiento').val(nacimiento);

                        // Datos del cliente
                        $('#formNuevoCliente #cliente_id_hidden').val(cliente.id);
                        $('#formNuevoCliente #nombre').val(cliente.nombre);
                        $('#formNuevoCliente #documento').val(cliente.documento);
                        $('#formNuevoCliente #cuil').val(cliente.cuil);
                        $('#formNuevoCliente #particular_area').val(cliente.particular_area);
                        $('#formNuevoCliente #particular').val(cliente.particular);
                        $('#formNuevoCliente #celular_area').val(cliente.celular_area);
                        $('#formNuevoCliente #celular').val(cliente.celular);
                        $('#formNuevoCliente #email').val(cliente.email);
                        $('#formNuevoCliente #calle').val(cliente.calle);
                        $('#formNuevoCliente #nro').val(cliente.nro);
                        $('#formNuevoCliente #cp').val(cliente.cp);
                        $('#formNuevoCliente #nacionalidad').val(cliente.nacionalidad);
                        $('#formNuevoCliente #estado_civil').val(cliente.estado_civil).trigger('change');
                        $('#formNuevoCliente #llego').val(cliente.llego);
                        $('#formNuevoCliente #iva').val(cliente.iva);
                        $('#formNuevoCliente #conyuge').val(cliente.conyuge);

                        // Inicializar select2 dentro del modal
                        $('#provincia_id, #localidad').select2({
                            theme: 'bootstrap-5',
                            dropdownParent: $('#nuevoClienteModal')
                        });

                        // Traer provincia desde localidad_id
                        $.ajax({
                            url: '{{ url("localidads/info") }}/' + cliente.localidad_id, // endpoint que devuelve {id, nombre, provincia_id}
                            type: 'GET',
                            success: function(localidad) {
                                $('#provincia_id')
                                    .val(localidad.provincia_id)
                                    .data('old-localidad', localidad.id)
                                    .trigger('change');

                                $('#formNuevoCliente').data('confirmed', false);
                                $('#nuevoClienteModal').modal({ backdrop: 'static', keyboard: false }).modal('show');
                            },
                            error: function() {
                                alert('No se pudo cargar la provincia de la localidad.');
                            }
                        });
                    },
                    error: function () {
                        alert('No se pudieron cargar los datos del cliente.');
                    }
                });
            });


            // Guardar cliente (existente o nuevo)
            $('#formNuevoCliente').submit(function (e) {
                e.preventDefault();
                $.ajax({
                    url: '{{ route("clientes.quickstore") }}',
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function (cliente) {
                        $('#formNuevoCliente').data('confirmed', true);
                        $('#nuevoClienteModal').modal('hide');

                        // Agregar cliente al select2 si no existe
                        var newOption = new Option(cliente.text, cliente.id, true, true);
                        $('#cliente_id').append(newOption).trigger('change');
                    },
                    error: function (xhr) {
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            alert(Object.values(errors).join("\n"));
                        } else {
                            alert("Error al guardar cliente");
                        }
                    }
                });
            });

            // Si se cierra sin confirmar => limpiar select
            $('#nuevoClienteModal').on('hidden.bs.modal', function () {
                if (!$('#formNuevoCliente').data('confirmed')) {
                    $('#cliente_id').val(null).trigger('change');
                }
            });

            $('#nuevoClienteModal').on('shown.bs.modal', function () {
                $('#provincia_id, #localidad').select2({
                    theme: 'bootstrap-5',
                    dropdownParent: $('#nuevoClienteModal')
                }).next('.select2-container').addClass('form-control');;
                // InputMask
                $('#cuil').inputmask('99-99999999-9', { placeholder: 'XX-XXXXXXXX-X' });

            });



            // Mostrar/ocultar cónyuge
            function toggleConyuge() {
                var estadoCivil = $('#estado_civil').val();
                if (estadoCivil === 'Casado/a' || estadoCivil === 'Concubino/a') {
                    $('#conyuge-container').show();
                    $('#conyuge').attr('required', true);
                } else {
                    $('#conyuge-container').hide();
                    $('#conyuge').removeAttr('required').val('');
                }
            }

            $('#estado_civil').on('change', toggleConyuge);

            // Ejecutar al cargar (por si hay old() con Casado/a o Concubino/a)
            toggleConyuge();



        });
    </script>


@endsection
