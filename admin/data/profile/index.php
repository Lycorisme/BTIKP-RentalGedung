<?php
// --- 1. Setup & Konfigurasi ---
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../includes/admin/auth.php';

// Inisialisasi Koneksi
$conn = getDB();

// Cek Login
requireLogin();

// Ambil ID User dari Session
$user_id = $_SESSION['user_id'];

// --- 2. Logic Handler (POST Requests) ---
$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action']) && $_POST['action'] === 'save_changes') {
            
            $conn->beginTransaction(); // Mulai Transaksi Database

            // 1. Update Profile Info
            if (empty($_POST['nama_lengkap']) || empty($_POST['username']) || empty($_POST['email'])) {
                throw new Exception("Nama, Username, dan Email wajib diisi.");
            }

            $nama_lengkap = trim($_POST['nama_lengkap']);
            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            $no_telepon = trim($_POST['no_telepon']);

            // Cek Unik Username/Email (kecuali punya sendiri)
            $check = $conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
            $check->execute([$username, $email, $user_id]);
            if ($check->rowCount() > 0) {
                throw new Exception("Username atau Email sudah digunakan orang lain.");
            }

            $stmt = $conn->prepare("UPDATE users SET nama_lengkap = ?, username = ?, email = ?, no_telepon = ? WHERE id = ?");
            $stmt->execute([$nama_lengkap, $username, $email, $no_telepon, $user_id]);
            
            // Update Session Data
            $_SESSION['nama_lengkap'] = $nama_lengkap;
            $_SESSION['username'] = $username;
            $success_msg = "Profil berhasil diperbarui.";

            // 2. Update Password (Jika diisi)
            if (!empty($_POST['new_password'])) {
                $current_password = $_POST['current_password'];
                $new_password = $_POST['new_password'];
                $confirm_password = $_POST['confirm_password'];

                if (empty($current_password)) {
                    throw new Exception("Masukkan password saat ini untuk mengubah password.");
                }

                // Ambil password lama dari DB
                $stmtAuth = $conn->prepare("SELECT password FROM users WHERE id = ?");
                $stmtAuth->execute([$user_id]);
                $user_data = $stmtAuth->fetch(PDO::FETCH_ASSOC);

                if (!password_verify($current_password, $user_data['password'])) {
                    throw new Exception("Password saat ini salah.");
                }

                if (strlen($new_password) < 6) {
                    throw new Exception("Password baru minimal 6 karakter.");
                }

                if ($new_password !== $confirm_password) {
                    throw new Exception("Konfirmasi password baru tidak cocok.");
                }

                $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
                $updatePass = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $updatePass->execute([$new_hash, $user_id]);
                
                $success_msg .= " Password juga berhasil diubah.";
            }

            $conn->commit(); // Simpan perubahan
        }

    } catch (Exception $e) {
        $conn->rollBack(); // Batalkan jika ada error
        $error_msg = $e->getMessage();
    }
}

// --- 3. Fetch User Data Terbaru ---
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Fallback jika user dihapus saat login
if (!$user) {
    session_destroy();
    header("Location: /situs-rental-gedung/admin/login.php");
    exit;
}

require_once __DIR__ . '/../../../includes/admin/header_admin.php';
?>

