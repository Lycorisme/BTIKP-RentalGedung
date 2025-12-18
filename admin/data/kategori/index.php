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

// --- Daftar Icon FontAwesome (Preset) ---
$icon_presets = [
    'Umum' => [
        'fa-solid fa-building' => 'Gedung / Bangunan',
        'fa-solid fa-hotel' => 'Hotel / Penginapan',
        'fa-solid fa-warehouse' => 'Gudang / Aula Besar',
        'fa-solid fa-shop' => 'Toko / Ruko',
        'fa-solid fa-house' => 'Rumah / Villa',
    ],
    'Acara & Kegiatan' => [
        'fa-solid fa-handshake' => 'Meeting / Konferensi',
        'fa-solid fa-ring' => 'Pernikahan / Wedding',
        'fa-solid fa-cake-candles' => 'Ulang Tahun / Pesta',
        'fa-solid fa-music' => 'Konser / Pertunjukan',
        'fa-solid fa-graduation-cap' => 'Wisuda / Seminar',
    ],
    'Olahraga & Outdoor' => [
        'fa-solid fa-futbol' => 'Lapangan Futsal/Bola',
        'fa-solid fa-basketball' => 'Lapangan Basket',
        'fa-solid fa-person-swimming' => 'Kolam Renang',
        'fa-solid fa-tree' => 'Taman / Outdoor',
    ],
    'Lainnya' => [
        'fa-solid fa-star' => 'VIP / Eksklusif',
        'fa-solid fa-tag' => 'Umum / Standar',
        'fa-solid fa-shapes' => 'Lain-lain',
    ]
];

// --- Helper Functions ---

function createSlug($string){
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string)));
    return $slug;
}

