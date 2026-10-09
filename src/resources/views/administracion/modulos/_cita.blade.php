{{-- Una cita dentro de la tarjeta de su módulo. Se abre para ver los datos. --}}
@php
    $estatus  = strtolower((string) $cita->estatus);
    $enLinea  = $cita->origen === 'linea';
    $movible  = $cita->exepcion !== 'Si' && in_array($estatus, ['pendiente', 'confirmada'], true) && $cita->fecha >= $hoy;
    $folio    = str_pad($cita->consecutivo, 5, '0', STR_PAD_LEFT);
    $hora     = substr($cita->hora, 0, 5);
@endphp
<li class="md-cita">
    <details>
        <summary>
            <span class="md-hora">{{ $hora }}</span>
            <span class="md-quien">
                <span class="md-quien__nombre">{{ $cita->solicitante ?: 'Sin nombre' }}</span>
                <span class="md-quien__detalle">
                    {{ $folio }} · {{ $cita->tipo }}
                    @if ($cita->exepcion === 'Si')
                        <span class="tn-origen tn-origen--excepcion">Excepción</span>
                    @elseif ($enLinea)
                        <span class="tn-origen tn-origen--linea">En línea</span>
                    @else
                        <span class="tn-origen tn-origen--ventanilla">Ventanilla</span>
                    @endif
                    @if (! empty($mostrarLugar) && $cita->lugar_auxiliar)· {{ $cita->lugar_auxiliar }}@endif
                </span>
            </span>
            <span class="tn-estatus tn-estatus--{{ $estatus }}">{{ $estatus }}</span>
        </summary>

        <div class="md-datos">
            <dl>
                <dt>Correo</dt><dd>{{ $cita->correo ?: '—' }}</dd>
                <dt>Teléfono</dt><dd>{{ $cita->telefono ?: '—' }}</dd>
                <dt>Edad</dt><dd>{{ $cita->edad ?: '—' }}</dd>
                <dt>Sexo</dt><dd>{{ $sexos[$cita->sexo] ?? ($cita->sexo ?: '—') }}</dd>
                <dt>Municipio</dt><dd>{{ $cita->municipio ?: '—' }}</dd>
                <dt>Grupo vulnerable</dt><dd>{{ $cita->vulnerables ?: '—' }}</dd>
                <dt>Caso</dt><dd>{{ $cita->tipo_caso ?: '—' }}</dd>
                @if ($cita->observaciones || $cita->conflicto)
                    <dt>Motivo</dt><dd>{{ $cita->observaciones ?: $cita->conflicto }}</dd>
                @endif
                <dt>Atiende</dt><dd>{{ $nombreBonito($cita->atiende) ?? 'Sin asignar' }}</dd>
                <dt>Horario</dt><dd>{{ $hora }}@if ($cita->hora_fin) a {{ substr($cita->hora_fin, 0, 5) }}@endif</dd>
                <dt>Registrada</dt><dd>{{ $cita->created_at ? \Illuminate\Support\Carbon::parse($cita->created_at)->format('d/m/Y H:i') : '—' }}</dd>
            </dl>
            <div class="md-acciones">
                <a class="btn btn-sm btn-outline-secondary" href="{{ route('citas.documento', $cita->id) }}" target="_blank" rel="noopener">
                    <i class="bi bi-file-pdf"></i> Acuse
                </a>
                @if ($movible)
                    <button type="button" class="btn btn-sm text-white" style="background: var(--tn-verde);"
                            data-bs-toggle="modal" data-bs-target="#mdModalReasignar"
                            data-cita="{{ $cita->id }}" data-tipo="{{ $cita->tipo }}" data-linea="{{ $enLinea ? 1 : 0 }}"
                            data-resumen="{{ $folio }} · {{ $cita->solicitante }} · {{ $cita->tipo }} a las {{ $hora }}">
                        <i class="bi bi-arrow-left-right"></i> Reasignar módulo
                    </button>
                @endif
            </div>
        </div>
    </details>
</li>
