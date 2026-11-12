@extends('layouts.app')
@section('title', 'Agenda de '.$conciliador->name)

{{--
    Administración → Conciliadores → editar (ConciliadoresController). Una
    tarjeta por sede: modalidad, días, horario y sala. Reglas en
    App\Support\SedesConciliador::validar; la vista previa de la semana se
    arma en el navegador con los mismos datos.
--}}

@php
    use App\Support\SedesConciliador as SC;
    use App\Models\PermisosConciliador;

    $filas = old('sedes', array_map(fn ($s) => [
        'sede' => $s['sede'], 'modalidad' => $s['modalidad'], 'dias' => $s['dias'],
        'inicio' => $s['inicio'], 'fin' => $s['fin'], 'sala' => $s['sala'],
        'desde' => $s['desde'] ?? null, 'hasta' => $s['hasta'] ?? null, 'exclusiva' => $s['exclusiva'] ?? false,
    ], $sedes));
    if (! $filas) {
        $filas = [['sede' => $conciliador->delegacion, 'modalidad' => SC::PRESENCIAL, 'dias' => array_keys(PermisosConciliador::DIAS),
                   'inicio' => PermisosConciliador::PREDETERMINADO['inicio'], 'fin' => PermisosConciliador::PREDETERMINADO['fin'], 'sala' => null]];
    }
@endphp

@section('page_css')
    @include('administracion.conciliadores._estilos')
@endsection

