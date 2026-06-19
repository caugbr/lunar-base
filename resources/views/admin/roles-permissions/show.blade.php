@extends('admin.layout')

@section('header_title', 'Roles e Permissões')
@section('header_subtitle', 'Visualização do esquema de controle de acesso')

@section('content')
<x-admin-alert message="Para mudar esses valores, edite <code>config/rolesPermissions.php</code>" />
<div class="admin-card">
    <div class="admin-card-header">
        <h2>
            <x-lucide-file-text class="lucid-icon" />
            Níveis e permissões
        </h2>
    </div>
    @php
        $config = config('rolesPermissions');
        $roles = $config['roles'] ?? [];
        $permissionsByRole = $config['permissionsByRole'] ?? [];
        $permissionGroups = $config['permissionGroups'] ?? [];
    @endphp

    {{-- Cards dos Roles --}}
    <div class="roles-grid">
        @foreach($roles as $slug => $role)
            @php
                $rolePermissions = $permissionsByRole[$slug] ?? [];
                $permissionCount = count($rolePermissions);
            @endphp
            <div class="role-card">
                <div class="role-header">
                    <div class="role-badge role-{{ $slug }}">
                        {{ strtoupper($slug) }}
                    </div>
                    <h3 class="role-name">{{ $role['name'] }}</h3>
                    <span class="role-count">{{ $permissionCount }} {{ $permissionCount !== 1 ? 'permissões' : 'permissão' }}</span>
                </div>

                @if(isset($role['description']))
                    <p class="role-description">{{ $role['description'] }}</p>
                @endif

                <div class="permissions-list">
                    @forelse($rolePermissions as $permission)
                        @php
                            $permLabel = null;
                            foreach ($permissionGroups as $group) {
                                if (isset($group[$permission])) {
                                    $permLabel = $group[$permission];
                                    break;
                                }
                            }
                        @endphp
                        <div class="permission-item">
                            <x-lucide-check class="lucid-icon permission-check" />
                            <span class="permission-slug">{{ $permission }}</span>
                            @if($permLabel)
                                <span class="permission-label">{{ $permLabel }}</span>
                            @endif
                        </div>
                    @empty
                        <p class="no-permissions">Nenhuma permissão atribuída</p>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
