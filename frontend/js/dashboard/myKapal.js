// Variabel global
let currentKapalData = null;
let dpiList = [];
let myKapalData = [];
let currentStep = 1;
let currentTab = 'verified';

// Fungsi untuk memuat data kapal
async function loadKapalData() {
    const loadingIndicator = document.getElementById('loadingIndicator');
    const errorState = document.getElementById('errorState');

    try {
        // Tampilkan loading, sembunyikan yang lain
        loadingIndicator.classList.remove('hidden');
        errorState.classList.add('hidden');

        // Clear containers
        document.getElementById('verifiedContainer').innerHTML = '';
        document.getElementById('unverifiedContainer').innerHTML = '';

        // Fetch data dari API
        const user = JSON.parse(localStorage.getItem('currentUser'));
        const response = await fetch('../../backend/system/kapal.php?id=' + user.id);
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

            myKapalData = processedData;

            // Tampilkan data yang sudah difilter
            if (processedData.length > 0) {
                displayKapalData(processedData);
            } else {
                showEmptyStates();
            }
        } else {
            // Tampilkan empty state
            showEmptyStates();
        }
    } catch (error) {
        console.error('Error loading kapal data:', error);
        loadingIndicator.classList.add('hidden');
        errorState.classList.remove('hidden');
        document.getElementById('errorMessage').textContent = error.message || 'Terjadi kesalahan saat memuat data kapal.';
    }
}

// Fungsi untuk menampilkan empty states
function showEmptyStates() {
    document.getElementById('verifiedEmptyState').classList.remove('hidden');
    document.getElementById('unverifiedEmptyState').classList.remove('hidden');
    updateTabCounts(0, 0);
}

// Fungsi untuk menampilkan data kapal berdasarkan kategori
function displayKapalData(kapalData) {
    const verifiedContainer = document.getElementById('verifiedContainer');
    const unverifiedContainer = document.getElementById('unverifiedContainer');

    // Reset containers
    verifiedContainer.innerHTML = '';
    unverifiedContainer.innerHTML = '';

    let verifiedCount = 0;
    let unverifiedCount = 0;

    kapalData.forEach(kapal => {
        const isVerified = kapal.verified_at !== null;

        if (isVerified) {
            verifiedCount++;
            createKapalCard(kapal, verifiedContainer, true);
        } else {
            unverifiedCount++;
            createKapalCard(kapal, unverifiedContainer, false);
        }
    });

    // Update tab counts
    updateTabCounts(verifiedCount, unverifiedCount);

    // Show/hide empty states
    document.getElementById('verifiedEmptyState').classList.toggle('hidden', verifiedCount > 0);
    document.getElementById('unverifiedEmptyState').classList.toggle('hidden', unverifiedCount > 0);

    // Tambahkan event listener untuk dropdown status
    attachStatusDropdownListeners();
    attachActionMenuListeners();
}

