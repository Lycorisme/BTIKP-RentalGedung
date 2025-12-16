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
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?= htmlspecialchars($settings['nama_website'] ?? 'Rental Gedung') ?></title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        },
                        secondary: {
                            50: '#fdf4ff',
                            100: '#fae8ff',
                            200: '#f5d0fe',
                            300: '#f0abfc',
                            400: '#e879f9',
                            500: '#d946ef',
                            600: '#c026d3',
                            700: '#a21caf',
                            800: '#86198f',
                            900: '#701a75',
                        },
                        accent: {
                            50: '#fff7ed',
                            100: '#ffedd5',
                            200: '#fed7aa',
                            300: '#fdba74',
                            400: '#fb923c',
                            500: '#f97316',
                            600: '#ea580c',
                            700: '#c2410c',
                            800: '#9a3412',
                            900: '#7c2d12',
                        },
                        success: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            500: '#22c55e',
                            600: '#16a34a',
                        },
                        warning: {
                            50: '#fffbeb',
                            100: '#fef3c7',
                            500: '#eab308',
                            600: '#ca8a04',
                        },
                        danger: {
                            50: '#fef2f2',
                            100: '#fee2e2',
                            500: '#ef4444',
                            600: '#dc2626',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-purple-50 font-sans antialiased">

<!-- Top Navigation -->
<nav class="fixed top-0 left-0 right-0 z-50 bg-white/80 backdrop-blur-xl border-b border-slate-200/60 shadow-lg">
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Logo & Brand -->
            <div class="flex items-center gap-3">
                <button id="sidebar-toggle" class="lg:hidden p-2 rounded-xl hover:bg-slate-100 transition-colors">
                    <i class="fa-solid fa-bars text-slate-700 text-xl"></i>
                </button>
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-xl bg-gradient-to-br from-primary-500 to-secondary-500 flex items-center justify-center shadow-lg shadow-primary-500/30">
                        <i class="fa-solid fa-building text-white text-lg"></i>
                    </div>
                    <div class="hidden sm:block">
                        <h1 class="text-lg font-bold bg-gradient-to-r from-primary-600 to-secondary-600 bg-clip-text text-transparent">
                            <?= htmlspecialchars($settings['nama_website'] ?? 'Rental Gedung') ?>
                        </h1>
                        <p class="text-xs text-slate-500">Admin Dashboard</p>
                    </div>
                </div>
            </div>
            
            <!-- Right Section -->
            <div class="flex items-center gap-3">
                <!-- Notifications -->
                <button class="relative p-2 rounded-xl hover:bg-slate-100 transition-colors">
                    <i class="fa-solid fa-bell text-slate-600 text-lg"></i>
                    <span class="absolute top-1 right-1 h-2 w-2 bg-danger-500 rounded-full animate-pulse"></span>
                </button>
                
                <!-- User Menu -->
                <div class="relative group">
                    <button class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-slate-100 transition-colors">
                        <div class="h-8 w-8 rounded-lg bg-gradient-to-br from-primary-400 to-secondary-500 flex items-center justify-center text-white font-bold text-sm">
                            <?= strtoupper(substr(getUserName(), 0, 1)) ?>
                        </div>
                        <div class="hidden md:block text-left">
                            <p class="text-sm font-semibold text-slate-700"><?= htmlspecialchars(getUserName()) ?></p>
                            <p class="text-xs text-slate-500 capitalize"><?= htmlspecialchars(getUserRole()) ?></p>
                        </div>
                        <i class="fa-solid fa-chevron-down text-slate-400 text-xs"></i>
                    </button>
                    
                    <!-- Dropdown -->
                    <div class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-2xl border border-slate-200 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all">
                        <div class="p-4 border-b border-slate-100">
                            <p class="font-semibold text-slate-800"><?= htmlspecialchars(getUserName()) ?></p>
                            <p class="text-sm text-slate-500"><?= htmlspecialchars(getUserEmail()) ?></p>
                        </div>
                        <div class="p-2">
                            <a href="/situs-rental-gedung/public/" class="flex items-center gap-3 px-4 py-2 rounded-xl hover:bg-slate-50 transition-colors text-slate-700">
                                <i class="fa-solid fa-home w-5"></i>
                                <span>Lihat Website</span>
                            </a>
                            <?php if (isSuperAdmin()): ?>
                            <a href="/situs-rental-gedung/admin/settings/" class="flex items-center gap-3 px-4 py-2 rounded-xl hover:bg-slate-50 transition-colors text-slate-700">
                                <i class="fa-solid fa-cog w-5"></i>
                                <span>Pengaturan</span>
                            </a>
                            <?php endif; ?>
                            <a href="?logout=1" class="flex items-center gap-3 px-4 py-2 rounded-xl hover:bg-red-50 transition-colors text-red-600">
                                <i class="fa-solid fa-right-from-bracket w-5"></i>
                                <span>Logout</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>

<!-- Main Container -->
<div class="flex pt-16 min-h-screen">
    <!-- Sidebar -->
    <aside id="sidebar" class="fixed lg:sticky top-16 left-0 h-[calc(100vh-4rem)] w-64 bg-white/80 backdrop-blur-xl border-r border-slate-200/60 shadow-xl transition-transform -translate-x-full lg:translate-x-0 z-40">
        <div class="h-full overflow-y-auto p-4">
            <nav class="space-y-2">
                <!-- Dashboard -->
                <a href="/situs-rental-gedung/admin/data/gedung/" 
                    class="flex items-center gap-3 px-4 py-3 rounded-xl <?= $current_dir === 'gedung' || $current_dir === 'admin' ? 'bg-gradient-to-r from-primary-500/10 to-secondary-500/10 text-primary-600 font-semibold' : 'text-slate-600 hover:bg-slate-50' ?> transition-all">
                    <i class="fa-solid fa-gauge-high w-5"></i>
                    <span>Dashboard</span>
                </a>
                
                <!-- Data Management -->
                <div class="pt-4 pb-2 px-4">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Data Management</p>
                </div>
                
                <a href="/situs-rental-gedung/admin/data/gedung/" 
                    class="flex items-center gap-3 px-4 py-3 rounded-xl <?= $current_dir === 'gedung' ? 'bg-gradient-to-r from-primary-500/10 to-secondary-500/10 text-primary-600 font-semibold' : 'text-slate-600 hover:bg-slate-50' ?> transition-all">
                    <i class="fa-solid fa-building w-5"></i>
                    <span>Gedung</span>
                </a>
                
                <a href="/situs-rental-gedung/admin/data/booking/" 
                    class="flex items-center gap-3 px-4 py-3 rounded-xl <?= $current_dir === 'booking' ? 'bg-gradient-to-r from-primary-500/10 to-secondary-500/10 text-primary-600 font-semibold' : 'text-slate-600 hover:bg-slate-50' ?> transition-all">
                    <i class="fa-solid fa-calendar-check w-5"></i>
                    <span>Booking</span>
                </a>
                
                <a href="/situs-rental-gedung/admin/data/pelanggan/" 
                    class="flex items-center gap-3 px-4 py-3 rounded-xl <?= $current_dir === 'pelanggan' ? 'bg-gradient-to-r from-primary-500/10 to-secondary-500/10 text-primary-600 font-semibold' : 'text-slate-600 hover:bg-slate-50' ?> transition-all">
                    <i class="fa-solid fa-users w-5"></i>
                    <span>Pelanggan</span>
                </a>
                
                <a href="/situs-rental-gedung/admin/data/jadwal/" 
                    class="flex items-center gap-3 px-4 py-3 rounded-xl <?= $current_dir === 'jadwal' ? 'bg-gradient-to-r from-primary-500/10 to-secondary-500/10 text-primary-600 font-semibold' : 'text-slate-600 hover:bg-slate-50' ?> transition-all">
                    <i class="fa-solid fa-calendar-days w-5"></i>
                    <span>Jadwal</span>
                </a>
                
                <!-- Reports -->
                <div class="pt-4 pb-2 px-4">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Laporan</p>
                </div>
                
                <a href="/situs-rental-gedung/laporan/" 
                    class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-600 hover:bg-slate-50 transition-all">
                    <i class="fa-solid fa-file-lines w-5"></i>
                    <span>Semua Laporan</span>
                </a>
                
                <!-- Settings (Superadmin Only) -->
                <?php if (isSuperAdmin()): ?>
                <div class="pt-4 pb-2 px-4">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">System</p>
                </div>
                
                <a href="/situs-rental-gedung/admin/settings/" 
                    class="flex items-center gap-3 px-4 py-3 rounded-xl <?= $current_dir === 'settings' ? 'bg-gradient-to-r from-primary-500/10 to-secondary-500/10 text-primary-600 font-semibold' : 'text-slate-600 hover:bg-slate-50' ?> transition-all">
                    <i class="fa-solid fa-cog w-5"></i>
                    <span>Pengaturan</span>
                </a>
                <?php endif; ?>
            </nav>
        </div>
    </aside>
    
    <!-- Overlay for mobile -->
    <div id="sidebar-overlay" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-30 lg:hidden hidden"></div>
    
    <!-- Main Content -->
    <main class="flex-1 p-4 sm:p-6 lg:p-8">
        <div class="max-w-7xl mx-auto">