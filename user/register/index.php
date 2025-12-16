<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../modules/settings.php';
require_once __DIR__ . '/../../modules/crud.php';

$error = '';
$success = '';
$settings = get_all_settings();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: /situs-rental-gedung/user/dashboard/');
    exit;
}

// Handle registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');
    $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
    $no_telepon = trim($_POST['no_telepon'] ?? '');
    
    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($nama_lengkap)) {
        $error = 'Semua field wajib diisi!';
    } elseif ($password !== $confirm_password) {
        $error = 'Konfirmasi password tidak cocok!';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid!';
    } else {
        try {
            $db = getDB();
            
            // Check duplicate username
            $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $error = 'Username sudah digunakan!';
            } else {
                // Check duplicate email
                $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    $error = 'Email sudah terdaftar!';
                } else {
                    // Insert user
                    $userData = [
                        'username' => $username,
                        'email' => $email,
                        'password' => $password,
                        'role' => 'penyewa',
                        'nama_lengkap' => $nama_lengkap,
                        'no_telepon' => $no_telepon,
                        'is_active' => 1
                    ];
                    
                    $result = create('users', $userData);
                    
                    if ($result['success']) {
                        $user_id = $result['id'];
                        
                        // Insert pelanggan
                        $pelangganData = [
                            'user_id' => $user_id
                        ];
                        create('pelanggan', $pelangganData);
                        
                        // Auto login
                        $_SESSION['user_id'] = $user_id;
                        $_SESSION['username'] = $username;
                        $_SESSION['email'] = $email;
                        $_SESSION['role'] = 'penyewa';
                        $_SESSION['nama_lengkap'] = $nama_lengkap;
                        $_SESSION['login_time'] = time();
                        
                        header('Location: /situs-rental-gedung/user/dashboard/?welcome=1');
                        exit;
                    } else {
                        $error = 'Pendaftaran gagal. Silakan coba lagi.';
                    }
                }
            }
        } catch (PDOException $e) {
            $error = 'Terjadi kesalahan sistem. Silakan coba lagi.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - <?= htmlspecialchars($settings['nama_website'] ?? 'Rental Gedung') ?></title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Poppins', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gradient-to-br from-blue-50 via-purple-50 to-pink-50 min-h-screen flex items-center justify-center p-4">

<div class="w-full max-w-lg">
    <!-- Logo -->
    <div class="text-center mb-8">
        <div class="inline-flex h-20 w-20 rounded-3xl bg-gradient-to-br from-blue-500 to-purple-600 items-center justify-center shadow-2xl shadow-blue-500/30 mb-4">
            <i class="fa-solid fa-user-plus text-white text-3xl"></i>
        </div>
        <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent mb-2">
            Daftar Akun
        </h1>
        <p class="text-slate-600">Buat akun untuk mulai booking gedung</p>
    </div>

    <!-- Register Card -->
    <div class="bg-white rounded-3xl shadow-2xl shadow-slate-200/50 p-8">
        <?php if ($error): ?>
        <div class="mb-6 p-4 rounded-2xl bg-red-50 border border-red-200 text-red-600 flex items-center gap-2">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
        <?php endif; ?>

        <form method="POST" class="space-y-5">
            <!-- Username -->
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">
                    <i class="fa-solid fa-user text-blue-500 mr-2"></i>Username
                </label>
                <input type="text" name="username" required 
                    class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-blue-500 focus:outline-none transition-all"
                    placeholder="Pilih username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            </div>

            <!-- Email -->
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">
                    <i class="fa-solid fa-envelope text-blue-500 mr-2"></i>Email
                </label>
                <input type="email" name="email" required 
                    class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-blue-500 focus:outline-none transition-all"
                    placeholder="nama@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>

            <!-- Nama Lengkap -->
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">
                    <i class="fa-solid fa-id-card text-blue-500 mr-2"></i>Nama Lengkap
                </label>
                <input type="text" name="nama_lengkap" required 
                    class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-blue-500 focus:outline-none transition-all"
                    placeholder="Nama lengkap Anda" value="<?= htmlspecialchars($_POST['nama_lengkap'] ?? '') ?>">
            </div>

            <!-- No Telepon -->
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">
                    <i class="fa-solid fa-phone text-blue-500 mr-2"></i>No. Telepon
                </label>
                <input type="tel" name="no_telepon" 
                    class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-blue-500 focus:outline-none transition-all"
                    placeholder="08xxx (opsional)" value="<?= htmlspecialchars($_POST['no_telepon'] ?? '') ?>">
            </div>

            <!-- Password -->
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">
                    <i class="fa-solid fa-lock text-blue-500 mr-2"></i>Password
                </label>
                <input type="password" name="password" required 
                    class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-blue-500 focus:outline-none transition-all"
                    placeholder="Minimal 6 karakter">
            </div>

            <!-- Confirm Password -->
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">
                    <i class="fa-solid fa-lock text-blue-500 mr-2"></i>Konfirmasi Password
                </label>
                <input type="password" name="confirm_password" required 
                    class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-blue-500 focus:outline-none transition-all"
                    placeholder="Ulangi password">
            </div>

            <!-- Submit Button -->
            <button type="submit" 
                class="w-full py-3 rounded-xl bg-gradient-to-r from-blue-500 to-purple-600 text-white font-bold shadow-lg shadow-blue-500/30 hover:shadow-xl hover:translate-y-[-2px] transition-all">
                <i class="fa-solid fa-user-plus mr-2"></i>Daftar Sekarang
            </button>
        </form>

        <!-- Links -->
        <div class="mt-6 text-center space-y-2">
            <p class="text-slate-600 text-sm">
                Sudah punya akun? 
                <a href="/situs-rental-gedung/user/login/" class="text-blue-600 hover:text-blue-700 font-semibold">
                    Login Sekarang
                </a>
            </p>
            <p class="text-slate-600 text-sm">
                <a href="/situs-rental-gedung/public/" class="text-blue-600 hover:text-blue-700 font-semibold">
                    <i class="fa-solid fa-home mr-1"></i>Kembali ke Beranda
                </a>
            </p>
        </div>
    </div>
</div>

</body>
</html>