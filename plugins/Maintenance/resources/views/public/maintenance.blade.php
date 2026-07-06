<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ setting('maintenance.maintenance_title', 'Site em Manutenção') }}</title>

    @php
    $skin = config('admin.skin');
    $varsFile = $skin === 'default' ? 'css/admin/vars.css' : "css/admin/skins/vars-{$skin}.css";
    @endphp
    <link rel="stylesheet" href="{{ asset($varsFile) }}">
    <link rel="stylesheet" href="{{ asset('css/admin/admin.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/maintenance/css/maintenance.css') }}">
</head>
<body class="maintenance-body"{!! setting('site_theme') ? ' data-theme="' . setting('site_theme') . '"' : '' !!}>


    <div class="maintenance-card">
        <div class="maintenance-icon-wrapper">
            {{-- <x-lucide-wrench class="lucid-icon" /> --}}
            <x-dynamic-component :component="'lucide-' . setting('maintenance.maintenance_icon')" class="lucid-icon" />
        </div>

        <h1 class="maintenance-title">
            {{ setting('maintenance.maintenance_title', 'Site em Manutenção') }}
        </h1>

        <p class="maintenance-text">
            {!! nl2br(e(setting('maintenance.maintenance_text', 'Estamos trabalhando em novidades e melhorias no nosso site. Voltamos em instantes!'))) !!}
        </p>

        <div class="maintenance-footer">
            {{-- <x-lucide-settings class="lucid-icon" /> --}}
            @php
            $footerIcon = setting('maintenance.maintenance_footer_icon');
            @endphp
            @if($footerIcon)
            <x-dynamic-component :component="'lucide-' . $footerIcon" class="lucid-icon" />
            @endif
            @php
            $footerText = setting('maintenance.maintenance_footer_text');
            @endphp
            @if($footerText)
            <span>{{ $footerText }}</span>
            @else
            <span>Equipe {{ setting('general.site_name', 'Lunar Base') }}</span>
            @endif
        </div>
    </div>

    @stack('scripts')
</body>
</html>
