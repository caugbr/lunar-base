<select {{ $attributes->merge(['class' => 'form-input']) }}>
    <option value="">{{ $placeholder }}</option>
    @foreach($pages as $page)
        @php
            $optionValue = $page->{$valueField};
            $optionLabel = $page->{$labelField};
            if ($showNamespace && !empty($page->namespace)) {
                $optionLabel = $page->namespace . ' / ' . $optionLabel;
            }
        @endphp
        <option value="{{ $optionValue }}" @selected($selected == $optionValue)>
            {{ $optionLabel }}
        </option>
    @endforeach
</select>
