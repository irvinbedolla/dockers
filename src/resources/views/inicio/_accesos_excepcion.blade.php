{{--
    Accesos rápidos del Departamento de Conciliación, Género e Igualdad, en
    la tarjeta del saludo del Inicio. Los datos salen de
    App\Support\CasosExcepcion::inicio(); todas las sedes.

    Espera $casos.
--}}
@include('inicio._accesos_estilos')

<div class="ar">
    {{-- 1. Casos de hoy --}}
    <div class="ar-tarjeta">
        <div class="ar-cabeza">
            <span class="ar-icono" style="background:#F6E4EA;color:#8C1D40;"><i class="bi bi-shield-exclamation" aria-hidden="true"></i></span>
            <h3 class="ar-titulo">Casos de hoy <span class="ar-sub">{{ \Illuminate\Support\Str::ucfirst(now()->locale('es')->isoFormat('dddd D [de] MMMM')) }}</span></h3>
        </div>
        <div class="ar-cifra">{{ number_format($casos['hoy']['total']) }}</div>
        <div class="ar-chips">
            @forelse ($casos['hoy']['porSede'] as $s)
                <a class="ar-chip" href="{{ route('excepcion', ['sede' => $s['sede']]) }}">{{ $s['sede'] }} <b>{{ $s['total'] }}</b></a>
            @empty
                <span class="ar-nota">Ninguna sede tiene casos hoy.</span>
            @endforelse
        </div>
        <a class="ar-pie" href="{{ $casos['hoy']['url'] }}">Ver los casos de hoy <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
    </div>

    {{-- 2. Por atender --}}
    <div class="ar-tarjeta">
        <div class="ar-cabeza">
            <span class="ar-icono"><i class="bi bi-hourglass-split" aria-hidden="true"></i></span>
            <h3 class="ar-titulo">Por atender <span class="ar-sub">Sin formato terminado, últimos 30 días</span></h3>
        </div>
        <div class="ar-cifra">{{ number_format($casos['porAtender']['total']) }}</div>
        <div class="ar-chips">
            <span class="ar-chip">Por llegar <b>{{ $casos['porAtender']['porLlegar'] }}</b></span>
            <span class="ar-chip">En sala <b>{{ $casos['porAtender']['enSala'] }}</b></span>
            @if ($casos['porAtender']['atendidas'])
                <span class="ar-chip" title="Ya pasaron con el auxiliar, pero falta el formato">Sin formato <b>{{ $casos['porAtender']['atendidas'] }}</b></span>
            @endif
        </div>
        <a class="ar-pie" href="{{ $casos['porAtender']['url'] }}">Atender casos <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
    </div>

    {{-- 3. Citas en línea --}}
    <div class="ar-tarjeta">
        <div class="ar-cabeza">
            <span class="ar-icono"><i class="bi bi-globe2" aria-hidden="true"></i></span>
            <h3 class="ar-titulo">Citas en línea <span class="ar-sub">Con caso de excepción, hoy</span></h3>
        </div>
        <div class="ar-cifra">{{ number_format($casos['enLinea']['total']) }}</div>
        <div class="ar-chips">
            <span class="ar-chip">Próximos 7 días <b>{{ $casos['enLinea']['proximas'] }}</b></span>
        </div>
        <a class="ar-pie" href="{{ route('excepcion', ['origen' => 'linea']) }}">Ver citas en línea <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
    </div>

    {{-- 4. Solicitudes del mes --}}
    <div class="ar-tarjeta">
        <div class="ar-cabeza">
            <span class="ar-icono"><i class="bi bi-folder2-open" aria-hidden="true"></i></span>
            <h3 class="ar-titulo">Solicitudes <span class="ar-sub">Con caso de excepción este mes</span></h3>
        </div>
        <div class="ar-cifra">{{ number_format($casos['solicitudes']['total']) }}</div>
        <span class="ar-nota">Sólo consulta: se abren en modo vista.</span>
        @if (\Illuminate\Support\Facades\Route::has('seguimiento'))
            <a class="ar-chip" href="{{ route('seguimiento') }}"><i class="bi bi-signpost-2" aria-hidden="true"></i> Seguimiento de casos</a>
        @endif
        <a class="ar-pie" href="{{ $casos['solicitudes']['url'] }}">Ver solicitudes <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
    </div>
</div>
