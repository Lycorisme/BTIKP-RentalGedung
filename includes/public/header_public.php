<?php
// Mulai sesi jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Deteksi path root untuk include
$root_path = $_SERVER['DOCUMENT_ROOT'] . '/situs-rental-gedung';
require_once $root_path . '/config/database.php';

// Ambil semua settings dari database
function get_public_settings() {
    $db = getDB();
    // PERBAIKAN: Menggunakan kolom `key` dan `value` (dengan backtick karena key adalah reserved word)
    $stmt = $db->query("SELECT `key`, `value` FROM settings");
    $settings = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $settings[$row['key']] = $row['value'];
    }
    return $settings;
}

$settings = get_public_settings();

// --- LOGIC TEMA WARNA DINAMIS ---
// Mapping pilihan admin ke Palet Warna Tailwind (50, 100, 500, 600, 700)
$selected_theme = $settings['public_theme'] ?? 'ocean';

$theme_palettes = [
    'indigo' => [ // Indigo Royal
        '50' => '#eef2ff', '100' => '#e0e7ff', '500' => '#6366f1', '600' => '#4f46e5', '700' => '#4338ca'
    ],
    'ocean' => [ // Ocean Blue (Default)
        '50' => '#f0f9ff', '100' => '#e0f2fe', '500' => '#0ea5e9', '600' => '#0284c7', '700' => '#0369a1'
    ],
    'nature' => [ // Nature Green
        '50' => '#f0fdf4', '100' => '#dcfce7', '500' => '#22c55e', '600' => '#16a34a', '700' => '#15803d'
    ],
    'rose' => [ // Elegant Rose
        '50' => '#fff1f2', '100' => '#ffe4e6', '500' => '#f43f5e', '600' => '#e11d48', '700' => '#be123c'
    ],
    'sunset' => [ // Sunset Orange
        '50' => '#fff7ed', '100' => '#ffedd5', '500' => '#f97316', '600' => '#ea580c', '700' => '#c2410c'
    ],
    'teal' => [ // Teal Professional
        '50' => '#f0fdfa', '100' => '#ccfbf1', '500' => '#14b8a6', '600' => '#0d9488', '700' => '#0f766e'
    ],
];

// Fallback jika tema tidak ditemukan
$colors = $theme_palettes[$selected_theme] ?? $theme_palettes['ocean'];

// Base URL Helper
$base_url = "/situs-rental-gedung";
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($settings['nama_website'] ?? 'Rental Gedung') ?> - <?= htmlspecialchars($settings['nama_panjang'] ?? '') ?></title>
    
    <?php if(!empty($settings['favicon_url'])): ?>
    <link rel="icon" type="image/png" href="<?= $base_url . '/' . $settings['favicon_url'] ?>">
    <?php endif; ?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '<?= $colors['50'] ?>',
                            100: '<?= $colors['100'] ?>',
                            500: '<?= $colors['500'] ?>',
                            600: '<?= $colors['600'] ?>',
                            700: '<?= $colors['700'] ?>',
                        }
                    }
                }
            }
        }
    </script>

    <style>
        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: <?= $colors['500'] ?>; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: <?= $colors['700'] ?>; }
        
        .navbar-scrolled {
            background-color: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            padding-top: 1rem;
            padding-bottom: 1rem;
        }
    </style>
