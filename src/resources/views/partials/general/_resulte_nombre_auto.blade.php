<script>
    (function () {
        var LEYENDA = 'QUIEN O QUIENES RESULTEN RESPONSABLES Y/O BENEFICIARIOS Y/O USUFRUCTUARIOS Y/O PROPIETARIOS DE LA FUENTE DE EMPLEO UBICADA EN ';
        var CAMPOS = ['estado_citado[]', 'municipio_citado[]', 'vialidad_citado[]', 'calle_citado[]',
                      'n_ext_citado[]', 'n_int_citado[]', 'colonia_citado[]', 'cp_citado[]'];

        function campo(nombre, i) {
            return document.querySelectorAll('[name="' + nombre + '"]')[i] || null;
        }
        function valor(nombre, i) {
            var el = campo(nombre, i);
            return el ? String(el.value || '').trim() : '';
        }
        function textoOpcion(nombre, i) {
            var el = campo(nombre, i);
            if (!el || el.selectedIndex < 0 || !el.value) { return ''; }
            return el.options[el.selectedIndex].text.trim().toUpperCase();
        }

        function actualizarNombre(i) {
            var flag = campo('resulte_responsable[]', i);
            var nombreInput = campo('nombre_citado[]', i);
            if (!flag || flag.value !== 'Si' || !nombreInput) { return; }

            var actual = nombreInput.value || '';
            var corte = actual.indexOf('UBICADA EN ');
            var prefijo = corte >= 0 ? actual.slice(0, corte + 'UBICADA EN '.length) : LEYENDA;

            var vialidad = valor('vialidad_citado[]', i);
            var nombre = prefijo + (vialidad ? vialidad + ' ' : '') + valor('calle_citado[]', i) +
                ', NÚMERO ' + valor('n_ext_citado[]', i);
            var interior = valor('n_int_citado[]', i);
            if (interior) { nombre += ' INT. ' + interior; }
            nombre += ' COLONIA ' + valor('colonia_citado[]', i) + ', ' + textoOpcion('municipio_citado[]', i) +
                ', ' + textoOpcion('estado_citado[]', i) + ', C.P. ' + valor('cp_citado[]', i) + '.';

            if (nombre === actual) { return; }
            nombreInput.value = nombre;

            // Encabezado de la tarjeta del citado, si lo hay.
            var tarjeta = nombreInput.closest('.ra-parte');
            var cab = tarjeta && tarjeta.querySelector('.ra-parte__nombre');
            if (cab) { cab.textContent = nombre; }
        }

        function alCambiar(e) {
            var el = e.target;
            if (!el || !el.name || CAMPOS.indexOf(el.name) === -1) { return; }
            var i = Array.prototype.indexOf.call(document.querySelectorAll('[name="' + el.name + '"]'), el);
            if (i >= 0) { actualizarNombre(i); }
        }

        document.addEventListener('input', alCambiar);
        document.addEventListener('change', alCambiar);
    })();
</script>
