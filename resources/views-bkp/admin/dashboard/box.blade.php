{{-- admin/dashboard/box.blade.php --}}
<div class="dashboard-box" style="grid-column: span {{ $span ?? 1 }}">

    {{-- Header do box --}}
    @if($title || $icon)
    <div class="box-header">
        <h3>
            @if($icon)
            <x-dynamic-component component="lucide-{{ $icon }}" class="lucid-icon" />
            @endif
            {{ $title }}
        </h3>
    </div>
    @endif

    {{-- Conteúdo (view do controller) --}}
    <div class="box-content">
        {!! $content !!}
    </div>
</div>
