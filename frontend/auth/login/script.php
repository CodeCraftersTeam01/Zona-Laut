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

            // Animate in
            requestAnimationFrame(() => {
                toast.classList.add('show');
            });

            // Auto remove
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

        clearAll() {
            this.toasts.forEach(toast => {
                this.hide(toast);
            });
        }
    }

    // Initialize toast system
    const toastSystem = new ToastSystem();

    // Auth System Class
    class AuthSystem {
        constructor() {
            this.baseURL = '../auth.php';
            this.currentUser = null;
            this.checkExistingSession();
        }

        async login(credentials) {
            try {
                const response = await fetch(`${this.baseURL}?action=login`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(credentials)
                });

                const result = await response.json();

                if (result.success) {
                    this.currentUser = result.user;
                    // Simpan session data
                    localStorage.setItem('authToken', result.token);
                    localStorage.setItem('currentUser', JSON.stringify(result.user));

                    if (credentials.remember) {
                        localStorage.setItem('rememberMe', 'true');
                    }
                }

                return result;
            } catch (error) {
                console.error('Error during login:', error);
                return {
                    success: false,
                    message: 'Terjadi kesalahan jaringan. Silakan coba lagi.'
                };
            }
        }

        checkExistingSession() {
            const token = localStorage.getItem('authToken');
            const user = localStorage.getItem('currentUser');

            if (token && user) {
                this.currentUser = JSON.parse(user);
                // Auto redirect jika sudah login
                this.redirectIfLoggedIn();
            }
        }

        redirectIfLoggedIn(redirectUrl = '../../dashboard') {
            if (this.currentUser) {
                window.location.href = redirectUrl;
                return true;
            }
            return false;
        }

        logout() {
            this.currentUser = null;
            localStorage.removeItem('authToken');
            localStorage.removeItem('currentUser');
            localStorage.removeItem('rememberMe');
        }

        isLoggedIn() {
            return this.currentUser !== null;
        }
    }

    // Initialize auth system
    const auth = new AuthSystem();

    // Initialize the map (only on desktop)
    if (window.innerWidth > 768) {
        const map = L.map('map').setView([-2.5489, 118.0149], 5);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        const monitoringPoints = [{
                lat: -6.2088,
                lng: 106.8456,
                name: 'Jakarta Bay'
            },
            {
                lat: -5.1477,
                lng: 119.4327,
                name: 'Makassar Strait'
            },
            {
                lat: -8.4553,
                lng: 115.1035,
                name: 'Bali Waters'
            },
            {
                lat: 1.3521,
                lng: 103.8198,
                name: 'Singapore Strait'
            },
            {
                lat: -0.7893,
                lng: 113.9213,
                name: 'Kalimantan Coast'
            }
        ];

        monitoringPoints.forEach(point => {
            L.marker([point.lat, point.lng])
                .addTo(map)
                .bindPopup(`<b>${point.name}</b><br>Monitoring Station`);
        });
    }

    // Toggle password visibility
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

    // Apple-style checkbox
    document.getElementById('rememberCheckbox').addEventListener('click', function() {
        this.classList.toggle('checked');
    });

    // Network status monitoring
    function checkNetworkStatus() {
        if (!navigator.onLine) {
            toastSystem.error('Koneksi Terputus', 'Periksa koneksi internet Anda dan coba lagi.');
            return false;
        }
        return true;
    }

    // Enhanced error handling
    function handleApiError(error) {
        console.error('API Error:', error);

        if (error.name === 'TypeError' && error.message.includes('Failed to fetch')) {
            return {
                success: false,
                message: 'Koneksi jaringan terputus. Periksa koneksi internet Anda.'
            };
        }

        return {
            success: false,
            message: 'Terjadi kesalahan sistem. Silakan coba lagi dalam beberapa saat.'
        };
    }

    // Form submission dengan fetch
    // Form submission dengan fetch
    document.getElementById('loginForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        if (!checkNetworkStatus()) {
            return;
        }

        // Get form values
        const credentials = {
            username: document.getElementById('username').value.trim(),
            password: document.getElementById('password').value,
            remember: document.getElementById('rememberCheckbox').classList.contains('checked')
        };

        // Basic validation
        if (!credentials.username || !credentials.password) {
            toastSystem.error('Validasi Gagal', 'Harap isi semua field yang diperlukan.');
            return;
        }

        // Show loading state
        const loginButton = document.getElementById('loginButton');
        const loginText = document.getElementById('loginText');
        const originalText = loginText.textContent;

        loginButton.disabled = true;
        loginButton.classList.add('loading');
        loginText.textContent = 'Mengautentikasi...';

        try {
            const result = await auth.login(credentials);

            if (result.success) {
                // Simpan user data ke localStorage
                localStorage.setItem('currentUser', JSON.stringify(result.user));
                localStorage.setItem('authToken', result.token);

                toastSystem.success('Login Berhasil!', 'Mengarahkan ke dashboard...', 2000);

                // Redirect ke dashboard setelah 2 detik
                setTimeout(() => {
                    window.location.href = '../../dashboard';
                }, 2000);

            } else {
                // Handle case where WhatsApp verification is needed
                if (result.needs_verification) {
                    // Simpan user data untuk verifikasi
                    localStorage.setItem('currentUser', JSON.stringify(result.user));
                    localStorage.setItem('authToken', result.token);
                    localStorage.setItem('pendingVerification', 'true');

                    toastSystem.warning('Verifikasi Diperlukan', result.message, 3000);

                    // Redirect ke halaman verifikasi setelah 3 detik
                    setTimeout(() => {
                        window.location.href = result.redirect_url || '../verify';
                    }, 3000);

                } else {
                    // Handle other login failures
                    toastSystem.error('Login Gagal', result.message || 'Username atau password salah.');

                    // Add error animation to inputs
                    document.getElementById('username').classList.add('error-shake');
                    document.getElementById('password').classselass.add('error-shake');
                    setTimeout(() => {
                        document.getElementById('username').classList.remove('error-shake');
                        document.getElementById('password').classList.remove('error-shake');
                    }, 500);
                }
            }
        } catch (error) {
            console.error('Login error:', error);
            const errorResult = handleApiError(error);
            toastSystem.error('Sistem Error', errorResult.message);
        } finally {
            // Reset button state
            loginButton.disabled = false;
            loginButton.classList.remove('loading');
            loginText.textContent = originalText;
        }
    });

    // Add focus effects
    document.querySelectorAll('.form-input').forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('ring-2', 'ring-blue-200');
        });

        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('ring-2', 'ring-blue-200');
        });
    });

    // Add error shake animation
    const errorShakeStyles = document.createElement('style');
    errorShakeStyles.textContent = `
            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                25% { transform: translateX(-5px); }
                75% { transform: translateX(5px); }
            }
            .error-shake {
                animation: shake 0.5s ease-in-out;
                border-color: #ff453a !important;
            }
        `;
    document.head.appendChild(errorShakeStyles);

    // Auto-focus username field
    document.getElementById('username').focus();

    // Demo credentials helper (bisa dihapus di production)
    function fillDemoCredentials() {
        document.getElementById('username').value = 'demo@zonalaut.id';
        document.getElementById('password').value = 'demo123';
        document.getElementById('rememberCheckbox').classList.add('checked');
        toastSystem.info('Demo Credentials', 'Kredensial demo telah diisi. Silakan klik Login.');
    }



    // Check if user is already logged in
    if (auth.isLoggedIn()) {
        toastSystem.info('Session Ditemukan', 'Anda sudah login, mengarahkan ke dashboard...', 2000);
        setTimeout(() => {
            auth.redirectIfLoggedIn();
        }, 2000);
    }
</script>