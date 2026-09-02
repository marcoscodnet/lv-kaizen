$(document).ready(function () {

    // Context: 'vendedor' by default. The audit view sets window.cobroContexto = 'auditor'
    var contexto = window.cobroContexto || 'vendedor';
    var esVendedor = contexto === 'vendedor';
    var esAuditor = contexto === 'auditor';

    // Unique id per payment row + accumulated files per row (allows many files per payment)
    var pagoUidSeq = 0;
    var rowFilesByUid = {};

    // Pagos que el usuario ya había cargado y vuelven por old() después de un
    // error de validación. Se consumen una sola vez, al armar las filas, para
    // que un cartel de error no le borre toda la operación.
    var pagosOld = window.pagosOld || [];

    // Rebuild the file input's FileList from the accumulated files and refresh previews
    function syncInputFiles(uid) {
        var dt = new DataTransfer();
        (rowFilesByUid[uid] || []).forEach(function (f) { dt.items.add(f); });
        var $input = $('.comprobante-file[data-uid="' + uid + '"]');
        if ($input.length) $input[0].files = dt.files;
        renderPreviews(uid);
    }

    function addFilesToRow(uid, fileList) {
        if (!rowFilesByUid[uid]) rowFilesByUid[uid] = [];
        Array.prototype.forEach.call(fileList, function (f) {
            if (f.size > 5 * 1024 * 1024) {
                alert('El archivo "' + f.name + '" supera los 5MB y no se agregó.');
                return;
            }
            rowFilesByUid[uid].push(f);
        });
        syncInputFiles(uid);
    }

    function removeFileFromRow(uid, idx) {
        if (rowFilesByUid[uid]) {
            rowFilesByUid[uid].splice(idx, 1);
            syncInputFiles(uid);
        }
    }

    function clearRowFiles(uid) {
        rowFilesByUid[uid] = [];
        syncInputFiles(uid);
    }

    function renderPreviews(uid) {
        var $row = $('.pago-item[data-uid="' + uid + '"]');
        var $cont = $row.find('.comprobante-previews');
        if ($cont.length === 0) return;
        $cont.empty();
        (rowFilesByUid[uid] || []).forEach(function (f, idx) {
            var inner = f.type && f.type.indexOf('image/') === 0
                ? '<img src="' + URL.createObjectURL(f) + '" style="max-width:80px; max-height:60px; display:block;">'
                : '<span class="badge bg-secondary" style="white-space:normal;">' + (f.name || 'archivo') + '</span>';
            var $chip = $(
                '<div class="comprobante-chip border rounded p-1 text-center" style="position:relative;">' +
                    inner +
                    '<button type="button" class="btn btn-danger btn-sm comprobante-remove" ' +
                    'data-uid="' + uid + '" data-idx="' + idx + '" ' +
                    'style="position:absolute; top:-8px; right:-8px; padding:0 6px; line-height:1.2;">&times;</button>' +
                '</div>'
            );
            $cont.append($chip);
        });
    }

    function getEntidadOptions(forma) {
        return entidadsData
            .filter(function (e) { return e.forma === forma; })
            .map(function (e) {
                return '<option value="' + e.id + '" data-autorizacion="' + (e.autorizacion ? 1 : 0) + '" data-tangible="' + (e.tangible ? 1 : 0) + '">' + e.nombre + '</option>';
            })
            .join('');
    }

    function getPagoHtml(forma, uid) {
        var labelFecha = forma === 'Contado' ? 'Fecha de pago' : 'Aprobación Crédito';

        // Proof block (only seller can upload)
        var comprobanteHtml = esVendedor ? `
                <div class="row mt-2 comprobante-wrapper" style="display:none;">
                    <div class="col-md-12">
                        <label>Comprobantes</label>
                        <div class="d-flex gap-2 align-items-start flex-wrap">
                            <div>
                                <input type="file" name="comprobantes_${uid}[]" multiple data-uid="${uid}"
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
            <div class="card p-3 mb-3 pago-item" data-uid="${uid}">
                <input type="hidden" name="pago_uid[]" value="${uid}">
                <div class="row">
                    <div class="col-md-4">
                        <label>Forma de pago</label>
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

    function agregarFilaPago(forma, datos) {
        var uid = ++pagoUidSeq;
        var $row = $(getPagoHtml(forma, uid)).appendTo('#cuerpoPago');
        $row.find('.js-pago-select').select2({ language: 'es' });
        new AutoNumeric.multiple($row.find('.formato-numero-pago').get(), {
            digitGroupSeparator: '.',
            decimalCharacter: ',',
            decimalPlaces: 2,
            unformatOnSubmit: true
        });
        if (datos) aplicarDatosPago($row, datos);
        toggleComprobante($row.find('.js-pago-select'));
        actualizarTotalesPago();
        return $row;
    }

    // Escribe un importe respetando AutoNumeric si la fila ya lo tiene inicializado
    function setImporte($input, valor) {
        if (!$input.length) return;
        if (valor === null || typeof valor === 'undefined' || valor === '') return;
        var an = AutoNumeric.getAutoNumericElement($input[0]);
        if (an) an.set(parseFloat(String(valor).replace(',', '.')) || 0);
        else $input.val(valor);
    }

    /**
     * Las opciones de forma de pago se filtran por la condición de venta. Si el
     * usuario cargó un pago con una condición y después la cambió, al rearmar la
     * fila esa opción ya no está y el pago se perdía en silencio.
     *
     * Se agrega la opción que falta para que lo cargado nunca desaparezca.
     */
    function asegurarOpcionEntidad($select, entidadId) {
        if ($select.find('option[value="' + entidadId + '"]').length) {
            return;
        }

        var entidad = (entidadsData || []).filter(function (e) {
            return String(e.id) === String(entidadId);
        })[0];

        if (!entidad) return;

        $select.append(
            '<option value="' + entidad.id + '"' +
            ' data-autorizacion="' + (entidad.autorizacion ? 1 : 0) + '"' +
            ' data-tangible="' + (entidad.tangible ? 1 : 0) + '">' +
            entidad.nombre + '</option>'
        );
    }

    // Vuelca en una fila los datos que el usuario había cargado
    function aplicarDatosPago($row, d) {
        if (!d) return;

        if (d.entidad_id) {
            var $sel = $row.find('select[name="entidad_id[]"]');
            asegurarOpcionEntidad($sel, d.entidad_id);
            $sel.val(String(d.entidad_id));
            if ($sel.hasClass('select2-hidden-accessible')) $sel.trigger('change.select2');
            toggleComprobante($sel);
        }

        setImporte($row.find('input[name="monto[]"]'), d.monto);
        if (d.fecha_pago) $row.find('input[name="fecha_pago[]"]').val(d.fecha_pago);
        if (d.detalle) $row.find('textarea[name="detalle[]"]').val(d.detalle);
        if (d.observaciones) $row.find('textarea[name="observaciones[]"]').val(d.observaciones);

        // Campos del auditor
        setImporte($row.find('input[name="pagado[]"]'), d.pagado);
        if (d.contadora) $row.find('input[name="contadora[]"]').val(d.contadora);
    }

    /**
     * Reconstruye los pagos que venían en old(). Sobre las filas que ya existen
     * (vistas de edición, que las arma el servidor) escribe encima, y agrega las
     * que falten. Devuelve true si había algo para restaurar.
     */
    function restaurarPagosOld(forma) {
        if (!pagosOld.length) return false;

        var datos = pagosOld;
        pagosOld = [];

        var $filas = $('#cuerpoPago .pago-item');
        datos.forEach(function (d, i) {
            var $fila = $filas.eq(i);
            if ($fila.length) aplicarDatosPago($fila, d);
            else agregarFilaPago(forma, d);
        });

        actualizarTotalesPago();
        return true;
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

        // Show the proof block for every payment method EXCEPT physical cash (tangible entity)
        var esEfectivo = parseInt($select.find('option:selected').data('tangible'), 10) === 1;

        if (!esEfectivo) {
            $wrapper.css('display', 'flex');
        } else {
            $wrapper.hide();
            clearRowFiles($row.data('uid'));
        }
    }

    $(document).on('forma:changed', function (e, forma) {
        if (esAuditor) {
            $('#cuerpoPago, #totalesPago').show();
            restaurarPagosOld(forma);
            return;
        }

        if (forma === '') {
            $('#addItemPago, #cuerpoPago, #totalesPago').hide();
        } else {
            $('#addItemPago, #cuerpoPago, #totalesPago').show();
            // Primero lo que el usuario ya había cargado; si no hay nada que
            // restaurar y no quedó ninguna fila, se arranca con una vacía.
            if (!restaurarPagosOld(forma) && $('#cuerpoPago .pago-item').length === 0) {
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

    // Recalculate totals on load for pre-existing payments (edit view)
    setTimeout(function () {
        actualizarTotalesPago();
    }, 500);

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
                addFilesToRow($filaActiva.data('uid'), [file]);
                $modalCamara.modal('hide');
            }, 'image/png');
        });

        // Accumulate files when the user selects them manually (supports multiple)
        $('body').on('change', '.comprobante-file', function () {
            if (this.files && this.files.length) {
                addFilesToRow($(this).data('uid'), this.files);
            }
        });

        // Remove a single accumulated file
        $('body').on('click', '.comprobante-remove', function () {
            removeFileFromRow($(this).data('uid'), parseInt($(this).data('idx'), 10));
        });
    }
});
