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

function getActionColor($action) {
    $action = strtolower($action);
    if (strpos($action, 'delete') !== false || strpos($action, 'hapus') !== false) return 'text-rose-600 bg-rose-50 border-rose-100';
    if (strpos($action, 'update') !== false || strpos($action, 'edit') !== false || strpos($action, 'ubah') !== false) return 'text-amber-600 bg-amber-50 border-amber-100';
    if (strpos($action, 'create') !== false || strpos($action, 'tambah') !== false || strpos($action, 'simpan') !== false) return 'text-emerald-600 bg-emerald-50 border-emerald-100';
    if (strpos($action, 'login') !== false || strpos($action, 'masuk') !== false) return 'text-blue-600 bg-blue-50 border-blue-100';
    if (strpos($action, 'logout') !== false || strpos($action, 'keluar') !== false) return 'text-slate-600 bg-slate-50 border-slate-100';
    return 'text-indigo-600 bg-indigo-50 border-indigo-100';
}

// Ambil list Module unik untuk Filter
$stmtModule = $conn->query("SELECT DISTINCT module FROM activity_logs ORDER BY module ASC");
$modules = $stmtModule->fetchAll(PDO::FETCH_COLUMN);

// --- 2. Logic Handler (POST Requests) ---
$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // A. Delete Single (Hard Delete)
        if (isset($_POST['action']) && $_POST['action'] === 'delete') {
            $id = (int)$_POST['id'];
            $stmt = $conn->prepare("DELETE FROM activity_logs WHERE id = ?");
            if ($stmt->execute([$id])) $success_msg = "Log aktivitas berhasil dihapus permanen.";
        }

        // B. Bulk Delete
        if (isset($_POST['action']) && $_POST['action'] === 'bulk_delete') {
            $ids = explode(',', $_POST['ids']);
            $ids = array_filter(array_map('intval', $ids));
            if (!empty($ids)) {
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $stmt = $conn->prepare("DELETE FROM activity_logs WHERE id IN ($placeholders)");
                if ($stmt->execute($ids)) $success_msg = count($ids) . " log aktivitas berhasil dihapus permanen.";
            }
        }

    } catch (Exception $e) {
        $error_msg = "Error: " . $e->getMessage();
    }
}

// --- 3. Filter & Pagination Logic ---
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 15; 
$offset = ($page - 1) * $limit;

$search = $_GET['search'] ?? '';
$module_filter = $_GET['module'] ?? '';

// Menentukan apakah filter harus terbuka
$is_filter_active = !empty($search) || !empty($module_filter);

$where_clauses = [];
$params = [];

// Search Logic
if (!empty($search)) {
    $where_clauses[] = "(l.description LIKE ? OR l.ip_address LIKE ? OR u.username LIKE ? OR u.nama_lengkap LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param; $params[] = $search_param; $params[] = $search_param; $params[] = $search_param;
}

// Filter Module
if (!empty($module_filter)) {
    $where_clauses[] = "l.module = ?";
    $params[] = $module_filter;
}

$where_sql = count($where_clauses) > 0 ? "WHERE " . implode(" AND ", $where_clauses) : "";

// Count Query
$count_query = "SELECT COUNT(*) FROM activity_logs l LEFT JOIN users u ON l.user_id = u.id $where_sql";
$stmt = $conn->prepare($count_query);
$stmt->execute($params);
$total_rows = $stmt->fetchColumn();
$total_pages = ceil($total_rows / $limit);

// Main Query (Termasuk old_value & new_value)
$query = "SELECT l.*, u.username, u.nama_lengkap, u.role 
          FROM activity_logs l 
          LEFT JOIN users u ON l.user_id = u.id 
          $where_sql 
          ORDER BY l.created_at DESC 
          LIMIT $limit OFFSET $offset";
$stmt = $conn->prepare($query);
$stmt->execute($params);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buat URL Base untuk Pagination
$query_params = $_GET;
unset($query_params['page']);
$base_url = '?' . http_build_query($query_params);
$pagination_url = empty($query_params) ? '?page=' : $base_url . '&page=';

