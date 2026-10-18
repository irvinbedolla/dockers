@extends('layouts.app')
@section('title', 'Solicitud '.($general->NUE ?: ''))
{{--
    Solicitud con caso de excepción en modo consulta
    (CasosExcepcionController::solicitud). No tiene ningún formulario: el
    Departamento de Conciliación, Género e Igualdad la ve, no la edita.
--}}
@php
    $titulo = fn ($t) => \Illuminate\Support\Str::title(mb_strtolower(trim((string) $t)));
    $fecha  = fn ($f) => $f ? \Illuminate\Support\Carbon::parse($f)->locale('es')->isoFormat('D [de] MMMM YYYY') : '—';
    $dato   = fn ($v) => ($v === null || $v === '') ? '—' : $v;
    $sexo   = ['H' => 'Hombre', 'M' => 'Mujer', 'NB' => 'No binario'];
@endphp

@section('page_css')
    @include('turnos._estilos')
    <style>
        .sx-migas { font-size: 12.5px; color: var(--tn-tenue); text-decoration: none; }
        .sx-migas:hover { color: var(--tn-verde); }
        .sx-aviso { display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 999px; background: #EEF2F2; color: var(--tn-suave); font-size: 12.5px; font-weight: 700; }
        .sx-caso { display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 999px; background: #F6E4EA; color: #8C1D40; font-size: 12.5px; font-weight: 700; }
        .sx-bloque { margin-bottom: 16px; }
        .sx-bloque h4 { font-size: 15px; font-weight: 800; color: var(--tn-tinta); margin: 0 0 12px; display: flex; align-items: center; gap: 8px; }
        .sx-datos { display: grid; grid-template-columns: repeat(auto-fill, minmax(210px, 1fr)); gap: 12px 18px; margin: 0; }
        .sx-datos dt { font-size: 11.5px; font-weight: 700; letter-spacing: .03em; color: var(--tn-tenue); text-transform: uppercase; }
        .sx-datos dd { margin: 2px 0 0; font-size: 14px; color: var(--tn-tinta); overflow-wrap: anywhere; }
        .sx-texto { white-space: pre-line; font-size: 14px; color: var(--tn-tinta); background: var(--tn-fondo); border-radius: 10px; padding: 12px 14px; margin: 12px 0 0; }
        .sx-persona + .sx-persona { margin-top: 16px; padding-top: 16px; border-top: 1px dashed var(--tn-borde); }
        .sx-persona h5 { font-size: 14.5px; font-weight: 800; color: var(--tn-tinta); margin: 0 0 10px; }
        .sx-tabla th { font-size: 12px; color: var(--tn-suave); }
        .sx-tabla td { font-size: 13.5px; }
    </style>
@endsection

@section('content')
    <section class="section tn">
        <div class="section-header d-flex justify-content-between align-items-end flex-wrap gap-2 mb-3">
            <div>
                <a class="sx-migas" href="{{ url()->previous() !== url()->current() ? url()->previous() : route('excepcion', ['vista' => 'solicitudes']) }}"><i class="bi bi-arrow-left"></i> Casos de excepción</a>
                <h3 class="page__heading mb-1">Solicitud {{ $general->NUE ?: 'sin expediente' }}</h3>
                <span class="sx-caso"><i class="bi bi-shield-exclamation"></i> Caso de excepción</span>
                <span class="sx-aviso"><i class="bi bi-eye"></i> Sólo consulta</span>
            </div>
        </div>

        <div class="section-body">
            <div class="tn-tarjeta sx-bloque">
                <h4><i class="bi bi-folder2-open"></i> Datos de la solicitud</h4>
                <dl class="sx-datos">
                    <div><dt>Expediente</dt><dd>{{ $dato($general->NUE) }}</dd></div>
                    <div><dt>Fecha de solicitud</dt><dd>{{ $fecha($general->fecha) }}</dd></div>
                    <div><dt>Fecha del conflicto</dt><dd>{{ $fecha($general->fecha_conflicto) }}</dd></div>
                    <div><dt>Sede</dt><dd>{{ $dato($general->delegacion) }}</dd></div>
                    <div><dt>Estatus</dt><dd>{{ $dato($general->estatus) }}</dd></div>
                    <div><dt>Modalidad</dt><dd>{{ $dato($general->tipo) }}</dd></div>
                    <div><dt>Conciliador</dt><dd>{{ $conciliador ? $titulo($conciliador) : '—' }}</dd></div>
                    @if ($cita)
                        <div><dt>Cita de origen</dt><dd>{{ \App\Support\FolioCita::mostrar($cita) }} · {{ $fecha($cita->fecha) }}</dd></div>
                        <div><dt>Tipo de caso (cita)</dt><dd>{{ $dato($cita->tipo_caso) }}</dd></div>
                    @endif
                </dl>
                @if ($general->observaciones)
                    <p class="sx-texto">{{ $general->observaciones }}</p>
                @endif
            </div>

            <div class="tn-tarjeta sx-bloque">
                <h4><i class="bi bi-person"></i> {{ $solicitantes->count() > 1 ? 'Personas solicitantes' : 'Persona solicitante' }}</h4>
                @forelse ($solicitantes as $s)
                    <div class="sx-persona">
                        <h5>{{ $titulo($s->nombre) }}</h5>
                        <dl class="sx-datos">
                            <div><dt>CURP</dt><dd>{{ $dato($s->curp) }}</dd></div>
                            <div><dt>Sexo</dt><dd>{{ $sexo[$s->sexo] ?? $dato($s->sexo) }}</dd></div>
                            <div><dt>Edad</dt><dd>{{ $dato($s->edad) }}</dd></div>
                            <div><dt>Teléfono</dt><dd>{{ $dato($s->telefono1) }}{{ $s->telefono2 ? ' · '.$s->telefono2 : '' }}</dd></div>
                            <div><dt>Correo</dt><dd>{{ $dato($s->email) }}</dd></div>
                            <div><dt>Domicilio</dt><dd>{{ trim(implode(' ', array_filter([$s->tipo_vialidad, $s->calle, $s->num_ext, $s->num_int ? 'int. '.$s->num_int : null]))) ?: '—' }}{{ $s->colonia ? ', '.$s->colonia : '' }}{{ $s->municipio ? ', '.$s->municipio : '' }}</dd></div>
                            <div><dt>Puesto</dt><dd>{{ $dato($s->puesto) }}</dd></div>
                            <div><dt>Ingreso</dt><dd>{{ $fecha($s->fecha_ingreso) }}</dd></div>
                            <div><dt>Salida</dt><dd>{{ $fecha($s->fecha_salida) }}</dd></div>
                            <div><dt>Discapacidad</dt><dd>{{ $s->discapacidad === 'Si' ? ($s->tipo_discapacidad ?: 'Sí') : 'No' }}</dd></div>
                        </dl>
                        @if ($s->descripcionSolicitud)
                            <p class="sx-texto">{{ $s->descripcionSolicitud }}</p>
                        @endif
                    </div>
                @empty
                    <p class="text-muted mb-0">Sin solicitante capturado.</p>
                @endforelse
            </div>

            <div class="tn-tarjeta sx-bloque">
                <h4><i class="bi bi-building"></i> {{ $citados->count() > 1 ? 'Citados' : 'Citado' }}</h4>
                @forelse ($citados as $c)
                    <div class="sx-persona">
                        <h5>{{ trim($c->nombre.' '.$c->primer_apellido.' '.$c->segundo_apellido) }}</h5>
                        <dl class="sx-datos">
                            <div><dt>Tipo de persona</dt><dd>{{ $dato($c->tipo_persona) }}</dd></div>
                            <div><dt>RFC</dt><dd>{{ $dato($c->rfc) }}</dd></div>
                            <div><dt>Domicilio</dt><dd>{{ trim(implode(' ', array_filter([$c->tipo_vialidad, $c->calle, $c->n_ext, $c->n_int ? 'int. '.$c->n_int : null]))) ?: '—' }}{{ $c->colonia ? ', '.$c->colonia : '' }}{{ $c->municipio ? ', '.$c->municipio : '' }}</dd></div>
                            <div><dt>Notificación</dt><dd>{{ $dato($c->estatus) }}</dd></div>
                        </dl>
                    </div>
                @empty
                    <p class="text-muted mb-0">Sin citados capturados.</p>
                @endforelse
            </div>

            <div class="tn-tarjeta sx-bloque">
                <h4><i class="bi bi-calendar-event"></i> Audiencias</h4>
                @if ($audiencias->isEmpty())
                    <p class="text-muted mb-0">Todavía no tiene audiencias.</p>
                @else
                    <div class="table-responsive">
                        <table class="table sx-tabla mb-0">
                            <thead><tr><th>#</th><th>Fecha</th><th>Hora</th><th>Sala</th><th>Conciliador</th><th>Estatus</th></tr></thead>
                            <tbody>
                                @foreach ($audiencias as $a)
                                    <tr>
                                        <td>{{ $a->numero_audiencia ?: $loop->iteration }}</td>
                                        <td>{{ $fecha($a->fecha) }}</td>
                                        <td>{{ substr((string) $a->hora, 0, 5) }}</td>
                                        <td>{{ $dato($a->sala) }}</td>
                                        <td>{{ $a->conciliador ? $titulo($a->conciliador) : '—' }}</td>
                                        <td>{{ $dato($a->estatus) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
