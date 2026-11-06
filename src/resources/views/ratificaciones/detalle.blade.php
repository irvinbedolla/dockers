@extends('layouts.app')
@section('title', 'Ratificación ' . ($folio->NUE ?: $folio->id))

@php
    $txt = fn ($valor) => filled($valor) ? trim(preg_replace('/\s+/', ' ', (string) $valor)) : null;
    $nombre = fn (...$partes) => $txt(implode(' ', array_filter($partes, 'filled')));
    $fecha = function ($valor) {
        if (blank($valor) || str_starts_with((string) $valor, '0000')) { return null; }
        try { return \Carbon\Carbon::parse($valor)->format('d/m/Y'); } catch (\Throwable $e) { return (string) $valor; }
    };
    $hora = function ($valor) {
        if (blank($valor)) { return null; }
        try { return \Carbon\Carbon::parse($valor)->format('H:i'); } catch (\Throwable $e) { return (string) $valor; }
    };
    $dinero = fn ($valor) => filled($valor) ? '$' . number_format((float) $valor, 2) : null;

    $rep = $representante;
    $empleador = $rep
        ? $nombre($rep->nombres_patronal, $rep->primer_apellido_patronal, $rep->segundo_apellido_patronal)
        : $nombre($folio->empresa, $folio->primero_empresa, $folio->segundo_empresa);
    $representanteLegal = $rep && $rep->reprecentante === 'Si'
        ? $nombre($rep->nombre_representante, $rep->primer_apellido_representante, $rep->segundo_apellido_representante)
        : null;

    $datosEmpleador = [
        ['Nombre o razón social', $empleador],
        ['Tipo de persona',       $rep ? $txt($rep->tipo) : null],
        ['RFC',                   $rep ? $txt($rep->rfc_patronal) : null],
        ['CURP',                  $rep ? $txt($rep->curp_patronal) : $txt($folio->curp_solicitante)],
        ['Correo',                $rep ? $txt($rep->email_patronal) : null],
        ['Teléfono',              $rep ? $txt($rep->telefono_patronal) : null],
        ['Representante legal',   $representanteLegal],
    ];

    $sexos = ['H' => 'Hombre', 'M' => 'Mujer'];
    $datosTrabajador = [
        ['Nombre',          $nombre($folio->trabajador, $folio->primero_trabajador, $folio->segundo_trabajador)],
        ['Edad',            filled($folio->edad) ? $folio->edad . ' años' : null],
        ['Sexo',            $sexos[$folio->sexo] ?? $txt($folio->sexo)],
        ['CURP',            $txt($folio->trabajador_curp)],
        ['Correo',          $txt($folio->email)],
        ['Teléfono',        $txt($folio->telefono)],
        ['Identificación',  $txt($folio->tipo_identificacion)],
    ];

    $datosRelacion = [
        ['Inicio de la relación',  $fecha($folio->fecha_inicio)],
        ['Término de la relación', $fecha($folio->fecha_termino)],
        ['Categoría o puesto',     $txt($folio->categoria)],
        ['Frecuencia de pago',     $txt($folio->frecuencia)],
        ['Salario',                $dinero($folio->salario)],
        ['Días trabajados por semana', $txt($folio->dias)],
    ];

    $prestaciones = collect([
        'Aguinaldo'        => 'Aguinaldo',
        'Vacaciones'       => 'Vacaciones',
        'PrimaVacacional'  => 'Prima vacacional',
        'PagoPTU'          => 'Pago de PTU',
        'Gratificación'    => 'Gratificación',
        'PrimaAntigüedad'  => 'Prima de antigüedad',
        'Otras'            => 'Otras',
    ])->filter(fn ($texto, $columna) => (int) $folio->{$columna} === 1)->values();
    if (filled($folio->Especifique) && $prestaciones->contains('Otras')) {
        $prestaciones = $prestaciones->map(fn ($p) => $p === 'Otras' ? 'Otras: ' . $folio->Especifique : $p);
    }

    $documentos = [
        ['INE',                              $folio->ine,                     $tipo_doc_abogado, $id_doc_abogado],
        ['Documento que acredita la representación', $folio->representacion,  $tipo_doc_abogado, $id_doc_abogado],
        ['CURP',                             $folio->documentoCurp,           'ratificacion',    $folio->id],
        ['Identificación',                   $folio->documentoidentificacion, 'ratificacion',    $folio->id],
    ];

    $regreso = url()->previous() !== url()->current() ? url()->previous() : route('agenda');
