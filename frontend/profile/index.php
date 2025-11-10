<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - Zona Laut</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Mengganti CDN Tailwind dengan file CSS lokal -->
    <link rel="shortcut icon" href="../images/logo.png" type="image/x-icon">
    <link rel="stylesheet" href="../src/output.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <style>
        /* Animasi tambahan */
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes scaleIn {
            from {
                opacity: 0;
                transform: scale(0.9);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .animate-slideInUp {
            animation: slideInUp 0.3s ease-out;
        }

        .animate-fadeIn {
            animation: fadeIn 0.2s ease-out;
        }

        .animate-scaleIn {
            animation: scaleIn 0.2s ease-out;
        }

        /* Styling untuk form yang lebih menarik */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #374151;
        }

        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            transition: all 0.2s;
            font-size: 0.875rem;
        }

        .form-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-input:read-only {
            background-color: #f9fafb;
            color: #6b7280;
            cursor: not-allowed;
        }

        /* Styling untuk tombol */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: all 0.2s;
            cursor: pointer;
            border: none;
            font-size: 0.875rem;
        }

        .btn-primary {
            background-color: #3b82f6;
            color: white;
        }

        .btn-primary:hover {
            background-color: #2563eb;
        }

        .btn-secondary {
            background-color: #6b7280;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #4b5563;
        }

        .btn-success {
            background-color: #10b981;
            color: white;
        }

        .btn-success:hover {
            background-color: #059669;
        }

        /* Loading spinner */
        .spinner {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        /* Card styling */
        .card {
            background: white;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            transition: all 0.3s;
        }

        .card:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        /* Modal styling */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 50;
            padding: 1rem;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .modal-content {
            background: white;
            border-radius: 0.75rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            max-width: 32rem;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            transform: scale(0.9);
            opacity: 0;
            transition: all 0.3s ease;
        }

        .modal-overlay.active .modal-content {
            transform: scale(1);
            opacity: 1;
        }

        /* Section styling */
        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .main-content {
            z-index: 1 !important;
        }

        .z-100 {
            z-index: 100 !important;
        }

        /* Profile specific styles */
        .profile-avatar {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            font-weight: bold;
            margin: 0 auto;
            border: 4px solid white;
            box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.1);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .stat-card {
            background: white;
            border-radius: 0.5rem;
            padding: 1.25rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            text-align: center;
        }

        .stat-card .value {
            font-size: 1.875rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.25rem;
        }

        .stat-card .label {
            font-size: 0.875rem;
            color: #6b7280;
        }

        .password-toggle {
            position: relative;
        }

        .password-toggle .toggle-btn {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6b7280;
            cursor: pointer;
            font-size: 1rem;
        }
    </style>
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
                    <h1 class="text-2xl font-bold text-gray-800">Profil Saya</h1>
                    <p class="text-gray-600">Kelola informasi akun dan data pribadi Anda</p>
                </div>
            </div>
        </div>

        <!-- Loading Indicator -->
        <div id="loadingIndicator" class="flex justify-center items-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
            <span class="ml-3 text-gray-600">Memuat data profil...</span>
        </div>

        <!-- Profile Content -->
        <div class="px-6 pb-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Sidebar Profil -->
                <div class="lg:col-span-1">
                    <div class="card p-6">
                        <div class="text-center mb-6">
                            <div class="profile-avatar" id="userAvatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <h2 class="text-xl font-bold text-gray-800 mt-4" id="displayName">Loading...</h2>
                            <p class="text-gray-600">Pemilik Kapal</p>
                        </div>

                        <div class="stats-grid">
                            <div class="stat-card">
                                <div class="value" id="kapalCount">0</div>
                                <div class="label">Kapal</div>
                            </div>
                            <div class="stat-card">
                                <div class="value" id="tangkapanCount">0</div>
                                <div class="label">Tangkapan</div>
                            </div>
                        </div>

                        <div class="mt-6 text-center text-sm text-gray-500">
                            <p>Terdaftar sejak: <span id="registeredDate">-</span></p>
                        </div>
                    </div>
                </div>

                <!-- Form Profil -->
                <div class="lg:col-span-2">
                    <div class="card p-6">
                        <h3 class="section-title">
                            <i class="fas fa-user-edit mr-2"></i>Informasi Pribadi
                        </h3>

                        <form id="profileForm" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- ID Pemilik (Read Only) -->
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-id-card mr-2"></i>ID Pemilik
                                    </label>
                                    <input type="text" id="id" class="form-input" readonly>
                                </div>

                                <!-- Nama Pemilik -->
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-user mr-2"></i>Nama Pemilik *
                                    </label>
                                    <input type="text" id="nama_pemilik" name="nama_pemilik" class="form-input" placeholder="Masukkan nama lengkap" required>
                                </div>

                                <!-- Email -->
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-envelope mr-2"></i>Email *
                                    </label>
                                    <input type="email" id="email_pemilik" name="email" class="form-input" placeholder="Masukkan alamat email" required>
                                </div>

                                <!-- Nomor Telepon -->
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-phone mr-2"></i>Nomor Telepon *
                                    </label>
                                    <input type="tel" id="nomor_telepon" name="nomor_telepon" class="form-input" placeholder="Masukkan nomor telepon" required>
                                </div>
                            </div>

                            <!-- Alamat -->
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-map-marker-alt mr-2"></i>Alamat *
                                </label>
                                <textarea id="alamat" name="alamat" class="form-input" rows="3" placeholder="Masukkan alamat lengkap" required></textarea>
                            </div>

                            <!-- Password -->
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-lock mr-2"></i>Password
                                </label>
                                <div class="password-toggle">
                                    <input type="password" id="password" name="password" class="form-input" placeholder="Masukkan password baru">
                                    <button type="button" class="toggle-btn" id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <p class="text-sm text-gray-500 mt-1">Kosongkan jika tidak ingin mengubah password</p>
                            </div>

                            <!-- Informasi -->
                            <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                                <div class="flex items-start">
                                    <i class="fas fa-info-circle text-blue-500 mt-1 mr-3"></i>
                                    <div>
                                        <p class="text-sm text-blue-700">
                                            <strong>Informasi:</strong> Pastikan data yang Anda masukkan sudah benar dan valid.
                                            Perubahan pada profil akan langsung tersimpan di sistem.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex justify-end space-x-3 mt-6 pt-6 border-t border-gray-200">
                                <button type="button" onclick="resetForm()" class="btn btn-secondary">
                                    <i class="fas fa-redo mr-2"></i>Reset
                                </button>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save mr-2"></i>Simpan Perubahan
                                </button>
                            </div>
                        </form>
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
                <p class="text-red-600 mb-4" id="errorMessage">Terjadi kesalahan saat memuat data profil.</p>
                <button onclick="loadProfileData()" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg transition duration-200">
                    <i class="fas fa-redo mr-2"></i>Coba Lagi
                </button>
            </div>
        </div>

    </main>

    <!-- Toast Container -->
    <div class="toast-container fixed top-5 right-5 z-50 flex flex-col gap-2 max-w-[400px]" id="toastContainer"></div>

    <script src="../js/script.js"></script>
    <script src="../js/dashboard/auth.js"></script>
    <script src="../js/dashboard/sidebar.js"></script>

    <script>
        // Variabel global
        let originalProfileData = null;

        // Fungsi untuk memuat data profil
        async function loadProfileData() {
            const loadingIndicator = document.getElementById('loadingIndicator');
            const errorState = document.getElementById('errorState');

            try {
                // Tampilkan loading, sembunyikan yang lain
                loadingIndicator.classList.remove('hidden');
                errorState.classList.add('hidden');

                // Fetch data dari API
                const user = JSON.parse(localStorage.getItem('currentUser'));
                const response = await fetch('../../backend/system/pemilik.php?id=' + user.id);
                const result = await response.json();

                // Sembunyikan loading
                loadingIndicator.classList.add('hidden');

                if (result.success && result.data) {
                    let profileData = null;

                    // Jika data adalah array, cari data user yang sesuai
                    if (Array.isArray(result.data)) {
                        profileData = result.data.find(pemilik => pemilik.id == user.id);
                    }
                    // Jika data adalah object single
                    else if (typeof result.data === 'object' && result.data !== null) {
                        profileData = result.data;
                    }

                    if (profileData) {
                        displayProfileData(profileData);
                        await loadStatistics(user.id);
                    } else {
                        showErrorState('Data profil tidak ditemukan');
                    }
                } else {
                    showErrorState(result.message || 'Gagal memuat data profil');
                }
            } catch (error) {
                console.error('Error loading profile data:', error);
                loadingIndicator.classList.add('hidden');
                showErrorState(error.message || 'Terjadi kesalahan saat memuat data profil.');
            }
        }

        // Fungsi untuk menampilkan data profil
        function displayProfileData(profileData) {
            // Isi form dengan data
            document.getElementById('id').value = profileData.id || '';
            document.getElementById('nama_pemilik').value = profileData.nama_pemilik || '';
            document.getElementById('email_pemilik').value = profileData.email || '';
            document.getElementById('nomor_telepon').value = profileData.nomor_telepon || '';
            document.getElementById('alamat').value = profileData.alamat || '';

            // Update display name dan avatar
            const displayName = document.getElementById('displayName');
            const userAvatar = document.getElementById('userAvatar');

            displayName.textContent = profileData.nama_pemilik || 'User';

            if (profileData.nama_pemilik) {
                const initials = profileData.nama_pemilik.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
                userAvatar.innerHTML = initials;
                userAvatar.classList.remove('fa-user');
            }

            // Simpan data original untuk reset
            originalProfileData = {
                ...profileData
            };
        }

        // Fungsi untuk load statistics
        // Fungsi untuk load statistics
        async function loadStatistics(userId) {
            try {
                // Fetch data kapal
                let kapalCount = 0;
                try {
                    const kapalResponse = await fetch('../../backend/system/kapal.php');
                    if (!kapalResponse.ok) throw new Error('HTTP error ' + kapalResponse.status);

                    const kapalResult = await kapalResponse.json();

                    if (kapalResult.success && kapalResult.data) {
                        if (Array.isArray(kapalResult.data)) {
                            kapalCount = kapalResult.data.filter(kapal => kapal.id_pemilik == userId).length;
                        } else if (typeof kapalResult.data === 'object' && kapalResult.data !== null) {
                            kapalCount = kapalResult.data.id_pemilik == userId ? 1 : 0;
                        }
                    }
                } catch (kapalError) {
                    console.error('Error loading kapal data:', kapalError);
                    kapalCount = 0;
                }

                // Fetch data tangkapan
                let tangkapanCount = 0;
                try {
                    const tangkapanResponse = await fetch('../../backend/system/tangkapan.php');
                    if (!tangkapanResponse.ok) throw new Error('HTTP error ' + tangkapanResponse.status);

                    const tangkapanResult = await tangkapanResponse.json();

                    if (tangkapanResult.success && tangkapanResult.data) {
                        if (Array.isArray(tangkapanResult.data)) {
                            tangkapanCount = tangkapanResult.data.length;
                        } else if (typeof tangkapanResult.data === 'object' && tangkapanResult.data !== null) {
                            tangkapanCount = 1;
                        }
                    }
                } catch (tangkapanError) {
                    console.error('Error loading tangkapan data:', tangkapanError);
                    tangkapanCount = 0;
                }

                // Update UI
                document.getElementById('kapalCount').textContent = kapalCount;
                document.getElementById('tangkapanCount').textContent = tangkapanCount;

                // Set registered date
               
                let tanggalDaftar = new Date(JSON.parse(localStorage.getItem('currentUser')).created_at);
                const registeredDate = tanggalDaftar.toLocaleDateString('id-ID', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
                document.getElementById('registeredDate').textContent = registeredDate;

            } catch (error) {
                console.error('Unexpected error in loadStatistics:', error);
                document.getElementById('kapalCount').textContent = '0';
                document.getElementById('tangkapanCount').textContent = '0';
            }
        }

        // Fungsi untuk menampilkan error state
        function showErrorState(message) {
            const errorState = document.getElementById('errorState');
            document.getElementById('errorMessage').textContent = message;
            errorState.classList.remove('hidden');
        }

        // Fungsi untuk toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                passwordInput.type = 'password';
                icon.className = 'fas fa-eye';
            }
        });

        // Fungsi untuk reset form
        function resetForm() {
            if (originalProfileData) {
                document.getElementById('nama_pemilik').value = originalProfileData.nama_pemilik || '';
                document.getElementById('email_pemilik').value = originalProfileData.email || '';
                document.getElementById('nomor_telepon').value = originalProfileData.nomor_telepon || '';
                document.getElementById('alamat').value = originalProfileData.alamat || '';
                document.getElementById('password').value = '';
            }
            showToast('Form telah direset ke data semula', 'info');
        }

        // Fungsi untuk validasi form
        function validateForm(formData) {
            const errors = [];

            if (!formData.nama_pemilik.trim()) {
                errors.push('Nama pemilik harus diisi');
            }

            if (!formData.email.trim()) {
                errors.push('Email harus diisi');
            } else if (!/\S+@\S+\.\S+/.test(formData.email)) {
                errors.push('Format email tidak valid');
            }

            if (!formData.nomor_telepon.trim()) {
                errors.push('Nomor telepon harus diisi');
            }

            if (!formData.alamat.trim()) {
                errors.push('Alamat harus diisi');
            }

            return errors;
        }

        // Fungsi untuk update profile
        async function updateProfile(formData) {
            try {
                const submitBtn = document.querySelector('#profileForm button[type="submit"]');
                const originalText = submitBtn.innerHTML;

                // Show loading
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...';
                submitBtn.disabled = true;

                const user = JSON.parse(localStorage.getItem('currentUser'));
                const data = {
                    action: 'edit_profile',
                    id: user.id,
                    nama_pemilik: formData.nama_pemilik,
                    email: formData.email,
                    nomor_telepon: formData.nomor_telepon,
                    alamat: formData.alamat
                };

                // Jika password diisi, tambahkan ke data
                if (formData.password) {
                    data.password = formData.password;
                }

                const response = await fetch('../../backend/system/pemilik.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    // Update localStorage
                    const currentUser = JSON.parse(localStorage.getItem('currentUser'));
                    const updatedUser = {
                        ...currentUser,
                        nama_pemilik: formData.nama_pemilik,
                        email: formData.email
                    };
                    localStorage.setItem('currentUser', JSON.stringify(updatedUser));

                    // Update display
                    document.getElementById('displayName').textContent = formData.nama_pemilik;
                    const userAvatar = document.getElementById('userAvatar');
                    const initials = formData.nama_pemilik.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
                    userAvatar.innerHTML = initials;

                    // Update original data
                    originalProfileData = {
                        ...originalProfileData,
                        ...formData
                    };

                    showToast('Profil berhasil diperbarui!', 'success');
                } else {
                    throw new Error(result.message || 'Gagal memperbarui profil');
                }
            } catch (error) {
                console.error('Error updating profile:', error);
                showToast('Gagal memperbarui profil: ' + error.message, 'error');
            } finally {
                const submitBtn = document.querySelector('#profileForm button[type="submit"]');
                submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Simpan Perubahan';
                submitBtn.disabled = false;
            }
        }

        // Event listener untuk form submission
        document.getElementById('profileForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = {
                nama_pemilik: document.getElementById('nama_pemilik').value,
                email: document.getElementById('email_pemilik').value,
                nomor_telepon: document.getElementById('nomor_telepon').value,
                alamat: document.getElementById('alamat').value,
                password: document.getElementById('password').value
            };

            // Validasi form
            const errors = validateForm(formData);
            if (errors.length > 0) {
                showToast(errors[0], 'error');
                return;
            }

            // Update profile
            await updateProfile(formData);
        });

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

        // Muat data saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            loadProfileData();
            checkAuth();
        });
    </script>
</body>

</html>