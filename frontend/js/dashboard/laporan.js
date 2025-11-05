// Variabel global
        let currentReportData = [];
        let kapalList = [];
        let dpiList = [];
        let charts = {};

        // Fungsi untuk memuat daftar kapal
        async function loadKapalList() {
            try {
                const user = JSON.parse(localStorage.getItem('currentUser'));
                const response = await fetch('../../backend/system/kapal.php?id=' + user.id);
                const data = await response.json();

                if (data.success) {
                    kapalList = data.data || [];
                    
                    // Update filter dropdown
                    const filterSelect = document.getElementById('filterKapal');
                    filterSelect.innerHTML = '<option value="">Semua Kapal</option>';
                    
                    kapalList.forEach(kapal => {
                        if (kapal.verified_at) { // Hanya tampilkan kapal terverifikasi
                            const option = document.createElement('option');
                            option.value = kapal.id;
                            option.textContent = kapal.nama_kapal;
                            filterSelect.appendChild(option);
                        }
                    });
                }
            } catch (error) {
                console.error('Error loading kapal list:', error);
            }
        }

        // Fungsi untuk memuat daftar DPI (Zona)
        async function loadDPIList() {
            try {
                const response = await fetch('../../backend/system/dpi.php');
                const data = await response.json();

                if (data.success) {
                    dpiList = data.data;
                    
                    // Update filter dropdown
                    const filterSelect = document.getElementById('filterZona');
                    filterSelect.innerHTML = '<option value="">Semua Zona</option>';
                    
                    dpiList.forEach(dpi => {
                        const option = document.createElement('option');
                        option.value = dpi.id;
                        option.textContent = dpi.nama_dpi;
                        filterSelect.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('Error loading DPI list:', error);
            }
        }

        // Fungsi untuk memformat angka sebagai Rupiah
        function formatRupiah(angka) {
            if (!angka) return 'Rp 0';
            
            const number = parseFloat(angka);
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(number);
        }

        // Fungsi untuk memformat tanggal
        function formatTanggal(tanggal) {
            if (!tanggal) return '-';
            
            try {
                const date = new Date(tanggal);
                return date.toLocaleDateString('id-ID', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                });
            } catch (error) {
                console.error('Error formatting date:', error);
                return 'Invalid Date';
            }
        }

        // Fungsi untuk menampilkan toast notification
        function showToast(message, type = 'info') {
            const toastContainer = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            const bgColor = type === 'success' ? 'toast-success' : type === 'error' ? 'toast-error' : 'toast-info';
            const icon = type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle';

            toast.className = `toast ${bgColor}`;
            toast.innerHTML = `
                <i class="fas ${icon} mr-2"></i>
                <span>${message}</span>
            `;

            toastContainer.appendChild(toast);

            // Hapus toast setelah 5 detik
            setTimeout(() => {
                toast.remove();
            }, 5000);
        }

        // Fungsi untuk mendapatkan rentang tanggal berdasarkan periode
        function getDateRange(period) {
            const now = new Date();
            let startDate, endDate;
            
            switch(period) {
                case 'today':
                    startDate = new Date(now);
                    endDate = new Date(now);
                    break;
                case 'yesterday':
                    startDate = new Date(now);
                    startDate.setDate(now.getDate() - 1);
                    endDate = new Date(startDate);
                    break;
                case 'week':
                    startDate = new Date(now);
                    startDate.setDate(now.getDate() - now.getDay());
                    endDate = new Date(now);
                    endDate.setDate(now.getDate() + (6 - now.getDay()));
                    break;
                case 'lastWeek':
                    startDate = new Date(now);
                    startDate.setDate(now.getDate() - now.getDay() - 7);
                    endDate = new Date(startDate);
                    endDate.setDate(startDate.getDate() + 6);
                    break;
                case 'month':
                    startDate = new Date(now.getFullYear(), now.getMonth(), 1);
                    endDate = new Date(now.getFullYear(), now.getMonth() + 1, 0);
                    break;
                case 'lastMonth':
                    startDate = new Date(now.getFullYear(), now.getMonth() - 1, 1);
                    endDate = new Date(now.getFullYear(), now.getMonth(), 0);
                    break;
                case 'quarter':
                    const quarter = Math.floor(now.getMonth() / 3);
                    startDate = new Date(now.getFullYear(), quarter * 3, 1);
                    endDate = new Date(now.getFullYear(), quarter * 3 + 3, 0);
                    break;
                case 'year':
                    startDate = new Date(now.getFullYear(), 0, 1);
                    endDate = new Date(now.getFullYear(), 11, 31);
                    break;
                case 'custom':
                    // Tanggal akan diambil dari input
                    const customStart = document.getElementById('customStartDate').value;
                    const customEnd = document.getElementById('customEndDate').value;
                    
                    if (!customStart || !customEnd) {
                        showToast('Harap pilih rentang tanggal kustom', 'error');
                        return null;
                    }
                    
                    startDate = new Date(customStart);
                    endDate = new Date(customEnd);
                    break;
                default:
                    startDate = new Date(now);
                    startDate.setDate(now.getDate() - now.getDay());
                    endDate = new Date(now);
                    endDate.setDate(now.getDate() + (6 - now.getDay()));
            }
            
            // Format tanggal ke YYYY-MM-DD
            const formatDate = (date) => {
                return date.toISOString().split('T')[0];
            };
            
            return {
                start: formatDate(startDate),
                end: formatDate(endDate)
            };
        }

        // FUNGSI YANG DIPERBAIKI: Menangani perubahan periode
        function handlePeriodChange() {
            const period = document.getElementById('reportPeriod').value;
            const customDateRangeGroup = document.getElementById('customDateRangeGroup');
            
            if (period === 'custom') {
                customDateRangeGroup.style.display = 'block';
            } else {
                customDateRangeGroup.style.display = 'none';
            }
        }

        // Fungsi untuk menghasilkan laporan
        async function generateReport() {
            const loadingIndicator = document.getElementById('loadingIndicator');
            const reportContent = document.getElementById('reportContent');
            const emptyState = document.getElementById('emptyState');
            const errorState = document.getElementById('errorState');

            try {
                // Tampilkan loading, sembunyikan yang lain
                loadingIndicator.classList.remove('hidden');
                reportContent.classList.add('hidden');
                emptyState.classList.add('hidden');
                errorState.classList.add('hidden');

                // Dapatkan parameter filter
                const period = document.getElementById('reportPeriod').value;
                const kapalFilter = document.getElementById('filterKapal').value;
                const zonaFilter = document.getElementById('filterZona').value;
                
                // Dapatkan rentang tanggal
                const dateRange = getDateRange(period);
                if (!dateRange) return;

                // Fetch data dari API
                const user = JSON.parse(localStorage.getItem('currentUser'));
                let url = `../../backend/system/tangkapan.php?id_pemilik=${user.id}&start_date=${dateRange.start}&end_date=${dateRange.end}`;
                
                if (kapalFilter) {
                    url += `&id_kapal=${kapalFilter}`;
                }
                
                if (zonaFilter) {
                    url += `&id_dpi=${zonaFilter}`;
                }

                const response = await fetch(url);
                const result = await response.json();

                // Sembunyikan loading
                loadingIndicator.classList.add('hidden');

                if (result.success && result.data) {
                    let processedData = [];

                    // Jika data adalah array
                    if (Array.isArray(result.data)) {
                        processedData = result.data;
                    }
                    // Jika data adalah object single (bukan array)
                    else if (typeof result.data === 'object' && result.data !== null) {
                        processedData = [result.data];
                    }

                    currentReportData = processedData;

                    // Tampilkan laporan
                    if (processedData.length > 0) {
                        displayReport(processedData, dateRange, period);
                        reportContent.classList.remove('hidden');
                    } else {
                        emptyState.classList.remove('hidden');
                    }
                } else {
                    // Tampilkan empty state
                    emptyState.classList.remove('hidden');
                }
            } catch (error) {
                console.error('Error generating report:', error);
                loadingIndicator.classList.add('hidden');
                errorState.classList.remove('hidden');
                document.getElementById('errorMessage').textContent = error.message || 'Terjadi kesalahan saat membuat laporan.';
            }
        }

        // Fungsi untuk menampilkan laporan
        function displayReport(data, dateRange, period) {
            // Update header laporan
            document.getElementById('reportPeriodText').textContent = `Periode: ${formatPeriodText(period, dateRange)}`;
            document.getElementById('reportGenerated').textContent = `Dibuat pada: ${new Date().toLocaleDateString('id-ID', { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            })}`;

            // Update statistik
            updateReportStats(data);
            
            // Tampilkan data detail
            displayReportTable(data);
            
            // Buat chart
            createCharts(data);
            
            // Tampilkan ringkasan per kategori
            displayCategorySummaries(data);
            
            // Tampilkan metrik kinerja
            displayPerformanceMetrics(data);
        }

        // Fungsi untuk memformat teks periode
        function formatPeriodText(period, dateRange) {
            const formatDate = (dateStr) => {
                const date = new Date(dateStr);
                return date.toLocaleDateString('id-ID', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
            };
            
            switch(period) {
                case 'today':
                    return `Hari Ini (${formatDate(dateRange.start)})`;
                case 'yesterday':
                    return `Kemarin (${formatDate(dateRange.start)})`;
                case 'week':
                    return `Minggu Ini (${formatDate(dateRange.start)} - ${formatDate(dateRange.end)})`;
                case 'lastWeek':
                    return `Minggu Lalu (${formatDate(dateRange.start)} - ${formatDate(dateRange.end)})`;
                case 'month':
                    return `Bulan Ini (${new Date(dateRange.start).toLocaleDateString('id-ID', { month: 'long', year: 'numeric' })})`;
                case 'lastMonth':
                    return `Bulan Lalu (${new Date(dateRange.start).toLocaleDateString('id-ID', { month: 'long', year: 'numeric' })})`;
                case 'quarter':
                    return `Kuartal Ini (${formatDate(dateRange.start)} - ${formatDate(dateRange.end)})`;
                case 'year':
                    return `Tahun Ini (${new Date(dateRange.start).getFullYear()})`;
                case 'custom':
                    return `${formatDate(dateRange.start)} - ${formatDate(dateRange.end)}`;
                default:
                    return `${formatDate(dateRange.start)} - ${formatDate(dateRange.end)}`;
            }
        }

        // Fungsi untuk memperbarui statistik laporan
        function updateReportStats(data) {
            const totalTangkapan = data.length;
            const totalBerat = data.reduce((sum, item) => sum + parseFloat(item.berat_ikan || 0), 0);
            const totalNilai = data.reduce((sum, item) => sum + parseFloat(item.total || 0), 0);
            
            // Hitung jumlah kapal unik
            const kapalUnik = [...new Set(data.map(item => item.id_kapal))].length;

            document.getElementById('reportTotalTangkapan').textContent = totalTangkapan.toLocaleString();
            document.getElementById('reportTotalBerat').textContent = totalBerat.toFixed(2) + ' kg';
            document.getElementById('reportTotalNilai').textContent = formatRupiah(totalNilai);
            document.getElementById('reportTotalKapal').textContent = kapalUnik.toLocaleString();
        }

        // Fungsi untuk menampilkan tabel data laporan
        function displayReportTable(data) {
            const tableBody = document.getElementById('reportTableBody');
            tableBody.innerHTML = '';

            data.forEach((tangkapan, index) => {
                const row = document.createElement('tr');
                row.className = 'animate-slideInUp';
                row.style.animationDelay = `${index * 0.05}s`;

                // Format nilai
                const beratFormatted = parseFloat(tangkapan.berat_ikan).toFixed(2);
                const hargaFormatted = formatRupiah(tangkapan.harga_perkilo);
                const totalFormatted = formatRupiah(tangkapan.total);
                const tanggalFormatted = formatTanggal(tangkapan.tanggal_tangkapan);

                row.innerHTML = `
                    <td class="font-medium">${index + 1}</td>
                    <td>${tanggalFormatted}</td>
                    <td>${tangkapan.nama_kapal || 'Tidak Diketahui'}</td>
                    <td>${tangkapan.nama_ikan}</td>
                    <td>${beratFormatted}</td>
                    <td>${hargaFormatted}</td>
                    <td class="font-semibold">${totalFormatted}</td>
                    <td>${tangkapan.nama_dpi || '-'}</td>
                `;

                tableBody.appendChild(row);
            });
        }

        // Fungsi untuk membuat chart
        function createCharts(data) {
            // Hancurkan chart yang ada
            Object.values(charts).forEach(chart => {
                if (chart) chart.destroy();
            });
            
            charts = {};

            // Data untuk chart
            const ikanData = aggregateByCategory(data, 'nama_ikan');
            const kapalData = aggregateByCategory(data, 'nama_kapal');
            const zonaData = aggregateByCategory(data, 'nama_dpi');
            const trendData = aggregateByMonth(data);

            // Chart untuk jenis ikan
            const ikanCtx = document.getElementById('ikanChart').getContext('2d');
            charts.ikan = new Chart(ikanCtx, {
                type: 'doughnut',
                data: {
                    labels: ikanData.labels,
                    datasets: [{
                        data: ikanData.values,
                        backgroundColor: [
                            '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6',
                            '#06b6d4', '#84cc16', '#f97316', '#6366f1', '#ec4899'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                font: {
                                    size: 11
                                }
                            }
                        }
                    }
                }
            });

            // Chart untuk kapal
            const kapalCtx = document.getElementById('kapalChart').getContext('2d');
            charts.kapal = new Chart(kapalCtx, {
                type: 'bar',
                data: {
                    labels: kapalData.labels,
                    datasets: [{
                        label: 'Total Berat (kg)',
                        data: kapalData.values,
                        backgroundColor: '#3b82f6',
                        borderColor: '#2563eb',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });

            // Chart untuk zona
            const zonaCtx = document.getElementById('zonaChart').getContext('2d');
            charts.zona = new Chart(zonaCtx, {
                type: 'pie',
                data: {
                    labels: zonaData.labels,
                    datasets: [{
                        data: zonaData.values,
                        backgroundColor: [
                            '#10b981', '#f59e0b', '#3b82f6', '#ef4444', '#8b5cf6',
                            '#06b6d4', '#84cc16', '#f97316', '#6366f1', '#ec4899'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                font: {
                                    size: 11
                                }
                            }
                        }
                    }
                }
            });

            // Chart untuk trend bulanan
            const trendCtx = document.getElementById('trendChart').getContext('2d');
            charts.trend = new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: trendData.labels,
                    datasets: [{
                        label: 'Total Berat (kg)',
                        data: trendData.values,
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderColor: '#3b82f6',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        // Fungsi untuk mengagregasi data berdasarkan kategori
        function aggregateByCategory(data, categoryField) {
            const aggregated = {};
            
            data.forEach(item => {
                const category = item[categoryField] || 'Tidak Diketahui';
                const berat = parseFloat(item.berat_ikan) || 0;
                
                if (aggregated[category]) {
                    aggregated[category] += berat;
                } else {
                    aggregated[category] = berat;
                }
            });
            
            // Urutkan berdasarkan nilai tertinggi
            const sorted = Object.entries(aggregated)
                .sort((a, b) => b[1] - a[1])
                .slice(0, 8); // Ambil 8 teratas
            
            return {
                labels: sorted.map(item => item[0]),
                values: sorted.map(item => item[1])
            };
        }

        // Fungsi untuk mengagregasi data berdasarkan bulan
        function aggregateByMonth(data) {
            const monthlyData = {};
            const monthNames = [
                'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
                'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'
            ];
            
            data.forEach(item => {
                const date = new Date(item.tanggal_tangkapan);
                const monthKey = `${date.getFullYear()}-${date.getMonth()}`;
                const berat = parseFloat(item.berat_ikan) || 0;
                
                if (monthlyData[monthKey]) {
                    monthlyData[monthKey] += berat;
                } else {
                    monthlyData[monthKey] = berat;
                }
            });
            
            // Urutkan berdasarkan bulan
            const sorted = Object.entries(monthlyData)
                .sort((a, b) => a[0].localeCompare(b[0]))
                .slice(-6); // Ambil 6 bulan terakhir
            
            return {
                labels: sorted.map(item => {
                    const [year, month] = item[0].split('-');
                    return `${monthNames[parseInt(month)]} ${year}`;
                }),
                values: sorted.map(item => item[1])
            };
        }

        // Fungsi untuk menampilkan ringkasan per kategori
        function displayCategorySummaries(data) {
            displayIkanSummary(data);
            displayKapalSummary(data);
            displayZonaSummary(data);
        }

        // Fungsi untuk menampilkan ringkasan per jenis ikan
        function displayIkanSummary(data) {
            const ikanSummary = document.getElementById('ikanSummary');
            const ikanData = {};
            
            data.forEach(item => {
                const ikan = item.nama_ikan;
                const berat = parseFloat(item.berat_ikan) || 0;
                const nilai = parseFloat(item.total) || 0;
                
                if (ikanData[ikan]) {
                    ikanData[ikan].berat += berat;
                    ikanData[ikan].nilai += nilai;
                    ikanData[ikan].count += 1;
                } else {
                    ikanData[ikan] = {
                        berat: berat,
                        nilai: nilai,
                        count: 1
                    };
                }
            });
            
            // Urutkan berdasarkan nilai tertinggi
            const sorted = Object.entries(ikanData)
                .sort((a, b) => b[1].nilai - a[1].nilai)
                .slice(0, 5); // Ambil 5 teratas
            
            ikanSummary.innerHTML = sorted.map(([ikan, data], index) => `
                <div class="summary-item">
                    <div class="flex items-center flex-1">
                        <span class="w-6 h-6 bg-blue-100 text-blue-800 rounded-full flex items-center justify-center text-xs font-bold mr-2">
                            ${index + 1}
                        </span>
                        <span class="font-medium text-sm">${ikan}</span>
                    </div>
                    <div class="text-right">
                        <div class="font-semibold text-sm">${formatRupiah(data.nilai)}</div>
                        <div class="text-xs text-gray-500">${data.berat.toFixed(2)} kg</div>
                    </div>
                </div>
            `).join('');
        }

        // Fungsi untuk menampilkan ringkasan per kapal
        function displayKapalSummary(data) {
            const kapalSummary = document.getElementById('kapalSummary');
            const kapalData = {};
            
            data.forEach(item => {
                const kapal = item.nama_kapal || 'Tidak Diketahui';
                const berat = parseFloat(item.berat_ikan) || 0;
                const nilai = parseFloat(item.total) || 0;
                
                if (kapalData[kapal]) {
                    kapalData[kapal].berat += berat;
                    kapalData[kapal].nilai += nilai;
                    kapalData[kapal].count += 1;
                } else {
                    kapalData[kapal] = {
                        berat: berat,
                        nilai: nilai,
                        count: 1
                    };
                }
            });
            
            // Urutkan berdasarkan nilai tertinggi
            const sorted = Object.entries(kapalData)
                .sort((a, b) => b[1].nilai - a[1].nilai)
                .slice(0, 5); // Ambil 5 teratas
            
            kapalSummary.innerHTML = sorted.map(([kapal, data], index) => `
                <div class="summary-item">
                    <div class="flex items-center flex-1">
                        <span class="w-6 h-6 bg-green-100 text-green-800 rounded-full flex items-center justify-center text-xs font-bold mr-2">
                            ${index + 1}
                        </span>
                        <span class="font-medium text-sm">${kapal}</span>
                    </div>
                    <div class="text-right">
                        <div class="font-semibold text-sm">${formatRupiah(data.nilai)}</div>
                        <div class="text-xs text-gray-500">${data.count} tangkapan</div>
                    </div>
                </div>
            `).join('');
        }

        // Fungsi untuk menampilkan ringkasan per zona
        function displayZonaSummary(data) {
            const zonaSummary = document.getElementById('zonaSummary');
            const zonaData = {};
            
            data.forEach(item => {
                const zona = item.nama_dpi || 'Tidak Diketahui';
                const berat = parseFloat(item.berat_ikan) || 0;
                const nilai = parseFloat(item.total) || 0;
                
                if (zonaData[zona]) {
                    zonaData[zona].berat += berat;
                    zonaData[zona].nilai += nilai;
                    zonaData[zona].count += 1;
                } else {
                    zonaData[zona] = {
                        berat: berat,
                        nilai: nilai,
                        count: 1
                    };
                }
            });
            
            // Urutkan berdasarkan nilai tertinggi
            const sorted = Object.entries(zonaData)
                .sort((a, b) => b[1].nilai - a[1].nilai)
                .slice(0, 5); // Ambil 5 teratas
            
            zonaSummary.innerHTML = sorted.map(([zona, data], index) => `
                <div class="summary-item">
                    <div class="flex items-center flex-1">
                        <span class="w-6 h-6 bg-purple-100 text-purple-800 rounded-full flex items-center justify-center text-xs font-bold mr-2">
                            ${index + 1}
                        </span>
                        <span class="font-medium text-sm">${zona}</span>
                    </div>
                    <div class="text-right">
                        <div class="font-semibold text-sm">${formatRupiah(data.nilai)}</div>
                        <div class="text-xs text-gray-500">${data.berat.toFixed(2)} kg</div>
                    </div>
                </div>
            `).join('');
        }

        // Fungsi untuk menampilkan metrik kinerja
        function displayPerformanceMetrics(data) {
            displayTopKapal(data);
            displayTopIkan(data);
        }

        // Fungsi untuk menampilkan kapal dengan produktivitas tertinggi
        function displayTopKapal(data) {
            const topKapal = document.getElementById('topKapal');
            const kapalData = {};
            
            data.forEach(item => {
                const kapal = item.nama_kapal || 'Tidak Diketahui';
                const berat = parseFloat(item.berat_ikan) || 0;
                
                if (kapalData[kapal]) {
                    kapalData[kapal] += berat;
                } else {
                    kapalData[kapal] = berat;
                }
            });
            
            // Urutkan berdasarkan berat tertinggi
            const sorted = Object.entries(kapalData)
                .sort((a, b) => b[1] - a[1])
                .slice(0, 3); // Ambil 3 teratas
            
            topKapal.innerHTML = sorted.map(([kapal, berat], index) => `
                <div class="performance-item">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center mr-3 ${
                        index === 0 ? 'bg-yellow-100 text-yellow-800' : 
                        index === 1 ? 'bg-gray-100 text-gray-800' : 
                        'bg-orange-100 text-orange-800'
                    }">
                        <i class="fas fa-trophy ${index === 0 ? 'text-yellow-500' : index === 1 ? 'text-gray-500' : 'text-orange-500'}"></i>
                    </div>
                    <div class="flex-1">
                        <div class="font-medium text-sm">${kapal}</div>
                        <div class="text-xs text-gray-500">${berat.toFixed(2)} kg</div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs font-semibold px-2 py-1 rounded-full ${
                            index === 0 ? 'bg-yellow-100 text-yellow-800' : 
                            index === 1 ? 'bg-gray-100 text-gray-800' : 
                            'bg-orange-100 text-orange-800'
                        }">
                            #${index + 1}
                        </div>
                    </div>
                </div>
            `).join('');
        }

        // Fungsi untuk menampilkan jenis ikan dengan nilai tertinggi
        function displayTopIkan(data) {
            const topIkan = document.getElementById('topIkan');
            const ikanData = {};
            
            data.forEach(item => {
                const ikan = item.nama_ikan;
                const nilai = parseFloat(item.total) || 0;
                
                if (ikanData[ikan]) {
                    ikanData[ikan] += nilai;
                } else {
                    ikanData[ikan] = nilai;
                }
            });
            
            // Urutkan berdasarkan nilai tertinggi
            const sorted = Object.entries(ikanData)
                .sort((a, b) => b[1] - a[1])
                .slice(0, 3); // Ambil 3 teratas
            
            topIkan.innerHTML = sorted.map(([ikan, nilai], index) => `
                <div class="performance-item">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center mr-3 ${
                        index === 0 ? 'bg-yellow-100 text-yellow-800' : 
                        index === 1 ? 'bg-gray-100 text-gray-800' : 
                        'bg-orange-100 text-orange-800'
                    }">
                        <i class="fas fa-fish ${index === 0 ? 'text-yellow-500' : index === 1 ? 'text-gray-500' : 'text-orange-500'}"></i>
                    </div>
                    <div class="flex-1">
                        <div class="font-medium text-sm">${ikan}</div>
                        <div class="text-xs text-gray-500">${formatRupiah(nilai)}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs font-semibold px-2 py-1 rounded-full ${
                            index === 0 ? 'bg-yellow-100 text-yellow-800' : 
                            index === 1 ? 'bg-gray-100 text-gray-800' : 
                            'bg-orange-100 text-orange-800'
                        }">
                            #${index + 1}
                        </div>
                    </div>
                </div>
            `).join('');
        }

        // Fungsi untuk mereset filter laporan
        function resetReportFilters() {
            document.getElementById('reportPeriod').value = 'week';
            document.getElementById('filterKapal').value = '';
            document.getElementById('filterZona').value = '';
            document.getElementById('customDateRangeGroup').style.display = 'none';
            document.getElementById('customStartDate').value = '';
            document.getElementById('customEndDate').value = '';
        }

        // Fungsi untuk mencetak laporan
        function printReport() {
            if (currentReportData.length === 0) {
                showToast('Tidak ada data untuk dicetak', 'error');
                return;
            }
            window.print();
        }

        // Fungsi untuk mengekspor ke Excel
        function exportToExcel() {
            if (currentReportData.length === 0) {
                showToast('Tidak ada data untuk diekspor', 'error');
                return;
            }

            try {
                // Siapkan data untuk Excel
                const dataForExcel = currentReportData.map(item => ({
                    'Tanggal': formatTanggal(item.tanggal_tangkapan),
                    'Nama Kapal': item.nama_kapal || 'Tidak Diketahui',
                    'Jenis Ikan': item.nama_ikan,
                    'Berat (kg)': parseFloat(item.berat_ikan).toFixed(2),
                    'Harga per Kg': parseFloat(item.harga_perkilo),
                    'Total Nilai': parseFloat(item.total),
                    'Zona': item.nama_dpi || '-'
                }));

                // Buat worksheet
                const ws = XLSX.utils.json_to_sheet(dataForExcel);
                
                // Buat workbook
                const wb = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(wb, ws, 'Laporan Tangkapan');
                
                // Ekspor ke file
                const period = document.getElementById('reportPeriod').value;
                const fileName = `Laporan_Tangkapan_${period}_${new Date().toISOString().split('T')[0]}.xlsx`;
                XLSX.writeFile(wb, fileName);
                
                showToast('Laporan berhasil diekspor ke Excel', 'success');
            } catch (error) {
                console.error('Error exporting to Excel:', error);
                showToast('Gagal mengekspor ke Excel', 'error');
            }
        }

        // Fungsi untuk mengekspor ke PDF
        function exportToPDF() {
            if (currentReportData.length === 0) {
                showToast('Tidak ada data untuk diekspor', 'error');
                return;
            }
            showToast('Fitur ekspor PDF akan segera tersedia', 'info');
            // Implementasi ekspor PDF bisa ditambahkan menggunakan jsPDF
        }

        // Muat data saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            loadKapalList();
            loadDPIList();
            checkAuth();
            
            // Set tahun saat ini di footer
            document.getElementById('currentYear').textContent = new Date().getFullYear();
            
            // Set tanggal default untuk periode kustom
            const today = new Date().toISOString().split('T')[0];
            const lastWeek = new Date();
            lastWeek.setDate(lastWeek.getDate() - 7);
            const lastWeekFormatted = lastWeek.toISOString().split('T')[0];
            
            document.getElementById('customStartDate').value = lastWeekFormatted;
            document.getElementById('customEndDate').value = today;
        });