require_once __DIR__ . '/../../../includes/admin/header_admin.php';
?>

<div x-data="logManager()" x-init="initData()" class="p-6 max-w-[1600px] mx-auto pb-24 relative">
    
    <?php if($success_msg): ?>
    <script>
        Swal.fire({ icon: 'success', title: 'Berhasil', text: '<?= $success_msg ?>', timer: 1500, showConfirmButton: false });
        localStorage.removeItem('logs_selected_ids');
    </script>
    <?php endif; ?>
    <?php if($error_msg): ?>
    <script>Swal.fire({ icon: 'error', title: 'Gagal', text: '<?= $error_msg ?>' });</script>
    <?php endif; ?>

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Activity Logs</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Riwayat aktivitas & audit perubahan data sistem.</p>
        </div>
        
        <div class="flex items-center gap-2">
            <button @click="showFilter = !showFilter" 
                    :class="showFilter ? 'bg-indigo-100 text-primary ring-2 ring-primary/20' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300'"
                    class="flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm font-semibold transition-all hover:bg-slate-50 dark:hover:bg-slate-700">
                <i class="fa-solid fa-filter"></i>
                <span>Filter</span>
                <span x-show="activeFilterCount > 0" class="flex h-5 w-5 items-center justify-center rounded-full bg-primary text-[10px] text-white" x-text="activeFilterCount"></span>
            </button>
            
            <a href="index.php" class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 font-semibold shadow-sm hover:bg-slate-50 transition-all">
                <i class="fa-solid fa-rotate-right"></i>
                <span class="hidden sm:inline">Refresh</span>
            </a>
        </div>
    </div>

    <div x-show="showFilter" x-collapse 
         class="mb-6 bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-5">
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div class="col-span-1">
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1 uppercase">Pencarian</label>
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-slate-400"></i>
                    <input type="text" x-model="filter.search" @keyup.enter="applyFilters()" placeholder="Deskripsi, User, IP..." 
                           class="w-full pl-10 pr-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-sm focus:ring-2 focus:ring-primary/20 outline-none transition-all dark:text-white">
                </div>
            </div>

            <div class="col-span-1">
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1 uppercase">Modul Sistem</label>
                <select x-model="filter.module" class="w-full px-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-sm focus:ring-2 focus:ring-primary/20 outline-none transition-all dark:text-white appearance-none">
                    <option value="">Semua Modul</option>
                    <?php foreach($modules as $mod): ?>
                        <option value="<?= htmlspecialchars($mod) ?>"><?= ucfirst($mod) ?></option>
                    <?php endforeach; ?>
                </select>
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
                                   class="rounded border-slate-300 text-primary focus:ring-primary h-4 w-4">
                        </th>
                        <th class="p-4 w-12 text-center">No</th>
                        <th class="p-4">Waktu</th>
                        <th class="p-4">User</th>
                        <th class="p-4">Aksi & Modul</th>
                        <th class="p-4">Deskripsi</th>
                        <th class="p-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700 text-sm">
                    <?php if (count($logs) > 0): ?>
                        <?php foreach($logs as $index => $row): ?>
                        <tr class="transition-colors hover:bg-slate-50 dark:hover:bg-slate-700/30 text-slate-600 dark:text-slate-300">
                            <td class="p-4 text-center">
                                <input type="checkbox" 
                                       value="<?= $row['id'] ?>" 
                                       x-model="selectedItems"
                                       @change="updateSelectionState()"
                                       class="rounded border-slate-300 text-primary focus:ring-primary h-4 w-4">
                            </td>
                            <td class="p-4 text-center font-mono text-slate-400"><?= $offset + $index + 1 ?></td>
                            
                            <td class="p-4 whitespace-nowrap">
                                <div class="font-semibold text-slate-700 dark:text-slate-200">
                                    <?= date('d M Y', strtotime($row['created_at'])) ?>
                                </div>
                                <div class="text-xs text-slate-400 font-mono">
                                    <?= date('H:i:s', strtotime($row['created_at'])) ?>
                                </div>
                            </td>

                            <td class="p-4">
                                <?php if($row['username']): ?>
                                    <div class="flex items-center gap-2">
                                        <div class="h-8 w-8 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-[10px] font-bold uppercase text-slate-500 border border-slate-200 dark:border-slate-600">
                                            <?= substr($row['username'], 0, 2) ?>
                                        </div>
                                        <div>
                                            <div class="font-bold text-xs"><?= htmlspecialchars($row['username']) ?></div>
                                            <div class="text-[10px] uppercase text-slate-400"><?= htmlspecialchars($row['role'] ?? 'User') ?></div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <span class="text-xs italic text-slate-400">System / Guest</span>
                                <?php endif; ?>
                            </td>

                            <td class="p-4">
                                <div class="flex flex-col items-start gap-1">
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded border uppercase tracking-wider <?= getActionColor($row['action']) ?>">
                                        <?= htmlspecialchars($row['action']) ?>
                                    </span>
                                    <span class="text-[10px] font-mono text-slate-400">
                                        <?= htmlspecialchars($row['module']) ?>
                                        <?php if($row['record_id']): ?>
                                            <span class="text-slate-300">#<?= $row['record_id'] ?></span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </td>

                            <td class="p-4">
                                <p class="text-xs leading-relaxed max-w-[250px] line-clamp-2" title="<?= htmlspecialchars($row['description']) ?>">
                                    <?= htmlspecialchars($row['description']) ?>
                                </p>
                            </td>

                            <td class="p-4 text-center">
                                <div class="flex justify-center items-center gap-2">
                                    <?php if(!empty($row['old_value']) || !empty($row['new_value'])): ?>
                                    <button @click='openViewModal(<?= json_encode($row) ?>)'
                                            class="p-2 rounded-lg text-blue-500 bg-blue-50 hover:bg-blue-100 dark:hover:bg-slate-700 transition-all" title="Lihat Perubahan">
                                        <i class="fa-regular fa-eye"></i>
                                    </button>
                                    <?php else: ?>
                                    <button class="p-2 rounded-lg text-slate-300 cursor-default" title="Tidak ada detail">
                                        <i class="fa-regular fa-eye-slash"></i>
                                    </button>
                                    <?php endif; ?>

                                    <button @click="confirmDelete(<?= $row['id'] ?>)" 
                                            class="p-2 rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-slate-700 transition-all" title="Hapus Log">
                                        <i class="fa-regular fa-trash-can"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="p-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="h-16 w-16 bg-slate-100 dark:bg-slate-700 rounded-full flex items-center justify-center mb-4">
                                        <i class="fa-solid fa-clipboard-list text-3xl"></i>
                                    </div>
                                    <p class="font-medium">Tidak ada log aktivitas</p>
                                    <p class="text-xs mt-1 text-slate-400">Belum ada aktivitas yang terekam.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex flex-col md:flex-row items-center justify-between gap-4">
            <span class="text-xs text-slate-500 dark:text-slate-400">
                Menampilkan <span class="font-bold text-slate-700 dark:text-slate-200"><?= count($logs) ?></span> dari <span class="font-bold text-slate-700 dark:text-slate-200"><?= $total_rows ?></span> data
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

            <button @click="confirmBulkDelete" 
                    class="px-4 py-2 rounded-xl bg-red-500 text-white hover:bg-red-600 shadow-lg shadow-red-500/30 text-xs font-bold transition-all flex items-center gap-2">
                <i class="fa-regular fa-trash-can"></i> Hapus Permanen
            </button>
        </div>
    </div>

    <div x-show="viewModalOpen" style="display: none;"
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="closeViewModal"></div>

        <div class="relative bg-white dark:bg-slate-800 w-full max-w-4xl rounded-2xl shadow-2xl border border-slate-100 dark:border-slate-700 overflow-hidden flex flex-col max-h-[90vh]"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="scale-95 opacity-0"
             x-transition:enter-end="scale-100 opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="scale-100 opacity-100"
             x-transition:leave-end="scale-95 opacity-0">
            
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-700/30 shrink-0">
                <div>
                    <h3 class="font-bold text-lg text-slate-800 dark:text-white">Detail Log Aktivitas</h3>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="text-xs px-2 py-0.5 rounded font-bold border uppercase" :class="getActionColor(selectedLog.action)" x-text="selectedLog.action"></span>
                        <span class="text-xs text-slate-500" x-text="selectedLog.created_at"></span>
                    </div>
                </div>
                <button @click="closeViewModal" class="text-slate-400 hover:text-rose-500 transition-colors">
                    <i class="fa-solid fa-times text-xl"></i>
                </button>
            </div>
            
            <div class="p-6 overflow-y-auto bg-slate-50/50 dark:bg-slate-900/50 flex-1">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div class="bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm">
                        <div class="text-xs uppercase font-bold text-slate-400 mb-2">User Info</div>
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold">
                                <i class="fa-regular fa-user"></i>
                            </div>
                            <div>
                                <div class="font-bold text-slate-700 dark:text-slate-200" x-text="selectedLog.username || 'System'"></div>
                                <div class="text-xs text-slate-500">
                                    <span x-text="selectedLog.role"></span> • <span x-text="selectedLog.ip_address"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm">
                        <div class="text-xs uppercase font-bold text-slate-400 mb-2">Context Info</div>
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-full bg-amber-50 flex items-center justify-center text-amber-600 font-bold">
                                <i class="fa-solid fa-database"></i>
                            </div>
                            <div>
                                <div class="font-bold text-slate-700 dark:text-slate-200" x-text="selectedLog.module"></div>
                                <div class="text-xs text-slate-500">Record ID: <span x-text="selectedLog.record_id || '-'"></span></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-6">
                    <div class="text-xs uppercase font-bold text-slate-400 mb-2">Deskripsi Aktivitas</div>
                    <p class="text-sm text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 p-3 rounded-lg border border-slate-200 dark:border-slate-700" x-text="selectedLog.description"></p>
                </div>

                <div x-show="selectedLog.old_value || selectedLog.new_value" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div x-show="selectedLog.old_value">
                        <div class="text-xs uppercase font-bold text-red-500 mb-2 flex items-center gap-2">
                            <i class="fa-solid fa-circle-minus"></i> Data Lama (Sebelum)
                        </div>
                        <div class="bg-red-50 dark:bg-red-900/10 border border-red-100 dark:border-red-900/30 rounded-xl p-4 overflow-x-auto max-h-[300px]">
                            <pre class="text-xs font-mono text-slate-600 dark:text-slate-300 whitespace-pre-wrap" x-text="formatJson(selectedLog.old_value)"></pre>
                        </div>
                    </div>
                    
                    <div x-show="selectedLog.new_value">
                        <div class="text-xs uppercase font-bold text-emerald-500 mb-2 flex items-center gap-2">
                            <i class="fa-solid fa-circle-plus"></i> Data Baru (Sesudah)
                        </div>
                        <div class="bg-emerald-50 dark:bg-emerald-900/10 border border-emerald-100 dark:border-emerald-900/30 rounded-xl p-4 overflow-x-auto max-h-[300px]">
                            <pre class="text-xs font-mono text-slate-600 dark:text-slate-300 whitespace-pre-wrap" x-text="formatJson(selectedLog.new_value)"></pre>
                        </div>
                    </div>
                </div>
                
                <div x-show="!selectedLog.old_value && !selectedLog.new_value" class="text-center py-8 text-slate-400 italic text-sm border-t border-slate-100 dark:border-slate-700 mt-4">
                    Tidak ada detail snapshot data yang tersimpan untuk aktivitas ini.
                </div>

            </div>
            
            <div class="p-4 border-t border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-800 flex justify-end">
                <button @click="closeViewModal" class="px-6 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm transition-all">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <form id="deleteForm" method="POST" class="hidden">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id" id="deleteId">
    </form>
    
    <form id="bulkDeleteForm" method="POST" class="hidden">
        <input type="hidden" name="action" value="bulk_delete">
        <input type="hidden" name="ids" id="bulkDeleteIds">
    </form>
