(function () {
    'use strict';
    window.addEventListener('load', function () {
        // Fetch all the forms we want to apply custom Bootstrap validation styles to
        var forms = document.getElementsByClassName('needs-validation');
        // Loop over them and prevent submission
        var validation = Array.prototype.filter.call(forms, function (form) {
            form.addEventListener('submit', function (event) {
                if (form.checkValidity() === false ){
                    event.preventDefault();
                    event.stopPropagation();
                    form.classList.add('was-validated');
                } else {
                    $('#nuevo_turno').show();
                    setTimeout(function() {
                        $('#menu_carga').hide();

                    }, 3000);
                }
            }, false);
        });
    }, false);
})();

function crear_turnos() {
    $('#nuevo_turno').show();
}

function disponibles() {
    $('#nuevo_turno').show();
}

function no_disponible() {
    $('#nuevo_turno').show();
}