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

// --- Helper Functions ---

function formatRupiah($angka){
    return "Rp " . number_format($angka, 0, ',', '.');
}

function statusBadge($status) {
    $colors = [
        'pending' => 'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400',
        'disetujui' => 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400',
        'approved' => 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400',
        'paid' => 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400',
        'selesai' => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
        'completed' => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
        'batal' => 'bg-rose-100 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400',
        'cancelled' => 'bg-rose-100 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400',
        'ditolak' => 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400',
        'rejected' => 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400',
    ];
    $status = strtolower($status);
    return $colors[$status] ?? 'bg-gray-100 text-gray-600';
}

// --- 2. Logic Handler (POST Requests) ---
$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // A. Update Status (Via Modal)
        if (isset($_POST['action']) && $_POST['action'] === 'update_status') {
            $id = intval($_POST['booking_id']);
            $status = $_POST['status'];
            $allowed = ['pending', 'disetujui', 'ditolak', 'selesai', 'batal'];
            
            if (in_array($status, $allowed)) {
                $stmt = $conn->prepare("UPDATE booking SET status = ? WHERE id = ?");
                if ($stmt->execute([$status, $id])) $success_msg = "Status booking berhasil diperbarui.";
                else throw new Exception("Gagal update status.");
            } else {
                throw new Exception("Status tidak valid.");
            }
        }

        // B. Single Actions (Soft Delete & Restore)
        if (isset($_POST['action']) && $_POST['action'] === 'soft_delete') {
            $id = intval($_POST['id']);
            $stmt = $conn->prepare("UPDATE booking SET deleted_at = NOW() WHERE id = ?");
            if ($stmt->execute([$id])) $success_msg = "Data dipindahkan ke sampah.";
            else throw new Exception("Gagal menghapus data.");
        }

        if (isset($_POST['action']) && $_POST['action'] === 'restore') {
            $id = intval($_POST['id']);
            $stmt = $conn->prepare("UPDATE booking SET deleted_at = NULL WHERE id = ?");
            if ($stmt->execute([$id])) $success_msg = "Data berhasil dipulihkan.";
            else throw new Exception("Gagal memulihkan data.");
        }

        // C. Bulk Actions (Aksi Massal)
        if (isset($_POST['action']) && $_POST['action'] === 'bulk_soft_delete') {
            $ids = explode(',', $_POST['ids']);
            $ids = array_filter(array_map('intval', $ids));
            if (!empty($ids)) {
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $stmt = $conn->prepare("UPDATE booking SET deleted_at = NOW() WHERE id IN ($placeholders)");
                if ($stmt->execute($ids)) $success_msg = count($ids) . " booking dipindahkan ke sampah.";
            }
        }

        if (isset($_POST['action']) && $_POST['action'] === 'bulk_restore') {
            $ids = explode(',', $_POST['ids']);
            $ids = array_filter(array_map('intval', $ids));
            if (!empty($ids)) {
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $stmt = $conn->prepare("UPDATE booking SET deleted_at = NULL WHERE id IN ($placeholders)");
                if ($stmt->execute($ids)) $success_msg = count($ids) . " booking berhasil dipulihkan.";
            }
        }

    } catch (Exception $e) {
        $error_msg = "Error: " . $e->getMessage();
    }
}

// --- 3. Filter & Pagination Logic ---
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Filter Vars
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$date_start = $_GET['date_start'] ?? '';
$date_end = $_GET['date_end'] ?? '';
$show_deleted = isset($_GET['show_deleted']) && $_GET['show_deleted'] == '1';

// Build Query
$where_clauses = [];
$params = [];

if (!empty($search)) {
    $where_clauses[] = "(b.booking_code LIKE ? OR u.nama_lengkap LIKE ? OR g.nama LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param; $params[] = $search_param; $params[] = $search_param;
}

if (!empty($status_filter)) {
    $where_clauses[] = "b.status = ?";
    $params[] = $status_filter;
}

if (!empty($date_start) && !empty($date_end)) {
    $where_clauses[] = "b.tanggal_mulai BETWEEN ? AND ?";
    $params[] = $date_start; $params[] = $date_end;
}

if (!$show_deleted) {
    $where_clauses[] = "b.deleted_at IS NULL";
}

$where_sql = count($where_clauses) > 0 ? "WHERE " . implode(" AND ", $where_clauses) : "";

