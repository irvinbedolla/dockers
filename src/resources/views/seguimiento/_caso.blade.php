{{--
    El cuerpo del seguimiento de un caso (App\Support\SeguimientoCaso::linea):
    encabezado, responsables, próxima audiencia, etapas y línea de tiempo con
    sus documentos. Lo usan la página completa (seguimiento/ver) y el modal
    de las pantallas de audiencia (seguimiento/_boton_modal).
    Requiere turnos/_estilos y seguimiento/_estilos.
--}}
@php
    $dia = fn ($c) => \Illuminate\Support\Str::ucfirst($c->locale('es')->isoFormat('ddd D MMM YYYY'));
@endphp

<div class="sg-cabeza">
    <div>
        <h4 class="sg-persona">{{ $caso['persona'] }}</h4>
        @if ($caso['excepcion'])
            <span class="sg-chip sg-chip--excepcion mt-1"><i class="bi bi-shield-exclamation"></i> Caso de excepción · {{ $caso['excepcion'] }}</span>
        @endif
        <div class="sg-datos">
            @if ($caso['nue'])<span class="sg-dato">NUE <b>{{ $caso['nue'] }}</b></span>@endif
            @if ($caso['folio'])<span class="sg-dato">Cita <b>{{ $caso['folio'] }}</b></span>@endif
            @if ($caso['curp'])<span class="sg-dato">CURP <b>{{ $caso['curp'] }}</b></span>@endif
            <span class="sg-dato"><i class="bi bi-geo-alt"></i> {{ $caso['sede'] }}</span>
        </div>
    </div>
    <div class="text-end">
        <span class="sg-estado {{ $caso['favorable'] ? 'is-favorable' : ($caso['resuelto'] ? 'is-resuelto' : '') }}">{{ $caso['estado'] }}</span>
        @if ($caso['dias'] !== null)
            <div class="sg-dias">{{ $caso['resuelto'] ? 'Se resolvió en' : 'Lleva' }} <b>{{ $caso['dias'] }}</b> {{ $caso['dias'] === 1 ? 'día' : 'días' }}</div>
        @endif
    </div>
</div>

@if ($caso['responsables'])
    <div class="sg-responsables">
        @foreach ($caso['responsables'] as $r)
            <div class="sg-responsable">
                <i class="bi bi-{{ $r['icono'] }}" aria-hidden="true"></i>
                <div><span>{{ $r['rol'] }}</span><b>{{ $r['nombre'] }}</b></div>
            </div>
        @endforeach
    </div>
@endif

@if ($caso['proxima'])
    @php($p = $caso['proxima'])
    <div class="sg-proxima">
        <i class="bi bi-calendar-event" aria-hidden="true"></i>
        <div>
            <b>{{ $p['numero'] ? 'Audiencia '.$p['numero'] : 'Próxima audiencia' }}:</b>
            {{ \Illuminate\Support\Str::ucfirst($p['cuando']->locale('es')->isoFormat('dddd D [de] MMMM [a las] HH:mm')) }}
            @if ($p['conciliador']) · con <b>{{ $p['conciliador'] }}</b>@endif
            @if ($p['sala']) · {{ $p['sala'] }}@endif
        </div>
    </div>
@endif

<div class="sg-etapas" aria-label="Etapas del caso">
    @foreach ($caso['etapas'] as $e)
        <span class="sg-etapa {{ $e['hecha'] ? 'is-hecha' : '' }}">{{ $e['nombre'] }}</span>
    @endforeach
</div>

@if (! $caso['eventos'])
    <p class="sg-nota">Todavía no hay movimientos con fecha.</p>
@else
    <ol class="sg-linea">
        @php($marcoHoy = false)
        @foreach ($caso['eventos'] as $e)
            @if ($e['futuro'] && ! $marcoHoy)
                @php($marcoHoy = true)
                <li class="sg-hoy" aria-hidden="true"><span>Hoy · lo que sigue</span></li>
            @endif
            @if ($e['dias'])
                <li class="sg-hueco" aria-hidden="true"><span>+{{ $e['dias'] }} {{ $e['dias'] === 1 ? 'día' : 'días' }}</span></li>
            @endif
            <li class="sg-evento sg-evento--{{ $e['tono'] }} {{ $e['futuro'] ? 'sg-evento--futuro' : '' }}">
                <div class="sg-evento__fecha">
                    <b>{{ $dia($e['cuando']) }}</b>
                    @unless ($e['soloFecha']){{ $e['cuando']->format('H:i') }}@endunless
                </div>
                <span class="sg-evento__punto"><i class="bi bi-{{ $e['icono'] }}" aria-hidden="true"></i></span>
                <div>
                    <div class="sg-evento__titulo">
                        {{ $e['titulo'] }}
                        <span class="sg-evento__etapa">{{ \App\Support\SeguimientoCaso::ETAPAS[$e['etapa']] }}</span>
                        @if ($e['futuro'])<span class="sg-evento__etapa" style="background:#FBF4E1;color:#7A5D10;">Programado</span>@endif
                    </div>
                    @if ($e['detalle'])<div class="sg-evento__detalle">{{ $e['detalle'] }}</div>@endif
                    @if (! empty($e['docs']))
                        <div class="sg-docs">
                            @foreach ($e['docs'] as $d)
                                <a class="sg-doc" href="{{ $d['url'] }}" target="_blank" rel="noopener"><i class="bi bi-file-earmark-pdf" aria-hidden="true"></i> {{ $d['nombre'] }}</a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </li>
        @endforeach
    </ol>
@endif

@if (! $caso['folio'])
    <p class="sg-nota"><i class="bi bi-info-circle"></i> Esta solicitud no está ligada a una cita: se levantó sin cita o antes de que el sistema las ligara.</p>
@elseif ($caso['vinculo'] === 'historico')
    <p class="sg-nota"><i class="bi bi-info-circle"></i> La cita se ligó después por la CURP: es la más cercana a la solicitud.</p>
@endif
<p class="sg-nota sg-no-imprimir"><i class="bi bi-file-earmark-pdf"></i> Los documentos se abren en otra pestaña. Los que genera el sistema (acuses, actas, convenios) salen con los datos de hoy.</p>
