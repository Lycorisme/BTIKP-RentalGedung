<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../modules/settings.php';
require_once __DIR__ . '/auth.php';

requireLogin();

$settings = get_all_settings();
$nama_website = $settings['nama_website'] ?? 'Rental Gedung';
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?= htmlspecialchars($nama_website) ?></title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        primary: '#4F46E5',
                        secondary: '#10B981',
                        dark: '#1E293B',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-50 font-sans text-slate-800 antialiased selection:bg-indigo-500 selection:text-white">

<div class="flex h-screen overflow-hidden">
    <!-- Sidebar -->
    <aside id="sidebar" class="absolute z-50 -translate-x-full transition-transform duration-300 lg:static lg:translate-x-0 flex h-full w-72 flex-col bg-gradient-to-b from-indigo-900 via-purple-900 to-slate-900 text-white shadow-2xl">
        <!-- Logo -->
        <div class="flex items-center justify-center h-24 border-b border-white/10 bg-white/5 backdrop-blur-sm">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-xl bg-gradient-to-tr from-pink-500 to-orange-400 flex items-center justify-center shadow-lg">
                    <i class="fa-solid fa-building text-lg"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold tracking-tight">Gedung<span class="text-indigo-300">Kita</span></h1>
                    <p class="text-xs text-slate-400 uppercase tracking-widest">Admin Panel</p>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto py-6 px-4 space-y-2">
            <p class="px-4 text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Main Menu</p>
            
            <a href="/situs-rental-gedung/admin/data/gedung/" class="<?= strpos($_SERVER['REQUEST_URI'], '/admin/data/gedung') !== false ? 'flex items-center gap-3 rounded-2xl bg-white/20 px-4 py-3 text-sm font-semibold text-white shadow-inner backdrop-blur-md transition-all border border-white/10' : 'group flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium text-slate-300 transition-all hover:bg-white/10 hover:text-white hover:pl-6' ?>">
                <i class="fa-solid fa-building-user w-5 <?= strpos($_SERVER['REQUEST_URI'], '/admin/data/gedung') !== false ? '' : 'text-teal-300 group-hover:text-white transition-colors' ?>"></i> Data Gedung
            </a>

            <a href="/situs-rental-gedung/admin/data/booking/" class="<?= strpos($_SERVER['REQUEST_URI'], '/admin/data/booking') !== false ? 'flex items-center gap-3 rounded-2xl bg-white/20 px-4 py-3 text-sm font-semibold text-white shadow-inner backdrop-blur-md transition-all border border-white/10' : 'group flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium text-slate-300 transition-all hover:bg-white/10 hover:text-white hover:pl-6' ?>">
                <i class="fa-solid fa-calendar-check w-5 <?= strpos($_SERVER['REQUEST_URI'], '/admin/data/booking') !== false ? '' : 'text-indigo-300 group-hover:text-white transition-colors' ?>"></i> Booking
            </a>

            <a href="/situs-rental-gedung/admin/data/pelanggan/" class="<?= strpos($_SERVER['REQUEST_URI'], '/admin/data/pelanggan') !== false ? 'flex items-center gap-3 rounded-2xl bg-white/20 px-4 py-3 text-sm font-semibold text-white shadow-inner backdrop-blur-md transition-all border border-white/10' : 'group flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium text-slate-300 transition-all hover:bg-white/10 hover:text-white hover:pl-6' ?>">
                <i class="fa-solid fa-users w-5 <?= strpos($_SERVER['REQUEST_URI'], '/admin/data/pelanggan') !== false ? '' : 'text-blue-300 group-hover:text-white transition-colors' ?>"></i> Pelanggan
            </a>

            <a href="/situs-rental-gedung/admin/data/jadwal/" class="<?= strpos($_SERVER['REQUEST_URI'], '/admin/data/jadwal') !== false ? 'flex items-center gap-3 rounded-2xl bg-white/20 px-4 py-3 text-sm font-semibold text-white shadow-inner backdrop-blur-md transition-all border border-white/10' : 'group flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium text-slate-300 transition-all hover:bg-white/10 hover:text-white hover:pl-6' ?>">
                <i class="fa-solid fa-calendar-days w-5 <?= strpos($_SERVER['REQUEST_URI'], '/admin/data/jadwal') !== false ? '' : 'text-purple-300 group-hover:text-white transition-colors' ?>"></i> Jadwal
            </a>

            <p class="px-4 text-xs font-semibold text-slate-400 uppercase tracking-wider mt-8 mb-2">Settings</p>

            <a href="/situs-rental-gedung/laporan/" class="group flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium text-slate-300 transition-all hover:bg-white/10 hover:text-white hover:pl-6">
                <i class="fa-solid fa-file-invoice-dollar w-5 text-yellow-300 group-hover:text-white transition-colors"></i> Laporan
            </a>
            
            <?php if (isSuperAdmin()): ?>
            <a href="/situs-rental-gedung/admin/settings/" class="<?= strpos($_SERVER['REQUEST_URI'], '/admin/settings') !== false ? 'flex items-center gap-3 rounded-2xl bg-white/20 px-4 py-3 text-sm font-semibold text-white shadow-inner backdrop-blur-md transition-all border border-white/10' : 'group flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium text-slate-300 transition-all hover:bg-white/10 hover:text-white hover:pl-6' ?>">
                <i class="fa-solid fa-gear w-5 <?= strpos($_SERVER['REQUEST_URI'], '/admin/settings') !== false ? '' : 'text-slate-300 group-hover:text-white transition-colors' ?>"></i> Pengaturan
            </a>
            <?php endif; ?>
        </nav>

        <!-- User Profile -->
        <div class="border-t border-white/10 bg-black/20 p-4 backdrop-blur-sm">
            <div class="flex items-center gap-3">
                <img class="h-10 w-10 rounded-full border-2 border-white/30 shadow-md" src="https://ui-avatars.com/api/?name=<?= urlencode(getUserName()) ?>&background=random" alt="User">
                <div class="flex-1">
                    <p class="text-sm font-semibold text-white"><?= htmlspecialchars(getUserName()) ?></p>
                    <p class="text-xs text-slate-400"><?= htmlspecialchars(getUserRole()) ?></p>
                </div>
                <a href="?logout=1" class="rounded-full p-2 text-slate-400 hover:bg-white/10 hover:text-white transition-colors" title="Logout">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </a>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="flex h-full flex-1 flex-col overflow-hidden bg-slate-50 relative">
        <!-- Header -->
        <header class="flex h-20 items-center justify-between bg-white/80 px-8 backdrop-blur-md sticky top-0 z-40 border-b border-slate-200/60">
            <div class="flex items-center gap-4">
                <button id="mobile-menu-btn" class="rounded-xl bg-slate-100 p-2 text-slate-600 lg:hidden hover:bg-indigo-50 hover:text-indigo-600 transition-colors">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
                
                <div class="hidden md:flex items-center gap-2 rounded-2xl bg-slate-100 px-4 py-2.5 transition-all focus-within:ring-2 focus-within:ring-indigo-500/20 focus-within:bg-white w-64 lg:w-96">
                    <i class="fa-solid fa-magnifying-glass text-slate-400"></i>
                    <input type="text" placeholder="Cari data..." class="bg-transparent text-sm font-medium text-slate-600 outline-none placeholder:text-slate-400 w-full">
                </div>
            </div>

            <div class="flex items-center gap-4">
                <a href="/situs-rental-gedung/public/" class="hidden sm:flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 text-slate-600 hover:bg-indigo-50 hover:text-indigo-600 transition-all text-sm font-medium">
                    <i class="fa-solid fa-globe"></i> Lihat Website
                </a>
                <div class="h-8 w-[1px] bg-slate-200 hidden sm:block"></div>
                <div class="flex items-center gap-2 cursor-pointer hover:bg-slate-100 p-1.5 rounded-xl transition-colors">
                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-bold text-slate-700"><?= htmlspecialchars(getUserName()) ?></p>
                        <p class="text-xs text-slate-500">Online</p>
                    </div>
                    <img class="h-9 w-9 rounded-xl border border-slate-200 shadow-sm" src="https://ui-avatars.com/api/?name=<?= urlencode(getUserName()) ?>&background=0D8ABC&color=fff" alt="">
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="flex-1 overflow-y-auto p-4 lg:p-8 scroll-smooth">