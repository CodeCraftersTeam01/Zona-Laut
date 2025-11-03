// dashboard.js - Dashboard Management System

// Data counters
let dashboardData = {
    totalDpi: 0,
    totalKapal: 0,
    totalPemilik: 0,
    totalUsers: 0,
    totalTangkapanHariIni: 0,
    totalBeratHariIni: 0,
    totalAktivitas: 0,
    loginHariIni: 0,
    recentActivities: []
};

// Initialize dashboard
async function initializeDashboard() {
    console.log('Initializing dashboard...');
    await fetchAllData();
    updateCounters();
    renderRecentActivities();
    startAutoRefresh();
}

// Fetch all data for dashboard
async function fetchAllData() {
    try {
        console.log('Fetching all dashboard data...');
        await Promise.all([
            fetchDpiCount(),
            fetchKapalCount(),
            fetchPemilikCount(),
            fetchUsersCount(),
            fetchTangkapanHariIni(),
            fetchRecentActivities()
        ]);
        console.log('All data fetched successfully');
    } catch (error) {
        console.error('Error fetching dashboard data:', error);
        showError('Gagal memuat data dashboard');
    }
}

// Fetch DPI count
async function fetchDpiCount() {
    try {
        const response = await fetch('system/dpi.php');
        const data = await response.json();
        console.log('DPI Data:', data);
        if (data.success) {
            dashboardData.totalDpi = data.data.length;
        }
    } catch (error) {
        console.error('Error fetching DPI:', error);
        dashboardData.totalDpi = 0;
    }
}

// Fetch Kapal count
async function fetchKapalCount() {
    try {
        const response = await fetch('system/kapal.php');
        const data = await response.json();
        console.log('Kapal Data:', data);
        if (data.success) {
            dashboardData.totalKapal = data.data.length;
        }
    } catch (error) {
        console.error('Error fetching kapal:', error);
        dashboardData.totalKapal = 0;
    }
}

// Fetch Pemilik count
async function fetchPemilikCount() {
    try {
        const response = await fetch('system/pemilik.php');
        const data = await response.json();
        console.log('Pemilik Data:', data);
        if (data.success) {
            dashboardData.totalPemilik = data.data.length;
        }
    } catch (error) {
        console.error('Error fetching pemilik:', error);
        dashboardData.totalPemilik = 0;
    }
}

// Fetch Users count
async function fetchUsersCount() {
    try {
        // Coba endpoint users.php
        const response = await fetch('system/users.php');
        const data = await response.json();
        console.log('Users Data:', data);
        if (data.success) {
            dashboardData.totalUsers = data.data.length;
        }
    } catch (error) {
        console.log('Users endpoint not available, using pemilik as fallback');
        // Fallback: gunakan data pemilik sebagai users
        dashboardData.totalUsers = dashboardData.totalPemilik;
    }
}

// Fetch tangkapan hari ini
async function fetchTangkapanHariIni() {
    try {
        const today = new Date().toISOString().split('T')[0];
        console.log('Today:', today);
        
        const response = await fetch('system/tangkapan.php');
        const data = await response.json();
        console.log('Tangkapan Data:', data);
        
        if (data.success) {
            const tangkapanHariIni = data.data.filter(item => {
                if (!item.tanggal_tangkapan) return false;
                console.log('Tangkapan date:', item.tanggal_tangkapan, 'Match:', item.tanggal_tangkapan === today);
                return item.tanggal_tangkapan === today;
            });
            
            console.log('Tangkapan hari ini found:', tangkapanHariIni);
            
            dashboardData.totalTangkapanHariIni = tangkapanHariIni.length;
            dashboardData.totalBeratHariIni = tangkapanHariIni.reduce((total, item) => 
                total + parseFloat(item.berat_ikan || 0), 0
            );
        }
    } catch (error) {
        console.error('Error fetching tangkapan:', error);
        dashboardData.totalTangkapanHariIni = 0;
        dashboardData.totalBeratHariIni = 0;
    }
}

