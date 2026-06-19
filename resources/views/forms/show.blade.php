{{-- Ajuste 'layouts.app' para o nome do seu layout público (ex: 'public.layout', 'app', etc) --}}
@extends('layouts.app')

@section('title', $form->title)

@section('content')
<div class="container" style="max-width: 800px; margin: 0 auto; padding: 2rem 1rem;">

    <h1 style="font-size: 2rem; font-weight: 600; color: #1f2937; margin-bottom: 1.5rem;">
        {{ $form->title }}
    </h1>

    {{-- Mensagem de sucesso após o envio --}}
    @if(session('success'))
        <div style="background: #d1fae5; color: #065f46; padding: 1rem; border-radius: 0.375rem; margin-bottom: 1.5rem; border: 1px solid #a7f3d0;">
            {{ session('success') }}
        </div>
    @endif

    {{-- Início do Formulário --}}
    <form action="{{ route('public.forms.submit', $form->slug) }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- AQUI ESTÁ A MÁGICA: Reaproveitamos o mesmo partial da Admin! --}}
        @include('forms.partials.fields', ['fields' => $form->fields_schema])

        <div style="margin-top: 2rem;">
            <button type="submit" style="background: #3b82f6; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 0.375rem; font-weight: 500; cursor: pointer; font-size: 1rem;">
                Enviar Mensagem
            </button>
        </div>
    </form>

</div>
@endsection
