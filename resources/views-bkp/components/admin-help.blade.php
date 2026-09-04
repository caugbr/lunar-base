@php
    use Illuminate\Support\Facades\Route;

    $routeName = Route::currentRouteName();
    $helpView = null;

    if ($routeName) {
        $flatName = str_replace('.', '-', $routeName);

        // Tenta no Core (admin.help.admin-pages-index)
        $corePath = "admin.help.{$flatName}";

        if (view()->exists($corePath)) {
            $helpView = $corePath;
        } else {
            // Busca dinâmica: varre todos os namespaces de plugins registrados
            $hints = array_keys(view()->getFinder()->getHints());

            foreach ($hints as $namespace) {
                $pluginPath = "{$namespace}::help.{$flatName}";
                if (view()->exists($pluginPath)) {
                    $helpView = $pluginPath;
                    break;
                }
            }
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
        class="admin-btn admin-btn-secondary"
        title="Ajuda da interface"
        style="padding: 8px;"
        disabled>
        <x-lucide-circle-help class="lucid-icon" />
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
        const helpButton = document.querySelector('button[title="Ajuda da interface"]');
        if (helpButton) {
            helpButton.disabled = false;
        }
    });
</script>
@endif
