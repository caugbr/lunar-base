{{-- Injeta folha de estilos limpa baseada na diretriz --}}
@once
{{-- @push('styles') --}}
<link rel="stylesheet" href="{{ asset('plugins/faq/css/faq.css') }}">
{{-- @endpush --}}
@endonce

<div class="faq-card" id="faq-{{ $faq['slug'] }}">
    @if(!empty($faq['title']))
        <div class="faq-card-header">
            <h3 class="faq-card-title">
                <x-lucide-file-question-mark class="lucid-icon" />
                {{ $faq['title'] }}
            </h3>
        </div>
    @endif

    <div class="faq-items-list">
        @foreach($faq['items'] as $item)
            @if(!empty($item['question']) && !empty($item['answer']))
                <details class="faq-item">
                    <summary class="faq-question">
                        <span>{{ $item['question'] }}</span>
                        <span class="faq-toggle-icon"></span>
                    </summary>
                    {{-- 💡 O segredo da animação está na estrutura destas duas classes abaixo --}}
                    <div class="faq-answer">
                        <div class="faq-answer-content">
                            {!! nl2br(e($item['answer'])) !!}
                        </div>
                    </div>
                </details>
            @endif
        @endforeach
    </div>
</div>
