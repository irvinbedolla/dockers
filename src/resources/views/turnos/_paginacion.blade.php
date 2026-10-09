{{--
    Paginación en español. La de Laravel sale como "pagination.previous"
    porque el proyecto no trae lang/es/pagination.php.
--}}
@if ($pagina->hasPages())
    @php
        $actual = $pagina->currentPage();
        $ultima = $pagina->lastPage();
        $desde  = max(1, $actual - 2);
        $hasta  = min($ultima, $actual + 2);
    @endphp
    <nav aria-label="Páginas">
        <ul class="pagination pagination-sm mb-0">
            <li class="page-item {{ $pagina->onFirstPage() ? 'disabled' : '' }}">
                <a class="page-link" href="{{ $pagina->previousPageUrl() ?? '#' }}" aria-label="Anterior">&lsaquo; Anterior</a>
            </li>
            @if ($desde > 1)
                <li class="page-item"><a class="page-link" href="{{ $pagina->url(1) }}">1</a></li>
                @if ($desde > 2)<li class="page-item disabled"><span class="page-link">…</span></li>@endif
            @endif
            @for ($p = $desde; $p <= $hasta; $p++)
                <li class="page-item {{ $p === $actual ? 'active' : '' }}" @if($p === $actual) aria-current="page" @endif>
                    <a class="page-link" href="{{ $pagina->url($p) }}">{{ $p }}</a>
                </li>
            @endfor
            @if ($hasta < $ultima)
                @if ($hasta < $ultima - 1)<li class="page-item disabled"><span class="page-link">…</span></li>@endif
                <li class="page-item"><a class="page-link" href="{{ $pagina->url($ultima) }}">{{ $ultima }}</a></li>
            @endif
            <li class="page-item {{ $pagina->hasMorePages() ? '' : 'disabled' }}">
                <a class="page-link" href="{{ $pagina->nextPageUrl() ?? '#' }}" aria-label="Siguiente">Siguiente &rsaquo;</a>
            </li>
        </ul>
    </nav>
@endif
