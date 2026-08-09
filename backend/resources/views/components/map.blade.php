@props([
    'mapKey' => null,
    'height' => '400px',
    'markers' => [],
    'center' => ['lat' => 40.4168, 'lng' => -3.7038],
    'zoom' => 13,
    'interactive' => true,
])

@php
    $mapId = 'map-' . uniqid();
@endphp

<div {{ $attributes->merge(['class' => 'relative overflow-hidden rounded-lg border border-slate-200']) }} data-map-key="{{ $mapKey }}">
    <div id="{{ $mapId }}" style="height: {{ $height }}; width: 100%;"></div>
</div>

@once
    @push('scripts')
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    @endpush
@endonce

@push('scripts')
<script>
    (function() {
        const mapId = '{{ $mapId }}';
        const mapKey = @json($mapKey);
        const center = @json($center);
        const zoom = {{ $zoom }};
        const markers = @json($markers);
        const interactive = {{ $interactive ? 'true' : 'false' }};

        // Esperar a que Leaflet esté disponible
        function initMap() {
            if (typeof L === 'undefined') {
                setTimeout(initMap, 100);
                return;
            }

            // Inicializar mapa
            const map = L.map(mapId, {
                center: [center.lat, center.lng],
                zoom: zoom,
                dragging: interactive,
                touchZoom: interactive,
                scrollWheelZoom: interactive,
                doubleClickZoom: interactive,
                boxZoom: interactive,
                keyboard: interactive,
                zoomControl: interactive,
            });

            // Añadir capa de tiles (OpenStreetMap)
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                maxZoom: 19,
            }).addTo(map);

            // Añadir marcadores
            markers.forEach(marker => {
                const markerObj = L.marker([marker.lat, marker.lng]).addTo(map);

                if (marker.popup) {
                    markerObj.bindPopup(marker.popup);
                }

                if (marker.tooltip) {
                    markerObj.bindTooltip(marker.tooltip);
                }
            });

            // Si solo hay un marcador, centrarlo
            if (markers.length === 1) {
                map.setView([markers[0].lat, markers[0].lng], zoom);
            }

            // Si hay múltiples marcadores, ajustar bounds
            if (markers.length > 1) {
                const bounds = L.latLngBounds(markers.map(m => [m.lat, m.lng]));
                map.fitBounds(bounds, { padding: [50, 50] });
            }

            // Invalidar tamaño después de un pequeño delay (fix para modales/tabs)
            setTimeout(() => {
                map.invalidateSize();
            }, 100);

            // Exponer el mapa globalmente para poder manipularlo desde fuera
            window['leafletMap_' + mapId] = map;
            window.dispatchEvent(new CustomEvent('leaflet-map:ready', {
                detail: { mapId, mapKey, map }
            }));
        }

        // Iniciar cuando el DOM esté listo
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initMap);
        } else {
            initMap();
        }
    })();
</script>
@endpush
