{{--
    Conceptos que se cobran junto con la moto: patentamiento, seguro, casco.

    Se cargan en la misma pantalla de la venta y por dentro se guardan como una
    venta de artículos colgada de esta venta, sin pagos propios.

    Uso:
        @include('includes.articulos_venta', [
            'articulosJson' => $articulosJson,   // catálogo (no hace falta en solo lectura)
            'items'         => optional($venta->ventaArticulos)->piezas,
            'soloLectura'   => false,
        ])
--}}
@php
    $artSoloLectura = $soloLectura ?? false;
    $artItems       = collect($items ?? []);
    $artCatalogo    = $articulosJson ?? collect();

    // Al volver de un error de validación mandan los valores que se habían cargado
    $artOld = [];
    foreach ((array) old('pieza_id', []) as $iArt => $piezaIdOld) {
        if (empty($piezaIdOld)) { continue; }
        $artOld[] = [
            'pieza_id'    => $piezaIdOld,
            'sucursal_id' => old('sucursal_id_item.' . $iArt),
            'cantidad'    => old('cantidad.' . $iArt),
            'precio'      => old('precio_articulo.' . $iArt),
        ];
    }

    // old() manda; si no hay, se muestra lo que está guardado
    $artFilas = $artOld ?: $artItems->map(function ($p) {
        return [
            'pieza_id'    => $p->pieza_id,
            'sucursal_id' => $p->sucursal_id,
            'cantidad'    => $p->cantidad,
            'precio'      => $p->precio,
        ];
    })->all();

    // Importe de la moto, para mostrar el total a cobrar de la operación
    $artPrecioUnidad = (float) ($precioUnidad ?? 0);

    // Precio de lista, solo para avisar si se cobró por debajo. Nunca bloquea.
    $artSugerido = (float) ($precioSugerido ?? 0);

    // Sucursal de la operación: es la que llevan los ítems por defecto.
    // El vendedor no la puede cambiar; el administrador sí.
    $artSucursalId     = $sucursalId ?? null;
    $artSucursalNombre = $sucursalNombre ?? '';
    $artEsAdmin        = auth()->check() && auth()->user()->hasRole('Administrador');
    $artSucursalFija   = $artSoloLectura || !$artEsAdmin;

    $artTotal = collect($artFilas)->sum(function ($f) {
        return (float) ($f['precio'] ?? 0) * (float) ($f['cantidad'] ?: 1);
    });

    // Los importes llegan crudos ("300000.00"), tanto de la base como de old()
    // porque AutoNumeric envía sin formato. Hay que pintarlos en formato local
    // antes de mostrarlos: con el punto como separador de miles, un valor crudo
    // se leería multiplicado por cien.
    $artImporte = function ($valor) {
        return number_format((float) $valor, 2, ',', '.');
    };
@endphp

