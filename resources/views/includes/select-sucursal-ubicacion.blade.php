<div class="col-12 mt-3">
    <label>Ubicaciones donde se encuentra la pieza</label>

    <table class="table table-bordered mt-2">
        <thead>
        <tr>
            <th>Sucursal</th>
            <th>Ubicación</th>
            <th>
                <a href="#" class="btn btn-success btn-sm addRowUbicacion">
                    <i class="fa fa-plus"></i>
                </a>
            </th>
        </tr>
        </thead>

        <tbody id="cuerpoUbicaciones">
        </tbody>
    </table>
</div>

{{-- Dejar opciones de sucursales para JS --}}
<script>
    window.sucursalesOptions = `
        @foreach($sucursales as $sucursal)
    <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
        @endforeach
    `;
</script>
