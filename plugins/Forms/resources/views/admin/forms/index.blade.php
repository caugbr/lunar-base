@extends('admin.layout')
@section('header_title', 'Formulários')
@section('header_subtitle', 'Gerencie os formulários dinâmicos do site')
@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h2><x-lucide-form class="lucid-icon" /> Lista de Formulários</h2>
        <a href="{{ route('admin.forms.create') }}" class="admin-btn admin-btn-primary">
            <x-lucide-plus class="lucid-icon" /> <span>Novo Formulário</span>
        </a>
    </div>

    {{-- Filtros --}}
    <form method="GET" action="{{ route('admin.forms.index') }}" class="admin-filters">
        <div class="admin-filters-row">
            <div class="admin-filter-group">
                <input type="text" name="title" value="{{ request('title') }}" class="admin-filter-input" placeholder="Buscar por título...">
            </div>
            <div class="admin-filter-group">
                <select name="is_active" class="admin-filter-select">
                    <option value="">Todos os status</option>
                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Ativos</option>
                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inativos</option>
                </select>
            </div>
            <div class="admin-filter-actions">
                <button type="submit" class="admin-btn admin-btn-primary">
                    <x-lucide-filter class="lucid-icon" /> Filtrar
                </button>
                <a href="{{ route('admin.forms.index') }}" class="admin-btn admin-btn-secondary">
                    <x-lucide-brush-cleaning class="lucid-icon" /> Limpar
                </a>
            </div>
        </div>
    </form>

    <div class="table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Slug</th>
                    <th>Título</th>
                    <th>E-mail de destino</th>
                    <th>Status</th>
                    <th>Respostas</th>
                    <th>Criado em</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($forms as $form)
                <tr>
                    <td><code>{{ $form->slug }}</code></td>
                    <td>{{ $form->title ?? '—' }}</td>
                    <td>{{ $form->email_to ?? '—' }}</td>
                    <td>
                        @if($form->is_active)
                            <span style="background: #d1fae5; color: #065f46; padding: 2px 8px; border-radius: 9999px; font-size: 0.75rem; font-weight: 600;">Ativo</span>
                        @else
                            <span style="background: #fee2e2; color: #991b1b; padding: 2px 8px; border-radius: 9999px; font-size: 0.75rem; font-weight: 600;">Inativo</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('admin.forms.submissions.index', $form->id) }}" style="color: #3b82f6; font-weight: 500; text-decoration: none;">
                            {{ $form->submissions_count }} {{ Str::plural('resposta', $form->submissions_count) }}
                        </a>
                    </td>
                    <td>{{ $form->created_at->format('d/m/Y H:i') }}</td>
                    <td class="admin-actions">
                        <div>
                            <a href="{{ route('admin.forms.submissions.index', $form->id) }}" class="admin-btn admin-btn-secondary" style="padding: 4px 12px;" title="Ver respostas">
                                <x-lucide-inbox class="lucid-icon" />
                            </a>
                            <a href="{{ route('admin.forms.edit', $form->id) }}" class="admin-btn admin-btn-secondary" style="padding: 4px 12px;" title="Editar formulário">
                                <x-lucide-pencil class="lucid-icon" />
                            </a>
                            <a href="{{ route('admin.forms.show', $form->id) }}" class="admin-btn admin-btn-secondary" style="padding: 4px 12px;" title="Ver formulário">
                                <x-lucide-eye class="lucid-icon" />
                            </a>
                            <form method="POST" action="{{ route('admin.forms.destroy', $form->id) }}" data-confirm="Remover este formulário e todas as suas respostas?" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="admin-btn admin-btn-danger" style="padding: 4px 12px;" title="Excluir formulário">
                                    <x-lucide-trash-2 class="lucid-icon" />
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="admin-text-center admin-text-muted">
                        Nenhum formulário cadastrado
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="admin-pagination">
        {{ $forms->appends(request()->query())->links() }}
    </div>
</div>
@endsection
