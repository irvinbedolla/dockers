@extends('layouts.app')
@section('title', 'Reporte estadístico')
{{--
    /estadisticas/reporte (ReporteEstadisticoController, App\Support\ReporteEstadistico):
    la presentación "Estadística" calculada con el sistema para un mes o un
    rango de fechas. Se descarga en PDF, Excel o imagen. Lo que no sale del
    sistema se edita en Datos de referencia.
--}}
@php
    $f = $filtros;
    $hoy = \Illuminate\Support\Carbon::today();
    $mesPasado = $hoy->copy()->subMonthNoOverflow();
    $atajos = [
        'Mes pasado'   => ['mes' => $mesPasado->format('Y-m')],
        'Este mes'     => ['mes' => $hoy->format('Y-m')],
        'Lo que va del año' => ['desde' => $hoy->copy()->startOfYear()->toDateString(), 'hasta' => $mesPasado->copy()->endOfMonth()->toDateString()],
        'Año pasado'   => ['desde' => $hoy->copy()->subYear()->startOfYear()->toDateString(), 'hasta' => $hoy->copy()->subYear()->endOfYear()->toDateString()],
    ];
    $periodoQs = $f['modo'] === 'mes' ? ['mes' => $f['mes']] : ['desde' => $f['desde'], 'hasta' => $f['hasta']];
    // El diseño viaja en la URL sólo si no es el tradicional.
    $conDiseno = fn (array $q, ?string $d = null) => $q + (($d ?? $f['diseno']) !== 'tradicional' ? ['diseno' => $d ?? $f['diseno']] : []);
    $consulta = $conDiseno($periodoQs);
    $activo = fn (array $a) => $a == $periodoQs;
    $descargar = fn (string $formato) => route('estadisticas.reporte.descargar', $consulta + ['formato' => $formato]);
    $corteDia = \Illuminate\Support\Carbon::parse($corte);
    $modo = 'web';
@endphp

