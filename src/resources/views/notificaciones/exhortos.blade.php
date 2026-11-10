@extends('layouts.app')
@section('title', 'Exhortos')

{{--
    Notificaciones → Exhortos (ExhortosController). Registro de exhortos con
    expediente, solicitante, citado, estado y municipio, y si ya se
    contestaron. "Por registrar": citados que el notificador marcó como
    Exhorto y aún no tienen registro.
--}}

@php
    $url = fn (array $cambios) => route('exhortos.index', array_filter(
        array_merge(request()->except('page'), $cambios),
        fn ($v) => $v !== null && $v !== ''
    ));
    $lugar = fn ($municipio, $estado) => trim(($municipio ? $municipio.', ' : '').($estado ?? ''), ', ') ?: '—';
    $nombre = fn (...$p) => \App\Http\Controllers\ExhortosController::nombre(...$p);
    $pestanas = [
        'pendientes'    => 'Sin contestar',
        'contestados'   => 'Contestados',
        'todos'         => 'Todos',
        'por_registrar' => 'Por registrar',
    ];
    $hayFiltros = ! empty($filtros['q']) || ! empty($filtros['estado']) || ! empty($filtros['sede']);
@endphp

@section('page_css')
    @include('notificaciones._estilos')
    <style>
        .ex-contestado { display: flex; align-items: center; gap: 8px; margin: 0; }
        .ex-estado { width: auto; min-width: 150px; font-weight: 600; font-size: 12.5px; border-radius: 999px; padding-left: 12px; cursor: pointer; }
        .ex-estado--no { background-color: #FBF4E1; color: #8A6A12; border-color: #F0DFAE; }
        .ex-estado--si { background-color: #E3F4E8; color: #1E7B3C; border-color: #BFE3CA; }
        .ex-estado option { background: #fff; color: #2E3C3D; }
        .ex-pestana--aviso .nt-pestana__num { background: #FBF4E1; color: #8A6A12; }

        .ex-citados { display: flex; flex-direction: column; gap: 6px; margin-top: 8px; }
        .ex-citado {
            display: flex; justify-content: space-between; align-items: center; gap: 10px; width: 100%;
            padding: 8px 10px; border: 1px solid var(--tn-borde); border-radius: 10px; background: #fff; text-align: left;
        }
        .ex-citado:hover:not(:disabled), .ex-citado.is-elegido { border-color: var(--tn-verde); background: #F4F8F6; }
        .ex-citado:disabled { opacity: .6; cursor: not-allowed; }
        .ex-citado b { display: block; font-size: 13.5px; color: var(--tn-tinta); }
        .ex-citado small { display: block; font-size: 12px; color: var(--tn-tenue); }
        .ex-citado__marca { font-size: 11.5px; font-weight: 700; white-space: nowrap; color: var(--tn-verde); }
        .ex-ayuda { font-size: 12.5px; color: var(--tn-tenue); }
    </style>
@endsection

@section('content')
    <section class="section tn">
        <div class="section-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <a href="{{ route('index_notificaciones') }}" class="text-decoration-none small" style="color: var(--tn-verde-claro);">
                    <i class="bi bi-arrow-left"></i> Notificaciones
                </a>
                <h3 class="page__heading mb-0">Exhortos</h3>
            </div>
            <button type="button" class="btn tn-boton-dorado" data-bs-toggle="modal" data-bs-target="#exModal" data-exhorto="">
                <i class="bi bi-plus-lg me-1"></i> Registrar exhorto
            </button>
        </div>

        <div class="section-body">
            @foreach (['success' => 'success', 'error' => 'danger'] as $clave => $tipo)
                @if (session($clave))
                    <div class="alert alert-{{ $tipo }} alert-dismissible fade show" role="alert">
                        {{ session($clave) }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                @endif
            @endforeach

            <form method="GET" action="{{ route('exhortos.index') }}" class="tn-tarjeta">
                <input type="hidden" name="vista" value="{{ $vista }}">
                <div class="nt-filtros">
                    <div>
                        <label for="exBuscar">Buscar</label>
                        <input type="search" id="exBuscar" name="q" class="form-control" maxlength="100"
                               placeholder="Expediente, solicitante o citado" value="{{ $filtros['q'] ?? '' }}">
                    </div>
                    <div>
                        <label for="exEstadoFiltro">Estado</label>
                        <select id="exEstadoFiltro" name="estado" class="form-select">
                            <option value="">Todos</option>
                            @foreach ($estados as $e)
                                <option value="{{ $e->id }}" @selected((string) ($filtros['estado'] ?? '') === (string) $e->id)>{{ $e->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if (count($sedes) > 1)
                        <div>
                            <label for="exSede">Sede</label>
                            <select id="exSede" name="sede" class="form-select">
                                <option value="">Todas</option>
                                @foreach ($sedes as $s)
                                    <option value="{{ $s }}" @selected(($filtros['sede'] ?? '') === $s)>{{ $s }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="nt-filtros__acciones">
                        <button type="submit" class="btn tn-boton-dorado"><i class="bi bi-funnel me-1"></i> Filtrar</button>
                        @if ($hayFiltros)
                            <a href="{{ route('exhortos.index', ['vista' => $vista]) }}" class="btn btn-outline-secondary">Limpiar</a>
                        @endif
                    </div>
                </div>
            </form>

            <div class="nt-pestanas">
                @foreach ($pestanas as $clave => $texto)
                    <a class="nt-pestana {{ $vista === $clave ? 'is-activa' : '' }} {{ $clave === 'por_registrar' && $conteos[$clave] ? 'ex-pestana--aviso' : '' }}"
                       href="{{ $url(['vista' => $clave]) }}"
                       @if ($clave === 'por_registrar') title="Citados que el notificador marcó como Exhorto y aún no tienen registro" @endif>
                        {{ $texto }} <span class="nt-pestana__num">{{ number_format($conteos[$clave]) }}</span>
                    </a>
                @endforeach
            </div>

            <div class="tn-tarjeta">
                @if ($filas->isEmpty())
                    <div class="nt-vacio">
                        <i class="bi bi-envelope-paper" aria-hidden="true"></i>
                        {{ $vista === 'por_registrar' ? 'No hay citados marcados como Exhorto pendientes de registrar.' : 'No hay exhortos con estos filtros.' }}
                    </div>
                @elseif ($vista === 'por_registrar')
                    <div class="table-responsive">
                        <table class="table table-hover nt-tabla">
                            <thead>
                                <tr>
                                    <th>Expediente</th>
                                    <th>Solicitante</th>
                                    <th>Citado</th>
                                    <th>Estado / municipio</th>
                                    <th class="text-end">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($filas as $f)
                                    @php
                                        $citado = $nombre($f->nombre, $f->primer_apellido, $f->segundo_apellido);
                                        $prellenado = [
                                            'NUE' => $f->NUE, 'solicitante' => $f->solicitante, 'citado' => $citado,
                                            'estado_id' => $f->estado_citado, 'municipio_id' => $f->municipio_citado,
                                            'id_citado' => $f->id_citado, 'contestado' => false,
                                        ];
                                    @endphp
                                    <tr>
                                        <td>
                                            <span class="nt-nue">{{ $f->NUE }}</span>
                                            <span class="nt-sub">{{ $f->delegacion }}</span>
                                        </td>
                                        <td>{{ $f->solicitante ?: '—' }}</td>
                                        <td><span class="nt-citado" title="{{ $citado }}">{{ $citado ?: 'Sin nombre' }}</span></td>
                                        <td>{{ $lugar($f->municipio, $f->estado) }}</td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#exModal"
                                                    data-exhorto="{{ json_encode($prellenado, JSON_UNESCAPED_UNICODE) }}">
                                                <i class="bi bi-plus-lg"></i> Registrar
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover nt-tabla">
                            <thead>
                                <tr>
                                    <th>Expediente</th>
                                    <th>Solicitante</th>
                                    <th>Citado</th>
                                    <th>Estado / municipio</th>
                                    <th>¿Contestado?</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($filas as $f)
                                    @php
                                        $datos = [
                                            'id' => $f->id, 'NUE' => $f->NUE, 'solicitante' => $f->solicitante, 'citado' => $f->citado,
                                            'estado_id' => $f->estado_id, 'municipio_id' => $f->municipio_id, 'id_citado' => $f->id_citado,
                                            'contestado' => (bool) $f->contestado, 'fecha_contestacion' => $f->fecha_contestacion ? substr($f->fecha_contestacion, 0, 10) : '',
                                            'observaciones' => $f->observaciones,
                                        ];
                                    @endphp
                                    <tr>
                                        <td>
                                            <span class="nt-nue">{{ $f->NUE }}</span>
                                            <span class="nt-sub">{{ $f->delegacion }} · registrado {{ \Illuminate\Support\Carbon::parse($f->created_at)->format('d/m/Y') }}</span>
                                        </td>
                                        <td>{{ $f->solicitante }}</td>
                                        <td>
                                            <span class="nt-citado" title="{{ $f->citado }}">{{ $f->citado }}</span>
                                            @if ($f->observaciones)<span class="nt-sub" title="{{ $f->observaciones }}"><i class="bi bi-chat-left-text"></i> {{ \Illuminate\Support\Str::limit($f->observaciones, 60) }}</span>@endif
                                        </td>
                                        <td>{{ $lugar($f->municipio, $f->estado) }}</td>
                                        <td>
                                            {{-- Select y no interruptor: cambiar el estado es un solo toque, y se guarda al elegir. --}}
                                            <form method="POST" action="{{ route('exhortos.contestado', $f->id) }}" class="ex-contestado">
                                                @csrf
                                                @method('PUT')
                                                <div>
                                                    <select name="contestado" id="exC{{ $f->id }}" onchange="this.form.submit()"
                                                            class="form-select form-select-sm ex-estado ex-estado--{{ $f->contestado ? 'si' : 'no' }}"
                                                            aria-label="¿Contestado? Exhorto de {{ $f->NUE }}">
                                                        <option value="0" @selected(! $f->contestado)>Sin contestar</option>
                                                        <option value="1" @selected($f->contestado)>Contestado</option>
                                                    </select>
                                                    @if ($f->contestado && $f->fecha_contestacion)
                                                        <span class="nt-sub mt-1">{{ \Illuminate\Support\Carbon::parse($f->fecha_contestacion)->format('d/m/Y') }}</span>
                                                    @endif
                                                </div>
                                            </form>
                                        </td>
                                        <td>
                                            <div class="nt-acciones">
                                                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#exModal"
                                                        data-exhorto="{{ json_encode($datos, JSON_UNESCAPED_UNICODE) }}">
                                                    <i class="bi bi-pencil-square"></i> Editar
                                                </button>
                                                <form method="POST" action="{{ route('exhortos.borrar', $f->id) }}" class="m-0"
                                                      onsubmit="return confirm('¿Borrar el exhorto de {{ $f->NUE }}? No se puede deshacer.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Borrar"><i class="bi bi-trash"></i></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                @if ($filas->total() > 0)
                    <div class="nt-pie">
                        <span>Mostrando {{ number_format($filas->firstItem()) }}–{{ number_format($filas->lastItem()) }} de {{ number_format($filas->total()) }}</span>
                        @include('turnos._paginacion', ['pagina' => $filas])
                    </div>
                @endif
            </div>
        </div>
    </section>

    {{-- Registrar / editar --}}
    <div class="modal fade" id="exModal" tabindex="-1" aria-labelledby="exModalTitulo" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <form class="modal-content" method="POST" action="{{ route('exhortos.crear') }}" id="exForm"
                  data-crear="{{ route('exhortos.crear') }}" data-actualizar="{{ route('exhortos.actualizar', '__ID__') }}">
                @csrf
                <input type="hidden" name="_method" value="POST" id="exMetodo">
                <input type="hidden" name="_exhorto" value="" id="exId">
                <input type="hidden" name="id_citado" value="" id="exIdCitado">
                <div class="modal-header">
                    <h5 class="modal-title" id="exModalTitulo">Registrar exhorto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    @if ($errors->any())
                        <div class="alert alert-danger py-2">
                            @foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach
                        </div>
                    @endif

                    <label class="form-label fw-bold small" for="exNue">Número de expediente <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="exNue" name="NUE" maxlength="60" required placeholder="MOR/SOL/2026/00123" autocomplete="off">
                        <button type="button" class="btn btn-outline-secondary" id="exBuscarNue"><i class="bi bi-search me-1"></i> Buscar</button>
                    </div>
                    <div class="ex-ayuda mt-1" id="exAyudaNue">Si el expediente está en el sistema, al buscarlo se llenan el solicitante y sus citados.</div>
                    <div class="ex-citados" id="exCitados" hidden></div>

                    <div class="row g-3 mt-1">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small" for="exSolicitante">Solicitante <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="exSolicitante" name="solicitante" maxlength="255" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small" for="exCitado">Citado <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="exCitado" name="citado" rows="2" maxlength="2000" required></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small" for="exEstado">Estado <span class="text-danger">*</span></label>
                            <select class="form-select" id="exEstado" name="estado_id" required>
                                <option value="">Elige el estado…</option>
                                @foreach ($estados as $e)<option value="{{ $e->id }}">{{ $e->nombre }}</option>@endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small" for="exMunicipio">Municipio</label>
                            <select class="form-select" id="exMunicipio" name="municipio_id" disabled>
                                <option value="">Primero elige el estado</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <span class="form-label fw-bold small d-block">¿Fue contestado?</span>
                            <div class="form-check form-switch mt-1">
                                <input type="hidden" name="contestado" value="0">
                                <input class="form-check-input" type="checkbox" role="switch" id="exContestado" name="contestado" value="1">
                                <label class="form-check-label" for="exContestado" id="exContestadoTexto">No, sin contestar</label>
                            </div>
                        </div>
                        <div class="col-md-6" id="exFechaCaja" hidden>
                            <label class="form-label fw-bold small" for="exFecha">Fecha de contestación</label>
                            <input type="date" class="form-control" id="exFecha" name="fecha_contestacion" max="{{ now()->toDateString() }}">
                            <div class="ex-ayuda mt-1">Si la dejas vacía, se pone la de hoy.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold small" for="exObs">Observaciones</label>
                            <textarea class="form-control" id="exObs" name="observaciones" rows="2" maxlength="2000" placeholder="Opcional: a quién se dirigió, número de oficio…"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn tn-boton-dorado" id="exGuardar">Registrar</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        (function () {
            var URL_EXPEDIENTE = @json(route('exhortos.expediente'));
            var URL_MUNICIPIOS = @json(url('/munCitado'));
            // Si la validación falló, se vuelve a abrir con lo capturado.
            var VIEJO = @json($errors->any() ? array_merge(old(), ['id' => old('_exhorto')]) : null);

            var modal = document.getElementById('exModal');
            var form = document.getElementById('exForm');
            var $ = function (id) { return document.getElementById(id); };
            var nue = $('exNue'), estado = $('exEstado'), municipio = $('exMunicipio'), contestado = $('exContestado');
            var cajaCitados = $('exCitados'), ayuda = $('exAyudaNue');

            function esc(t) { return String(t == null ? '' : t).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }

            function cargarMunicipios(idEstado, elegido) {
                municipio.innerHTML = '<option value="">' + (idEstado ? 'Cargando…' : 'Primero elige el estado') + '</option>';
                municipio.disabled = true;
                if (!idEstado) return Promise.resolve();
                return fetch(URL_MUNICIPIOS + '/' + encodeURIComponent(idEstado), { headers: { Accept: 'application/json' } })
                    .then(function (r) { return r.json(); })
                    .then(function (lista) {
                        lista.sort(function (a, b) { return a.nombre.localeCompare(b.nombre, 'es'); });
                        municipio.innerHTML = '<option value="">Sin municipio (sólo el estado)</option>' + lista.map(function (m) {
                            return '<option value="' + m.id + '">' + esc(m.nombre) + '</option>';
                        }).join('');
                        municipio.disabled = false;
                        if (elegido) municipio.value = String(elegido);
                    })
                    .catch(function () { municipio.innerHTML = '<option value="">No se pudieron cargar los municipios</option>'; });
            }

            function pintarContestado() {
                $('exFechaCaja').hidden = !contestado.checked;
                $('exContestadoTexto').textContent = contestado.checked ? 'Sí, contestado' : 'No, sin contestar';
            }

            function llenar(d) {
                d = d || {};
                var editar = !!d.id;
                form.setAttribute('action', editar ? form.dataset.actualizar.replace('__ID__', d.id) : form.dataset.crear);
                $('exMetodo').value = editar ? 'PUT' : 'POST';
                $('exId').value = d.id || '';
                $('exModalTitulo').textContent = editar ? 'Editar exhorto' : 'Registrar exhorto';
                $('exGuardar').textContent = editar ? 'Guardar cambios' : 'Registrar';
                nue.value = d.NUE || '';
                $('exSolicitante').value = d.solicitante || '';
                $('exCitado').value = d.citado || '';
                $('exIdCitado').value = d.id_citado || '';
                $('exObs').value = d.observaciones || '';
                contestado.checked = d.contestado === true || d.contestado === '1' || d.contestado === 1;
                $('exFecha').value = d.fecha_contestacion || '';
                estado.value = d.estado_id ? String(d.estado_id) : '';
                cargarMunicipios(estado.value, d.municipio_id);
                cajaCitados.hidden = true;
                cajaCitados.innerHTML = '';
                ayuda.textContent = 'Si el expediente está en el sistema, al buscarlo se llenan el solicitante y sus citados.';
                pintarContestado();
            }

            modal.addEventListener('show.bs.modal', function (e) {
                if (!e.relatedTarget) return; // reabierto por error de validación: ya trae los datos
                var crudo = e.relatedTarget.getAttribute('data-exhorto');
                llenar(crudo ? JSON.parse(crudo) : null);
            });

            estado.addEventListener('change', function () { cargarMunicipios(estado.value); });
            contestado.addEventListener('change', pintarContestado);
            // El citado escrito a mano ya no es el del sistema.
            $('exCitado').addEventListener('input', function () { $('exIdCitado').value = ''; });

            function buscar() {
                var q = nue.value.trim();
                if (q.length < 5) { ayuda.textContent = 'Escribe el número de expediente completo, por ejemplo MOR/SOL/2026/00123.'; return; }
                ayuda.textContent = 'Buscando…';
                fetch(URL_EXPEDIENTE + '?nue=' + encodeURIComponent(q), { headers: { Accept: 'application/json' } })
                    .then(function (r) { return r.json(); })
                    .then(function (j) {
                        var x = j.expediente;
                        if (!x) {
                            ayuda.textContent = 'No está en el sistema (o es de otra sede): captura los datos a mano.';
                            cajaCitados.hidden = true;
                            return;
                        }
                        nue.value = x.NUE;
                        if (x.solicitante) $('exSolicitante').value = x.solicitante;
                        ayuda.textContent = x.citados.length ? 'Elige al citado que se exhortó:' : 'El expediente no tiene citados; captúralo a mano.';
                        cajaCitados.innerHTML = x.citados.map(function (c, i) {
                            return '<button type="button" class="ex-citado" data-i="' + i + '"' + (c.registrado ? ' disabled' : '') + '>' +
                                '<span><b>' + esc(c.nombre) + '</b><small>' + esc(c.lugar || 'Sin estado') + ' · ' + esc(c.estatus) + '</small></span>' +
                                '<span class="ex-citado__marca">' + (c.registrado ? 'Ya registrado' : 'Elegir') + '</span></button>';
                        }).join('');
                        cajaCitados.hidden = !x.citados.length;
                        cajaCitados.onclick = function (e) {
                            var b = e.target.closest('.ex-citado');
                            if (!b || b.disabled) return;
                            var c = x.citados[+b.getAttribute('data-i')];
                            cajaCitados.querySelectorAll('.ex-citado').forEach(function (o) { o.classList.toggle('is-elegido', o === b); });
                            $('exCitado').value = c.nombre;
                            $('exIdCitado').value = c.id;
                            if (c.estado_id) {
                                estado.value = String(c.estado_id);
                                cargarMunicipios(estado.value, c.municipio_id);
                            }
                        };
                    })
                    .catch(function () { ayuda.textContent = 'No se pudo buscar; captura los datos a mano o intenta de nuevo.'; });
            }
            $('exBuscarNue').addEventListener('click', buscar);
            nue.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); buscar(); } });

            form.addEventListener('submit', function () { $('exGuardar').disabled = true; });

            if (VIEJO) {
                llenar(VIEJO);
                $('exIdCitado').value = VIEJO.id_citado || '';
                bootstrap.Modal.getOrCreateInstance(modal).show();
            }
        })();
    </script>
@endsection
