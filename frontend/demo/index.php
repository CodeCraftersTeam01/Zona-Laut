<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demo Monitoring - Zona Laut</title>
    <link href="../src/output.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../css/demo.css">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head>

<body class="bg-gradient-to-br from-white via-blue-50 to-blue-100 min-h-screen font-sans">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b border-blue-200">
        <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <div class="w-8 h-8 sm:w-10 sm:h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center mr-2 sm:mr-3">
                            <i class="fas fa-anchor text-white text-sm sm:text-lg"></i>
                        </div>
                        <div class="flex items-center">
                            <span class="text-blue-900 font-bold text-lg sm:text-xl"><a href="./frontend">Zona Laut</a></span>
                            <span class="ml-2 sm:ml-3 bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs font-medium">DEMO</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center space-x-2 sm:space-x-4">
                    <div class="text-xs sm:text-sm text-blue-700 hidden sm:block">
                        <i class="fas fa-clock mr-1"></i>
                        <span id="currentTime">--:--:--</span>
                    </div>
                    <a href="frontend/auth/login" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 sm:px-4 sm:py-2 rounded-lg font-medium transition duration-300 text-sm sm:text-base">
                        <span class="hidden sm:inline">Masuk ke Akun</span>
                        <span class="sm:hidden"><i class="fas fa-sign-in-alt"></i></span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Mobile Time Display -->
    <div class="sm:hidden bg-blue-600 text-white py-2 px-4 text-center text-sm">
        <i class="fas fa-clock mr-2"></i>
        <span id="mobileTime">--:--:--</span>
    </div>

    <!-- Main Dashboard -->
    <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 py-4 sm:py-6 lg:py-8">

        <!-- Stats Overview -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 lg:gap-6 mb-4 sm:mb-6 lg:mb-8">
            <div class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-6 border border-blue-200 shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-600 text-xs sm:text-sm font-medium">Kapal Aktif</p>
                        <p class="text-blue-900 text-xl sm:text-2xl font-bold mt-1"><span id="activeShips">0</span></p>
                    </div>
                    <div class="bg-blue-100 p-2 sm:p-3 rounded-lg sm:rounded-xl">
                        <i class="fas fa-ship text-blue-600 text-lg sm:text-xl"></i>
                    </div>
                </div>
                <div class="mt-2 sm:mt-3 flex items-center text-green-600 text-xs sm:text-sm">
                    <i class="fas fa-arrow-up mr-1"></i>
                    <span id="shipChange">Loading...</span>
                </div>
            </div>

            <div class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-6 border border-blue-200 shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-cyan-600 text-xs sm:text-sm font-medium">Zona Monitoring</p>
                        <p class="text-cyan-900 text-xl sm:text-2xl font-bold mt-1"><span id="zoneCount">0</span></p>
                    </div>
                    <div class="bg-cyan-100 p-2 sm:p-3 rounded-lg sm:rounded-xl">
                        <i class="fas fa-map-marked-alt text-cyan-600 text-lg sm:text-xl"></i>
                    </div>
                </div>
                <div class="mt-2 sm:mt-3 flex items-center text-blue-600 text-xs sm:text-sm">
                    <i class="fas fa-circle mr-1"></i>
                    <span id="zoneStatus">Loading...</span>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="space-y-4 sm:space-y-6 lg:space-y-8">
            <!-- Map Visualization -->
            <div class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-6 border border-blue-200 shadow-lg">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-4 sm:mb-6 gap-3">
                    <h2 class="text-lg sm:text-xl font-bold text-blue-900">Peta Monitoring Zona Laut</h2>
                    <div class="flex flex-wrap gap-2">
                        <button onclick="fullScreen()" class="bg-blue-100 hover:bg-blue-200 text-blue-700 px-3 py-2 rounded-lg text-xs sm:text-sm font-medium transition duration-300 flex-1 sm:flex-none min-w-[120px]">
                            <i class="fas fa-expand mr-1"></i>Fullscreen
                        </button>
                        <button onclick="refreshMap()" class="bg-blue-100 hover:bg-blue-200 text-blue-700 px-3 py-2 rounded-lg text-xs sm:text-sm font-medium transition duration-300 flex-1 sm:flex-none min-w-[120px]">
                            <i class="fas fa-sync-alt mr-1"></i>Refresh
                        </button>
                        <button onclick="toggleLayers()" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg text-xs sm:text-sm font-medium transition duration-300 flex-1 sm:flex-none min-w-[120px]">
                            <i class="fas fa-layer-group mr-1"></i>Layers
                        </button>
                        <button onclick="toggleVesselMovement()" class="bg-purple-600 hover:bg-purple-700 text-white px-3 py-2 rounded-lg text-xs sm:text-sm font-medium transition duration-300 flex-1 sm:flex-none min-w-[120px]">
                            <i class="fas fa-pause mr-1" id="movementIcon"></i><span id="movementText">Pause</span>
                        </button>
                    </div>
                </div>

                <!-- Map Container -->
                <div id="monitoringMap" class="rounded-lg sm:rounded-xl h-64 sm:h-80 lg:h-96 border border-blue-300"></div>

                <div class="mt-3 sm:mt-4 grid grid-cols-2 sm:grid-cols-4 gap-2 sm:gap-3 text-xs sm:text-sm text-blue-700">
                    <div class="flex items-center">
                        <div class="w-2 h-2 sm:w-3 sm:h-3 bg-blue-600 rounded-full mr-2"></div>
                        <span class="truncate">Kapal Aktif</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-2 h-2 sm:w-3 sm:h-3 border-2 border-blue-500 rounded-full mr-2"></div>
                        <span class="truncate">Zona Penangkapan</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-2 h-2 sm:w-3 sm:h-3 border-2 border-green-500 rounded-full mr-2"></div>
                        <span class="truncate">Zona Konservasi</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-2 h-2 sm:w-3 sm:h-3 border-2 border-red-500 rounded-full mr-2"></div>
                        <span class="truncate">Zona Terlarang</span>
                    </div>
                </div>
            </div>

            <!-- Active Vessels -->
            <div class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-6 border border-blue-200 shadow-lg">
                <div class="flex items-center justify-between mb-3 sm:mb-4">
                    <h2 class="text-lg sm:text-xl font-bold text-blue-900">Kapal Aktif</h2>
                    <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded-full text-xs font-medium" id="vesselCount">0 kapal</span>
                </div>

                <div class="space-y-2 sm:space-y-3" id="vesselsList">
                    <div class="text-center py-6 sm:py-8 text-gray-500">
                        <i class="fas fa-spinner fa-spin text-xl sm:text-2xl mb-2"></i>
                        <p class="text-sm sm:text-base">Memuat data kapal...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script src="../js/demo/script.js"></script>
    <script src="../js/script.js"></script>

   >
</body>

</html>