@php
    // Default context: 'vendedor' if not specified
    $contexto = $contexto ?? 'vendedor';
    $esAuditor = $contexto === 'auditor';
    $esVendedor = $contexto === 'vendedor';
@endphp

{{-- Forma de pago (solo vendedor) --}}
@if($esVendedor)
    <div class="row">
        <div class="col-lg-3">
            <div class="form-group">
                <label for="forma">Forma de pago</label>
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
                $requiereAutorizacion = $entidadPago && $entidadPago->autorizacion;
            @endphp
            <div class="card p-3 mb-3 pago-item">
                <div class="row">
                    <div class="col-md-4">
                        <label>Entidad</label>
                        <select name="entidad_id[]" class="form-control js-pago-select"
                            {{ $esAuditor ? 'disabled' : 'required' }}>
                            @foreach($entidads as $entidad)
                                <option value="{{ $entidad->id }}"
                                        data-autorizacion="{{ $entidad->autorizacion }}"
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

                {{-- Comprobante: visible si la entidad requiere autorización --}}
                <div class="row mt-2 comprobante-wrapper" style="display: {{ $requiereAutorizacion ? 'flex' : 'none' }};">
                    <div class="col-md-12">
                        <label>Comprobante</label>
                        @if(!empty($pago->comprobante_path))
                            <div class="mb-2 comprobante-actual">
                                <a href="{{ asset($pago->comprobante_path) }}" target="_blank" class="btn btn-sm btn-info">
                                    <i class="fa fa-file"></i> Ver comprobante actual
                                </a>
                                @if($esVendedor)
                                    <small class="text-muted">Subir uno nuevo lo reemplaza.</small>
                                @endif
                            </div>
                        @endif

                        @if($esVendedor)
                            <div class="d-flex gap-2 align-items-start flex-wrap">
                                <div>
                                    <input type="file" name="comprobante[]"
                                           class="form-control form-control-sm comprobante-file"
                                           accept="image/jpeg,image/png,application/pdf">
                                    <small class="text-muted">JPG, PNG o PDF (max 5MB)</small>
                                </div>
                                <div>
                                    <button type="button" class="btn btn-sm btn-primary btn-capturar-comprobante">
                                        📸 Capturar
                                    </button>
                                </div>
                                <div>
                                    <img class="comprobante-preview border" style="display:none; max-width: 150px; max-height: 100px;">
                                </div>
                            </div>
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
