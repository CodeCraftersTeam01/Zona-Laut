// dashboard-map.js - Integrated Dashboard and Map System
console.log('Dashboard-Map System loaded');

// Global variables
let dashboardData = {
    totalDpi: 0,
    totalKapal: 0,
    totalPemilik: 0,
    totalUsers: 0,
    totalTangkapanHariIni: 0,
    totalBeratHariIni: 0,
    totalAktivitas: 0,
    loginHariIni: 0,
    recentActivities: []
};

// Map variables
let map;
let zoneLayers = [];
let vesselMarkers = [];
let layersVisible = true;
let vesselMovementEnabled = false;
let vesselMovementInterval;
let isMapInitialized = false;
let dpiData = []; // Store DPI data for vessel positioning

// Initialize everything when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, starting system...');
    
    // Initialize dashboard immediately
    initializeDashboard();
    
    // Initialize map after a short delay
    setTimeout(() => {
        if (document.getElementById('monitoringMap')) {
            console.log('Initializing map...');
            initMap();
        }
    }, 500);
});

// ==================== DASHBOARD FUNCTIONS ====================

async function initializeDashboard() {
    console.log('Initializing dashboard...');
    showLoading();
    
    try {
        await fetchAllData();
        updateCounters();
        renderRecentActivities();
        renderVesselsList(); // Render vessels list
        startAutoRefresh();
    } catch (error) {
        console.error('Dashboard init error:', error);
    }
}

async function fetchAllData() {
    try {
        console.log('Fetching dashboard data...');
        
        // Gunakan Promise.allSettled untuk handle error individual
        const results = await Promise.allSettled([
            fetchDpiCount(),
            fetchKapalCount(),
            fetchPemilikCount(),
            fetchUsersCount(),
            fetchTangkapanHariIni(),
            fetchRecentActivities()
        ]);
        
        console.log('Dashboard data fetch completed');
    } catch (error) {
        console.error('Error in fetchAllData:', error);
        throw error;
    }
}

async function fetchDpiCount() {
    try {
        const response = await fetch('system/dpi.php');
        if (!response.ok) throw new Error('DPI fetch failed');
        const data = await response.json();
        
        if (data.success) {
            dashboardData.totalDpi = data.data?.length || 0;
            console.log('DPI count:', dashboardData.totalDpi);
            // Store DPI data for map
            dpiData = data.data || [];
        }
    } catch (error) {
        console.error('Error fetching DPI:', error);
        dashboardData.totalDpi = 0;
        dpiData = [];
    }
}

async function fetchKapalCount() {
    try {
        const response = await fetch('system/kapal.php');
        if (!response.ok) throw new Error('Kapal fetch failed');
        const data = await response.json();
        
        if (data.success) {
            dashboardData.totalKapal = data.data?.length || 0;
            console.log('Kapal count:', dashboardData.totalKapal);
            
            // Simpan data kapal untuk map
            window.kapalData = data.data || [];
        }
    } catch (error) {
        console.error('Error fetching kapal:', error);
        dashboardData.totalKapal = 0;
        window.kapalData = [];
    }
}

async function fetchPemilikCount() {
    try {
        const response = await fetch('system/pemilik.php');
        if (!response.ok) throw new Error('Pemilik fetch failed');
        const data = await response.json();
        
        if (data.success) {
            dashboardData.totalPemilik = data.data?.length || 0;
            console.log('Pemilik count:', dashboardData.totalPemilik);
            
            // Count today's logins from pemilik data
            const today = new Date().toISOString().split('T')[0];
            dashboardData.loginHariIni = data.data.filter(pemilik => 
                pemilik.last_login === today
            ).length;
            
            console.log('Today logins:', dashboardData.loginHariIni);
        }
    } catch (error) {
        console.error('Error fetching pemilik:', error);
        dashboardData.totalPemilik = 0;
        dashboardData.loginHariIni = 0;
    }
}

async function fetchUsersCount() {
    try {
        const response = await fetch('system/users.php');
        const data = await response.json();
        
        if (data.success) {
            dashboardData.totalUsers = data.data?.length || 0;
        }
    } catch (error) {
        console.log('Using pemilik as users fallback');
        dashboardData.totalUsers = dashboardData.totalPemilik;
    }
}

