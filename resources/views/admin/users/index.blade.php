@extends('admin.layout')

@section('header_title', 'Usuários')
@section('header_subtitle', 'Gerencie administradores e parceiros')

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h2><x-lucide-users class="lucid-icon" /> Usuários</h2>
        <a href="{{ route('admin.users.create') }}" class="admin-btn admin-btn-primary">
            <x-lucide-plus class="lucid-icon" /> <span>Novo Usuário</span>
        </a>
    </div>

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
                    {{-- <td>
                        @if($user->role == 'admin')
                            <span class="admin-badge admin-badge-admin">Admin</span>
                        @else
                            <span class="admin-badge admin-badge-partner">Parceiro</span>
                        @endif
                    </td> --}}
                    <td>
                        @if($user->isAdmin())
                            <span class="admin-badge admin-badge-admin">Admin</span>
                        @else
                            <span class="admin-badge admin-badge-editor">Editor</span>
                        @endif
                    </td>
                    {{-- <td>
                        @if($user->partner)
                            <a href="{{ route('admin.partners.edit', $user->partner->id) }}" class="admin-btn-link">Ver dados</a>
                        @else
                            -
                        @endif
                    </td> --}}
                    <td>{{ $user->created_at->format('d/m/Y') }}</td>
                    <td class="admin-actions">
                        <div>
                            <a href="{{ route('admin.users.edit', $user->id) }}" class="admin-btn admin-btn-secondary" style="padding: 4px 12px;">
                                <x-lucide-pencil class="lucid-icon" />
                            </a>

                            @if($user->hasTwoFactorEnabled())
                                @if(setting('auth.2fa_enabled'))
                                <form method="POST" action="{{ route('admin.users.two-factor.disable', $user->id) }}" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="admin-btn admin-btn-secondary" style="padding: 4px 12px;" onclick="return confirm('Desativar 2FA de {{ $user->name }}?')">
                                        <x-lucide-shield-off class="lucid-icon" />
                                    </button>
                                </form>
                                @else
                                    <button type="button" disabled class="admin-btn admin-btn-secondary" style="padding: 4px 12px;">
                                        <x-lucide-shield-off class="lucid-icon" />
                                    </button>
                                @endif
                            @endif

                            @if($user->id !== auth()->id())
                            <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="admin-btn admin-btn-danger" style="padding: 4px 12px;" onclick="return confirm('Remover este usuário?')"><x-lucide-trash-2 class="lucid-icon" /></button>
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
        {{ $users->links('pagination::custom') }}
    </div>
</div>
@endsection
