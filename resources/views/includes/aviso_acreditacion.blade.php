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
        // El neteo de la operación va contra lo COBRADO, no contra lo acreditado.
        // Que un cheque o un crédito todavía no se hayan acreditado es una
        // cuestión de tiempo, no una diferencia de la venta.
        $avisoDiffCobro = $avisoCobrado - $avisoSugerido;
        $avisoBase = $avisoEtiqueta . ': $' . $avisoFmt($avisoSugerido)
            . ' · Cobrado: $' . $avisoFmt($avisoCobrado);

        // Lo acreditado se informa aparte, como estado de la cobranza
        $avisoFaltaAcreditar = $avisoCobrado - $avisoAcreditado;
    @endphp
    <div class="row mb-3">
        <div class="col-12">
            @if(abs($avisoDiffCobro) < 0.01)
                <div class="alert alert-success mb-0">Cobro completo ✓ — {{ $avisoBase }}</div>
            @elseif($avisoDiffCobro < 0)
                <div class="alert alert-danger mb-0">
                    <strong>Faltan ${{ $avisoFmt(abs($avisoDiffCobro)) }}</strong> — {{ $avisoBase }}
                </div>
            @else
                <div class="alert alert-warning mb-0">
                    Cobrado de más — {{ $avisoBase }} · Excedente: ${{ $avisoFmt($avisoDiffCobro) }}
                </div>
            @endif

            {{-- Estado de la acreditación: informativo, es cuestión de tiempo --}}
            <div class="mt-2 small text-muted">
                Acreditado: ${{ $avisoFmt($avisoAcreditado) }} de ${{ $avisoFmt($avisoCobrado) }} cobrados.
                @if($avisoFaltaAcreditar > 0.01)
                    Pendiente de acreditar: ${{ $avisoFmt($avisoFaltaAcreditar) }}.
                @elseif($avisoCobrado > 0)
                    Todo acreditado.
                @endif
            </div>
        </div>
    </div>
@endif
