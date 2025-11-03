// Global variables
let map;
let zoneLayers = [];
let vesselMarkers = [];
let dpiData = [];
let kapalData = [];
let layersVisible = true;
let vesselMovementEnabled = true;
let vesselMovementInterval;

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

// Simple vessel icon (tanpa efek 3D kompleks)
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
        iconSize: [28, 28],
        iconAnchor: [14, 14]
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

    // Update marker position directly (tanpa animation yang complex)
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
    const mobileIcon = document.getElementById('mobileMovementIcon');
    const mobileText = document.getElementById('mobileMovementText');

    if (vesselMovementEnabled) {
        icon.className = 'fas fa-pause mr-1';
        text.textContent = 'Pause';
        if (mobileIcon) mobileIcon.className = 'fas fa-pause text-lg mb-1';
        if (mobileText) mobileText.textContent = 'Pause';
        startVesselMovement();
        showNotification('Pergerakan kapal diaktifkan', 'success');
    } else {
        icon.className = 'fas fa-play mr-1';
        text.textContent = 'Play';
        if (mobileIcon) mobileIcon.className = 'fas fa-play text-lg mb-1';
        if (mobileText) mobileText.textContent = 'Play';
        stopVesselMovement();
        showNotification('Pergerakan kapal dijeda', 'info');
    }
}

// Initialize vessels on map dengan koordinat yang FIXED - HANYA KAPAL AKTIF
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

                // Create marker dengan koordinat geografis FIXED
                const marker = L.marker(vesselPosition, {
                    icon: createVesselIcon(vesselType)
                }).addTo(map);

                // Bind popup dengan informasi kapal
                marker.bindPopup(`
                            <div class="p-3 min-w-48 max-w-xs">
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

                console.log(`Kapal aktif ${kapal.nama_kapal} di posisi:`, vesselPosition);
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

// Initialize Map dengan data real
async function initMap() {
    // Load data first
    const dpiLoaded = await fetchDPIData();
    const kapalLoaded = await fetchKapalData();

    if (!dpiLoaded || !kapalLoaded) {
        initDemoData();
        return;
    }

    // Initialize map if not exists
    if (!map) {
        map = L.map('monitoringMap', {
            zoomControl: true,
            attributionControl: true
        });

        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: 'ZONA LAUT',
            maxZoom: 13
        }).addTo(map);

        // Set initial view
        map.setView([-6.2000, 106.8166], 10);
    }

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

                const circle = L.circle([lat, lng], {
                    color: '#3b82f6',
                    fillColor: '#3b82f6',
                    fillOpacity: 0.1,
                    weight: 2,
                    radius: radius
                }).addTo(map);

                circle.bindPopup(`
                            <div class="p-3 min-w-48 max-w-xs">
                                <h3 class="font-bold text-lg text-gray-800">${dpi.nama_dpi}</h3>
                                <div class="space-y-1 text-sm text-gray-600">
                                    <div class="flex justify-between">
                                        <span>Luas:</span>
                                        <span class="font-medium">${dpi.luas} km²</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Radius:</span>
                                        <span class="font-medium">${Math.round(radius / 1000)} km</span>
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

    // Initialize vessels (hanya yang aktif)
    initializeVessels();

    updateDashboardStats();
    updateVesselsList();
}

function getVesselCountInDPI(dpiId) {
    // Hanya hitung kapal dengan status 1 (aktif)
    return kapalData.filter(kapal => kapal.id_dpi == dpiId && kapal.status === 1).length;
}

function updateDashboardStats() {
    // Hanya hitung kapal dengan status 1 (aktif)
    const activeShips = kapalData.filter(kapal => kapal.status === 1).length;
    document.getElementById('activeShips').textContent = activeShips;
    document.getElementById('vesselCount').textContent = `${activeShips} kapal`;
    document.getElementById('shipChange').textContent = `Total ${activeShips} kapal aktif`;

    const zoneCount = dpiData.length;
    document.getElementById('zoneCount').textContent = zoneCount;
    document.getElementById('zoneStatus').textContent = zoneCount > 0 ? 'Semua aktif' : 'Tidak ada zona';
}

