<?php
require_once __DIR__ . '/../../../includes/admin/header_admin.php';
require_once __DIR__ . '/../../../modules/crud.php';

$success = '';
$error = '';

// Handle Status Update
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];
    $catatan = $_GET['catatan'] ?? '';
    
    $status_map = [
        'approve' => 'disetujui',
        'reject' => 'ditolak',
        'complete' => 'selesai',
        'cancel' => 'batal'
    ];
    
    if (isset($status_map[$action])) {
        $data = ['status' => $status_map[$action]];
        if (!empty($catatan)) {
            $data['catatan_admin'] = $catatan;
        }
        
        $result = update('booking', $id, $data);
        if ($result['success']) {
            $success = 'Status booking berhasil diupdate!';
        } else {
            $error = $result['message'];
        }
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $result = delete('booking', $id);
    if ($result['success']) {
        $success = 'Booking berhasil dihapus!';
    } else {
        $error = $result['message'];
    }
}

// Get data with pagination and search
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$where = [];
if (!empty($status_filter)) {
    $where['b.status'] = $status_filter;
}

// Custom query to join with users and gedung
$sql = "SELECT b.*, u.nama_lengkap as penyewa_nama, u.email as penyewa_email, 
        g.nama as gedung_nama, g.harga_per_hari
        FROM booking b
        JOIN users u ON b.penyewa_id = u.id
        JOIN gedung g ON b.gedung_id = g.id";

if (!empty($search)) {
    $sql .= " WHERE (b.booking_code LIKE ? OR u.nama_lengkap LIKE ? OR g.nama LIKE ?)";
}

if (!empty($status_filter)) {
    $sql .= (!empty($search) ? " AND" : " WHERE") . " b.status = ?";
}

$sql .= " ORDER BY b.created_at DESC LIMIT 10 OFFSET ?";

$offset = ($page - 1) * 10;
$params = [];

if (!empty($search)) {
    $search_param = "%$search%";
    $params = [$search_param, $search_param, $search_param];
}

if (!empty($status_filter)) {
    $params[] = $status_filter;
}

$params[] = $offset;

$booking_list = query($sql, $params);

// Get total for pagination
$count_sql = "SELECT COUNT(*) as total FROM booking b";
if (!empty($where)) {
    $count_sql .= " WHERE b.status = ?";
    $total_booking = query($count_sql, [$status_filter])[0]['total'] ?? 0;
} else {
    $total_booking = count_records('booking');
}

$total_pages = ceil($total_booking / 10);

// Get statistics
$pending = count_records('booking', ['status' => 'pending']);
$disetujui = count_records('booking', ['status' => 'disetujui']);
$selesai = count_records('booking', ['status' => 'selesai']);
$ditolak = count_records('booking', ['status' => 'ditolak']);
?>

<!-- Page Header -->
<div class="mb-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-slate-800 mb-2">Manajemen Booking</h1>
            <p class="text-slate-600">Kelola dan approve booking dari penyewa</p>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-2xl p-6 text-white shadow-lg">
        <div class="flex items-center justify-between mb-4">
            <div class="h-12 w-12 rounded-xl bg-white/20 flex items-center justify-center">
                <i class="fa-solid fa-clock text-2xl"></i>
            </div>
            <span class="text-3xl font-bold"><?= $pending ?></span>
        </div>
        <p class="text-orange-100">Pending</p>
    </div>
    
    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-2xl p-6 text-white shadow-lg">
        <div class="flex items-center justify-between mb-4">
            <div class="h-12 w-12 rounded-xl bg-white/20 flex items-center justify-center">
                <i class="fa-solid fa-check-circle text-2xl"></i>
            </div>
            <span class="text-3xl font-bold"><?= $disetujui ?></span>
        </div>
        <p class="text-green-100">Disetujui</p>
    </div>
    
    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl p-6 text-white shadow-lg">
        <div class="flex items-center justify-between mb-4">
            <div class="h-12 w-12 rounded-xl bg-white/20 flex items-center justify-center">
                <i class="fa-solid fa-flag-checkered text-2xl"></i>
            </div>
            <span class="text-3xl font-bold"><?= $selesai ?></span>
        </div>
        <p class="text-blue-100">Selesai</p>
    </div>
    
    <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-2xl p-6 text-white shadow-lg">
        <div class="flex items-center justify-between mb-4">
            <div class="h-12 w-12 rounded-xl bg-white/20 flex items-center justify-center">
                <i class="fa-solid fa-times-circle text-2xl"></i>
            </div>
            <span class="text-3xl font-bold"><?= $ditolak ?></span>
        </div>
        <p class="text-red-100">Ditolak</p>
    </div>
