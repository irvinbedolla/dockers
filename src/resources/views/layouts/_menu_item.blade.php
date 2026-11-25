{{-- Un renglón del menú lateral (layouts.menu). Espera $item, $destino y $activo. --}}
<li class="nav-item side-menus {{ $activo ? 'active' : '' }}">
    {{-- aria-label va siempre, no sólo cuando el menú está angosto:
         con .sidebar-mini el tema le pone display:none al <span>, y
         eso también lo esconde de los lectores de pantalla. Sin
         esto, el menú colapsado son treinta enlaces sin nombre.

         data-etiqueta es de donde toma su texto el globito. --}}
    <a class="nav-link"
    href="{{ $destino }}"
    aria-label="{{ $item['label'] }}"
    data-etiqueta="{{ $item['label'] }}"
    @isset($item['id']) id="{{ $item['id'] }}" @endisset
    @if ($activo) aria-current="page" @endif>
        <i class="{{ $item['icon'] }}" aria-hidden="true"></i>
        <span class="text-dark">{{ $item['label'] }}</span>
        @isset($item['badge'])
            <span id="{{ $item['badge'] }}" class="badge bg-danger ms-1" style="display: none;">0</span>
        @endisset
    </a>
</li>
