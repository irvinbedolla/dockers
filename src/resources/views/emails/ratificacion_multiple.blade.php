{{-- Acuse de ratificación múltiple (App\Mail\RatificacionMultipleMail). --}}
@extends('emails.layouts.ccl')

@php
    $fechaLarga = fn ($f) => ucfirst($f->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY'));
@endphp

@section('titulo', 'Solicitud de ratificación múltiple · Folio '.$lote->folio)
@section('preheader', $lote->trabajadores.' trabajadores · '.$lote->delegacion.'. Tu acuse va adjunto.')
@section('hero_titulo', 'Recibimos tu solicitud de ratificación múltiple')
@section('hero_texto')
    Hola, {{ $lote->solicitante }}. Registramos la solicitud de <strong style="color:#FFFFFF;">{{ $lote->empresa }}</strong>
    con {{ $lote->trabajadores }} trabajadores. Cada uno tiene su cita.
@endsection

@section('contenido')
<tr>
    <td class="px" style="padding:28px 32px 0;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#F5F7F7"
               style="background-color:#F5F7F7; border:1px solid #E3E8E8; border-radius:10px;">
            <tr>
                <td style="padding:20px 22px;">
                    <p style="margin:0; font-family:Arial, Helvetica, sans-serif; font-size:11px; letter-spacing:1px; text-transform:uppercase; color:#8A9899; font-weight:bold;">Folio de la solicitud</p>
                    <p style="margin:4px 0 6px; font-family:'Courier New', Courier, monospace; font-size:30px; line-height:36px; font-weight:bold; color:#2E3C3D;">{{ $lote->folio }}</p>
                    <p style="margin:0; font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:20px; color:#5E6E6F;">
                        {{ $lote->trabajadores }} trabajadores · Oficina de {{ $lote->delegacion }}
                    </p>
                </td>
            </tr>
        </table>
    </td>
</tr>
<tr>
    <td class="px" style="padding:20px 32px 0; font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:21px; color:#2E3C3D;">
        <p style="margin:0 0 8px; font-weight:bold;">Días y horarios</p>
        @foreach ($dias as $d)
            <p style="margin:0 0 4px;">{{ $fechaLarga($d['fecha']) }}: de {{ $d['desde'] }} a {{ $d['hasta'] }} h · {{ $d['n'] }} {{ $d['n'] === 1 ? 'trabajador' : 'trabajadores' }}</p>
        @endforeach
        <p style="margin:12px 0 0; color:#5E6E6F;">
            En el acuse adjunto está la hora de cada trabajador. Cada convenio se revisa y se atiende por separado:
            es necesario que comparezcan la persona trabajadora y la parte patronal (o su representante) con identificación oficial.
        </p>
    </td>
</tr>
<tr>
    <td class="px" align="center" style="padding:22px 32px 4px;">
        @include('emails.partials._boton', ['url' => $acuse, 'texto' => 'Ver el acuse', 'ancho' => 200, 'fondo' => '#496163', 'color' => '#FFFFFF'])
    </td>
</tr>
@include('emails.partials._aviso_gratuito')
@endsection
