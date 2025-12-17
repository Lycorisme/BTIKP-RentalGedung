<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/auth.php';

// Require login for all admin pages
requireLogin();
requireRole(['admin', 'superadmin']);

// Check session timeout
checkSessionTimeout();

$settings = get_all_settings();
$current_page = basename($_SERVER['PHP_SELF'], '.php');
// Ambil direktori parent untuk highlight menu active
$current_dir = basename(dirname($_SERVER['PHP_SELF'])); 

// --- Logic Helper Menu Active ---
function menuActive($dirs, $is_dashboard = false) {
    global $current_dir, $current_page;
    
    // Khusus dashboard
    if ($is_dashboard) {
        return ($current_dir == 'admin' && $current_page == 'index') || ($current_dir == 'data' && $current_page == 'dashboard') || $current_dir == 'dashboard' 
            ? 'bg-white/20 text-white shadow-inner backdrop-blur-md border border-white/10' 
            : 'text-white/70 hover:bg-white/10 hover:text-white hover:pl-6';
    }

    // Untuk menu lain (array atau string)
    if (is_array($dirs)) {
        return in_array($current_dir, $dirs) 
            ? 'bg-white/20 text-white shadow-inner backdrop-blur-md border border-white/10' 
            : 'text-white/70 hover:bg-white/10 hover:text-white hover:pl-6';
    }
    
    return $current_dir == $dirs 
        ? 'bg-white/20 text-white shadow-inner backdrop-blur-md border border-white/10' 
        : 'text-white/70 hover:bg-white/10 hover:text-white hover:pl-6';
}

$userName = htmlspecialchars(getUserName());
$userRole = htmlspecialchars(getUserRole());
// Gunakan avatar dari folder uploads jika user punya, jika tidak pakai UI Avatars
$userAvatar = "https://ui-avatars.com/api/?name=" . urlencode($userName) . "&background=random&color=fff";

