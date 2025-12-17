<?php
require_once '../../../includes/admin/header_admin.php';

// Inisialisasi variabel $pdo menggunakan helper function dari database.php
$pdo = getDB();

// --- 1. Query Statistik ---

// A. Total Pendapatan (Status Selesai/Disetujui)
$queryPendapatan = "SELECT SUM(total_harga) as total FROM booking WHERE status IN ('selesai', 'disetujui') AND deleted_at IS NULL";
$stmt = $pdo->query($queryPendapatan);
$pendapatan = $stmt->fetch()['total'] ?? 0;

// B. Booking Aktif (Pending/Disetujui)
$queryBookingAktif = "SELECT COUNT(*) as total FROM booking WHERE status IN ('pending', 'disetujui') AND deleted_at IS NULL";
$stmt = $pdo->query($queryBookingAktif);
$bookingAktif = $stmt->fetch()['total'] ?? 0;

// C. Gedung Terpakai (Occupancy)
$queryGedungTotal = "SELECT COUNT(*) as total FROM gedung WHERE deleted_at IS NULL";
$queryGedungTerpakai = "SELECT COUNT(*) as total FROM gedung WHERE status != 'tersedia' AND deleted_at IS NULL";
$totalGedung = $pdo->query($queryGedungTotal)->fetch()['total'] ?? 0;
$gedungTerpakai = $pdo->query($queryGedungTerpakai)->fetch()['total'] ?? 0;

// D. Pelanggan Baru (Bulan Ini)
$queryPelangganBaru = "SELECT COUNT(*) as total FROM users WHERE role = 'penyewa' AND MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE()) AND deleted_at IS NULL";
$stmt = $pdo->query($queryPelangganBaru);
$pelangganBaru = $stmt->fetch()['total'] ?? 0;

// --- 2. Query Data Tabel Terbaru ---
$queryTerbaru = "SELECT b.*, u.nama_lengkap, g.nama as nama_gedung 
                 FROM booking b 
                 JOIN users u ON b.penyewa_id = u.id 
                 JOIN gedung g ON b.gedung_id = g.id 
                 WHERE b.deleted_at IS NULL
                 ORDER BY b.created_at DESC LIMIT 5";
$stmt = $pdo->query($queryTerbaru);
$bookings = $stmt->fetchAll();
?>

<div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end animate-entry">
    <div>
        <h2 class="text-3xl font-bold text-slate-800 dark:text-white tracking-tight">Dashboard Overview</h2>
        <p class="mt-1 text-slate-500 dark:text-slate-400">Ringkasan aktivitas penyewaan gedung hari ini.</p>
    </div>
    <div class="flex gap-2">
        <a href="/situs-rental-gedung/admin/data/booking/" class="flex items-center gap-2 rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/30 hover:brightness-110 hover:translate-y-[-2px] transition-all">
            <i class="fa-solid fa-plus"></i> Booking Baru
        </a>
    </div>
</div>

