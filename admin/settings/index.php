<?php
require_once __DIR__ . '/../../includes/admin/header_admin.php';
requireRole('superadmin'); // Only superadmin can access settings

$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = getDB();
        
        // Handle file uploads
        if (isset($_FILES['logo_url']) && $_FILES['logo_url']['error'] === UPLOAD_ERR_OK) {
            $upload = upload_logo('logo_url');
            if ($upload['success']) {
                update_setting('logo_url', $upload['path']);
            }
        }
        
        if (isset($_FILES['favicon_url']) && $_FILES['favicon_url']['error'] === UPLOAD_ERR_OK) {
            $upload = upload_logo('favicon_url', '../../../uploads/favicon/');
            if ($upload['success']) {
                update_setting('favicon_url', str_replace('logos', 'favicon', $upload['path']));
            }
        }
        
        if (isset($_FILES['kop_surat_logo']) && $_FILES['kop_surat_logo']['error'] === UPLOAD_ERR_OK) {
            $upload = upload_logo('kop_surat_logo');
            if ($upload['success']) {
                update_setting('kop_surat_logo', $upload['path']);
            }
        }
        
        if (isset($_FILES['ttd_image']) && $_FILES['ttd_image']['error'] === UPLOAD_ERR_OK) {
            $upload = upload_logo('ttd_image');
            if ($upload['success']) {
                update_setting('ttd_image', $upload['path']);
            }
        }
        
        // Update text settings
        $text_fields = [
            'nama_website', 'nama_panjang', 'theme_color',
            'instansi_nama', 'instansi_alamat', 'instansi_telepon', 'instansi_email',
            'ttd_nama', 'ttd_nip', 'ttd_jabatan'
        ];
        
        foreach ($text_fields as $field) {
            if (isset($_POST[$field])) {
                update_setting($field, trim($_POST[$field]));
            }
        }
        
        $success = 'Pengaturan berhasil disimpan!';
        
        // Refresh settings
        $settings = get_all_settings();
    } catch (Exception $e) {
        $error = 'Terjadi kesalahan: ' . $e->getMessage();
    }
}

// Get current settings
$settings = get_all_settings();
?>

<!-- Page Header -->
<div class="mb-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-slate-800 mb-2">Pengaturan Sistem</h1>
            <p class="text-slate-600">Kelola pengaturan website dan konfigurasi sistem</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="/situs-rental-gedung/admin/data/gedung/" 
                class="px-6 py-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold transition-all">
                <i class="fa-solid fa-arrow-left mr-2"></i>Kembali
            </a>
        </div>
    </div>
</div>

<!-- Success/Error Messages -->
<?php if ($success): ?>
<div class="mb-6 p-4 rounded-2xl bg-success-50 border-2 border-success-200 text-success-700 flex items-center gap-3">
    <i class="fa-solid fa-circle-check text-2xl"></i>
    <span class="font-semibold"><?= htmlspecialchars($success) ?></span>
</div>
<?php endif; ?>

<?php if ($error): ?>
<div class="mb-6 p-4 rounded-2xl bg-danger-50 border-2 border-danger-200 text-danger-700 flex items-center gap-3">
    <i class="fa-solid fa-circle-exclamation text-2xl"></i>
    <span class="font-semibold"><?= htmlspecialchars($error) ?></span>
</div>
<?php endif; ?>

