@extends('admin.layout')
@section('header_title', 'Perguntas Frequentes (FAQ)')
@section('header_subtitle', 'Crie centrais de dúvidas e guias de suporte por meio de listas sanfonadas')
@section('content')

{{-- Injeta a folha de estilos administrativa de forma isolada --}}
@once
@push('styles')
<link rel="stylesheet" href="{{ asset('plugins/faq/css/faq-admin.css') }}">
@endpush
@endonce

<div class="admin-card">
    <div class="admin-card-header">
        <h2><x-lucide-file-question-mark class="lucid-icon" /> Minhas Centrais de FAQ</h2>
        {{-- <a href="#quick_create_form" class="admin-btn admin-btn-primary" onclick="event.preventDefault(); document.getElementById('quick_create_form').scrollIntoView({ behavior: 'smooth' })">
            <x-lucide-plus class="lucid-icon" /> <span>Novo FAQ</span>
        </a> --}}
    </div>

    {{-- Formulário Rápido de Criação --}}
    <form method="POST" action="{{ route('admin.faq.store') }}" id="quick_create_form" class="faq-quick-create">
        @csrf
        <h3><x-lucide-plus class="lucid-icon" /> Criar Novo FAQ</h3>
        <div class="admin-form-row">
            <div class="form-group" style="flex: 2;">
                <label for="title">Nome / Título Administrativo *</label>
                <input type="text" name="title" id="title" placeholder="Ex: Dúvidas Frequentes" required class="form-input">
            </div>
            <div class="form-group" style="flex: 2;">
                <label for="slug">Slug (Código de Incorporação) *</label>
                <input type="text" name="slug" id="slug" placeholder="Ex: faq-geral" required class="form-input">
                <input type="hidden" name="items_json" value="[]">
            </div>
            <div class="form-group btn-form-group">
                <button type="submit" class="admin-btn admin-btn-primary btn-submit">
                    <x-lucide-save class="lucid-icon" /> Criar FAQ
                </button>
            </div>
        </div>
    </form>

    {{-- Listagem de FAQs Existentes --}}
    <div class="table-wrap" id="quick-create-section">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Título</th>
                    <th>Shortcode de Incorporação</th>
                    <th>Qtd. Perguntas</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($faqs as $faq)
                <tr>
                    <td><strong>{{ $faq['title'] }}</strong></td>
                    <td><code class="faq-code-slug">[faq slug="{{ $faq['slug'] }}"]</code></td>
                    <td>{{ count($faq['items'] ?? []) }} pergunta(s)</td>
                    <td class="admin-actions">
                        <div>
                            <a href="{{ route('admin.faq.edit', $faq['slug']) }}" class="admin-btn admin-btn-secondary faq-table-actions" title="Editar perguntas">
                                <x-lucide-pencil class="lucid-icon" />
                            </a>
                            <form method="POST" action="{{ route('admin.faq.destroy', $faq['slug']) }}" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="admin-btn admin-btn-danger faq-table-actions" onclick="return confirm('Remover este FAQ de forma permanente?')" title="Excluir FAQ">
                                    <x-lucide-trash-2 class="lucid-icon" />
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="admin-text-center admin-text-muted faq-table-empty">
                        Nenhuma central de FAQ cadastrada ainda. Use o formulário acima para iniciar.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    // Gerador de Slug automático
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
</script>
@endsection
