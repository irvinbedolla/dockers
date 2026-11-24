@extends('ratificacion_multiple._layout')
@section('titulo', 'Solicitud de ratificación múltiple')
{{--
    /ratificacion-multiple (RatificacionMultipleController::formulario): solicitud
    patronal de ratificación múltiple de convenios, con las secciones del formato
    institucional. El folio de representación es obligatorio.
--}}
@section('contenido')
<div class="rm-card">
    <div class="rm-card__cab">
        <h1><i class="bi bi-people-fill me-2"></i>Solicitud patronal de ratificación múltiple de convenios</h1>
        <p>Para {{ $minimo }} o más trabajadores del mismo patrón: una sola solicitud con la relación de trabajadores en Excel. Cada trabajador tiene su cita, su convenio y su acta.</p>
    </div>
    <div class="p-4">
        @if ($errors->any())
            <div class="alert alert-danger">
                <b>Revisa lo siguiente:</b>
                <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="alert alert-light border d-flex gap-3 align-items-start">
            <i class="bi bi-info-circle-fill fs-5" style="color: var(--rm-verde);"></i>
            <div class="rm-ayuda" style="font-size: 14px;">
                <b>Antes de empezar</b>
                <ol class="mb-0 ps-3">
                    <li>Necesitas tu <b>folio de representación patronal</b>. Si no lo tienes, primero <a href="{{ route('poder-crear') }}" target="_blank" rel="noopener">registra tu poder</a>.</li>
                    <li>Descarga el <a href="{{ route('ratificacion_multiple.formato') }}"><b>formato para ratificaciones múltiples</b></a> y llena un renglón por trabajador (el renglón 13 es el ejemplo).</li>
                    <li>El sistema revisa el archivo antes de agendar: si algún monto queda por debajo del mínimo legal, te lo indica para que lo corrijas.</li>
                </ol>
            </div>
        </div>

        <form method="POST" action="{{ route('ratificacion_multiple.revisar') }}" enctype="multipart/form-data" id="rmForm" novalidate>
            @csrf

            <fieldset class="rm-seccion">
                <legend>I y II. Parte patronal y representante legal</legend>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label" for="rmFolio">Folio de representación patronal <span class="rm-req">*</span></label>
                        <input type="text" id="rmFolio" name="poder_folio" class="form-control" inputmode="numeric" maxlength="10" required
                               value="{{ old('poder_folio') }}" autocomplete="off" oninput="this.value = this.value.replace(/\D/g, '').slice(0, 10);">
                        <div class="form-text"><a href="{{ route('poder-crear') }}" target="_blank" rel="noopener">¿No tienes folio? Registra tu poder</a></div>
                    </div>
                    <div class="col-md-8">
                        <div id="rmFolioInfo" class="rm-folio rm-folio--espera">
                            Escribe tu folio: la razón social, el domicilio y los datos del representante se toman de él.
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset class="rm-seccion">
                <legend>III. Datos de la solicitud</legend>
                <div class="row g-3">
                    <div class="col-md-6">
                        <span class="form-label d-block">Motivo de la ratificación <span class="rm-req">*</span></span>
                        @foreach ($motivos as $clave => $texto)
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="motivo" id="rmMotivo{{ $loop->index }}" value="{{ $clave }}" required @checked(old('motivo') === $clave)>
                                <label class="form-check-label" for="rmMotivo{{ $loop->index }}">{{ $loop->iteration }}. {{ $texto }}</label>
                            </div>
                        @endforeach
                    </div>
                    <div class="col-md-6">
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label class="form-label" for="rmSede">Delegación regional <span class="rm-req">*</span></label>
                                <select id="rmSede" name="delegacion" class="form-select" required>
                                    <option value="">Elige una</option>
                                    @foreach ($sedes as $s)<option value="{{ $s }}" @selected(old('delegacion') === $s)>{{ $s }}</option>@endforeach
                                </select>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label" for="rmFecha">Fecha propuesta <span class="rm-req">*</span></label>
                                <input type="date" id="rmFecha" name="fecha_propuesta" class="form-control" required
                                       min="{{ now()->addDay()->toDateString() }}" max="{{ now()->addDays(59)->toDateString() }}" value="{{ old('fecha_propuesta') }}">
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label" for="rmN">Número de trabajadores <span class="rm-req">*</span></label>
                                <input type="number" id="rmN" name="trabajadores_declarados" class="form-control" min="{{ $minimo }}" max="100" required value="{{ old('trabajadores_declarados') }}">
                            </div>
                        </div>
                        <div class="form-text mt-2">Las citas (15 min por trabajador) se reparten en máximo 3 días hábiles a partir de la fecha propuesta, con hasta 3 trabajadores a la misma hora.</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="rmObs">Observaciones generales <span class="rm-ayuda">(opcional)</span></label>
                        <textarea id="rmObs" name="observaciones" class="form-control" rows="2" maxlength="1000">{{ old('observaciones') }}</textarea>
                    </div>
                </div>
            </fieldset>

            <fieldset class="rm-seccion">
                <legend>IV. Relación de trabajadores y convenios</legend>
                <div class="row g-3 align-items-end">
                    <div class="col-md-7">
                        <label class="form-label" for="rmArchivo">Formato para ratificaciones múltiples (.xlsx) <span class="rm-req">*</span></label>
                        <input type="file" id="rmArchivo" name="archivo" class="form-control" accept=".xlsx" required>
                    </div>
                    <div class="col-md-5">
                        <a class="btn btn-outline-secondary w-100" href="{{ route('ratificacion_multiple.formato') }}"><i class="bi bi-file-earmark-excel me-1"></i> Descargar el formato</a>
                    </div>
                </div>
                <div class="form-text mb-2">Sube el formato sin cambiar su acomodo. Lo revisamos antes de agendar y te decimos qué corregir, renglón por renglón.</div>
            </fieldset>

            <fieldset class="rm-seccion">
                <legend>V. Documentación anexa</legend>
                <p class="rm-ayuda mb-2"><i class="bi bi-check2-circle me-1"></i> La identificación oficial del representante y el documento que acredita su personalidad se toman de tu folio de representación.</p>
                <div class="row g-3">
                    <div class="col-md-7">
                        <label class="form-label" for="rmConstancia">Constancia de situación fiscal <span class="rm-ayuda">(opcional, PDF)</span></label>
                        <input type="file" id="rmConstancia" name="constancia" class="form-control" accept="application/pdf">
                    </div>
                </div>
                <div class="mb-2"></div>
            </fieldset>

            <fieldset class="rm-seccion">
                <legend>Contacto de quien registra</legend>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="rmNombre">Nombre completo <span class="rm-req">*</span></label>
                        <input type="text" id="rmNombre" name="solicitante" class="form-control" maxlength="150" required value="{{ old('solicitante') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="rmTel">Teléfono (10 dígitos) <span class="rm-req">*</span></label>
                        <input type="tel" id="rmTel" name="telefono" class="form-control" inputmode="numeric" maxlength="10" required value="{{ old('telefono') }}"
                               oninput="this.value = this.value.replace(/\D/g, '').slice(0, 10);">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="rmCorreo">Correo electrónico <span class="rm-req">*</span></label>
                        <input type="email" id="rmCorreo" name="correo" class="form-control" maxlength="150" required value="{{ old('correo') }}">
                        <div class="form-text">Ahí te llega el acuse con todas las citas.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="rmCorreo2">Confirma tu correo <span class="rm-req">*</span></label>
                        <input type="email" id="rmCorreo2" name="correo_confirmation" class="form-control" maxlength="150" required value="{{ old('correo_confirmation') }}" autocomplete="off">
                    </div>
                </div>
                <div class="mb-2"></div>
            </fieldset>

            <fieldset class="rm-seccion">
                <legend>VI. Declaración del solicitante</legend>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="rmDeclaracion" name="declaracion" value="1" required @checked(old('declaracion'))>
                    <label class="form-check-label" for="rmDeclaracion" style="font-size: 14px; text-align: justify;">
                        Manifiesto bajo protesta de decir verdad que la información proporcionada es cierta, que los datos individualizados corresponden a cada
                        trabajador y que solicito la intervención del Centro de Conciliación Laboral del Estado de Michoacán para llevar a cabo las actuaciones
                        correspondientes para la ratificación de los convenios presentados. Asimismo, reconozco que cada convenio será objeto de revisión y atención
                        individual, y que la presentación de esta solicitud no implica su aprobación automática ni sustituye la comparecencia y manifestación de
                        voluntad de las partes.
                    </label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="rmPriv" name="acepta_privacidad" value="1" required @checked(old('acepta_privacidad'))>
                    <label class="form-check-label" for="rmPriv" style="font-size: 14px;">
                        He leído el <a href="#" data-bs-toggle="modal" data-bs-target="#rmPrivacidad">Aviso de privacidad</a> y acepto el uso de mis datos para esta solicitud.
                    </label>
                </div>
            </fieldset>

            <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
                <a href="{{ route('citas') }}" class="btn btn-link px-0"><i class="bi bi-arrow-left"></i> Volver a citas en línea</a>
                <button type="submit" class="btn btn-rm-oro btn-lg" id="rmEnviar">
                    <span class="rm-boton-texto"><i class="bi bi-search me-1"></i> Revisar mi formato</span>
                    <span class="rm-cargando"><span class="spinner-border spinner-border-sm me-1"></span> Revisando el archivo…</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
