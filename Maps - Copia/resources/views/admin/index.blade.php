@extends('admin.layout')

@section('header_title', 'Mapas')
@section('header_subtitle', 'Gerencie mapas interativos OpenStreetMap')

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h2><x-lucide-map class="lucid-icon" /> Lista de Mapas</h2>
        <a href="{{ route('admin.maps.create') }}" class="admin-btn admin-btn-primary">
            <x-lucide-plus class="lucid-icon" /> <span>Novo Mapa</span>
        </a>
    </div>

    @if(session('success'))
        <div class="admin-alert admin-alert-success">
            <x-lucide-check-circle class="lucid-icon" /> {{ session('success') }}
        </div>
    @endif

    <div class="table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Título</th>
                    <th>Coordenadas</th>
                    <th>Zoom</th>
                    <th>Marcadores</th>
                    <th>Shortcode</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($maps as $map)
                <tr>
                    <td>
                        <strong>{{ $map->title }}</strong>
                        @if($map->description)
                            <br><small class="admin-text-muted">{{ Str::limit($map->description, 60) }}</small>
                        @endif
                    </td>
                    <td>
                        <code style="font-size: 0.8rem;">
                            {{ number_format($map->center_lat, 5) }}, {{ number_format($map->center_lng, 5) }}
                        </code>
                    </td>
                    <td>{{ $map->zoom }}</td>
                    <td>
                        <span class="admin-badge admin-badge-active">{{ $map->markers_count }}</span>
                    </td>
                    <td>
                        <code style="font-size: 0.75rem; background: var(--color-bg-dark); padding: 2px 6px; border-radius: 4px;">
                            [map id="{{ $map->id }}"]
                        </code>
                    </td>
                    <td class="admin-actions">
                        <div>
                            <a href="{{ route('admin.maps.edit', $map->id) }}" class="admin-btn admin-btn-secondary" style="padding: 4px 12px;">
                                <x-lucide-pencil class="lucid-icon" />
                            </a>
                            <form method="POST" action="{{ route('admin.maps.destroy', $map->id) }}" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="admin-btn admin-btn-danger" style="padding: 4px 12px;" onclick="return confirm('Excluir este mapa e todos os seus marcadores?')">
                                    <x-lucide-trash-2 class="lucid-icon" />
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="admin-text-center admin-text-muted" style="padding: 40px 0;">
                        <x-lucide-map class="lucid-icon" style="width: 48px; height: 48px; margin-bottom: 12px; opacity: 0.3;" />
                        <p>Nenhum mapa cadastrado ainda.</p>
                        <a href="{{ route('admin.maps.create') }}" class="admin-btn admin-btn-primary" style="margin-top: 12px;">
                            Criar primeiro mapa
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($maps->hasPages())
        <div class="admin-pagination">
            {{ $maps->links() }}
        </div>
    @endif
</div>
@endsection