// Fungsi untuk membuat card kapal
function createKapalCard(kapal, container, isVerified) {
    const card = document.createElement('div');
    card.className = `card p-6 animate-slideInUp ${isVerified ? 'card-verified' : 'card-unverified'}`;
    card.style.animationDelay = `${container.children.length * 0.1}s`;

    // Tentukan warna status berdasarkan status kapal (1 = aktif, 0 = nonaktif)
    const statusColor = getStatusColor(kapal.status, isVerified);
    const statusText = getStatusText(kapal.status, isVerified);
    const isStatusDisabled = !isVerified;

    // Format tanggal verifikasi
    const verifiedDate = formatVerifiedDate(kapal.verified_at);
    const createdDate = formatCreatedDate(kapal.created_at);

    card.innerHTML = `
                <div class="flex items-start justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="w-14 h-14 ${isVerified ? 'bg-blue-100' : 'bg-yellow-100'} rounded-xl flex items-center justify-center">
                            <i class="fas fa-ship ${isVerified ? 'text-blue-600' : 'text-yellow-600'} text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-800">${kapal.nama_kapal || 'Nama Tidak Tersedia'}</h3>
                            <div class="flex items-center mt-1 space-x-2">
                                <div class="relative">
                                    <button class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium ${statusColor} status-dropdown-btn ${isStatusDisabled ? 'cursor-not-allowed opacity-50' : ''}" 
                                        data-kapal-id="${kapal.id}" data-current-status="${kapal.status}" 
                                        data-verified="${isVerified}" ${isStatusDisabled ? 'disabled' : ''}>
                                        ${statusText}
                                        ${!isStatusDisabled ? '<i class="fas fa-chevron-down ml-1 text-xs"></i>' : ''}
                                    </button>
                                    ${!isStatusDisabled ? `
                                    <div class="absolute left-0 mt-1 w-40 bg-white border border-gray-200 rounded-lg shadow-lg z-10 hidden status-dropdown-menu">
                                        <button class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 rounded-t-lg flex items-center ${kapal.status == 1 ? 'bg-green-50 text-green-700' : 'text-gray-700'}" data-status="1">
                                            <i class="fas fa-circle text-green-500 mr-2 text-xs"></i>
                                            Aktif
                                            ${kapal.status == 1 ? '<i class="fas fa-check ml-auto text-green-500"></i>' : ''}
                                        </button>
                                        <button class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 rounded-b-lg flex items-center ${kapal.status == 0 ? 'bg-red-50 text-red-700' : 'text-gray-700'}" data-status="0">
                                            <i class="fas fa-circle text-red-500 mr-2 text-xs"></i>
                                            Nonaktif
                                            ${kapal.status == 0 ? '<i class="fas fa-check ml-auto text-red-500"></i>' : ''}
                                        </button>
                                    </div>
                                    ` : ''}
                                </div>
                                <span class="status-badge ${isVerified ? 'status-verified' : 'status-pending'}">
                                    <i class="fas ${isVerified ? 'fa-check-circle' : 'fa-clock'} mr-1"></i>
                                    ${isVerified ? 'Terverifikasi' : 'Menunggu Verifikasi'}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="relative">
                        <button class="text-gray-400 hover:text-blue-600 transition duration-200 action-menu-btn">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <div class="absolute right-0 mt-1 w-48 bg-white border border-gray-200 rounded-lg shadow-lg z-10 hidden action-menu">
                            <button onclick="openDetailModal(${kapal.id})" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-t-lg flex items-center">
                                <i class="fas fa-eye mr-2 text-blue-500"></i>Lihat Detail
                            </button>
                            ${isVerified ? `
                            <button onclick="openEditModalFromCard(${kapal.id})" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center">
                                <i class="fas fa-edit mr-2 text-green-500"></i>Edit Data
                            </button>
                            ` : `
                            <button class="w-full text-left px-4 py-2 text-sm text-gray-500 cursor-not-allowed flex items-center" disabled>
                                <i class="fas fa-edit mr-2 text-gray-400"></i>Edit Data
                            </button>
                            `}
                            <button onclick="deleteKapal(${kapal.id})" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 rounded-b-lg flex items-center">
                                <i class="fas fa-trash mr-2"></i>Hapus Kapal
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-user text-gray-600"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Pemilik Kapal</p>
                            <p class="font-medium text-gray-800">${kapal.nama_pemilik || 'Tidak Tersedia'}</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-fish text-gray-600"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Jenis Kapal</p>
                            <p class="font-medium text-gray-800">${kapal.jenis_kapal || 'Tidak Tersedia'}</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-map-marker-alt text-gray-600"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Zona Penangkapan</p>
                            <p class="font-medium text-gray-800">${kapal.nama_dpi || 'Tidak Tersedia'}</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas ${isVerified ? 'fa-calendar-check' : 'fa-hourglass-half'} text-gray-600"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">${isVerified ? 'Terverifikasi' : 'Dibuat'}</p>
                            <p class="font-medium text-gray-800">${isVerified ? verifiedDate : createdDate}</p>
                        </div>
                    </div>
                </div>
                
                
            `;

    container.appendChild(card);
}

// Fungsi untuk update tab counts
function updateTabCounts(verifiedCount, unverifiedCount) {
    document.getElementById('verifiedCount').textContent = verifiedCount;
    document.getElementById('unverifiedCount').textContent = unverifiedCount;
}

// Fungsi untuk menampilkan tab
function showTab(tabName) {
    // Update tab active state
    document.querySelectorAll('.tab').forEach(tab => {
        tab.classList.remove('active');
    });
    document.querySelector(`.tab[onclick="showTab('${tabName}')"]`).classList.add('active');

    // Update content visibility
    document.getElementById('verifiedSection').classList.toggle('hidden', tabName !== 'verified');
    document.getElementById('unverifiedSection').classList.toggle('hidden', tabName !== 'unverified');

    currentTab = tabName;
}

