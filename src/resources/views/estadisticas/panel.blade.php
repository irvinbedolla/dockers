@extends('layouts.app')
@section('title', 'Panel estadístico')
{{--
    /estadisticas (EstadisticasController). Arriba, la descarga de reportes:
    es el mismo formulario de /seer/estadistica, con el mismo destino
    (seer.mostar) y los mismos nombres de campo, sólo reacomodado. Luego las
    cifras del mes (convenios pagados, montos y tasa de conciliación) que
    antes iban en el Inicio, y abajo el mapa de sedes y las gráficas del
    Inicio del Directivo, incluidos desde sus parciales para no mantener dos
    copias. Aquí se irán sumando gráficas.

    Cada reporte se ofrece sólo con los formatos que de verdad genera
    SeerController::mostrar_reporte (p. ej. INEGI siempre sale en Excel) y
    sólo si el usuario tiene su permiso reportes_*.
--}}
@php
    // [valor que espera mostrar_reporte, etiqueta, permiso, formatos, filtro extra]
    // Formatos: pdf → tipo=1, excel → tipo=2, grafica → tipo=3.
    $catalogo = [
        'Trámites' => [
            ['Solicitudes',              'Solicitudes',               'reportes_solicitudes',               ['excel'],        null],
            ['Audiencias',               'Audiencias',                'reportes_audiencias',                ['excel'],        'conciliador'],
            ['Ratificaciones',           'Ratificaciones',            'reportes_ratificaciones',            ['excel'],        null],
            ['Cumplimientos',            'Cumplimientos',             'reportes_cumplimientos',             ['pdf', 'excel'], null],
            ['CumplimientosProgramados', 'Cumplimientos programados', 'reportes_cumplimientos_programados', ['excel'],        null],
            ['Convenios',                'Convenios',                 'reportes_convenios',                 ['excel'],        null],
            ['Notificaciones',           'Notificaciones',            'reportes_notificaciones',            ['excel'],        'notificador'],
        ],
        'Productividad' => [
            ['Productividad',            'Productividad',             'reportes_productividad',             ['pdf'],          null],
            ['AudienciaConciliador',     'Audiencias por conciliador','reportes_conciliador',               ['excel'],        null],
            ['ReporteActividad',         'Por actividad económica',   'reportes_actividad',                 ['pdf'],          null],
        ],
        'Generales' => [
            ['GeneralSede',              'General por sede',          'reportes_general_sede',              ['pdf'],          null],
            ['EstadisticaMexico',        'INEGI',                     'reportes_inegi',                     ['excel'],        null],
            ['Motivos',                  'Motivos',                   'reportes_motivos',                   ['excel'],        null],
            ['ReporteMunicipio',         'Por municipio',             'reportes_municipios',                ['pdf'],          null],
            ['SeguroSocial',             'Seguro Social',             'reportes_seguro_social',             ['excel'],        null],
            ['CumplimientosGrafica',     'Gráfica de cumplimientos',  'reportes_graficas',                  ['grafica'],      null],
        ],
    ];

    $usuario = auth()->user();
    $catalogo = array_filter(array_map(
        fn ($grupo) => array_values(array_filter($grupo, fn ($r) => $usuario->can($r[2]))),
        $catalogo
    ));

    $hoy = now();
@endphp

