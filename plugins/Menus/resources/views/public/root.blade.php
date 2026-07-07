@once
<link rel="stylesheet" href="{{ asset('plugins/menus/css/menus-public.css') }}">
@endonce

{{-- Contêiner Raiz do Menu --}}
<ul class="menus-list">
    @include('menus::public.menu', ['items' => $items])
</ul>
