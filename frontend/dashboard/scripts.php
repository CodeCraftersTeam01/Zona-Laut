<script src="../js/dashboard/handleresize.js"></script>
<script src="../js/dashboard/sidebar.js"></script>
<script src="../js/dashboard/auth.js"></script>
<script type="module">
    // Professional Toast System
    import {
        ToastSystem
    } from '../js/dashboard/toast.js';
    import {
        WeatherSystem
    } from '../js/dashboard/bmkg.js';
    import {
        VesselSearchSystem
    } from '../js/dashboard/vessel_search.js';

    import {
        ControlPanelSlider
    } from '../js/dashboard/slider.js';


    // Initialize systems
    let vesselSearchSystem;
    const toastSystem = new ToastSystem();

    function updateSearchData() {
        if (vesselSearchSystem) {
            vesselSearchSystem.updateVesselData(kapalData);
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


    // Initialize dashboard
    function initDashboard() {
        checkAuth();

        // Show welcome message
        setTimeout(() => {
            const user = JSON.parse(localStorage.getItem('currentUser'));
            toastSystem.success('Selamat Datang!', `Halo ${user.username}, selamat menggunakan Zona Laut Dashboard`);
        }, 1000);
    }

    let map;
    let zoneLayers = [];
    let vesselMarkers = [];
    let dpiData = [];
    let kapalData = [];
    // Map type toggle functionality
    let satelliteLayer;
    let isSatelliteView = false;
    let myKapalData = [];
    let vesselMovementEnabled = true;
    let vesselMovementInterval;
    let currentTileLayer;
    let geocoder;
    let controlPanelSlider;

    // Initialize fullscreen map
    async function initFullScreenMap() {
        // Initialize map
        map = L.map('dashboardMap', {
            zoomControl: false,
            attributionControl: true
        });

        // Add default OpenStreetMap tiles
        currentTileLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: 'ZONA LAUT',
            maxZoom: 18,
        }).addTo(map);

        // Set initial view
        map.setView([-6.2000, 106.8166], 10);

        // Update coordinates on mouse move
        map.on('mousemove', function(e) {
            const latElement = document.getElementById('latCoord');
            const lngElement = document.getElementById('lngCoord');
            if (latElement && lngElement) {
                latElement.textContent = e.latlng.lat.toFixed(4);
                lngElement.textContent = e.latlng.lng.toFixed(4);
            }
        });

        // Setup event listeners
        setupEventListeners();

        // Load data
        const dpiLoaded = await fetchDPIData();
        const kapalLoaded = await fetchKapalData();
        const myKapalLoaded = await MyKapalData();

        if (!dpiLoaded || !kapalLoaded) {
            initDemoData();
            return;
        }

        // Initialize zones and vessels
        initializeZones();
        initializeVessels();
        updateVesselsList();

        // INISIALISASI VESSEL SEARCH SYSTEM SETELAH SEMUA DATA READY
        vesselSearchSystem = new VesselSearchSystem({
            vesselMarkers: vesselMarkers,
            toastSystem: toastSystem,
            map: map,
            createVesselIcon: createVesselIcon
        });

        // Update data search
        updateSearchData();
    }

    function initMapTypeToggle() {
        const mapTypeBtns = document.querySelectorAll('.map-type-btn');
        const mapTypeLabel = document.getElementById('mapTypeLabel');

        mapTypeBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const type = btn.dataset.type;

                // Update button states
                mapTypeBtns.forEach(b => {
                    b.classList.remove('active', 'bg-white', 'text-primary', 'shadow-sm');
                    b.classList.add('text-gray-600');
                });

                btn.classList.add('active', 'bg-white', 'text-primary', 'shadow-sm');
                btn.classList.remove('text-gray-600');

                // Update map
                toggleMapType(type);
                mapTypeLabel.textContent = type === 'satellite' ? 'Satelit' : 'Standard';
            });
        });
    }

    function toggleMapType(type) {
        if (type === 'satellite' && !isSatelliteView) {
            // Switch to satellite view
            if (currentTileLayer) {
                map.removeLayer(currentTileLayer);
            }

            satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community',
                maxZoom: 18
            }).addTo(map);

            currentTileLayer = satelliteLayer;
            isSatelliteView = true;

            toastSystem.success('Mode Peta', 'Berhasil beralih ke tampilan satelit');

        } else if (type === 'standard' && isSatelliteView) {
            // Switch to standard view
            if (satelliteLayer) {
                map.removeLayer(satelliteLayer);
            }

            currentTileLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: 'ZONA LAUT',
                maxZoom: 18,
            }).addTo(map);

            isSatelliteView = false;

            toastSystem.info('Mode Peta', 'Berhasil beralih ke tampilan standar');
        }
    }

    // Setup event listeners
    // Initialize slider functionality dengan enhanced styles
    function initControlPanelSlider() {
        const slider = document.getElementById('slider');
        const controlPanel = document.getElementById('controlPanel');

        if (!slider || !controlPanel) {
            console.warn('❌ Slider or Control Panel element not found');
            return;
        }

        controlPanelSlider = new ControlPanelSlider();

        // Enhanced double click functionality
        slider.addEventListener('dblclick', (e) => {
            e.preventDefault();
            controlPanelSlider.toggle();

            // Haptic feedback
            if (navigator.vibrate) {
                navigator.vibrate(15);
            }
        });

        // Enhanced hover effects
        slider.style.cursor = 'grab';
        slider.style.transition = 'all 0.3s cubic-bezier(0.25, 0.1, 0.25, 1)';

        slider.addEventListener('mouseenter', () => {
            slider.style.backgroundColor = 'rgba(0, 0, 0, 0.1)';
            slider.style.transform = 'scale(1.05)';
        });

        slider.addEventListener('mouseleave', () => {
            if (!controlPanelSlider.isDragging) {
                slider.style.backgroundColor = '#DEDEDE';
                slider.style.transform = 'scale(1)';
            }
        });

        // console.log('🎯 Enhanced Control Panel Slider initialized');
    }

    // Enhanced initialization dengan error handling
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            if (document.getElementById('slider') && document.getElementById('controlPanel')) {
                initControlPanelSlider();
                // console.log('✅ Enhanced Control Panel Slider initialized successfully');
            }
        }, 500);
    });

    window.addEventListener('load', function() {
        if (!controlPanelSlider && document.getElementById('slider')) {
            setTimeout(initControlPanelSlider, 300);
        }
    });

    // Update existing setupEventListeners
    function setupEventListeners() {
        // Mobile menu toggle
       

        // Vessel movement toggle
        document.getElementById('toggleMovement').addEventListener('click', toggleVesselMovement);
        initControlPanelSlider();
        initMapTypeToggle();

        // Initialize enhanced control panel slider
        if (!controlPanelSlider && document.getElementById('slider')) {
            setTimeout(initControlPanelSlider, 200);
        }
    }

    // Initialize demo data jika fetch gagal
    function initDemoData() {
        // console.log('Menggunakan data demo...');

        // Demo DPI data
        dpiData = [{
                id: 1,
                nama_dpi: 'Zona Penangkapan Utara',
                luas: 500,
                location: '-6.1000,106.9000'
            },
            {
                id: 2,
                nama_dpi: 'Zona Konservasi Selatan',
                luas: 300,
                location: '-6.3000,106.7000'
            }
        ];

        // Demo kapal data - dengan status
        kapalData = [{
                id: 1,
                nama_kapal: 'KM. Bahari Jaya',
                jenis_kapal: 'Trawler',
                nama_pemilik: 'PT. Laut Sejahtera',
                id_dpi: 1,
                nama_dpi: 'Zona Penangkapan Utara',
                status: 1 // Aktif
            },
            {
                id: 2,
                nama_kapal: 'KM. Nusantara',
                jenis_kapal: 'Long Liner',
                nama_pemilik: 'CV. Samudra Makmur',
                id_dpi: 1,
                nama_dpi: 'Zona Penangkapan Utara',
                status: 0 // Nonaktif
            },
            {
                id: 3,
                nama_kapal: 'KM. Maritim',
                jenis_kapal: 'Purse Seine',
                nama_pemilik: 'PT. Perikanan Indonesia',
                id_dpi: 2,
                nama_dpi: 'Zona Konservasi Selatan',
                status: 1 // Aktif
            }
        ];

        // Filter hanya kapal aktif untuk myKapalData
        myKapalData = kapalData.filter(kapal => kapal.status === 1);

        // Initialize zones and vessels dengan data demo
        initializeZones();
        initializeVessels();
        updateVesselsList();

        // Show control panels
        document.getElementById('controlPanel').classList.remove('hidden');

        toastSystem.success('Data Demo Dimuat', 'Menggunakan data demo karena koneksi server bermasalah');
    }

    // Fetch data from backend
    async function fetchDPIData() {
        try {
            const response = await fetch('../../backend/system/dpi.php');
            const data = await response.json();

            if (data.success) {
                dpiData = data.data;
                // console.log('DPI Data loaded:', dpiData);
                return true;
            } else {
                throw new Error(data.message || 'Failed to fetch DPI data');
            }
        } catch (error) {
            console.error('Error fetching DPI data:', error);
            toastSystem.error('Gagal memuat data DPI', 'Menggunakan data demo');
            return false;
        }
    }

    async function fetchKapalData() {
        try {
            const response = await fetch('../../backend/system/kapal.php');
            const data = await response.json();

            if (data.success) {
                // 🔹 Filter hanya kapal dengan status 1 (aktif) dan verified_at tidak null
                if (Array.isArray(data.data)) {
                    kapalData = data.data.filter(kapal => kapal.status === 1 && kapal.verified_at !== null);
                } else if (typeof data.data === 'object' && data.data !== null) {
                    // Jika data single object, cek dua kondisi
                    kapalData = (kapal.status === 1 && kapal.verified_at !== null) ? [data.data] : [];
                } else {
                    kapalData = [];
                }

                // console.log('Kapal Data loaded (aktif saja):', kapalData);
                updateSearchData();
                return true;
            } else {
                throw new Error(data.message || 'Failed to fetch kapal data');
            }
        } catch (error) {
            console.error('Error fetching kapal data:', error);
            toastSystem.error('Gagal memuat data kapal', 'Menggunakan data demo');
            return false;
        }
    }

    async function MyKapalData() {
        try {
            const user = JSON.parse(sessionStorage.getItem('currentUser'));
            const response = await fetch(`../../backend/system/kapal.php?id=${user.id}`);
            const data = await response.json();

            if (data.success) {
                // Filter hanya kapal dengan status 1 (aktif) dan verified_at tidak null
                if (Array.isArray(data.data)) {
                    myKapalData = data.data.filter(kapal => kapal.status === 1 && kapal.verified_at !== null);
                } else if (typeof data.data === 'object' && data.data !== null) {
                    // Jika data single object, cek dua kondisi
                    myKapalData = (data.data.status === 1 && data.data.verified_at !== null) ? [data.data] : [];
                } else {
                    myKapalData = [];
                }

                console.log('My Kapal Data loaded (aktif & terverifikasi):', myKapalData);
                return true;
            } else {
                throw new Error(data.message || 'Failed to fetch my kapal data');
            }
        } catch (error) {
            console.error('Error fetching my kapal data:', error);
            toastSystem.error('Gagal memuat data kapal saya', 'Tidak Ada Data Yang Tersedia');
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

    // Initialize weather system
    const weatherSystem = new WeatherSystem();

    // Variabel untuk melacak popup yang sedang aktif
    let activePopupMarker = null;

    // Fungsi untuk update popup dengan informasi cuaca - FIXED DUPLICATION
    async function updateVesselPopupWithWeather(marker, vesselData) {
        // Cegah multiple execution untuk marker yang sama
        if (activePopupMarker === marker) {
            return;
        }

        activePopupMarker = marker;

        try {
            const position = marker.getLatLng();
            const weather = await weatherSystem.getWeather(position.lat, position.lng);

            const weatherIcon = weatherSystem.getWeatherIcon(weather.weather);

            const weatherHTML = `
            <div class="weather-info mt-4 pt-4 border-t border-gray-200" id="weather-info-${vesselData.id}">
                <h5 class="font-semibold text-sm text-gray-800 mb-2 flex items-center gap-2">
                    <i class="fas ${weatherIcon} text-blue-500"></i>
                    Informasi Cuaca (${weather.source})
                </h5>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Lokasi:</span>
                        <span class="font-medium">${weather.location}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Kondisi:</span>
                        <span class="font-medium">${weather.weather}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Suhu:</span>
                        <span class="font-medium">${weather.temperature}°C</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Kelembaban:</span>
                        <span class="font-medium">${weather.humidity}%</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Angin:</span>
                        <span class="font-medium">${weather.windSpeed} km/jam</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Arah Angin:</span>
                        <span class="font-medium">${weather.windDirection}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Gelombang:</span>
                        <span class="font-medium">${weather.waveHeight} m</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Jarak Pandang:</span>
                        <span class="font-medium">${weather.visibility} km</span>
                    </div>
                    <div class="col-span-2 text-center mt-1">
                        <span class="text-xs text-gray-500">Update: ${weather.lastUpdate}</span>
                    </div>
                </div>
            </div>
        `;

            // Dapatkan konten popup saat ini
            const currentContent = marker.getPopup().getContent();

            // Hapus bagian cuaca lama jika ada
            const contentWithoutOldWeather = currentContent.replace(
                /<div class="weather-info[^>]*>[\s\S]*?<\/div>\s*$/,
                ''
            );

            // Tambahkan cuaca baru
            const newContent = contentWithoutOldWeather + weatherHTML;
            marker.setPopupContent(newContent);

        } catch (error) {
            console.error('Error updating popup with weather:', error);

            const errorHTML = `
            <div class="weather-info mt-4 pt-4 border-t border-gray-200">
                <div class="text-center text-orange-500 text-xs">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    Data cuaca sementara tidak tersedia
                </div>
            </div>
        `;

            const currentContent = marker.getPopup().getContent();
            const contentWithoutOldWeather = currentContent.replace(
                /<div class="weather-info[^>]*>[\s\S]*?<\/div>\s*$/,
                ''
            );
            const newContent = contentWithoutOldWeather + errorHTML;
            marker.setPopupContent(newContent);
        }
    }

    // Modifikasi fungsi initializeVessels untuk hanya menampilkan kapal aktif
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

                    let vesselPosition;
                    if (kapal.currentPosition) {
                        vesselPosition = kapal.currentPosition;
                    } else {
                        vesselPosition = getRandomPositionInCircle([centerLat, centerLng], radius * 0.8);
                        kapal.currentPosition = vesselPosition;
                    }

                    const vesselType = kapal.jenis_kapal?.toLowerCase().includes('long') ? 'longliner' :
                        kapal.jenis_kapal?.toLowerCase().includes('purse') ? 'purse-seine' : 'trawler';

                    // Create marker
                    const marker = L.marker(vesselPosition, {
                        icon: createVesselIcon(vesselType)
                    }).addTo(map);

                    // Buat popup content awal TANPA cuaca
                    const initialPopupContent = `
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
                    <div class="weather-placeholder mt-4 pt-4 border-t border-gray-200">
                        <div class="text-center text-gray-500 text-xs">
                            <i class="fas fa-sync-alt fa-spin mr-1"></i>
                            Memuat data cuaca BMKG...
                        </div>
                    </div>
                </div>
            `;

                    marker.bindPopup(initialPopupContent);

                    // Event handler dengan sekali eksekusi
                    let hasWeatherLoaded = false;

                    marker.on('popupopen', function() {
                        if (!hasWeatherLoaded) {
                            updateVesselPopupWithWeather(marker, kapal);
                            hasWeatherLoaded = true;
                        }
                    });

                    // Reset flag ketika popup ditutup
                    marker.on('popupclose', function() {
                        activePopupMarker = null;
                    });

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

        // Update search system jika sudah diinisialisasi
        if (vesselSearchSystem) {
            vesselSearchSystem.updateVesselMarkers(vesselMarkers);
            updateSearchData();
        }

        // Tampilkan jumlah kapal aktif di console untuk debugging
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
            toastSystem.success('Pergerakan kapal diaktifkan', 'Kapal sekarang bergerak di zona masing-masing');
        } else {
            icon.className = 'fas fa-play mr-2';
            text.textContent = 'Play';
            stopVesselMovement();
            toastSystem.info('Pergerakan kapal dijeda', 'Posisi kapal diam untuk sementara');
        }
    }

    function getVesselCountInDPI(dpiId) {
        // Hanya hitung kapal dengan status 1 (aktif)
        return kapalData.filter(kapal => kapal.id_dpi == dpiId && kapal.status === 1).length;
    }

    function updateVesselsList() {
        const vesselsList = document.getElementById('vesselsList');
        const vesselsListMobile = document.getElementById('vesselsListMobile');
        const vesselCount = document.getElementById('vesselCount');
        const vesselCountMobile = document.getElementById('vesselCountMobile');

        // 🧩 Cek elemen ada sebelum manipulasi
        if (!vesselsList || !vesselCount || !vesselsListMobile || !vesselCountMobile) {
            console.error('Elemen vesselsList atau vesselCount tidak ditemukan');
            return;
        }

        // 🛠️ Cek apakah data kapal ada
        if (!myKapalData) {
            console.warn("⚠️ Tidak ada data kapal sama sekali:", myKapalData);
            vesselsList.innerHTML = "<p class='text-muted p-3'>Tidak ada data kapal.</p>";
            vesselsListMobile.innerHTML = "<p class='text-muted p-3'>Tidak ada data kapal.</p>";
            vesselCount.textContent = "0";
            vesselCountMobile.textContent = "0";
            return;
        }

        // 🔍 Deteksi apakah data dikirim langsung atau di dalam properti 'data'
        const data = myKapalData.data ? myKapalData.data : myKapalData;

        // 🔄 Pastikan dalam bentuk array agar aman di-loop
        const vessels = Array.isArray(data) ? data : [data];

        // Filter hanya kapal aktif
        const activeVessels = vessels.filter(vessel => vessel.status === 1);

        // 🧮 Update jumlah kapal aktif
        vesselCount.textContent = activeVessels.length.toString();
        vesselCountMobile.textContent = activeVessels.length.toString();

        // 🧹 Bersihkan daftar sebelum render ulang
        vesselsList.innerHTML = '';
        vesselsListMobile.innerHTML = '';

        // ⚓ Jika kosong, tampilkan pesan
        if (activeVessels.length === 0) {
            vesselsList.innerHTML = `
            <div class="text-center py-4 text-gray-500">
                <i class="fas fa-ship text-lg mb-2"></i>
                <p class="text-sm">Tidak ada kapal aktif</p>
            </div>
        `;
            vesselsListMobile.innerHTML = `
            <div class="text-center py-4 text-gray-500">
                <i class="fas fa-ship text-lg mb-2"></i>
                <p class="text-sm">Tidak ada kapal aktif</p>
            </div>
        `;
            return;
        }

        // 🚢 Render setiap kapal aktif ke daftar
        activeVessels.forEach((kapal) => {
            const vesselType =
                kapal.jenis_kapal?.toLowerCase().includes('long') ? 'Long Liner' :
                kapal.jenis_kapal?.toLowerCase().includes('purse') ? 'Purse Seine' :
                kapal.jenis_kapal?.toLowerCase().includes('trawl') ? 'Trawler' :
                kapal.jenis_kapal || 'Tidak diketahui';

            const vesselElement = document.createElement('div');
            vesselElement.className =
                'flex items-center justify-between p-3 bg-blue-50 rounded-lg hover:bg-blue-100 transition duration-200 cursor-pointer';

            vesselElement.innerHTML = `
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                    <i class="fas fa-ship text-white text-xs"></i>
                </div>
                <div>
                    <p class="text-blue-900 font-bold text-sm mb-0">${kapal.nama_kapal}</p>
                    <p class="text-blue-600 text-xs mb-0">${vesselType}</p>
                </div>
            </div>
            <div class="text-right">
                <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs font-medium">Aktif</span>
            </div>
        `;

            // 🗺️ Tambahkan event klik untuk fokus ke kapal di peta
            vesselElement.addEventListener('click', () => {
                const vesselMarker = vesselMarkers.find(v => v.vesselId === kapal.id);
                if (vesselMarker && vesselMarker.marker) {
                    map.setView(vesselMarker.marker.getLatLng(), 13);
                    vesselMarker.marker.openPopup();
                } else {
                    console.warn(`Marker kapal ${kapal.nama_kapal} tidak ditemukan di peta.`);
                }
            });

            const vesselElementMobile = document.createElement('div');
            vesselElementMobile.className =
                'flex items-center justify-between p-3 bg-blue-50 rounded-lg hover:bg-blue-100 transition duration-200 cursor-pointer';

            vesselElementMobile.innerHTML = `
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                    <i class="fas fa-ship text-white text-xs"></i>
                </div>
                <div>
                    <p class="text-blue-900 font-bold text-sm mb-0">${kapal.nama_kapal}</p>
                    <p class="text-blue-600 text-xs mb-0">${vesselType}</p>
                </div>
            </div>
            <div class="text-right">
                <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs font-medium">Aktif</span>
            </div>
        `;

            // 🗺️ Tambahkan event klik untuk fokus ke kapal di peta
            vesselElementMobile.addEventListener('click', () => {
                const vesselMarker = vesselMarkers.find(v => v.vesselId === kapal.id);
                if (vesselMarker && vesselMarker.marker) {
                    map.setView(vesselMarker.marker.getLatLng(), 13);
                    vesselMarker.marker.openPopup();
                } else {
                    console.warn(`Marker kapal ${kapal.nama_kapal} tidak ditemukan di peta.`);
                }
            });

            // ⛵ Masukkan ke daftar
            vesselsList.appendChild(vesselElement);
            vesselsListMobile.appendChild(vesselElementMobile);
        });
    }

    // Initialize dashboard when page loads
    document.addEventListener('DOMContentLoaded', function() {
        initFullScreenMap();
        initDashboard();
    });
</script>