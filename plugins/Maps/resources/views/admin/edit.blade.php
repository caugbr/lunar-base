@extends('admin.layout')

@section('header_title', $map->exists ? 'Editar Mapa' : 'Novo Mapa')
@section('header_subtitle', $map->exists ? 'Modifique o mapa, marcadores e áreas destacadas' : 'Crie um novo mapa interativo')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <link rel="stylesheet" href="{{ asset('plugins/maps/css/maps-admin.css') }}">
@endpush

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h2>
            <x-lucide-map-pin class="lucid-icon" />
            {{ $map->exists ? 'Editar: ' . $map->title : 'Novo Mapa' }}
        </h2>
        <div class="top-buttons">
            <a href="{{ route('admin.maps.index') }}" class="admin-btn admin-btn-secondary">
                <x-lucide-arrow-left class="lucid-icon" /> <span>Voltar</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="admin-alert admin-alert-success">
            <x-lucide-check-circle class="lucid-icon" /> {{ session('success') }}
        </div>
    @endif

    <form method="POST"
          action="{{ $map->exists ? route('admin.maps.update', $map->id) : route('admin.maps.store') }}"
          enctype="multipart/form-data"
          id="map_edit_form"
          x-data="mapEditor()"
          x-init="init()">
        @csrf
        @if($map->exists) @method('PUT') @endif

        {{-- ═════════ Título (largura total) ═════════ --}}
        <div class="form-group map-title-row">
            <input type="text" name="title" x-model="form.title" placeholder="Título do mapa" required>
            @error('title') <small class="error">{{ $message }}</small> @enderror
        </div>

        {{-- ═════════ Layout 2/3 + 1/3 ═════════ --}}
        <div class="map-editor-layout">

            {{-- ────────── COLUNA ESQUERDA (2/3) ────────── --}}
            <div class="map-editor-left">

                {{-- Cartão do mapa com toolbar embutida --}}
                <div class="edit-box map-preview-box">
                    <header>
                        <x-lucide-map class="lucid-icon" />
                        <span>Mapa</span>

                        {{-- Coordenadas ao vivo --}}
                        <span class="coord-badge" title="Centro atual do mapa">
                            <x-lucide-crosshair class="lucid-icon" />
                            <code x-text="format(form.center_lat) + ', ' + format(form.center_lng)"></code>
                            <span class="zoom-pill">z <span x-text="form.zoom"></span></span>
                        </span>

                        <button type="button" class="admin-btn admin-btn-secondary btn-sm" @click="fitMarkers()"
                                :disabled="markers.length === 0" title="Ajustar aos marcadores">
                            <x-lucide-scan class="lucid-icon" />
                        </button>
                    </header>

                    {{-- Barra de busca (Nominatim) --}}
                    <div class="map-toolbar">
                        <div class="search-wrap">
                            <x-lucide-search class="lucid-icon" />
                            <input type="text" x-model="searchQuery" @keydown.enter.prevent="searchAddress()"
                                   placeholder="Buscar endereço ou lugar…">
                            <button type="button" class="admin-btn admin-btn-primary btn-sm"
                                    @click="searchAddress()" :disabled="searching || searchQuery.length < 3">
                                <span x-show="!searching">Buscar</span>
                                <span x-show="searching">…</span>
                            </button>
                        </div>
                        <div class="search-results" x-show="searchResults.length > 0" @click.outside="searchResults = []">
                            <template x-for="(r, i) in searchResults" :key="i">
                                <div class="search-result" @click="selectAddress(r)">
                                    <x-lucide-map-pin class="lucid-icon" />
                                    <span x-text="r.display_name"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <article class="map-canvas-wrap">
                        <div id="map-preview" class="map-canvas"></div>
                        <div class="map-hint">
                            <x-lucide-mouse-pointer-click class="lucid-icon" />
                            Clique no mapa para adicionar um marcador
                        </div>
                    </article>
                </div>

                {{-- Descrição --}}
                <div class="edit-box">
                    <header><x-lucide-align-left class="lucid-icon" /> Descrição</header>
                    <article>
                        <div class="form-group">
                            <textarea name="description" x-model="form.description" rows="3"
                                      placeholder="Breve descrição do mapa (opcional)"></textarea>
                        </div>
                    </article>
                </div>

                {{-- Lista de marcadores como chips clicáveis --}}
                <div class="edit-box">
                    <header>
                        <x-lucide-list class="lucid-icon" /> Marcadores
                        <span class="counter" x-text="markers.length"></span>
                        <div style="margin-left: auto; display: flex; gap: 6px;">
                            <button type="button" class="admin-btn admin-btn-secondary btn-sm"
                                    @click="deleteAll()" :disabled="markers.length === 0">
                                <x-lucide-trash class="lucid-icon" /> Limpar
                            </button>
                        </div>
                    </header>
                    <article>
                        <div class="markers-chips" x-show="markers.length > 0">
                            <template x-for="(m, i) in sortedMarkerIndexes()" :key="markers[m].uid || i">
                                <button type="button" class="marker-chip"
                                        :class="{ 'is-selected': selectedIdx === m }"
                                        @click="selectMarker(m)"
                                        :style="'--pin-color:' + (markers[m].color || '#e74c3c')">
                                    <span class="dot"></span>
                                    <span x-text="markers[m].title || 'Sem título'"></span>
                                </button>
                            </template>
                        </div>
                        <p class="empty-hint" x-show="markers.length === 0">
                            Nenhum marcador ainda. Clique no mapa ou use o painel à direita.
                        </p>
                    </article>
                </div>
                {{-- ═ Área destacada (GeoJSON) ═ --}}
                <div class="edit-box">
                    <header><x-lucide-shapes class="lucid-icon" /> Área destacada</header>
                    <article>
                        <div class="form-group">
                            <label>Lugar pré-cadastrado</label>
                            <select name="geojson_place" x-model="form.geojson_place" @change="loadPlaceGeoJson()">
                                <option value="">— Nenhum —</option>
                                <template x-for="p in places" :key="p.pid">
                                    <option :value="p.pid" x-text="p.name + ' (' + (p.type || '') + ')'"></option>
                                </template>
                            </select>
                            <small>
                                <a href="#" @click.prevent="showFinder = !showFinder">
                                    <x-lucide-plus class="lucid-icon" /> Buscar novo lugar
                                </a>
                            </small>
                        </div>

                        {{-- Buscador de novos GeoJSON --}}
                        <div class="geojson-finder" x-show="showFinder" x-transition>
                            <div class="admin-form-row">
                                <input type="text" x-model="finder.country" placeholder="País">
                                <input type="text" x-model="finder.state" placeholder="Estado">
                            </div>
                            <div class="admin-form-row">
                                <input type="text" x-model="finder.city" placeholder="Cidade">
                                <input type="text" x-model="finder.neighborhood" placeholder="Bairro">
                            </div>
                            <button type="button" class="admin-btn admin-btn-secondary btn-sm"
                                    @click="findPlace()" :disabled="finding || !finderHasQuery()">
                                <x-lucide-search class="lucid-icon" />
                                <span x-text="finding ? 'Buscando…' : 'Procurar'"></span>
                            </button>

                            <div class="finder-results" x-show="finder.results.length > 0">
                                <template x-for="(r, i) in finder.results" :key="i">
                                    <div class="finder-result">
                                        <div>
                                            <strong x-text="r.name"></strong>
                                            <small x-text="r.display_name"></small>
                                        </div>
                                        <div class="finder-save">
                                            <input type="text" x-model="r._pid"
                                                   :placeholder="suggestPid(r.name)" size="14">
                                            <button type="button" class="admin-btn admin-btn-primary btn-sm"
                                                    @click="savePlace(r)"
                                                    :disabled="savingPlace">Salvar</button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- GeoJSON inline (colado) --}}
                        <div class="form-group">
                            <label>Ou cole um GeoJSON</label>
                            <textarea name="geojson_inline_raw" x-model="form.geojson_inline_raw"
                                      rows="4" placeholder='{"type":"FeatureCollection","features":[…]}'
                                      @change="applyInlineGeoJson()"></textarea>
                            <small x-show="geojsonError" class="error" x-text="geojsonError"></small>
                        </div>

                        {{-- Estilo --}}
                        <details class="geojson-style">
                            <summary>Estilo da área</summary>
                            <div class="admin-form-row">
                                <div class="form-group">
                                    <label>Linha</label>
                                    <input type="color" name="geojson_color" x-model="form.geojson_color"
                                           @input="restyleGeoJson()">
                                </div>
                                <div class="form-group">
                                    <label>Preenchimento</label>
                                    <input type="color" name="geojson_fill_color" x-model="form.geojson_fill_color"
                                           @input="restyleGeoJson()">
                                </div>
                            </div>
                            <div class="admin-form-row">
                                <div class="form-group">
                                    <label>Espessura (0–10)</label>
                                    <input type="number" min="0" max="10" step="1"
                                           name="geojson_weight" x-model.number="form.geojson_weight"
                                           @input="restyleGeoJson()">
                                </div>
                                <div class="form-group">
                                    <label>Opacidade linha</label>
                                    <input type="number" min="0" max="1" step="0.1"
                                           name="geojson_opacity" x-model.number="form.geojson_opacity"
                                           @input="restyleGeoJson()">
                                </div>
                                <div class="form-group">
                                    <label>Opacidade preench.</label>
                                    <input type="number" min="0" max="1" step="0.1"
                                           name="geojson_fill_opacity" x-model.number="form.geojson_fill_opacity"
                                           @input="restyleGeoJson()">
                                </div>
                            </div>
                        </details>
                    </article>
                </div>

                {{-- Shortcode --}}
                @if($map->exists)
                <div class="edit-box">
                    <header><x-lucide-code class="lucid-icon" /> Shortcode</header>
                    <article>
                        <div class="shortcode-row">
                            <input type="text" readonly value='[map id="{{ $map->id }}"]'
                                   onfocus="this.select()" class="shortcode-input">
                            <button type="button" class="admin-btn admin-btn-secondary"
                                    @click="copyShortcode('[map id=&quot;{{ $map->id }}&quot;]')">
                                <x-lucide-copy class="lucid-icon" /> Copiar
                            </button>
                        </div>
                        <small>Cole em qualquer post ou página para exibir este mapa.</small>
                    </article>
                </div>
                @endif
            </div>

            {{-- ────────── COLUNA DIREITA (1/3) ────────── --}}
            <div class="map-editor-right">

                {{-- ═ Configurações do mapa ═ --}}
                <div class="edit-box">
                    <header><x-lucide-settings class="lucid-icon" /> Configurações</header>
                    <article>
                        <div class="admin-form-row">
                            <div class="form-group">
                                <label>Latitude</label>
                                <input type="number" step="0.0000001" name="center_lat"
                                       x-model.number="form.center_lat" @change="applyFormToMap()" required>
                            </div>
                            <div class="form-group">
                                <label>Longitude</label>
                                <input type="number" step="0.0000001" name="center_lng"
                                       x-model.number="form.center_lng" @change="applyFormToMap()" required>
                            </div>
                        </div>

                        <div class="admin-form-row">
                            <div class="form-group">
                                <label>Zoom (1–19)</label>
                                <input type="number" min="1" max="19" name="zoom"
                                       x-model.number="form.zoom" @change="applyFormToMap()" required>
                            </div>
                            <div class="form-group">
                                <label>Altura (px)</label>
                                <input type="number" min="100" max="1600" name="height"
                                       x-model.number="form.height" @change="applyFormToMap()" required>
                            </div>
                        </div>

                        <div class="admin-form-row">
                            <div class="form-group">
                                <label>Largura (px)</label>
                                <input type="number" min="100" max="2400" name="width"
                                       x-model.number="form.width" :disabled="form.fullwidth"
                                       @change="applyFormToMap()">
                            </div>
                            <div class="form-group" style="align-self: end;">
                                <label class="checkbox-inline">
                                    <input type="hidden" name="fullwidth" value="0">
                                    <input type="checkbox" name="fullwidth" value="1"
                                           x-model="form.fullwidth" @change="applyFormToMap()">
                                    <span>Largura 100%</span>
                                </label>
                            </div>
                        </div>

                        <div class="switch-stack">
                            <label class="checkbox-inline">
                                <input type="hidden" name="show_zoom_controls" value="0">
                                <input type="checkbox" name="show_zoom_controls" value="1"
                                       x-model="form.show_zoom_controls" @change="applyFormToMap()">
                                <x-lucide-zoom-in class="lucid-icon" /> Controles de zoom
                            </label>
                            <label class="checkbox-inline">
                                <input type="hidden" name="allow_drag" value="0">
                                <input type="checkbox" name="allow_drag" value="1"
                                       x-model="form.allow_drag" @change="applyFormToMap()">
                                <x-lucide-move class="lucid-icon" /> Permitir arrastar
                            </label>
                            <label class="checkbox-inline">
                                <input type="hidden" name="allow_scroll_zoom" value="0">
                                <input type="checkbox" name="allow_scroll_zoom" value="1"
                                       x-model="form.allow_scroll_zoom" @change="applyFormToMap()">
                                <x-lucide-scan-search class="lucid-icon" /> Zoom com scroll
                            </label>
                        </div>
                        <div class="buttons">
                            <button type="submit" class="admin-btn admin-btn-primary" style="width: 100%;">
                                <x-lucide-save class="lucid-icon" />
                                {{ $map->exists ? 'Salvar alterações' : 'Criar mapa' }}
                            </button>
                        </div>
                    </article>
                </div>

                {{-- ═ Marker sidebox (estados: idle / previewing / editing) ═ --}}
                <div class="edit-box marker-sidebox" :class="markerBoxClass()">
                    <header>
                        <x-lucide-map-pin class="lucid-icon" />
                        <span x-text="selectedIdx === null ? 'Marcador' : ('Marcador #' + (selectedIdx + 1))"></span>
                        <button type="button" class="admin-btn admin-btn-secondary btn-sm"
                                @click="newMarkerAtCenter()" style="margin-left: auto;"
                                title="Novo marcador no centro">
                            <x-lucide-plus class="lucid-icon" />
                        </button>
                    </header>
                    <article>
                        <template x-if="selectedIdx === null">
                            <p class="empty-hint">
                                Clique em um pino no mapa ou em um chip da lista para editar.
                                Ou <a href="#" @click.prevent="newMarkerAtCenter()">crie um novo</a>.
                            </p>
                        </template>

                        <template x-if="selectedIdx !== null">
                            <div class="marker-editor">
                                <div class="admin-form-row">
                                    <div class="form-group">
                                        <label>Latitude</label>
                                        <input type="number" step="0.0000001"
                                               x-model.number="markers[selectedIdx].lat"
                                               @change="refreshMarker(selectedIdx)">
                                    </div>
                                    <div class="form-group">
                                        <label>Longitude</label>
                                        <input type="number" step="0.0000001"
                                               x-model.number="markers[selectedIdx].lng"
                                               @change="refreshMarker(selectedIdx)">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Título</label>
                                    <input type="text" x-model="markers[selectedIdx].title"
                                           @input="refreshMarker(selectedIdx)" placeholder="Título do marcador">
                                </div>

                                <div class="form-group">
                                    <label>Balão (conteúdo do popup)</label>
                                    <textarea x-model="markers[selectedIdx].content" rows="3"
                                              @input="refreshMarker(selectedIdx)"
                                              placeholder="Texto ou HTML curto"></textarea>
                                </div>

                                <div class="admin-form-row">
                                    <div class="form-group">
                                        <label>Cor</label>
                                        <input type="color" x-model="markers[selectedIdx].color"
                                               @input="refreshMarker(selectedIdx)"
                                               style="height: 34px; padding: 2px; width: 100%;">
                                    </div>
                                    <div class="form-group" style="align-self: end;">
                                        <button type="button" class="admin-btn admin-btn-danger" style="width: 100%;"
                                                @click="removeSelected()">
                                            <x-lucide-trash-2 class="lucid-icon" /> Remover
                                        </button>
                                    </div>
                                </div>

                                {{-- Parameters (key=value) --}}
                                {{-- <div class="params-editor">
                                    <div class="params-head">
                                        <span>Parâmetros extras</span>
                                        <span class="counter" x-text="paramCount(selectedIdx)"></span>
                                    </div>
                                    <template x-for="(v, k) in getParams(selectedIdx)" :key="k">
                                        <div class="param-row">
                                            <code x-text="k"></code>
                                            <span x-text="v"></span>
                                            <button type="button" class="param-remove"
                                                    @click="removeParam(selectedIdx, k)" title="Remover">×</button>
                                        </div>
                                    </template>
                                    <div class="param-add">
                                        <input type="text" x-model="newParam.key" placeholder="nome">
                                        <input type="text" x-model="newParam.value" placeholder="valor">
                                        <button type="button" class="admin-btn admin-btn-secondary btn-sm"
                                                @click="addParam()"
                                                :disabled="!newParam.key || !newParam.value">＋</button>
                                    </div>
                                </div> --}}
                            </div>
                        </template>
                    </article>
                </div>


                {{-- ═ Import via JSON ═ --}}
                <div class="edit-box">
                    <header><x-lucide-upload class="lucid-icon" /> Importar marcadores</header>
                    <article>
                        <div class="form-group">
                            {{-- <input type="file" name="markers_json" accept=".json,application/json"> --}}
                            <x-upload-area name="markers_json" accept=".json,application/json" buttonLabel="Escolher arquivo" message="Solte um arquivo aqui para atualizar" />
                            <small>
                                Formato: array JSON com objetos <code>{ lat, lng, title, content, color }</code>.
                                Marcadores com <code>uid</code> já existente são ignorados.
                            </small>
                        </div>
                    </article>
                </div>

                {{-- ═ Ações ═ --}}
                {{-- <div class="edit-box">
                    <header><x-lucide-save class="lucid-icon" /> Ações</header>
                    <article>
                        <div class="buttons vertical">
                            <button type="submit" class="admin-btn admin-btn-primary" style="width: 100%;">
                                <x-lucide-save class="lucid-icon" />
                                {{ $map->exists ? 'Salvar alterações' : 'Criar mapa' }}
                            </button>
                            <a href="{{ route('admin.maps.index') }}"
                               class="admin-btn admin-btn-secondary" style="width: 100%; text-align: center;">
                                Cancelar
                            </a>
                        </div>
                    </article>
                </div> --}}
            </div>
        </div>

        {{-- Campos ocultos dos marcadores (renderizados pelo Alpine para postar) --}}
        <template x-for="(m, i) in markers" :key="'hf-' + i">
            <div style="display: none;">
                <input type="hidden" :name="'markers[' + i + '][id]'"    :value="m.id || ''">
                <input type="hidden" :name="'markers[' + i + '][uid]'"   :value="m.uid || ''">
                <input type="hidden" :name="'markers[' + i + '][title]'" :value="m.title || ''">
                <input type="hidden" :name="'markers[' + i + '][content]'" :value="m.content || ''">
                <input type="hidden" :name="'markers[' + i + '][lat]'"   :value="m.lat">
                <input type="hidden" :name="'markers[' + i + '][lng]'"   :value="m.lng">
                <input type="hidden" :name="'markers[' + i + '][color]'" :value="m.color || '#e74c3c'">
                <input type="hidden" :name="'markers[' + i + '][icon]'"  :value="m.icon || 'map-pin'">
                <input type="hidden" :name="'markers[' + i + '][parameters]'" :value="serializeParams(m.parameters)">
            </div>
        </template>
    </form>
