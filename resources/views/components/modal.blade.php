@props([
    'id' => 'modal-' . Str::random(6),
    'title' => '',
    'size' => 'md', // sm, md, lg, xl
    'showFooter' => true,
])

<div
    x-data="modalComponent('{{ $id }}')"
    @modal-open.window="if ($event.detail?.id === id) open()"
    @modal-close.window="if ($event.detail?.id === id) close()"
    x-cloak
    class="modal-overlay"
    role="dialog"
    aria-modal="true"
>
    {{-- Backdrop (Efeito de fade-in escurecido suave) --}}
    <div
        x-show="isOpen"
        x-transition.opacity.duration.300ms {{-- 💡 Transição nativa de opacidade sem Tailwind! --}}
        @click="close()"
        class="modal-backdrop"
    ></div>

    {{-- Modal Box (Efeito de escala e fade-in suave) --}}
    <div
        x-show="isOpen"
        x-transition.duration.300ms {{-- 💡 Transição nativa de escala e fade sem Tailwind! --}}
        @click.stop
        class="modal-box"
        :class="modalSizeClass"
    >
        {{-- Header --}}
        @if($title)
        <div class="modal-header">
            <h3 class="modal-title">{{ $title }}</h3>
            <button @click="close()" class="modal-close" aria-label="Fechar">
                <x-lucide-x class="lucid-icon" />
            </button>
        </div>
        @endif

        {{-- Body --}}
        <div class="modal-body">
            {{ $slot }}
        </div>

        {{-- Footer --}}
        @if($showFooter)
        <div class="modal-footer">
            {{ $footer ?? '' }}
            <button @click="close()" class="admin-btn admin-btn-secondary">
                Fechar
            </button>
        </div>
        @endif
    </div>
</div>

@push('styles')
<style>
    [x-cloak] { display: none !important; }

    .modal-overlay {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        pointer-events: none;
    }

    .modal-overlay > * {
        pointer-events: auto;
    }

    .modal-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(4px);
    }

    .modal-box {
        position: relative;
        background: white;
        border-radius: 0.75rem;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        display: flex;
        flex-direction: column;
        max-height: 90vh;
        width: 100%;
        overflow: hidden;
    }

    .modal-box.sm { max-width: 24rem; }
    .modal-box.md { max-width: 32rem; }
    .modal-box.lg { max-width: 48rem; }
    .modal-box.xl { max-width: 64rem; }
    .modal-box.full { max-width: 95vw; max-height: 95vh; }

    .modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #e5e7eb;
        background: #f9fafb;
    }

    .modal-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
    }

    .modal-close {
        background: none;
        border: none;
        padding: 0.5rem;
        color: #9ca3af;
        cursor: pointer;
        border-radius: 0.375rem;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modal-close:hover {
        background: #e5e7eb;
        color: #374151;
    }

    .modal-body {
        padding: 1.5rem;
        overflow-y: auto;
        flex: 1;
    }

    .modal-footer {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.75rem;
        padding: 1rem 1.5rem;
        border-top: 1px solid #e5e7eb;
        background: #f9fafb;
    }
</style>
@endpush

@push('scripts')
<script>
function modalComponent(id) {
    return {
        id: id,
        isOpen: false,

        open() {
            this.isOpen = true;
            document.body.style.overflow = 'hidden';
            this.$nextTick(() => {
                const firstInput = this.$el?.querySelector('input, textarea, select, button');
                firstInput?.focus();
            });
        },

        close() {
            this.isOpen = false;
            document.body.style.overflow = '';
        },

        get modalSizeClass() {
            const sizes = { sm: 'sm', md: 'md', lg: 'lg', xl: 'xl', full: 'full' };
            return sizes['{{ $size }}'] || 'md';
        }
    }
}
</script>
@endpush
