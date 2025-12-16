<?php
require_once __DIR__ . '/../../../includes/admin/header_admin.php';
require_once __DIR__ . '/../../../modules/crud.php';

$success = '';
$error = '';

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $gedung = read_by_id('gedung', $id);
    
    if ($gedung) {
        // Delete photo if exists
        if (!empty($gedung['foto_utama']) && file_exists('../../../' . $gedung['foto_utama'])) {
            unlink('../../../' . $gedung['foto_utama']);
        }
        
        $result = delete('gedung', $id);
        if ($result['success']) {
            $success = 'Gedung berhasil dihapus!';
        } else {
            $error = $result['message'];
        }
    }
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $nama = sanitize($_POST['nama']);
    $deskripsi = sanitize($_POST['deskripsi']);
    $harga_per_hari = (float)$_POST['harga_per_hari'];
    $luas_m2 = !empty($_POST['luas_m2']) ? (int)$_POST['luas_m2'] : null;
    $kapasitas_orang = !empty($_POST['kapasitas_orang']) ? (int)$_POST['kapasitas_orang'] : null;
    $alamat_lengkap = sanitize($_POST['alamat_lengkap']);
    $status = $_POST['status'];
    
    $data = [
        'nama' => $nama,
        'deskripsi' => $deskripsi,
        'harga_per_hari' => $harga_per_hari,
        'luas_m2' => $luas_m2,
        'kapasitas_orang' => $kapasitas_orang,
        'alamat_lengkap' => $alamat_lengkap,
        'status' => $status
    ];
    
    // Handle file upload
    if (isset($_FILES['foto_utama']) && $_FILES['foto_utama']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../../../uploads/gedung/';
        $upload = upload_file('foto_utama', $upload_dir, ['jpg', 'jpeg', 'png', 'gif'], 5242880);
        
        if ($upload['success']) {
            $data['foto_utama'] = 'uploads/gedung/' . $upload['filename'];
            
            // Delete old photo if updating
            if ($id > 0) {
                $old_gedung = read_by_id('gedung', $id);
                if ($old_gedung && !empty($old_gedung['foto_utama'])) {
                    $old_path = __DIR__ . '/../../../' . $old_gedung['foto_utama'];
                    if (file_exists($old_path)) {
                        unlink($old_path);
                    }
                }
            }
        }
    }
    
    if ($id > 0) {
        // Update
        $result = update('gedung', $id, $data);
        if ($result['success']) {
            $success = 'Gedung berhasil diupdate!';
        } else {
            $error = $result['message'];
        }
    } else {
        // Insert
        $data['created_by'] = getUserId();
        $result = create('gedung', $data);
        if ($result['success']) {
            $success = 'Gedung berhasil ditambahkan!';
        } else {
            $error = $result['message'];
        }
    }
}

// Get data with pagination and search
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

if (!empty($search)) {
    $gedung_list = multi_search('gedung', ['nama', 'deskripsi', 'alamat_lengkap'], $search, 100);
    $total_gedung = count($gedung_list);
    $pagination = [
        'data' => $gedung_list,
        'total' => $total_gedung,
        'current_page' => 1,
        'total_pages' => 1
    ];
} else {
    $where = [];
    if (!empty($status_filter)) {
        $where['status'] = $status_filter;
    }
    $pagination = paginate('gedung', $where, $page, 10);
}

// Get statistics
$total_gedung = count_records('gedung');
$tersedia = count_records('gedung', ['status' => 'tersedia']);
$maintenance = count_records('gedung', ['status' => 'maintenance']);
$full_booked = count_records('gedung', ['status' => 'full_booked']);
?>

