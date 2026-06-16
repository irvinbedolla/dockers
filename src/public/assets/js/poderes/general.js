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
                    if (window.jQuery && $('#crear_poder').length) {
                        $('#crear_poder').show();
                    }
                    if (typeof window.loading === 'function') {
                        window.loading();
                    }
                    setTimeout(function() {
                        if (window.jQuery && $('#menu_carga').length) {
                            $('#menu_carga').hide();
                        }

                    }, 3000);
                }
            }, false);
        });
    }, false);
})();

function nuevo_poder() {
    $('#nuevo_poder').show();
}

function editar_poder() {
    $('#nuevo_poder').show();
}