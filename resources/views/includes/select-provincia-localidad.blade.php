@php
    $provinciaSeleccionada = old('provincia_id', $sucursal->localidad->provincia_id ?? $cliente->localidad->provincia_id ?? '');

    $localidadSeleccionada = old('localidad_id', $sucursal->localidad_id ?? $cliente->localidad_id ?? '');
    $nombreLocalidad = $sucursal->localidad->nombre ?? $cliente->localidad->nombre ?? null;
@endphp
<div class="col-lg-offset-3 col-lg-6 col-md-2">
    <div class="form-group">
        <label for="email">Provincia</label>
        <select name="provincia_id" class="form-control provincia-select js-example-basic-single"
                data-old-localidad="{{ $localidadSeleccionada }}">
            <option value=""></option>
            @foreach($provincias as $provinciaId => $provincia)
                <option value="{{ $provinciaId }}" {{ $provinciaSeleccionada == $provinciaId ? 'selected' : '' }}>
                    {{ $provincia }}
                </option>
            @endforeach
        </select>
    </div>
</div>
<div class="col-lg-offset-3 col-lg-6 col-md-2">
    <div class="form-group">
        <label for="localidad">Localidad</label>
        <select id="localidad" name="localidad_id" class="form-control localidad-select js-example-basic-single">
            <option value=""></option>
            @if($localidadSeleccionada)
                <option value="{{ $localidadSeleccionada }}" selected>{{ $nombreLocalidad ?? 'Localidad seleccionada' }}</option>
            @endif
        </select>
    </div>
</div>
