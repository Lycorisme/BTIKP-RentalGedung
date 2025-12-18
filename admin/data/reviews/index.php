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

function renderStars($rating) {
    $stars = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $rating) {
            $stars .= '<i class="fa-solid fa-star text-amber-400 text-xs"></i>';
        } else {
            $stars .= '<i class="fa-regular fa-star text-slate-300 text-xs"></i>';
        }
    }
    return $stars;
}

function visibilityBadge($show) {
    return $show 
        ? '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400">Ditampilkan</span>' 
        : '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400">Disembunyikan</span>';
}

// --- 2. Logic Handler (POST Requests) ---
$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // A. Toggle Visibility (Single)
        if (isset($_POST['action']) && $_POST['action'] === 'toggle_visibility') {
            $id = (int)$_POST['id'];
            $current_val = (int)$_POST['current_val'];
            $new_val = $current_val ? 0 : 1; // Flip value
            
            $stmt = $conn->prepare("UPDATE reviews SET tampilkan = ? WHERE id = ?");
            if ($stmt->execute([$new_val, $id])) {
                $success_msg = "Status visibilitas ulasan berhasil diubah.";
            }
        }

        // B. Delete (Hard Delete - karena tidak ada deleted_at di schema)
        if (isset($_POST['action']) && $_POST['action'] === 'delete') {
            $id = (int)$_POST['id'];
            $stmt = $conn->prepare("DELETE FROM reviews WHERE id = ?");
            if ($stmt->execute([$id])) {
                $success_msg = "Ulasan berhasil dihapus permanen.";
            }
        }

        // C. Bulk Delete
        if (isset($_POST['action']) && $_POST['action'] === 'bulk_delete') {
            $ids = explode(',', $_POST['ids']);
            $ids = array_filter(array_map('intval', $ids));
            if (!empty($ids)) {
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $stmt = $conn->prepare("DELETE FROM reviews WHERE id IN ($placeholders)");
                if ($stmt->execute($ids)) {
                    $success_msg = count($ids) . " ulasan berhasil dihapus permanen.";
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
$rating_filter = $_GET['rating'] ?? '';
$visibility_filter = $_GET['visibility'] ?? '';

// Menentukan apakah filter harus terbuka
$is_filter_active = !empty($search) || !empty($rating_filter) || $visibility_filter !== '';

$where_clauses = [];
$params = [];

// Join Tables untuk info detail
$base_sql = "FROM reviews r 
             JOIN users u ON r.user_id = u.id 
             JOIN gedung g ON r.gedung_id = g.id";

if (!empty($search)) {
    $where_clauses[] = "(u.nama_lengkap LIKE ? OR g.nama LIKE ? OR r.komentar LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param; $params[] = $search_param; $params[] = $search_param;
}

if (!empty($rating_filter)) {
    $where_clauses[] = "r.rating = ?";
    $params[] = $rating_filter;
}

if ($visibility_filter !== '') {
    $where_clauses[] = "r.tampilkan = ?";
    $params[] = $visibility_filter;
}

$where_sql = count($where_clauses) > 0 ? "WHERE " . implode(" AND ", $where_clauses) : "";

// Count Total
$count_query = "SELECT COUNT(*) $base_sql $where_sql";
$stmt = $conn->prepare($count_query);
$stmt->execute($params);
$total_rows = $stmt->fetchColumn();
$total_pages = ceil($total_rows / $limit);

// Fetch Data
$query = "SELECT r.*, u.nama_lengkap, u.username, g.nama as nama_gedung 
          $base_sql $where_sql 
          ORDER BY r.created_at DESC LIMIT $limit OFFSET $offset";
$stmt = $conn->prepare($query);
$stmt->execute($params);
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buat URL Base untuk Pagination
$query_params = $_GET;
unset($query_params['page']);
$base_url = '?' . http_build_query($query_params);
$pagination_url = empty($query_params) ? '?page=' : $base_url . '&page=';

require_once __DIR__ . '/../../../includes/admin/header_admin.php';
?>

<div x-data="reviewManager()" x-init="initData()" class="p-6 max-w-[1600px] mx-auto pb-24 relative">
    
    <?php if($success_msg): ?>
    <script>
        Swal.fire({ icon: 'success', title: 'Berhasil', text: '<?= $success_msg ?>', timer: 1500, showConfirmButton: false });
        localStorage.removeItem('reviews_selected_ids');
    </script>
    <?php endif; ?>
    <?php if($error_msg): ?>
    <script>Swal.fire({ icon: 'error', title: 'Gagal', text: '<?= $error_msg ?>' });</script>
    <?php endif; ?>

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Ulasan & Rating</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Kelola ulasan pengguna terhadap gedung.</p>
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
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div class="col-span-1 md:col-span-2">
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1 uppercase">Pencarian</label>
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-slate-400"></i>
                    <input type="text" x-model="filter.search" @keyup.enter="applyFilters()" placeholder="Nama User / Gedung / Isi Komentar..." 
                           class="w-full pl-10 pr-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-sm focus:ring-2 focus:ring-primary/20 outline-none transition-all dark:text-white">
                </div>
            </div>

            <div class="col-span-1">
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1 uppercase">Rating</label>
                <select x-model="filter.rating" class="w-full px-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-sm focus:ring-2 focus:ring-primary/20 outline-none transition-all dark:text-white appearance-none">
                    <option value="">Semua Rating</option>
                    <option value="5">⭐⭐⭐⭐⭐ (5)</option>
                    <option value="4">⭐⭐⭐⭐ (4)</option>
                    <option value="3">⭐⭐⭐ (3)</option>
                    <option value="2">⭐⭐ (2)</option>
                    <option value="1">⭐ (1)</option>
                </select>
            </div>

            <div class="col-span-1">
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1 uppercase">Visibilitas</label>
                <select x-model="filter.visibility" class="w-full px-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-sm focus:ring-2 focus:ring-primary/20 outline-none transition-all dark:text-white appearance-none">
                    <option value="">Semua</option>
                    <option value="1">Ditampilkan</option>
                    <option value="0">Disembunyikan</option>
                </select>
            </div>

            <div class="md:col-start-4 flex justify-end">
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
                        <th class="p-4">User & Gedung</th>
                        <th class="p-4 w-32">Rating</th>
                        <th class="p-4">Komentar</th>
                        <th class="p-4 text-center">Tgl</th>
                        <th class="p-4 text-center">Status</th>
                        <th class="p-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700 text-sm">
                    <?php if (count($reviews) > 0): ?>
                        <?php foreach($reviews as $index => $row): ?>
                        <tr class="transition-colors hover:bg-slate-50 dark:hover:bg-slate-700/30 text-slate-600 dark:text-slate-300">
                            
                            <td class="p-4 text-center">
                                <input type="checkbox" 
                                       value="<?= $row['id'] ?>" 
                                       x-model="selectedItems"
                                       @change="updateSelectionState()"
                                       class="rounded border-slate-300 text-primary focus:ring-primary h-4 w-4">
                            </td>
                            <td class="p-4 text-center font-mono text-slate-400"><?= $offset + $index + 1 ?></td>
                            <td class="p-4">
                                <div class="flex flex-col">
                                    <span class="font-bold text-slate-800 dark:text-white"><?= htmlspecialchars($row['nama_lengkap']) ?></span>
                                    <span class="text-xs text-primary mb-1"><?= htmlspecialchars($row['nama_gedung']) ?></span>
                                </div>
                            </td>
                            <td class="p-4">
                                <div class="flex gap-0.5">
                                    <?= renderStars($row['rating']) ?>
                                </div>
                                <span class="text-[10px] text-slate-400 font-mono mt-1 block"><?= $row['rating'] ?>.0 / 5.0</span>
                            </td>
                            <td class="p-4">
                                <div class="relative group cursor-pointer" @click='openViewModal(<?= json_encode($row) ?>)'>
                                    <p class="line-clamp-2 text-sm text-slate-600 dark:text-slate-300 italic">"<?= htmlspecialchars($row['komentar']) ?>"</p>
                                    <span class="text-[10px] text-primary group-hover:underline">Lihat selengkapnya</span>
                                </div>
                            </td>
                            <td class="p-4 text-center text-xs text-slate-500">
                                <?= date('d M Y', strtotime($row['created_at'])) ?>
                            </td>
                            <td class="p-4 text-center">
                                <button @click="toggleVisibility(<?= $row['id'] ?>, <?= $row['tampilkan'] ?>)" 
                                        class="hover:opacity-80 transition-opacity" title="Klik untuk ubah">
                                    <?= visibilityBadge($row['tampilkan']) ?>
                                </button>
                            </td>
                            <td class="p-4 text-right">
                                <div class="flex justify-end items-center gap-2">
                                    <button @click='openViewModal(<?= json_encode($row) ?>)' 
                                            class="p-2 rounded-lg text-slate-400 hover:text-blue-500 hover:bg-blue-50 dark:hover:bg-slate-700 transition-all" title="Detail">
                                        <i class="fa-regular fa-eye"></i>
                                    </button>
                                    <button @click="confirmDelete(<?= $row['id'] ?>)" 
                                            class="p-2 rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-slate-700 transition-all" title="Hapus Permanen">
                                        <i class="fa-regular fa-trash-can"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="p-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="h-16 w-16 bg-slate-100 dark:bg-slate-700 rounded-full flex items-center justify-center mb-4">
                                        <i class="fa-regular fa-star text-3xl"></i>
                                    </div>
                                    <p class="font-medium">Belum ada ulasan</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex flex-col md:flex-row items-center justify-between gap-4">
            <span class="text-xs text-slate-500 dark:text-slate-400">
                Menampilkan <span class="font-bold text-slate-700 dark:text-slate-200"><?= count($reviews) ?></span> dari <span class="font-bold text-slate-700 dark:text-slate-200"><?= $total_rows ?></span> data
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
         class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="viewModalOpen = false"></div>

        <div class="relative bg-white dark:bg-slate-800 w-full max-w-lg rounded-2xl shadow-2xl border border-slate-100 dark:border-slate-700 overflow-hidden"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="-translate-y-10 opacity-0 scale-95"
             x-transition:enter-end="translate-y-0 opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="translate-y-0 opacity-100 scale-100"
             x-transition:leave-end="-translate-y-10 opacity-0 scale-95">
            
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-700/30">
                <h3 class="font-bold text-lg text-slate-800 dark:text-white">Detail Ulasan</h3>
                <button @click="viewModalOpen = false" class="text-slate-400 hover:text-rose-500 transition-colors">
                    <i class="fa-solid fa-times text-xl"></i>
                </button>
            </div>
            
            <div class="p-6">
                <div class="flex items-center gap-4 mb-4">
                    <div class="h-12 w-12 rounded-full bg-slate-200 flex items-center justify-center text-slate-500 font-bold text-xl">
                        <span x-text="viewData.nama_lengkap ? viewData.nama_lengkap.charAt(0) : '?'"></span>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-800 dark:text-white" x-text="viewData.nama_lengkap"></h4>
                        <p class="text-xs text-slate-500" x-text="viewData.nama_gedung"></p>
                    </div>
                    <div class="ml-auto flex items-center gap-1 bg-amber-50 px-3 py-1 rounded-full border border-amber-100">
                        <i class="fa-solid fa-star text-amber-400 text-sm"></i>
                        <span class="font-bold text-amber-600 text-sm" x-text="viewData.rating"></span>
                    </div>
                </div>
                
                <div class="bg-slate-50 dark:bg-slate-900 p-4 rounded-xl border border-slate-100 dark:border-slate-700 mb-4">
                    <p class="text-slate-600 dark:text-slate-300 italic text-sm leading-relaxed" x-text="viewData.komentar"></p>
                </div>

                <div class="text-right">
                    <span class="text-xs text-slate-400" x-text="'Dibuat pada: ' + viewData.created_at"></span>
                </div>
            </div>
        </div>
    </div>

    <form id="deleteForm" method="POST" class="hidden">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id" id="deleteId">
    </form>
    
    <form id="toggleVisibilityForm" method="POST" class="hidden">
        <input type="hidden" name="action" value="toggle_visibility">
        <input type="hidden" name="id" id="toggleId">
        <input type="hidden" name="current_val" id="toggleVal">
    </form>

    <form id="bulkDeleteForm" method="POST" class="hidden">
        <input type="hidden" name="action" value="bulk_delete">
        <input type="hidden" name="ids" id="bulkDeleteIds">
    </form>
</div>

<script>
    function reviewManager() {
        return {
            filter: {
                search: '<?= addslashes($search) ?>',
                rating: '<?= $rating_filter ?>',
                visibility: '<?= $visibility_filter ?>'
            },
            showFilter: <?= $is_filter_active ? 'true' : 'false' ?>,
            
            get activeFilterCount() {
                let count = 0;
                if (this.filter.search) count++;
                if (this.filter.rating) count++;
                if (this.filter.visibility) count++;
                return count;
            },

            applyFilters() {
                const params = new URLSearchParams();
                if (this.filter.search) params.append('search', this.filter.search);
                if (this.filter.rating) params.append('rating', this.filter.rating);
                if (this.filter.visibility) params.append('visibility', this.filter.visibility);
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
                const savedSelection = localStorage.getItem('reviews_selected_ids');
                if (savedSelection) {
                    try {
                        this.selectedItems = JSON.parse(savedSelection);
                        this.$nextTick(() => { this.updateSelectionState(); });
                    } catch(e) { console.error('Error parsing selection', e); }
                }
            },

            updateSelectionState() {
                localStorage.setItem('reviews_selected_ids', JSON.stringify(this.selectedItems));
                if (this.selectedItems.length === 0) this.allSelected = false;
            },

            toggleAll() {
                this.allSelected = !this.allSelected;
                this.selectedItems = [];
                if (this.allSelected) {
                    document.querySelectorAll('input[type="checkbox"][x-model="selectedItems"]').forEach(el => {
                        this.selectedItems.push(el.value);
                    });
                }
                this.updateSelectionState();
            },

            cancelSelection() {
                this.selectedItems = [];
                this.allSelected = false;
                localStorage.removeItem('reviews_selected_ids');
            },

            // View Modal
            viewModalOpen: false,
            viewData: {},
            openViewModal(data) {
                this.viewData = data;
                this.viewModalOpen = true;
            },

            // SweetAlerts Actions
            confirmDelete(id) {
                Swal.fire({
                    title: 'Hapus Permanen?',
                    text: "Ulasan ini akan dihapus selamanya.",
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
            
            toggleVisibility(id, currentVal) {
                // Submit form directly usually, but can confirm if needed.
                document.getElementById('toggleId').value = id;
                document.getElementById('toggleVal').value = currentVal;
                document.getElementById('toggleVisibilityForm').submit();
            },

            confirmBulkDelete() {
                Swal.fire({
                    title: 'Hapus ' + this.selectedItems.length + ' Ulasan?',
                    text: "Data yang dipilih akan dihapus permanen.",
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