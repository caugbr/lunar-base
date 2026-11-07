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
            return '<span class="link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucid-icon"><path d="m9 18 6-6-6-6"/></svg></span>';
        }
        return '';
    }
}

// Consolida todas as mensagens pontuais a serem exibidas
$alerts = [];

// Mensagem passada explicitamente via prop
if ($message) {
    $alerts[] = [
        'type' => $type ?? 'info',
        'message' => $message,
        'noticeId' => $noticeId,
    ];
} else {
    // Mapeamento sem exclusao mutua: exibe todas as mensagens presentes na sessao
    $types = ['error', 'warning', 'success', 'info'];

    foreach ($types as $t) {
        if (session()->has($t)) {
            $alerts[] = [
                'type' => $t,
                'message' => session($t),
                'noticeId' => null,
            ];
        }
    }

    if (isset($errors) && $errors->any() && !session()->has('error')) {
        $alerts[] = [
            'type' => 'error',
            'message' => $errors->first(),
            'noticeId' => null,
        ];
    }
}
@endphp

{{-- Renderiza os avisos persistentes do banco --}}
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
                <button type="button" class="transparent-btn alert-dismiss-btn dismiss-check">
                    @php
                    $closeText = $pNotice->close_text ?? 'Ok, ciente';
                    @endphp
                    <span class="close-txt">{{ $closeText }}</span>
                    <x-lucide-check class="lucid-icon" />
                </button>
                {!! $pNotice->type === 'custom' ? nl2br($pNotice->message) : $pNotice->message !!}
                {!! addLinkIcon($pNotice->message) !!}
            </span>

            <span class="dismiss-x-check">
                <button type="button" class="transparent-btn alert-dismiss-btn dismiss-x" title="Dispensar aviso">
                    <x-lucide-x class="lucid-icon" />
                </button>
                <button type="button" class="transparent-btn alert-dismiss-btn dismiss-check">
                    @php
                    $closeText = $pNotice->close_text ?? 'Ok, ciente';
                    @endphp
                    <span class="close-txt">{{ $closeText }}</span>
                    <x-lucide-check class="lucid-icon" />
                </button>
            </span>
        </div>
    @endforeach
@endif

{{-- Renderiza todas as mensagens de sessao acumuladas --}}
@foreach($alerts as $alertItem)
    <div
        class="admin-alert admin-alert-{{ $alertItem['type'] }}"
        @if($alertItem['noticeId']) data-persistent-id="{{ $alertItem['noticeId'] }}" @endif
    >
        @if($alertItem['type'] === 'success')
            <x-lucide-circle-check class="lucid-icon" />
        @elseif($alertItem['type'] === 'warning')
            <x-lucide-circle-alert class="lucid-icon" />
        @elseif($alertItem['type'] === 'error')
            <x-lucide-circle-x class="lucid-icon" />
        @else
            <x-lucide-info class="lucid-icon" />
        @endif

        <span class="alert-content">
            {!! $alertItem['message'] !!}
            {!! addLinkIcon($alertItem['message']) !!}
        </span>

        @if($dismissible)
            <button type="button" class="transparent-btn alert-dismiss-btn" title="Dispensar aviso">
                <x-lucide-x class="lucid-icon" />
            </button>
        @endif
    </div>
@endforeach

{{-- Script unico de descarte mantido --}}
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