<div x-data="profileManager()" class="p-6 max-w-[1600px] mx-auto pb-24 relative">

    <?php if($success_msg): ?>
    <script>Swal.fire({ icon: 'success', title: 'Berhasil', text: '<?= $success_msg ?>', timer: 2000, showConfirmButton: false });</script>
    <?php endif; ?>
    <?php if($error_msg): ?>
    <script>Swal.fire({ icon: 'error', title: 'Gagal', text: '<?= $error_msg ?>' });</script>
    <?php endif; ?>

    <div class="mb-8">
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Profile Saya</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400">Kelola informasi akun dan keamanan Anda.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700 overflow-hidden relative">
                <div class="h-32 bg-gradient-to-r from-primary to-indigo-600"></div>
                
                <div class="px-6 pb-6 text-center relative">
                    <div class="-mt-16 mb-4 inline-flex">
                        <div class="h-32 w-32 rounded-full border-4 border-white dark:border-slate-800 bg-slate-200 dark:bg-slate-700 flex items-center justify-center shadow-md">
                            <span class="text-4xl font-bold text-slate-500 dark:text-slate-300">
                                <?= strtoupper(substr($user['username'] ?? 'U', 0, 1)) ?>
                            </span>
                        </div>
                    </div>
                    
                    <h2 class="text-xl font-bold text-slate-800 dark:text-white"><?= htmlspecialchars($user['nama_lengkap'] ?? '') ?></h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">@<?= htmlspecialchars($user['username'] ?? '') ?></p>
                    
                    <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-700 uppercase tracking-wide">
                        <?= htmlspecialchars($user['role'] ?? 'User') ?>
                    </div>

                    <div class="mt-6 pt-6 border-t border-slate-100 dark:border-slate-700 grid grid-cols-1 gap-4 text-left">
                        <div class="flex items-center gap-3 text-sm text-slate-600 dark:text-slate-300">
                            <div class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-500">
                                <i class="fa-regular fa-envelope"></i>
                            </div>
                            <span class="truncate"><?= htmlspecialchars($user['email'] ?? '') ?></span>
                        </div>
                        <div class="flex items-center gap-3 text-sm text-slate-600 dark:text-slate-300">
                            <div class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-500">
                                <i class="fa-solid fa-phone"></i>
                            </div>
                            <span><?= !empty($user['no_telepon']) ? htmlspecialchars($user['no_telepon']) : '-' ?></span>
                        </div>
                        <div class="flex items-center gap-3 text-sm text-slate-600 dark:text-slate-300">
                            <div class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-500">
                                <i class="fa-regular fa-calendar"></i>
                            </div>
                            <span>Bergabung: <?= isset($user['created_at']) ? date('d M Y', strtotime($user['created_at'])) : '-' ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-2 space-y-8">
            
            <form id="profileForm" method="POST" autocomplete="off">
                <input type="hidden" name="action" value="save_changes">

                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 sm:p-8 mb-8">
                    <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-100 dark:border-slate-700">
                        <div class="p-2 bg-blue-50 dark:bg-blue-900/30 rounded-lg text-blue-600 dark:text-blue-400">
                            <i class="fa-regular fa-id-card text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-slate-800 dark:text-white">Edit Informasi Pribadi</h3>
                            <p class="text-xs text-slate-500">Perbarui detail identitas akun Anda.</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" x-model="form.nama_lengkap" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 focus:ring-2 focus:ring-primary/20 outline-none transition-all dark:text-white" required>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Username</label>
                            <input type="text" name="username" x-model="form.username" autocomplete="off" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 focus:ring-2 focus:ring-primary/20 outline-none transition-all dark:text-white" required>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">No. Telepon</label>
                            <input type="text" name="no_telepon" x-model="form.no_telepon" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 focus:ring-2 focus:ring-primary/20 outline-none transition-all dark:text-white">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Alamat Email</label>
                            <div class="relative">
                                <i class="fa-regular fa-envelope absolute left-4 top-3.5 text-slate-400"></i>
                                <input type="email" name="email" x-model="form.email" class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 focus:ring-2 focus:ring-primary/20 outline-none transition-all dark:text-white" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 sm:p-8" x-data="{ showPass: false }">
                    <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-100 dark:border-slate-700">
                        <div class="p-2 bg-rose-50 dark:bg-rose-900/30 rounded-lg text-rose-600 dark:text-rose-400">
                            <i class="fa-solid fa-shield-halved text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-slate-800 dark:text-white">Ganti Password</h3>
                            <p class="text-xs text-slate-500">Isi hanya jika ingin mengubah password.</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Password Saat Ini</label>
                            <div class="relative">
                                <input 
                                    :type="showPass ? 'text' : 'password'" 
                                    name="current_password" 
                                    x-model="form.current_password" 
                                    autocomplete="new-password"
                                    readonly
                                    onfocus="this.removeAttribute('readonly');"
                                    class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 focus:ring-2 focus:ring-rose-500/20 outline-none transition-all dark:text-white"
                                >
                                <button type="button" @click="showPass = !showPass" class="absolute right-4 top-3 text-slate-400 hover:text-slate-600">
                                    <i class="fa-regular" :class="showPass ? 'fa-eye-slash' : 'fa-eye'"></i>
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Password Baru</label>
                                <input :type="showPass ? 'text' : 'password'" name="new_password" x-model="form.new_password" autocomplete="new-password" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 focus:ring-2 focus:ring-rose-500/20 outline-none transition-all dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Konfirmasi Password Baru</label>
                                <input :type="showPass ? 'text' : 'password'" name="confirm_password" x-model="form.confirm_password" autocomplete="new-password" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 focus:ring-2 focus:ring-rose-500/20 outline-none transition-all dark:text-white">
                            </div>
                        </div>
                    </div>
                </div>

            </form>
            </div>
    </div>

    <div x-show="isDirty" 
         style="display: none;"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-10"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-10"
         class="fixed bottom-6 left-1/2 -translate-x-1/2 z-40 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-2xl rounded-2xl px-6 py-3 flex items-center gap-4 min-w-[350px] justify-between">
        
        <div class="flex items-center gap-6">
            <div class="flex items-center gap-2">
                <span class="bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400 h-8 w-8 rounded-full flex items-center justify-center">
                    <i class="fa-solid fa-pen"></i>
                </span>
                <span class="text-sm font-semibold text-slate-700 dark:text-white">Perubahan Belum Disimpan</span>
            </div>
            
            <button @click="resetForm" 
                    class="text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 text-sm font-medium transition-colors border-l pl-6 border-slate-200 dark:border-slate-600">
                Batal
            </button>

            <button @click="submitForm" 
                    class="px-4 py-2 rounded-xl bg-primary text-white hover:bg-primary/90 shadow-lg shadow-primary/30 text-xs font-bold transition-all flex items-center gap-2">
                <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
            </button>
        </div>
    </div>

