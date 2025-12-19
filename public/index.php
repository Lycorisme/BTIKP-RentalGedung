<?php
require_once __DIR__ . '/../includes/public/header_public.php';

// Inisialisasi variabel default
$stats = [
    'total_gedung' => 0,
    'total_event' => 0,
    'avg_rating' => 0
];
$gedung_list = [];
$kategori_list = [];
$fasilitas_list = [];

try {
    $db = getDB();

    // 1. Ambil Statistik Dashboard
    // Total Gedung
    $stmt = $db->query("SELECT COUNT(*) FROM gedung WHERE deleted_at IS NULL AND status = 'tersedia'");
    $stats['total_gedung'] = $stmt->fetchColumn();

    // Total Event (Booking yang sudah selesai atau disetujui)
    $stmt = $db->query("SELECT COUNT(*) FROM booking WHERE status IN ('selesai', 'disetujui')");
    $stats['total_event'] = $stmt->fetchColumn();

    // Rata-rata Rating Global
    $stmt = $db->query("SELECT AVG(rating) FROM reviews WHERE tampilkan = 1");
    $stats['avg_rating'] = number_format((float)$stmt->fetchColumn(), 1);

    // 2. Ambil Kategori untuk Dropdown Pencarian
    $stmt = $db->query("SELECT * FROM kategori_gedung WHERE is_active = 1 AND deleted_at IS NULL ORDER BY nama ASC");
    $kategori_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Ambil Fasilitas Unggulan (Limit 3)
    $stmt = $db->query("SELECT * FROM fasilitas WHERE is_active = 1 AND deleted_at IS NULL ORDER BY urutan ASC LIMIT 3");
    $fasilitas_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. Ambil Data Gedung Populer (Limit 3) + Rating Rata-rata per Gedung
    $query_gedung = "
        SELECT 
            g.*, 
            k.nama as kategori_nama,
            (SELECT COUNT(*) FROM reviews r WHERE r.gedung_id = g.id AND r.tampilkan = 1) as review_count,
            (SELECT AVG(rating) FROM reviews r WHERE r.gedung_id = g.id AND r.tampilkan = 1) as rating_avg
        FROM gedung g 
        LEFT JOIN kategori_gedung k ON g.kategori_id = k.id 
        WHERE g.deleted_at IS NULL 
        ORDER BY g.created_at DESC 
        LIMIT 3
    ";
    $stmt = $db->query($query_gedung);
    $gedung_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    // Silent error agar tampilan tidak rusak total, idealnya dilog
    error_log("Error in public/index.php: " . $e->getMessage());
}
?>

