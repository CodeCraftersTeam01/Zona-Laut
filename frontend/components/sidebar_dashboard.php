<nav class="sidebar w-64 bg-bg-white border-r border-border h-screen fixed left-0 top-0 flex flex-col z-40 md:translate-x-0 -translate-x-full shadow-xl" id="sidebar">
    <div class="sidebar-header p-6 border-b border-border flex items-center gap-3">
        <!-- Logo -->
        <div class="sidebar-logo">
            <i class="fas fa-anchor"></i>
        </div>
        <div class="sidebar-brand">
            <div class="font-bold text-lg text-text-dark">Zona Laut</div>
            <div class="text-xs text-text-light mt-1">Marine Monitoring</div>
        </div>
    </div>

    <div class="sidebar-nav flex-1 py-4">
        <?php
        // Mendapatkan path saat ini
        $current_path = $_SERVER['REQUEST_URI'];

        // Daftar menu dengan URL dan deteksi aktif
        $menus = [
            [
                'url' => '../../frontend/dashboard',
                'icon' => 'fas fa-map-marked-alt',
                'text' => 'Dashboard',
                'active' => strpos($current_path, 'dashboard') !== false
            ],
            [
                'url' => '../../frontend/mykapal',
                'icon' => 'fas fa-ship',
                'text' => 'My Kapal',
                'active' => strpos($current_path, 'mykapal') !== false
            ],
            [
                'url' => '../../frontend/tangkapan',
                'icon' => 'fas fa-fish',
                'text' => 'Tangkapan',
                'active' => strpos($current_path, 'tangkapan') !== false
            ],
            [
                'url' => '../../frontend/laporan',
                'icon' => 'fas fa-chart-bar',
                'text' => 'Laporan',
                'active' => strpos($current_path, 'laporan') !== false
            ]
        ];

        // Generate menu items
        foreach ($menus as $menu) {
            $activeClass = $menu['active'] ? 'bg-primary/10 text-primary border-primary' : 'text-text-light hover:bg-bg-light hover:text-primary';
            echo "
            <a href=\"{$menu['url']}\" class=\"nav-item flex items-center gap-3 px-6 py-3 no-underline transition-all duration-300 border-l-3 border-transparent {$activeClass}\">
                <i class=\"{$menu['icon']} w-5 text-center\"></i>
                <span>{$menu['text']}</span>
            </a>";
        }
        ?>
    </div>

    <div class="sidebar-footer p-6 border-t border-border relative">
        <div class="user-info flex items-center gap-3">
            <!-- User Avatar -->
            <div class="user-avatar w-10 h-10 bg-gradient-to-br from-green-400 to-green-600 rounded-full flex items-center justify-center text-white text-sm font-semibold" id="userAvatar">
                <i class="fas fa-user"></i>
            </div>
            <div class="user-details flex-1">
                <div class="user-name font-semibold text-sm text-text-dark" id="userName">Loading...</div>
                <div class="user-role text-xs text-text-light" id="email">Administrator</div>
            </div>
            <button class="user-menu-btn w-10 h-10 border-none bg-bg-white rounded-lg flex items-center justify-center text-text-dark cursor-pointer transition-all duration-300 hover:bg-bg-light" id="userMenuBtn">
                <i class="fas fa-ellipsis-v"></i>
            </button>
        </div>

        <!-- User Menu Popup - POSISI DI ATAS -->
        <div class="user-menu-popup absolute left-6 right-6 bg-white rounded-lg shadow-xl border border-border transform -translate-y-2 z-50 hidden" id="userMenuPopup" style="bottom: 100%; margin-bottom: 8px;">
            <a href="../../frontend/profile" class="user-menu-item flex items-center gap-3 px-4 py-3 text-text-dark no-underline transition-all duration-200 hover:bg-bg-light border-b border-border">
                <i class="fas fa-user-circle w-4 text-center text-gray-500"></i>
                <span class="text-sm">Profil Saya</span>
            </a>
            <a href="../../frontend/help" class="user-menu-item flex items-center gap-3 px-4 py-3 text-text-dark no-underline transition-all duration-200 hover:bg-bg-light border-b border-border">
                <i class="fas fa-question-circle w-4 text-center text-gray-500"></i>
                <span class="text-sm">Bantuan</span>
            </a>
            <a href="#" class="user-menu-item flex items-center gap-3 px-4 py-3 text-red-600 no-underline transition-all duration-200 hover:bg-red-50 logout-btn" id="logoutBtn">
                <i class="fas fa-sign-out-alt w-4 text-center"></i>
                <span class="text-sm">Keluar</span>
            </a>
        </div>
    </div>
</nav>
