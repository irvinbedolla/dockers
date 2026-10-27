{{--
    Administración → Módulos: tope de citas en línea por conexión (IP), correo
    y teléfono (App\Support\LimiteCitas), para que una sola oficina no acapare
    los turnos. Aplica a todas las sedes. Espera $limite, $limiteHoy, $sede,
    $fecha y $origen.
--}}
@php
    use App\Support\LimiteCitas;
    $migradoLimite = LimiteCitas::migrado();
    $activoLimite  = $migradoLimite && LimiteCitas::activo($limite);
    $exentas       = LimiteCitas::listaExentas((string) $limite->exentas);
    $regla = function (int $n, string $que) use ($limite) {
        return $n > 0 ? $n.' '.($n === 1 ? 'cita' : 'citas').' por '.$que : null;
    };
    $reglas = array_filter([
        $regla((int) $limite->por_ip, 'conexión (IP)'),
        $regla((int) $limite->por_correo, 'correo'),
        $regla((int) $limite->por_telefono, 'teléfono'),
    ]);
@endphp

<div class="tn-tarjeta md-limite mb-3" id="limiteCitas">
    <div class="md-limite__cab">
        <div>
            <h5 class="md-limite__titulo">
                <i class="bi bi-shield-check" aria-hidden="true"></i> Límite de citas en línea
                @if (! $migradoLimite)
                    <span class="md-chip md-chip--inactivo">Falta la migración</span>
                @elseif ($activoLimite)
                    <span class="md-chip">Activo</span>
                @else
                    <span class="md-chip md-chip--respaldo">Sin límite</span>
                @endif
            </h5>
            <p class="md-limite__texto mb-0">
                @if (! $migradoLimite)
                    Corre <code>php artisan migrate</code> para empezar a guardar desde qué conexión se agenda y poder poner un tope.
                @elseif ($activoLimite)
                    Máximo {{ implode(' · ', $reglas) }} <b>{{ LimiteCitas::PERIODOS[$limite->periodo] }}</b>, en todas las sedes.
                    @if ($exentas)
                        {{ count($exentas) === 1 ? '1 conexión exenta' : count($exentas).' conexiones exentas' }}.
                    @endif
                @else
                    Cualquier conexión puede registrar todas las citas que quiera. Pon un tope para que una sola oficina no acapare los turnos.
                @endif
            </p>
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
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#mdModalLimite">
                    <i class="bi bi-sliders"></i> Cambiar límite
                </button>
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

@if ($migradoLimite)
    <div class="modal fade" id="mdModalLimite" tabindex="-1" aria-labelledby="mdModalLimiteTitulo" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
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
                    <p class="md-persona__nota">Aplica a las citas que se agendan en <b>/generar-cita</b>, en todas las sedes. Las de ventanilla no tienen límite. Deja <b>0</b> para no limitar.</p>

                    <div class="row g-3">
                        <div class="col-12 col-sm-4">
                            <label class="form-label" for="limPorIp">Por conexión (IP)</label>
                            <input type="number" class="form-control" id="limPorIp" name="por_ip" min="0" max="{{ LimiteCitas::MAXIMO }}" value="{{ old('por_ip', $limite->por_ip) }}" required>
                        </div>
                        <div class="col-12 col-sm-4">
                            <label class="form-label" for="limPorCorreo">Por correo</label>
                            <input type="number" class="form-control" id="limPorCorreo" name="por_correo" min="0" max="{{ LimiteCitas::MAXIMO }}" value="{{ old('por_correo', $limite->por_correo) }}" required>
                        </div>
                        <div class="col-12 col-sm-4">
                            <label class="form-label" for="limPorTel">Por teléfono</label>
                            <input type="number" class="form-control" id="limPorTel" name="por_telefono" min="0" max="{{ LimiteCitas::MAXIMO }}" value="{{ old('por_telefono', $limite->por_telefono) }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="limPeriodo">Se cuentan las citas registradas</label>
                            <select class="form-select" id="limPeriodo" name="periodo">
                                @foreach (LimiteCitas::PERIODOS as $clave => $texto)
                                    <option value="{{ $clave }}" @selected(old('periodo', $limite->periodo) === $clave)>{{ \Illuminate\Support\Str::ucfirst($texto) }}</option>
                                @endforeach
                            </select>
                            <div class="form-text">Cuentan todas las citas registradas en ese tiempo, sin importar para qué día son ni si después se cancelan.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="limExentas">Conexiones sin límite (opcional)</label>
                            <textarea class="form-control" id="limExentas" name="exentas" rows="3" placeholder="Una por renglón. Ej. 201.144.10.25 o 201.144.10.0/24">{{ old('exentas', $limite->exentas) }}</textarea>
                            <div class="form-text">
                                Por ejemplo, la de un módulo del Centro donde se ayuda a la gente a agendar. Tu conexión ahora es
                                <code>{{ request()->ip() }}</code>.
                            </div>
                        </div>
                    </div>

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

    @if ($errors->hasAny(['por_ip', 'por_correo', 'por_telefono', 'periodo', 'exentas']))
        {{-- Si el límite no se guardó por un dato mal escrito, se vuelve a abrir el diálogo. --}}
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var m = document.getElementById('mdModalLimite');
                if (m && window.bootstrap) { bootstrap.Modal.getOrCreateInstance(m).show(); }
            });
        </script>
    @endif
@endif
