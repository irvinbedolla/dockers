{{--
    Datos generales de la solicitud (captura del auxiliar). Lo usan
    inicioSolicitud (trabajador) e inicioSolicitudP (patronal).

    Espera: $accion (ruta del POST), $ramas, $municipios, $mostrarMotivos,
    $tipo_solicitud, $draftId y $prellenado (PrellenadoCita::paraVista() o
    null; sólo el trabajador lo usa).

    Con prellenado se ponen el municipio de la fuente de empleo (de la cita),
    la delegación (sale del municipio) y los objetos sugeridos por lo que la
    persona escribió; todo resaltado hasta que el auxiliar lo revisa.

    Lo que manda el formulario no cambia: dSolicitud, delegacion,
    motivo_solicitud[], ramaIndustrial y actividad_economica.
--}}
@php
    $inicio = $prellenado['inicio'] ?? ['municipio' => null, 'motivos' => []];
    $motivosCatalogo = collect($mostrarMotivos)->mapWithKeys(fn ($m) => [(string) $m['id'] => $m['motivo']]);
@endphp

<div class="sa-cabeza">
    <a href="{{ route('solicitud') }}" class="sa-cabeza__regreso"><i class="bi bi-arrow-left"></i> Nueva solicitud</a>
    <h3 class="page__heading">Datos generales de la solicitud</h3>
    <p class="sa-cabeza__sub">{{ (string) $tipo_solicitud === '2' ? 'Solicitud patronal individual' : 'Solicitud de la persona trabajadora' }}</p>
</div>

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Revisa los campos:</strong>
        <ul class="mb-0">
            @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
