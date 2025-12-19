<?php
// 1. INISIALISASI & LOGIC
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/admin/auth.php';

// Cek Permission Superadmin
if (!isSuperAdmin()) {
    header("Location: /situs-rental-gedung/admin/");
    exit;
}

// --- FUNGSI HELPER ---

// 1. Fungsi Upload File Baru
function handle_upload($file_key, $target_dir) {
    if (!isset($_FILES[$file_key]) || $_FILES[$file_key]['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false];
    }

    $file = $_FILES[$file_key];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'ico', 'svg', 'webp'];
    
    if (!in_array($ext, $allowed)) return ['success' => false, 'message' => 'Format file tidak valid'];
    
    // Pastikan folder tujuan ada
    $abs_target_dir = __DIR__ . '/' . $target_dir;
    if (!file_exists($abs_target_dir)) {
        mkdir($abs_target_dir, 0777, true);
    }

    // Buat nama unik agar tidak bentrok
    $filename = uniqid('img_') . '.' . $ext;
    $target_file = $abs_target_dir . $filename;
    
    // Path relatif untuk disimpan di database (menghilangkan ../../)
    $db_path = str_replace('../../', '', $target_dir) . $filename;

    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        return ['success' => true, 'path' => $db_path];
    }
    
    return ['success' => false];
}

// 2. Fungsi Hapus File Lama
function delete_old_file($db_key) {
    try {
        $db = getDB();
        // Ambil path file lama dari database
        $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([$db_key]);
        $old_path = $stmt->fetchColumn();

        if ($old_path) {
            // Konversi path DB ke Absolute Path server
            // Path di DB: uploads/logos/img.jpg
            // Kita perlu mundur 2 folder dari admin/settings/index.php
            $full_path = __DIR__ . '/../../' . $old_path;
            
            // Hapus jika file ada
            if (file_exists($full_path) && is_file($full_path)) {
                unlink($full_path);
            }
        }
    } catch (Exception $e) {
        // Abaikan error delete agar proses update tetap berjalan
        error_log("Gagal menghapus file lama: " . $e->getMessage());
    }
}

$error = '';

// --- HANDLE POST SUBMISSION ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = getDB();
        
        // A. HANDLE GAMBAR (Upload Baru -> Hapus Lama -> Update DB)
        $image_fields = [
            'logo_url'          => '../../uploads/logos/',
            'favicon_url'       => '../../uploads/favicon/',
            'kop_surat_logo'    => '../../uploads/laporan/',
            'ttd_image'         => '../../uploads/logos/',
            'public_hero_image' => '../../uploads/hero/'
        ];

        foreach ($image_fields as $field_key => $target_dir) {
            // Cek apakah user mengupload file untuk field ini
            if (isset($_FILES[$field_key]) && $_FILES[$field_key]['error'] === UPLOAD_ERR_OK) {
                
                // 1. Upload file baru dulu
                $upload = handle_upload($field_key, $target_dir);
                
                if ($upload['success']) {
                    // 2. Jika upload sukses, HAPUS file lama
                    delete_old_file($field_key);
                    
                    // 3. Update database dengan path baru
                    update_setting($field_key, $upload['path']);
                }
            }
        }
        
        // B. HANDLE TEXT SETTINGS
        $text_fields = [
            // Branding & Theme
            'nama_website', 'nama_panjang', 'app_theme', 
            // Public Page
            'maintenance_mode', 'maintenance_message', 'public_theme',
            // Institusi
            'instansi_nama', 'instansi_alamat', 'instansi_telepon', 'instansi_email',
            // Kontak
            'company_address', 'company_email', 'company_phone', 'footer_copyright',
            // Sosmed
            'social_instagram', 'social_facebook',
            // TTD
            'ttd_nama', 'ttd_nip', 'ttd_jabatan',
            // Payment Gateway
            'xendit_api_key', 'xendit_callback_token'
        ];
        
        foreach ($text_fields as $field) {
            if (isset($_POST[$field])) {
                update_setting($field, trim($_POST[$field]));
            }
        }
        
        // C. REFRESH HALAMAN
        header("Location: index.php?status=saved");
        exit;
        
    } catch (Exception $e) {
        $error = 'Terjadi kesalahan: ' . $e->getMessage();
    }
}

