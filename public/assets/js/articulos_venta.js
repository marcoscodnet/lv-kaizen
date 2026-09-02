/**
 * Grilla de conceptos adicionales dentro de la venta de la moto:
 * patentamiento, seguro, casco y lo que se cobre junto con la unidad.
 *
 * El catálogo lo publica includes/articulos_venta.blade.php en
 * window.articulosCatalogo, indexado por artículo, con las sucursales donde se
 * puede tomar cada uno. Los conceptos que no llevan stock vienen con todas las
 * sucursales activas, así que se comportan igual que un accesorio.
 */
$(document).ready(function () {

    var catalogo = window.articulosCatalogo || {};

    if ($('#cuerpoArticulo').length === 0) {
        return; // La pantalla no tiene la grilla (vista de solo lectura)
    }

    /**
     * Los selects van con select2, como el resto del sistema: la lista de
     * artículos es larga y sin buscador no se encuentra nada.
     *
     * Se destruye antes de inicializar porque las opciones de sucursal se
     * rearman cada vez que cambia el artículo.
     */
    function initSelect2($contexto) {
        $contexto.find('.selectArticulo, .sucursalArticulo').each(function () {
            var $select = $(this);

            if ($select.hasClass('select2-hidden-accessible')) {
                $select.select2('destroy');
            }

            $select.select2({ language: 'es', width: '100%' });
        });
    }

    function opcionesArticuloHtml() {
        var html = '<option value="">Seleccionar...</option>';
        Object.keys(catalogo).forEach(function (piezaId) {
            var primera = catalogo[piezaId][0];
            var etiqueta = ((primera.codigo || '') + ' - ' + (primera.descripcion || '')).trim();
            html += '<option value="' + piezaId + '">' + etiqueta + '</option>';
        });
        return html;
    }

    // Llena el selector de sucursal con las opciones del artículo elegido
    function cargarSucursales($fila) {
        var piezaId = $fila.find('.selectArticulo').val();
        var $sucursal = $fila.find('.sucursalArticulo');

        if ($sucursal.hasClass('select2-hidden-accessible')) {
            $sucursal.select2('destroy');
        }

        $sucursal.empty();

        if (piezaId && catalogo[piezaId]) {
            catalogo[piezaId].forEach(function (opcion) {
                $sucursal.append('<option value="' + opcion.sucursal_id + '">' + opcion.sucursal_nombre + '</option>');
            });
        }

        $sucursal.select2({ language: 'es', width: '100%' });
    }

    // Lee un input de importe respetando AutoNumeric si ya está inicializado
    function leerImporte(el) {
        if (!el) return 0;

        var an = AutoNumeric.getAutoNumericElement(el);
        if (an) {
            return parseFloat(AutoNumeric.getNumericString(el) || 0) || 0;
        }

        return parseFloat(($(el).val() || '0').replace(/\./g, '').replace(',', '.')) || 0;
    }

    /**
     * Importe de la moto. El vendedor lo tipea, así que se lee del campo en
     * vivo; en las pantallas de solo lectura no existe y se cae al valor que
     * dejó el servidor.
     */
    function precioMoto() {
        var $campo = $('#monto_unidad');

        if ($campo.length) {
            return leerImporte($campo[0]);
        }

        return parseFloat($('#totalACobrar').data('precio-unidad')) || 0;
    }

    function importeDeFila($fila) {
        var precio = leerImporte($fila.find('.precioArticulo')[0]);
        var cantidad = parseFloat($fila.find('.cantidadArticulo').val() || 1) || 1;

        return precio * cantidad;
    }

    function actualizarTotalArticulos() {
        var total = 0;
        $('#cuerpoArticulo tr').each(function () {
            total += importeDeFila($(this));
        });

        var fmt = function (n) {
            return n.toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        };

        $('#totalArticulos').val(fmt(total));

        // Importe a cobrar de toda la operación: la moto más los conceptos
        var moto = precioMoto();
        var $aCobrar = $('#totalACobrar');

        if ($aCobrar.length) {
            $aCobrar.val(fmt(moto + total));
        }

        avisarSiCobraDeMenos(moto, fmt);
        controlarCobroDeLaVenta();

        $(document).trigger('articulos:changed', [total]);
    }

    /** Importe a cobrar de toda la operación: la moto más los conceptos. */
    function importeACobrar() {
        var total = 0;
        $('#cuerpoArticulo tr').each(function () {
            total += importeDeFila($(this));
        });
        return precioMoto() + total;
    }

    /** Lo que el vendedor cargó en las filas de pago. */
    function totalCobrado() {
        var total = 0;
        $('input[name="monto[]"]').each(function () {
            total += leerImporte(this);
        });
        return total;
    }

    /**
     * Neteo de la operación: lo cargado en pagos contra el importe a cobrar.
     * Es distinto del acreditado, que es una cuestión de tiempo: acá se controla
     * que el vendedor haya cargado todo lo que cobra.
     *
     * Si falta, no se puede confirmar. Si sobra, avisa y deja seguir.
     */
    function controlarCobroDeLaVenta() {
        var $aviso = $('#avisoCobroVenta');
        if ($aviso.length === 0) return true;

        var fmt = function (n) {
            return n.toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        };

        var aCobrar = importeACobrar();
        var cobrado = totalCobrado();
        var diff = cobrado - aCobrar;

        // Sin pagos cargados todavía no hay nada que reclamar
        if (aCobrar <= 0 || cobrado <= 0) {
            $aviso.hide();
            return true;
        }

        var base = 'A cobrar: $' + fmt(aCobrar) + ' · Cargado en pagos: $' + fmt(cobrado);

        $aviso.show().removeClass('alert-success alert-warning alert-danger');

        if (Math.abs(diff) < 0.01) {
            $aviso.addClass('alert-success').html('Cobro completo ✓ — ' + base);
            return true;
        }

        if (diff < 0) {
            $aviso.addClass('alert-danger').html(
                '<strong>Faltan $' + fmt(Math.abs(diff)) + '</strong> — ' + base +
                '. No se puede confirmar hasta cubrir el importe a cobrar.'
            );
            return false;
        }

        $aviso.addClass('alert-warning').html('Cobrado de más — ' + base + ' · Excedente: $' + fmt(diff));
        return true;
    }

    /**
     * Si la moto se cobra por debajo del precio de lista, se avisa — pero se
     * deja guardar. El vendedor cobra lo que negoció; el control es informativo.
     */
    function avisarSiCobraDeMenos(moto, fmt) {
        var $aviso = $('#avisoImporteMoto');
        if ($aviso.length === 0) return;

        var sugerido = parseFloat($('#totalACobrar').data('sugerido')) || 0;

        if (sugerido <= 0 || moto <= 0 || moto >= sugerido - 0.01) {
            $aviso.hide();
            return;
        }

        $aviso.show().html(
            'La moto se está cobrando por debajo del sugerido — Sugerido: $' + fmt(sugerido) +
            ' · Se cobra: $' + fmt(moto) +
            ' · Diferencia: $' + fmt(sugerido - moto)
        );
    }

    function agregarFilaArticulo() {
        var fila = '<tr>' +
            '<td><select name="pieza_id[]" class="form-control selectArticulo">' + opcionesArticuloHtml() + '</select></td>' +
            '<td><select name="sucursal_id_item[]" class="form-control sucursalArticulo"></select></td>' +
            '<td><input type="number" name="cantidad[]" class="form-control cantidadArticulo" min="1" value="1"></td>' +
            '<td><input type="text" name="precio_articulo[]" class="form-control formato-numero precioArticulo"></td>' +
            '<td><a href="#" class="btn btn-danger btn-sm removeRowArticulo"><i class="fa fa-times text-white"></i></a></td>' +
            '</tr>';

        var $fila = $(fila).appendTo('#cuerpoArticulo');

        new AutoNumeric.multiple($fila.find('.formato-numero').get(), {
            digitGroupSeparator: '.',
            decimalCharacter: ',',
            decimalPlaces: 2,
            unformatOnSubmit: true
        });

        initSelect2($fila);

        return $fila;
    }

    $('#addRowArticulo').on('click', function (e) {
        e.preventDefault();
        agregarFilaArticulo();
    });

    $('body').on('click', '.removeRowArticulo', function (e) {
        e.preventDefault();
        $(this).closest('tr').remove();
        actualizarTotalArticulos();
    });

    $('body').on('change', '.selectArticulo', function () {
        cargarSucursales($(this).closest('tr'));
        actualizarTotalArticulos();
    });

    $('body').on('input change', '.precioArticulo, .cantidadArticulo', actualizarTotalArticulos);

    // El importe de la moto lo tipea el vendedor: recalcula el total a cobrar
    $('body').on('input change', '#monto_unidad', actualizarTotalArticulos);

    // Los pagos también entran en el neteo de la operación
    $('body').on('input change', 'input[name="monto[]"]', controlarCobroDeLaVenta);
    $('body').on('click', '.removeItemPago, #addItemPago', function () {
        setTimeout(controlarCobroDeLaVenta, 0);
    });
    $(document).on('forma:changed', function () {
        setTimeout(controlarCobroDeLaVenta, 0);
    });

    // No se confirma una venta cobrando de menos
    $('#formVenta').on('submit', function (e) {
        if (!controlarCobroDeLaVenta()) {
            e.preventDefault();
            e.stopImmediatePropagation();

            var $aviso = $('#avisoCobroVenta');
            if ($aviso.length) {
                $('html, body').animate({ scrollTop: $aviso.offset().top - 120 }, 300);
            }

            return false;
        }
    });

    // Filas que vinieron armadas del servidor (edición, o vuelta de un error)
    initSelect2($('#cuerpoArticulo'));
    actualizarTotalArticulos();
});
