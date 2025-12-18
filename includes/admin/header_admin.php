<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Sesuaikan path ini dengan struktur folder Anda
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/auth.php';

// Require login for all admin pages
requireLogin();
requireRole(['admin', 'superadmin']);

// Check session timeout
checkSessionTimeout();

$settings = get_all_settings();
$current_page = basename($_SERVER['PHP_SELF'], '.php');
$current_dir = basename(dirname($_SERVER['PHP_SELF'])); 

// --- Theme & Color Logic (Server Side) ---
$theme_presets = [
    'indigo' => ['primary' => '#4F46E5', 'secondary' => '#10B981', 'sidebar' => 'from-indigo-900 via-purple-900 to-slate-900'],
    'ocean'  => ['primary' => '#0ea5e9', 'secondary' => '#3b82f6', 'sidebar' => 'from-sky-900 via-blue-900 to-slate-900'],
    'nature' => ['primary' => '#16a34a', 'secondary' => '#ca8a04', 'sidebar' => 'from-green-900 via-emerald-900 to-slate-900'],
    'rose'   => ['primary' => '#e11d48', 'secondary' => '#db2777', 'sidebar' => 'from-rose-900 via-pink-900 to-slate-900'],
    'sunset' => ['primary' => '#ea580c', 'secondary' => '#d97706', 'sidebar' => 'from-orange-900 via-red-900 to-slate-900'],
    'teal'   => ['primary' => '#0d9488', 'secondary' => '#0891b2', 'sidebar' => 'from-teal-900 via-cyan-900 to-slate-900']
];

// Ambil tema dari database
$activeTheme = $settings['app_theme'] ?? 'indigo';
$current_colors = $theme_presets[$activeTheme] ?? $theme_presets['indigo'];
$nprogress_color = $current_colors['secondary']; // Warna Loading Bar

// --- Logic Helper Menu Active ---
function menuActive($dirs, $is_dashboard = false) {
    global $current_dir, $current_page;
    
    $activeClass = 'bg-white/20 text-white shadow-inner backdrop-blur-md border border-white/10 active-menu';
    $inactiveClass = 'text-white/70 hover:bg-white/10 hover:text-white hover:pl-6';

    if ($is_dashboard) {
        return ($current_dir == 'admin' && $current_page == 'index') || ($current_dir == 'data' && $current_page == 'dashboard') || $current_dir == 'dashboard' 
            ? $activeClass 
            : $inactiveClass;
    }

    if (is_array($dirs)) {
        return in_array($current_dir, $dirs) ? $activeClass : $inactiveClass;
    }
    
    return $current_dir == $dirs ? $activeClass : $inactiveClass;
}