// Fungsi untuk memformat tanggal dibuat
function formatCreatedDate(createdAt) {
    if (!createdAt) return 'Tidak Tersedia';

    try {
        const date = new Date(createdAt);
        return date.toLocaleDateString('id-ID', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    } catch (error) {
        console.error('Error formatting date:', error);
        return 'Tidak Valid';
    }
}

// Fungsi untuk memformat tanggal verifikasi
function formatVerifiedDate(verifiedAt) {
    if (!verifiedAt) return 'Tidak Tersedia';

    try {
        const date = new Date(verifiedAt);
        return date.toLocaleDateString('id-ID', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    } catch (error) {
        console.error('Error formatting date:', error);
        return 'Tidak Valid';
    }
}

// Fungsi untuk menambahkan event listener pada action menu
function attachActionMenuListeners() {
    // Toggle action menu
    document.querySelectorAll('.action-menu-btn').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            const menu = this.nextElementSibling;
            const allMenus = document.querySelectorAll('.action-menu');

            // Tutup semua menu lainnya
            allMenus.forEach(m => {
                if (m !== menu) m.classList.add('hidden');
            });

            // Toggle menu saat ini
            menu.classList.toggle('hidden');
        });
    });

    // Tutup menu ketika klik di luar
    document.addEventListener('click', function () {
        document.querySelectorAll('.action-menu').forEach(menu => {
            menu.classList.add('hidden');
        });
    });
}

// Fungsi untuk menambahkan event listener pada dropdown status
function attachStatusDropdownListeners() {
    // Toggle dropdown hanya untuk kapal terverifikasi
    document.querySelectorAll('.status-dropdown-btn:not([disabled])').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            const isVerified = this.getAttribute('data-verified') === 'true';

            if (!isVerified) return; // Hanya untuk kapal terverifikasi

            const dropdown = this.nextElementSibling;
            const allDropdowns = document.querySelectorAll('.status-dropdown-menu');

            // Tutup semua dropdown lainnya
            allDropdowns.forEach(d => {
                if (d !== dropdown) d.classList.add('hidden');
            });

            // Toggle dropdown saat ini
            dropdown.classList.toggle('hidden');
        });
    });

    // Pilih status baru hanya untuk kapal terverifikasi
    document.querySelectorAll('.status-dropdown-menu button').forEach(option => {
        option.addEventListener('click', function () {
            const dropdown = this.closest('.status-dropdown-menu');
            const btn = dropdown.previousElementSibling;
            const isVerified = btn.getAttribute('data-verified') === 'true';

            if (!isVerified) return; // Hanya untuk kapal terverifikasi

            const newStatus = this.getAttribute('data-status');
            const kapalId = btn.getAttribute('data-kapal-id');

            updateKapalStatus(kapalId, parseInt(newStatus), btn);
            dropdown.classList.add('hidden');
        });
    });

    // Tutup dropdown ketika klik di luar
    document.addEventListener('click', function () {
        document.querySelectorAll('.status-dropdown-menu').forEach(dropdown => {
            dropdown.classList.add('hidden');
        });
    });
}

