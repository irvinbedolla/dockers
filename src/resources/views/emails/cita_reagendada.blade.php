{{-- Aviso de reagenda con el acuse nuevo. Datos: $d, $fechaAnterior y $moduloAnterior. --}}
@extends('emails.layouts.ccl')

@section('titulo', 'Tu cita cambió de fecha · Folio '.$d['folio'])
@section('preheader', 'Tu nueva cita es el '.$d['fechaLarga'].' a las '.$d['hora'].' h en '.$d['sede'].'. Tu nuevo acuse con código QR va adjunto.')
@section('hero_titulo', 'Tu cita cambió de fecha')
@section('hero_texto')
    Hola, {{ $d['nombre'] }}. Reprogramamos tu cita de <strong style="color:#FFFFFF;">{{ $d['tipoCorto'] }}</strong>. Estos son los nuevos datos.
@endsection

@section('contenido')
    {{-- Lo que cambió --}}
    <tr>
        <td class="px" style="padding:28px 32px 0;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#FBF7EA"
                   style="background-color:#FBF7EA; border-left:4px solid #CEA845; border-radius:0 8px 8px 0;">
                <tr>
                    <td style="padding:14px 18px; font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:21px; color:#2E3C3D;">
                        Antes: <span style="color:#8A9899; text-decoration:line-through;">{{ $fechaAnterior }}</span><br>
                        Ahora: <strong>{{ $d['fechaLarga'] }} a las {{ $d['hora'] }} h</strong><br>
                        <span style="color:#5E6E6F;">Tu código QR sigue siendo el mismo; preséntate en la nueva fecha y hora. Te adjuntamos el acuse actualizado.</span>
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    @include('emails.partials._cita_detalle', ['moduloAnterior' => $moduloAnterior])
    @include('emails.partials._que_llevar')
@endsection