</head>
<body class="font-sans text-slate-800 antialiased bg-slate-50 selection:bg-brand-100 selection:text-brand-700">

    <nav class="fixed w-full z-50 transition-all duration-300 py-4 lg:py-6" id="navbar">
        <div class="container mx-auto px-4 md:px-6">
            <div class="flex justify-between items-center">
                
                <a href="<?= $base_url ?>/" class="flex items-center gap-2 group">
                    <?php if(!empty($settings['logo_url'])): ?>
                        <img src="<?= $base_url . '/' . $settings['logo_url'] ?>" alt="Logo" class="h-10 w-auto object-contain transition-transform group-hover:scale-105">
                    <?php else: ?>
                        <div class="h-10 w-10 bg-brand-600 rounded-xl flex items-center justify-center text-white font-bold text-xl shadow-lg shadow-brand-500/30">
                            <?= substr($settings['nama_website'] ?? 'R', 0, 1) ?>
                        </div>
                    <?php endif; ?>
                    <div class="flex flex-col">
                        <span class="text-xl font-bold text-slate-800 tracking-tight group-hover:text-brand-600 transition-colors">
                            <?= $settings['nama_website'] ?? 'Rental Gedung' ?>
                        </span>
                    </div>
                </a>

                <div class="hidden lg:flex items-center gap-8">
                    <div class="flex items-center gap-6 text-sm font-semibold text-slate-600">
                        <a href="<?= $base_url ?>/" class="hover:text-brand-600 transition-colors <?= ($_SERVER['REQUEST_URI'] == $base_url.'/' || $_SERVER['REQUEST_URI'] == $base_url.'/index.php') ? 'text-brand-600' : '' ?>">Beranda</a>
                        <a href="<?= $base_url ?>/public/gedung/" class="hover:text-brand-600 transition-colors <?= (strpos($_SERVER['REQUEST_URI'], '/gedung') !== false) ? 'text-brand-600' : '' ?>">Daftar Gedung</a>
                        <a href="<?= $base_url ?>/public/cek-jadwal.php" class="hover:text-brand-600 transition-colors">Cek Jadwal</a>
                    </div>

                    <?php if (isset($_SESSION['user'])): ?>
                        <div class="relative group">
                            <button class="flex items-center gap-3 pl-4 border-l border-slate-200 focus:outline-none">
                                <div class="text-right hidden xl:block">
                                    <p class="text-sm font-bold text-slate-800"><?= htmlspecialchars($_SESSION['user']['nama_lengkap']) ?></p>
                                    <p class="text-xs text-slate-500 capitalize"><?= $_SESSION['user']['role'] ?></p>
                                </div>
                                <?php if (!empty($_SESSION['user']['avatar'])): ?>
                                    <img src="<?= $base_url . '/' . $_SESSION['user']['avatar'] ?>" class="h-10 w-10 rounded-full object-cover border-2 border-brand-100 ring-2 ring-transparent group-hover:ring-brand-500 transition-all">
                                <?php else: ?>
                                    <div class="h-10 w-10 rounded-full bg-brand-100 text-brand-600 flex items-center justify-center font-bold text-lg">
                                        <?= substr($_SESSION['user']['nama_lengkap'], 0, 1) ?>
                                    </div>
                                <?php endif; ?>
                                <i class="fa-solid fa-chevron-down text-slate-400 text-xs group-hover:text-brand-600 transition-colors"></i>
                            </button>

                            <div class="absolute right-0 top-full mt-2 w-56 bg-white rounded-xl shadow-xl border border-slate-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all transform origin-top-right z-50">
                                <div class="p-2">
                                    <a href="<?= $base_url ?>/user/dashboard/" class="block px-4 py-2 rounded-lg text-sm text-slate-600 hover:bg-brand-50 hover:text-brand-600 transition-colors">
                                        <i class="fa-solid fa-gauge mr-2"></i> Dashboard
                                    </a>
                                    <a href="<?= $base_url ?>/user/booking/" class="block px-4 py-2 rounded-lg text-sm text-slate-600 hover:bg-brand-50 hover:text-brand-600 transition-colors">
                                        <i class="fa-solid fa-receipt mr-2"></i> Riwayat Booking
                                    </a>
                                    <div class="h-px bg-slate-100 my-2"></div>
                                    <a href="<?= $base_url ?>/auth/logout.php" class="block px-4 py-2 rounded-lg text-sm text-red-600 hover:bg-red-50 hover:text-red-700 transition-colors">
                                        <i class="fa-solid fa-arrow-right-from-bracket mr-2"></i> Logout
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="flex items-center gap-3 pl-4 border-l border-slate-200">
                            <a href="<?= $base_url ?>/user/login/" class="text-sm font-bold text-slate-600 hover:text-brand-600 px-4 py-2 transition-colors">
                                Masuk
                            </a>
                            <a href="<?= $base_url ?>/user/register/" class="px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-bold rounded-full shadow-lg shadow-brand-500/30 transition-all hover:-translate-y-0.5">
                                Daftar Sekarang
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <button class="lg:hidden p-2 text-slate-600 hover:text-brand-600 transition-colors" onclick="document.getElementById('mobile-menu').classList.toggle('hidden')">
                    <i class="fa-solid fa-bars text-2xl"></i>
                </button>
            </div>
        </div>

        <div id="mobile-menu" class="hidden absolute top-full left-0 w-full bg-white border-t border-slate-100 shadow-xl lg:hidden animate-fade-in-down">
            <div class="p-4 space-y-3">
                <a href="<?= $base_url ?>/" class="block px-4 py-3 rounded-xl hover:bg-brand-50 text-slate-600 hover:text-brand-600 font-semibold transition-colors">
                    Beranda
                </a>
                <a href="<?= $base_url ?>/public/gedung/" class="block px-4 py-3 rounded-xl hover:bg-brand-50 text-slate-600 hover:text-brand-600 font-semibold transition-colors">
                    Daftar Gedung
                </a>
                <a href="<?= $base_url ?>/public/cek-jadwal.php" class="block px-4 py-3 rounded-xl hover:bg-brand-50 text-slate-600 hover:text-brand-600 font-semibold transition-colors">
                    Cek Jadwal
                </a>
                
                <div class="h-px bg-slate-100 my-2"></div>
                
                <?php if (isset($_SESSION['user'])): ?>
                    <a href="<?= $base_url ?>/user/dashboard/" class="block px-4 py-3 rounded-xl hover:bg-brand-50 text-slate-600 hover:text-brand-600 font-semibold transition-colors">
                        Dashboard Saya
                    </a>
                    <a href="<?= $base_url ?>/auth/logout.php" class="block px-4 py-3 rounded-xl bg-red-50 text-red-600 font-semibold transition-colors text-center">
                        Logout
                    </a>
                <?php else: ?>
                    <div class="grid grid-cols-2 gap-3 pt-2">
                        <a href="<?= $base_url ?>/user/login/" class="block px-4 py-3 rounded-xl border border-slate-200 text-slate-600 font-bold text-center hover:bg-slate-50">
                            Masuk
                        </a>
                        <a href="<?= $base_url ?>/user/register/" class="block px-4 py-3 rounded-xl bg-brand-600 text-white font-bold text-center hover:bg-brand-700 shadow-lg shadow-brand-500/30">
                            Daftar
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    
    <div class="h-20 lg:h-24"></div>