<?php
require_once __DIR__ . '/../../../includes/admin/header_admin.php';
require_once __DIR__ . '/../../../modules/crud.php';

$success = '';
$error = '';

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $result = delete('pelanggan', $id);
    if ($result['success']) {
        $success = 'Pelanggan berhasil dihapus!';
    } else {
        $error = $result['message'];
    }
}

// Handle Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['id'];
    $no_ktp = sanitize($_POST['no_ktp']);
    $alamat = sanitize($_POST['alamat']);
    $pekerjaan = sanitize($_POST['pekerjaan']);
    $perusahaan = sanitize($_POST['perusahaan']);
    
    $data = [
        'no_ktp' => $no_ktp,
        'alamat' => $alamat,
        'pekerjaan' => $pekerjaan,
        'perusahaan' => $perusahaan
    ];
    
    $result = update('pelanggan', $id, $data);
    if ($result['success']) {
        $success = 'Data pelanggan berhasil diupdate!';
    } else {
        $error = $result['message'];
    }
}

// Get data
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Custom query to join with users
$sql = "SELECT p.*, u.username, u.email, u.nama_lengkap, u.no_telepon, u.is_active, u.created_at as user_created
        FROM pelanggan p
        JOIN users u ON p.user_id = u.id";

if (!empty($search)) {
    $sql .= " WHERE (u.nama_lengkap LIKE ? OR u.email LIKE ? OR p.no_ktp LIKE ?)";
}

$sql .= " ORDER BY p.created_at DESC LIMIT 10 OFFSET ?";

$offset = ($page - 1) * 10;
$params = [];

if (!empty($search)) {
    $search_param = "%$search%";
    $params = [$search_param, $search_param, $search_param];
}

$params[] = $offset;

$pelanggan_list = query($sql, $params);

// Get total
$total_pelanggan = count_records('pelanggan');
$total_pages = ceil($total_pelanggan / 10);

// Get booking count for each customer
$booking_counts = [];
foreach ($pelanggan_list as $pelanggan) {
    $booking_counts[$pelanggan['user_id']] = count_records('booking', ['penyewa_id' => $pelanggan['user_id']]);
}
?>

<!-- Page Header -->
<div class="mb-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-slate-800 mb-2">Data Pelanggan</h1>
            <p class="text-slate-600">Kelola informasi pelanggan/penyewa</p>
        </div>
    </div>
</div>

<!-- Statistics Card -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl p-6 text-white shadow-lg">
        <div class="flex items-center justify-between mb-4">
            <div class="h-12 w-12 rounded-xl bg-white/20 flex items-center justify-center">
                <i class="fa-solid fa-users text-2xl"></i>
            </div>
            <span class="text-3xl font-bold"><?= $total_pelanggan ?></span>
        </div>
        <p class="text-blue-100">Total Pelanggan</p>
    </div>
    
    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-2xl p-6 text-white shadow-lg">
        <div class="flex items-center justify-between mb-4">
            <div class="h-12 w-12 rounded-xl bg-white/20 flex items-center justify-center">
                <i class="fa-solid fa-user-check text-2xl"></i>
            </div>
            <span class="text-3xl font-bold"><?= count_records('users', ['role' => 'penyewa', 'is_active' => 1]) ?></span>
        </div>
        <p class="text-green-100">Aktif</p>
    </div>
    
    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl p-6 text-white shadow-lg">
        <div class="flex items-center justify-between mb-4">
            <div class="h-12 w-12 rounded-xl bg-white/20 flex items-center justify-center">
                <i class="fa-solid fa-calendar-check text-2xl"></i>
            </div>
            <span class="text-3xl font-bold"><?= count_records('booking') ?></span>
        </div>
        <p class="text-purple-100">Total Booking</p>
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