// --- Logic Helper Tema ---
$activeTheme = $settings['app_theme'] ?? 'indigo';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?= htmlspecialchars($settings['nama_website'] ?? 'Rental Gedung') ?></title>
    
    <?php if(!empty($settings['favicon_url'])): ?>
    <link rel="icon" href="/situs-rental-gedung/<?= $settings['favicon_url'] ?>" type="image/x-icon">
    <?php endif; ?>

    <script src="https://cdn.tailwindcss.com"></script>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Data Preset Tema
        const themes = {
            'indigo': { primary: '#4F46E5', secondary: '#10B981', dark: '#1e1b4b', sidebar: 'from-indigo-900 via-purple-900 to-slate-900' },
            'ocean': { primary: '#0ea5e9', secondary: '#3b82f6', dark: '#0f172a', sidebar: 'from-sky-900 via-blue-900 to-slate-900' },
            'nature': { primary: '#16a34a', secondary: '#ca8a04', dark: '#14532d', sidebar: 'from-green-900 via-emerald-900 to-slate-900' },
            'rose': { primary: '#e11d48', secondary: '#db2777', dark: '#881337', sidebar: 'from-rose-900 via-pink-900 to-slate-900' },
            'sunset': { primary: '#ea580c', secondary: '#d97706', dark: '#431407', sidebar: 'from-orange-900 via-red-900 to-slate-900' },
            'teal': { primary: '#0d9488', secondary: '#0891b2', dark: '#115e59', sidebar: 'from-teal-900 via-cyan-900 to-slate-900' }
        };

        const activeThemeName = '<?= $activeTheme ?>';
        const currentTheme = themes[activeThemeName] || themes['indigo'];

        tailwind.config = {
            darkMode: 'class', // --- 1. Aktivasi Dark Mode Manual ---
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        primary: currentTheme.primary,
                        secondary: currentTheme.secondary,
                        dark: currentTheme.dark,
                    },
                    animation: {
                        'fade-in-up': 'fadeInUp 0.5s ease-out forwards',
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    },
                    keyframes: {
                        fadeInUp: {
                            '0%': { opacity: '0', transform: 'translateY(10px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        }
                    }
                }
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const sidebar = document.getElementById('sidebar');
            if(sidebar) {
                sidebar.classList.add(...currentTheme.sidebar.split(' '));
            }
        });

        // --- 3. Logic JavaScript (Penyimpanan & Init) ---
        // Cek LocalStorage saat loading agar tidak kedip
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    
    <style>
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .animate-entry { animation: fadeInUp 0.4s ease-out forwards; }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-900 font-sans text-slate-800 dark:text-slate-100 antialiased selection:bg-primary selection:text-white overflow-hidden transition-colors duration-300">

    <div class="flex h-screen overflow-hidden">

        <aside id="sidebar" class="absolute z-50 -translate-x-full transition-transform duration-300 lg:static lg:translate-x-0 flex h-full w-72 flex-col bg-gradient-to-b text-white shadow-2xl">
            
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

            <nav class="flex-1 overflow-y-auto py-6 px-4 space-y-1 no-scrollbar">
                
                <p class="px-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2 mt-2">Main</p>
                
                <a href="/situs-rental-gedung/admin/data/dashboard/" 
                   class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold transition-all group <?= menuActive(['dashboard', 'admin'], true) ?>">
                    <i class="fa-solid fa-gauge-high w-5 group-hover:text-white transition-colors"></i>
                    Dashboard
                </a>
                <p class="px-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2 mt-6">Transaksi</p>

                <a href="/situs-rental-gedung/admin/data/booking/" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition-all group <?= menuActive('booking') ?>">
                    <div class="relative">
                        <i class="fa-solid fa-calendar-check w-5 group-hover:text-white transition-colors"></i>
                        <span id="sidebar-notif-dot" class="hidden absolute -top-1 -right-1 h-2.5 w-2.5 rounded-full bg-rose-500 border-2 border-indigo-900"></span>
                    </div>
                    <span>Booking Masuk</span>
                    <span id="sidebar-notif-count" class="hidden ml-auto bg-rose-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-lg shadow-rose-500/30">0</span>
                </a>

                <p class="px-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2 mt-6">Master Data</p>

                <a href="/situs-rental-gedung/admin/data/gedung/" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition-all group <?= menuActive('gedung') ?>">
                    <i class="fa-solid fa-building-user w-5 group-hover:text-white transition-colors"></i> Data Gedung
                </a>

                <a href="/situs-rental-gedung/admin/data/fasilitas/" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition-all group <?= menuActive('fasilitas') ?>">
                    <i class="fa-solid fa-couch w-5 group-hover:text-white transition-colors"></i> Fasilitas
                </a>

                <a href="/situs-rental-gedung/admin/data/promo/" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition-all group <?= menuActive('promo') ?>">
                    <i class="fa-solid fa-tags w-5 group-hover:text-white transition-colors"></i> Promo & Diskon
                </a>

                <a href="/situs-rental-gedung/admin/data/pelanggan/" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition-all group <?= menuActive('pelanggan') ?>">
                    <i class="fa-solid fa-users w-5 group-hover:text-white transition-colors"></i> Pelanggan
                </a>

                <a href="/situs-rental-gedung/admin/data/users/" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition-all group <?= menuActive('users') ?>">
                    <i class="fa-solid fa-user-shield w-5 group-hover:text-white transition-colors"></i> Manajemen Pengguna
                </a>

                <p class="px-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2 mt-6">Operasional</p>

                <a href="/situs-rental-gedung/admin/data/jadwal/" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition-all group <?= menuActive('jadwal') ?>">
                    <i class="fa-solid fa-clock w-5 group-hover:text-white transition-colors"></i> Jadwal Gedung
                </a>

                <a href="/situs-rental-gedung/admin/data/reviews/" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition-all group <?= menuActive('reviews') ?>">
                    <i class="fa-solid fa-star w-5 group-hover:text-white transition-colors"></i> Ulasan & Rating
                </a>

                <p class="px-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2 mt-6">Laporan & Analitik</p>

                <a href="/situs-rental-gedung/admin/data/analisis/" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition-all group <?= menuActive('analisis') ?>">
                    <i class="fa-solid fa-chart-pie w-5 group-hover:text-white transition-colors"></i> Analisis Bisnis
                </a>

                <a href="/situs-rental-gedung/admin/laporan/" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition-all group <?= menuActive('laporan') ?>">
                    <i class="fa-solid fa-file-invoice-dollar w-5 group-hover:text-white transition-colors"></i> Laporan Transaksi
                </a>

                <p class="px-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2 mt-6">System</p>

                <a href="/situs-rental-gedung/admin/data/trash/" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition-all group <?= menuActive('trash') ?>">
                    <i class="fa-solid fa-trash-can w-5 group-hover:text-white transition-colors"></i> Data Sampah
                </a>

                <a href="/situs-rental-gedung/admin/data/logs/" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition-all group <?= menuActive('logs') ?>">
                    <i class="fa-solid fa-list-check w-5 group-hover:text-white transition-colors"></i> Log Aktivitas
                </a>
                
                <?php if (isSuperAdmin()): ?>
                <a href="/situs-rental-gedung/admin/settings/index.php" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition-all group <?= menuActive('settings') ?>">
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
                    <button onclick="confirmLogout(event)" class="rounded-full p-2 text-slate-400 hover:bg-white/10 hover:text-white transition-colors" title="Logout">
                        <i class="fa-solid fa-right-from-bracket"></i>
                    </button>
                </div>
            </div>
        </aside>

        <div class="flex h-full flex-1 flex-col overflow-hidden bg-slate-50 dark:bg-slate-900 relative transition-colors duration-300">
            
            <header class="flex h-20 items-center justify-between bg-white/80 dark:bg-slate-900/80 px-8 backdrop-blur-md sticky top-0 z-40 border-b border-slate-200/60 dark:border-slate-700/60 transition-all duration-300">
                <div class="flex items-center gap-4">
                    <button id="mobile-menu-btn" class="rounded-xl bg-slate-100 dark:bg-slate-800 p-2 text-slate-600 dark:text-slate-300 lg:hidden hover:bg-indigo-50 hover:text-indigo-600 transition-colors">
                        <i class="fa-solid fa-bars text-xl"></i>
                    </button>
                    
                    <div class="hidden md:flex items-center gap-2 rounded-2xl bg-slate-100 dark:bg-slate-800 px-4 py-2.5 transition-all focus-within:ring-2 focus-within:ring-primary/20 focus-within:bg-white dark:focus-within:bg-slate-800 w-64 lg:w-96 shadow-sm">
                        <i class="fa-solid fa-magnifying-glass text-slate-400"></i>
                        <input type="text" placeholder="Cari data..." class="bg-transparent text-sm font-medium text-slate-600 dark:text-slate-200 outline-none placeholder:text-slate-400 w-full">
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    
                    <button onclick="toggleTheme()" class="relative rounded-xl bg-white dark:bg-slate-800 p-2.5 text-slate-500 dark:text-slate-300 shadow-sm border border-slate-100 dark:border-slate-700 hover:bg-indigo-50 hover:text-primary transition-all" title="Ganti Tema">
                        <i id="theme-icon-moon" class="fa-regular fa-moon text-lg block dark:hidden"></i>
                        <i id="theme-icon-sun" class="fa-regular fa-sun text-lg hidden dark:block text-yellow-400"></i>
                    </button>

                    <button onclick="toggleNotifModal()" class="relative rounded-xl bg-white dark:bg-slate-800 p-2.5 text-slate-500 dark:text-slate-300 shadow-sm border border-slate-100 dark:border-slate-700 hover:bg-indigo-50 hover:text-primary transition-all">
                        <i class="fa-regular fa-bell text-lg"></i>
                        <span id="header-notif-badge" class="hidden absolute top-2 right-2 h-2.5 w-2.5 rounded-full bg-rose-500 ring-2 ring-white animate-pulse"></span>
                    </button>

                    <div class="h-8 w-[1px] bg-slate-200 dark:bg-slate-700 hidden sm:block"></div>
                    
                    <div class="relative">
                        <button onclick="toggleUserDropdown()" class="flex items-center gap-2 cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-800 p-1.5 rounded-xl transition-colors focus:outline-none">
                            <div class="text-right hidden sm:block">
                                <p class="text-sm font-bold text-slate-700 dark:text-slate-200"><?= $userName ?></p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Online</p>
                            </div>
                            <img class="h-9 w-9 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm" src="<?= $userAvatar ?>" alt="">
                        </button>
                        
                        <div id="user-menu-dropdown" class="absolute right-0 mt-2 w-56 bg-white dark:bg-slate-800 rounded-xl shadow-xl border border-slate-100 dark:border-slate-700 py-2 invisible opacity-0 transition-all z-50 transform origin-top-right">
                            
                            <a href="/situs-rental-gedung/admin/data/profile/" class="block px-4 py-2 text-sm text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 hover:text-primary dark:hover:text-primary transition-colors">
                                <i class="fa-regular fa-id-card mr-2 w-5 text-center"></i> Profile Saya
                            </a>

                            <a href="/situs-rental-gedung/public/" class="block px-4 py-2 text-sm text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 hover:text-primary dark:hover:text-primary transition-colors">
                                <i class="fa-solid fa-globe mr-2 w-5 text-center"></i> Lihat Website
                            </a>
                            
                            <div class="border-t border-slate-100 dark:border-slate-700 my-1"></div>
                            
                            <button onclick="confirmLogout(event)" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 transition-colors">
                                <i class="fa-solid fa-power-off mr-2 w-5 text-center"></i> Logout
                            </button>
                        </div>
                    </div>
                </div>
            </header>

            <div id="notif-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm opacity-0 invisible transition-all duration-300">
                <div class="bg-white dark:bg-slate-800 w-full max-w-md rounded-2xl shadow-2xl transform scale-95 transition-all duration-300" id="notif-modal-content">
                    <div class="flex justify-between items-center px-6 py-4 border-b border-slate-100 dark:border-slate-700">
                        <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">Notifikasi Masuk</h3>
                        <button onclick="toggleNotifModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                            <i class="fa-solid fa-times text-xl"></i>
                        </button>
                    </div>
                    
                    <div id="notif-list-modal" class="max-h-[60vh] overflow-y-auto no-scrollbar p-2">
                        <div class="px-6 py-12 text-center text-slate-400 text-sm">
                            <i class="fa-regular fa-bell-slash text-4xl mb-3 opacity-50 block mx-auto"></i>
                            <p>Tidak ada notifikasi baru</p>
                        </div>
                    </div>

                    <div class="border-t border-slate-100 dark:border-slate-700 p-4 bg-slate-50 dark:bg-slate-800/50 rounded-b-2xl flex justify-center">
                        <a href="/situs-rental-gedung/admin/data/booking/" class="text-sm font-bold text-primary hover:underline">
                            Lihat Semua Data Booking
                        </a>
                    </div>
                </div>
            </div>

            <main class="flex-1 overflow-y-auto p-4 lg:p-8 no-scrollbar animate-entry">

