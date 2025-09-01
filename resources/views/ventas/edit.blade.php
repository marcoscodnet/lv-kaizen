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
                        <span class="ms-2">Editar venta unidad</span>
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
                                    <input type="hidden" id="unidad_id" name="unidad_id" value="{{ $venta->unidad->id }}">
                                    <input type="text" class="form-control" id="producto" name="producto"
                                           value="{{ isset($venta->unidad->producto) ? $venta->unidad->producto->tipounidad->nombre : '' }} {{ isset($venta->unidad->producto) ? $venta->unidad->producto->marca->nombre : '' }} {{ isset($venta->unidad->producto) ? $venta->unidad->producto->modelo->nombre : '' }} {{ isset($venta->unidad->producto) ? $venta->unidad->producto->color->nombre : '' }}"
                                           readonly>
                                </div>
                            </div>

                        </div>

                        <div class="row">

                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label for="motor">Motor</label>
                                    <input type="text" class="form-control" id="motor" name="motor" value="{{ $venta->unidad->motor }}" readonly>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label for="cuadro">Cuadro</label>
                                    <input type="text" class="form-control" id="cuadro" name="cuadro" value="{{ $venta->unidad->cuadro }}" readonly>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label for="precio">Importe sugerido</label>
                                    <input type="text" class="form-control" id="precio" name="precio"
                                           value="{{ isset($venta->unidad->producto) ? $venta->unidad->producto->precio : '' }}" readonly>
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
                                           value="{{ $fechaValor }}" readonly required>

                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label for="user_id">Vendedor</label>
                                    <select name="user_id" id="user_id" class="form-control js-example-basic-single" required>
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
                                    <select id="sucursal_id" name="sucursal_id" class="form-control js-example-basic-single" required>
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

                        <div class="row">
                            <div class="col-lg-3">
                                <div class="form-group d-flex align-items-end gap-2">
                                    <div class="flex-grow-1">
                                        <label for="forma">Forma de pago</label>
                                        <select name="forma" id="forma" class="form-control" required>
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
                                    <div class="row">
                                        <div class="col text-start" style="margin-bottom: 1%">
                                            <button type="button" id="addItemPago" class="btn btn-success btn-sm mt-2">
                                                <i class="fa fa-plus"></i> Agregar pago
                                            </button>
                                        </div>
                                    </div>



                                    @foreach($venta->pagos as $i => $pago)
                                        <div class="card p-3 mb-3 pago-item">
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <label>Entidad</label>
                                                    <select name="entidad_id[]" class="form-control js-example-basic-single" required>
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
                                                           value="{{ old('monto.'.$i, $pago->monto) }}" required>
                                                </div>
                                                <div class="col-md-2">
                                                    <label id="fechaPago">Fecha Pago</label>
                                                    <input type="date" name="fecha_pago[]" class="form-control"
                                                           value="{{ old('fecha_pago.'.$i, $pago->fecha ? date('Y-m-d', strtotime($pago->fecha)) : '') }}" required>
                                                </div>
                                                <div class="col-md-2">
                                                    <label>Acreditado</label>
                                                    <input type="number" name="pagado[]" class="form-control"
                                                           value="{{ old('pagado.'.$i, $pago->pagado) }}">
                                                </div>
                                                <div class="col-md-2">
                                                    <label>Fecha Contadora</label>
                                                    <input type="date" name="contadora[]" class="form-control"
                                                           value="{{ old('contadora.'.$i, $pago->contadora ? date('Y-m-d', strtotime($pago->contadora)) : '') }}">
                                                </div>
                                            </div>

                                            <div class="row mt-2">
                                                <div class="col-5">
                                                    <label>Observaciones vendedor</label>
                                                    <textarea name="detalle[]" class="form-control" rows="2">{{ old('detalle.'.$i, $pago->detalle) }}</textarea>
                                                </div>
                                                <div class="col-5">
                                                    <label>Observaciones</label>
                                                    <textarea name="observaciones[]" class="form-control" rows="2">{{ old('observaciones.'.$i, $pago->observacion) }}</textarea>
                                                </div>
                                                <div class="col-md-1 d-flex align-items-end">
                                                    <button type="button" class="btn btn-danger btn-sm removeItemPago">
                                                        <i class="fa fa-times"></i>
                                                    </button>
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
                                <input type="text" id="totalMonto" name="totalMonto" class="form-control" value="0" readonly>
                            </div>
                            <div class="col-md-3">
                                <label>Importe Acreditado</label>
                                <input type="text" id="totalAcreditado" name="totalAcreditado" class="form-control" value="0" readonly>
                            </div>
                        </div>

                        {{-- Botones --}}
                        <div class="row mt-3">
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">Guardar</button>
                                <a href='{{ route('ventas.index') }}' class="btn btn-warning">Volver</a>
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


    <script src="{{ asset('bower_components/inputmask/dist/min/jquery.inputmask.bundle.min.js') }}"></script>

    <script src="{{ asset('assets/js/combo-provincia-localidad.js') }}"></script>

    <script>
        function actualizarTotales() {
            let totalMonto = 0;
            let totalAcreditado = 0;

            $('input[name="monto[]"]').each(function() {
                let val = parseFloat($(this).val());
                if (!isNaN(val)) totalMonto += val;
            });

            $('input[name="pagado[]"]').each(function() {
                let val = parseFloat($(this).val());
                if (!isNaN(val)) totalAcreditado += val;
            });

            $('#totalMonto').val(totalMonto.toFixed(2));
            $('#totalAcreditado').val(totalAcreditado.toFixed(2));
        }


        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        var localidadUrl = "{{ url('localidads') }}";

        $(document).ready(function () {
            actualizarTotales();

            // Ejecutar al cambiar monto o pagado
            $('body').on('input', 'input[name="monto[]"], input[name="pagado[]"]', actualizarTotales);

            // Ejecutar al agregar o eliminar pagos
            $('body').on('click', '#addItemPago, .removeItemPago', function() {
                setTimeout(actualizarTotales, 100); // pequeño delay para que el DOM se actualice
            });

            function toggleDivs() {
                const valor = $('#forma').val();

                // Ocultar todos
                $('#cuerpoVenta').hide();

                // Mostrar el que corresponde
                if (valor !== '') {
                    if (valor === 'Contado') {

                        $('#fechaPago').html('Fecha de pago');
                    }
                    else{
                        $('#fechaPago').html('Aprobación Crédito');
                    }
                    $('#cuerpoVenta').show();
                }
            }

            // Ejecutar al cargar por si hay uno preseleccionado
            toggleDivs();

            // Ejecutar al cambiar
            $('#forma').on('change', toggleDivs);
            // Inicializar Select2 básico
            $('.js-example-basic-single').each(function () {
                if ($(this).hasClass("select2-hidden-accessible")) {
                    $(this).select2('destroy'); // destruir si ya estaba inicializado
                }
                $(this).select2({ width: '100%'});
            });

            // Select2 para clientes con búsqueda AJAX
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
                        url: '{{ url("clientes") }}/' + clienteId,
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
                    url: '{{ url("clientes") }}/' + clienteId,
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


            // Guardar nuevo cliente
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

            // función que devuelve el bloque de pago
            function getPagoHtml() {
                return `
        <div class="card p-3 mb-3 pago-item">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <label>Entidad</label>
                                                <select name="entidad_id[]" class="form-control js-example-basic-single" required>
                                                    <option value="">Seleccione...</option>
                                                    @foreach($entidads as $entidadId => $entidad)
                <option value="{{ $entidadId }}" {{ old('entidad_id') == $entidadId ? 'selected' : '' }}>
                                                            {{ $entidad }}
                </option>
@endforeach
                </select>
            </div>
            <div class="col-md-2">
                                                <label>Importe</label>
                                                <input type="number" name="monto[]" class="form-control" required>
                                            </div>
            <div class="col-md-2">
                <label id="fechaPago"></label>
                <input type="date" name="fecha_pago[]" class="form-control" required>
            </div>
            <div class="col-md-2">
                <label>Acreditado</label>
                <input type="number" name="pagado[]" class="form-control">
            </div>
            <div class="col-md-2">
                <label>Fecha</label>
                <input type="date" name="contadora[]" class="form-control">
            </div>

        </div>
        <div class="row mt-2">
            <div class="col-5">
                <label>Observaciones vendedor</label>
                <textarea name="detalle[]" class="form-control" rows="2"></textarea>
            </div>
            <div class="col-5">
                <label>Observaciones</label>
                <textarea name="observaciones[]" class="form-control" rows="2"></textarea>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="button" class="btn btn-danger btn-sm removeItemPago"><i class="fa fa-times"></i></button>
            </div>
        </div>
    </div>`;
            }

            // Agregar pago
            $('#addItemPago').on('click', function () {
                $('#cuerpoVenta').append(getPagoHtml());
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

            // Eliminar pago
            $('body').on('click', '.removeItemPago', function () {
                $(this).closest('.pago-item').remove();
            });

        });
    </script>


@endsection
