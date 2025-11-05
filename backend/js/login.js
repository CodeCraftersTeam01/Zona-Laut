// Toggle password visibility
document.getElementById('togglePassword').addEventListener('click', function () {
    const passwordInput = document.getElementById('password');
    const icon = this.querySelector('i');

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
});

// Form submission
document.getElementById('loginForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value.trim();

    const form = e.target;
    const formData = new FormData(form);
    formData.append('email', email);
    formData.append('password', password);

    fetch('./system/authenticate.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            const alertDiv = document.querySelector('.alert');
            alertDiv.classList.remove('alert-info', 'alert-danger', 'alert-success');

            if (data.success) {
                alertDiv.classList.add('alert-success');
                alertDiv.innerHTML = '<i class="fas fa-check-circle me-2"></i> Login berhasil! Mengalihkan...';
                setTimeout(() => {
                    window.location.href = 'dashboard.html';
                }, 1500);
            } else {
                alertDiv.classList.add('alert-danger');
                alertDiv.innerHTML = '<i class="fas fa-exclamation-circle me-2"></i> Email atau password salah!';
                document.getElementById('password').value = '';
            }
        })
        .catch(error => {
            console.error('Error during authentication:', error);
        });
});