<div class="card mt-4">
    <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Conceptos adicionales</h5>
        @unless($artSoloLectura)
            <button type="button" id="addRowArticulo" class="btn btn-light btn-sm">
                <i class="fa fa-plus"></i> Agregar
            </button>
        @endunless
    </div>
    <div class="card-body">
        <p class="text-muted small">
            Patentamiento, seguro, accesorios y todo lo que se cobre junto con la moto.
            Se suma al importe a cobrar de esta venta.
            @if($artSucursalFija && !$artSoloLectura)
                Los artículos salen de la sucursal de la venta.
            @endif
        </p>

        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 40%;">Concepto</th>
                        <th style="width: 20%;">Sucursal</th>
                        <th style="width: 12%;">Cantidad</th>
                        <th style="width: 18%;">Importe</th>
                        @unless($artSoloLectura)<th style="width: 5%;"></th>@endunless
                    </tr>
                </thead>
                <tbody id="cuerpoArticulo">
                    @forelse($artFilas as $iFila => $fila)
                        @php
                            $opcionesSucursal = $artCatalogo[$fila['pieza_id']] ?? collect();
                        @endphp
                        <tr>
                            <td>
                                <select name="pieza_id[]" class="form-control selectArticulo" {{ $artSoloLectura ? 'disabled' : '' }}>
                                    <option value="">Seleccionar...</option>
                                    @foreach($artCatalogo as $piezaId => $opciones)
                                        @php $primera = $opciones[0]; @endphp
                                        <option value="{{ $piezaId }}" {{ $fila['pieza_id'] == $piezaId ? 'selected' : '' }}>
                                            {{ trim($primera['codigo'] . ' - ' . $primera['descripcion']) }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            @php
                                // La sucursal no se elige: es la de la venta. No se puede
                                // vender un artículo del depósito de otra sucursal.
                                $filaSucursalId = $fila['sucursal_id'] ?: $artSucursalId;
                                $filaSucursalNombre = optional($opcionesSucursal->firstWhere('sucursal_id', $filaSucursalId))['sucursal_nombre']
                                    ?? $artSucursalNombre;
                            @endphp
                            <td>
                                @if($artSucursalFija)
                                    @unless($artSoloLectura)
                                        <input type="hidden" name="sucursal_id_item[]" class="sucursalArticuloId" value="{{ $filaSucursalId }}">
                                    @endunless
                                    <input type="text" class="form-control sucursalArticuloNombre"
                                           value="{{ $filaSucursalNombre }}" readonly disabled>
                                @else
                                    {{-- El administrador sí puede tomar de otra sucursal --}}
                                    <select name="sucursal_id_item[]" class="form-control sucursalArticulo">
                                        @foreach($opcionesSucursal as $opcion)
                                            <option value="{{ $opcion['sucursal_id'] }}" {{ $filaSucursalId == $opcion['sucursal_id'] ? 'selected' : '' }}>
                                                {{ $opcion['sucursal_nombre'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                @endif
                            </td>
                            <td>
                                <input type="number" name="cantidad[]" class="form-control cantidadArticulo" min="1"
                                       value="{{ $fila['cantidad'] ?: 1 }}" {{ $artSoloLectura ? 'disabled' : '' }}>
                            </td>
                            <td>
                                <input type="text" name="precio_articulo[]" class="form-control formato-numero precioArticulo"
                                       value="{{ $artImporte($fila['precio']) }}" {{ $artSoloLectura ? 'disabled' : '' }}>
                            </td>
                            @unless($artSoloLectura)
                                <td>
                                    <a href="#" class="btn btn-danger btn-sm removeRowArticulo"><i class="fa fa-times text-white"></i></a>
                                </td>
                            @endunless
                        </tr>
                    @empty
                        @if($artSoloLectura)
                            <tr><td colspan="4" class="text-muted">Sin conceptos adicionales.</td></tr>
                        @endif
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="row mt-3">
            <div class="col-md-4 offset-md-4">
                <label>Total conceptos</label>
                <input type="text" id="totalArticulos" class="form-control"
                       value="{{ number_format((float) $artTotal, 2, ',', '.') }}" readonly disabled>
            </div>
            <div class="col-md-4">
                <label class="fw-bold">Importe a cobrar</label>
                <input type="text" id="totalACobrar" class="form-control fw-bold"
                       data-precio-unidad="{{ (float) $artPrecioUnidad }}"
                       data-sugerido="{{ (float) $artSugerido }}"
                       value="{{ number_format($artPrecioUnidad + (float) $artTotal, 2, ',', '.') }}" readonly disabled>
                <small class="text-muted">Moto + conceptos. Es contra este número que se controla lo acreditado.</small>
            </div>
        </div>

        {{-- Aviso si la moto se cobra por debajo del precio de lista. No bloquea. --}}
        <div class="row mt-2">
            <div class="col-12">
                @if($artSoloLectura)
                    {{-- En la lupa no corre el JS de la grilla: se resuelve acá --}}
                    @if($artSugerido > 0 && $artPrecioUnidad > 0 && $artPrecioUnidad < $artSugerido - 0.01)
                        <div class="alert alert-warning mb-0">
                            La moto se cobró por debajo del sugerido — Sugerido: ${{ $artImporte($artSugerido) }}
                            · Se cobró: ${{ $artImporte($artPrecioUnidad) }}
                            · Diferencia: ${{ $artImporte($artSugerido - $artPrecioUnidad) }}
                        </div>
                    @endif
                @else
                    <div id="avisoImporteMoto" class="alert alert-warning mb-0" style="display:none"></div>
                @endif
            </div>
        </div>

        @unless($artSoloLectura)
            {{-- Neteo de la operación: lo cargado en pagos contra el importe a
                 cobrar. Si falta, NO deja confirmar. Si sobra, avisa y deja. --}}
            <div class="row mt-2">
                <div class="col-12">
                    <div id="avisoCobroVenta" class="alert mb-0" style="display:none"></div>
                </div>
            </div>
        @endunless
    </div>
</div>

@unless($artSoloLectura)
    <script>
        // Catálogo de conceptos, indexado por artículo: cada uno trae las
        // sucursales donde se puede tomar. Los que no llevan stock vienen con
        // todas las sucursales activas.
        window.articulosCatalogo = @json($artCatalogo);

        // El vendedor toma de su sucursal y no la puede cambiar; el administrador sí.
        window.articulosSucursalFija = {{ $artSucursalFija ? 'true' : 'false' }};
    </script>
@endunless
