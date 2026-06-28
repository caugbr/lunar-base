@props(['style' => 'vertical'])

<span class="switch-theme st-{{ $style }}">
    <a class="theme-light" href="#" title="Tema claro">
        <x-lucide-sun class="lucid-icon" />
    </a>
    <a class="theme-dark" href="#" title="Tema escuro">
        <x-lucide-moon class="lucid-icon" />
    </a>
</span>

<script>
    const savedTheme = localStorage.getItem('savedTheme');
    if (savedTheme) {
        document.body.dataset.theme = savedTheme;
        setTimeout(() => {
            const widget = document.querySelector('div#lunar-widget .lunar-widget');
            if (widget) {
                widget.dataset.theme = savedTheme;
            }
        }, 2000);
    }
</script>

@push('accessibility-styles')
<style>
.switch-theme {
    height: 32px;
    width: 64px;
    border-radius: 8px;
    display: inline-flex;
    gap: 0;
    align-items: stretch;
    margin: auto;
    vertical-align: middle;
    overflow: hidden;
    border: 1px solid currentColor;
}
.switch-theme.st-vertical {
    flex-direction: column;
    width: 32px;
    height: 64px;
}

.switch-theme a:first-child {
    border-right: 1px solid currentColor;
}

.switch-theme.st-vertical a:first-child {
    border-right: 0;
    border-bottom: 1px solid currentColor;
}

.switch-theme a:last-child {
    margin-left: -1px;
}

.switch-theme a {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
}

.switch-theme a.theme-dark {
    pointer-events: none;
}

.switch-theme a.theme-dark svg {
    fill: #c1b4f8;
}

[data-theme="light"] .switch-theme a.theme-dark {
    color: #402e8d;
    pointer-events: all;
}

[data-theme="light"] .switch-theme a.theme-dark svg {
    fill: none;
}

[data-theme="light"] .switch-theme a.theme-light {
    pointer-events: none;
}

[data-theme="light"] .switch-theme a.theme-light svg {
    fill: #c1b4f8;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const lightThemeLink = document.querySelector('.switch-theme a.theme-light');
    const darkThemeLink = document.querySelector('.switch-theme a.theme-dark');
    lightThemeLink.addEventListener('click', event => {
        event.preventDefault();
        document.body.dataset.theme = 'light';
        const widget = document.querySelector('div#lunar-widget .lunar-widget');
        if (widget) {
            widget.dataset.theme = 'light';
        }
        localStorage.setItem('savedTheme', 'light');
    });
    darkThemeLink.addEventListener('click', event => {
        event.preventDefault();
        document.body.dataset.theme = 'dark';
        const widget = document.querySelector('div#lunar-widget .lunar-widget');
        if (widget) {
            widget.dataset.theme = 'dark';
        }
        localStorage.setItem('savedTheme', 'dark');
    });
});
</script>
@endpush