@endphp

@section('content')
@include('turnos._estilos')
<style>
    .rd { max-width: 1120px; margin: 0 auto; }

    .rd-cabeza { display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 12px 20px; margin-bottom: 18px; }
    .rd-regreso { display: inline-flex; align-items: center; gap: 6px; font-size: 13px; color: var(--tn-verde-claro); text-decoration: none; }
    .rd-regreso:hover { color: var(--tn-verde); text-decoration: none; }
    .rd-cabeza h3 { margin: 4px 0 0; font-size: 24px; font-weight: 800; color: var(--tn-tinta); }
    .rd-cabeza__sub { margin: 4px 0 0; font-size: 13.5px; color: var(--tn-tenue); }

    /* Estatus con el color del semáforo de la agenda (SemaforoAgenda). */
    .rd-estatus {
        display: inline-flex; align-items: center; gap: 7px; padding: 4px 12px; border-radius: 999px;
        font-size: 12.5px; font-weight: 700; white-space: nowrap;
        color: var(--c); background: color-mix(in srgb, var(--c) 13%, #fff);
    }
    .rd-estatus::before { content: ""; width: 8px; height: 8px; border-radius: 50%; background: currentColor; }
    .rd-estatus--chico { font-size: 11.5px; padding: 2px 10px; }

    /* Franja con lo que se busca primero: cuándo, dónde y con quién. */
    .rd-franja { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 1px; background: var(--tn-borde); border: 1px solid var(--tn-borde); border-radius: 12px; overflow: hidden; margin-bottom: 16px; }
    .rd-franja__dato { background: #fff; padding: 14px 18px; display: flex; gap: 12px; align-items: center; min-width: 0; }
    .rd-franja__dato .bi { font-size: 18px; color: var(--tn-verde); flex: 0 0 auto; }
    .rd-franja__dato small { display: block; font-size: 11.5px; letter-spacing: .04em; text-transform: uppercase; color: var(--tn-tenue); font-weight: 700; }
    .rd-franja__dato b { display: block; font-size: 15px; color: var(--tn-tinta); overflow-wrap: anywhere; }

    .rd-rejilla { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; margin-bottom: 16px; }
    .rd-rejilla > .tn-tarjeta { margin: 0; }
    .rd-ancho { grid-column: 1 / -1; }
    @media (max-width: 860px) { .rd-rejilla { grid-template-columns: 1fr; } }

    .rd .tn-titulo-seccion { display: flex; align-items: center; gap: 8px; }
    .rd .tn-titulo-seccion .bi { font-size: 14px; color: var(--tn-verde-claro); }
    .rd-titulo-extra { margin-left: auto; text-transform: none; letter-spacing: 0; font-weight: 600; }

    /* Pares etiqueta / valor */
    .rd-datos { display: grid; grid-template-columns: repeat(auto-fill, minmax(210px, 1fr)); gap: 14px 22px; margin: 0; }
    .rd-datos div { min-width: 0; }
    .rd-datos dt { font-size: 12px; font-weight: 600; color: var(--tn-tenue); margin: 0 0 2px; }
    .rd-datos dd { font-size: 14.5px; color: var(--tn-tinta); margin: 0; overflow-wrap: anywhere; }
    .rd-vacio { color: #B3BEBF; }

    .rd-aviso { display: flex; gap: 10px; align-items: flex-start; padding: 10px 14px; margin-bottom: 14px; border-radius: 10px; background: #FBF4E1; color: #6B5210; font-size: 13px; }
    .rd-aviso .bi { color: #CEA845; font-size: 16px; }

    .rd-fichas { display: flex; flex-wrap: wrap; gap: 6px; }
    .rd-ficha { padding: 3px 10px; border-radius: 999px; background: #EEF2F2; color: var(--tn-verde); font-size: 12.5px; font-weight: 600; }

    .rd-docs { display: grid; grid-template-columns: repeat(auto-fill, minmax(230px, 1fr)); gap: 10px; }
    .rd-doc { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border: 1px solid var(--tn-borde); border-radius: 10px; font-size: 13.5px; color: var(--tn-tinta); text-decoration: none; min-width: 0; }
    .rd-doc .bi { font-size: 20px; flex: 0 0 auto; }
    a.rd-doc .bi { color: #B42318; }
    a.rd-doc:hover { border-color: var(--tn-verde-claro); background: var(--tn-fondo); color: var(--tn-tinta); text-decoration: none; }
    span.rd-doc { color: var(--tn-tenue); background: var(--tn-fondo); border-style: dashed; }
    span.rd-doc .bi { color: #B3BEBF; }
    .rd-doc em { margin-left: auto; font-style: normal; font-size: 12px; color: var(--tn-verde-claro); }

    /* Cumplimientos */
    .rd-totales { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 10px; margin-bottom: 14px; }
    .rd-total { padding: 12px 14px; border-radius: 10px; background: var(--tn-fondo); }
    .rd-total small { display: block; font-size: 12px; color: var(--tn-tenue); font-weight: 600; }
    .rd-total b { font-size: 18px; color: var(--tn-tinta); }
    .rd-total--pagado b { color: #1E7B3C; }
    .rd-total--pendiente b { color: #8A6A12; }

    .rd-tabla { width: 100%; border-collapse: collapse; font-size: 13.5px; }
    .rd-tabla th { font-size: 11.5px; letter-spacing: .04em; text-transform: uppercase; color: var(--tn-tenue); font-weight: 700; padding: 8px 10px; border-bottom: 1px solid var(--tn-borde); text-align: left; white-space: nowrap; }
    .rd-tabla td { padding: 11px 10px; border-bottom: 1px solid var(--tn-borde); color: var(--tn-tinta); vertical-align: top; }
    .rd-tabla tr:last-child td { border-bottom: 0; }
    .rd-tabla .rd-num { text-align: right; white-space: nowrap; font-variant-numeric: tabular-nums; }
    .rd-tabla .rd-idx { color: var(--tn-tenue); font-weight: 700; width: 1%; }
    .rd-tabla__nota { display: block; margin-top: 3px; font-size: 12px; color: var(--tn-tenue); }
    .rd-sin { text-align: center; padding: 22px 10px; color: var(--tn-tenue); font-size: 13.5px; }

    /* En celular la tabla pasa a tarjetas apiladas. */
    @media (max-width: 720px) {
        .rd-tabla thead { display: none; }
        .rd-tabla, .rd-tabla tbody, .rd-tabla tr, .rd-tabla td { display: block; width: 100%; }
        .rd-tabla tr { padding: 10px 0; border-bottom: 1px solid var(--tn-borde); }
        .rd-tabla tr:last-child { border-bottom: 0; }
        .rd-tabla td { border: 0; padding: 3px 0; text-align: left !important; }
        .rd-tabla td[data-k]::before { content: attr(data-k) ": "; color: var(--tn-tenue); font-size: 12px; font-weight: 600; }
    }

    .rd-observaciones { margin: 0; font-size: 14px; color: var(--tn-tinta); white-space: pre-line; }
</style>

<section class="section">
    <div class="section-body">
        <div class="rd tn">

            <div class="rd-cabeza">
                <div>
                    <a class="rd-regreso" href="{{ $regreso }}"><i class="bi bi-arrow-left"></i> Regresar</a>
                    <h3>Ratificación {{ $folio->NUE ?: 'sin expediente' }}</h3>
                    <p class="rd-cabeza__sub">Folio de solicitud {{ $folio->id }}{{ $empleador ? ' · ' . $empleador : '' }}</p>
                </div>
                @if (filled($folio->estatus))
                    <span class="rd-estatus" style="--c: {{ \App\Support\SemaforoAgenda::ratificacion($folio->estatus) }}">{{ $folio->estatus }}</span>
                @endif
            </div>

            <div class="rd-franja">
                <div class="rd-franja__dato"><i class="bi bi-calendar-event"></i><div><small>Fecha</small><b>{{ $fecha($folio->fecha) ?? '—' }}</b></div></div>
                <div class="rd-franja__dato"><i class="bi bi-clock"></i><div><small>Hora</small><b>{{ $hora($folio->hora) ?? '—' }}</b></div></div>
                <div class="rd-franja__dato"><i class="bi bi-geo-alt"></i><div><small>Sede</small><b>{{ $folio->delegacion ?: '—' }}</b></div></div>
                <div class="rd-franja__dato"><i class="bi bi-person-badge"></i><div><small>Conciliador</small><b>{{ $conciliador ?: 'Sin asignar' }}</b></div></div>
            </div>

            <div class="rd-rejilla">
                <div class="tn-tarjeta">
                    <h4 class="tn-titulo-seccion"><i class="bi bi-building"></i> Empleador(a)
                        @if ($rep)
                            <span class="rd-titulo-extra">
                                Registro patronal {{ $rep->idAbogado }}
                                <span class="rd-estatus rd-estatus--chico" style="--c: {{ $rep->estatus === 'Validado' ? '#1E7B3C' : '#8A6A12' }}">{{ $rep->estatus }}</span>
                            </span>
                        @endif
                    </h4>
                    @unless ($rep)
                        <div class="rd-aviso"><i class="bi bi-exclamation-circle"></i><span>No hay registro patronal ligado; se muestran los datos capturados en la ratificación.</span></div>
                    @endunless
                    <dl class="rd-datos">
                        @foreach ($datosEmpleador as [$etiqueta, $valor])
                            @continue($etiqueta === 'Representante legal' && ! $valor)
                            <div><dt>{{ $etiqueta }}</dt><dd>{!! $valor !== null ? e($valor) : '<span class="rd-vacio">—</span>' !!}</dd></div>
                        @endforeach
                        @if ($rep && $rep->fechaVigencia)
                            <div><dt>Vigencia del poder</dt><dd>{{ $fecha($rep->fechaVigencia) }}</dd></div>
                        @endif
                    </dl>
                </div>

                <div class="tn-tarjeta">
                    <h4 class="tn-titulo-seccion"><i class="bi bi-person"></i> Trabajador(a)</h4>
                    <dl class="rd-datos">
                        @foreach ($datosTrabajador as [$etiqueta, $valor])
                            <div><dt>{{ $etiqueta }}</dt><dd>{!! $valor !== null ? e($valor) : '<span class="rd-vacio">—</span>' !!}</dd></div>
                        @endforeach
                    </dl>
                </div>

                <div class="tn-tarjeta rd-ancho">
                    <h4 class="tn-titulo-seccion"><i class="bi bi-briefcase"></i> Relación laboral y convenio</h4>
                    <dl class="rd-datos">
                        @foreach ($datosRelacion as [$etiqueta, $valor])
                            <div><dt>{{ $etiqueta }}</dt><dd>{!! $valor !== null ? e($valor) : '<span class="rd-vacio">—</span>' !!}</dd></div>
                        @endforeach
                        <div><dt>Motivo de la conciliación</dt><dd>{!! filled($folio->motivo) ? e($folio->motivo) : '<span class="rd-vacio">—</span>' !!}</dd></div>
                        <div><dt>Monto total del convenio</dt><dd><b>{!! $dinero($folio->monto) ? e($dinero($folio->monto)) : '<span class="rd-vacio">—</span>' !!}</b></dd></div>
                        <div><dt>Tipo de pago</dt><dd>{!! filled($folio->tipo_pago) ? e($folio->tipo_pago) : '<span class="rd-vacio">—</span>' !!}</dd></div>
                        @if ($prestaciones->isNotEmpty())
                            <div class="rd-ancho">
                                <dt>Prestaciones</dt>
                                <dd class="rd-fichas">
                                    @foreach ($prestaciones as $p)<span class="rd-ficha">{{ $p }}</span>@endforeach
                                </dd>
                            </div>
                        @endif
                    </dl>
                </div>

                <div class="tn-tarjeta rd-ancho">
                    <h4 class="tn-titulo-seccion">
                        <i class="bi bi-cash-coin"></i> Cumplimientos
                        <span class="rd-titulo-extra">{{ $cumplimientos->count() }} {{ $cumplimientos->count() === 1 ? 'registro' : 'registros' }}</span>
                    </h4>

                    @if ($cumplimientos->isEmpty())
                        <div class="rd-sin"><i class="bi bi-inbox"></i> Esta ratificación no tiene cumplimientos registrados.</div>
                    @else
                        <div class="rd-totales">
                            <div class="rd-total"><small>Total programado</small><b>{{ $dinero($resumen['total']) }}</b></div>
                            <div class="rd-total rd-total--pagado"><small>Pagado</small><b>{{ $dinero($resumen['pagado']) }}</b></div>
                            <div class="rd-total rd-total--pendiente">
                                <small>Por pagar ({{ $resumen['pendientes'] }} {{ $resumen['pendientes'] === 1 ? 'pendiente' : 'pendientes' }})</small>
                                <b>{{ $dinero($resumen['pendiente']) }}</b>
                            </div>
                        </div>

                        <table class="rd-tabla">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Fecha</th>
                                    <th>Descripción</th>
                                    <th>Forma de pago</th>
                                    <th class="rd-num">Monto</th>
                                    <th>Estatus</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($cumplimientos as $pago)
                                    <tr>
                                        <td class="rd-idx">{{ $loop->iteration }}</td>
                                        <td data-k="Fecha">
                                            {{ $fecha($pago->fecha) ?? '—' }}
                                            @if ($hora($pago->hora))<span class="rd-tabla__nota">{{ $hora($pago->hora) }} h</span>@endif
                                        </td>
                                        <td data-k="Descripción">
                                            {{ $pago->descripcion ?: '—' }}
                                            @if (filled($pago->observaciones))<span class="rd-tabla__nota">{{ $pago->observaciones }}</span>@endif
                                        </td>
                                        <td data-k="Forma de pago">{{ $pago->forma_pago ?: '—' }}</td>
                                        <td data-k="Monto" class="rd-num">
                                            {{ $dinero($pago->monto) }}
                                            @if ((float) $pago->monto_pc > 0)<span class="rd-tabla__nota">+ {{ $dinero($pago->monto_pc) }} pena conv.</span>@endif
                                        </td>
                                        <td data-k="Estatus">
                                            <span class="rd-estatus rd-estatus--chico" style="--c: {{ \App\Support\SemaforoAgenda::cumplimiento($pago->estatus) }}">{{ $pago->estatus }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>

                <div class="tn-tarjeta {{ filled($folio->observaciones) ? '' : 'rd-ancho' }}">
                    <h4 class="tn-titulo-seccion"><i class="bi bi-paperclip"></i> Documentos</h4>
                    <div class="rd-docs">
                        @foreach ($documentos as [$etiqueta, $archivo, $tipoDoc, $idDoc])
                            @if (filled($archivo))
                                <a class="rd-doc" target="_blank" rel="noopener"
                                   href="{{ signedDocRoute('documentos.ver', ['tipo' => $tipoDoc, 'id' => $idDoc, 'archivo' => $archivo]) }}">
                                    <i class="bi bi-file-earmark-pdf"></i><span>{{ $etiqueta }}</span><em>Ver</em>
                                </a>
                            @else
                                <span class="rd-doc"><i class="bi bi-file-earmark"></i><span>{{ $etiqueta }}</span><em>No cargado</em></span>
                            @endif
                        @endforeach
                    </div>
                </div>

                @if (filled($folio->observaciones))
                    <div class="tn-tarjeta">
                        <h4 class="tn-titulo-seccion"><i class="bi bi-chat-left-text"></i> Observaciones</h4>
                        <p class="rd-observaciones">{{ $folio->observaciones }}</p>
                    </div>
                @endif
            </div>

        </div>
    </div>
</section>
@endsection
