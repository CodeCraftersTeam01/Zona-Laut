const sidebar = document.getElementById('sidebar');
const mobileMenuBtn = document.getElementById('mobileMenuBtn');

mobileMenuBtn.addEventListener('click', function () {
    sidebar.classList.toggle('-translate-x-full');

    // Cek apakah berada di URL dashboard
    const isDashboard = window.location.href.includes('/frontend/dashboard');

    if (isDashboard) {
        // Di dashboard - toggle antara top-5 dan top-20
        this.classList.toggle('top-5');
        this.classList.toggle('top-20');
    }
    // Jika bukan dashboard, tidak melakukan apa-apa (tetap top-5)
});

// Set posisi awal saat halaman dimuat - HANYA EKSEKUSI SEKALI
document.addEventListener('DOMContentLoaded', function () {
    const isDashboard = window.location.href.includes('/frontend/dashboard');

    if (isDashboard) {
        // Hanya di dashboard: set ke top-20 sekali saja
        mobileMenuBtn.classList.add('top-20');
        mobileMenuBtn.classList.remove('top-5');
    } else {
        // Di luar dashboard: set ke top-5
        mobileMenuBtn.classList.add('top-5');
        mobileMenuBtn.classList.remove('top-20');
    }
    // Load user data from localStorage
    function loadUserData() {
        try {
            const userData = localStorage.getItem('currentUser');
            if (userData) {
                const user = JSON.parse(userData);
                const userNameElement = document.getElementById('userName');
                const emailElement = document.getElementById('email');
                const userAvatarElement = document.getElementById('userAvatar');

                // Set user name
                userNameElement.textContent = user.name || user.username || 'User';

                // Set avatar with initials
                if (user.name || user.username || user.email) {
                    const name = user.name || user.username;
                    const initials = name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
                    userAvatarElement.innerHTML = initials;
                    const email = user.email || '';
                    const limit = 15; // jumlah karakter yang mau ditampilkan
                    emailElement.textContent = email.length > limit ? email.slice(0, limit) + '...' : email;

                    userAvatarElement.classList.remove('fa-user');
                }
            } else {
                document.getElementById('userName').textContent = 'Guest';
            }
        } catch (error) {
            console.error('Error loading user data:', error);
            document.getElementById('userName').textContent = 'Error';
        }
    }

    // Toggle user menu popup
    function toggleUserMenu() {
        const userMenu = document.getElementById('userMenuPopup');
        userMenu.classList.toggle('hidden');
    }

    // Close user menu when clicking outside
    function closeUserMenu(event) {
        const userMenu = document.getElementById('userMenuPopup');
        const userMenuBtn = document.getElementById('userMenuBtn');

        if (!userMenu.contains(event.target) && !userMenuBtn.contains(event.target)) {
            userMenu.classList.add('hidden');
        }
    }

    // Logout function
    function logout() {
        if (confirm('Apakah Anda yakin ingin keluar?')) {
            // Clear user data from localStorage
            localStorage.removeItem('currentUser');
            localStorage.removeItem('authToken');
            localStorage.removeItem('userData');

            // Redirect to login page
            window.location.href = '../auth/login';
        }
    }

    // Initialize
    loadUserData();

    // Event listeners
    document.getElementById('userMenuBtn').addEventListener('click', function (e) {
        e.stopPropagation();
        toggleUserMenu();
    });

    document.addEventListener('click', closeUserMenu);

    document.getElementById('logoutBtn').addEventListener('click', function (e) {
        e.preventDefault();
        logout();
    });

    // Close menu when clicking on menu items (except logout)
    document.querySelectorAll('.user-menu-item:not(.logout-btn)').forEach(item => {
        item.addEventListener('click', function () {
            document.getElementById('userMenuPopup').classList.add('hidden');
        });
    });

    // Prevent menu from closing when clicking inside the menu
    document.getElementById('userMenuPopup').addEventListener('click', function (e) {
        e.stopPropagation();
    });

});