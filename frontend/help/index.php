<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bantuan & Panduan - Zona Laut</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../src/output.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <style>
        /* Animasi dan Styling Khusus */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-20px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .animate-fadeIn { animation: fadeIn 0.5s ease-out; }
        .animate-slideIn { animation: slideIn 0.3s ease-out; }

        /* FAQ Styling */
        .faq-item {
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            margin-bottom: 1rem;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .faq-item:hover {
            border-color: #3b82f6;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
        }

        .faq-question {
            padding: 1.5rem;
            background: white;
            cursor: pointer;
            display: flex;
            justify-content: between;
            align-items: center;
            font-weight: 600;
            color: #374151;
            transition: background-color 0.2s;
        }

        .faq-question:hover {
            background-color: #f8fafc;
        }

        .faq-answer {
            padding: 0 1.5rem;
            max-height: 0;
            overflow: hidden;
            background: #f8fafc;
            transition: all 0.3s ease;
        }

        .faq-item.active .faq-answer {
            padding: 1.5rem;
            max-height: 500px;
        }

        .faq-icon {
            transition: transform 0.3s ease;
        }

        .faq-item.active .faq-icon {
            transform: rotate(180deg);
        }

        /* Contact Card Styling */
        .contact-card {
            border-radius: 0.75rem;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            border: 1px solid #e5e7eb;
        }

        .contact-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .contact-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.5rem;
        }

        /* Step by Step Guide */
        .step-container {
            position: relative;
            padding-left: 3rem;
            margin-bottom: 2rem;
        }

        .step-number {
            position: absolute;
            left: 0;
            top: 0;
            width: 2.5rem;
            height: 2.5rem;
            background: #3b82f6;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.1rem;
        }

        .step-content {
            background: white;
            padding: 1.5rem;
            border-radius: 0.75rem;
            border: 1px solid #e5e7eb;
        }

        /* Quick Action Cards */
        .quick-action-card {
            background: white;
            border-radius: 0.75rem;
            padding: 1.5rem;
            border: 1px solid #e5e7eb;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .quick-action-card:hover {
            border-color: #3b82f6;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(59, 130, 246, 0.1);
        }

        /* Search Styling */
        .search-container {
            position: relative;
            max-width: 500px;
            margin: 0 auto 3rem;
        }

        .search-input {
            width: 100%;
            padding: 1rem 3rem 1rem 1.5rem;
            border: 2px solid #e5e7eb;
            border-radius: 2rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .search-icon {
            position: absolute;
            right: 1.5rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6b7280;
        }

        /* Category Tabs */
        .category-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 2rem;
            justify-content: center;
        }

        .category-tab {
            padding: 0.75rem 1.5rem;
            border: 2px solid #e5e7eb;
            border-radius: 2rem;
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .category-tab.active,
        .category-tab:hover {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .step-container {
                padding-left: 2.5rem;
            }
            
            .step-number {
                width: 2rem;
                height: 2rem;
                font-size: 0.9rem;
            }
            
            .contact-card {
                padding: 1.5rem;
            }
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
                    <h1 class="text-2xl font-bold text-gray-800">Bantuan & Panduan</h1>
                    <p class="text-gray-600">Temukan solusi dan panduan penggunaan sistem Zona Laut</p>
                </div>
                <div class="mt-4 md:mt-0">
                    <div class="flex items-center space-x-2 text-sm text-gray-500">
                        <i class="fas fa-clock"></i>
                        <span>Terakhir diperbarui: <span id="lastUpdated">30 Oktober 2024</span></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Section -->
        <div class="px-6 mb-8">
            <div class="search-container">
                <input type="text" id="searchHelp" class="search-input" placeholder="Cari pertanyaan atau topik bantuan...">
                <div class="search-icon">
                    <i class="fas fa-search"></i>
                </div>
            </div>

            <!-- Category Tabs -->
            <div class="category-tabs">
                <div class="category-tab active" data-category="all">Semua</div>
                <div class="category-tab" data-category="penggunaan">Cara Penggunaan</div>
                <div class="category-tab" data-category="teknis">Masalah Teknis</div>
                <div class="category-tab" data-category="akun">Manajemen Akun</div>
                <div class="category-tab" data-category="kapal">Data Kapal</div>
                <div class="category-tab" data-category="tangkapan">Data Tangkapan</div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="px-6 mb-8">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Aksi Cepat</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="quick-action-card" onclick="scrollToSection('faq-penggunaan')">
                    <div class="text-blue-500 text-center mb-3">
                        <i class="fas fa-play-circle text-3xl"></i>
                    </div>
                    <h3 class="font-semibold text-center mb-2">Panduan Penggunaan</h3>
                    <p class="text-sm text-gray-600 text-center">Pelajari cara menggunakan sistem step by step</p>
                </div>
                <div class="quick-action-card" onclick="scrollToSection('contact-support')">
                    <div class="text-green-500 text-center mb-3">
                        <i class="fas fa-headset text-3xl"></i>
                    </div>
                    <h3 class="font-semibold text-center mb-2">Hubungi Support</h3>
                    <p class="text-sm text-gray-600 text-center">Butuh bantuan langsung dari tim kami</p>
                </div>
                <div class="quick-action-card" onclick="showVideoTutorial()">
                    <div class="text-purple-500 text-center mb-3">
                        <i class="fas fa-video text-3xl"></i>
                    </div>
                    <h3 class="font-semibold text-center mb-2">Video Tutorial</h3>
                    <p class="text-sm text-gray-600 text-center">Tonton panduan visual penggunaan sistem</p>
                </div>
                <div class="quick-action-card" onclick="downloadManual()">
                    <div class="text-orange-500 text-center mb-3">
                        <i class="fas fa-file-pdf text-3xl"></i>
                    </div>
                    <h3 class="font-semibold text-center mb-2">Download Manual</h3>
                    <p class="text-sm text-gray-600 text-center">Unduh panduan lengkap dalam format PDF</p>
                </div>
            </div>
        </div>

        <!-- Getting Started Guide -->
        <div class="px-6 mb-8">
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl p-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Mulai Menggunakan Zona Laut</h2>
                
                <div class="max-w-4xl mx-auto">
                    <div class="step-container animate-fadeIn">
                        <div class="step-number">1</div>
                        <div class="step-content">
                            <h3 class="font-semibold text-lg mb-2">Registrasi dan Login</h3>
                            <p class="text-gray-600 mb-3">Daftar akun baru atau login menggunakan kredensial yang sudah ada</p>
                            <ul class="list-disc list-inside text-sm text-gray-600 space-y-1">
                                <li>Pastikan email yang digunakan valid</li>
                                <li>Gunakan password yang kuat</li>
                                <li>Simpan informasi login dengan aman</li>
                            </ul>
                        </div>
                    </div>

                    <div class="step-container animate-fadeIn" style="animation-delay: 0.1s">
                        <div class="step-number">2</div>
                        <div class="step-content">
                            <h3 class="font-semibold text-lg mb-2">Kelola Profil</h3>
                            <p class="text-gray-600 mb-3">Lengkapi data profil dan informasi kontak Anda</p>
                            <ul class="list-disc list-inside text-sm text-gray-600 space-y-1">
                                <li>Update foto profil dan informasi pribadi</li>
                                <li>Verifikasi alamat email dan nomor telepon</li>
                                <li>Atur preferensi notifikasi</li>
                            </ul>
                        </div>
                    </div>

                    <div class="step-container animate-fadeIn" style="animation-delay: 0.2s">
                        <div class="step-number">3</div>
                        <div class="step-content">
                            <h3 class="font-semibold text-lg mb-2">Input Data Kapal</h3>
                            <p class="text-gray-600 mb-3">Tambahkan dan kelola data kapal yang dimiliki</p>
                            <ul class="list-disc list-inside text-sm text-gray-600 space-y-1">
                                <li>Input spesifikasi teknis kapal</li>
                                <li>Upload dokumen kapal yang diperlukan</li>
                                <li>Atur jadwal perawatan rutin</li>
                            </ul>
                        </div>
                    </div>

                    <div class="step-container animate-fadeIn" style="animation-delay: 0.3s">
                        <div class="step-number">4</div>
                        <div class="step-content">
                            <h3 class="font-semibold text-lg mb-2">Catat Hasil Tangkapan</h3>
                            <p class="text-gray-600 mb-3">Rekam data tangkapan ikan secara berkala</p>
                            <ul class="list-disc list-inside text-sm text-gray-600 space-y-1">
                                <li>Input jenis dan jumlah tangkapan</li>
                                <li>Catat lokasi dan waktu penangkapan</li>
                                <li>Monitor trend hasil tangkapan</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FAQ Section -->
        <div class="px-6 mb-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Pertanyaan yang Sering Diajukan</h2>
            
            <div id="faq-container">
                <!-- FAQ akan di-generate oleh JavaScript -->
            </div>
        </div>

        <!-- Contact Support -->
        <div id="contact-support" class="px-6 mb-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Hubungi Tim Support</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="contact-card">
                    <div class="contact-icon bg-blue-100 text-blue-600">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <h3 class="font-semibold text-lg mb-2">Email Support</h3>
                    <p class="text-gray-600 mb-4">Kirim pertanyaan detail via email</p>
                    <a href="mailto:support@zonalaut.com" class="text-blue-600 font-semibold">support@zonalaut.com</a>
                    <p class="text-sm text-gray-500 mt-2">Response dalam 24 jam</p>
                </div>

                <div class="contact-card">
                    <div class="contact-icon bg-green-100 text-green-600">
                        <i class="fas fa-phone"></i>
                    </div>
                    <h3 class="font-semibold text-lg mb-2">Telepon</h3>
                    <p class="text-gray-600 mb-4">Hubungi langsung tim support</p>
                    <a href="tel:+623317654321" class="text-green-600 font-semibold">+62 33 1765 4321</a>
                    <p class="text-sm text-gray-500 mt-2">Senin - Jumat, 08:00 - 17:00 WIB</p>
                </div>

                <div class="contact-card">
                    <div class="contact-icon bg-purple-100 text-purple-600">
                        <i class="fas fa-comments"></i>
                    </div>
                    <h3 class="font-semibold text-lg mb-2">Live Chat</h3>
                    <p class="text-gray-600 mb-4">Chat langsung dengan support</p>
                    <button onclick="startLiveChat()" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition duration-200">
                        Mulai Chat
                    </button>
                    <p class="text-sm text-gray-500 mt-2">Tersedia 24/7</p>
                </div>
            </div>
        </div>

        <!-- Emergency Contact -->
        <div class="px-6 mb-8">
            <div class="bg-red-50 border border-red-200 rounded-2xl p-6">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-red-500 text-2xl mr-4"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-red-800 mb-2">Kontak Darurat</h3>
                        <p class="text-red-700 mb-4">Untuk masalah urgent yang membutuhkan penanganan segera</p>
                        <div class="flex flex-wrap gap-4">
                            <div class="flex items-center">
                                <i class="fas fa-phone text-red-500 mr-2"></i>
                                <span class="font-semibold">Emergency Hotline: +62 33 1765 9999</span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-whatsapp text-green-500 mr-2"></i>
                                <span class="font-semibold">WhatsApp: +62 812 3456 7890</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </main>

    <!-- Toast Container -->
    <div class="toast-container fixed top-5 right-5 z-50 flex flex-col gap-2 max-w-[400px]" id="toastContainer"></div>

    <script src="../js/script.js"></script>
    <script src="../js/dashboard/auth.js"></script>
    <script src="../js/dashboard/sidebar.js"></script>

    <script>
        // Data FAQ
        const faqData = [
            {
                question: "Bagaimana cara mendaftar akun baru?",
                answer: "Untuk mendaftar akun baru, klik tombol 'Daftar' di halaman login, isi formulir pendaftaran dengan data yang valid, verifikasi email Anda, dan login menggunakan kredensial yang telah dibuat.",
                category: "akun"
            },
            {
                question: "Apa yang harus dilakukan jika lupa password?",
                answer: "Klik 'Lupa Password' di halaman login, masukkan email terdaftar, ikuti instruksi reset password yang dikirim ke email Anda, dan buat password baru.",
                category: "akun"
            },
            {
                question: "Bagaimana cara menambahkan data kapal baru?",
                answer: "Pergi ke menu 'Data Kapal', klik tombol 'Tambah Kapal', isi semua informasi yang diperlukan termasuk spesifikasi teknis, dan simpan data.",
                category: "kapal"
            },
            {
                question: "Bagaimana menginput data tangkapan ikan?",
                answer: "Akses menu 'Data Tangkapan', pilih 'Tambah Data', isi form dengan informasi tangkapan termasuk jenis ikan, jumlah, lokasi, dan tanggal, lalu simpan.",
                category: "tangkapan"
            },
            {
                question: "Apa saja dokumen yang diperlukan untuk registrasi kapal?",
                answer: "Dokumen yang diperlukan meliputi: Sertifikat kepemilikan kapal, Surat Izin Penangkapan Ikan (SIPI), dokumen identitas pemilik, dan sertifikat kelayakan kapal.",
                category: "kapal"
            },
            {
                question: "Bagaimana cara mengubah informasi profil?",
                answer: "Klik menu 'Profil Saya', edit informasi yang ingin diubah, termasuk foto profil, data kontak, dan preferensi, lalu klik 'Simpan Perubahan'.",
                category: "akun"
            },
            {
                question: "Apa yang harus dilakukan jika mengalami error saat login?",
                answer: "Pastikan email dan password benar, cek koneksi internet, clear cache browser, atau gunakan fitur 'Lupa Password' jika diperlukan. Jika masalah berlanjut, hubungi support.",
                category: "teknis"
            },
            {
                question: "Bagaimana cara mengunduh laporan data tangkapan?",
                answer: "Pergi ke menu 'Laporan', pilih periode yang diinginkan, tentukan format laporan (PDF/Excel), dan klik 'Unduh Laporan'.",
                category: "penggunaan"
            },
            {
                question: "Apakah data yang diinput aman dan terjamin?",
                answer: "Ya, semua data dienkripsi dan disimpan dengan sistem keamanan tingkat tinggi. Backup data dilakukan secara berkala untuk mencegah kehilangan data.",
                category: "teknis"
            },
            {
                question: "Bagaimana cara menghapus akun?",
                answer: "Hubungi tim support melalui email atau telepon untuk proses penghapusan akun. Proses ini membutuhkan verifikasi identitas untuk keamanan.",
                category: "akun"
            }
        ];

        // Initialize Help Page
        document.addEventListener('DOMContentLoaded', function() {
            initializeHelpPage();
            checkAuth();
        });

        function initializeHelpPage() {
            renderFAQ();
            setupEventListeners();
            updateLastUpdated();
        }

        function renderFAQ() {
            const container = document.getElementById('faq-container');
            container.innerHTML = '';

            faqData.forEach((faq, index) => {
                const faqItem = document.createElement('div');
                faqItem.className = 'faq-item animate-fadeIn';
                faqItem.dataset.category = faq.category;
                faqItem.innerHTML = `
                    <div class="faq-question" onclick="toggleFAQ(${index})">
                        <span>${faq.question}</span>
                        <i class="fas fa-chevron-down faq-icon"></i>
                    </div>
                    <div class="faq-answer">
                        <p>${faq.answer}</p>
                    </div>
                `;
                container.appendChild(faqItem);
            });
        }

        function toggleFAQ(index) {
            const faqItem = document.querySelectorAll('.faq-item')[index];
            const isActive = faqItem.classList.contains('active');
            
            // Close all FAQ items
            document.querySelectorAll('.faq-item').forEach(item => {
                item.classList.remove('active');
            });
            
            // Open clicked item if it wasn't active
            if (!isActive) {
                faqItem.classList.add('active');
            }
        }

        function setupEventListeners() {
            // Search functionality
            document.getElementById('searchHelp').addEventListener('input', function(e) {
                filterFAQ(e.target.value);
            });

            // Category tabs
            document.querySelectorAll('.category-tab').forEach(tab => {
                tab.addEventListener('click', function() {
                    const category = this.dataset.category;
                    
                    // Update active tab
                    document.querySelectorAll('.category-tab').forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Filter FAQ
                    filterFAQByCategory(category);
                });
            });
        }

        function filterFAQ(searchTerm) {
            const faqItems = document.querySelectorAll('.faq-item');
            const lowerSearchTerm = searchTerm.toLowerCase();

            faqItems.forEach(item => {
                const question = item.querySelector('.faq-question span').textContent.toLowerCase();
                const answer = item.querySelector('.faq-answer p').textContent.toLowerCase();
                
                if (question.includes(lowerSearchTerm) || answer.includes(lowerSearchTerm)) {
                    item.style.display = 'block';
                    item.classList.add('animate-slideIn');
                } else {
                    item.style.display = 'none';
                }
            });
        }

        function filterFAQByCategory(category) {
            const faqItems = document.querySelectorAll('.faq-item');
            
            faqItems.forEach(item => {
                if (category === 'all' || item.dataset.category === category) {
                    item.style.display = 'block';
                    item.classList.add('animate-slideIn');
                } else {
                    item.style.display = 'none';
                }
            });
        }

        function scrollToSection(sectionId) {
            const element = document.getElementById(sectionId);
            if (element) {
                element.scrollIntoView({ behavior: 'smooth' });
            }
        }

        function showVideoTutorial() {
            showToast('Fitur video tutorial akan segera hadir!', 'info');
        }

        function downloadManual() {
            showToast('Download manual pengguna akan segera tersedia!', 'info');
        }

        function startLiveChat() {
            showToast('Live chat support akan segera tersedia!', 'info');
        }

        function updateLastUpdated() {
            const now = new Date();
            const options = { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric',
                timeZone: 'Asia/Jakarta'
            };
            document.getElementById('lastUpdated').textContent = now.toLocaleDateString('id-ID', options);
        }

        function showToast(message, type = 'info') {
            const toastContainer = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            const bgColor = type === 'success' ? 'bg-green-500' : 
                           type === 'error' ? 'bg-red-500' : 
                           type === 'warning' ? 'bg-yellow-500' : 'bg-blue-500';
            const icon = type === 'success' ? 'fa-check-circle' : 
                        type === 'error' ? 'fa-exclamation-circle' : 
                        type === 'warning' ? 'fa-exclamation-triangle' : 'fa-info-circle';

            toast.className = `${bgColor} text-white px-4 py-3 rounded-lg shadow-lg flex items-center animate-fadeIn`;
            toast.innerHTML = `
                <i class="fas ${icon} mr-2"></i>
                <span>${message}</span>
            `;

            toastContainer.appendChild(toast);

            setTimeout(() => {
                toast.remove();
            }, 5000);
        }
    </script>
</body>

</html>