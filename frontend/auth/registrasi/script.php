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

        // Success Modal System
        class SuccessModal {
            constructor() {
                this.modal = this.createModal();
            }

            createModal() {
                const modal = document.createElement('div');
                modal.className = 'success-modal';
                modal.innerHTML = `
                    <div class="success-modal-content">
                        <div class="success-icon">
                            <i class="fas fa-check"></i>
                        </div>
                        <h2 class="success-title" id="successModalTitle">Registrasi Berhasil!</h2>
                        <p class="success-message" id="successModalMessage">Akun Anda telah berhasil dibuat. Mengarahkan ke halaman login...</p>
                        <div class="success-progress">
                            <div class="success-progress-bar"></div>
                        </div>
                    </div>
                `;

                document.body.appendChild(modal);
                return modal;
            }

            show(title, message, redirectUrl = '../login', delay = 3000) {
                document.getElementById('successModalTitle').textContent = title;
                document.getElementById('successModalMessage').textContent = message;
                
                this.modal.classList.add('show');

                // Redirect after delay
                setTimeout(() => {
                    this.hide();
                    window.location.href = redirectUrl;
                }, delay);
            }

            hide() {
                this.modal.classList.remove('show');
            }
        }

        // Initialize systems
        const toastSystem = new ToastSystem();
        const successModal = new SuccessModal();

        // Initialize the map (only on desktop)
        if (window.innerWidth > 768) {
            const map = L.map('map').setView([-2.5489, 118.0149], 5);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);
            
            const monitoringPoints = [
                { lat: -6.2088, lng: 106.8456, name: 'Jakarta Bay' },
                { lat: -5.1477, lng: 119.4327, name: 'Makassar Strait' },
                { lat: -8.4553, lng: 115.1035, name: 'Bali Waters' },
                { lat: 1.3521, lng: 103.8198, name: 'Singapore Strait' },
                { lat: -0.7893, lng: 113.9213, name: 'Kalimantan Coast' }
            ];
            
            monitoringPoints.forEach(point => {
                L.marker([point.lat, point.lng])
                    .addTo(map)
                    .bindPopup(`<b>${point.name}</b><br>Monitoring Station`);
            });
        }
        
        // Form validation state
        const formState = {
            username: { valid: false, length: false, format: false },
            email: { valid: false, format: false },
            phone: { valid: false, start: false, length: false },
            password: { valid: false, length: false, number: false, uppercase: false, special: false, strength: 0 },
            confirmPassword: { valid: false, match: false },
            address: { valid: false, length: false },
            terms: false
        };

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
        
        document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('confirmPassword');
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
        document.getElementById('termsCheckbox').addEventListener('click', function() {
            this.classList.toggle('checked');
            formState.terms = this.classList.contains('checked');
            calculateFormProgress();
        });
        
        // Update validation item
        function updateValidationItem(itemId, isValid) {
            const item = document.getElementById(itemId);
            if (isValid) {
                item.classList.add('valid');
                item.classList.remove('invalid', 'pending');
                item.innerHTML = '<i class="fas fa-check-circle text-xs"></i><span>' + item.textContent + '</span>';
            } else {
                item.classList.add('invalid');
                item.classList.remove('valid', 'pending');
                item.innerHTML = '<i class="fas fa-times-circle text-xs"></i><span>' + item.textContent + '</span>';
            }
        }
        
        // Calculate password strength
        function calculatePasswordStrength(password) {
            let strength = 0;

            if (password.length >= 8) strength += 25;
            if (password.length >= 12) strength += 10;
            if (/[a-z]/.test(password)) strength += 10;
            if (/[A-Z]/.test(password)) strength += 15;
            if (/[0-9]/.test(password)) strength += 15;
            if (/[^a-zA-Z0-9]/.test(password)) strength += 25;

            return Math.min(strength, 100);
        }
        
        // Update password strength display
        function updatePasswordStrength(password) {
            const strength = calculatePasswordStrength(password);
            const strengthBar = document.getElementById('passwordStrength');
            const strengthText = document.getElementById('passwordStrengthText');

            strengthBar.style.width = strength + '%';

            if (strength < 40) {
                strengthBar.style.background = '#ff453a';
                strengthText.textContent = 'Lemah';
                strengthText.className = 'password-strength strength-weak';
            } else if (strength < 70) {
                strengthBar.style.background = '#ff9f0a';
                strengthText.textContent = 'Cukup';
                strengthText.className = 'password-strength strength-medium';
            } else if (strength < 90) {
                strengthBar.style.background = '#30d158';
                strengthText.textContent = 'Kuat';
                strengthText.className = 'password-strength strength-strong';
            } else {
                strengthBar.style.background = '#00b83e';
                strengthText.textContent = 'Sangat Kuat';
                strengthText.className = 'password-strength strength-very-strong';
            }

            return strength;
        }
        
        // Calculate overall form progress
        function calculateFormProgress() {
            const fields = [
                formState.username.valid,
                formState.email.valid,
                formState.phone.valid,
                formState.password.valid,
                formState.confirmPassword.valid,
                formState.address.valid,
                formState.terms
            ];

            const validFields = fields.filter(Boolean).length;
            const progress = Math.round((validFields / fields.length) * 100);

            document.getElementById('progressFill').style.width = progress + '%';
            document.getElementById('progressText').textContent = progress + '%';

            // Enable/disable submit button
            const submitButton = document.getElementById('submitButton');
            const allValid = validFields === fields.length;
            submitButton.disabled = !allValid;

            return progress;
        }
        
        // Validation functions
        function validateUsername(value) {
            const lengthValid = value.length >= 3;
            const formatValid = /^[a-zA-Z0-9]+$/.test(value);
            return { lengthValid, formatValid, overallValid: lengthValid && formatValid };
        }

        function validateEmail(value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            const formatValid = emailRegex.test(value);
            return { formatValid, overallValid: formatValid };
        }

        function validatePhone(value) {
            const startValid = value.startsWith('08');
            const lengthValid = value.length >= 10 && value.length <= 12;
            const numericValid = /^[0-9]+$/.test(value);
            const overallValid = startValid && lengthValid && numericValid;
            return { startValid, lengthValid, numericValid, overallValid };
        }

        function validatePassword(value) {
            const lengthValid = value.length >= 8;
            const numberValid = /[0-9]/.test(value);
            const uppercaseValid = /[A-Z]/.test(value);
            const specialValid = /[^a-zA-Z0-9]/.test(value);
            const overallValid = lengthValid && numberValid;
            return { lengthValid, numberValid, uppercaseValid, specialValid, overallValid };
        }

        // Update input status
        function updateInputStatus(inputElement, isValid, value) {
            if (isValid) {
                inputElement.classList.add('valid');
                inputElement.classList.remove('invalid');
            } else if (value.length > 0) {
                inputElement.classList.add('invalid');
                inputElement.classList.remove('valid');
            } else {
                inputElement.classList.remove('valid', 'invalid');
            }
        }

        // Enhanced event listeners with debouncing
        let validationTimeout;

        // Username validation
        document.getElementById('username').addEventListener('input', function(e) {
            clearTimeout(validationTimeout);
            validationTimeout = setTimeout(() => {
                const value = e.target.value;
                const validation = validateUsername(value);
                
                formState.username.length = validation.lengthValid;
                formState.username.format = validation.formatValid;
                formState.username.valid = validation.overallValid;
                
                updateValidationItem('usernameLength', validation.lengthValid);
                updateValidationItem('usernameFormat', validation.formatValid);
                updateInputStatus(this, validation.overallValid, value);
                
                calculateFormProgress();
            }, 300);
        });
        
        // Email validation
        document.getElementById('email').addEventListener('input', function(e) {
            clearTimeout(validationTimeout);
            validationTimeout = setTimeout(() => {
                const value = e.target.value;
                const validation = validateEmail(value);

                formState.email.format = validation.formatValid;
                formState.email.valid = validation.overallValid;

                updateValidationItem('emailFormat', validation.formatValid);
                updateInputStatus(this, validation.overallValid, value);

                calculateFormProgress();
            }, 300);
        });
        
        // Phone validation
        document.getElementById('phone').addEventListener('input', function(e) {
            clearTimeout(validationTimeout);
            validationTimeout = setTimeout(() => {
                const value = e.target.value;
                const validation = validatePhone(value);

                formState.phone.start = validation.startValid;
                formState.phone.length = validation.lengthValid;
                formState.phone.valid = validation.overallValid;

                updateValidationItem('phoneStart', validation.startValid);
                updateValidationItem('phoneLength', validation.lengthValid);
                updateInputStatus(this, validation.overallValid, value);

                calculateFormProgress();
            }, 300);
        });
        
        // Password validation
        document.getElementById('password').addEventListener('input', function(e) {
            clearTimeout(validationTimeout);
            validationTimeout = setTimeout(() => {
                const value = e.target.value;
                const validation = validatePassword(value);

                formState.password.length = validation.lengthValid;
                formState.password.number = validation.numberValid;
                formState.password.uppercase = validation.uppercaseValid;
                formState.password.special = validation.specialValid;
                formState.password.valid = validation.overallValid;

                updateValidationItem('passwordLength', validation.lengthValid);
                updateValidationItem('passwordNumber', validation.numberValid);
                updateValidationItem('passwordUppercase', validation.uppercaseValid);
                updateValidationItem('passwordSpecial', validation.specialValid);
                
                const strength = updatePasswordStrength(value);
                formState.password.strength = strength;
                
                updateInputStatus(this, validation.overallValid, value);

                document.getElementById('confirmPassword').dispatchEvent(new Event('input'));
                calculateFormProgress();
            }, 300);
        });
        
        // Confirm password validation
        document.getElementById('confirmPassword').addEventListener('input', function(e) {
            clearTimeout(validationTimeout);
            validationTimeout = setTimeout(() => {
                const value = e.target.value;
                const passwordValue = document.getElementById('password').value;
                const matchValid = value === passwordValue && value !== '';

                formState.confirmPassword.match = matchValid;
                formState.confirmPassword.valid = matchValid;

                updateValidationItem('passwordMatch', matchValid);
                updateInputStatus(this, matchValid, value);

                calculateFormProgress();
            }, 300);
        });
        
        // Address validation
        document.getElementById('address').addEventListener('input', function(e) {
            clearTimeout(validationTimeout);
            validationTimeout = setTimeout(() => {
                const value = e.target.value;
                const lengthValid = value.trim().length >= 10;

                formState.address.length = lengthValid;
                formState.address.valid = lengthValid;

                updateValidationItem('addressLength', lengthValid);
                updateInputStatus(this, lengthValid, value);

                calculateFormProgress();
            }, 300);
        });

        // Network status monitoring
        function checkNetworkStatus() {
            if (!navigator.onLine) {
                toastSystem.error('Koneksi Terputus', 'Periksa koneksi internet Anda dan coba lagi.');
                return false;
            }
            return true;
        }

        // Final form validation function
        function validateForm(data) {
            return formState.username.valid &&
                formState.email.valid &&
                formState.phone.valid &&
                formState.password.valid &&
                formState.confirmPassword.valid &&
                formState.address.valid &&
                formState.terms;
        }

        // Reset form validation
        function resetFormValidation() {
            // Reset form state
            Object.keys(formState).forEach(key => {
                if (typeof formState[key] === 'object') {
                    Object.keys(formState[key]).forEach(subKey => {
                        formState[key][subKey] = false;
                    });
                } else {
                    formState[key] = false;
                }
            });
            
            // Reset UI
            document.querySelectorAll('.validation-item').forEach(item => {
                item.className = 'validation-item pending';
                const icon = item.querySelector('i');
                if (icon) {
                    icon.className = 'fas fa-circle text-xs';
                }
            });
            
            document.querySelectorAll('.form-input').forEach(input => {
                input.classList.remove('valid', 'invalid');
            });
            
            document.getElementById('passwordStrength').style.width = '0%';
            document.getElementById('passwordStrengthText').textContent = 'Lemah';
            document.getElementById('passwordStrengthText').className = 'password-strength strength-weak';
            
            calculateFormProgress();
        }

        // Form submission dengan fetch
        // Form submission dengan fetch
