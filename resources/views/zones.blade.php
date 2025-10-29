<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Fishing Zones - {{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <style>[x-cloak]{display:none !important;}</style>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            @include('layouts.navigation')

            <main>
                <div class="py-12">
                    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 text-gray-900 dark:text-gray-100">
                                <h1 class="text-3xl font-bold mb-2">Fishing Zones</h1>
                                <p class="text-gray-600 dark:text-gray-400 mb-6">Interactive map showing all fishing zones and the species found in each area.</p>

                                <div id="map" class="w-full h-96 border-2 border-gray-300 dark:border-gray-600 rounded-lg mb-6"></div>

                                <div id="zonesList" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <!-- Zones will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>

        <script>
            // Initialize map
            const map = L.map('map').setView([10.3157, 123.8854], 7);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors',
                maxZoom: 19,
            }).addTo(map);

            const layers = {};

            // Fetch and display zones
            fetch('{{ route("api.zones.data") }}')
                .then(response => response.json())
                .then(data => {
                    const zonesList = document.getElementById('zonesList');
                    
                    data.zones.forEach(zone => {
                        // Add to map
                        if (zone.geometry && zone.geometry.features) {
                            const layer = L.geoJSON(zone.geometry, {
                                style: function() {
                                    return {
                                        color: zone.color,
                                        weight: 2,
                                        opacity: 0.8,
                                        fillOpacity: 0.3,
                                    };
                                },
                                onEachFeature: function(feature, layer) {
                                    layer.bindPopup(`<strong>${zone.name}</strong>`);
                                }
                            }).addTo(map);
                            
                            layers[zone.id] = layer;
                        }

                        // Add to list
                        const card = document.createElement('div');
                        card.className = 'border dark:border-gray-700 rounded-lg p-4 hover:shadow-lg transition cursor-pointer';
                        card.innerHTML = `
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-lg font-semibold">${zone.name}</h3>
                                <div class="w-6 h-6 rounded" style="background-color: ${zone.color}"></div>
                            </div>
                        `;
                        
                        card.addEventListener('click', function() {
                            if (layers[zone.id]) {
                                map.fitBounds(layers[zone.id].getBounds());
                            }
                        });
                        
                        zonesList.appendChild(card);
                    });
                })
                .catch(error => console.error('Error loading zones:', error));
        </script>
        @stack('scripts')
    </body>
</html>
