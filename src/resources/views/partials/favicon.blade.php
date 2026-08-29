{{--
    Favicon del sistema. Un solo lugar para todas las cabeceras: cada vista con
    <head> propio incluye este parcial, así el icono no depende de que alguien
    se acuerde de copiar los <link> al crear una pantalla nueva.

    Los archivos viven en public/ (raíz), no en assets/images/, porque el
    navegador pide /favicon.ico por su cuenta cuando la cabecera no declara nada.

    El ?v= se sube cuando cambie el icono: el favicon se cachea de forma muy
    agresiva y sin eso los usuarios siguen viendo el anterior durante semanas.
--}}
<link rel="icon" href="{{ asset('favicon.ico') }}?v=1" sizes="32x32">
<link rel="icon" href="{{ asset('favicon.svg') }}?v=1" type="image/svg+xml">
<link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}?v=1">
<link rel="manifest" href="{{ asset('site.webmanifest') }}?v=1">
<meta name="theme-color" content="#496163">
