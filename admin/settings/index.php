<?php
// 1. INISIALISASI & LOGIC (Tanpa Output HTML)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include Config & Auth secara manual di awal
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/admin/auth.php';

// Cek Permission Superadmin
if (!isSuperAdmin()) {
    // Redirect langsung jika bukan superadmin (mencegah error header)
    header("Location: /situs-rental-gedung/admin/");
    exit;
}

// Fungsi Helper Upload (Didefinisikan disini agar aman dari dependensi header)
function handle_upload($file_key, $target_dir) {
    if (!isset($_FILES[$file_key]) || $_FILES[$file_key]['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false];
    }

    $file = $_FILES[$file_key];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'ico', 'svg'];
    
    if (!in_array($ext, $allowed)) return ['success' => false, 'message' => 'Format file tidak valid'];
    
    // Pastikan folder tujuan ada
    $abs_target_dir = __DIR__ . '/' . $target_dir;
    if (!file_exists($abs_target_dir)) {
        mkdir($abs_target_dir, 0777, true);
    }

    // Nama file unik
    $filename = uniqid('img_') . '.' . $ext;
    $target_file = $abs_target_dir . $filename;
    
    // Path untuk disimpan di database (relative dari root project)
    // Kita bersihkan path relatif ../../ untuk DB
    $db_path = str_replace('../../', '', $target_dir) . $filename;

    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        return ['success' => true, 'path' => $db_path];
    }
    
    return ['success' => false];
}

$error = '';

// --- HANDLE POST SUBMISSION ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = getDB();
        
        // 1. Handle File Uploads
        
        // Logo Utama -> uploads/logos/
        $up_logo = handle_upload('logo_url', '../../uploads/logos/');
        if ($up_logo['success']) update_setting('logo_url', $up_logo['path']);
        
        // Favicon -> uploads/favicon/
        $up_fav = handle_upload('favicon_url', '../../uploads/favicon/');
        if ($up_fav['success']) update_setting('favicon_url', $up_fav['path']);
        
        // Logo Kop Surat -> uploads/laporan/
        $up_kop = handle_upload('kop_surat_logo', '../../uploads/laporan/');
        if ($up_kop['success']) update_setting('kop_surat_logo', $up_kop['path']);
        
        // Tanda Tangan -> uploads/logos/
        $up_ttd = handle_upload('ttd_image', '../../uploads/logos/');
        if ($up_ttd['success']) update_setting('ttd_image', $up_ttd['path']);
        
        // 2. Update Text Settings
        $text_fields = [
            'nama_website', 'nama_panjang', 'app_theme', 
            'instansi_nama', 'instansi_alamat', 'instansi_telepon', 'instansi_email',
            'company_address', 'company_email', 'company_phone', 'footer_copyright',
            'social_instagram', 'social_facebook',
            'ttd_nama', 'ttd_nip', 'ttd_jabatan'
        ];
        
        foreach ($text_fields as $field) {
            if (isset($_POST[$field])) {
                update_setting($field, trim($_POST[$field]));
            }
        }
        
        // 3. DIRECT REFRESH (Solusi Error Header)
        // Redirect ke halaman sendiri untuk memuat ulang settingan baru & CSS tema
        header("Location: index.php?status=saved");
        exit;
        
    } catch (Exception $e) {
        $error = 'Terjadi kesalahan: ' . $e->getMessage();
    }
}

// 2. TAMPILKAN VIEW (Baru boleh include header di sini)
require_once __DIR__ . '/../../includes/admin/header_admin.php'; 

// Ambil setting terbaru untuk ditampilkan di form
$settings = get_all_settings();
?>

<div class="mb-6 lg:mb-8 animate-entry">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl lg:text-3xl font-bold text-slate-800 dark:text-white mb-1">Pengaturan Sistem</h1>
            <p class="text-sm lg:text-base text-slate-600 dark:text-slate-400">Kelola identitas, tema, dan konfigurasi website.</p>
        </div>
    </div>
</div>

