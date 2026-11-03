{{-- Confirmación de cita en línea. Datos: $d (App\Support\CitaCorreo::datos). --}}
@extends('emails.layouts.ccl')

@section('titulo', 'Tu cita está agendada · Folio '.$d['folio'])
@section('preheader', $d['tipo'].' · '.$d['fechaLarga'].' a las '.$d['hora'].' h en '.$d['sede'].'. Tu acuse con código QR va adjunto.')
@section('hero_titulo', '¡Tu cita quedó agendada!')
@section('hero_texto')
    Hola, {{ $d['nombre'] }}. Registramos tu cita de <strong style="color:#FFFFFF;">{{ $d['tipoCorto'] }}</strong>. Aquí tienes los detalles.
@endsection

@section('contenido')
    @include('emails.partials._cita_detalle')
    @include('emails.partials._que_llevar')
@endsection
