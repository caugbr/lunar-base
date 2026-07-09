(function() {
    'use strict';

    if (typeof L === 'undefined') {
        console.error('Map Locations: Leaflet library not loaded');
        return;
    }

    const mapData = window.lunarMaps || {};
    if (Object.keys(mapData).length === 0) return;

    function createColoredIcon(colorHex, markerIndex) {
        return L.divIcon({
            className: 'custom-marker-public',
            html: `<div style="background:${colorHex || '#e74c3c'};width:28px;height:28px;border-radius:50% 50% 50% 0;transform:rotate(-45deg);border:2px solid white;box-shadow:0 2px 6px rgba(0,0,0,0.3);display:flex;align-items:center;justify-content:center;"><span style="transform:rotate(45deg);color:white;font-size:12px;font-weight:bold;">${markerIndex + 1}</span></div>`,
            iconSize: [28, 28],
            iconAnchor: [14, 28],
            popupAnchor: [0, -28],
        });
    }

    function initMap(mapId, config) {
        const mapEl = document.getElementById(mapId);
        if (!mapEl) return;

        const map = L.map(mapId, {
            center: [parseFloat(config.center_lat), parseFloat(config.center_lng)],
            zoom: parseInt(config.zoom),
            zoomControl: config.show_zoom_controls !== false,
            dragging: config.allow_drag !== false,
            scrollWheelZoom: config.allow_scroll_zoom !== false,
        });

        L.tileLayer(config.tile_url || 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: config.attribution || '&copy; OpenStreetMap',
            maxZoom: 19,
        }).addTo(map);

        if (config.markers && Array.isArray(config.markers)) {
            config.markers.forEach((marker, index) => {
                if (!marker.lat || !marker.lng) return;

                const icon = createColoredIcon(marker.color, index);
                const leafletMarker = L.marker([marker.lat, marker.lng], { icon }).addTo(map);

                if (marker.title || marker.content) {
                    leafletMarker.bindPopup(`<strong>${marker.title || 'Marcador'}</strong>${marker.content ? '<br>' + marker.content : ''}`);
                }
            });
        }

        return map;
    }

    function initAllMaps() {
        Object.keys(mapData).forEach(mapId => initMap(mapId, mapData[mapId]));
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAllMaps);
    } else {
        initAllMaps();
    }

    window.initLunarMaps = initAllMaps;
})();
