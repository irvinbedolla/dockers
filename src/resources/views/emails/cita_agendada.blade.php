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

    @if ($d['excepcion'])
        <tr>
            <td class="px" style="padding:16px 32px 0; font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:21px; color:#2E3C3D;">
                Por el motivo que indicaste, te atenderá <strong>personal especializado</strong> del Centro.
            </td>
        </tr>
    @endif

    {{-- Qué llevar --}}
    <tr>
        <td class="px" style="padding:26px 32px 8px;">
            <h2 style="margin:0 0 10px; font-family:Arial, Helvetica, sans-serif; font-size:17px; line-height:22px; color:#496163; font-weight:bold;">Qué llevar a tu cita</h2>
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                @foreach ($d['queLlevar'] as $punto)
                    <tr>
                        <td valign="top" width="24" style="width:24px; padding:5px 0; font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:21px; color:#2E9E57; font-weight:bold;">&#10003;</td>
                        <td valign="top" style="padding:5px 0; font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:21px; color:#2E3C3D;">{!! $punto !!}</td>
                    </tr>
                @endforeach
            </table>
            <p style="margin:12px 0 0; font-family:Arial, Helvetica, sans-serif; font-size:13px; line-height:19px; color:#5E6E6F;">
                ¿Vas en representación de otra persona o de una empresa? Lleva además el poder notarial o carta poder que lo acredite.
            </p>
        </td>
    </tr>
@endsection
