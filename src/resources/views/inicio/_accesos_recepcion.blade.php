{{--
    Accesos rápidos de recepción (Morelia 01, Morelia 02 y General), dentro
    de la tarjeta del saludo del Inicio. Los datos los arma
    App\Support\AccesosRecepcion::para(): crear turno, turnos de hoy, citas
    en línea de hoy y la pantalla de sala de espera.

    Espera $accesos.
--}}
@include('inicio._accesos_estilos')

<div class="ar">
    {{-- 1. Crear un turno --}}
    <div class="ar-tarjeta">
        <div class="ar-cabeza">
            <span class="ar-icono"><i class="bi bi-calendar-plus" aria-hidden="true"></i></span>
            <h3 class="ar-titulo">Crear un turno <span class="ar-sub">En ventanilla</span></h3>
        </div>
        @if ($accesos['crear'])
            <div class="ar-botones">
                @foreach ($accesos['crear'] as $c)
                    <a class="ar-boton" href="{{ $c['url'] }}"><i class="bi bi-plus-lg" aria-hidden="true"></i> {{ $c['etiqueta'] }}</a>
                @endforeach
            </div>
        @else
            <span class="ar-nota">Tus trámites no se registran como turno.</span>
        @endif
    </div>

    {{-- 2. Turnos de hoy --}}
    <div class="ar-tarjeta">
        <div class="ar-cabeza">
            <span class="ar-icono"><i class="bi bi-people" aria-hidden="true"></i></span>
            <h3 class="ar-titulo">Turnos de hoy <span class="ar-sub">{{ \Illuminate\Support\Str::ucfirst(now()->locale('es')->isoFormat('dddd D [de] MMMM')) }}</span></h3>
        </div>
        <div class="ar-cifra">{{ number_format($accesos['hoy']['total']) }}</div>
        <div class="ar-chips">
            @foreach ($accesos['hoy']['desglose'] as $d)
                @if ($d['url'])
                    <a class="ar-chip" href="{{ $d['url'] }}">{{ $d['etiqueta'] }} <b>{{ $d['total'] }}</b></a>
                @else
                    <span class="ar-chip" title="De la agenda de hoy">{{ $d['etiqueta'] }} <b>{{ $d['total'] }}</b></span>
                @endif
            @endforeach
        </div>
        <a class="ar-pie" href="{{ $accesos['hoy']['url'] }}">Ver los turnos de hoy <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
    </div>

    {{-- 3. Citas en línea de hoy --}}
    <div class="ar-tarjeta">
        <div class="ar-cabeza">
            <span class="ar-icono"><i class="bi bi-globe2" aria-hidden="true"></i></span>
            <h3 class="ar-titulo">Citas en línea <span class="ar-sub">Agendadas para hoy</span></h3>
        </div>
        <div class="ar-cifra">{{ number_format($accesos['enLinea']['total']) }}</div>
        <div class="ar-chips">
            <span class="ar-chip">Por llegar <b>{{ $accesos['enLinea']['porLlegar'] }}</b></span>
            <span class="ar-chip">En sala <b>{{ $accesos['enLinea']['enSala'] }}</b></span>
            @if ($accesos['enLinea']['tarde'] ?? 0)
                <span class="ar-chip" title="Expiraron, pero se atienden de todos modos">Llegaron tarde <b>{{ $accesos['enLinea']['tarde'] }}</b></span>
            @endif
            @if ($accesos['enLinea']['esperando'] ?? 0)
                <a class="ar-chip" href="{{ $accesos['enLinea']['urlEspera'] }}" style="background:#FBE3E3;border-color:#F1BDBD;color:#8A1717;"
                   title="Llevan {{ \App\Support\SigueEnModulo::ESPERA_ALERTA }} min o más en sala sin que las llamen">
                    <i class="bi bi-hourglass-split" aria-hidden="true"></i> {{ \App\Support\SigueEnModulo::ESPERA_ALERTA }}+ min en sala <b style="color:inherit">{{ $accesos['enLinea']['esperando'] }}</b>
                </a>
            @endif
        </div>
        <a class="ar-pie" href="{{ $accesos['enLinea']['url'] }}">Ver citas en línea <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
    </div>

    {{-- 4. Pantalla de sala de espera --}}
    <div class="ar-tarjeta">
        <div class="ar-cabeza">
            <span class="ar-icono"><i class="bi bi-display" aria-hidden="true"></i></span>
            <h3 class="ar-titulo">{{ count($accesos['pantallas']) > 1 ? 'Tus pantallas' : 'Tu pantalla' }} <span class="ar-sub">Sala de espera, para la tele</span></h3>
        </div>
        @if ($accesos['pantallas'])
            <div class="ar-botones">
                @foreach ($accesos['pantallas'] as $sede => $url)
                    <a class="ar-boton ar-boton--claro" href="{{ $url }}" target="_blank" rel="noopener" title="Abrir la pantalla de {{ $sede }} en otra pestaña">
                        {{ $sede }} <i class="bi bi-box-arrow-up-right" style="font-size: 11px" aria-hidden="true"></i>
                    </a>
                @endforeach
            </div>
        @else
            <span class="ar-nota">Tu cuenta no tiene sede con pantalla.</span>
        @endif
    </div>
</div>