@endif
@if (session()->has('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
@endif

@if ($prellenado)
    @include('solicitudes.auxiliares._prellenado_banner', [
        'texto' => 'Lo resaltado viene de la cita: confírmalo con la persona. <span id="saPendientes" class="fw-bold"></span>',
    ])
@endif

<div class="sa-requisitos">
    <i class="bi bi-info-circle" aria-hidden="true"></i>
    <div>
        <b>Requisitos:</b> teléfono, correo electrónico e identificación oficial (INE, pasaporte, licencia de conducir o cédula profesional).
        Si es menor de edad, su identificación es la CURP o el acta de nacimiento.
    </div>
</div>

<form class="needs-validation" novalidate method="POST" action="{{ $accion }}" id="saForm">
    @csrf
    <input type="hidden" name="tipo_solicitud" value="{{ $tipo_solicitud }}">
    <input type="hidden" name="draft_id" value="{{ $draftId }}">

    <div class="tn-tarjeta sa-bloque">
        <p class="sa-bloque__titulo"><span class="sa-bloque__num">1</span> ¿Dónde está el trabajo?</p>
        <div class="row g-3">
            <div class="col-md-7">
                <label class="form-label" for="dSolicitud">
                    Municipio de la fuente de empleo <span class="sa-req">*</span>
                    @if ($inicio['municipio'])<span class="sa-de-cita"><i class="bi bi-magic"></i> de la cita</span>@endif
                </label>
                <select id="dSolicitud" class="form-select" name="dSolicitud" required>
                    <option value="">Elige el municipio</option>
                    @foreach ($municipios as $municipio)
                        <option value="{{ $municipio['id'] }}" data-delegacion-id="{{ $municipio['delegacion_id'] }}">{{ $municipio['nombre'] }}</option>
                    @endforeach
                </select>
                <div class="invalid-feedback">Elige el municipio.</div>
            </div>
            <div class="col-md-5">
                <label class="form-label" for="delegacion">Delegación <span class="sa-req">*</span></label>
                <select class="form-select" id="delegacion" name="delegacion" required>
                    <option value="">Sale del municipio</option>
                </select>
                <div class="form-text">Se asigna sola según el municipio.</div>
                <div class="invalid-feedback">Elige primero el municipio.</div>
            </div>
        </div>
    </div>

    <div class="tn-tarjeta sa-bloque">
        <p class="sa-bloque__titulo">
            <span class="sa-bloque__num">2</span> Objeto de la solicitud
            <span class="sa-bloque__ayuda">Puedes agregar varios.</span>
        </p>
        <div class="row g-3">
            <div class="col-md-5">
                <label class="form-label" for="motivo_solicitud">Agregar objeto <span class="sa-req">*</span></label>
                <select class="form-select" id="motivo_solicitud">
                    <option value="">Elige para agregar…</option>
                    @foreach ($mostrarMotivos as $motivo)
                        <option value="{{ $motivo['id'] }}">{{ $motivo['motivo'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-7">
                <span class="form-label d-block">Objetos agregados</span>
                <div class="sa-objetos" id="saObjetos" aria-live="polite">
                    <span class="sa-objetos__vacio" id="saObjetosVacio">Todavía no agregas ninguno.</span>
                </div>
                <div class="sa-error" id="errorMotivo" hidden>Agrega al menos un objeto de la solicitud.</div>
            </div>
        </div>
    </div>

    <div class="tn-tarjeta sa-bloque">
        <p class="sa-bloque__titulo"><span class="sa-bloque__num">3</span> Rama industrial del negocio</p>
        <div class="row g-3">
            <div class="col-md-5">
                <label class="form-label" for="ramaIndustrial">Rama industrial <span class="sa-req">*</span></label>
                <select id="ramaIndustrial" class="form-select" name="ramaIndustrial" required>
                    <option value="">Elige la rama</option>
                    @foreach ($ramas as $rama)
                        <option value="{{ $rama['id'] }}">{{ $rama['rama_industrial'] }}</option>
                    @endforeach
                </select>
                <div class="invalid-feedback">Elige la rama industrial.</div>
            </div>
            <div class="col-md-7">
                <label class="form-label" for="actividad_economica">Actividad económica del patrón(a) o empresa <span class="sa-req">*</span></label>
                <input type="text" name="actividad_economica" id="actividad_economica" class="form-control" maxlength="255" required
                       placeholder="Ej.: comercio de productos al por menor, construcción, servicios médicos…"
                       oninput="this.value = this.value.toUpperCase()">
                <div class="invalid-feedback">Escribe la actividad económica.</div>
            </div>
        </div>
    </div>

    <div class="sa-acciones">
        <button type="button" class="btn btn-outline-secondary" onclick="history.back()"><i class="bi bi-arrow-left me-1"></i> Regresar</button>
        <button type="submit" class="btn tn-boton-dorado">Guardar y continuar <i class="bi bi-arrow-right ms-1"></i></button>
    </div>
</form>

<script src="{{ asset('assets/js/estadistica/estadistica.js') }}"></script>
<script>
(function () {
    var CATALOGO = @json($motivosCatalogo);
    var DELEGACIONES = { 1: 'Morelia', 2: 'Zitácuaro', 3: 'Uruapan', 4: 'Lázaro Cárdenas', 5: 'Zamora', 6: 'Sahuayo' };
    var PRELLENADO = @json($prellenado ? $inicio : null);

    var form = document.getElementById('saForm');
    var municipio = document.getElementById('dSolicitud');
    var delegacion = document.getElementById('delegacion');
    var selectObjeto = document.getElementById('motivo_solicitud');
    var caja = document.getElementById('saObjetos');
    var vacio = document.getElementById('saObjetosVacio');
    var error = document.getElementById('errorMotivo');
    var porRevisar = [];

    function esc(t) { return String(t == null ? '' : t).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }

    // Revisión del prellenado: lo resaltado se quita al tocarlo.
    function contar() {
        var span = document.getElementById('saPendientes');
        if (!span) { return; }
        var n = porRevisar.filter(function (el) { return el.isConnected && el.classList.contains('campo-prellenado'); }).length;
        span.textContent = n ? 'Faltan ' + n + ' por revisar.' : 'Ya revisaste lo prellenado.';
    }
    function resaltar(el, quitarCon) {
        porRevisar.push(el);
        el.classList.add('campo-prellenado');
        (quitarCon || ['focus', 'change']).forEach(function (ev) {
            el.addEventListener(ev, function () { el.classList.remove('campo-prellenado'); contar(); }, { once: true });
        });
        contar();
    }

    // Municipio → delegación (cada municipio pertenece a una sola; se elige sola).
    function ponerDelegacion() {
        var op = municipio.options[municipio.selectedIndex];
        var nombre = op ? DELEGACIONES[op.getAttribute('data-delegacion-id')] : null;
        delegacion.innerHTML = nombre
            ? '<option value="' + esc(nombre) + '">' + esc(nombre) + '</option>'
            : '<option value="">Sale del municipio</option>';
        delegacion.value = nombre || '';
    }
    municipio.addEventListener('change', ponerDelegacion);

    // Objetos de la solicitud: fichas con su input oculto motivo_solicitud[].
    function agregar(id, sugerido) {
        id = String(id);
        if (!CATALOGO[id] || caja.querySelector('[data-id="' + id + '"]')) { return false; }
        var ficha = document.createElement('span');
        ficha.className = 'sa-objeto' + (sugerido ? ' is-sugerido campo-prellenado' : '');
        ficha.setAttribute('data-id', id);
        ficha.innerHTML = (sugerido ? '<span class="sa-objeto__sugerido"><i class="bi bi-magic"></i> Sugerido</span>' : '') +
            esc(CATALOGO[id]) +
            '<input type="hidden" name="motivo_solicitud[]" value="' + esc(id) + '">' +
            (sugerido ? '<button type="button" class="sa-objeto__ok" data-ok aria-label="Confirmar ' + esc(CATALOGO[id]) + '" title="Confirmar"><i class="bi bi-check-lg"></i></button>' : '') +
            '<button type="button" data-quitar aria-label="Quitar ' + esc(CATALOGO[id]) + '" title="Quitar">&times;</button>';
        ficha.querySelector('[data-quitar]').addEventListener('click', function () { ficha.remove(); estadoObjetos(); contar(); });
        if (sugerido) {
            porRevisar.push(ficha);
            ficha.querySelector('[data-ok]').addEventListener('click', function () {
                ficha.classList.remove('campo-prellenado', 'is-sugerido');
                ficha.querySelectorAll('.sa-objeto__sugerido, [data-ok]').forEach(function (x) { x.remove(); });
                contar();
            });
        }
        caja.appendChild(ficha);
        estadoObjetos();
        return true;
    }
    function estadoObjetos() {
        var hay = caja.querySelectorAll('.sa-objeto').length > 0;
        vacio.hidden = hay;
        if (hay) { caja.classList.remove('is-error'); error.hidden = true; }
    }
    selectObjeto.addEventListener('change', function () {
        if (selectObjeto.value && !agregar(selectObjeto.value, false)) {
            if (window.swal) { swal('Ya está agregado', 'Ese objeto ya está en la lista.', 'info'); }
        }
        selectObjeto.value = '';
    });

    // Al menos un objeto (además de la validación de Bootstrap de los demás campos).
    form.addEventListener('submit', function (e) {
        if (!caja.querySelector('.sa-objeto')) {
            e.preventDefault();
            e.stopImmediatePropagation();
            caja.classList.add('is-error');
            error.hidden = false;
            form.classList.add('was-validated');
            caja.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }, true);

    // Prellenado de la cita.
    if (PRELLENADO) {
        if (PRELLENADO.municipio && municipio.querySelector('option[value="' + PRELLENADO.municipio + '"]')) {
            municipio.value = String(PRELLENADO.municipio);
            ponerDelegacion();
            resaltar(municipio);
        }
        (PRELLENADO.motivos || []).forEach(function (id) { agregar(id, true); });
        contar();
    }
})();
</script>
@include('solicitudes.auxiliares._pollLock')
