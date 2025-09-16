@extends('layouts.app')

@section('headSection')
    <link rel="stylesheet" href="{{ asset('bower_components/select2/dist/css/select2.min.css') }}">
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5><i class="fas fa-edit"></i> Editar Movimiento - Caja #{{ $mov->caja_id }}</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('movimiento_cajas.update', $mov->id) }}" method="POST">
                @csrf
                @method('PUT')
                @include('includes.messages')
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label for="concepto_id" class="form-label">Concepto</label>
                        <select name="concepto_id" id="concepto_id" class="form-control select2" required>
                            <option value="">Seleccione</option>
                            @foreach($conceptos as $concepto)
                                <option value="{{ $concepto->id }}"
                                    {{ $mov->concepto_id == $concepto->id ? 'selected' : '' }}>
                                    {{ $concepto->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label for="medio_id" class="form-label">Medio de Pago</label>
                        <select name="medio_id" id="medio_id" class="form-control select2" required>
                            <option value="">Seleccione</option>
                            @foreach($medios as $medio)
                                <option value="{{ $medio->id }}"
                                    {{ $mov->medio_id == $medio->id ? 'selected' : '' }}>
                                    {{ $medio->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row g-3 mt-2">
                    <div class="col-12 col-md-2">
                        <label for="tipo" class="form-label">Tipo</label>
                        <select name="tipo" id="tipo" class="form-control" required>
                            <option value="">Seleccione</option>
                            <option value="Ingreso" {{ $mov->tipo == 'Ingreso' ? 'selected' : '' }}>Ingreso</option>
                            <option value="Egreso" {{ $mov->tipo == 'Egreso' ? 'selected' : '' }}>Egreso</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-2">
                        <label for="monto" class="form-label">Monto</label>
                        <input type="number" name="monto" id="monto" class="form-control"
                               step="0.01" value="{{ $mov->monto }}" required>
                    </div>
                    <div class="col-12 col-md-2" id="acreditado-container">
                        <label for="acreditado" class="form-label">Acreditado</label>
                        <select name="acreditado" id="acreditado" class="form-control">
                            <option value="">Seleccione</option>
                            <option value="1" {{ old('acreditado', $mov->acreditado ?? '') == '1' ? 'selected' : '' }}>Sí</option>
                            <option value="0" {{ old('acreditado', $mov->acreditado ?? '') == '0' ? 'selected' : '' }}>No</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-3">
                        <label for="referencia" class="form-label">Referencia</label>
                        <input type="text" name="referencia" id="referencia"
                               class="form-control" value="{{ $mov->referencia }}">
                    </div>
                </div>

                <div class="mt-3 d-flex flex-column flex-md-row gap-2">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                    <a href='{{ route('cajas.show', $mov->caja_id) }}' class="btn btn-warning">Volver</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('footerSection')
    <script src="{{ asset('bower_components/select2/dist/js/select2.full.min.js') }}"></script>
    <script>
        $(document).ready(function() {
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
        });


    </script>

@endsection
