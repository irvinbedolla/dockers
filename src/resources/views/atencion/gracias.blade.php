{{-- Después de contestar el formulario de atención (AtencionTramiteController::gracias). --}}
@extends('atencion.layout')
@section('titulo', '¡Gracias por tu opinión!')

@section('contenido')
<div class="at-tarjeta">
    <div class="at-cabeza">
        <h1><i class="bi bi-check2-circle me-2" aria-hidden="true"></i>¡Gracias por tu opinión!</h1>
        <p>Recibimos tus respuestas{{ $folio ? ' del folio '.$folio : '' }}.</p>
    </div>
    <div class="at-cuerpo">
        <p class="mb-3">Tu opinión nos ayuda a mejorar la atención del Centro de Conciliación Laboral. La revisamos para corregir lo que no funciona y reconocer lo que sí.</p>
        <div class="at-aviso at-aviso--info mb-3">
            Si reportaste una situación que requiere atención, el Centro la revisará. Recuerda que todos nuestros servicios son <b>gratuitos</b>:
            si alguien te pidió dinero, denúncialo al <b>070, opción 1</b> (Secretaría de Contraloría del Estado).
        </div>
        <a class="btn btn-verde" href="https://cclmichoacan.gob.mx"><i class="bi bi-house me-1"></i> Ir al sitio del Centro</a>
    </div>
</div>
@endsection