$userName = htmlspecialchars(getUserName());
$userRole = htmlspecialchars(getUserRole());
$userAvatar = "https://ui-avatars.com/api/?name=" . urlencode($userName) . "&background=random&color=fff";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?= htmlspecialchars($settings['nama_website'] ?? 'Rental Gedung') ?></title>
    
    <script>
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark')
        } else {
            document.documentElement.classList.remove('dark')
        }
    </script>

    <?php if(!empty($settings['favicon_url'])): ?>
    <link rel="icon" href="/situs-rental-gedung/<?= $settings['favicon_url'] ?>" type="image/x-icon">
    <?php endif; ?>

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/nprogress/0.2.0/nprogress.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/nprogress/0.2.0/nprogress.min.js"></script>

    <script>
        // Pass PHP Theme Data to JS
        const themes = <?= json_encode($theme_presets) ?>;
        const activeThemeName = '<?= $activeTheme ?>';
        const currentTheme = themes[activeThemeName] || themes['indigo'];

        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        primary: currentTheme.primary,
                        secondary: currentTheme.secondary,
                        dark: '#1e1b4b', // Default dark base
                    }
                }
            }
        }
        
        document.addEventListener('alpine:init', () => {
            Alpine.data('layout', () => ({
                // Inisialisasi berdasarkan class HTML (hasil script anti-flicker)
                darkMode: document.documentElement.classList.contains('dark'),
                sidebarOpen: false,
                userDropdownOpen: false,
                
                init() {
                    // Watcher untuk update localStorage saat tombol ditekan
                    this.$watch('darkMode', val => {
                        localStorage.setItem('theme', val ? 'dark' : 'light');
                        if(val) document.documentElement.classList.add('dark');
                        else document.documentElement.classList.remove('dark');
                    });
                    
                    // Reset sidebar on resize
                    window.addEventListener('resize', () => {
                        if(window.innerWidth >= 1024) {
                            this.sidebarOpen = false;
                        }
                    });
                },

                toggleTheme() { this.darkMode = !this.darkMode; },
                toggleSidebar() { this.sidebarOpen = !this.sidebarOpen; },

                confirmLogout() {
                    this.userDropdownOpen = false;
                    Swal.fire({
                        title: 'Konfirmasi Logout',
                        text: "Apakah Anda yakin ingin keluar dari aplikasi?",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        cancelButtonColor: '#64748b',
                        confirmButtonText: 'Ya, Logout',
                        cancelButtonText: 'Batal',
                        reverseButtons: true,
                        background: this.darkMode ? '#1e293b' : '#fff',
                        color: this.darkMode ? '#fff' : '#000'
                    }).then((result) => {
                        if (result.isConfirmed) window.location.href = '?logout=1';
                    });
                }
            }));

            Alpine.data('notifications', () => ({
                notifOpen: false,
                count: 0,
                items: [],
                lastCount: 0,
                
                init() {
                    if (Notification.permission !== "granted") Notification.requestPermission();
                    this.fetchNotifications();
                    setInterval(() => { this.fetchNotifications(); }, 10000);
                },

                async fetchNotifications() {
                    try {
                        const response = await fetch('/situs-rental-gedung/admin/api/check_notifications.php');
                        const result = await response.json();

                        if (result.status === 'success') {
                            this.count = result.count;
                            this.items = result.data;

                            if (this.count > 0 && this.count > this.lastCount) {
                                const audio = document.getElementById('notif-sound');
                                if(audio) audio.play().catch(e => console.log('Audio blocked'));
                                
                                if (Notification.permission === "granted") {
                                    new Notification("Order Baru Masuk!", {
                                        body: `Ada ${this.count} booking menunggu konfirmasi.`,
                                        icon: '/situs-rental-gedung/assets/logo.png'
                                    });
                                }
                            }
                            this.lastCount = this.count;
                        }
                    } catch (error) {
                        console.error('Error fetching notifications:', error);
                    }
                }
            }));
        });
    </script>
    
    <style>
        /* DYNAMIC NPROGRESS COLOR BASED ON DATABASE SETTINGS */
        #nprogress .bar { background: <?= $nprogress_color ?> !important; height: 3px !important; }
        #nprogress .peg { box-shadow: 0 0 10px <?= $nprogress_color ?>, 0 0 5px <?= $nprogress_color ?> !important; }
        #nprogress .spinner-icon { border-top-color: <?= $nprogress_color ?> !important; border-left-color: <?= $nprogress_color ?> !important; }
        
        /* Smooth Transition for Main Content */
        #main-content { transition: opacity 0.3s ease-in-out; }
        .fade-out { opacity: 0.5; pointer-events: none; }
        
        /* Utils */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        [x-cloak] { display: none !important; }
        
        /* Pastikan background dark instant */
        html.dark body { background-color: #0f172a; }
    </style>
</head>

<body x-data="layout" id="body-app"
      class="bg-slate-50 dark:bg-slate-900 font-sans text-slate-800 dark:text-slate-100 antialiased selection:bg-primary selection:text-white overflow-hidden transition-colors duration-300">

    <div x-data="notifications" class="flex h-screen overflow-hidden relative">

        <div x-show="sidebarOpen" 
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="sidebarOpen = false"
             class="fixed inset-0 bg-slate-900/80 z-40 lg:hidden backdrop-blur-sm"
             style="display: none;"></div>

        <aside class="fixed lg:static inset-y-0 left-0 z-50 flex h-full w-72 flex-col bg-gradient-to-b text-white shadow-2xl transition-transform duration-300 transform lg:transform-none <?= htmlspecialchars($current_colors['sidebar']) ?>"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
            
            <div class="flex items-center justify-center h-24 border-b border-white/10 bg-white/5 backdrop-blur-sm">
                <div class="flex items-center gap-3">
                    <?php if(!empty($settings['logo_url'])): ?>
                        <img src="/situs-rental-gedung/<?= $settings['logo_url'] ?>" class="h-10 w-auto object-contain bg-white/10 rounded-lg p-1">
                    <?php else: ?>
                        <div class="h-10 w-10 rounded-xl bg-gradient-to-tr from-primary to-secondary flex items-center justify-center shadow-lg">
                            <i class="fa-solid fa-building text-lg text-white"></i>
                        </div>
                    <?php endif; ?>
                    <div>
                        <h1 class="text-lg font-bold tracking-tight leading-tight">
                            <?= htmlspecialchars($settings['nama_website'] ?? 'Rental Gedung') ?>
                        </h1>
                        <p class="text-[10px] text-slate-400 uppercase tracking-widest">Admin Panel</p>
                    </div>
                </div>
            </div>

            <nav class="flex-1 overflow-y-auto py-6 px-4 space-y-1 no-scrollbar" id="sidebar-nav">
                
                <p class="px-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2 mt-2">Main</p>
                <a href="/situs-rental-gedung/admin/data/dashboard/" data-spa class="nav-link flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold transition-all group <?= menuActive(['dashboard', 'admin'], true) ?>">
                    <i class="fa-solid fa-gauge-high w-5 group-hover:text-white transition-colors"></i> Dashboard
                </a>

                <p class="px-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2 mt-6">Transaksi</p>
                <a href="/situs-rental-gedung/admin/data/booking/" data-spa class="nav-link flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition-all group <?= menuActive('booking') ?>">
                    <div class="relative">
                        <i class="fa-solid fa-calendar-check w-5 group-hover:text-white transition-colors"></i>
                        <span x-show="count > 0" x-transition class="absolute -top-1 -right-1 h-2.5 w-2.5 rounded-full bg-rose-500 border-2 border-indigo-900"></span>
                    </div>
                    <span>Booking Masuk</span>
                    <span x-show="count > 0" x-text="count" class="ml-auto bg-rose-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-lg shadow-rose-500/30"></span>
                </a>

                <p class="px-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2 mt-6">Master Data</p>
                <a href="/situs-rental-gedung/admin/data/gedung/" data-spa class="nav-link flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition-all group <?= menuActive('gedung') ?>">
                    <i class="fa-solid fa-building-user w-5 group-hover:text-white transition-colors"></i> Data Gedung
                </a>
                <a href="/situs-rental-gedung/admin/data/kategori/" data-spa class="nav-link flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition-all group <?= menuActive('kategori') ?>">
                    <i class="fa-solid fa-layer-group w-5 group-hover:text-white transition-colors"></i> Kategori Gedung
                </a>
                <a href="/situs-rental-gedung/admin/data/fasilitas/" data-spa class="nav-link flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition-all group <?= menuActive('fasilitas') ?>">
                    <i class="fa-solid fa-couch w-5 group-hover:text-white transition-colors"></i> Fasilitas
                </a>
                <a href="/situs-rental-gedung/admin/data/promo/" data-spa class="nav-link flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition-all group <?= menuActive('promo') ?>">
                    <i class="fa-solid fa-tags w-5 group-hover:text-white transition-colors"></i> Promo & Diskon
                </a>
                <a href="/situs-rental-gedung/admin/data/pelanggan/" data-spa class="nav-link flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition-all group <?= menuActive('pelanggan') ?>">
                    <i class="fa-solid fa-users w-5 group-hover:text-white transition-colors"></i> Pelanggan
                </a>
                <a href="/situs-rental-gedung/admin/data/users/" data-spa class="nav-link flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition-all group <?= menuActive('users') ?>">
                    <i class="fa-solid fa-user-shield w-5 group-hover:text-white transition-colors"></i> Manajemen Pengguna
                </a>

                <p class="px-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2 mt-6">Operasional</p>
                <a href="/situs-rental-gedung/admin/data/jadwal/" data-spa class="nav-link flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition-all group <?= menuActive('jadwal') ?>">
                    <i class="fa-solid fa-clock w-5 group-hover:text-white transition-colors"></i> Jadwal Gedung
                </a>
                <a href="/situs-rental-gedung/admin/data/reviews/" data-spa class="nav-link flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition-all group <?= menuActive('reviews') ?>">
                    <i class="fa-solid fa-star w-5 group-hover:text-white transition-colors"></i> Ulasan & Rating
                </a>

                <p class="px-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2 mt-6">Laporan & Analitik</p>
                <a href="/situs-rental-gedung/admin/data/analisis/" data-spa class="nav-link flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition-all group <?= menuActive('analisis') ?>">
                    <i class="fa-solid fa-chart-pie w-5 group-hover:text-white transition-colors"></i> Analisis Bisnis
                </a>
                <a href="/situs-rental-gedung/admin/laporan/" data-spa class="nav-link flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition-all group <?= menuActive('laporan') ?>">
                    <i class="fa-solid fa-file-invoice-dollar w-5 group-hover:text-white transition-colors"></i> Laporan Transaksi
                </a>

                <p class="px-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2 mt-6">System</p>
                <a href="/situs-rental-gedung/admin/data/trash/" data-spa class="nav-link flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition-all group <?= menuActive('trash') ?>">
                    <i class="fa-solid fa-trash-can w-5 group-hover:text-white transition-colors"></i> Data Sampah
                </a>
                <a href="/situs-rental-gedung/admin/data/logs/" data-spa class="nav-link flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition-all group <?= menuActive('logs') ?>">
                    <i class="fa-solid fa-list-check w-5 group-hover:text-white transition-colors"></i> Log Aktivitas
                </a>
                
                <?php if (isSuperAdmin()): ?>
                <a href="/situs-rental-gedung/admin/settings/index.php" data-spa class="nav-link flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition-all group <?= menuActive('settings') ?>">
                    <i class="fa-solid fa-gear w-5 group-hover:text-white transition-colors"></i> Pengaturan
                </a>
                <?php endif; ?>
            </nav>

            <div class="border-t border-white/10 bg-black/20 p-4 backdrop-blur-sm">
                <div class="flex items-center gap-3">
                    <img class="h-10 w-10 rounded-full border-2 border-white/30 shadow-md" src="<?= $userAvatar ?>" alt="Admin">
                    <div class="flex-1 overflow-hidden">
                        <p class="text-sm font-semibold text-white truncate"><?= $userName ?></p>
                        <p class="text-xs text-slate-400 capitalize truncate"><?= $userRole ?></p>
                    </div>
                    <button @click="confirmLogout" class="rounded-full p-2 text-slate-400 hover:bg-white/10 hover:text-white transition-colors" title="Logout">
                        <i class="fa-solid fa-right-from-bracket"></i>
                    </button>
                </div>
            </div>
        </aside>

        <div class="flex h-full flex-1 flex-col overflow-hidden bg-slate-50 dark:bg-slate-900 relative transition-colors duration-300">
            
            <header class="flex h-20 items-center justify-between bg-white/80 dark:bg-slate-900/80 px-8 backdrop-blur-md sticky top-0 z-30 border-b border-slate-200/60 dark:border-slate-700/60 transition-all duration-300">
                <div class="flex items-center gap-4">
                    <button @click="toggleSidebar" class="rounded-xl bg-slate-100 dark:bg-slate-800 p-2 text-slate-600 dark:text-slate-300 lg:hidden hover:bg-indigo-50 hover:text-indigo-600 transition-colors">
                        <i class="fa-solid fa-bars text-xl"></i>
                    </button>
                    
                    <div class="hidden md:flex items-center gap-2 rounded-2xl bg-slate-100 dark:bg-slate-800 px-4 py-2.5 transition-all focus-within:ring-2 focus-within:ring-primary/20 focus-within:bg-white dark:focus-within:bg-slate-800 w-64 lg:w-96 shadow-sm">
                        <i class="fa-solid fa-magnifying-glass text-slate-400"></i>
                        <input type="text" placeholder="Cari data..." class="bg-transparent text-sm font-medium text-slate-600 dark:text-slate-200 outline-none placeholder:text-slate-400 w-full">
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <button @click="toggleTheme()" class="relative rounded-xl bg-white dark:bg-slate-800 p-2.5 text-slate-500 dark:text-slate-300 shadow-sm border border-slate-100 dark:border-slate-700 hover:bg-indigo-50 hover:text-primary transition-all">
                        <i x-show="!darkMode" class="fa-regular fa-moon text-lg"></i>
                        <i x-show="darkMode" class="fa-regular fa-sun text-lg text-yellow-400"></i>
                    </button>

                    <button @click="notifOpen = !notifOpen" class="relative rounded-xl bg-white dark:bg-slate-800 p-2.5 text-slate-500 dark:text-slate-300 shadow-sm border border-slate-100 dark:border-slate-700 hover:bg-indigo-50 hover:text-primary transition-all">
                        <i class="fa-regular fa-bell text-lg"></i>
                        <span x-show="count > 0" x-transition class="absolute top-2 right-2 h-2.5 w-2.5 rounded-full bg-rose-500 ring-2 ring-white animate-pulse"></span>
                    </button>

                    <div class="h-8 w-[1px] bg-slate-200 dark:bg-slate-700 hidden sm:block"></div>
                    
                    <div class="relative">
                        <button @click="userDropdownOpen = !userDropdownOpen" 
                                @click.outside="userDropdownOpen = false"
                                class="flex items-center gap-2 cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-800 p-1.5 rounded-xl transition-colors focus:outline-none">
                            <div class="text-right hidden sm:block">
                                <p class="text-sm font-bold text-slate-700 dark:text-slate-200"><?= $userName ?></p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Online</p>
                            </div>
                            <img class="h-9 w-9 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm" src="<?= $userAvatar ?>" alt="">
                        </button>
                        
                        <div x-show="userDropdownOpen" 
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-56 bg-white dark:bg-slate-800 rounded-xl shadow-xl border border-slate-100 dark:border-slate-700 py-2 z-50 origin-top-right" 
                             style="display: none;">
                            
                            <a href="/situs-rental-gedung/admin/data/profile/" data-spa class="block px-4 py-2 text-sm text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 hover:text-primary dark:hover:text-primary transition-colors">
                                <i class="fa-regular fa-id-card mr-2 w-5 text-center"></i> Profile Saya
                            </a>
                            <a href="/situs-rental-gedung/public/" class="block px-4 py-2 text-sm text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 hover:text-primary dark:hover:text-primary transition-colors">
                                <i class="fa-solid fa-globe mr-2 w-5 text-center"></i> Lihat Website
                            </a>
                            <div class="border-t border-slate-100 dark:border-slate-700 my-1"></div>
                            <button @click="confirmLogout" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 transition-colors">
                                <i class="fa-solid fa-power-off mr-2 w-5 text-center"></i> Logout
                            </button>
                        </div>
                    </div>
                </div>
            </header>

            <main id="main-content" class="flex-1 overflow-y-auto p-4 lg:p-8 no-scrollbar relative"
                  x-data="{ show: false }" 
                  x-init="setTimeout(() => show = true, 100)"
                  x-show="show"
                  x-transition:enter="transition ease-out duration-500"
                  x-transition:enter-start="opacity-0 translate-y-4"
                  x-transition:enter-end="opacity-100 translate-y-0">

<audio id="notif-sound" src="https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3" preload="auto"></audio>

<div x-show="notifOpen" 
     style="display: none;"
     class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/60 backdrop-blur-md"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">
        
    <div @click.outside="notifOpen = false"
         class="bg-white dark:bg-slate-800 w-full max-w-md rounded-2xl shadow-2xl transform transition-all m-4"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95 translate-y-4"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95">
            
        <div class="flex justify-between items-center px-6 py-4 border-b border-slate-100 dark:border-slate-700">
            <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">Notifikasi Masuk</h3>
            <button @click="notifOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                <i class="fa-solid fa-times text-xl"></i>
            </button>
        </div>
        
        <div class="max-h-[60vh] overflow-y-auto no-scrollbar p-2">
            <template x-if="count > 0">
                <div>
                    <template x-for="item in items" :key="item.id">
                        <a :href="'/situs-rental-gedung/admin/data/booking/detail.php?id=' + item.id" data-spa @click="notifOpen = false" class="block p-4 mb-2 bg-white dark:bg-slate-700 border border-slate-100 dark:border-slate-600 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-600 hover:border-primary/30 hover:shadow-md transition-all group">
                            <div class="flex justify-between items-start mb-1">
                                <div class="flex items-center gap-2">
                                    <div class="h-2 w-2 rounded-full bg-primary animate-pulse"></div>
                                    <p class="text-sm font-bold text-slate-800 dark:text-white group-hover:text-primary transition-colors" x-text="item.nama_lengkap"></p>
                                </div>
                                <span class="text-[10px] font-medium text-slate-400 dark:text-slate-300 bg-slate-100 dark:bg-slate-600 px-2 py-0.5 rounded-full" x-text="item.time_ago"></span>
                            </div>
                            <div class="flex justify-between items-center mt-2">
                                <p class="text-xs text-slate-500 dark:text-slate-400">Kode: <span class="font-mono font-semibold text-slate-700 dark:text-slate-200" x-text="item.booking_code"></span></p>
                                <span class="text-[10px] font-bold bg-amber-100 text-amber-600 dark:bg-amber-900/50 dark:text-amber-400 px-2 py-1 rounded-lg">Menunggu Konfirmasi</span>
                            </div>
                        </a>
                    </template>
                </div>
            </template>

            <template x-if="count === 0">
                <div class="px-6 py-12 text-center text-slate-400 text-sm">
                    <div class="bg-slate-50 dark:bg-slate-700 h-16 w-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fa-regular fa-bell-slash text-2xl text-slate-300 dark:text-slate-500"></i>
                    </div>
                    <p class="font-medium text-slate-500 dark:text-slate-400">Tidak ada notifikasi baru</p>
                    <p class="text-xs mt-1 dark:text-slate-500">Semua pesanan sudah ditangani.</p>
                </div>
            </template>
        </div>

        <div class="border-t border-slate-100 dark:border-slate-700 p-4 bg-slate-50 dark:bg-slate-800/50 rounded-b-2xl flex justify-center">
            <a href="/situs-rental-gedung/admin/data/booking/" data-spa @click="notifOpen = false" class="text-sm font-bold text-primary hover:underline">
                Lihat Semua Data Booking
            </a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Definisi Class Active/Inactive untuk JS (sama dengan PHP helper)
    const activeClasses = ['bg-white/20', 'text-white', 'shadow-inner', 'backdrop-blur-md', 'border', 'border-white/10', 'active-menu'];
    const inactiveClasses = ['text-white/70', 'hover:bg-white/10', 'hover:text-white', 'hover:pl-6'];

    // Function untuk intercept klik
    function handleSpaNavigation(e) {
        // Cari elemen anchor terdekat yang memiliki atribut data-spa
        const link = e.target.closest('a[data-spa]');
        if (!link) return;

        e.preventDefault();
        const url = link.href;
        
        // Jangan reload jika url sama
        if (url === window.location.href) return;

        // Visual Feedback
        NProgress.start();
        const mainContent = document.getElementById('main-content');
        mainContent.classList.add('fade-out');

        // Update Active State di Sidebar secara instan
        document.querySelectorAll('#sidebar-nav a').forEach(el => {
            el.classList.remove(...activeClasses);
            el.classList.add(...inactiveClasses);
        });
        link.classList.remove(...inactiveClasses);
        link.classList.add(...activeClasses);

        // Fetch Halaman Baru
        fetch(url)
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.text();
            })
            .then(html => {
                // Parse HTML string menjadi DOM
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                // Ambil konten <main> dari halaman tujuan
                const newContent = doc.querySelector('#main-content');
                
                if (newContent) {
                    // Update Title Browser
                    document.title = doc.title;
                    
                    // Update URL Browser tanpa reload
                    window.history.pushState({}, '', url);
                    
                    // Ganti isi Main Content
                    mainContent.innerHTML = newContent.innerHTML;
                    
                    // Re-Execute Scripts yang ada di dalam main content (PENTING untuk Chart.js / Datatables)
                    const scripts = mainContent.querySelectorAll('script');
                    scripts.forEach(oldScript => {
                        const newScript = document.createElement('script');
                        Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                        newScript.appendChild(document.createTextNode(oldScript.innerHTML));
                        oldScript.parentNode.replaceChild(newScript, oldScript);
                    });

                    // Scroll to top
                    mainContent.scrollTop = 0;
                    
                    // Close sidebar on mobile if open
                    if (window.innerWidth < 1024) {
                        const bodyData = document.querySelector('[x-data="layout"]');
                        if (bodyData && bodyData.__x) {
                            bodyData.__x.$data.sidebarOpen = false;
                        }
                    }
                } else {
                    // Fallback jika struktur halaman tidak cocok
                    window.location.href = url;
                }
            })
            .catch(error => {
                console.error('SPA Navigation Error:', error);
                window.location.href = url; // Fallback reload biasa jika error
            })
            .finally(() => {
                mainContent.classList.remove('fade-out');
                NProgress.done();
            });
    }

    // Attach event listener ke body (delegation)
    document.body.addEventListener('click', handleSpaNavigation);

    // Handle tombol Back/Forward browser
    window.addEventListener('popstate', () => {
        window.location.reload(); // Reload penuh saat back button agar state aman
    });
});
</script>