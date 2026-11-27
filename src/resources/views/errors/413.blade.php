@extends('errors._marco')

{{--
    413: el envío pesó más de lo que acepta PHP (post_max_size, ver el
    Dockerfile; los poderes van por el pool "poderes", php-fpm-poderes.conf)
    o nginx (client_max_body_size). Laravel lo detecta antes de leer el
    formulario, así que no se guardó nada.
--}}
@section('codigo', '413')
@section('titulo', 'Los archivos pesan demasiado')

@section('texto')
    Entre todos los documentos que adjuntaste se pasó del máximo que acepta el
    sistema en un solo envío, así que no se guardó nada. Regresa, revisa que
    cada PDF pese máximo 10 MB y que entre todos no pasen de 40 MB (en el
    registro de poderes, el acta constitutiva y el documento que acredita la
    personería pueden pesar hasta 150 MB cada uno, y entre todos 400 MB;
    puedes escanearlos en blanco y negro o comprimirlos) y vuelve a enviarlo.
@endsection
