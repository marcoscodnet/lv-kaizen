$(document).ready(function () {

    function getEntidadOptions(forma) {
        return entidadsData
            .filter(function (e) { return e.forma === forma; })
            .map(function (e) {
                return '<option value="' + e.id + '">' + e.nombre + '</option>';
            })
            .join('');
    }

    function getPagoHtml(forma) {
        var labelFecha = forma === 'Contado' ? 'Fecha de pago' : 'Aprobación Crédito';
        return `
            <div class="card p-3 mb-3 pago-item">
                <div class="row">
                    <div class="col-md-3">
                        <label>Entidad</label>
                        <select name="entidad_id[]" class="form-control js-pago-select" required>
                            ${getEntidadOptions(forma)}
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label>Importe</label>
                        <input type="text" name="monto[]" class="form-control formato-numero-pago" required>
                    </div>
                    <div class="col-md-2">
                        <label class="labelFechaPago">${labelFecha}</label>
                        <input type="date" name="fecha_pago[]" class="form-control" required>
                    </div>
                    <div class="col-md-2">
                        <label>Acreditado</label>
                        <input type="text" name="pagado[]" class="form-control formato-numero-pago">
                    </div>
                    <div class="col-md-2">
                        <label>Fecha Contadora</label>
                        <input type="date" name="contadora[]" class="form-control">
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-5">
                        <label>Observaciones vendedor</label>
                        <textarea name="detalle[]" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="col-5">
                        <label>Observaciones</label>
                        <textarea name="observaciones[]" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="button" class="btn btn-danger btn-sm removeItemPago">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>`;
    }

    function agregarFilaPago(forma) {
        var $row = $(getPagoHtml(forma)).appendTo('#cuerpoPago');
        $row.find('.js-pago-select').select2({ language: 'es' });
        new AutoNumeric.multiple($row.find('.formato-numero-pago').get(), {
            digitGroupSeparator: '.',
            decimalCharacter: ',',
            decimalPlaces: 2,
            unformatOnSubmit: true
        });
        actualizarTotalesPago();
    }

    function actualizarTotalesPago() {
        var totalMonto = 0;
        var totalAcreditado = 0;

        $('input[name="monto[]"]').each(function () {
            var val = parseFloat($(this).val().replace(/\./g, '').replace(',', '.')) || 0;
            totalMonto += val;
        });

        $('input[name="pagado[]"]').each(function () {
            var val = parseFloat($(this).val().replace(/\./g, '').replace(',', '.')) || 0;
            totalAcreditado += val;
        });

        var elMonto = AutoNumeric.getAutoNumericElement('#totalMonto');
        var elAcreditado = AutoNumeric.getAutoNumericElement('#totalAcreditado');
        if (elMonto) elMonto.set(totalMonto);
        if (elAcreditado) elAcreditado.set(totalAcreditado);
    }

    $(document).on('forma:changed', function (e, forma) {
        if (forma === '') {
            $('#addItemPago, #cuerpoPago, #totalesPago').hide();
        } else {
            //$('.labelFechaPago').text(forma === 'Contado' ? 'Fecha de pago' : 'Aprobación Crédito');
            $('#addItemPago, #cuerpoPago, #totalesPago').show();
            if ($('#cuerpoPago .pago-item').length === 0) {
                agregarFilaPago(forma);
            }
        }
    });

    $('#addItemPago').on('click', function () {
        agregarFilaPago($('#forma').val());
    });

    $('body').on('click', '.removeItemPago', function () {
        $(this).closest('.pago-item').remove();
        actualizarTotalesPago();
    });

    $('body').on('input', 'input[name="monto[]"], input[name="pagado[]"]', actualizarTotalesPago);
});
