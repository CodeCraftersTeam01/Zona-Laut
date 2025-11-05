<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Zona Laut</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" rel="stylesheet">
    <link rel="shortcut icon" href="../images/logo.png" type="image/x-icon">
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
    <!-- Mengganti CDN Tailwind dengan file CSS lokal -->
    <link rel="stylesheet" href="../src/output.css">
    <link rel="stylesheet" href="../css/dashboard.css">
</head>

<body class="bg-bg-light text-text-dark min-h-screen flex">
    <!-- Mobile Menu Button -->
    <button class="md:hidden fixed top-20 right-6 z-50 w-10 h-10 bg-primary duration-300 ease-out text-white border-none rounded-lg flex items-center justify-center text-xl cursor-pointer" id="mobileMenuBtn">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar -->
    <?php include '../components/sidebar_dashboard.php'; ?>

    <!-- Main Content -->
    <main class="main-content relative w-full flex-1 md:ml-64 ml-0 pt-4 transition-all duration-300">
        <!-- Header -->
        <header class="header flex z-10 absolute w-full justify-center md:justify-end items-center mb-8 flex-wrap gap-4 p-4">
            <div class="header-actions flex items-center gap-4 flex-wrap justify-end">
                <div class="search-box relative min-w-[250px] flex-1 max-w-[400px]">
                    <i class="fas fa-search search-icon absolute left-4 top-1/2 -translate-y-1/2 text-text-light"></i>
                    <input type="text" class="search-input w-full py-3 px-4 pl-12 border border-border rounded-xl bg-bg-white text-sm transition-all duration-300 focus:outline-none focus:border-primary focus:ring-3 focus:ring-primary/10" placeholder="Cari kapal">
                </div>
                <div class="action-buttons flex items-center gap-2">
                    <a href="frontend/profile/" class="user-menu-btn w-10 h-10 border-none bg-bg-white rounded-lg flex items-center justify-center text-text-dark cursor-pointer transition-all duration-300 hover:bg-bg-light" id="headerUserMenu">
                        <i class="fas fa-user"></i>
                    </a>
                </div>
            </div>
        </header>

        <!-- Map Section -->
        <div style="border-radius:0 !important;" class="map-container z-5 fixed bg-bg-white overflow-hidden h-full w-full top-0 left-0">
            <div id="dashboardMap" class="h-full w-full"></div>
        </div>

        <!-- Control Panel (ditambahkan untuk menghindari error) -->
        <div class="control-panel fixed bottom-4 right-4 bg-white p-4 rounded-lg shadow-lg z-30" id="controlPanel">
            <div style="background-color:#DEDEDE;padding:3px;width:30%;margin:0 auto; position:relative; top:-10px; border-radius:100px;" id="slider"></div>
            <div class="flex items-center space-x-4">
                <button id="toggleMovement" class="bg-primary text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-pause mr-2" id="movementIcon"></i>
                    <span id="movementText">Pause</span>
                </button>
                <div class="coordinates bg-gray-100 px-3 py-2 rounded text-sm">
                    <span>Lat: <span id="latCoord">-6.2000</span></span>
                    <span class="ml-2">Lng: <span id="lngCoord">106.8166</span></span>
                </div>
            </div>
            <!-- Vessels List Panel (ditambahkan untuk menghindari error) -->
            <div class="vessels-panel fixed lg:block hidden top-25 right-4 bg-white p-4 rounded-lg shadow-lg z-30 w-80 vesselsPanelMobile" id="vesselsPanelMobile">
                <div class="setting-item mb-[10px]">
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-sm font-medium text-gray-700">Tipe Peta</label>
                        <span class="text-xs text-gray-500" id="mapTypeLabel">Standard</span>
                    </div>
                    <div class="flex bg-gray-100 rounded-lg p-1">
                        <button class="map-type-btn flex-1 py-2 px-3 rounded-md text-sm font-medium transition-all duration-300 bg-white text-primary shadow-sm" data-type="standard">
                            <i class="fas fa-map mr-2"></i>Standard
                        </button>
                        <button class="map-type-btn flex-1 py-2 px-3 rounded-md text-sm font-medium transition-all duration-300 text-gray-600" data-type="satellite">
                            <i class="fas fa-satellite mr-2"></i>Satelit
                        </button>
                    </div>
                </div>
                <div class="flex items-center justify-between mb-2">
                    <label class="text-sm font-medium text-gray-700">Daftar kapal Saya</label>
                    <span class="text-xs text-gray-500" id="vesselCountMobile">Standard</span>
                </div>

                <div id="vesselsListMobile" style="max-height: 150px !important;" class="space-y-2 max-h-80 overflow-y-auto">
                    <!-- Daftar kapal akan dimuat di sini -->
                </div>
            </div>
        </div>

        <!-- Vessels List Panel (ditambahkan untuk menghindari error) -->
        <div class="vessels-panel fixed lg:block hidden top-25 right-4 bg-white p-4 rounded-lg shadow-lg z-30 w-80 " id="vesselsPanel">
            <div class="setting-item mb-[10px]">
                <div class="flex items-center justify-between mb-2">
                    <label class="text-sm font-medium text-gray-700">Tipe Peta</label>
                    <span class="text-xs text-gray-500" id="mapTypeLabel">Standard</span>
                </div>
                <div class="flex bg-gray-100 rounded-lg p-1">
                    <button class="map-type-btn flex-1 py-2 px-3 rounded-md text-sm font-medium transition-all duration-300 bg-white text-primary shadow-sm" data-type="standard">
                        <i class="fas fa-map mr-2"></i>Standard
                    </button>
                    <button class="map-type-btn flex-1 py-2 px-3 rounded-md text-sm font-medium transition-all duration-300 text-gray-600" data-type="satellite">
                        <i class="fas fa-satellite mr-2"></i>Satelit
                    </button>
                </div>
            </div>
            <div class="flex items-center justify-between mb-2">
                <label class="text-sm font-medium text-gray-700">Daftar kapal Saya</label>
                <span class="text-xs text-gray-500" id="vesselCount">Standard</span>
            </div>
            <div id="vesselsList" style="max-height: 250px !important;" class="space-y-2 max-h-80 overflow-y-auto">
                <!-- Daftar kapal akan dimuat di sini -->
            </div>
        </div>

    </main>

    <!-- Toast Container -->
    <div class="toast-container fixed top-5 right-5 z-50 flex flex-col gap-2 max-w-[400px]" id="toastContainer"></div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
    <?php include 'scripts.php'; ?>
    <script src="../js/script.js"></script>
</body>

</html>