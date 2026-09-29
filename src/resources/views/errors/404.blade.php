@extends('errors._marco')

@section('codigo', '404')
@section('titulo', 'Esta página no existe')

@section('texto')
    La dirección que abriste no corresponde a ninguna pantalla del sistema.
    Puede que el enlace esté mal escrito, que venga de un correo viejo, o que
    esa sección haya cambiado de lugar.
@endsection

{{-- Se muestra sólo la ruta, nunca la consulta: ahí viajan folios, CURP y
     nombres, y esta pantalla se ve en voz alta por teléfono con soporte. --}}
@section('ruta', Str::limit(request()->path() === '/' ? '/' : '/'.request()->path(), 120))
