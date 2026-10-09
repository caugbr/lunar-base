@props([
    'message'     => null,
    'type'        => null,
    'dismissible' => true,
    'noticeId'    => null,
])

@php
if (!function_exists('addLinkIcon')) {
    function addLinkIcon($text) {
        if (str_contains($text, '<a ')) {
            return '<span class="link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right-icon lucide-chevron-right lucid-icon"><path d="m9 18 6-6-6-6"/></svg></span>';
        }
        return '';
    }
}

// Resolve a mensagem pontual (passada via prop ou da sessão)
$sessionMessage = $message;
$sessionType    = $type;

if (!$sessionMessage) {
    if (session()->has('success')) {
        $sessionMessage = session('success');
        $sessionType    = 'success';
    } elseif (session()->has('warning')) {
        $sessionMessage = session('warning');
        $sessionType    = 'warning';
    } elseif (session()->has('info')) {
        $sessionMessage = session('info');
        $sessionType    = 'info';
    } elseif (session()->has('error') || (isset($errors) && $errors->any())) {
        $sessionMessage = session('error') ?? $errors->first();
        $sessionType    = 'error';
    }
}
$sessionType = $sessionType ?? 'info';
@endphp

{{-- RENDERIZA OS AVISOS PERSISTENTES DO BANCO (Injetados pelo Composer) --}}
@if(isset($persistentNotices) && $persistentNotices->isNotEmpty() && !$message)
    @foreach($persistentNotices as $pNotice)
        @php
        $color = '';
        if ($pNotice->color ?? null && $pNotice->color !== '') {
            $color = " style=\"--box-color: {$pNotice->color}\"";
        }
        @endphp
        <div class="admin-alert admin-alert-{{ $pNotice->type }}" data-persistent-id="{{ $pNotice->id }}"{!! $color !!}>
            @if($pNotice->icon ?? null)
                <x-dynamic-component component="lucide-{{ $pNotice->icon }}" class="lucid-icon" />
            @elseif($pNotice->type === 'success')
                <x-lucide-circle-check class="lucid-icon" />
            @elseif($pNotice->type === 'warning')
                <x-lucide-circle-alert class="lucid-icon" />
            @elseif($pNotice->type === 'error')
                <x-lucide-circle-x class="lucid-icon" />
            @else
                <x-lucide-info class="lucid-icon" />
            @endif

            <span class="alert-content">
                {!! $pNotice->type === 'custom' ? nl2br($pNotice->message) : $pNotice->message !!}
                {!! addLinkIcon($pNotice->message) !!}
            </span>

            <span class="dismiss-x-check">
                <button type="button" class="transparent-btn alert-dismiss-btn dismiss-x" title="Dispensar aviso">
                    <x-lucide-x class="lucid-icon" />
                </button>
                <button type="button" class="transparent-btn alert-dismiss-btn dismiss-check">
                    Ok, ciente!
                    <x-lucide-check class="lucid-icon" />
                </button>
            </span>
        </div>
    @endforeach
@endif

{{-- RENDERIZA A MENSAGEM COMUM DE SESSÃO OU MANUAL --}}
@if($sessionMessage)
    <div
        class="admin-alert admin-alert-{{ $sessionType }}"
        @if($noticeId) data-persistent-id="{{ $noticeId }}" @endif
    >
        @if($sessionType === 'success')
            <x-lucide-circle-check class="lucid-icon" />
        @elseif($sessionType === 'warning')
            <x-lucide-circle-alert class="lucid-icon" />
        @elseif($sessionType === 'error')
            <x-lucide-circle-x class="lucid-icon" />
        @else
            <x-lucide-info class="lucid-icon" />
        @endif

        <span class="alert-content">
            {!! $sessionMessage !!}
            {!! addLinkIcon($sessionMessage) !!}
        </span>

        @if($dismissible)
            <button type="button" class="transparent-btn alert-dismiss-btn" title="Dispensar aviso">
                <x-lucide-x class="lucid-icon" />
            </button>
        @endif
    </div>
@endif

{{-- SCRIPT ÚNICO DE DESCARTE --}}
<script>
    if (!window.__adminAlertDismissInitialized) {
        window.__adminAlertDismissInitialized = true;

        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.alert-dismiss-btn');
            if (!btn) return;

            const alert = btn.closest('.admin-alert');
            if (!alert) return;

            const persistentId = alert.dataset.persistentId;

            if (persistentId) {
                const token = '{{ csrf_token() }}';

                fetch(`/admin/notices/${persistentId}/dismiss`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    }
                }).catch(err => console.error('Erro ao dispensar aviso:', err));
            }

            alert.style.transition = 'opacity 0.25s ease, transform 0.25s ease, margin-bottom 0.25s ease 0.1s';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-4px)';
            const h = alert.getBoundingClientRect().height;
            alert.style.marginBottom = `-${h}px`;

            setTimeout(() => alert.remove(), 250);
        });
    }
</script>
