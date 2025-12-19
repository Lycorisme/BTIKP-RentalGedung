<?php
// Ambil ID gedung dari URL
$gedung_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($gedung_id <= 0) {
    header("Location: /situs-rental-gedung/public/gedung/");
    exit;
}

// Include header
require_once __DIR__ . '/../../includes/public/header_public.php';

// Ambil data gedung dari database
try {
    $db = getDB();
    
    // Query gedung dengan kategori
    $stmt = $db->prepare("
        SELECT g.*, k.nama as kategori_nama 
        FROM gedung g 
        LEFT JOIN kategori_gedung k ON g.kategori_id = k.id 
        WHERE g.id = ? AND g.deleted_at IS NULL
    ");
    $stmt->execute([$gedung_id]);
    $gedung = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$gedung) {
        header("Location: /situs-rental-gedung/public/gedung/");
        exit;
    }
    
    // Ambil foto-foto gedung
    $stmt_foto = $db->prepare("SELECT * FROM gedung_foto WHERE gedung_id = ? ORDER BY urutan ASC, created_at ASC");
    $stmt_foto->execute([$gedung_id]);
    $fotos = $stmt_foto->fetchAll(PDO::FETCH_ASSOC);
    
    // Ambil fasilitas gedung
    $stmt_fasilitas = $db->prepare("
        SELECT f.* FROM fasilitas f
        INNER JOIN gedung_fasilitas gf ON f.id = gf.fasilitas_id
        WHERE gf.gedung_id = ?
    ");
    $stmt_fasilitas->execute([$gedung_id]);
    $fasilitas = $stmt_fasilitas->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

// Status badge styling
$status_badges = [
    'tersedia' => ['bg' => 'bg-green-100', 'text' => 'text-green-700', 'icon' => 'fa-circle-check', 'label' => 'Tersedia'],
    'maintenance' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-700', 'icon' => 'fa-screwdriver-wrench', 'label' => 'Maintenance'],
    'full_booked' => ['bg' => 'bg-red-100', 'text' => 'text-red-700', 'icon' => 'fa-calendar-xmark', 'label' => 'Full Booked']
];

$current_status = $status_badges[$gedung['status']] ?? $status_badges['tersedia'];
?>

<!-- Breadcrumb -->
<div class="bg-white border-b border-slate-200">
    <div class="container mx-auto px-4 md:px-6 py-4">
        <div class="flex items-center gap-2 text-sm text-slate-600">
            <a href="<?= $base_url ?>/" class="hover:text-brand-600 transition-colors">Beranda</a>
            <i class="fa-solid fa-chevron-right text-xs text-slate-400"></i>
            <a href="<?= $base_url ?>/public/gedung/" class="hover:text-brand-600 transition-colors">Daftar Gedung</a>
            <i class="fa-solid fa-chevron-right text-xs text-slate-400"></i>
            <span class="text-brand-600 font-semibold"><?= htmlspecialchars($gedung['nama']) ?></span>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="container mx-auto px-4 md:px-6 py-8 lg:py-12">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left Column: Detail Gedung -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Header Info -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4 mb-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <h1 class="text-2xl lg:text-3xl font-bold text-slate-900"><?= htmlspecialchars($gedung['nama']) ?></h1>
                            <span class="<?= $current_status['bg'] ?> <?= $current_status['text'] ?> px-3 py-1 rounded-full text-xs font-bold flex items-center gap-1.5">
                                <i class="fa-solid <?= $current_status['icon'] ?>"></i>
                                <?= $current_status['label'] ?>
                            </span>
                        </div>
                        <?php if ($gedung['kategori_nama']): ?>
                        <p class="text-sm text-slate-500">
                            <i class="fa-solid fa-tag mr-1"></i>
                            Kategori: <span class="font-semibold text-brand-600"><?= htmlspecialchars($gedung['kategori_nama']) ?></span>
                        </p>
                        <?php endif; ?>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-slate-500 mb-1">Harga Sewa</p>
                        <p class="text-3xl font-bold text-brand-600">
                            Rp <?= number_format($gedung['harga_per_hari'], 0, ',', '.') ?>
                        </p>
                        <p class="text-xs text-slate-400">per hari</p>
                    </div>
                </div>
                
                <!-- Quick Info -->
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4 pt-4 border-t border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="h-12 w-12 rounded-xl bg-brand-50 flex items-center justify-center text-brand-600">
                            <i class="fa-solid fa-user-group text-xl"></i>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500">Kapasitas</p>
                            <p class="text-lg font-bold text-slate-800"><?= number_format($gedung['kapasitas_orang']) ?> Orang</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="h-12 w-12 rounded-xl bg-brand-50 flex items-center justify-center text-brand-600">
                            <i class="fa-solid fa-ruler-combined text-xl"></i>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500">Luas Area</p>
                            <p class="text-lg font-bold text-slate-800"><?= number_format($gedung['luas_m2']) ?> m²</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 col-span-2 md:col-span-1">
                        <div class="h-12 w-12 rounded-xl bg-brand-50 flex items-center justify-center text-brand-600">
                            <i class="fa-solid fa-location-dot text-xl"></i>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500">Lokasi</p>
                            <p class="text-sm font-bold text-slate-800 line-clamp-2"><?= htmlspecialchars($gedung['alamat_lengkap']) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gallery -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <?php if (!empty($fotos) || !empty($gedung['foto_utama'])): ?>
                <div class="relative">
                    <!-- Main Image -->
                    <div class="aspect-video w-full overflow-hidden bg-slate-100" id="mainImage">
                        <?php 
                        $main_image = !empty($fotos) ? $fotos[0]['filename'] : $gedung['foto_utama'];
                        ?>
                        <img src="<?= $base_url . '/' . htmlspecialchars($main_image) ?>" 
                             alt="<?= htmlspecialchars($gedung['nama']) ?>" 
                             class="w-full h-full object-cover">
                    </div>
                    
                    <!-- Thumbnail Gallery -->
                    <?php if (count($fotos) > 1): ?>
                    <div class="p-4 bg-slate-50">
                        <div class="grid grid-cols-4 md:grid-cols-6 gap-2">
                            <?php foreach ($fotos as $index => $foto): ?>
                            <button onclick="changeMainImage('<?= $base_url . '/' . htmlspecialchars($foto['filename']) ?>')" 
                                    class="aspect-video rounded-lg overflow-hidden border-2 border-transparent hover:border-brand-500 transition-all <?= $index === 0 ? 'ring-2 ring-brand-500' : '' ?>">
                                <img src="<?= $base_url . '/' . htmlspecialchars($foto['filename']) ?>" 
                                     alt="Foto <?= $index + 1 ?>" 
                                     class="w-full h-full object-cover">
                            </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <div class="aspect-video w-full bg-slate-100 flex items-center justify-center">
                    <div class="text-center text-slate-400">
                        <i class="fa-solid fa-image text-6xl mb-4"></i>
                        <p>Tidak ada foto tersedia</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Deskripsi -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <h2 class="text-xl font-bold text-slate-900 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-align-left text-brand-600"></i>
                    Deskripsi Gedung
                </h2>
                <div class="prose prose-slate max-w-none text-slate-600 leading-relaxed">
                    <?= nl2br(htmlspecialchars($gedung['deskripsi'])) ?>
                </div>
            </div>

            <!-- Fasilitas -->
            <?php if (!empty($fasilitas)): ?>
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <h2 class="text-xl font-bold text-slate-900 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-list-check text-brand-600"></i>
                    Fasilitas Tersedia
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <?php foreach ($fasilitas as $f): ?>
                    <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <div class="h-10 w-10 rounded-lg bg-brand-100 text-brand-600 flex items-center justify-center">
                            <i class="fa-solid <?= htmlspecialchars($f['icon'] ?? 'fa-check') ?>"></i>
                        </div>
                        <span class="font-semibold text-slate-700"><?= htmlspecialchars($f['nama']) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

        </div>

        <!-- Right Column: Booking Card -->
        <div class="lg:col-span-1">
            <div class="sticky top-24">
                <div class="bg-white rounded-2xl shadow-xl border border-slate-200 p-6">
                    <h3 class="text-lg font-bold text-slate-900 mb-4">Reservasi Gedung</h3>
                    
                    <div class="space-y-4 mb-6">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Tanggal Mulai</label>
                            <input type="date" 
                                   class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none transition-all"
                                   min="<?= date('Y-m-d') ?>">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Tanggal Selesai</label>
                            <input type="date" 
                                   class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none transition-all"
                                   min="<?= date('Y-m-d') ?>">
                        </div>
                    </div>

                    <div class="bg-slate-50 rounded-xl p-4 mb-6">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm text-slate-600">Harga per hari</span>
                            <span class="font-bold text-slate-900">Rp <?= number_format($gedung['harga_per_hari'], 0, ',', '.') ?></span>
                        </div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm text-slate-600">Durasi</span>
                            <span class="font-bold text-slate-900">1 hari</span>
                        </div>
                        <div class="h-px bg-slate-200 my-3"></div>
                        <div class="flex justify-between items-center">
                            <span class="text-base font-bold text-slate-900">Total Estimasi</span>
                            <span class="text-xl font-bold text-brand-600">Rp <?= number_format($gedung['harga_per_hari'], 0, ',', '.') ?></span>
                        </div>
                    </div>

                    <?php if ($gedung['status'] === 'tersedia'): ?>
                        <?php if (isset($_SESSION['user'])): ?>
                        <a href="<?= $base_url ?>/user/booking/?gedung_id=<?= $gedung_id ?>" 
                           class="block w-full py-4 bg-brand-600 hover:bg-brand-700 text-white text-center font-bold rounded-xl shadow-lg shadow-brand-500/30 transition-all hover:-translate-y-0.5">
                            <i class="fa-solid fa-calendar-check mr-2"></i>
                            Booking Sekarang
                        </a>
                        <?php else: ?>
                        <a href="<?= $base_url ?>/user/login/?redirect=<?= urlencode('/public/gedung-detail/?id=' . $gedung_id) ?>" 
                           class="block w-full py-4 bg-brand-600 hover:bg-brand-700 text-white text-center font-bold rounded-xl shadow-lg shadow-brand-500/30 transition-all hover:-translate-y-0.5">
                            <i class="fa-solid fa-right-to-bracket mr-2"></i>
                            Login untuk Booking
                        </a>
                        <?php endif; ?>
                    <?php else: ?>
                    <button disabled 
                            class="block w-full py-4 bg-slate-300 text-slate-500 text-center font-bold rounded-xl cursor-not-allowed">
                        <i class="fa-solid fa-ban mr-2"></i>
                        Tidak Tersedia
                    </button>
                    <?php endif; ?>

                    <div class="mt-4 pt-4 border-t border-slate-100">
                        <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $settings['company_phone'] ?? '') ?>?text=Halo, saya tertarik dengan gedung <?= urlencode($gedung['nama']) ?>" 
                           target="_blank"
                           class="flex items-center justify-center gap-2 w-full py-3 border-2 border-green-500 text-green-600 hover:bg-green-50 font-bold rounded-xl transition-all">
                            <i class="fa-brands fa-whatsapp text-xl"></i>
                            Hubungi via WhatsApp
                        </a>
                    </div>
                </div>

                <!-- Info Tambahan -->
                <div class="mt-6 bg-blue-50 border border-blue-200 rounded-xl p-4">
                    <div class="flex items-start gap-3">
                        <i class="fa-solid fa-circle-info text-blue-600 text-xl mt-0.5"></i>
                        <div class="text-sm text-blue-800">
                            <p class="font-bold mb-1">Informasi Penting</p>
                            <ul class="space-y-1 text-blue-700">
                                <li>• Booking minimal H-3 dari tanggal acara</li>
                                <li>• Pembayaran DP 50% untuk konfirmasi</li>
                                <li>• Gratis pembatalan H-7 sebelum acara</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function changeMainImage(imageUrl) {
    const mainImage = document.getElementById('mainImage');
    mainImage.innerHTML = `<img src="${imageUrl}" alt="Gedung" class="w-full h-full object-cover">`;
    
    // Update active thumbnail
    const thumbnails = document.querySelectorAll('[onclick^="changeMainImage"]');
    thumbnails.forEach(thumb => {
        thumb.classList.remove('ring-2', 'ring-brand-500');
        thumb.classList.add('border-transparent');
    });
    event.target.closest('button').classList.add('ring-2', 'ring-brand-500');
}

// Auto calculate duration and total price
const startDate = document.querySelector('input[type="date"]:first-of-type');
const endDate = document.querySelector('input[type="date"]:last-of-type');

function calculateTotal() {
    if (startDate.value && endDate.value) {
        const start = new Date(startDate.value);
        const end = new Date(endDate.value);
        const diffTime = Math.abs(end - start);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
        
        if (diffDays > 0) {
            const pricePerDay = <?= $gedung['harga_per_hari'] ?>;
            const total = pricePerDay * diffDays;
            
            document.querySelector('.bg-slate-50 .font-bold.text-slate-900:nth-of-type(2)').textContent = diffDays + ' hari';
            document.querySelector('.text-xl.font-bold.text-brand-600').textContent = 'Rp ' + total.toLocaleString('id-ID');
        }
    }
}

startDate?.addEventListener('change', calculateTotal);
endDate?.addEventListener('change', calculateTotal);
</script>

<?php require_once __DIR__ . '/../../includes/public/footer_public.php'; ?>