</div>

<script>
    function logManager() {
        return {
            filter: {
                search: '<?= addslashes($search) ?>',
                module: '<?= $module_filter ?>',
            },
            showFilter: <?= $is_filter_active ? 'true' : 'false' ?>,
            
            get activeFilterCount() {
                let count = 0;
                if (this.filter.search) count++;
                if (this.filter.module) count++;
                return count;
            },

            // --- Logic Filter SPA ---
            applyFilters() {
                const params = new URLSearchParams();
                if (this.filter.search) params.append('search', this.filter.search);
                if (this.filter.module) params.append('module', this.filter.module);
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

            initData() {
                const savedSelection = localStorage.getItem('logs_selected_ids');
                if (savedSelection) {
                    try {
                        this.selectedItems = JSON.parse(savedSelection);
                        this.$nextTick(() => { this.updateSelectionState(); });
                    } catch(e) { console.error(e); }
                }
            },

            updateSelectionState() {
                localStorage.setItem('logs_selected_ids', JSON.stringify(this.selectedItems));
                if (this.selectedItems.length === 0) this.allSelected = false;
            },

            toggleAll() {
                this.allSelected = !this.allSelected;
                const checkboxes = document.querySelectorAll('input[type="checkbox"][value]');
                if (this.allSelected) {
                    checkboxes.forEach(el => {
                        if (!this.selectedItems.includes(el.value)) this.selectedItems.push(el.value);
                    });
                } else {
                    checkboxes.forEach(el => {
                        this.selectedItems = this.selectedItems.filter(id => id !== el.value);
                    });
                }
                this.updateSelectionState();
            },

            cancelSelection() {
                this.selectedItems = [];
                this.allSelected = false;
                localStorage.removeItem('logs_selected_ids');
            },

            // --- Detail View Logic (New Feature) ---
            viewModalOpen: false,
            selectedLog: {},

            openViewModal(log) {
                this.selectedLog = log;
                this.viewModalOpen = true;
            },
            
            closeViewModal() {
                this.viewModalOpen = false;
                this.selectedLog = {};
            },

            formatJson(jsonString) {
                if (!jsonString) return '';
                try {
                    // Coba parse kalau dia string
                    if (typeof jsonString === 'string') {
                        const obj = JSON.parse(jsonString);
                        return JSON.stringify(obj, null, 2); // Pretty print JSON
                    }
                    // Kalau sudah object (terkadang PHP fetch assoc sudah convert)
                    return JSON.stringify(jsonString, null, 2);
                } catch (e) {
                    return jsonString; // Kalau gagal parse, return as is
                }
            },
            
            getActionColor(action) {
                if (!action) return 'bg-gray-100 text-gray-600 border-gray-200';
                action = action.toLowerCase();
                if (action.includes('delete') || action.includes('hapus')) return 'bg-rose-50 text-rose-600 border-rose-200';
                if (action.includes('update') || action.includes('edit')) return 'bg-amber-50 text-amber-600 border-amber-200';
                if (action.includes('create') || action.includes('tambah')) return 'bg-emerald-50 text-emerald-600 border-emerald-200';
                return 'bg-blue-50 text-blue-600 border-blue-200';
            },

            // --- SweetAlerts ---
            confirmDelete(id) {
                Swal.fire({
                    title: 'Hapus Log Ini?',
                    text: "Data log akan dihapus permanen.",
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
            
            confirmBulkDelete() {
                Swal.fire({
                    title: 'Hapus ' + this.selectedItems.length + ' Log?',
                    text: "Log yang dipilih akan dihapus permanen.",
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
            }
        }
    }
</script>

<?php
require_once __DIR__ . '/../../../includes/admin/footer_admin.php';
?>