</div>

<!-- Success/Error Messages -->
<?php if ($success): ?>
<div class="mb-6 p-4 rounded-2xl bg-success-50 border-2 border-success-200 text-success-700 flex items-center gap-3" data-auto-hide>
    <i class="fa-solid fa-circle-check text-2xl"></i>
    <span class="font-semibold"><?= htmlspecialchars($success) ?></span>
</div>
<?php endif; ?>

<?php if ($error): ?>
<div class="mb-6 p-4 rounded-2xl bg-danger-50 border-2 border-danger-200 text-danger-700 flex items-center gap-3" data-auto-hide>
    <i class="fa-solid fa-circle-exclamation text-2xl"></i>
    <span class="font-semibold"><?= htmlspecialchars($error) ?></span>
</div>
<?php endif; ?>

<!-- Search & Filter -->
<div class="bg-white rounded-2xl shadow-lg border border-slate-200 p-6 mb-6">
    <form method="GET" class="flex flex-col md:flex-row gap-4">
        <div class="flex-1">
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 transform -translate-y-1/2 text-slate-400"></i>
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                    class="w-full pl-12 pr-4 py-3 rounded-xl border-2 border-slate-200 focus:border-primary-500 focus:outline-none transition-all"
                    placeholder="Cari kode booking, nama penyewa, atau gedung...">
            </div>
        </div>
        <select name="status" class="px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-primary-500 focus:outline-none transition-all">
            <option value="">Semua Status</option>
            <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
            <option value="disetujui" <?= $status_filter === 'disetujui' ? 'selected' : '' ?>>Disetujui</option>
            <option value="ditolak" <?= $status_filter === 'ditolak' ? 'selected' : '' ?>>Ditolak</option>
            <option value="selesai" <?= $status_filter === 'selesai' ? 'selected' : '' ?>>Selesai</option>
            <option value="batal" <?= $status_filter === 'batal' ? 'selected' : '' ?>>Batal</option>
        </select>
        <button type="submit" class="px-6 py-3 rounded-xl bg-primary-500 hover:bg-primary-600 text-white font-semibold transition-all">
            <i class="fa-solid fa-filter mr-2"></i>Filter
        </button>
        <?php if (!empty($search) || !empty($status_filter)): ?>
        <a href="?" class="px-6 py-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold transition-all">
            <i class="fa-solid fa-rotate-right mr-2"></i>Reset
        </a>
        <?php endif; ?>
    </form>
</div>

