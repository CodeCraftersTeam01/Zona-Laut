<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Tangkapan Ikan - Zona Laut</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
    <!-- Mengganti CDN Tailwind dengan file CSS lokal -->
    <link rel="stylesheet" href="../src/output.css">
    <link rel="stylesheet" href="../css/dashboard.css">
   <link rel="stylesheet" href="../css/laporan.css">
</head>

<body class="bg-gray-50 text-gray-800 min-h-screen flex">
    <!-- Mobile Menu Button -->
    <button class="md:hidden fixed top-20 right-6 z-50 w-10 h-10 bg-blue-600 text-white border-none rounded-lg flex items-center justify-center text-xl cursor-pointer no-print" id="mobileMenuBtn">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar -->
    <?php include '../components/sidebar_dashboard.php'; ?>

    <!-- Main Content -->
    <main class="main-content mt-5 relative w-full flex-1 z-10 md:ml-64 ml-0 transition-all duration-300">
        <div class="p-4 md:p-6">
            <!-- Header Section -->
            <div class="mb-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">Laporan Tangkapan Ikan</h1>
                        <p class="text-gray-600 mt-1">Analisis dan ringkasan data tangkapan ikan dari kapal Anda</p>
                    </div>
                    <div class="mt-4 md:mt-0 btn-group">
                        <button onclick="printReport()" class="btn btn-primary no-print">
                            <i class="fas fa-print mr-2"></i>Cetak Laporan
                        </button>
                        <button onclick="exportToExcel()" class="btn btn-success no-print">
                            <i class="fas fa-file-excel mr-2"></i>Export Excel
                        </button>
                        <button onclick="exportToPDF()" class="btn btn-danger no-print">
                            <i class="fas fa-file-pdf mr-2"></i>Export PDF
                        </button>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="filter-section no-print">
                <div class="flex flex-col md:flex-row md:items-end gap-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 flex-1">
                        <div class="form-group">
                            <label class="form-label">Periode Laporan</label>
                            <select id="reportPeriod" class="form-select" onchange="handlePeriodChange()">
                                <option value="today">Hari Ini</option>
                                <option value="yesterday">Kemarin</option>
                                <option value="week" selected>Minggu Ini</option>
                                <option value="lastWeek">Minggu Lalu</option>
                                <option value="month">Bulan Ini</option>
                                <option value="lastMonth">Bulan Lalu</option>
                                <option value="quarter">Kuartal Ini</option>
                                <option value="year">Tahun Ini</option>
                                <option value="custom">Periode Kustom</option>
                            </select>
                        </div>
                        
                        <!-- INI BAGIAN YANG DIPERBAIKI -->
                        <div class="form-group" id="customDateRangeGroup" style="display: none;">
                            <label class="form-label">Rentang Tanggal Kustom</label>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <input type="date" id="customStartDate" class="form-input" placeholder="Mulai">
                                </div>
                                <div>
                                    <input type="date" id="customEndDate" class="form-input" placeholder="Akhir">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Filter Kapal</label>
                            <select id="filterKapal" class="form-select">
                                <option value="">Semua Kapal</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Filter Zona</label>
                            <select id="filterZona" class="form-select">
                                <option value="">Semua Zona</option>
                            </select>
                        </div>
                    </div>
                    <div class="btn-group">
                        <button onclick="generateReport()" class="btn btn-primary">
                            <i class="fas fa-chart-bar mr-2"></i>Buat Laporan
                        </button>
                        <button onclick="resetReportFilters()" class="btn btn-secondary">
                            <i class="fas fa-redo mr-2"></i>Reset
                        </button>
                    </div>
                </div>
            </div>

            <!-- Loading Indicator -->
            <div id="loadingIndicator" class="flex flex-col justify-center items-center py-12 hidden">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mb-4"></div>
                <span class="text-gray-600">Membuat laporan...</span>
            </div>

            <!-- Report Content -->
            <div id="reportContent" class="hidden">
                <!-- Report Header -->
                <div class="report-header">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                        <div>
                            <h2 class="text-xl font-bold text-gray-800" id="reportTitle">Laporan Tangkapan Ikan</h2>
                            <p class="text-gray-600 mt-1" id="reportPeriodText">Periode: Minggu Ini</p>
                            <p class="text-gray-500 text-sm mt-1" id="reportGenerated">Dibuat pada: -</p>
                        </div>
                        <div class="mt-2 md:mt-0">
                            <img src="../images/logo.png" alt="Logo" class="h-12">
                        </div>
                    </div>
                </div>

                <!-- Executive Summary -->
                <div class="report-section">
                    <h3 class="section-title">Ringkasan Eksekutif</h3>
                    <div class="stats-grid">
                        <div class="stat-card blue">
                            <div class="icon">
                                <i class="fas fa-fish"></i>
                            </div>
                            <div class="value" id="reportTotalTangkapan">0</div>
                            <div class="label">Total Tangkapan</div>
                        </div>
                        <div class="stat-card green">
                            <div class="icon">
                                <i class="fas fa-weight-hanging"></i>
                            </div>
                            <div class="value" id="reportTotalBerat">0 kg</div>
                            <div class="label">Total Berat Ikan</div>
                        </div>
                        <div class="stat-card yellow">
                            <div class="icon">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <div class="value" id="reportTotalNilai">Rp 0</div>
                            <div class="label">Total Nilai Tangkapan</div>
                        </div>
                        <div class="stat-card purple">
                            <div class="icon">
                                <i class="fas fa-ship"></i>
                            </div>
                            <div class="value" id="reportTotalKapal">0</div>
                            <div class="label">Kapal Aktif</div>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="report-section">
                    <h3 class="section-title">Analisis Data</h3>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                        <div class="card p-4">
                            <h4 class="font-semibold text-gray-700 mb-4">Tangkapan per Jenis Ikan</h4>
                            <div class="chart-container">
                                <canvas id="ikanChart"></canvas>
                            </div>
                        </div>
                        <div class="card p-4">
                            <h4 class="font-semibold text-gray-700 mb-4">Tangkapan per Kapal</h4>
                            <div class="chart-container">
                                <canvas id="kapalChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="card p-4">
                            <h4 class="font-semibold text-gray-700 mb-4">Tangkapan per Zona</h4>
                            <div class="chart-container">
                                <canvas id="zonaChart"></canvas>
                            </div>
                        </div>
                        <div class="card p-4">
                            <h4 class="font-semibold text-gray-700 mb-4">Trend Tangkapan per Bulan</h4>
                            <div class="chart-container">
                                <canvas id="trendChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detailed Data -->
                <div class="report-section">
                    <h3 class="section-title">Data Detail Tangkapan</h3>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Nama Kapal</th>
                                    <th>Jenis Ikan</th>
                                    <th>Berat (kg)</th>
                                    <th>Harga/Kg</th>
                                    <th>Total Nilai</th>
                                    <th>Zona</th>
                                </tr>
                            </thead>
                            <tbody id="reportTableBody">
                                <!-- Data akan diisi oleh JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Summary by Category -->
                <div class="report-section">
                    <h3 class="section-title">Ringkasan per Kategori</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="card p-4">
                            <h4 class="font-semibold text-gray-700 mb-4">Per Jenis Ikan</h4>
                            <div class="space-y-3" id="ikanSummary">
                                <!-- Ringkasan per jenis ikan -->
                            </div>
                        </div>
                        <div class="card p-4">
                            <h4 class="font-semibold text-gray-700 mb-4">Per Kapal</h4>
                            <div class="space-y-3" id="kapalSummary">
                                <!-- Ringkasan per kapal -->
                            </div>
                        </div>
                        <div class="card p-4">
                            <h4 class="font-semibold text-gray-700 mb-4">Per Zona</h4>
                            <div class="space-y-3" id="zonaSummary">
                                <!-- Ringkasan per zona -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Performance Metrics -->
                <div class="report-section">
                    <h3 class="section-title">Metrik Kinerja</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="card p-4">
                            <h4 class="font-semibold text-gray-700 mb-4">Kapal dengan Produktivitas Tertinggi</h4>
                            <div id="topKapal">
                                <!-- Kapal dengan produktivitas tertinggi -->
                            </div>
                        </div>
                        <div class="card p-4">
                            <h4 class="font-semibold text-gray-700 mb-4">Jenis Ikan dengan Nilai Tertinggi</h4>
                            <div id="topIkan">
                                <!-- Jenis ikan dengan nilai tertinggi -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="mt-8 pt-4 border-t border-gray-200 text-center text-gray-500 text-sm">
                    <p>Laporan ini dibuat secara otomatis oleh Sistem Manajemen Tangkapan Ikan Zona Laut</p>
                    <p class="mt-1">© <span id="currentYear"></span> Zona Laut. Semua hak dilindungi.</p>
                </div>
            </div>

            <!-- Empty State -->
            <div id="emptyState" class="hidden text-center py-12">
                <div class="max-w-md mx-auto">
                    <div class="w-24 h-24 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-chart-bar text-gray-400 text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">Belum Ada Data Laporan</h3>
                    <p class="text-gray-500 mb-6">Pilih periode dan filter untuk membuat laporan.</p>
                    <button onclick="generateReport()" class="btn btn-primary">
                        <i class="fas fa-chart-bar mr-2"></i>Buat Laporan
                    </button>
                </div>
            </div>

            <!-- Error State -->
            <div id="errorState" class="hidden">
                <div class="bg-red-50 border border-red-200 rounded-lg p-6 text-center">
                    <div class="w-16 h-16 mx-auto mb-4 bg-red-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-red-500 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-red-800 mb-2">Gagal Membuat Laporan</h3>
                    <p class="text-red-600 mb-4" id="errorMessage">Terjadi kesalahan saat membuat laporan.</p>
                    <button onclick="generateReport()" class="btn btn-danger">
                        <i class="fas fa-redo mr-2"></i>Coba Lagi
                    </button>
                </div>
            </div>
        </div>
    </main>

    <!-- Toast Container -->
    <div class="toast-container no-print" id="toastContainer"></div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="../js/script.js"></script>
    <script src="../js/dashboard/auth.js"></script>
    <script src="../js/dashboard/sidebar.js"></script>

    <script src="../js/dashboard/laporan.js"></script>
</body>

</html>