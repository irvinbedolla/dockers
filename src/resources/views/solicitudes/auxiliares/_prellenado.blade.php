{{--
    Prellenado de la solicitud con los datos de la cita (App\Support\PrellenadoCita).
    Se incluye en solicitanteAux: si no hay prellenado en la sesión no hace nada.
    Sólo pone valores en los campos que existen; el auxiliar revisa todo con la
    persona antes de guardar.
--}}
@php $prellenado = \App\Support\PrellenadoCita::paraVista(); @endphp
@if ($prellenado)
    <div class="alert alert-warning d-flex justify-content-between align-items-center flex-wrap gap-2" role="status" id="avisoPrellenado"
         style="background:#FBF4E1;border-color:#F0DFA8;color:#7A5D10;">
        <span>
            <i class="bi bi-magic me-1"></i>
            <strong>Datos prellenados de la cita {{ $prellenado['folio'] }}.</strong>
            Revísalos con la persona y completa lo que falta.
        </span>
        <form method="POST" action="{{ route('mi_modulo.descartar') }}" class="m-0">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-secondary">Descartar prellenado</button>
        </form>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var datos = @json($prellenado['datos']);
        var form = document.querySelector('input[name="nombre"]') ? document.querySelector('input[name="nombre"]').form : null;
        if (!form) { return; }

        function poner(nombre, valor) {
            var el = form.querySelector('[name="' + nombre + '"]');
            if (!el || el.value) { return; }
            el.value = valor;
            el.dispatchEvent(new Event('input', { bubbles: true }));
            el.dispatchEvent(new Event('change', { bubbles: true }));
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
            }
            var intentos = 0;
            var espera = setInterval(function () {
                var mun = form.querySelector('[name="municipio_solicitante"]');
                if (mun && mun.querySelector('option[value="' + datos.municipio_solicitante + '"]')) {
                    mun.value = datos.municipio_solicitante;
                    mun.dispatchEvent(new Event('change', { bubbles: true }));
                    clearInterval(espera);
                }
                if (++intentos > 40) { clearInterval(espera); }
            }, 250);
        }
    });
    </script>
@endif
