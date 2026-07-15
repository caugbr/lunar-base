@extends('admin.layout')
@section('header_title', 'Banners')
@section('header_subtitle', 'Gerencie banners, hooks e estatisticas de cliques')

@once
@push('styles')
<link rel="stylesheet" href="{{ asset('plugins/banners/css/banners.css') }}">
@endpush
@endonce

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h2><x-lucide-square class="lucid-icon" /> Banners</h2>
        <a href="{{ route('admin.banners.create') }}" class="admin-btn admin-btn-primary">
            <x-lucide-plus class="lucid-icon" /> Novo Banner
        </a>
    </div>

    <div class="table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Banner</th>
                    <th>Slug</th>
                    <th>Status</th>
                    <th>Hook</th>
                    <th>Cliques (Total)</th>
                    <th>Acoes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($banners as $banner)
                <tr>
                    <td>
                        <div class="banner-preview-cell">
                            @if($banner->image)
                                <img src="{{ $banner->image_url }}" alt="{{ $banner->title }}" class="banner-thumb">
                            @else
                                <div class="banner-thumb-placeholder">
                                    <x-lucide-image class="lucid-icon" />
                                </div>
                            @endif
                            <div class="banner-info">
                                <strong>{{ $banner->title }}</strong>
                                <small>{{ Str::limit($banner->link_url, 40) }}</small>
                            </div>
                        </div>
                    </td>
                    <td><code>{{ $banner->slug }}</code></td>
                    <td>
                        @if($banner->is_active)
                            <span class="admin-badge admin-badge-active">Ativo</span>
                        @else
                            <span class="admin-badge admin-badge-inactive">Inativo</span>
                        @endif
                    </td>
                    <td>
                        @if($banner->hook)
                            <span class="admin-badge admin-badge-info">{{ $banner->hook }}</span>
                        @else
                            <span class="admin-text-muted">Manual / Shortcode</span>
                        @endif
                    </td>
                    <td>
                        <strong>{{ number_format($banner->clicks) }}</strong>
                        <small class="admin-text-muted">cliques</small>
                    </td>
                    <td class="admin-actions">
                        <div>
                            <a href="{{ route('admin.banners.stats', $banner->id) }}" class="admin-btn admin-btn-secondary" style="padding: 4px 12px;" title="Estatisticas">
                                <x-lucide-bar-chart-3 class="lucid-icon" />
                            </a>
                            <a href="{{ route('admin.banners.edit', $banner->id) }}" class="admin-btn admin-btn-secondary" style="padding: 4px 12px;" title="Editar">
                                <x-lucide-pencil class="lucid-icon" />
                            </a>
                            <form method="POST" action="{{ route('admin.banners.destroy', $banner->id) }}" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="admin-btn admin-btn-danger" style="padding: 4px 12px;" onclick="return confirm('Remover este banner?')" title="Excluir">
                                    <x-lucide-trash-2 class="lucid-icon" />
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="admin-text-center admin-text-muted" style="padding: 30px;">
                        Nenhum banner cadastrado. <a href="{{ route('admin.banners.create') }}">Crie o primeiro</a>.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
