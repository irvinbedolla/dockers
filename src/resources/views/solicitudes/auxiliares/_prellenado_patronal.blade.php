{{--
    Prellenado de la solicitud patronal individual con los datos de su cita
    (App\Support\PrellenadoCita + App\Support\CitaPatronal). Si no hay un
    prellenado patronal en la sesión no hace nada.

    Espera:
      $clave   'datos'  → "Datos del Solicitante Patronal" (folio, excepción)
               'citado' → "Datos Personales del Citado" (nombre, apellidos, descripción)
      $form    id del formulario a llenar
      $texto   qué se prellenó aquí

    Sólo pone valores en campos visibles y vacíos (la excepción sí cambia su
    "No" por omisión); los resalta hasta que el auxiliar los revisa. Al poner
    el folio dispara su validación (keyup), como si lo hubieran tecleado.
--}}
@php $prellenado = \App\Support\PrellenadoCita::paraVista('patronal'); @endphp
@if ($prellenado && ! empty($prellenado[$clave]))
    <div class="alert alert-warning d-flex justify-content-between align-items-center flex-wrap gap-2" role="status"
         style="background:#FBF4E1;border-color:#F0DFA8;color:#7A5D10;">
        <span>
            <i class="bi bi-magic me-1"></i>
            <strong>Datos prellenados de la cita {{ $prellenado['folio'] }}@if (! empty($prellenado['persona'])) · {{ $prellenado['persona'] }}@endif.</strong>
            {{ $texto }}
            <span id="prellenadoPatronalPendientes" class="fw-bold"></span>
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
        var datos = @json($prellenado[$clave]);
        var form = document.getElementById(@json($form));
        if (!form) { return; }

        var porRevisar = [];
        function contar() {
            var n = porRevisar.filter(function (el) { return el.classList.contains('campo-prellenado'); }).length;
            var span = document.getElementById('prellenadoPatronalPendientes');
            if (span) { span.textContent = n ? '· Faltan ' + n + ' campo' + (n === 1 ? '' : 's') + ' resaltado' + (n === 1 ? '' : 's') + ' por revisar.' : '· Ya revisaste todos los campos prellenados.'; }
        }
        function resaltar(el) {
            porRevisar.push(el);
            el.classList.add('campo-prellenado');
            var listo = function () { el.classList.remove('campo-prellenado'); contar(); };
            el.addEventListener('focus', listo, { once: true });
            setTimeout(function () { el.addEventListener('change', listo, { once: true }); }, 0);
            contar();
        }

        // Después de los demás DOMContentLoaded de la página (p. ej. el que valida el folio en keyup).
        setTimeout(function () {
            Object.keys(datos).forEach(function (nombre) {
                // Hay hidden con el mismo name (p. ej. curp/rfc en citados): se toma el campo visible.
                var el = Array.prototype.filter.call(form.querySelectorAll('[name="' + nombre + '"]'), function (x) { return x.type !== 'hidden'; })[0];
                if (!el || (el.value && nombre !== 'excepcion')) { return; }
                el.value = datos[nombre];
                ['input', 'change', 'keyup'].forEach(function (ev) { el.dispatchEvent(new Event(ev, { bubbles: true })); });
                resaltar(el);
            });
        }, 0);
    });
    </script>
@endif
