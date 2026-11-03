@extends('errors._marco')

{{--
    413: el envío pesó más de lo que acepta PHP (post_max_size, ver el
    Dockerfile). Laravel lo detecta antes de leer el formulario, así que no
    se guardó nada. Pasa sobre todo al subir los PDF de un poder.
--}}
@section('codigo', '413')
@section('titulo', 'Los archivos pesan demasiado')

@section('texto')
    Entre todos los documentos que adjuntaste se pasó del máximo que acepta el
    sistema en un solo envío, así que no se guardó nada. Regresa, revisa que
    cada PDF pese máximo 10 MB y que entre todos no pasen de 40 MB (puedes
    escanearlos en blanco y negro o comprimirlos) y vuelve a enviarlo.
@endsection
