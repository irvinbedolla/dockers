@extends('layouts.app')
@section('title', 'Audiencias de hoy')

{{--
    Audiencias de hoy (LlegadasAudienciaController / App\Support\LlegadasAudiencia).
    La recepción de audiencias marca qué parte llegó: el trabajador y cada
    citado. Al marcar, el conciliador recibe un aviso con sonido. Se actualiza
    sola cada 30 s para que dos recepcionistas vean lo mismo.

    No sustituye la comparecencia, que el conciliador registra con
    identificación dentro de la audiencia.
--}}

@section('page_css')
    @include('turnos._estilos')
    <style>
        .ah-cabeza { display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 12px; margin-bottom: 14px; }
        .ah-cabeza p { margin: 2px 0 0; font-size: 13px; color: var(--tn-tenue); }
        .ah-barra { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; margin-bottom: 14px; }
        .ah-barra .form-control { max-width: 380px; }
        .ah-filtros { display: flex; flex-wrap: wrap; gap: 6px; }
        .ah-filtro {
            display: inline-flex; align-items: center; gap: 7px; padding: 6px 13px; border-radius: 10px;
            border: 1px solid var(--tn-borde); background: #fff; color: var(--tn-tinta); font-size: 13px; font-weight: 600; cursor: pointer;
        }
        .ah-filtro b { min-width: 22px; padding: 0 7px; border-radius: 999px; background: var(--tn-fondo); font-size: 12px; text-align: center; }
        .ah-filtro.is-activo { border-color: var(--tn-verde); box-shadow: inset 0 0 0 1px var(--tn-verde); }
        .ah-filtro--faltan b { background: #FBF4E1; color: #7A5D10; }
        .ah-filtro--completas b { background: #E5F3EA; color: #1E7B3C; }

        .ah-lista { display: grid; gap: 10px; }
        .ah-aud {
            display: grid; grid-template-columns: 92px minmax(0, 1fr); gap: 16px;
            background: #fff; border: 1px solid var(--tn-borde); border-left: 5px solid #D5DEDE; border-radius: 12px; padding: 14px 16px;
        }
        .ah-aud.is-parcial { border-left-color: #CEA845; }
        .ah-aud.is-completa { border-left-color: #2F9E5B; }
        .ah-hora { font-size: 24px; font-weight: 800; color: var(--tn-tinta); line-height: 1; font-variant-numeric: tabular-nums; }
        .ah-sala { margin-top: 6px; display: inline-block; padding: 2px 9px; border-radius: 999px; background: #EEF2F2; color: var(--tn-verde); font-size: 12px; font-weight: 700; }
        .ah-cab { display: flex; flex-wrap: wrap; justify-content: space-between; gap: 6px 12px; align-items: baseline; margin-bottom: 8px; }
        .ah-nue { font-weight: 800; color: var(--tn-tinta); font-variant-numeric: tabular-nums; }
        .ah-conc { font-size: 12.5px; color: var(--tn-tenue); }
        .ah-estado { font-size: 12px; font-weight: 700; color: var(--tn-tenue); }
        .ah-aud.is-completa .ah-estado { color: #1E7B3C; }
        .ah-aud.is-parcial .ah-estado { color: #7A5D10; }

        .ah-parte { display: grid; grid-template-columns: 104px minmax(0, 1fr) auto; gap: 10px; align-items: center; padding: 7px 0; border-top: 1px dashed #EDF1F1; }
        .ah-parte:first-of-type { border-top: 0; }
        .ah-rol { font-size: 11.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: var(--tn-tenue); }
        .ah-nombre { font-size: 14px; font-weight: 600; color: var(--tn-tinta); overflow-wrap: anywhere; }
        .ah-accion { display: flex; align-items: center; gap: 8px; justify-content: flex-end; }
        .btn.ah-llego { border: 1px solid #2F9E5B; color: #1E7B3C; background: #fff; font-weight: 700; font-size: 13px; padding: 5px 14px; border-radius: 9px; white-space: nowrap; }
        .btn.ah-llego:not(.btn-social):not(.btn-social-icon):hover { background: #E5F3EA; color: #1E7B3C; border-color: #2F9E5B !important; }
        .ah-ok { display: inline-flex; align-items: center; gap: 6px; padding: 4px 11px; border-radius: 999px; background: #E5F3EA; color: #1E7B3C; font-weight: 700; font-size: 13px; white-space: nowrap; }
        .ah-deshacer { border: 0; background: none; color: var(--tn-tenue); font-size: 12px; text-decoration: underline; cursor: pointer; padding: 0; }
        .ah-vacio { text-align: center; color: var(--tn-tenue); padding: 40px 12px; background: #fff; border: 1px dashed var(--tn-borde); border-radius: 12px; }
        .ah-mensaje { font-size: 13px; font-weight: 600; padding: 8px 12px; border-radius: 10px; margin-bottom: 12px; background: #FBE9E7; color: #9A2B1C; }

        @media (max-width: 720px) {
            .ah-aud { grid-template-columns: 1fr; gap: 8px; }
            .ah-parte { grid-template-columns: 1fr auto; }
            .ah-rol { grid-column: 1 / -1; }
        }
    </style>
@endsection

@section('content')
    <section class="section tn">
        <div class="section-header ah-cabeza">
            <div>
                <h3 class="page__heading mb-0">Audiencias de hoy</h3>
                <p>{{ \Illuminate\Support\Str::ucfirst(now()->locale('es')->isoFormat('dddd D [de] MMMM')) }} · Marca a cada persona en cuanto llegue; su conciliador recibe el aviso.</p>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                @if (count($sedes) > 1)
                    <form method="GET" action="{{ route('recepcion.audiencias_hoy') }}">
                        <select name="sede" class="form-select form-select-sm" onchange="this.form.submit()" aria-label="Sede">
                            @foreach ($sedes as $s)
                                <option value="{{ $s }}" @selected($s === $sede)>{{ $s }}</option>
                            @endforeach
                        </select>
                    </form>
                @endif
                <span class="small" style="color: var(--tn-tenue);" id="ahActualizado"></span>
            </div>
        </div>

        <div class="section-body">
            <div class="ah-mensaje" id="ahMensaje" role="alert" hidden></div>

            <div class="ah-barra">
                <input type="search" class="form-control" id="ahBuscar" placeholder="Buscar por nombre, NUE, sala o conciliador" autocomplete="off">
                <div class="ah-filtros" id="ahFiltros"></div>
            </div>

            <div class="ah-lista" id="ahLista"></div>
        </div>
    </section>
@endsection

@section('scripts')
<script>
(function () {
    var URL_DATOS = @json($urlDatos);
    var URL_LLEGADA = @json(route('recepcion.audiencias_hoy.llegada', ['id' => '__ID__']));
    var TOKEN = @json(csrf_token());
    var datos = @json($datos);
    var filtro = 'todas';
    var texto = '';

    function esc(t) {
        return String(t == null ? '' : t).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }
    function sinAcentos(t) { return String(t || '').normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase(); }

    function pintarFiltros() {
        var k = datos.cifras;
        var faltan = k.total - k.completas;
        var op = [['todas', 'Todas', k.total, ''], ['faltan', 'Faltan por llegar', faltan, 'ah-filtro--faltan'], ['completas', 'Completas', k.completas, 'ah-filtro--completas']];
        document.getElementById('ahFiltros').innerHTML = op.map(function (o) {
            return '<button type="button" class="ah-filtro ' + o[3] + (filtro === o[0] ? ' is-activo' : '') + '" data-filtro="' + o[0] + '">' + o[1] + ' <b>' + o[2] + '</b></button>';
        }).join('');
    }

    function parteHtml(a, p) {
        var accion = p.llegada
            ? '<span class="ah-ok"><i class="bi bi-check-circle-fill"></i> Llegó ' + esc(p.llegada) + '</span>' +
              '<button type="button" class="ah-deshacer" data-aud="' + a.id + '" data-clave="' + esc(p.clave) + '" data-llego="0" title="Marcada por ' + esc(p.por) + '">Deshacer</button>'
            : '<button type="button" class="btn ah-llego" data-aud="' + a.id + '" data-clave="' + esc(p.clave) + '" data-llego="1"><i class="bi bi-person-check"></i> Llegó</button>';
        return '<div class="ah-parte">' +
            '<span class="ah-rol">' + (p.tipo === 'solicitante' ? 'Trabajador' : 'Citado') + '</span>' +
            '<span class="ah-nombre">' + esc(p.nombre) + '</span>' +
            '<span class="ah-accion">' + accion + '</span></div>';
    }

    function pintar() {
        pintarFiltros();
        var lista = (datos.audiencias || []).filter(function (a) {
            if (filtro === 'faltan' && a.completa) { return false; }
            if (filtro === 'completas' && !a.completa) { return false; }
            if (!texto) { return true; }
            var donde = sinAcentos(a.nue + ' ' + a.sala + ' ' + a.conciliador + ' ' + a.partes.map(function (p) { return p.nombre; }).join(' '));
            return donde.indexOf(texto) !== -1;
        });

        document.getElementById('ahLista').innerHTML = lista.length
            ? lista.map(function (a) {
                var estado = a.completa ? 'is-completa' : (a.llegaron > 0 ? 'is-parcial' : '');
                return '<article class="ah-aud ' + estado + '">' +
                    '<div><div class="ah-hora">' + esc(a.hora) + '</div><span class="ah-sala">' + esc(a.sala || 'Sin sala') + '</span></div>' +
                    '<div><div class="ah-cab"><div><span class="ah-nue">' + esc(a.nue) + '</span> <span class="ah-conc">· ' + esc(a.conciliador || 'Sin conciliador') + '</span></div>' +
                    '<span class="ah-estado">' + (a.completa ? 'Todos llegaron' : 'Llegaron ' + a.llegaron + ' de ' + a.partes.length) + '</span></div>' +
                    (a.partes.length ? a.partes.map(function (p) { return parteHtml(a, p); }).join('') : '<span class="ah-conc">Sin partes registradas.</span>') +
                    '</div></article>';
            }).join('')
            : '<div class="ah-vacio">' + (datos.audiencias.length ? 'Ninguna audiencia coincide.' : 'No hay audiencias pendientes hoy en ' + esc(datos.sede) + '.') + '</div>';

        var g = new Date(datos.generado);
        document.getElementById('ahActualizado').textContent = 'Actualizado ' + g.toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' });
    }

    function actualizar() {
        return fetch(URL_DATOS, { headers: { 'Accept': 'application/json' }, cache: 'no-store' })
            .then(function (r) { if (!r.ok) { throw new Error(r.status); } return r.json(); })
            .then(function (j) { datos = j; pintar(); })
            .catch(function () { document.getElementById('ahActualizado').textContent = 'Sin conexión, reintentando…'; });
    }

    function aviso(texto) {
        var m = document.getElementById('ahMensaje');
        m.textContent = texto; m.hidden = false;
        clearTimeout(aviso.t); aviso.t = setTimeout(function () { m.hidden = true; }, 8000);
    }

    document.getElementById('ahLista').addEventListener('click', function (e) {
        var b = e.target.closest('[data-clave]');
        if (!b || b.disabled) { return; }
        b.disabled = true;
        fetch(URL_LLEGADA.replace('__ID__', b.getAttribute('data-aud')), {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': TOKEN, 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ clave: b.getAttribute('data-clave'), llego: b.getAttribute('data-llego') === '1' })
        })
            .then(function (r) { return r.json().catch(function () { return {}; }).then(function (j) { return { ok: r.ok, j: j, status: r.status }; }); })
            .then(function (res) {
                if (!res.ok) { aviso(res.status === 419 ? 'La sesión expiró; recarga la página.' : (res.j.message || 'No se pudo guardar.')); }
                return actualizar();
            })
            .catch(function () { aviso('Sin conexión; intenta de nuevo.'); b.disabled = false; });
    });

    document.getElementById('ahFiltros').addEventListener('click', function (e) {
        var b = e.target.closest('[data-filtro]');
        if (b) { filtro = b.getAttribute('data-filtro'); pintar(); }
    });
    document.getElementById('ahBuscar').addEventListener('input', function () { texto = sinAcentos(this.value.trim()); pintar(); });

    pintar();
    setInterval(actualizar, 30000);
})();
</script>
@endsection
