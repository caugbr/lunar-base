@extends('admin.layout')

@section('header_title', $map->exists ? 'Editar Mapa' : 'Novo Mapa')
@section('header_subtitle', $map->exists ? 'Modifique as configurações do mapa' : 'Crie um novo mapa interativo')

@section('content')
@once
@push('styles')
    <link rel="stylesheet" href="{{ asset('plugins/maps/css/maps.css') }}">
@endpush
@endonce

<div class="admin-card">
    <div class="admin-card-header">
        <h2><x-lucide-map-pin class="lucid-icon" /> {{ $map->exists ? 'Editar: ' . $map->title : 'Novo Mapa' }}</h2>
        <div class="top-buttons">
            @if($map->exists)
                <a href="{{ route('admin.maps.index') }}" class="admin-btn admin-btn-secondary">
                    <x-lucide-arrow-left class="lucid-icon" /> <span>Voltar</span>
                </a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="admin-alert admin-alert-success">
            <x-lucide-check-circle class="lucid-icon" /> {{ session('success') }}
        </div>
    @endif

    <form action="{{ $map->exists ? route('admin.maps.update', $map->id) : route('admin.maps.store') }}" method="POST" x-data="mapEditor()" x-init="init()">
        @csrf
        @if($map->exists) @method('PUT') @endif

        {{-- ═══════════════════════════════════════════════════════
             LAYOUT DUAS COLUNAS: Esquerda (maior) + Direita (menor)
             ═══════════════════════════════════════════════════════ --}}
        <div class="map-editor-layout">

            {{-- ═══════════════════════════════════════════════════
                 COLUNA ESQUERDA (maior) - Mapa + Descrição
                 ═══════════════════════════════════════════════════ --}}
            <div class="map-editor-left">

                {{-- Título (igual ao campo "Título do post") --}}
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <input type="text" name="title" x-model="form.title" class="form-input" required
                           placeholder="Título do mapa" style="font-size: 1.1rem; font-weight: 600;">
                    @error('title') <small class="error">{{ $message }}</small> @enderror
                </div>

                {{-- MAPA (ocupa o lugar do editor TinyMCE) --}}
                <div class="edit-box">
                    <header>
                        <x-lucide-map class="lucid-icon" /> Preview do Mapa
                        <div style="margin-left: auto; display: flex; gap: 8px;">
                            <button type="button" class="admin-btn admin-btn-secondary btn-sm" @click="updateMapPreview()">
                                <x-lucide-refresh-cw class="lucid-icon" /> Atualizar
                            </button>
                        </div>
                    </header>
                    <article style="padding: 0;">
                        <div id="map-preview" style="width: 100%; height: 500px; border-radius: 0 0 8px 8px;"></div>
                    </article>
                </div>

                {{-- Descrição curta --}}
                <div class="edit-box">
                    <header>Descrição</header>
                    <article>
                        <div class="form-group">
                            <textarea name="description" x-model="form.description" class="form-input" rows="4"
                                      placeholder="Breve descrição do mapa (opcional)"></textarea>
                            <small>Descrição exibida abaixo do mapa na página pública</small>
                        </div>
                    </article>
                </div>

                {{-- Slug (aparece só na edição, igual ao Posts) --}}
                @if($map->exists)
                <div class="edit-box">
                    <header>Shortcode</header>
                    <article>
                        <div class="form-group">
                            <div style="display: flex; gap: 8px;">
                                <input type="text" readonly value='[map id="{{ $map->id }}"]' class="form-input"
                                       style="font-family: monospace; background: var(--color-bg-dark); flex: 1;">
                                <button type="button" class="admin-btn admin-btn-secondary"
                                        @click="navigator.clipboard.writeText('[map id=&quot;{{ $map->id }}&quot;]')">
                                    <x-lucide-copy class="lucid-icon" />
                                </button>
                            </div>
                            <small>Cole este shortcode em qualquer post ou página para exibir o mapa</small>
                        </div>
                    </article>
                </div>
                @endif
            </div>

            {{-- ═══════════════════════════════════════════════════
                 COLUNA DIREITA (menor) - Boxes empilhados
                 ═══════════════════════════════════════════════════ --}}
            <div class="map-editor-right">

                {{-- Box 1: Configurações do Mapa --}}
                <div class="edit-box">
                    <header><x-lucide-settings class="lucid-icon" /> Configurações</header>
                    <article>
                        <div class="admin-form-row">
                            <div class="form-group">
                                <label for="center_lat">Latitude *</label>
                                <input type="number" step="0.0000001" name="center_lat" id="center_lat"
                                       x-model="form.center_lat" class="form-input" required>
                                @error('center_lat') <small class="error">{{ $message }}</small> @enderror
                            </div>
                            <div class="form-group">
                                <label for="center_lng">Longitude *</label>
                                <input type="number" step="0.0000001" name="center_lng" id="center_lng"
                                       x-model="form.center_lng" class="form-input" required>
                                @error('center_lng') <small class="error">{{ $message }}</small> @enderror
                            </div>
                        </div>

                        <div class="admin-form-row">
                            <div class="form-group">
                                <label for="zoom">Zoom (1-18) *</label>
                                <input type="number" min="1" max="18" name="zoom" id="zoom"
                                       x-model="form.zoom" class="form-input" required>
                            </div>
                            <div class="form-group">
                                <label for="height">Altura (px) *</label>
                                <input type="number" min="100" max="1200" name="height" id="height"
                                       x-model="form.height" class="form-input" required>
                            </div>
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 8px; margin-top: 8px;">
                            <label style="display: flex; align-items: center; gap: 8px; font-size: 0.85rem; cursor: pointer;">
                                <input type="checkbox" name="show_zoom_controls" x-model="form.show_zoom_controls" value="1">
                                <x-lucide-zoom-in class="lucid-icon" style="width: 16px; height: 16px;" />
                                Controles de zoom
                            </label>
                            <label style="display: flex; align-items: center; gap: 8px; font-size: 0.85rem; cursor: pointer;">
                                <input type="checkbox" name="allow_drag" x-model="form.allow_drag" value="1">
                                <x-lucide-move class="lucid-icon" style="width: 16px; height: 16px;" />
                                Permitir arrastar
                            </label>
                            <label style="display: flex; align-items: center; gap: 8px; font-size: 0.85rem; cursor: pointer;">
                                <input type="checkbox" name="allow_scroll_zoom" x-model="form.allow_scroll_zoom" value="1">
                                <x-lucide-scroll class="lucid-icon" style="width: 16px; height: 16px;" />
                                Zoom com scroll
                            </label>
                        </div>
                    </article>
                </div>

                {{-- Box 2: Busca de Endereço --}}
                <div class="edit-box">
                    <header><x-lucide-search class="lucid-icon" /> Buscar Endereço</header>
                    <article>
                        <div class="form-group">
                            <div style="display: flex; gap: 8px;">
                                <input type="text" x-model="searchQuery" @keydown.enter.prevent="searchAddress()"
                                       class="form-input" placeholder="Digite um endereço...">
                                <button type="button" class="admin-btn admin-btn-secondary" @click="searchAddress()" :disabled="searching">
                                    <x-lucide-search class="lucid-icon" />
                                </button>
                            </div>
                            <small>Use a API Nominatim (OpenStreetMap) para encontrar coordenadas</small>
                        </div>

                        <div x-show="searchResults.length > 0" style="margin-top: 8px; border: 1px solid var(--color-border); border-radius: 6px; overflow: hidden;">
                            <template x-for="(result, idx) in searchResults" :key="idx">
                                <div @click="selectAddress(result)"
                                     style="padding: 8px 12px; cursor: pointer; font-size: 0.8rem; border-bottom: 1px solid var(--color-border);"
                                     onmouseover="this.style.background='var(--color-bg-dark)'"
                                     onmouseout="this.style.background='transparent'">
                                    <span x-text="result.display_name"></span>
                                </div>
                            </template>
                        </div>
                    </article>
                </div>

                {{-- Box 3: Marcadores --}}
                <div class="edit-box">
                    <header style="display: flex; justify-content: space-between; align-items: center;">
                        <span>
                            <x-lucide-map-pin class="lucid-icon" /> Marcadores
                            <span x-text="'(' + markers.length + ')'" style="font-size: 0.8rem; color: var(--color-text-muted);"></span>
                        </span>
                        <button type="button" class="admin-btn admin-btn-primary btn-sm" @click="addMarker()">
                            <x-lucide-plus class="lucid-icon" /> Adicionar
                        </button>
                    </header>
                    <article>
                        <div x-show="markers.length === 0" style="padding: 1.5rem; text-align: center; color: var(--color-text-muted); font-size: 0.85rem;">
                            <x-lucide-map-pin class="lucid-icon" style="width: 32px; height: 32px; opacity: 0.3; margin-bottom: 8px;" />
                            <p>Nenhum marcador ainda.</p>
                            <small>Clique em "Adicionar" ou clique diretamente no mapa</small>
                        </div>

                        <template x-for="(marker, index) in markers" :key="index">
                            <div style="border: 1px solid var(--color-border); border-radius: 6px; padding: 10px; margin-bottom: 8px; background: var(--color-bg-dark);">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                                    <span style="font-weight: 600; font-size: 0.8rem;" x-text="'#' + (index + 1) + ' - ' + (marker.title || 'Sem título')"></span>
                                    <div style="display: flex; gap: 4px;">
                                        <button type="button" @click="focusMarker(index)" class="admin-btn admin-btn-secondary btn-sm" title="Centralizar">
                                            <x-lucide-crosshair class="lucid-icon" />
                                        </button>
                                        <button type="button" @click="removeMarker(index)" class="admin-btn admin-btn-danger btn-sm" title="Remover">
                                            <x-lucide-x class="lucid-icon" />
                                        </button>
                                    </div>
                                </div>

                                <div class="form-group" style="margin-bottom: 6px;">
                                    <input type="hidden" :name="'markers[' + index + '][id]'" :value="marker.id || ''">
                                    <input type="text" :name="'markers[' + index + '][title]'" x-model="marker.title"
                                           class="form-input" placeholder="Título *" style="width: 100%;">
                                </div>

                                <div class="admin-form-row" style="margin-bottom: 6px;">
                                    <div class="form-group">
                                        <input type="number" step="0.0000001" :name="'markers[' + index + '][lat]'"
                                               x-model="marker.lat" class="form-input" placeholder="Lat" required style="width: 100%;">
                                    </div>
                                    <div class="form-group">
                                        <input type="number" step="0.0000001" :name="'markers[' + index + '][lng]'"
                                               x-model="marker.lng" class="form-input" placeholder="Lng" required style="width: 100%;">
                                    </div>
                                </div>

                                <div class="form-group" style="margin-bottom: 6px;">
                                    <textarea :name="'markers[' + index + '][content]'" x-model="marker.content"
                                              class="form-input" rows="2" placeholder="Conteúdo do popup (HTML)" style="width: 100%;"></textarea>
                                </div>

                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <label style="font-size: 0.75rem;">Cor:</label>
                                    <input type="color" :name="'markers[' + index + '][color]'" x-model="marker.color"
                                           style="width: 32px; height: 26px; border: none; cursor: pointer; border-radius: 4px;">
                                    <input type="hidden" :name="'markers[' + index + '][icon]'" value="map-pin">
                                </div>
                            </div>
                        </template>
                    </article>
                </div>

                {{-- Box 4: Ações (Salvar) --}}
                <div class="edit-box">
                    <header><x-lucide-save class="lucid-icon" /> Ações</header>
                    <article>
                        <div class="buttons" style="display: flex; flex-direction: column; gap: 8px;">
                            <button type="submit" class="admin-btn admin-btn-primary" style="width: 100%;">
                                <x-lucide-save class="lucid-icon" />
                                {{ $map->exists ? 'Salvar Alterações' : 'Criar Mapa' }}
                            </button>
                            <a href="{{ route('admin.maps.index') }}" class="admin-btn admin-btn-secondary" style="width: 100%; text-align: center;">
                                Cancelar
                            </a>
                        </div>
                    </article>
                </div>

            </div>
        </div>
    </form>