(function () {
    var folio = document.getElementById('rmFolio');
    var info = document.getElementById('rmFolioInfo');
    var URL_FOLIO = @json(url('/validar_folio_abogado'));
    var espera = null, consulta = 0, folioOk = false;
    function esc(t) { return String(t == null ? '' : t).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
    function pintar(clase, html) { info.className = 'rm-folio rm-folio--' + clase; info.innerHTML = html; }

    // Mismo servicio que el formulario de citas: sólo un folio elegible deja continuar.
    function validar() {
        clearTimeout(espera);
        var v = folio.value.trim();
        folioOk = false;
        if (!v) { pintar('espera', 'Escribe tu folio: la razón social, el domicilio y los datos del representante se toman de él.'); return; }
        pintar('espera', '<span class="spinner-border spinner-border-sm me-1"></span> Buscando el folio…');
        var esta = ++consulta;
        espera = setTimeout(function () {
            fetch(URL_FOLIO + '/' + encodeURIComponent(v), { headers: { 'Accept': 'application/json' } })
                .then(function (r) { return r.ok ? r.json() : Promise.reject(r.status); })
                .then(function (d) {
                    if (esta !== consulta) return;
                    if (!d || d.success === false) return Promise.reject(404);
                    var rep = [d.nombre_representante, d.primer_apellido_representante, d.segundo_apellido_representante].filter(Boolean).join(' ');
                    folioOk = d.status === 'elegible';
                    pintar(folioOk ? 'ok' : 'error', '<b>' + esc(d.nombre) + '</b>' + (rep ? '<br>Representante: ' + esc(rep) : '') + '<br>' +
                        (folioOk ? '<i class="bi bi-check-circle-fill"></i> Folio elegible' : '<i class="bi bi-exclamation-triangle-fill"></i> ' + esc(d.message || 'El folio no es elegible.')));
                    var nombre = document.getElementById('rmNombre');
                    if (folioOk && rep && !nombre.value) nombre.value = rep.toUpperCase();
                })
                .catch(function (e) {
                    if (esta !== consulta) return;
                    pintar('error', '<i class="bi bi-exclamation-triangle-fill"></i> ' + (e === 404
                        ? 'El folio no existe. Revísalo o <a href="{{ route('poder-crear') }}" target="_blank" rel="noopener">registra tu poder</a>.'
                        : 'No se pudo validar el folio. Inténtalo de nuevo.'));
                });
        }, 450);
    }
    folio.addEventListener('input', validar);
    if (folio.value) validar();

    // Revisión en pantalla antes de enviar (con SweetAlert, nunca el aviso del navegador).
    var form = document.getElementById('rmForm');
    form.addEventListener('submit', function (e) {
        var faltan = [];
        if (!folio.value) faltan.push('el folio de representación patronal');
        else if (!folioOk) faltan.push('un folio de representación elegible');
        if (!form.querySelector('[name=motivo]:checked')) faltan.push('el motivo de la ratificación');
        ['rmSede', 'rmFecha', 'rmN', 'rmArchivo', 'rmNombre', 'rmTel', 'rmCorreo', 'rmCorreo2'].forEach(function (id) {
            var el = document.getElementById(id);
            if (!el.value) faltan.push(form.querySelector('label[for=' + id + ']').textContent.replace('*', '').trim().toLowerCase());
        });
        var correo = document.getElementById('rmCorreo').value, correo2 = document.getElementById('rmCorreo2').value;
        if (correo && correo2 && correo.toLowerCase() !== correo2.toLowerCase()) faltan.push('que los dos correos coincidan');
        if (!document.getElementById('rmDeclaracion').checked) faltan.push('aceptar la declaración bajo protesta');
        if (!document.getElementById('rmPriv').checked) faltan.push('aceptar el aviso de privacidad');
        if (faltan.length) {
            e.preventDefault();
            Swal.fire({ icon: 'warning', title: 'Falta información', html: 'Revisa: <br>• ' + faltan.map(esc).join('<br>• '), confirmButtonColor: '#496163', confirmButtonText: 'Entendido' });
            return;
        }
        form.classList.add('is-enviando');
        document.getElementById('rmEnviar').disabled = true;
    });
})();
</script>
@endsection