<!-- Search -->
<div class="bg-white rounded-2xl shadow-lg border border-slate-200 p-6 mb-6">
    <form method="GET" class="flex gap-4">
        <div class="flex-1">
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 transform -translate-y-1/2 text-slate-400"></i>
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                    class="w-full pl-12 pr-4 py-3 rounded-xl border-2 border-slate-200 focus:border-primary-500 focus:outline-none transition-all"
                    placeholder="Cari nama, email, atau KTP...">
            </div>
        </div>
        <button type="submit" class="px-6 py-3 rounded-xl bg-primary-500 hover:bg-primary-600 text-white font-semibold transition-all">
            <i class="fa-solid fa-search mr-2"></i>Cari
        </button>
        <?php if (!empty($search)): ?>
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
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-700 uppercase tracking-wider">Nama</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-700 uppercase tracking-wider">Kontak</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-700 uppercase tracking-wider">KTP</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-700 uppercase tracking-wider">Pekerjaan</th>
                    <th class="px-6 py-4 text-center text-xs font-bold text-slate-700 uppercase tracking-wider">Booking</th>
                    <th class="px-6 py-4 text-center text-xs font-bold text-slate-700 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 text-center text-xs font-bold text-slate-700 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                <?php if (empty($pelanggan_list)): ?>
                <tr>
                    <td colspan="8" class="px-6 py-12 text-center text-slate-500">
                        <i class="fa-solid fa-inbox text-4xl mb-3 block text-slate-300"></i>
                        Tidak ada data pelanggan
                    </td>
                </tr>
                <?php else: ?>
                    <?php 
                    $no = ($page - 1) * 10 + 1;
                    foreach ($pelanggan_list as $pelanggan): 
                    ?>
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 text-sm text-slate-700"><?= $no++ ?></td>
                        <td class="px-6 py-4">
                            <div class="font-semibold text-slate-800"><?= htmlspecialchars($pelanggan['nama_lengkap']) ?></div>
                            <div class="text-sm text-slate-500">@<?= htmlspecialchars($pelanggan['username']) ?></div>
                            <div class="text-xs text-slate-400">Bergabung: <?= date('d M Y', strtotime($pelanggan['user_created'])) ?></div>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <div class="text-slate-700"><?= htmlspecialchars($pelanggan['email']) ?></div>
                            <?php if (!empty($pelanggan['no_telepon'])): ?>
                            <div class="text-slate-500"><?= htmlspecialchars($pelanggan['no_telepon']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-700">
                            <?= htmlspecialchars($pelanggan['no_ktp'] ?: '-') ?>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <?php if (!empty($pelanggan['pekerjaan'])): ?>
                            <div class="text-slate-700"><?= htmlspecialchars($pelanggan['pekerjaan']) ?></div>
                            <?php if (!empty($pelanggan['perusahaan'])): ?>
                            <div class="text-slate-500 text-xs"><?= htmlspecialchars($pelanggan['perusahaan']) ?></div>
                            <?php endif; ?>
                            <?php else: ?>
                            <span class="text-slate-400">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center justify-center h-8 w-8 rounded-lg bg-primary-100 text-primary-700 font-bold">
                                <?= $booking_counts[$pelanggan['user_id']] ?? 0 ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <?php if ($pelanggan['is_active']): ?>
                            <span class="px-3 py-1 rounded-lg text-xs font-bold bg-success-100 text-success-700">
                                Aktif
                            </span>
                            <?php else: ?>
                            <span class="px-3 py-1 rounded-lg text-xs font-bold bg-slate-100 text-slate-700">
                                Nonaktif
                            </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2">
                                <button onclick='editPelanggan(<?= json_encode($pelanggan) ?>)' 
                                    class="p-2 rounded-lg bg-blue-100 hover:bg-blue-200 text-blue-600 transition-colors"
                                    title="Edit">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <button onclick="confirmDelete('?delete=<?= $pelanggan['id'] ?>')" 
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
            Menampilkan data pelanggan
        </div>
        <div class="flex gap-2">
            <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" 
                class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold transition-all">
                <i class="fa-solid fa-chevron-left"></i>
            </a>
            <?php endif; ?>
            
            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
            <a href="?page=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" 
                class="px-4 py-2 rounded-lg <?= $i === $page ? 'bg-primary-500 text-white' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' ?> font-semibold transition-all">
                <?= $i ?>
            </a>
            <?php endfor; ?>
            
            <?php if ($page < $total_pages): ?>
            <a href="?page=<?= $page + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" 
                class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold transition-all">
                <i class="fa-solid fa-chevron-right"></i>
            </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Edit Modal -->
<div id="pelangganModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-2xl w-full">
        <div class="border-b border-slate-200 px-8 py-6 rounded-t-3xl">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold text-slate-800">
                    <i class="fa-solid fa-user-pen mr-3 text-primary-500"></i>
                    Edit Data Pelanggan
                </h2>
                <button onclick="closeModal()" class="p-2 rounded-xl hover:bg-slate-100 transition-colors">
                    <i class="fa-solid fa-xmark text-2xl text-slate-400"></i>
                </button>
            </div>
        </div>
        
        <form method="POST" class="p-8 space-y-6">
            <input type="hidden" name="id" id="pelanggan_id">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- No KTP -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">
                        No. KTP
                    </label>
                    <input type="text" name="no_ktp" id="pelanggan_ktp" 
                        class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-primary-500 focus:outline-none transition-all"
                        placeholder="1234567890123456">
                </div>
                
                <!-- Pekerjaan -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">
                        Pekerjaan
                    </label>
                    <input type="text" name="pekerjaan" id="pelanggan_pekerjaan" 
                        class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-primary-500 focus:outline-none transition-all"
                        placeholder="Pegawai Swasta">
                </div>
                
                <!-- Perusahaan -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-slate-700 mb-2">
                        Perusahaan
                    </label>
                    <input type="text" name="perusahaan" id="pelanggan_perusahaan" 
                        class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-primary-500 focus:outline-none transition-all"
                        placeholder="PT. Contoh Indonesia">
                </div>
                
                <!-- Alamat -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-slate-700 mb-2">
                        Alamat
                    </label>
                    <textarea name="alamat" id="pelanggan_alamat" rows="3"
                        class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-primary-500 focus:outline-none transition-all"
                        placeholder="Jl. Contoh No. 123, Kota"></textarea>
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
function closeModal() {
    document.getElementById('pelangganModal').classList.add('hidden');
}

function editPelanggan(pelanggan) {
    document.getElementById('pelangganModal').classList.remove('hidden');
    
    document.getElementById('pelanggan_id').value = pelanggan.id;
    document.getElementById('pelanggan_ktp').value = pelanggan.no_ktp || '';
    document.getElementById('pelanggan_pekerjaan').value = pelanggan.pekerjaan || '';
    document.getElementById('pelanggan_perusahaan').value = pelanggan.perusahaan || '';
    document.getElementById('pelanggan_alamat').value = pelanggan.alamat || '';
}

// Close modal on outside click
document.getElementById('pelangganModal').addEventListener('click', function(e) {
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