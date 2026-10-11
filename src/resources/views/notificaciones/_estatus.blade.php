{{-- Chip de estatus de un citado. Uso: @include('notificaciones._estatus', ['estatus' => $x]) --}}
@php
    $color = match ($estatus) {
        'Sin asignar'                                                   => 'gris',
        'Pendiente'                                                     => 'ambar',
        'Finalizado exitosamente', 'Exitosa por Instructivo',
        'Notificada', 'Recibe pero no firma'                            => 'verde',
        'Notificada en Audiencia', 'Exhorto'                            => 'azul',
        'No exitosa se constituye', 'No exitosa no se constituye',
        'No notificada'                                                 => 'rojo',
        default                                                         => 'gris',
    };
@endphp
<span class="nt-estatus nt-estatus--{{ $color }}">{{ $estatus ?: 'Sin estatus' }}</span>
