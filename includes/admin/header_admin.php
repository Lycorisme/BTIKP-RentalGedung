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
// Ambil tema dari database, default 'indigo'
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
            'indigo': { // Default Royal
                primary: '#4F46E5', 
                secondary: '#10B981',
                dark: '#1e1b4b', // Indigo 950
                sidebar: 'from-indigo-900 via-purple-900 to-slate-900'
            },
            'ocean': { // Biru Laut
                primary: '#0ea5e9', // Sky 500
                secondary: '#3b82f6', // Blue 500
                dark: '#0f172a', // Slate 900
                sidebar: 'from-sky-900 via-blue-900 to-slate-900'
            },
            'nature': { // Hijau Alam
                primary: '#16a34a', // Green 600
                secondary: '#ca8a04', // Yellow 600
                dark: '#14532d', // Green 900
                sidebar: 'from-green-900 via-emerald-900 to-slate-900'
            },
            'rose': { // Merah Muda Elegan
                primary: '#e11d48', // Rose 600
                secondary: '#db2777', // Pink 600
                dark: '#881337', // Rose 900
                sidebar: 'from-rose-900 via-pink-900 to-slate-900'
            },
            'sunset': { // Oranye Hangat
                primary: '#ea580c', // Orange 600
                secondary: '#d97706', // Amber 600
                dark: '#431407', // Orange 950
                sidebar: 'from-orange-900 via-red-900 to-slate-900'
            },
            'teal': { // Professional Teal
                primary: '#0d9488', // Teal 600
                secondary: '#0891b2', // Cyan 600
                dark: '#115e59', // Teal 800
                sidebar: 'from-teal-900 via-cyan-900 to-slate-900'
            }
        };

        // Pilih tema aktif berdasarkan PHP
        const activeThemeName = '<?= $activeTheme ?>';
        const currentTheme = themes[activeThemeName] || themes['indigo'];

        tailwind.config = {
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

        // Helper untuk inject class gradient ke sidebar saat load
        document.addEventListener('DOMContentLoaded', () => {
            const sidebar = document.getElementById('sidebar');
            if(sidebar) {
                // Hapus class lama jika ada (optional) & tambah class gradient tema
                sidebar.classList.add(...currentTheme.sidebar.split(' '));
            }
        });
    </script>
    
    <style>
        /* Hide Scrollbar but allow scrolling */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
        }
        
        /* Smooth Entry Animation Wrapper */
        .animate-entry {
            animation: fadeInUp 0.4s ease-out forwards;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(15px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body class="bg-slate-50 font-sans text-slate-800 antialiased selection:bg-primary selection:text-white overflow-hidden">

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

            <nav class="flex-1 overflow-y-auto py-6 px-4 space-y-2 no-scrollbar">
                
                <p class="px-4 text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Main Menu</p>
                
                <a href="/situs-rental-gedung/admin/data/dashboard/" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold transition-all group <?= menuActive(['dashboard', 'admin'], true) ?>">
                    <i class="fa-solid fa-grid-2 w-5 group-hover:text-white transition-colors"></i> Dashboard
                </a>

                <a href="/situs-rental-gedung/admin/data/booking/" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition-all group <?= menuActive('booking') ?>">
                    <div class="relative">
                        <i class="fa-solid fa-calendar-check w-5 group-hover:text-white transition-colors"></i>
                        <span id="sidebar-notif-dot" class="hidden absolute -top-1 -right-1 h-2.5 w-2.5 rounded-full bg-rose-500 border-2 border-indigo-900"></span>
                    </div>
                    <span>Booking Masuk</span>
                    <span id="sidebar-notif-count" class="hidden ml-auto bg-rose-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-lg shadow-rose-500/30">0</span>
                </a>

                <a href="/situs-rental-gedung/admin/data/gedung/" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition-all group <?= menuActive('gedung') ?>">
                    <i class="fa-solid fa-building-user w-5 group-hover:text-white transition-colors"></i> Data Gedung
                </a>

                <a href="/situs-rental-gedung/admin/data/pelanggan/" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition-all group <?= menuActive('pelanggan') ?>">
                    <i class="fa-solid fa-users w-5 group-hover:text-white transition-colors"></i> Pelanggan
                </a>

                <a href="/situs-rental-gedung/admin/data/jadwal/" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition-all group <?= menuActive('jadwal') ?>">
                    <i class="fa-solid fa-clock w-5 group-hover:text-white transition-colors"></i> Jadwal
                </a>

                <p class="px-4 text-xs font-semibold text-slate-400 uppercase tracking-wider mt-8 mb-2">Settings</p>

                <a href="/situs-rental-gedung/admin/laporan/" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition-all group <?= menuActive('laporan') ?>">
                    <i class="fa-solid fa-file-invoice-dollar w-5 group-hover:text-white transition-colors"></i> Laporan
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
                    <a href="?logout=1" class="rounded-full p-2 text-slate-400 hover:bg-white/10 hover:text-white transition-colors" title="Logout">
                        <i class="fa-solid fa-right-from-bracket"></i>
                    </a>
                </div>
            </div>
        </aside>

        <div class="flex h-full flex-1 flex-col overflow-hidden bg-slate-50 relative">
            
            <header class="flex h-20 items-center justify-between bg-white/80 px-8 backdrop-blur-md sticky top-0 z-40 border-b border-slate-200/60 transition-all duration-300">
                <div class="flex items-center gap-4">
                    <button id="mobile-menu-btn" class="rounded-xl bg-slate-100 p-2 text-slate-600 lg:hidden hover:bg-indigo-50 hover:text-indigo-600 transition-colors">
                        <i class="fa-solid fa-bars text-xl"></i>
                    </button>
                    
                    <div class="hidden md:flex items-center gap-2 rounded-2xl bg-slate-100 px-4 py-2.5 transition-all focus-within:ring-2 focus-within:ring-primary/20 focus-within:bg-white w-64 lg:w-96 shadow-sm">
                        <i class="fa-solid fa-magnifying-glass text-slate-400"></i>
                        <input type="text" placeholder="Cari data..." class="bg-transparent text-sm font-medium text-slate-600 outline-none placeholder:text-slate-400 w-full">
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    
                    <button onclick="toggleNotifModal()" class="relative rounded-xl bg-white p-2.5 text-slate-500 shadow-sm border border-slate-100 hover:bg-indigo-50 hover:text-primary transition-all">
                        <i class="fa-regular fa-bell text-lg"></i>
                        <span id="header-notif-badge" class="hidden absolute top-2 right-2 h-2.5 w-2.5 rounded-full bg-rose-500 ring-2 ring-white animate-pulse"></span>
                    </button>

                    <div class="h-8 w-[1px] bg-slate-200 hidden sm:block"></div>
                    
                    <div class="relative group">
                        <div class="flex items-center gap-2 cursor-pointer hover:bg-slate-100 p-1.5 rounded-xl transition-colors">
                            <div class="text-right hidden sm:block">
                                <p class="text-sm font-bold text-slate-700"><?= $userName ?></p>
                                <p class="text-xs text-slate-500">Online</p>
                            </div>
                            <img class="h-9 w-9 rounded-xl border border-slate-200 shadow-sm" src="<?= $userAvatar ?>" alt="">
                        </div>
                        
                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-xl border border-slate-100 py-2 invisible opacity-0 group-hover:visible group-hover:opacity-100 transition-all z-50 transform origin-top-right">
                            <a href="/situs-rental-gedung/public/" class="block px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 hover:text-primary">
                                <i class="fa-solid fa-globe mr-2"></i> Lihat Website
                            </a>
                            <div class="border-t border-slate-100 my-1"></div>
                            <a href="?logout=1" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                <i class="fa-solid fa-power-off mr-2"></i> Logout
                            </a>
                        </div>
                    </div>
                </div>
            </header>

            <div id="notif-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm opacity-0 invisible transition-all duration-300">
                <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl transform scale-95 transition-all duration-300" id="notif-modal-content">
                    <div class="flex justify-between items-center px-6 py-4 border-b border-slate-100">
                        <h3 class="text-lg font-bold text-slate-800">Notifikasi Masuk</h3>
                        <button onclick="toggleNotifModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                            <i class="fa-solid fa-times text-xl"></i>
                        </button>
                    </div>
                    
                    <div id="notif-list-modal" class="max-h-[60vh] overflow-y-auto no-scrollbar p-2">
                        <div class="px-6 py-12 text-center text-slate-400 text-sm">
                            <i class="fa-regular fa-bell-slash text-4xl mb-3 opacity-50 block mx-auto"></i>
                            <p>Tidak ada notifikasi baru</p>
                        </div>
                    </div>

                    <div class="border-t border-slate-100 p-4 bg-slate-50 rounded-b-2xl flex justify-center">
                        <a href="/situs-rental-gedung/admin/data/booking/" class="text-sm font-bold text-primary hover:underline">
                            Lihat Semua Data Booking
                        </a>
                    </div>
                </div>
            </div>

            <main class="flex-1 overflow-y-auto p-4 lg:p-8 no-scrollbar animate-entry">

<audio id="notif-sound" src="https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3" preload="auto"></audio>

<script>
    // --- Modal Logic ---
    const notifModal = document.getElementById('notif-modal');
    const notifModalContent = document.getElementById('notif-modal-content');

    function toggleNotifModal() {
        if (notifModal.classList.contains('invisible')) {
            // Show
            notifModal.classList.remove('invisible', 'opacity-0');
            notifModalContent.classList.remove('scale-95');
            notifModalContent.classList.add('scale-100');
        } else {
            // Hide
            notifModal.classList.add('invisible', 'opacity-0');
            notifModalContent.classList.remove('scale-100');
            notifModalContent.classList.add('scale-95');
        }
    }

    // Close modal when clicking outside
    notifModal.addEventListener('click', function(e) {
        if (e.target === notifModal) {
            toggleNotifModal();
        }
    });

    // --- Notification Polling Logic ---
    let lastCount = 0;

    async function fetchNotifications() {
        try {
            const response = await fetch('/situs-rental-gedung/admin/api/check_notifications.php');
            const result = await response.json();

            if (result.status === 'success') {
                const count = result.count;
                const data = result.data;

                // Elements
                const headerBadge = document.getElementById('header-notif-badge');
                const sidebarDot = document.getElementById('sidebar-notif-dot');
                const sidebarCount = document.getElementById('sidebar-notif-count');
                const notifListModal = document.getElementById('notif-list-modal');

                if (count > 0) {
                    // Show Badges
                    headerBadge.classList.remove('hidden');
                    sidebarDot.classList.remove('hidden');
                    sidebarCount.classList.remove('hidden');
                    sidebarCount.innerText = count;

                    // Play Sound if new notification arrives
                    if (count > lastCount) {
                        const audio = document.getElementById('notif-sound');
                        audio.play().catch(e => console.log('Audio play blocked'));
                        
                        // Browser Notification (Optional)
                        if (Notification.permission === "granted") {
                            new Notification("Order Baru Masuk!", {
                                body: `Ada ${count} booking menunggu konfirmasi.`,
                                icon: '/situs-rental-gedung/assets/logo.png'
                            });
                        }
                    }

                    // Render List for Modal
                    let listHtml = '';
                    data.forEach(item => {
                        listHtml += `
                            <a href="/situs-rental-gedung/admin/data/booking/detail.php?id=${item.id}" class="block p-4 mb-2 bg-white border border-slate-100 rounded-xl hover:bg-slate-50 hover:border-primary/30 hover:shadow-md transition-all group">
                                <div class="flex justify-between items-start mb-1">
                                    <div class="flex items-center gap-2">
                                        <div class="h-2 w-2 rounded-full bg-primary animate-pulse"></div>
                                        <p class="text-sm font-bold text-slate-800 group-hover:text-primary transition-colors">${item.nama_lengkap}</p>
                                    </div>
                                    <span class="text-[10px] font-medium text-slate-400 bg-slate-100 px-2 py-0.5 rounded-full">${item.time_ago}</span>
                                </div>
                                <div class="flex justify-between items-center mt-2">
                                    <p class="text-xs text-slate-500">Kode: <span class="font-mono font-semibold text-slate-700">${item.booking_code}</span></p>
                                    <span class="text-[10px] font-bold bg-amber-100 text-amber-600 px-2 py-1 rounded-lg">Menunggu Konfirmasi</span>
                                </div>
                            </a>
                        `;
                    });
                    notifListModal.innerHTML = listHtml;

                } else {
                    // Hide Badges
                    headerBadge.classList.add('hidden');
                    sidebarDot.classList.add('hidden');
                    sidebarCount.classList.add('hidden');
                    
                    // Empty State
                    notifListModal.innerHTML = `
                        <div class="px-6 py-12 text-center text-slate-400 text-sm">
                            <div class="bg-slate-50 h-16 w-16 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fa-regular fa-bell-slash text-2xl text-slate-300"></i>
                            </div>
                            <p class="font-medium text-slate-500">Tidak ada notifikasi baru</p>
                            <p class="text-xs mt-1">Semua pesanan sudah ditangani.</p>
                        </div>
                    `;
                }

                lastCount = count;
            }
        } catch (error) {
            console.error('Error fetching notifications:', error);
        }
    }

    // Request Notification Permission on Load
    if (Notification.permission !== "granted") {
        Notification.requestPermission();
    }

    // Run immediately and loop
    fetchNotifications();
    setInterval(fetchNotifications, 10000); // Poll every 10s
</script>