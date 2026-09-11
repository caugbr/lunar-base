@extends('admin.layout')

@section('header_title', 'Usuários')
@section('header_subtitle', 'Gerencie administradores e parceiros')

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h2><x-lucide-users class="lucid-icon" /> Usuários ({{ count($users) }})</h2>
        <a href="{{ route('admin.users.create') }}" class="admin-btn admin-btn-primary">
            <x-lucide-plus class="lucid-icon" /> <span>Novo Usuário</span>
        </a>
    </div>

    <!-- Filtros -->
    <form method="GET" action="{{ route('admin.users.index') }}" class="admin-filters">
        <div class="admin-filters-row">
            <div class="admin-filter-group">
                <input type="text" name="name" value="{{ request('name') }}" class="admin-filter-input" placeholder="Buscar por nome...">
            </div>
            <div class="admin-filter-group">
                <input type="text" name="email" value="{{ request('email') }}" class="admin-filter-input" placeholder="Buscar por email...">
            </div>
            <div class="admin-filter-group">
                <select name="role" class="admin-filter-select">
                    <option value="">Todos os perfis</option>
                    @foreach($roles as $role_id => $role)
                    <option value="{{ $role_id }}"{{ request('role') == $role_id ? ' selected' : '' }}>{{ $role['name'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="admin-filter-actions">
                <button type="submit" class="admin-btn admin-btn-primary">
                    <x-lucide-filter class="lucid-icon" /> Filtrar
                </button>
                <a href="{{ route('admin.users.index') }}" class="admin-btn admin-btn-secondary">
                    <x-lucide-brush-cleaning class="lucid-icon" /> Limpar
                </a>
            </div>
        </div>
    </form>

    <div class="table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Perfil</th>
                    {{-- <th>Parceiro</th> --}}
                    <th>Data</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        @if($user->isAdmin())
                            <span class="admin-badge admin-badge-admin">Admin</span>
                        @else
                            <span class="admin-badge admin-badge-editor">Editor</span>
                        @endif
                    </td>
                    <td>{{ $user->created_at->format('d/m/Y') }}</td>
                    <td class="admin-actions">
                        <div>
                            <a href="{{ route('admin.users.edit', $user->id) }}" class="admin-btn admin-btn-secondary">
                                <x-lucide-pencil class="lucid-icon" />
                            </a>

                            @if($user->hasTwoFactorEnabled())
                                @if(setting('auth.2fa_enabled'))
                                <form method="POST" action="{{ route('admin.users.two-factor.disable', $user->id) }}" style="display: inline;" data-confirm="Desativar 2FA de {{ $user->name }}?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="admin-btn admin-btn-secondary">
                                        <x-lucide-shield-off class="lucid-icon" />
                                    </button>
                                </form>
                                @else
                                    <button type="button" disabled class="admin-btn admin-btn-secondary">
                                        <x-lucide-shield-off class="lucid-icon" />
                                    </button>
                                @endif
                            @endif

                            @if($user->id !== auth()->id())
                            <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}" data-confirm="Remover este usuário?" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="admin-btn admin-btn-danger">
                                    <x-lucide-trash-2 class="lucid-icon" />
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="admin-pagination">
        {{ $users->links() }}
    </div>
</div>
@endsection
