@extends('admin.layout')

@section('header_title', 'Plugins')
@section('header_subtitle', 'Gerencie as extensões e módulos adicionais do sistema')

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h2><x-lucide-puzzle class="lucid-icon" /> Plugins instalados</h2>
        <a class="admin-btn admin-btn-secondary" href="/tutorials/plugins.html" target="_blank">
            <x-lucide-external-link class="lucid-icon" />
            Como criar um plugin
        </a>
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
                    <th style="display: flex; justify-content: space-between;">
                        Ações
                        <div class="check-all">
                            <form method="POST" action="{{ route('admin.plugins.toggle_all', 1) }}" style="display: inline;">
                                @csrf
                                <button type="submit" class="transparent-btn status-all" title="Ativar todos os plugins">
                                    <x-lucide-check-check class="lucid-icon" />
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.plugins.toggle_all', 0) }}" style="display: inline;">
                                @csrf
                                <button type="submit" class="transparent-btn status-all" title="Desativar todos os plugins">
                                    <x-lucide-minus class="lucid-icon" />
                                </button>
                            </form>
                        </div>
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse($plugins as $plugin)
                @php
                    $kebabName = Str::kebab($plugin->name);
                    $helpView = null;
                    if (view()->exists("{$kebabName}-help::help")) {
                        $helpView = "{$kebabName}-help::help";
                    }
                @endphp
                <tr class="{{ $plugin->is_active ? 'active' : 'inactive' }}">
                    <td>
                        <strong>{{ $plugin->name }}</strong>
                        @if($helpView)
                        <button type="button"
                            onclick="window.dispatchEvent(new CustomEvent('modal-open', { detail: { id: 'help-{{ $plugin->id }}' } }))"
                            class="transparent-btn"
                            title="Ver documentação">
                            <x-lucide-help-circle class="lucid-icon" />
                        </button>
                        <x-modal id="help-{{ $plugin->id }}" title="Ajuda: {{ $plugin->name }}" size="lg">
                            @include($helpView)
                        </x-modal>
                        @endif
                    </td>
                    <td style="max-width: 320px; white-space: normal;">
                        <span class="admin-text-muted" style="font-size: 0.875rem;">{{ $plugin->description ?? 'Nenhuma descrição fornecida.' }}</span>
                        {{-- <div style="font-size: 0.725rem; color: #888; font-family: monospace; margin-top: 4px;">
                            Provider: {{ $plugin->service_provider_class }}
                        </div> --}}
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
                                <x-switch name="is_active" checked="{{ old('is_active', $plugin->is_active) }}" active="Ligado" inactive="Desligado" style="top: 0;" onChange="this.form.submit()" />
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

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin/pages/plugin-help.css') }}">
<style>
    .status-all {
        width: 24px;
        height: 24px;
        border: 1px solid var(--color-border);
        border-radius: 4px;
        margin: 0 4px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .status-all:hover {
        background-color: #3b82f6;
        color: #ffffff;
    }
    tr.inactive {
        background-color: #f5f5f5;
    }
</style>
@endpush
@push('scripts')
{{-- Alpine CDN --}}
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush
