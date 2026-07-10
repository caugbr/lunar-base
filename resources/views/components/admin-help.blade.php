@php
    use Illuminate\Support\Facades\Route;

    $routeName = Route::currentRouteName(); // Ex: "admin.posts.index"
    $helpView = null;

    if ($routeName) {
        $flatPath = 'admin.help.' . str_replace('.', '-', $routeName);

        if (view()->exists($flatPath)) {
            $helpView = $flatPath;
        }
    }
@endphp

@if ($helpView)
<link rel="stylesheet" href="{{ asset('css/admin/admin-help.css') }}">
<div class="admin-help">
    <x-modal title="Ajuda" :id="$helpView" size="lg">
        <div class="stage">
            @include($helpView)
        </div>
    </x-modal>
    <button type="button"
        onclick="window.dispatchEvent(new CustomEvent('modal-open', { detail: { id: '{{ $helpView }}' } }))"
        class="transparent-btn"
        title="Ajuda da interface">
        <x-lucide-circle-question-mark class="lucid-icon" />
    </button>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const a = document.querySelector('.admin-help > a');
        if (a) {
            a.addEventListener('click', event => {
                event.preventDefault();
                const header = document.querySelector('.admin-header');
                header.classList.toggle('show-help');
            });
        }
    });
</script>
@endif
