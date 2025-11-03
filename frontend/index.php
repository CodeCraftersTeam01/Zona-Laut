<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zona Laut - Dashboard Monitoring Penangkapan Ikan</title>
    <link href="./src/output.css" rel="stylesheet">
    <link rel="stylesheet" href="./css/style.css">
    <link href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gradient-to-br from-white via-blue-50 to-blue-100 min-h-screen font-sans">
    <!-- Wave Background Elements -->
    <div class="fixed top-0 left-0 w-full h-64 bg-gradient-to-b from-blue-500/10 to-transparent -z-10"></div>
    <div class="fixed bottom-0 left-0 w-full h-64 bg-gradient-to-t from-blue-400/10 to-transparent -z-10"></div>

    <!-- Floating Bubbles -->
    <div class="fixed top-1/4 left-10 w-6 h-6 bg-blue-300/30 rounded-full animate-bubble"></div>
    <div class="fixed top-1/3 right-20 w-4 h-4 bg-blue-400/40 rounded-full animate-bubble" style="animation-delay: 1s;"></div>
    <div class="fixed bottom-1/4 left-1/4 w-8 h-8 bg-blue-200/20 rounded-full animate-bubble" style="animation-delay: 2s;"></div>

    <!-- Navigation -->
    <?php include './components/navbar.php'; ?>

    <!-- Hero Section -->
    <section class="pt-32 pb-20 px-4 bg-gradient-to-br relative from-white via-blue-50 to-blue-100">
        <div id="dashboardMap" class="absolute top-0 left-0 z-0 h-full w-full" style="opacity: 30%;"></div>
        <div class="max-w-7xl mx-auto z-10 relative">
            <div class="text-center mb-16">
                <div class="inline-flex items-center bg-blue-100 text-blue-800 px-4 py-2 rounded-full text-sm font-medium mb-6">
                    <i class="fas fa-compass mr-2"></i>
                    Solusi Monitoring Perikanan Modern
                </div>
                <h1 class="text-4xl md:text-6xl font-bold text-blue-900 mb-6 leading-tight">
                    Kelola <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-cyan-600">Zona Laut</span> dengan Presisi
                </h1>
                <p class="text-xl text-blue-700 max-w-3xl mx-auto mb-10">
                    Platform monitoring terintegrasi untuk memantau data penangkapan ikan secara real-time dengan akurasi tinggi dan antarmuka yang intuitif.
                </p>
                <div class="flex flex-col sm:flex-row justify-center gap-4">
                    <a href="frontend/auth/registrasi/" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-4 rounded-xl font-semibold text-lg transition duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                        Mulai Monitoring
                    </a>
                    <a href="frontend/demo/" class="bg-white text-blue-700 border-2 border-blue-200 hover:border-blue-300 px-8 py-4 rounded-xl font-semibold text-lg transition duration-300 shadow-md hover:shadow-lg">
                        <i class="fas fa-play-circle mr-2"></i>Lihat Demo
                    </a>
                </div>
            </div>

            <!-- Hero Dashboard Preview -->
            <div class="mt-20 max-w-6xl mx-auto">
                <div class="bg-white rounded-3xl border border-blue-200 p-8 shadow-2xl relative overflow-hidden">
                    <!-- Wave decoration -->
                    <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-blue-400 to-cyan-400"></div>

                    <div class="flex space-x-2 mb-6">
                        <div class="w-3 h-3 rounded-full bg-red-400"></div>
                        <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                        <div class="w-3 h-3 rounded-full bg-green-400"></div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Stats Cards -->
                        <div class="bg-gradient-to-br from-blue-50 to-white rounded-2xl p-6 border border-blue-100 shadow-sm">
                            <div class="flex items-center">
                                <div class="bg-blue-100 p-3 rounded-xl">
                                    <i class="fas fa-ship text-blue-600 text-xl"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-blue-600 text-sm font-medium">Total Kapal</p>
                                    <p class="text-blue-900 text-2xl font-bold">248</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gradient-to-br from-cyan-50 to-white rounded-2xl p-6 border border-cyan-100 shadow-sm">
                            <div class="flex items-center">
                                <div class="bg-cyan-100 p-3 rounded-xl">
                                    <i class="fas fa-fish text-cyan-600 text-xl"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-cyan-600 text-sm font-medium">Tangkapan Hari Ini</p>
                                    <p class="text-cyan-900 text-2xl font-bold">5.2 ton</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gradient-to-br from-indigo-50 to-white rounded-2xl p-6 border border-indigo-100 shadow-sm">
                            <div class="flex items-center">
                                <div class="bg-indigo-100 p-3 rounded-xl">
                                    <i class="fas fa-map-marker-alt text-indigo-600 text-xl"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-indigo-600 text-sm font-medium">Zona Aktif</p>
                                    <p class="text-indigo-900 text-2xl font-bold">18</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Mini Wave Pattern -->
                    <div class="mt-8 flex justify-center space-x-1">
                        <div class="w-2 h-4 bg-blue-300 rounded-full"></div>
                        <div class="w-2 h-6 bg-blue-400 rounded-full"></div>
                        <div class="w-2 h-8 bg-blue-500 rounded-full"></div>
                        <div class="w-2 h-6 bg-blue-400 rounded-full"></div>
                        <div class="w-2 h-4 bg-blue-300 rounded-full"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-20 px-4 bg-gradient-to-b from-white to-blue-50">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-blue-900 mb-4">Fitur Unggulan Platform</h2>
                <p class="text-xl text-blue-700 max-w-3xl mx-auto">
                    Teknologi canggih untuk mendukung industri perikanan yang berkelanjutan dan efisien
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="bg-white rounded-3xl p-8 border border-blue-100 shadow-lg transition-transform duration-300 hover:-translate-y-2 hover:shadow-xl">
                    <div class="bg-blue-100 w-14 h-14 rounded-xl flex items-center justify-center mb-6">
                        <i class="fas fa-map-marked-alt text-blue-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-blue-900 mb-4">Pemetaan Zona Laut</h3>
                    <p class="text-blue-700">
                        Visualisasi zona penangkapan ikan dengan peta interaktif dan batas-batas yang jelas untuk pengelolaan yang efektif.
                    </p>
                </div>

                <!-- Feature 2 -->
                <div class="bg-white rounded-3xl p-8 border border-blue-100 shadow-lg transition-transform duration-300 hover:-translate-y-2 hover:shadow-xl">
                    <div class="bg-cyan-100 w-14 h-14 rounded-xl flex items-center justify-center mb-6">
                        <i class="fas fa-chart-line text-cyan-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-blue-900 mb-4">Analisis Data Real-time</h3>
                    <p class="text-blue-700">
                        Pantau data penangkapan ikan secara real-time dengan dashboard analitik yang komprehensif dan mudah dipahami.
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="bg-white rounded-3xl p-8 border border-blue-100 shadow-lg transition-transform duration-300 hover:-translate-y-2 hover:shadow-xl">
                    <div class="bg-indigo-100 w-14 h-14 rounded-xl flex items-center justify-center mb-6">
                        <i class="fas fa-ship text-indigo-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-blue-900 mb-4">Pelacakan Kapal</h3>
                    <p class="text-blue-700">
                        Lacak pergerakan kapal penangkap ikan dengan teknologi GPS untuk memastikan operasi berjalan sesuai regulasi.
                    </p>
                </div>

                <!-- Feature 4 -->
                <div class="bg-white rounded-3xl p-8 border border-blue-100 shadow-lg transition-transform duration-300 hover:-translate-y-2 hover:shadow-xl">
                    <div class="bg-blue-100 w-14 h-14 rounded-xl flex items-center justify-center mb-6">
                        <i class="fas fa-database text-blue-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-blue-900 mb-4">Manajemen Data</h3>
                    <p class="text-blue-700">
                        Kelola data kapal, alat tangkap, dan hasil tangkapan dengan sistem terpusat yang aman dan terorganisir.
                    </p>
                </div>

                <!-- Feature 5 -->
                <div class="bg-white rounded-3xl p-8 border border-blue-100 shadow-lg transition-transform duration-300 hover:-translate-y-2 hover:shadow-xl">
                    <div class="bg-cyan-100 w-14 h-14 rounded-xl flex items-center justify-center mb-6">
                        <i class="fas fa-bell text-cyan-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-blue-900 mb-4">Notifikasi & Alert</h3>
                    <p class="text-blue-700">
                        Dapatkan pemberitahuan instan tentang aktivitas mencurigakan, pelanggaran zona, dan kondisi darurat.
                    </p>
                </div>

                <!-- Feature 6 -->
                <div class="bg-white rounded-3xl p-8 border border-blue-100 shadow-lg transition-transform duration-300 hover:-translate-y-2 hover:shadow-xl">
                    <div class="bg-indigo-100 w-14 h-14 rounded-xl flex items-center justify-center mb-6">
                        <i class="fas fa-file-export text-indigo-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-blue-900 mb-4">Laporan Otomatis</h3>
                    <p class="text-blue-700">
                        Hasilkan laporan periodik secara otomatis untuk analisis tren dan pemenuhan kewajiban regulasi.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 px-4 bg-gradient-to-b from-blue-50 to-white">
        <div class="max-w-5xl mx-auto text-center">
            <div class="bg-white rounded-3xl p-12 border border-blue-200 shadow-xl relative overflow-hidden">
                <!-- Wave decoration at bottom -->
                <div class="absolute bottom-0 left-0 w-full h-3 bg-gradient-to-r from-blue-300 via-cyan-400 to-blue-300"></div>

                <div class="inline-flex items-center bg-blue-100 text-blue-800 px-4 py-2 rounded-full text-sm font-medium mb-6">
                    <i class="fas fa-anchor mr-2"></i>
                    Siap Memulai?
                </div>
                <h2 class="text-3xl md:text-4xl font-bold text-blue-900 mb-6">Tingkatkan Efisiensi Operasi Laut Anda</h2>
                <p class="text-xl text-blue-700 mb-8 max-w-2xl mx-auto">
                    Bergabunglah dengan puluhan perusahaan perikanan yang telah menggunakan Zona Laut untuk meningkatkan produktivitas dan keberlanjutan.
                </p>
                <div class="flex flex-col sm:flex-row justify-center gap-4">
                    <a href="login.html" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-4 rounded-xl font-semibold text-lg transition duration-300 shadow-lg hover:shadow-xl">
                        Masuk Ke Dashboard
                    </a>
                    <a href="#" class="bg-white text-blue-700 border-2 border-blue-200 hover:border-blue-300 px-8 py-4 rounded-xl font-semibold text-lg transition duration-300 shadow-md hover:shadow-lg">
                        <i class="fas fa-calendar-alt mr-2"></i>Lihat Demo
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-blue-900 text-white py-12 px-4">
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="flex items-center mb-6 md:mb-0">
                    <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center mr-3">
                        <i class="fas fa-anchor text-blue-600 text-lg"></i>
                    </div>
                    <span class="text-white font-bold text-xl">Zona Laut</span>
                </div>
                <div class="flex space-x-6">
                    <a href="#" class="text-blue-200 hover:text-white transition duration-300">
                        <i class="fab fa-twitter text-xl"></i>
                    </a>
                    <a href="#" class="text-blue-200 hover:text-white transition duration-300">
                        <i class="fab fa-facebook text-xl"></i>
                    </a>
                    <a href="#" class="text-blue-200 hover:text-white transition duration-300">
                        <i class="fab fa-linkedin text-xl"></i>
                    </a>
                    <a href="#" class="text-blue-200 hover:text-white transition duration-300">
                        <i class="fab fa-instagram text-xl"></i>
                    </a>
                </div>
            </div>
            <div class="border-t border-blue-700 mt-8 pt-8 text-center text-blue-200">
                <p>&copy; 2025 Zona Laut. All rights reserved. | Membangun Masa Depan Perikanan Berkelanjutan</p>
            </div>
        </div>
    </footer>

    <script src="js/script.js"></script>
    <script>
        let map;

        document.addEventListener('DOMContentLoaded', () => {
            // Inisialisasi peta tanpa kontrol dan interaksi
            const map = L.map('dashboardMap', {
                zoomControl: false, // 🔹 Hilangkan tombol zoom (+/-)
                dragging: false, // 🔹 Nonaktifkan drag/geser
                scrollWheelZoom: false, // 🔹 Nonaktifkan zoom dengan scroll
                doubleClickZoom: false, // 🔹 Nonaktifkan zoom double-click
                boxZoom: false, // 🔹 Nonaktifkan zoom area
                keyboard: false, // 🔹 Nonaktifkan kontrol keyboard
                touchZoom: false // 🔹 Nonaktifkan pinch zoom di mobile
            }).setView([-5.5489, 118.0149], 5); // Pusat di Indonesia

            // Tambahkan layer dengan opacity (misal 0.6)
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; Zona Laut'
            }).addTo(map);
        });
    </script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <style>
        @keyframes bubble {
            0% {
                transform: translateY(0) scale(1);
                opacity: 0.7;
            }

            100% {
                transform: translateY(-100px) scale(1.2);
                opacity: 0;
            }
        }

        .animate-bubble {
            animation: bubble 6s infinite ease-in;
        }
    </style>
</body>

</html>