// 2. TAMPILKAN VIEW
require_once __DIR__ . '/../../includes/admin/header_admin.php'; 

// Ambil setting terbaru untuk ditampilkan di form
$settings = get_all_settings();
?>

<div class="mb-6 lg:mb-8 animate-entry">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl lg:text-3xl font-bold text-slate-800 dark:text-white mb-1">Pengaturan Sistem</h1>
            <p class="text-sm lg:text-base text-slate-600 dark:text-slate-400">Kelola identitas, tema, halaman publik, dan konfigurasi website.</p>
        </div>
    </div>
</div>

<form method="POST" enctype="multipart/form-data" id="settingsForm" class="animate-entry" style="animation-delay: 0.1s">
    
    <div class="bg-white dark:bg-slate-800 rounded-t-2xl shadow-sm border border-slate-200 dark:border-slate-700 border-b-0 overflow-hidden transition-colors duration-300">
        <nav class="flex flex-nowrap overflow-x-auto scrollbar-hide" id="settingsTabs">
            <button type="button" onclick="switchTab('branding')" id="btn-branding"
                class="tab-btn active flex-shrink-0 flex items-center gap-2 px-5 lg:px-6 py-4 text-sm font-bold border-b-2 border-primary text-primary transition-colors whitespace-nowrap">
                <i class="fa-solid fa-palette"></i> Branding (Admin)
            </button>
            
            <button type="button" onclick="switchTab('landing')" id="btn-landing"
                class="tab-btn flex-shrink-0 flex items-center gap-2 px-5 lg:px-6 py-4 text-sm font-bold border-b-2 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors whitespace-nowrap">
                <i class="fa-solid fa-desktop"></i> Halaman Public
            </button>

            <button type="button" onclick="switchTab('contact')" id="btn-contact"
                class="tab-btn flex-shrink-0 flex items-center gap-2 px-5 lg:px-6 py-4 text-sm font-bold border-b-2 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors whitespace-nowrap">
                <i class="fa-solid fa-address-book"></i> Kontak
            </button>

            <button type="button" onclick="switchTab('payment')" id="btn-payment"
                class="tab-btn flex-shrink-0 flex items-center gap-2 px-5 lg:px-6 py-4 text-sm font-bold border-b-2 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors whitespace-nowrap">
                <i class="fa-solid fa-credit-card"></i> Payment
            </button>
            
            <button type="button" onclick="switchTab('kop')" id="btn-kop"
                class="tab-btn flex-shrink-0 flex items-center gap-2 px-5 lg:px-6 py-4 text-sm font-bold border-b-2 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors whitespace-nowrap">
                <i class="fa-solid fa-file-signature"></i> Kop Surat
            </button>
            
            <button type="button" onclick="switchTab('institusi')" id="btn-institusi"
                class="tab-btn flex-shrink-0 flex items-center gap-2 px-5 lg:px-6 py-4 text-sm font-bold border-b-2 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors whitespace-nowrap">
                <i class="fa-solid fa-building-columns"></i> Institusi
            </button>
        </nav>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-b-2xl shadow-lg border border-slate-200 dark:border-slate-700 p-5 lg:p-8 transition-colors duration-300">
        
        <div id="tab-branding" class="tab-content block space-y-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="space-y-6">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white border-b dark:border-slate-700 pb-2">Identitas Admin Panel</h3>
                    
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Nama Website (Singkat)</label>
                        <input type="text" name="nama_website" value="<?= htmlspecialchars($settings['nama_website'] ?? '') ?>" 
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all"
                            placeholder="GedungKita">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Tagline / Nama Panjang</label>
                        <input type="text" name="nama_panjang" value="<?= htmlspecialchars($settings['nama_panjang'] ?? '') ?>" 
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all"
                            placeholder="Sistem Reservasi Gedung Serbaguna">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-3">Pilih Tema Aplikasi</label>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                            <?php 
                            $themes = [
                                'indigo' => ['color' => '#4F46E5', 'name' => 'Indigo Royal', 'ring' => 'indigo'],
                                'ocean' => ['color' => '#0ea5e9', 'name' => 'Ocean Blue', 'ring' => 'sky'],
                                'nature' => ['color' => '#16a34a', 'name' => 'Nature Green', 'ring' => 'green'],
                                'rose' => ['color' => '#e11d48', 'name' => 'Elegant Rose', 'ring' => 'rose'],
                                'sunset' => ['color' => '#ea580c', 'name' => 'Sunset Orange', 'ring' => 'orange'],
                                'teal' => ['color' => '#0d9488', 'name' => 'Teal Professional', 'ring' => 'teal'],
                            ];
                            foreach($themes as $key => $theme): ?>
                            <label class="cursor-pointer relative group">
                                <input type="radio" name="app_theme" value="<?= $key ?>" class="peer sr-only" <?= ($settings['app_theme'] ?? 'indigo') == $key ? 'checked' : '' ?>>
                                <div class="p-4 rounded-xl border-2 border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-700/50 peer-checked:border-<?= $theme['ring'] ?>-500 peer-checked:bg-<?= $theme['ring'] ?>-50 dark:peer-checked:bg-<?= $theme['ring'] ?>-900/20 transition-all text-center h-full flex flex-col items-center justify-center">
                                    <div class="h-8 w-8 rounded-full bg-[<?= $theme['color'] ?>] mb-2 shadow-md shadow-<?= $theme['ring'] ?>-200 dark:shadow-none ring-2 ring-offset-2 ring-<?= $theme['ring'] ?>-500 dark:ring-offset-slate-900"></div>
                                    <span class="text-xs font-bold text-slate-600 dark:text-slate-300 peer-checked:text-<?= $theme['ring'] ?>-700 dark:peer-checked:text-<?= $theme['ring'] ?>-400"><?= $theme['name'] ?></span>
                                </div>
                                <div class="absolute top-2 right-2 text-<?= $theme['ring'] ?>-600 dark:text-<?= $theme['ring'] ?>-400 opacity-0 peer-checked:opacity-100 scale-0 peer-checked:scale-100 transition-all">
                                    <i class="fa-solid fa-circle-check text-lg"></i>
                                </div>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white border-b dark:border-slate-700 pb-2">Logo & Aset</h3>
                    
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Logo Website</label>
                        <div class="flex flex-col sm:flex-row items-start gap-4 p-4 bg-slate-50 dark:bg-slate-700/50 rounded-xl border border-slate-200 dark:border-slate-600">
                            <?php if (!empty($settings['logo_url'])): ?>
                                <div class="bg-white dark:bg-slate-800 p-2 rounded-lg shadow-sm border border-slate-100 dark:border-slate-600 w-full sm:w-auto flex justify-center">
                                    <img src="/situs-rental-gedung/<?= htmlspecialchars($settings['logo_url']) ?>" class="h-12 w-auto object-contain">
                                </div>
                            <?php endif; ?>
                            <div class="flex-1 w-full">
                                <input type="file" name="logo_url" accept="image/*" class="w-full text-sm text-slate-500 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 transition-all">
                                <p class="text-xs text-slate-400 dark:text-slate-500 mt-2">Format: JPG, PNG. Lokasi: <code>uploads/logos/</code></p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Favicon</label>
                        <div class="flex flex-col sm:flex-row items-start gap-4 p-4 bg-slate-50 dark:bg-slate-700/50 rounded-xl border border-slate-200 dark:border-slate-600">
                            <?php if (!empty($settings['favicon_url'])): ?>
                                <div class="bg-white dark:bg-slate-800 p-2 rounded-lg shadow-sm border border-slate-100 dark:border-slate-600 w-full sm:w-auto flex justify-center">
                                    <img src="/situs-rental-gedung/<?= htmlspecialchars($settings['favicon_url']) ?>" class="h-8 w-8 object-contain">
                                </div>
                            <?php endif; ?>
                            <div class="flex-1 w-full">
                                <input type="file" name="favicon_url" accept="image/*" class="w-full text-sm text-slate-500 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 transition-all">
                                <p class="text-xs text-slate-400 dark:text-slate-500 mt-2">Format: ICO, PNG. Lokasi: <code>uploads/favicon/</code></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="tab-landing" class="tab-content hidden space-y-8">
            
            <div class="bg-slate-50 dark:bg-slate-700/30 rounded-2xl p-6 border border-slate-200 dark:border-slate-700">
                <div class="flex flex-col md:flex-row gap-6 md:items-start">
                    <div class="flex-1 space-y-4">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="p-2 bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-400 rounded-lg">
                                <i class="fa-solid fa-triangle-exclamation text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-slate-800 dark:text-white">Mode Maintenance</h3>
                                <p class="text-sm text-slate-500 dark:text-slate-400">Atur akses pengunjung ke halaman publik.</p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <label class="cursor-pointer relative w-full">
                                <input type="radio" name="maintenance_mode" value="0" class="peer sr-only" <?= ($settings['maintenance_mode'] ?? '0') == '0' ? 'checked' : '' ?>>
                                <div class="px-5 py-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 peer-checked:bg-green-50 peer-checked:border-green-500 dark:peer-checked:bg-green-900/20 text-slate-600 dark:text-slate-400 peer-checked:text-green-600 dark:peer-checked:text-green-400 transition-all flex items-center justify-center gap-2 shadow-sm h-full">
                                    <i class="fa-solid fa-globe"></i> Live / Online
                                </div>
                            </label>
                            <label class="cursor-pointer relative w-full">
                                <input type="radio" name="maintenance_mode" value="1" class="peer sr-only" <?= ($settings['maintenance_mode'] ?? '0') == '1' ? 'checked' : '' ?>>
                                <div class="px-5 py-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 peer-checked:bg-red-50 peer-checked:border-red-500 dark:peer-checked:bg-red-900/20 text-slate-600 dark:text-slate-400 peer-checked:text-red-600 dark:peer-checked:text-red-400 transition-all flex items-center justify-center gap-2 shadow-sm h-full">
                                    <i class="fa-solid fa-screwdriver-wrench"></i> Maintenance
                                </div>
                            </label>
                        </div>
                        </div>

                    <div class="flex-1">
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Pesan Maintenance</label>
                        <textarea name="maintenance_message" rows="3" 
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white focus:border-primary outline-none"
                            placeholder="Mohon maaf, website sedang dalam perbaikan..."><?= htmlspecialchars($settings['maintenance_message'] ?? 'Website sedang dalam perbaikan. Silakan kembali nanti.') ?></textarea>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                <div>
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white border-b dark:border-slate-700 pb-2 mb-4">Tema Halaman Depan</h3>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-3">Pilih Varian Warna</label>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        <?php 
                        $themes = [
                            'indigo' => ['color' => '#4F46E5', 'name' => 'Indigo Royal', 'ring' => 'indigo'],
                            'ocean' => ['color' => '#0ea5e9', 'name' => 'Ocean Blue', 'ring' => 'sky'],
                            'nature' => ['color' => '#16a34a', 'name' => 'Nature Green', 'ring' => 'green'],
                            'rose' => ['color' => '#e11d48', 'name' => 'Elegant Rose', 'ring' => 'rose'],
                            'sunset' => ['color' => '#ea580c', 'name' => 'Sunset Orange', 'ring' => 'orange'],
                            'teal' => ['color' => '#0d9488', 'name' => 'Teal Professional', 'ring' => 'teal'],
                        ];
                        foreach($themes as $key => $theme): ?>
                        <label class="cursor-pointer relative group">
                            <input type="radio" name="public_theme" value="<?= $key ?>" class="peer sr-only" <?= ($settings['public_theme'] ?? 'ocean') == $key ? 'checked' : '' ?>>
                            <div class="p-4 rounded-xl border-2 border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-700/50 peer-checked:border-<?= $theme['ring'] ?>-500 peer-checked:bg-<?= $theme['ring'] ?>-50 dark:peer-checked:bg-<?= $theme['ring'] ?>-900/20 transition-all text-center h-full flex flex-col items-center justify-center">
                                <div class="h-8 w-8 rounded-full mb-2 shadow-md shadow-<?= $theme['ring'] ?>-200 dark:shadow-none ring-2 ring-offset-2 ring-<?= $theme['ring'] ?>-500 dark:ring-offset-slate-900" style="background-color: <?= $theme['color'] ?>"></div>
                                <span class="text-xs font-bold text-slate-600 dark:text-slate-300 peer-checked:text-<?= $theme['ring'] ?>-700 dark:peer-checked:text-<?= $theme['ring'] ?>-400"><?= $theme['name'] ?></span>
                            </div>
                            <div class="absolute top-2 right-2 text-<?= $theme['ring'] ?>-600 dark:text-<?= $theme['ring'] ?>-400 opacity-0 peer-checked:opacity-100 scale-0 peer-checked:scale-100 transition-all">
                                <i class="fa-solid fa-circle-check text-lg"></i>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white border-b dark:border-slate-700 pb-2 mb-4">Hero Image</h3>
                    <div class="space-y-4">
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300">Gambar Banner Utama</label>
                        <div class="flex flex-col items-start gap-4 p-4 bg-slate-50 dark:bg-slate-700/50 rounded-xl border border-slate-200 dark:border-slate-600">
                            <?php if (!empty($settings['public_hero_image'])): ?>
                                <div class="w-full relative group rounded-lg overflow-hidden shadow-sm border border-slate-100 dark:border-slate-600">
                                    <img src="/situs-rental-gedung/<?= htmlspecialchars($settings['public_hero_image']) ?>" class="w-full h-48 object-cover">
                                    <div class="absolute inset-0 bg-black/50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                        <span class="text-white text-xs font-bold">Gambar Saat Ini</span>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <div class="flex-1 w-full">
                                <input type="file" name="public_hero_image" accept="image/*" class="w-full text-sm text-slate-500 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 transition-all">
                                <p class="text-xs text-slate-400 dark:text-slate-500 mt-2">
                                    Disarankan ukuran: 1920x1080px (Landscape).<br>
                                    Format: JPG, PNG, WEBP. Lokasi: <code>uploads/hero/</code>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div id="tab-contact" class="tab-content hidden space-y-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="space-y-6">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white border-b dark:border-slate-700 pb-2">Informasi Kontak</h3>
                    
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Alamat Kantor</label>
                        <textarea name="company_address" rows="3" 
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all"
                            placeholder="Alamat lengkap footer"><?= htmlspecialchars($settings['company_address'] ?? '') ?></textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Email Support</label>
                            <input type="email" name="company_email" value="<?= htmlspecialchars($settings['company_email'] ?? '') ?>" 
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white focus:border-primary outline-none" placeholder="support@domain.com">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">No. Telepon / WA</label>
                            <input type="text" name="company_phone" value="<?= htmlspecialchars($settings['company_phone'] ?? '') ?>" 
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white focus:border-primary outline-none" placeholder="+62...">
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white border-b dark:border-slate-700 pb-2">Footer & Sosial Media</h3>
                    
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Teks Copyright</label>
                        <input type="text" name="footer_copyright" value="<?= htmlspecialchars($settings['footer_copyright'] ?? '') ?>" 
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white focus:border-primary outline-none" 
                            placeholder="© 2025 Rental Gedung.">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2"><i class="fa-brands fa-instagram text-pink-600 mr-1"></i> Instagram URL</label>
                            <input type="text" name="social_instagram" value="<?= htmlspecialchars($settings['social_instagram'] ?? '') ?>" 
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white focus:border-primary outline-none" placeholder="https://instagram.com/...">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2"><i class="fa-brands fa-facebook text-blue-600 mr-1"></i> Facebook URL</label>
                            <input type="text" name="social_facebook" value="<?= htmlspecialchars($settings['social_facebook'] ?? '') ?>" 
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white focus:border-primary outline-none" placeholder="https://facebook.com/...">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="tab-payment" class="tab-content hidden space-y-8">
            <div class="space-y-6 max-w-3xl">
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4 text-sm text-blue-700 dark:text-blue-300 flex items-start gap-3">
                    <i class="fa-solid fa-circle-info mt-0.5 text-lg"></i>
                    <div>
                        <p class="font-bold">Konfigurasi Xendit Payment Gateway</p>
                        <p class="mt-1">Masukkan API Key dari Dashboard Xendit Anda. Pastikan menggunakan mode yang sesuai (Test/Live).</p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Xendit Secret API Key</label>
                    <div class="relative">
                        <input type="password" name="xendit_api_key" value="<?= htmlspecialchars($settings['xendit_api_key'] ?? '') ?>" 
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white focus:border-primary outline-none font-mono"
                            placeholder="xnd_development_...">
                        <p class="text-xs text-slate-500 mt-2">Dapatkan di <a href="https://dashboard.xendit.co/settings/developers#api-keys" target="_blank" class="text-primary hover:underline">Dashboard Xendit > Settings > API Keys</a>.</p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Callback Verification Token</label>
                    <input type="text" name="xendit_callback_token" value="<?= htmlspecialchars($settings['xendit_callback_token'] ?? '') ?>" 
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white focus:border-primary outline-none font-mono"
                        placeholder="Token verifikasi webhook...">
                    <p class="text-xs text-slate-500 mt-2">Digunakan untuk memvalidasi request webhook (Opsional tapi direkomendasikan).</p>
                </div>

                <div class="p-4 bg-slate-100 dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
                    <p class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Webhook URL untuk Xendit:</p>
                    <div class="flex items-center gap-2">
                        <code class="flex-1 bg-white dark:bg-slate-900 px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-600 font-mono text-xs select-all text-slate-600 dark:text-slate-400">
                            <?= (isset($_SERVER['HTTPS']) ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . "/situs-rental-gedung/api/xendit/" ?>
                        </code>
                        <button type="button" onclick="navigator.clipboard.writeText(this.previousElementSibling.innerText); Swal.fire({icon:'success', title:'Disalin!', toast:true, position:'top-end', showConfirmButton:false, timer:1500})" 
                                class="p-2 rounded-lg bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 hover:text-primary transition-colors">
                            <i class="fa-regular fa-copy"></i>
                        </button>
                    </div>
                    <p class="text-xs text-slate-500 mt-2">Masukkan URL ini di Dashboard Xendit pada menu <b>Settings > Callbacks > Invoices</b>.</p>
                </div>
            </div>
        </div>

        <div id="tab-kop" class="tab-content hidden space-y-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="space-y-6">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white border-b dark:border-slate-700 pb-2">Header Laporan</h3>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Logo Institusi (Kop)</label>
                        <div class="flex flex-col sm:flex-row items-start gap-4 p-4 bg-slate-50 dark:bg-slate-700/50 rounded-xl border border-slate-200 dark:border-slate-600">
                            <?php if (!empty($settings['kop_surat_logo'])): ?>
                                <div class="bg-white dark:bg-slate-800 p-2 rounded-lg shadow-sm border border-slate-100 dark:border-slate-600 w-full sm:w-auto flex justify-center">
                                    <img src="/situs-rental-gedung/<?= htmlspecialchars($settings['kop_surat_logo']) ?>" class="h-16 w-auto object-contain">
                                </div>
                            <?php endif; ?>
                            <div class="flex-1 w-full">
                                <input type="file" name="kop_surat_logo" accept="image/*" class="w-full text-sm text-slate-500 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary transition-all">
                                <p class="text-xs text-slate-400 dark:text-slate-500 mt-2">Lokasi: <code>uploads/laporan/</code></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white border-b dark:border-slate-700 pb-2">Penandatangan Laporan</h3>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Nama Lengkap</label>
                            <input type="text" name="ttd_nama" value="<?= htmlspecialchars($settings['ttd_nama'] ?? '') ?>" 
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white focus:border-primary outline-none" placeholder="Budi Santoso, S.Kom">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">NIP / ID</label>
                            <input type="text" name="ttd_nip" value="<?= htmlspecialchars($settings['ttd_nip'] ?? '') ?>" 
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white focus:border-primary outline-none" placeholder="198...">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Jabatan</label>
                        <input type="text" name="ttd_jabatan" value="<?= htmlspecialchars($settings['ttd_jabatan'] ?? '') ?>" 
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white focus:border-primary outline-none" placeholder="Kepala UPT">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Scan Tanda Tangan</label>
                        <div class="flex flex-col sm:flex-row items-start gap-4 p-4 bg-slate-50 dark:bg-slate-700/50 rounded-xl border border-slate-200 dark:border-slate-600">
                            <?php if (!empty($settings['ttd_image'])): ?>
                                <div class="bg-white dark:bg-slate-800 p-2 rounded-lg shadow-sm border border-slate-100 dark:border-slate-600 w-full sm:w-auto flex justify-center">
                                    <img src="/situs-rental-gedung/<?= htmlspecialchars($settings['ttd_image']) ?>" class="h-12 w-auto object-contain">
                                </div>
                            <?php endif; ?>
                            <div class="flex-1 w-full">
                                <input type="file" name="ttd_image" accept="image/*" class="w-full text-sm text-slate-500 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary transition-all">
                                <p class="text-xs text-slate-400 dark:text-slate-500 mt-2">Lokasi: <code>uploads/logos/</code></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="tab-institusi" class="tab-content hidden space-y-8">
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4 text-sm text-blue-700 dark:text-blue-300 flex items-start gap-3">
                <i class="fa-solid fa-circle-info mt-0.5"></i>
                <p>Data ini digunakan untuk keperluan internal surat-menyurat dan dokumen resmi.</p>
            </div>

            <div class="grid grid-cols-1 gap-6 max-w-2xl">
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Nama Resmi Institusi</label>
                    <input type="text" name="instansi_nama" value="<?= htmlspecialchars($settings['instansi_nama'] ?? '') ?>" 
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white focus:border-primary outline-none">
                </div>
                
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Alamat Lengkap</label>
                    <textarea name="instansi_alamat" rows="3" 
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white focus:border-primary outline-none"><?= htmlspecialchars($settings['instansi_alamat'] ?? '') ?></textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Telepon Kantor</label>
                        <input type="text" name="instansi_telepon" value="<?= htmlspecialchars($settings['instansi_telepon'] ?? '') ?>" 
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white focus:border-primary outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Email Resmi</label>
                        <input type="email" name="instansi_email" value="<?= htmlspecialchars($settings['instansi_email'] ?? '') ?>" 
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white focus:border-primary outline-none">
                    </div>
                </div>
            </div>
        </div>

    </div>
    
    <div class="mt-8 flex justify-end pb-8">
        <button type="submit" 
            class="w-full sm:w-auto px-8 py-4 rounded-xl bg-gradient-to-r from-primary to-secondary hover:brightness-110 text-white font-bold text-lg shadow-lg shadow-primary/30 transform hover:-translate-y-1 transition-all">
            <i class="fa-solid fa-save mr-2"></i>Simpan Perubahan
        </button>
    </div>
</form>

<script>
    // Tab Switching Logic
    function switchTab(tabId) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active', 'border-primary', 'text-primary');
            // Reset to inactive state (including dark mode)
            btn.classList.add('border-transparent', 'text-slate-500', 'dark:text-slate-400');
        });
        
        document.getElementById('tab-' + tabId).classList.remove('hidden');
        const activeBtn = document.getElementById('btn-' + tabId);
        
        // Set active state
        activeBtn.classList.add('active', 'border-primary', 'text-primary');
        activeBtn.classList.remove('border-transparent', 'text-slate-500', 'dark:text-slate-400');
    }

    // Confirmation Alert Logic with Auto Submit
    document.getElementById('settingsForm').addEventListener('submit', function(e) {
        e.preventDefault(); // Mencegah submit langsung
        
        Swal.fire({
            title: 'Simpan Perubahan?',
            text: "Halaman akan dimuat ulang untuk menerapkan pengaturan baru.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: tailwind.config.theme.extend.colors.primary || '#3085d6',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'Ya, Simpan!',
            cancelButtonText: 'Batal',
            background: document.documentElement.classList.contains('dark') ? '#1e293b' : '#fff',
            color: document.documentElement.classList.contains('dark') ? '#f8fafc' : '#1e293b'
        }).then((result) => {
            if (result.isConfirmed) {
                // Tampilkan Loading
                Swal.fire({
                    title: 'Menyimpan...',
                    text: 'Mohon tunggu sebentar',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    background: document.documentElement.classList.contains('dark') ? '#1e293b' : '#fff',
                    color: document.documentElement.classList.contains('dark') ? '#f8fafc' : '#1e293b',
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Submit Form Manual
                this.submit();
            }
        });
    });

    // Alert Error PHP (Jika ada error saat POST)
    <?php if ($error): ?>
    document.addEventListener('DOMContentLoaded', () => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: "<?= addslashes($error) ?>",
            background: document.documentElement.classList.contains('dark') ? '#1e293b' : '#fff',
            color: document.documentElement.classList.contains('dark') ? '#f8fafc' : '#1e293b'
        });
    });
    <?php endif; ?>
</script>

<?php require_once __DIR__ . '/../../includes/admin/footer_admin.php'; ?>