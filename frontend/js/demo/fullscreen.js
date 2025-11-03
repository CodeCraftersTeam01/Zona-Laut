  // Global variables
        let map;
        let zoneLayers = [];
        let vesselMarkers = [];
        let dpiData = [];
        let kapalData = [];
        let vesselMovementEnabled = true;
        let vesselMovementInterval;
        let currentTileLayer;
        let controlPanelVisible = true;
        let geocoder;

        // Initialize fullscreen map
        async function initFullScreenMap() {
            // Initialize map
            map = L.map('fullscreenMap', {
                zoomControl: false,
                attributionControl: true
            });

            // Add default OpenStreetMap tiles
            currentTileLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: 'ZONA LAUT',
                maxZoom: 18,
            }).addTo(map);

            // Initialize geocoder for location search
            geocoder = L.Control.geocoder({
                defaultMarkGeocode: false,
                position: 'topleft'
            }).on('markgeocode', function(e) {
                const {
                    center,
                    name
                } = e.geocode;
                map.setView(center, 13);

                // Add marker for searched location
                L.marker(center).addTo(map)
                    .bindPopup(`<b>${name}</b><br>Lat: ${center.lat.toFixed(4)}, Lng: ${center.lng.toFixed(4)}`)
                    .openPopup();
            }).addTo(map);

            // Add scale control
            L.control.scale({
                imperial: false,
                position: 'bottomright'
            }).addTo(map);

            // Set initial view
            map.setView([-6.2000, 106.8166], 10);

            // Update coordinates on mouse move
            map.on('mousemove', function(e) {
                document.getElementById('latCoord').textContent = e.latlng.lat.toFixed(4);
                document.getElementById('lngCoord').textContent = e.latlng.lng.toFixed(4);
            });

            // Load data
            const dpiLoaded = await fetchDPIData();
            const kapalLoaded = await fetchKapalData();

            if (!dpiLoaded || !kapalLoaded) {
                initDemoData();
                return;
            }

            // Initialize zones and vessels
            initializeZones();
            initializeVessels();
            updateVesselsList();

            // Adjust UI for mobile
            adjustUIForMobile();
        }

        // Fetch data from backend
        async function fetchDPIData() {
            try {
                const response = await fetch('../../backend/system/dpi.php');
                const data = await response.json();

                if (data.success) {
                    dpiData = data.data;
                    console.log('DPI Data loaded:', dpiData);
                    return true;
                } else {
                    throw new Error(data.message || 'Failed to fetch DPI data');
                }
            } catch (error) {
                console.error('Error fetching DPI data:', error);
                showNotification('Gagal memuat data DPI', 'error');
                return false;
            }
        }

        async function fetchKapalData() {
            try {
                const response = await fetch('../../backend/system/kapal.php');
                const data = await response.json();

                if (data.success) {
                    // Filter hanya kapal dengan status 1 (aktif)
                    // 🔹 Filter hanya kapal dengan status 1 (aktif) dan verified_at tidak null
                    if (Array.isArray(data.data)) {
                        kapalData = data.data.filter(kapal => kapal.status === 1 && kapal.verified_at !== null);
                    } else if (typeof data.data === 'object' && data.data !== null) {
                        // Jika data single object, cek dua kondisi
                        kapalData = (kapal.status === 1 && kapal.verified_at !== null) ? [data.data] : [];
                    } else {
                        kapalData = [];
                    }
                    console.log('Kapal Data loaded (aktif saja):', kapalData);
                    return true;
                } else {
                    throw new Error(data.message || 'Failed to fetch kapal data');
                }
            } catch (error) {
                console.error('Error fetching kapal data:', error);
                showNotification('Gagal memuat data kapal', 'error');
                return false;
            }
        }

        // Initialize zones on map
        function initializeZones() {
            // Clear existing zone layers
            zoneLayers.forEach(layer => {
                if (map.hasLayer(layer)) {
                    map.removeLayer(layer);
                }
            });
            zoneLayers = [];

            // Process DPI data and create zones
            if (dpiData.length > 0) {
                const bounds = [];

                dpiData.forEach(dpi => {
                    if (dpi.location) {
                        const [lat, lng] = dpi.location.split(',').map(coord => parseFloat(coord.trim()));
                        bounds.push([lat, lng]);

                        const radius = Math.sqrt(dpi.luas * 1000000 / Math.PI);

                        // Determine zone color based on type
                        let color = '#3b82f6'; // Default blue for fishing zones
                        if (dpi.nama_dpi.toLowerCase().includes('konservasi')) {
                            color = '#10b981'; // Green for conservation
                        } else if (dpi.nama_dpi.toLowerCase().includes('terlarang')) {
                            color = '#ef4444'; // Red for restricted
                        }

                        const circle = L.circle([lat, lng], {
                            color: color,
                            fillColor: color,
                            fillOpacity: 0.1,
                            weight: 2,
                            radius: radius
                        }).addTo(map);

                        circle.bindPopup(`
                            <div class="p-3 min-w-48">
                                <h3 class="font-bold text-lg text-gray-800">${dpi.nama_dpi}</h3>
                                <div class="space-y-1 text-sm text-gray-600">
                                    <div class="flex justify-between">
                                        <span>Luas:</span>
                                        <span class="font-medium">${dpi.luas} km²</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Radius:</span>
                                        <span class="font-medium">${Math.round(radius/1000)} km</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Kapal Aktif:</span>
                                        <span class="font-medium">${getVesselCountInDPI(dpi.id)}</span>
                                    </div>
                                </div>
                            </div>
                        `);

                        zoneLayers.push(circle);
                    }
                });

                // Fit map to show all zones
                if (bounds.length > 0) {
                    const group = new L.featureGroup(bounds.map(coord => L.marker(coord)));
                    map.fitBounds(group.getBounds().pad(0.1));
                }
            }
        }

        // Vessel icon creation
        function createVesselIcon(type = 'trawler') {
            const colors = {
                trawler: '#3b82f6',
                longliner: '#10b981',
                'purse-seine': '#8b5cf6',
                default: '#6b7280'
            };

            const color = colors[type] || colors.default;

            return L.divIcon({
                className: `vessel-marker vessel-${type}`,
                html: `
                    <div class="vessel-simple" style="border-color: ${color}">
                        <i class="fas fa-ship" style="color: ${color}"></i>
                    </div>
                `,
                iconSize: [32, 32],
                iconAnchor: [16, 16]
            });
        }

        // Calculate random position within circle radius
        function getRandomPositionInCircle(center, radiusMeters) {
            const [centerLat, centerLng] = center;

            // Convert radius from meters to degrees
            const latRadius = radiusMeters / 111320;
            const lngRadius = radiusMeters / (111320 * Math.cos(centerLat * Math.PI / 180));

            // Generate random point within circle
            const r = Math.sqrt(Math.random());
            const theta = Math.random() * 2 * Math.PI;

            const randomLat = centerLat + (r * latRadius * Math.cos(theta));
            const randomLng = centerLng + (r * lngRadius * Math.sin(theta));

            return [randomLat, randomLng];
        }

        // Initialize vessels on map - HANYA KAPAL AKTIF
        function initializeVessels() {
            // Clear existing vessel markers
            vesselMarkers.forEach(({
                marker
            }) => {
                if (marker && map.hasLayer(marker)) {
                    map.removeLayer(marker);
                }
            });
            vesselMarkers = [];

            // Filter hanya kapal dengan status 1 (aktif)
            const activeKapalData = kapalData.filter(kapal => kapal.status === 1);

            // Add only active vessels to map
            activeKapalData.forEach(kapal => {
                if (kapal.id_dpi) {
                    const dpi = dpiData.find(d => d.id == kapal.id_dpi);
                    if (dpi && dpi.location) {
                        const [centerLat, centerLng] = dpi.location.split(',').map(coord => parseFloat(coord.trim()));
                        const radius = Math.sqrt(dpi.luas * 1000000 / Math.PI);

                        // Generate atau gunakan posisi yang sudah ada
                        let vesselPosition;
                        if (kapal.currentPosition) {
                            vesselPosition = kapal.currentPosition;
                        } else {
                            // Generate posisi random dalam radius 80%
                            vesselPosition = getRandomPositionInCircle([centerLat, centerLng], radius * 0.8);
                            kapal.currentPosition = vesselPosition;
                        }

                        const vesselType = kapal.jenis_kapal?.toLowerCase().includes('long') ? 'longliner' :
                            kapal.jenis_kapal?.toLowerCase().includes('purse') ? 'purse-seine' : 'trawler';

                        // Create marker
                        const marker = L.marker(vesselPosition, {
                            icon: createVesselIcon(vesselType)
                        }).addTo(map);

                        // Bind popup dengan informasi kapal
                        marker.bindPopup(`
                            <div class="p-3 min-w-48">
                                <h4 class="font-bold text-lg text-gray-800">${kapal.nama_kapal}</h4>
                                <div class="space-y-1 text-sm text-gray-600">
                                    <div class="flex justify-between">
                                        <span>Jenis:</span>
                                        <span class="font-medium">${kapal.jenis_kapal}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Pemilik:</span>
                                        <span class="font-medium">${kapal.nama_pemilik}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Zona:</span>
                                        <span class="font-medium">${kapal.nama_dpi || 'Tidak ada'}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Status:</span>
                                        <span class="font-medium text-green-600">Aktif</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Koordinat:</span>
                                        <span class="font-mono text-xs">${vesselPosition[0].toFixed(6)}, ${vesselPosition[1].toFixed(6)}</span>
                                    </div>
                                </div>
                            </div>
                        `);

                        vesselMarkers.push({
                            marker: marker,
                            vesselId: kapal.id,
                            dpi: dpi
                        });
                    }
                }
            });

            // Start vessel movement
            if (vesselMovementEnabled) {
                startVesselMovement();
            }

            // Log jumlah kapal aktif
            console.log(`Kapal aktif yang ditampilkan di peta: ${activeKapalData.length} dari total ${kapalData.length} kapal`);
        }

        // Move vessel within its DPI zone
        function moveVesselWithinZone(vesselMarker, dpi, vesselId) {
            if (!vesselMovementEnabled || !vesselMarker) return;

            const [centerLat, centerLng] = dpi.location.split(',').map(coord => parseFloat(coord.trim()));
            const radius = Math.sqrt(dpi.luas * 1000000 / Math.PI);

            // Get current position
            const currentPos = vesselMarker.getLatLng();

            // Generate small movement (5% of zone radius)
            const movementRadius = radius * 0.05;
            const newPosition = getRandomPositionInCircle([currentPos.lat, currentPos.lng], movementRadius);

            // Update marker position
            vesselMarker.setLatLng(newPosition);

            // Update vessel data
            const vessel = kapalData.find(k => k.id === vesselId);
            if (vessel) {
                vessel.currentPosition = newPosition;
            }
        }

        // Initialize vessel movement
        function startVesselMovement() {
            if (vesselMovementInterval) {
                clearInterval(vesselMovementInterval);
            }

            vesselMovementInterval = setInterval(() => {
                vesselMarkers.forEach((markerData) => {
                    const vessel = kapalData.find(k => k.id === markerData.vesselId);
                    if (vessel && vessel.id_dpi && markerData.marker) {
                        const dpi = dpiData.find(d => d.id == vessel.id_dpi);
                        if (dpi) {
                            moveVesselWithinZone(markerData.marker, dpi, vessel.id);
                        }
                    }
                });
            }, 5000);
        }

        function stopVesselMovement() {
            if (vesselMovementInterval) {
                clearInterval(vesselMovementInterval);
                vesselMovementInterval = null;
            }
        }

        function toggleVesselMovement() {
            vesselMovementEnabled = !vesselMovementEnabled;

            const icon = document.getElementById('movementIcon');
            const text = document.getElementById('movementText');

            if (vesselMovementEnabled) {
                icon.className = 'fas fa-pause mr-2';
                text.textContent = 'Pause';
                startVesselMovement();
                showNotification('Pergerakan kapal diaktifkan', 'success');
            } else {
                icon.className = 'fas fa-play mr-2';
                text.textContent = 'Play';
                stopVesselMovement();
                showNotification('Pergerakan kapal dijeda', 'info');
            }
        }

        function getVesselCountInDPI(dpiId) {
            // Hanya hitung kapal dengan status 1 (aktif)
            return kapalData.filter(kapal => kapal.id_dpi == dpiId && kapal.status === 1).length;
        }

        function updateVesselsList() {
            const vesselsList = document.getElementById('vesselsList');
            const vesselCount = document.getElementById('vesselCount');

            vesselsList.innerHTML = '';

            // Filter hanya kapal dengan status 1 (aktif)
            const activeKapalData = kapalData.filter(kapal => kapal.status === 1);
            vesselCount.textContent = activeKapalData.length;

            if (activeKapalData.length === 0) {
                vesselsList.innerHTML = `
                    <div class="text-center py-4 text-gray-500">
                        <i class="fas fa-ship text-lg mb-2"></i>
                        <p class="text-sm">Tidak ada kapal aktif</p>
                    </div>
                `;
                return;
            }

            activeKapalData.forEach(kapal => {
                const vesselType = kapal.jenis_kapal?.toLowerCase().includes('long') ? 'Long Liner' :
                    kapal.jenis_kapal?.toLowerCase().includes('purse') ? 'Purse Seine' : 'Trawler';

                const vesselElement = document.createElement('div');
                vesselElement.className = 'flex items-center justify-between p-3 bg-blue-50 rounded-lg hover:bg-blue-100 transition duration-200 cursor-pointer';
                vesselElement.innerHTML = `
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                            <i class="fas fa-ship text-white text-xs"></i>
                        </div>
                        <div>
                            <p class="text-blue-900 font-bold text-sm">${kapal.nama_kapal}</p>
                            <p class="text-blue-600 text-xs">${vesselType}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs font-medium">Aktif</span>
                    </div>
                `;

                // Add click event to focus on vessel
                vesselElement.addEventListener('click', () => {
                    const vesselMarker = vesselMarkers.find(v => v.vesselId === kapal.id);
                    if (vesselMarker && vesselMarker.marker) {
                        map.setView(vesselMarker.marker.getLatLng(), 13);
                        vesselMarker.marker.openPopup();
                        // Close control panel on mobile after selection
                        if (window.innerWidth < 768) {
                            toggleControlPanel();
                        }
                    }
                });

                vesselsList.appendChild(vesselElement);
            });
        }

        // Control functions
        function toggleControlPanel() {
            const controlPanel = document.getElementById('controlPanel');
            controlPanelVisible = !controlPanelVisible;

            controlPanel.style.transition = 'all 0.3s ease-in-out';
            if (controlPanelVisible) {
                controlPanel.classList.remove('hidden');
                controlPanel.classList.remove('-translate-x-full');
                controlPanel.classList.add('translate-x-0');
            } else {
                controlPanel.classList.add('-translate-x-full');
                controlPanel.classList.remove('translate-x-0');
            }
        }

        function toggleFullScreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(err => {
                    console.error(`Error attempting to enable fullscreen: ${err.message}`);
                });
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                }
            }
        }

        function showCurrentLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    position => {
                        const {
                            latitude,
                            longitude
                        } = position.coords;
                        map.setView([latitude, longitude], 13);

                        // Add marker for current location
                        L.marker([latitude, longitude])
                            .addTo(map)
                            .bindPopup('<b>Lokasi Anda</b>')
                            .openPopup();
                    },
                    error => {
                        showNotification('Tidak dapat mengakses lokasi Anda', 'error');
                        console.error('Geolocation error:', error);
                    }
                );
            } else {
                showNotification('Browser tidak mendukung geolocation', 'error');
            }
        }

        function zoomIn() {
            map.zoomIn();
        }

        function zoomOut() {
            map.zoomOut();
        }

        function resetView() {
            if (dpiData.length > 0) {
                const bounds = dpiData
                    .filter(dpi => dpi.location)
                    .map(dpi => {
                        const [lat, lng] = dpi.location.split(',').map(coord => parseFloat(coord.trim()));
                        return [lat, lng];
                    });

                if (bounds.length > 0) {
                    const group = new L.featureGroup(bounds.map(coord => L.marker(coord)));
                    map.fitBounds(group.getBounds().pad(0.1));
                }
            } else {
                map.setView([-6.2000, 106.8166], 10);
            }
        }

        function refreshData() {
            showNotification('Memperbarui data...', 'info');

            // Stop vessel movement during refresh
            stopVesselMovement();

            // Reload data from backend
            Promise.all([fetchDPIData(), fetchKapalData()]).then(([dpiSuccess, kapalSuccess]) => {
                if (dpiSuccess && kapalSuccess) {
                    // Reinitialize with new data
                    initializeZones();
                    initializeVessels();
                    updateVesselsList();
                    showNotification('Data berhasil diperbarui', 'success');
                } else {
                    showNotification('Gagal memuat data terbaru', 'error');
                }
            });
        }

        function toggleVesselLayer() {
            const showVessels = document.getElementById('vesselLayer').checked;

            vesselMarkers.forEach(({
                marker
            }) => {
                if (showVessels) {
                    map.addLayer(marker);
                } else {
                    map.removeLayer(marker);
                }
            });

            showNotification(showVessels ? 'Layer kapal ditampilkan' : 'Layer kapal disembunyikan', 'info');
        }

        function toggleZoneLayer() {
            const showZones = document.getElementById('zoneLayer').checked;

            zoneLayers.forEach(layer => {
                if (showZones) {
                    map.addLayer(layer);
                } else {
                    map.removeLayer(layer);
                }
            });

            showNotification(showZones ? 'Layer zona ditampilkan' : 'Layer zona disembunyikan', 'info');
        }

        function toggleSatelliteLayer() {
            const useSatellite = document.getElementById('satelliteLayer').checked;

            // Remove current tile layer
            map.removeLayer(currentTileLayer);

            // Add new tile layer based on selection
            if (useSatellite) {
                currentTileLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                    attribution: 'ZONA LAUT | Tiles &copy; Esri'
                }).addTo(map);
                showNotification('Tampilan satelit diaktifkan', 'info');
            } else {
                currentTileLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: 'ZONA LAUT'
                }).addTo(map);
                showNotification('Tampilan peta standar diaktifkan', 'info');
            }
        }

        function exitFullScreen() {
            if (document.fullscreenElement) {
                document.exitFullscreen();
            }
            window.location.href = "./";
        }

        function showNotification(message, type) {
            const existingNotification = document.querySelector('.notification-toast');
            if (existingNotification) existingNotification.remove();

            const bgColor = type === 'success' ? 'bg-green-500' :
                type === 'error' ? 'bg-red-500' :
                type === 'info' ? 'bg-blue-500' : 'bg-gray-500';

            const notification = document.createElement('div');
            notification.className = `notification-toast fixed top-4 right-4 ${bgColor} text-white p-4 rounded-lg shadow-lg z-50 transform transition-all duration-300`;
            notification.innerHTML = `
                <div class="flex items-center space-x-3">
                    <i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'exclamation-triangle' : 'info'}-circle"></i>
                    <span class="font-medium">${message}</span>
                </div>
            `;

            document.body.appendChild(notification);

            setTimeout(() => {
                notification.style.opacity = '0';
                notification.style.transform = 'translateY(-10px)';
                setTimeout(() => {
                    if (notification.parentNode) notification.parentNode.removeChild(notification);
                }, 300);
            }, 3000);
        }

        function adjustUIForMobile() {
            const mobileMenuButton = document.getElementById('mobileMenuButton');
            const controlPanel = document.getElementById('controlPanel');

            if (window.innerWidth < 768) {
                // Hide control panel by default on mobile
                controlPanel.classList.add('-translate-x-full');
                controlPanelVisible = false;

                // Show mobile menu button
                mobileMenuButton.classList.remove('hidden');

                // Adjust panel width for mobile
                controlPanel.classList.remove('w-80');
                controlPanel.classList.add('w-72');
            } else {
                // Show control panel by default on desktop
                controlPanel.classList.remove('-translate-x-full');
                controlPanelVisible = true;

                // Hide mobile menu button
                mobileMenuButton.classList.add('hidden');

                // Reset panel width for desktop
                controlPanel.classList.remove('w-72');
                controlPanel.classList.add('w-80');
            }
        }

        function initDemoData() {
            console.log('Using demo data as fallback');
            dpiData = [{
                "id": 14,
                "nama_dpi": "laut selatan",
                "luas": 122,
                "location": "-6.853486362532471,113.71673583984376"
            }];

            // Demo data dengan status
            kapalData = [{
                "id": 2,
                "nama_kapal": "TDR-3000",
                "id_pemilik": 1,
                "jenis_kapal": "Kapal Nelayan",
                "id_dpi": 14,
                "nama_pemilik": "arjuna",
                "nama_dpi": "laut selatan",
                "status": 1 // Aktif
            }, {
                "id": 3,
                "nama_kapal": "Kapal Nonaktif",
                "id_pemilik": 1,
                "jenis_kapal": "Kapal Penangkap Ikan",
                "id_dpi": 14,
                "nama_pemilik": "demo",
                "nama_dpi": "laut selatan",
                "status": 0 // Nonaktif
            }];

            // Initialize with demo data
            initializeZones();
            initializeVessels();
            updateVesselsList();
            showNotification('Menggunakan data demo - hanya kapal aktif yang ditampilkan', 'info');
        }

        // Initialize when page loads
        document.addEventListener('DOMContentLoaded', function() {
            initFullScreenMap();

            // Setup mobile menu button
            document.getElementById('mobileMenuButton').addEventListener('click', toggleControlPanel);

            // Handle window resize
            window.addEventListener('resize', adjustUIForMobile);

            // Auto-refresh every 60 seconds
            setInterval(refreshData, 60000);
        });