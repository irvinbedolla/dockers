{{--
    Administración → Módulos: tope de citas en línea por conexión (IP), correo,
    teléfono y nombre completo, cada uno con su periodo, y listas blanca y
    negra de correos (App\Support\LimiteCitas), para que una sola oficina no
    acapare los turnos. Aplica a todas las sedes. Espera $limite, $limiteHoy,
    $sede, $fecha y $origen.
--}}
@php
    use App\Support\LimiteCitas;
    $migradoLimite = LimiteCitas::migrado();
    $porCriterio   = LimiteCitas::porCriterio();
    $activoLimite  = $migradoLimite && LimiteCitas::activo($limite);
    $exentas       = LimiteCitas::listaExentas((string) $limite->exentas);
    $listaBlanca   = LimiteCitas::listaCorreos((string) $limite->lista_blanca);
    $listaNegra    = LimiteCitas::listaCorreos((string) $limite->lista_negra);
    $reglas        = LimiteCitas::resumen($limite);
    $ayudaCriterio = [
        'ip'       => 'Misma conexión a internet.',
        'correo'   => 'Mismo correo electrónico.',
        'telefono' => 'Mismo número (sólo dígitos).',
        'nombre'   => 'Nombre con apellidos de quien agenda.',
    ];
    $camposLimite = array_merge(['exentas', 'lista_blanca', 'lista_negra'], ...array_map(fn ($c) => [$c[0], $c[1]], array_values(LimiteCitas::CRITERIOS)));
    $porAlcance   = LimiteCitas::porAlcance();
    $sedesTodas   = LimiteCitas::sedes();
    $porTramite   = LimiteCitas::porTramite();
    $tramitesTodos = LimiteCitas::tramites();
    // Al volver por un error se respeta lo que se había marcado (nada marcado = todas).
    $hayOld = old('por_ip') !== null;
    foreach (array_keys(LimiteCitas::CRITERIOS) as $k) {
        $camposLimite[] = 'sedes_'.$k;
        $camposLimite[] = 'sedes_'.$k.'.*';
        $camposLimite[] = 'canal_'.$k;
        $camposLimite[] = 'tramites_'.$k;
        $camposLimite[] = 'tramites_'.$k.'.*';
    }
@endphp

<div class="tn-tarjeta md-limite mb-3" id="limiteCitas">
    <div class="md-limite__cab">
        <div>
            <h5 class="md-limite__titulo">
                <i class="bi bi-shield-check" aria-hidden="true"></i> Límite de citas en línea
                @if (! $porCriterio)
                    <span class="md-chip md-chip--inactivo">Falta la migración</span>
                @elseif ($activoLimite)
                    <span class="md-chip">Activo</span>
                @else
                    <span class="md-chip md-chip--respaldo">Sin límite</span>
                @endif
            </h5>
            <p class="md-limite__texto mb-0">
                @if (! $porCriterio)
                    Corre <code>php artisan migrate</code> para poder poner un tope por conexión, correo, teléfono y nombre, cada uno con su periodo, y las listas de correos.
                @elseif ($reglas)
                    Máximo {{ implode(' · ', $reglas) }}.
                    @if ($exentas)
                        {{ count($exentas) === 1 ? '1 conexión exenta' : count($exentas).' conexiones exentas' }}.
                    @endif
                @else
                    Cualquier conexión puede registrar todas las citas que quiera. Pon un tope para que una sola oficina no acapare los turnos.
                @endif
            </p>
            @if ($porCriterio && ($listaBlanca || $listaNegra))
                <p class="md-limite__texto mb-0">
                    @if ($listaBlanca)
                        <span class="md-chip md-chip--respaldo"><i class="bi bi-check-circle" aria-hidden="true"></i> {{ count($listaBlanca) }} en lista blanca</span>
                    @endif
                    @if ($listaNegra)
                        <span class="md-chip md-chip--inactivo"><i class="bi bi-slash-circle" aria-hidden="true"></i> {{ count($listaNegra) }} en lista negra</span>
                    @endif
                </p>
            @endif
            @if ($migradoLimite && $limite->actualizado_por_nombre)
                <p class="md-persona__nota mb-0">Último cambio: {{ \Illuminate\Support\Str::title(mb_strtolower($limite->actualizado_por_nombre)) }} · {{ \Illuminate\Support\Carbon::parse($limite->updated_at)->format('d/m/Y H:i') }}</p>
            @endif
        </div>
        @if ($migradoLimite)
            <div class="md-limite__acciones">
                @if (Route::has('estadisticas.turnos'))
                    <a class="btn btn-outline-secondary btn-sm" href="{{ route('estadisticas.turnos', ['desde' => now()->toDateString(), 'hasta' => now()->addDays(30)->toDateString(), 'agrupar' => 'semana', 'origen' => 'linea']) }}#agendadores">
                        <i class="bi bi-bar-chart"></i> Ver quién agenda
                    </a>
                @endif
                @if ($porCriterio)
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#mdModalLimite">
                        <i class="bi bi-sliders"></i> Cambiar límite
                    </button>
                @endif
            </div>
        @endif
    </div>

    @if ($migradoLimite)
        <div class="md-limite__hoy">
            <span><b>{{ number_format($limiteHoy['citas']) }}</b> citas en línea registradas hoy</span>
            <span class="{{ $limiteHoy['bloqueos'] ? 'md-limite__alerta' : '' }}"><b>{{ number_format($limiteHoy['bloqueos']) }}</b> {{ $limiteHoy['bloqueos'] === 1 ? 'intento detenido' : 'intentos detenidos' }} por el límite</span>
            @if ($limiteHoy['top'])
                <span>La conexión con más citas hoy: <code>{{ $limiteHoy['top']->ip }}</code> · <b>{{ $limiteHoy['top']->citas }}</b></span>
            @endif
        </div>
    @endif
