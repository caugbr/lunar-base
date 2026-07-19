/* ═══════════════════════════════════════════════════════════════
   Maps Plugin — Admin editor (Alpine component)
   Registra window.mapEditor() usado por edit.blade.php.
   Depende de: Leaflet 1.9+, Alpine 3, window.mapEditorData.
   ═══════════════════════════════════════════════════════════════ */

function mapEditor() {
    const D = window.mapEditorData || {};
    return {
        // ── Estado ──────────────────────────────────────────
        map: null,
        tileLayer: null,
        geojsonLayer: null,
        leafletMarkers: [],       // paralelo a this.markers
        selectedIdx: null,        // marker atualmente aberto no sidebox
        newParam: { key: '', value: '' },

        // Busca de endereço (Nominatim)
        searching: false,
        searchQuery: '',
        searchResults: [],

        // GeoJSON pré-cadastrados
        places: [],
        showFinder: false,
        finder: { country: '', state: '', city: '', neighborhood: '', results: [] },
        finding: false,
        savingPlace: false,
        geojsonError: '',

        // Form (bound com Alpine)
        form: {
            title: D.map.title || '',
            description: D.map.description || '',
            center_lat: parseFloat(D.map.center_lat) || 0,
            center_lng: parseFloat(D.map.center_lng) || 0,
            zoom: parseInt(D.map.zoom) || 13,
            width: parseInt(D.map.width) || 800,
            height: parseInt(D.map.height) || 500,
            fullwidth: !!D.map.fullwidth,
            show_zoom_controls: !!D.map.show_zoom_controls,
            allow_drag: D.map.allow_drag === undefined ? true : !!D.map.allow_drag,
            allow_scroll_zoom: !!D.map.allow_scroll_zoom,

            geojson_place: D.map.geojson_place || '',
            geojson_inline_raw: D.map.geojson_inline ? JSON.stringify(D.map.geojson_inline, null, 2) : '',
            geojson_color: D.map.geojson_color || '#ff7800',
            geojson_weight: parseInt(D.map.geojson_weight) || 3,
            geojson_opacity: parseFloat(D.map.geojson_opacity) || 0.8,
            geojson_fill_color: D.map.geojson_fill_color || '#ffa500',
            geojson_fill_opacity: parseFloat(D.map.geojson_fill_opacity) || 0.2,
        },

        markers: [],

        // ── Lifecycle ──────────────────────────────────────
        async init() {
            // Normaliza markers vindos do backend
            this.markers = (D.markers || []).map(m => ({
                id: m.id || null,
                uid: m.uid || this._uid(),
                title: m.title || '',
                content: m.content || '',
                lat: parseFloat(m.lat),
                lng: parseFloat(m.lng),
                color: m.color || '#e74c3c',
                icon: m.icon || 'map-pin',
                parameters: m.parameters || {},
            }));

            this.$nextTick(() => this.initMap());
            this.loadPlacesIndex();
        },

        initMap() {
            const el = document.getElementById('map-preview');
            if (!el) return;

            // Aplica largura/altura via CSS
            this._applyDimensions(el);

            this.map = L.map('map-preview', {
                center: [this.form.center_lat, this.form.center_lng],
                zoom: this.form.zoom,
                zoomControl: this.form.show_zoom_controls,
                dragging: this.form.allow_drag,
                scrollWheelZoom: this.form.allow_scroll_zoom,
            });

            this.tileLayer = L.tileLayer(D.tileUrl, {
                attribution: D.attribution,
                maxZoom: 19,
            }).addTo(this.map);

            // Sincronização: mapa → form
            this.map.on('moveend', () => {
                const c = this.map.getCenter();
                this.form.center_lat = +c.lat.toFixed(7);
                this.form.center_lng = +c.lng.toFixed(7);
                this.form.zoom = this.map.getZoom();
            });

            // Click no mapa: cria novo marker
            this.map.on('click', (e) => this.addMarkerAt(e.latlng.lat, e.latlng.lng));

            // Render markers iniciais
            this.markers.forEach((_, i) => this._buildMarker(i));

            // GeoJSON inicial
            if (this.form.geojson_place) {
                this.loadPlaceGeoJson();
            } else if (this.form.geojson_inline_raw) {
                this.applyInlineGeoJson();
            }
        },

        // ── Sincronização form → mapa ───────────────────────
        applyFormToMap() {
            if (!this.map) return;
            const el = this.map.getContainer();
            this._applyDimensions(el);

            const lat = parseFloat(this.form.center_lat);
            const lng = parseFloat(this.form.center_lng);
            const zoom = parseInt(this.form.zoom);
            if (!isNaN(lat) && !isNaN(lng)) {
                this.map.setView([lat, lng], zoom, { animate: true });
            }

            // Controles
            if (this.form.show_zoom_controls && !this.map.zoomControl._map) {
                this.map.zoomControl.addTo(this.map);
            } else if (!this.form.show_zoom_controls && this.map.zoomControl._map) {
                this.map.zoomControl.remove();
            }

            this.form.allow_drag ? this.map.dragging.enable() : this.map.dragging.disable();
            this.form.allow_scroll_zoom ? this.map.scrollWheelZoom.enable() : this.map.scrollWheelZoom.disable();

            this.map.invalidateSize();
        },

        _applyDimensions(el) {
            el.style.width = this.form.fullwidth ? '100%' : (this.form.width + 'px');
            el.style.height = this.form.height + 'px';
        },

        // ── Marcadores ──────────────────────────────────────
        _buildMarker(idx) {
            const m = this.markers[idx];
            if (!m || isNaN(m.lat) || isNaN(m.lng)) return;

            const icon = L.divIcon({
                className: 'custom-marker',
                html: `<div class="custom-pin-admin" style="background:${m.color || '#e74c3c'}">
                         <span>${idx + 1}</span>
                       </div>`,
                iconSize: [28, 28],
                iconAnchor: [14, 28],
                popupAnchor: [0, -28],
            });
            const marker = L.marker([m.lat, m.lng], { icon, draggable: true }).addTo(this.map);
            if (m.title || m.content) {
                marker.bindPopup(this._popupHtml(m.title, m.content));
            }
            marker.on('click', () => this.selectMarker(idx));
            marker.on('dragend', (e) => {
                const p = e.target.getLatLng();
                this.markers[idx].lat = +p.lat.toFixed(7);
                this.markers[idx].lng = +p.lng.toFixed(7);
                if (this.selectedIdx === idx) this.$nextTick();
            });

            this.leafletMarkers[idx] = marker;
        },

        refreshMarker(idx) {
            if (this.leafletMarkers[idx]) {
                this.map.removeLayer(this.leafletMarkers[idx]);
            }
            this._buildMarker(idx);
            // atualiza seleção visual
            if (this.selectedIdx === idx) this._highlightSelected();
        },

        addMarkerAt(lat, lng) {
            const idx = this.markers.push({
                id: null, uid: this._uid(),
                title: '', content: '',
                lat: +lat.toFixed(7), lng: +lng.toFixed(7),
                color: '#e74c3c', icon: 'map-pin',
                parameters: {},
            }) - 1;
            this._buildMarker(idx);
            this.selectMarker(idx);
        },

        newMarkerAtCenter() {
            this.addMarkerAt(this.form.center_lat, this.form.center_lng);
        },

        selectMarker(idx) {
            this.selectedIdx = idx;
            const m = this.markers[idx];
            if (m && this.map) {
                this.map.panTo([m.lat, m.lng], { animate: true });
                if (this.leafletMarkers[idx]) {
                    this.leafletMarkers[idx].openPopup();
                }
            }
            this._highlightSelected();
        },

        _highlightSelected() {
            this.leafletMarkers.forEach((mk, i) => {
                if (!mk) return;
                const el = mk.getElement();
                if (!el) return;
                el.classList.toggle('is-selected', i === this.selectedIdx);
            });
        },

        async removeSelected() {
            if (this.selectedIdx === null) return;
            const confirmed = await Dialog.confirm('Remover este marcador?');
            if (!confirmed) return;
            const idx = this.selectedIdx;
            if (this.leafletMarkers[idx]) this.map.removeLayer(this.leafletMarkers[idx]);
            this.markers.splice(idx, 1);
            this.leafletMarkers.splice(idx, 1);
            this.selectedIdx = null;
            // Recria todos para atualizar numeração
            this.leafletMarkers.forEach(mk => mk && this.map.removeLayer(mk));
            this.leafletMarkers = [];
            this.markers.forEach((_, i) => this._buildMarker(i));
        },

        async deleteAll() {
            if (this.markers.length === 0) return;
            const confirmed = await Dialog.confirm(`Remover todos os ${this.markers.length} marcadores?`);
            if (!confirmed) return;
            this.leafletMarkers.forEach(mk => mk && this.map.removeLayer(mk));
            this.markers = [];
            this.leafletMarkers = [];
            this.selectedIdx = null;
        },

        fitMarkers() {
            if (this.markers.length === 0 || !this.map) return;
            const bounds = L.latLngBounds(this.markers.map(m => [m.lat, m.lng]));
            this.map.fitBounds(bounds, { padding: [40, 40], maxZoom: 16 });
        },

        markerBoxClass() {
            return { 'is-editing': this.selectedIdx !== null };
        },

        sortedMarkerIndexes() {
            return this.markers
                .map((_, i) => i)
                .sort((a, b) => (this.markers[a].title || '').localeCompare(this.markers[b].title || ''));
        },

        // ── Parameters ──────────────────────────────────────
        getParams(idx) {
            const p = this.markers[idx]?.parameters;
            if (!p) return {};
            if (typeof p === 'string') {
                try { return Object.fromEntries(new URLSearchParams(p)); }
                catch { return {}; }
            }
            return p;
        },
        paramCount(idx) { return Object.keys(this.getParams(idx)).length; },
        addParam() {
            if (this.selectedIdx === null || !this.newParam.key) return;
            const p = { ...this.getParams(this.selectedIdx), [this.newParam.key]: this.newParam.value };
            this.markers[this.selectedIdx].parameters = p;
            this.newParam = { key: '', value: '' };
        },
        removeParam(idx, key) {
            const p = { ...this.getParams(idx) };
            delete p[key];
            this.markers[idx].parameters = p;
        },
        serializeParams(p) {
            if (!p) return '';
            if (typeof p === 'string') return p;
            return new URLSearchParams(p).toString();
        },

        // ── Busca de endereço (Nominatim via /api/maps/geocode) ─
        async searchAddress() {
            if (this.searchQuery.length < 3) return;
            this.searching = true; this.searchResults = [];
            try {
                const r = await fetch(D.routes.geocode + '?q=' + encodeURIComponent(this.searchQuery));
                const j = await r.json();
                this.searchResults = j.results || [];
            } catch (e) { console.error(e); }
            finally { this.searching = false; }
        },
        selectAddress(r) {
            this.form.center_lat = r.lat;
            this.form.center_lng = r.lng;
            this.form.zoom = Math.max(this.form.zoom, 14);
            this.searchQuery = ''; this.searchResults = [];
            this.applyFormToMap();
        },

        // ── GeoJSON: índice pré-cadastrado ─────────────────
        async loadPlacesIndex() {
            try {
                const r = await fetch(D.routes.geojsonList);
                this.places = await r.json();
            } catch (e) { this.places = []; }
        },

        async loadPlaceGeoJson() {
            this._clearGeoJson();
            if (!this.form.geojson_place) return;
            try {
                const r = await fetch(D.routes.geojsonShow + this.form.geojson_place);
                if (!r.ok) return;
                const gj = await r.json();
                this._drawGeoJson(gj);
                this.form.geojson_inline_raw = ''; // dá prioridade ao select
            } catch (e) { console.error(e); }
        },

        applyInlineGeoJson() {
            this.geojsonError = '';
            this._clearGeoJson();
            const raw = this.form.geojson_inline_raw?.trim();
            if (!raw) return;
            try {
                const gj = JSON.parse(raw);
                this._drawGeoJson(gj);
                this.form.geojson_place = ''; // dá prioridade ao inline
            } catch (e) {
                this.geojsonError = 'JSON inválido: ' + e.message;
            }
        },

        _drawGeoJson(gj) {
            this.geojsonLayer = L.geoJSON(gj, { style: this._geoJsonStyle() }).addTo(this.map);
            try {
                const b = this.geojsonLayer.getBounds();
                if (b.isValid()) this.map.fitBounds(b, { padding: [30, 30] });
            } catch {}
        },

        _clearGeoJson() {
            if (this.geojsonLayer) {
                this.map.removeLayer(this.geojsonLayer);
                this.geojsonLayer = null;
            }
        },

        _geoJsonStyle() {
            return {
                color: this.form.geojson_color,
                weight: this.form.geojson_weight,
                opacity: this.form.geojson_opacity,
                fillColor: this.form.geojson_fill_color,
                fillOpacity: this.form.geojson_fill_opacity,
            };
        },

        restyleGeoJson() {
            if (this.geojsonLayer) this.geojsonLayer.setStyle(this._geoJsonStyle());
        },

        // ── GeoJSON finder (Nominatim) ─────────────────────
        finderHasQuery() {
            return !!(this.finder.country || this.finder.state || this.finder.city || this.finder.neighborhood);
        },
        async findPlace() {
            if (!this.finderHasQuery()) return;
            this.finding = true; this.finder.results = [];
            try {
                const r = await fetch(D.routes.geojsonFind, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': D.csrf,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        country: this.finder.country,
                        state: this.finder.state,
                        city: this.finder.city,
                        neighborhood: this.finder.neighborhood,
                    }),
                });
                const j = await r.json();
                this.finder.results = (j.results || []).map(x => ({ ...x, _pid: this.suggestPid(x.name) }));
            } catch (e) { console.error(e); }
            finally { this.finding = false; }
        },
        suggestPid(name) {
            return (name || '').toLowerCase()
                .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                .replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '')
                .slice(0, 40);
        },
        async savePlace(r) {
            const pid = r._pid || this.suggestPid(r.name);
            if (!pid) {
                Dialog.alert('Informe um identificador');
                return;
            }
            this.savingPlace = true;
            try {
                const resp = await fetch(D.routes.geojsonSave, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': D.csrf,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        osm_id: r.osm_id, osm_type: r.osm_type,
                        name: r.name, pid,
                    }),
                });
                const j = await resp.json();
                await this.loadPlacesIndex();
                this.form.geojson_place = pid;
                await this.loadPlaceGeoJson();
                this.showFinder = false;
                this.finder.results = [];
            } catch (e) { console.error(e); }
            finally { this.savingPlace = false; }
        },

        // ── Utils ──────────────────────────────────────────
        format(v) { return typeof v === 'number' ? v.toFixed(5) : (v || '—'); },
        _uid() { return 'uid_' + Math.random().toString(36).substring(2, 8); },
        _popupHtml(title, content) {
            const parts = [];
            if (title) parts.push(`<strong>${this._esc(title)}</strong>`);
            if (content) parts.push(content); // content permite HTML
            return parts.join('<br>');
        },
        _esc(s) {
            const d = document.createElement('div'); d.textContent = s; return d.innerHTML;
        },
        copyShortcode(sc) {
            navigator.clipboard.writeText(sc).then(() => {
                // feedback simples
            });
        },
    };
}

// Expõe global para o Alpine
window.mapEditor = mapEditor;
