$(document).ready(function () {
    var cambiosRealizados = false;

    $('input, select, textarea').on('change', function() {
        cambiosRealizados = true;
    });

    window.addEventListener('beforeunload', function (event) {
        if (cambiosRealizados) {
            var mensaje = "¡Atención! Puede perder algunos cambios. ¿Estás seguro de abandonar la página?";
            event.returnValue = mensaje;
            return mensaje;
        }
    });

    $('form').on('submit', function() {
        cambiosRealizados = false;
    });
});