<form method="POST" enctype="multipart/form-data" id="settingsForm" class="animate-entry" style="animation-delay: 0.1s">
    
    <div class="bg-white dark:bg-slate-800 rounded-t-2xl shadow-sm border border-slate-200 dark:border-slate-700 border-b-0 overflow-hidden transition-colors duration-300">
        <nav class="flex flex-nowrap overflow-x-auto scrollbar-hide" id="settingsTabs">
            <button type="button" onclick="switchTab('branding')" id="btn-branding"
                class="tab-btn active flex-shrink-0 flex items-center gap-2 px-5 lg:px-6 py-4 text-sm font-bold border-b-2 border-primary text-primary transition-colors whitespace-nowrap">
                <i class="fa-solid fa-palette"></i> Branding & Tema
            </button>
            <button type="button" onclick="switchTab('public')" id="btn-public"
                class="tab-btn flex-shrink-0 flex items-center gap-2 px-5 lg:px-6 py-4 text-sm font-bold border-b-2 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors whitespace-nowrap">
                <i class="fa-solid fa-globe"></i> Kontak & Footer
            </button>
            <button type="button" onclick="switchTab('kop')" id="btn-kop"
                class="tab-btn flex-shrink-0 flex items-center gap-2 px-5 lg:px-6 py-4 text-sm font-bold border-b-2 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors whitespace-nowrap">
                <i class="fa-solid fa-file-signature"></i> Kop Surat
            </button>
            <button type="button" onclick="switchTab('institusi')" id="btn-institusi"
                class="tab-btn flex-shrink-0 flex items-center gap-2 px-5 lg:px-6 py-4 text-sm font-bold border-b-2 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors whitespace-nowrap">
                <i class="fa-solid fa-building-columns"></i> Data Institusi
            </button>
        </nav>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-b-2xl shadow-lg border border-slate-200 dark:border-slate-700 p-5 lg:p-8 transition-colors duration-300">
        
        <div id="tab-branding" class="tab-content block space-y-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="space-y-6">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white border-b dark:border-slate-700 pb-2">Identitas Website</h3>
                    
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
                            
                            <label class="cursor-pointer relative group">
                                <input type="radio" name="app_theme" value="indigo" class="peer sr-only" <?= ($settings['app_theme'] ?? 'indigo') == 'indigo' ? 'checked' : '' ?>>
                                <div class="p-4 rounded-xl border-2 border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-700/50 peer-checked:border-indigo-500 peer-checked:bg-indigo-50 dark:peer-checked:bg-indigo-900/20 transition-all text-center h-full flex flex-col items-center justify-center">
                                    <div class="h-8 w-8 rounded-full bg-[#4F46E5] mb-2 shadow-md shadow-indigo-200 dark:shadow-none ring-2 ring-offset-2 ring-indigo-500 dark:ring-offset-slate-900"></div>
                                    <span class="text-xs font-bold text-slate-600 dark:text-slate-300 peer-checked:text-indigo-700 dark:peer-checked:text-indigo-400">Indigo Royal</span>
                                </div>
                                <div class="absolute top-2 right-2 text-indigo-600 dark:text-indigo-400 opacity-0 peer-checked:opacity-100 scale-0 peer-checked:scale-100 transition-all">
                                    <i class="fa-solid fa-circle-check text-lg"></i>
                                </div>
                            </label>

                            <label class="cursor-pointer relative group">
                                <input type="radio" name="app_theme" value="ocean" class="peer sr-only" <?= ($settings['app_theme'] ?? '') == 'ocean' ? 'checked' : '' ?>>
                                <div class="p-4 rounded-xl border-2 border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-700/50 peer-checked:border-sky-500 peer-checked:bg-sky-50 dark:peer-checked:bg-sky-900/20 transition-all text-center h-full flex flex-col items-center justify-center">
                                    <div class="h-8 w-8 rounded-full bg-[#0ea5e9] mb-2 shadow-md shadow-sky-200 dark:shadow-none ring-2 ring-offset-2 ring-sky-500 dark:ring-offset-slate-900"></div>
                                    <span class="text-xs font-bold text-slate-600 dark:text-slate-300 peer-checked:text-sky-700 dark:peer-checked:text-sky-400">Ocean Blue</span>
                                </div>
                                <div class="absolute top-2 right-2 text-sky-600 dark:text-sky-400 opacity-0 peer-checked:opacity-100 scale-0 peer-checked:scale-100 transition-all">
                                    <i class="fa-solid fa-circle-check text-lg"></i>
                                </div>
                            </label>

                            <label class="cursor-pointer relative group">
                                <input type="radio" name="app_theme" value="nature" class="peer sr-only" <?= ($settings['app_theme'] ?? '') == 'nature' ? 'checked' : '' ?>>
                                <div class="p-4 rounded-xl border-2 border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-700/50 peer-checked:border-green-600 peer-checked:bg-green-50 dark:peer-checked:bg-green-900/20 transition-all text-center h-full flex flex-col items-center justify-center">
                                    <div class="h-8 w-8 rounded-full bg-[#16a34a] mb-2 shadow-md shadow-green-200 dark:shadow-none ring-2 ring-offset-2 ring-green-600 dark:ring-offset-slate-900"></div>
                                    <span class="text-xs font-bold text-slate-600 dark:text-slate-300 peer-checked:text-green-700 dark:peer-checked:text-green-400">Nature Green</span>
                                </div>
                                <div class="absolute top-2 right-2 text-green-600 dark:text-green-400 opacity-0 peer-checked:opacity-100 scale-0 peer-checked:scale-100 transition-all">
                                    <i class="fa-solid fa-circle-check text-lg"></i>
                                </div>
                            </label>

                            <label class="cursor-pointer relative group">
                                <input type="radio" name="app_theme" value="rose" class="peer sr-only" <?= ($settings['app_theme'] ?? '') == 'rose' ? 'checked' : '' ?>>
                                <div class="p-4 rounded-xl border-2 border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-700/50 peer-checked:border-rose-500 peer-checked:bg-rose-50 dark:peer-checked:bg-rose-900/20 transition-all text-center h-full flex flex-col items-center justify-center">
                                    <div class="h-8 w-8 rounded-full bg-[#e11d48] mb-2 shadow-md shadow-rose-200 dark:shadow-none ring-2 ring-offset-2 ring-rose-500 dark:ring-offset-slate-900"></div>
                                    <span class="text-xs font-bold text-slate-600 dark:text-slate-300 peer-checked:text-rose-700 dark:peer-checked:text-rose-400">Elegant Rose</span>
                                </div>
                                <div class="absolute top-2 right-2 text-rose-600 dark:text-rose-400 opacity-0 peer-checked:opacity-100 scale-0 peer-checked:scale-100 transition-all">
                                    <i class="fa-solid fa-circle-check text-lg"></i>
                                </div>
                            </label>

                            <label class="cursor-pointer relative group">
                                <input type="radio" name="app_theme" value="sunset" class="peer sr-only" <?= ($settings['app_theme'] ?? '') == 'sunset' ? 'checked' : '' ?>>
                                <div class="p-4 rounded-xl border-2 border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-700/50 peer-checked:border-orange-500 peer-checked:bg-orange-50 dark:peer-checked:bg-orange-900/20 transition-all text-center h-full flex flex-col items-center justify-center">
                                    <div class="h-8 w-8 rounded-full bg-[#ea580c] mb-2 shadow-md shadow-orange-200 dark:shadow-none ring-2 ring-offset-2 ring-orange-500 dark:ring-offset-slate-900"></div>
                                    <span class="text-xs font-bold text-slate-600 dark:text-slate-300 peer-checked:text-orange-700 dark:peer-checked:text-orange-400">Sunset Orange</span>
                                </div>
                                <div class="absolute top-2 right-2 text-orange-600 dark:text-orange-400 opacity-0 peer-checked:opacity-100 scale-0 peer-checked:scale-100 transition-all">
                                    <i class="fa-solid fa-circle-check text-lg"></i>
                                </div>
                            </label>

                            <label class="cursor-pointer relative group">
                                <input type="radio" name="app_theme" value="teal" class="peer sr-only" <?= ($settings['app_theme'] ?? '') == 'teal' ? 'checked' : '' ?>>
                                <div class="p-4 rounded-xl border-2 border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-700/50 peer-checked:border-teal-500 peer-checked:bg-teal-50 dark:peer-checked:bg-teal-900/20 transition-all text-center h-full flex flex-col items-center justify-center">
                                    <div class="h-8 w-8 rounded-full bg-[#0d9488] mb-2 shadow-md shadow-teal-200 dark:shadow-none ring-2 ring-offset-2 ring-teal-500 dark:ring-offset-slate-900"></div>
                                    <span class="text-xs font-bold text-slate-600 dark:text-slate-300 peer-checked:text-teal-700 dark:peer-checked:text-teal-400">Teal Professional</span>
                                </div>
                                <div class="absolute top-2 right-2 text-teal-600 dark:text-teal-400 opacity-0 peer-checked:opacity-100 scale-0 peer-checked:scale-100 transition-all">
                                    <i class="fa-solid fa-circle-check text-lg"></i>
                                </div>
                            </label>

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

        <div id="tab-public" class="tab-content hidden space-y-8">
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
            text: "Halaman akan dimuat ulang untuk menerapkan tema baru.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: tailwind.config.theme.extend.colors.primary || '#3085d6',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'Ya, Simpan!',
            cancelButtonText: 'Batal',
            // Adapt SweetAlert to Dark Mode
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
                    // Adapt Loading to Dark Mode
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