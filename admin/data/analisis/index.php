<?php
// --- 1. Setup & Konfigurasi ---
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../includes/admin/auth.php';

// Inisialisasi Koneksi
$conn = getDB();

// Cek Login & Role
requireLogin();
requireRole(['admin', 'superadmin']);

// --- 2. Filter Logic (Date Range) ---
// Default: 30 Hari Terakhir
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$end_date   = isset($_GET['end_date'])   ? $_GET['end_date']   : date('Y-m-d');

// Cek apakah user sedang melakukan filter custom (untuk auto-open filter)
$is_filter_active = (isset($_GET['start_date']) && $_GET['start_date'] !== date('Y-m-d', strtotime('-30 days'))) || 
                    (isset($_GET['end_date']) && $_GET['end_date'] !== date('Y-m-d'));

// --- 3. Pagination Logic (Untuk Tabel Performa) ---
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10; // Maksimal 10 data per page
$offset = ($page - 1) * $limit;

// --- 4. Data Fetching Functions ---

function getSummaryStats($conn, $start, $end) {
    $sql = "SELECT 
                COALESCE(SUM(total_bayar), 0) as total_revenue,
                COUNT(*) as total_bookings,
                COALESCE(SUM(CASE WHEN status IN ('disetujui', 'selesai') THEN 1 ELSE 0 END), 0) as success_bookings,
                COALESCE(SUM(CASE WHEN status = 'batal' THEN 1 ELSE 0 END), 0) as cancelled_bookings
            FROM booking 
            WHERE DATE(created_at) BETWEEN ? AND ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$start, $end]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getRevenueTrend($conn, $start, $end) {
    $sql = "SELECT 
                DATE(created_at) as tanggal, 
                SUM(total_bayar) as omzet 
            FROM booking 
            WHERE status IN ('disetujui', 'selesai') 
              AND DATE(created_at) BETWEEN ? AND ?
            GROUP BY DATE(created_at)
            ORDER BY tanggal ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$start, $end]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getGedungPerformance($conn, $start, $end, $limit, $offset) {
    // Data Query
    $sql = "SELECT 
                g.nama, 
                COUNT(b.id) as jumlah_sewa,
                COALESCE(SUM(b.total_bayar), 0) as total_pendapatan
            FROM gedung g
            LEFT JOIN booking b ON g.id = b.gedung_id 
                AND b.status IN ('disetujui', 'selesai')
                AND DATE(b.created_at) BETWEEN ? AND ?
            GROUP BY g.id
            ORDER BY total_pendapatan DESC
            LIMIT $limit OFFSET $offset";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$start, $end]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Count Total Rows (For Pagination)
    $sqlCount = "SELECT COUNT(DISTINCT g.id) 
                 FROM gedung g 
                 LEFT JOIN booking b ON g.id = b.gedung_id 
                    AND b.status IN ('disetujui', 'selesai')
                    AND DATE(b.created_at) BETWEEN ? AND ?";
    $stmtCount = $conn->prepare($sqlCount);
    $stmtCount->execute([$start, $end]);
    $total_rows = $stmtCount->fetchColumn();

    return ['data' => $data, 'total' => $total_rows];
}

// Eksekusi Query
$summary = getSummaryStats($conn, $start_date, $end_date);
$trend   = getRevenueTrend($conn, $start_date, $end_date);
$performanceData = getGedungPerformance($conn, $start_date, $end_date, $limit, $offset);

$performance = $performanceData['data'];
$total_rows_performance = $performanceData['total'];
$total_pages = ceil($total_rows_performance / $limit);

// Hitung Conversion Rate
$conversion_rate = $summary['total_bookings'] > 0 
    ? round(($summary['success_bookings'] / $summary['total_bookings']) * 100, 1) 
    : 0;

// Helper Rupiah
function formatRupiah($angka){
    return "Rp " . number_format($angka, 0, ',', '.');
}

// Build Pagination URL (Keep Date Filters)
$query_params = $_GET;
unset($query_params['page']);
$base_url = '?' . http_build_query($query_params);
$pagination_url = empty($query_params) ? '?page=' : $base_url . '&page=';