<!-- Page Header -->
<div class="mb-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-slate-800 mb-2">Manajemen Gedung</h1>
            <p class="text-slate-600">Kelola data gedung yang tersedia untuk disewakan</p>
        </div>
        <button onclick="openModal()" 
            class="px-6 py-3 rounded-xl bg-gradient-to-r from-primary-500 to-secondary-500 text-white font-semibold shadow-lg shadow-primary-500/30 hover:shadow-xl hover:translate-y-[-2px] transition-all">
            <i class="fa-solid fa-plus mr-2"></i>Tambah Gedung
        </button>
    </div>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl p-6 text-white shadow-lg">
        <div class="flex items-center justify-between mb-4">
            <div class="h-12 w-12 rounded-xl bg-white/20 flex items-center justify-center">
                <i class="fa-solid fa-building text-2xl"></i>
            </div>
            <span class="text-3xl font-bold"><?= $total_gedung ?></span>
        </div>
        <p class="text-blue-100">Total Gedung</p>
    </div>
    
    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-2xl p-6 text-white shadow-lg">
        <div class="flex items-center justify-between mb-4">
            <div class="h-12 w-12 rounded-xl bg-white/20 flex items-center justify-center">
                <i class="fa-solid fa-check-circle text-2xl"></i>
            </div>
            <span class="text-3xl font-bold"><?= $tersedia ?></span>
        </div>
        <p class="text-green-100">Tersedia</p>
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
    
    <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-2xl p-6 text-white shadow-lg">
        <div class="flex items-center justify-between mb-4">
            <div class="h-12 w-12 rounded-xl bg-white/20 flex items-center justify-center">
                <i class="fa-solid fa-calendar-xmark text-2xl"></i>
            </div>
            <span class="text-3xl font-bold"><?= $full_booked ?></span>
        </div>
        <p class="text-red-100">Full Booked</p>
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
                    placeholder="Cari nama gedung, deskripsi, atau alamat...">
            </div>
        </div>
        <select name="status" class="px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-primary-500 focus:outline-none transition-all">
            <option value="">Semua Status</option>
            <option value="tersedia" <?= $status_filter === 'tersedia' ? 'selected' : '' ?>>Tersedia</option>
            <option value="maintenance" <?= $status_filter === 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
            <option value="full_booked" <?= $status_filter === 'full_booked' ? 'selected' : '' ?>>Full Booked</option>
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
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-700 uppercase tracking-wider">No</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-700 uppercase tracking-wider">Foto</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-700 uppercase tracking-wider">Nama Gedung</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-700 uppercase tracking-wider">Harga/Hari</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-700 uppercase tracking-wider">Kapasitas</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-700 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 text-center text-xs font-bold text-slate-700 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                <?php if (empty($pagination['data'])): ?>
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                        <i class="fa-solid fa-inbox text-4xl mb-3 block text-slate-300"></i>
                        Tidak ada data gedung
                    </td>
                </tr>
                <?php else: ?>
                    <?php 
                    $no = ($pagination['current_page'] - 1) * 10 + 1;
                    foreach ($pagination['data'] as $gedung): 
                    ?>
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 text-sm text-slate-700"><?= $no++ ?></td>
                        <td class="px-6 py-4">
                            <?php if (!empty($gedung['foto_utama'])): ?>
                            <img src="/situs-rental-gedung/<?= htmlspecialchars($gedung['foto_utama']) ?>" 
                                alt="<?= htmlspecialchars($gedung['nama']) ?>"
                                class="h-16 w-24 object-cover rounded-lg border-2 border-slate-200"
                                onerror="this.src='https://via.placeholder.com/200x150?text=No+Image'">
                            <?php else: ?>
                            <div class="h-16 w-24 bg-slate-200 rounded-lg flex items-center justify-center">
                                <i class="fa-solid fa-image text-slate-400"></i>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-semibold text-slate-800"><?= htmlspecialchars($gedung['nama']) ?></div>
                            <div class="text-sm text-slate-500 line-clamp-1"><?= htmlspecialchars($gedung['deskripsi']) ?></div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-primary-600">Rp <?= number_format($gedung['harga_per_hari'], 0, ',', '.') ?></div>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-700">
                            <?php if ($gedung['kapasitas_orang']): ?>
                            <div><i class="fa-solid fa-users mr-1 text-primary-500"></i><?= $gedung['kapasitas_orang'] ?> orang</div>
                            <?php endif; ?>
                            <?php if ($gedung['luas_m2']): ?>
                            <div><i class="fa-solid fa-ruler-combined mr-1 text-primary-500"></i><?= $gedung['luas_m2'] ?> m²</div>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <?php
                            $badge_class = [
                                'tersedia' => 'bg-success-100 text-success-700',
                                'maintenance' => 'bg-warning-100 text-warning-700',
                                'full_booked' => 'bg-danger-100 text-danger-700'
                            ];
                            $badge_text = [
                                'tersedia' => 'Tersedia',
                                'maintenance' => 'Maintenance',
                                'full_booked' => 'Full Booked'
                            ];
                            ?>
                            <span class="px-3 py-1 rounded-lg text-xs font-bold <?= $badge_class[$gedung['status']] ?? 'bg-slate-100 text-slate-700' ?>">
                                <?= $badge_text[$gedung['status']] ?? $gedung['status'] ?>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2">
                                <button onclick='editGedung(<?= json_encode($gedung) ?>)' 
                                    class="p-2 rounded-lg bg-blue-100 hover:bg-blue-200 text-blue-600 transition-colors"
                                    title="Edit">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <button onclick="confirmDelete('?delete=<?= $gedung['id'] ?>')" 
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
    <?php if ($pagination['total_pages'] > 1): ?>
    <div class="px-6 py-4 border-t border-slate-200 flex items-center justify-between">
        <div class="text-sm text-slate-600">
            Menampilkan <?= ($pagination['current_page'] - 1) * 10 + 1 ?> - 
            <?= min($pagination['current_page'] * 10, $pagination['total']) ?> 
            dari <?= $pagination['total'] ?> data
        </div>
        <div class="flex gap-2">
            <?php if ($pagination['has_prev']): ?>
            <a href="?page=<?= $pagination['current_page'] - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($status_filter) ? '&status=' . $status_filter : '' ?>" 
                class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold transition-all">
                <i class="fa-solid fa-chevron-left"></i>
            </a>
            <?php endif; ?>
            
            <?php for ($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['total_pages'], $pagination['current_page'] + 2); $i++): ?>
            <a href="?page=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($status_filter) ? '&status=' . $status_filter : '' ?>" 
                class="px-4 py-2 rounded-lg <?= $i === $pagination['current_page'] ? 'bg-primary-500 text-white' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' ?> font-semibold transition-all">
                <?= $i ?>
            </a>
            <?php endfor; ?>
            
            <?php if ($pagination['has_next']): ?>
            <a href="?page=<?= $pagination['current_page'] + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?><?= !empty($status_filter) ? '&status=' . $status_filter : '' ?>" 
                class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold transition-all">
                <i class="fa-solid fa-chevron-right"></i>
            </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Add/Edit Modal -->
