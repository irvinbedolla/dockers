@extends('layouts.app')
@section('title', 'Cancelación/Reagenda de citas')

@section('page_css')
    <style>
        :root { --color-guinda: #496163; --color-oro: #CEA845; }
        .rg-encabezado { background: var(--color-guinda); }
        .rg-actual { font-size: 13.5px; color: #6C7A7B; margin-bottom: 12px; }
        .rg-actual strong { color: #2E3C3D; }
        .rg-motivo { border-top: 1px solid #E3E8E8; padding-top: 14px; margin-top: 4px; }

        .rg-barra { display: flex; flex-wrap: wrap; justify-content: space-between; gap: 10px; margin-bottom: 14px; }
        .rg-buscar { display: flex; gap: 8px; flex: 1 1 320px; max-width: 520px; }
        .rg-dia { display: flex; align-items: center; gap: 6px; }
        .rg-dia input { width: auto; }
        .rg-titulo { font-size: 15px; font-weight: 600; color: #2E3C3D; margin-bottom: 8px; }
        .rg-titulo small { font-weight: 400; color: #6C7A7B; }

        .rg-estatus { display: inline-block; padding: 2px 9px; border-radius: 999px; font-size: 12px; font-weight: 600; text-transform: capitalize; }
        .rg-estatus--pendiente  { background: #FBF4E1; color: #7A5D10; }
        .rg-estatus--confirmada { background: #E3EEF9; color: #1F4E79; }
        .rg-estatus--atendido   { background: #E5F3EA; color: #1E7B3C; }
        .rg-estatus--expirada   { background: #F1F1F1; color: #6B6B6B; }
        .rg-estatus--cancelada  { background: #FBE4E6; color: #A3162E; }
        .rg-sin-accion { font-size: 12px; color: #8A9899; }
    </style>
    @include('turnos._horario_estilos')
@endsection

@section('content')
<section class="section">
    <div class="section-header d-flex justify-content-between">
        <h3 class="page__heading">Cancelación/Reagenda de citas</h3>
        @role('Super Usuario')
            <a href="{{ route('administracion_historial', 'cancelacion_reagenda_cita') }}" class="btn btn-outline-secondary btn-sm">Historial</a>
        @endrole
    </div>
    <div class="section-body">
        <div class="card"><div class="card-body">

            @foreach (['success' => 'success', 'error' => 'danger'] as $clave => $color)
                @if (session($clave))
                    <div class="alert alert-{{ $color }} alert-dismissible fade show" role="alert">
                        {{ session($clave) }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                @endif
            @endforeach
            @if ($errors->any())
                <div class="alert alert-dark"><ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
            @endif

            @php
                $buscando = mb_strlen($buscar) >= 3;
                $diaTexto = \Illuminate\Support\Str::ucfirst(\Illuminate\Support\Carbon::parse($fecha)->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY'));
            @endphp

            <div class="rg-barra">
                <form method="GET" class="rg-buscar">
                    <input type="search" name="buscar" value="{{ $buscar }}" class="form-control"
                           placeholder="Buscar por folio, CURP o nombre" minlength="3" required>
                    <button class="btn btn-primary">Buscar</button>
                    @if ($buscando)
                        <a href="{{ route('citas_cancelacion.index') }}" class="btn btn-outline-secondary">Limpiar</a>
                    @endif
                </form>

                {{-- Citas de un día: flechas para moverse y calendario para saltar. --}}
                <form method="GET" class="rg-dia">
                    <a href="{{ route('citas_cancelacion.index', ['fecha' => $diaAnterior]) }}" class="btn btn-outline-secondary" aria-label="Día anterior"><i class="bi bi-chevron-left"></i></a>
                    <input type="date" name="fecha" value="{{ $fecha }}" class="form-control" onchange="this.form.submit()" aria-label="Día">
                    <a href="{{ route('citas_cancelacion.index', ['fecha' => $diaSiguiente]) }}" class="btn btn-outline-secondary" aria-label="Día siguiente"><i class="bi bi-chevron-right"></i></a>
                    @if ($fecha !== $hoy || $buscando)
                        <a href="{{ route('citas_cancelacion.index') }}" class="btn btn-outline-secondary">Hoy</a>
                    @endif
                </form>
            </div>

            <div class="rg-titulo">
                @if ($buscando)
                    Resultados para “{{ $buscar }}” <small>· {{ $citas->count() }} {{ $citas->count() === 1 ? 'cita' : 'citas' }}{{ $citas->count() === 50 ? ' (se muestran las 50 más recientes)' : '' }}</small>
                @else
                    {{ $diaTexto }}{{ $fecha === $hoy ? ' (hoy)' : '' }}
                    <small>· {{ $citas->count() }} {{ $citas->count() === 1 ? 'cita' : 'citas' }} · {{ $citas->where('estatus', 'pendiente')->count() }} pendientes</small>
                @endif
            </div>

            @if ($citas->isEmpty())
                <p class="text-muted mb-0">{{ $buscando ? 'No se encontraron citas.' : 'No hay citas este día.' }}</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead><tr><th>Folio</th><th>Solicitante</th><th>Sede</th><th>Trámite</th><th>Fecha</th><th>Hora</th><th>Estatus</th><th></th></tr></thead>
                        <tbody>
                        @foreach ($citas as $cita)
                            <tr>
                                <td>{{ $cita->folio_cita }}</td>
                                <td>{{ $cita->solicitante }}</td>
                                <td>{{ $cita->delegacion }}</td>
                                <td>{{ $cita->tipo }}</td>
                                <td>{{ $cita->fecha->format('d/m/Y') }}</td>
                                <td>{{ substr($cita->getRawOriginal('hora'), 0, 5) }}</td>
                                <td><span class="rg-estatus rg-estatus--{{ $cita->estatus }}">{{ $cita->estatus }}</span></td>
                                <td class="text-end text-nowrap">
                                    @if (\App\Http\Controllers\CancelacionCitasController::movible($cita, $hoy))
                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                                data-bs-toggle="modal" data-bs-target="#modalReagendar"
                                                data-folio="{{ $cita->folio_cita }}"
                                                data-accion="{{ route('citas_cancelacion.reagendar', $cita->id) }}"
                                                data-sede="{{ $cita->delegacion }}"
                                                data-tipo="{{ $cita->tipo }}"
                                                data-excepcion="{{ $cita->exepcion === 'Si' ? 'Si' : 'No' }}"
                                                data-fecha="{{ $cita->fecha->format('Y-m-d') }}"
                                                data-hora="{{ substr($cita->getRawOriginal('hora'), 0, 5) }}">Reagendar</button>
                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                                data-bs-toggle="modal" data-bs-target="#modalCancelar"
                                                data-folio="{{ $cita->folio_cita }}"
                                                data-accion="{{ route('citas_cancelacion.cancelar', $cita->id) }}">Cancelar</button>
                                    @elseif ($cita->estatus === 'pendiente')
                                        <span class="rg-sin-accion">Ya pasó</span>
                                    @else
                                        <span class="rg-sin-accion">Sólo pendientes</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div></div>
    </div>
</section>
@endsection

@push('modals')
<div class="modal fade" id="modalCancelar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog"><form method="POST" class="modal-content">
        @csrf
        <div class="modal-header"><h5 class="modal-title">Cancelar cita <span data-folio></span></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
        <div class="modal-body">
            <label class="form-label">Motivo</label>
            <textarea name="motivo" class="form-control" rows="3" minlength="10" maxlength="500" required></textarea>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            <button class="btn btn-danger">Cancelar cita</button>
        </div>
    </form></div>
</div>

{{--
    Reagendar: el mismo selector de día y hora de /generar-cita (estilos en
    turnos._horario_estilos) y los mismos datos, /api/obtenerTurnosDisponibles,
    así que sólo ofrece horarios con lugar. Al guardar se vuelve a revisar.
--}}
<div class="modal fade" id="modalReagendar" tabindex="-1" aria-labelledby="rgTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg hc-dialogo"><form method="POST" class="modal-content">
        @csrf
        <input type="hidden" name="fecha">
        <input type="hidden" name="hora">
        <div class="modal-header rg-encabezado py-3">
            <div>
                <h5 class="modal-title text-white mb-0" id="rgTitulo"><i class="bi bi-calendar-week me-2" aria-hidden="true"></i>Reagendar cita <span data-folio></span></h5>
                <span class="hc-contexto" data-contexto></span>
            </div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>

        <div class="modal-body hc-cuerpo">
            <div class="rg-actual" data-actual></div>

            <div class="hc-semana">
                <button type="button" class="hc-flecha" data-anterior aria-label="Semana anterior"><i class="bi bi-chevron-left"></i></button>
                <span class="hc-semana__texto" data-semana aria-live="polite"></span>
                <button type="button" class="hc-flecha" data-siguiente aria-label="Semana siguiente"><i class="bi bi-chevron-right"></i></button>
            </div>
            <div class="hc-dias" data-dias role="tablist" aria-label="Días de la semana"></div>
            <div class="hc-horas" data-horas aria-live="polite"></div>

            <div class="rg-motivo">
                <label class="form-label" for="rgMotivo">Motivo de la reagenda</label>
                <textarea id="rgMotivo" name="motivo" class="form-control" rows="2" minlength="10" maxlength="500" required></textarea>
            </div>
        </div>

        <div class="modal-footer hc-pie">
            <div class="hc-elegido" data-elegido>Toca un horario para elegirlo.</div>
            <div class="hc-pie__botones">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button class="btn btn-primary" data-guardar disabled><i class="bi bi-check2 me-1" aria-hidden="true"></i>Reagendar</button>
            </div>
        </div>
    </form></div>
</div>
@endpush

@push('scripts')
<script>
    // Cada botón trae la URL de su cita; el diálogo la toma al abrirse.
    document.querySelectorAll('#modalCancelar, #modalReagendar').forEach(modal => {
        modal.addEventListener('show.bs.modal', e => {
            const boton = e.relatedTarget;
            modal.querySelector('form').reset();
            modal.querySelector('form').action = boton.dataset.accion;
            modal.querySelector('[data-folio]').textContent = boton.dataset.folio;
        });
    });

    // Selector de día y hora: la misma tira de días de /generar-cita
    // (_cita_scripts), con la sede, el trámite y la excepción de la cita.
    (function () {
        const URL_HORARIOS = "{{ url('/api/obtenerTurnosDisponibles') }}";
        const SEMANAS_A_BUSCAR = 8;    // al abrir, busca la primera semana con lugar
        const modal = document.getElementById('modalReagendar');
        const $ = s => modal.querySelector(s);
        const el = {
            semana: $('[data-semana]'), anterior: $('[data-anterior]'), siguiente: $('[data-siguiente]'),
            dias: $('[data-dias]'), horas: $('[data-horas]'), elegido: $('[data-elegido]'),
            contexto: $('[data-contexto]'), actual: $('[data-actual]'), guardar: $('[data-guardar]'),
            fecha: $('[name=fecha]'), hora: $('[name=hora]')
        };
        let cita = null;            // sede, tipo, excepcion, fecha y hora actuales
        let lunes = null, lunesMinimo = null, porDia = {}, diaActivo = null, elegido = null;
        let peticion = 0;           // descarta respuestas viejas si se navega rápido

        const pad = n => String(n).padStart(2, '0');
        const ymd = d => d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());
        const lunesDe = d => { const x = new Date(d.getFullYear(), d.getMonth(), d.getDate()); x.setDate(x.getDate() - (x.getDay() + 6) % 7); return x; };
        const masDias = (d, n) => { const x = new Date(d); x.setDate(x.getDate() + n); return x; };
        const fmt = (d, o) => d.toLocaleDateString('es-MX', o);
        const largo = f => { const t = fmt(new Date(f + 'T00:00:00'), { weekday: 'long', day: 'numeric', month: 'long' }).replace(',', ''); return t.charAt(0).toUpperCase() + t.slice(1); };
        const nodo = (tag, clase, texto) => { const n = document.createElement(tag); if (clase) n.className = clase; if (texto != null) n.textContent = texto; return n; };

        async function traerSemana(inicio) {
            const q = new URLSearchParams({ sede: cita.sede, tipo: cita.tipo, excepcion: cita.excepcion, start: ymd(inicio), end: ymd(masDias(inicio, 4)) });
            const r = await fetch(URL_HORARIOS + '?' + q.toString(), { headers: { Accept: 'application/json' } });
            if (!r.ok) throw new Error('HTTP ' + r.status);
            const dias = {};
            for (let i = 0; i < 5; i++) dias[ymd(masDias(inicio, i))] = [];
            (await r.json()).forEach(function (e) {
                const estado = e.extendedProps && e.extendedProps.estado;
                if (estado !== 'disponible' && estado !== 'turnos') return;
                const [f, h] = String(e.start).split('T');
                // Su propio horario no es una opción: sería no moverla.
                if (dias[f] && !(f === cita.fecha && h.slice(0, 5) === cita.hora)) dias[f].push(h.slice(0, 5));
            });
            Object.keys(dias).forEach(k => dias[k].sort());
            return dias;
        }

        function mensaje(icono, texto, accion) {
            el.horas.textContent = '';
            const m = nodo('div', 'hc-mensaje');
            m.appendChild(nodo('i', 'bi ' + icono));
            m.appendChild(nodo('div', null, texto));
            if (accion) {
                const b = nodo('button', 'btn btn-outline-secondary btn-sm', accion.texto);
                b.type = 'button';
                b.addEventListener('click', accion.fn);
                m.appendChild(b);
            }
            el.horas.appendChild(m);
        }

        function pintarSemana() {
            const viernes = masDias(lunes, 4);
            el.semana.textContent = lunes.getMonth() === viernes.getMonth()
                ? lunes.getDate() + ' al ' + viernes.getDate() + ' de ' + fmt(viernes, { month: 'long', year: 'numeric' })
                : fmt(lunes, { day: 'numeric', month: 'short' }) + ' al ' + fmt(viernes, { day: 'numeric', month: 'short', year: 'numeric' });
            el.anterior.disabled = lunes <= lunesMinimo;

            el.dias.textContent = '';
            Object.keys(porDia).forEach(function (f) {
                const d = new Date(f + 'T00:00:00');
                const libres = porDia[f].length;
                const b = nodo('button', 'hc-dia' + (f === diaActivo ? ' is-activo' : ''));
                b.type = 'button';
                b.setAttribute('role', 'tab');
                b.setAttribute('aria-selected', f === diaActivo ? 'true' : 'false');
                b.disabled = libres === 0;
                b.appendChild(nodo('span', 'hc-dia__semana', fmt(d, { weekday: 'short' }).replace('.', '')));
                b.appendChild(nodo('span', 'hc-dia__numero', String(d.getDate())));
                b.appendChild(nodo('span', 'hc-dia__mes', fmt(d, { month: 'short' }).replace('.', '')));
                b.appendChild(nodo('span', 'hc-dia__libres', libres ? (libres === 1 ? '1 horario' : libres + ' horarios') : 'Sin lugar'));
                b.addEventListener('click', function () { diaActivo = f; pintarSemana(); pintarHoras(); });
                el.dias.appendChild(b);
            });
        }

        function pintarHoras() {
            if (!diaActivo) {
                mensaje('bi-calendar-x', 'No hay horarios libres esta semana.', { texto: 'Ver la semana siguiente', fn: () => irA(masDias(lunes, 7)) });
                return;
            }
            el.horas.textContent = '';
            const horas = porDia[diaActivo];
            [['Mañana', horas.filter(h => h < '13:00')], ['Tarde', horas.filter(h => h >= '13:00')]].forEach(function ([titulo, lista]) {
                if (!lista.length) return;
                const g = nodo('div', 'hc-grupo');
                g.appendChild(nodo('div', 'hc-grupo__titulo', titulo));
                const cont = nodo('div', 'hc-grupo__horas');
                lista.forEach(function (h) {
                    const b = nodo('button', 'hc-hora', h);
                    b.type = 'button';
                    const es = elegido && elegido.fecha === diaActivo && elegido.hora === h;
                    if (es) b.classList.add('is-elegida');
                    b.setAttribute('aria-pressed', es ? 'true' : 'false');
                    b.addEventListener('click', () => elegir(diaActivo, h));
                    cont.appendChild(b);
                });
                g.appendChild(cont);
                el.horas.appendChild(g);
            });
        }

        function elegir(fecha, hora) {
            elegido = { fecha, hora };
            el.fecha.value = fecha;
            el.hora.value = hora;
            el.elegido.textContent = '';
            el.elegido.appendChild(document.createTextNode('Nuevo horario: '));
            el.elegido.appendChild(nodo('strong', null, largo(fecha) + ' · ' + hora + ' h'));
            el.guardar.disabled = false;
            pintarHoras();
        }

        async function irA(inicio, buscarHaciaAdelante) {
            const mia = ++peticion;
            lunes = inicio;
            diaActivo = null;
            el.dias.textContent = '';
            el.semana.textContent = 'Buscando horarios…';
            mensaje('bi-hourglass-split', 'Buscando horarios disponibles…');
            try {
                let intentos = buscarHaciaAdelante ? SEMANAS_A_BUSCAR : 1;
                let dias = await traerSemana(lunes);
                while (--intentos > 0 && !Object.values(dias).some(h => h.length)) {
                    lunes = masDias(lunes, 7);
                    dias = await traerSemana(lunes);
                    if (mia !== peticion) return;
                }
                if (mia !== peticion) return;
                porDia = dias;
                diaActivo = Object.keys(porDia).find(f => porDia[f].length) || null;
                pintarSemana();
                pintarHoras();
            } catch (e) {
                if (mia !== peticion) return;
                el.semana.textContent = '';
                mensaje('bi-wifi-off', 'No se pudieron cargar los horarios.', { texto: 'Intentar de nuevo', fn: () => irA(lunes) });
            }
        }

        el.anterior.addEventListener('click', () => irA(masDias(lunes, -7)));
        el.siguiente.addEventListener('click', () => irA(masDias(lunes, 7)));

        modal.addEventListener('show.bs.modal', function (e) {
            const b = e.relatedTarget.dataset;
            cita = { sede: b.sede, tipo: b.tipo, excepcion: b.excepcion, fecha: b.fecha, hora: b.hora };
            elegido = null;
            el.fecha.value = el.hora.value = '';
            el.guardar.disabled = true;
            el.elegido.textContent = 'Toca un horario para elegirlo.';
            el.contexto.textContent = 'Oficina ' + cita.sede + ' · ' + cita.tipo + (cita.excepcion === 'Si' ? ' · Excepción' : '');
            el.actual.textContent = '';
            el.actual.appendChild(document.createTextNode('Cita actual: '));
            el.actual.appendChild(nodo('strong', null, largo(cita.fecha) + ' · ' + cita.hora + ' h'));

            // Sábado o domingo: la primera semana útil es la siguiente.
            const hoy = new Date();
            lunesMinimo = lunesDe(hoy.getDay() === 0 || hoy.getDay() === 6 ? masDias(hoy, 2) : hoy);
            irA(lunesMinimo, true);
        });
    })();
</script>
@endpush