<!-- Settings Form -->
<form method="POST" enctype="multipart/form-data">
    <!-- Tabs -->
    <div class="bg-white rounded-2xl shadow-lg border border-slate-200 mb-6">
        <div class="border-b border-slate-200">
            <nav class="flex flex-wrap -mb-px">
                <button type="button" onclick="showTab('branding')" 
                    class="tab-btn active px-6 py-4 text-sm font-semibold border-b-2 border-primary-500 text-primary-600">
                    <i class="fa-solid fa-palette mr-2"></i>Branding
                </button>
                <button type="button" onclick="showTab('institusi')" 
                    class="tab-btn px-6 py-4 text-sm font-semibold border-b-2 border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300">
                    <i class="fa-solid fa-building mr-2"></i>Institusi
                </button>
                <button type="button" onclick="showTab('kop-surat')" 
                    class="tab-btn px-6 py-4 text-sm font-semibold border-b-2 border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300">
                    <i class="fa-solid fa-file-signature mr-2"></i>Kop Surat & TTD
                </button>
            </nav>
        </div>
    </div>

    <!-- Tab Content -->
    <div class="space-y-6">
        
        <!-- Branding Tab -->
        <div id="tab-branding" class="tab-content">
            <div class="bg-white rounded-2xl shadow-lg border border-slate-200 p-8">
                <h2 class="text-2xl font-bold text-slate-800 mb-6">
                    <i class="fa-solid fa-palette mr-3 text-primary-500"></i>Branding & Identitas
                </h2>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Nama Website -->
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">
                            Nama Website
                        </label>
                        <input type="text" name="nama_website" value="<?= htmlspecialchars($settings['nama_website'] ?? '') ?>" 
                            class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-primary-500 focus:outline-none transition-all"
                            placeholder="Rental Gedung Profesional">
                    </div>
                    
                    <!-- Nama Panjang -->
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">
                            Nama Panjang
                        </label>
                        <input type="text" name="nama_panjang" value="<?= htmlspecialchars($settings['nama_panjang'] ?? '') ?>" 
                            class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-primary-500 focus:outline-none transition-all"
                            placeholder="Sistem Manajemen Rental Gedung">
                    </div>
                    
                    <!-- Theme Color -->
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">
                            Warna Tema
                        </label>
                        <div class="flex gap-3">
                            <input type="color" name="theme_color" value="<?= htmlspecialchars($settings['theme_color'] ?? '#3B82F6') ?>" 
                                class="h-12 w-20 rounded-xl border-2 border-slate-200 cursor-pointer">
                            <input type="text" value="<?= htmlspecialchars($settings['theme_color'] ?? '#3B82F6') ?>" 
                                class="flex-1 px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-primary-500 focus:outline-none transition-all"
                                readonly>
                        </div>
                    </div>
                    
                    <!-- Logo Upload -->
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">
                            Logo Website
                        </label>
                        <div class="flex items-center gap-4">
                            <?php if (!empty($settings['logo_url'])): ?>
                            <img src="/situs-rental-gedung/<?= htmlspecialchars($settings['logo_url']) ?>" 
                                class="h-12 w-12 rounded-xl object-cover border-2 border-slate-200">
                            <?php endif; ?>
                            <input type="file" name="logo_url" accept="image/*" 
                                class="flex-1 px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-primary-500 focus:outline-none transition-all">
                        </div>
                        <p class="text-xs text-slate-500 mt-1">Format: JPG, PNG, GIF (Max 2MB)</p>
                    </div>
                    
                    <!-- Favicon Upload -->
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">
                            Favicon
                        </label>
                        <div class="flex items-center gap-4">
                            <?php if (!empty($settings['favicon_url'])): ?>
                            <img src="/situs-rental-gedung/<?= htmlspecialchars($settings['favicon_url']) ?>" 
                                class="h-12 w-12 rounded-xl object-cover border-2 border-slate-200">
                            <?php endif; ?>
                            <input type="file" name="favicon_url" accept="image/*" 
                                class="flex-1 px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-primary-500 focus:outline-none transition-all">
                        </div>
                        <p class="text-xs text-slate-500 mt-1">Format: ICO, PNG (Rekomendasi 32x32px)</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Institusi Tab -->
        <div id="tab-institusi" class="tab-content hidden">
            <div class="bg-white rounded-2xl shadow-lg border border-slate-200 p-8">
                <h2 class="text-2xl font-bold text-slate-800 mb-6">
                    <i class="fa-solid fa-building mr-3 text-primary-500"></i>Informasi Institusi
                </h2>
                
                <div class="grid grid-cols-1 gap-6">
                    <!-- Nama Institusi -->
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">
                            Nama Institusi
                        </label>
                        <input type="text" name="instansi_nama" value="<?= htmlspecialchars($settings['instansi_nama'] ?? '') ?>" 
                            class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-primary-500 focus:outline-none transition-all"
                            placeholder="PT. Rental Gedung Maju">
                    </div>
                    
                    <!-- Alamat -->
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">
                            Alamat Lengkap
                        </label>
                        <textarea name="instansi_alamat" rows="3" 
                            class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-primary-500 focus:outline-none transition-all"
                            placeholder="Jl. Profesional No.123, Banjarmasin"><?= htmlspecialchars($settings['instansi_alamat'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Telepon -->
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">
                                Telepon
                            </label>
                            <input type="text" name="instansi_telepon" value="<?= htmlspecialchars($settings['instansi_telepon'] ?? '') ?>" 
                                class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-primary-500 focus:outline-none transition-all"
                                placeholder="0511-1234567">
                        </div>
                        
                        <!-- Email -->
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">
                                Email
                            </label>
                            <input type="email" name="instansi_email" value="<?= htmlspecialchars($settings['instansi_email'] ?? '') ?>" 
                                class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-primary-500 focus:outline-none transition-all"
                                placeholder="info@rentalgedung.co.id">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Kop Surat Tab -->
        <div id="tab-kop-surat" class="tab-content hidden">
            <div class="bg-white rounded-2xl shadow-lg border border-slate-200 p-8">
                <h2 class="text-2xl font-bold text-slate-800 mb-6">
                    <i class="fa-solid fa-file-signature mr-3 text-primary-500"></i>Kop Surat & Tanda Tangan
                </h2>
                
                <div class="grid grid-cols-1 gap-6">
                    <!-- Logo Kop Surat -->
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">
                            Logo Kop Surat
                        </label>
                        <div class="flex items-center gap-4">
                            <?php if (!empty($settings['kop_surat_logo'])): ?>
                            <img src="/situs-rental-gedung/<?= htmlspecialchars($settings['kop_surat_logo']) ?>" 
                                class="h-16 w-auto rounded-xl object-contain border-2 border-slate-200 p-2 bg-white">
                            <?php endif; ?>
                            <input type="file" name="kop_surat_logo" accept="image/*" 
                                class="flex-1 px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-primary-500 focus:outline-none transition-all">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nama Penandatangan -->
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">
                                Nama Penandatangan
                            </label>
                            <input type="text" name="ttd_nama" value="<?= htmlspecialchars($settings['ttd_nama'] ?? '') ?>" 
                                class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-primary-500 focus:outline-none transition-all"
                                placeholder="Budi Santoso">
                        </div>
                        
                        <!-- NIP -->
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">
                                NIP
                            </label>
                            <input type="text" name="ttd_nip" value="<?= htmlspecialchars($settings['ttd_nip'] ?? '') ?>" 
                                class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-primary-500 focus:outline-none transition-all"
                                placeholder="198001012015041001">
                        </div>
                        
                        <!-- Jabatan -->
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">
                                Jabatan
                            </label>
                            <input type="text" name="ttd_jabatan" value="<?= htmlspecialchars($settings['ttd_jabatan'] ?? '') ?>" 
                                class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-primary-500 focus:outline-none transition-all"
                                placeholder="Direktur Utama">
                        </div>
                        
                        <!-- Gambar TTD -->
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">
                                Gambar Tanda Tangan
                            </label>
                            <div class="flex items-center gap-4">
                                <?php if (!empty($settings['ttd_image'])): ?>
                                <img src="/situs-rental-gedung/<?= htmlspecialchars($settings['ttd_image']) ?>" 
                                    class="h-16 w-auto rounded-xl object-contain border-2 border-slate-200 p-2 bg-white">
                                <?php endif; ?>
                                <input type="file" name="ttd_image" accept="image/*" 
                                    class="flex-1 px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-primary-500 focus:outline-none transition-all">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
    
    <!-- Save Button -->
    <div class="mt-8 flex justify-end">
        <button type="submit" 
            class="px-8 py-4 rounded-xl bg-gradient-to-r from-primary-500 to-secondary-500 text-white font-bold text-lg shadow-lg shadow-primary-500/30 hover:shadow-xl hover:translate-y-[-2px] transition-all">
            <i class="fa-solid fa-save mr-2"></i>Simpan Pengaturan
        </button>
    </div>
</form>

<script>
function showTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.add('hidden');
    });
    
    // Remove active class from all buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active', 'border-primary-500', 'text-primary-600');
        btn.classList.add('border-transparent', 'text-slate-500');
    });
    
    // Show selected tab
    document.getElementById('tab-' + tabName).classList.remove('hidden');
    
    // Add active class to clicked button
    event.target.classList.add('active', 'border-primary-500', 'text-primary-600');
    event.target.classList.remove('border-transparent', 'text-slate-500');
}

<?php if ($success): ?>
    showSuccess('<?= addslashes($success) ?>');
<?php endif; ?>

<?php if ($error): ?>
    showError('<?= addslashes($error) ?>');
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/../../includes/admin/footer_admin.php'; ?>