<audio id="notif-sound" src="https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3" preload="auto"></audio>

<script>
    // --- Logic Helper Dark Mode ---
    function toggleTheme() {
        if (document.documentElement.classList.contains('dark')) {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('theme', 'light');
        } else {
            document.documentElement.classList.add('dark');
            localStorage.setItem('theme', 'dark');
        }
    }

    // --- Logic Helper User Dropdown (Click) ---
    function toggleUserDropdown() {
        const dropdown = document.getElementById('user-menu-dropdown');
        dropdown.classList.toggle('invisible');
        dropdown.classList.toggle('opacity-0');
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const dropdown = document.getElementById('user-menu-dropdown');
        const button = dropdown.previousElementSibling; // Tombol trigger
        
        if (!dropdown.contains(event.target) && !button.contains(event.target)) {
            dropdown.classList.add('invisible');
            dropdown.classList.add('opacity-0');
        }
    });

    // --- Modal Logic ---
    const notifModal = document.getElementById('notif-modal');
    const notifModalContent = document.getElementById('notif-modal-content');

    function toggleNotifModal() {
        if (notifModal.classList.contains('invisible')) {
            notifModal.classList.remove('invisible', 'opacity-0');
            notifModalContent.classList.remove('scale-95');
            notifModalContent.classList.add('scale-100');
        } else {
            notifModal.classList.add('invisible', 'opacity-0');
            notifModalContent.classList.remove('scale-100');
            notifModalContent.classList.add('scale-95');
        }
    }

    notifModal.addEventListener('click', function(e) {
        if (e.target === notifModal) toggleNotifModal();
    });

    // --- Logout Confirmation ---
    function confirmLogout(e) {
        e.preventDefault();
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
            background: document.documentElement.classList.contains('dark') ? '#1e293b' : '#fff',
            color: document.documentElement.classList.contains('dark') ? '#fff' : '#000'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '?logout=1';
            }
        });
    }

    // --- Notification Polling Logic ---
    let lastCount = 0;

    async function fetchNotifications() {
        try {
            const response = await fetch('/situs-rental-gedung/admin/api/check_notifications.php');
            const result = await response.json();

            if (result.status === 'success') {
                const count = result.count;
                const data = result.data;

                const headerBadge = document.getElementById('header-notif-badge');
                const sidebarDot = document.getElementById('sidebar-notif-dot');
                const sidebarCount = document.getElementById('sidebar-notif-count');
                const notifListModal = document.getElementById('notif-list-modal');

                if (count > 0) {
                    headerBadge.classList.remove('hidden');
                    sidebarDot.classList.remove('hidden');
                    sidebarCount.classList.remove('hidden');
                    sidebarCount.innerText = count;

                    if (count > lastCount) {
                        const audio = document.getElementById('notif-sound');
                        audio.play().catch(e => console.log('Audio play blocked'));
                        if (Notification.permission === "granted") {
                            new Notification("Order Baru Masuk!", {
                                body: `Ada ${count} booking menunggu konfirmasi.`,
                                icon: '/situs-rental-gedung/assets/logo.png'
                            });
                        }
                    }

                    let listHtml = '';
                    data.forEach(item => {
                        listHtml += `
                            <a href="/situs-rental-gedung/admin/data/booking/detail.php?id=${item.id}" class="block p-4 mb-2 bg-white dark:bg-slate-700 border border-slate-100 dark:border-slate-600 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-600 hover:border-primary/30 hover:shadow-md transition-all group">
                                <div class="flex justify-between items-start mb-1">
                                    <div class="flex items-center gap-2">
                                        <div class="h-2 w-2 rounded-full bg-primary animate-pulse"></div>
                                        <p class="text-sm font-bold text-slate-800 dark:text-white group-hover:text-primary transition-colors">${item.nama_lengkap}</p>
                                    </div>
                                    <span class="text-[10px] font-medium text-slate-400 dark:text-slate-300 bg-slate-100 dark:bg-slate-600 px-2 py-0.5 rounded-full">${item.time_ago}</span>
                                </div>
                                <div class="flex justify-between items-center mt-2">
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Kode: <span class="font-mono font-semibold text-slate-700 dark:text-slate-200">${item.booking_code}</span></p>
                                    <span class="text-[10px] font-bold bg-amber-100 text-amber-600 dark:bg-amber-900/50 dark:text-amber-400 px-2 py-1 rounded-lg">Menunggu Konfirmasi</span>
                                </div>
                            </a>
                        `;
                    });
                    notifListModal.innerHTML = listHtml;

                } else {
                    headerBadge.classList.add('hidden');
                    sidebarDot.classList.add('hidden');
                    sidebarCount.classList.add('hidden');
                    notifListModal.innerHTML = `
                        <div class="px-6 py-12 text-center text-slate-400 text-sm">
                            <div class="bg-slate-50 dark:bg-slate-700 h-16 w-16 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fa-regular fa-bell-slash text-2xl text-slate-300 dark:text-slate-500"></i>
                            </div>
                            <p class="font-medium text-slate-500 dark:text-slate-400">Tidak ada notifikasi baru</p>
                            <p class="text-xs mt-1 dark:text-slate-500">Semua pesanan sudah ditangani.</p>
                        </div>
                    `;
                }
                lastCount = count;
            }
        } catch (error) {
            console.error('Error fetching notifications:', error);
        }
    }

    if (Notification.permission !== "granted") Notification.requestPermission();
    fetchNotifications();
    setInterval(fetchNotifications, 10000);
</script>