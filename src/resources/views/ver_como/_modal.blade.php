{{--
    Modal "Ver como" del encabezado (botón del ojo, sólo Super Usuario).
    Persona: busca por nombre o correo (VerComoController::buscar).
    Rol: roles + sede. Ambos mandan a VerComoController::iniciar.
--}}
@php
    $vcReal    = \App\Support\VerComo::real() ?? auth()->user();
    $vcDatos   = \App\Support\VerComo::activo() ? app('ver_como.datos') : null;
    $vcFrase   = $vcDatos ? \App\Support\VerComo::frase($vcDatos, auth()->user()) : null;
    $vcRoles   = \Spatie\Permission\Models\Role::where('guard_name', 'web')->orderBy('name')->pluck('name')->all();
    $vcSedes   = \App\Support\Recepcion::TODAS_LAS_SEDES;
@endphp
<style>
    /* Botón del ojo en el encabezado (layouts.header). */
    .vc-ojo { position: relative; display: inline-flex !important; align-items: center; justify-content: center;
        width: 38px; height: 38px; padding: 0 !important; flex: 0 0 38px; border-radius: 50%; color: #fff !important; font-size: 19px; line-height: 1; transition: background .15s; }
    .vc-ojo:hover, .vc-ojo:focus { background: rgba(255, 255, 255, .14); }
    .vc-ojo.is-activo { background: #CEA845; color: #2E3C3D !important; }
    .vc-ojo.is-activo::after { content: ''; position: absolute; top: 4px; right: 4px; width: 9px; height: 9px; border-radius: 50%;
        background: #E2445C; border: 2px solid #496163; }
    .vc-modal .modal-header { background: #2E3C3D; color: #fff; }
    .vc-modal .modal-header .btn-close { filter: invert(1) grayscale(1); }
    .vc-modal__actual { display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap;
        padding: 10px 12px; margin-bottom: 14px; border-radius: 10px; background: #FBF7EA; border: 1px solid #EBDDB0; font-size: 13.5px; }
    .vc-modal .nav-pills .nav-link { font-weight: 600; font-size: 14px; color: #496163; }
    .vc-modal .nav-pills .nav-link.active { background: #496163; color: #fff; }
    .vc-resultados { max-height: 330px; overflow-y: auto; margin-top: 10px; }
    .vc-persona { display: flex; justify-content: space-between; align-items: center; gap: 10px; width: 100%;
        padding: 9px 10px; border: 1px solid #E3E8E8; border-radius: 10px; background: #fff; text-align: left; margin-bottom: 6px; }
    .vc-persona:hover, .vc-persona:focus { border-color: #496163; background: #F7F9F9; }
    .vc-persona b { display: block; font-size: 14px; color: #2E3C3D; }
    .vc-persona small { display: block; color: #6C7A7B; font-size: 12.5px; }
    .vc-persona .vc-ir { white-space: nowrap; font-size: 12.5px; font-weight: 700; color: #496163; }
    .vc-vacio { color: #6C7A7B; font-size: 13.5px; padding: 10px 2px; }
    .vc-roles-modal { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 2px 10px;
        max-height: 250px; overflow-y: auto; padding: 8px 10px; border: 1px solid #E3E8E8; border-radius: 8px; }
    .vc-roles-modal .form-check-label { font-size: 13.5px; }
    .vc-previa { margin-top: 10px; font-size: 13.5px; color: #5B6B6C; }
    .vc-previa b { color: #2E3C3D; }
</style>

<div class="modal fade vc-modal" id="vcModal" tabindex="-1" aria-labelledby="vcModalTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="vcModalTitulo"><i class="bi bi-eye me-2" aria-hidden="true"></i>Ver como</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                @if ($vcFrase)
                    <div class="vc-modal__actual">
                        <span><i class="bi bi-eye-fill me-1" aria-hidden="true"></i> Estás viendo como <b>{{ $vcFrase }}</b>.</span>
                        <form method="POST" action="{{ route('ver_como.salir') }}" class="m-0">@csrf
                            <button class="btn btn-sm btn-dark"><i class="bi bi-x-circle me-1"></i>Salir de "Ver como"</button>
                        </form>
                    </div>
                @else
                    <p class="small text-muted mb-3">Mira el sistema como lo ve otra persona o un rol: su menú, sus permisos y su sede. Es de sólo lectura: no se guarda nada.</p>
                @endif

                <ul class="nav nav-pills mb-3" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="vcTabPersona" data-bs-toggle="pill" data-bs-target="#vcPanelPersona" type="button" role="tab" aria-controls="vcPanelPersona" aria-selected="true">
                            <i class="bi bi-person me-1"></i>Una persona
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="vcTabRol" data-bs-toggle="pill" data-bs-target="#vcPanelRol" type="button" role="tab" aria-controls="vcPanelRol" aria-selected="false">
                            <i class="bi bi-people me-1"></i>Un rol y su sede
                        </button>
                    </li>
                </ul>

                <div class="tab-content">
                    {{-- Persona --}}
                    <div class="tab-pane fade show active" id="vcPanelPersona" role="tabpanel" aria-labelledby="vcTabPersona">
                        <label class="form-label small fw-bold" for="vcBuscar">Nombre o correo</label>
                        <input type="search" id="vcBuscar" class="form-control" placeholder="Escribe al menos 2 letras…" autocomplete="off">
                        <div class="vc-resultados" id="vcResultados" aria-live="polite">
                            <div class="vc-vacio">Busca a la persona para ver el sistema como ella.</div>
                        </div>
                        <form method="POST" action="{{ route('ver_como.iniciar') }}" id="vcFormPersona" hidden>
                            @csrf
                            <input type="hidden" name="tipo" value="usuario">
                            <input type="hidden" name="user_id" id="vcUserId">
                        </form>
                    </div>

                    {{-- Rol --}}
                    <div class="tab-pane fade" id="vcPanelRol" role="tabpanel" aria-labelledby="vcTabRol">
                        <form method="POST" action="{{ route('ver_como.iniciar') }}" id="vcFormRol">
                            @csrf
                            <input type="hidden" name="tipo" value="rol">
                            <label class="form-label small fw-bold">Rol (puedes marcar varios; un auxiliar lleva "Auxiliar" y su especialidad)</label>
                            <div class="vc-roles-modal">
                                @foreach ($vcRoles as $i => $r)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $r }}" id="vcModalRol{{ $i }}">
                                        <label class="form-check-label" for="vcModalRol{{ $i }}">{{ $r }}</label>
                                    </div>
                                @endforeach
                            </div>
                            <label class="form-label small fw-bold mt-3" for="vcModalSede">Sede</label>
                            <select id="vcModalSede" name="sede" class="form-select" required>
                                <option value="">Elige la sede…</option>
                                @foreach ($vcSedes as $s)<option value="{{ $s }}">{{ $s }}</option>@endforeach
                            </select>
                            <div class="vc-previa" id="vcPrevia" hidden></div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <a href="{{ route('ver_como.index') }}" class="btn btn-link btn-sm me-auto">Ver la lista completa</a>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" form="vcFormRol" class="btn btn-primary" id="vcAplicarRol" disabled><i class="bi bi-eye me-1"></i>Ver como este rol</button>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        var URL_BUSCAR = @json(route('ver_como.buscar'));
        var modal = document.getElementById('vcModal');
        if (!modal) return;
        var buscar = document.getElementById('vcBuscar'), lista = document.getElementById('vcResultados');
        var formPersona = document.getElementById('vcFormPersona'), userId = document.getElementById('vcUserId');
        var formRol = document.getElementById('vcFormRol'), aplicar = document.getElementById('vcAplicarRol'), previa = document.getElementById('vcPrevia');
        var tabRol = document.getElementById('vcTabRol'), tabPersona = document.getElementById('vcTabPersona');
        var espera = null, peticion = 0;

        function esc(t) { return String(t == null ? '' : t).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
        function vacio(texto) { lista.innerHTML = '<div class="vc-vacio">' + esc(texto) + '</div>'; }

        buscar.addEventListener('input', function () {
            clearTimeout(espera);
            var q = buscar.value.trim();
            if (q.length < 2) { vacio('Busca a la persona para ver el sistema como ella.'); return; }
            espera = setTimeout(function () {
                var mia = ++peticion;
                vacio('Buscando…');
                fetch(URL_BUSCAR + '?q=' + encodeURIComponent(q), { headers: { 'Accept': 'application/json' } })
                    .then(function (r) { return r.json(); })
                    .then(function (j) {
                        if (mia !== peticion) return;
                        if (!j.personas || !j.personas.length) { vacio('No encontramos a nadie con "' + q + '".'); return; }
                        lista.innerHTML = j.personas.map(function (p) {
                            return '<button type="button" class="vc-persona" data-id="' + p.id + '">' +
                                '<span><b>' + esc(p.nombre) + (p.inactivo ? ' <span class="text-danger small">· Inactivo</span>' : '') + '</b>' +
                                '<small>' + esc(p.cargo) + (p.correo ? ' · ' + esc(p.correo) : '') + '</small></span>' +
                                '<span class="vc-ir"><i class="bi bi-eye me-1"></i>Ver como</span></button>';
                        }).join('');
                    })
                    .catch(function () { if (mia === peticion) vacio('No se pudo buscar; intenta de nuevo.'); });
            }, 300);
        });
        lista.addEventListener('click', function (e) {
            var b = e.target.closest('.vc-persona');
            if (!b) return;
            userId.value = b.getAttribute('data-id');
            b.disabled = true;
            formPersona.submit();
        });

        // Rol: el botón se habilita con al menos un rol y la sede; muestra cómo se va a leer.
        function revisarRol() {
            var roles = Array.prototype.map.call(formRol.querySelectorAll('input[name="roles[]"]:checked'), function (c) { return c.value; });
            var sede = formRol.elements.sede.value;
            aplicar.disabled = !(roles.length && sede);
            previa.hidden = !(roles.length && sede);
            if (roles.length && sede) {
                var mostrar = roles.indexOf('Auxiliar') !== -1 && roles.some(function (r) { return r.indexOf('Auxiliar ') === 0; })
                    ? roles.filter(function (r) { return r !== 'Auxiliar'; }) : roles;
                previa.innerHTML = 'Verás el sistema como <b>' + esc(mostrar.join(' y ')) + '</b> de <b>' + esc(sede) + '</b>.';
            }
        }
        formRol.addEventListener('change', revisarRol);
        // El botón de aplicar sólo tiene sentido en la pestaña de rol.
        function botonSegunPestana(enRol) { aplicar.hidden = !enRol; }
        tabRol.addEventListener('shown.bs.tab', function () { botonSegunPestana(true); });
        tabPersona.addEventListener('shown.bs.tab', function () { botonSegunPestana(false); buscar.focus(); });
        botonSegunPestana(false);
        modal.addEventListener('shown.bs.modal', function () { if (!tabRol.classList.contains('active')) buscar.focus(); });
    })();
</script>
