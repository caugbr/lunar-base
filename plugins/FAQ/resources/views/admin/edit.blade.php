@extends('admin.layout')
@section('header_title', 'FAQ')
@section('header_subtitle', isset($faq) ? 'Editar Itens de: ' . $faq['title'] : 'Criar Nova FAQ')
@section('content')

{{-- Injeta a folha de estilos administrativa de forma isolada --}}
@once
@push('styles')
<link rel="stylesheet" href="{{ asset('plugins/faq/css/faq-admin.css') }}">
@endpush
@endonce

<div class="admin-card" x-data="faqEditor({{ json_encode($faq['items'] ?? []) }})" x-cloak>
    <form method="POST" action="{{ isset($faq) ? route('admin.faq.update', $faq['slug']) : route('admin.faq.store') }}" id="faq_form">
        @csrf
        @if(isset($faq)) @method('PUT') @endif

        <div class="admin-card-header">
            <h2><x-lucide-file-question-mark class="lucid-icon" /> Propriedades do FAQ</h2>
            <a href="{{ route('admin.faq.index') }}" class="admin-btn admin-btn-secondary">
                <x-lucide-arrow-left class="lucid-icon" /> Voltar
            </a>
        </div>

        <div class="settings-group">
            <div class="admin-form-row">
                <div class="form-group" style="flex: 2;">
                    <label for="title">Título do Bloco *</label>
                    <input type="text" name="title" id="title" value="{{ old('title', $faq['title'] ?? '') }}" placeholder="Ex: Perguntas Gerais" required class="form-input">
                </div>
                <div class="form-group" style="flex: 2;">
                    <label for="slug">Slug (URL de Incorporação) *</label>
                    <input type="text" name="slug" id="slug" value="{{ old('slug', $faq['slug'] ?? '') }}" placeholder="Ex: geral" required class="form-input" @if(isset($faq)) readonly class="form-input faq-readonly-input" @endif>
                    <small class="form-help">Usado no shortcode de incorporação para carregar este FAQ.</small>
                </div>
            </div>
        </div>

        {{-- Lista de Itens --}}
        <div class="settings-group faq-sections-divider">
            <h3>
                <x-lucide-list-collapse class="lucid-icon" /> Perguntas e Respostas Cadastradas
            </h3>

            {{-- Lista Dinâmica Reativa via Alpine --}}
            <div class="faq-items-wrapper">
                <template x-for="(item, index) in items" :key="index">
                    <div class="faq-item-card">

                        {{-- Botão de Exclusão --}}
                        <button type="button" @click="removeItem(index)" class="faq-item-remove-btn" title="Excluir este bloco">
                            <x-lucide-trash-2 class="lucid-icon" style="color: #ef4444;" />
                        </button>

                        <div class="faq-item-index">
                            Pergunta #<span x-text="index + 1"></span>
                        </div>

                        <div class="form-group faq-item-form-group">
                            <label style="font-size: 0.8rem; font-weight: 600;">Pergunta</label>
                            <input type="text" x-model="item.question" placeholder="Digite o enunciado da dúvida..." class="form-input" required>
                        </div>

                        <div class="form-group faq-item-form-group-last">
                            <label style="font-size: 0.8rem; font-weight: 600;">Resposta</label>
                            <textarea x-model="item.answer" placeholder="Digite o texto de resposta..." class="form-input" rows="3" required></textarea>
                        </div>
                    </div>
                </template>

                {{-- Estado Vazio --}}
                <template x-if="items.length === 0">
                    <p class="faq-items-empty">
                        Nenhuma pergunta inserida. Clique no botão abaixo para adicionar a primeira!
                    </p>
                </template>
            </div>
        </div>

        {{-- Input oculto que transmite o JSON estruturado para o Controller --}}
        <input type="hidden" name="items_json" :value="JSON.stringify(items)">

        <div class="buttons faq-action-buttons">
            <button type="submit" class="admin-btn admin-btn-primary">
                <x-lucide-save class="lucid-icon" /> Salvar Modificações
            </button>
            <button type="button" @click="addItem()" class="admin-btn admin-btn-secondary">
                <x-lucide-plus class="lucid-icon" /> Adicionar Pergunta
            </button>
        </div>
    </form>
</div>

<x-lost-changes-warn selector="#faq_form" />

@push('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script>
    function faqEditor(initialItems) {
        return {
            items: initialItems || [],

            addItem() {
                this.items.push({
                    question: '',
                    answer: ''
                });
            },

            removeItem(index) {
                this.items.splice(index, 1);
            }
        }
    }

    // Gerador de Slug automático na criação
    @if(!isset($faq))
    document.getElementById('title').addEventListener('input', function() {
        const slugField = document.getElementById('slug');
        if (slugField && !slugField.dataset.manuallyEdited) {
            slugField.value = this.value.toString().toLowerCase()
                .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                .trim()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/[\s_-]+/g, '-')
                .replace(/^-+|-+$/g, '');
        }
    });
    document.getElementById('slug').addEventListener('input', function() {
        this.dataset.manuallyEdited = 'true';
    });
    @endif
</script>
@endpush
@endsection
