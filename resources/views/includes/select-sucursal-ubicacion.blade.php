@php
    $sucursalSeleccionada = old('sucursal_id', $pieza->ubicacion->sucursal_id ?? '');
    $ubicacionSeleccionada = old('ubicacion_id', $pieza->ubicacion_id ?? '');
    $nombreUbicacion = $pieza->ubicacion->nombre ?? null;

@endphp

<div class="col-12 col-lg-5">
    <div class="form-group">
        <label for="sucursal_id">Sucursal</label>
        <select name="sucursal_id" id="sucursal_id"
                class="form-control sucursal-select js-example-basic-single"
                data-old-ubicacion="{{ $ubicacionSeleccionada }}"
                required>
            <option value=""></option>
            @foreach($sucursales as $sucursal)
                <option value="{{ $sucursal->id }}"
                    {{ $sucursalSeleccionada == $sucursal->id ? 'selected' : '' }}>
                    {{ $sucursal->nombre }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<div class="col-12 col-lg-5">
    <div class="form-group">
        <label for="ubicacion_id">Ubicación</label>
        <select id="ubicacion_id" name="ubicacion_id"
                class="form-control ubicacion-select js-example-basic-single" required>
            <option value=""></option>
            @if($ubicacionSeleccionada)
                <option value="{{ $ubicacionSeleccionada }}" selected>
                    {{ $nombreUbicacion ?? 'Ubicación seleccionada' }}
                </option>
            @endif
        </select>
    </div>
</div>