async function fetchTangkapanHariIni() {
    try {
        const today = new Date().toISOString().split('T')[0];
        const response = await fetch('system/tangkapan.php');
        const data = await response.json();
        
        if (data.success) {
            const tangkapanHariIni = data.data?.filter(item => 
                item.tanggal_tangkapan === today
            ) || [];
            
            dashboardData.totalTangkapanHariIni = tangkapanHariIni.length;
            dashboardData.totalBeratHariIni = tangkapanHariIni.reduce((total, item) => 
                total + parseFloat(item.berat_ikan || 0), 0
            );
        }
    } catch (error) {
        console.error('Error fetching tangkapan:', error);
        dashboardData.totalTangkapanHariIni = 0;
        dashboardData.totalBeratHariIni = 0;
    }
}

async function fetchRecentActivities() {
    try {
        // Fetch pemilik data to get login activities
        const pemilikResponse = await fetch('system/pemilik.php');
        const pemilikData = await pemilikResponse.json();
        
        if (pemilikData.success && pemilikData.data) {
            const activities = [];
            const today = new Date().toISOString().split('T')[0];
            
            // Create activities from pemilik last_login data
            pemilikData.data.forEach(pemilik => {
                if (pemilik.last_login) {
                    activities.push({
                        type: 'login',
                        description: `${pemilik.nama_pemilik} berhasil login`,
                        time: `${pemilik.last_login}T00:00:00`, // Add time to make it valid ISO
                        icon: 'bi bi-box-arrow-in-right',
                        color: 'success'
                    });
                }
            });
            
            // Add system activities
            activities.push({
                type: 'system',
                description: 'Sistem dashboard dimulai',
                time: new Date().toISOString(),
                icon: 'bi bi-play-circle',
                color: 'primary'
            });
            
            // Sort by time (newest first) and take latest 5
            dashboardData.recentActivities = activities
                .sort((a, b) => new Date(b.time) - new Date(a.time))
                .slice(0, 5);
                
            dashboardData.totalAktivitas = activities.length;
            
        } else {
            throw new Error('Invalid pemilik data');
        }
        
    } catch (error) {
        console.error('Error fetching activities:', error);
        // Fallback to demo activities
        const now = new Date();
        dashboardData.recentActivities = [
            {
                type: 'login',
                description: 'User admin berhasil login',
                time: new Date(now.getTime() - 5 * 60000).toISOString(),
                icon: 'bi bi-box-arrow-in-right',
                color: 'success'
            },
            {
                type: 'system',
                description: 'Sistem dashboard dimulai',
                time: now.toISOString(),
                icon: 'bi bi-play-circle',
                color: 'primary'
            }
        ];
        dashboardData.totalAktivitas = 2;
    }
}

function getActivityIcon(activityType) {
    const icons = {
        'login': 'bi bi-box-arrow-in-right',
        'logout': 'bi bi-box-arrow-right',
        'kapal': 'fas fa-ship',
        'tangkapan': 'fas fa-fish',
        'dpi': 'bi bi-geo-alt',
        'user': 'bi bi-person',
        'system': 'bi bi-gear',
        'default': 'bi bi-activity'
    };
    return icons[activityType] || icons.default;
}

function getActivityColor(activityType) {
    const colors = {
        'login': 'success',
        'logout': 'secondary',
        'kapal': 'info',
        'tangkapan': 'warning',
        'dpi': 'primary',
        'user': 'success',
        'system': 'primary',
        'default': 'secondary'
    };
    return colors[activityType] || colors.default;
}

function updateCounters() {
    console.log('Updating counters...');
    
    // Update tanpa animasi dulu untuk simplicity
    updateElementText('dpi-counter', dashboardData.totalDpi);
    updateElementText('kapal-counters', dashboardData.totalKapal);
    updateElementText('pemilik-counter', dashboardData.totalPemilik);
    updateElementText('users-counter', dashboardData.totalUsers);
    updateElementText('total-aktivitas', dashboardData.totalAktivitas);
    updateElementText('login-hari-ini', dashboardData.loginHariIni);
    
    updateAdditionalStats();
}

function updateElementText(elementId, value) {
    const element = document.getElementById(elementId);
    if (element) {
        element.textContent = value;
    }
}

