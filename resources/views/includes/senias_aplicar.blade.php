{{--
    Señas del cliente para aplicar como pago de una operación
    (venta de moto, venta de artículos o servicio).

    La plata de la seña ya entró el día que se dejó (a la caja, si fue efectivo,
    o a la cuenta, si fue transferencia/tarjeta): al tildarla se suma como pago
    con forma "Seña" y no genera movimiento nuevo. Solo se pueden tildar las
    DISPONIBLES; las pendientes de autorización se muestran deshabilitadas. Cada
    seña vale lo acreditado.

    Se cargan por ajax al elegir el cliente (senias.aplicables). El total
    tildado entra en el control de "falta cobrar" de cada pantalla, que lee
    .senia-aplicada:checked y escucha el evento senias:changed.

    Parámetros:
      $columna      venta_id | venta_pieza_id | servicio_id
      $operacionId  null al crear, el id al editar
--}}
@php
    // Al volver con errores de validación se respetan los tildes que se habían hecho
    $seniasOld = old('_senias_enviadas') ? array_map('intval', (array) old('senias', [])) : null;
@endphp
<div id="bloqueSenias" class="card border-warning mb-3" style="display:none;"
     data-url="{{ route('senias.aplicables') }}"
     data-columna="{{ $columna }}"
     data-operacion-id="{{ $operacionId }}"
     data-old='@json($seniasOld)'>
    <div class="card-header bg-warning-subtle py-2">
        <strong><i class="fas fa-hand-holding-usd"></i> Señas del cliente</strong>
        <span class="text-muted small ms-2">Tildá las que se aplican a esta operación. La plata ya se registró el día de la seña.</span>
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
            // Que el control de "falta cobrar" de la pantalla se recalcule
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

            $.getJSON($bloque.data('url'), {
                cliente_id: clienteId,
                columna: $bloque.data('columna'),
                operacion_id: $bloque.data('operacion-id') || ''
            }, function (senias) {
                var old = primeraCarga ? $bloque.data('old') : null;
                primeraCarga = false;
                $lista.empty();

                if (!senias.length) {
                    $bloque.hide();
                    actualizarTotal();
                    return;
                }

                senias.forEach(function (s) {
                    var habilitada = s.disponible || s.aplicada;
                    var tildada = habilitada && (old ? old.indexOf(s.id) !== -1 : s.aplicada);
                    var extra = '';
                    if (!habilitada) {
                        extra = ' <span class="badge bg-warning text-dark">Pendiente de autorización</span>';
                    } else if (Math.abs(s.cobrado - s.monto) >= 0.01) {
                        extra = ' <span class="text-muted">(cobrado ' + fmt(s.cobrado) + ', acreditado ' + fmt(s.monto) + ')</span>';
                    }
                    $lista.append(
                        '<div class="form-check">' +
                            '<input class="form-check-input senia-aplicada" type="checkbox" name="senias[]" ' +
                                'id="senia_' + s.id + '" value="' + s.id + '" data-monto="' + s.monto + '"' +
                                (tildada ? ' checked' : '') + (habilitada ? '' : ' disabled') + '>' +
                            '<label class="form-check-label' + (habilitada ? '' : ' text-muted') + '" for="senia_' + s.id + '">' +
                                'Seña #' + s.id + ' — <strong>' + fmt(habilitada ? s.monto : s.cobrado) + '</strong> — ' + escapar(s.fecha) +
                                ' · ' + escapar(s.sucursal) + ' · ' + escapar(s.formas) +
                                (s.detalle ? ' · <span class="text-muted">' + escapar(s.detalle) + '</span>' : '') +
                                extra +
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