<div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-8 animate-entry" style="animation-delay: 0.1s">
    
    <div class="relative overflow-hidden rounded-[2rem] bg-white dark:bg-slate-800 p-6 shadow-xl shadow-slate-200/50 dark:shadow-none border border-slate-100 dark:border-slate-700 group hover:shadow-2xl hover:shadow-primary/20 transition-all duration-300">
        <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-primary/10 dark:bg-primary/20 transition-all group-hover:scale-150 group-hover:bg-primary/20 dark:group-hover:bg-primary/30"></div>
        <div class="relative z-10 flex items-center justify-between">
            <div>
                <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Total Pendapatan</p>
                <h3 class="mt-2 text-2xl font-bold text-slate-800 dark:text-white">Rp <?= number_format($pendapatan, 0, ',', '.') ?></h3>
                <p class="mt-1 text-xs font-medium text-emerald-500 flex items-center gap-1">
                    <i class="fa-solid fa-arrow-trend-up"></i> Realtime
                </p>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-primary to-secondary text-white shadow-lg shadow-primary/30">
                <i class="fa-solid fa-wallet text-xl"></i>
            </div>
        </div>
    </div>

    <div class="relative overflow-hidden rounded-[2rem] bg-white dark:bg-slate-800 p-6 shadow-xl shadow-slate-200/50 dark:shadow-none border border-slate-100 dark:border-slate-700 group hover:shadow-2xl hover:shadow-pink-100/50 dark:hover:shadow-none transition-all duration-300">
        <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-pink-50 dark:bg-pink-900/20 transition-all group-hover:scale-150 group-hover:bg-pink-100 dark:group-hover:bg-pink-900/30"></div>
        <div class="relative z-10 flex items-center justify-between">
            <div>
                <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Booking Aktif</p>
                <h3 class="mt-2 text-2xl font-bold text-slate-800 dark:text-white"><?= $bookingAktif ?> Order</h3>
                <p class="mt-1 text-xs font-medium text-slate-400 dark:text-slate-500 flex items-center gap-1">
                    Pending & Disetujui
                </p>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-pink-500 to-rose-500 text-white shadow-lg shadow-pink-500/30">
                <i class="fa-solid fa-calendar-check text-xl"></i>
            </div>
        </div>
    </div>

    <div class="relative overflow-hidden rounded-[2rem] bg-white dark:bg-slate-800 p-6 shadow-xl shadow-slate-200/50 dark:shadow-none border border-slate-100 dark:border-slate-700 group hover:shadow-2xl hover:shadow-orange-100/50 dark:hover:shadow-none transition-all duration-300">
        <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-orange-50 dark:bg-orange-900/20 transition-all group-hover:scale-150 group-hover:bg-orange-100 dark:group-hover:bg-orange-900/30"></div>
        <div class="relative z-10 flex items-center justify-between">
            <div>
                <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Gedung Terpakai</p>
                <h3 class="mt-2 text-2xl font-bold text-slate-800 dark:text-white"><?= $gedungTerpakai ?> / <?= $totalGedung ?></h3>
                <p class="mt-1 text-xs font-medium text-slate-400 dark:text-slate-500">
                    Occupancy Rate
                </p>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-orange-400 to-yellow-500 text-white shadow-lg shadow-orange-500/30">
                <i class="fa-solid fa-building text-xl"></i>
            </div>
        </div>
    </div>

    <div class="relative overflow-hidden rounded-[2rem] bg-white dark:bg-slate-800 p-6 shadow-xl shadow-slate-200/50 dark:shadow-none border border-slate-100 dark:border-slate-700 group hover:shadow-2xl hover:shadow-teal-100/50 dark:hover:shadow-none transition-all duration-300">
        <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-teal-50 dark:bg-teal-900/20 transition-all group-hover:scale-150 group-hover:bg-teal-100 dark:group-hover:bg-teal-900/30"></div>
        <div class="relative z-10 flex items-center justify-between">
            <div>
                <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Pelanggan Baru</p>
                <h3 class="mt-2 text-2xl font-bold text-slate-800 dark:text-white"><?= $pelangganBaru ?> User</h3>
                <p class="mt-1 text-xs font-medium text-emerald-500 flex items-center gap-1">
                    <i class="fa-solid fa-user-plus"></i> Bulan ini
                </p>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-teal-400 to-emerald-500 text-white shadow-lg shadow-teal-500/30">
                <i class="fa-solid fa-users text-xl"></i>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8 animate-entry" style="animation-delay: 0.2s">
    
    <div class="lg:col-span-2 flex flex-col gap-6">
        
        <div class="rounded-[2rem] bg-white dark:bg-slate-800 shadow-xl shadow-slate-200/40 dark:shadow-none border border-slate-100 dark:border-slate-700 overflow-hidden transition-colors duration-300">
            <div class="flex items-center justify-between px-8 py-6 border-b border-slate-100 dark:border-slate-700">
                <div>
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white">Penyewaan Terbaru</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Status booking yang baru masuk.</p>
                </div>
                <a href="/situs-rental-gedung/admin/data/booking/" class="text-sm font-bold text-primary hover:text-secondary hover:underline transition-colors">Lihat Semua</a>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                    <thead class="bg-slate-50 dark:bg-slate-700/50 text-xs uppercase text-slate-500 dark:text-slate-400 font-bold">
                        <tr>
                            <th class="px-8 py-4">Penyewa</th>
                            <th class="px-6 py-4">Gedung</th>
                            <th class="px-6 py-4">Tanggal</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        <?php if (empty($bookings)): ?>
                        <tr>
                            <td colspan="5" class="px-8 py-8 text-center text-slate-400 dark:text-slate-500">Belum ada data booking.</td>
                        </tr>
                        <?php else: ?>
                            <?php foreach($bookings as $row): ?>
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/30 transition-colors group">
                                <td class="px-8 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 rounded-full bg-primary/10 dark:bg-primary/20 flex items-center justify-center text-primary font-bold text-xs">
                                            <?= substr($row['nama_lengkap'], 0, 2) ?>
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-800 dark:text-white"><?= htmlspecialchars($row['nama_lengkap']) ?></div>
                                            <div class="text-xs text-slate-400 dark:text-slate-500"><?= htmlspecialchars($row['booking_code']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-medium text-slate-700 dark:text-slate-300"><?= htmlspecialchars($row['nama_gedung']) ?></td>
                                <td class="px-6 py-4 text-slate-600 dark:text-slate-400"><?= date('d M Y', strtotime($row['tanggal_mulai'])) ?></td>
                                <td class="px-6 py-4">
                                    <?php
                                    // Mapping kelas warna untuk Light & Dark mode
                                    $statusClass = [
                                        'pending' => 'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400',
                                        'disetujui' => 'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400',
                                        'selesai' => 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400',
                                        'ditolak' => 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400',
                                        'batal' => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
                                    ];
                                    $class = $statusClass[$row['status']] ?? 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300';
                                    ?>
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold capitalize <?= $class ?>">
                                        <?= $row['status'] ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="/situs-rental-gedung/admin/data/booking/detail.php?id=<?= $row['id'] ?>" class="text-slate-400 hover:text-primary dark:hover:text-primary transition-colors">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="flex flex-col gap-6">
        
        <div class="relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-primary to-secondary p-8 text-white shadow-xl shadow-primary/20 dark:shadow-none">
            <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/10 blur-2xl"></div>
            <div class="absolute -left-10 -bottom-10 h-40 w-40 rounded-full bg-black/10 blur-2xl"></div>
            
            <h3 class="text-2xl font-bold relative z-10">Laporan PDF?</h3>
            <p class="mt-2 text-white/80 text-sm relative z-10">Cetak laporan pendapatan bulanan dengan mudah.</p>
            <a href="/situs-rental-gedung/admin/laporan/" class="inline-block mt-6 w-full rounded-xl bg-white py-3 text-sm font-bold text-center text-primary hover:bg-slate-50 transition-colors shadow-lg relative z-10">
                Buka Laporan
            </a>
        </div>

        <div class="rounded-[2rem] bg-white dark:bg-slate-800 shadow-xl shadow-slate-200/40 dark:shadow-none border border-slate-100 dark:border-slate-700 p-6 transition-colors duration-300">
            <h3 class="font-bold text-slate-800 dark:text-white mb-4">Gedung Terpopuler</h3>
            <div class="space-y-4">
                <?php
                // Query simple untuk gedung popular
                $stmt = $pdo->query("SELECT * FROM gedung WHERE deleted_at IS NULL LIMIT 3");
                while($g = $stmt->fetch()):
                ?>
                <a href="/situs-rental-gedung/admin/data/gedung/edit.php?id=<?= $g['id'] ?>" class="flex items-center gap-4 p-3 rounded-2xl hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors cursor-pointer group">
                    <div class="h-12 w-16 rounded-xl bg-slate-200 dark:bg-slate-700 overflow-hidden relative border border-slate-100 dark:border-slate-600">
                         <img src="<?= !empty($g['foto_utama']) ? '/situs-rental-gedung/' . $g['foto_utama'] : 'https://placehold.co/100x100?text=Gedung' ?>" class="h-full w-full object-cover" alt="">
                    </div>
                    <div class="flex-1 overflow-hidden">
                        <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200 group-hover:text-primary transition-colors truncate"><?= htmlspecialchars($g['nama']) ?></h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Rp <?= number_format($g['harga_per_hari']/1000000, 1) ?> jt / hari</p>
                    </div>
                </a>
                <?php endwhile; ?>
            </div>
        </div>

    </div>
</div>

<?php
require_once '../../../includes/admin/footer_admin.php';
?>