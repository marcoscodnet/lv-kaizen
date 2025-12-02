$(document).ready(function () {
    $('body').on('change', '.sucursal-select', function () {

        let row = $(this).closest('tr');
        let sucursalID = $(this).val();
        let ubicacionSelect = row.find('.ubicacion-select');

        ubicacionSelect.empty().append('<option value="">Cargando...</option>');

        // Valor seleccionado SOLO de esa fila
        let selectedUbicacionId = ubicacionSelect.data('old-ubicacion') || "";

        if (sucursalID) {

            $.ajax({
                url: ubicacionUrl + '/' + sucursalID,
                type: 'GET',
                dataType: 'json',
                success: function (data) {

                    ubicacionSelect.empty().append('<option value=""></option>');

                    $.each(data, function (_, value) {
                        let selected = value.id == selectedUbicacionId ? 'selected' : '';
                        ubicacionSelect.append(
                            `<option value="${value.id}" ${selected}>${value.nombre}</option>`
                        );
                    });

                    ubicacionSelect.select2();
                }
            });

        } else {
            ubicacionSelect.empty().append('<option value="">Seleccione una sucursal primero</option>');
            ubicacionSelect.select2();
        }
    });
});
