<footer class="site-footer">
    <div class="container">
        <div class="footer-inner">
            <div class="footer-brand footer-column">
                <span class="logo">{{ setting('site_name', 'Lunar Base') }}</span>
                <p>{{ setting('site_description', 'Laravel admin starter kit') }}</p>
            </div>

            @if($termsAndPrivacy['terms'] || $termsAndPrivacy['privacy'])
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
                </ul>
            </div>
            @endif

            @php
            $socials = settingsGroup('social');
            $showSocial = !!count($socials);
            $socials = array_filter($socials);
            @endphp

            @if($showSocial && count($socials))
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
            <p>&copy; {{ date('Y') }} {{ setting('general.site_name') }}. Todos os direitos reservados.</p>
        </div>
    </div>
</footer>
