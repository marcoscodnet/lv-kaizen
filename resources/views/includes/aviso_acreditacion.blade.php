{{--
    Control informativo de acreditación. NO bloquea la operación.

    Compara lo ACREDITADO (pagos.pagado) contra el importe sugerido del
    documento — no contra el importe que cargó el vendedor — y muestra la
    diferencia a favor o en contra.

    Uso:
        @include('includes.aviso_acreditacion', [
            'pagos'    => $venta->pagos,
            'sugerido' => $precioSugerido,
        ])

    Opcionales:
        'etiqueta' => 'Servicio'   (cómo se llama el importe sugerido, por defecto "Sugerido")
        'totales'  => false        (para no repetir las cajas de totales si la vista ya las tiene)
--}}
@php
    $avisoSugerido   = (float) ($sugerido ?? 0);
    $avisoPagos      = collect($pagos ?? []);
    $avisoCobrado    = (float) $avisoPagos->sum(function ($p) { return (float) $p->monto; });
    $avisoAcreditado = (float) $avisoPagos->sum(function ($p) { return (float) $p->pagado; });
    $avisoDiff       = $avisoAcreditado - $avisoSugerido;
    $avisoEtiqueta   = $etiqueta ?? 'Sugerido';
    $avisoTotales    = $totales ?? true;
    $avisoFmt        = function ($n) { return number_format((float) $n, 2, ',', '.'); };
@endphp

@if($avisoTotales)
    <div class="row mb-2">
        <div class="col-md-3">
            <label>Importe {{ strtolower($avisoEtiqueta) }}</label>
            <input type="text" class="form-control" value="{{ $avisoFmt($avisoSugerido) }}" readonly disabled>
        </div>
        <div class="col-md-3">
            <label>Importe cobrado</label>
            <input type="text" class="form-control" value="{{ $avisoFmt($avisoCobrado) }}" readonly disabled>
        </div>
        <div class="col-md-3">
            <label>Importe acreditado</label>
            <input type="text" class="form-control" value="{{ $avisoFmt($avisoAcreditado) }}" readonly disabled>
        </div>
    </div>
@endif

@if($avisoSugerido > 0)
    @php
        $avisoBase = $avisoEtiqueta . ': $' . $avisoFmt($avisoSugerido)
            . ' · Acreditado: $' . $avisoFmt($avisoAcreditado);
    @endphp
    <div class="row mb-3">
        <div class="col-12">
            @if(abs($avisoDiff) < 0.01)
                <div class="alert alert-success mb-0">Acreditación completa ✓ — {{ $avisoBase }}</div>
            @elseif($avisoDiff < 0)
                <div class="alert alert-warning mb-0">
                    Acreditación parcial — {{ $avisoBase }} · Falta: ${{ $avisoFmt(abs($avisoDiff)) }}
                </div>
            @else
                <div class="alert alert-danger mb-0">
                    Acreditado de más — {{ $avisoBase }} · Excedente: ${{ $avisoFmt($avisoDiff) }}
                </div>
            @endif
        </div>
    </div>
@endif
