{{-- Aviso de cambio de módulo con el acuse actualizado. Datos: $d y $moduloAnterior. --}}
@extends('emails.layouts.ccl')

@section('titulo', 'Tu cita cambió de módulo · Folio '.$d['folio'])
@section('preheader', 'Tu cita sigue el '.$d['fechaLarga'].' a las '.$d['hora'].' h. Sólo cambió el módulo: ahora es '.$d['modulo'].'.')
@section('hero_titulo', 'Tu cita cambió de módulo')
@section('hero_texto')
    Hola, {{ $d['nombre'] }}. Tu cita <strong style="color:#FFFFFF;">sigue el mismo día y a la misma hora</strong>; sólo cambió el módulo donde te atenderán.
@endsection

@section('contenido')
    @include('emails.partials._cita_detalle', ['moduloAnterior' => $moduloAnterior])
    <tr><td style="padding:0 0 8px; font-size:0; line-height:0;">&nbsp;</td></tr>
@endsection
