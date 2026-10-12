@extends('layouts.app')

@section('headSection')
    <link rel="stylesheet" href="{{ asset('bower_components/select2/dist/css/select2.min.css') }}">
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5><i class="fas fa-plus"></i> Registrar Movimiento - Caja #{{ $caja->id }}</h5>
        </div>
        @include('includes.messages')
        <div class="card-body">
            <form action="{{ route('movimiento_cajas.store') }}" method="POST">
                @csrf
                <input type="hidden" name="caja_id" value="{{ $caja->id }}">

                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label for="concepto_id" class="form-label">Concepto</label>
                        <select name="concepto_id" id="concepto_id" class="form-control select2" required>
                            <option value="">Seleccione</option>
                            @foreach($conceptos as $concepto)
                                <option value="{{ $concepto->id }}" data-senia="{{ $concepto->nombre === \App\Models\Senia::CONCEPTO ? 1 : 0 }}" {{ old('concepto_id') == $concepto->id ? 'selected' : '' }}>
                                    {{ $concepto->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-4">
                        <label for="entidad_id" class="form-label">Forma de pago</label>
                        <select name="entidad_id" id="entidad_id" class="form-control select2">
                            <option value="">Seleccione</option>
                            @foreach($entidads as $entidad)
                                <option value="{{ $entidad->id }}" {{ old('entidad_id') == $entidad->id ? 'selected' : '' }}>
                                    {{ $entidad->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Seña: el ingreso queda atado a un cliente y después se aplica a su venta --}}
                <div class="row g-3 mt-2" id="senia-container" style="display:none;">
                    <div class="col-12 col-md-8">
                        <label for="cliente_id" class="form-label">Cliente que seña</label>
                        <div class="d-flex gap-2">
                            <div class="flex-grow-1">
                                <select name="cliente_id" id="cliente_id" class="form-control">
                                    @if(old('cliente_id'))
                                        <option value="{{ old('cliente_id') }}" selected>{{ optional(\App\Models\Cliente::find(old('cliente_id')))->nombre }}</option>
                                    @endif
                                </select>
                            </div>
                            @can('cliente-crear')
                                <a href="{{ route('clientes.create') }}" target="_blank" class="btn btn-outline-success" title="Alta de cliente (se abre en otra pestaña)">
                                    <i class="fas fa-user-plus"></i>
                                </a>
                            @endcan
                        </div>
                        <small class="text-muted">La seña queda pendiente y se aplica como pago cuando se hace la venta a este cliente.</small>
                    </div>
                </div>

                <div class="row g-3 mt-2">
                    <div class="col-12 col-md-2">
                        <label for="tipo" class="form-label">Tipo</label>
                        <select name="tipo" id="tipo" class="form-control" required>
                            <option value="">Seleccione</option>
                            <option value="Ingreso" {{ old('tipo') == 'Ingreso' ? 'selected' : '' }}>Ingreso</option>
                            <option value="Egreso" {{ old('tipo') == 'Egreso' ? 'selected' : '' }}>Egreso</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-2">
                        <label for="monto" class="form-label">Monto</label>
                        <input type="text" name="monto" id="monto" class="form-control formato-numero" step="0.01" value="{{ old('monto') }}" required>
                    </div>

                    <div class="col-12 col-md-2" id="acreditado-container">
                        <label for="acreditado" class="form-label">Acreditado</label>
                        <select name="acreditado" id="acreditado" class="form-control">
                            <option value="">Seleccione</option>
                            <option value="1" {{ old('acreditado', $mov->acreditado ?? '') == '1' ? 'selected' : '' }}>Sí</option>
                            <option value="0" {{ old('acreditado', $mov->acreditado ?? '') == '0' ? 'selected' : '' }}>No</option>
                        </select>
                    </div>


                    <div class="col-12 col-md-2">
                        <label for="referencia" class="form-label">Referencia</label>
                        <input type="text" name="referencia" id="referencia" class="form-control" value="{{ old('referencia') }}">
                    </div>
                </div>

                <div class="mt-3 d-flex flex-column flex-md-row gap-2">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                    <a href="{{ route('cajas.show', $caja->id) }}" class="btn btn-warning">Volver</a>
                </div>

            </form>
        </div>
    </div>
@endsection

@section('footerSection')
    <script src="{{ asset('bower_components/select2/dist/js/select2.full.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/autonumeric@4.10.5/dist/autoNumeric.min.js"></script>
    <script>
        $(document).ready(function() {
            new AutoNumeric.multiple('.formato-numero', {
                digitGroupSeparator: '.',
                decimalCharacter: ',',
                decimalPlaces: 2,
                unformatOnSubmit: true
            });
            $('.select2').select2({ width: '100%' });
            function toggleAcreditado() {
                if ($('#tipo').val() === 'Ingreso') {
                    $('#acreditado-container').show();
                    $('#acreditado').prop('required', true);
                } else {
                    $('#acreditado-container').hide();
                    $('#acreditado').prop('required', false);
                    $('#acreditado').val('');
                }
            }

            $('#tipo').change(toggleAcreditado);
            toggleAcreditado(); // Ejecutar al cargar la página

            // Seña: pide cliente, fuerza Ingreso y forma de pago
            $('#cliente_id').select2({
                width: '100%',
                minimumInputLength: 3,
                language: 'es',
                placeholder: 'Buscar cliente por nombre o documento',
                ajax: {
                    url: '{{ route("cliente.search") }}',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) { return { search: params.term }; },
                    processResults: function (response) { return { results: response }; },
                    cache: true
                }
            });

            function toggleSenia() {
                var esSenia = $('#concepto_id option:selected').data('senia') == 1;
                $('#senia-container').toggle(esSenia);
                $('#cliente_id').prop('required', esSenia);
                $('#entidad_id').prop('required', esSenia);
                if (esSenia) {
                    $('#tipo').val('Ingreso').trigger('change');
                    $('#tipo option[value="Egreso"]').prop('disabled', true);
                } else {
                    $('#tipo option[value="Egreso"]').prop('disabled', false);
                }
            }
            $('#concepto_id').on('change', toggleSenia);
            toggleSenia();
        });
    </script>
@endsection
