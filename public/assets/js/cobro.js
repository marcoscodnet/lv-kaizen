$(document).ready(function () {

    // Context: 'vendedor' by default. The audit view sets window.cobroContexto = 'auditor'
    var contexto = window.cobroContexto || 'vendedor';
    var esVendedor = contexto === 'vendedor';
    var esAuditor = contexto === 'auditor';

    function getEntidadOptions(forma) {
        return entidadsData
            .filter(function (e) { return e.forma === forma; })
            .map(function (e) {
                return '<option value="' + e.id + '" data-autorizacion="' + (e.autorizacion ? 1 : 0) + '">' + e.nombre + '</option>';
            })
            .join('');
    }

    function getPagoHtml(forma) {
        var labelFecha = forma === 'Contado' ? 'Fecha de pago' : 'Aprobación Crédito';

        // Proof block (only seller can upload)
        var comprobanteHtml = esVendedor ? `
                <div class="row mt-2 comprobante-wrapper" style="display:none;">
                    <div class="col-md-12">
                        <label>Comprobante</label>
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
                    </div>
                </div>` : '';

        // Auditor fields (Acreditado). Only visible for auditor.
        var camposAuditorHtml = `
                <div class="col-md-2 campos-auditor" style="display:${esAuditor ? 'block' : 'none'};">
                    <label>Acreditado</label>
                    <input type="text" name="pagado[]" class="form-control formato-numero-pago">
                </div>`;

        var fechaContadoraHtml = `
                <div class="row mt-2 campos-auditor" style="display:${esAuditor ? 'flex' : 'none'};">
                    <div class="col-md-3">
                        <label>Fecha Contadora</label>
                        <input type="date" name="contadora[]" class="form-control">
                    </div>
                </div>`;

        var btnRemoverHtml = esVendedor ? `
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="button" class="btn btn-danger btn-sm removeItemPago">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>` : '';

        return `
            <div class="card p-3 mb-3 pago-item">
                <div class="row">
                    <div class="col-md-4">
                        <label>Entidad</label>
                        <select name="entidad_id[]" class="form-control js-pago-select" ${esAuditor ? 'disabled' : 'required'}>
                            ${getEntidadOptions(forma)}
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Importe</label>
                        <input type="text" name="monto[]" class="form-control formato-numero-pago" ${esAuditor ? 'readonly' : 'required'}>
                    </div>
                    <div class="col-md-3">
                        <label class="labelFechaPago">${labelFecha}</label>
                        <input type="date" name="fecha_pago[]" class="form-control" ${esAuditor ? 'readonly' : 'required'}>
                    </div>
                    ${camposAuditorHtml}
                </div>
                ${fechaContadoraHtml}
                ${comprobanteHtml}
                <div class="row mt-2">
                    <div class="col-5">
                        <label>Observaciones vendedor</label>
                        <textarea name="detalle[]" class="form-control" rows="2" ${esAuditor ? 'readonly' : ''}></textarea>
                    </div>
                    <div class="col-5">
                        <label>Observaciones</label>
                        <textarea name="observaciones[]" class="form-control" rows="2" ${esAuditor ? 'readonly' : ''}></textarea>
                    </div>
                    ${btnRemoverHtml}
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
        toggleComprobante($row.find('.js-pago-select'));
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

    // Show/hide the proof block based on whether the selected entity requires authorization
    function toggleComprobante($select) {
        var $row = $select.closest('.pago-item');
        var $wrapper = $row.find('.comprobante-wrapper');
        if ($wrapper.length === 0) return; // Not present in auditor context

        var requiere = parseInt($select.find('option:selected').data('autorizacion'), 10) === 1;

        if (requiere) {
            $wrapper.css('display', 'flex');
        } else {
            $wrapper.hide();
            $row.find('.comprobante-file').val('');
            $row.find('.comprobante-preview').hide().attr('src', '');
        }
    }

    $(document).on('forma:changed', function (e, forma) {
        if (esAuditor) {
            $('#cuerpoPago, #totalesPago').show();
            return;
        }

        if (forma === '') {
            $('#addItemPago, #cuerpoPago, #totalesPago').hide();
        } else {
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

    // Toggle proof block on entity change
    $('body').on('change', '.js-pago-select', function () {
        toggleComprobante($(this));
    });

    // Initialize state on load for pre-existing payments (edit view)
    $('.pago-item .js-pago-select').each(function () {
        toggleComprobante($(this));
    });

    // ============================================================
    // Proof capture with camera (seller only)
    // ============================================================
    if (esVendedor) {
        var $filaActiva = null;
        var streamActivo = null;
        var $modalCamara = $('#capturarComprobanteModal');

        $('body').on('click', '.btn-capturar-comprobante', function () {
            $filaActiva = $(this).closest('.pago-item');
            $modalCamara.modal('show');
        });

        $modalCamara.on('shown.bs.modal', function () {
            var video = document.getElementById('videoComprobante');
            navigator.mediaDevices.getUserMedia({
                video: { width: { ideal: 1280 }, height: { ideal: 720 } },
                audio: false
            }).then(function (stream) {
                streamActivo = stream;
                video.srcObject = stream;
                video.play();
            }).catch(function (err) {
                alert('No se pudo acceder a la cámara:\n' + err.name + '\n' + err.message);
            });
        });

        $modalCamara.on('hidden.bs.modal', function () {
            if (streamActivo) {
                streamActivo.getTracks().forEach(function (t) { t.stop(); });
                streamActivo = null;
            }
        });

        $('#btnTomarFotoComprobante').on('click', function () {
            if (!$filaActiva) return;

            var video = document.getElementById('videoComprobante');
            var canvas = document.getElementById('canvasComprobante');
            var ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            canvas.toBlob(function (blob) {
                var file = new File([blob], 'comprobante_' + Date.now() + '.png', { type: 'image/png' });
                var dt = new DataTransfer();
                dt.items.add(file);
                var $fileInput = $filaActiva.find('.comprobante-file');
                $fileInput[0].files = dt.files;

                var dataUrl = canvas.toDataURL('image/png');
                $filaActiva.find('.comprobante-preview').attr('src', dataUrl).show();

                $modalCamara.modal('hide');
            }, 'image/png');
        });

        // Preview when user selects a file manually
        $('body').on('change', '.comprobante-file', function () {
            var file = this.files[0];
            var $preview = $(this).closest('.pago-item').find('.comprobante-preview');

            if (!file) {
                $preview.hide().attr('src', '');
                return;
            }

            if (file.size > 5 * 1024 * 1024) {
                alert('El archivo supera los 5MB. Por favor seleccione uno más chico.');
                this.value = '';
                $preview.hide().attr('src', '');
                return;
            }

            if (file.type.startsWith('image/')) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    $preview.attr('src', e.target.result).show();
                };
                reader.readAsDataURL(file);
            } else {
                $preview.hide().attr('src', '');
            }
        });
    }
});
