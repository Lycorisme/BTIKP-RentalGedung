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
        'tersedia' => 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400',
        'maintenance' => 'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400',
        'full_booked' => 'bg-rose-100 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400',
    ];
    return $colors[$status] ?? 'bg-gray-100 text-gray-600';
}

function uploadFotoGedung($file) {
    $target_dir = "../../../uploads/gedung/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);
    
    $file_extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $new_filename = uniqid('gedung_') . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    $allowed_types = ['jpg', 'jpeg', 'png', 'webp'];
    
    if (!in_array($file_extension, $allowed_types)) return ['status' => false, 'msg' => 'Format file tidak valid (Gunakan JPG, PNG, WEBP).'];
    if ($file["size"] > 5000000) return ['status' => false, 'msg' => 'Ukuran file terlalu besar (Max 5MB).'];
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return ['status' => true, 'filename' => 'uploads/gedung/' . $new_filename];
    }
    return ['status' => false, 'msg' => 'Gagal mengupload file ke server.'];
}

// --- 2. Logic Handler (POST Requests) ---
$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Cek Crash Post Max Size
    if (empty($_POST) && empty($_FILES) && $_SERVER['CONTENT_LENGTH'] > 0) {
        $error_msg = "Ukuran file yang diupload melebihi batas server (post_max_size). Silakan upload foto yang lebih kecil.";
    } else {
        try {
            // A. Create & Update
            if (isset($_POST['action']) && ($_POST['action'] === 'create' || $_POST['action'] === 'update')) {
                // Validasi Input Dasar
                if (empty($_POST['nama']) || empty($_POST['harga'])) {
                    throw new Exception("Nama Gedung dan Harga wajib diisi.");
                }

                $nama = trim($_POST['nama']);
                $deskripsi = trim($_POST['deskripsi']);
                $harga = str_replace(['Rp', '.', ' '], '', $_POST['harga']); // Bersihkan format rupiah
                $luas = (int)$_POST['luas'];
                $kapasitas = (int)$_POST['kapasitas'];
                $alamat = trim($_POST['alamat']);
                $status = $_POST['status'];
                $user_id = $_SESSION['user_id'] ?? 1;

                // Handle Foto
                $foto_path = $_POST['old_foto'] ?? null;
                if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                    $upload = uploadFotoGedung($_FILES['foto']);
                    if ($upload['status']) {
                        $foto_path = $upload['filename'];
                        // Hapus foto lama jika update dan sukses upload baru
                        if (!empty($_POST['old_foto']) && file_exists("../../../" . $_POST['old_foto'])) {
                            unlink("../../../" . $_POST['old_foto']);
                        }
                    } else {
                        throw new Exception($upload['msg']);
                    }
                }

                if ($_POST['action'] === 'create') {
                    $stmt = $conn->prepare("INSERT INTO gedung (nama, deskripsi, harga_per_hari, luas_m2, kapasitas_orang, alamat_lengkap, status, foto_utama, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    if ($stmt->execute([$nama, $deskripsi, $harga, $luas, $kapasitas, $alamat, $status, $foto_path, $user_id])) {
                        $success_msg = "Gedung berhasil ditambahkan.";
                    } else {
                        throw new Exception("Gagal menyimpan data ke database.");
                    }
                } else {
                    $id = (int)$_POST['id'];
                    $stmt = $conn->prepare("UPDATE gedung SET nama=?, deskripsi=?, harga_per_hari=?, luas_m2=?, kapasitas_orang=?, alamat_lengkap=?, status=?, foto_utama=? WHERE id=?");
                    if ($stmt->execute([$nama, $deskripsi, $harga, $luas, $kapasitas, $alamat, $status, $foto_path, $id])) {
                        $success_msg = "Data gedung berhasil diperbarui.";
                    } else {
                        throw new Exception("Gagal memperbarui data database.");
                    }
                }
            }

            // B. Single Actions
            if (isset($_POST['action']) && $_POST['action'] === 'soft_delete') {
                $id = (int)$_POST['id'];
                $stmt = $conn->prepare("UPDATE gedung SET deleted_at = NOW() WHERE id = ?");
                if ($stmt->execute([$id])) $success_msg = "Gedung dipindahkan ke sampah.";
            }

            if (isset($_POST['action']) && $_POST['action'] === 'restore') {
                $id = (int)$_POST['id'];
                $stmt = $conn->prepare("UPDATE gedung SET deleted_at = NULL WHERE id = ?");
                if ($stmt->execute([$id])) $success_msg = "Gedung berhasil dipulihkan.";
            }

            // C. Bulk Actions
            if (isset($_POST['action']) && $_POST['action'] === 'bulk_soft_delete') {
                $ids = explode(',', $_POST['ids']);
                $ids = array_filter(array_map('intval', $ids));
                if (!empty($ids)) {
                    $placeholders = implode(',', array_fill(0, count($ids), '?'));
                    $stmt = $conn->prepare("UPDATE gedung SET deleted_at = NOW() WHERE id IN ($placeholders)");
                    if ($stmt->execute($ids)) $success_msg = count($ids) . " gedung dipindahkan ke sampah.";
                }
            }

            if (isset($_POST['action']) && $_POST['action'] === 'bulk_restore') {
                $ids = explode(',', $_POST['ids']);
                $ids = array_filter(array_map('intval', $ids));
                if (!empty($ids)) {
                    $placeholders = implode(',', array_fill(0, count($ids), '?'));
                    $stmt = $conn->prepare("UPDATE gedung SET deleted_at = NULL WHERE id IN ($placeholders)");
                    if ($stmt->execute($ids)) $success_msg = count($ids) . " gedung berhasil dipulihkan.";
                }
            }

        } catch (Exception $e) {
            $error_msg = "Error: " . $e->getMessage();
        }
    }
}

