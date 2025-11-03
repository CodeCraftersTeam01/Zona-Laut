 // Variabel global
        let currentTangkapanData = null;
        let kapalList = [];
        let dpiList = [];
        let tangkapanData = [];
        let currentAction = 'add'; // 'add' atau 'edit'

        // Fungsi untuk memuat data tangkapan
        async function loadTangkapanData() {
            const loadingIndicator = document.getElementById('loadingIndicator');
            const errorState = document.getElementById('errorState');
            const emptyState = document.getElementById('emptyState');

            try {
                // Tampilkan loading, sembunyikan yang lain
                loadingIndicator.classList.remove('hidden');
                errorState.classList.add('hidden');
                emptyState.classList.add('hidden');

                // Fetch data dari API
                const user = JSON.parse(localStorage.getItem('currentUser'));
                const response = await fetch('../../backend/system/tangkapan.php?id_pemilik=' + user.id);
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

                    tangkapanData = processedData;

                    // Tampilkan data yang sudah difilter
                    if (processedData.length > 0) {
                        displayTangkapanData(processedData);
                        updateStats(processedData);
                    } else {
                        emptyState.classList.remove('hidden');
                        updateStats([]);
                    }
                } else {
                    // Tampilkan empty state
                    emptyState.classList.remove('hidden');
                    updateStats([]);
                }
            } catch (error) {
                console.error('Error loading tangkapan data:', error);
                loadingIndicator.classList.add('hidden');
                errorState.classList.remove('hidden');
                document.getElementById('errorMessage').textContent = error.message || 'Terjadi kesalahan saat memuat data tangkapan.';
            }
        }

        // Fungsi untuk menampilkan data tangkapan dalam tabel
        function displayTangkapanData(data) {
            const tableBody = document.getElementById('tangkapanTableBody');
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
                    <td>${index + 1}</td>
                    <td>
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-2">
                                <i class="fas fa-ship text-blue-600 text-xs"></i>
                            </div>
                            <span>${tangkapan.nama_kapal || 'Tidak Diketahui'}</span>
                        </div>
                    </td>
                    <td>${tangkapan.nama_ikan}</td>
                    <td>${beratFormatted} kg</td>
                    <td>${hargaFormatted}</td>
                    <td class="font-semibold">${totalFormatted}</td>
                    <td>${tangkapan.nama_dpi || '-'}</td>
                    <td>${tanggalFormatted}</td>
                    <td class="action-cell">
                        <div class="flex space-x-2">
                            <button onclick="openDetailModal(${tangkapan.id})" class="text-blue-600 hover:text-blue-800 transition-colors" title="Lihat Detail">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button onclick="openEditModal(${tangkapan.id})" class="text-green-600 hover:text-green-800 transition-colors" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="deleteTangkapan(${tangkapan.id})" class="text-red-600 hover:text-red-800 transition-colors" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                `;

                tableBody.appendChild(row);
            });
        }

        // Fungsi untuk memperbarui statistik
        function updateStats(data) {
            const totalTangkapan = data.length;
            const totalBerat = data.reduce((sum, item) => sum + parseFloat(item.berat_ikan || 0), 0);
            const totalNilai = data.reduce((sum, item) => sum + parseFloat(item.total || 0), 0);
            
            // Hitung jumlah kapal unik
            const kapalUnik = [...new Set(data.map(item => item.id_kapal))].length;

            document.getElementById('totalTangkapan').textContent = totalTangkapan;
            document.getElementById('totalBerat').textContent = totalBerat.toFixed(2) + ' kg';
            document.getElementById('totalNilai').textContent = formatRupiah(totalNilai);
            document.getElementById('totalKapal').textContent = kapalUnik;
        }

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

        // Fungsi untuk memformat tanggal dan waktu
        function formatTanggalWaktu(tanggal, waktu) {
            if (!tanggal) return '-';
            
            try {
                const date = new Date(tanggal + ' ' + (waktu || ''));
                return date.toLocaleDateString('id-ID', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
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
            const bgColor = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500';
            const icon = type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle';

            toast.className = `${bgColor} text-white px-4 py-3 rounded-lg shadow-lg flex items-center animate-fadeIn`;
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

        // ============================ MODAL FUNCTIONS ============================

        // Fungsi untuk membuka modal detail
        function openDetailModal(tangkapanId) {
            const tangkapan = getTangkapanById(tangkapanId);
            if (!tangkapan) {
                showToast('Data tangkapan tidak ditemukan', 'error');
                return;
            }

            currentTangkapanData = tangkapan;

            const detailContent = document.getElementById('detailContent');
            const tanggalWaktuFormatted = formatTanggalWaktu(tangkapan.tanggal_tangkapan, tangkapan.waktu_tangkapan);

            detailContent.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-ship text-blue-600 text-lg"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Nama Kapal</p>
                            <p class="font-semibold text-gray-800">${tangkapan.nama_kapal || 'Tidak Diketahui'}</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-fish text-purple-600 text-lg"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Jenis Ikan</p>
                            <p class="font-semibold text-gray-800">${tangkapan.nama_ikan}</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-weight-hanging text-green-600 text-lg"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Berat Ikan</p>
                            <p class="font-semibold text-gray-800">${parseFloat(tangkapan.berat_ikan).toFixed(2)} kg</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-money-bill-wave text-yellow-600 text-lg"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Harga per Kg</p>
                            <p class="font-semibold text-gray-800">${formatRupiah(tangkapan.harga_perkilo)}</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-map-marker-alt text-indigo-600 text-lg"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Zona Penangkapan</p>
                            <p class="font-semibold text-gray-800">${tangkapan.nama_dpi || '-'}</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-calendar-alt text-red-600 text-lg"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Tanggal & Waktu</p>
                            <p class="font-semibold text-gray-800">${tanggalWaktuFormatted}</p>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4 p-4 bg-green-50 rounded-lg border border-green-200">
                    <h4 class="font-medium text-green-800 mb-2">Ringkasan Nilai</h4>
                    <div class="flex justify-between items-center">
                        <span class="text-green-700">Total Nilai Tangkapan:</span>
                        <span class="text-xl font-bold text-green-800">${formatRupiah(tangkapan.total)}</span>
                    </div>
                </div>
                
                <div class="mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <h4 class="font-medium text-gray-800 mb-2">Informasi Tambahan</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">ID Tangkapan:</span>
                            <span class="font-medium text-gray-800">#${tangkapan.id}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">ID Kapal:</span>
                            <span class="font-medium text-gray-800">${tangkapan.id_kapal}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">ID Pemilik:</span>
                            <span class="font-medium text-gray-800">${tangkapan.id_pemilik}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">ID DPI:</span>
                            <span class="font-medium text-gray-800">${tangkapan.id_dpi || '-'}</span>
                        </div>
                    </div>
                </div>
            `;

            // Tampilkan modal detail
            const modal = document.getElementById('detailModal');
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        // Fungsi untuk menutup modal detail
        function closeDetailModal() {
            const modal = document.getElementById('detailModal');
            modal.classList.remove('active');
            document.body.style.overflow = ''; // Restore background scroll
            currentTangkapanData = null;

            // Reset modal setelah animasi selesai
            setTimeout(() => {
                document.getElementById('detailContent').innerHTML = '';
            }, 300);
        }

        // Fungsi untuk membuka modal form (tambah/edit)
        function openFormModal(action, tangkapanId = null) {
            currentAction = action;
            
            // Set judul modal
            document.getElementById('formModalTitle').textContent = 
                action === 'add' ? 'Tambah Tangkapan Baru' : 'Edit Data Tangkapan';
            
            // Reset form
            document.getElementById('tangkapanForm').reset();
            document.getElementById('tangkapanId').value = '';
            
            // Isi dropdown kapal (hanya yang terverifikasi)
            const kapalSelect = document.getElementById('kapalSelect');
            kapalSelect.innerHTML = '<option value="">Pilih Kapal</option>';
            
            kapalList.forEach(kapal => {
                if (kapal.verified_at) { // Hanya kapal terverifikasi
                    const option = document.createElement('option');
                    option.value = kapal.id;
                    option.textContent = kapal.nama_kapal;
                    kapalSelect.appendChild(option);
                }
            });
            
            // Isi dropdown zona
            const zonaSelect = document.getElementById('zonaSelect');
            zonaSelect.innerHTML = '<option value="">Pilih Zona</option>';
            
            dpiList.forEach(dpi => {
                const option = document.createElement('option');
                option.value = dpi.id;
                option.textContent = dpi.nama_dpi;
                zonaSelect.appendChild(option);
            });
            
            // Jika edit, isi form dengan data yang ada
            if (action === 'edit' && tangkapanId) {
                const tangkapan = getTangkapanById(tangkapanId);
                if (tangkapan) {
                    currentTangkapanData = tangkapan;
                    
                    document.getElementById('tangkapanId').value = tangkapan.id;
                    document.getElementById('kapalSelect').value = tangkapan.id_kapal;
                    document.getElementById('namaIkan').value = tangkapan.nama_ikan;
                    document.getElementById('beratIkan').value = tangkapan.berat_ikan;
                    document.getElementById('hargaPerKilo').value = tangkapan.harga_perkilo;
                    document.getElementById('zonaSelect').value = tangkapan.id_dpi;
                    document.getElementById('tanggalTangkapan').value = tangkapan.tanggal_tangkapan;
                    document.getElementById('waktuTangkapan').value = tangkapan.waktu_tangkapan;
                    
                    // Update total display
                    updateTotalDisplay();
                }
            } else {
                // Set tanggal default ke hari ini untuk tambah data
                const today = new Date().toISOString().split('T')[0];
                document.getElementById('tanggalTangkapan').value = today;
                
                // Set waktu default ke waktu sekarang
                const now = new Date();
                const hours = now.getHours().toString().padStart(2, '0');
                const minutes = now.getMinutes().toString().padStart(2, '0');
                document.getElementById('waktuTangkapan').value = `${hours}:${minutes}`;
            }
            
            // Tampilkan modal
            const modal = document.getElementById('formModal');
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        // Fungsi untuk membuka modal tambah
        function openTambahModal() {
            openFormModal('add');
        }

        // Fungsi untuk membuka modal edit
        function openEditModal(tangkapanId) {
            if (tangkapanId) {
                openFormModal('edit', tangkapanId);
            } else if (currentTangkapanData) {
                // Jika dipanggil dari modal detail
                closeDetailModal();
                setTimeout(() => {
                    openFormModal('edit', currentTangkapanData.id);
                }, 300);
            }
        }

        // Fungsi untuk menutup modal form
        function closeFormModal() {
            const modal = document.getElementById('formModal');
            modal.classList.remove('active');
            document.body.style.overflow = ''; // Restore background scroll
            currentTangkapanData = null;
        }

        // Fungsi untuk mendapatkan data tangkapan berdasarkan ID
        function getTangkapanById(tangkapanId) {
            // Cari di tangkapanData
            if (tangkapanData && Array.isArray(tangkapanData)) {
                return tangkapanData.find(tangkapan => tangkapan.id == tangkapanId);
            }
            return null;
        }

        // Fungsi untuk memperbarui tampilan total
        function updateTotalDisplay() {
            const berat = parseFloat(document.getElementById('beratIkan').value) || 0;
            const harga = parseFloat(document.getElementById('hargaPerKilo').value) || 0;
            const total = berat * harga;
            
            document.getElementById('totalDisplay').textContent = formatRupiah(total);
        }

        // Event listener untuk form
        document.getElementById('tangkapanForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;

            // Tampilkan loading
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...';
            submitBtn.disabled = true;

            // Hitung total
            const berat = parseFloat(formData.get('berat_ikan'));
            const harga = parseFloat(formData.get('harga_perkilo'));
            const total = berat * harga;

            const user = JSON.parse(localStorage.getItem('currentUser'));
            
            const data = {
                action: currentAction,
                id: currentAction === 'edit' ? parseInt(formData.get('id')) : null,
                id_kapal: parseInt(formData.get('id_kapal')),
                id_pemilik: user.id,
                nama_ikan: formData.get('nama_ikan'),
                berat_ikan: berat,
                harga_perkilo: harga,
                total: total,
                id_dpi: parseInt(formData.get('id_dpi')),
                tanggal_tangkapan: formData.get('tanggal_tangkapan'),
                waktu_tangkapan: formData.get('waktu_tangkapan')
            };

            try {
                const response = await fetch('../../backend/system/tangkapan.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    showToast(
                        currentAction === 'add' 
                            ? 'Data tangkapan berhasil ditambahkan' 
                            : 'Data tangkapan berhasil diperbarui', 
                        'success'
                    );
                    closeFormModal();
                    // Refresh data tangkapan
                    loadTangkapanData();
                } else {
                    throw new Error(result.message || 'Gagal menyimpan data');
                }
            } catch (error) {
                console.error('Error saving tangkapan:', error);
                showToast('Gagal menyimpan data tangkapan: ' + error.message, 'error');
            } finally {
                // Kembalikan tombol ke state semula
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        });

        // Event listener untuk input berat dan harga (untuk update total otomatis)
        document.getElementById('beratIkan').addEventListener('input', updateTotalDisplay);
        document.getElementById('hargaPerKilo').addEventListener('input', updateTotalDisplay);

        // Fungsi untuk menghapus tangkapan
        async function deleteTangkapan(tangkapanId) {
            if (!confirm('Apakah Anda yakin ingin menghapus data tangkapan ini? Data yang sudah dihapus tidak dapat dikembalikan.')) {
                return;
            }

            try {
                const response = await fetch('../../backend/system/tangkapan.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'delete',
                        id_pemilik: JSON.parse(localStorage.getItem('currentUser')).id,
                        id: parseInt(tangkapanId)
                    })
                });

                const result = await response.json();

                if (result.success) {
                    showToast('Data tangkapan berhasil dihapus', 'success');
                    // Refresh data tangkapan
                    loadTangkapanData();
                    window.location.reload();
                } else {
                    throw new Error(result.message || 'Gagal menghapus data');
                }
            } catch (error) {
                console.error('Error deleting tangkapan:', error);
                showToast('Gagal menghapus data tangkapan: ' + error.message, 'error');
            }
        }

        // Fungsi untuk menerapkan filter
        function applyFilters() {
            const kapalFilter = document.getElementById('filterKapal').value;
            const tanggalMulai = document.getElementById('filterTanggalMulai').value;
            const tanggalAkhir = document.getElementById('filterTanggalAkhir').value;

            let filteredData = [...tangkapanData];

            // Filter berdasarkan kapal
            if (kapalFilter) {
                filteredData = filteredData.filter(tangkapan => tangkapan.id_kapal == kapalFilter);
            }

            // Filter berdasarkan tanggal
            if (tanggalMulai) {
                filteredData = filteredData.filter(tangkapan => 
                    tangkapan.tanggal_tangkapan >= tanggalMulai
                );
            }

            if (tanggalAkhir) {
                filteredData = filteredData.filter(tangkapan => 
                    tangkapan.tanggal_tangkapan <= tanggalAkhir
                );
            }

            // Tampilkan data yang sudah difilter
            if (filteredData.length > 0) {
                displayTangkapanData(filteredData);
                updateStats(filteredData);
                document.getElementById('emptyState').classList.add('hidden');
            } else {
                document.getElementById('tangkapanTableBody').innerHTML = '';
                document.getElementById('emptyState').classList.remove('hidden');
                updateStats([]);
            }
        }

        // Fungsi untuk mereset filter
        function resetFilters() {
            document.getElementById('filterKapal').value = '';
            document.getElementById('filterTanggalMulai').value = '';
            document.getElementById('filterTanggalAkhir').value = '';
            
            // Tampilkan semua data
            if (tangkapanData.length > 0) {
                displayTangkapanData(tangkapanData);
                updateStats(tangkapanData);
                document.getElementById('emptyState').classList.add('hidden');
            } else {
                document.getElementById('emptyState').classList.remove('hidden');
            }
        }

        // Event listener untuk menutup modal ketika klik di luar konten
        document.addEventListener('click', function(e) {
            // Modal detail
            const detailModal = document.getElementById('detailModal');
            if (e.target === detailModal) {
                closeDetailModal();
            }

            // Modal form
            const formModal = document.getElementById('formModal');
            if (e.target === formModal) {
                closeFormModal();
            }
        });

        // Event listener untuk tombol escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeDetailModal();
                closeFormModal();
            }
        });

        // Muat data saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            loadTangkapanData();
            loadKapalList();
            loadDPIList();
            checkAuth();
        });