<section id="home" class="relative pt-16 pb-20 lg:pt-24 lg:pb-32 overflow-hidden">
    <div class="absolute inset-0 z-0">
        <?php if (!empty($settings['public_hero_image'])): ?>
            <img src="<?= $base_url . '/' . htmlspecialchars($settings['public_hero_image']) ?>" 
                 alt="Background Gedung" 
                 class="w-full h-full object-cover">
        <?php else: ?>
            <img src="https://images.unsplash.com/photo-1497366216548-37526070297c?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80" 
                 alt="Background Gedung" 
                 class="w-full h-full object-cover">
        <?php endif; ?>
        <div class="absolute inset-0 bg-gradient-to-br from-slate-900/95 via-slate-800/90 to-slate-600/80 mix-blend-multiply"></div>
        <div class="absolute inset-0 hero-pattern opacity-30"></div>
    </div>
    
    <div class="container mx-auto px-4 md:px-6 relative z-10 text-center">
        <span class="inline-block py-1 px-3 rounded-full bg-blue-500/20 border border-blue-400/30 text-blue-100 text-sm font-semibold mb-6 backdrop-blur-sm animate-fade-in-up">
            ✨ Solusi Ruangan Terbaik di <?= !empty($settings['instansi_alamat']) ? 'Kota Kami' : 'Indonesia' ?>
        </span>
        
        <h1 class="text-4xl md:text-6xl font-extrabold text-white mb-6 leading-tight tracking-tight">
            Temukan Ruang Sempurna <br/> 
            Untuk <span class="text-blue-200">Setiap Momen Anda</span>
        </h1>
        
        <p class="text-lg md:text-xl text-blue-100 mb-10 max-w-2xl mx-auto font-light">
            Sewa gedung pertemuan, ruang rapat, dan aula dengan fasilitas lengkap dan proses pemesanan yang mudah, cepat, dan transparan.
        </p>
        
        <div class="flex flex-col sm:flex-row justify-center gap-4">
            <a href="#gedung" class="px-8 py-4 bg-white text-slate-800 font-bold rounded-full shadow-xl hover:bg-gray-50 transition transform hover:-translate-y-1 flex items-center justify-center gap-2">
                <i class="fa-solid fa-magnifying-glass"></i> Cari Gedung
            </a>
            <a href="#fasilitas" class="px-8 py-4 bg-transparent border-2 border-white/30 text-white font-bold rounded-full hover:bg-white/10 transition backdrop-blur-sm">
                Pelajari Prosedur
            </a>
        </div>
        
        <div class="mt-16 grid grid-cols-2 md:grid-cols-4 gap-8 border-t border-white/10 pt-10 text-white/90">
            <div>
                <div class="text-3xl font-bold text-white"><?= number_format($stats['total_gedung']) ?>+</div>
                <div class="text-sm text-blue-200">Gedung & Ruangan</div>
            </div>
            <div>
                <div class="text-3xl font-bold text-white"><?= number_format($stats['total_event']) ?>+</div>
                <div class="text-sm text-blue-200">Event Sukses</div>
            </div>
            <div>
                <div class="text-3xl font-bold text-white">24/7</div>
                <div class="text-sm text-blue-200">Layanan Support</div>
            </div>
            <div>
                <div class="text-3xl font-bold text-white"><?= $stats['avg_rating'] > 0 ? $stats['avg_rating'] : '5.0' ?></div>
                <div class="text-sm text-blue-200">Rating Kepuasan</div>
            </div>
        </div>
    </div>
</section>