@section('content')
    <section class="section tn">
        <div class="section-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <a href="{{ route('conciliadores.index') }}" class="text-decoration-none small" style="color: var(--tn-verde-claro);">
                    <i class="bi bi-arrow-left"></i> Conciliadores
                </a>
                <h3 class="page__heading mb-0">{{ $conciliador->name }}</h3>
                <div class="cn-sub">
                    Adscrito a {{ $conciliador->delegacion ?: 'sin sede' }} ·
                    {{ $porSede ? 'Agenda por sede' : 'Agenda de siempre' }}
                    @if (count($sedes) > 1) · <span class="cn-insignia cn-insignia--multi"><i class="bi bi-diagram-3-fill"></i> Agenda múltiple</span>@endif
                </div>
            </div>
        </div>

        <div class="section-body">
            @foreach (['success' => 'success', 'warning' => 'warning', 'error' => 'danger'] as $clave => $color)
                @if (session($clave))
                    <div class="alert alert-{{ $color }} alert-dismissible fade show" role="alert">
                        {{ session($clave) }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                @endif
            @endforeach
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Revisa la agenda:</strong>
                    <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            @endif

            <div class="tn-tarjeta mb-3">
                <ul class="cn-reglas">
                    <li>Puede atender varias sedes, aunque no sean de su delegación: <strong>a lo más una presencial</strong> y las demás <strong>virtuales</strong>.</li>
                    <li>Cada sede tiene sus <strong>días fijos</strong> y su horario. El reparto automático sólo le da audiencias de una sede en sus días y horas de esa sede, y nunca dos a la misma hora.</li>
                    <li>El citatorio, el acta y el convenio salen de la sede donde se celebra la audiencia. Fuera de su sede se imprime la sala que pongas aquí (si no, «{{ SC::SALA_FUERA }}»).</li>
                    <li>Los cambios aplican a las audiencias que se agenden desde ahora: las ya agendadas no se mueven.</li>
                </ul>
                @if ($pendientes)
                    <div class="cn-pendientes mt-2">
                        <i class="bi bi-calendar-event"></i> Audiencias por celebrar:
                        {{ collect($pendientes)->map(fn ($n, $s) => "{$s} {$n}")->implode(' · ') }}
                    </div>
                @endif
            </div>

            <form method="POST" action="{{ route('conciliadores.guardar', $conciliador->id) }}" id="cnForm" novalidate>
                @csrf
                @method('PUT')

                <div id="cnFilas">
                    @foreach ($filas as $i => $f)
                        @include('administracion.conciliadores._fila', ['i' => $i, 'f' => $f, 'conciliador' => $conciliador])
                    @endforeach
                </div>

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="cnAgregar"><i class="bi bi-plus-lg"></i> Agregar sede</button>
                    <span class="cn-sub" id="cnContador"></span>
                </div>

                <div class="tn-tarjeta mt-3">
                    <h5 class="mb-2" style="font-size: 15px; font-weight: 700; color: var(--tn-tinta);">Así queda su semana</h5>
                    <div class="table-responsive">
                        <table class="cn-tabla cn-previa">
                            <thead><tr>@foreach (PermisosConciliador::DIAS as $dia => $corto)<th class="cn-dia">{{ $corto }}</th>@endforeach</tr></thead>
                            <tbody><tr id="cnPrevia">@foreach (PermisosConciliador::DIAS as $dia => $corto)<td class="cn-dia" data-dia="{{ $dia }}"></td>@endforeach</tr></tbody>
                        </table>
                    </div>
                    <div id="cnAvisos" aria-live="polite"></div>
                </div>

                <div class="tn-tarjeta mt-3">
                    <label for="cnMotivo" class="cn-sub fw-bold">Motivo del cambio (opcional, queda en la bitácora)</label>
                    <input type="text" class="form-control" id="cnMotivo" name="motivo" maxlength="255" value="{{ old('motivo') }}" placeholder="Ej. Oficio de comisión a Uruapan, del 15 de noviembre">
                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <a href="{{ route('conciliadores.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                        <button type="submit" class="btn tn-boton-dorado"><i class="bi bi-check2"></i> Guardar agenda</button>
                    </div>
                </div>
            </form>

            @if ($porSede)
                <form method="POST" action="{{ route('conciliadores.quitar', $conciliador->id) }}" class="tn-tarjeta mt-3 d-flex justify-content-between align-items-center flex-wrap gap-2"
                      onsubmit="return confirm('¿Quitar la agenda por sede? Vuelve a la agenda de siempre ({{ $conciliador->delegacion }} y su subsede, según quedó). Las audiencias ya agendadas no se mueven.');">
                    @csrf
                    @method('DELETE')
                    <span class="cn-sub">Quitar la agenda por sede y volver a la de siempre (su sede y su subsede, con un solo horario).</span>
                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-arrow-counterclockwise"></i> Volver a la agenda de siempre</button>
                </form>
            @endif

            @if ($historial->isNotEmpty())
                <div class="tn-tarjeta mt-3">
                    <h5 class="mb-2" style="font-size: 15px; font-weight: 700; color: var(--tn-tinta);">Cambios de su agenda</h5>
                    @include('administracion.conciliadores._historial', ['historial' => $historial])
                </div>
            @endif
        </div>
    </section>

    <template id="cnPlantilla">
        @include('administracion.conciliadores._fila', ['i' => '__I__', 'f' => [
            'sede' => '', 'modalidad' => SC::VIRTUAL, 'dias' => [],
            'inicio' => PermisosConciliador::PREDETERMINADO['inicio'], 'fin' => PermisosConciliador::PREDETERMINADO['fin'], 'sala' => null,
            'desde' => null, 'hasta' => null, 'exclusiva' => false,
        ], 'conciliador' => $conciliador])
    </template>
@endsection

@push('scripts')
<script>
(function () {
    const COLORES = @json(SC::COLORES);
    const DIAS = @json(PermisosConciliador::DIAS);
    const ADSCRIPCION = @json($conciliador->delegacion);
    const SALA_FUERA = @json(SC::SALA_FUERA);
    const PRESENCIAL = @json(SC::PRESENCIAL);
    const caja = document.getElementById('cnFilas');
    const plantilla = document.getElementById('cnPlantilla');

    function filas() { return Array.from(caja.querySelectorAll('[data-fila]')); }

    // Los nombres van sedes[0], sedes[1]…: al quitar una se renumeran.
    function renumerar() {
        filas().forEach(function (fila, i) {
            fila.querySelectorAll('[name], [id], [for]').forEach(function (el) {
                ['name', 'id', 'for'].forEach(function (atr) {
                    const v = el.getAttribute(atr);
                    if (v) { el.setAttribute(atr, v.replace(/sedes\[[^\]]+\]/, 'sedes[' + i + ']').replace(/cn-\d+-|cn-__I__-/, 'cn-' + i + '-')); }
                });
            });
        });
        document.getElementById('cnContador').textContent = filas().length + (filas().length === 1 ? ' sede' : ' sedes');
    }

    function leer() {
        return filas().map(function (fila) {
            const modo = fila.querySelector('input[type=radio]:checked');
            return {
                fila: fila,
                sede: fila.querySelector('select').value,
                modalidad: modo ? modo.value : '',
                dias: Array.from(fila.querySelectorAll('input[type=checkbox]:checked')).map(function (c) { return c.value; }),
                inicio: fila.querySelector('[data-campo=inicio]').value,
                fin: fila.querySelector('[data-campo=fin]').value
            };
        });
    }

    function pintar() {
        const datos = leer();
        const avisos = {};

        datos.forEach(function (d) {
            const color = COLORES[d.sede] || '#496163';
            d.fila.style.setProperty('--c', color);
            d.fila.classList.toggle('cn-fila--virtual', d.modalidad !== PRESENCIAL);
            d.fila.querySelector('[data-titulo]').textContent = (d.sede || 'Nueva sede') + (d.modalidad ? ' · ' + (d.modalidad === PRESENCIAL ? 'Presencial' : 'Virtual') : '');
            const sala = d.fila.querySelector('[data-campo=sala]');
            sala.placeholder = d.sede && d.sede !== ADSCRIPCION ? SALA_FUERA : 'La de siempre';
        });

        // Vista previa de la semana.
        document.querySelectorAll('#cnPrevia td').forEach(function (td) {
            const dia = td.getAttribute('data-dia');
            const bloques = datos.filter(function (d) { return d.sede && d.dias.indexOf(dia) !== -1; })
                .sort(function (a, b) { return a.inicio.localeCompare(b.inicio); });
            td.innerHTML = '';
            if (!bloques.length) { td.innerHTML = '<span class="cn-vacio">—</span>'; }
            bloques.forEach(function (d) {
                const b = document.createElement('span');
                b.className = 'cn-bloque' + (d.modalidad === PRESENCIAL ? '' : ' cn-bloque--virtual');
                b.style.setProperty('--c', COLORES[d.sede] || '#496163');
                b.textContent = d.sede + ' ';
                const h = document.createElement('small');
                h.textContent = d.inicio + '–' + d.fin;
                b.appendChild(h);
                td.appendChild(b);
            });
            for (let i = 0; i < bloques.length; i++) {
                for (let j = i + 1; j < bloques.length; j++) {
                    if (bloques[i].inicio < bloques[j].fin && bloques[j].inicio < bloques[i].fin) {
                        const par = bloques[i].sede + ' y ' + bloques[j].sede;
                        (avisos[par] = avisos[par] || []).push(DIAS[dia]);
                    }
                }
            }
        });

        // Las mismas reglas que el servidor, para avisar antes de guardar.
        const errores = [];
        if (datos.filter(function (d) { return d.modalidad === PRESENCIAL; }).length > 1) { errores.push('Sólo una sede puede ser presencial.'); }
        const sedes = datos.map(function (d) { return d.sede; }).filter(Boolean);
        if (new Set(sedes).size !== sedes.length) { errores.push('Una sede aparece dos veces.'); }
        datos.forEach(function (d) {
            if (d.sede && !d.dias.length) { errores.push(d.sede + ': elige al menos un día.'); }
            if (d.inicio && d.fin && d.inicio >= d.fin) { errores.push((d.sede || 'Sede') + ': la salida debe ser después de la entrada.'); }
        });

        const caja = document.getElementById('cnAvisos');
        caja.innerHTML = '';
        errores.forEach(function (t) { const p = document.createElement('div'); p.className = 'text-danger small mt-1'; p.textContent = t; caja.appendChild(p); });
        Object.keys(avisos).forEach(function (par) {
            const p = document.createElement('div');
            p.className = 'cn-aviso-previa';
            p.textContent = avisos[par].join(', ') + ': ' + par + ' comparten horario. Se vale (como hoy la sede y su subsede): el sistema nunca le da dos audiencias a la misma hora.';
            caja.appendChild(p);
        });

        filas().forEach(function (f) { f.querySelector('[data-quitar]').disabled = filas().length === 1; });
    }

    document.getElementById('cnAgregar').addEventListener('click', function () {
        if (filas().length >= Object.keys(COLORES).length) { return; }
        caja.insertAdjacentHTML('beforeend', plantilla.innerHTML.replace(/__I__/g, String(filas().length)));
        renumerar();
        pintar();
    });

    caja.addEventListener('click', function (e) {
        const boton = e.target.closest('[data-quitar]');
        if (!boton || filas().length === 1) { return; }
        boton.closest('[data-fila]').remove();
        renumerar();
        pintar();
    });
    caja.addEventListener('change', pintar);
    caja.addEventListener('input', pintar);

    renumerar();
    pintar();
})();
</script>
@endpush