// Form submission dengan fetch - PERBAIKAN

function testing(){
    document.getElementById('registrationForm').dispatchEvent(new Event('submit'));
}
document.getElementById('registrationForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    console.log("Form submitted"); // Debug log
    
    if (!checkNetworkStatus()) {
        console.log("Network check failed");
        return;
    }
    
    // Get form values
    const formData = {
        username: document.getElementById('username').value.trim(),
        email: document.getElementById('email').value.trim(),
        phone: document.getElementById('phone').value.trim(),
        password: document.getElementById('password').value,
        address: document.getElementById('address').value.trim()
    };

    console.log("Form data:", formData); // Debug log

    // Final validation
    if (!validateForm(formData)) {
        console.log("Form validation failed");
        toastSystem.error('Validasi Gagal', 'Harap perbaiki semua field yang masih error sebelum melanjutkan.');
        return;
    }

    console.log("Form validation passed"); // Debug log

    // Show loading state
    const submitButton = document.getElementById('submitButton');
    const submitText = document.getElementById('submitText');
    const originalText = submitText.textContent;
    
    submitButton.disabled = true;
    submitButton.classList.add('loading');
    submitText.textContent = 'Mendaftarkan...';

    try {
        console.log("Sending request to server..."); // Debug log
        
        const response = await fetch('../auth.php?action=register', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        });

        console.log("Response received:", response); // Debug log

        const result = await response.json();
        console.log("Result:", result); // Debug log

        if (result.success) {
            console.log("Registration successful"); // Debug log
            
            // Simpan user data ke localStorage untuk verifikasi
            if (result.user) {
                localStorage.setItem('currentUser', JSON.stringify(result.user));
                localStorage.setItem('authToken', result.token);
                localStorage.setItem('pendingVerification', 'true');
            }

            // Jika perlu verifikasi WhatsApp
            if (result.needs_verification) {
                successModal.show(
                    'Registrasi Berhasil!',
                    result.message || 'Akun Anda telah berhasil dibuat. Mengarahkan ke verifikasi WhatsApp...',
                    result.redirect_url || '../verify',
                    3000
                );
            } else {
                // Jika tidak perlu verifikasi (fallback)
                successModal.show(
                    'Registrasi Berhasil!',
                    result.message || 'Akun Anda telah berhasil dibuat. Mengarahkan ke login...',
                    '../login',
                    3000
                );
            }
            
            // Reset form
            document.getElementById('registrationForm').reset();
            document.getElementById('termsCheckbox').classList.remove('checked');
            
            // Reset all validation states
            resetFormValidation();
            
        } else {
            console.log("Registration failed:", result.message); // Debug log
            toastSystem.error('Registrasi Gagal', result.message || 'Terjadi kesalahan saat mendaftar.');
        }
    } catch (error) {
        console.error('Registration error:', error);
        
        if (error.name === 'TypeError' && error.message.includes('Failed to fetch')) {
            toastSystem.error('Koneksi Gagal', 'Tidak dapat terhubung ke server. Periksa koneksi internet Anda dan coba lagi.');
        } else if (error.name === 'SyntaxError') {
            toastSystem.error('Response Error', 'Terjadi kesalahan dalam memproses response server.');
        } else {
            toastSystem.error('Sistem Error', 'Terjadi kesalahan tak terduga. Silakan refresh halaman dan coba lagi.');
        }
    } finally {
        // Reset button state
        submitButton.disabled = false;
        submitButton.classList.remove('loading');
        submitText.textContent = originalText;
    }
});
        
        // Initialize progress
        calculateFormProgress();

        // Demo toast buttons (bisa dihapus di production)
        // toastSystem.success('Success Demo', 'Ini adalah contoh pesan sukses!');
        // toastSystem.error('Error Demo', 'Ini adalah contoh pesan error!');
        // toastSystem.warning('Warning Demo', 'Ini adalah contoh pesan peringatan!');
        // toastSystem.info('Info Demo', 'Ini adalah contoh pesan informasi!');
    </script>