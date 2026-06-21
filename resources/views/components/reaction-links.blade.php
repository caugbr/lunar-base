@props([
    'type',
    'id',
    'data' => [],
])

@php
$positiveCount = $data['positive'] ?? 0;
$negativeCount = $data['negative'] ?? 0;
$userReaction = $data['user'] ?? null;

$reactionType = $data['reactionType'] ?? setting('reading.post_reaction_type', 'thumbs');
$allowNegative = $data['allowNegative'] ?? setting('reading.post_negative_reaction', false);

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
<style>
.reaction-links {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.reaction-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.375rem 0.75rem;
    border: 1px solid #e5e7eb;
    border-radius: 9999px;
    background: #ffffff;
    color: #6b7280;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.15s ease;
}

.reaction-btn:hover {
    background: #f3f4f6;
    border-color: #d1d5db;
}

.reaction-btn.active {
    background: #eff6ff;
    border-color: #3b82f6;
    color: #3b82f6;
}

.reaction-btn.active.reaction-negative {
    background: #fef2f2;
    border-color: #ef4444;
    color: #ef4444;
}

.reaction-btn .lucid-icon {
    width: 16px;
    height: 16px;
}

.reaction-count {
    font-weight: 500;
    min-width: 1ch;
}
</style>
@endpush

@push('scripts')
<script>
document.querySelectorAll('.reaction-links').forEach(container => {
    const type = container.dataset.type;
    const id = container.dataset.id;

    container.querySelectorAll('.reaction-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            const value = btn.dataset.value;

            try {
                const response = await fetch(`/react/${type}/${id}/${value}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                        'Accept': 'application/json',
                    },
                });

                if (!response.ok) throw new Error('Erro na reação');

                const data = await response.json();

                const posBtn = container.querySelector('.reaction-positive');
                const negBtn = container.querySelector('.reaction-negative');

                if (posBtn) {
                    posBtn.querySelector('.reaction-count').textContent = data.positive;
                    posBtn.classList.toggle('active', data.user_reaction === 1);
                }
                if (negBtn) {
                    negBtn.querySelector('.reaction-count').textContent = data.negative;
                    negBtn.classList.toggle('active', data.user_reaction === -1);
                }

            } catch (err) {
                console.error('Erro ao reagir:', err);
            }
        });
    });
});
</script>
@endpush
@endonce
