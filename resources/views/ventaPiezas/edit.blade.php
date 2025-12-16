@extends('layouts.app')
@section('headSection')

    <link rel="stylesheet" href="{{ asset('bower_components/select2/dist/css/select2.min.css') }}">

@endsection

@section('content')
    <div class="card mb-3">
        <div class="card-header">
            <div class="row flex-between-end">
                <div class="col-auto align-self-center">
                    <h5 class="mb-0" data-anchor="data-anchor"><i class="fa fa-shopping-cart" aria-hidden="true"></i><span class="ms-2">Editar venta pieza</span></h5>
                </div>
                <div class="col-auto ms-auto">

                </div>
            </div>
        </div>
        <div class="card-body bg-body-tertiary">
            <form role="form" action="{{ route('ventaPiezas.update',$ventaPieza->id) }}" method="post" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    {{ method_field('PUT') }}
                <div class="tab-content">
                    <div class="box-body">

                        @include('includes.messages')
                        <div class="row">

                            <div class="col-12 col-lg-5">
                                <div class="form-group">
                                    <label for="user_id">Vendedor</label>
                                    <select name="user_id" class="form-control select-simple" required>

                                        @foreach($users as $userId => $user)
                                            <option value="{{ $userId }}"
                                                {{ old('user_id', $ventaPieza->user_id) == $userId ? 'selected' : '' }}>
                                                {{ $user }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-12 col-lg-3">
                                <div class="form-group">
                                    <label for="fecha">Fecha</label>
                                    <input type="date" class="form-control" id="fecha" name="fecha"  value="@if (old('fecha')){{ old('fecha') }}@else{{ ($ventaPieza->fecha)?date('Y-m-d', strtotime($ventaPieza->fecha)):'' }}@endif" readonly required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">


                            <table class="table">
                                <thead>

                                <th>Pieza</th>
                                <th>Sucursal</th>
                                <th>Costo</th>
                                <th>$ min.</th>
                                <th>Cantidad</th>
                                <th>Precio</th>

                                <th><a href="#" class="addRowPieza btn btn-success btn-sm">
                                        <i class="fa fa-plus"></i>
                                    </a></th>

                                </thead>

                                <tbody id="cuerpoPieza">
                                @php
                                    $oldPiezas = old('pieza_id', []);
                                    $oldSucursalIds = old('sucursal_id_item', []);
                                    $oldCostos = old('costo', []);
                                    $oldPreciosMinimos = old('precio_minimo', []);
                                    $oldCantidades = old('cantidad', []);
                                    $oldPrecios = old('precio', []);
                                @endphp

                                @foreach($oldPiezas ?: $ventaPieza->piezas as $i => $pieza)
                                    @php
                                        // Para el caso old, $pieza es el id. Si viene del modelo, es un objeto.
                                        $piezaId = is_object($pieza) ? $pieza->pieza_id : $pieza;

                                        // Para sucursal, costo, precio_minimo, cantidad, precio tratamos igual
                                        $sucursalId = old('sucursal_id_item.' . $i) ?? (is_object($pieza) ? $pieza->sucursal_id : '');
                                        $costo = old('costo.' . $i) ?? (is_object($pieza) ? $pieza->costo : '');
                                        $precioMinimo = old('precio_minimo.' . $i) ?? (is_object($pieza) ? $pieza->precio_minimo : '');
                                        $cantidad = old('cantidad.' . $i) ?? (is_object($pieza) ? $pieza->cantidad : '');
                                        $precio = old('precio.' . $i) ?? (is_object($pieza) ? $pieza->precio : '');
                                    @endphp
                                    <tr data-sucursal-id="{{ $sucursalId  }}">
                                        <td style="width: 25%;">
                                            <select name="pieza_id[]" class="form-control select-simple selectPieza" required>
                                                <option value="">Seleccione...</option>
                                                @foreach($stockPiezasJson as $id => $piezas)
                                                    <option value="{{ $id }}" {{ $piezaId == $id ? 'selected' : '' }}>
                                                        {{ $piezas[0]['codigo'] }} - {{ $piezas[0]['descripcion'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td style="width: 20%;">
                                            <select name="sucursal_id_item[]" class="form-control sucursalSelect" required>
                                                <option value="">Seleccione...</option>
                                                <!-- Las opciones se llenan con JS -->
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" name="costo[]" class="form-control" value="{{ $costo }}">
                                        </td>
                                        <td>
                                            <input type="number" name="precio_minimo[]" class="form-control" value="{{ $precioMinimo }}">
                                        </td>
                                        <td>
                                            <input type="number" name="cantidad[]" class="form-control" value="{{ $cantidad }}">
                                        </td>
                                        <td>
                                            <input type="number" name="precio[]" class="form-control" value="{{ $precio }}">
                                        </td>
                                        <td><a href="#" class="btn btn-danger btn-sm removeRow"><i class="fa fa-times text-white"></i></a></td>
                                    </tr>
                                @endforeach


                                </tbody>




                            </table>
                        </div>

                        <div class="row">
                            <div class="col-12 col-lg-9">
                                <div class="form-group">

                                        <label for="descripcion" class="col-md-12">Descripción</label>
                                </div>
                            </div>

                        </div>

                        <div class="row">
                            <div class="col-12 col-lg-9">
                                <div class="form-group">


                                    <!-- Fila 2: Área de texto -->

                                        <textarea id="descripcion" name="descripcion" class="form-control" rows="3">
                                            {{ old('descripcion', $ventaPieza->descripcion) }}
                                        </textarea>

                                </div>
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-12 col-lg-2">
                                <div class="form-group">
                                    <label for="destino">Destino</label>

                                    <select name="destino" id="destino" class="form-control" required>
                                        <option value="">
                                            Seleccionar...
                                        </option>
                                        @foreach (config('destinos') as $key => $label)
                                            <option value="{{ $key }}" {{ old('destino', $ventaPieza->destino ?? '') == $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>


                                </div>
                            </div>

                        </div>
                        <div class="row" id="divSalon" style="display: none">

                            <!--<div class="col-12 col-lg-4">
                                <div class="form-group">
                                    <label for="cliente">Cliente</label>
                                    <input type="text" class="form-control" id="cliente" name="cliente" placeholder="Cliente" value="{{ old('cliente', $ventaPieza->cliente) }}">
                                </div>
                            </div>
                            <div class="col-12 col-lg-2">
                                <div class="form-group">
                                    <label for="documento">Documento</label>
                                    <input type="text" class="form-control" id="documento" name="documento" placeholder="Documento" value="{{ old('documento', $ventaPieza->documento) }}">
                                </div>
                            </div>
                            <div class="col-12 col-lg-2">
                                <div class="form-group">
                                    <label for="telefono">Teléfono</label>
                                    <input type="text" class="form-control" id="telefono" name="telefono" placeholder="Teléfono" value="{{ old('telefono', $ventaPieza->telefono) }}">
                                </div>
                            </div>
                            <div class="col-12 col-lg-3">
                                <div class="form-group">
                                    <label for="moto">Moto</label>
                                    <input type="text" class="form-control" id="moto" name="moto" placeholder="Moto" value="{{ old('moto', $ventaPieza->moto) }}">
                                </div>
                            </div>-->

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
                                            @elseif(isset($ventaPieza) && $ventaPieza->cliente)
                                                {{-- Mostrar cliente existente en la venta --}}
                                                <option value="{{ $ventaPieza->cliente_id }}" selected>
                                                    {{ $ventaPieza->cliente->full_name_phone }}
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

                        <div class="row" id="divSucursal" style="display: none">

                            <div class="col-12 col-lg-4">
                                <div class="form-group">
                                    <label for="sucursal_id">Sucursal</label>
                                    <select name="sucursal_id" class="form-control select-simple">

                                        @foreach($sucursals as $sucursalId => $sucursal)
                                            <option value="{{ $sucursalId }}" {{ old('sucursal_id', $ventaPieza->sucursal_id) == $sucursalId ? 'selected' : '' }}>{{ $sucursal }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-lg-3">
                                <div class="form-group">
                                    <label for="pedido">Nro. Pedido Reparación</label>
                                    <input type="text" class="form-control" id="pedido_sucursal" name="pedido_sucursal" placeholder="Nro. Pedido Reparación" value="{{ old('pedido', $ventaPieza->pedido) }}">
                                </div>
                            </div>




                        </div>

                        <div class="row" id="divTaller" style="display: none">


                            <div class="col-12 col-lg-3">
                                <div class="form-group">
                                    <label for="pedido">Nro. Pedido Reparación</label>
                                    <input type="text" class="form-control" id="pedido_taller" name="pedido_taller" placeholder="Nro. Pedido Reparación" value="{{ old('pedido', $ventaPieza->pedido) }}">
                                </div>
                            </div>


                            <input type="hidden" name="pedido" id="pedido_hidden" value="{{ old('pedido', $ventaPieza->pedido) }}">


                        </div>

                        <div class="row" style="margin-top: 10px;">
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">Guardar</button>
                                <a href='{{ route('ventaPiezas.index') }}' class="btn btn-warning">Volver</a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- Modal Nuevo Cliente -->
    <div class="modal fade" id="nuevoClienteModal" tabindex="-1" aria-labelledby="nuevoClienteLabel" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen-sm-down modal-xl">
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
                            <div class="col-12 col-md-4">
                                <label class="form-label">Nombre</label>
                                <input type="text" name="nombre" id="nombre" class="form-control" required>
                                <input type="hidden" name="cliente_id" id="cliente_id_hidden">
                            </div>

                            <!-- Documento -->
                            <div class="col-12 col-md-2">
                                <label class="form-label">Documento</label>
                                <input type="text" name="documento" id="documento" class="form-control" required>
                            </div>

                            <!-- CUIL -->
                            <div class="col-12 col-md-3">
                                <label class="form-label">CUIL</label>
                                <input type="text" name="cuil" id="cuil" placeholder="XX-XXXXXXXX-X" class="form-control">
                            </div>

                            <!-- Nacimiento -->
                            <div class="col-12 col-md-3">
                                <label class="form-label">F. Nacimiento</label>
                                <input type="date" name="nacimiento" id="nacimiento" class="form-control" required>
                            </div>

                            <!-- Particular -->
                            <div class="col-4 col-md-1">
                                <label class="form-label">Área</label>
                                <input type="text" name="particular_area" id="particular_area" class="form-control" required>
                            </div>
                            <div class="col-8 col-md-2">
                                <label class="form-label">Particular</label>
                                <input type="text" name="particular" id="particular" class="form-control" required>
                            </div>

                            <!-- Celular -->
                            <div class="col-4 col-md-1">
                                <label class="form-label">Área</label>
                                <input type="text" name="celular_area" id="celular_area" class="form-control" required>
                            </div>
                            <div class="col-8 col-md-2">
                                <label class="form-label">Celular</label>
                                <input type="text" name="celular" id="celular" class="form-control" required>
                            </div>
                            <!-- Email -->
                            <div class="col-12 col-md-5">
                                <label class="form-label">E-mail</label>
                                <input type="email" name="email" id="email" class="form-control" required>
                            </div>
                            <!-- Dirección -->
                            <div class="col-12 col-md-4">
                                <label class="form-label">Calle</label>
                                <input type="text" name="calle" id="calle" class="form-control" required>
                            </div>
                            <div class="col-12 col-md-2">
                                <label class="form-label">Nro</label>
                                <input type="text" name="nro" id="nro" class="form-control" required>
                            </div>
                            <div class="col-12 col-md-2">
                                <label class="form-label">CP</label>
                                <input type="text" name="cp" id="cp" class="form-control" required>
                            </div>

                            @include('includes.select-provincia-localidad')



                            <!-- Nacionalidad -->
                            <div class="col-12 col-md-4">
                                <label class="form-label">Nacionalidad</label>
                                <input type="text" name="nacionalidad" id="nacionalidad" class="form-control" required>
                            </div>

                            <!-- Estado Civil -->
                            <div class="col-12 col-md-3">
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

                            <div class="col-12 col-md-4" id="conyuge-container" style="display: none;">
                                <div class="form-group">
                                    <label for="conyuge">Cónyuge</label>
                                    <input type="text" class="form-control" id="conyuge" name="conyuge" placeholder="Cónyuge" value="{{ old('conyuge') }}" required>
                                </div>
                            </div>

                            <!-- Cómo llegó -->
                            <div class="col-12 col-md-6">
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
                            <div class="col-12 col-md-6">
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
    <!-- /.content-wrapper -->
@endsection
<style>
    .select2-container--default .select2-selection--single .select2-selection__rendered {

        width: 300px !important;
    }
</style>
@section('footerSection')
    <!-- jQuery 3 -->
    <script src="{{ asset('bower_components/jquery/dist/jquery.min.js') }}"></script>
    <!-- Bootstrap 3.3.7 -->
    <script src="{{ asset('bower_components/bootstrap/dist/js/bootstrap.min.js') }}"></script>
    <!-- SlimScroll -->
    <script src="{{ asset('bower_components/jquery-slimscroll/jquery.slimscroll.min.js') }}"></script>
    <!-- FastClick -->
    <script src="{{ asset('bower_components/fastclick/lib/fastclick.js') }}"></script>
    <!-- FastClick -->
    <script src="{{ asset('bower_components/fastclick/lib/fastclick.js') }}"></script>

    <!-- Select2 -->
    <script src="{{ asset('bower_components/select2/dist/js/select2.min.js') }}"></script>
    <script src="{{ asset('bower_components/select2/dist/js/i18n/es.js') }}"></script>

    <!-- Inputmask -->
    <script src="{{ asset('bower_components/inputmask/dist/min/jquery.inputmask.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/combo-provincia-localidad.js') }}"></script>

    <script src="{{ asset('assets/js/confirm-exit.js') }}"></script>


    <!-- page script -->
    <script>
        const stockPiezas = @json($stockPiezasJson);
        var localidadUrl = "{{ url('localidads') }}";
        $(document).ready(function () {
            function toggleDivs() {
                const valor = $('#destino').val();

                // Ocultar todos
                $('#divSalon, #divSucursal, #divTaller').hide();

                // Mostrar el que corresponde
                if (valor === 'Salón') {
                    $('#divSalon').show();
                } else if (valor === 'Sucursal') {
                    $('#divSucursal').show();
                } else if (valor === 'Taller') {
                    $('#divTaller').show();
                }
            }

            // Ejecutar al cargar por si hay uno preseleccionado
            toggleDivs();

            // Ejecutar al cambiar
            $('#destino').on('change', toggleDivs);
            const piezaSelectHtml = `{!! '<select name="pieza_id[]" class="form-control select-simple selectPieza">' !!}
            {!! '<option value=\'\'>Seleccionar...</option>' !!}
            @foreach($stockPiezasJson as $piezaId => $piezas)
            @php
                $first = $piezas[0];
                $label = $first['codigo'] . ' - ' . $first['descripcion'];
            @endphp
            {!! '<option value="'.$piezaId.'">'.$label.'</option>' !!}
            @endforeach
            {!! '</select>' !!}`;

            $('.addRowPieza').on('click', function (e) {
                e.preventDefault();
                addRowPieza();
            });

            function initSimpleSelects(context = document) {
                $(context).find('.select-simple').select2({
                    language: 'es'
                });
            }

            function addRowPieza() {
                const tr = `
            <tr>
                <td style="width: 25%;">${piezaSelectHtml}</td>
                <td style="width: 20%;">
                    <select name="sucursal_id_item[]" class="form-control sucursalSelect">
                        <option value="">Seleccionar...</option>
                    </select>
                </td>
                <td><input type="number" name="costo[]" class="form-control costo" readonly></td>
                <td><input type="number" name="precio_minimo[]" class="form-control precio_minimo" readonly></td>
                <td><input type="number" name="cantidad[]" class="form-control cantidad" required></td>
                <td><input type="number" name="precio[]" class="form-control precio" required></td>
                <td><a href="#" class="btn btn-danger btn-sm removeRow"><i class="fa fa-times text-white"></i></a></td>
            </tr>
        `;
                const $row = $(tr).appendTo('#cuerpoPieza');

                initSimpleSelects($row); // 👈 clave
            }

            $('body').on('click', '.removeRow', function (e) {
                e.preventDefault();
                var confirmDelete = confirm('¿Estás seguro?');

                if (confirmDelete) {
                    $(this).closest('tr').remove();
                }

            });

            $('body').on('change', '.selectPieza', function () {
                const piezaId = $(this).val();
                const row = $(this).closest('tr');
                const sucursalSelect = row.find('.sucursalSelect');
                const costoInput = row.find('.costo');
                const precioMinimoInput = row.find('.precio_minimo');

                sucursalSelect.empty();
                costoInput.val('');
                precioMinimoInput.val('');

                if (piezaId && stockPiezas[piezaId]) {
                    const opciones = stockPiezas[piezaId];

                    sucursalSelect.append('<option value="">Seleccionar...</option>');
                    opciones.forEach(op => {
                        sucursalSelect.append('<option value="' + op.sucursal_id + '">' + op.sucursal_nombre + '</option>');
                    });

                    // Mostramos los datos de la primera sucursal por defecto
                    const first = opciones[0];
                    costoInput.val(first.costo);
                    precioMinimoInput.val(first.precio_minimo);
                }
            });

            // Si querés cambiar los valores de costo y precio mínimo según la sucursal seleccionada:
            $('body').on('change', '.sucursalSelect', function () {
                const sucursalId = $(this).val();
                const row = $(this).closest('tr');
                const piezaId = row.find('.selectPieza').val();
                const costoInput = row.find('.costo');
                const precioMinimoInput = row.find('.precio_minimo');

                if (piezaId && stockPiezas[piezaId]) {
                    const match = stockPiezas[piezaId].find(sp => sp.sucursal_id == sucursalId);
                    if (match) {
                        costoInput.val(match.costo);
                        precioMinimoInput.val(match.precio_minimo);
                    }
                }
            });

            $('#cuerpoPieza tr').each(function () {
                const row = $(this);
                const piezaSelect = row.find('.selectPieza');
                const sucursalSelect = row.find('.sucursalSelect');
                const costoInput = row.find('.costo');
                const precioMinimoInput = row.find('.precio_minimo');

                const sucursalIdGuardada = row.data('sucursal-id');

                // Disparo el change para cargar las sucursales en el select
                piezaSelect.trigger('change');

                // Espera un pequeño delay para que cargue el select de sucursal (ya que se llena dinámicamente)
                setTimeout(() => {
                    if (sucursalIdGuardada) {
                        sucursalSelect.val(sucursalIdGuardada).trigger('change');
                    }
                }, 100);
            });

            $('form').on('submit', function () {
                const destino = $('#destino').val();

                let pedido = '';
                if (destino === 'Sucursal') {
                    pedido = $('#pedido_sucursal').val();
                } else if (destino === 'Taller') {
                    pedido = $('#pedido_taller').val();
                }

                $('#pedido_hidden').val(pedido);
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
