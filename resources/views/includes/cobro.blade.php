@php
    // Default context: 'vendedor' if not specified
    $contexto = $contexto ?? 'vendedor';
    $esAuditor = $contexto === 'auditor';
    $esVendedor = $contexto === 'vendedor';

    // Las filas de pago las arma cobro.js, así que un error de validación las
    // borraba todas. Se pasan los valores de old() para que las reconstruya
    // con lo que el usuario ya había cargado.
    // Ojo: los comprobantes adjuntos no se pueden restaurar (el navegador no
    // deja re-armar un input file), hay que volver a subirlos.
    $pagosOld = [];
    foreach ((array) old('entidad_id', []) as $iOld => $entidadIdOld) {
        $pagosOld[] = [
            'entidad_id'    => $entidadIdOld,
            'monto'         => old('monto.' . $iOld),
            'fecha_pago'    => old('fecha_pago.' . $iOld),
            'detalle'       => old('detalle.' . $iOld),
            'observaciones' => old('observaciones.' . $iOld),
            'pagado'        => old('pagado.' . $iOld),
            'contadora'     => old('contadora.' . $iOld),
        ];
    }
@endphp

<script>
    window.pagosOld = @json($pagosOld);
</script>

{{-- Condición de venta: Contado / Crédito (solo vendedor) --}}
@if($esVendedor)
    <div class="row">
        <div class="col-lg-3">
            <div class="form-group">
                <label for="forma">Condición de venta</label>
                <select name="forma" id="forma" class="form-control">
                    <option value="">Seleccionar...</option>
                    @foreach (config('formas') as $key => $label)
                        <option value="{{ $key }}" {{ old('forma', $formaActual ?? '') == $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
@endif

<div class="row mb-2 mt-2">
    <div class="col">
        @if($esVendedor)
            <button type="button" id="addItemPago" class="btn btn-success btn-sm" style="display:none">
                <i class="fa fa-plus"></i> Agregar pago
            </button>
        @endif
    </div>
</div>

<div id="cuerpoPago" style="display:none">
    @isset($pagosExistentes)
        @foreach($pagosExistentes as $i => $pago)
            @php
                $entidadPago = $entidads->firstWhere('id', $pago->entidad_id);
                $esEfectivo = $entidadPago && $entidadPago->tangible;
                $uid = 'e' . $i;
            @endphp
            <div class="card p-3 mb-3 pago-item" data-uid="{{ $uid }}">
                <input type="hidden" name="pago_uid[]" value="{{ $uid }}">
                <div class="row">
                    <div class="col-md-4">
                        <label>Forma de pago</label>
                        <select name="entidad_id[]" class="form-control js-pago-select"
                            {{ $esAuditor ? 'disabled' : 'required' }}>
                            @foreach($entidads as $entidad)
                                <option value="{{ $entidad->id }}"
                                        data-autorizacion="{{ $entidad->autorizacion }}"
                                        data-tangible="{{ $entidad->tangible }}"
                                    {{ $pago->entidad_id == $entidad->id ? 'selected' : '' }}>
                                    {{ $entidad->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @if($esAuditor)
                            {{-- disabled selects don't submit, send via hidden --}}
                            <input type="hidden" name="entidad_id[]" value="{{ $pago->entidad_id }}">
                        @endif
                    </div>
                    <div class="col-md-3">
                        <label>Importe</label>
                        <input type="text" name="monto[]" class="form-control formato-numero"
                               value="{{ old('monto.'.$i, $pago->monto) }}"
                            {{ $esAuditor ? 'readonly' : 'required' }}>
                    </div>
                    <div class="col-md-3">
                        <label class="labelFechaPago">Fecha Pago</label>
                        <input type="date" name="fecha_pago[]" class="form-control"
                               value="{{ old('fecha_pago.'.$i, $pago->fecha ? \Carbon\Carbon::parse($pago->fecha)->format('Y-m-d') : '') }}"
                            {{ $esAuditor ? 'readonly' : 'required' }}>
                    </div>

                    {{-- Campos del auditor: solo visibles en contexto auditor --}}
                    <div class="col-md-2 campos-auditor" style="display: {{ $esAuditor ? 'block' : 'none' }};">
                        <label>Acreditado</label>
                        <input type="text" name="pagado[]" class="form-control formato-numero"
                               value="{{ old('pagado.'.$i, $pago->pagado) }}">
                    </div>
                </div>

                <div class="row mt-2 campos-auditor" style="display: {{ $esAuditor ? 'flex' : 'none' }};">
                    <div class="col-md-3">
                        <label>Fecha Contadora</label>
                        <input type="date" name="contadora[]" class="form-control"
                               value="{{ old('contadora.'.$i, $pago->contadora ? \Carbon\Carbon::parse($pago->contadora)->format('Y-m-d') : '') }}">
                    </div>
                </div>

                {{-- Comprobantes: visibles salvo efectivo --}}
                <div class="row mt-2 comprobante-wrapper" style="display: {{ $esEfectivo ? 'none' : 'flex' }};">
                    <div class="col-md-12">
                        <label>Comprobantes</label>

                        @if($pago->comprobantes && $pago->comprobantes->count())
                            <div class="d-flex flex-wrap gap-2 mb-2 comprobante-actual">
                                @foreach($pago->comprobantes as $comp)
                                    @php $ext = strtolower(pathinfo($comp->path, PATHINFO_EXTENSION)); @endphp
                                    <a href="{{ asset($comp->path) }}" target="_blank" class="border rounded p-1 text-center" style="text-decoration:none;">
                                        @if(in_array($ext, ['jpg','jpeg','png']))
                                            <img src="{{ asset($comp->path) }}" style="max-width:120px; max-height:90px; display:block;">
                                        @else
                                            <span class="badge bg-secondary">📄 Ver PDF</span>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        @endif

                        @if($esVendedor)
                            <div class="d-flex gap-2 align-items-start flex-wrap">
                                <div>
                                    <input type="file" name="comprobantes_{{ $uid }}[]" multiple data-uid="{{ $uid }}"
                                           class="form-control form-control-sm comprobante-file"
                                           accept="image/jpeg,image/png,application/pdf">
                                    <small class="text-muted">JPG, PNG o PDF (max 5MB c/u). Podés agregar varios.</small>
                                </div>
                                <div>
                                    <button type="button" class="btn btn-sm btn-primary btn-capturar-comprobante">
                                        📸 Capturar
                                    </button>
                                </div>
                            </div>
                            <div class="comprobante-previews d-flex flex-wrap gap-2 mt-2"></div>
                        @endif
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col-5">
                        <label>Observaciones vendedor</label>
                        <textarea name="detalle[]" class="form-control" rows="2"
                                  {{ $esAuditor ? 'readonly' : '' }}>{{ old('detalle.'.$i, $pago->detalle) }}</textarea>
                    </div>
                    <div class="col-5">
                        <label>Observaciones</label>
                        <textarea name="observaciones[]" class="form-control" rows="2"
                                  {{ $esAuditor ? 'readonly' : '' }}>{{ old('observaciones.'.$i, $pago->observacion) }}</textarea>
                    </div>
                    @if($esVendedor)
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="button" class="btn btn-danger btn-sm removeItemPago">
                                <i class="fa fa-times"></i>
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    @endisset
</div>

<div class="row mt-2" id="totalesPago" style="display:none">
    <div class="col-md-3">
        <label>Total importe</label>
        <input type="text" id="totalMonto" name="totalMonto" class="form-control formato-numero" value="0" readonly>
    </div>
    <div class="col-md-3 campos-auditor" style="display: {{ $esAuditor ? 'block' : 'none' }};">
        <label>Total acreditado</label>
        <input type="text" id="totalAcreditado" name="totalAcreditado" class="form-control formato-numero" value="0" readonly>
    </div>
</div>

{{-- Modal de captura con cámara (solo vendedor) --}}
@if($esVendedor)
    <div class="modal fade" id="capturarComprobanteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Capturar comprobante</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <video id="videoComprobante" width="640" height="480" autoplay class="border"></video>
                    <canvas id="canvasComprobante" width="640" height="480" style="display:none;"></canvas>
                </div>
                <div class="modal-footer">
                    <button type="button" id="btnTomarFotoComprobante" class="btn btn-primary">📸 Tomar foto</button>
                    <button type="button" class="btn btn-warning" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
@endif
