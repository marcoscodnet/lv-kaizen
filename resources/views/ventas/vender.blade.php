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
                        <span class="ms-2">Vender unidad</span>
                    </h5>
                </div>
            </div>
        </div>
        <div class="card-body bg-body-tertiary">
            <form role="form" action="{{ route('ventas.store') }}" method="post">
                {{ csrf_field() }}
                <div class="tab-content">
                    <div class="box-body">
                        @include('includes.messages')

                        {{-- Datos de la unidad --}}
                        <div class="row">
                            <div class="col-lg-5">
                                <div class="form-group">
                                    <label for="producto">Producto</label>
                                    <input type="hidden" id="unidad_id" name="unidad_id" value="{{ $unidad->id }}">
                                    <input type="text" class="form-control" id="producto" name="producto"
                                           value="{{ isset($unidad->producto) ? $unidad->producto->tipounidad->nombre : '' }} {{ isset($unidad->producto) ? $unidad->producto->marca->nombre : '' }} {{ isset($unidad->producto) ? $unidad->producto->modelo->nombre : '' }} {{ isset($unidad->producto) ? $unidad->producto->color->nombre : '' }}"
                                           readonly>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label for="motor">Motor</label>
                                    <input type="text" class="form-control" id="motor" name="motor" value="{{ $unidad->motor }}" readonly>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label for="cuadro">Cuadro</label>
                                    <input type="text" class="form-control" id="cuadro" name="cuadro" value="{{ $unidad->cuadro }}" readonly>
                                </div>
                            </div>
                        </div>

                        {{-- Fecha, vendedor y sucursal --}}
                        <div class="row">
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label for="fecha">Fecha</label>
                                    <input type="date" class="form-control" id="fecha" name="fecha"
                                           value="{{ now()->format('Y-m-d') }}" readonly required>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label for="user_id">Vendedor</label>
                                    <select name="user_id" id="user_id" class="form-control js-example-basic-single" required>
                                        @foreach($users as $userId => $user)
                                            <option value="{{ $userId }}" {{ old('user_id', auth()->id()) == $userId ? 'selected' : '' }}>
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
                                            <option value="{{ $sucursalId }}" {{ old('sucursal_id') == $sucursalId ? 'selected' : '' }}>
                                                {{ $sucursal }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Cliente --}}
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group d-flex align-items-end gap-2">
                                    <div class="flex-grow-1">
                                        <label for="cliente_id">Cliente</label>
                                        <select name="cliente_id" id="cliente_id" class="form-control js-example-basic-single" required></select>
                                    </div>
                                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#nuevoClienteModal">
                                        <i class="fa fa-plus"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label for="precio">Importe sugerido</label>
                                    <input type="text" class="form-control" id="precio" name="precio"
                                           value="{{ isset($unidad->producto) ? $unidad->producto->precio : '' }}" readonly>
                                </div>
                            </div>
                        </div>

                        {{-- Botones --}}
                        <div class="row mt-3">
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">Guardar</button>
                                <a href='{{ route('ventas.unidads') }}' class="btn btn-warning">Volver</a>
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
                                <input type="text" name="nombre" class="form-control" required>
                            </div>

                            <!-- Documento -->
                            <div class="col-md-2 mb-3">
                                <label class="form-label">Documento</label>
                                <input type="text" name="documento" class="form-control" required>
                            </div>

                            <!-- CUIL -->
                            <div class="col-md-3 mb-3">
                                <label class="form-label">CUIL</label>
                                <input type="text" name="cuil" placeholder="XX-XXXXXXXX-X" class="form-control">
                            </div>

                            <!-- Nacimiento -->
                            <div class="col-md-3 mb-3">
                                <label class="form-label">F. Nacimiento</label>
                                <input type="date" name="nacimiento" class="form-control" required>
                            </div>

                            <!-- Particular -->
                            <div class="col-md-1 mb-3">
                                <label class="form-label">Área</label>
                                <input type="text" name="particular_area" class="form-control" required>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="form-label">Particular</label>
                                <input type="text" name="particular" class="form-control" required>
                            </div>

                            <!-- Celular -->
                            <div class="col-md-1 mb-3">
                                <label class="form-label">Área</label>
                                <input type="text" name="celular_area" class="form-control" required>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="form-label">Celular</label>
                                <input type="text" name="celular" class="form-control" required>
                            </div>
                            <!-- Email -->
                            <div class="col-md-5 mb-3">
                                <label class="form-label">E-mail</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <!-- Dirección -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Calle</label>
                                <input type="text" name="calle" class="form-control" required>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="form-label">Nro</label>
                                <input type="text" name="nro" class="form-control" required>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="form-label">CP</label>
                                <input type="text" name="cp" class="form-control" required>
                            </div>

                                @include('includes.select-provincia-localidad')



                            <!-- Nacionalidad -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nacionalidad</label>
                                <input type="text" name="nacionalidad" class="form-control" required>
                            </div>

                            <!-- Estado Civil -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Estado Civil</label>
                                <select name="estado_civil" class="form-control" required>
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

                            <!-- Cómo llegó -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Cómo llegó? *</label>
                                <select name="llego" class="form-control" required>
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
                                <label class="form-label">Condición IVA *</label>
                                <select name="iva" class="form-control" required>
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

    <script src="{{ asset('bower_components/inputmask/dist/min/jquery.inputmask.bundle.min.js') }}"></script>

    <script src="{{ asset('assets/js/combo-provincia-localidad.js') }}"></script>

    <script>
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        var localidadUrl = "{{ url('localidads') }}";

        $(document).ready(function () {
            // InputMask
            $('#cuil').inputmask('99-99999999-9', { placeholder: 'XX-XXXXXXXX-X' });

            // Inicializar Select2 básico
            $('.js-example-basic-single').each(function () {
                if ($(this).hasClass("select2-hidden-accessible")) {
                    $(this).select2('destroy'); // destruir si ya estaba inicializado
                }
                $(this).select2({ width: '100%' });
            });

            // Select2 para clientes con búsqueda AJAX
            $('#cliente_id').select2({
                minimumInputLength: 3,
                ajax: {
                    url: '{{ route("cliente.search") }}',
                    type: "get",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return { search: params.term };
                    },
                    processResults: function (response) {
                        return { results: response };
                    },
                    cache: true
                }
            });

            // Guardar nuevo cliente
            $('#formNuevoCliente').submit(function (e) {
                e.preventDefault();
                $.ajax({
                    url: '{{ route("clientes.quickstore") }}',
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function (cliente) {
                        $('#nuevoClienteModal').modal('hide');
                        var newOption = new Option(cliente.text, cliente.id, true, true);
                        $('#cliente_id').append(newOption).trigger('change');
                        $('#formNuevoCliente')[0].reset();
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

            // Inicializar Select2 de provincias y localidades dentro del modal
            $('#nuevoClienteModal').on('shown.bs.modal', function () {
                var $provincia = $('#provincia_id');

                // Destruir si ya estaba inicializado
                if ($provincia.hasClass('select2-hidden-accessible')) {
                    $provincia.select2('destroy');
                }

                $provincia.select2({
                    theme: 'bootstrap-5',
                    dropdownParent: $('#nuevoClienteModal'),
                    width: '100%',
                    placeholder: 'Seleccione una provincia'
                });

                // Trigger change si ya hay valor
                if ($provincia.val()) {
                    $provincia.trigger('change');
                }

                // Cargar localidades dependientes
                $provincia.off('change').on('change', function () {
                    var provinciaId = $(this).val();
                    var $localidad = $('#localidad_id');

                    // Destruir y vaciar antes de cargar nuevas
                    if ($localidad.hasClass('select2-hidden-accessible')) {
                        $localidad.select2('destroy');
                    }
                    $localidad.empty();

                    if (provinciaId) {
                        $.ajax({
                            url: localidadUrl + '/' + provinciaId,
                            type: 'GET',
                            dataType: 'json',
                            success: function (data) {
                                var localidades = data.map(function (loc) {
                                    return { id: loc.id, text: loc.nombre };
                                });

                                $localidad.select2({
                                    theme: 'bootstrap-5',
                                    dropdownParent: $('#nuevoClienteModal'),
                                    width: '100%',
                                    data: localidades,
                                    placeholder: 'Seleccione una localidad'
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>


@endsection
