$(document).ready(function () {
    $('.provincia-select').on('change', function () {
        let provinciaID = $(this).val();
        let localidadSelect = $(this).closest('form').find('.localidad-select');

        localidadSelect.empty().append('<option value="">Cargando...</option>');
        let selectedLocalidadId = $('.provincia-select').data('old-localidad');
        console.log('seleccionada: '+selectedLocalidadId);
        if (provinciaID) {
            $.ajax({
                url: localidadUrl + '/' + provinciaID,  // Usando la variable de JS
                type: 'GET',
                dataType: 'json',
                success: function (data) {
                    localidadSelect.empty().append('<option value=""></option>');
                    $.each(data, function (key, value) {
                        let selected = value.id == selectedLocalidadId ? 'selected' : '';
                        localidadSelect.append('<option value="' + value.id + '" ' + selected + '>' + value.nombre + '</option>');
                    });
                    localidadSelect.select2();
                }
            });
        } else {
            localidadSelect.empty().append('<option value="">Seleccione una provincia primero</option>');
            localidadSelect.select2(); // También reinicializás si está vacío
        }
    });
});