function updateAdditionalStats() {
    updateElementText('tangkapan-hari-ini', dashboardData.totalTangkapanHariIni);
    
    const beratElement = document.getElementById('berat-hari-ini');
    if (beratElement) {
        beratElement.textContent = dashboardData.totalBeratHariIni.toFixed(1) + ' kg';
    }
}

function renderRecentActivities() {
    const activitiesContainer = document.getElementById('recent-activities');
    if (!activitiesContainer) return;

    // Sort activities by time (newest first)
    const sortedActivities = [...dashboardData.recentActivities].sort((a, b) => 
        new Date(b.time) - new Date(a.time)
    );

    if (sortedActivities.length === 0) {
        activitiesContainer.innerHTML = `
            <div class="text-center text-muted py-4">
                <i class="bi bi-inbox fs-1"></i>
                <p class="mt-2 mb-0">Tidak ada aktivitas</p>
            </div>
        `;
        return;
    }

    activitiesContainer.innerHTML = sortedActivities.map(activity => `
        <div class="d-flex align-items-center mb-3 p-2 border rounded">
            <div class="me-3">
                <i class="${activity.icon} text-${activity.color}"></i>
            </div>
            <div class="flex-grow-1">
                <div class="small fw-semibold">${activity.description}</div>
                <small class="text-muted">${formatTime(activity.time)}</small>
            </div>
        </div>
    `).join('');
}

function renderVesselsList() {
    const vesselsListContainer = document.getElementById('vesselsList');
    if (!vesselsListContainer) return;

    const activeVessels = window.kapalData ? window.kapalData.filter(kapal => 
        kapal.status === 1 || kapal.status === '1' || kapal.status === true
    ) : [];

    if (activeVessels.length === 0) {
        vesselsListContainer.innerHTML = `
            <div class="text-center text-muted py-3">
                <i class="fas fa-ship fs-4"></i>
                <p class="mt-2 mb-0">Tidak ada kapal aktif</p>
            </div>
        `;
        return;
    }

    vesselsListContainer.innerHTML = activeVessels.slice(0, 5).map(kapal => `
        <div class="d-flex align-items-center mb-2 p-2 border rounded">
            <div class="me-3">
                <i class="fas fa-ship text-info"></i>
            </div>
            <div class="flex-grow-1">
                <div class="small fw-semibold">${kapal.nama_kapal || 'Kapal'}</div>
                <small class="text-muted">${kapal.jenis_kapal || 'Tidak diketahui'}</small>
            </div>
            <div class="badge bg-success">Aktif</div>
        </div>
    `).join('');

    // Show "more" indicator if there are more vessels
    if (activeVessels.length > 5) {
        vesselsListContainer.innerHTML += `
            <div class="text-center mt-2">
                <small class="text-muted">+${activeVessels.length - 5} kapal lainnya</small>
            </div>
        `;
    }
}

function formatTime(timeString) {
    try {
        const date = new Date(timeString);
        const now = new Date();
        const diffMs = now - date;
        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMs / 3600000);
        const diffDays = Math.floor(diffMs / 86400000);
        
        if (diffMins < 1) {
            return 'Baru saja';
        } else if (diffMins < 60) {
            return `${diffMins} menit lalu`;
        } else if (diffHours < 24) {
            return `${diffHours} jam lalu`;
        } else if (diffDays === 1) {
            return 'Kemarin';
        } else if (diffDays < 7) {
            return `${diffDays} hari lalu`;
        } else {
            return date.toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'short'
            });
        }
    } catch (error) {
        return 'Waktu tidak valid';
    }
}

function startAutoRefresh() {
    // Auto refresh setiap 2 menit
    setInterval(async () => {
        console.log('Auto-refreshing dashboard...');
        await fetchAllData();
        updateCounters();
        renderRecentActivities();
        renderVesselsList(); // Refresh vessels list too
    }, 120000);
}

// ==================== MAP FUNCTIONS ====================

function initMap() {
    console.log('Initializing map...');
    
    if (isMapInitialized) {
        console.log('Map already initialized');
        hideLoading();
        return;
    }

    showLoading();
    
    try {
        // Create map instance
        map = L.map('monitoringMap', {
            zoomControl: true,
            attributionControl: true,
            minZoom: 4,
            maxZoom: 18
        });

        // Add tile layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© ZONA LAUT',
            maxZoom: 18
        }).addTo(map);

        // Set initial view untuk seluruh Indonesia
        map.setView([-2.5489, 118.0149], 5); // Center Indonesia, zoom level 5
        console.log('Map created successfully with Indonesia view');

        // Load map data
        loadMapData();
        
    } catch (error) {
        console.error('Error creating map:', error);
        showNotification('Gagal membuat peta', 'error');
        showDemoMap();
        hideLoading();
    }
}

