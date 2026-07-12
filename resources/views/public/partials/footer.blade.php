<footer class="site-footer">
    <div class="container">
        <div class="footer-inner">
            <div class="footer-brand footer-column">
                <span class="logo">{{ setting('site_name', 'Lunar Base') }}</span>
                <p>{{ setting('site_description', 'Laravel admin starter kit') }}</p>
            </div>

            @if(setting('general.cookies_consent') || $termsAndPrivacy['terms'] || $termsAndPrivacy['privacy'])
            <div class="footer-links footer-column">
                <h4>Legal</h4>
                <ul class="footer-links-list">
                    @if($termsAndPrivacy['terms'])
                    <li>
                        <a href="{{ $termsAndPrivacy['terms']->url }}">{{ $termsAndPrivacy['terms']->title }}</a>
                    </li>
                    @endif
                    @if($termsAndPrivacy['privacy'])
                    <li>
                        <a href="{{ $termsAndPrivacy['privacy']->url }}">{{ $termsAndPrivacy['privacy']->title }}</a>
                    </li>
                    @endif
                    @if(setting('general.cookies_consent'))
                    <li>
                        <a href="#" id="open-cookie-preferences-link">Preferências de Cookies</a>
                    </li>
                    @endif
                </ul>
            </div>
            @endif

            @php
            $socials = settingsGroup('social');
            $socials = array_filter($socials, function($key, $val) {
                return !empty($val) && str_ends_with($key, '_url');
            }, ARRAY_FILTER_USE_BOTH);
            $showSocial = !!count($socials);
            @endphp

            @if($showSocial)
            <div class="footer-links footer-column">
                <h4>Social</h4>
                <ul class="footer-links-list">
                @foreach($socials as $network => $url)
                    <li>
                        <a href="{{ $url }}" target="_blank" rel="noopener">
                            {{ ucfirst(str_replace('_url', '', $network)) }}
                        </a>
                    </li>
                @endforeach
                </ul>
            </div>
            @endif
        </div>

        <div class="footer-bottom">
            <p>
                &copy; {{ date('Y') }} {{ setting('general.site_name') }}.
                Todos os direitos reservados.
            </p>
            @if($footerText)
            <p>{!! $footerText !!}</p>
            @endif
        </div>
    </div>
</footer>
@php
$useSwitchThemes = setting('reading.switch_themes');
$useTextSize = setting('reading.increase_text_size');
$useVlibras = setting('reading.vlibras');
$positionClass = setting('reading.position', 'right-middle');
$textSizeSteps = setting('reading.text_size_steps', 2);
$textSizeStepValue = setting('reading.text_size_step_value', 4);
@endphp
@if($useSwitchThemes || $useTextSize || $useVlibras)
<div class="accessibility {{ $positionClass }}">
    @if($useSwitchThemes)
    <x-switch-theme />
    @endif
    @if($useTextSize)
    <x-text-size :variation="$textSizeSteps" :step="$textSizeStepValue" />
    @endif
    @if($useVlibras)
    <x-vlibras />
    @endif
</div>
<style>
/* --- Estilos Base + Padrão (right-middle) --- */
.accessibility {
    position: fixed;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    z-index: 99999;

    /* Comportamento Padrão (Fallback): Direita e Centralizado Verticalmente */
    right: 10px;
    left: auto;
    top: 50%;
    bottom: auto;
    transform: translateY(-50%);
}

/* --- ALINHAMENTO VERTICAL --- */

/* Superior (Esquerda / Direita) */
.accessibility.left-top,
.accessibility.right-top {
    top: 10px;
    bottom: auto;
    transform: none;
}

/* Centralizado (Esquerda / Direita) */
.accessibility.left-middle,
.accessibility.right-middle {
    top: 50%;
    bottom: auto;
    transform: translateY(-50%);
}

/* Inferior (Esquerda / Direita) */
.accessibility.left-bottom,
.accessibility.right-bottom {
    top: auto;
    bottom: 10px;
    transform: none;
}

/* --- ALINHAMENTO HORIZONTAL --- */

/* Força alinhamento à Esquerda */
.accessibility.left-top,
.accessibility.left-middle,
.accessibility.left-bottom {
    left: 10px;
    right: auto;
}

/* Força alinhamento à Direita (útil para sobrescrever estados anteriores) */
.accessibility.right-top,
.accessibility.right-middle,
.accessibility.right-bottom {
    right: 10px;
    left: auto;
}

.accessibility div[vw] {
    transform: none !important;
    position: static !important;
    margin: 0 !important;
}
</style>
@endif