// Count Total
$count_query = "SELECT COUNT(*) FROM booking b 
                LEFT JOIN users u ON b.penyewa_id = u.id 
                LEFT JOIN gedung g ON b.gedung_id = g.id 
                $where_sql";
$stmt = $conn->prepare($count_query);
$stmt->execute($params);
$total_rows = $stmt->fetchColumn();
$total_pages = ceil($total_rows / $limit);

// Main Query
$query = "SELECT b.*, u.nama_lengkap, g.nama AS nama_gedung 
          FROM booking b 
          LEFT JOIN users u ON b.penyewa_id = u.id 
          LEFT JOIN gedung g ON b.gedung_id = g.id 
          $where_sql 
          ORDER BY b.created_at DESC 
          LIMIT $limit OFFSET $offset";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../../../includes/admin/header_admin.php';
?>

<div x-data="bookingManager()" class="p-6 max-w-[1600px] mx-auto pb-24 relative">
    
    <?php if($success_msg): ?>
    <script>Swal.fire({ icon: 'success', title: 'Berhasil', text: '<?= $success_msg ?>', timer: 1500, showConfirmButton: false });</script>
    <?php endif; ?>
    <?php if($error_msg): ?>
    <script>Swal.fire({ icon: 'error', title: 'Gagal', text: '<?= $error_msg ?>' });</script>
    <?php endif; ?>

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Data Booking</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Kelola semua reservasi gedung masuk.</p>
        </div>
        
        <div class="flex items-center gap-2">
            <button @click="showFilter = !showFilter" 
                    :class="showFilter ? 'bg-indigo-100 text-primary ring-2 ring-primary/20' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300'"
                    class="flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm font-semibold transition-all hover:bg-slate-50 dark:hover:bg-slate-700">
                <i class="fa-solid fa-filter"></i>
                <span>Filter</span>
                <span x-show="activeFilterCount > 0" class="flex h-5 w-5 items-center justify-center rounded-full bg-primary text-[10px] text-white" x-text="activeFilterCount"></span>
            </button>

            <a href="index.php" class="p-2.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-500 hover:text-primary transition-colors">
                <i class="fa-solid fa-rotate-right"></i>
            </a>
        </div>
    </div>

    <div x-show="showFilter" x-collapse 
         class="mb-6 bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-5">
        
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            
            <div class="col-span-1 md:col-span-1">
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1 uppercase">Pencarian</label>
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-slate-400"></i>
                    <input type="text" name="search" x-model="filter.search" placeholder="Kode / Nama / Gedung..." 
                           class="w-full pl-10 pr-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-sm focus:ring-2 focus:ring-primary/20 outline-none transition-all dark:text-white">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1 uppercase">Status</label>
                <select name="status" x-model="filter.status" class="w-full px-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-sm focus:ring-2 focus:ring-primary/20 outline-none transition-all dark:text-white appearance-none">
                    <option value="">Semua Status</option>
                    <option value="pending">Pending</option>
                    <option value="disetujui">Disetujui</option>
                    <option value="paid">Paid</option>
                    <option value="selesai">Selesai</option>
                    <option value="batal">Batal</option>
                    <option value="ditolak">Ditolak</option>
                </select>
            </div>

            <div class="col-span-1 md:col-span-1">
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1 uppercase">Tanggal Mulai</label>
                <div class="flex gap-2">
                    <input type="date" name="date_start" x-model="filter.date_start" class="w-1/2 px-2 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs dark:text-white">
                    <input type="date" name="date_end" x-model="filter.date_end" class="w-1/2 px-2 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs dark:text-white">
                </div>
            </div>

            <div class="flex items-center justify-between gap-3">
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <div class="relative">
                        <input type="checkbox" name="show_deleted" value="1" x-model="filter.show_deleted" class="sr-only peer">
                        <div class="w-10 h-6 bg-slate-200 rounded-full peer 
                                    peer-focus:ring-4 peer-focus:ring-primary/30 dark:peer-focus:ring-primary/20 
                                    peer-checked:after:translate-x-full peer-checked:after:border-white 
                                    after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 
                                    peer-checked:bg-primary"></div>
                    </div>
                    <span class="text-xs font-semibold text-slate-600 dark:text-slate-300">Sampah</span>
                </label>
                
                <button type="submit" 
                        class="bg-primary hover:bg-primary/90 shadow-lg shadow-primary/30 text-white px-6 py-2 rounded-xl text-sm font-semibold transition-all">
                    Terapkan
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 dark:bg-slate-700/50 text-xs uppercase text-slate-500 dark:text-slate-400 font-bold tracking-wider">
                    <tr>
                        <th class="p-4 w-10 text-center">
                            <input type="checkbox" @click="toggleAll" x-model="allSelected" class="rounded border-slate-300 text-primary focus:ring-primary h-4 w-4">
                        </th>
                        <th class="p-4 w-12 text-center">No</th>
                        <th class="p-4">Kode Booking</th>
                        <th class="p-4">Penyewa</th>
                        <th class="p-4 hidden md:table-cell">Gedung</th>
                        <th class="p-4">Tanggal & Durasi</th>
                        <th class="p-4 text-center">Status</th>
                        <th class="p-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700 text-sm">
                    <?php if (count($bookings) > 0): ?>
                        <?php foreach($bookings as $index => $row): ?>
                        <tr class="transition-colors hover:bg-slate-50 dark:hover:bg-slate-700/30 group 
                                   <?= !empty($row['deleted_at']) ? 'bg-red-50/60 dark:bg-red-900/10 text-red-700 dark:text-red-300' : 'text-slate-600 dark:text-slate-300' ?>">
                            
                            <td class="p-4 text-center">
                                <input type="checkbox" value="<?= $row['id'] ?>" x-model="selectedItems" class="rounded border-slate-300 text-primary focus:ring-primary h-4 w-4">
                            </td>
                            <td class="p-4 text-center font-mono text-slate-400"><?= $offset + $index + 1 ?></td>
                            <td class="p-4 font-semibold font-mono">
                                #<?= htmlspecialchars($row['booking_code']) ?>
                                <?php if(!empty($row['deleted_at'])): ?>
                                    <span class="ml-2 text-[10px] bg-red-100 text-red-600 px-1.5 py-0.5 rounded border border-red-200">DELETED</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4">
                                <div class="flex items-center gap-2">
                                    <div class="h-8 w-8 rounded-full bg-slate-200 dark:bg-slate-600 flex items-center justify-center text-xs font-bold uppercase">
                                        <?= substr($row['nama_lengkap'] ?? 'U', 0, 1) ?>
                                    </div>
                                    <span class="truncate max-w-[150px]" title="<?= htmlspecialchars($row['nama_lengkap']) ?>">
                                        <?= htmlspecialchars($row['nama_lengkap']) ?>
                                    </span>
                                </div>
                            </td>
                            <td class="p-4 hidden md:table-cell">
                                <span class="truncate max-w-[200px] block" title="<?= htmlspecialchars($row['nama_gedung']) ?>">
                                    <?= htmlspecialchars($row['nama_gedung']) ?>
                                </span>
                            </td>
                            <td class="p-4">
                                <div class="flex flex-col">
                                    <span class="font-medium"><?= date('d M Y', strtotime($row['tanggal_mulai'])) ?></span>
                                    <span class="text-xs opacity-70"><?= $row['durasi_hari'] ?> Hari</span>
                                </div>
                            </td>
                            <td class="p-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= statusBadge($row['status']) ?>">
                                    <?= ucfirst($row['status']) ?>
                                </span>
                            </td>
                            <td class="p-4 text-right">
                                <div class="flex justify-end items-center gap-2">
                                    <?php if(empty($row['deleted_at'])): ?>
                                        <button @click="openModal('<?= $row['id'] ?>', '<?= $row['booking_code'] ?>', '<?= $row['status'] ?>')" 
                                                class="p-2 rounded-lg text-slate-400 hover:text-primary hover:bg-indigo-50 dark:hover:bg-slate-700 transition-all" title="Edit Status">
                                            <i class="fa-regular fa-pen-to-square"></i>
                                        </button>
                                        <a href="detail.php?id=<?= $row['id'] ?>" class="p-2 rounded-lg text-slate-400 hover:text-blue-500 hover:bg-blue-50 dark:hover:bg-slate-700 transition-all" title="Lihat Detail">
                                            <i class="fa-regular fa-eye"></i>
                                        </a>
                                        <button @click="confirmDelete(<?= $row['id'] ?>)" 
                                                class="p-2 rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-slate-700 transition-all" title="Hapus">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </button>
                                    <?php else: ?>
                                        <button @click="confirmRestore(<?= $row['id'] ?>)" 
                                                class="p-2 rounded-lg text-emerald-500 bg-emerald-50 hover:bg-emerald-100 transition-all text-xs font-bold" title="Pulihkan">
                                            Restore
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="p-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="h-16 w-16 bg-slate-100 dark:bg-slate-700 rounded-full flex items-center justify-center mb-4">
                                        <i class="fa-regular fa-folder-open text-3xl"></i>
                                    </div>
                                    <p class="font-medium">Tidak ada data ditemukan</p>
                                    <p class="text-xs mt-1">Coba ubah filter pencarian Anda.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex flex-col md:flex-row items-center justify-between gap-4">
            <span class="text-xs text-slate-500 dark:text-slate-400">
                Menampilkan <span class="font-bold text-slate-700 dark:text-slate-200"><?= count($bookings) ?></span> dari <span class="font-bold text-slate-700 dark:text-slate-200"><?= $total_rows ?></span> data
            </span>
            
            <div class="flex items-center gap-1">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>&search=<?= $search ?>&status=<?= $status_filter ?>" class="px-3 py-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 text-xs transition-colors">Prev</a>
                <?php endif; ?>

                <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                    <?php if ($p == $page || $p == 1 || $p == $total_pages || ($p >= $page - 1 && $p <= $page + 1)): ?>
                        <a href="?page=<?= $p ?>&search=<?= $search ?>&status=<?= $status_filter ?>" 
                           class="px-3 py-1 rounded-lg border text-xs font-bold transition-colors <?= $p == $page ? 'bg-primary border-primary text-white shadow-md shadow-primary/30' : 'border-slate-200 bg-white hover:bg-slate-50 text-slate-600' ?>">
                            <?= $p ?>
                        </a>
                    <?php elseif ($p == 2 || $p == $total_pages - 1): ?>
                        <span class="text-slate-400 px-1">...</span>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?= $page + 1 ?>&search=<?= $search ?>&status=<?= $status_filter ?>" class="px-3 py-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 text-xs transition-colors">Next</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div x-show="selectedItems.length > 0" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-10"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-10"
         class="fixed bottom-6 left-1/2 -translate-x-1/2 z-40 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-2xl rounded-2xl px-6 py-3 flex items-center gap-4 min-w-[300px] justify-between">
        
        <div class="flex items-center gap-3">
            <span class="bg-primary text-white text-xs font-bold px-2.5 py-1 rounded-lg" x-text="selectedItems.length"></span>
            <span class="text-sm font-semibold text-slate-700 dark:text-white">Item Terpilih</span>
        </div>

        <div class="flex items-center gap-2">
            <button @click="confirmBulkRestore" 
                    class="px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 text-xs font-bold transition-colors">
                <i class="fa-solid fa-rotate-left mr-1"></i> Restore All
            </button>
            <button @click="confirmBulkDelete" 
                    class="px-3 py-1.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 text-xs font-bold transition-colors">
                <i class="fa-regular fa-trash-can mr-1"></i> Delete All
            </button>
        </div>
    </div>

    <div x-show="modalOpen" style="display: none;"
         class="fixed inset-0 z-50 flex items-start justify-center pt-20"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" @click="closeModal"></div>

        <div class="relative bg-white dark:bg-slate-800 w-full max-w-lg rounded-2xl shadow-2xl border border-slate-100 dark:border-slate-700 overflow-hidden"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="-translate-y-full opacity-0"
             x-transition:enter-end="translate-y-0 opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="translate-y-0 opacity-100"
             x-transition:leave-end="-translate-y-full opacity-0">
            
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-700/30">
                <h3 class="font-bold text-lg text-slate-800 dark:text-white">Update Status Booking</h3>
                <button @click="closeModal" class="text-slate-400 hover:text-rose-500 transition-colors">
                    <i class="fa-solid fa-times text-xl"></i>
                </button>
            </div>
            
            <form method="POST" class="p-6">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="booking_id" :value="currentId">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Kode Booking</label>
                    <input type="text" :value="currentCode" disabled class="w-full px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-500 cursor-not-allowed">
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Pilih Status Baru</label>
                    <div class="grid grid-cols-2 gap-3">
                        <?php foreach(['pending', 'disetujui', 'ditolak', 'selesai', 'batal'] as $st): ?>
                        <label class="cursor-pointer">
                            <input type="radio" name="status" value="<?= $st ?>" x-model="currentStatus" class="peer sr-only">
                            <div class="px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-700 text-center text-sm font-medium capitalize transition-all peer-checked:border-primary peer-checked:bg-primary/10 peer-checked:text-primary hover:bg-slate-50 dark:hover:bg-slate-700">
                                <?= $st ?>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" @click="closeModal" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-700 transition-colors font-medium text-sm">Batal</button>
                    <button type="submit" class="px-6 py-2 rounded-xl bg-primary text-white hover:bg-primary/90 shadow-lg shadow-primary/30 transition-all font-bold text-sm">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <form id="deleteForm" method="POST" class="hidden">
        <input type="hidden" name="action" value="soft_delete">
        <input type="hidden" name="id" id="deleteId">
    </form>
    
    <form id="restoreForm" method="POST" class="hidden">
        <input type="hidden" name="action" value="restore">
        <input type="hidden" name="id" id="restoreId">
    </form>

    <form id="bulkDeleteForm" method="POST" class="hidden">
        <input type="hidden" name="action" value="bulk_soft_delete">
        <input type="hidden" name="ids" id="bulkDeleteIds">
    </form>

    <form id="bulkRestoreForm" method="POST" class="hidden">
        <input type="hidden" name="action" value="bulk_restore">
        <input type="hidden" name="ids" id="bulkRestoreIds">
    </form>