require_once __DIR__ . '/../../../includes/admin/header_admin.php';
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div x-data="analyticsManager()" class="p-6 max-w-[1600px] mx-auto pb-24 relative">

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Analisis Pendapatan</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Dashboard performa penyewaan gedung & finansial.</p>
        </div>
        
        <button @click="showFilter = !showFilter" 
                :class="showFilter ? 'bg-indigo-100 text-primary ring-2 ring-primary/20' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300'"
                class="flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm font-semibold transition-all hover:bg-slate-50 dark:hover:bg-slate-700">
            <i class="fa-solid fa-filter"></i>
            <span>Filter Periode</span>
        </button>
    </div>

    <div x-show="showFilter" x-collapse 
         class="mb-8 bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-5">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div class="col-span-1">
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1 uppercase">Tanggal Mulai</label>
                <input type="date" name="start_date" value="<?= $start_date ?>" class="w-full px-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-sm focus:ring-2 focus:ring-primary/20 outline-none transition-all dark:text-white">
            </div>
            <div class="col-span-1">
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1 uppercase">Tanggal Selesai</label>
                <input type="date" name="end_date" value="<?= $end_date ?>" class="w-full px-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-sm focus:ring-2 focus:ring-primary/20 outline-none transition-all dark:text-white">
            </div>
            <div class="col-span-1">
                <button type="submit" class="bg-primary hover:bg-primary/90 shadow-lg shadow-primary/30 text-white px-6 py-2 rounded-xl text-sm font-semibold transition-all w-full">
                    Terapkan Filter
                </button>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm border border-slate-100 dark:border-slate-700 relative overflow-hidden group">
            <div class="absolute right-0 top-0 h-24 w-24 bg-emerald-50 dark:bg-emerald-900/20 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
            <div class="relative z-10">
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Total Pendapatan</p>
                <h3 class="text-2xl font-bold text-slate-800 dark:text-white"><?= formatRupiah($summary['total_revenue']) ?></h3>
                <div class="mt-2 flex items-center text-xs text-emerald-600 bg-emerald-100 w-fit px-2 py-0.5 rounded-full font-bold">
                    <i class="fa-solid fa-sack-dollar mr-1"></i> Gross Revenue
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm border border-slate-100 dark:border-slate-700 relative overflow-hidden group">
            <div class="absolute right-0 top-0 h-24 w-24 bg-blue-50 dark:bg-blue-900/20 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
            <div class="relative z-10">
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Total Transaksi</p>
                <h3 class="text-2xl font-bold text-slate-800 dark:text-white"><?= number_format($summary['total_bookings']) ?></h3>
                <div class="mt-2 flex items-center text-xs text-blue-600 bg-blue-100 w-fit px-2 py-0.5 rounded-full font-bold">
                    <i class="fa-solid fa-receipt mr-1"></i> Semua Status
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm border border-slate-100 dark:border-slate-700 relative overflow-hidden group">
            <div class="absolute right-0 top-0 h-24 w-24 bg-purple-50 dark:bg-purple-900/20 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
            <div class="relative z-10">
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Success Rate</p>
                <h3 class="text-2xl font-bold text-slate-800 dark:text-white"><?= $conversion_rate ?>%</h3>
                <div class="mt-2 flex items-center text-xs text-purple-600 bg-purple-100 w-fit px-2 py-0.5 rounded-full font-bold">
                    <i class="fa-solid fa-chart-line mr-1"></i> Booking Disetujui
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm border border-slate-100 dark:border-slate-700 relative overflow-hidden group">
            <div class="absolute right-0 top-0 h-24 w-24 bg-rose-50 dark:bg-rose-900/20 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
            <div class="relative z-10">
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Dibatalkan / Ditolak</p>
                <h3 class="text-2xl font-bold text-slate-800 dark:text-white"><?= number_format($summary['cancelled_bookings']) ?></h3>
                <div class="mt-2 flex items-center text-xs text-rose-600 bg-rose-100 w-fit px-2 py-0.5 rounded-full font-bold">
                    <i class="fa-solid fa-ban mr-1"></i> Lost Potential
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <div class="lg:col-span-2 bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700">
            <h3 class="font-bold text-lg text-slate-800 dark:text-white mb-4">Grafik Tren Pendapatan</h3>
            <div class="relative h-72 w-full">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 flex flex-col items-center justify-center">
            <h3 class="font-bold text-lg text-slate-800 dark:text-white mb-4 self-start w-full">Distribusi Status</h3>
            <div class="relative h-60 w-full flex justify-center">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center">
            <h3 class="font-bold text-lg text-slate-800 dark:text-white">Performa Gedung (Top Sales)</h3>
            <button onclick="window.print()" class="text-slate-500 hover:text-slate-700 text-sm flex items-center gap-2">
                <i class="fa-solid fa-print"></i> Print Laporan
            </button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 dark:bg-slate-700/50 text-xs uppercase text-slate-500 dark:text-slate-400 font-bold tracking-wider">
                    <tr>
                        <th class="p-4 w-12 text-center">Rank</th>
                        <th class="p-4">Nama Gedung</th>
                        <th class="p-4 text-center">Jumlah Sewa</th>
                        <th class="p-4 text-right">Total Pendapatan</th>
                        <th class="p-4 w-32 text-center">Kontribusi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700 text-sm">
                    <?php if(count($performance) > 0): ?>
                        <?php foreach($performance as $index => $row): ?>
                        <?php 
                            $percentage = $summary['total_revenue'] > 0 
                                ? ($row['total_pendapatan'] / $summary['total_revenue']) * 100 
                                : 0;
                            
                            $rank = $offset + $index + 1;
                        ?>
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 text-slate-600 dark:text-slate-300">
                            <td class="p-4 text-center font-bold text-slate-400">#<?= $rank ?></td>
                            <td class="p-4 font-semibold text-slate-800 dark:text-white"><?= htmlspecialchars($row['nama']) ?></td>
                            <td class="p-4 text-center"><?= number_format($row['jumlah_sewa']) ?>x</td>
                            <td class="p-4 text-right font-mono"><?= formatRupiah($row['total_pendapatan']) ?></td>
                            <td class="p-4 text-center">
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 h-1.5 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                                        <div class="h-full bg-primary rounded-full" style="width: <?= $percentage ?>%"></div>
                                    </div>
                                    <span class="text-[10px] font-bold w-8"><?= round($percentage) ?>%</span>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="p-8 text-center text-slate-400">Belum ada data transaksi pada periode ini.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex flex-col md:flex-row items-center justify-between gap-4">
            <span class="text-xs text-slate-500 dark:text-slate-400">
                Menampilkan <span class="font-bold text-slate-700 dark:text-slate-200"><?= count($performance) ?></span> dari <span class="font-bold text-slate-700 dark:text-slate-200"><?= $total_rows_performance ?></span> gedung
            </span>
            
            <div class="flex items-center gap-1">
                <?php if ($page > 1): ?>
                    <a href="<?= $pagination_url . ($page - 1) ?>" class="px-3 py-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 text-xs transition-colors">Prev</a>
                <?php endif; ?>

                <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                    <?php if ($p == $page || $p == 1 || $p == $total_pages || ($p >= $page - 1 && $p <= $page + 1)): ?>
                        <a href="<?= $pagination_url . $p ?>" 
                           class="px-3 py-1 rounded-lg border text-xs font-bold transition-colors <?= $p == $page ? 'bg-primary border-primary text-white shadow-md shadow-primary/30' : 'border-slate-200 bg-white hover:bg-slate-50 text-slate-600' ?>">
                            <?= $p ?>
                        </a>
                    <?php elseif ($p == 2 || $p == $total_pages - 1): ?>
                        <span class="text-slate-400 px-1">...</span>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="<?= $pagination_url . ($page + 1) ?>" class="px-3 py-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 text-xs transition-colors">Next</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<script>
    function analyticsManager() {
        return {
            showFilter: <?= $is_filter_active ? 'true' : 'false' ?>
        }
    }

    // Konfigurasi Chart Revenue Trend
    const ctxRevenue = document.getElementById('revenueChart').getContext('2d');
    
    // Data dari PHP
    const trendData = <?= json_encode($trend) ?>;
    const labels = trendData.map(d => d.tanggal);
    const data = trendData.map(d => d.omzet);

    new Chart(ctxRevenue, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Pendapatan (Rp)',
                data: data,
                borderColor: '#6366f1', // Indigo-500
                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                borderWidth: 2,
                tension: 0.3,
                fill: true,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#6366f1',
                pointRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) label += ': ';
                            if (context.parsed.y !== null) {
                                label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(context.parsed.y);
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#f1f5f9' }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });

    // Konfigurasi Chart Status
    const ctxStatus = document.getElementById('statusChart').getContext('2d');
    const success = <?= $summary['success_bookings'] ?>;
    const cancelled = <?= $summary['cancelled_bookings'] ?>;
    const pending = <?= $summary['total_bookings'] - $summary['success_bookings'] - $summary['cancelled_bookings'] ?>;

    new Chart(ctxStatus, {
        type: 'doughnut',
        data: {
            labels: ['Sukses', 'Pending', 'Batal'],
            datasets: [{
                data: [success, pending, cancelled],
                backgroundColor: [
                    '#10b981', // Emerald-500
                    '#f59e0b', // Amber-500
                    '#ef4444'  // Rose-500
                ],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { usePointStyle: true, padding: 20 }
                }
            },
            cutout: '70%'
        }
    });
</script>

<?php
require_once __DIR__ . '/../../../includes/admin/footer_admin.php';
?>