@extends('admin.layout')

@section('header_title', 'Plugins')
@section('header_subtitle', 'Gerencie as extensões e módulos adicionais do sistema')

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h2><x-lucide-puzzle class="lucid-icon" /> Lista de Plugins</h2>
    </div>
    <div class="table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Descrição</th>
                    <th>Versão</th>
                    <th>Pasta</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($plugins as $plugin)
                <tr>
                    <td><strong>{{ $plugin->name }}</strong></td>
                    <td style="max-width: 320px; white-space: normal;">
                        <span class="admin-text-muted" style="font-size: 0.875rem;">{{ $plugin->description ?? 'Nenhuma descrição fornecida.' }}</span>
                        <div style="font-size: 0.725rem; color: #888; font-family: monospace; margin-top: 4px;">
                            Provider: {{ $plugin->service_provider_class }}
                        </div>
                    </td>
                    <td><code>v{{ $plugin->version }}</code></td>
                    <td><code>plugins/{{ $plugin->folder_name }}</code></td>
                    <td>
                        <span class="admin-badge {{ $plugin->is_active ? 'admin-badge-active' : 'admin-badge-suspended' }}">
                            {{ $plugin->is_active ? 'Ativo' : 'Inativo' }}
                        </span>
                    </td>
                    <td class="admin-actions">
                        <div>
                            {{-- Formulário de ativação rápida utilizando a estrutura nativa de switches do Lunar Base --}}
                            <form method="POST" action="{{ route('admin.plugins.toggle', $plugin->id) }}" style="display: inline;">
                                @csrf
                                {{-- <label class="switch-label">
                                    <input type="checkbox"
                                           name="is_active"
                                           {{ $plugin->is_active ? 'checked' : '' }}
                                           onChange="this.form.submit()"
                                           class="switch-input">
                                    <span class="switch-track">
                                        <span class="switch-thumb"></span>
                                    </span>
                                    <span class="switch-text"
                                          data-active="Ativar"
                                          data-inactive="Desativar">
                                    </span>
                                </label> --}}
                                <x-switch name="is_active" checked="{{ old('is_active', $plugin->is_active) }}" active="Ativar" inactive="Desativar" style="top: 0;" onChange="this.form.submit()" />
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="admin-text-center admin-text-muted">Nenhum plugin instalado</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
