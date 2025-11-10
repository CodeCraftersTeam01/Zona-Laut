<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Tangkapan Ikan - Zona Laut</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
    <!-- Mengganti CDN Tailwind dengan file CSS lokal -->
    <link rel="stylesheet" href="../src/output.css">
    <link rel="shortcut icon" href="../images/logo.png" type="image/x-icon">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/tangkapan.css">
</head>

<body class="bg-bg-light text-text-dark min-h-screen flex">
    <!-- Mobile Menu Button -->
    <button class="md:hidden fixed top-20 right-6 z-50 w-10 h-10 bg-primary duration-300 ease-out text-white border-none rounded-lg flex items-center justify-center text-xl cursor-pointer" id="mobileMenuBtn">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar -->
    <?php include '../components/sidebar_dashboard.php'; ?>

    <!-- Main Content -->
    <main class="main-content mt-5  relative w-full flex-1 z-10 md:ml-64 ml-0 pt-4 transition-all duration-300">

        <!-- Header Section -->
        <div class="px-6 py-4">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Data Tangkapan Ikan</h1>
                    <p class="text-gray-600">Kelola dan pantau data tangkapan ikan dari kapal Anda</p>
                </div>
                <button onclick="openTambahModal()" class="mt-4 md:mt-0 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition duration-200 flex items-center">
                    <i class="fas fa-plus mr-2"></i>Tambah Tangkapan
                </button>
            </div>
        </div>

        <!-- Stats Section -->
        <div class="px-6 pb-4">
            <div class="stats-grid">
                <div class="stat-card blue">
                    <div class="icon">
                        <i class="fas fa-fish text-xl"></i>
                    </div>
                    <div class="value" id="totalTangkapan">0</div>
                    <div class="label">Total Tangkapan</div>
                </div>
                <div class="stat-card green">
                    <div class="icon">
                        <i class="fas fa-weight-hanging text-xl"></i>
                    </div>
                    <div class="value" id="totalBerat">0 kg</div>
                    <div class="label">Total Berat Ikan</div>
                </div>
                <div class="stat-card yellow">
                    <div class="icon">
                        <i class="fas fa-money-bill-wave text-xl"></i>
                    </div>
                    <div class="value" id="totalNilai">Rp 0</div>
                    <div class="label">Total Nilai Tangkapan</div>
                </div>
                <div class="stat-card purple">
                    <div class="icon">
                        <i class="fas fa-ship text-xl"></i>
                    </div>
                    <div class="value" id="totalKapal">0</div>
                    <div class="label">Kapal Aktif</div>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="px-6 pb-4">
            <div class="filter-section">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex flex-col md:flex-row gap-4 flex-1">
                        <div class="form-group mb-0">
                            <label class="form-label">Filter Kapal</label>
                            <select id="filterKapal" class="form-select">
                                <option value="">Semua Kapal</option>
                            </select>
                        </div>
                        <div class="form-group mb-0">
                            <label class="form-label">Filter Tanggal Mulai</label>
                            <input type="date" id="filterTanggalMulai" class="form-input">
                        </div>
                        <div class="form-group mb-0">
                            <label class="form-label">Filter Tanggal Akhir</label>
                            <input type="date" id="filterTanggalAkhir" class="form-input">
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button onclick="applyFilters()" class="btn btn-primary">
                            <i class="fas fa-filter mr-2"></i>Terapkan Filter
                        </button>
                        <button onclick="resetFilters()" class="btn btn-secondary">
                            <i class="fas fa-redo mr-2"></i>Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading Indicator -->
        <div id="loadingIndicator" class="flex justify-center items-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
            <span class="ml-3 text-gray-600">Memuat data tangkapan...</span>
        </div>

        <!-- Data Tangkapan Section -->
        <div class="px-6 pb-6">
            <!-- Data Table -->
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Kapal</th>
                            <th>Jenis Ikan</th>
                            <th>Berat (kg)</th>
                            <th>Harga/Kg</th>
                            <th>Total Nilai</th>
                            <th>Zona</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tangkapanTableBody">
                        <!-- Data akan diisi oleh JavaScript -->
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            <div id="emptyState" class="hidden text-center py-12">
                <div class="max-w-md mx-auto">
                    <div class="w-24 h-24 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-fish text-gray-400 text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">Tidak Ada Data Tangkapan</h3>
                    <p class="text-gray-500 mb-6">Belum ada data tangkapan ikan yang tercatat.</p>
                    <button onclick="openTambahModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition duration-200">
                        <i class="fas fa-plus mr-2"></i>Tambah Tangkapan
                    </button>
                </div>
            </div>

            <!-- Error State -->
            <div id="errorState" class="hidden px-6">
                <div class="bg-red-50 border border-red-200 rounded-lg p-6 text-center">
                    <div class="w-16 h-16 mx-auto mb-4 bg-red-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-red-500 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-red-800 mb-2">Gagal Memuat Data</h3>
                    <p class="text-red-600 mb-4" id="errorMessage">Terjadi kesalahan saat memuat data tangkapan.</p>
                    <button onclick="loadTangkapanData()" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg transition duration-200">
                        <i class="fas fa-redo mr-2"></i>Coba Lagi
                    </button>
                </div>
            </div>
        </div>

    </main>

    <!-- Modal Detail Tangkapan -->
    <div id="detailModal" class="modal-overlay z-100">
        <div class="modal-content">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-2xl font-bold text-gray-800">Detail Tangkapan Ikan</h3>
                    <button onclick="closeDetailModal()" class="text-gray-500 hover:text-gray-700 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <div id="detailContent" class="space-y-4">
                    <!-- Detail tangkapan akan dimuat di sini -->
                </div>

                <div class="flex justify-end space-x-3 mt-6 pt-4 border-t border-gray-200">
                    <button onclick="closeDetailModal()" class="btn btn-secondary">
                        Tutup
                    </button>
                    <button id="editFromDetailBtn" onclick="openEditModal()" class="btn btn-primary">
                        <i class="fas fa-edit mr-2"></i>Edit Data
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah/Edit Tangkapan -->
    <div id="formModal" class="modal-overlay z-100">
        <div class="modal-content">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 id="formModalTitle" class="text-2xl font-bold text-gray-800">Tambah Tangkapan Baru</h3>
                    <button onclick="closeFormModal()" class="text-gray-500 hover:text-gray-700 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <form id="tangkapanForm" class="space-y-4">
                    <input type="hidden" id="tangkapanId" name="id">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label class="form-label">Kapal *</label>
                            <select id="kapalSelect" name="id_kapal" class="form-select" required>
                                <option value="">Pilih Kapal</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Jenis Ikan *</label>
                            <input type="text" id="namaIkan" name="nama_ikan"
                                class="form-input" placeholder="Masukkan jenis ikan" required>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label class="form-label">Berat Ikan (kg) *</label>
                            <input type="number" id="beratIkan" name="berat_ikan" step="0.01" min="0"
                                class="form-input" placeholder="0.00" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Harga per Kg (Rp) *</label>
                            <input type="number" id="hargaPerKilo" name="harga_perkilo" min="0"
                                class="form-input" placeholder="0" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Zona Penangkapan *</label>
                        <select id="zonaSelect" name="id_dpi" class="form-select" required>
                            <option value="">Pilih Zona</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label class="form-label">Tanggal Tangkapan *</label>
                            <input type="date" id="tanggalTangkapan" name="tanggal_tangkapan"
                                class="form-input" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Waktu Tangkapan *</label>
                            <input type="time" id="waktuTangkapan" name="waktu_tangkapan"
                                class="form-input" required>
                        </div>
                    </div>

                    <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                        <div class="flex items-start">
                            <i class="fas fa-info-circle text-blue-500 mt-1 mr-3"></i>
                            <div>
                                <p class="text-sm text-blue-700">
                                    <strong>Informasi:</strong> Total nilai tangkapan akan dihitung otomatis berdasarkan berat ikan dan harga per kilogram.
                                </p>
                                <p class="text-sm font-medium text-blue-800 mt-2">
                                    Total: <span id="totalDisplay">Rp 0</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 mt-6 pt-4 border-t border-gray-200">
                        <button type="button" onclick="closeFormModal()" class="btn btn-secondary">
                            Batal
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save mr-2"></i>Simpan Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container fixed top-5 right-5 z-50 flex flex-col gap-2 max-w-[400px]" id="toastContainer"></div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
    <script src="../js/script.js"></script>
    <script src="../js/dashboard/auth.js"></script>
    <script src="../js/dashboard/sidebar.js"></script>

    <script src="../js/dashboard/tangkapan.js"></script>
</body>

</html>