</div>

@push('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
function mapEditor() {
    return {
        map: null,
        leafletMarkers: [],
        searching: false,
        searchQuery: '',
        searchResults: [],

        form: {
            title: @json($map->title ?? ''),
            slug: @json($map->slug ?? ''),
            description: @json($map->description ?? ''),
            center_lat: @json($map->center_lat ?? setting('maps_default_lat', '-23.5505')),
            center_lng: @json($map->center_lng ?? setting('maps_default_lng', '-46.6333')),
            zoom: @json($map->zoom ?? setting('maps_default_zoom', 13)),
            height: @json($map->height ?? 500),
            show_zoom_controls: @json($map->show_zoom_controls ?? true),
            allow_drag: @json($map->allow_drag ?? true),
            allow_scroll_zoom: @json($map->allow_scroll_zoom ?? true),
        },

        markers: @json($map->exists ? $map->markers->toArray() : []),

        init() {
            this.$nextTick(() => this.initMap());
        },

        initMap() {
            if (this.map) this.map.remove();

            this.map = L.map('map-preview', {
                center: [parseFloat(this.form.center_lat), parseFloat(this.form.center_lng)],
                zoom: parseInt(this.form.zoom),
                zoomControl: this.form.show_zoom_controls,
                dragging: this.form.allow_drag,
                scrollWheelZoom: this.form.allow_scroll_zoom,
            });

            L.tileLayer(@json(setting('maps_tile_url', 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png')), {
                attribution: @json(setting('maps_attribution', '&copy; OpenStreetMap')),
                maxZoom: 19,
            }).addTo(this.map);

            this.map.on('click', (e) => this.addMarkerAt(e.latlng.lat, e.latlng.lng));
            this.renderMarkers();
        },

        renderMarkers() {
            this.leafletMarkers.forEach(m => this.map.removeLayer(m));
            this.leafletMarkers = [];

            this.markers.forEach((marker, index) => {
                if (!marker.lat || !marker.lng) return;

                const icon = L.divIcon({
                    className: 'custom-marker',
                    html: `<div style="background:${marker.color || '#e74c3c'};width:28px;height:28px;border-radius:50% 50% 50% 0;transform:rotate(-45deg);border:2px solid white;box-shadow:0 2px 6px rgba(0,0,0,0.3);display:flex;align-items:center;justify-content:center;"><span style="transform:rotate(45deg);color:white;font-size:12px;font-weight:bold;">${index + 1}</span></div>`,
                    iconSize: [28, 28],
                    iconAnchor: [14, 28],
                    popupAnchor: [0, -28],
                });

                const leafletMarker = L.marker([marker.lat, marker.lng], { icon, draggable: true }).addTo(this.map);

                if (marker.title || marker.content) {
                    leafletMarker.bindPopup(`<strong>${marker.title || 'Marcador'}</strong>${marker.content ? '<br>' + marker.content : ''}`);
                }

                leafletMarker.on('dragend', (e) => {
                    const pos = e.target.getLatLng();
                    this.markers[index].lat = pos.lat;
                    this.markers[index].lng = pos.lng;
                });

                this.leafletMarkers.push(leafletMarker);
            });
        },

        addMarker() {
            this.markers.push({ id: null, title: '', lat: parseFloat(this.form.center_lat), lng: parseFloat(this.form.center_lng), content: '', color: '#e74c3c', icon: 'map-pin' });
            this.$nextTick(() => this.renderMarkers());
        },

        addMarkerAt(lat, lng) {
            this.markers.push({ id: null, title: '', lat, lng, content: '', color: '#e74c3c', icon: 'map-pin' });
            this.$nextTick(() => this.renderMarkers());
        },

        removeMarker(index) {
            this.markers.splice(index, 1);
            this.renderMarkers();
        },

        focusMarker(index) {
            const marker = this.markers[index];
            if (marker.lat && marker.lng) {
                this.map.setView([marker.lat, marker.lng], Math.max(this.map.getZoom(), 15));
                if (this.leafletMarkers[index]) this.leafletMarkers[index].openPopup();
            }
        },

        updateMapPreview() {
            this.map.setView([parseFloat(this.form.center_lat), parseFloat(this.form.center_lng)], parseInt(this.form.zoom));
            this.renderMarkers();
        },

        async searchAddress() {
            if (!this.searchQuery || this.searchQuery.length < 3) return;
            this.searching = true;
            this.searchResults = [];

            try {
                const response = await fetch(`/api/maps/geocode?q=${encodeURIComponent(this.searchQuery)}`);
                const data = await response.json();
                this.searchResults = data.results || [];
            } catch (error) {
                console.error('Erro na busca:', error);
            } finally {
                this.searching = false;
            }
        },

        selectAddress(result) {
            this.form.center_lat = result.lat;
            this.form.center_lng = result.lng;
            this.searchQuery = '';
            this.searchResults = [];
            this.updateMapPreview();
        }
    }
}
</script>
@endpush

@push('styles')
<style>
    /* ═══ Layout duas colunas (igual ao Posts) ══ */
    .map-editor-layout {
        display: grid;
        grid-template-columns: 1fr 340px;
        gap: 1.5rem;
        align-items: start;
    }

    .map-editor-left {
        min-width: 0;
    }

    .map-editor-right {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    /* Ajuste do header do edit-box para alinhar ícone + texto + botão */
    .edit-box header {
        display: flex;
        align-items: center;
        gap: 8px;
        position: relative;
        z-index: 9999;
    }

    .edit-box header .lucid-icon {
        width: 18px;
        height: 18px;
    }

    /* Responsivo: empilha em telas menores */
    @media (max-width: 1024px) {
        .map-editor-layout {
            grid-template-columns: 1fr;
        }
        .map-editor-right {
            order: -1;
        }
    }

    /* Custom marker no admin */
    .custom-marker {
        background: transparent !important;
        border: none !important;
    }
</style>
@endpush
@endsection