// Fetch recent activities
async function fetchRecentActivities() {
    const activities = [];
    const today = new Date().toISOString().split('T')[0];
    
    console.log('=== FETCHING ACTIVITIES FOR:', today, '===');
    
    try {
        // 1. Check pemilik yang login hari ini
        const pemilikResponse = await fetch('system/pemilik.php');
        const pemilikData = await pemilikResponse.json();
        
        if (pemilikData.success) {
            console.log('Total pemilik:', pemilikData.data.length);
            
            const pemilikLoginHariIni = pemilikData.data.filter(pemilik => {
                if (!pemilik.last_login) {
                    console.log('No last_login for:', pemilik.nama_pemilik);
                    return false;
                }
                
                const lastLoginDate = new Date(pemilik.last_login).toISOString().split('T')[0];
                const isToday = lastLoginDate === today;
                
                if (isToday) {
                    console.log('✅ Login today:', pemilik.nama_pemilik, pemilik.last_login);
                }
                
                return isToday;
            });
            
            console.log('Pemilik login hari ini:', pemilikLoginHariIni.length);
            dashboardData.loginHariIni = pemilikLoginHariIni.length;
            
            pemilikLoginHariIni.forEach(pemilik => {
                activities.push({
                    type: 'login',
                    user: pemilik.nama_pemilik,
                    description: `${pemilik.nama_pemilik} melakukan login`,
                    time: pemilik.last_login,
                    icon: 'bi bi-person-check',
                    color: 'success'
                });
            });
        }

        // 2. Check tangkapan hari ini
        const tangkapanResponse = await fetch('system/tangkapan.php');
        const tangkapanData = await tangkapanResponse.json();
        
        if (tangkapanData.success) {
            const tangkapanHariIni = tangkapanData.data.filter(item => {
                if (!item.tanggal_tangkapan) return false;
                const isToday = item.tanggal_tangkapan === today;
                
                if (isToday) {
                    console.log('✅ Tangkapan today:', item.nama_ikan, item.tanggal_tangkapan);
                }
                
                return isToday;
            });
            
            console.log('Tangkapan hari ini:', tangkapanHariIni.length);
            
            tangkapanHariIni.forEach(tangkapan => {
                activities.push({
                    type: 'tangkapan',
                    user: tangkapan.nama_pemilik || 'Unknown',
                    description: `Menangkap ${tangkapan.nama_ikan} (${tangkapan.berat_ikan} kg)`,
                    time: `${tangkapan.tanggal_tangkapan} ${tangkapan.waktu_tangkapan || ''}`,
                    icon: 'bi bi-fish',
                    color: 'primary'
                });
            });
        }

        // 3. Check kapal baru (created_at hari ini)
        const kapalResponse = await fetch('system/kapal.php');
        const kapalData = await kapalResponse.json();
        
        if (kapalData.success) {
            const kapalBaruHariIni = kapalData.data.filter(kapal => {
                if (!kapal.created_at) return false;
                const createdDate = new Date(kapal.created_at).toISOString().split('T')[0];
                const isToday = createdDate === today;
                
                if (isToday) {
                    console.log('✅ Kapal baru:', kapal.nama_kapal, kapal.created_at);
                }
                
                return isToday;
            });
            
            console.log('Kapal baru hari ini:', kapalBaruHariIni.length);
            
            kapalBaruHariIni.forEach(kapal => {
                activities.push({
                    type: 'kapal',
                    user: kapal.nama_pemilik || 'System',
                    description: `Kapal ${kapal.nama_kapal} ditambahkan`,
                    time: kapal.created_at,
                    icon: 'fas fa-ship',
                    color: 'info'
                });
            });
        }

        // 4. Check pemilik baru (created_at hari ini)
        if (pemilikData.success) {
            const pemilikBaruHariIni = pemilikData.data.filter(pemilik => {
                if (!pemilik.created_at) return false;
                const createdDate = new Date(pemilik.created_at).toISOString().split('T')[0];
                const isToday = createdDate === today;
                
                if (isToday) {
                    console.log('✅ Pemilik baru:', pemilik.nama_pemilik, pemilik.created_at);
                }
                
                return isToday;
            });
            
            console.log('Pemilik baru hari ini:', pemilikBaruHariIni.length);
            
            pemilikBaruHariIni.forEach(pemilik => {
                activities.push({
                    type: 'pemilik',
                    user: 'System',
                    description: `Pemilik ${pemilik.nama_pemilik} terdaftar`,
                    time: pemilik.created_at,
                    icon: 'bi bi-person-plus',
                    color: 'warning'
                });
            });
        }

    } catch (error) {
        console.error('Error fetching activities:', error);
    }
    
    // Update total aktivitas
    dashboardData.totalAktivitas = activities.length;
    console.log('Total activities found:', activities.length);
    
    // Sort activities by time (newest first) and limit to 10
    dashboardData.recentActivities = activities
        .sort((a, b) => new Date(b.time) - new Date(a.time))
        .slice(0, 10);
}

