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

function getTypeColor($type) {
    $colors = [
        'gedung'    => 'bg-blue-100 text-blue-600 border-blue-200',
        'pelanggan' => 'bg-purple-100 text-purple-600 border-purple-200',
        'promo'     => 'bg-pink-100 text-pink-600 border-pink-200',
        'jadwal'    => 'bg-orange-100 text-orange-600 border-orange-200',
    ];
    return $colors[$type] ?? 'bg-gray-100 text-gray-600';
}

function getTypeLabel($type) {
    return ucfirst($type);
}

// --- 2. Logic Handler (POST Requests) ---
$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        
        // Helper untuk eksekusi query berdasarkan tipe
        $executeAction = function($action, $type, $id) use ($conn) {
            $tableMap = [
                'gedung'    => 'gedung',
                'pelanggan' => 'users',
                'promo'     => 'promos',
                'jadwal'    => 'jadwal'
            ];

            if (!isset($tableMap[$type])) return false;
            $table = $tableMap[$type];

            if ($action === 'restore') {
                $sql = "UPDATE $table SET deleted_at = NULL WHERE id = ?";
            } elseif ($action === 'permanent_delete') {
                $sql = "DELETE FROM $table WHERE id = ?";
            } else {
                return false;
            }

            $stmt = $conn->prepare($sql);
            return $stmt->execute([$id]);
        };

        // A. Single Action
        if (isset($_POST['action']) && ($_POST['action'] === 'restore' || $_POST['action'] === 'permanent_delete')) {
            $compositeId = $_POST['id']; // Format: "type|id"
            list($type, $realId) = explode('|', $compositeId);
            
            if ($executeAction($_POST['action'], $type, $realId)) {
                $success_msg = ($_POST['action'] === 'restore') ? "Data berhasil dipulihkan." : "Data berhasil dihapus permanen.";
            } else {
                throw new Exception("Gagal memproses data.");
            }
        }

        // B. Bulk Actions
        if (isset($_POST['action']) && ($_POST['action'] === 'bulk_restore' || $_POST['action'] === 'bulk_permanent_delete')) {
            $ids = explode(',', $_POST['ids']); // List of "type|id"
            $count = 0;
            $mode = ($_POST['action'] === 'bulk_restore') ? 'restore' : 'permanent_delete';

            foreach ($ids as $compositeId) {
                if (strpos($compositeId, '|') !== false) {
                    list($type, $realId) = explode('|', $compositeId);
                    if ($executeAction($mode, $type, $realId)) {
                        $count++;
                    }
                }
            }
            
            if ($count > 0) {
                $success_msg = $count . " data berhasil " . ($mode === 'restore' ? "dipulihkan." : "dihapus permanen.");
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
$type_filter = $_GET['type'] ?? '';

// Menentukan apakah filter harus terbuka
$is_filter_active = !empty($search) || !empty($type_filter);

// Membangun Query UNION yang kompleks untuk mengambil data dari 4 tabel
// PERBAIKAN: Menambahkan 'COLLATE utf8mb4_unicode_ci' untuk menyamakan collation

$queries = [];
$params = [];

// 1. Gedung
$queries[] = "SELECT id, 'gedung' as type, nama COLLATE utf8mb4_unicode_ci as label, deleted_at FROM gedung WHERE deleted_at IS NOT NULL";

// 2. Pelanggan (Users role penyewa)
$queries[] = "SELECT id, 'pelanggan' as type, nama_lengkap COLLATE utf8mb4_unicode_ci as label, deleted_at FROM users WHERE deleted_at IS NOT NULL AND role = 'penyewa'";

// 3. Promos
$queries[] = "SELECT id, 'promo' as type, nama_promo COLLATE utf8mb4_unicode_ci as label, deleted_at FROM promos WHERE deleted_at IS NOT NULL";

// 4. Jadwal (Join dengan Gedung untuk label)
$queries[] = "SELECT j.id, 'jadwal' as type, CONCAT(g.nama, ' (', j.tanggal_mulai, ')') COLLATE utf8mb4_unicode_ci as label, j.deleted_at 
              FROM jadwal j 
              JOIN gedung g ON j.gedung_id = g.id 
              WHERE j.deleted_at IS NOT NULL";

// Gabungkan Query
$unionSql = implode(" UNION ALL ", $queries);

// Bungkus dalam Subquery untuk Filtering & Pagination global
$finalSql = "SELECT * FROM ($unionSql) as trash_table WHERE 1=1";

if (!empty($search)) {
    $finalSql .= " AND (label LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
}

if (!empty($type_filter)) {
    $finalSql .= " AND type = ?";
    $params[] = $type_filter;
}

// Count Total Rows (untuk pagination)
$stmtCount = $conn->prepare("SELECT COUNT(*) FROM ($finalSql) as counted_table");
$stmtCount->execute($params); 
$total_rows = $stmtCount->fetchColumn();
$total_pages = ceil($total_rows / $limit);

// Fetch Data
$finalSql .= " ORDER BY deleted_at DESC LIMIT $limit OFFSET $offset";
$stmt = $conn->prepare($finalSql);
$stmt->execute($params);
$trashItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buat URL Base untuk Pagination
$query_params = $_GET;
unset($query_params['page']);
$base_url = '?' . http_build_query($query_params);
$pagination_url = empty($query_params) ? '?page=' : $base_url . '&page=';

require_once __DIR__ . '/../../../includes/admin/header_admin.php';
?>

<div x-data="trashManager()" x-init="initData()" class="p-6 max-w-[1600px] mx-auto pb-24 relative">
    
    <?php if($success_msg): ?>
    <script>
        Swal.fire({ icon: 'success', title: 'Berhasil', text: '<?= $success_msg ?>', timer: 1500, showConfirmButton: false });
        localStorage.removeItem('trash_selected_ids');
    </script>
    <?php endif; ?>
    <?php if($error_msg): ?>
    <script>Swal.fire({ icon: 'error', title: 'Gagal', text: '<?= $error_msg ?>' });</script>
    <?php endif; ?>

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Tong Sampah (Recycle Bin)</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Pulihkan data yang terhapus atau hapus selamanya.</p>
        </div>
        
        <div class="flex items-center gap-2">
            <button @click="showFilter = !showFilter" 
                    :class="showFilter ? 'bg-indigo-100 text-primary ring-2 ring-primary/20' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300'"
                    class="flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm font-semibold transition-all hover:bg-slate-50 dark:hover:bg-slate-700">
                <i class="fa-solid fa-filter"></i>
                <span>Filter</span>
                <span x-show="activeFilterCount > 0" class="flex h-5 w-5 items-center justify-center rounded-full bg-primary text-[10px] text-white" x-text="activeFilterCount"></span>
            </button>
        </div>
    </div>

    <div x-show="showFilter" x-collapse 
         class="mb-6 bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-5">
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div class="col-span-1">
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1 uppercase">Pencarian</label>
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-slate-400"></i>
                    <input type="text" x-model="filter.search" @keyup.enter="applyFilters()" placeholder="Cari nama data..." 
                           class="w-full pl-10 pr-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-sm focus:ring-2 focus:ring-primary/20 outline-none transition-all dark:text-white">
                </div>
            </div>

            <div class="col-span-1">
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1 uppercase">Tipe Data</label>
                <select x-model="filter.type" class="w-full px-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-sm focus:ring-2 focus:ring-primary/20 outline-none transition-all dark:text-white appearance-none">
                    <option value="">Semua Tipe</option>
                    <option value="gedung">Gedung</option>
                    <option value="pelanggan">Pelanggan</option>
                    <option value="promo">Promo</option>
                    <option value="jadwal">Jadwal</option>
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
                        <th class="p-4">Tipe Data</th>
                        <th class="p-4">Data / Label</th>
                        <th class="p-4">Dihapus Pada</th>
                        <th class="p-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700 text-sm">
                    <?php if (count($trashItems) > 0): ?>
                        <?php foreach($trashItems as $index => $row): ?>
                        <tr class="transition-colors hover:bg-slate-50 dark:hover:bg-slate-700/30 text-slate-600 dark:text-slate-300">
                            <td class="p-4 text-center">
                                <input type="checkbox" 
                                       value="<?= $row['type'] . '|' . $row['id'] ?>" 
                                       x-model="selectedItems"
                                       @change="updateSelectionState()"
                                       class="rounded border-slate-300 text-primary focus:ring-primary h-4 w-4">
                            </td>
                            <td class="p-4 text-center font-mono text-slate-400"><?= $offset + $index + 1 ?></td>
                            
                            <td class="p-4">
                                <span class="px-2 py-1 rounded-md text-[10px] font-bold uppercase border <?= getTypeColor($row['type']) ?>">
                                    <?= getTypeLabel($row['type']) ?>
                                </span>
                            </td>

                            <td class="p-4 font-bold text-slate-700 dark:text-slate-200">
                                <?= htmlspecialchars($row['label']) ?>
                                <div class="text-[10px] text-slate-400 font-mono font-normal mt-0.5">ID: <?= $row['id'] ?></div>
                            </td>

                            <td class="p-4 text-slate-500">
                                <div class="flex items-center gap-2">
                                    <i class="fa-regular fa-clock text-slate-400"></i>
                                    <?= date('d M Y H:i', strtotime($row['deleted_at'])) ?>
                                </div>
                            </td>

                            <td class="p-4 text-center">
                                <div class="flex justify-center items-center gap-2">
                                    <button @click="confirmAction('restore', '<?= $row['type'] . '|' . $row['id'] ?>')" 
                                            class="p-2 rounded-lg text-emerald-500 bg-emerald-50 hover:bg-emerald-100 transition-all text-xs font-bold flex items-center gap-1" title="Pulihkan">
                                        <i class="fa-solid fa-rotate-left"></i> Pulihkan
                                    </button>

                                    <button @click="confirmAction('permanent_delete', '<?= $row['type'] . '|' . $row['id'] ?>')" 
                                            class="p-2 rounded-lg text-red-500 bg-red-50 hover:bg-red-100 transition-all text-xs font-bold flex items-center gap-1" title="Hapus Permanen">
                                        <i class="fa-solid fa-fire"></i> Musnahkan
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="p-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="h-16 w-16 bg-slate-100 dark:bg-slate-700 rounded-full flex items-center justify-center mb-4">
                                        <i class="fa-solid fa-recycle text-3xl"></i>
                                    </div>
                                    <p class="font-medium">Tong sampah kosong</p>
                                    <p class="text-xs mt-1">Tidak ada data yang dihapus.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex flex-col md:flex-row items-center justify-between gap-4">
            <span class="text-xs text-slate-500 dark:text-slate-400">
                Menampilkan <span class="font-bold text-slate-700 dark:text-slate-200"><?= count($trashItems) ?></span> dari <span class="font-bold text-slate-700 dark:text-slate-200"><?= $total_rows ?></span> data
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

            <button @click="confirmBulkAction('bulk_restore')" 
                    class="px-4 py-2 rounded-xl bg-emerald-500 text-white hover:bg-emerald-600 shadow-lg shadow-emerald-500/30 text-xs font-bold transition-all flex items-center gap-2">
                <i class="fa-solid fa-rotate-left"></i> Pulihkan Semua
            </button>

            <button @click="confirmBulkAction('bulk_permanent_delete')" 
                    class="px-4 py-2 rounded-xl bg-red-500 text-white hover:bg-red-600 shadow-lg shadow-red-500/30 text-xs font-bold transition-all flex items-center gap-2">
                <i class="fa-solid fa-fire"></i> Musnahkan
            </button>
        </div>
    </div>

    <form id="actionForm" method="POST" class="hidden">
        <input type="hidden" name="action" id="formAction">
        <input type="hidden" name="id" id="formId"> <input type="hidden" name="ids" id="formIds"> </form>
    
</div>

<script>
    function trashManager() {
        return {
            filter: {
                search: '<?= addslashes($search) ?>',
                type: '<?= $type_filter ?>',
            },
            showFilter: <?= $is_filter_active ? 'true' : 'false' ?>,
            
            get activeFilterCount() {
                let count = 0;
                if (this.filter.search) count++;
                if (this.filter.type) count++;
                return count;
            },

            // --- Logic Filter SPA ---
            applyFilters() {
                const params = new URLSearchParams();
                if (this.filter.search) params.append('search', this.filter.search);
                if (this.filter.type) params.append('type', this.filter.type);
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
                const savedSelection = localStorage.getItem('trash_selected_ids');
                if (savedSelection) {
                    try {
                        this.selectedItems = JSON.parse(savedSelection);
                        this.$nextTick(() => { this.updateSelectionState(); });
                    } catch(e) { console.error(e); }
                }
            },

            updateSelectionState() {
                localStorage.setItem('trash_selected_ids', JSON.stringify(this.selectedItems));
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
                localStorage.removeItem('trash_selected_ids');
            },

            // --- SweetAlerts Actions ---
            
            // Single Action
            confirmAction(type, compositeId) {
                let title, text, btnColor, btnText;
                
                if (type === 'restore') {
                    title = 'Pulihkan Data?';
                    text = 'Data akan dikembalikan ke modul asalnya.';
                    btnColor = '#10b981';
                    btnText = 'Ya, Pulihkan';
                } else {
                    title = 'Hapus Permanen?';
                    text = 'PERINGATAN: Data akan hilang selamanya dan tidak bisa dikembalikan!';
                    btnColor = '#ef4444';
                    btnText = 'Ya, Musnahkan';
                }

                Swal.fire({
                    title: title,
                    text: text,
                    icon: type === 'permanent_delete' ? 'warning' : 'question',
                    showCancelButton: true,
                    confirmButtonColor: btnColor,
                    cancelButtonColor: '#cbd5e1',
                    confirmButtonText: btnText
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('formAction').value = type;
                        document.getElementById('formId').value = compositeId;
                        document.getElementById('actionForm').submit();
                    }
                });
            },
            
            // Bulk Action
            confirmBulkAction(type) {
                let title, text, btnColor, btnText;
                
                if (type === 'bulk_restore') {
                    title = 'Pulihkan ' + this.selectedItems.length + ' Data?';
                    text = 'Semua data yang dipilih akan dikembalikan.';
                    btnColor = '#10b981';
                    btnText = 'Ya, Pulihkan Semua';
                } else {
                    title = 'Musnahkan ' + this.selectedItems.length + ' Data?';
                    text = 'PERINGATAN: Data yang dipilih akan hilang selamanya!';
                    btnColor = '#ef4444';
                    btnText = 'Ya, Musnahkan Semua';
                }

                Swal.fire({
                    title: title,
                    text: text,
                    icon: type === 'bulk_permanent_delete' ? 'warning' : 'question',
                    showCancelButton: true,
                    confirmButtonColor: btnColor,
                    cancelButtonColor: '#cbd5e1',
                    confirmButtonText: btnText
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('formAction').value = type;
                        document.getElementById('formIds').value = this.selectedItems.join(',');
                        document.getElementById('actionForm').submit();
                    }
                });
            }
        }
    }
</script>

<?php
require_once __DIR__ . '/../../../includes/admin/footer_admin.php';
?>