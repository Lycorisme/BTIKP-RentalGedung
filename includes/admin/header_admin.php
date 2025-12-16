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
</head>
<body class="bg-slate-50 font-sans text-slate-800 antialiased selection:bg-primary selection:text-white">

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

            <nav class="flex-1 overflow-y-auto py-6 px-4 space-y-2">
                
                <p class="px-4 text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Main Menu</p>
                
                <a href="/situs-rental-gedung/admin/data/dashboard/" 
                   class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold transition-all group <?= menuActive(['dashboard', 'admin'], true) ?>">
                    <i class="fa-solid fa-gauge-high w-5 group-hover:text-white transition-colors"></i>
                    Dashboard
                </a>

                <a href="/situs-rental-gedung/admin/data/booking/" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition-all group <?= menuActive('booking') ?>">
                    <i class="fa-solid fa-calendar-check w-5 group-hover:text-white transition-colors"></i> Booking Masuk
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
            
            <header class="flex h-20 items-center justify-between bg-white/80 px-8 backdrop-blur-md sticky top-0 z-40 border-b border-slate-200/60">
                <div class="flex items-center gap-4">
                    <button id="mobile-menu-btn" class="rounded-xl bg-slate-100 p-2 text-slate-600 lg:hidden hover:bg-indigo-50 hover:text-indigo-600 transition-colors">
                        <i class="fa-solid fa-bars text-xl"></i>
                    </button>
                    
                    <div class="hidden md:flex items-center gap-2 rounded-2xl bg-slate-100 px-4 py-2.5 transition-all focus-within:ring-2 focus-within:ring-primary/20 focus-within:bg-white w-64 lg:w-96">
                        <i class="fa-solid fa-magnifying-glass text-slate-400"></i>
                        <input type="text" placeholder="Cari data..." class="bg-transparent text-sm font-medium text-slate-600 outline-none placeholder:text-slate-400 w-full">
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <button class="relative rounded-xl bg-white p-2.5 text-slate-500 shadow-sm border border-slate-100 hover:bg-indigo-50 hover:text-primary transition-all">
                        <i class="fa-regular fa-bell text-lg"></i>
                        <span class="absolute right-2 top-2 h-2.5 w-2.5 rounded-full bg-red-500 ring-2 ring-white"></span>
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
                        
                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-xl border border-slate-100 py-2 invisible opacity-0 group-hover:visible group-hover:opacity-100 transition-all z-50">
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

            <main class="flex-1 overflow-y-auto p-4 lg:p-8 scroll-smooth">