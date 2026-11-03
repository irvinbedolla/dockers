{{--
    "Qué llevar a tu cita" según el trámite. Lo comparten la confirmación y el
    aviso de reagenda. Espera $d (App\Support\CitaCorreo::datos).
--}}
@if ($d['excepcion'])
    <tr>
        <td class="px" style="padding:16px 32px 0; font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:21px; color:#2E3C3D;">
            Por el motivo que indicaste, te atenderá <strong>personal especializado</strong> del Centro.
        </td>
    </tr>
@endif

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
