@once
    <!-- CSS da "moldura" do plugin (Mac ou Plain) -->
    <link rel="stylesheet" href="{{ asset('plugins/prismhighlight/css/prismhighlight.css') }}">

    @php
        // Mapeia a escolha do tema para o arquivo CSS correto no CDNjs (v1.29.0)
        $themeCss = match($theme) {
            'tomorrow'       => 'https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css',
            'okaidia'        => 'https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-okaidia.min.css',
            'dracula'        => 'https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-dracula.min.css',
            'one-dark'       => 'https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-one-dark.min.css',
            'solarizedlight' => 'https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-solarizedlight.min.css',
            default          => 'https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism.min.css', // Fallback
        };
    @endphp

    <!-- Carrega o tema escolhido dinamicamente -->
    <link rel="stylesheet" href="{{ $themeCss }}">

    <!-- Prism.js Core e Componentes de Linguagem (mantidos iguais) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-markup.min.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-css.min.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-javascript.min.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-php.min.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-scss.min.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-bash.min.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-sql.min.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-json.min.js" defer></script>
@endonce

@php
    $showHeader = ($style === 'mac') || !empty($title);
@endphp

<div class="prism-code-block theme-{{ $theme }} style-{{ $style }}">
    @if($showHeader)
    <div class="prism-header">
        @if($style === 'mac')
        <div class="prism-dots">
            <span class="dot red"></span>
            <span class="dot yellow"></span>
            <span class="dot green"></span>
        </div>
        @endif

        @if(!empty($title))
            <div class="prism-title">{{ $title }}</div>
        @endif
    </div>
    @endif

    <div class="prism-body">
        <pre><code class="language-{{ $lang }}">{{ $content }}</code></pre>
    </div>
</div>
