<link rel="stylesheet" href="{{ asset('css/forms.css') }}">

<div class="dynamic-form-wrapper" id="form-{{ $form->slug }}">

    {{-- Mensagem de sucesso (opcional, mas recomendado) --}}
    @if(session('success'))
        <div style="background: #d1fae5; color: #065f46; padding: 1rem; border-radius: 0.375rem; margin-bottom: 1.5rem; border: 1px solid #a7f3d0;">
            {{ session('success') }}
        </div>
    @endif

    @if($form->title)
    <h2>{{ $form->title }}</h2>
    @endif

    <form action="{{ route('public.forms.submit', $form->slug) }}" method="POST" enctype="multipart/form-data" id="form-{{ $form->slug }}">
        @csrf

        {{-- A MÁGICA: Reaproveitamos o mesmo partial da Admin e do Show! --}}
        @include('forms.partials.fields', ['fields' => $form->fields_schema])

        <div class="buttons">
            <button type="submit">{{ $form->submit_button_label ?? 'Enviar' }}</button>
        </div>
    </form>
</div>
