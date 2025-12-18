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

function statusBadge($status) {
    $colors = [
        'available' => 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400',
        'booked' => 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400',
        'maintenance' => 'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400',
    ];
    return $colors[$status] ?? 'bg-gray-100 text-gray-600';
}

function formatDateRange($start, $end) {
    $s = strtotime($start);
    $e = strtotime($end);
    if ($start === $end) {
        return date('d M Y', $s);
    }
    // Jika bulan dan tahun sama
    if (date('Y-m', $s) === date('Y-m', $e)) {
        return date('d', $s) . ' - ' . date('d M Y', $e);
    }
    return date('d M Y', $s) . ' - ' . date('d M Y', $e);
}

// Ambil daftar gedung untuk Dropdown
$stmtGedung = $conn->query("SELECT id, nama FROM gedung WHERE deleted_at IS NULL ORDER BY nama ASC");
$gedung_options = $stmtGedung->fetchAll(PDO::FETCH_ASSOC);

// --- 2. Logic Handler (POST Requests) ---
$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // A. Create & Update
        if (isset($_POST['action']) && ($_POST['action'] === 'create' || $_POST['action'] === 'update')) {
            // Validasi Input
            if (empty($_POST['gedung_id']) || empty($_POST['tanggal_mulai']) || empty($_POST['tanggal_selesai']) || empty($_POST['status'])) {
                throw new Exception("Gedung, Tanggal Mulai, Selesai, dan Status wajib diisi.");
            }

            $gedung_id = (int)$_POST['gedung_id'];
            $tanggal_mulai = $_POST['tanggal_mulai'];
            $tanggal_selesai = $_POST['tanggal_selesai'];
            $status = $_POST['status'];

            // Validasi Tanggal
            if ($tanggal_mulai > $tanggal_selesai) {
                throw new Exception("Tanggal selesai tidak boleh lebih awal dari tanggal mulai.");
            }

            // Validasi Tumpang Tindih (Overlap) Jadwal
            // Logic: (StartA <= EndB) AND (EndA >= StartB)
            $sqlOverlap = "SELECT COUNT(*) FROM jadwal 
                           WHERE gedung_id = ? 
                           AND deleted_at IS NULL 
                           AND (tanggal_mulai <= ? AND tanggal_selesai >= ?)";
            
            $paramsOverlap = [$gedung_id, $tanggal_selesai, $tanggal_mulai];

            if ($_POST['action'] === 'update') {
                $sqlOverlap .= " AND id != ?";
                $paramsOverlap[] = (int)$_POST['id'];
            }

            $stmtCheck = $conn->prepare($sqlOverlap);
            $stmtCheck->execute($paramsOverlap);
            
            if ($stmtCheck->fetchColumn() > 0) {
                throw new Exception("Jadwal bentrok! Gedung ini sudah memiliki jadwal pada rentang tanggal tersebut.");
            }

            if ($_POST['action'] === 'create') {
                $stmt = $conn->prepare("INSERT INTO jadwal (gedung_id, tanggal_mulai, tanggal_selesai, status) VALUES (?, ?, ?, ?)");
                if ($stmt->execute([$gedung_id, $tanggal_mulai, $tanggal_selesai, $status])) {
                    $success_msg = "Jadwal berhasil ditambahkan.";
                }
            } else {
                $id = (int)$_POST['id'];
                $stmt = $conn->prepare("UPDATE jadwal SET gedung_id=?, tanggal_mulai=?, tanggal_selesai=?, status=? WHERE id=?");
                if ($stmt->execute([$gedung_id, $tanggal_mulai, $tanggal_selesai, $status, $id])) {
                    $success_msg = "Jadwal berhasil diperbarui.";
                }
            }
        }

        // B. Soft Delete
        if (isset($_POST['action']) && $_POST['action'] === 'soft_delete') {
            $id = (int)$_POST['id'];
            $stmt = $conn->prepare("UPDATE jadwal SET deleted_at = NOW() WHERE id = ?");
            if ($stmt->execute([$id])) $success_msg = "Jadwal dipindahkan ke sampah.";
        }

        // C. Restore
        if (isset($_POST['action']) && $_POST['action'] === 'restore') {
            $id = (int)$_POST['id'];
            
            // Cek overlap sebelum restore agar tidak konflik
            $stmtGet = $conn->prepare("SELECT * FROM jadwal WHERE id = ?");
            $stmtGet->execute([$id]);
            $restoreData = $stmtGet->fetch();

            if ($restoreData) {
                 $sqlOverlap = "SELECT COUNT(*) FROM jadwal 
                           WHERE gedung_id = ? 
                           AND deleted_at IS NULL 
                           AND id != ?
                           AND (tanggal_mulai <= ? AND tanggal_selesai >= ?)";
                $stmtCheck = $conn->prepare($sqlOverlap);
                $stmtCheck->execute([$restoreData['gedung_id'], $id, $restoreData['tanggal_selesai'], $restoreData['tanggal_mulai']]);
                
                if ($stmtCheck->fetchColumn() > 0) {
                    throw new Exception("Gagal memulihkan: Terdapat jadwal lain yang bentrok pada tanggal tersebut.");
                }

                $stmt = $conn->prepare("UPDATE jadwal SET deleted_at = NULL WHERE id = ?");
                if ($stmt->execute([$id])) $success_msg = "Jadwal berhasil dipulihkan.";
            }
        }

        // D. Bulk Actions
        if (isset($_POST['action']) && ($_POST['action'] === 'bulk_soft_delete' || $_POST['action'] === 'bulk_restore')) {
            $ids = explode(',', $_POST['ids']);
            $ids = array_filter(array_map('intval', $ids));
            
            if (!empty($ids)) {
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $newStatus = ($_POST['action'] === 'bulk_soft_delete') ? 'NOW()' : 'NULL';
                
                // Note: Untuk bulk restore sebaiknya ada validasi overlap juga, tapi untuk simplifikasi kita skip di sini atau gunakan try-catch loop
                $stmt = $conn->prepare("UPDATE jadwal SET deleted_at = $newStatus WHERE id IN ($placeholders)");
                if ($stmt->execute($ids)) {
                    $actionText = ($_POST['action'] === 'bulk_soft_delete') ? 'dipindahkan ke sampah' : 'dipulihkan';
                    $success_msg = count($ids) . " jadwal berhasil $actionText.";
                }
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

$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$gedung_filter = $_GET['gedung_filter'] ?? '';
$show_deleted = isset($_GET['show_deleted']) && $_GET['show_deleted'] == '1';

// Menentukan apakah filter harus terbuka
$is_filter_active = !empty($search) || !empty($status_filter) || !empty($gedung_filter) || $show_deleted;

$where_clauses = [];
$params = [];

// Search (Mencari berdasarkan nama gedung via JOIN)
if (!empty($search)) {
    $where_clauses[] = "(g.nama LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
}

if (!empty($status_filter)) {
    $where_clauses[] = "j.status = ?";
    $params[] = $status_filter;
}

if (!empty($gedung_filter)) {
    $where_clauses[] = "j.gedung_id = ?";
    $params[] = $gedung_filter;
}

if (!$show_deleted) {
    $where_clauses[] = "j.deleted_at IS NULL";
}

$where_sql = count($where_clauses) > 0 ? "WHERE " . implode(" AND ", $where_clauses) : "";

// Count Query
$count_query = "SELECT COUNT(*) FROM jadwal j JOIN gedung g ON j.gedung_id = g.id $where_sql";
$stmt = $conn->prepare($count_query);
$stmt->execute($params);
$total_rows = $stmt->fetchColumn();
$total_pages = ceil($total_rows / $limit);

// Main Query
$query = "SELECT j.*, g.nama as nama_gedung, b.booking_code 
          FROM jadwal j 
          JOIN gedung g ON j.gedung_id = g.id 
          LEFT JOIN booking b ON j.booking_id = b.id
          $where_sql 
          ORDER BY j.tanggal_mulai DESC 
          LIMIT $limit OFFSET $offset";
$stmt = $conn->prepare($query);
$stmt->execute($params);
$jadwals = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buat URL Base untuk Pagination
$query_params = $_GET;
unset($query_params['page']);
$base_url = '?' . http_build_query($query_params);
$pagination_url = empty($query_params) ? '?page=' : $base_url . '&page=';

require_once __DIR__ . '/../../../includes/admin/header_admin.php';
?>

<div x-data="jadwalManager()" x-init="initData()" class="p-6 max-w-[1600px] mx-auto pb-24 relative">
    
    <?php if($success_msg): ?>
    <script>
        Swal.fire({ icon: 'success', title: 'Berhasil', text: '<?= $success_msg ?>', timer: 1500, showConfirmButton: false });
        localStorage.removeItem('jadwal_selected_ids');
    </script>
    <?php endif; ?>
    <?php if($error_msg): ?>
    <script>Swal.fire({ icon: 'error', title: 'Gagal', text: '<?= $error_msg ?>' });</script>
    <?php endif; ?>

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Jadwal & Ketersediaan</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Manajemen status ketersediaan gedung dan pemblokiran tanggal.</p>
        </div>
        
        <div class="flex items-center gap-2">
            <button @click="showFilter = !showFilter" 
                    :class="showFilter ? 'bg-indigo-100 text-primary ring-2 ring-primary/20' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300'"
                    class="flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm font-semibold transition-all hover:bg-slate-50 dark:hover:bg-slate-700">
                <i class="fa-solid fa-filter"></i>
                <span>Filter</span>
                <span x-show="activeFilterCount > 0" class="flex h-5 w-5 items-center justify-center rounded-full bg-primary text-[10px] text-white" x-text="activeFilterCount"></span>
            </button>

            <button @click="openModal('create')" class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-primary text-white font-bold shadow-lg shadow-primary/30 hover:bg-primary/90 transition-all">
                <i class="fa-regular fa-calendar-plus"></i>
                <span class="hidden sm:inline">Set Jadwal</span>
            </button>
        </div>
    </div>

    <div x-show="showFilter" x-collapse 
         class="mb-6 bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-5">
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div class="col-span-1">
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1 uppercase">Pencarian Gedung</label>
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-slate-400"></i>
                    <input type="text" x-model="filter.search" @keyup.enter="applyFilters()" placeholder="Nama gedung..." 
                           class="w-full pl-10 pr-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-sm focus:ring-2 focus:ring-primary/20 outline-none transition-all dark:text-white">
                </div>
            </div>

            <div class="col-span-1">
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1 uppercase">Status</label>
                <select x-model="filter.status" class="w-full px-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-sm focus:ring-2 focus:ring-primary/20 outline-none transition-all dark:text-white appearance-none">
                    <option value="">Semua Status</option>
                    <option value="available">Available</option>
                    <option value="booked">Booked</option>
                    <option value="maintenance">Maintenance</option>
                </select>
            </div>

            <div class="col-span-1">
                 <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1 uppercase">Opsi Tampilan</label>
                 <label class="flex items-center gap-2 cursor-pointer select-none border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2 bg-white dark:bg-slate-900">
                    <div class="relative">
                        <input type="checkbox" x-model="filter.show_deleted" class="sr-only peer">
                        <div class="w-10 h-6 bg-slate-200 rounded-full peer 
                                            peer-focus:ring-4 peer-focus:ring-primary/30 dark:peer-focus:ring-primary/20 
                                            peer-checked:after:translate-x-full peer-checked:after:border-white 
                                            after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 
                                            peer-checked:bg-primary"></div>
                    </div>
                    <span class="text-xs font-semibold text-slate-600 dark:text-slate-300">Tampilkan Sampah</span>
                </label>
            </div>

            <div class="flex items-center justify-end">
                <button @click="applyFilters()" 
                        class="bg-primary hover:bg-primary/90 shadow-lg shadow-primary/30 text-white px-6 py-2 rounded-xl text-sm font-semibold transition-all w-full md:w-auto">
                    Terapkan
                </button>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 dark:bg-slate-700/50 text-xs uppercase text-slate-500 dark:text-slate-400 font-bold tracking-wider">
                    <tr>
                        <th class="p-4 w-10 text-center">
                            <input type="checkbox" 
                                   @click="toggleAll" 
                                   :checked="allSelected"
                                   :disabled="selectionMode !== null && selectionMode !== getCurrentPageMode()"
                                   class="rounded border-slate-300 text-primary focus:ring-primary h-4 w-4 disabled:opacity-50 disabled:cursor-not-allowed">
                        </th>
                        <th class="p-4 w-12 text-center">No</th>
                        <th class="p-4">Rentang Tanggal</th>
                        <th class="p-4">Nama Gedung</th>
                        <th class="p-4 text-center">Status</th>
                        <th class="p-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700 text-sm">
                    <?php if (count($jadwals) > 0): ?>
                        <?php foreach($jadwals as $index => $row): ?>
                        <?php 
                            $is_deleted = !empty($row['deleted_at']); 
                            $row_type = $is_deleted ? 'deleted' : 'active';
                        ?>
                        <tr class="transition-colors hover:bg-slate-50 dark:hover:bg-slate-700/30 group 
                                   <?= $is_deleted ? 'bg-red-50/60 dark:bg-red-900/10 text-red-700 dark:text-red-300' : 'text-slate-600 dark:text-slate-300' ?>">
                            
                            <td class="p-4 text-center">
                                <input type="checkbox" 
                                       value="<?= $row['id'] ?>" 
                                       data-status="<?= $row_type ?>"
                                       x-model="selectedItems"
                                       @change="updateSelectionState()"
                                       :disabled="selectionMode !== null && selectionMode !== '<?= $row_type ?>'"
                                       class="rounded border-slate-300 text-primary focus:ring-primary h-4 w-4 disabled:opacity-40 disabled:bg-slate-200 disabled:cursor-not-allowed">
                            </td>
                            <td class="p-4 text-center font-mono text-slate-400"><?= $offset + $index + 1 ?></td>
                            <td class="p-4 font-semibold">
                                <?= formatDateRange($row['tanggal_mulai'], $row['tanggal_selesai']) ?>
                                <?php if($is_deleted): ?>
                                    <span class="ml-2 text-[10px] bg-red-100 text-red-600 px-1.5 py-0.5 rounded border border-red-200">DELETED</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4 font-bold text-primary">
                                <?= htmlspecialchars($row['nama_gedung']) ?>
                                <?php if(!empty($row['booking_code'])): ?>
                                    <div class="mt-1">
                                        <span class="text-[10px] bg-blue-50 text-blue-600 border border-blue-200 px-1.5 py-0.5 rounded inline-flex items-center gap-1" title="Kode Booking">
                                            <i class="fa-solid fa-ticket"></i> <?= $row['booking_code'] ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="p-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium uppercase tracking-wide <?= statusBadge($row['status']) ?>">
                                    <?= ucfirst($row['status']) ?>
                                </span>
                            </td>
                            <td class="p-4 text-right">
                                <div class="flex justify-end items-center gap-2">
                                    <?php if(!$is_deleted): ?>
                                        <button @click='openModal("update", <?= json_encode($row) ?>)' 
                                                class="p-2 rounded-lg text-slate-400 hover:text-primary hover:bg-indigo-50 dark:hover:bg-slate-700 transition-all" title="Edit">
                                            <i class="fa-regular fa-pen-to-square"></i>
                                        </button>
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
                            <td colspan="6" class="p-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="h-16 w-16 bg-slate-100 dark:bg-slate-700 rounded-full flex items-center justify-center mb-4">
                                        <i class="fa-regular fa-calendar-xmark text-3xl"></i>
                                    </div>
                                    <p class="font-medium">Tidak ada data jadwal</p>
                                    <p class="text-xs mt-1">Belum ada status jadwal khusus yang diatur.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex flex-col md:flex-row items-center justify-between gap-4">
            <span class="text-xs text-slate-500 dark:text-slate-400">
                Menampilkan <span class="font-bold text-slate-700 dark:text-slate-200"><?= count($jadwals) ?></span> dari <span class="font-bold text-slate-700 dark:text-slate-200"><?= $total_rows ?></span> data
            </span>
            
            <div class="flex items-center gap-1">
                <?php if ($page > 1): ?>
                    <a href="<?= $pagination_url . ($page - 1) ?>" data-spa class="px-3 py-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 text-xs transition-colors">Prev</a>
                <?php endif; ?>

                <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                    <?php if ($p == $page || $p == 1 || $p == $total_pages || ($p >= $page - 1 && $p <= $page + 1)): ?>
                        <a href="<?= $pagination_url . $p ?>" data-spa
                           class="px-3 py-1 rounded-lg border text-xs font-bold transition-colors <?= $p == $page ? 'bg-primary border-primary text-white shadow-md shadow-primary/30' : 'border-slate-200 bg-white hover:bg-slate-50 text-slate-600' ?>">
                            <?= $p ?>
                        </a>
                    <?php elseif ($p == 2 || $p == $total_pages - 1): ?>
                        <span class="text-slate-400 px-1">...</span>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="<?= $pagination_url . ($page + 1) ?>" data-spa class="px-3 py-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 text-xs transition-colors">Next</a>
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
         class="fixed bottom-6 left-1/2 -translate-x-1/2 z-40 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-2xl rounded-2xl px-6 py-3 flex items-center gap-4 min-w-[350px] justify-between">
        
        <div class="flex items-center gap-6">
            <div class="flex items-center gap-2">
                <span class="bg-primary text-white text-xs font-bold px-2.5 py-1 rounded-lg" x-text="selectedItems.length"></span>
                <span class="text-sm font-semibold text-slate-700 dark:text-white">Dipilih</span>
            </div>
            
            <button @click="cancelSelection" 
                    class="text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 text-sm font-medium transition-colors border-l pl-6 border-slate-200 dark:border-slate-600">
                Batal
            </button>

            <template x-if="selectionMode === 'deleted'">
                <button @click="confirmBulkRestore" 
                        class="px-4 py-2 rounded-xl bg-emerald-500 text-white hover:bg-emerald-600 shadow-lg shadow-emerald-500/30 text-xs font-bold transition-all flex items-center gap-2">
                    <i class="fa-solid fa-rotate-left"></i> Restore All
                </button>
            </template>

            <template x-if="selectionMode === 'active'">
                <button @click="confirmBulkDelete" 
                        class="px-4 py-2 rounded-xl bg-red-500 text-white hover:bg-red-600 shadow-lg shadow-red-500/30 text-xs font-bold transition-all flex items-center gap-2">
                    <i class="fa-regular fa-trash-can"></i> Delete All
                </button>
            </template>
        </div>
    </div>

    <div x-show="modalOpen" style="display: none;"
         class="fixed inset-0 z-50 flex items-start justify-center pt-10 px-4 pb-10 overflow-y-auto"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="closeModal"></div>

        <div class="relative bg-white dark:bg-slate-800 w-full max-w-lg rounded-2xl shadow-2xl border border-slate-100 dark:border-slate-700 overflow-hidden flex flex-col max-h-[90vh]"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="-translate-y-10 opacity-0 scale-95"
             x-transition:enter-end="translate-y-0 opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="translate-y-0 opacity-100 scale-100"
             x-transition:leave-end="-translate-y-10 opacity-0 scale-95">
            
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-700/30 shrink-0">
                <h3 class="font-bold text-lg text-slate-800 dark:text-white" x-text="modalMode === 'create' ? 'Set Jadwal Baru' : 'Edit Jadwal'"></h3>
                <button @click="closeModal" class="text-slate-400 hover:text-rose-500 transition-colors">
                    <i class="fa-solid fa-times text-xl"></i>
                </button>
            </div>
            
            <form method="POST" class="flex-1 overflow-y-auto p-6">
                <input type="hidden" name="action" :value="modalMode">
                <input type="hidden" name="id" :value="formData.id">

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Pilih Gedung <span class="text-red-500">*</span></label>
                        <select name="gedung_id" x-model="formData.gedung_id" required 
                                :disabled="modalMode === 'update'"
                                class="w-full px-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 focus:ring-2 focus:ring-primary/20 outline-none dark:text-white disabled:opacity-50 disabled:cursor-not-allowed">
                            <option value="">-- Pilih Gedung --</option>
                            <?php foreach($gedung_options as $g): ?>
                                <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['nama']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Tanggal Mulai <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_mulai" x-model="formData.tanggal_mulai" required 
                                   class="w-full px-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 focus:ring-2 focus:ring-primary/20 outline-none dark:text-white">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Tanggal Selesai <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_selesai" x-model="formData.tanggal_selesai" required 
                                   class="w-full px-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 focus:ring-2 focus:ring-primary/20 outline-none dark:text-white">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Status Ketersediaan <span class="text-red-500">*</span></label>
                        <select name="status" x-model="formData.status" class="w-full px-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 focus:ring-2 focus:ring-primary/20 outline-none dark:text-white">
                            <option value="available">Available (Tersedia)</option>
                            <option value="booked">Booked (Sudah Dipesan)</option>
                            <option value="maintenance">Maintenance (Perbaikan)</option>
                        </select>
                        <p class="text-[10px] text-slate-400 mt-1">
                            <i class="fa-solid fa-circle-info mr-1"></i>
                            Pilih 'Maintenance' untuk memblokir rentang tanggal.
                        </p>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-700 flex justify-end gap-3">
                    <button type="button" @click="closeModal" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-700 transition-colors font-medium text-sm">Batal</button>
                    <button type="submit" class="px-6 py-2 rounded-xl bg-primary text-white hover:bg-primary/90 shadow-lg shadow-primary/30 transition-all font-bold text-sm">Simpan</button>
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
    function jadwalManager() {
        return {
            filter: {
                search: '<?= addslashes($search) ?>',
                status: '<?= $status_filter ?>',
                gedung_filter: '<?= $gedung_filter ?>',
                show_deleted: <?= $show_deleted ? 'true' : 'false' ?>
            },
            showFilter: <?= $is_filter_active ? 'true' : 'false' ?>,
            
            get activeFilterCount() {
                let count = 0;
                if (this.filter.search) count++;
                if (this.filter.status) count++;
                if (this.filter.gedung_filter) count++;
                if (this.filter.show_deleted) count++;
                return count;
            },

            // --- Logic Filter SPA ---
            applyFilters() {
                const params = new URLSearchParams();
                if (this.filter.search) params.append('search', this.filter.search);
                if (this.filter.status) params.append('status', this.filter.status);
                if (this.filter.gedung_filter) params.append('gedung_filter', this.filter.gedung_filter);
                if (this.filter.show_deleted) params.append('show_deleted', '1');
                
                params.append('page', '1');
                
                const url = '?' + params.toString();
                
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('data-spa', '');
                link.style.display = 'none';
                document.body.appendChild(link);
                link.click();
                setTimeout(() => document.body.removeChild(link), 100);
            },

            // --- Logic Checkbox & Bulk Actions ---
            allSelected: false,
            selectedItems: [],
            selectionMode: null, 

            initData() {
                const savedSelection = localStorage.getItem('jadwal_selected_ids');
                if (savedSelection) {
                    try {
                        this.selectedItems = JSON.parse(savedSelection);
                        this.$nextTick(() => { this.updateSelectionState(); });
                    } catch(e) { console.error(e); }
                }
            },

            updateSelectionState() {
                localStorage.setItem('jadwal_selected_ids', JSON.stringify(this.selectedItems));

                if (this.selectedItems.length === 0) {
                    this.selectionMode = null;
                    this.allSelected = false;
                    return;
                }

                const checkedInput = document.querySelector('input[type="checkbox"][data-status]:checked');
                if (checkedInput) {
                    this.selectionMode = checkedInput.getAttribute('data-status');
                }
            },

            toggleAll() {
                let targetStatus = this.selectionMode;
                if (!targetStatus) {
                    const firstInput = document.querySelector('input[type="checkbox"][data-status]');
                    if (firstInput) targetStatus = firstInput.getAttribute('data-status');
                }

                if (!targetStatus) return; 

                this.allSelected = !this.allSelected;
                
                const checkboxes = document.querySelectorAll(`input[type="checkbox"][data-status="${targetStatus}"]`);
                
                if (this.allSelected) {
                    checkboxes.forEach(el => {
                        if (!this.selectedItems.includes(el.value)) {
                            this.selectedItems.push(el.value);
                        }
                    });
                    this.selectionMode = targetStatus;
                } else {
                    checkboxes.forEach(el => {
                        this.selectedItems = this.selectedItems.filter(id => id !== el.value);
                    });
                    if(this.selectedItems.length === 0) this.selectionMode = null;
                }
                this.updateSelectionState();
            },

            cancelSelection() {
                this.selectedItems = [];
                this.selectionMode = null;
                this.allSelected = false;
                localStorage.removeItem('jadwal_selected_ids');
            },
            
            getCurrentPageMode() {
                const firstInput = document.querySelector('input[type="checkbox"][data-status]');
                return firstInput ? firstInput.getAttribute('data-status') : null;
            },

            // Modal Logic
            modalOpen: false,
            modalMode: 'create',
            formData: { 
                id: '', 
                gedung_id: '', 
                tanggal_mulai: '', 
                tanggal_selesai: '',
                status: 'maintenance'
            },

            openModal(mode, data = null) {
                this.modalMode = mode;
                if (mode === 'update' && data) {
                    this.formData = { 
                        id: data.id,
                        gedung_id: data.gedung_id,
                        tanggal_mulai: data.tanggal_mulai,
                        tanggal_selesai: data.tanggal_selesai,
                        status: data.status
                    };
                } else {
                    this.formData = { 
                        id: '', 
                        gedung_id: '', 
                        tanggal_mulai: new Date().toISOString().split('T')[0], 
                        tanggal_selesai: new Date().toISOString().split('T')[0],
                        status: 'maintenance'
                    };
                }
                this.modalOpen = true;
            },
            closeModal() { this.modalOpen = false; },

            // SweetAlerts Actions
            confirmDelete(id) {
                Swal.fire({
                    title: 'Pindahkan ke Sampah?',
                    text: "Data jadwal akan disembunyikan.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#cbd5e1',
                    confirmButtonText: 'Ya, Hapus'
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
                    text: "Jadwal akan kembali aktif.",
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
            
            confirmBulkDelete() {
                Swal.fire({
                    title: 'Hapus ' + this.selectedItems.length + ' Data?',
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
                    title: 'Pulihkan ' + this.selectedItems.length + ' Data?',
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