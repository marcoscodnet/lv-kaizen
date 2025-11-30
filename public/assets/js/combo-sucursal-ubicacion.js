$(document).ready(function () {
    $('.sucursal-select').on('change', function () {
        let sucursalID = $(this).val();
        let ubicacionSelect = $(this).closest('form').find('.ubicacion-select');

        ubicacionSelect.empty().append('<option value="">Cargando...</option>');
        let selectedUbicacionId = $('.sucursal-select').data('old-ubicacion');
        //console.log('seleccionada: '+selectedUbicacionId);
        if (sucursalID) {
            $.ajax({
                url: ubicacionUrl + '/' + sucursalID,  // Usando la variable de JS
                type: 'GET',
                dataType: 'json',
                success: function (data) {
                    ubicacionSelect.empty().append('<option value=""></option>');
                    $.each(data, function (key, value) {
                        let selected = value.id == selectedUbicacionId ? 'selected' : '';
                        ubicacionSelect.append('<option value="' + value.id + '" ' + selected + '>' + value.nombre + '</option>');
                    });
                    ubicacionSelect.select2();
                }
            });
        } else {
            ubicacionSelect.empty().append('<option value="">Seleccione una sucursal primero</option>');
            ubicacionSelect.select2(); // También reinicializás si está vacío
        }
    });
});