<div id="gedungModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b border-slate-200 px-8 py-6 rounded-t-3xl">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold text-slate-800">
                    <i class="fa-solid fa-building mr-3 text-primary-500"></i>
                    <span id="modalTitle">Tambah Gedung</span>
                </h2>
                <button onclick="closeModal()" class="p-2 rounded-xl hover:bg-slate-100 transition-colors">
                    <i class="fa-solid fa-xmark text-2xl text-slate-400"></i>
                </button>
            </div>
        </div>
        
        <form method="POST" enctype="multipart/form-data" class="p-8 space-y-6">
            <input type="hidden" name="id" id="gedung_id">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nama Gedung -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-slate-700 mb-2">
                        Nama Gedung <span class="text-danger-500">*</span>
                    </label>
                    <input type="text" name="nama" id="gedung_nama" required 
                        class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-primary-500 focus:outline-none transition-all"
                        placeholder="Contoh: Ruang Rapat VIP">
                </div>
                
                <!-- Harga -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">
                        Harga per Hari <span class="text-danger-500">*</span>
                    </label>
                    <input type="number" name="harga_per_hari" id="gedung_harga" required min="0" step="1000"
                        class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-primary-500 focus:outline-none transition-all"
                        placeholder="500000">
                </div>
                
                <!-- Status -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">
                        Status <span class="text-danger-500">*</span>
                    </label>
                    <select name="status" id="gedung_status" required 
                        class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-primary-500 focus:outline-none transition-all">
                        <option value="tersedia">Tersedia</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="full_booked">Full Booked</option>
                    </select>
                </div>
                
                <!-- Luas -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">
                        Luas (m²)
                    </label>
                    <input type="number" name="luas_m2" id="gedung_luas" min="0"
                        class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-primary-500 focus:outline-none transition-all"
                        placeholder="100">
                </div>
                
                <!-- Kapasitas -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">
                        Kapasitas (orang)
                    </label>
                    <input type="number" name="kapasitas_orang" id="gedung_kapasitas" min="0"
                        class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-primary-500 focus:outline-none transition-all"
                        placeholder="50">
                </div>
                
                <!-- Foto -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-slate-700 mb-2">
                        Foto Gedung
                    </label>
                    <input type="file" name="foto_utama" accept="image/*" 
                        class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-primary-500 focus:outline-none transition-all">
                    <p class="text-xs text-slate-500 mt-1">Format: JPG, PNG, GIF (Max 5MB)</p>
                </div>
                
                <!-- Alamat -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-slate-700 mb-2">
                        Alamat Lengkap
                    </label>
                    <textarea name="alamat_lengkap" id="gedung_alamat" rows="2"
                        class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-primary-500 focus:outline-none transition-all"
                        placeholder="Jl. Contoh No. 123, Kota"></textarea>
                </div>
                
                <!-- Deskripsi -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-slate-700 mb-2">
                        Deskripsi
                    </label>
                    <textarea name="deskripsi" id="gedung_deskripsi" rows="4"
                        class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-primary-500 focus:outline-none transition-all"
                        placeholder="Deskripsi lengkap tentang gedung..."></textarea>
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
    document.getElementById('modalTitle').textContent = 'Tambah Gedung';
    document.getElementById('gedungModal').classList.remove('hidden');
    document.getElementById('gedung_id').value = '';
    document.querySelector('form').reset();
}

function closeModal() {
    document.getElementById('gedungModal').classList.add('hidden');
}

function editGedung(gedung) {
    document.getElementById('modalTitle').textContent = 'Edit Gedung';
    document.getElementById('gedungModal').classList.remove('hidden');
    
    document.getElementById('gedung_id').value = gedung.id;
    document.getElementById('gedung_nama').value = gedung.nama;
    document.getElementById('gedung_deskripsi').value = gedung.deskripsi || '';
    document.getElementById('gedung_harga').value = gedung.harga_per_hari;
    document.getElementById('gedung_luas').value = gedung.luas_m2 || '';
    document.getElementById('gedung_kapasitas').value = gedung.kapasitas_orang || '';
    document.getElementById('gedung_alamat').value = gedung.alamat_lengkap || '';
    document.getElementById('gedung_status').value = gedung.status;
}

// Close modal on outside click
document.getElementById('gedungModal').addEventListener('click', function(e) {
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