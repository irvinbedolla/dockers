{{-- Invitación al formulario de atención del trámite. Datos: $d (AtencionTramite::resumen + nombre, parte, liga). --}}
@extends('emails.layouts.ccl')

@section('titulo', 'Tu opinión es importante · Folio '.$d['folio'])
@section('preheader', 'Cuéntanos cómo te atendimos en tu trámite de conciliación. Son 7 preguntas; tienes hasta el '.$d['vence'].'.')
@section('hero_titulo', 'Tu opinión es importante')
@section('hero_texto')
    Hola{{ $d['nombre'] ? ', '.$d['nombre'] : '' }}. Tu trámite de conciliación concluyó. Queremos saber cómo te atendimos para seguir mejorando.
@endsection

@section('contenido')
    <tr>
        <td class="px" style="padding:24px 32px 6px; font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:23px; color:#2E3C3D;">
            Contesta un breve formulario de <strong>7 preguntas</strong> (te toma unos 3 minutos) sobre la atención de Recepción,
            de la persona que capturó tus datos y del Conciliador(a) que llevó tu audiencia. Tus respuestas nos ayudan a
            corregir lo que no funciona y a reconocer lo que sí.
        </td>
    </tr>

    {{-- Folio --}}
    <tr>
        <td class="px" style="padding:14px 32px 6px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#F4F7F7"
                   style="background-color:#F4F7F7; border:1px solid #DCE4E4; border-radius:10px;">
                <tr>
                    <td style="padding:16px 20px; font-family:Arial, Helvetica, sans-serif; color:#2E3C3D;">
                        <span style="display:block; font-size:12px; line-height:16px; color:#5E6E6F; font-weight:bold; letter-spacing:.5px;">FOLIO DE TU SOLICITUD</span>
                        <span style="display:block; font-size:22px; line-height:30px; color:#496163; font-weight:bold;">{{ $d['folio'] }}</span>
                        <span style="display:block; font-size:13px; line-height:19px; color:#5E6E6F;">
                            {{ $d['sede'] }} · levantada el {{ $d['fecha'] }}{{ $d['parte'] ? ' · '.$d['parte'] : '' }}
                        </span>
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    <tr>
        <td class="px" align="center" style="padding:18px 32px 6px;">
            @include('emails.partials._boton', ['url' => $d['liga'], 'texto' => 'Contestar el formulario', 'ancho' => 260, 'fondo' => '#496163', 'color' => '#FFFFFF'])
        </td>
    </tr>

    <tr>
        <td class="px" style="padding:12px 32px 6px; font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:21px; color:#2E3C3D;">
            <strong>Tienes hasta el {{ $d['vence'] }}</strong> para contestarlo; después, la liga caduca.
        </td>
    </tr>
    <tr>
        <td class="px" style="padding:6px 32px 18px; font-family:Arial, Helvetica, sans-serif; font-size:13px; line-height:19px; color:#5E6E6F;">
            Si el botón no abre, entra a <a href="{{ $d['ligaSola'] }}" target="_blank" style="color:#496163; font-weight:bold;">{{ $d['ligaSola'] }}</a>
            y escribe tu folio. Cada parte (trabajadora y empleadora) puede contestar una vez.
        </td>
    </tr>

@endsection
