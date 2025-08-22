$(document).ready(function () {
    $('.provincia-select').each(function () {
        let $provincia = $(this);
        let $form = $provincia.closest('form');
        let $localidad = $form.find('.localidad-select');

        // Inicializamos Select2 al cargar
        $provincia.select2({ theme: 'bootstrap-5' });
        $localidad.select2({ theme: 'bootstrap-5' });

        // Evento de cambio
        $provincia.on('change', function () {
            let provinciaID = $(this).val();
            let selectedLocalidadId = $(this).data('old-localidad');

            // Destruir Select2 antes de actualizar
            $localidad.select2('destroy');
            $localidad.empty().append('<option value="">Cargando...</option>');

            if (provinciaID) {
                $.getJSON(localidadUrl + '/' + provinciaID, function (data) {
                    $localidad.empty().append('<option value=""></option>');
                    $.each(data, function (key, value) {
                        let selected = value.id == selectedLocalidadId ? 'selected' : '';
                        $localidad.append('<option value="' + value.id + '" ' + selected + '>' + value.nombre + '</option>');
                    });

                    // Reinicializamos Select2 correctamente
                    $localidad.select2({ theme: 'bootstrap-5' });

                    // Abrir dropdown si había valor seleccionado
                    if (selectedLocalidadId) {
                        $localidad.select2('open');
                    }
                });
            } else {
                $localidad.empty().append('<option value="">Seleccione una provincia primero</option>');
                $localidad.select2({ theme: 'bootstrap-5' });
            }
        });
    });
});
