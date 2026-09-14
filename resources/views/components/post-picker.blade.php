<select {{ $attributes->merge(['class' => 'form-input post-picker-select']) }}>
    @if($placeholder !== false)
        <option value="">{{ $placeholder }}</option>
    @endif

    @foreach($posts as $post)
        @php
            $optionValue = $post->{$valueField};
            $optionLabel = $post->{$labelField};

            if ($showBadges) {
                $badges = [];

                if ($post->sticky) {
                    $badges[] = 'FIXADO';
                }

                if ($post->featured) {
                    $badges[] = '★';
                }

                if ($post->status !== 'published') {
                    $badges[] = ucfirst($post->status);
                }

                if (!empty($badges)) {
                    $optionLabel = '[' . implode(' | ', $badges) . '] ' . $optionLabel;
                }
            }
        @endphp

        <option value="{{ $optionValue }}" @selected((string)$selected === (string)$optionValue)>
            {{ $optionLabel }}
        </option>
    @endforeach
</select>