@section('page_css')
    @include('turnos._estilos')
    <style>
        .es-reportes { margin-bottom: 18px; }
        .es-reportes__cabeza { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; }
        .es-reportes__icono {
            width: 40px; height: 40px; border-radius: 10px; flex: 0 0 auto;
            display: grid; place-items: center; font-size: 19px;
            background: #FBF4E1; color: #8A6A12;
        }
        .es-reportes__titulo { font-size: 15px; font-weight: 700; color: var(--tn-tinta); margin: 0; line-height: 1.2; }
        .es-reportes__sub { font-size: 12.5px; color: var(--tn-tenue); }

        .es-campos {
            display: grid;
            grid-template-columns: minmax(200px, 1.4fr) minmax(300px, 2fr) minmax(160px, 1fr);
            gap: 14px 16px;
            align-items: start;
        }
        .es-campos label, .es-etiqueta {
            display: block; font-size: 12px; font-weight: 700; letter-spacing: .03em;
            color: var(--tn-suave); margin-bottom: 5px;
        }
        .es-periodo__fechas { display: grid; grid-template-columns: 1fr auto 1fr; gap: 8px; align-items: center; }
        .es-periodo__a { font-size: 12.5px; color: var(--tn-tenue); }
        .es-atajos { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 7px; }
        .es-atajo {
            border: 1px solid var(--tn-borde); background: #fff; color: var(--tn-suave);
            font-size: 12px; font-weight: 600; padding: 3px 10px; border-radius: 999px;
        }
        .es-atajo:hover { border-color: var(--tn-verde-claro); color: var(--tn-verde); }
        .es-atajo.is-activo { background: var(--tn-verde); border-color: var(--tn-verde); color: #fff; }

        .es-filtro[hidden] { display: none; }

        .es-pie {
            display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px;
            margin-top: 16px; padding-top: 14px; border-top: 1px solid var(--tn-borde);
        }
        .es-pie__ayuda { font-size: 12.5px; color: var(--tn-tenue); margin: 0; }
        .es-pie__ayuda.is-error { color: #8C1D40; font-weight: 600; }
        .es-formatos { display: flex; flex-wrap: wrap; gap: 8px; }
        .es-formato { min-width: 112px; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; gap: 6px; }
        .es-formato[hidden] { display: none; }
        .btn.es-formato--pdf { --bs-btn-color: #8C1D40; --bs-btn-border-color: #E8C6D2; --bs-btn-hover-bg: #F6E4EA; --bs-btn-hover-color: #8C1D40; --bs-btn-hover-border-color: #D9A3B6; background: #fff; }
        .btn.es-formato--excel { --bs-btn-color: #1E7B3C; --bs-btn-border-color: #BFE0CA; --bs-btn-hover-bg: #E5F3EA; --bs-btn-hover-color: #1E7B3C; --bs-btn-hover-border-color: #95CBA7; background: #fff; }
        .btn.es-formato--grafica { --bs-btn-color: #496163; --bs-btn-border-color: #C9D5D5; --bs-btn-hover-bg: #EEF2F2; --bs-btn-hover-color: #354647; --bs-btn-hover-border-color: #A9BBBC; background: #fff; }
        /* style.css pinta de blanco cualquier .btn:hover; se le gana igual que en el resto. */
        .btn.es-formato--pdf:not(.btn-social):not(.btn-social-icon):hover { background: #F6E4EA; color: #8C1D40; border-color: #D9A3B6 !important; }
        .btn.es-formato--excel:not(.btn-social):not(.btn-social-icon):hover { background: #E5F3EA; color: #1E7B3C; border-color: #95CBA7 !important; }
        .btn.es-formato--grafica:not(.btn-social):not(.btn-social-icon):hover { background: #EEF2F2; color: #354647; border-color: #A9BBBC !important; }
        .es-formato:disabled { opacity: .5; }

        .es-seccion { margin: 6px 2px 10px; }

        @media (max-width: 1100px) {
            .es-campos { grid-template-columns: 1fr 1fr; }
            .es-campo--periodo { grid-column: 1 / -1; order: 3; }
        }
        @media (max-width: 640px) {
            .es-campos { grid-template-columns: 1fr; }
            .es-periodo__fechas { grid-template-columns: 1fr; }
            .es-periodo__a { display: none; }
            .es-formatos, .es-formato { width: 100%; }
        }
    </style>
@endsection

@section('content')
    <section class="section tn">
        <div class="section-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <h3 class="page__heading mb-0">Panel estadístico</h3>
                <span class="small" style="color: var(--tn-tenue);">Reportes y cifras del Centro por sede</span>
            </div>
        </div>

        <div class="section-body">
            {{-- ===================== Descarga de reportes ===================== --}}
            <div class="tn-tarjeta es-reportes">
                <div class="es-reportes__cabeza">
                    <span class="es-reportes__icono"><i class="bi bi-file-earmark-arrow-down"></i></span>
                    <div>
                        <h2 class="es-reportes__titulo">Descargar reportes</h2>
                        <span class="es-reportes__sub">El reporte se genera en una pestaña nueva; los grandes pueden tardar un poco.</span>
                    </div>
                </div>

                @if (empty($catalogo))
                    <p class="es-pie__ayuda mb-0">Tu cuenta no tiene permiso para generar reportes.</p>
                @else
                <form id="es-form" method="POST" action="{{ route('seer.mostar') }}" target="_blank" novalidate>
                    @csrf
                    {{-- mostrar_reporte siempre lee auxiliar; el filtro ya no se ofrece. --}}
                    <input type="hidden" name="auxiliar" value="Todos">

                    <div class="es-campos">
                        <div class="es-campo">
                            <label for="es-tipo">Reporte</label>
                            <select id="es-tipo" name="tipo_reporte" class="form-select" required>
                                <option value="">Elige un reporte…</option>
                                @foreach ($catalogo as $grupo => $reportes)
                                    <optgroup label="{{ $grupo }}">
                                        @foreach ($reportes as [$valor, $etiqueta, , $formatos, $filtro])
                                            <option value="{{ $valor }}" data-formatos="{{ implode(',', $formatos) }}" data-filtro="{{ $filtro }}">{{ $etiqueta }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>

                        <div class="es-campo es-campo--periodo">
                            <span class="es-etiqueta" id="es-periodo-etq">Periodo</span>
                            <div class="es-periodo__fechas" role="group" aria-labelledby="es-periodo-etq">
                                <input type="date" id="es-desde" name="fecha_inicial" class="form-control" required
                                       aria-label="Fecha inicial" value="{{ $hoy->copy()->startOfMonth()->toDateString() }}">
                                <span class="es-periodo__a">a</span>
                                <input type="date" id="es-hasta" name="fecha_final" class="form-control" required
                                       aria-label="Fecha final" value="{{ $hoy->toDateString() }}">
                            </div>
                            <div class="es-atajos">
                                <button type="button" class="es-atajo is-activo" data-periodo="mes">Este mes</button>
                                <button type="button" class="es-atajo" data-periodo="mes-pasado">Mes pasado</button>
                                <button type="button" class="es-atajo" data-periodo="trimestre">Últimos 3 meses</button>
                                <button type="button" class="es-atajo" data-periodo="anio">Este año</button>
                            </div>
                        </div>

                        <div class="es-campo">
                            <label for="es-sede">Sede</label>
                            <select id="es-sede" name="sede" class="form-select" required>
                                <option value="Todos">Todas las sedes</option>
                                @foreach ($sedes as $sede)
                                    <option value="{{ $sede }}">{{ $sede }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Siempre se envían (con "Todos" por omisión), como en el formulario original. --}}
                        <div class="es-campo es-filtro" data-filtro="conciliador" hidden>
                            <label for="es-conciliador">Conciliador</label>
                            <select id="es-conciliador" name="conciliador" class="form-select">
                                <option value="Todos">Todos</option>
                                @foreach ($conciliadores as $c)
                                    <option value="{{ $c->id }}">{{ \Illuminate\Support\Str::title(mb_strtolower($c->name)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="es-campo es-filtro" data-filtro="notificador" hidden>
                            <label for="es-notificador">Notificador</label>
                            <select id="es-notificador" name="notificador" class="form-select">
                                <option value="Todos">Todos</option>
                                @foreach ($notificadores as $n)
                                    <option value="{{ $n->id }}">{{ \Illuminate\Support\Str::title(mb_strtolower($n->name)) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="es-pie">
                        <p class="es-pie__ayuda" id="es-ayuda" aria-live="polite">Elige un reporte para ver en qué formatos se puede descargar.</p>
                        <div class="es-formatos">
                            <button type="submit" name="tipo" value="1" class="btn es-formato es-formato--pdf" data-formato="pdf" disabled>
                                <i class="bi bi-file-earmark-pdf"></i> PDF
                            </button>
                            <button type="submit" name="tipo" value="2" class="btn es-formato es-formato--excel" data-formato="excel" disabled>
                                <i class="bi bi-file-earmark-excel"></i> Excel
                            </button>
                            <button type="submit" name="tipo" value="3" class="btn es-formato es-formato--grafica" data-formato="grafica" hidden disabled>
                                <i class="bi bi-bar-chart"></i> Gráfica
                            </button>
                        </div>
                    </div>
                </form>
                @endif
            </div>

            {{-- ===================== Cifras del mes ===================== --}}
            {{-- Antes iban en el Inicio de todos los roles. --}}
            <h3 class="tn-titulo-seccion es-seccion">Cifras del mes</h3>
            @include('inicio._tarjetas')

            {{-- ===================== Mapa y gráficas ===================== --}}
            <h3 class="tn-titulo-seccion es-seccion">Sedes · totales acumulados</h3>
        </div>

        @include('inicio._mapa')
        @include('inicio._panel_directivo')
    </section>
@endsection

@section('scripts')
    @include('inicio._mapa_scripts')
    @include('inicio._panel_directivo_scripts')

    <script>
    (function () {
        var form = document.getElementById('es-form');
        if (!form) { return; }

        var tipo    = document.getElementById('es-tipo');
        var desde   = document.getElementById('es-desde');
        var hasta   = document.getElementById('es-hasta');
        var ayuda   = document.getElementById('es-ayuda');
        var botones = form.querySelectorAll('.es-formato');
        var filtros = form.querySelectorAll('.es-filtro');
        var textoAyuda = ayuda.textContent;

        function avisar(texto, esError) {
            ayuda.textContent = texto;
            ayuda.classList.toggle('is-error', !!esError);
        }

        // Muestra sólo los formatos y el filtro que tiene el reporte elegido.
        function alCambiarTipo() {
            var opcion   = tipo.options[tipo.selectedIndex];
            var formatos = opcion && opcion.value ? opcion.dataset.formatos.split(',') : [];
            var filtro   = opcion ? opcion.dataset.filtro : '';

            botones.forEach(function (b) {
                var lo = formatos.indexOf(b.dataset.formato) !== -1;
                // Sin reporte elegido se ven PDF y Excel apagados, como pista.
                b.hidden   = formatos.length ? !lo : b.dataset.formato === 'grafica';
                b.disabled = !lo;
            });
            filtros.forEach(function (f) {
                f.hidden = f.dataset.filtro !== filtro;
                // Oculto vuelve a "Todos" para que no se cuele un filtro viejo.
                if (f.hidden) { f.querySelector('select').value = 'Todos'; }
            });
            avisar(formatos.length ? 'Listo: elige el formato para descargarlo.' : textoAyuda, false);
        }

        // --- atajos de periodo ---
        function iso(d) {
            var m = d.getMonth() + 1, dia = d.getDate();
            return d.getFullYear() + '-' + (m < 10 ? '0' : '') + m + '-' + (dia < 10 ? '0' : '') + dia;
        }
        function rango(cual) {
            var hoy = new Date();
            var a = hoy.getFullYear(), m = hoy.getMonth();
            switch (cual) {
                case 'mes':        return [new Date(a, m, 1), hoy];
                case 'mes-pasado': return [new Date(a, m - 1, 1), new Date(a, m, 0)];
                case 'trimestre':  return [new Date(a, m - 2, 1), hoy];
                case 'anio':       return [new Date(a, 0, 1), hoy];
            }
        }
        var atajos = form.querySelectorAll('.es-atajo');
        atajos.forEach(function (b) {
            b.addEventListener('click', function () {
                var r = rango(b.dataset.periodo);
                desde.value = iso(r[0]);
                hasta.value = iso(r[1]);
                atajos.forEach(function (x) { x.classList.toggle('is-activo', x === b); });
            });
        });
        [desde, hasta].forEach(function (i) {
            i.addEventListener('input', function () {
                atajos.forEach(function (x) { x.classList.remove('is-activo'); });
            });
        });

        tipo.addEventListener('change', alCambiarTipo);

        form.addEventListener('submit', function (e) {
            var error = !tipo.value ? 'Elige un reporte.'
                : !desde.value || !hasta.value ? 'Indica la fecha inicial y la final.'
                : desde.value > hasta.value ? 'La fecha inicial no puede ser posterior a la final.'
                : '';
            if (error) {
                e.preventDefault();
                avisar(error, true);
                return;
            }
            // El reporte abre en otra pestaña: aquí sólo se avisa un momento.
            var boton = e.submitter;
            if (boton) {
                var original = boton.innerHTML;
                setTimeout(function () { boton.disabled = true; boton.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Generando…'; }, 0);
                setTimeout(function () { boton.disabled = false; boton.innerHTML = original; }, 4000);
            }
            avisar('Generando el reporte en una pestaña nueva…', false);
        });

        alCambiarTipo();
    })();
    </script>
@endsection
