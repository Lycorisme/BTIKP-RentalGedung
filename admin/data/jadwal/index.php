<?php
require_once __DIR__ . '/../../../includes/admin/header_admin.php';
require_once __DIR__ . '/../../../modules/crud.php';

$success = '';
$error = '';

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gedung_id = (int)$_POST['gedung_id'];
    $tanggal = $_POST['tanggal'];
    $status = $_POST['status'];
    $booking_id = !empty($_POST['booking_id']) ? (int)$_POST['booking_id'] : null;
    
    // Check if schedule exists
    $existing = read_one('jadwal', ['gedung_id' => $gedung_id, 'tanggal' => $tanggal]);
    
    $data = [
        'gedung_id' => $gedung_id,
        'tanggal' => $tanggal,
        'status' => $status,
        'booking_id' => $booking_id
    ];
    
    if ($existing) {
        // Update
        $result = update('jadwal', $existing['id'], $data);
        if ($result['success']) {
            $success = 'Jadwal berhasil diupdate!';
        } else {
            $error = $result['message'];
        }
    } else {
        // Insert
        $result = create('jadwal', $data);
        if ($result['success']) {
            $success = 'Jadwal berhasil ditambahkan!';
        } else {
            $error = $result['message'];
        }
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $result = delete('jadwal', $id);
    if ($result['success']) {
        $success = 'Jadwal berhasil dihapus!';
    } else {
        $error = $result['message'];
    }
}

// Get data
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$gedung_filter = isset($_GET['gedung']) ? (int)$_GET['gedung'] : 0;
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('Y-m');

// Custom query
$sql = "SELECT j.*, g.nama as gedung_nama, b.booking_code
        FROM jadwal j
        JOIN gedung g ON j.gedung_id = g.id
        LEFT JOIN booking b ON j.booking_id = b.id
        WHERE 1=1";

$params = [];

if ($gedung_filter > 0) {
    $sql .= " AND j.gedung_id = ?";
    $params[] = $gedung_filter;
}

if (!empty($status_filter)) {
    $sql .= " AND j.status = ?";
    $params[] = $status_filter;
}

if (!empty($bulan)) {
    $sql .= " AND DATE_FORMAT(j.tanggal, '%Y-%m') = ?";
    $params[] = $bulan;
}

$sql .= " ORDER BY j.tanggal DESC LIMIT 10 OFFSET ?";
$offset = ($page - 1) * 10;
$params[] = $offset;

$jadwal_list = query($sql, $params);

// Get total
$count_sql = "SELECT COUNT(*) as total FROM jadwal WHERE 1=1";
$count_params = [];
if ($gedung_filter > 0) {
    $count_sql .= " AND gedung_id = ?";
    $count_params[] = $gedung_filter;
}
if (!empty($status_filter)) {
    $count_sql .= " AND status = ?";
    $count_params[] = $status_filter;
}
if (!empty($bulan)) {
    $count_sql .= " AND DATE_FORMAT(tanggal, '%Y-%m') = ?";
    $count_params[] = $bulan;
}

$total_result = query($count_sql, $count_params);
$total_jadwal = $total_result[0]['total'] ?? 0;
$total_pages = ceil($total_jadwal / 10);

// Get gedung list for filter
$gedung_list = read('gedung', [], 100, 0, 'nama ASC');

// Get statistics
$available = count_records('jadwal', ['status' => 'available']);
$booked = count_records('jadwal', ['status' => 'booked']);
$maintenance = count_records('jadwal', ['status' => 'maintenance']);
?>

