@extends('admin.layout')
@section('header_title', 'Menus de Navegação')
@section('header_subtitle', 'Crie e organize as árvores de links do seu site')
@section('content')

<div class="admin-card">
    <div class="admin-card-header">
        <h2><x-lucide-menu class="lucid-icon" /> Meus Menus</h2>
    </div>

    {{-- Formulário Rápido de Criação --}}
    <form method="POST" action="{{ route('admin.menus.store') }}" style="margin-bottom: 2rem; background: var(--color-bg-dark); padding: 1.5rem; border-radius: 8px; border: 1px solid var(--color-border);">
        @csrf
        <h3 style="font-size: 1rem; margin-bottom: 1rem;"><x-lucide-plus class="lucid-icon" /> Criar Novo Menu</h3>
        <div class="admin-form-row">
            <div class="form-group">
                <label for="name">Nome do Menu *</label>
                <input type="text" name="name" id="name" placeholder="Ex: Menu Principal" required class="form-input">
            </div>
            <div class="form-group">
                <label for="slug">Slug (URL) *</label>
                <input type="text" name="slug" id="slug" placeholder="Ex: main-menu" required class="form-input">
            </div>
            <div class="form-group" style="align-self: flex-end; flex: 0;">
                <button type="submit" class="admin-btn admin-btn-primary" style="white-space: nowrap">
                    <x-lucide-save class="lucid-icon" /> Criar Menu
                </button>
            </div>
        </div>
    </form>

    {{-- Listagem de Menus Existentes --}}
    <div class="table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Slug de Associação (Hook)</th>
                    <th>Qtd. Links</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($menus as $menu)
                <tr>
                    <td><strong>{{ $menu->name }}</strong></td>
                    <td><code>{{ $menu->slug }}</code></td>
                    <td>{{ $menu->items()->count() }} link(s)</td>
                    <td class="admin-actions">
                        <div>
                            <a href="{{ route('admin.menus.edit', $menu->id) }}" class="admin-btn admin-btn-secondary" style="padding: 4px 12px;" title="Definir links">
                                <x-lucide-pencil class="lucid-icon" />
                            </a>
                            <form method="POST" action="{{ route('admin.menus.destroy', $menu->id) }}" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="admin-btn admin-btn-danger" style="padding: 4px 12px;" onclick="return confirm('Remover este menu e todos os seus links?')" title="Excluir menu">
                                    <x-lucide-trash-2 class="lucid-icon" />
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="admin-text-center admin-text-muted" style="padding: 30px;">
                        Nenhum menu cadastrado ainda. Use o formulário acima para criar o primeiro.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    // Gerador de Slug automático
    document.getElementById('name').addEventListener('input', function() {
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
