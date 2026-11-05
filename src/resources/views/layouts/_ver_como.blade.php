{{--
    Aviso flotante de "Ver como" (App\Support\VerComo): a quién se está viendo,
    que es de sólo lectura y los botones para cambiar o salir. Lo incluye
    layouts.app sólo cuando el modo está activo.
--}}
@php
    $vcEtiqueta = \App\Support\VerComo::frase(app('ver_como.datos'), auth()->user());
@endphp
<style>
    .vc-barra {
        position: fixed; left: 50%; bottom: 14px; transform: translateX(-50%); z-index: 2000;
        display: flex; align-items: center; gap: 10px; flex-wrap: wrap; justify-content: center;
        max-width: calc(100vw - 24px); padding: 8px 10px 8px 16px; border-radius: 999px;
        background: #2E3C3D; color: #fff; box-shadow: 0 6px 24px rgba(0, 0, 0, .28); font-size: 13.5px;
    }
    .vc-barra b { color: #F2D27A; }
    .vc-barra__ro { font-size: 11.5px; padding: 2px 8px; border-radius: 999px; background: rgba(255, 255, 255, .14); white-space: nowrap; }
    .vc-barra .btn { border-radius: 999px; font-size: 12.5px; padding: 3px 12px; white-space: nowrap; }
    .vc-barra form { margin: 0; }
    .vc-bloqueado {
        position: fixed; left: 50%; bottom: 70px; transform: translateX(-50%); z-index: 2000; max-width: calc(100vw - 24px);
        padding: 8px 14px; border-radius: 10px; background: #FBE4E6; color: #A3162E; font-size: 13px; box-shadow: 0 4px 16px rgba(0, 0, 0, .15);
    }
    @media (max-width: 640px) { .vc-barra { border-radius: 14px; } }
</style>
@if (session('ver_como_bloqueado'))
    <div class="vc-bloqueado" role="alert"><i class="bi bi-lock-fill me-1"></i>{{ session('ver_como_bloqueado') }}</div>
@endif
<div class="vc-barra" role="status" aria-live="polite">
    <span><i class="bi bi-eye-fill me-1" aria-hidden="true"></i>Estás viendo como <b>{{ $vcEtiqueta }}</b></span>
    <span class="vc-barra__ro" title="Mientras estés en Ver como no se guarda nada">Sólo lectura</span>
    {{-- Con el modal del encabezado lo abre; en páginas sin él (errores) va a la pantalla de Ver como. --}}
    <a href="{{ route('ver_como.index') }}" class="btn btn-sm btn-outline-light" @if (empty($sinModal)) data-bs-toggle="modal" data-bs-target="#vcModal" @endif>Cambiar</a>
    <form method="POST" action="{{ route('ver_como.salir') }}">
        @csrf
        <button class="btn btn-sm btn-light">Salir</button>
    </form>
</div>
