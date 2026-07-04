@php
    // Se $fields for null ou não for array, não faz nada
    $fields = $fields ?? [];
    if (!is_array($fields)) {
        $fields = [];
    }

    // Suporta tanto array plano quanto agrupado (como no settings)
    // Se o primeiro item tiver 'fields', é um array agrupado
    $isGrouped = false;
    if (!empty($fields)) {
        $firstItem = reset($fields);
        $isGrouped = is_array($firstItem) && isset($firstItem['fields']);
    }
@endphp

@stack('styles')

{{-- Se for agrupado (como settings), faz loop duplo --}}
@if($isGrouped)
    @foreach($fields as $groupKey => $group)
        @if(isset($group['title']))
            <div class="group-header" style="margin: 2rem 0 1.5rem; padding-bottom: 0.5rem; border-bottom: 2px solid #e5e7eb;">
                <h3 style="display: flex; align-items: center; gap: 0.5rem; font-size: 1.25rem; font-weight: 600; color: #1f2937;">
                    @if(isset($group['icon']))
                        <x-dynamic-component component="lucide-{{ $group['icon'] }}" class="lucid-icon" />
                    @endif
                    {{ $group['title'] }}
                </h3>
                @if(isset($group['description']))
                    <p style="font-size: 0.875rem; color: #6b7280; margin: 0.25rem 0 0;">
                        {{ $group['description'] }}
                    </p>
                @endif
            </div>
        @endif

        @foreach($group['fields'] as $def)
            @include('forms::public.partials.field-single', ['def' => $def])
        @endforeach
    @endforeach

{{-- Se for array plano (como formulários genéricos), loop simples --}}
@else
    @foreach($fields as $def)
        @include('forms::public.partials.field-single', ['def' => $def])
    @endforeach
@endif