// --- 3. Filter & Pagination Logic ---
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$show_deleted = isset($_GET['show_deleted']) && $_GET['show_deleted'] == '1';

// Menentukan apakah filter harus terbuka (termasuk jika show_deleted aktif)
$is_filter_active = !empty($search) || !empty($status_filter) || $show_deleted;

$where_clauses = [];
$params = [];

if (!empty($search)) {
    $where_clauses[] = "(nama LIKE ? OR alamat_lengkap LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param; $params[] = $search_param;
}

if (!empty($status_filter)) {
    $where_clauses[] = "status = ?";
    $params[] = $status_filter;
}

if (!$show_deleted) {
    $where_clauses[] = "deleted_at IS NULL";
}

$where_sql = count($where_clauses) > 0 ? "WHERE " . implode(" AND ", $where_clauses) : "";

$count_query = "SELECT COUNT(*) FROM gedung $where_sql";
$stmt = $conn->prepare($count_query);
$stmt->execute($params);
$total_rows = $stmt->fetchColumn();
$total_pages = ceil($total_rows / $limit);

$query = "SELECT * FROM gedung $where_sql ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$stmt = $conn->prepare($query);
$stmt->execute($params);
$gedungs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buat URL Base untuk Pagination
$query_params = $_GET;
unset($query_params['page']); // Hapus page saat ini agar bisa diganti
$base_url = '?' . http_build_query($query_params);
$pagination_url = empty($query_params) ? '?page=' : $base_url . '&page=';

require_once __DIR__ . '/../../../includes/admin/header_admin.php';
?>

