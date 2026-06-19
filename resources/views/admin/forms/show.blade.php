@extends('admin.layout')
@section('header_title', 'Formulários')
@section('header_subtitle', 'Pré-visualização: ' . $form->title)
@section('content')
<link rel="stylesheet" href="{{ asset('css/vars.css') }}">
<link rel="stylesheet" href="{{ asset('css/dynamic-forms.css') }}">
<div class="admin-card">
    <div class="admin-card-header">
        <h2><x-lucide-eye class="lucid-icon" /> Pré-visualização do Formulário</h2>
        <div class="top-buttons">
            {{-- Indicador visual de status --}}
            @if(!$form->is_active)
                <span style="background: #fee2e2; color: #991b1b; padding: 4px 12px; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; height: 26px;">
                    Inativo (Não visível publicamente)
                </span>
            @else
                <span style="background: #d1fae5; color: #065f46; padding: 4px 12px; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; height: 26px;">
                    Ativo
                </span>
            @endif
            <a href="{{ route('admin.forms.edit', $form->id) }}" class="admin-btn admin-btn-secondary">
                <x-lucide-arrow-left class="lucid-icon" /> Voltar
            </a>
        </div>
    </div>

    {{-- Área de renderização --}}
    <div class="form-wrapper" data-theme="light">
        <div class="theme-switcher">
            <x-switch name="theme-switch" id="theme-switch" active="Dark" inactive="Light" />
        </div>
        {{-- Reutilizamos a view de embed para mostrar exatamente como ficará no site --}}
        @include('forms.embed', ['form' => $form])
    </div>
</div>
<style>
    .form-wrapper {
        background-color: #222222;
        padding: 2rem;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        width: 1000px;
        max-width: 94%;
        margin: 0 auto;
        position: relative;
    }
    .form-wrapper[data-theme="light"] {
        background-color: #f1f1f1;
        border-color: #cccccc;
    }
    .theme-switcher {
        position: absolute;
        top: 10px;
        right: 20px;
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const switcher = document.querySelector('#theme-switch');
        if (switcher) {
            const wrapper = document.querySelector('.form-wrapper');
            switcher.addEventListener('input', function() {
                wrapper.dataset.theme = this.checked ? 'dark' : 'light';
            });
        }
    });
</script>
@endsection
