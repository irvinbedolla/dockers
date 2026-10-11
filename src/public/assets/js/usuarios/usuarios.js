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
                    $('#nuevo_usuario').show();
                    setTimeout(function() {
                        $('#menu_carga').hide();

                    }, 3000);
                }
            }, false);
        });
    }, false);
})();

function crear_usuario() {
    $('#nuevo_usuario').show();
}

function editar_usuario() {
    $('#nuevo_usuario').show();
}

// Contraseña aleatoria de 12 caracteres para el alta, la edición y el
// cambio rápido. Sin letras que se confunden al dictarla (l, 1, O, 0, I).
window.siconcilioClave = function () {
    var letras = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
    var bytes = new Uint32Array(12);
    window.crypto.getRandomValues(bytes);
    return Array.prototype.map.call(bytes, function (n) { return letras[n % letras.length]; }).join('');
};