async function loadMapData() {
    console.log('Loading map data...');
    
    try {
        // Try to load real data
        const [dpiResponse, kapalResponse] = await Promise.all([
            fetch('system/dpi.php'),
            fetch('system/kapal.php')
        ]);

        const dpiDataResult = await dpiResponse.json();
        const kapalData = await kapalResponse.json();

        if (dpiDataResult.success && kapalData.success) {
            console.log('Real data loaded successfully');
            dpiData = dpiDataResult.data || [];
            processRealMapData(dpiData, kapalData.data);
            showNotification('Peta berhasil dimuat', 'success');
        } else {
            throw new Error('Invalid data format from API');
        }
        
    } catch (error) {
        console.log('Using demo data due to error:', error);
        showDemoMap();
        showNotification('Menggunakan data demo', 'info');
    } finally {
        // Always hide loading after map is ready
        setTimeout(hideLoading, 500);
    }
}

function processRealMapData(dpiData, kapalData) {
    console.log('Processing real map data...');
    
    clearMapLayers();
    
    // Add DPI zones
    if (dpiData && dpiData.length > 0) {
        dpiData.forEach(dpi => {
            if (dpi.location) {
                try {
                    const [lat, lng] = dpi.location.split(',').map(coord => parseFloat(coord.trim()));
                    const radius = Math.sqrt((dpi.luas || 100) * 1000000 / Math.PI);
                    
                    const circle = L.circle([lat, lng], {
                        color: '#3b82f6',
                        fillColor: '#3b82f6',
                        fillOpacity: 0.1,
                        weight: 2,
                        radius: radius
                    }).addTo(map);
                    
                    circle.bindPopup(`
                        <div class="p-2">
                            <h6 class="fw-bold mb-1">${dpi.nama_dpi || 'Zona DPI'}</h6>
                            <div class="small">
                                <div>Luas: ${dpi.luas || 0} km²</div>
                                <div>Radius: ${Math.round(radius/1000)} km</div>
                            </div>
                        </div>
                    `);
                    
                    zoneLayers.push({
                        layer: circle,
                        center: [lat, lng],
                        radius: radius,
                        dpiId: dpi.id
                    });
                } catch (error) {
                    console.error('Error creating DPI zone:', error);
                }
            }
        });
        console.log('DPI zones added:', zoneLayers.length);
    }
    
    // Add vessel markers
    addVesselMarkers(kapalData);
    
    // Update map stats
    updateMapStats();
    
    isMapInitialized = true;
}

function addVesselMarkers(kapalData) {
    if (!map || !kapalData) return;
    
    // Clear existing markers
    vesselMarkers.forEach(({ marker }) => {
        if (marker && map.hasLayer(marker)) {
            map.removeLayer(marker);
        }
    });
    vesselMarkers = [];

    const activeVessels = kapalData.filter(kapal => 
        kapal.status === 1 || kapal.status === '1' || kapal.status === true
    );
    
    console.log('Active vessels found:', activeVessels.length);

    activeVessels.forEach((kapal, index) => {
        try {
            let position;
            
            // Find DPI for this vessel
            const vesselDpi = findDpiForVessel(kapal);
            
            if (vesselDpi) {
                // Generate random position within DPI radius
                position = generateRandomPositionInDpi(vesselDpi.center, vesselDpi.radius);
            } else {
                // Fallback to random position around Indonesia
                position = generateRandomPositionAroundIndonesia();
            }

            const vesselType = getVesselType(kapal.jenis_kapal);
            const marker = L.marker(position, {
                icon: createVesselIcon(vesselType)
            }).addTo(map);

            marker.bindPopup(`
                <div class="p-2" style="min-width: 200px;">
                    <h6 class="fw-bold mb-1">${kapal.nama_kapal || 'Kapal'}</h6>
                    <div class="small">
                        <div>Jenis: ${kapal.jenis_kapal || 'Tidak diketahui'}</div>
                        <div>Pemilik: ${kapal.nama_pemilik || 'Tidak diketahui'}</div>
                        <div>DPI: ${vesselDpi ? vesselDpi.dpiName : 'Tidak ada'}</div>
                        <div class="text-success">Status: Aktif</div>
                    </div>
                </div>
            `);

            vesselMarkers.push({ 
                marker, 
                vesselId: kapal.id,
                kapalData: kapal,
                currentDpi: vesselDpi,
                currentPosition: position
            });

        } catch (error) {
            console.error('Error creating vessel marker:', error);
        }
    });

    console.log('Vessel markers created:', vesselMarkers.length);
}