// Fungsi untuk update status kapal
async function updateKapalStatus(kapalId, newStatus, statusButton) {
    try {
        // Tampilkan loading state
        const originalText = statusButton.innerHTML;
        statusButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Memperbarui...';
        statusButton.disabled = true;

        const response = await fetch('../../backend/system/kapal.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'update_status',
                id: parseInt(kapalId),
                status: newStatus
            })
        });

        const result = await response.json();

        if (result.success) {
            // Update tampilan status
            const isVerified = statusButton.getAttribute('data-verified') === 'true';
            const statusColor = getStatusColor(newStatus, isVerified);
            const statusText = getStatusText(newStatus, isVerified);

            statusButton.className = `inline-flex items-center px-3 py-1 rounded-full text-sm font-medium ${statusColor} status-dropdown-btn`;
            statusButton.setAttribute('data-current-status', newStatus);
            statusButton.innerHTML = `${statusText} <i class="fas fa-chevron-down ml-1 text-xs"></i>`;

            // Update dropdown options
            const dropdown = statusButton.nextElementSibling;
            if (dropdown) {
                dropdown.querySelectorAll('button').forEach(option => {
                    const optionStatus = parseInt(option.getAttribute('data-status'));
                    if (optionStatus === newStatus) {
                        option.className = `w-full text-left px-4 py-2 text-sm hover:bg-gray-50 rounded-t-lg flex items-center ${newStatus == 1 ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700'}`;
                        option.innerHTML = `
                                    <i class="fas fa-circle ${newStatus == 1 ? 'text-green-500' : 'text-red-500'} mr-2 text-xs"></i>
                                    ${newStatus == 1 ? 'Aktif' : 'Nonaktif'}
                                    <i class="fas fa-check ml-auto ${newStatus == 1 ? 'text-green-500' : 'text-red-500'}"></i>
                                `;
                    } else {
                        option.className = 'w-full text-left px-4 py-2 text-sm hover:bg-gray-50 rounded-b-lg flex items-center text-gray-700';
                        option.innerHTML = `
                                    <i class="fas fa-circle ${optionStatus == 1 ? 'text-green-500' : 'text-red-500'} mr-2 text-xs"></i>
                                    ${optionStatus == 1 ? 'Aktif' : 'Nonaktif'}
                                `;
                    }
                });
            }

            // Tampilkan toast sukses
            showToast('Status kapal berhasil diperbarui', 'success');
        } else {
            throw new Error(result.message || 'Gagal memperbarui status');
        }
    } catch (error) {
        console.error('Error updating status:', error);
        showToast('Gagal memperbarui status: ' + error.message, 'error');

        // Kembalikan ke state semula
        const currentStatus = statusButton.getAttribute('data-current-status');
        const isVerified = statusButton.getAttribute('data-verified') === 'true';
        const statusColor = getStatusColor(currentStatus, isVerified);
        const statusText = getStatusText(currentStatus, isVerified);
        statusButton.className = `inline-flex items-center px-3 py-1 rounded-full text-sm font-medium ${statusColor} status-dropdown-btn`;
        statusButton.innerHTML = `${statusText} <i class="fas fa-chevron-down ml-1 text-xs"></i>`;
    } finally {
        statusButton.disabled = false;
    }
}

// Fungsi untuk menentukan warna status berdasarkan status dan verifikasi
function getStatusColor(status, isVerified) {
    if (!isVerified) {
        return 'bg-gray-100 text-gray-800'; // Nonaktif permanen untuk kapal tidak terverifikasi
    }
    return status == 1 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
}

