{{--
    Prellenado de la solicitud con los datos de la cita (App\Support\PrellenadoCita).
    Se incluye en solicitanteAux: si no hay prellenado en la sesión no hace nada.
    Sólo pone valores en los campos que existen; el auxiliar revisa todo con la
    persona antes de guardar. Los campos prellenados se resaltan hasta que el
    auxiliar los revisa (entra al campo o lo cambia) y el aviso lleva la cuenta
    de los que faltan.
--}}
@php $prellenado = \App\Support\PrellenadoCita::paraVista(); @endphp
@if ($prellenado)
    <div class="alert alert-warning d-flex justify-content-between align-items-center flex-wrap gap-2" role="status" id="avisoPrellenado"
         style="background:#FBF4E1;border-color:#F0DFA8;color:#7A5D10;">
        <span>
            <i class="bi bi-magic me-1"></i>
            <strong>Datos prellenados de la cita {{ $prellenado['folio'] }}.</strong>
            Revísalos con la persona y completa lo que falta. El estado y municipio del domicilio no vienen
            en la cita (la cita trae el de la fuente de empleo): pregúntalos.
            <span id="prellenadoPendientes" class="fw-bold"></span>
        </span>
        <form method="POST" action="{{ route('mi_modulo.descartar') }}" class="m-0">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-secondary">Descartar prellenado</button>
        </form>
    </div>
    <style>
        .campo-prellenado { background-color: #FFF8E1 !important; box-shadow: 0 0 0 2px #F0DFA8 !important; }
    </style>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var datos = @json($prellenado['datos']);
        var form = document.querySelector('input[name="nombre"]') ? document.querySelector('input[name="nombre"]').form : null;
        if (!form) { return; }

        // Revisión: cada campo prellenado queda resaltado hasta que el auxiliar lo toca.
        var porRevisar = [];
        function contar() {
            var n = porRevisar.filter(function (el) { return el.classList.contains('campo-prellenado'); }).length;
            var span = document.getElementById('prellenadoPendientes');
            if (span) { span.textContent = n ? '· Faltan ' + n + ' campo' + (n === 1 ? '' : 's') + ' resaltado' + (n === 1 ? '' : 's') + ' por revisar.' : '· Ya revisaste todos los campos prellenados.'; }
        }
        function resaltar(el) {
            if (el.type === 'hidden' || porRevisar.indexOf(el) !== -1) { return; }
            porRevisar.push(el);
            el.classList.add('campo-prellenado');
            var listo = function () { el.classList.remove('campo-prellenado'); contar(); };
            el.addEventListener('focus', listo, { once: true });
            // Al prellenar se disparan input/change: se escucha hasta después.
            setTimeout(function () { el.addEventListener('change', listo, { once: true }); }, 0);
            contar();
        }

        function poner(nombre, valor) {
            var el = form.querySelector('[name="' + nombre + '"]');
            // "excepcion" ya trae "No" por omisión: la cita de excepción sí lo cambia.
            if (!el || (el.value && nombre !== 'excepcion')) { return; }
            el.value = valor;
            el.dispatchEvent(new Event('input', { bubbles: true }));
            el.dispatchEvent(new Event('change', { bubbles: true }));
            resaltar(el);
        }

        Object.keys(datos).forEach(function (k) {
            if (k !== 'estado_solicitante' && k !== 'municipio_solicitante') { poner(k, datos[k]); }
        });

        // El municipio se carga por AJAX al elegir el estado: se espera a que llegue.
        if (datos.estado_solicitante) {
            var estado = form.querySelector('[name="estado_solicitante"]');
            if (estado && !estado.value) {
                estado.value = datos.estado_solicitante;
                if (window.jQuery) { jQuery(estado).trigger('change'); } else { estado.dispatchEvent(new Event('change', { bubbles: true })); }
                resaltar(estado);
            }
            var intentos = 0;
            var espera = setInterval(function () {
                var mun = form.querySelector('[name="municipio_solicitante"]');
                if (mun && mun.querySelector('option[value="' + datos.municipio_solicitante + '"]')) {
                    mun.value = datos.municipio_solicitante;
                    mun.dispatchEvent(new Event('change', { bubbles: true }));
                    resaltar(mun);
                    clearInterval(espera);
                }
                if (++intentos > 40) { clearInterval(espera); }
            }, 250);
        }
    });
    </script>
@endif