<!-- Page Header -->
<div class="mb-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-slate-800 mb-2">Manajemen Jadwal</h1>
            <p class="text-slate-600">Kelola ketersediaan gedung per tanggal</p>
        </div>
        <button onclick="openModal()" 
            class="px-6 py-3 rounded-xl bg-gradient-to-r from-primary-500 to-secondary-500 text-white font-semibold shadow-lg shadow-primary-500/30 hover:shadow-xl hover:translate-y-[-2px] transition-all">
            <i class="fa-solid fa-plus mr-2"></i>Tambah Jadwal
        </button>
    </div>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-2xl p-6 text-white shadow-lg">
        <div class="flex items-center justify-between mb-4">
            <div class="h-12 w-12 rounded-xl bg-white/20 flex items-center justify-center">
                <i class="fa-solid fa-check-circle text-2xl"></i>
            </div>
            <span class="text-3xl font-bold"><?= $available ?></span>
        </div>
        <p class="text-green-100">Available</p>
    </div>
    
    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl p-6 text-white shadow-lg">
        <div class="flex items-center justify-between mb-4">
            <div class="h-12 w-12 rounded-xl bg-white/20 flex items-center justify-center">
                <i class="fa-solid fa-calendar-check text-2xl"></i>
            </div>
            <span class="text-3xl font-bold"><?= $booked ?></span>
        </div>
        <p class="text-blue-100">Booked</p>
    </div>
    
    <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-2xl p-6 text-white shadow-lg">
        <div class="flex items-center justify-between mb-4">
            <div class="h-12 w-12 rounded-xl bg-white/20 flex items-center justify-center">
                <i class="fa-solid fa-wrench text-2xl"></i>
            </div>
            <span class="text-3xl font-bold"><?= $maintenance ?></span>
        </div>
        <p class="text-orange-100">Maintenance</p>
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