<div x-data="gedungManager()" x-init="initData()" class="p-6 max-w-[1600px] mx-auto pb-24 relative">
    
    <?php if($success_msg): ?>
    <script>
        Swal.fire({ icon: 'success', title: 'Berhasil', text: '<?= $success_msg ?>', timer: 1500, showConfirmButton: false });
        // Clear local storage on success action
        localStorage.removeItem('gedung_selected_ids');
    </script>
    <?php endif; ?>
    <?php if($error_msg): ?>
    <script>Swal.fire({ icon: 'error', title: 'Gagal', text: '<?= $error_msg ?>' });</script>
    <?php endif; ?>

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Data Gedung</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Manajemen daftar gedung dan aula yang disewakan.</p>
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
                <span class="hidden sm:inline">Tambah Gedung</span>
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
                    <input type="text" x-model="filter.search" @keyup.enter="applyFilters()" placeholder="Nama gedung / alamat..." 
                           class="w-full pl-10 pr-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-sm focus:ring-2 focus:ring-primary/20 outline-none transition-all dark:text-white">
                </div>
            </div>

            <div class="col-span-1">
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1 uppercase">Status</label>
                <select x-model="filter.status" class="w-full px-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-sm focus:ring-2 focus:ring-primary/20 outline-none transition-all dark:text-white appearance-none">
                    <option value="">Semua Status</option>
                    <option value="tersedia">Tersedia</option>
                    <option value="maintenance">Maintenance</option>
                    <option value="full_booked">Full Booked</option>
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
                        <th class="p-4">Foto</th>
                        <th class="p-4">Nama Gedung</th>
                        <th class="p-4">Harga / Hari</th>
                        <th class="p-4 hidden md:table-cell">Kapasitas</th>
                        <th class="p-4 text-center">Status</th>
                        <th class="p-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700 text-sm">
                    <?php if (count($gedungs) > 0): ?>
                        <?php foreach($gedungs as $index => $row): ?>
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
                            <td class="p-4">
                                <?php if($row['foto_utama']): ?>
                                    <img src="/situs-rental-gedung/<?= htmlspecialchars($row['foto_utama']) ?>" alt="Foto" class="h-10 w-16 object-cover rounded-lg shadow-sm border border-slate-200 dark:border-slate-600">
                                <?php else: ?>
                                    <div class="h-10 w-16 bg-slate-200 dark:bg-slate-700 rounded-lg flex items-center justify-center text-slate-400">
                                        <i class="fa-regular fa-image"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="p-4 font-semibold">
                                <div class="truncate max-w-[200px]" title="<?= htmlspecialchars($row['nama']) ?>">
                                    <?= htmlspecialchars($row['nama']) ?>
                                </div>
                                <?php if($is_deleted): ?>
                                    <span class="text-[10px] bg-red-100 text-red-600 px-1.5 py-0.5 rounded border border-red-200">DELETED</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4 font-mono text-slate-500 dark:text-slate-400">
                                <?= formatRupiah($row['harga_per_hari']) ?>
                            </td>
                            <td class="p-4 hidden md:table-cell">
                                <i class="fa-solid fa-users text-slate-400 mr-1"></i> <?= number_format($row['kapasitas_orang']) ?>
                            </td>
                            <td class="p-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= statusBadge($row['status']) ?>">
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
                            <td colspan="8" class="p-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="h-16 w-16 bg-slate-100 dark:bg-slate-700 rounded-full flex items-center justify-center mb-4">
                                        <i class="fa-regular fa-building text-3xl"></i>
                                    </div>
                                    <p class="font-medium">Tidak ada data gedung</p>
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
                Menampilkan <span class="font-bold text-slate-700 dark:text-slate-200"><?= count($gedungs) ?></span> dari <span class="font-bold text-slate-700 dark:text-slate-200"><?= $total_rows ?></span> data
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
                <h3 class="font-bold text-lg text-slate-800 dark:text-white" x-text="modalMode === 'create' ? 'Tambah Gedung Baru' : 'Edit Data Gedung'"></h3>
                <button @click="closeModal" class="text-slate-400 hover:text-rose-500 transition-colors">
                    <i class="fa-solid fa-times text-xl"></i>
                </button>
            </div>
            
            <form method="POST" enctype="multipart/form-data" class="flex-1 overflow-y-auto p-6">
                <input type="hidden" name="action" :value="modalMode">
                <input type="hidden" name="id" :value="formData.id">
                <input type="hidden" name="old_foto" :value="formData.foto_utama">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2 mb-2 flex justify-center">
                        <div class="relative group cursor-pointer w-full" @click="$refs.fileInput.click()">
                            <div class="h-48 w-full bg-slate-100 dark:bg-slate-900 border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-xl flex flex-col items-center justify-center overflow-hidden hover:border-primary hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
                                <template x-if="imagePreview">
                                    <img :src="imagePreview" class="h-full w-full object-cover">
                                </template>
                                <template x-if="!imagePreview">
                                    <div class="text-center p-4">
                                        <i class="fa-solid fa-cloud-arrow-up text-3xl text-slate-400 mb-2"></i>
                                        <p class="text-sm text-slate-500">Klik untuk upload foto utama</p>
                                    </div>
                                </template>
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
                                    <span class="text-white font-medium text-sm"><i class="fa-solid fa-pen mr-2"></i>Ganti Foto</span>
                                </div>
                            </div>
                            <input x-ref="fileInput" type="file" name="foto" class="hidden" accept="image/*" @change="previewImage">
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Nama Gedung <span class="text-red-500">*</span></label>
                        <input type="text" name="nama" x-model="formData.nama" required class="w-full px-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 focus:ring-2 focus:ring-primary/20 outline-none dark:text-white">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Harga / Hari <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-4 top-2 text-slate-400">Rp</span>
                            <input type="number" name="harga" x-model="formData.harga_per_hari" required class="w-full pl-10 pr-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 focus:ring-2 focus:ring-primary/20 outline-none dark:text-white">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Status</label>
                        <select name="status" x-model="formData.status" class="w-full px-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 focus:ring-2 focus:ring-primary/20 outline-none dark:text-white">
                            <option value="tersedia">Tersedia</option>
                            <option value="maintenance">Maintenance</option>
                            <option value="full_booked">Full Booked</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Kapasitas (Orang)</label>
                        <input type="number" name="kapasitas" x-model="formData.kapasitas_orang" class="w-full px-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 focus:ring-2 focus:ring-primary/20 outline-none dark:text-white">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Luas (m²)</label>
                        <input type="number" name="luas" x-model="formData.luas_m2" class="w-full px-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 focus:ring-2 focus:ring-primary/20 outline-none dark:text-white">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Alamat Lengkap</label>
                        <textarea name="alamat" x-model="formData.alamat_lengkap" rows="2" class="w-full px-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 focus:ring-2 focus:ring-primary/20 outline-none dark:text-white resize-none"></textarea>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Deskripsi & Fasilitas</label>
                        <textarea name="deskripsi" x-model="formData.deskripsi" rows="3" class="w-full px-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 focus:ring-2 focus:ring-primary/20 outline-none dark:text-white resize-none"></textarea>
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
    function gedungManager() {
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
                if (this.filter.status) count++;
                if (this.filter.show_deleted) count++;
                return count;
            },

            // --- Logic Filter SPA ---
            applyFilters() {
                const params = new URLSearchParams();
                if (this.filter.search) params.append('search', this.filter.search);
                if (this.filter.status) params.append('status', this.filter.status);
                if (this.filter.show_deleted) params.append('show_deleted', '1');
                
                // Reset page ke 1 saat filter berubah
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
            selectionMode: null, // 'active' atau 'deleted' atau null

            initData() {
                // Load selection from local storage
                const savedSelection = localStorage.getItem('gedung_selected_ids');
                if (savedSelection) {
                    try {
                        this.selectedItems = JSON.parse(savedSelection);
                        // Trigger update state to calculate mode immediately
                        this.$nextTick(() => {
                           this.updateSelectionState(); 
                        });
                    } catch(e) { console.error('Error parsing selection', e); }
                }
            },

            // Fungsi utama untuk menentukan mode berdasarkan item yang dipilih
            updateSelectionState() {
                // Simpan ke local storage
                localStorage.setItem('gedung_selected_ids', JSON.stringify(this.selectedItems));

                if (this.selectedItems.length === 0) {
                    this.selectionMode = null;
                    this.allSelected = false;
                    return;
                }

                // Cari status item PERTAMA yang dipilih untuk menentukan mode
                // Kita cari input di DOM yang valuenya ada di selectedItems
                // Karena kita menggunakan pagination, kita hanya bisa mendeteksi mode 
                // dari item yang terlihat di halaman ini.
                
                // Cari elemen input yang dicentang di halaman ini
                const checkedInput = document.querySelector('input[type="checkbox"][data-status]:checked');
                
                if (checkedInput) {
                    this.selectionMode = checkedInput.getAttribute('data-status');
                } else if(this.selectionMode === null && this.selectedItems.length > 0) {
                    // Jika ada items di storage tapi tidak ada di page ini (beda page),
                    // Kita pertahankan mode sebelumnya atau perlu logika fetch (skip for simple impl).
                    // Asumsi: user berinteraksi di halaman ini.
                }
            },

            toggleAll() {
                // Jika sedang dalam mode tertentu, toggleAll hanya mempengaruhi item yang sesuai mode
                // Jika belum ada mode, kita tentukan mode berdasarkan item pertama yang available di table
                
                let targetStatus = this.selectionMode;
                
                // Jika belum ada mode, ambil status dari baris pertama yang terlihat
                if (!targetStatus) {
                    const firstInput = document.querySelector('input[type="checkbox"][data-status]');
                    if (firstInput) targetStatus = firstInput.getAttribute('data-status');
                }

                if (!targetStatus) return; // Tabel kosong

                this.allSelected = !this.allSelected;
                
                const checkboxes = document.querySelectorAll(`input[type="checkbox"][data-status="${targetStatus}"]`);
                
                if (this.allSelected) {
                    // Tambahkan semua yang sesuai status ke array (jika belum ada)
                    checkboxes.forEach(el => {
                        if (!this.selectedItems.includes(el.value)) {
                            this.selectedItems.push(el.value);
                        }
                    });
                    this.selectionMode = targetStatus;
                } else {
                    // Hapus yang ada di halaman ini dari array
                    checkboxes.forEach(el => {
                        this.selectedItems = this.selectedItems.filter(id => id !== el.value);
                    });
                    // Jika array kosong, reset mode
                    if(this.selectedItems.length === 0) this.selectionMode = null;
                }
                
                this.updateSelectionState();
            },

            cancelSelection() {
                this.selectedItems = [];
                this.selectionMode = null;
                this.allSelected = false;
                localStorage.removeItem('gedung_selected_ids');
            },

            getCurrentPageMode() {
                // Helper untuk disable checkbox 'Select All' jika statusnya mixed di satu halaman (jarang terjadi jika difilter benar)
                // Tapi berguna jika filter menampilkan SEMUA.
                const firstInput = document.querySelector('input[type="checkbox"][data-status]');
                return firstInput ? firstInput.getAttribute('data-status') : null;
            },

            // Modal Logic
            modalOpen: false,
            modalMode: 'create',
            imagePreview: null,
            formData: { id: '', nama: '', harga_per_hari: '', luas_m2: '', kapasitas_orang: '', alamat_lengkap: '', deskripsi: '', status: 'tersedia', foto_utama: '' },

            openModal(mode, data = null) {
                this.modalMode = mode;
                this.imagePreview = null;
                if (mode === 'update' && data) {
                    this.formData = { ...data };
                    if (this.formData.foto_utama) this.imagePreview = '/situs-rental-gedung/' + this.formData.foto_utama;
                } else {
                    this.formData = { id: '', nama: '', harga_per_hari: '', luas_m2: '', kapasitas_orang: '', alamat_lengkap: '', deskripsi: '', status: 'tersedia', foto_utama: '' };
                }
                this.modalOpen = true;
            },
            closeModal() { this.modalOpen = false; },
            previewImage(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = (e) => { this.imagePreview = e.target.result; };
                    reader.readAsDataURL(file);
                }
            },

            // SweetAlerts Actions (Standard Form Submit)
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