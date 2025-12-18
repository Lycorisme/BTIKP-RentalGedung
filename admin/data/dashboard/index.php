<?php
// --- 1. Setup & Konfigurasi ---
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../includes/admin/auth.php';

// Inisialisasi Koneksi PDO
$conn = getDB();

// Cek Login & Role
requireLogin();
requireRole(['admin', 'superadmin']);

// --- 2. Query Statistik (Logic Dashboard) ---

// A. Total Pendapatan (Hanya yang sudah bayar/selesai)
// Menggunakan total_bayar agar akurat sesuai uang masuk
$sqlPendapatan = "SELECT SUM(total_bayar) as total 
                  FROM booking 
                  WHERE status IN ('paid', 'selesai') 
                  AND deleted_at IS NULL";
$pendapatan = $conn->query($sqlPendapatan)->fetchColumn() ?? 0;

// B. Booking Aktif (Perlu perhatian admin)
$sqlBookingAktif = "SELECT COUNT(*) FROM booking 
                    WHERE status IN ('pending', 'disetujui', 'paid') 
                    AND deleted_at IS NULL";
$bookingAktif = $conn->query($sqlBookingAktif)->fetchColumn() ?? 0;

// C. Rasio Okupansi Gedung
$totalGedung = $conn->query("SELECT COUNT(*) FROM gedung WHERE deleted_at IS NULL")->fetchColumn();
$gedungTerpakai = $conn->query("SELECT COUNT(*) FROM gedung WHERE status != 'tersedia' AND deleted_at IS NULL")->fetchColumn();

// D. Pelanggan Baru (Bulan Ini)
$sqlPelanggan = "SELECT COUNT(*) FROM users 
                 WHERE role = 'penyewa' 
                 AND MONTH(created_at) = MONTH(CURRENT_DATE()) 
                 AND YEAR(created_at) = YEAR(CURRENT_DATE()) 
                 AND deleted_at IS NULL";
$pelangganBaru = $conn->query($sqlPelanggan)->fetchColumn() ?? 0;

// E. Booking Terbaru (Limit 5)
$sqlTerbaru = "SELECT b.*, u.nama_lengkap, g.nama as nama_gedung 
               FROM booking b 
               LEFT JOIN users u ON b.penyewa_id = u.id 
               LEFT JOIN gedung g ON b.gedung_id = g.id 
               WHERE b.deleted_at IS NULL 
               ORDER BY b.created_at DESC LIMIT 5";
$stmtTerbaru = $conn->query($sqlTerbaru);
$bookings = $stmtTerbaru->fetchAll(PDO::FETCH_ASSOC);

// F. Gedung Terpopuler (Berdasarkan jumlah booking 'selesai')
$sqlPopuler = "SELECT g.*, 
              (SELECT COUNT(*) FROM booking b WHERE b.gedung_id = g.id AND b.status = 'selesai') as total_sewa
              FROM gedung g
              WHERE g.deleted_at IS NULL
              ORDER BY total_sewa DESC LIMIT 4";
$stmtPopuler = $conn->query($sqlPopuler);
$gedungPopuler = $stmtPopuler->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../../../includes/admin/header_admin.php';
?>