@section('page_css')
    @include('turnos._estilos')
    @include('estadisticas.reporte._estilos')
    <style>
        .re-migas { font-size: 12.5px; color: var(--tn-tenue); text-decoration: none; display: inline-flex; align-items: center; gap: 4px; }
        .re-migas:hover { color: var(--tn-verde); }
        .re-descargas { display: flex; flex-wrap: wrap; gap: 8px; }
        .re-filtros { display: flex; flex-wrap: wrap; align-items: end; gap: 12px 16px; }
        .re-filtros label, .re-etq { display: block; font-size: 11.5px; font-weight: 700; letter-spacing: .03em; color: var(--tn-suave); margin-bottom: 5px; }
        .re-atajos, .re-segmento { display: flex; flex-wrap: wrap; gap: 6px; }
        .re-atajo { padding: 5px 12px; border-radius: 999px; border: 1px solid var(--tn-borde); background: #fff; color: var(--tn-suave); font-size: 12.5px; font-weight: 600; text-decoration: none; white-space: nowrap; cursor: pointer; }
        .re-atajo:hover { border-color: var(--tn-verde-claro); color: var(--tn-verde); }
        .re-atajo.is-activo { background: var(--tn-verde); border-color: var(--tn-verde); color: #fff; }
        .re-campos-fecha { display: flex; gap: 8px; align-items: end; }
        .re-campos-fecha .form-control { min-width: 150px; }
        .re-oculto { display: none !important; }
        .re-nota { font-size: 12.5px; color: var(--tn-suave); margin: 12px 0 0; }
        .re-nota a { color: var(--tn-verde); font-weight: 600; }
        .re-btn-ver { background: var(--tn-verde); border-color: var(--tn-verde); color: #fff; font-weight: 600; }
        .re-btn-ver:hover { background: #3B5052; color: #fff; }

        .re-lista { margin-top: 18px; display: grid; gap: 18px; }
        .re-item { min-width: 0; background: #fff; border: 1px solid var(--tn-borde); border-radius: 12px; padding: 10px 10px 8px; }
        .re-item__pie { display: flex; justify-content: space-between; align-items: center; font-size: 12px; color: var(--tn-tenue); padding: 6px 4px 0; }
        /* La lámina mide 960 × 540; aquí se escala al ancho disponible. */
        .re-marco { position: relative; width: 100%; aspect-ratio: 16 / 9; overflow: hidden; border-radius: 6px; box-shadow: 0 0 0 1px #EEF1F1; }
        .re-marco > .re-lam { position: absolute; left: 0; top: 0; transform-origin: 0 0; }
        @media (min-width: 1400px) { .re-lista { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); } }
    </style>
@endsection

@section('content')
    <section class="section tn">
        <div class="section-header d-flex justify-content-between align-items-end flex-wrap gap-2 mb-3">
            <div>
                <a class="re-migas" href="{{ route('estadisticas.index') }}"><i class="bi bi-arrow-left"></i> Panel estadístico</a>
                <h3 class="page__heading mb-0">Reporte estadístico</h3>
                <span class="small" style="color: var(--tn-tenue);">{{ $periodo }} · por delegación, sede y oficina, histórico y comparativos</span>
            </div>
            <div class="re-descargas">
                <a class="btn btn-outline-secondary btn-sm" href="{{ $descargar('pdf') }}"><i class="bi bi-file-earmark-pdf"></i> PDF</a>
                <a class="btn btn-outline-secondary btn-sm" href="{{ $descargar('xlsx') }}"><i class="bi bi-file-earmark-excel"></i> Excel</a>
                <a class="btn btn-outline-secondary btn-sm" href="{{ $descargar('png') }}" target="_blank" rel="noopener"><i class="bi bi-image"></i> Imagen</a>
            </div>
        </div>

        <div class="section-body">
            <form class="tn-tarjeta" method="GET" action="{{ route('estadisticas.reporte') }}" id="reFiltros">
                <div class="re-filtros">
                    <div>
                        <span class="re-etq">Periodo</span>
                        <div class="re-atajos">
                            @foreach ($atajos as $etiqueta => $a)
                                <a class="re-atajo {{ $activo($a) ? 'is-activo' : '' }}" href="{{ route('estadisticas.reporte', $conDiseno($a)) }}">{{ $etiqueta }}</a>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <span class="re-etq">Elegir</span>
                        <div class="re-segmento" role="tablist">
                            <button type="button" class="re-atajo {{ $f['modo'] === 'mes' ? 'is-activo' : '' }}" data-modo="mes">Un mes</button>
                            <button type="button" class="re-atajo {{ $f['modo'] === 'rango' ? 'is-activo' : '' }}" data-modo="rango">Rango de fechas</button>
                        </div>
                    </div>
                    <div class="re-campos-fecha {{ $f['modo'] === 'mes' ? '' : 're-oculto' }}" data-campos="mes">
                        <div>
                            <label for="reMes">Mes</label>
                            <input type="month" id="reMes" name="mes" class="form-control" value="{{ $f['mes'] ?? $mesPasado->format('Y-m') }}" {{ $f['modo'] === 'mes' ? '' : 'disabled' }}>
                        </div>
                    </div>
                    <div class="re-campos-fecha {{ $f['modo'] === 'rango' ? '' : 're-oculto' }}" data-campos="rango">
                        <div><label for="reDesde">Desde</label><input type="date" id="reDesde" name="desde" class="form-control" value="{{ $f['desde'] }}" {{ $f['modo'] === 'rango' ? '' : 'disabled' }}></div>
                        <div><label for="reHasta">Hasta</label><input type="date" id="reHasta" name="hasta" class="form-control" value="{{ $f['hasta'] }}" {{ $f['modo'] === 'rango' ? '' : 'disabled' }}></div>
                    </div>
                    <div>
                        <span class="re-etq">Diseño</span>
                        <div class="re-segmento">
                            @foreach (\App\Support\ReporteEstadistico::DISENOS as $clave => $nombre)
                                <a class="re-atajo {{ $f['diseno'] === $clave ? 'is-activo' : '' }}" href="{{ route('estadisticas.reporte', $conDiseno($periodoQs, $clave)) }}" title="Cambia la vista, el PDF y las imágenes">{{ $nombre }}</a>
                            @endforeach
                        </div>
                        @if ($f['diseno'] !== 'tradicional')<input type="hidden" name="diseno" value="{{ $f['diseno'] }}">@endif
                    </div>
                    <div>
                        <button type="submit" class="btn re-btn-ver" id="reVer"><i class="bi bi-arrow-repeat"></i> Ver reporte</button>
                    </div>
                </div>
                @if ($errors->any())
                    <div class="alert alert-danger py-2 mt-3 mb-0 small">{{ $errors->first() }}</div>
                @endif
                <p class="re-nota mb-0">
                    <i class="bi bi-info-circle"></i>
                    Las tablas del periodo salen de lo que registra el sistema. Las láminas históricas suman lo capturado hasta el
                    {{ $corteDia->copy()->subDay()->isoFormat('D [de] MMMM [de] YYYY') }} más lo que el sistema registra desde el {{ $corteDia->isoFormat('D [de] MMMM [de] YYYY') }}.
                    Los comparativos, la PEA, la población y los textos se cambian en
                    <a href="{{ route('datos_referencia.index') }}">Datos de referencia</a>.
                </p>
            </form>

            {{-- Las láminas se acomodan en su marco escalable al cargar (ver abajo). --}}
            <div class="re-lista" id="reLista" style="visibility: hidden;">
                @include('estadisticas.reporte._laminas')
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    (function () {
        // Cada lámina (960 × 540) va en un marco que la escala al ancho disponible.
        var lista = document.getElementById('reLista');
        Array.prototype.slice.call(lista.querySelectorAll(':scope > .re-lam')).forEach(function (lam, i) {
            var item = document.createElement('div');
            item.className = 're-item';
            item.innerHTML = '<div class="re-marco"></div><div class="re-item__pie"><span>Lámina ' + (i + 1) + '</span></div>';
            lista.insertBefore(item, lam);
            item.firstChild.appendChild(lam);
        });
        lista.style.visibility = '';

        function escalar() {
            document.querySelectorAll('.re-marco').forEach(function (m) {
                var lam = m.querySelector('.re-lam');
                if (lam) { lam.style.transform = 'scale(' + (m.clientWidth / 960) + ')'; }
            });
        }
        escalar();
        window.addEventListener('resize', escalar);
        if (window.ResizeObserver) {
            var obs = new ResizeObserver(escalar);
            document.querySelectorAll('.re-marco').forEach(function (m) { obs.observe(m); });
        }

        // Un mes o rango de fechas: sólo se envían los campos visibles.
        document.querySelectorAll('[data-modo]').forEach(function (b) {
            b.addEventListener('click', function () {
                var modo = b.dataset.modo;
                document.querySelectorAll('[data-modo]').forEach(function (o) { o.classList.toggle('is-activo', o === b); });
                document.querySelectorAll('[data-campos]').forEach(function (c) {
                    var ver = c.dataset.campos === modo;
                    c.classList.toggle('re-oculto', !ver);
                    c.querySelectorAll('input').forEach(function (i) { i.disabled = !ver; });
                });
            });
        });

        // Calcular puede tardar: el botón se bloquea para no mandar dos veces.
        var form = document.getElementById('reFiltros');
        form.addEventListener('submit', function (e) {
            var desde = document.getElementById('reDesde'), hasta = document.getElementById('reHasta');
            if (!desde.disabled && desde.value && hasta.value && desde.value > hasta.value) {
                e.preventDefault();
                Swal.fire({ icon: 'warning', title: 'Revisa las fechas', text: 'La fecha final no puede ser antes de la inicial.', confirmButtonColor: '#496163', confirmButtonText: 'Entendido' });
                return;
            }
            var btn = document.getElementById('reVer');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Calculando…';
        });
    })();
    </script>
@endsection
