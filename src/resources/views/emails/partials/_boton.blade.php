{{--
    Botón que se ve igual en Gmail y en Outlook de escritorio (este último lo
    dibuja con VML). Espera: $url, $texto, $ancho (px, para Outlook),
    $fondo, $color y opcional $borde.
--}}
@php
    $borde = $borde ?? $fondo;
@endphp
<!--[if mso]>
<v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ $url }}"
    style="height:46px; v-text-anchor:middle; width:{{ $ancho }}px;" arcsize="16%" strokecolor="{{ $borde }}" strokeweight="2px" fillcolor="{{ $fondo }}">
    <w:anchorlock/>
    <center style="color:{{ $color }}; font-family:Arial, Helvetica, sans-serif; font-size:15px; font-weight:bold;">{{ $texto }}</center>
</v:roundrect>
<![endif]-->
<!--[if !mso]><!-- -->
<a href="{{ $url }}" target="_blank"
   style="display:inline-block; padding:12px 22px; border:2px solid {{ $borde }}; border-radius:8px; background-color:{{ $fondo }}; color:{{ $color }}; font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:20px; font-weight:bold; text-decoration:none; white-space:nowrap; mso-hide:all;">{{ $texto }}</a>
<!--<![endif]-->
