<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi - Zona Laut</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="../../css/register.css">
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
                        <i class="fas fa-water"></i>
                    </div>
                    <div class="brand-text">
                        <h1>Zona Laut</h1>
                        <p>Enterprise Monitoring Platform</p>
                    </div>
                </div>
                
                <div class="map-content">
                    <div class="map-stats fade-in delay-1">
                        <div class="stat-item">
                            <div class="stat-value">500+</div>
                            <div class="stat-label">Pengguna Aktif</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">99.9%</div>
                            <div class="stat-label">Uptime</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">24/7</div>
                            <div class="stat-label">Monitoring</div>
                        </div>
                    </div>
                    
                    <div class="map-features fade-in delay-2">
                        <h3 style="margin-bottom: 1rem; font-weight: 600;">Bergabunglah Dengan Kami</h3>
                        <div class="feature-grid">
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="fas fa-map-marked-alt"></i>
                                </div>
                                <span>Pemetaan Zona Laut Real-time</span>
                            </div>
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <span>Analisis Data Canggih</span>
                            </div>
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="fas fa-ship"></i>
                                </div>
                                <span>Pelacakan Kapal Akurat</span>
                            </div>
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <span>Keamanan Enterprise</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="map-footer fade-in delay-3">
                    <div class="status-indicator">
                        <div class="status-dot"></div>
                        <span>System Status: Online</span>
                        <div class="security-badge">
                            <i class="fas fa-shield-alt"></i>
                            <span>Secure Registration</span>
                        </div>
                    </div>
                    <p>© 2025 Zona Laut Enterprise. All rights reserved. v3.2.1 • Build 2456</p>
                </div>
            </div>
        </section>
        
        <!-- Registration Section -->
        <section class="registration-section">
            <div class="registration-container">
                <div class="registration-header fade-in">
                    <div class="registration-icon">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <h2>Daftar Zona Laut</h2>
                    <p>Bergabunglah dengan platform monitoring perikanan terdepan</p>
                </div>
                
                <!-- Progress Indicator -->
                <div class="mb-4 fade-in delay-1">
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-sm font-medium text-gray-700">Progress Pendaftaran</span>
                        <span class="text-sm font-bold" id="progressText">0%</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" id="progressFill"></div>
                    </div>
                </div>
                
                <form class="registration-form" id="registrationForm">
                    <!-- Username Field -->
                    <div class="form-group fade-in delay-1">
                        <label class="form-label" for="username">
                            <i class="fas fa-user"></i>
                            Username
                        </label>
                        <div class="input-wrapper">
                            <input type="text" id="username" class="form-input input-status" placeholder="Masukkan username" required>
                        </div>
                        <div class="validation-item pending" id="usernameLength">
                            <i class="fas fa-circle text-xs"></i>
                            <span>Minimal 3 karakter</span>
                        </div>
                        <div class="validation-item pending" id="usernameFormat">
                            <i class="fas fa-circle text-xs"></i>
                            <span>Hanya huruf dan angka</span>
                        </div>
                    </div>
                    
                    <!-- Email Field -->
                    <div class="form-group fade-in delay-1">
                        <label class="form-label" for="email">
                            <i class="fas fa-envelope"></i>
                            Email
                        </label>
                        <div class="input-wrapper">
                            <input type="email" id="email" class="form-input input-status" placeholder="nama@email.com" required>
                        </div>
                        <div class="validation-item pending" id="emailFormat">
                            <i class="fas fa-circle text-xs"></i>
                            <span>Format email valid</span>
                        </div>
                    </div>
                    
                    <!-- Phone Field -->
                    <div class="form-group fade-in delay-2">
                        <label class="form-label" for="phone">
                            <i class="fas fa-phone"></i>
                            Nomor Telepon
                        </label>
                        <div class="input-wrapper">
                            <input type="tel" id="phone" class="form-input input-status" placeholder="08xxxxxxxxxx" required>
                        </div>
                        <div class="validation-item pending" id="phoneStart">
                            <i class="fas fa-circle text-xs"></i>
                            <span>Dimulai dengan 08</span>
                        </div>
                        <div class="validation-item pending" id="phoneLength">
                            <i class="fas fa-circle text-xs"></i>
                            <span>10-12 digit</span>
                        </div>
                    </div>
                    
                    <!-- Password Field -->
                    <div class="form-group fade-in delay-2">
                        <label class="form-label" for="password">
                            <i class="fas fa-lock"></i>
                            Password
                        </label>
                        <div class="input-wrapper">
                            <input type="password" id="password" class="form-input input-status" placeholder="Minimal 8 karakter" required>
                            <button type="button" class="password-toggle" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        
                        <!-- Password Strength Meter -->
                        <div class="mt-2">
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-xs font-medium">Kekuatan Password:</span>
                                <span class="password-strength strength-weak" id="passwordStrengthText">Lemah</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" id="passwordStrength" style="width: 0%"></div>
                            </div>
                        </div>
                        
                        <div class="validation-item pending" id="passwordLength">
                            <i class="fas fa-circle text-xs"></i>
                            <span>Minimal 8 karakter</span>
                        </div>
                        <div class="validation-item pending" id="passwordNumber">
                            <i class="fas fa-circle text-xs"></i>
                            <span>Mengandung angka</span>
                        </div>
                        <div class="validation-item pending" id="passwordUppercase">
                            <i class="fas fa-circle text-xs"></i>
                            <span>Mengandung huruf besar</span>
                        </div>
                        <div class="validation-item pending" id="passwordSpecial">
                            <i class="fas fa-circle text-xs"></i>
                            <span>Mengandung karakter spesial</span>
                        </div>
                    </div>
                    
                    <!-- Confirm Password Field -->
                    <div class="form-group fade-in delay-3">
                        <label class="form-label" for="confirmPassword">
                            <i class="fas fa-lock"></i>
                            Konfirmasi Password
                        </label>
                        <div class="input-wrapper">
                            <input type="password" id="confirmPassword" class="form-input input-status" placeholder="Ketik ulang password" required>
                            <button type="button" class="password-toggle" id="toggleConfirmPassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="validation-item pending" id="passwordMatch">
                            <i class="fas fa-circle text-xs"></i>
                            <span>Password harus cocok</span>
                        </div>
                    </div>
                    
                    <!-- Address Field -->
                    <div class="form-group fade-in delay-3">
                        <label class="form-label" for="address">
                            <i class="fas fa-map-marker-alt"></i>
                            Alamat
                        </label>
                        <div class="input-wrapper">
                            <textarea id="address" class="form-input input-status" placeholder="Masukkan alamat lengkap" rows="2" required></textarea>
                        </div>
                        <div class="validation-item pending" id="addressLength">
                            <i class="fas fa-circle text-xs"></i>
                            <span>Minimal 10 karakter</span>
                        </div>
                    </div>
                    
                    <!-- Terms and Conditions -->
                    <div class="checkbox-container fade-in delay-4">
                        <div class="apple-checkbox" id="termsCheckbox"></div>
                        <label class="checkbox-label">
                            Saya menyetujui <a href="#">Syarat & Ketentuan</a> dan <a href="#">Kebijakan Privasi</a>
                        </label>
                    </div>
                    
                    <button type="submit" class="register-button fade-in delay-4" id="submitButton" disabled>
                        <i class="fas fa-user-plus"></i>
                        <span id="submitText">Daftar Sekarang</span>
                    </button>
                    
                    <div class="security-notice fade-in delay-4">
                        <i class="fas fa-lock"></i>
                        <span>Data Anda aman dan terenkripsi</span>
                    </div>
                </form>
                
                <div class="registration-footer fade-in delay-4">
                    <p>Sudah punya akun? 
                        <a href="frontend/auth/login" class="login-link">Masuk di sini</a>
                    </p>
                </div>
            </div>
        </section>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <?php include 'script.php'?>
    <script src="../../js/script.js"></script>
</body>
</html>