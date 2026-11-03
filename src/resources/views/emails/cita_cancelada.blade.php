{{-- Aviso de cita cancelada. Datos: $d (App\Support\CitaCorreo::datos) y $nuevaCita (liga para agendar otra). --}}
@extends('emails.layouts.ccl')

@section('titulo', 'Tu cita fue cancelada · Folio '.$d['folio'])
@section('preheader', 'Tu cita del '.$d['fechaLarga'].' a las '.$d['hora'].' h fue cancelada. Si lo deseas, puedes generar una nueva.')
@section('hero_titulo', 'Tu cita fue cancelada')
@section('hero_texto')
    Hola, {{ $d['nombre'] }}. Te avisamos que tu cita de <strong style="color:#FFFFFF;">{{ $d['tipoCorto'] }}</strong> fue cancelada.
@endsection

@section('contenido')
    {{-- La cita que se canceló --}}
    <tr>
        <td class="px" style="padding:28px 32px 0;">
            @php
                $filas = [
                    ['Folio', '<strong>'.e($d['folio']).'</strong>'],
                    ['Trámite', e($d['tipo'])],
                    ['Fecha', '<span style="text-decoration:line-through;">'.e($d['fechaLarga']).'</span>'],
                    ['Hora', '<span style="text-decoration:line-through;">'.e($d['hora']).' h</span>'],
                    ['Oficina', e($d['sede'])],
                ];
            @endphp
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                @foreach ($filas as [$etiqueta, $valor])
                    <tr>
                        <td class="etiqueta" valign="top" width="110" style="width:110px; padding:10px 0; border-bottom:1px solid #EEF2F2; font-family:Arial, Helvetica, sans-serif; font-size:13px; line-height:20px; color:#8A9899;">{{ $etiqueta }}</td>
                        <td valign="top" style="padding:10px 0; border-bottom:1px solid #EEF2F2; font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:21px; color:#2E3C3D;">{!! $valor !!}</td>
                    </tr>
                @endforeach
            </table>
        </td>
    </tr>

    {{-- Qué hacer ahora --}}
    <tr>
        <td class="px" style="padding:22px 32px 0;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#FBF7EA"
                   style="background-color:#FBF7EA; border-left:4px solid #CEA845; border-radius:0 8px 8px 0;">
                <tr>
                    <td style="padding:14px 18px; font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:21px; color:#2E3C3D;">
                        <strong>Tu acuse y su código QR ya no son válidos.</strong>
                        Si deseas que te atendamos, genera una nueva cita; es gratuita y puedes elegir el día y la hora que más te convengan.
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    <tr>
        <td class="px" style="padding:22px 32px 8px;">
            @include('emails.partials._boton', ['url' => $nuevaCita, 'texto' => 'Generar una nueva cita', 'ancho' => 240, 'fondo' => '#CEA845', 'color' => '#FFFFFF'])
        </td>
    </tr>
@endsection