</div>

<script>
    function bookingManager() {
        return {
            filter: {
                search: '<?= addslashes($search) ?>',
                status: '<?= $status_filter ?>',
                date_start: '<?= $date_start ?>',
                date_end: '<?= $date_end ?>',
                show_deleted: <?= $show_deleted ? 'true' : 'false' ?>
            },

            showFilter: <?= (!empty($search) || !empty($status_filter)) ? 'true' : 'false' ?>,
            
            get activeFilterCount() {
                let count = 0;
                if (this.filter.search) count++;
                if (this.filter.status) count++;
                if (this.filter.date_start || this.filter.date_end) count++;
                if (this.filter.show_deleted) count++;
                return count;
            },
            
            // Selection Logic
            allSelected: false,
            selectedItems: [],
            toggleAll() {
                this.allSelected = !this.allSelected;
                this.selectedItems = [];
                if (this.allSelected) {
                    document.querySelectorAll('input[type="checkbox"][x-model="selectedItems"]').forEach(el => {
                        this.selectedItems.push(el.value);
                    });
                }
            },

            modalOpen: false,
            currentId: '',
            currentCode: '',
            currentStatus: '',

            openModal(id, code, status) {
                this.currentId = id;
                this.currentCode = code;
                this.currentStatus = status.toLowerCase();
                this.modalOpen = true;
            },
            closeModal() {
                this.modalOpen = false;
            },

            // Single Actions
            confirmDelete(id) {
                Swal.fire({
                    title: 'Pindahkan ke Sampah?',
                    text: "Data akan disembunyikan tapi tidak dihapus permanen.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#cbd5e1',
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('deleteId').value = id;
                        document.getElementById('deleteForm').submit();
                    }
                });
            },
            
            confirmRestore(id) {
                Swal.fire({
                    title: 'Pulihkan Data?',
                    text: "Data akan kembali aktif dan muncul di daftar utama.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#cbd5e1',
                    confirmButtonText: 'Ya, Pulihkan'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('restoreId').value = id;
                        document.getElementById('restoreForm').submit();
                    }
                });
            },

            // Bulk Actions
            confirmBulkDelete() {
                Swal.fire({
                    title: 'Hapus ' + this.selectedItems.length + ' Booking?',
                    text: "Data yang dipilih akan dipindahkan ke sampah.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#cbd5e1',
                    confirmButtonText: 'Ya, Hapus Semua'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('bulkDeleteIds').value = this.selectedItems.join(',');
                        document.getElementById('bulkDeleteForm').submit();
                    }
                });
            },
            
            confirmBulkRestore() {
                Swal.fire({
                    title: 'Pulihkan ' + this.selectedItems.length + ' Booking?',
                    text: "Data yang dipilih akan kembali aktif.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#cbd5e1',
                    confirmButtonText: 'Ya, Pulihkan Semua'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('bulkRestoreIds').value = this.selectedItems.join(',');
                        document.getElementById('bulkRestoreForm').submit();
                    }
                });
            }
        }
    }
</script>

<?php
require_once __DIR__ . '/../../../includes/admin/footer_admin.php';
?>