function updateVesselsList() {
    const vesselsList = document.getElementById('vesselsList');
    vesselsList.innerHTML = '';

    // Filter hanya kapal dengan status 1 (aktif)
    const activeKapalData = kapalData.filter(kapal => kapal.status === 1);

    if (activeKapalData.length === 0) {
        vesselsList.innerHTML = `
                    <div class="text-center py-6 sm:py-8 text-gray-500">
                        <i class="fas fa-ship text-2xl sm:text-3xl mb-2"></i>
                        <p class="text-sm sm:text-base">Tidak ada kapal aktif</p>
                    </div>
                `;
        return;
    }

    activeKapalData.forEach(kapal => {
        const vesselType = kapal.jenis_kapal?.toLowerCase().includes('long') ? 'Long Liner' :
            kapal.jenis_kapal?.toLowerCase().includes('purse') ? 'Purse Seine' : 'Trawler';

        const vesselElement = document.createElement('div');
        vesselElement.className = 'flex items-center justify-between p-3 sm:p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition duration-200';
        vesselElement.innerHTML = `
                    <div class="flex items-center space-x-3 sm:space-x-4 min-w-0 flex-1">
                        <div class="w-8 h-8 sm:w-10 sm:h-10 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-ship text-white text-sm sm:text-base"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-blue-900 font-bold text-sm sm:text-base truncate">${kapal.nama_kapal}</p>
                            <p class="text-blue-600 text-xs sm:text-sm truncate">${vesselType} • ${kapal.nama_dpi || 'Tanpa Zona'}</p>
                            <p class="text-blue-500 text-xs mt-1 truncate">${kapal.nama_pemilik}</p>
                        </div>
                    </div>
                    <div class="text-right flex-shrink-0 ml-2">
                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs font-medium">Aktif</span>
                    </div>
                `;
        vesselsList.appendChild(vesselElement);
    });
}

function refreshMap() {
    showNotification('Memperbarui data...', 'info');

    // Stop vessel movement during refresh
    stopVesselMovement();

    // Reload data from backend
    Promise.all([fetchDPIData(), fetchKapalData()]).then(([dpiSuccess, kapalSuccess]) => {
        if (dpiSuccess && kapalSuccess) {
            // Reinitialize map with new data
            initMap().then(() => {
                showNotification('Data berhasil diperbarui', 'success');
            });
        } else {
            showNotification('Gagal memuat data terbaru', 'error');
        }
    });
}

function fullScreen() {
    const transition = document.createElement('div');
    transition.className = 'fixed w-full h-full inset-0 bg-white z-50 flex items-center justify-center';
    transition.innerHTML = `
                <div class="text-center">
                    <i class="fas fa-expand-arrows-alt text-4xl text-blue-600 mb-4 animate-pulse"></i>
                    <p class="text-blue-900 font-medium">Masuk ke mode layar penuh...</p>
                </div>
            `;
    transition.style.opacity = '0';
    transition.style.filter = 'blur(10px)';
    document.getElementById('monitoringMap').style.opacity = '0';
    transition.style.transition = 'all 0.3s ease';
    document.body.appendChild(transition);
    setTimeout(() => {
        transition.style.opacity = '1';
        transition.style.filter = 'blur(0px)';
    }, 100);
    setTimeout(() => {
        window.location.href = "fullscreen.php";
    }, 1500);
}

function toggleLayers() {
    layersVisible = !layersVisible;
    zoneLayers.forEach(layer => {
        if (layersVisible) {
            map.addLayer(layer);
        } else {
            map.removeLayer(layer);
        }
    });

    showNotification(layersVisible ? 'Layer zona ditampilkan' : 'Layer zona disembunyikan', 'info');
}

function showNotification(message, type) {
    const existingNotification = document.querySelector('.notification-toast');
    if (existingNotification) existingNotification.remove();

    const bgColor = type === 'success' ? 'bg-green-500' :
        type === 'error' ? 'bg-red-500' :
            type === 'info' ? 'bg-blue-500' : 'bg-gray-500';

    const notification = document.createElement('div');
    notification.className = `notification-toast fixed top-4 right-4 ${bgColor} text-white p-3 sm:p-4 rounded-lg shadow-lg z-50 transform transition-all duration-300 max-w-xs sm:max-w-sm`;
    notification.innerHTML = `
                <div class="flex items-center space-x-3">
                    <i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'exclamation-triangle' : 'info'}-circle"></i>
                    <span class="font-medium text-sm sm:text-base">${message}</span>
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

function updateTime() {
    const now = new Date();
    const timeString = now.toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
    document.getElementById('currentTime').textContent = timeString;
    document.getElementById('mobileTime').textContent = timeString;
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

    // Initialize map with demo data
    if (!map) {
        map = L.map('monitoringMap');
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
        map.setView([-6.853486, 113.716736], 10);
    }

    // Clear existing data
    zoneLayers.forEach(layer => map.removeLayer(layer));
    vesselMarkers.forEach(({
        marker
    }) => map.removeLayer(marker));

    zoneLayers = [];
    vesselMarkers = [];

    // Initialize with demo data
    initMap().then(() => {
        showNotification('Menggunakan data demo - hanya kapal aktif yang ditampilkan', 'info');
    });
}

// Handle window resize for better mobile experience
function handleResize() {
    if (map) {
        setTimeout(() => {
            map.invalidateSize();
        }, 300);
    }
}

document.addEventListener('DOMContentLoaded', function () {
    initMap();
    setInterval(updateTime, 1000);
    updateTime();

    // Auto-refresh every 60 seconds
    setInterval(refreshMap, 60000);

    // Handle window resize
    window.addEventListener('resize', handleResize);

    // Add touch support for mobile
    if ('ontouchstart' in window) {
        document.body.classList.add('touch-device');
    }
});