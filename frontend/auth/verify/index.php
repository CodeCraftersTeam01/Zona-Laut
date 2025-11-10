<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi WhatsApp - Zona Laut</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/login.css">
    <link rel="shortcut icon" href="../../images/logo.png" type="image/x-icon">
    <style>
        /* Verification Specific Styles */
        .verification-container {
            max-width: 440px;
            width: 100%;
            margin: 0 auto;
        }

        .verification-icon {
            width: 90px;
            height: 90px;
            background: linear-gradient(135deg, #25d366, #128c7e);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            color: white;
            font-size: 2.2rem;
            box-shadow: 0 8px 20px rgba(37, 211, 102, 0.3);
            animation: pulse 2s infinite;
        }

        .verification-header {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .verification-header h1 {
            font-size: 1.8rem;
            font-weight: 700;
            color: #1d1d1f;
            margin-bottom: 0.75rem;
        }

        .verification-header p {
            color: #86868b;
            font-size: 1rem;
            line-height: 1.5;
        }

        .whatsapp-display {
            background: rgba(37, 211, 102, 0.05);
            border: 1px solid rgba(37, 211, 102, 0.2);
            border-radius: 12px;
            padding: 1.25rem;
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .phone-number {
            font-size: 1.1rem;
            font-weight: 600;
            color: #25d366;
            margin-bottom: 0.5rem;
        }

        .whatsapp-note {
            color: #86868b;
            font-size: 0.85rem;
        }

        .code-input-container {
            margin-bottom: 1.5rem;
        }

        .code-inputs {
            display: flex;
            gap: 0.6rem;
            justify-content: center;
            margin-bottom: 1.25rem;
        }

        .code-input {
            width: 50px;
            height: 60px;
            border: 2px solid #d2d2d7;
            border-radius: 10px;
            text-align: center;
            font-size: 1.5rem;
            font-weight: 700;
            color: #1d1d1f;
            background: white;
            transition: all 0.3s ease;
        }

        .code-input:focus {
            outline: none;
            border-color: #25d366;
            box-shadow: 0 0 0 3px rgba(37, 211, 102, 0.1);
            transform: scale(1.05);
        }

        .code-input.filled {
            border-color: #25d366;
            background: rgba(37, 211, 102, 0.05);
        }

        .timer-container {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .timer {
            font-size: 1rem;
            font-weight: 600;
            color: #ff453a;
            margin-bottom: 0.75rem;
        }

        .timer.expired {
            color: #86868b;
        }

        .resend-link {
            color: #25d366;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
            transition: color 0.2s;
            font-size: 0.9rem;
        }

        .resend-link:hover {
            color: #128c7e;
        }

        .resend-link.disabled {
            color: #86868b;
            cursor: not-allowed;
            opacity: 0.6;
        }

        .verification-actions {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .verify-button {
            background: linear-gradient(135deg, #25d366, #128c7e);
            color: white;
            border: none;
            padding: 1rem;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            box-shadow: 0 4px 12px rgba(37, 211, 102, 0.3);
        }

        .verify-button:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(37, 211, 102, 0.4);
        }

        .verify-button:disabled {
            background: #d2d2d7;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .cancel-button {
            background: transparent;
            color: #86868b;
            border: 2px solid #d2d2d7;
            padding: 0.875rem;
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .cancel-button:hover {
            background: #f5f5f7;
            border-color: #86868b;
        }

        .troubleshoot-section {
            background: #f5f5f7;
            border-radius: 10px;
            padding: 1.25rem;
            margin-bottom: 1.5rem;
        }

        .troubleshoot-title {
            font-weight: 600;
            color: #1d1d1f;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.95rem;
        }

        .troubleshoot-list {
            list-style: none;
            padding: 0;
        }

        .troubleshoot-item {
            display: flex;
            align-items: flex-start;
            gap: 0.6rem;
            margin-bottom: 0.75rem;
            padding: 0.6rem;
            background: white;
            border-radius: 6px;
            border: 1px solid #e5e5e7;
        }

        .troubleshoot-item:last-child {
            margin-bottom: 0;
        }

        .troubleshoot-icon {
            color: #25d366;
            font-size: 0.8rem;
            margin-top: 0.15rem;
        }

        .troubleshoot-content {
            flex: 1;
        }

        .troubleshoot-content strong {
            color: #1d1d1f;
            display: block;
            margin-bottom: 0.2rem;
            font-size: 0.85rem;
        }

        .troubleshoot-content p {
            color: #86868b;
            font-size: 0.8rem;
            margin: 0;
            line-height: 1.3;
        }

        .alternative-methods {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .alternative-title {
            color: #86868b;
            font-size: 0.85rem;
            margin-bottom: 0.75rem;
        }

        .email-button {
            background: linear-gradient(135deg, #0071e3, #0077ed);
            color: white;
            border: none;
            padding: 0.875rem 1.25rem;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 4px 12px rgba(0, 113, 227, 0.3);
            font-size: 0.9rem;
        }

        .email-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 113, 227, 0.4);
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }

            100% {
                transform: scale(1);
            }
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-5px);
            }

            75% {
                transform: translateX(5px);
            }
        }

        .shake {
            animation: shake 0.5s ease-in-out;
        }

        /* Success State */
        .success-state {
            text-align: center;
            padding: 1.5rem 0;
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #25d366, #128c7e);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            color: white;
            font-size: 2rem;
            box-shadow: 0 8px 20px rgba(37, 211, 102, 0.3);
        }

        .success-title {
            font-size: 1.6rem;
            font-weight: 700;
            color: #1d1d1f;
            margin-bottom: 0.75rem;
        }

        .success-message {
            color: #86868b;
            font-size: 1rem;
            margin-bottom: 1.5rem;
            line-height: 1.5;
        }

        .continue-button {
            background: linear-gradient(135deg, #0071e3, #0077ed);
            color: white;
            border: none;
            padding: 1rem 1.5rem;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 4px 12px rgba(0, 113, 227, 0.3);
        }

        .continue-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 113, 227, 0.4);
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .verification-container {
                padding: 1rem;
                max-width: 100%;
            }

            .verification-icon {
                width: 70px;
                height: 70px;
                font-size: 1.8rem;
                margin-bottom: 1rem;
            }

            .verification-header h1 {
                font-size: 1.5rem;
            }

            .code-input {
                width: 45px;
                height: 55px;
                font-size: 1.3rem;
            }

            .whatsapp-display {
                padding: 1rem;
            }

            .troubleshoot-section {
                padding: 1rem;
            }
        }

        /* Compact layout adjustments */
        .login-section {
            align-items: normal !important;
            padding: 1.5rem !important;
        }

        .verification-container>* {
            margin-bottom: 1.25rem;
        }

        .verification-container>*:last-child {
            margin-bottom: 0;
        }
    </style>
</head>

<body>
    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>

    <div class="container">
        <!-- Map Section - Hidden on Mobile -->
        <section class="map-section">
            <div id="map"></div>
            <div class="map-overlay">
                <div class="brand-section fade-in">
                    <div class="brand-logo">
                        <i class="fab fa-whatsapp"></i>
                    </div>
                    <div class="brand-text">
                        <h1>Zona Laut</h1>
                        <p>WhatsApp Verification</p>
                    </div>
                </div>

                <div class="map-content">
                    <div class="map-stats fade-in delay-1">
                        <div class="stat-item">
                            <div class="stat-value">99.9%</div>
                            <div class="stat-label">Terkirim</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">
                                < 5s</div>
                                    <div class="stat-label">Rata-rata Waktu</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value">100%</div>
                                <div class="stat-label">Keandalan</div>
                            </div>
                        </div>

                        <div class="map-features fade-in delay-2">
                            <h3 style="margin-bottom: 1rem; font-weight: 600;">Verifikasi WhatsApp</h3>
                            <div class="feature-grid">
                                <div class="feature-item">
                                    <div class="feature-icon">
                                        <i class="fas fa-bolt"></i>
                                    </div>
                                    <span>Instant Delivery</span>
                                </div>
                                <div class="feature-item">
                                    <div class="feature-icon">
                                        <i class="fas fa-shield-alt"></i>
                                    </div>
                                    <span>Secure & Private</span>
                                </div>
                                <div class="feature-item">
                                    <div class="feature-icon">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <span>Expire 10 Menit</span>
                                </div>
                                <div class="feature-item">
                                    <div class="feature-icon">
                                        <i class="fas fa-sync"></i>
                                    </div>
                                    <span>Auto Retry</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="map-footer fade-in delay-3">
                        <div class="status-indicator">
                            <div class="status-dot"></div>
                            <span>WhatsApp Status: Connected</span>
                            <div class="security-badge">
                                <i class="fab fa-whatsapp"></i>
                                <span>Verified</span>
                            </div>
                        </div>
                        <p>© 2025 Zona Laut Enterprise. All rights reserved. v3.2.1 • Build 2456</p>
                    </div>
                </div>
        </section>

        <!-- Verification Section -->
        <section class="login-section">
            <div class="verification-container">
                <!-- Verification Form (Default State) -->
                <div id="verificationForm">
                    <div class="verification-icon">
                        <i class="fab fa-whatsapp"></i>
                    </div>

                    <div class="verification-header">
                        <h1>Verifikasi WhatsApp</h1>
                        <p>Kami telah mengirimkan kode OTP 6-digit ke WhatsApp Anda. Masukkan kode tersebut di bawah ini.</p>
                    </div>

                    <div class="whatsapp-display">
                        <div class="phone-number" id="userPhone">Memuat...</div>
                        <div class="whatsapp-note">Kode OTP akan kadaluarsa dalam 10 menit</div>
                    </div>

                    <div class="code-input-container">
                        <div class="code-inputs" id="codeInputs">
                            <input type="text" class="code-input" maxlength="1" data-index="0">
                            <input type="text" class="code-input" maxlength="1" data-index="1">
                            <input type="text" class="code-input" maxlength="1" data-index="2">
                            <input type="text" class="code-input" maxlength="1" data-index="3">
                            <input type="text" class="code-input" maxlength="1" data-index="4">
                            <input type="text" class="code-input" maxlength="1" data-index="5">
                        </div>

                        <div class="timer-container">
                            <div class="timer" id="timer">10:00</div>
                            <a href="#" class="resend-link disabled" id="resendLink">Kirim ulang kode OTP</a>
                        </div>
                    </div>

                    <div class="verification-actions">
                        <button class="verify-button" id="verifyButton" disabled>
                            <i class="fas fa-check-circle"></i>
                            Verifikasi WhatsApp
                        </button>
                        <button class="cancel-button" onclick="goBack()">
                            <i class="fas fa-arrow-left"></i>
                            Kembali ke Login
                        </button>
                    </div>

                    <div class="troubleshoot-section">
                        <div class="troubleshoot-title">
                            <i class="fas fa-question-circle"></i>
                            Tidak menerima kode?
                        </div>
                        <ul class="troubleshoot-list">
                            <li class="troubleshoot-item">
                                <div class="troubleshoot-icon">
                                    <i class="fas fa-sync"></i>
                                </div>
                                <div class="troubleshoot-content">
                                    <strong>Kirim ulang kode</strong>
                                    <p>Tunggu hingga timer habis, lalu klik kirim ulang</p>
                                </div>
                            </li>
                            <li class="troubleshoot-item">
                                <div class="troubleshoot-icon">
                                    <i class="fas fa-signal"></i>
                                </div>
                                <div class="troubleshoot-content">
                                    <strong>Periksa koneksi</strong>
                                    <p>Pastikan koneksi internet Anda stabil</p>
                                </div>
                            </li>
                            <li class="troubleshoot-item">
                                <div class="troubleshoot-icon">
                                    <i class="fas fa-mobile-alt"></i>
                                </div>
                                <div class="troubleshoot-content">
                                    <strong>Nomor salah?</strong>
                                    <p>Pastikan nomor WhatsApp yang terdaftar sudah benar</p>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <div class="alternative-methods">
                        <div class="alternative-title">Alternatif verifikasi</div>
                        <button class="email-button" onclick="switchToEmail()">
                            <i class="fas fa-envelope"></i>
                            Verifikasi via Email
                        </button>
                    </div>
                </div>

                <!-- Success State (Hidden by default) -->
                <div id="successState" class="success-state" style="display: none;">
                    <div class="success-icon">
                        <i class="fas fa-check"></i>
                    </div>
                    <h2 class="success-title">WhatsApp Terverifikasi!</h2>
                    <p class="success-message">Nomor WhatsApp Anda telah berhasil diverifikasi. Anda sekarang dapat mengakses semua fitur Zona Laut Enterprise.</p>
                    <button class="continue-button" onclick="redirectToDashboard()">
                        <i class="fas fa-rocket"></i>
                        Lanjutkan ke Dashboard
                    </button>
                </div>
            </div>
        </section>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="../../js/script.js"></script>
    <script>
        // Professional Toast System
        class ToastSystem {
            constructor() {
                this.container = document.getElementById('toastContainer');
                this.toasts = new Set();
            }

            show(type, title, message, duration = 5000) {
                const toast = this.createToast(type, title, message, duration);
                this.container.appendChild(toast);
                this.toasts.add(toast);

                requestAnimationFrame(() => {
                    toast.classList.add('show');
                });

                if (duration > 0) {
                    setTimeout(() => {
                        this.hide(toast);
                    }, duration);
                }

                return toast;
            }

            createToast(type, title, message, duration) {
                const toast = document.createElement('div');
                toast.className = `toast ${type}`;

                const icons = {
                    success: 'fa-check',
                    error: 'fa-exclamation-triangle',
                    warning: 'fa-exclamation',
                    info: 'fa-info-circle'
                };

                toast.innerHTML = `
                    <div class="toast-header">
                        <div class="toast-title">
                            <div class="toast-icon">
                                <i class="fas ${icons[type]}"></i>
                            </div>
                            ${title}
                        </div>
                        <button class="toast-close" onclick="toastSystem.hide(this.parentElement.parentElement)">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="toast-body">
                        <p class="toast-message">${message}</p>
                    </div>
                    <div class="toast-progress">
                        <div class="toast-progress-bar" style="animation-duration: ${duration}ms"></div>
                    </div>
                `;

                return toast;
            }

            hide(toast) {
                if (this.toasts.has(toast)) {
                    toast.classList.remove('show');
                    toast.classList.add('hide');

                    setTimeout(() => {
                        if (toast.parentNode) {
                            toast.parentNode.removeChild(toast);
                        }
                        this.toasts.delete(toast);
                    }, 400);
                }
            }

            success(title, message, duration = 5000) {
                return this.show('success', title, message, duration);
            }

            error(title, message, duration = 5000) {
                return this.show('error', title, message, duration);
            }

            warning(title, message, duration = 5000) {
                return this.show('warning', title, message, duration);
            }

            info(title, message, duration = 5000) {
                return this.show('info', title, message, duration);
            }
        }

        // Initialize toast system
        const toastSystem = new ToastSystem();

        class WhatsAppVerificationSystem {
            constructor() {
                this.codeLength = 6;
                this.timerDuration = 10 * 60; // 10 minutes in seconds
                this.timeLeft = this.timerDuration;
                this.timerInterval = null;
                this.verificationCode = '';
                this.userPhone = '';
                this.apiKey = ''; // Ganti dengan API key Fontee Anda
                this.otpSent = false; // Flag untuk menandai OTP sudah dikirim

                this.initializeEventListeners();
                this.loadUserData();

                // Hanya kirim OTP otomatis jika belum pernah dikirim
                if (!this.hasSentOTP()) {
                    this.sendOTP();
                } else {
                    this.startTimerFromStorage();
                }
            }

            // Cek apakah OTP sudah dikirim dari localStorage
            hasSentOTP() {
                const otpData = localStorage.getItem('whatsappOtpData');
                if (!otpData) return false;

                const data = JSON.parse(otpData);
                const now = new Date().getTime();

                // Cek apakah OTP masih valid (dalam 10 menit)
                if (now - data.timestamp < 10 * 60 * 1000) {
                    this.timeLeft = Math.max(0, Math.floor((10 * 60 * 1000 - (now - data.timestamp)) / 1000));
                    return true;
                }

                // Hapus data OTP yang sudah expired
                localStorage.removeItem('whatsappOtpData');
                return false;
            }

            // Start timer dari data yang disimpan
            startTimerFromStorage() {
                this.updateTimerDisplay();
                this.startTimer();
            }

            initializeEventListeners() {
                // Code input handling
                const codeInputs = document.querySelectorAll('.code-input');
                codeInputs.forEach((input, index) => {
                    input.addEventListener('input', (e) => this.handleCodeInput(e, index));
                    input.addEventListener('keydown', (e) => this.handleKeyDown(e, index));
                    input.addEventListener('paste', (e) => this.handlePaste(e));
                });

                // Verify button
                document.getElementById('verifyButton').addEventListener('click', () => this.verifyCode());

                // Resend link
                document.getElementById('resendLink').addEventListener('click', (e) => {
                    e.preventDefault();
                    this.resendOTP();
                });
            }

            async loadUserData() {
                try {
                    // Get user data from localStorage
                    const currentUser = JSON.parse(localStorage.getItem('currentUser') || '{}');
                    this.userPhone = currentUser.phone || currentUser.nomor_telepon || '';

                    if (!this.userPhone) {
                        throw new Error('Nomor WhatsApp tidak ditemukan');
                    }

                    // Format phone number for display
                    const formattedPhone = this.formatPhoneNumber(this.userPhone);
                    document.getElementById('userPhone').textContent = formattedPhone;

                } catch (error) {
                    toastSystem.error('Error', 'Data user tidak ditemukan. Silakan login kembali.');
                    setTimeout(() => {
                        window.location.href = '../auth/login.html';
                    }, 3000);
                }
            }

            formatPhoneNumber(phone) {
                // Remove non-digit characters
                const cleaned = phone.replace(/\D/g, '');

                // Format: +62 812-3456-7890
                if (cleaned.startsWith('62')) {
                    return `+${cleaned.slice(0, 2)} ${cleaned.slice(2, 5)}-${cleaned.slice(5, 8)}-${cleaned.slice(8)}`;
                } else if (cleaned.startsWith('0')) {
                    return `+62 ${cleaned.slice(1, 4)}-${cleaned.slice(4, 7)}-${cleaned.slice(7)}`;
                }

                return phone;
            }

            async sendOTP() {
                // Cek cooldown (minimal 30 detik antara pengiriman)
                const otpData = localStorage.getItem('whatsappOtpData');
                if (otpData) {
                    const data = JSON.parse(otpData);
                    const now = new Date().getTime();
                    const timeSinceLastSend = now - data.timestamp;

                    if (timeSinceLastSend < 30000) { // 30 detik cooldown
                        const remaining = Math.ceil((30000 - timeSinceLastSend) / 1000);
                        toastSystem.warning('Tunggu Sebentar', `Tunggu ${remaining} detik sebelum mengirim ulang OTP.`);
                        return;
                    }
                }

                try {
                    // Show loading state
                    const resendLink = document.getElementById('resendLink');
                    if (!this.otpSent) {
                        // First time sending - disable inputs temporarily
                        document.querySelectorAll('.code-input').forEach(input => {
                            input.disabled = true;
                        });
                    }

                    const response = await fetch('../whatsapp_verification.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            action: 'send_otp',
                            phone: this.userPhone,
                            api_key: this.apiKey
                        })
                    });

                    const result = await response.json();

                    if (result.success) {
                        // Simpan data OTP di localStorage
                        const otpData = {
                            phone: this.userPhone,
                            timestamp: new Date().getTime(),
                            sent: true
                        };
                        localStorage.setItem('whatsappOtpData', JSON.stringify(otpData));

                        this.otpSent = true;
                        toastSystem.success('OTP Terkirim!', 'Kode verifikasi telah dikirim ke WhatsApp Anda.');
                        this.timeLeft = this.timerDuration;
                        this.startTimer();

                        // Enable inputs setelah OTP terkirim
                        document.querySelectorAll('.code-input').forEach(input => {
                            input.disabled = false;
                        });

                        // Focus ke input pertama
                        this.focusFirstInput();

                    } else {
                        throw new Error(result.message || 'Gagal mengirim OTP');
                    }
                } catch (error) {
                    console.error('Send OTP Error:', error);
                    toastSystem.error('Gagal Mengirim', 'Tidak dapat mengirim kode OTP. Silakan coba lagi.');

                    // Enable inputs jika gagal
                    document.querySelectorAll('.code-input').forEach(input => {
                        input.disabled = false;
                    });
                }
            }

            async resendOTP() {
                const resendLink = document.getElementById('resendLink');

                if (resendLink.classList.contains('disabled')) {
                    return;
                }

                // Show loading state
                resendLink.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengirim...';
                resendLink.classList.add('disabled');

                try {
                    await this.sendOTP();

                    // Reset UI state
                    resendLink.innerHTML = 'Kirim ulang kode OTP';
                    resendLink.classList.add('disabled');

                } catch (error) {
                    resendLink.innerHTML = 'Kirim ulang kode OTP';
                    resendLink.classList.remove('disabled');
                }
            }

            handleCodeInput(e, index) {
                const input = e.target;
                const value = input.value;

                // Only allow numbers
                if (!/^\d*$/.test(value)) {
                    input.value = '';
                    return;
                }

                if (value.length === 1) {
                    input.classList.add('filled');

                    // Move to next input
                    if (index < this.codeLength - 1) {
                        const nextInput = document.querySelector(`.code-input[data-index="${index + 1}"]`);
                        nextInput.focus();
                    }
                } else if (value.length === 0) {
                    input.classList.remove('filled');
                }

                this.updateVerificationCode();
                this.updateVerifyButton();
            }

            handleKeyDown(e, index) {
                if (e.key === 'Backspace' && e.target.value === '' && index > 0) {
                    const prevInput = document.querySelector(`.code-input[data-index="${index - 1}"]`);
                    prevInput.focus();
                    prevInput.value = '';
                    prevInput.classList.remove('filled');
                }
            }

            handlePaste(e) {
                e.preventDefault();
                const pasteData = e.clipboardData.getData('text');
                const numbers = pasteData.replace(/\D/g, '').slice(0, this.codeLength);

                const codeInputs = document.querySelectorAll('.code-input');
                numbers.split('').forEach((num, index) => {
                    if (codeInputs[index]) {
                        codeInputs[index].value = num;
                        codeInputs[index].classList.add('filled');
                    }
                });

                if (numbers.length === this.codeLength) {
                    document.getElementById('verifyButton').focus();
                } else {
                    const nextEmptyIndex = numbers.length;
                    if (codeInputs[nextEmptyIndex]) {
                        codeInputs[nextEmptyIndex].focus();
                    }
                }

                this.updateVerificationCode();
                this.updateVerifyButton();
            }

            updateVerificationCode() {
                const codeInputs = document.querySelectorAll('.code-input');
                this.verificationCode = Array.from(codeInputs)
                    .map(input => input.value)
                    .join('');
            }

            updateVerifyButton() {
                const verifyButton = document.getElementById('verifyButton');
                verifyButton.disabled = this.verificationCode.length !== this.codeLength;
            }

            startTimer() {
                this.updateTimerDisplay();

                this.timerInterval = setInterval(() => {
                    this.timeLeft--;
                    this.updateTimerDisplay();

                    if (this.timeLeft <= 0) {
                        this.stopTimer();
                        document.getElementById('resendLink').classList.remove('disabled');
                    }
                }, 1000);
            }

            stopTimer() {
                if (this.timerInterval) {
                    clearInterval(this.timerInterval);
                    this.timerInterval = null;
                }
            }

            updateTimerDisplay() {
                const timerElement = document.getElementById('timer');
                const minutes = Math.floor(this.timeLeft / 60);
                const seconds = this.timeLeft % 60;

                timerElement.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

                if (this.timeLeft <= 30) {
                    timerElement.style.color = '#ff453a';
                }

                if (this.timeLeft <= 0) {
                    timerElement.textContent = '00:00';
                    timerElement.classList.add('expired');
                }
            }

            clearInputs() {
                const codeInputs = document.querySelectorAll('.code-input');
                codeInputs.forEach(input => {
                    input.value = '';
                    input.classList.remove('filled');
                });
                this.verificationCode = '';
                this.updateVerifyButton();
            }

            focusFirstInput() {
                const firstInput = document.querySelector('.code-input[data-index="0"]');
                if (firstInput) firstInput.focus();
            }

            async verifyCode() {
                const verifyButton = document.getElementById('verifyButton');

                if (this.verificationCode.length !== this.codeLength) {
                    toastSystem.error('Kode Tidak Lengkap', 'Harap masukkan semua 6 digit kode OTP.');
                    return;
                }

                // Show loading state
                verifyButton.disabled = true;
                verifyButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memverifikasi...';

                try {
                    const response = await fetch('../whatsapp_verification.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            action: 'verify_otp',
                            phone: this.userPhone,
                            code: this.verificationCode
                        })
                    });

                    const result = await response.json();

                    if (result.success) {
                        this.showSuccessState();
                    } else {
                        throw new Error(result.message || 'Kode OTP tidak valid');
                    }
                } catch (error) {
                    toastSystem.error('Verifikasi Gagal', error.message);
                    this.shakeInputs();
                    verifyButton.disabled = false;
                    verifyButton.innerHTML = '<i class="fas fa-check-circle"></i> Verifikasi WhatsApp';
                }
            }

            shakeInputs() {
                const codeInputs = document.querySelectorAll('.code-input');
                codeInputs.forEach(input => {
                    input.classList.add('shake');
                    setTimeout(() => {
                        input.classList.remove('shake');
                    }, 500);
                });
            }

            showSuccessState() {
                document.getElementById('verificationForm').style.display = 'none';
                document.getElementById('successState').style.display = 'block';

                // Update user verification status in localStorage
                const currentUser = JSON.parse(localStorage.getItem('currentUser') || '{}');
                currentUser.whatsapp_verified = true;
                currentUser.verified_at = new Date().toISOString();
                localStorage.setItem('currentUser', JSON.stringify(currentUser));

                // Hapus data OTP dari localStorage
                localStorage.removeItem('whatsappOtpData');

                toastSystem.success('Berhasil!', 'WhatsApp Anda telah berhasil diverifikasi.');
            }

            // Method untuk cleanup
            cleanup() {
                this.stopTimer();
            }
        }

        // Update function goBack untuk cleanup
        function goBack() {
            // Optional: cleanup verification data jika OTP sudah expired
            const otpData = localStorage.getItem('whatsappOtpData');
            if (otpData) {
                const data = JSON.parse(otpData);
                const now = new Date().getTime();
                // Hanya hapus jika OTP sudah expired
                if (now - data.timestamp > 10 * 60 * 1000) {
                    localStorage.removeItem('whatsappOtpData');
                }
            }
            window.location.href = '../auth/login.html';
        }

        function switchToEmail() {
            window.location.href = 'email_verification.html';
        }

        function redirectToDashboard() {
            window.location.href = '../../dashboard';
        }

        function contactSupport() {
            const phoneNumber = '+6281234567890';
            const message = 'Halo, saya butuh bantuan untuk verifikasi WhatsApp di Zona Laut Enterprise.';
            const whatsappUrl = `https://wa.me/${phoneNumber}?text=${encodeURIComponent(message)}`;
            window.open(whatsappUrl, '_blank');
        }

        // Initialize verification system when page loads
        document.addEventListener('DOMContentLoaded', function() {
            const verificationSystem = new WhatsAppVerificationSystem();

            // Simpan reference untuk cleanup jika needed
            window.verificationSystem = verificationSystem;

            // Initialize map for desktop
            if (window.innerWidth > 768) {
                const map = L.map('map').setView([-2.5489, 118.0149], 5);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                }).addTo(map);
            }
        });
    </script>
</body>

</html>