</div>
<div class="admin-card">
    <div class="admin-card-header">
        <h2>
            <x-lucide-shield class="lucid-icon" />
            Matriz de Permissões
        </h2>
    </div>

    {{-- Tabela completa de referência --}}
    <div class="reference-section">
        {{-- <h3 class="reference-title">
            <x-lucide-shield class="lucid-icon" />
            Matriz de Permissões
        </h3> --}}

        <div class="matrix-table-wrapper">
            <table class="matrix-table">
                <thead>
                    <tr>
                        <th>Permissão</th>
                        @foreach($roles as $slug => $role)
                            <th class="role-col role-col-{{ $slug }}">{{ $role['name'] }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($permissionGroups as $groupKey => $groupPermissions)
                        <tr class="group-row">
                            <td colspan="{{ count($roles) + 1 }}" class="group-name">
                                {{ ucfirst($groupKey) }}
                            </td>
                        </tr>
                        @foreach($groupPermissions as $permSlug => $permLabel)
                            <tr>
                                <td class="perm-cell">
                                    <span class="perm-slug">{{ $permSlug }}</span>
                                    <span class="perm-desc">{{ $permLabel }}</span>
                                </td>
                                @foreach($roles as $slug => $role)
                                    @php
                                        $hasPerm = in_array($permSlug, $permissionsByRole[$slug] ?? [], true);
                                    @endphp
                                    <td class="role-col role-col-{{ $slug }}">
                                        @if($hasPerm)
                                            <x-lucide-check class="lucid-icon check-yes" />
                                        @else
                                            <span class="check-no">—</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>
@endsection

@push('styles')
<style>
    /* ===== Aviso de Config ===== */
    .config-notice {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 1rem 1.25rem;
        background-color: #fef3c7;
        border: 1px solid #f59e0b;
        border-radius: 0.5rem;
        margin-bottom: 1.5rem;
        color: #92400e;
    }

    .config-notice .lucid-icon {
        width: 20px;
        height: 20px;
        flex-shrink: 0;
        color: #f59e0b;
    }

    .config-notice code {
        background: rgba(0,0,0,0.05);
        padding: 0.125rem 0.375rem;
        border-radius: 0.25rem;
        font-family: ui-monospace, monospace;
        font-size: 0.875rem;
    }

    /* ===== Grid de Roles ===== */
    .roles-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2.5rem;
    }

    .role-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 0.75rem;
        padding: 1.5rem;
        transition: box-shadow 0.2s ease;
    }

    .role-card:hover {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }

    .role-header {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 0.75rem;
        flex-wrap: wrap;
    }

    .role-badge {
        font-size: 0.65rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
    }

    .role-badge.role-admin {
        background: #fee2e2;
        color: #991b1b;
    }

    .role-badge.role-editor {
        background: #dbeafe;
        color: #1e40af;
    }

    .role-badge.role-author {
        background: #dcfce7;
        color: #166534;
    }

    .role-badge.role-subscriber {
        background: #f3f4f6;
        color: #4b5563;
    }

    .role-name {
        font-size: 1.125rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
        flex-grow: 1;
    }

    .role-count {
        font-size: 0.75rem;
        color: #6b7280;
        background: #f3f4f6;
        padding: 0.25rem 0.5rem;
        border-radius: 9999px;
    }

    .role-description {
        font-size: 0.875rem;
        color: #6b7280;
        margin: 0 0 1rem 0;
        line-height: 1.5;
    }

    /* ===== Lista de Permissões ===== */
    .permissions-list {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .permission-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.375rem 0.5rem;
        background: #f9fafb;
        border-radius: 0.375rem;
        font-size: 0.875rem;
    }

    .permission-check {
        width: 14px;
        height: 14px;
        color: #22c55e;
        flex-shrink: 0;
    }

    .permission-slug {
        font-family: ui-monospace, monospace;
        font-size: 0.8rem;
        color: #374151;
    }

    .permission-label {
        color: #6b7280;
        font-size: 0.8rem;
        margin-left: auto;
    }

    .no-permissions {
        font-size: 0.875rem;
        color: #9ca3af;
        font-style: italic;
        margin: 0;
    }

    /* ===== Seção de Referência ===== */
    /* .reference-section {
        padding-top: 2rem;
    } */

    .reference-title {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 1.25rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 1.5rem;
    }

    .reference-title .lucid-icon {
        width: 20px;
        height: 20px;
        color: #6b7280;
    }

    /* ===== Tabela Matriz ===== */
    .matrix-table-wrapper {
        overflow-x: auto;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
    }

    .matrix-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.875rem;
        min-width: 600px;
    }

    .matrix-table th {
        background: #f9fafb;
        padding: 0.75rem 1rem;
        text-align: left;
        font-weight: 600;
        color: #374151;
        border-bottom: 2px solid #e5e7eb;
        white-space: nowrap;
    }

    .matrix-table td {
        padding: 0.625rem 1rem;
        border-bottom: 1px solid #f3f4f6;
        vertical-align: middle;
    }

    .group-row td {
        background: #f3f4f6;
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #6b7280;
        padding: 0.5rem 1rem;
    }

    .perm-cell {
        display: flex;
        flex-direction: column;
        gap: 0.125rem;
    }

    .perm-slug {
        font-family: ui-monospace, monospace;
        font-size: 0.8rem;
        color: #374151;
    }

    .perm-desc {
        font-size: 0.75rem;
        color: #6b7280;
    }

    .role-col {
        text-align: center;
        width: 100px;
    }

    .check-yes {
        width: 18px;
        height: 18px;
        color: #22c55e;
        margin: 0 auto;
        display: block;
    }

    .check-no {
        color: #d1d5db;
        display: block;
        text-align: center;
    }

    /* Destaque admin */
    .role-col-admin {
        background: #fef2f2;
    }

    .role-col-editor {
        background: #eff6ff;
    }

    .role-col-author {
        background: #f0fdf4;
    }

    /* Responsivo */
    @media (max-width: 768px) {
        .roles-grid {
            grid-template-columns: 1fr;
        }

        .matrix-table th,
        .matrix-table td {
            padding: 0.5rem;
        }
    }
</style>
@endpush