<!-- Filter -->
<div class="bg-white rounded-2xl shadow-lg border border-slate-200 p-6 mb-6">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <select name="gedung" class="px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-primary-500 focus:outline-none transition-all">
            <option value="">Semua Gedung</option>
            <?php foreach ($gedung_list as $gedung): ?>
            <option value="<?= $gedung['id'] ?>" <?= $gedung_filter === $gedung['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($gedung['nama']) ?>
            </option>
            <?php endforeach; ?>
        </select>
        
        <select name="status" class="px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-primary-500 focus:outline-none transition-all">
            <option value="">Semua Status</option>
            <option value="available" <?= $status_filter === 'available' ? 'selected' : '' ?>>Available</option>
            <option value="booked" <?= $status_filter === 'booked' ? 'selected' : '' ?>>Booked</option>
            <option value="maintenance" <?= $status_filter === 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
        </select>
        
        <input type="month" name="bulan" value="<?= htmlspecialchars($bulan) ?>" 
            class="px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-primary-500 focus:outline-none transition-all">
        
        <div class="flex gap-2">
            <button type="submit" class="flex-1 px-6 py-3 rounded-xl bg-primary-500 hover:bg-primary-600 text-white font-semibold transition-all">
                <i class="fa-solid fa-filter mr-2"></i>Filter
            </button>
            <?php if ($gedung_filter || $status_filter): ?>
            <a href="?" class="px-6 py-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold transition-all">
                <i class="fa-solid fa-rotate-right"></i>
            </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Data Table -->
<div class="bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-slate-50 border-b-2 border-slate-200">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-700 uppercase tracking-wider">No</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-700 uppercase tracking-wider">Tanggal</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-700 uppercase tracking-wider">Gedung</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-700 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-700 uppercase tracking-wider">Booking</th>
                    <th class="px-6 py-4 text-center text-xs font-bold text-slate-700 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                <?php if (empty($jadwal_list)): ?>
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                        <i class="fa-solid fa-calendar-xmark text-4xl mb-3 block text-slate-300"></i>
                        Tidak ada data jadwal
                    </td>
                </tr>
                <?php else: ?>
                    <?php 
                    $no = ($page - 1) * 10 + 1;
                    foreach ($jadwal_list as $jadwal): 
                    ?>
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 text-sm text-slate-700"><?= $no++ ?></td>
                        <td class="px-6 py-4">
                            <div class="font-semibold text-slate-800"><?= date('d M Y', strtotime($jadwal['tanggal'])) ?></div>
                            <div class="text-xs text-slate-500"><?= date('l', strtotime($jadwal['tanggal'])) ?></div>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-700">
                            <?= htmlspecialchars($jadwal['gedung_nama']) ?>
                        </td>
                        <td class="px-6 py-4">
                            <?php
                            $badge_class = [
                                'available' => 'bg-success-100 text-success-700',
                                'booked' => 'bg-blue-100 text-blue-700',
                                'maintenance' => 'bg-warning-100 text-warning-700'
                            ];
                            ?>
                            <span class="px-3 py-1 rounded-lg text-xs font-bold <?= $badge_class[$jadwal['status']] ?? 'bg-slate-100 text-slate-700' ?>">
                                <?= ucfirst($jadwal['status']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-700">
                            <?php if (!empty($jadwal['booking_code'])): ?>
                            <span class="text-primary-600 font-mono"><?= htmlspecialchars($jadwal['booking_code']) ?></span>
                            <?php else: ?>
                            <span class="text-slate-400">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2">
                                <button onclick="confirmDelete('?delete=<?= $jadwal['id'] ?>')" 
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
            Total: <?= $total_jadwal ?> jadwal
        </div>
        <div class="flex gap-2">
            <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?><?= $gedung_filter ? '&gedung=' . $gedung_filter : '' ?><?= $status_filter ? '&status=' . $status_filter : '' ?><?= $bulan ? '&bulan=' . $bulan : '' ?>" 
                class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold transition-all">
                <i class="fa-solid fa-chevron-left"></i>
            </a>
            <?php endif; ?>
            
            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
            <a href="?page=<?= $i ?><?= $gedung_filter ? '&gedung=' . $gedung_filter : '' ?><?= $status_filter ? '&status=' . $status_filter : '' ?><?= $bulan ? '&bulan=' . $bulan : '' ?>" 
                class="px-4 py-2 rounded-lg <?= $i === $page ? 'bg-primary-500 text-white' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' ?> font-semibold transition-all">
                <?= $i ?>
            </a>
            <?php endfor; ?>
            
            <?php if ($page < $total_pages): ?>
            <a href="?page=<?= $page + 1 ?><?= $gedung_filter ? '&gedung=' . $gedung_filter : '' ?><?= $status_filter ? '&status=' . $status_filter : '' ?><?= $bulan ? '&bulan=' . $bulan : '' ?>" 
                class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold transition-all">
                <i class="fa-solid fa-chevron-right"></i>
            </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Add Modal -->
<div id="jadwalModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-2xl w-full">
        <div class="border-b border-slate-200 px-8 py-6 rounded-t-3xl">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold text-slate-800">
                    <i class="fa-solid fa-calendar-plus mr-3 text-primary-500"></i>
                    Tambah Jadwal
                </h2>
                <button onclick="closeModal()" class="p-2 rounded-xl hover:bg-slate-100 transition-colors">
                    <i class="fa-solid fa-xmark text-2xl text-slate-400"></i>
                </button>
            </div>
        </div>
        
        <form method="POST" class="p-8 space-y-6">
            <div class="grid grid-cols-1 gap-6">
                <!-- Gedung -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">
                        Gedung <span class="text-danger-500">*</span>
                    </label>
                    <select name="gedung_id" required 
                        class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-primary-500 focus:outline-none transition-all">
                        <option value="">Pilih Gedung</option>
                        <?php foreach ($gedung_list as $gedung): ?>
                        <option value="<?= $gedung['id'] ?>"><?= htmlspecialchars($gedung['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Tanggal -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">
                        Tanggal <span class="text-danger-500">*</span>
                    </label>
                    <input type="date" name="tanggal" required 
                        class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-primary-500 focus:outline-none transition-all">
                </div>
                
                <!-- Status -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">
                        Status <span class="text-danger-500">*</span>
                    </label>
                    <select name="status" required 
                        class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-primary-500 focus:outline-none transition-all">
                        <option value="available">Available</option>
                        <option value="booked">Booked</option>
                        <option value="maintenance">Maintenance</option>
                    </select>
                </div>
                
                <!-- Booking ID (Optional) -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">
                        Booking ID (Opsional)
                    </label>
                    <input type="number" name="booking_id" 
                        class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-primary-500 focus:outline-none transition-all"
                        placeholder="Kosongkan jika tidak terkait booking">
                    <p class="text-xs text-slate-500 mt-1">Isi hanya jika status = booked</p>
                </div>
            </div>
            
            <!-- Buttons -->
            <div class="flex gap-3 justify-end pt-6 border-t border-slate-200">
                <button type="button" onclick="closeModal()" 
                    class="px-6 py-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold transition-all">
                    Batal
                </button>
                <button type="submit" 
                    class="px-6 py-3 rounded-xl bg-gradient-to-r from-primary-500 to-secondary-500 text-white font-semibold shadow-lg shadow-primary-500/30 hover:shadow-xl hover:translate-y-[-2px] transition-all">
                    <i class="fa-solid fa-save mr-2"></i>Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal() {
    document.getElementById('jadwalModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('jadwalModal').classList.add('hidden');
}

// Close modal on outside click
document.getElementById('jadwalModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

<?php if ($success): ?>
    showSuccess('<?= addslashes($success) ?>');
<?php endif; ?>

<?php if ($error): ?>
    showError('<?= addslashes($error) ?>');
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/../../../includes/admin/footer_admin.php'; ?>