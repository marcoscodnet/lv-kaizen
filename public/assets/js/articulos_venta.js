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

        $sucursal.empty();

        if (!piezaId || !catalogo[piezaId]) {
            return;
        }

        catalogo[piezaId].forEach(function (opcion) {
            $sucursal.append('<option value="' + opcion.sucursal_id + '">' + opcion.sucursal_nombre + '</option>');
        });
    }

    function importeDeFila($fila) {
        var el = $fila.find('.precioArticulo')[0];
        if (!el) return 0;

        var an = AutoNumeric.getAutoNumericElement(el);
        var precio = an
            ? parseFloat(AutoNumeric.getNumericString(el) || 0)
            : parseFloat(($(el).val() || '0').replace(/\./g, '').replace(',', '.')) || 0;

        var cantidad = parseFloat($fila.find('.cantidadArticulo').val() || 1) || 1;

        return (precio || 0) * cantidad;
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
        var $aCobrar = $('#totalACobrar');
        if ($aCobrar.length) {
            var precioUnidad = parseFloat($aCobrar.data('precio-unidad')) || 0;
            $aCobrar.val(fmt(precioUnidad + total));
        }

        $(document).trigger('articulos:changed', [total]);
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

    actualizarTotalArticulos();
});
