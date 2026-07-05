@php
$positiveCount = $data['positive'] ?? 0;
$negativeCount = $data['negative'] ?? 0;
$userReaction = $data['user'] ?? null;

$reactionType = setting('reading.post_reaction_type', 'thumbs');
$allowNegative = setting('reading.post_negative_reaction', false);

$icons = [
    'thumbs' => ['up' => 'thumbs-up', 'down' => 'thumbs-down'],
    'heart'  => ['up' => 'heart', 'down' => 'heart-crack'],
    'star'   => ['up' => 'star', 'down' => 'star-off'],
];

$positiveIcon = $icons[$reactionType]['up'] ?? 'thumbs-up';
$negativeIcon = $icons[$reactionType]['down'] ?? 'thumbs-down';
@endphp

<div class="reaction-links" data-type="{{ $type }}" data-id="{{ $id }}">
    <button type="button"
            class="reaction-btn reaction-positive {{ $userReaction === 1 ? 'active' : '' }}"
            data-value="plus"
            title="Gostei">
        <x-dynamic-component component="lucide-{{ $positiveIcon }}" class="lucid-icon" />
        <span class="reaction-count">{{ $positiveCount }}</span>
    </button>

    @if($allowNegative)
        <button type="button"
                class="reaction-btn reaction-negative {{ $userReaction === -1 ? 'active' : '' }}"
                data-value="minus"
                title="Não gostei">
            <x-dynamic-component component="lucide-{{ $negativeIcon }}" class="lucid-icon" />
            <span class="reaction-count">{{ $negativeCount }}</span>
        </button>
    @endif
</div>

@once
@push('styles')
<link rel="stylesheet" href="{{ asset('plugins/reactions/css/reactions.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('plugins/reactions/js/reactions.js') }}"></script>
@endpush
@endonce
