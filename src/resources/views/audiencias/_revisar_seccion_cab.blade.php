{{--
    Encabezado de una sección de Revisar audiencia. La sección se ve en modo
    lectura; "Editar" la abre para cambiar datos (ver el script de
    revisar_audiencia). Espera $titulo y opcional $nota.
--}}
<div class="ra-seccion__cab">
    <div>
        <h4 class="ra-seccion__titulo">{{ $titulo }}</h4>
        @isset($nota)<span class="ra-seccion__nota">{{ $nota }}</span>@endisset
    </div>
    <div class="ra-seccion__herr">
        <span class="ra-seccion__cambios" hidden><i class="bi bi-dot"></i> Cambios sin guardar</span>
        @if ($puedeEditar)
            <button type="button" class="btn btn-sm ra-editar"><i class="bi bi-pencil"></i> <span>Editar</span></button>
        @endif
    </div>
</div>