// Update counter display dengan animasi
function updateCounters() {
    console.log('Updating counters with data:', dashboardData);
    
    animateCounter('dpi-counter', dashboardData.totalDpi);
    animateCounter('kapal-counters', dashboardData.totalKapal);
    animateCounter('pemilik-counter', dashboardData.totalPemilik);
    animateCounter('users-counter', dashboardData.totalUsers);
    animateCounter('total-aktivitas', dashboardData.totalAktivitas);
    animateCounter('login-hari-ini', dashboardData.loginHariIni);
    
    // Update additional stats
    updateAdditionalStats();
}

// Animate counter
function animateCounter(elementId, targetValue) {
    const element = document.getElementById(elementId);
    if (!element) {
        console.warn('Element not found:', elementId);
        return;
    }

    let current = 0;
    const increment = targetValue / 100;
    const timer = setInterval(() => {
        current += increment;
        if (current >= targetValue) {
            current = targetValue;
            clearInterval(timer);
        }
        element.textContent = Math.floor(current);
    }, 20);
}

// Update additional statistics
function updateAdditionalStats() {
    // Update tangkapan hari ini
    const tangkapanElement = document.getElementById('tangkapan-hari-ini');
    const beratElement = document.getElementById('berat-hari-ini');
    
    if (tangkapanElement) {
        tangkapanElement.textContent = dashboardData.totalTangkapanHariIni;
    }
    if (beratElement) {
        beratElement.textContent = dashboardData.totalBeratHariIni.toFixed(2) + ' kg';
    }
    
    console.log('Additional stats updated - Tangkapan:', dashboardData.totalTangkapanHariIni, 'Berat:', dashboardData.totalBeratHariIni);
}

// Render recent activities
function renderRecentActivities() {
    const activitiesContainer = document.getElementById('recent-activities');
    if (!activitiesContainer) {
        console.warn('Recent activities container not found');
        return;
    }

    console.log('Rendering activities:', dashboardData.recentActivities);

    if (dashboardData.recentActivities.length === 0) {
        activitiesContainer.innerHTML = `
            <div class="text-center text-muted py-4">
                <i class="bi bi-inbox fs-1"></i>
                <p class="mt-2">Tidak ada aktivitas hari ini</p>
            </div>
        `;
        return;
    }

    activitiesContainer.innerHTML = dashboardData.recentActivities.map(activity => `
        <div class="activity-item d-flex align-items-center mb-3 p-3 border rounded">
            <div class="activity-icon me-3">
                <i class="${activity.icon} text-${activity.color} fs-4"></i>
            </div>
            <div class="activity-content flex-grow-1">
                <div class="fw-semibold">${activity.description}</div>
                <small class="text-muted">${formatDateTime(activity.time)}</small>
            </div>
        </div>
    `).join('');
}

// Format date time untuk display
function formatDateTime(dateTimeString) {
    if (!dateTimeString) return 'Waktu tidak diketahui';
    
    try {
        const date = new Date(dateTimeString);
        const now = new Date();
        const diffMs = now - date;
        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMs / 3600000);
        
        if (diffMins < 1) return 'Baru saja';
        if (diffMins < 60) return `${diffMins} menit yang lalu`;
        if (diffHours < 24) return `${diffHours} jam yang lalu`;
        
        return date.toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'short',
            hour: '2-digit',
            minute: '2-digit'
        });
    } catch (error) {
        return 'Waktu tidak valid';
    }
}

// Auto refresh data setiap 2 menit
function startAutoRefresh() {
    setInterval(async () => {
        console.log('Auto-refreshing dashboard data...');
        await fetchAllData();
        updateCounters();
        renderRecentActivities();
    }, 120000); // 2 menit
}

// Error handling
function showError(message) {
    console.error('Dashboard Error:', message);
    // Bisa ditambahkan notifikasi toast di sini
    const activitiesContainer = document.getElementById('recent-activities');
    if (activitiesContainer) {
        activitiesContainer.innerHTML = `
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle"></i>
                ${message}
            </div>
        `;
    }
}

// Check if all required endpoints are available
async function checkEndpoints() {
    const endpoints = [
        'system/dpi.php',
        'system/kapal.php',
        'system/pemilik.php',
        'system/tangkapan.php'
    ];
    
    for (const endpoint of endpoints) {
        try {
            const response = await fetch(endpoint);
            if (!response.ok) {
                console.error(`Endpoint ${endpoint} returned:`, response.status);
            }
        } catch (error) {
            console.error(`Cannot reach endpoint ${endpoint}:`, error);
        }
    }
}


// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, starting dashboard...');
    checkEndpoints().then(() => {
        initializeDashboard();
    });
});