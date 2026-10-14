@extends('layouts.app')
@section('title', 'Flujo de citas en línea')

{{--
    Administración → Flujo de citas en línea (FlujoCitaController).
    El interruptor global del flujo nuevo cita → solicitud (App\Support\FlujoCita).
--}}

@php
    use App\Support\FlujoCita;

    $descripciones = [
        FlujoCita::APAGADO => ['bi-toggle-off', 'Todo como hoy. Nada del flujo nuevo se ve ni se ejecuta.'],
        FlujoCita::PRUEBAS => ['bi-cone-striped', 'Sólo las citas de prueba y los auxiliares piloto usan el flujo nuevo. La operación sigue tradicional.'],
        FlujoCita::ACTIVO  => ['bi-flag-fill', 'Banderazo: todas las citas en línea de todo el estado usan el flujo nuevo.'],
    ];
    $estadoForm  = old('estado', $estado);
    $pilotosForm = array_map('intval', (array) old('pilotos', $pilotos));
@endphp

@section('page_css')
    @include('turnos._estilos')
    <style>
        .fc { max-width: 980px; }
        .fc-estados { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; }
        .fc-estado {
            display: flex; gap: 12px; align-items: flex-start; margin: 0; cursor: pointer;
            padding: 14px 16px; border: 1px solid var(--tn-borde); border-radius: 12px; background: #fff;
        }
        .fc-estado:has(input:checked) { border-color: var(--tn-verde); box-shadow: inset 0 0 0 1px var(--tn-verde); background: #F7FAFA; }
        .fc-estado--activo:has(input:checked) { border-color: #1E7B3C; box-shadow: inset 0 0 0 1px #1E7B3C; background: #F1F8F3; }
        .fc-estado input { margin-top: 4px; flex: 0 0 auto; }
        .fc-estado i { font-size: 20px; color: var(--tn-verde); }
        .fc-estado__titulo { display: block; font-weight: 700; font-size: 15px; color: var(--tn-tinta); }
        .fc-estado__texto { display: block; font-size: 12.5px; color: var(--tn-tenue); line-height: 1.35; margin-top: 2px; }
        .fc-actual { display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 999px; font-size: 12.5px; font-weight: 700; }
        .fc-actual--apagado { background: #EEF2F2; color: var(--tn-suave); }
        .fc-actual--pruebas { background: #FBF4E1; color: #7A5D10; }
        .fc-actual--activo { background: #E5F3EA; color: #1E7B3C; }
        .fc label.fc-label { font-size: 13px; font-weight: 700; color: var(--tn-suave); margin-bottom: 4px; display: block; }
        .fc-pilotos { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 10px; }
        .fc-sede { border: 1px solid var(--tn-borde); border-radius: 10px; padding: 8px 12px; }
        .fc-sede legend { float: none; width: auto; font-size: 11.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--tn-tenue); padding: 0 6px; margin: 0; }
        .fc-sede .form-check-label { font-size: 13px; }
        .fc-sede small { color: var(--tn-tenue); }
        .fc-nota { font-size: 12.5px; color: var(--tn-tenue); }
        @media (max-width: 768px) { .fc-estados { grid-template-columns: 1fr; } }
    </style>
@endsection

@section('content')
    <section class="section tn">
        <div class="section-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <a href="{{ route('configuracion') }}" class="text-decoration-none small" style="color: var(--tn-verde-claro);">
                    <i class="bi bi-arrow-left"></i> Administración
                </a>
                <h3 class="page__heading mb-0">Flujo de citas en línea</h3>
                <span class="small" style="color: var(--tn-tenue);">Prellenado de la solicitud, aviso al auxiliar, "Mi módulo" y llamado en pantallas.</span>
            </div>
            <span class="fc-actual fc-actual--{{ $estado }}">
                <i class="bi {{ $descripciones[$estado][0] }}"></i> Ahora: {{ FlujoCita::ESTADOS[$estado] }}
            </span>
        </div>

        <div class="section-body fc">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('flujo_cita.guardar') }}" id="fcForm">
                @csrf
                @method('PUT')

                <div class="tn-tarjeta mb-3">
                    <p class="tn-titulo-seccion">Estado del flujo nuevo</p>
                    <div class="fc-estados">
                        @foreach (FlujoCita::ESTADOS as $valor => $etiqueta)
                            <label class="fc-estado fc-estado--{{ $valor }}">
                                <input class="form-check-input" type="radio" name="estado" value="{{ $valor }}" @checked($estadoForm === $valor)>
                                <span>
                                    <span class="fc-estado__titulo"><i class="bi {{ $descripciones[$valor][0] }} me-1"></i>{{ $etiqueta }}</span>
                                    <span class="fc-estado__texto">{{ $descripciones[$valor][1] }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="tn-tarjeta mb-3">
                    <p class="tn-titulo-seccion">Pruebas</p>
                    <div class="row g-3">
                        <div class="col-md-7">
                            <label class="fc-label" for="correos">Correos de prueba</label>
                            <textarea class="form-control" id="correos" name="correos" rows="4" placeholder="uno por renglón">{{ old('correos', $correos) }}</textarea>
                            <div class="fc-nota mt-1">
                                Las citas agendadas en <a href="{{ route('citas') }}" target="_blank" rel="noopener">/generar-cita</a> con estos correos quedan marcadas como prueba.
                                En <a href="{{ route('nueva_cita') }}">/turnos/nuevo</a> también hay una casilla "Cita de prueba" (sólo Super Usuario).
                            </div>
                        </div>
                        <div class="col-md-5">
                            <label class="fc-label" for="minutos">Avisar al auxiliar</label>
                            <div class="input-group" style="max-width: 220px;">
                                <input type="number" class="form-control" id="minutos" name="minutos" min="1" max="60" value="{{ old('minutos', $minutos) }}">
                                <span class="input-group-text">min antes</span>
                            </div>
                            <div class="fc-nota mt-2">
                                Citas de prueba: <strong>{{ $pruebas['hoy'] }}</strong> hoy, <strong>{{ $pruebas['total'] }}</strong> en total.<br>
                                Se borran con <code>php artisan citas:limpiar-pruebas</code>.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tn-tarjeta mb-3">
                    <p class="tn-titulo-seccion">Auxiliares piloto</p>
                    <p class="fc-nota mb-2">En "Pruebas", sólo ellos (y los Super Usuario) ven "Mi módulo". En "Activo" lo ve todo el que atiende un módulo.</p>
                    @if ($personal->isEmpty())
                        <p class="fc-nota mb-0">No hay módulos activos con persona asignada.</p>
                    @else
                        <div class="fc-pilotos">
                            @foreach ($personal as $sede => $gente)
                                <fieldset class="fc-sede">
                                    <legend>{{ $sede }}</legend>
                                    @foreach ($gente as $p)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="pilotos[]" value="{{ $p->id }}" id="piloto-{{ $p->id }}-{{ $loop->index }}" @checked(in_array((int) $p->id, $pilotosForm, true))>
                                            <label class="form-check-label" for="piloto-{{ $p->id }}-{{ $loop->index }}">
                                                {{ \Illuminate\Support\Str::title(mb_strtolower($p->name)) }} <small>· {{ preg_replace('/^m[oó]dulo/i', 'Módulo', $p->modulo) }}</small>
                                            </label>
                                        </div>
                                    @endforeach
                                </fieldset>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('configuracion') }}" class="btn btn-outline-secondary">Cancelar</a>
                    <button type="submit" class="btn tn-boton-dorado">Guardar</button>
                </div>
            </form>
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        // El banderazo pide confirmación: cambia la operación de todo el estado.
        document.getElementById('fcForm').addEventListener('submit', function (e) {
            var elegido = this.querySelector('input[name="estado"]:checked');
            if (elegido && elegido.value === 'activo' && @json($estado) !== 'activo'
                && !confirm('Vas a activar el flujo nuevo para TODAS las citas en línea del estado. ¿Continuar?')) {
                e.preventDefault();
            }
        });
    </script>
@endsection
