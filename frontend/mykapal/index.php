<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyKapal - Zona Laut</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
    <!-- Mengganti CDN Tailwind dengan file CSS lokal -->
    <link rel="stylesheet" href="../src/output.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/myKapal.css">
</head>

<body class="bg-bg-light text-text-dark min-h-screen flex">
    <!-- Mobile Menu Button -->
    <button class="md:hidden fixed top-20 right-6 z-50 w-10 h-10 bg-primary duration-300 ease-out text-white border-none rounded-lg flex items-center justify-center text-xl cursor-pointer" id="mobileMenuBtn">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar -->
    <?php include '../components/sidebar_dashboard.php'; ?>

    <!-- Main Content -->
    <main class="main-content relative w-full flex-1 z-10 md:ml-64 ml-0 pt-4 transition-all duration-300">

        <!-- Header Section -->
        <div class="px-6 py-4">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Data Kapal Saya</h1>
                    <p class="text-gray-600">Kelola dan pantau data kapal yang terverifikasi dan menunggu verifikasi</p>
                </div>
                <button onclick="openTambahModal()" class="mt-4 md:mt-0 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition duration-200 flex items-center">
                    <i class="fas fa-plus mr-2"></i>Tambah Kapal
                </button>
            </div>
        </div>

        <!-- Loading Indicator -->
        <div id="loadingIndicator" class="flex justify-center items-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
            <span class="ml-3 text-gray-600">Memuat data kapal...</span>
        </div>

        <!-- Data Kapal Section -->
        <div class="px-6 pb-6">
            <!-- Tab Navigation -->
            <div class="tab-container">
                <button class="tab active" onclick="showTab('verified')">
                    <i class="fas fa-check-circle mr-2"></i>Kapal Terverifikasi
                    <span id="verifiedCount" class="ml-2 bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">0</span>
                </button>
                <button class="tab" onclick="showTab('unverified')">
                    <i class="fas fa-clock mr-2"></i>Menunggu Verifikasi
                    <span id="unverifiedCount" class="ml-2 bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full">0</span>
                </button>
            </div>

            <!-- Kapal Terverifikasi -->
            <div id="verifiedSection" class="tab-content">
                <h3 class="section-title">Kapal Terverifikasi</h3>
                <div id="verifiedContainer" class="grid grid-cols-1 gap-6">
                    <!-- Kapal terverifikasi akan ditampilkan di sini -->
                </div>

                <!-- Empty State untuk Kapal Terverifikasi -->
                <div id="verifiedEmptyState" class="hidden text-center py-12">
                    <div class="max-w-md mx-auto">
                        <div class="w-24 h-24 mx-auto mb-4 bg-green-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-check-circle text-green-500 text-3xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-700 mb-2">Tidak Ada Kapal Terverifikasi</h3>
                        <p class="text-gray-500 mb-6">Belum ada kapal yang terverifikasi dalam sistem.</p>
                    </div>
                </div>
            </div>

            <!-- Kapal Menunggu Verifikasi -->
            <div id="unverifiedSection" class="tab-content hidden">
                <h3 class="section-title">Menunggu Verifikasi</h3>
                <div id="unverifiedContainer" class="grid grid-cols-1 gap-6">
                    <!-- Kapal tidak terverifikasi akan ditampilkan di sini -->
                </div>

                <!-- Empty State untuk Kapal Tidak Terverifikasi -->
                <div id="unverifiedEmptyState" class="hidden text-center py-12">
                    <div class="max-w-md mx-auto">
                        <div class="w-24 h-24 mx-auto mb-4 bg-yellow-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-clock text-yellow-500 text-3xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-700 mb-2">Tidak Ada Kapal Menunggu Verifikasi</h3>
                        <p class="text-gray-500 mb-6">Semua kapal sudah terverifikasi atau belum ada kapal yang ditambahkan.</p>
                        <button onclick="openTambahModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition duration-200">
                            <i class="fas fa-plus mr-2"></i>Tambah Kapal
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Error State -->
        <div id="errorState" class="hidden px-6">
            <div class="bg-red-50 border border-red-200 rounded-lg p-6 text-center">
                <div class="w-16 h-16 mx-auto mb-4 bg-red-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-red-500 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-red-800 mb-2">Gagal Memuat Data</h3>
                <p class="text-red-600 mb-4" id="errorMessage">Terjadi kesalahan saat memuat data kapal.</p>
                <button onclick="loadKapalData()" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg transition duration-200">
                    <i class="fas fa-redo mr-2"></i>Coba Lagi
                </button>
            </div>
        </div>

    </main>

    <!-- Modal Detail Kapal -->
    <div id="detailModal" class="modal-overlay z-100">
        <div class="modal-content">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-2xl font-bold text-gray-800">Detail Kapal</h3>
                    <button onclick="closeDetailModal()" class="text-gray-500 hover:text-gray-700 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <div id="detailContent" class="space-y-4">
                    <!-- Detail kapal akan dimuat di sini -->
                </div>

                <div class="flex justify-end space-x-3 mt-6 pt-4 border-t border-gray-200">
                    <button onclick="closeDetailModal()" class="btn btn-secondary">
                        Tutup
                    </button>
                    <button id="editFromDetailBtn" onclick="openEditModal()" class="btn btn-primary">
                        <i class="fas fa-edit mr-2"></i>Edit Kapal
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit Kapal -->
    <div id="editModal" class="modal-overlay z-100">
        <div class="modal-content">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-2xl font-bold text-gray-800">Edit Data Kapal</h3>
                    <button onclick="closeEditModal()" class="text-gray-500 hover:text-gray-700 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <form id="editForm" class="space-y-4">
                    <input type="hidden" id="editKapalId" name="id">

                    <div class="form-group">
                        <label class="form-label">Nama Kapal *</label>
                        <input type="text" id="editNamaKapal" name="nama_kapal"
                            class="form-input" placeholder="Masukkan nama kapal" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Jenis Kapal *</label>
                        <select id="editJenisKapal" name="jenis_kapal" class="form-select" required>
                            <option value="">Pilih Jenis Kapal</option>
                            <option value="Kapal Penangkap Ikan">Kapal Penangkap Ikan</option>
                            <option value="Kapal Angkut">Kapal Angkut</option>
                            <option value="Kapal Nelayan">Kapal Nelayan</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Zona Penangkapan *</label>
                        <select id="editZona" name="id_dpi" class="form-select" required>
                            <option value="">Pilih Zona</option>
                            <!-- Options akan diisi via JavaScript -->
                        </select>
                    </div>

                    <!-- Informasi yang tidak bisa diedit -->
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <h4 class="text-sm font-medium text-gray-700 mb-3">Informasi Tidak Dapat Diedit</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500">Pemilik Kapal:</span>
                                <p class="font-medium text-gray-800" id="editPemilikDisplay"></p>
                            </div>
                            <div>
                                <span class="text-gray-500">Status Verifikasi:</span>
                                <p class="font-medium text-gray-800" id="editVerifikasiDisplay"></p>
                            </div>
                            <div>
                                <span class="text-gray-500">Status Kapal:</span>
                                <p class="font-medium text-gray-800" id="editStatusDisplay"></p>
                            </div>
                            <div>
                                <span class="text-gray-500">Tanggal Verifikasi:</span>
                                <p class="font-medium text-gray-800" id="editVerifiedAtDisplay"></p>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 mt-6 pt-4 border-t border-gray-200">
                        <button type="button" onclick="closeEditModal()" class="btn btn-secondary">
                            Batal
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save mr-2"></i>Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Kapal -->
    <div id="tambahModal" class="modal-overlay z-100">
        <div class="modal-content">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-2xl font-bold text-gray-800">Tambah Kapal Baru</h3>
                    <button onclick="closeTambahModal()" class="text-gray-500 hover:text-gray-700 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Step Indicator -->
                <div class="step-indicator mb-8">
                    <div class="step active">
                        1
                        <span class="step-label">Informasi</span>
                    </div>
                    <div class="step">
                        2
                        <span class="step-label">Konfirmasi</span>
                    </div>
                </div>

                <!-- Form Steps -->
                <form id="tambahForm">
                    <!-- Step 1: Informasi Kapal -->
                    <div id="step1" class="form-step active">
                        <div class="space-y-6">
                            <div class="form-group">
                                <label class="form-label">Nama Kapal *</label>
                                <input type="text" id="tambahNamaKapal" name="nama_kapal"
                                    class="form-input" placeholder="Masukkan nama kapal" required>
                                <p class="text-xs text-gray-500 mt-1">Contoh: KM. Bahari Jaya, KM. Nusantara, dll.</p>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Jenis Kapal *</label>
                                <select id="tambahJenisKapal" name="jenis_kapal" class="form-select" required>
                                    <option value="">Pilih Jenis Kapal</option>
                                    <option value="Kapal Penangkap Ikan">Kapal Penangkap Ikan</option>
                                    <option value="Kapal Angkut">Kapal Angkut</option>
                                    <option value="Kapal Nelayan">Kapal Nelayan</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Zona Penangkapan *</label>
                                <select id="tambahZona" name="id_dpi" class="form-select" required>
                                    <option value="">Memuat zona penangkapan...</option>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Pilih zona penangkapan ikan yang sesuai</p>
                            </div>

                            <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                                <div class="flex items-start">
                                    <i class="fas fa-info-circle text-blue-500 mt-1 mr-3"></i>
                                    <div>
                                        <p class="text-sm text-blue-700">
                                            <strong>Informasi:</strong> Kapal yang ditambahkan akan menunggu proses verifikasi oleh administrator sebelum dapat digunakan dalam sistem monitoring.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end mt-8">
                            <button type="button" onclick="showStep(2)" class="btn btn-primary">
                                Lanjutkan <i class="fas fa-arrow-right ml-2"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Step 2: Konfirmasi -->
                    <div id="step2" class="form-step">
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 mb-6">
                            <h4 class="font-medium text-gray-800 mb-4">Konfirmasi Data Kapal</h4>
                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Nama Kapal:</span>
                                    <span class="font-medium text-gray-800" id="confirmNamaKapal"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Jenis Kapal:</span>
                                    <span class="font-medium text-gray-800" id="confirmJenisKapal"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Zona Penangkapan:</span>
                                    <span class="font-medium text-gray-800" id="confirmZona"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Pemilik:</span>
                                    <span class="font-medium text-gray-800" id="confirmPemilik"></span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200 mb-6">
                            <div class="flex items-start">
                                <i class="fas fa-exclamation-triangle text-yellow-500 mt-1 mr-3"></i>
                                <div>
                                    <p class="text-sm text-yellow-700">
                                        <strong>Perhatian:</strong> Pastikan data yang dimasukkan sudah benar. Data yang sudah dikirim akan melalui proses verifikasi oleh administrator dan tidak dapat diubah selama proses verifikasi berlangsung.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-between mt-8">
                            <button type="button" onclick="showStep(1)" class="btn btn-secondary">
                                <i class="fas fa-arrow-left mr-2"></i>Kembali
                            </button>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check mr-2"></i>Simpan Kapal
                            </button>
                        </div>
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

    <script src="../js/dashboard/myKapal.js"></script>
</body>

</html>