<div class="p-6 max-w-[1600px] mx-auto pb-24" x-data="{ loaded: false }" x-init="setTimeout(() => loaded = true, 100)">
    
    <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end transition-all duration-500"
         :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'">
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

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-8 transition-all duration-500 delay-100"
         :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'">
        
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
                        Pending & Proses
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8 transition-all duration-500 delay-200"
         :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'">
        
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
                                <td colspan="5" class="px-8 py-12 text-center text-slate-400 dark:text-slate-500">
                                    <div class="flex flex-col items-center">
                                        <i class="fa-regular fa-calendar-xmark text-3xl mb-2 opacity-50"></i>
                                        <p>Belum ada data booking terbaru.</p>
                                    </div>
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php foreach($bookings as $row): ?>
                                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/30 transition-colors group">
                                    <td class="px-8 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="h-10 w-10 rounded-full bg-primary/10 dark:bg-primary/20 flex items-center justify-center text-primary font-bold text-xs uppercase">
                                                <?= substr($row['nama_lengkap'] ?? 'U', 0, 2) ?>
                                            </div>
                                            <div>
                                                <div class="font-bold text-slate-800 dark:text-white truncate max-w-[120px]"><?= htmlspecialchars($row['nama_lengkap']) ?></div>
                                                <div class="text-xs text-slate-400 dark:text-slate-500 font-mono">#<?= htmlspecialchars($row['booking_code']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 font-medium text-slate-700 dark:text-slate-300 truncate max-w-[150px]">
                                        <?= htmlspecialchars($row['nama_gedung']) ?>
                                    </td>
                                    <td class="px-6 py-4 text-slate-600 dark:text-slate-400 whitespace-nowrap">
                                        <?= date('d M Y', strtotime($row['tanggal_mulai'])) ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php
                                        $statusClass = [
                                            'pending' => 'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400',
                                            'disetujui' => 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400',
                                            'paid' => 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400',
                                            'selesai' => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
                                            'ditolak' => 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400',
                                            'batal' => 'bg-rose-100 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400',
                                        ];
                                        $class = $statusClass[$row['status']] ?? 'bg-gray-100 text-gray-600';
                                        ?>
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold capitalize <?= $class ?>">
                                            <?= $row['status'] ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="/situs-rental-gedung/admin/data/booking/detail.php?id=<?= $row['id'] ?>" 
                                           class="p-2 rounded-lg text-slate-400 hover:text-primary hover:bg-slate-100 dark:hover:bg-slate-700 transition-all inline-block">
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
                <p class="mt-2 text-white/80 text-sm relative z-10">Cetak laporan pendapatan bulanan dengan mudah untuk arsip bulanan.</p>
                <a href="/situs-rental-gedung/admin/laporan/" class="inline-block mt-6 w-full rounded-xl bg-white py-3 text-sm font-bold text-center text-primary hover:bg-slate-50 transition-colors shadow-lg relative z-10">
                    Buka Laporan
                </a>
            </div>

            <div class="rounded-[2rem] bg-white dark:bg-slate-800 shadow-xl shadow-slate-200/40 dark:shadow-none border border-slate-100 dark:border-slate-700 p-6 transition-colors duration-300">
                <h3 class="font-bold text-slate-800 dark:text-white mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-fire text-orange-500"></i> Gedung Terpopuler
                </h3>
                <div class="space-y-4">
                    <?php if (empty($gedungPopuler)): ?>
                        <p class="text-sm text-slate-400 text-center py-4">Belum ada data statistik.</p>
                    <?php else: ?>
                        <?php foreach($gedungPopuler as $g): ?>
                        <div class="flex items-center gap-4 p-3 rounded-2xl hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors group cursor-default">
                            <div class="h-12 w-16 rounded-xl bg-slate-200 dark:bg-slate-700 overflow-hidden relative border border-slate-100 dark:border-slate-600 shrink-0">
                                 <?php if(!empty($g['foto_utama'])): ?>
                                    <img src="/situs-rental-gedung/<?= $g['foto_utama'] ?>" class="h-full w-full object-cover" alt="">
                                 <?php else: ?>
                                    <div class="flex h-full w-full items-center justify-center text-slate-400"><i class="fa-regular fa-image"></i></div>
                                 <?php endif; ?>
                            </div>
                            <div class="flex-1 overflow-hidden">
                                <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200 group-hover:text-primary transition-colors truncate"><?= htmlspecialchars($g['nama']) ?></h4>
                                <div class="flex justify-between items-center mt-1">
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Rp <?= number_format($g['harga_per_hari']/1000000, 1) ?> jt</p>
                                    <span class="text-[10px] bg-slate-100 dark:bg-slate-700 px-2 py-0.5 rounded-full font-bold text-slate-600 dark:text-slate-300">
                                        <?= $g['total_sewa'] ?>x Sewa
                                    </span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../../../includes/admin/footer_admin.php';
?>