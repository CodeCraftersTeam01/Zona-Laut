<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Zona Laut</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="shortcut icon" href="../../images/logo.png" type="image/x-icon">
    <link rel="stylesheet" href="../../css/login.css">
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
                        <h3 style="margin-bottom: 1rem; font-weight: 600;">Fitur Utama</h3>
                        <div class="feature-grid">
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="fas fa-map-marked-alt"></i>
                                </div>
                                <span>Pemetaan Zona Laut</span>
                            </div>
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <span>Analisis Data Real-time</span>
                            </div>
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="fas fa-ship"></i>
                                </div>
                                <span>Pelacakan Kapal</span>
                            </div>
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="fas fa-bell"></i>
                                </div>
                                <span>Notifikasi & Alert</span>
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
                            <span>Secure</span>
                        </div>
                    </div>
                    <p>© 2025 Zona Laut Enterprise. All rights reserved. v3.2.1 • Build 2456</p>
                </div>
            </div>
        </section>
        
        <!-- Login Section -->
        <section class="login-section">
            <div class="login-container">
                <div class="login-header fade-in">
                    <div class="login-icon">
                        <i class="fas fa-sign-in-alt"></i>
                    </div>
                    <h2>Masuk ke Dashboard</h2>
                    <p>Akses sistem monitoring Zona Laut</p>
                </div>
                
                <form class="login-form" id="loginForm">
                    <div class="form-group fade-in delay-1">
                        <label class="form-label" for="username">
                            <i class="fas fa-user"></i>
                            Username atau Email
                        </label>
                        <div class="input-wrapper">
                            <input type="text" id="username" name="username" class="form-input" placeholder="username@zonalaut.id" required>
                        </div>
                    </div>
                    
                    <div class="form-group fade-in delay-2">
                        <label class="form-label" for="password">
                            <i class="fas fa-lock"></i>
                            Password
                        </label>
                        <div class="input-wrapper">
                            <input type="password" id="password" name="password" class="form-input" placeholder="••••••••" required>
                            <button type="button" class="password-toggle" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-options fade-in delay-3">
                        <div class="checkbox-container">
                            <div class="apple-checkbox" id="rememberCheckbox"></div>
                            <label for="rememberCheckbox">Tetap masuk</label>
                        </div>
                        <a href="#" class="forgot-password">Lupa Password?</a>
                    </div>
                    
                    <button type="submit" class="login-button fade-in delay-4" id="loginButton">
                        <i class="fas fa-sign-in-alt"></i>
                        <span id="loginText">Masuk ke Dashboard</span>
                    </button>
                    
                    <div class="security-notice fade-in delay-4">
                        <i class="fas fa-lock"></i>
                        <span>SSL Terenkripsi</span>
                    </div>
                </form>
                
                <div class="login-footer fade-in delay-4">
                    <p>Belum memiliki akun? 
                        <a href="frontend/auth/registrasi" class="register-link">Daftar Enterprise</a>
                    </p>
                </div>
            </div>
        </section>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="../../js/script.js"></script>
    <?php include 'script.php'?>
</body>
</html>