// Fungsi untuk menentukan teks status
function getStatusText(status, isVerified) {
    if (!isVerified) {
        return 'Nonaktif'; // Selalu nonaktif untuk kapal tidak terverifikasi
    }
    return status == 1 ? 'Aktif' : 'Nonaktif';
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

// Fungsi untuk membuka modal detail
function openDetailModal(kapalId) {
    const kapal = getKapalById(kapalId);
    if (!kapal) {
        showToast('Data kapal tidak ditemukan', 'error');
        return;
    }

    currentKapalData = kapal;

    const detailContent = document.getElementById('detailContent');
    const verifiedDate = formatVerifiedDate(kapal.verified_at);
    const createdDate = formatCreatedDate(kapal.created_at);
    const isVerified = kapal.verified_at !== null;
    const statusText = isVerified ? (kapal.status == 1 ? 'Aktif' : 'Nonaktif') : 'Nonaktif';
    const statusColor = isVerified ? (kapal.status == 1 ? 'text-green-600' : 'text-red-600') : 'text-gray-600';

    // Show/hide edit button based on verification status
    const editButton = document.getElementById('editFromDetailBtn');
    if (isVerified) {
        editButton.classList.remove('hidden');
    } else {
        editButton.classList.add('hidden');
    }

    detailContent.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-ship text-blue-600 text-lg"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Nama Kapal</p>
                            <p class="font-semibold text-gray-800">${kapal.nama_kapal}</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 ${isVerified ? 'bg-green-100' : 'bg-yellow-100'} rounded-lg flex items-center justify-center">
                            <i class="fas ${isVerified ? 'fa-check-circle text-green-600' : 'fa-clock text-yellow-600'} text-lg"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Status Verifikasi</p>
                            <p class="font-semibold ${isVerified ? 'text-green-600' : 'text-yellow-600'}">${isVerified ? 'Terverifikasi' : 'Menunggu Verifikasi'}</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-fish text-purple-600 text-lg"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Jenis Kapal</p>
                            <p class="font-semibold text-gray-800">${kapal.jenis_kapal}</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-user text-yellow-600 text-lg"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Pemilik</p>
                            <p class="font-semibold text-gray-800">${kapal.nama_pemilik}</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-map-marker-alt text-indigo-600 text-lg"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Zona Penangkapan</p>
                            <p class="font-semibold text-gray-800">${kapal.nama_dpi || 'Tidak ada'}</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 ${isVerified ? (kapal.status == 1 ? 'bg-green-100' : 'bg-red-100') : 'bg-gray-100'} rounded-lg flex items-center justify-center">
                            <i class="fas ${isVerified ? (kapal.status == 1 ? 'fa-play-circle text-green-600' : 'fa-pause-circle text-red-600') : 'fa-ban text-gray-600'} text-lg"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Status Kapal</p>
                            <p class="font-semibold ${statusColor}">${statusText}</p>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4 p-4 ${isVerified ? 'bg-green-50 border-green-200' : 'bg-yellow-50 border-yellow-200'} rounded-lg border">
                    <h4 class="font-medium ${isVerified ? 'text-green-800' : 'text-yellow-800'} mb-2">Informasi Verifikasi</h4>
                    <p class="text-sm ${isVerified ? 'text-green-700' : 'text-yellow-700'}">
                        ${isVerified ?
            `Kapal ini telah terverifikasi pada <span class="font-semibold">${verifiedDate}</span>` :
            `Kapal ini sedang menunggu proses verifikasi administrator. Dibuat pada <span class="font-semibold">${createdDate}</span>`
        }
                    </p>
                </div>
                
                <div class="mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <h4 class="font-medium text-gray-800 mb-2">Informasi Tambahan</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">ID Kapal:</span>
                            <span class="font-medium text-gray-800">#${kapal.id}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">ID Pemilik:</span>
                            <span class="font-medium text-gray-800">${kapal.id_pemilik}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">ID DPI:</span>
                            <span class="font-medium text-gray-800">${kapal.id_dpi || '-'}</span>
                        </div>
                        ${!isVerified ? `
                        <div class="flex justify-between">
                            <span class="text-gray-600">Status Edit:</span>
                            <span class="font-medium text-gray-800">Tidak tersedia</span>
                        </div>
                        ` : ''}
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
    currentKapalData = null;

    // Reset modal setelah animasi selesai
    setTimeout(() => {
        document.getElementById('detailContent').innerHTML = '';
    }, 300);
}

// Fungsi untuk membuka modal edit dari modal detail
function openEditModal() {
    if (!currentKapalData) return;

    closeDetailModal();

    // Tunggu sebentar sebelum membuka modal edit
    setTimeout(() => {
        // Isi form dengan data saat ini
        document.getElementById('editKapalId').value = currentKapalData.id;
        document.getElementById('editNamaKapal').value = currentKapalData.nama_kapal;
        document.getElementById('editJenisKapal').value = currentKapalData.jenis_kapal;

        // Isi dropdown zona
        const zonaSelect = document.getElementById('editZona');
        zonaSelect.innerHTML = '<option value="">Pilih Zona</option>';

        dpiList.forEach(dpi => {
            const option = document.createElement('option');
            option.value = dpi.id;
            option.textContent = dpi.nama_dpi;
            option.selected = dpi.id == currentKapalData.id_dpi;
            zonaSelect.appendChild(option);
        });

        // Tampilkan informasi yang tidak bisa diedit
        document.getElementById('editPemilikDisplay').textContent = currentKapalData.nama_pemilik;
        document.getElementById('editVerifikasiDisplay').textContent = currentKapalData.verified_at ? 'Terverifikasi' : 'Belum Terverifikasi';
        document.getElementById('editStatusDisplay').textContent = currentKapalData.status == 1 ? 'Aktif' : 'Nonaktif';
        document.getElementById('editVerifiedAtDisplay').textContent = formatVerifiedDate(currentKapalData.verified_at);

        // Tampilkan modal edit
        const modal = document.getElementById('editModal');
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }, 300);
}

// Fungsi untuk membuka modal edit langsung dari card
function openEditModalFromCard(kapalId) {
    const kapal = getKapalById(kapalId);
    if (!kapal) {
        showToast('Data kapal tidak ditemukan', 'error');
        return;
    }

    currentKapalData = kapal;

    // Isi form dengan data saat ini
    document.getElementById('editKapalId').value = currentKapalData.id;
    document.getElementById('editNamaKapal').value = currentKapalData.nama_kapal;
    document.getElementById('editJenisKapal').value = currentKapalData.jenis_kapal;

    // Isi dropdown zona
    const zonaSelect = document.getElementById('editZona');
    zonaSelect.innerHTML = '<option value="">Pilih Zona</option>';

    dpiList.forEach(dpi => {
        const option = document.createElement('option');
        option.value = dpi.id;
        option.textContent = dpi.nama_dpi;
        option.selected = dpi.id == currentKapalData.id_dpi;
        zonaSelect.appendChild(option);
    });

    // Tampilkan informasi yang tidak bisa diedit
    document.getElementById('editPemilikDisplay').textContent = currentKapalData.nama_pemilik;
    document.getElementById('editVerifikasiDisplay').textContent = currentKapalData.verified_at ? 'Terverifikasi' : 'Belum Terverifikasi';
    document.getElementById('editStatusDisplay').textContent = currentKapalData.status == 1 ? 'Aktif' : 'Nonaktif';
    document.getElementById('editVerifiedAtDisplay').textContent = formatVerifiedDate(currentKapalData.verified_at);

    // Tampilkan modal edit
    const modal = document.getElementById('editModal');
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

// Fungsi untuk menutup modal edit
function closeEditModal() {
    const modal = document.getElementById('editModal');
    modal.classList.remove('active');
    document.body.style.overflow = ''; // Restore background scroll
    currentKapalData = null;
}

// Fungsi untuk membuka modal tambah
function openTambahModal() {
    // Reset form dan step
    document.getElementById('tambahForm').reset();
    showStep(1);

    // Isi dropdown zona
    const zonaSelect = document.getElementById('tambahZona');
    zonaSelect.innerHTML = '<option value="">Memuat zona penangkapan...</option>';

    // Load DPI list jika belum dimuat
    if (dpiList.length === 0) {
        loadDPIList().then(() => {
            populateTambahZonaSelect();
        });
    } else {
        populateTambahZonaSelect();
    }

    // Tampilkan modal tambah
    const modal = document.getElementById('tambahModal');
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

// Fungsi untuk mengisi dropdown zona di modal tambah
function populateTambahZonaSelect() {
    const zonaSelect = document.getElementById('tambahZona');
    zonaSelect.innerHTML = '<option value="">Pilih Zona</option>';

    dpiList.forEach(dpi => {
        const option = document.createElement('option');
        option.value = dpi.id;
        option.textContent = dpi.nama_dpi;
        zonaSelect.appendChild(option);
    });
}

// Fungsi untuk menutup modal tambah
function closeTambahModal() {
    const modal = document.getElementById('tambahModal');
    modal.classList.remove('active');
    document.body.style.overflow = ''; // Restore background scroll
    currentStep = 1;
}

// Fungsi untuk menampilkan step tertentu dalam form tambah
function showStep(stepNumber) {
    // Sembunyikan semua step
    document.querySelectorAll('.form-step').forEach(step => {
        step.classList.remove('active');
    });

    // Update step indicator
    document.querySelectorAll('.step').forEach((step, index) => {
        step.classList.remove('active', 'completed');
        if (index + 1 < stepNumber) {
            step.classList.add('completed');
        } else if (index + 1 === stepNumber) {
            step.classList.add('active');
        }
    });

    // Tampilkan step yang diminta
    document.getElementById(`step${stepNumber}`).classList.add('active');
    currentStep = stepNumber;

    // Jika step 2, isi data konfirmasi
    if (stepNumber === 2) {
        fillConfirmationData();
    }
}

// Fungsi untuk mengisi data konfirmasi
function fillConfirmationData() {
    const namaKapal = document.getElementById('tambahNamaKapal').value;
    const jenisKapal = document.getElementById('tambahJenisKapal').value;
    const zonaSelect = document.getElementById('tambahZona');
    const zonaText = zonaSelect.options[zonaSelect.selectedIndex].text;
    const user = JSON.parse(localStorage.getItem('currentUser'));

    document.getElementById('confirmNamaKapal').textContent = namaKapal || '-';
    document.getElementById('confirmJenisKapal').textContent = jenisKapal || '-';
    document.getElementById('confirmZona').textContent = zonaText || '-';
    document.getElementById('confirmPemilik').textContent = user.nama || '-';
}

// Fungsi untuk mendapatkan data kapal berdasarkan ID
function getKapalById(kapalId) {
    // Cari di myKapalData (data yang sudah difilter)
    if (myKapalData && Array.isArray(myKapalData)) {
        return myKapalData.find(kapal => kapal.id == kapalId);
    }
    return null;
}

// Event listener untuk form edit
document.getElementById('editForm').addEventListener('submit', async function (e) {
    e.preventDefault();

    if (!currentKapalData) {
        showToast('Data kapal tidak valid', 'error');
        return;
    }

    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;

    // Tampilkan loading
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...';
    submitBtn.disabled = true;

    const data = {
        action: 'edit',
        id: parseInt(formData.get('id')),
        nama_kapal: formData.get('nama_kapal'),
        jenis_kapal: formData.get('jenis_kapal'),
        id_dpi: parseInt(formData.get('id_dpi')),
        // Field berikut tidak boleh diubah, gunakan nilai asli
        id_pemilik: currentKapalData.id_pemilik,
        verification: currentKapalData.verification || 0,
        status: currentKapalData.status
    };

    try {
        const response = await fetch('../../backend/system/kapal.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            showToast('Data kapal berhasil diperbarui', 'success');
            closeEditModal();
            // Refresh data kapal
            loadKapalData();
        } else {
            throw new Error(result.message || 'Gagal memperbarui data');
        }
    } catch (error) {
        console.error('Error updating kapal:', error);
        showToast('Gagal memperbarui data kapal: ' + error.message, 'error');
    } finally {
        // Kembalikan tombol ke state semula
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
});

// Event listener untuk form tambah
document.getElementById('tambahForm').addEventListener('submit', async function (e) {
    e.preventDefault();

    const user = JSON.parse(localStorage.getItem('currentUser'));
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;

    // Tampilkan loading
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...';
    submitBtn.disabled = true;

    const data = {
        action: 'add',
        nama_kapal: formData.get('nama_kapal'),
        jenis_kapal: formData.get('jenis_kapal'),
        id_dpi: parseInt(formData.get('id_dpi')),
        id_pemilik: user.id, // Gunakan ID user yang login
        verification: 0, // Default belum terverifikasi
        status: 0 // Default aktif
    };

    try {
        const response = await fetch('../../backend/system/kapal.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            showToast('Kapal berhasil ditambahkan. Menunggu verifikasi administrator.', 'success');
            closeTambahModal();
            // Refresh data kapal
            loadKapalData();
        } else {
            throw new Error(result.message || 'Gagal menambahkan data');
        }
    } catch (error) {
        console.error('Error adding kapal:', error);
        showToast('Gagal menambahkan kapal: ' + error.message, 'error');
    } finally {
        // Kembalikan tombol ke state semula
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
});

// Fungsi untuk menghapus kapal
async function deleteKapal(kapalId) {
    if (!confirm('Apakah Anda yakin ingin menghapus kapal ini? Data yang sudah dihapus tidak dapat dikembalikan.')) {
        return;
    }

    try {
        const response = await fetch('../../backend/system/kapal.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'delete',
                id: parseInt(kapalId)
            })
        });

        const result = await response.json();

        if (result.success) {
            showToast('Kapal berhasil dihapus', 'success');
            // Refresh data kapal
            loadKapalData();
        } else {
            throw new Error(result.message || 'Gagal menghapus data');
        }
    } catch (error) {
        console.error('Error deleting kapal:', error);
        showToast('Gagal menghapus kapal: ' + error.message, 'error');
    }
}

// Event listener untuk menutup modal ketika klik di luar konten
document.addEventListener('click', function (e) {
    // Modal detail
    const detailModal = document.getElementById('detailModal');
    if (e.target === detailModal) {
        closeDetailModal();
    }

    // Modal edit
    const editModal = document.getElementById('editModal');
    if (e.target === editModal) {
        closeEditModal();
    }

    // Modal tambah
    const tambahModal = document.getElementById('tambahModal');
    if (e.target === tambahModal) {
        closeTambahModal();
    }
});

// Event listener untuk tombol escape
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        closeDetailModal();
        closeEditModal();
        closeTambahModal();
    }
});

// Muat data saat halaman dimuat
document.addEventListener('DOMContentLoaded', function () {
    loadKapalData();
    loadDPIList();
    checkAuth();
});