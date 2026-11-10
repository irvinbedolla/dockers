{{--
    Cuando solicita el patrón: quién lo representa en esta audiencia, en una
    línea. Los datos completos y sus PDF están en la pestaña Documentos
    (_revisar_docs_representantes). Espera $representantes.
--}}
@php
    $repSolicitante = collect($representantes ?? [])->first(fn ($r) => collect($r['partes'])->contains(fn ($p) => str_starts_with($p, 'Solicitante')));
@endphp
<div class="col-12">
    <div class="ra-rep-resumen">
        <b>Representante legal:</b>
        @if (! $repSolicitante)
            <span class="ra-et ra-et--aviso">La audiencia no tiene un poder registrado para el patrón</span>
        @elseif ($repSolicitante['falta'])
            <span class="ra-et ra-et--falta">No se encontró el poder folio {{ $repSolicitante['folio'] }}</span>
        @else
            <span>{{ $repSolicitante['representante'] ?? $repSolicitante['otorga'].' (sin representante)' }}</span>
            <span class="ra-et ra-et--{{ $repSolicitante['estado']['nivel'] }}">{{ $repSolicitante['estado']['texto'] }}</span>
            <span class="ra-et">Folio {{ $repSolicitante['folio'] }} · vigencia {{ $repSolicitante['vigencia'] }}</span>
        @endif
        <a href="#" class="ra-ir ms-auto" data-pestana="citados">Ver datos y documentos del poder</a>
    </div>
</div>
