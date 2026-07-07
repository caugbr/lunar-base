@foreach($items as $item)
    @php
        $hasChildren = $item->children->isNotEmpty();

        // Compara a URL atual com a URL dinâmica do link para aplicar a classe 'active'
        $activeClass = request()->url() === $item->url ? 'active' : '';
    @endphp

    <li class="menu-item {{ $hasChildren ? 'has-children' : '' }} {{ $item->class }} {{ $activeClass }}">
        <a href="{{ $item->url }}" target="{{ $item->target }}" class="menu-link">
            {{ $item->label }}
        </a>

        @if($hasChildren)
            {{-- Abre a sublista e faz a chamada recursiva para renderizar os filhos --}}
            <ul class="sub-menu">
                @include('menus::public.menu', ['items' => $item->children])
            </ul>
        @endif
    </li>
@endforeach
