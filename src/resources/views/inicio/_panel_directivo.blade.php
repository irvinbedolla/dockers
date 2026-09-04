{{--
    Panel del rol Directivo: sin calendario. Muestra totales del sistema y su
    desglose por sede. $resumen viene de InicioController::resumenDirectivo(),
    con la forma ['solicitudes'|'audiencias'|'ratificaciones' => ['total' => int, 'porSede' => [sede => int]]].
--}}
<style>
    .panel-directivo .tile {
        background: #fff;
        border: 1px solid #E3E8E8;
        border-radius: 12px;
        padding: 20px 22px;
        height: 100%;
    }

    .panel-directivo .tile__label {
        font-size: 13px;
        color: #7B8A8B;
        margin: 0 0 6px;
    }

    .panel-directivo .tile__value {
        font-size: 32px;
        font-weight: 600;
        color: #496163;
        line-height: 1;
    }

    .panel-directivo .chart-card {
        background: #fff;
        border: 1px solid #E3E8E8;
        border-radius: 12px;
        padding: 18px 20px;
        height: 100%;
    }

    .panel-directivo .chart-card h6 {
        font-size: 13.5px;
        font-weight: 600;
        color: #496163;
        text-align: center;
        margin: 0 0 12px;
    }

    .panel-directivo .chart-card__canvas-wrap {
        position: relative;
        height: 220px;
    }

    .panel-directivo .chart-card table {
        margin: 14px 0 0;
        font-size: 12.5px;
    }

    .panel-directivo .chart-card table td {
        padding: 3px 4px;
        color: #52514e;
    }

    .panel-directivo .chart-card .swatch {
        display: inline-block;
        width: 10px;
        height: 10px;
        border-radius: 2px;
        margin-right: 6px;
    }
</style>

<div class="panel-directivo mt-3">
    <div class="row g-3">
        <div class="col-md-4">
            <div class="tile">
                <p class="tile__label">Solicitudes totales</p>
                <div class="tile__value">{{ number_format($resumen['solicitudes']['total']) }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="tile">
                <p class="tile__label">Audiencias</p>
                <div class="tile__value">{{ number_format($resumen['audiencias']['total']) }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="tile">
                <p class="tile__label">Ratificaciones</p>
                <div class="tile__value">{{ number_format($resumen['ratificaciones']['total']) }}</div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        @foreach ([
            ['id' => 'solicitudes',    'titulo' => 'Solicitudes por sede'],
            ['id' => 'audiencias',     'titulo' => 'Audiencias por sede'],
            ['id' => 'ratificaciones', 'titulo' => 'Ratificaciones por sede'],
        ] as $grafica)
            <div class="col-md-4">
                <div class="chart-card">
                    <h6>{{ $grafica['titulo'] }}</h6>
                    <div class="chart-card__canvas-wrap">
                        <canvas id="grafica-{{ $grafica['id'] }}-sede"></canvas>
                    </div>
                    <table id="tabla-{{ $grafica['id'] }}-sede" class="table-borderless w-100"></table>
                </div>
            </div>
        @endforeach
    </div>
</div>
