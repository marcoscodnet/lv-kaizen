$(document).ready(function () {
    $('.provincia-select').each(function () {
        let $provincia = $(this);
        let $form = $provincia.closest('form');
        let $localidad = $form.find('.localidad-select');

        // Detectar si estamos dentro de un modal
        let $modal = $form.closest('.modal');
        let dropdownParent = $modal.length ? $modal : $('body');

        // Inicializamos Select2 al cargar
        $provincia.select2({
            theme: 'bootstrap-5',
            dropdownParent: dropdownParent,
            width: '100%'
        });

        $localidad.select2({
            theme: 'bootstrap-5',
            dropdownParent: dropdownParent,
            width: '100%'
        });

        // Evento de cambio de provincia
        $provincia.on('change', function () {
            let provinciaID = $(this).val();
            let selectedLocalidadId = $(this).data('old-localidad');

            // Vaciar y mostrar "Cargando..."
            $localidad.select2('destroy');
            $localidad.empty().append('<option value="">Cargando...</option>');

            if (provinciaID) {
                $.getJSON(localidadUrl + '/' + provinciaID, function (data) {
                    $localidad.empty().append('<option value=""></option>');
                    $.each(data, function (key, value) {
                        let selected = value.id == selectedLocalidadId ? 'selected' : '';
                        $localidad.append('<option value="' + value.id + '" ' + selected + '>' + value.nombre + '</option>');
                    });

                    // Reinicializamos Select2 manteniendo el theme y dropdownParent
                    $localidad.select2({
                        theme: 'bootstrap-5',
                        dropdownParent: dropdownParent,
                        width: '100%'
                    }).next('.select2-container').addClass('form-control');;

                    // Abrir dropdown si hay valor seleccionado
                    if (selectedLocalidadId) {
                        $localidad.select2('open');
                    }
                });
            } else {
                $localidad.empty().append('<option value="">Seleccione una provincia primero</option>');
                $localidad.select2({
                    theme: 'bootstrap-5',
                    dropdownParent: dropdownParent,
                    width: '100%'
                }).next('.select2-container').addClass('form-control');;
            }
        });
    });
});
