const myForm = document.getElementById('form_roles');
myForm.addEventListener('submit', (event) => {
    $('#menu_carga').show();
    setTimeout(function() {
        $('#menu_carga').hide();
    }, 6000);
});
