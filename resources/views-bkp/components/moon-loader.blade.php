@props(['size', 'glow', 'dark', 'duration'])

@php
$vars = '';
if (!empty($size)) $vars .= "--moon-width: {$size}; ";
if (!empty($glow)) $vars .= "--glow-color: {$glow}; ";
if (!empty($dark)) $vars .= "--dark-color: {$dark}; ";
if (!empty($duration)) $vars .= "--duration: {$duration}; ";
if (!empty($vars)) $vars = ' style="' . trim($vars) . '"';
@endphp

@once
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/moon-loader.css') }}">
    @endpush
@endonce

<div class="moon-loader-wrapper"{!! $vars !!}>
    <div class="moon-loader" role="status" aria-label="Carregando"></div>
</div>