</div>

@if ($porCriterio)
    {{-- El diálogo va al final del <body> (stack modals del layout): dentro de la
         tarjeta quedaba debajo del fondo oscuro y no se podía usar. --}}
    @push('modals')
    <div class="modal fade" id="mdModalLimite" tabindex="-1" aria-labelledby="mdModalLimiteTitulo" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <form class="modal-content" method="POST" action="{{ route('modulos.limite') }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="sede_vista" value="{{ $sede }}">
                <input type="hidden" name="fecha_vista" value="{{ $fecha }}">
                <input type="hidden" name="origen_vista" value="{{ $origen }}">
                <div class="modal-header">
                    <h5 class="modal-title" id="mdModalLimiteTitulo">Límite de citas en línea</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p class="md-persona__nota">
                        Cada tope aplica en las sedes, el canal y los trámites que elijas: <b>en línea</b> (/generar-cita), <b>en ventanilla</b> (/turnos/nuevo) o en las dos,
                        y cuenta sólo las citas de esas sedes, ese canal y esos trámites. Deja <b>0</b> para no limitar ese dato.
                    </p>

                    <div class="md-lim-tabla {{ $porAlcance ? 'md-lim-tabla--alcance' : '' }} {{ $porTramite ? 'md-lim-tabla--tramites' : '' }}" role="group" aria-label="Topes por dato">
                        <div class="md-lim-tabla__cab" aria-hidden="true">
                            <span>Dato</span><span>Máximo de citas</span><span>Se cuentan las registradas</span>
                            @if ($porAlcance)<span>Sedes</span><span>Aplica</span>@endif
                            @if ($porTramite)<span>Trámites</span>@endif
                        </div>
                        @foreach (LimiteCitas::CRITERIOS as $criterio => [$col, $colPeriodo, $etiqueta])
                            <div class="md-lim-tabla__fila">
                                <div>
                                    <label class="form-label mb-0 fw-semibold" for="lim_{{ $col }}">{{ $etiqueta }}</label>
                                    <div class="md-persona__nota">{{ $ayudaCriterio[$criterio] }}</div>
                                </div>
                                <div>
                                    <input type="number" class="form-control @error($col) is-invalid @enderror" id="lim_{{ $col }}" name="{{ $col }}"
                                           min="0" max="{{ LimiteCitas::MAXIMO }}" value="{{ old($col, $limite->{$col}) }}" required>
                                    @error($col)<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div>
                                    <select class="form-select @error($colPeriodo) is-invalid @enderror" id="lim_{{ $colPeriodo }}" name="{{ $colPeriodo }}" aria-label="Periodo por {{ mb_strtolower($etiqueta) }}">
                                        @foreach (LimiteCitas::GRUPOS as $grupo => $claves)
                                            <optgroup label="{{ $grupo }}">
                                                @foreach ($claves as $clave)
                                                    <option value="{{ $clave }}" @selected(old($colPeriodo, $limite->{$colPeriodo}) === $clave)>{{ \Illuminate\Support\Str::ucfirst(LimiteCitas::PERIODOS[$clave]) }}</option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                    @error($colPeriodo)<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                @if ($porAlcance)
                                    @php
                                        $marcadas = $hayOld ? (array) old('sedes_'.$criterio, []) : $limite->{'sedes_'.$criterio};
                                        $marcadas = $marcadas ?: $sedesTodas;
                                        $canalSel = old('canal_'.$criterio, $limite->{'canal_'.$criterio});
                                        $soloLinea = in_array($criterio, LimiteCitas::SOLO_LINEA, true);
                                    @endphp
                                    <div>
                                        <span class="md-lim-tabla__etq" aria-hidden="true">Sedes</span>
                                        <div class="dropdown md-lim-sedes" data-todo="Todas" data-plural="sedes">
                                            <button type="button" class="form-select text-start md-lim-sedes__boton @error('sedes_'.$criterio) is-invalid @enderror @error('sedes_'.$criterio.'.*') is-invalid @enderror"
                                                    data-bs-toggle="dropdown" data-bs-auto-close="outside" data-bs-popper-config='{"strategy":"fixed"}'
                                                    aria-expanded="false" aria-label="Sedes por {{ mb_strtolower($etiqueta) }}">
                                                <span class="md-lim-sedes__texto">Todas</span>
                                            </button>
                                            <div class="dropdown-menu md-lim-sedes__menu">
                                                <div class="form-check">
                                                    <input class="form-check-input md-lim-sedes__todas" type="checkbox" id="limSedesTodas_{{ $criterio }}">
                                                    <label class="form-check-label fw-semibold" for="limSedesTodas_{{ $criterio }}">Todas las sedes</label>
                                                </div>
                                                <hr class="dropdown-divider">
                                                @foreach ($sedesTodas as $i => $s)
                                                    <div class="form-check">
                                                        <input class="form-check-input md-lim-sedes__sede" type="checkbox" name="sedes_{{ $criterio }}[]" value="{{ $s }}"
                                                               id="limSede_{{ $criterio }}_{{ $i }}" @checked(in_array($s, $marcadas, true))>
                                                        <label class="form-check-label" for="limSede_{{ $criterio }}_{{ $i }}">{{ $s }}</label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        @error('sedes_'.$criterio)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                        @error('sedes_'.$criterio.'.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                    <div>
                                        <span class="md-lim-tabla__etq" aria-hidden="true">Aplica</span>
                                        @if ($soloLinea)
                                            <input type="hidden" name="canal_{{ $criterio }}" value="linea">
                                            <select class="form-select" disabled aria-label="Dónde aplica por {{ mb_strtolower($etiqueta) }}">
                                                <option>En línea</option>
                                            </select>
                                            <div class="md-persona__nota">En ventanilla la conexión es la de la oficina.</div>
                                        @else
                                            <select class="form-select @error('canal_'.$criterio) is-invalid @enderror" name="canal_{{ $criterio }}" aria-label="Dónde aplica por {{ mb_strtolower($etiqueta) }}">
                                                @foreach (LimiteCitas::CANALES as $clave => $texto)
                                                    <option value="{{ $clave }}" @selected($canalSel === $clave)>{{ $texto }}</option>
                                                @endforeach
                                            </select>
                                            @error('canal_'.$criterio)<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        @endif
                                    </div>
                                @endif
                                @if ($porTramite)
                                    @php
                                        // Nada marcado = todos (así vienen de inicio).
                                        $marcados = $hayOld ? (array) old('tramites_'.$criterio, []) : $limite->{'tramites_'.$criterio};
                                        $marcados = $marcados ?: $tramitesTodos;
                                    @endphp
                                    <div>
                                        <span class="md-lim-tabla__etq" aria-hidden="true">Trámites</span>
                                        <div class="dropdown md-lim-sedes" data-todo="Todos" data-plural="trámites">
                                            <button type="button" class="form-select text-start md-lim-sedes__boton @error('tramites_'.$criterio) is-invalid @enderror @error('tramites_'.$criterio.'.*') is-invalid @enderror"
                                                    data-bs-toggle="dropdown" data-bs-auto-close="outside" data-bs-popper-config='{"strategy":"fixed"}'
                                                    aria-expanded="false" aria-label="Trámites por {{ mb_strtolower($etiqueta) }}">
                                                <span class="md-lim-sedes__texto">Todos</span>
                                            </button>
                                            <div class="dropdown-menu md-lim-sedes__menu">
                                                <div class="form-check">
                                                    <input class="form-check-input md-lim-sedes__todas" type="checkbox" id="limTramTodos_{{ $criterio }}">
                                                    <label class="form-check-label fw-semibold" for="limTramTodos_{{ $criterio }}">Todos los trámites</label>
                                                </div>
                                                <hr class="dropdown-divider">
                                                @foreach ($tramitesTodos as $i => $t)
                                                    <div class="form-check">
                                                        <input class="form-check-input md-lim-sedes__sede" type="checkbox" name="tramites_{{ $criterio }}[]" value="{{ $t }}"
                                                               id="limTram_{{ $criterio }}_{{ $i }}" @checked(in_array($t, $marcados, true))>
                                                        <label class="form-check-label" for="limTram_{{ $criterio }}_{{ $i }}">{{ $t }}</label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        @error('tramites_'.$criterio)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                        @error('tramites_'.$criterio.'.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <div class="form-text mb-3">
                        <b>Periodo móvil:</b> cuentan las citas registradas hoy (desde las 00:00) o en los últimos 7, 15 o 30 días.
                        <b>A partir de que se registra:</b> el plazo empieza con la primera cita de ese dato que nunca se había usado
                        o cuyo plazo anterior ya terminó; al vencer, la siguiente cita abre un plazo nuevo.
                        En los dos casos cuentan todas las citas registradas, sin importar para qué día son ni si después se cancelan.
                        @if ($porAlcance)
                            En ventanilla el correo y el teléfono son opcionales: si el auxiliar no los escribe, ese tope no se revisa.
                        @endif
                    </div>

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" for="limExentas">Conexiones sin límite (opcional)</label>
                            <textarea class="form-control @error('exentas') is-invalid @enderror" id="limExentas" name="exentas" rows="2" placeholder="Una por renglón. Ej. 201.144.10.25 o 201.144.10.0/24">{{ old('exentas', $limite->exentas) }}</textarea>
                            @error('exentas')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">
                                Por ejemplo, la de un módulo del Centro donde se ayuda a la gente a agendar. Tu conexión ahora es
                                <code>{{ request()->ip() }}</code>.
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="limBlanca"><i class="bi bi-check-circle text-success" aria-hidden="true"></i> Lista blanca de correos</label>
                            <textarea class="form-control @error('lista_blanca') is-invalid @enderror" id="limBlanca" name="lista_blanca" rows="4" placeholder="Uno por renglón. Ej. persona@correo.com o @empresa.com">{{ old('lista_blanca', $limite->lista_blanca) }}</textarea>
                            @error('lista_blanca')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">Con estos correos no aplica ningún tope (ni de conexión, teléfono o nombre).</div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="limNegra"><i class="bi bi-slash-circle text-danger" aria-hidden="true"></i> Lista negra de correos</label>
                            <textarea class="form-control @error('lista_negra') is-invalid @enderror" id="limNegra" name="lista_negra" rows="4" placeholder="Uno por renglón. Ej. persona@correo.com o @empresa.com">{{ old('lista_negra', $limite->lista_negra) }}</textarea>
                            @error('lista_negra')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">Con estos correos no se registra la cita en línea ni el turno en ventanilla.</div>
                        </div>
                    </div>
                    <div class="form-text">Escribe el correo completo, o <code>@dominio.com</code> para todos los correos de ese dominio.</div>

                    <div class="md-limite__nota mt-3">
                        <i class="bi bi-info-circle" aria-hidden="true"></i>
                        Varias personas pueden compartir una misma IP (una empresa, una escuela o un mismo proveedor de celular).
                        Un tope muy bajo por IP puede detener a gente que no tiene nada que ver; revisa antes en las estadísticas cuántas citas llegan de cada conexión.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Guardar límite</button>
                </div>
            </form>
        </div>
    </div>

    @if ($porAlcance)
        <script>
            // Sedes y trámites de cada tope: "Todas"/"Todos" marca o desmarca
            // todo; si no queda nada marcado se vuelve a marcar todo (nada
            // marcado = todas las sedes o todos los trámites).
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('#mdModalLimite .md-lim-sedes').forEach(function (caja) {
                    var todas = caja.querySelector('.md-lim-sedes__todas');
                    var sedes = Array.prototype.slice.call(caja.querySelectorAll('.md-lim-sedes__sede'));
                    var texto = caja.querySelector('.md-lim-sedes__texto');
                    var todo = caja.getAttribute('data-todo') || 'Todas';
                    var plural = caja.getAttribute('data-plural') || 'sedes';
                    function pintar() {
                        var marcadas = sedes.filter(function (s) { return s.checked; });
                        if (marcadas.length === 0) { sedes.forEach(function (s) { s.checked = true; }); marcadas = sedes; }
                        var completas = marcadas.length === sedes.length;
                        todas.checked = completas;
                        texto.textContent = completas ? todo
                            : (marcadas.length <= 2 ? marcadas.map(function (s) { return s.value; }).join(', ') : marcadas.length + ' ' + plural);
                        caja.querySelector('.md-lim-sedes__boton').title = completas ? todo + ' las ' + plural : marcadas.map(function (s) { return s.value; }).join(', ');
                    }
                    todas.addEventListener('change', function () {
                        if (todas.checked) { sedes.forEach(function (s) { s.checked = true; }); }
                        else { sedes.forEach(function (s, i) { s.checked = i === 0; }); }
                        pintar();
                    });
                    sedes.forEach(function (s) { s.addEventListener('change', pintar); });
                    pintar();
                });
            });
        </script>
    @endif

    @if ($errors->hasAny($camposLimite))
        {{-- Si el límite no se guardó por un dato mal escrito, se vuelve a abrir el diálogo. --}}
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var m = document.getElementById('mdModalLimite');
                if (m && window.bootstrap) { bootstrap.Modal.getOrCreateInstance(m).show(); }
            });
        </script>
    @endif
    @endpush
@endif
