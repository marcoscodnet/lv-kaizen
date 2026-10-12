{{--
    Señas del cliente para aplicar como pago de la venta.

    La seña ya entró a la caja el día que se pagó: al tildarla se suma como pago
    con forma "Seña" y no genera movimiento en la caja de hoy. Se cargan por ajax
    al elegir el cliente (senias.aplicables). El total tildado entra en el control
    de "falta cobrar" (articulos_venta.js lee .senia-aplicada:checked).

    Parámetros: $ventaId (null al vender, el id al editar)
--}}
@php
    // Al volver con errores de validación se respetan los tildes que se habían hecho
    $seniasOld = old('_senias_enviadas') ? array_map('intval', (array) old('senias', [])) : null;
@endphp
<div id="bloqueSenias" class="card border-warning mb-3" style="display:none;"
     data-url="{{ route('senias.aplicables') }}"
     data-venta-id="{{ $ventaId }}"
     data-old='@json($seniasOld)'>
    <div class="card-header bg-warning-subtle py-2">
        <strong><i class="fas fa-hand-holding-usd"></i> Señas del cliente</strong>
        <span class="text-muted small ms-2">Tildá las que se aplican a esta venta. Ya están registradas en la caja del día en que se pagaron.</span>
    </div>
    <div class="card-body py-2">
        <input type="hidden" name="_senias_enviadas" value="1">
        <div id="listaSenias"></div>
        <div class="text-end small mt-1">Total señas aplicadas: <strong id="totalSenias">$0,00</strong></div>
    </div>
</div>

{{-- jQuery se carga en el footer: el script va al stack del final --}}
@push('scripts')
<script>
    (function () {
        function fmt(n) {
            return '$' + Number(n).toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function escapar(t) {
            return $('<div>').text(t == null ? '' : t).html();
        }

        function actualizarTotal() {
            var total = 0;
            $('.senia-aplicada:checked').each(function () {
                total += parseFloat($(this).data('monto')) || 0;
            });
            $('#totalSenias').text(fmt(total));
            // Que el control de "falta cobrar" se recalcule (articulos_venta.js)
            $(document).trigger('senias:changed');
        }

        var primeraCarga = true;

        function cargarSenias(clienteId) {
            var $bloque = $('#bloqueSenias');
            var $lista = $('#listaSenias');

            if (!clienteId || clienteId === 'nuevo') {
                $lista.empty();
                $bloque.hide();
                actualizarTotal();
                return;
            }

            $.getJSON($bloque.data('url'), { cliente_id: clienteId, venta_id: $bloque.data('venta-id') || '' }, function (senias) {
                var old = primeraCarga ? $bloque.data('old') : null;
                primeraCarga = false;
                $lista.empty();

                if (!senias.length) {
                    $bloque.hide();
                    actualizarTotal();
                    return;
                }

                senias.forEach(function (s) {
                    var tildada = old ? old.indexOf(s.id) !== -1 : s.aplicada;
                    $lista.append(
                        '<div class="form-check">' +
                            '<input class="form-check-input senia-aplicada" type="checkbox" name="senias[]" ' +
                                'id="senia_' + s.id + '" value="' + s.id + '" data-monto="' + s.monto + '"' + (tildada ? ' checked' : '') + '>' +
                            '<label class="form-check-label" for="senia_' + s.id + '">' +
                                '<strong>' + fmt(s.monto) + '</strong> — ' + escapar(s.fecha) +
                                ' · ' + escapar(s.sucursal) + ' (caja #' + escapar(s.caja_id) + ')' +
                                (s.detalle ? ' · <span class="text-muted">' + escapar(s.detalle) + '</span>' : '') +
                            '</label>' +
                        '</div>'
                    );
                });

                $bloque.show();
                actualizarTotal();
            });
        }

        $(function () {
            $('body').on('change', '.senia-aplicada', actualizarTotal);
            $('#cliente_id').on('change', function () {
                cargarSenias($(this).val());
            });
            cargarSenias($('#cliente_id').val());
        });
    })();
</script>
@endpush
