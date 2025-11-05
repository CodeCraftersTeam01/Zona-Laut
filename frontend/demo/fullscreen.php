<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peta Monitoring - Zona Laut</title>
    <link href="../src/output.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <link rel="stylesheet" href="../css/fullscreen.css">
    <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
</head>

<body class="bg-gray-900 min-h-screen font-sans overflow-hidden">
    <!-- Fullscreen Map Container -->
    <div id="fullscreenMap" class="w-full h-screen fixed top-0 left-0 z-0"></div>

    <!-- Control Panel -->
    <div id="controlPanel" class="fixed top-4 left-4 z-50 bg-white rounded-xl shadow-lg p-4 w-80 max-h-[90vh] overflow-y-auto transition-all duration-300">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-blue-900">Peta Monitoring</h2>
            <button onclick="exitFullScreen()" class="text-gray-500 hover:text-gray-700 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Zoom Controls -->
        <div class="flex justify-between mb-4">
            <button onclick="zoomIn()" class="bg-blue-600 hover:bg-blue-700 text-white w-10 h-10 rounded-lg flex items-center justify-center transition-colors">
                <i class="fas fa-plus"></i>
            </button>
            <button onclick="zoomOut()" class="bg-blue-600 hover:bg-blue-700 text-white w-10 h-10 rounded-lg flex items-center justify-center transition-colors">
                <i class="fas fa-minus"></i>
            </button>
            <button onclick="resetView()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                <i class="fas fa-crosshairs mr-2"></i> Reset
            </button>
        </div>

        <!-- Layers Control -->
        <div class="mb-4">
            <h3 class="font-medium text-gray-700 mb-2">Layer Peta</h3>
            <div class="space-y-2">
                <div class="flex items-center">
                    <input type="checkbox" id="vesselLayer" checked class="mr-2" onchange="toggleVesselLayer()">
                    <label for="vesselLayer" class="text-gray-700">Kapal Aktif</label>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" id="zoneLayer" checked class="mr-2" onchange="toggleZoneLayer()">
                    <label for="zoneLayer" class="text-gray-700">Zona Monitoring</label>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" id="satelliteLayer" class="mr-2" onchange="toggleSatelliteLayer()">
                    <label for="satelliteLayer" class="text-gray-700">Tampilan Satelit</label>
                </div>
            </div>
        </div>

        <!-- Vessel Controls -->
        <div class="mb-4">
            <h3 class="font-medium text-gray-700 mb-2">Kontrol Kapal</h3>
            <div class="flex space-x-2">
                <button id="movementToggle" onclick="toggleVesselMovement()"
                    class="bg-purple-600 hover:bg-purple-700 text-white px-3 py-2 rounded-lg flex-1 flex items-center justify-center transition-colors">
                    <i class="fas fa-pause mr-2" id="movementIcon"></i>
                    <span id="movementText">Pause</span>
                </button>
                <button onclick="refreshData()" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg flex items-center justify-center transition-colors">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
        </div>

        <!-- Vessel List -->
        <div>
            <h3 class="font-medium text-gray-700 mb-2">Kapal Aktif (<span id="vesselCount">0</span>)</h3>
            <div id="vesselsList" class="space-y-2 max-h-60 overflow-y-auto">
                <div class="text-center py-4 text-gray-500">
                    <i class="fas fa-spinner fa-spin text-lg mb-2"></i>
                    <p class="text-sm">Memuat data kapal...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Map Controls - Right Side -->
    <div class="fixed top-4 right-4 z-50 flex flex-col space-y-2">
        <button onclick="toggleControlPanel()" class="bg-white hover:bg-gray-100 text-gray-700 w-10 h-10 rounded-lg flex items-center justify-center shadow-lg transition-colors">
            <i class="fas fa-layer-group"></i>
        </button>
        <button onclick="toggleFullScreen()" class="bg-white hover:bg-gray-100 text-gray-700 w-10 h-10 rounded-lg flex items-center justify-center shadow-lg transition-colors">
            <i class="fas fa-expand"></i>
        </button>
        <button onclick="showCurrentLocation()" class="bg-white hover:bg-gray-100 text-gray-700 w-10 h-10 rounded-lg flex items-center justify-center shadow-lg transition-colors">
            <i class="fas fa-location-arrow"></i>
        </button>
    </div>

    <!-- Legend -->
    <div class="fixed bottom-4 right-4 z-50 bg-white rounded-xl shadow-lg p-4">
        <h3 class="font-medium text-gray-700 mb-2">Legenda</h3>
        <div class="space-y-2 text-sm">
            <div class="flex items-center">
                <div class="w-3 h-3 bg-blue-600 rounded-full mr-2"></div>
                <span>Kapal Aktif</span>
            </div>
            <div class="flex items-center">
                <div class="w-3 h-3 border-2 border-blue-500 rounded-full mr-2"></div>
                <span>Zona Penangkapan</span>
            </div>
            <div class="flex items-center">
                <div class="w-3 h-3 border-2 border-green-500 rounded-full mr-2"></div>
                <span>Zona Konservasi</span>
            </div>
            <div class="flex items-center">
                <div class="w-3 h-3 border-2 border-red-500 rounded-full mr-2"></div>
                <span>Zona Terlarang</span>
            </div>
        </div>
    </div>

    <!-- Coordinates Display -->
    <div class="fixed bottom-4 left-4 z-50 bg-black bg-opacity-70 text-white px-3 py-2 rounded-lg text-sm backdrop-blur-sm">
        <div>Lat: <span id="latCoord">0.0000</span></div>
        <div>Lng: <span id="lngCoord">0.0000</span></div>
    </div>

    <!-- Mobile Menu Button (for smaller screens) -->
    <button id="mobileMenuButton" class="fixed top-4 left-4 z-50 bg-white hover:bg-gray-100 text-gray-700 w-10 h-10 rounded-lg flex items-center justify-center shadow-lg transition-colors md:hidden">
        <i class="fas fa-bars"></i>
    </button>

    <script src="../js/demo/fullScreen.js"></script>
</body>

</html>