@extends('layouts.app')

<style>
    .proceso-activo-container {
        max-width: 640px;
        margin: 60px auto;
        text-align: center;
    }
    .proceso-activo-icon {
        font-size: 64px;
        color: #CEA845;
        margin-bottom: 16px;
    }
    .proceso-activo-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #333;
        margin-bottom: 8px;
    }
    .proceso-activo-subtitle {
        font-size: 0.95rem;
        color: #666;
        margin-bottom: 28px;
        line-height: 1.6;
    }
    .proceso-info-box {
        background: #fff8e1;
        border: 1px solid #ffe082;
        border-radius: 8px;
        padding: 18px 24px;
        margin-bottom: 28px;
        text-align: left;
        font-size: 0.875rem;
        color: #555;
    }
    .proceso-info-box dt {
        font-weight: 600;
        color: #333;
    }
    .proceso-info-box dd {
        margin-bottom: 6px;
        margin-left: 0;
    }
    .btn-abandonar {
        background-color: #dc3545;
        border-color: #dc3545;
        color: #fff;
        font-weight: 600;
        padding: 10px 28px;
        border-radius: 5px;
    }
    .btn-abandonar:hover {
        background-color: #c82333;
        border-color: #bd2130;
        color: #fff;
    }
    .ttl-bar-wrap {
        background: #e0e0e0;
        border-radius: 4px;
        height: 8px;
        margin-bottom: 6px;
        overflow: hidden;
    }
    .ttl-bar {
        height: 8px;
        border-radius: 4px;
        background: #CEA845;
        transition: width 1s linear;
    }
    .ttl-label {
        font-size: 0.78rem;
        color: #888;
    }
</style>

@section('content')
<section class="section">
    <div class="section-header">
        <h3 class="page__heading">Solicitud</h3>
    </div>
    <div class="section-body">

        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                {{ session('info') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif

        <div class="proceso-activo-container">

            {{-- Ícono --}}
            <div class="proceso-activo-icon">
                <i class="bi bi-lock-fill"></i>
            </div>

            <div class="proceso-activo-title">
                Ya tienes un proceso en curso
            </div>

            <p class="proceso-activo-subtitle">
                Existe una solicitud de conciliación que fue iniciada en otra ventana o pestaña del
                navegador. Para evitar mezcla de información, no puedes abrir dos procesos al mismo tiempo.
            </p>

            {{-- Información del proceso activo --}}
            <div class="proceso-info-box">
                <dl class="row mb-0">
                    @php
                        $tipos = [1 => 'Trabajador(a)', 2 => 'Patronal Individual', 3 => 'Patronal Colectiva', 4 => 'Sindicato'];
                        $tipoLabel = $tipos[$lock['tipo_solicitud'] ?? 0] ?? 'Desconocido';
                        $inicio = \Carbon\Carbon::createFromTimestamp($lock['started_at'] ?? time());
                        $ultimaAct = \Carbon\Carbon::createFromTimestamp($lock['last_activity'] ?? time());
                    @endphp
                    <dt class="col-sm-5">Tipo de solicitud:</dt>
                    <dd class="col-sm-7">{{ $tipoLabel }}</dd>

                    <dt class="col-sm-5">Iniciado:</dt>
                    <dd class="col-sm-7">{{ $inicio->format('d/m/Y H:i') }}</dd>

                    <dt class="col-sm-5">Última actividad:</dt>
                    <dd class="col-sm-7">{{ $ultimaAct->diffForHumans() }}</dd>
                </dl>
            </div>

            {{-- Barra de tiempo restante --}}
            @php
                $ttlTotal = 2700; // 45 min en segundos
                $porcentaje = min(100, round(($minutos_restantes * 60 / $ttlTotal) * 100));
            @endphp
            <p class="ttl-label mb-1">
                <strong>Expira automáticamente en: {{ $minutos_restantes }} minuto(s)</strong>
                si no hay actividad
            </p>
            <div class="ttl-bar-wrap mb-4">
                <div class="ttl-bar" id="ttlBar" style="width: {{ $porcentaje }}%"></div>
            </div>

            {{-- Opciones --}}
            <div class="d-flex flex-column flex-sm-row justify-content-center align-items-center" style="gap: 16px;">

                {{-- Abandonar proceso --}}
                <form method="POST" action="{{ route('solicitud.abandonar') }}"
                      onsubmit="return confirm('¿Estás seguro de que deseas cancelar el proceso en curso? Se perderán todos los datos capturados hasta ahora.');">
                    @csrf
                    <button type="submit" class="btn btn-abandonar">
                        <i class="bi bi-x-circle mr-1"></i>
                        Cancelar proceso y empezar de nuevo
                    </button>
                </form>

            </div>

            <p class="mt-4" style="font-size: 0.8rem; color: #aaa;">
                Si cerraste la otra pestaña accidentalmente, el proceso expirará automáticamente
                tras {{ $minutos_restantes }} minuto(s) de inactividad y podrás iniciar uno nuevo.
            </p>

        </div>
    </div>
</section>

<script>
    // Cuenta regresiva visual en la barra de TTL
    (function () {
        var totalSeg   = {{ $minutos_restantes * 60 }};
        var ttlMax     = 2700;
        var bar        = document.getElementById('ttlBar');
        var label      = document.querySelector('.ttl-label strong');
        var segundos   = totalSeg;

        function actualizar() {
            if (segundos <= 0) {
                // Expiró: recargar para que el servidor limpie y muestre pantalla nueva
                location.reload();
                return;
            }
            var pct = Math.round((segundos / ttlMax) * 100);
            bar.style.width = pct + '%';
            var min = Math.ceil(segundos / 60);
            label.textContent = 'Expira automáticamente en: ' + min + ' minuto(s)';
            segundos--;
        }

        actualizar();
        setInterval(actualizar, 1000);
    })();
</script>
@endsection
