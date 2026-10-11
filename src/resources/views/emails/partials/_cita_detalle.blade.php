{{--
    Folio, datos de la cita y botones. Lo comparten la confirmación y el aviso
    de cambio de módulo. Espera $d (App\Support\CitaCorreo::datos) y opcional
    $moduloAnterior.
--}}
{{-- Folio y código QR. En celular el QR baja debajo del folio. --}}
<tr>
    <td class="px" style="padding:28px 32px 0;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#F5F7F7"
               style="background-color:#F5F7F7; border:1px solid #E3E8E8; border-radius:10px;">
            <tr>
                <td class="col" valign="middle" style="padding:20px 22px;">
                    <p style="margin:0; font-family:Arial, Helvetica, sans-serif; font-size:11px; line-height:14px; letter-spacing:1px; text-transform:uppercase; color:#8A9899; font-weight:bold;">Folio de tu cita</p>
                    <p class="folio" style="margin:4px 0 10px; font-family:'Courier New', Courier, monospace; font-size:30px; line-height:36px; font-weight:bold; letter-spacing:1px; color:#2E3C3D;">{{ $d['folio'] }}</p>
                    <span style="display:inline-block; padding:6px 12px; border-radius:999px; background-color:#FBF4E1; color:#8A6A12; font-family:Arial, Helvetica, sans-serif; font-size:13px; line-height:16px; font-weight:bold;">{{ $d['tipo'] }}</span>
                    @if (! empty($d['qrPng']))
                        <p style="margin:14px 0 0; font-family:Arial, Helvetica, sans-serif; font-size:13px; line-height:19px; color:#5E6E6F;">
                            Muestra este <strong style="color:#2E3C3D;">código QR</strong> en recepción al llegar: desde tu celular o impreso.
                        </p>
                    @endif
                </td>
                @if (! empty($d['qrPng']))
                    <td class="col" valign="middle" align="center" width="190" style="width:190px; padding:16px 20px 16px 0;">
                        <img src="{{ isset($message) ? $message->embedData($d['qrPng'], 'qr-cita-'.$d['folio'].'.png', 'image/png') : 'data:image/png;base64,'.base64_encode($d['qrPng']) }}"
                             width="170" height="170" alt="Código QR de la cita {{ $d['folio'] }}"
                             style="display:block; width:170px; height:170px; border:6px solid #FFFFFF; border-radius:8px; background-color:#FFFFFF; font-family:Arial, Helvetica, sans-serif; font-size:12px; color:#5E6E6F;">
                    </td>
                @endif
            </tr>
        </table>
    </td>
</tr>

{{-- Datos --}}
<tr>
    <td class="px" style="padding:20px 32px 0;">
        @php
            $filas = [
                ['Día', '<strong>'.e($d['fechaLarga']).'</strong>'],
                ['Hora', '<strong>'.e($d['hora']).' h</strong> &nbsp;<span style="color:#8A9899;">(tolerancia de 5 minutos)</span>'],
                ['Oficina', e($d['sede'])],
                ['Te atiende', (! empty($moduloAnterior) ? '<span style="color:#8A9899; text-decoration:line-through;">'.e($moduloAnterior).'</span> &rarr; ' : '').'<strong>'.e($d['modulo']).'</strong>'],
            ];
            if ($d['direccion']) {
                $filas[] = ['Dirección', e($d['direccion']).($d['mapa'] ? '<br><a href="'.e($d['mapa']).'" target="_blank" style="color:#496163; font-weight:bold; text-decoration:underline;">Ver en el mapa</a>' : '')];
            }
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

{{-- Botones --}}
<tr>
    <td class="px" style="padding:22px 32px 0;">
        {{-- Sin ancho fijo: la tabla toma el de los botones. En celular,
             .col y .boton los apilan a todo lo ancho. --}}
        <table role="presentation" class="contenedor-botones" cellpadding="0" cellspacing="0" border="0" align="left">
            <tr>
                <td class="col boton" valign="top" style="white-space:nowrap;">
                    @include('emails.partials._boton', ['url' => $d['calendario'], 'texto' => 'Agregar a Google Calendar', 'ancho' => 250, 'fondo' => '#CEA845', 'color' => '#FFFFFF'])
                </td>
                @if ($d['mapa'])
                    <td class="col-espacio" width="12" style="width:12px; font-size:0; line-height:0;">&nbsp;</td>
                    <td class="col boton" valign="top" style="white-space:nowrap;">
                        @include('emails.partials._boton', ['url' => $d['mapa'], 'texto' => 'Cómo llegar', 'ancho' => 170, 'fondo' => '#FFFFFF', 'color' => '#496163', 'borde' => '#496163'])
                    </td>
                @endif
            </tr>
        </table>
    </td>
</tr>

{{-- Acuse adjunto --}}
<tr>
    <td class="px" style="padding:22px 32px 0;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#FBF7EA"
               style="background-color:#FBF7EA; border-left:4px solid #CEA845; border-radius:0 8px 8px 0;">
            <tr>
                <td style="padding:14px 18px; font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:21px; color:#2E3C3D;">
                    <strong>También va tu acuse en PDF adjunto a este correo,</strong> con el mismo código QR.
                    Puedes presentar cualquiera de los dos. Te recomendamos llegar 10 minutos antes.
                </td>
            </tr>
        </table>
    </td>
</tr>
