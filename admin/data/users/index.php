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

function roleBadge($role) {
    $colors = [
        'superadmin' => 'bg-purple-100 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400 border-purple-200',
        'admin' => 'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400 border-blue-200',
        'penyewa' => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300 border-slate-200',
    ];
    return $colors[$role] ?? 'bg-gray-100 text-gray-600';
}

function activeBadge($isActive) {
    return $isActive 
        ? '<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Aktif</span>' 
        : '<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400"><span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>Nonaktif</span>';
}

// --- 2. Logic Handler (POST Requests) ---
$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // A. Create & Update
        if (isset($_POST['action']) && ($_POST['action'] === 'create' || $_POST['action'] === 'update')) {
            // Validasi Input Dasar
            if (empty($_POST['username']) || empty($_POST['email']) || empty($_POST['nama_lengkap'])) {
                throw new Exception("Username, Email, dan Nama Lengkap wajib diisi.");
            }

            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            $nama_lengkap = trim($_POST['nama_lengkap']);
            $no_telepon = trim($_POST['no_telepon']);
            $role = $_POST['role'];
            $is_active = (int)$_POST['is_active'];

            // Validasi Password
            $password = null;
            if (!empty($_POST['password'])) {
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            }

            if ($_POST['action'] === 'create') {
                if (empty($_POST['password'])) throw new Exception("Password wajib diisi untuk pengguna baru.");

                // Cek duplikasi username/email manual (opsional, tapi constraint DB akan handle)
                $check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
                $check->execute([$username, $email]);
                if ($check->rowCount() > 0) throw new Exception("Username atau Email sudah digunakan.");

                $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, nama_lengkap, no_telepon, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
                if ($stmt->execute([$username, $email, $password, $role, $nama_lengkap, $no_telepon, $is_active])) {
                    $success_msg = "User berhasil ditambahkan.";
                } else {
                    throw new Exception("Gagal menyimpan data ke database.");
                }
            } else {
                $id = (int)$_POST['id'];
                
                // Logic update password dinamis
                $query = "UPDATE users SET username=?, email=?, role=?, nama_lengkap=?, no_telepon=?, is_active=?";
                $params = [$username, $email, $role, $nama_lengkap, $no_telepon, $is_active];
                
                if ($password) {
                    $query .= ", password=?";
                    $params[] = $password;
                }
                $query .= " WHERE id=?";
                $params[] = $id;

                $stmt = $conn->prepare($query);
                if ($stmt->execute($params)) {
                    $success_msg = "Data user berhasil diperbarui.";
                } else {
                    throw new Exception("Gagal memperbarui data user.");
                }
            }
        }

        // B. Single Actions
        if (isset($_POST['action']) && $_POST['action'] === 'soft_delete') {
            $id = (int)$_POST['id'];
            // Prevent deleting self
            if ($id == $_SESSION['user_id']) throw new Exception("Anda tidak dapat menghapus akun sendiri.");
            
            $stmt = $conn->prepare("UPDATE users SET deleted_at = NOW() WHERE id = ?");
            if ($stmt->execute([$id])) $success_msg = "User dipindahkan ke sampah.";
        }

        if (isset($_POST['action']) && $_POST['action'] === 'restore') {
            $id = (int)$_POST['id'];
            $stmt = $conn->prepare("UPDATE users SET deleted_at = NULL WHERE id = ?");
            if ($stmt->execute([$id])) $success_msg = "User berhasil dipulihkan.";
        }

        // C. Bulk Actions
        if (isset($_POST['action']) && $_POST['action'] === 'bulk_soft_delete') {
            $ids = explode(',', $_POST['ids']);
            $ids = array_filter(array_map('intval', $ids));
            
            // Filter out own ID from bulk delete
            $ids = array_diff($ids, [$_SESSION['user_id']]);

            if (!empty($ids)) {
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $stmt = $conn->prepare("UPDATE users SET deleted_at = NOW() WHERE id IN ($placeholders)");
                if ($stmt->execute($ids)) $success_msg = count($ids) . " user dipindahkan ke sampah.";
            }
        }

        if (isset($_POST['action']) && $_POST['action'] === 'bulk_restore') {
            $ids = explode(',', $_POST['ids']);
            $ids = array_filter(array_map('intval', $ids));
            if (!empty($ids)) {
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $stmt = $conn->prepare("UPDATE users SET deleted_at = NULL WHERE id IN ($placeholders)");
                if ($stmt->execute($ids)) $success_msg = count($ids) . " user berhasil dipulihkan.";
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
$role_filter = $_GET['role'] ?? '';
$show_deleted = isset($_GET['show_deleted']) && $_GET['show_deleted'] == '1';

// Menentukan apakah filter harus terbuka
$is_filter_active = !empty($search) || !empty($role_filter) || $show_deleted;

$where_clauses = [];
$params = [];

if (!empty($search)) {
    $where_clauses[] = "(username LIKE ? OR email LIKE ? OR nama_lengkap LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param; $params[] = $search_param; $params[] = $search_param;
}

if (!empty($role_filter)) {
    $where_clauses[] = "role = ?";
    $params[] = $role_filter;
}

if (!$show_deleted) {
    $where_clauses[] = "deleted_at IS NULL";
}

$where_sql = count($where_clauses) > 0 ? "WHERE " . implode(" AND ", $where_clauses) : "";

$count_query = "SELECT COUNT(*) FROM users $where_sql";
$stmt = $conn->prepare($count_query);
$stmt->execute($params);
$total_rows = $stmt->fetchColumn();
$total_pages = ceil($total_rows / $limit);

$query = "SELECT * FROM users $where_sql ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$stmt = $conn->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buat URL Base untuk Pagination
$query_params = $_GET;
unset($query_params['page']);
$base_url = '?' . http_build_query($query_params);
$pagination_url = empty($query_params) ? '?page=' : $base_url . '&page=';

require_once __DIR__ . '/../../../includes/admin/header_admin.php';
?>

<div x-data="userManager()" x-init="initData()" class="p-6 max-w-[1600px] mx-auto pb-24 relative">
    
    <?php if($success_msg): ?>
    <script>
        Swal.fire({ icon: 'success', title: 'Berhasil', text: '<?= $success_msg ?>', timer: 1500, showConfirmButton: false });
        localStorage.removeItem('users_selected_ids');
    </script>
    <?php endif; ?>
    <?php if($error_msg): ?>
    <script>Swal.fire({ icon: 'error', title: 'Gagal', text: '<?= $error_msg ?>' });</script>
    <?php endif; ?>

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Manajemen Users</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Kelola akun administrator, staf, dan penyewa.</p>
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
                <i class="fa-solid fa-user-plus"></i>
                <span class="hidden sm:inline">Tambah User</span>
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
                    <input type="text" x-model="filter.search" @keyup.enter="applyFilters()" placeholder="Username / Email / Nama..." 
                           class="w-full pl-10 pr-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-sm focus:ring-2 focus:ring-primary/20 outline-none transition-all dark:text-white">
                </div>
            </div>

            <div class="col-span-1">
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1 uppercase">Role</label>
                <select x-model="filter.role" class="w-full px-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-sm focus:ring-2 focus:ring-primary/20 outline-none transition-all dark:text-white appearance-none">
                    <option value="">Semua Role</option>
                    <option value="superadmin">Super Admin</option>
                    <option value="admin">Admin</option>
                    <option value="penyewa">Penyewa</option>
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
                        <th class="p-4">User</th>
                        <th class="p-4">Role</th>
                        <th class="p-4">Kontak</th>
                        <th class="p-4 text-center">Status</th>
                        <th class="p-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700 text-sm">
                    <?php if (count($users) > 0): ?>
                        <?php foreach($users as $index => $row): ?>
                        <?php 
                            $is_deleted = !empty($row['deleted_at']); 
                            $row_type = $is_deleted ? 'deleted' : 'active';
                            $is_self = ($row['id'] == $_SESSION['user_id']);
                        ?>
                        <tr class="transition-colors hover:bg-slate-50 dark:hover:bg-slate-700/30 group 
                                   <?= $is_deleted ? 'bg-red-50/60 dark:bg-red-900/10 text-red-700 dark:text-red-300' : 'text-slate-600 dark:text-slate-300' ?>">
                            
                            <td class="p-4 text-center">
                                <input type="checkbox" 
                                       value="<?= $row['id'] ?>" 
                                       data-status="<?= $row_type ?>"
                                       x-model="selectedItems"
                                       @change="updateSelectionState()"
                                       :disabled="(selectionMode !== null && selectionMode !== '<?= $row_type ?>') || <?= $is_self ? 'true' : 'false' ?>"
                                       class="rounded border-slate-300 text-primary focus:ring-primary h-4 w-4 disabled:opacity-40 disabled:bg-slate-200 disabled:cursor-not-allowed">
                            </td>
                            <td class="p-4 text-center font-mono text-slate-400"><?= $offset + $index + 1 ?></td>
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center text-slate-500 font-bold text-lg">
                                        <?= strtoupper(substr($row['username'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div class="font-bold dark:text-white flex items-center gap-2">
                                            <?= htmlspecialchars($row['username']) ?>
                                            <?php if($is_self): ?>
                                                <span class="text-[10px] bg-blue-100 text-blue-600 px-1.5 rounded border border-blue-200">YOU</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-xs text-slate-400"><?= htmlspecialchars($row['nama_lengkap']) ?></div>
                                    </div>
                                </div>
                                <?php if($is_deleted): ?>
                                    <span class="mt-1 inline-block text-[10px] bg-red-100 text-red-600 px-1.5 py-0.5 rounded border border-red-200">DELETED</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-bold border <?= roleBadge($row['role']) ?>">
                                    <?= ucfirst($row['role']) ?>
                                </span>
                            </td>
                            <td class="p-4">
                                <div class="flex flex-col gap-1 text-xs text-slate-500 dark:text-slate-400">
                                    <span class="flex items-center gap-2"><i class="fa-regular fa-envelope w-4"></i> <?= htmlspecialchars($row['email']) ?></span>
                                    <span class="flex items-center gap-2"><i class="fa-solid fa-phone w-4"></i> <?= $row['no_telepon'] ? htmlspecialchars($row['no_telepon']) : '-' ?></span>
                                </div>
                            </td>
                            <td class="p-4 text-center">
                                <?= activeBadge($row['is_active']) ?>
                            </td>
                            <td class="p-4 text-right">
                                <div class="flex justify-end items-center gap-2">
                                    <?php if(!$is_deleted): ?>
                                        <button @click='openModal("update", <?= json_encode($row) ?>)' 
                                                class="p-2 rounded-lg text-slate-400 hover:text-primary hover:bg-indigo-50 dark:hover:bg-slate-700 transition-all" title="Edit">
                                            <i class="fa-regular fa-pen-to-square"></i>
                                        </button>
                                        <?php if(!$is_self): ?>
                                        <button @click="confirmDelete(<?= $row['id'] ?>)" 
                                                class="p-2 rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-slate-700 transition-all" title="Hapus">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </button>
                                        <?php endif; ?>
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
                            <td colspan="7" class="p-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="h-16 w-16 bg-slate-100 dark:bg-slate-700 rounded-full flex items-center justify-center mb-4">
                                        <i class="fa-regular fa-user text-3xl"></i>
                                    </div>
                                    <p class="font-medium">Tidak ada data user</p>
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
                Menampilkan <span class="font-bold text-slate-700 dark:text-slate-200"><?= count($users) ?></span> dari <span class="font-bold text-slate-700 dark:text-slate-200"><?= $total_rows ?></span> data
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

        <div class="relative bg-white dark:bg-slate-800 w-full max-w-2xl rounded-2xl shadow-2xl border border-slate-100 dark:border-slate-700 overflow-hidden flex flex-col max-h-[90vh]"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="-translate-y-10 opacity-0 scale-95"
             x-transition:enter-end="translate-y-0 opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="translate-y-0 opacity-100 scale-100"
             x-transition:leave-end="-translate-y-10 opacity-0 scale-95">
            
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-700/30 shrink-0">
                <h3 class="font-bold text-lg text-slate-800 dark:text-white" x-text="modalMode === 'create' ? 'Tambah User Baru' : 'Edit Data User'"></h3>
                <button @click="closeModal" class="text-slate-400 hover:text-rose-500 transition-colors">
                    <i class="fa-solid fa-times text-xl"></i>
                </button>
            </div>
            
            <form method="POST" class="flex-1 overflow-y-auto p-6">
                <input type="hidden" name="action" :value="modalMode">
                <input type="hidden" name="id" :value="formData.id">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_lengkap" x-model="formData.nama_lengkap" required class="w-full px-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 focus:ring-2 focus:ring-primary/20 outline-none dark:text-white">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Username <span class="text-red-500">*</span></label>
                        <input type="text" name="username" x-model="formData.username" required class="w-full px-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 focus:ring-2 focus:ring-primary/20 outline-none dark:text-white">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" x-model="formData.email" required class="w-full px-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 focus:ring-2 focus:ring-primary/20 outline-none dark:text-white">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">No Telepon</label>
                        <input type="text" name="no_telepon" x-model="formData.no_telepon" class="w-full px-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 focus:ring-2 focus:ring-primary/20 outline-none dark:text-white">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Role</label>
                        <select name="role" x-model="formData.role" class="w-full px-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 focus:ring-2 focus:ring-primary/20 outline-none dark:text-white">
                            <option value="penyewa">Penyewa</option>
                            <option value="admin">Admin</option>
                            <option value="superadmin">Super Admin</option>
                        </select>
                    </div>

                    <div class="md:col-span-2 border-t border-slate-100 dark:border-slate-700 pt-4 mt-2">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                            Password
                            <span x-show="modalMode === 'create'" class="text-red-500">*</span>
                            <span x-show="modalMode === 'update'" class="text-xs font-normal text-slate-400 ml-1">(Kosongkan jika tidak ingin mengubah)</span>
                        </label>
                        <input type="password" name="password" :required="modalMode === 'create'" class="w-full px-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 focus:ring-2 focus:ring-primary/20 outline-none dark:text-white" placeholder="******">
                    </div>

                    <div class="md:col-span-2 flex items-center gap-3 bg-slate-50 dark:bg-slate-700/30 p-3 rounded-xl border border-slate-100 dark:border-slate-700">
                        <input type="hidden" name="is_active" :value="formData.is_active ? 1 : 0">
                        <button type="button" @click="formData.is_active = !formData.is_active" 
                                :class="formData.is_active ? 'bg-emerald-500' : 'bg-slate-300 dark:bg-slate-600'"
                                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                            <span aria-hidden="true" 
                                  :class="formData.is_active ? 'translate-x-5' : 'translate-x-0'"
                                  class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                        </button>
                        <span class="text-sm font-medium text-slate-700 dark:text-slate-300" x-text="formData.is_active ? 'Akun Aktif' : 'Akun Nonaktif'"></span>
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
    function userManager() {
        return {
            filter: {
                search: '<?= addslashes($search) ?>',
                role: '<?= $role_filter ?>',
                show_deleted: <?= $show_deleted ? 'true' : 'false' ?>
            },
            showFilter: <?= $is_filter_active ? 'true' : 'false' ?>,
            
            get activeFilterCount() {
                let count = 0;
                if (this.filter.search) count++;
                if (this.filter.role) count++;
                if (this.filter.show_deleted) count++;
                return count;
            },

            applyFilters() {
                const params = new URLSearchParams();
                if (this.filter.search) params.append('search', this.filter.search);
                if (this.filter.role) params.append('role', this.filter.role);
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
                const savedSelection = localStorage.getItem('users_selected_ids');
                if (savedSelection) {
                    try {
                        this.selectedItems = JSON.parse(savedSelection);
                        this.$nextTick(() => { this.updateSelectionState(); });
                    } catch(e) { console.error('Error parsing selection', e); }
                }
            },

            updateSelectionState() {
                localStorage.setItem('users_selected_ids', JSON.stringify(this.selectedItems));

                if (this.selectedItems.length === 0) {
                    this.selectionMode = null;
                    this.allSelected = false;
                    return;
                }

                const checkedInput = document.querySelector('input[type="checkbox"][data-status]:checked');
                
                if (checkedInput) {
                    this.selectionMode = checkedInput.getAttribute('data-status');
                } else if(this.selectionMode === null && this.selectedItems.length > 0) {
                    // Logic retain mode
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
                        if (!el.disabled && !this.selectedItems.includes(el.value)) {
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
                localStorage.removeItem('users_selected_ids');
            },

            getCurrentPageMode() {
                const firstInput = document.querySelector('input[type="checkbox"][data-status]');
                return firstInput ? firstInput.getAttribute('data-status') : null;
            },

            // Modal Logic
            modalOpen: false,
            modalMode: 'create',
            formData: { id: '', username: '', email: '', nama_lengkap: '', no_telepon: '', role: 'penyewa', is_active: true },

            openModal(mode, data = null) {
                this.modalMode = mode;
                if (mode === 'update' && data) {
                    this.formData = { ...data, is_active: data.is_active == 1 };
                } else {
                    this.formData = { id: '', username: '', email: '', nama_lengkap: '', no_telepon: '', role: 'penyewa', is_active: true };
                }
                this.modalOpen = true;
            },
            closeModal() { this.modalOpen = false; },

            // SweetAlerts
            confirmDelete(id) {
                Swal.fire({
                    title: 'Pindahkan ke Sampah?',
                    text: "User ini tidak akan bisa login lagi.",
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
                    title: 'Pulihkan User?',
                    text: "Akun akan kembali aktif.",
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
                    title: 'Hapus ' + this.selectedItems.length + ' User?',
                    text: "User yang dipilih akan dipindahkan ke sampah.",
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
                    title: 'Pulihkan ' + this.selectedItems.length + ' User?',
                    text: "User yang dipilih akan kembali aktif.",
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