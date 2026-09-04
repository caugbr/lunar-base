@props([
    'message' => null,
    'type' => null,
])

@php
if(!empty($message) && empty($type)) {
    $type = 'info';
}

if (!function_exists('addLinkIcon')) {
    function addLinkIcon($text) {
        if(str_contains($text, '<a ')) {
            return '<span class="link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right-icon lucide-chevron-right lucid-icon"><path d="m9 18 6-6-6-6"/></svg></span>';
        }
        return '';
    }
}
@endphp

@if($msg = $type == 'success' ? $message : session('success'))
    <div class="admin-alert admin-alert-success">
        <x-lucide-circle-check class="lucid-icon" />
        {!! $msg !!}
        {!! addLinkIcon($msg) !!}
    </div>
@endif

@if($msg = $type == 'warning' ? $message : session('warning'))
    <div class="admin-alert admin-alert-warning">
        <x-lucide-circle-alert class="lucid-icon" />
        {!! $msg !!}
        {!! addLinkIcon($msg) !!}
    </div>
@endif

@if($msg = $type == 'info' ? $message : session('info'))
    <div class="admin-alert admin-alert-info">
        <x-lucide-info class="lucid-icon" />
        {!! $msg !!}
        {!! addLinkIcon($msg) !!}
    </div>
@endif

@php
$error = $type == 'error' ? $message : ($errors->any() ? $errors->first() : session('error')) ?? null;
@endphp

@if($error)
    <div class="admin-alert admin-alert-error">
        <x-lucide-circle-x class="lucid-icon" />
        {!! $error !!}
        {!! addLinkIcon($error) !!}
    </div>
@endif
