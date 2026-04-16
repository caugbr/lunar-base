@extends('admin.layout')

@section('header_title', 'Dashboard')
@section('header_subtitle', 'Visão geral do sistema')

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h2>
            <x-lucide-chart-column-big class="lucid-icon" />
            Visão Geral
        </h2>
    </div>

    {{-- Stats Grid --}}
    <div class="admin-stats">
        {{-- Total de Usuários --}}
        <div class="admin-stat-card">
            {{-- <div class="stat-icon">👥</div>
            <div class="stat-info"> --}}
                <h3>Total de Usuários</h3>
                <div class="value">{{ $totalUsers }}</div>
                <div class="stat-detail">
                    <span>Admin: {{ $totalAdmins }}</span>
                    <span>Editor: {{ $totalEditors }}</span>
                    <span>Viewer: {{ $totalViewers }}</span>
                </div>
            {{-- </div> --}}
        </div>

        {{-- Total de Páginas --}}
        <div class="admin-stat-card">
            {{-- <div class="stat-icon">📄</div> --}}
            <div class="stat-info">
                <h3>Total de Páginas</h3>
                <div class="value">{{ $totalPages }}</div>
                <div class="stat-detail">
                    <span>Publicadas: {{ $publishedPages }}</span>
                    <span>Rascunho: {{ $draftPages }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Últimas Páginas --}}
    <h2 style="margin-top: 2rem; margin-bottom: 1rem;">Últimas Páginas Criadas</h2>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Título</th>
                <th>Slug</th>
                <th>Autor</th>
                <th>Status</th>
                <th>Data</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentPages as $page)
            <tr>
                <td>{{ $page->title }}</td>
                <td><code>{{ $page->slug }}</code></td>
                <td>{{ $page->author?->name ?? '-' }}</td>
                <td>
                    @if($page->status === 'published')
                        <span class="admin-badge admin-badge-active">Publicado</span>
                    @else
                        <span class="admin-badge admin-badge-inactive">Rascunho</span>
                    @endif
                </td>
                <td>{{ $page->created_at->format('d/m/Y H:i') }}</td>
                <td class="admin-actions">
                    <a href="{{ route('admin.pages.edit', $page->id) }}" class="admin-btn admin-btn-secondary" style="padding: 4px 12px;">
                        <x-lucide-pencil class="lucid-icon" />
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="admin-text-center admin-text-muted">Nenhuma página criada ainda</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Últimos Usuários --}}
    <h2 style="margin-top: 2rem; margin-bottom: 1rem;">Últimos Usuários Cadastrados</h2>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Nome</th>
                <th>E-mail</th>
                <th>Role</th>
                <th>Data</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentUsers as $user)
            <tr>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>
                    @if($user->role?->slug === 'admin')
                        <span class="admin-badge admin-badge-active">Admin</span>
                    @elseif($user->role?->slug === 'editor')
                        <span class="admin-badge admin-badge-warning">Editor</span>
                    @else
                        <span class="admin-badge admin-badge-secondary">Visualizador</span>
                    @endif
                </td>
                <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                <td class="admin-actions">
                    <a href="{{ route('admin.users.edit', $user->id) }}" class="admin-btn admin-btn-secondary" style="padding: 4px 12px;">
                        <x-lucide-pencil class="lucid-icon" />
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="admin-text-center admin-text-muted">Nenhum usuário cadastrado ainda</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