<!-- Data Table -->
<div class="bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-slate-50 border-b-2 border-slate-200">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-700 uppercase tracking-wider">Kode Booking</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-700 uppercase tracking-wider">Penyewa</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-700 uppercase tracking-wider">Gedung</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-700 uppercase tracking-wider">Tanggal</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-700 uppercase tracking-wider">Total</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-700 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 text-center text-xs font-bold text-slate-700 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                <?php if (empty($booking_list)): ?>
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                        <i class="fa-solid fa-inbox text-4xl mb-3 block text-slate-300"></i>
                        Tidak ada data booking
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($booking_list as $booking): ?>
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="font-bold text-primary-600"><?= htmlspecialchars($booking['booking_code']) ?></div>
                            <div class="text-xs text-slate-500"><?= date('d M Y H:i', strtotime($booking['created_at'])) ?></div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-semibold text-slate-800"><?= htmlspecialchars($booking['penyewa_nama']) ?></div>
                            <div class="text-sm text-slate-500"><?= htmlspecialchars($booking['penyewa_email']) ?></div>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-700">
                            <?= htmlspecialchars($booking['gedung_nama']) ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-700">
                            <div><?= date('d M Y', strtotime($booking['tanggal_mulai'])) ?></div>
                            <div class="text-xs text-slate-500"><?= $booking['durasi_hari'] ?> hari</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-slate-800">Rp <?= number_format($booking['total_harga'], 0, ',', '.') ?></div>
                        </td>
                        <td class="px-6 py-4">
                            <?php
                            $badge_class = [
                                'pending' => 'bg-warning-100 text-warning-700',
                                'disetujui' => 'bg-success-100 text-success-700',
                                'ditolak' => 'bg-danger-100 text-danger-700',
                                'selesai' => 'bg-blue-100 text-blue-700',
                                'batal' => 'bg-slate-100 text-slate-700'
                            ];
                            ?>
                            <span class="px-3 py-1 rounded-lg text-xs font-bold <?= $badge_class[$booking['status']] ?? 'bg-slate-100 text-slate-700' ?>">
                                <?= ucfirst($booking['status']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2">
                                <?php if ($booking['status'] === 'pending'): ?>
                                <button onclick="confirmAction('?action=approve&id=<?= $booking['id'] ?>', 'Setujui booking ini?', 'Konfirmasi')" 
                                    class="p-2 rounded-lg bg-green-100 hover:bg-green-200 text-green-600 transition-colors"
                                    title="Setujui">
                                    <i class="fa-solid fa-check"></i>
                                </button>
                                <button onclick="confirmAction('?action=reject&id=<?= $booking['id'] ?>', 'Tolak booking ini?', 'Konfirmasi')" 
                                    class="p-2 rounded-lg bg-red-100 hover:bg-red-200 text-red-600 transition-colors"
                                    title="Tolak">
                                    <i class="fa-solid fa-times"></i>
                                </button>
                                <?php elseif ($booking['status'] === 'disetujui'): ?>
                                <button onclick="confirmAction('?action=complete&id=<?= $booking['id'] ?>', 'Tandai booking ini selesai?', 'Konfirmasi')" 
                                    class="p-2 rounded-lg bg-blue-100 hover:bg-blue-200 text-blue-600 transition-colors"
                                    title="Selesaikan">
                                    <i class="fa-solid fa-flag-checkered"></i>
                                </button>
                                <?php endif; ?>
                                <button onclick="confirmDelete('?delete=<?= $booking['id'] ?>')" 
                                    class="p-2 rounded-lg bg-red-100 hover:bg-red-200 text-red-600 transition-colors"
                                    title="Hapus">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div class="px-6 py-4 border-t border-slate-200 flex items-center justify-between">
        <div class="text-sm text-slate-600">
            Menampilkan data booking
        </div>
        <div class="flex gap-2">
            <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($status_filter) ? '&status=' . $status_filter : '' ?>" 
                class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold transition-all">
                <i class="fa-solid fa-chevron-left"></i>
            </a>
            <?php endif; ?>
            
            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
            <a href="?page=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($status_filter) ? '&status=' . $status_filter : '' ?>" 
                class="px-4 py-2 rounded-lg <?= $i === $page ? 'bg-primary-500 text-white' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' ?> font-semibold transition-all">
                <?= $i ?>
            </a>
            <?php endfor; ?>
            
            <?php if ($page < $total_pages): ?>
            <a href="?page=<?= $page + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($status_filter) ? '&status=' . $status_filter : '' ?>" 
                class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold transition-all">
                <i class="fa-solid fa-chevron-right"></i>
            </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
<?php if ($success): ?>
    showSuccess('<?= addslashes($success) ?>');
<?php endif; ?>

<?php if ($error): ?>
    showError('<?= addslashes($error) ?>');
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/../../../includes/admin/footer_admin.php'; ?>