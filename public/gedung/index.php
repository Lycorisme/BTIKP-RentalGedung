<?php
require_once __DIR__ . '/../../includes/public/header_public.php';
require_once __DIR__ . '/../../modules/crud.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'id DESC';

// Build where clause
$where = [];
if (!empty($search)) {
    $gedung_list = search('gedung', 'nama', $search, 100);
} else {
    if (!empty($status)) {
        $where['status'] = $status;
    }
    $gedung_list = read('gedung', $where, 100, 0, $sort);
}

$total_gedung = count($gedung_list);
?>

<div class="min-h-screen py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-12 text-center">
            <h1 class="text-5xl font-bold text-slate-800 mb-4">
                Daftar <span class="bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">Gedung</span>
            </h1>
            <p class="text-slate-600 text-lg">Temukan gedung yang sempurna untuk acara Anda</p>
        </div>
        
        <!-- Search & Filter -->
        <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/50 p-6 mb-12">
            <form method="GET" class="flex flex-col md:flex-row gap-4">
                <!-- Search -->
                <div class="flex-1">
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 transform -translate-y-1/2 text-slate-400"></i>
                        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
                            class="w-full pl-12 pr-4 py-3 rounded-xl border-2 border-slate-200 focus:border-blue-500 focus:outline-none transition-all"
                            placeholder="Cari nama gedung...">
                    </div>
                </div>
                
                <!-- Status Filter -->
                <select name="status" 
                    class="px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-blue-500 focus:outline-none transition-all">
                    <option value="">Semua Status</option>
                    <option value="tersedia" <?= $status === 'tersedia' ? 'selected' : '' ?>>Tersedia</option>
                    <option value="maintenance" <?= $status === 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
                    <option value="full_booked" <?= $status === 'full_booked' ? 'selected' : '' ?>>Full Booked</option>
                </select>
                
                <!-- Sort -->
                <select name="sort" 
                    class="px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-blue-500 focus:outline-none transition-all">
                    <option value="id DESC" <?= $sort === 'id DESC' ? 'selected' : '' ?>>Terbaru</option>
                    <option value="harga_per_hari ASC" <?= $sort === 'harga_per_hari ASC' ? 'selected' : '' ?>>Harga Terendah</option>
                    <option value="harga_per_hari DESC" <?= $sort === 'harga_per_hari DESC' ? 'selected' : '' ?>>Harga Tertinggi</option>
                    <option value="nama ASC" <?= $sort === 'nama ASC' ? 'selected' : '' ?>>Nama A-Z</option>
                </select>
                
                <!-- Submit -->
                <button type="submit" 
                    class="px-8 py-3 rounded-xl bg-gradient-to-r from-blue-500 to-purple-600 text-white font-semibold shadow-lg shadow-blue-500/30 hover:shadow-xl hover:translate-y-[-2px] transition-all">
                    <i class="fa-solid fa-filter mr-2"></i>Filter
                </button>
            </form>
        </div>
        
        <!-- Results Info -->
        <div class="mb-6 flex items-center justify-between">
            <p class="text-slate-600">
                Menampilkan <span class="font-bold text-slate-800"><?= $total_gedung ?></span> gedung
            </p>
        </div>
        
        <!-- Gedung Grid -->
        <?php if (empty($gedung_list)): ?>
            <div class="text-center py-20">
                <i class="fa-solid fa-building text-8xl text-slate-300 mb-6"></i>
                <h3 class="text-2xl font-bold text-slate-800 mb-2">Tidak Ada Gedung Ditemukan</h3>
                <p class="text-slate-600 mb-6">Coba ubah filter pencarian Anda</p>
                <a href="/situs-rental-gedung/public/gedung/" 
                    class="inline-flex items-center px-6 py-3 rounded-xl bg-blue-500 text-white font-semibold hover:bg-blue-600 transition-colors">
                    <i class="fa-solid fa-rotate-right mr-2"></i>Reset Filter
                </a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($gedung_list as $gedung): ?>
                <div class="group bg-white rounded-3xl shadow-xl shadow-slate-200/50 overflow-hidden hover:shadow-2xl hover:shadow-blue-500/20 transition-all duration-300 hover:translate-y-[-8px]">
                    <!-- Image -->
                    <div class="relative h-64 overflow-hidden">
                        <img src="/situs-rental-gedung/<?= htmlspecialchars($gedung['foto_utama'] ?? 'uploads/gedung/default.jpg') ?>" 
                            alt="<?= htmlspecialchars($gedung['nama']) ?>" 
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
                            onerror="this.src='https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=800&q=80'">
                        
                        <!-- Status Badge -->
                        <?php
                        $badge_colors = [
                            'tersedia' => 'bg-emerald-500',
                            'maintenance' => 'bg-orange-500',
                            'full_booked' => 'bg-red-500'
                        ];
                        $badge_text = [
                            'tersedia' => 'Tersedia',
                            'maintenance' => 'Maintenance',
                            'full_booked' => 'Full Booked'
                        ];
                        $badge_color = $badge_colors[$gedung['status']] ?? 'bg-slate-500';
                        ?>
                        <div class="absolute top-4 right-4 px-4 py-2 rounded-full <?= $badge_color ?> text-white font-bold shadow-lg">
                            <?= $badge_text[$gedung['status']] ?? $gedung['status'] ?>
                        </div>
                    </div>
                    
                    <!-- Content -->
                    <div class="p-6">
                        <h3 class="text-2xl font-bold text-slate-800 mb-2 group-hover:text-blue-600 transition-colors">
                            <?= htmlspecialchars($gedung['nama']) ?>
                        </h3>
                        
                        <?php if (!empty($gedung['deskripsi'])): ?>
                        <p class="text-slate-600 text-sm mb-4 line-clamp-2">
                            <?= htmlspecialchars(substr($gedung['deskripsi'], 0, 100)) ?>...
                        </p>
                        <?php endif; ?>
                        
                        <!-- Features -->
                        <div class="flex items-center gap-4 mb-4 text-sm text-slate-500">
                            <?php if ($gedung['kapasitas_orang']): ?>
                            <span><i class="fa-solid fa-users text-blue-500 mr-1"></i><?= $gedung['kapasitas_orang'] ?> Orang</span>
                            <?php endif; ?>
                            <?php if ($gedung['luas_m2']): ?>
                            <span><i class="fa-solid fa-ruler-combined text-blue-500 mr-1"></i><?= $gedung['luas_m2'] ?> m²</span>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Price & CTA -->
                        <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                            <div>
                                <p class="text-xs text-slate-500">Mulai dari</p>
                                <p class="text-2xl font-bold text-blue-600">
                                    Rp <?= number_format($gedung['harga_per_hari'], 0, ',', '.') ?>
                                </p>
                                <p class="text-xs text-slate-500">per hari</p>
                            </div>
                            <a href="/situs-rental-gedung/public/gedung-detail/?id=<?= $gedung['id'] ?>" 
                                class="px-6 py-3 rounded-xl bg-gradient-to-r from-blue-500 to-purple-600 text-white font-semibold shadow-lg shadow-blue-500/30 hover:shadow-xl hover:translate-y-[-2px] transition-all">
                                Detail
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/public/footer_public.php'; ?>