<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../modules/settings.php';

$error = '';
$settings = get_all_settings();

// Redirect if already logged in
if (isset($_SESSION['user_id']) && in_array($_SESSION['role'], ['admin', 'superadmin'])) {
    header('Location: /situs-rental-gedung/admin/data/gedung/');
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
            $stmt = $db->prepare("SELECT * FROM users WHERE username = ? AND password = ? AND role IN ('admin', 'superadmin') AND is_active = 1");
            $stmt->execute([$username, $password]);
            $user = $stmt->fetch();
            
            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                $_SESSION['login_time'] = time();
                
                header('Location: /situs-rental-gedung/admin/data/gedung/');
                exit;
            } else {
                $error = 'Username atau password salah!';
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
    <title>Admin Login - <?= htmlspecialchars($settings['nama_website'] ?? 'Rental Gedung') ?></title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gradient-to-br from-indigo-900 via-purple-900 to-slate-900 min-h-screen flex items-center justify-center p-4">

<div class="w-full max-w-md">
    <!-- Logo -->
    <div class="text-center mb-8">
        <div class="inline-flex h-16 w-16 rounded-2xl bg-gradient-to-tr from-pink-500 to-orange-400 items-center justify-center shadow-2xl shadow-pink-500/50 mb-4">
            <i class="fa-solid fa-building text-white text-2xl"></i>
        </div>
        <h1 class="text-3xl font-bold text-white mb-2">Admin Login</h1>
        <p class="text-slate-300">Masuk ke dashboard administrator</p>
    </div>

    <!-- Login Card -->
    <div class="bg-white/10 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 p-8">
        <?php if ($error): ?>
        <div class="mb-6 p-4 rounded-2xl bg-red-500/20 border border-red-500/30 text-red-200">
            <i class="fa-solid fa-circle-exclamation mr-2"></i><?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <!-- Username -->
            <div>
                <label class="block text-sm font-semibold text-white mb-2">
                    <i class="fa-solid fa-user mr-2"></i>Username
                </label>
                <input type="text" name="username" required 
                    class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/20 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-all"
                    placeholder="Masukkan username">
            </div>

            <!-- Password -->
            <div>
                <label class="block text-sm font-semibold text-white mb-2">
                    <i class="fa-solid fa-lock mr-2"></i>Password
                </label>
                <input type="password" name="password" required 
                    class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/20 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-all"
                    placeholder="Masukkan password">
            </div>

            <!-- Submit Button -->
            <button type="submit" 
                class="w-full py-3 rounded-xl bg-gradient-to-r from-pink-500 to-orange-400 text-white font-bold shadow-lg shadow-pink-500/50 hover:shadow-xl hover:translate-y-[-2px] transition-all">
                <i class="fa-solid fa-right-to-bracket mr-2"></i>Login
            </button>
        </form>

        <!-- Links -->
        <div class="mt-6 text-center space-y-2">
            <p class="text-slate-300 text-sm">
                Bukan admin? 
                <a href="/situs-rental-gedung/user/login/" class="text-pink-400 hover:text-pink-300 font-semibold">
                    Login sebagai Penyewa
                </a>
            </p>
            <p class="text-slate-300 text-sm">
                <a href="/situs-rental-gedung/public/" class="text-pink-400 hover:text-pink-300 font-semibold">
                    <i class="fa-solid fa-home mr-1"></i>Kembali ke Beranda
                </a>
            </p>
        </div>
    </div>

    <!-- Demo Info -->
    <div class="mt-6 bg-white/5 backdrop-blur-xl rounded-2xl border border-white/10 p-4">
        <p class="text-slate-300 text-xs text-center mb-2">
            <i class="fa-solid fa-info-circle mr-1"></i>Demo Credentials
        </p>
        <div class="grid grid-cols-2 gap-2 text-xs">
            <div class="bg-white/5 rounded-lg p-2">
                <p class="text-slate-400">Superadmin:</p>
                <p class="text-white font-mono">superadmin / super123</p>
            </div>
            <div class="bg-white/5 rounded-lg p-2">
                <p class="text-slate-400">Admin:</p>
                <p class="text-white font-mono">admin / admin123</p>
            </div>
        </div>
    </div>
</div>

</body>
</html>