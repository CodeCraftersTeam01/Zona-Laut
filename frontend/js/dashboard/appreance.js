// Settings functionality
let satelliteLayer;
let isSatelliteView = false;
let isFullscreen = false;

function initSettingsPanel() {
    const settingsToggle = document.getElementById('settingsToggle');
    const settingsPanel = document.getElementById('settingsPanel');
    const closeSettings = document.querySelector('.close-settings');
    const mapTypeBtns = document.querySelectorAll('.map-type-btn');
    const fullscreenToggle = document.getElementById('fullscreenToggle');
    const vesselLabelsToggle = document.getElementById('vesselLabelsToggle');
    const zoneVisibilityToggle = document.getElementById('zoneVisibilityToggle');
    const weatherOverlayToggle = document.getElementById('weatherOverlayToggle');
    const themeBtns = document.querySelectorAll('.theme-btn');
    const resetSettings = document.getElementById('resetSettings');
    const mapTypeLabel = document.getElementById('mapTypeLabel');

    // Toggle settings panel
    settingsToggle.addEventListener('click', () => {
        settingsPanel.classList.toggle('hidden');
        settingsPanel.classList.toggle('show');
    });

    closeSettings.addEventListener('click', () => {
        settingsPanel.classList.add('hidden');
        settingsPanel.classList.remove('show');
    });

    // Map type toggle
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

    // Fullscreen toggle
    fullscreenToggle.addEventListener('change', toggleFullscreen);

    // Vessel labels toggle
    vesselLabelsToggle.addEventListener('change', toggleVesselLabels);

    // Zone visibility toggle
    zoneVisibilityToggle.addEventListener('change', toggleZoneVisibility);

    // Weather overlay toggle
    weatherOverlayToggle.addEventListener('change', toggleWeatherOverlay);

    // Theme buttons
    themeBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const theme = btn.dataset.theme;
            changeMapTheme(theme);
            
            themeBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
        });
    });

    // Reset settings
    resetSettings.addEventListener('click', resetAllSettings);
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

function toggleFullscreen() {
    const mapContainer = document.getElementById('dashboardMap');
    
    if (!isFullscreen) {
        // Enter fullscreen
        if (mapContainer.requestFullscreen) {
            mapContainer.requestFullscreen();
        } else if (mapContainer.webkitRequestFullscreen) {
            mapContainer.webkitRequestFullscreen();
        } else if (mapContainer.msRequestFullscreen) {
            mapContainer.msRequestFullscreen();
        }
        isFullscreen = true;
        toastSystem.success('Layar Penuh', 'Mode layar penuh diaktifkan');
    } else {
        // Exit fullscreen
        if (document.exitFullscreen) {
            document.exitFullscreen();
        } else if (document.webkitExitFullscreen) {
            document.webkitExitFullscreen();
        } else if (document.msExitFullscreen) {
            document.msExitFullscreen();
        }
        isFullscreen = false;
        toastSystem.info('Layar Penuh', 'Mode layar penuh dinonaktifkan');
    }
}

function toggleVesselLabels() {
    const showLabels = document.getElementById('vesselLabelsToggle').checked;
    
    vesselMarkers.forEach(({ marker, vesselId }) => {
        const vessel = kapalData.find(k => k.id === vesselId);
        if (vessel) {
            if (showLabels) {
                marker.bindTooltip(vessel.nama_kapal, {
                    permanent: true,
                    direction: 'right',
                    className: 'vessel-label'
                });
            } else {
                marker.unbindTooltip();
            }
        }
    });
    
    toastSystem.info('Label Kapal', showLabels ? 'Label kapal ditampilkan' : 'Label kapal disembunyikan');
}

function toggleZoneVisibility() {
    const showZones = document.getElementById('zoneVisibilityToggle').checked;
    
    zoneLayers.forEach(layer => {
        if (showZones) {
            map.addLayer(layer);
        } else {
            map.removeLayer(layer);
        }
    });
    
    toastSystem.info('Zona Penangkapan', showZones ? 'Zona ditampilkan' : 'Zona disembunyikan');
}

function toggleWeatherOverlay() {
    const showWeather = document.getElementById('weatherOverlayToggle').checked;
    
    if (showWeather) {
        toastSystem.success('Overlay Cuaca', 'Informasi cuaca ditampilkan');
        // Implement weather overlay logic here
    } else {
        toastSystem.info('Overlay Cuaca', 'Informasi cuaca disembunyikan');
        // Remove weather overlay logic here
    }
}

function changeMapTheme(theme) {
    // Remove existing theme classes
    document.body.classList.remove('map-theme-light', 'map-theme-dark', 'map-theme-blue');
    
    // Add new theme class
    document.body.classList.add(`map-theme-${theme}`);
    
    toastSystem.success('Tema Peta', `Tema ${theme} diterapkan`);
}

function resetAllSettings() {
    // Reset to standard map
    if (isSatelliteView) {
        toggleMapType('standard');
        document.querySelector('.map-type-btn[data-type="standard"]').click();
    }
    
    // Reset toggles
    document.getElementById('fullscreenToggle').checked = false;
    document.getElementById('vesselLabelsToggle').checked = true;
    document.getElementById('zoneVisibilityToggle').checked = true;
    document.getElementById('weatherOverlayToggle').checked = false;
    
    // Reset theme to light
    changeMapTheme('light');
    document.querySelector('.theme-btn[data-theme="light"]').classList.add('active');
    document.querySelectorAll('.theme-btn').forEach(btn => {
        if (btn.dataset.theme !== 'light') {
            btn.classList.remove('active');
        }
    });
    
    // Apply reset changes
    toggleVesselLabels();
    toggleZoneVisibility();
    toggleWeatherOverlay();
    
    toastSystem.success('Pengaturan', 'Semua pengaturan telah direset ke default');
}

// Fullscreen change event
document.addEventListener('fullscreenchange', handleFullscreenChange);
document.addEventListener('webkitfullscreenchange', handleFullscreenChange);
document.addEventListener('msfullscreenchange', handleFullscreenChange);

function handleFullscreenChange() {
    isFullscreen = !!(document.fullscreenElement || 
                     document.webkitFullscreenElement || 
                     document.msFullscreenElement);
    document.getElementById('fullscreenToggle').checked = isFullscreen;
}

// // Add to your existing setupEventListeners function
// function setupEventListeners() {
//     // ... existing code ...
    
//     // Initialize settings panel
//     initSettingsPanel();
// }