function findDpiForVessel(kapal) {
    if (!dpiData || dpiData.length === 0) return null;
    
    // If vessel has assigned DPI
    if (kapal.id_dpi) {
        const assignedDpi = dpiData.find(dpi => dpi.id == kapal.id_dpi);
        if (assignedDpi && assignedDpi.location) {
            const [lat, lng] = assignedDpi.location.split(',').map(coord => parseFloat(coord.trim()));
            const radius = Math.sqrt((assignedDpi.luas || 100) * 1000000 / Math.PI);
            return {
                center: [lat, lng],
                radius: radius,
                dpiId: assignedDpi.id,
                dpiName: assignedDpi.nama_dpi
            };
        }
    }
    
    // Assign random DPI if no specific DPI assigned
    const randomDpi = dpiData[Math.floor(Math.random() * dpiData.length)];
    if (randomDpi && randomDpi.location) {
        const [lat, lng] = randomDpi.location.split(',').map(coord => parseFloat(coord.trim()));
        const radius = Math.sqrt((randomDpi.luas || 100) * 1000000 / Math.PI);
        return {
            center: [lat, lng],
            radius: radius,
            dpiId: randomDpi.id,
            dpiName: randomDpi.nama_dpi
        };
    }
    
    return null;
}

function generateRandomPositionInDpi(center, radius) {
    const [centerLat, centerLng] = center;
    
    // Convert radius to degrees (approximate)
    const radiusInDegrees = radius / 111000; // 1 degree ≈ 111km
    
    // Generate random point within circle
    const angle = Math.random() * 2 * Math.PI;
    const distance = Math.random() * radiusInDegrees * 0.8; // 80% of radius to keep inside
    
    const lat = centerLat + (distance * Math.cos(angle));
    const lng = centerLng + (distance * Math.sin(angle));
    
    return [lat, lng];
}

function generateRandomPositionAroundIndonesia() {
    // Random position within Indonesia territory
    const indonesiaBounds = {
        north: 6.0,   // Sabang
        south: -11.0, // Rote
        west: 95.0,   // Sabang
        east: 141.0   // Merauke
    };
    
    const lat = indonesiaBounds.south + Math.random() * (indonesiaBounds.north - indonesiaBounds.south);
    const lng = indonesiaBounds.west + Math.random() * (indonesiaBounds.east - indonesiaBounds.west);
    
    return [lat, lng];
}