// --- 2. Logic Handler (POST Requests) ---
$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // A. Create & Update
        if (isset($_POST['action']) && ($_POST['action'] === 'create' || $_POST['action'] === 'update')) {
            
            if (empty($_POST['nama'])) throw new Exception("Nama Kategori wajib diisi.");

            $nama = trim($_POST['nama']);
            // Auto generate slug jika kosong
            $slug = !empty($_POST['slug']) ? createSlug($_POST['slug']) : createSlug($nama);
            
            $deskripsi = trim($_POST['deskripsi']);
            $icon = trim($_POST['icon']);
            $warna = $_POST['warna'] ?? '#3b82f6';
            $urutan = (int)$_POST['urutan'];
            $is_active = (int)$_POST['is_active'];

            if ($_POST['action'] === 'create') {
                // Cek Slug Unik
                $stmtCheck = $conn->prepare("SELECT id FROM kategori_gedung WHERE slug = ?");
                $stmtCheck->execute([$slug]);
                if ($stmtCheck->rowCount() > 0) {
                    $slug .= '-' . time(); // Append timestamp jika duplikat
                }

                $stmt = $conn->prepare("INSERT INTO kategori_gedung (nama, slug, deskripsi, icon, warna, urutan, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
                if ($stmt->execute([$nama, $slug, $deskripsi, $icon, $warna, $urutan, $is_active])) {
                    $success_msg = "Kategori berhasil ditambahkan.";
                } else {
                    throw new Exception("Gagal menyimpan data.");
                }
            } else {
                $id = (int)$_POST['id'];
                
                // Cek Slug Unik (kecuali diri sendiri)
                $stmtCheck = $conn->prepare("SELECT id FROM kategori_gedung WHERE slug = ? AND id != ?");
                $stmtCheck->execute([$slug, $id]);
                if ($stmtCheck->rowCount() > 0) {
                    $slug .= '-' . time();
                }

                $stmt = $conn->prepare("UPDATE kategori_gedung SET nama=?, slug=?, deskripsi=?, icon=?, warna=?, urutan=?, is_active=? WHERE id=?");
                if ($stmt->execute([$nama, $slug, $deskripsi, $icon, $warna, $urutan, $is_active, $id])) {
                    $success_msg = "Kategori berhasil diperbarui.";
                } else {
                    throw new Exception("Gagal memperbarui data.");
                }
            }
        }

        // B. Single Actions
        if (isset($_POST['action']) && $_POST['action'] === 'soft_delete') {
            $id = (int)$_POST['id'];
            $stmt = $conn->prepare("UPDATE kategori_gedung SET deleted_at = NOW() WHERE id = ?");
            if ($stmt->execute([$id])) $success_msg = "Kategori dipindahkan ke sampah.";
        }

        if (isset($_POST['action']) && $_POST['action'] === 'restore') {
            $id = (int)$_POST['id'];
            $stmt = $conn->prepare("UPDATE kategori_gedung SET deleted_at = NULL WHERE id = ?");
            if ($stmt->execute([$id])) $success_msg = "Kategori berhasil dipulihkan.";
        }

        // C. Bulk Actions
        if (isset($_POST['action']) && $_POST['action'] === 'bulk_soft_delete') {
            $ids = explode(',', $_POST['ids']);
            $ids = array_filter(array_map('intval', $ids));
            if (!empty($ids)) {
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $stmt = $conn->prepare("UPDATE kategori_gedung SET deleted_at = NOW() WHERE id IN ($placeholders)");
                if ($stmt->execute($ids)) $success_msg = count($ids) . " kategori dipindahkan ke sampah.";
            }
        }

        if (isset($_POST['action']) && $_POST['action'] === 'bulk_restore') {
            $ids = explode(',', $_POST['ids']);
            $ids = array_filter(array_map('intval', $ids));
            if (!empty($ids)) {
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $stmt = $conn->prepare("UPDATE kategori_gedung SET deleted_at = NULL WHERE id IN ($placeholders)");
                if ($stmt->execute($ids)) $success_msg = count($ids) . " kategori berhasil dipulihkan.";
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
$status_filter = $_GET['status'] ?? ''; // Untuk is_active
$show_deleted = isset($_GET['show_deleted']) && $_GET['show_deleted'] == '1';

// Menentukan apakah filter harus terbuka
$is_filter_active = !empty($search) || $status_filter !== '' || $show_deleted;

$where_clauses = [];
$params = [];

if (!empty($search)) {
    $where_clauses[] = "(nama LIKE ? OR slug LIKE ? OR deskripsi LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

if ($status_filter !== '') {
    $where_clauses[] = "is_active = ?";
    $params[] = $status_filter;
}

if (!$show_deleted) {
    $where_clauses[] = "deleted_at IS NULL";
}

$where_sql = count($where_clauses) > 0 ? "WHERE " . implode(" AND ", $where_clauses) : "";

$count_query = "SELECT COUNT(*) FROM kategori_gedung $where_sql";
$stmt = $conn->prepare($count_query);
$stmt->execute($params);
$total_rows = $stmt->fetchColumn();
$total_pages = ceil($total_rows / $limit);

$query = "SELECT * FROM kategori_gedung $where_sql ORDER BY urutan ASC, created_at DESC LIMIT $limit OFFSET $offset";
$stmt = $conn->prepare($query);
$stmt->execute($params);
$kategoris = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buat URL Base untuk Pagination
$query_params = $_GET;
unset($query_params['page']);
$base_url = '?' . http_build_query($query_params);
$pagination_url = empty($query_params) ? '?page=' : $base_url . '&page=';

require_once __DIR__ . '/../../../includes/admin/header_admin.php';
?>

<div x-data="kategoriManager()" x-init="initData()" class="p-6 max-w-[1600px] mx-auto pb-24 relative">
    
    <?php if($success_msg): ?>
    <script>
        Swal.fire({ icon: 'success', title: 'Berhasil', text: '<?= $success_msg ?>', timer: 1500, showConfirmButton: false });
        localStorage.removeItem('kategori_selected_ids');
    </script>
    <?php endif; ?>
    <?php if($error_msg): ?>
    <script>Swal.fire({ icon: 'error', title: 'Gagal', text: '<?= $error_msg ?>' });</script>
    <?php endif; ?>

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Data Kategori</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Manajemen kategori gedung dan ruangan.</p>
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
                <i class="fa-solid fa-plus"></i>
                <span class="hidden sm:inline">Tambah Kategori</span>
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
                    <input type="text" x-model="filter.search" @keyup.enter="applyFilters()" placeholder="Nama kategori / deskripsi..." 
                           class="w-full pl-10 pr-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-sm focus:ring-2 focus:ring-primary/20 outline-none transition-all dark:text-white">
                </div>
            </div>

            <div class="col-span-1">
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1 uppercase">Status</label>
                <select x-model="filter.status" class="w-full px-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-sm focus:ring-2 focus:ring-primary/20 outline-none transition-all dark:text-white appearance-none">
                    <option value="">Semua Status</option>
                    <option value="1">Aktif</option>
                    <option value="0">Nonaktif</option>
                </select>
            </div>

            <div class="flex items-center justify-between gap-3">
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <div class="relative">
                        <input type="checkbox" x-model="filter.show_deleted" class="sr-only peer">
                        <div class="w-10 h-6 bg-slate-200 rounded-full peer 
                                    peer-focus:ring-4 peer-focus:ring-primary/30 dark:peer-focus:ring-primary/20 
                                    peer-checked:after:translate-x-full peer-checked:after:border-white 
                                    after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 
                                    peer-checked:bg-primary"></div>
                    </div>
                    <span class="text-xs font-semibold text-slate-600 dark:text-slate-300">Sampah</span>
                </label>
                
                <button @click="applyFilters()" 
                        class="bg-primary hover:bg-primary/90 shadow-lg shadow-primary/30 text-white px-6 py-2 rounded-xl text-sm font-semibold transition-all">
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
                        <th class="p-4 w-12 text-center">Ikon</th>
                        <th class="p-4">Nama Kategori & Slug</th>
                        <th class="p-4">Deskripsi</th>
                        <th class="p-4 text-center">Urutan</th>
                        <th class="p-4 text-center">Status</th>
                        <th class="p-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700 text-sm">
                    <?php if (count($kategoris) > 0): ?>
                        <?php foreach($kategoris as $index => $row): ?>
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
                            
                            <td class="p-4 text-center">
                                <div class="h-10 w-10 rounded-lg flex items-center justify-center text-lg shadow-sm mx-auto" 
                                     style="background-color: <?= $row['warna'] ?>20; color: <?= $row['warna'] ?>;">
                                    <i class="<?= htmlspecialchars($row['icon'] ?? 'fa-solid fa-tag') ?>"></i>
                                </div>
                            </td>

                            <td class="p-4 font-semibold">
                                <div class="flex flex-col">
                                    <span><?= htmlspecialchars($row['nama']) ?></span>
                                    <span class="text-xs text-slate-400 font-mono font-normal">/<?= htmlspecialchars($row['slug']) ?></span>
                                </div>
                                <?php if($is_deleted): ?>
                                    <span class="mt-1 inline-block text-[10px] bg-red-100 text-red-600 px-1.5 py-0.5 rounded border border-red-200">DELETED</span>
                                <?php endif; ?>
                            </td>

                            <td class="p-4 text-slate-500 dark:text-slate-400">
                                <?= htmlspecialchars(substr($row['deskripsi'], 0, 50)) . (strlen($row['deskripsi']) > 50 ? '...' : '') ?>
                            </td>

                            <td class="p-4 text-center font-mono text-slate-400">
                                <?= $row['urutan'] ?>
                            </td>

                            <td class="p-4 text-center">
                                <?php if($row['is_active']): ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400">
                                        <i class="fa-solid fa-check mr-1"></i> Aktif
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400">
                                        <i class="fa-solid fa-xmark mr-1"></i> Nonaktif
                                    </span>
                                <?php endif; ?>
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
                            <td colspan="8" class="p-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="h-16 w-16 bg-slate-100 dark:bg-slate-700 rounded-full flex items-center justify-center mb-4">
                                        <i class="fa-solid fa-layer-group text-3xl"></i>
                                    </div>
                                    <p class="font-medium">Tidak ada data kategori</p>
                                    <p class="text-xs mt-1">Coba ubah filter atau tambah data baru.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex flex-col md:flex-row items-center justify-between gap-4">
            <span class="text-xs text-slate-500 dark:text-slate-400">
                Menampilkan <span class="font-bold text-slate-700 dark:text-slate-200"><?= count($kategoris) ?></span> dari <span class="font-bold text-slate-700 dark:text-slate-200"><?= $total_rows ?></span> data
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
                    <i class="fa-solid fa-rotate-left"></i> Restore
                </button>
            </template>

            <template x-if="selectionMode === 'active'">
                <button @click="confirmBulkDelete" 
                        class="px-4 py-2 rounded-xl bg-red-500 text-white hover:bg-red-600 shadow-lg shadow-red-500/30 text-xs font-bold transition-all flex items-center gap-2">
                    <i class="fa-regular fa-trash-can"></i> Delete
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

        <div class="relative bg-white dark:bg-slate-800 w-full max-w-lg rounded-2xl shadow-2xl border border-slate-100 dark:border-slate-700 overflow-hidden flex flex-col"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="-translate-y-10 opacity-0 scale-95"
             x-transition:enter-end="translate-y-0 opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="translate-y-0 opacity-100 scale-100"
             x-transition:leave-end="-translate-y-10 opacity-0 scale-95">
            
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-700/30 shrink-0">
                <h3 class="font-bold text-lg text-slate-800 dark:text-white" x-text="modalMode === 'create' ? 'Tambah Kategori Baru' : 'Edit Kategori'"></h3>
                <button @click="closeModal" class="text-slate-400 hover:text-rose-500 transition-colors">
                    <i class="fa-solid fa-times text-xl"></i>
                </button>
            </div>
            
            <form method="POST" class="flex-1 overflow-y-auto p-6">
                <input type="hidden" name="action" :value="modalMode">
                <input type="hidden" name="id" :value="formData.id">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Nama Kategori <span class="text-red-500">*</span></label>
                        <input type="text" name="nama" x-model="formData.nama" @input="generateSlug" required 
                               class="w-full px-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 focus:ring-2 focus:ring-primary/20 outline-none dark:text-white">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Slug URL</label>
                        <input type="text" name="slug" x-model="formData.slug" 
                               class="w-full px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-500 font-mono text-xs focus:ring-2 focus:ring-primary/20 outline-none">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Icon FontAwesome</label>
                        <div class="flex gap-2">
                            <div class="h-10 w-10 flex items-center justify-center bg-slate-100 dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700">
                                <i :class="formData.icon" class="text-slate-500" :style="{ color: formData.warna }"></i>
                            </div>
                            <select name="icon" x-model="formData.icon" class="flex-1 px-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 focus:ring-2 focus:ring-primary/20 outline-none dark:text-white">
                                <option value="">-- Pilih Icon --</option>
                                <?php foreach($icon_presets as $group => $icons): ?>
                                    <optgroup label="<?= $group ?>">
                                        <?php foreach($icons as $class => $label): ?>
                                            <option value="<?= $class ?>"><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Warna Label</label>
                        <div class="flex items-center gap-2 h-10 border border-slate-200 dark:border-slate-700 rounded-xl px-2">
                            <input type="color" name="warna" x-model="formData.warna" class="h-8 w-12 rounded cursor-pointer border-0 p-0 bg-transparent">
                            <span class="text-xs font-mono text-slate-500" x-text="formData.warna"></span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Urutan</label>
                        <input type="number" name="urutan" x-model="formData.urutan" placeholder="0"
                               class="w-full px-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 focus:ring-2 focus:ring-primary/20 outline-none dark:text-white">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Status</label>
                        <select name="is_active" x-model="formData.is_active" class="w-full px-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 focus:ring-2 focus:ring-primary/20 outline-none dark:text-white">
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Deskripsi</label>
                        <textarea name="deskripsi" x-model="formData.deskripsi" rows="3" placeholder="Jelaskan kategori ini..."
                                  class="w-full px-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 focus:ring-2 focus:ring-primary/20 outline-none dark:text-white resize-none"></textarea>
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
    function kategoriManager() {
        return {
            filter: {
                search: '<?= addslashes($search) ?>',
                status: '<?= $status_filter ?>',
                show_deleted: <?= $show_deleted ? 'true' : 'false' ?>
            },
            showFilter: <?= $is_filter_active ? 'true' : 'false' ?>,
            
            get activeFilterCount() {
                let count = 0;
                if (this.filter.search) count++;
                if (this.filter.status !== '') count++;
                if (this.filter.show_deleted) count++;
                return count;
            },

            applyFilters() {
                const params = new URLSearchParams();
                if (this.filter.search) params.append('search', this.filter.search);
                if (this.filter.status !== '') params.append('status', this.filter.status);
                if (this.filter.show_deleted) params.append('show_deleted', '1');
                
                params.append('page', '1');
                window.location.search = params.toString();
            },

            // Checkbox & Bulk Actions
            allSelected: false,
            selectedItems: [],
            selectionMode: null,

            initData() {
                const savedSelection = localStorage.getItem('kategori_selected_ids');
                if (savedSelection) {
                    try {
                        this.selectedItems = JSON.parse(savedSelection);
                        this.$nextTick(() => { this.updateSelectionState(); });
                    } catch(e) { console.error('Error parsing selection', e); }
                }
            },

            updateSelectionState() {
                localStorage.setItem('kategori_selected_ids', JSON.stringify(this.selectedItems));

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
                localStorage.removeItem('kategori_selected_ids');
            },

            getCurrentPageMode() {
                const firstInput = document.querySelector('input[type="checkbox"][data-status]');
                return firstInput ? firstInput.getAttribute('data-status') : null;
            },

            // Modal Logic
            modalOpen: false,
            modalMode: 'create',
            formData: { id: '', nama: '', slug: '', deskripsi: '', icon: 'fa-solid fa-building', warna: '#3b82f6', urutan: 0, is_active: 1 },

            openModal(mode, data = null) {
                this.modalMode = mode;
                if (mode === 'update' && data) {
                    this.formData = { ...data };
                } else {
                    this.formData = { id: '', nama: '', slug: '', deskripsi: '', icon: 'fa-solid fa-building', warna: '#3b82f6', urutan: 0, is_active: 1 };
                }
                this.modalOpen = true;
            },
            closeModal() { this.modalOpen = false; },

            generateSlug() {
                this.formData.slug = this.formData.nama
                    .toLowerCase()
                    .replace(/[^a-z0-9 -]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-');
            },

            // SweetAlerts
            confirmDelete(id) {
                Swal.fire({
                    title: 'Pindahkan ke Sampah?',
                    text: "Data akan disembunyikan.",
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
                    text: "Data akan kembali aktif.",
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