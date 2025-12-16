<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../modules/settings.php';

$error = '';
$settings = get_all_settings();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    $redirect = $_SESSION['role'] === 'penyewa' ? '/situs-rental-gedung/user/dashboard/' : '/situs-rental-gedung/admin/data/gedung/';
    header("Location: $redirect");
    exit;
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if (empty($username) || empty($password)) {
        $error = 'Username dan password harus diisi!';
    } else {
        try {
            $db = getDB();
            $stmt = $db->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND password = ? AND is_active = 1");
            $stmt->execute([$username, $username, $password]);
            $user = $stmt->fetch();
            
            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                $_SESSION['login_time'] = time();
                
                $redirect = $user['role'] === 'penyewa' ? '/situs-rental-gedung/user/dashboard/' : '/situs-rental-gedung/admin/data/gedung/';
                header("Location: $redirect");
                exit;
            } else {
                $error = 'Username/email atau password salah!';
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
    <title>Login - <?= htmlspecialchars($settings['nama_website'] ?? 'Rental Gedung') ?></title>
    
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

<div class="w-full max-w-md">
    <!-- Logo -->
    <div class="text-center mb-8">
        <div class="inline-flex h-20 w-20 rounded-3xl bg-gradient-to-br from-blue-500 to-purple-600 items-center justify-center shadow-2xl shadow-blue-500/30 mb-4">
            <i class="fa-solid fa-building text-white text-3xl"></i>
        </div>
        <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent mb-2">
            Selamat Datang
        </h1>
        <p class="text-slate-600">Masuk untuk melanjutkan booking gedung</p>
    </div>

    <!-- Login Card -->
    <div class="bg-white rounded-3xl shadow-2xl shadow-slate-200/50 p-8">
        <?php if ($error): ?>
        <div class="mb-6 p-4 rounded-2xl bg-red-50 border border-red-200 text-red-600 flex items-center gap-2">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <!-- Username/Email -->
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">
                    <i class="fa-solid fa-user text-blue-500 mr-2"></i>Username atau Email
                </label>
                <input type="text" name="username" required 
                    class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-blue-500 focus:outline-none transition-all"
                    placeholder="Masukkan username atau email">
            </div>

            <!-- Password -->
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">
                    <i class="fa-solid fa-lock text-blue-500 mr-2"></i>Password
                </label>
                <input type="password" name="password" required 
                    class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-blue-500 focus:outline-none transition-all"
                    placeholder="Masukkan password">
            </div>

            <!-- Submit Button -->
            <button type="submit" 
                class="w-full py-3 rounded-xl bg-gradient-to-r from-blue-500 to-purple-600 text-white font-bold shadow-lg shadow-blue-500/30 hover:shadow-xl hover:translate-y-[-2px] transition-all">
                <i class="fa-solid fa-right-to-bracket mr-2"></i>Masuk
            </button>
        </form>

        <!-- Links -->
        <div class="mt-6 space-y-3">
            <div class="text-center">
                <p class="text-slate-600 text-sm">
                    Belum punya akun? 
                    <a href="/situs-rental-gedung/user/register/" class="text-blue-600 hover:text-blue-700 font-semibold">
                        Daftar Sekarang
                    </a>
                </p>
            </div>
            
            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-slate-200"></div>
                </div>
                <div class="relative flex justify-center text-xs">
                    <span class="px-2 bg-white text-slate-500">ATAU</span>
                </div>
            </div>

            <div class="text-center">
                <a href="/situs-rental-gedung/admin/login/" class="text-slate-600 hover:text-slate-700 text-sm font-medium">
                    <i class="fa-solid fa-user-shield mr-1"></i>Login sebagai Admin
                </a>
            </div>

            <div class="text-center pt-2">
                <a href="/situs-rental-gedung/public/" class="text-blue-600 hover:text-blue-700 text-sm font-semibold">
                    <i class="fa-solid fa-home mr-1"></i>Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>

    <!-- Demo Info -->
    <div class="mt-6 bg-white rounded-2xl shadow-lg p-4">
        <p class="text-slate-600 text-xs text-center mb-2">
            <i class="fa-solid fa-info-circle mr-1"></i>Demo Login Penyewa
        </p>
        <div class="bg-slate-50 rounded-lg p-3 text-center">
            <p class="text-slate-500 text-xs">Username/Email:</p>
            <p class="text-slate-800 font-mono text-sm">penyewa1 / budi@gmail.com</p>
            <p class="text-slate-500 text-xs mt-2">Password:</p>
            <p class="text-slate-800 font-mono text-sm">penyewa123</p>
        </div>
    </div>
</div>

</body>
</html>