<script>
    // Pide el motivo del retroceso (obligatorio) y envía el formulario.
    // Se llama cuando el usuario ya confirmó el retroceso. El formulario
    // debe tener un <input type="hidden" name="motivo">.
    function pedirMotivoRetroceso(form, nue) {
        var campo = form.querySelector('[name=motivo]');

        if (typeof swal !== 'function') {
            var texto = prompt('Motivo del retroceso de ' + nue + ' (obligatorio):');
            if (texto === null) { return; }
            if (texto.trim() === '') { alert('El motivo es obligatorio.'); return; }
            campo.value = texto.trim().substring(0, 1000);
            form.submit();
            return;
        }

        // SweetAlert 1.x — misma API que usa el resto del sistema.
        swal({
            title: 'Motivo del retroceso',
            text: 'Describe por qué se aplica el retroceso a ' + nue + '.',
            type: 'input',
            showCancelButton: true,
            closeOnConfirm: false,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Aplicar retroceso',
            cancelButtonText: 'Cancelar',
            inputPlaceholder: 'Ej. Error en el monto del convenio'
        }, function (motivo) {
            if (motivo === false) { return false; }

            motivo = motivo.trim();
            if (motivo === '') {
                swal.showInputError('El motivo es obligatorio.');
                return false;
            }
            if (motivo.length > 1000) {
                swal.showInputError('El motivo no debe exceder 1000 caracteres.');
                return false;
            }

            campo.value = motivo;
            form.submit();
        });
    }
</script>