function getVesselType(jenisKapal) {
    if (!jenisKapal) return 'trawler';
    
    const type = jenisKapal.toLowerCase();
    if (type.includes('long')) return 'longliner';
    if (type.includes('purse')) return 'purse-seine';
    return 'trawler';
}

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
            <div style="width:24px;height:24px;border:2px solid ${color};border-radius:50%;background:white;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 4px rgba(0,0,0,0.3);">
                <i class="fas fa-ship" style="color:${color};font-size:12px;"></i>
            </div>
        `,
        iconSize: [24, 24],
        iconAnchor: [12, 12]
    });
}

function showDemoMap() {
    console.log('Showing demo map...');
    
    if (!map) {
        map = L.map('monitoringMap', {
            minZoom: 4,
            maxZoom: 18
        });
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
        map.setView([-2.5489, 118.0149], 5); // Center Indonesia
    }

    clearMapLayers();
    
    // Create multiple demo DPI zones across Indonesia
    const demoDpis = [
        {
            center: [-6.2000, 106.8166], // Jakarta
            radius: 80000,
            dpiId: 'demo-1',
            dpiName: 'Zona DPI Jakarta'
        },
        {
            center: [-5.1477, 119.4327], // Makassar
            radius: 70000,
            dpiId: 'demo-2',
            dpiName: 'Zona DPI Makassar'
        },
        {
            center: [-0.7893, 113.9213], // Kalimantan
            radius: 90000,
            dpiId: 'demo-3',
            dpiName: 'Zona DPI Kalimantan'
        },
        {
            center: [-8.4095, 115.1889], // Bali
            radius: 60000,
            dpiId: 'demo-4',
            dpiName: 'Zona DPI Bali'
        }
    ];
    
    // Add demo DPI zones
    demoDpis.forEach(demoDpi => {
        const demoCircle = L.circle(demoDpi.center, {
            color: '#3b82f6',
            fillColor: '#3b82f6',
            fillOpacity: 0.1,
            weight: 2,
            radius: demoDpi.radius
        }).addTo(map);
        
        demoCircle.bindPopup(`
            <div class="p-2">
                <h6 class="fw-bold">${demoDpi.dpiName}</h6>
                <div class="small">Ini adalah data demonstrasi</div>
            </div>
        `);
        
        zoneLayers.push({
            layer: demoCircle,
            center: demoDpi.center,
            radius: demoDpi.radius,
            dpiId: demoDpi.dpiId
        });
        
        // Add 2-3 demo vessels within each DPI radius
        const vesselCount = 2 + Math.floor(Math.random() * 2); // 2-3 vessels per DPI
        
        for (let i = 0; i < vesselCount; i++) {
            const position = generateRandomPositionInDpi(demoDpi.center, demoDpi.radius);
            const vesselTypes = ['trawler', 'longliner', 'purse-seine'];
            const vesselType = vesselTypes[Math.floor(Math.random() * vesselTypes.length)];
            
            const marker = L.marker(position, {
                icon: createVesselIcon(vesselType)
            }).addTo(map);
            
            marker.bindPopup(`
                <div class="p-2">
                    <h6 class="fw-bold">Kapal ${demoDpi.dpiName} ${i + 1}</h6>
                    <div class="small">Jenis: ${vesselType}</div>
                    <div class="small">DPI: ${demoDpi.dpiName}</div>
                    <div class="small text-success">Status: Aktif</div>
                </div>
            `);
            
            vesselMarkers.push({ 
                marker, 
                vesselId: `demo-${demoDpi.dpiId}-${i}`,
                currentDpi: demoDpi,
                currentPosition: position
            });
        }
    });
    
    updateMapStats();
    isMapInitialized = true;
    
    console.log('Demo map created with', vesselMarkers.length, 'vessels across Indonesia');
}

function updateMapStats() {
    const activeShips = vesselMarkers.length;
    const zoneCount = zoneLayers.length;

    console.log('Updating map stats - Ships:', activeShips, 'Zones:', zoneCount);

    // Update the stats display
    updateElementText('activeShips', activeShips);
    updateElementText('vesselCount', activeShips);
    updateElementText('zoneCount', zoneCount);
    
    const vesselsCountEl = document.getElementById('vesselsCount');
    if (vesselsCountEl) {
        vesselsCountEl.textContent = activeShips;
    }
}

function clearMapLayers() {
    // Clear zones
    zoneLayers.forEach(zone => {
        if (map && map.hasLayer(zone.layer)) {
            map.removeLayer(zone.layer);
        }
    });
    zoneLayers = [];
    
    // Clear vessels
    vesselMarkers.forEach(({ marker }) => {
        if (map && marker && map.hasLayer(marker)) {
            map.removeLayer(marker);
        }
    });
    vesselMarkers = [];
}

// ==================== MAP CONTROLS ====================

function toggleVesselMovement() {
    if (!isMapInitialized) {
        showNotification('Peta belum siap', 'error');
        return;
    }
    
    vesselMovementEnabled = !vesselMovementEnabled;

    const icon = document.getElementById('movementIcon');
    const text = document.getElementById('movementText');

    if (icon && text) {
        if (vesselMovementEnabled) {
            icon.className = 'fas fa-pause';
            text.textContent = 'Pause';
            startVesselMovement();
            showNotification('Pergerakan kapal diaktifkan', 'success');
        } else {
            icon.className = 'fas fa-play';
            text.textContent = 'Play';
            stopVesselMovement();
            showNotification('Pergerakan kapal dijeda', 'info');
        }
    }
}

function startVesselMovement() {
    if (vesselMovementInterval) {
        clearInterval(vesselMovementInterval);
    }

    vesselMovementInterval = setInterval(() => {
        if (!vesselMovementEnabled || !map) return;
        
        vesselMarkers.forEach((vessel) => {
            if (vessel.marker && vessel.currentDpi) {
                const newPosition = moveVesselWithinDpi(vessel);
                vessel.marker.setLatLng(newPosition);
                vessel.currentPosition = newPosition;
            }
        });
    }, 3000); // Move every 3 seconds
}

function moveVesselWithinDpi(vessel) {
    const { currentPosition, currentDpi } = vessel;
    const [currentLat, currentLng] = currentPosition;
    const [centerLat, centerLng] = currentDpi.center;
    const radius = currentDpi.radius;
    
    // Convert radius to degrees
    const radiusInDegrees = radius / 111000;
    
    // Small random movement (2% of radius)
    const maxMovement = radiusInDegrees * 0.02;
    
    let newLat = currentLat + (Math.random() - 0.5) * maxMovement;
    let newLng = currentLng + (Math.random() - 0.5) * maxMovement;
    
    // Check if new position is within DPI radius
    const distanceFromCenter = calculateDistance([newLat, newLng], currentDpi.center);
    
    if (distanceFromCenter > radius * 0.9) { // Keep within 90% of radius
        // Move back towards center
        const angleToCenter = Math.atan2(centerLng - newLng, centerLat - newLat);
        newLat = currentLat + Math.cos(angleToCenter) * maxMovement * 0.5;
        newLng = currentLng + Math.sin(angleToCenter) * maxMovement * 0.5;
    }
    
    return [newLat, newLng];
}

function calculateDistance(pos1, pos2) {
    const [lat1, lng1] = pos1;
    const [lat2, lng2] = pos2;
    const R = 6371000; // Earth radius in meters
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLng = (lng2 - lng1) * Math.PI / 180;
    const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
              Math.sin(dLng/2) * Math.sin(dLng/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return R * c;
}

function stopVesselMovement() {
    if (vesselMovementInterval) {
        clearInterval(vesselMovementInterval);
        vesselMovementInterval = null;
    }
}

function toggleLayers() {
    if (!isMapInitialized) {
        showNotification('Peta belum siap', 'error');
        return;
    }
    
    layersVisible = !layersVisible;
    zoneLayers.forEach(zone => {
        if (layersVisible) {
            map.addLayer(zone.layer);
        } else {
            map.removeLayer(zone.layer);
        }
    });
    
    showNotification(
        layersVisible ? 'Layer zona ditampilkan' : 'Layer zona disembunyikan', 
        'info'
    );
}

function refreshMap() {
    if (!isMapInitialized) {
        showNotification('Peta belum siap', 'error');
        return;
    }
    
    showNotification('Memperbarui peta...', 'info');
    stopVesselMovement();
    showLoading();

    // Clear and reload map data
    clearMapLayers();
    
    setTimeout(() => {
        loadMapData();
    }, 1000);
}

function fullScreen() {
    showNotification('Fitur layar penuh akan segera tersedia', 'info');
}

// ==================== UTILITY FUNCTIONS ====================

function showNotification(message, type) {
    // Remove existing notification
    const existing = document.querySelector('.notification-toast');
    if (existing) existing.remove();

    const bgColor = type === 'success' ? 'bg-success' : 
                   type === 'error' ? 'bg-danger' : 'bg-info';

    const notification = document.createElement('div');
    notification.className = `notification-toast position-fixed top-0 end-0 ${bgColor} text-white p-3 rounded m-3 shadow`;
    notification.style.zIndex = '9999';
    notification.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'exclamation-triangle' : 'info'}-circle me-2"></i>
            <span class="fw-medium">${message}</span>
        </div>
    `;

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transition = 'opacity 0.3s ease';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

function showLoading() {
    const loading = document.getElementById('loadingIndicator');
    if (loading) {
        loading.style.display = 'flex';
    }
}

function hideLoading() {
    const loading = document.getElementById('loadingIndicator');
    if (loading) {
        loading.style.display = 'none';
    }
    console.log('Loading hidden');
}

// Handle window resize for map
window.addEventListener('resize', function() {
    if (map) {
        setTimeout(() => {
            map.invalidateSize();
        }, 100);
    }
});

// Cleanup when page unloads
window.addEventListener('beforeunload', function() {
    stopVesselMovement();
});