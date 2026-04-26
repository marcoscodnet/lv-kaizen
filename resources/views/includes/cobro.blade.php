{{-- Forma de pago --}}
<div class="row">
    <div class="col-lg-3">
        <div class="form-group">
            <label for="forma">Forma de pago</label>
            <select name="forma" id="forma" class="form-control" required>
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

<div class="row mb-2 mt-2">
    <div class="col">
        <button type="button" id="addItemPago" class="btn btn-success btn-sm" style="display:none">
            <i class="fa fa-plus"></i> Agregar pago
        </button>
    </div>
</div>

<div id="cuerpoPago" style="display:none">
    @isset($pagosExistentes)
        @foreach($pagosExistentes as $i => $pago)
            <div class="card p-3 mb-3 pago-item">
                <div class="row">
                    <div class="col-md-3">
                        <label>Entidad</label>
                        <select name="entidad_id[]" class="form-control js-pago-select" required>
                            @foreach($entidads as $entidad)
                                <option value="{{ $entidad->id }}" {{ $pago->entidad_id == $entidad->id ? 'selected' : '' }}>
                                    {{ $entidad->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label>Importe</label>
                        <input type="text" name="monto[]" class="form-control formato-numero"
                               value="{{ old('monto.'.$i, $pago->monto) }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="labelFechaPago">Fecha Pago</label>
                        <input type="date" name="fecha_pago[]" class="form-control"
                               value="{{ old('fecha_pago.'.$i, $pago->fecha ? \Carbon\Carbon::parse($pago->fecha)->format('Y-m-d') : '') }}" required>
                    </div>
                    <div class="col-md-2">
                        <label>Acreditado</label>
                        <input type="text" name="pagado[]" class="form-control formato-numero"
                               value="{{ old('pagado.'.$i, $pago->pagado) }}">
                    </div>
                    <div class="col-md-2">
                        <label>Fecha Contadora</label>
                        <input type="date" name="contadora[]" class="form-control"
                               value="{{ old('contadora.'.$i, $pago->contadora ? \Carbon\Carbon::parse($pago->contadora)->format('Y-m-d') : '') }}">
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-5">
                        <label>Observaciones vendedor</label>
                        <textarea name="detalle[]" class="form-control" rows="2">{{ old('detalle.'.$i, $pago->detalle) }}</textarea>
                    </div>
                    <div class="col-5">
                        <label>Observaciones</label>
                        <textarea name="observaciones[]" class="form-control" rows="2">{{ old('observaciones.'.$i, $pago->observaciones) }}</textarea>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="button" class="btn btn-danger btn-sm removeItemPago">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
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
    <div class="col-md-3">
        <label>Total acreditado</label>
        <input type="text" id="totalAcreditado" name="totalAcreditado" class="form-control formato-numero" value="0" readonly>
    </div>
</div>
