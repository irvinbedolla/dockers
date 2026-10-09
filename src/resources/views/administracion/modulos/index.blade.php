@extends('layouts.app')
@section('title', 'Módulos y citas')

{{--
    Administración → Módulos. Arriba se elige sede, día y origen; abajo, una
    tarjeta por módulo con quién lo atiende, qué recibe y sus citas del día.
    Editar un módulo cambia la asignación automática de aquí en adelante.
--}}

@php
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Str;

    $fechaLarga = Str::ucfirst(Carbon::parse($fecha)->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY'));
    $anterior   = Carbon::parse($fecha)->subDay()->toDateString();
    $siguiente  = Carbon::parse($fecha)->addDay()->toDateString();
    $url = fn (array $cambios) => route('modulos.index', array_filter(
        array_merge(['sede' => $sede, 'fecha' => $fecha, 'origen' => $origen], $cambios),
        fn ($v) => $v !== null && $v !== ''
    ));
    $iniciales = function (?string $nombre) {
        $partes = preg_split('/\s+/', trim((string) $nombre));
        return mb_strtoupper(mb_substr($partes[0] ?? '', 0, 1).mb_substr($partes[1] ?? '', 0, 1));
    };
    $nombreBonito = fn (?string $n) => $n ? Str::title(mb_strtolower(preg_replace('/\s+/', ' ', trim($n)))) : null;
    $sexos = ['H' => 'Hombre', 'M' => 'Mujer', 'NB' => 'No binario', 'LGBTTTIQ' => 'LGBTTTIQ'];
    $horasComida = [];
    for ($t = strtotime('08:30'); $t <= strtotime('17:30'); $t += 1800) { $horasComida[] = date('H:i', $t); }
    $sinModulo = $porModulo->get('sin', collect());
@endphp

@section('page_css')
    @include('turnos._estilos')
    <style>
        .md-barra {
            display: flex;
            flex-wrap: wrap;
            gap: 12px 18px;
            align-items: center;
            justify-content: space-between;
        }
        .md-sedes { display: flex; flex-wrap: wrap; gap: 6px; }
        .md-sede {
            padding: 6px 14px;
            border-radius: 999px;
            border: 1px solid var(--tn-borde);
            background: #fff;
            color: var(--tn-suave);
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
        }
        .md-sede:hover { color: var(--tn-verde); border-color: var(--tn-verde-claro); }
        .md-sede.is-activa { background: var(--tn-verde); border-color: var(--tn-verde); color: #fff; }

        .md-dia { display: flex; align-items: center; gap: 6px; }
        .md-dia .form-control { width: 160px; }

        .md-resumen { display: flex; flex-wrap: wrap; align-items: baseline; justify-content: space-between; gap: 10px; margin: 18px 0 12px; }
        .md-resumen h4 { margin: 0; font-size: 18px; font-weight: 700; color: var(--tn-tinta); }
        .md-filtros { display: flex; flex-wrap: wrap; gap: 6px; }

        .md-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(330px, 1fr));
            gap: 14px;
            align-items: start;
        }

        .md-modulo { padding: 0; overflow: hidden; }
        .md-modulo.is-inactivo { opacity: .65; }
        .md-cabeza { padding: 16px 18px 12px; border-bottom: 1px solid var(--tn-borde); }
        .md-cabeza__fila { display: flex; justify-content: space-between; align-items: flex-start; gap: 8px; }
        .md-nombre { margin: 0; font-size: 16px; font-weight: 700; color: var(--tn-tinta); }
        .md-num {
            min-width: 26px;
            padding: 1px 8px;
            border-radius: 999px;
            background: var(--tn-fondo);
            color: var(--tn-verde);
            font-size: 12.5px;
            font-weight: 700;
            text-align: center;
        }

        .md-persona { display: flex; align-items: center; gap: 10px; margin-top: 10px; }
        .md-avatar {
            flex: 0 0 auto;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            background: #E7ECEC;
            color: var(--tn-verde);
            font-size: 12px;
            font-weight: 700;
        }
        .md-avatar--vacio { background: #FDEBDD; color: #B4530F; }
        .md-persona__nombre { font-size: 13.5px; font-weight: 600; color: var(--tn-tinta); line-height: 1.2; }
        .md-persona__nota { font-size: 12px; color: var(--tn-tenue); }

        .md-chips { display: flex; flex-wrap: wrap; gap: 4px; margin-top: 10px; }
        .md-chip {
            padding: 1px 8px;
            border-radius: 6px;
            background: #EEF2F2;
            color: var(--tn-verde);
            font-size: 11.5px;
            font-weight: 700;
        }
        .md-chip--respaldo { background: #fff; border: 1px dashed var(--tn-verde-claro); color: var(--tn-suave); font-weight: 600; }
        .md-chip--comida { background: #FBF4E1; color: #8A6A12; }
        .md-chip--inactivo { background: #F6E4EA; color: #8C1D40; }

        .md-citas { list-style: none; margin: 0; padding: 0; }
        .md-cita { border-top: 1px solid var(--tn-borde); }
        .md-cita:first-child { border-top: 0; }
        .md-cita summary {
            display: grid;
            grid-template-columns: 48px minmax(0, 1fr) auto;
            gap: 10px;
            align-items: center;
            padding: 10px 18px;
            cursor: pointer;
            list-style: none;
        }
        .md-cita summary::-webkit-details-marker { display: none; }
        .md-cita summary:hover { background: #FAFBFB; }
        .md-cita[open] summary { background: var(--tn-fondo); }
        .md-hora { font-weight: 700; font-size: 14px; color: var(--tn-verde); font-variant-numeric: tabular-nums; }
        .md-quien { min-width: 0; }
        .md-quien__nombre { display: block; font-size: 13.5px; font-weight: 600; color: var(--tn-tinta); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .md-quien__detalle { display: flex; flex-wrap: wrap; gap: 4px 6px; align-items: center; font-size: 12px; color: var(--tn-tenue); }

        .md-datos { padding: 4px 18px 14px; background: var(--tn-fondo); }
        .md-datos dl {
            display: grid;
            grid-template-columns: 110px minmax(0, 1fr);
            gap: 4px 10px;
            margin: 0 0 10px;
            font-size: 12.5px;
        }
        .md-datos dt { color: var(--tn-tenue); font-weight: 600; }
        .md-datos dd { margin: 0; color: var(--tn-tinta); word-break: break-word; }
        .md-acciones { display: flex; flex-wrap: wrap; gap: 6px; }

        .md-vacio { padding: 16px 18px; margin: 0; font-size: 13px; color: var(--tn-tenue); }

        .md-nuevo {
            display: grid;
            place-items: center;
            gap: 6px;
            min-height: 160px;
            border: 2px dashed var(--tn-borde);
            border-radius: 12px;
            background: transparent;
            color: var(--tn-suave);
            font-weight: 600;
        }
        .md-nuevo:hover { border-color: var(--tn-verde-claro); color: var(--tn-verde); }
        .md-nuevo i { font-size: 24px; }

        /* Diálogo de reasignar */
        .md-opcion {
            display: flex;
            gap: 10px;
            align-items: flex-start;
            padding: 10px 12px;
            border: 1px solid var(--tn-borde);
            border-radius: 10px;
            margin-bottom: 8px;
            cursor: pointer;
        }
        .md-opcion:has(input:checked) { border-color: var(--tn-verde); box-shadow: inset 0 0 0 1px var(--tn-verde); }
        .md-opcion.is-bloqueada { cursor: not-allowed; background: var(--tn-fondo); color: var(--tn-tenue); }
        .md-opcion input { margin-top: 4px; }
        .md-opcion__titulo { font-weight: 700; font-size: 14px; }
        .md-opcion__nota { display: block; font-size: 12px; }
        .md-opcion__nota--mal { color: #B4530F; }
        .md-opcion__nota--ok { color: #1E7B3C; }
    </style>
@endsection

@section('content')
    <section class="section tn">
        <div class="section-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <a href="{{ route('configuracion') }}" class="text-decoration-none small" style="color: var(--tn-verde-claro);">
                    <i class="bi bi-arrow-left"></i> Administración
                </a>
                <h3 class="page__heading mb-0">Módulos y citas</h3>
            </div>
        </div>

        <div class="section-body">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            @endif

            <div class="tn-tarjeta md-barra">
                <nav class="md-sedes" aria-label="Sede">
                    @foreach ($sedes as $s)
                        <a class="md-sede {{ $s === $sede ? 'is-activa' : '' }}" href="{{ $url(['sede' => $s]) }}">{{ $s }}</a>
                    @endforeach
                </nav>

                <form method="GET" action="{{ route('modulos.index') }}" class="md-dia" id="mdDia">
                    <input type="hidden" name="sede" value="{{ $sede }}">
                    @if ($origen)<input type="hidden" name="origen" value="{{ $origen }}">@endif
                    <a class="btn btn-outline-secondary" href="{{ $url(['fecha' => $anterior]) }}" aria-label="Día anterior"><i class="bi bi-chevron-left"></i></a>
                    <input type="date" name="fecha" class="form-control" value="{{ $fecha }}" aria-label="Día">
                    <a class="btn btn-outline-secondary" href="{{ $url(['fecha' => $siguiente]) }}" aria-label="Día siguiente"><i class="bi bi-chevron-right"></i></a>
                    @if ($fecha !== $hoy)
                        <a class="btn btn-outline-secondary" href="{{ $url(['fecha' => $hoy]) }}">Hoy</a>
                    @endif
                </form>
            </div>

            <div class="md-resumen">
                <h4>{{ $fechaLarga }} · {{ $sede }}</h4>
                <div class="md-filtros">
                    <a class="tn-atajo {{ $origen ? '' : 'is-activo' }}" href="{{ $url(['origen' => null]) }}">Todas · {{ $conteo['total'] }}</a>
                    <a class="tn-atajo {{ $origen === 'linea' ? 'is-activo' : '' }}" href="{{ $url(['origen' => 'linea']) }}">En línea · {{ $conteo['linea'] }}</a>
                    <a class="tn-atajo {{ $origen === 'ventanilla' ? 'is-activo' : '' }}" href="{{ $url(['origen' => 'ventanilla']) }}">Ventanilla · {{ $conteo['ventanilla'] }}</a>
                </div>
            </div>

            <div class="md-grid">
                @foreach ($modulos as $modulo)
                    @php
                        $susCitas = $porModulo->get($modulo->id, collect());
                        $persona  = $modulo->persona;
                        $datosModulo = [
                            'id' => $modulo->id, 'nombre' => $modulo->nombre, 'user_id' => $modulo->user_id,
                            'tramites' => $modulo->tramites ?? [], 'respaldo' => $modulo->respaldo ?? [],
                            'hora_comida' => $modulo->hora_comida ? substr($modulo->hora_comida, 0, 5) : '',
                            'activo' => $modulo->activo, 'orden' => $modulo->orden,
                            'accion' => route('modulos.actualizar', $modulo),
                        ];
                    @endphp
                    <article class="tn-tarjeta md-modulo {{ $modulo->activo ? '' : 'is-inactivo' }}">
                        <header class="md-cabeza">
                            <div class="md-cabeza__fila">
                                <div>
                                    <h5 class="md-nombre">{{ $modulo->nombre }}</h5>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="md-num" title="Citas este día">{{ $susCitas->count() }}</span>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" title="Editar módulo"
                                            data-bs-toggle="modal" data-bs-target="#mdModalModulo"
                                            data-modulo="{{ json_encode($datosModulo, JSON_UNESCAPED_UNICODE) }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="md-persona">
                                @if ($persona)
                                    <span class="md-avatar" aria-hidden="true">{{ $iniciales($persona->name) }}</span>
                                    <span>
                                        <span class="md-persona__nombre d-block">{{ $nombreBonito($persona->name) }}</span>
                                        @if ($persona->estatus !== 'Activo')
                                            <span class="md-persona__nota" style="color:#B4530F;">Cuenta inactiva: no recibe citas</span>
                                        @endif
                                    </span>
                                @else
                                    <span class="md-avatar md-avatar--vacio" aria-hidden="true"><i class="bi bi-person-dash"></i></span>
                                    <span class="md-persona__nombre" style="color:#B4530F;">Sin persona asignada</span>
                                @endif
                            </div>

                            <div class="md-chips">
                                @if (! $modulo->activo)<span class="md-chip md-chip--inactivo">Inactivo</span>@endif
                                @foreach ($modulo->tramites ?? [] as $t)<span class="md-chip">{{ $t }}</span>@endforeach
                                @foreach ($modulo->respaldo ?? [] as $t)<span class="md-chip md-chip--respaldo" title="Sólo si los demás están ocupados">{{ $t }} · respaldo</span>@endforeach
                                @if ($modulo->hora_comida)<span class="md-chip md-chip--comida"><i class="bi bi-cup-hot"></i> {{ substr($modulo->hora_comida, 0, 5) }}</span>@endif
                            </div>
                        </header>

                        @if ($susCitas->isEmpty())
                            <p class="md-vacio">Sin citas este día.</p>
                        @else
                            <ul class="md-citas">
                                @foreach ($susCitas as $cita)
                                    @include('administracion.modulos._cita', ['cita' => $cita])
                                @endforeach
                            </ul>
                        @endif
                    </article>
                @endforeach

                @if ($sinModulo->isNotEmpty())
                    <article class="tn-tarjeta md-modulo">
                        <header class="md-cabeza">
                            <div class="md-cabeza__fila">
                                <h5 class="md-nombre">Sin módulo del catálogo</h5>
                                <span class="md-num">{{ $sinModulo->count() }}</span>
                            </div>
                            <p class="md-persona__nota mt-2 mb-0">Casos de excepción, turnos viejos de ventanilla o módulos que ya no existen.</p>
                        </header>
                        <ul class="md-citas">
                            @foreach ($sinModulo as $cita)
                                @include('administracion.modulos._cita', ['cita' => $cita, 'mostrarLugar' => true])
                            @endforeach
                        </ul>
                    </article>
                @endif

                <button type="button" class="md-nuevo" data-bs-toggle="modal" data-bs-target="#mdModalModulo" data-modulo="">
                    <i class="bi bi-plus-circle" aria-hidden="true"></i>
                    Nuevo módulo en {{ $sede }}
                </button>
            </div>
        </div>
    </section>

    {{-- Crear / editar módulo --}}
    <div class="modal fade" id="mdModalModulo" tabindex="-1" aria-labelledby="mdModalModuloTitulo" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <form class="modal-content" method="POST" id="mdFormModulo" action="{{ route('modulos.crear') }}">
                @csrf
                <input type="hidden" name="_method" value="POST" id="mdMetodo">
                <input type="hidden" name="delegacion" value="{{ $sede }}">
                <input type="hidden" name="fecha_vista" value="{{ $fecha }}">
                <input type="hidden" name="origen_vista" value="{{ $origen }}">
                <div class="modal-header">
                    <h5 class="modal-title" id="mdModalModuloTitulo">Nuevo módulo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-8">
                            <label class="form-label" for="mdNombre">Nombre</label>
                            <input type="text" class="form-control" id="mdNombre" name="nombre" maxlength="60" required placeholder="Modulo 6">
                        </div>
                        <div class="col-4">
                            <label class="form-label" for="mdOrden">Orden</label>
                            <input type="number" class="form-control" id="mdOrden" name="orden" min="0" max="999">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="mdPersona">Quién lo atiende</label>
                            <select class="form-select" id="mdPersona" name="user_id">
                                <option value="">Sin persona (no recibe citas)</option>
                                @foreach ($personal as $delegacion => $gente)
                                    <optgroup label="{{ $delegacion ?: 'Sin sede' }}">
                                        @foreach ($gente as $p)
                                            <option value="{{ $p->id }}">{{ $nombreBonito($p->name) }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            <div class="form-text">Al cambiar a la persona, sus citas pendientes de hoy en adelante pasan a quien elijas.</div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <span class="form-label d-block">Recibe</span>
                            @foreach ($tramites as $t)
                                <div class="form-check">
                                    <input class="form-check-input md-tramite" type="checkbox" name="tramites[]" value="{{ $t }}" id="mdT{{ $loop->index }}">
                                    <label class="form-check-label" for="mdT{{ $loop->index }}">{{ $t }}</label>
                                </div>
                            @endforeach
                        </div>
                        <div class="col-12 col-sm-6">
                            <span class="form-label d-block">De respaldo <i class="bi bi-info-circle text-muted" title="Sólo cuando ningún módulo que lo recibe de primera mano está libre"></i></span>
                            @foreach ($tramites as $t)
                                <div class="form-check">
                                    <input class="form-check-input md-respaldo" type="checkbox" name="respaldo[]" value="{{ $t }}" id="mdR{{ $loop->index }}">
                                    <label class="form-check-label" for="mdR{{ $loop->index }}">{{ $t }}</label>
                                </div>
                            @endforeach
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label" for="mdComida">Hora de comida</label>
                            <select class="form-select" id="mdComida" name="hora_comida">
                                <option value="">Ninguna</option>
                                @foreach ($horasComida as $h)<option value="{{ $h }}">{{ $h }} – {{ date('H:i', strtotime($h) + 1800) }}</option>@endforeach
                            </select>
                        </div>
                        <div class="col-12 col-sm-6 d-flex align-items-end">
                            <div class="form-check form-switch mb-2">
                                <input type="hidden" name="activo" value="0">
                                <input class="form-check-input" type="checkbox" role="switch" id="mdActivo" name="activo" value="1" checked>
                                <label class="form-check-label" for="mdActivo">Activo</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn tn-boton-dorado">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Reasignar cita --}}
    <div class="modal fade" id="mdModalReasignar" tabindex="-1" aria-labelledby="mdReasignarTitulo" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <form class="modal-content" method="POST" id="mdFormReasignar">
                @csrf
                <input type="hidden" name="fecha_vista" value="{{ $fecha }}">
                <input type="hidden" name="origen_vista" value="{{ $origen }}">
                <div class="modal-header">
                    <h5 class="modal-title" id="mdReasignarTitulo">Reasignar cita</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3" id="mdReasignarResumen"></p>
                    <div id="mdReasignarOpciones"></div>
                    <p class="small mb-0" id="mdReasignarCorreo" style="color: var(--tn-suave);"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn tn-boton-dorado" id="mdReasignarBoton" disabled>Mover cita</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        (function () {
            // Elegir otro día recarga.
            var dia = document.querySelector('#mdDia input[name="fecha"]');
            if (dia) dia.addEventListener('change', function () { this.form.submit(); });

            // --- Módulo: el mismo formulario crea y edita.
            var modalModulo = document.getElementById('mdModalModulo');
            var form = document.getElementById('mdFormModulo');
            var urlCrear = form.getAttribute('action');

            modalModulo.addEventListener('show.bs.modal', function (e) {
                var crudo = e.relatedTarget ? e.relatedTarget.getAttribute('data-modulo') : '';
                var m = crudo ? JSON.parse(crudo) : null;

                form.reset();
                document.getElementById('mdModalModuloTitulo').textContent = m ? 'Editar ' + m.nombre : 'Nuevo módulo';
                form.setAttribute('action', m ? m.accion : urlCrear);
                document.getElementById('mdMetodo').value = m ? 'PUT' : 'POST';
                document.getElementById('mdNombre').value = m ? m.nombre : '';
                document.getElementById('mdOrden').value = m ? m.orden : '';
                document.getElementById('mdPersona').value = m && m.user_id ? String(m.user_id) : '';
                document.getElementById('mdComida').value = m ? m.hora_comida : '';
                document.getElementById('mdActivo').checked = m ? !!m.activo : true;
                form.querySelectorAll('.md-tramite').forEach(function (c) { c.checked = !!(m && m.tramites.indexOf(c.value) !== -1); });
                form.querySelectorAll('.md-respaldo').forEach(function (c) { c.checked = !!(m && m.respaldo.indexOf(c.value) !== -1); });
            });

            // --- Reasignar. Las opciones vienen calculadas del servidor.
            var OPCIONES = @json($opciones);
            var urlReasignar = @json(route('modulos.reasignar', ['id' => '__ID__']));
            var modalReasignar = document.getElementById('mdModalReasignar');
            var cajaOpciones = document.getElementById('mdReasignarOpciones');
            var boton = document.getElementById('mdReasignarBoton');

            var TEXTOS = {
                actual:      'Aquí está ahora',
                libre:       'Libre a esa hora',
                ocupado:     'Ocupado: ',
                comida:      'Hora de comida',
                inactivo:    'Módulo inactivo',
                sin_persona: 'Sin persona asignada'
            };

            modalReasignar.addEventListener('show.bs.modal', function (e) {
                var b = e.relatedTarget;
                var id = b.getAttribute('data-cita');
                var tipo = b.getAttribute('data-tipo');
                var enLinea = b.getAttribute('data-linea') === '1';

                document.getElementById('mdFormReasignar').setAttribute('action', urlReasignar.replace('__ID__', id));
                document.getElementById('mdReasignarResumen').textContent = b.getAttribute('data-resumen');
                document.getElementById('mdReasignarCorreo').textContent = enLinea
                    ? 'Es una cita en línea: al moverla se le reenvía el acuse actualizado a su correo.'
                    : '';
                boton.disabled = true;
                cajaOpciones.textContent = '';

                (OPCIONES[id] || []).forEach(function (op) {
                    var elegible = op.estado === 'libre';
                    var etiqueta = document.createElement('label');
                    etiqueta.className = 'md-opcion' + (elegible ? '' : ' is-bloqueada');

                    var radio = document.createElement('input');
                    radio.type = 'radio';
                    radio.name = 'modulo_id';
                    radio.value = op.id;
                    radio.className = 'form-check-input';
                    radio.disabled = !elegible;
                    radio.addEventListener('change', function () { boton.disabled = false; });

                    var cuerpo = document.createElement('span');
                    var titulo = document.createElement('span');
                    titulo.className = 'md-opcion__titulo d-block';
                    titulo.textContent = op.nombre + (op.persona ? ' · ' + op.persona : '');

                    var nota = document.createElement('span');
                    nota.className = 'md-opcion__nota ' + (elegible ? 'md-opcion__nota--ok' : 'md-opcion__nota--mal');
                    nota.textContent = TEXTOS[op.estado] + (op.choque || '');

                    cuerpo.appendChild(titulo);
                    cuerpo.appendChild(nota);

                    if (elegible && !op.recibe) {
                        var aviso = document.createElement('span');
                        aviso.className = 'md-opcion__nota md-opcion__nota--mal';
                        aviso.textContent = 'Normalmente no recibe ' + tipo + '.';
                        cuerpo.appendChild(aviso);
                    }

                    etiqueta.appendChild(radio);
                    etiqueta.appendChild(cuerpo);
                    cajaOpciones.appendChild(etiqueta);
                });

                if (!cajaOpciones.children.length) {
                    cajaOpciones.textContent = 'No hay módulos en esta sede.';
                }
            });

            // Evita doble envío.
            document.getElementById('mdFormReasignar').addEventListener('submit', function () {
                boton.disabled = true;
                boton.innerHTML = '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span> Moviendo…';
            });
            // Si el diálogo se vuelve a abrir, el botón regresa a su estado.
            modalReasignar.addEventListener('hidden.bs.modal', function () {
                boton.textContent = 'Mover cita';
            });
        })();
    </script>
@endsection
