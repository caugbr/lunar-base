<link rel="stylesheet" href="{{ asset('css/public/vars.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/forms/css/dynamic-forms.css') }}">

<div class="dynamic-form-wrapper" id="form-{{ $form->slug }}">

    {{-- Mensagem de sucesso --}}
    @if(session('success'))
        <div class="success">
            {{ session('success') }}
        </div>
    @endif

    @if($form->title)
    <h2>{{ $form->title }}</h2>
    @endif

    <form action="{{ route('public.forms.submit', $form->slug) }}" method="POST" enctype="multipart/form-data" id="form-{{ $form->slug }}">
        @csrf

        @include('forms::public.partials.fields', ['fields' => $form->fields_schema])

        <div class="buttons">
            <button type="submit">{{ $form->submit_button_label ?? 'Enviar' }}</button>
        </div>
    </form>
</div>
