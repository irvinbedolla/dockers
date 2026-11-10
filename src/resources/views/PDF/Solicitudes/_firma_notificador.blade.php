{{--
    Firma del notificador hecha en SíNotifico (Módulo 3, App\Services\Sinotifico\Razones).
    Sólo aparece si la razón de este citado se firmó en la app; si no, la línea
    queda en blanco para firmar a mano, como siempre.
--}}
@php($firmaNotificadorApp = \App\Services\Sinotifico\Razones::firmaEstampada($citado ?? null))
@if ($firmaNotificadorApp)
    <center><img src="{{ $firmaNotificadorApp }}" alt="" style="height: 60px; margin-bottom: -26px;"></center>
@endif