<div class="container mx-auto px-4 md:px-6 -mt-10 relative z-20">
    <form action="<?= $base_url ?>/public/gedung/" method="GET" class="bg-white p-6 rounded-2xl shadow-xl shadow-gray-200/50 border border-gray-100 flex flex-col md:flex-row gap-4 items-end">
        <div class="w-full md:w-1/3">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Kategori Kegiatan</label>
            <select name="kategori" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-500 transition">
                <option value="">Semua Kategori</option>
                <?php foreach ($kategori_list as $kat): ?>
                    <option value="<?= htmlspecialchars($kat['id']) ?>"><?= htmlspecialchars($kat['nama']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="w-full md:w-1/3">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Rencana</label>
            <input type="date" name="tanggal" 
                   class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-500 transition text-gray-500"
                   min="<?= date('Y-m-d') ?>">
        </div>
        <div class="w-full md:w-1/3">
            <button type="submit" class="w-full bg-brand-600 hover:bg-brand-700 text-white font-bold py-3 rounded-lg transition shadow-lg shadow-brand-500/30">
                Cek Ketersediaan
            </button>
        </div>
    </form>
</div>

<section id="gedung" class="py-20 bg-slate-50">
    <div class="container mx-auto px-4 md:px-6">
        <div class="text-center mb-16">
            <span class="text-brand-600 font-bold tracking-wider uppercase text-sm">Pilihan Terbaik</span>
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mt-2">Gedung Terbaru</h2>
            <div class="w-20 h-1 bg-brand-500 mx-auto mt-4 rounded-full"></div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php if (!empty($gedung_list)): ?>
                <?php foreach ($gedung_list as $gedung): ?>
                <div class="bg-white rounded-2xl overflow-hidden shadow-md hover:shadow-xl transition duration-300 group border border-gray-100 flex flex-col h-full">
                    <div class="relative h-64 overflow-hidden shrink-0">
                        <?php if (!empty($gedung['foto_utama'])): ?>
                            <img src="<?= $base_url . '/' . htmlspecialchars($gedung['foto_utama']) ?>" 
                                 alt="<?= htmlspecialchars($gedung['nama']) ?>" 
                                 class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                        <?php else: ?>
                            <img src="https://images.unsplash.com/photo-1519167758481-83f550bb49b3?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" 
                                 alt="<?= htmlspecialchars($gedung['nama']) ?>" 
                                 class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                        <?php endif; ?>
                        
                        <div class="absolute top-4 left-4 bg-white/90 backdrop-blur px-3 py-1 rounded-lg text-xs font-bold text-brand-700 shadow-sm">
                            <i class="fa-solid fa-star text-yellow-400 mr-1"></i> 
                            <?= $gedung['rating_avg'] ? number_format($gedung['rating_avg'], 1) : '5.0' ?> 
                            (<?= $gedung['review_count'] ?> Review)
                        </div>
                        
                        <?php
                        $badge_colors = [
                            'tersedia' => 'bg-green-600',
                            'maintenance' => 'bg-yellow-600',
                            'full_booked' => 'bg-red-600'
                        ];
                        $badge_labels = [
                            'tersedia' => 'Tersedia',
                            'maintenance' => 'Maintenance',
                            'full_booked' => 'Full Booked'
                        ];
                        $badge_color = $badge_colors[$gedung['status']] ?? 'bg-gray-600';
                        $badge_label = $badge_labels[$gedung['status']] ?? 'Unknown';
                        ?>
                        <div class="absolute bottom-4 right-4 <?= $badge_color ?> text-white px-4 py-1 rounded-full text-sm font-bold shadow-lg">
                            <?= $badge_label ?>
                        </div>
                    </div>
                    
                    <div class="p-6 flex flex-col flex-grow">
                        <div class="mb-auto">
                            <h3 class="text-xl font-bold text-gray-900 mb-2 group-hover:text-brand-600 transition">
                                <?= htmlspecialchars($gedung['nama']) ?>
                            </h3>
                            <p class="text-gray-500 text-sm mb-4 line-clamp-2">
                                <?= htmlspecialchars(strip_tags($gedung['deskripsi'] ?? 'Tidak ada deskripsi')) ?>
                            </p>
                            
                            <div class="flex items-center gap-4 text-sm text-gray-500 mb-6">
                                <div class="flex items-center gap-1">
                                    <i class="fa-solid fa-user-group text-brand-500"></i> 
                                    <?= number_format($gedung['kapasitas_orang']) ?> Org
                                </div>
                                <div class="flex items-center gap-1">
                                    <i class="fa-solid fa-ruler-combined text-brand-500"></i> 
                                    <?= number_format($gedung['luas_m2']) ?>m²
                                </div>
                                <?php if(isset($gedung['kategori_nama'])): ?>
                                <div class="flex items-center gap-1">
                                    <i class="fa-solid fa-tag text-brand-500"></i> <?= htmlspecialchars($gedung['kategori_nama']) ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between pt-4 border-t border-gray-100 mt-4">
                            <div>
                                <p class="text-xs text-gray-400">Mulai dari</p>
                                <p class="text-lg font-bold text-brand-700">
                                    Rp <?= number_format($gedung['harga_per_hari'], 0, ',', '.') ?>
                                    <span class="text-xs text-gray-400 font-normal">/hari</span>
                                </p>
                            </div>
                            <a href="<?= $base_url ?>/public/gedung-detail/?id=<?= $gedung['id'] ?>" 
                               class="text-brand-600 hover:text-brand-800 font-semibold text-sm flex items-center gap-1">
                                Detail <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-span-3 text-center py-10">
                    <p class="text-gray-500 text-lg">Belum ada data gedung yang tersedia saat ini.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="text-center mt-12">
            <a href="<?= $base_url ?>/public/gedung/" 
               class="inline-block px-8 py-3 rounded-full border-2 border-brand-600 text-brand-600 font-bold hover:bg-brand-600 hover:text-white transition duration-300">
                Lihat Semua Gedung
            </a>
        </div>
    </div>
</section>

<section id="fasilitas" class="py-20 bg-white">
    <div class="container mx-auto px-4 md:px-6">
        <div class="flex flex-col lg:flex-row items-center gap-12">
            <div class="lg:w-1/2">
                <img src="https://images.unsplash.com/photo-1556761175-5973dc0f32e7?ixlib=rb-1.2.1&auto=format&fit=crop&w=1600&q=80" 
                     alt="Fasilitas Meeting" 
                     class="rounded-3xl shadow-2xl border-8 border-slate-50">
            </div>
            
            <div class="lg:w-1/2">
                <span class="text-brand-600 font-bold tracking-wider uppercase text-sm mb-2 block">Kenapa Kami?</span>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6 leading-snug">
                    Fasilitas Lengkap untuk <br> Kelancaran Acara Anda
                </h2>
                <p class="text-gray-500 mb-8 leading-relaxed">
                    Kami tidak hanya menyewakan ruangan, tetapi memberikan pengalaman terbaik dengan dukungan teknis dan fasilitas penunjang yang siap pakai.
                </p>
                
                <div class="space-y-6">
                    <?php if(!empty($fasilitas_list)): ?>
                        <?php foreach($fasilitas_list as $fas): ?>
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center text-brand-600 shrink-0">
                                <i class="<?= !empty($fas['icon']) ? htmlspecialchars($fas['icon']) : 'fa-solid fa-check' ?> text-xl"></i>
                            </div>
                            <div>
                                <h4 class="text-lg font-bold text-gray-800"><?= htmlspecialchars($fas['nama']) ?></h4>
                                <p class="text-sm text-gray-500 mt-1">
                                    Fasilitas <?= $fas['grup'] ?> dengan kualitas terbaik. 
                                    <?= $fas['harga_tambahan'] > 0 ? '(Tersedia tambahan)' : '(Termasuk)' ?>
                                </p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center text-brand-600 shrink-0">
                                <i class="fa-solid fa-wallet text-xl"></i>
                            </div>
                            <div>
                                <h4 class="text-lg font-bold text-gray-800">Harga Transparan</h4>
                                <p class="text-sm text-gray-500 mt-1">Tidak ada biaya tersembunyi. Apa yang Anda lihat adalah yang Anda bayar.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center text-brand-600 shrink-0">
                                <i class="fa-solid fa-calendar-check text-xl"></i>
                            </div>
                            <div>
                                <h4 class="text-lg font-bold text-gray-800">Booking Online Mudah</h4>
                                <p class="text-sm text-gray-500 mt-1">Cek ketersediaan dan pesan langsung melalui website tanpa ribet.</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-20 relative overflow-hidden bg-brand-700">
    <div class="absolute inset-0 bg-gradient-to-r from-slate-900 to-slate-600"></div>
    <div class="absolute top-0 right-0 -mt-20 -mr-20 w-96 h-96 rounded-full bg-white opacity-5"></div>
    <div class="absolute bottom-0 left-0 -mb-20 -ml-20 w-80 h-80 rounded-full bg-white opacity-5"></div>
    
    <div class="container mx-auto px-4 md:px-6 relative z-10 text-center">
        <h2 class="text-3xl md:text-4xl font-bold text-white mb-6">Siap Mengadakan Acara?</h2>
        <p class="text-blue-100 text-lg mb-10 max-w-2xl mx-auto">
            Jangan ragu untuk berkonsultasi dengan tim kami atau langsung lakukan pemesanan gedung sekarang juga.
        </p>
        
        <div class="flex flex-col sm:flex-row justify-center gap-4">
            <a href="<?= $base_url ?>/user/register/" 
               class="px-8 py-4 bg-white text-brand-700 font-bold rounded-full shadow-lg hover:bg-gray-100 transition transform hover:-translate-y-1">
                Buat Akun & Pesan
            </a>
            <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $settings['company_phone'] ?? '628123456789') ?>" 
               target="_blank"
               class="px-8 py-4 bg-brand-800 text-white font-bold rounded-full border border-brand-600 hover:bg-brand-900 transition flex items-center justify-center gap-2">
                <i class="fa-brands fa-whatsapp"></i> Hubungi Admin
            </a>
        </div>
    </div>
</section>

<style>
/* Hero Pattern */
.hero-pattern {
    background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}

/* Line Clamp */
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Fade In Animation */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in-up {
    animation: fadeInUp 0.6s ease-out;
}
</style>

<?php require_once __DIR__ . '/../includes/public/footer_public.php'; ?>