</div>
@endsection

@push('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    window.mapEditorData = {
        map: {!! json_encode($map->only([
            'id','title','description','center_lat','center_lng','zoom','width','height',
            'fullwidth','show_zoom_controls','allow_drag','allow_scroll_zoom',
            'geojson_place','geojson_inline',
            'geojson_color','geojson_weight','geojson_opacity',
            'geojson_fill_color','geojson_fill_opacity',
        ])) !!},
        markers: {!! json_encode($map->exists ? $map->markers->toArray() : []) !!},
        tileUrl: {!! json_encode(setting('maps_tile_url', 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png')) !!},
        attribution: {!! json_encode(setting('maps_attribution', '&copy; OpenStreetMap')) !!},
        routes: {
            geocode:     {!! json_encode(route('api.maps.geocode')) !!},
            geojsonList: {!! json_encode(route('api.maps.geojson.index')) !!},
            geojsonShow: {!! json_encode(url('api/maps/geojson')) !!} + '/',
            geojsonFind: {!! json_encode(route('api.maps.geojson.find')) !!},
            geojsonSave: {!! json_encode(route('api.maps.geojson.save')) !!}
        },
        csrf: "{{ csrf_token() }}"
    };
</script>
<script src="{{ asset('plugins/maps/js/admin-map.js') }}"></script>
@endpush
