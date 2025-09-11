@extends('layouts.app')
@section('headSection')

    <link rel="stylesheet" href="{{ asset('bower_components/select2/dist/css/select2.min.css') }}">

@endsection

@section('content')
    <div class="card mb-3">
        <div class="card-header">
            <div class="row flex-between-end">
                <div class="col-auto align-self-center">
                    <h5 class="mb-0" data-anchor="data-anchor"><i class="fa fa-shopping-cart" aria-hidden="true"></i><span class="ms-2">Crear venta pieza</span></h5>
                </div>
                <div class="col-auto ms-auto">

                </div>
            </div>
        </div>
        <div class="card-body bg-body-tertiary">
            <form role="form" action="{{ route('ventaPiezas.store') }}" method="post" >
                {{ csrf_field() }}
                <div class="tab-content">
                    <div class="box-body">

                        @include('includes.messages')
                        <div class="row">

                            <div class="col-12 col-lg-5">
                                <div class="form-group">
                                    <label for="user_id">Vendedor</label>
                                    <select name="user_id" class="form-control js-example-basic-single" disabled>

                                        @foreach($users as $userId => $user)
                                            <option value="{{ $userId }}"
                                                {{ old('user_id', auth()->id()) == $userId ? 'selected' : '' }}>
                                                {{ $user }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="user_id" value="{{ auth()->id() }}">
                                </div>
                            </div>

                            <div class="col-12 col-lg-3">
                                <div class="form-group">
                                    <label for="fecha">Fecha</label>
                                    <input type="date" class="form-control" id="fecha" name="fecha"  value="{{ now()->format('Y-m-d') }}" readonly required>
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
                                @endphp

                                @foreach($oldPiezas as $i => $piezaId)
                                    <tr>
                                        <td style="width: 25%;">
                                            <select name="pieza_id[]" class="form-control js-example-basic-single selectPieza" required>
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
                                                @foreach($sucursals as $sucursalId => $sucursal)
                                                    <option value="{{ $sucursalId }}" {{ old('sucursal_id_item.' . $i) == $sucursalId ? 'selected' : '' }}>
                                                        {{ $sucursal }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" name="costo[]" class="form-control" value="{{ old('costo.' . $i) }}">
                                        </td>
                                        <td>
                                            <input type="number" name="precio_minimo[]" class="form-control" value="{{ old('precio_minimo.' . $i) }}">
                                        </td>
                                        <td>
                                            <input type="number" name="cantidad[]" class="form-control" value="{{ old('cantidad.' . $i) }}">
                                        </td>
                                        <td>
                                            <input type="number" name="precio[]" class="form-control" value="{{ old('precio.' . $i) }}">
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
                                            @if (old('descripcion')){{ old('descripcion') }}@endif
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
                                            <option value="{{ $key }}" {{ old('destino', $ventaPiea->destino ?? '') == $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>


                                </div>
                            </div>

                        </div>
                        <div class="row" id="divSalon" style="display: none">

                            <div class="col-12 col-lg-4">
                                <div class="form-group">
                                    <label for="cliente">Cliente</label>
                                    <input type="text" class="form-control" id="cliente" name="cliente" placeholder="Cliente" value="{{ old('cliente') }}">
                                </div>
                            </div>
                            <div class="col-12 col-lg-2">
                                <div class="form-group">
                                    <label for="documento">Documento</label>
                                    <input type="text" class="form-control" id="documento" name="documento" placeholder="Documento" value="{{ old('documento') }}">
                                </div>
                            </div>
                            <div class="col-12 col-lg-2">
                                <div class="form-group">
                                    <label for="telefono">Teléfono</label>
                                    <input type="text" class="form-control" id="telefono" name="telefono" placeholder="Teléfono" value="{{ old('telefono') }}">
                                </div>
                            </div>
                            <div class="col-12 col-lg-3">
                                <div class="form-group">
                                    <label for="moto">Moto</label>
                                    <input type="text" class="form-control" id="moto" name="moto" placeholder="Moto" value="{{ old('moto') }}">
                                </div>
                            </div>



                        </div>

                        <div class="row" id="divSucursal" style="display: none">

                            <div class="col-12 col-lg-4">
                                <div class="form-group">
                                    <label for="sucursal_id">Sucursal</label>
                                    <select name="sucursal_id" class="form-control js-example-basic-single">

                                        @foreach($sucursals as $sucursalId => $sucursal)
                                            <option value="{{ $sucursalId }}" {{ old('sucursal_id') == $sucursalId ? 'selected' : '' }}>{{ $sucursal }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-lg-3">
                                <div class="form-group">
                                    <label for="pedido">Nro. Pedido Reparación</label>
                                    <input type="text" class="form-control" id="pedido_sucursal" name="pedido_sucursal" placeholder="Nro. Pedido Reparación" value="{{ old('pedido') }}">
                                </div>
                            </div>




                        </div>

                        <div class="row" id="divTaller" style="display: none">


                            <div class="col-12 col-lg-3">
                                <div class="form-group">
                                    <label for="pedido">Nro. Pedido Reparación</label>
                                    <input type="text" class="form-control" id="pedido_taller" name="pedido_taller" placeholder="Nro. Pedido Reparación" value="{{ old('pedido') }}">
                                </div>
                            </div>


                            <input type="hidden" name="pedido" id="pedido_hidden" value="{{ old('pedido') }}">

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


    <script src="{{ asset('assets/js/confirm-exit.js') }}"></script>


    <!-- page script -->
    <script>
        const stockPiezas = @json($stockPiezasJson);
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
            const piezaSelectHtml = `{!! '<select name="pieza_id[]" class="form-control js-example-basic-single selectPieza">' !!}
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
                $('#cuerpoPieza').append(tr);
                $('.js-example-basic-single').select2({
                language: 'es'});
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

                    // Filtrar solo la sucursal del usuario logueado
                    const sucursalUsuarioId = {{ auth()->user()->sucursal_id }};
                    const opcion = opciones.find(op => op.sucursal_id == sucursalUsuarioId);

                    if (opcion) {
                        sucursalSelect.append('<option value="' + opcion.sucursal_id + '">' + opcion.sucursal_nombre + '</option>');
                        costoInput.val(opcion.costo);
                        precioMinimoInput.val(opcion.precio_minimo);
                    }
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

        });
    </script>



@endsection
