{{-- Dirección del citado en dos líneas: calle y número / colonia, municipio. --}}
@php
    $calle = trim(implode(' ', array_filter([$n->tipo_vialidad ?? null, $n->calle ?? null])));
    $numero = trim(($n->n_ext ? '#'.$n->n_ext : '').(! empty($n->n_int) ? ' int. '.$n->n_int : ''));
    $lugar = array_filter([
        $n->colonia ? 'Col. '.$n->colonia : null,
        $n->municipio ?? null,
        ($n->estado ?? null) && ($n->estado ?? '') !== 'Michoacán' && ($n->estado ?? '') !== 'Michoacán de Ocampo' ? $n->estado : null,
    ]);
@endphp
<div class="nt-direccion">
    {{ $calle ?: 'Sin calle' }} {{ $numero }}
    @if ($lugar)<span class="nt-direccion__lugar">{{ implode(' · ', $lugar) }}</span>@endif
</div>
