<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../modules/settings.php';

$settings = get_all_settings();
$nama_website = $settings['nama_website'] ?? 'Rental Gedung';
$logo_url = $settings['logo_url'] ?? 'uploads/logos/logo_default.png';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($nama_website) ?></title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Poppins', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
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
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-purple-50 font-sans antialiased">

<!-- Navbar -->
<nav class="fixed top-0 left-0 right-0 z-50 bg-white/80 backdrop-blur-xl shadow-lg border-b border-slate-200/60">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-20">
            <!-- Logo -->
            <div class="flex items-center gap-3">
                <div class="h-12 w-12 rounded-2xl bg-gradient-to-br from-primary-500 to-secondary-500 flex items-center justify-center shadow-lg shadow-primary-500/30">
                    <i class="fa-solid fa-building text-white text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold bg-gradient-to-r from-primary-600 to-secondary-600 bg-clip-text text-transparent">
                        <?= htmlspecialchars($nama_website) ?>
                    </h1>
                    <p class="text-xs text-slate-500">Solusi Rental Gedung Terbaik</p>
                </div>
            </div>
            
            <!-- Desktop Menu -->
            <div class="hidden md:flex items-center gap-8">
                <a href="/situs-rental-gedung/public/" class="text-slate-700 hover:text-primary-600 font-medium transition-colors">
                    <i class="fa-solid fa-home mr-2"></i>Beranda
                </a>
                <a href="/situs-rental-gedung/public/gedung/" class="text-slate-700 hover:text-primary-600 font-medium transition-colors">
                    <i class="fa-solid fa-building mr-2"></i>Gedung
                </a>
                <a href="/situs-rental-gedung/laporan/" class="text-slate-700 hover:text-primary-600 font-medium transition-colors">
                    <i class="fa-solid fa-file-lines mr-2"></i>Laporan
                </a>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($_SESSION['role'] === 'penyewa'): ?>
                        <a href="/situs-rental-gedung/user/dashboard/" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-primary-500 to-primary-600 text-white font-semibold shadow-lg shadow-primary-500/30 hover:shadow-xl hover:translate-y-[-2px] transition-all">
                            <i class="fa-solid fa-gauge mr-2"></i>Dashboard
                        </a>
                    <?php else: ?>
                        <a href="/situs-rental-gedung/admin/data/gedung/" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-secondary-500 to-secondary-600 text-white font-semibold shadow-lg shadow-secondary-500/30 hover:shadow-xl hover:translate-y-[-2px] transition-all">
                            <i class="fa-solid fa-gauge mr-2"></i>Admin
                        </a>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="/situs-rental-gedung/user/login/" class="text-slate-700 hover:text-primary-600 font-medium transition-colors">
                        <i class="fa-solid fa-right-to-bracket mr-2"></i>Login
                    </a>
                    <a href="/situs-rental-gedung/user/register/" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-primary-500 to-secondary-500 text-white font-semibold shadow-lg shadow-primary-500/30 hover:shadow-xl hover:translate-y-[-2px] transition-all">
                        <i class="fa-solid fa-user-plus mr-2"></i>Daftar
                    </a>
                <?php endif; ?>
            </div>
            
            <!-- Mobile Menu Button -->
            <button id="mobile-menu-btn" class="md:hidden p-2 rounded-xl bg-slate-100 text-slate-700 hover:bg-primary-50 hover:text-primary-600 transition-colors">
                <i class="fa-solid fa-bars text-xl"></i>
            </button>
        </div>
    </div>
    
    <!-- Mobile Menu -->
    <div id="mobile-menu" class="hidden md:hidden bg-white border-t border-slate-200">
        <div class="px-4 py-4 space-y-3">
            <a href="/situs-rental-gedung/public/" class="block px-4 py-3 rounded-xl text-slate-700 hover:bg-primary-50 hover:text-primary-600 font-medium transition-colors">
                <i class="fa-solid fa-home mr-2"></i>Beranda
            </a>
            <a href="/situs-rental-gedung/public/gedung/" class="block px-4 py-3 rounded-xl text-slate-700 hover:bg-primary-50 hover:text-primary-600 font-medium transition-colors">
                <i class="fa-solid fa-building mr-2"></i>Gedung
            </a>
            <a href="/situs-rental-gedung/laporan/" class="block px-4 py-3 rounded-xl text-slate-700 hover:bg-primary-50 hover:text-primary-600 font-medium transition-colors">
                <i class="fa-solid fa-file-lines mr-2"></i>Laporan
            </a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="<?= $_SESSION['role'] === 'penyewa' ? '/situs-rental-gedung/user/dashboard/' : '/situs-rental-gedung/admin/data/gedung/' ?>" class="block px-4 py-3 rounded-xl bg-gradient-to-r from-primary-500 to-primary-600 text-white font-semibold text-center">
                    <i class="fa-solid fa-gauge mr-2"></i>Dashboard
                </a>
            <?php else: ?>
                <a href="/situs-rental-gedung/user/login/" class="block px-4 py-3 rounded-xl text-slate-700 hover:bg-primary-50 hover:text-primary-600 font-medium transition-colors">
                    <i class="fa-solid fa-right-to-bracket mr-2"></i>Login
                </a>
                <a href="/situs-rental-gedung/user/register/" class="block px-4 py-3 rounded-xl bg-gradient-to-r from-primary-500 to-secondary-500 text-white font-semibold text-center">
                    <i class="fa-solid fa-user-plus mr-2"></i>Daftar
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- Main Content Start -->
<main class="pt-20">