</div>

<script>
    function profileManager() {
        return {
            // Data Awal (Snapshot dari PHP)
            initial: {
                nama_lengkap: '<?= addslashes($user['nama_lengkap'] ?? '') ?>',
                username: '<?= addslashes($user['username'] ?? '') ?>',
                email: '<?= addslashes($user['email'] ?? '') ?>',
                no_telepon: '<?= addslashes($user['no_telepon'] ?? '') ?>'
            },
            
            // Data Form (Binding ke Input)
            form: {
                nama_lengkap: '<?= addslashes($user['nama_lengkap'] ?? '') ?>',
                username: '<?= addslashes($user['username'] ?? '') ?>',
                email: '<?= addslashes($user['email'] ?? '') ?>',
                no_telepon: '<?= addslashes($user['no_telepon'] ?? '') ?>',
                current_password: '',
                new_password: '',
                confirm_password: ''
            },

            // Cek apakah ada perubahan
            get isDirty() {
                const profileChanged = 
                    this.form.nama_lengkap !== this.initial.nama_lengkap ||
                    this.form.username !== this.initial.username ||
                    this.form.email !== this.initial.email ||
                    this.form.no_telepon !== this.initial.no_telepon;
                
                const passwordFilled = this.form.new_password.length > 0;

                return profileChanged || passwordFilled;
            },

            // Reset ke data awal
            resetForm() {
                this.form.nama_lengkap = this.initial.nama_lengkap;
                this.form.username = this.initial.username;
                this.form.email = this.initial.email;
                this.form.no_telepon = this.initial.no_telepon;
                this.form.current_password = '';
                this.form.new_password = '';
                this.form.confirm_password = '';
            },

            // Submit Form
            submitForm() {
                document.getElementById('profileForm').submit();
            }
        }
    }
</script>

<?php
require_once __DIR__ . '/../../../includes/admin/footer_admin.php';
?>