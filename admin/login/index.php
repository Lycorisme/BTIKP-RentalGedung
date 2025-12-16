<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gradient-to-br from-blue-600 via-purple-600 to-pink-600 min-h-screen flex items-center justify-center p-4 relative overflow-hidden">

<!-- Animated Background Elements -->
<div class="absolute inset-0 opacity-20">
    <div class="absolute top-0 left-0 w-96 h-96 bg-white rounded-full blur-3xl animate-pulse"></div>
    <div class="absolute bottom-0 right-0 w-96 h-96 bg-white rounded-full blur-3xl animate-pulse" style="animation-delay: 1s;"></div>
    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-white rounded-full blur-3xl animate-pulse" style="animation-delay: 2s;"></div>
</div>

<div class="w-full max-w-md relative z-10">
    <!-- Logo -->
    <div class="text-center mb-8">
        <div class="inline-flex h-20 w-20 rounded-3xl bg-white/20 backdrop-blur-xl items-center justify-center shadow-2xl mb-4 border border-white/30">
            <i class="fa-solid fa-building text-white text-3xl"></i>
        </div>
        <h1 class="text-4xl font-bold text-white mb-2">Admin Login</h1>
        <p class="text-blue-100 text-lg">Masuk ke dashboard administrator</p>
    </div>

    <!-- Login Card -->
    <div class="bg-white/10 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 p-8">
        <?php if ($error): ?>
        <div class="mb-6 p-4 rounded-2xl bg-red-500/20 border border-red-500/30 text-white flex items-center gap-3">
            <i class="fa-solid fa-circle-exclamation text-xl"></i>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <!-- Username -->
            <div>
                <label class="block text-sm font-bold text-white mb-2">
                    <i class="fa-solid fa-user mr-2"></i>Username
                </label>
                <input type="text" name="username" required 
                    class="w-full px-4 py-3 rounded-xl bg-white/10 border-2 border-white/20 text-white placeholder-blue-200 focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-white/40 transition-all backdrop-blur-sm"
                    placeholder="Masukkan username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            </div>

            <!-- Password -->
            <div>
                <label class="block text-sm font-bold text-white mb-2">
                    <i class="fa-solid fa-lock mr-2"></i>Password
                </label>
                <input type="password" name="password" required 
                    class="w-full px-4 py-3 rounded-xl bg-white/10 border-2 border-white/20 text-white placeholder-blue-200 focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-white/40 transition-all backdrop-blur-sm"
                    placeholder="Masukkan password">
            </div>

            <!-- Submit Button -->
            <button type="submit" 
                class="w-full py-4 rounded-xl bg-white text-purple-600 font-bold text-lg shadow-2xl hover:shadow-white/30 hover:translate-y-[-4px] transition-all">
                <i class="fa-solid fa-right-to-bracket mr-2"></i>Login
            </button>
        </form>

        <!-- Links -->
        <div class="mt-8 space-y-3 text-center">
            <div class="flex items-center gap-4">
                <div class="flex-1 h-px bg-white/20"></div>
                <span class="text-blue-100 text-sm">ATAU</span>
                <div class="flex-1 h-px bg-white/20"></div>
            </div>
            
            <p class="text-blue-100 text-sm">
                Bukan admin? 
                <a href="/situs-rental-gedung/user/login/" class="text-white hover:text-blue-100 font-bold underline">
                    Login sebagai Penyewa
                </a>
            </p>
            
            <p class="text-blue-100 text-sm">
                <a href="/situs-rental-gedung/public/" class="text-white hover:text-blue-100 font-bold">
                    <i class="fa-solid fa-home mr-1"></i>Kembali ke Beranda
                </a>
            </p>
        </div>
    </div>

    <!-- Demo Info -->
    <div class="mt-6 bg-white/10 backdrop-blur-xl rounded-2xl border border-white/20 p-6">
        <p class="text-white text-sm text-center mb-4 font-semibold">
            <i class="fa-solid fa-info-circle mr-2"></i>Demo Credentials
        </p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                <p class="text-blue-100 text-xs mb-2">Superadmin:</p>
                <p class="text-white font-mono text-sm font-bold">superadmin</p>
                <p class="text-white font-mono text-sm font-bold">super123</p>
            </div>
            <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                <p class="text-blue-100 text-xs mb-2">Admin:</p>
                <p class="text-white font-mono text-sm font-bold">admin</p>
                <p class="text-white font-mono text-sm font-bold">admin123</p>
            </div>
        </div>
    </div>
</div>

<script>
<?php if ($error): ?>
    Swal.fire({
        icon: 'error',
        title: 'Login Gagal',
        text: '<?= addslashes($error) ?>',
        confirmButtonColor: '#ef4444'
    });
<?php endif; ?>
</script>

</body>
</html>