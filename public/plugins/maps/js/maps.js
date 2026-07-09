/* ═══════════════════════════════════════════════════════════════
   Maps Plugin — Public runtime
   Renderiza mapas + marcadores + GeoJSON no front.
   Config vem de window.lunarMaps[mapId].
   ═══════════════════════════════════════════════════════════════ */

(function () {
    'use strict';

    if (typeof L === 'undefined') {
        console.error('Maps: Leaflet not loaded'); return;
    }

    function createIcon(colorHex, idx) {
        return L.divIcon({
            className: 'custom-marker-public',
            html: `<div class="custom-pin-public" style="background:${colorHex || '#e74c3c'}">
                     <span>${idx + 1}</span>
                   </div>`,
            iconSize: [28, 28],
            iconAnchor: [14, 28],
            popupAnchor: [0, -28],
        });
    }

    function popupHtml(title, content) {
        const p = [];
        if (title) p.push('<strong>' + escapeHtml(title) + '</strong>');
        if (content) p.push(content);
        return p.join('<br>');
    }

    function escapeHtml(s) {
        const d = document.createElement('div'); d.textContent = s; return d.innerHTML;
    }

    async function loadGeoJson(cfg) {
        // Prioridade 1: inline (já vem no cfg)
        if (cfg.geojson_inline) return cfg.geojson_inline;
        // Prioridade 2: place pré-cadastrado
        if (cfg.geojson_place && cfg.geojson_url) {
            try {
                const r = await fetch(cfg.geojson_url + cfg.geojson_place);
                if (r.ok) return await r.json();
            } catch (e) { console.warn('GeoJSON load failed', e); }
        }
        return null;
    }

    async function initMap(mapId, cfg) {
        const el = document.getElementById(mapId);
        if (!el) return;

        el.style.width = cfg.fullwidth ? '100%' : (cfg.width + 'px');
        el.style.height = cfg.height + 'px';
        if (cfg.fullwidth) el.classList.add('is-fullwidth');

        const map = L.map(mapId, {
            center: [parseFloat(cfg.center_lat), parseFloat(cfg.center_lng)],
            zoom: parseInt(cfg.zoom),
            zoomControl: cfg.show_zoom_controls !== false,
            dragging: cfg.allow_drag !== false,
            scrollWheelZoom: cfg.allow_scroll_zoom !== false,
        });

        L.tileLayer(cfg.tile_url || 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: cfg.attribution || '&copy; OpenStreetMap',
            maxZoom: 19,
        }).addTo(map);

        // Markers
        if (Array.isArray(cfg.markers)) {
            cfg.markers.forEach((m, i) => {
                if (!m.lat || !m.lng) return;
                const marker = L.marker([m.lat, m.lng], { icon: createIcon(m.color, i) }).addTo(map);
                if (m.title || m.content) marker.bindPopup(popupHtml(m.title, m.content));
            });
        }

        // GeoJSON
        const gj = await loadGeoJson(cfg);
        if (gj) {
            L.geoJSON(gj, {
                style: {
                    color: cfg.geojson_style?.color || '#ff7800',
                    weight: cfg.geojson_style?.weight || 3,
                    opacity: cfg.geojson_style?.opacity || 0.8,
                    fillColor: cfg.geojson_style?.fillColor || '#ffa500',
                    fillOpacity: cfg.geojson_style?.fillOpacity || 0.2,
                }
            }).addTo(map);
        }

        return map;
    }

    function initAll() {
        const data = window.lunarMaps || {};
        Object.keys(data).forEach(id => initMap(id, data[id]));
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }
    window.initLunarMaps = initAll;
})();
