<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

$error = '';
$settings = get_all_settings();

// --- 1. Routing Logic ---
// Jika sudah login, lempar langsung ke Dashboard
if (isset($_SESSION['user_id']) && in_array($_SESSION['role'], ['admin', 'superadmin'])) {
    header('Location: /situs-rental-gedung/admin/data/dashboard/');
    exit;
}

// --- 2. Login Logic ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitasi input
    $email = trim(htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8'));
    $password = trim($_POST['password'] ?? '');
    
    // Validasi Input Kosong
    if (empty($email) || empty($password)) {
        $error = 'Silakan isi email dan password!';
    } else {
        try {
            $db = getDB();
            
            // Query hanya berdasarkan EMAIL (Password dicek lewat PHP hash verification)
            // Kita juga filter role agar penyewa tidak bisa masuk ke halaman admin login ini
            $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND role IN ('admin', 'superadmin') LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                // --- LOGIKA VERIFIKASI PASSWORD ---
                $validPassword = false;
                $passwordNeedsRehash = false;

                // 1. Cek Hash Modern (Bcrypt/Argon2)
                if (password_verify($password, $user['password'])) {
                    $validPassword = true;
                    // Cek apakah hash perlu diperbarui algoritmannya
                    if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
                        $passwordNeedsRehash = true;
                    }
                } 
                // 2. Fallback: Cek Plain Text (Untuk user lama yang belum di-hash)
                elseif ($password === $user['password']) {
                    $validPassword = true;
                    $passwordNeedsRehash = true; // Wajib di-hash ulang demi keamanan
                }

                if ($validPassword) {
                    // Cek status aktif
                    if ($user['is_active'] == 0) {
                        $error = 'Akun Anda dinonaktifkan. Hubungi Superadmin.';
                    } else {
                        // AUTO RE-HASH: Jika login pakai plain text, update ke database jadi hash
                        if ($passwordNeedsRehash) {
                            $newHash = password_hash($password, PASSWORD_DEFAULT);
                            $updateStmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
                            $updateStmt->execute([$newHash, $user['id']]);
                        }

                        // Set Session
                        session_regenerate_id(true); // Security
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['email'] = $user['email'];
                        $_SESSION['role'] = $user['role'];
                        $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                        $_SESSION['login_time'] = time();
                        
                        // Catat Log Login (Optional, jika tabel activity_logs ada)
                        // require_once __DIR__ . '/../../includes/logger.php';
                        // logActivity($db, 'Login', 'Auth', $user['id'], "Login berhasil via Email: $email");

                        // Redirect ke Dashboard
                        header('Location: /situs-rental-gedung/admin/data/dashboard/');
                        exit;
                    }
                } else {
                    $error = 'Password salah!';
                }
            } else {
                $error = 'Email tidak ditemukan atau Anda tidak memiliki akses admin.';
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $error = 'Terjadi kesalahan sistem database.';
        }
    }
}

// --- Logic Helper Tema ---
$activeTheme = $settings['app_theme'] ?? 'indigo';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Administrator - <?= htmlspecialchars($settings['nama_website'] ?? 'Rental Gedung') ?></title>
    
    <?php if(!empty($settings['favicon_url'])): ?>
    <link rel="icon" href="/situs-rental-gedung/<?= $settings['favicon_url'] ?>" type="image/x-icon">
    <?php endif; ?>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const themes = {
            'indigo': { primary: '#4F46E5', secondary: '#10B981', dark: '#1e1b4b' },
            'ocean': { primary: '#0ea5e9', secondary: '#3b82f6', dark: '#0f172a' },
            'nature': { primary: '#16a34a', secondary: '#ca8a04', dark: '#14532d' },
            'rose': { primary: '#e11d48', secondary: '#db2777', dark: '#881337' },
            'sunset': { primary: '#ea580c', secondary: '#d97706', dark: '#431407' },
            'teal': { primary: '#0d9488', secondary: '#0891b2', dark: '#115e59' }
        };

        const activeThemeName = '<?= $activeTheme ?>';
        const currentTheme = themes[activeThemeName] || themes['indigo'];

        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        primary: currentTheme.primary,
                        secondary: currentTheme.secondary,
                        dark: currentTheme.dark,
                    },
                    animation: {
                        'fade-in-up': 'fadeInUp 0.5s ease-out forwards',
                    },
                    keyframes: {
                        fadeInUp: {
                            '0%': { opacity: '0', transform: 'translateY(10px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .bg-pattern {
            background-color: #f8fafc;
            background-image: radial-gradient(#e2e8f0 1px, transparent 1px);
            background-size: 24px 24px;
        }
    </style>
</head>
<body class="bg-pattern font-sans text-slate-800 antialiased min-h-screen flex items-center justify-center p-4 selection:bg-primary selection:text-white">

    <div class="w-full max-w-[400px] animate-fade-in-up">
        
        <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/50 overflow-hidden border border-slate-100">
            
            <div class="p-8 pb-0 text-center">
                <div class="inline-flex items-center justify-center h-16 w-16 rounded-2xl bg-gradient-to-tr from-primary to-secondary text-white shadow-lg shadow-primary/30 mb-6 transform transition-transform hover:scale-105 duration-300">
                    <?php if(!empty($settings['logo_url'])): ?>
                        <img src="/situs-rental-gedung/<?= $settings['logo_url'] ?>" class="h-10 w-10 object-contain brightness-0 invert">
                    <?php else: ?>
                        <i class="fa-solid fa-shield-halved text-3xl"></i>
                    <?php endif; ?>
                </div>
                
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Admin Portal</h1>
                <p class="text-slate-500 text-sm mt-2">Masuk untuk mengelola <?= htmlspecialchars($settings['nama_website'] ?? 'Sistem') ?></p>
            </div>

            <div class="p-8">
                <form method="POST" action="" class="space-y-5">
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 ml-1">Email Address</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-slate-400 group-focus-within:text-primary transition-colors">
                                <i class="fa-regular fa-envelope text-lg"></i>
                            </div>
                            <input type="email" name="email" 
                                class="block w-full rounded-2xl border-0 bg-slate-50 py-3.5 pl-11 pr-4 text-slate-800 ring-1 ring-inset ring-slate-200 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-primary focus:bg-white transition-all sm:text-sm sm:leading-6 font-medium" 
                                placeholder="nama@email.com"
                                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" 
                                required autocomplete="email" autofocus>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 ml-1">Password</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-slate-400 group-focus-within:text-primary transition-colors">
                                <i class="fa-solid fa-lock text-lg"></i>
                            </div>
                            <input type="password" name="password" 
                                class="block w-full rounded-2xl border-0 bg-slate-50 py-3.5 pl-11 pr-4 text-slate-800 ring-1 ring-inset ring-slate-200 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-primary focus:bg-white transition-all sm:text-sm sm:leading-6 font-medium" 
                                placeholder="••••••••" 
                                required>
                        </div>
                    </div>

                    <button type="submit" 
                        class="w-full rounded-2xl bg-primary px-3.5 py-3.5 text-sm font-bold text-white shadow-lg shadow-primary/30 hover:bg-opacity-90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary transition-all active:scale-[0.98]">
                        Masuk ke Dashboard
                        <i class="fa-solid fa-arrow-right ml-2"></i>
                    </button>

                </form>
            </div>

            <div class="bg-slate-50 p-4 text-center border-t border-slate-100">
                <a href="/situs-rental-gedung/public/" class="text-xs font-semibold text-slate-500 hover:text-primary transition-colors flex items-center justify-center gap-2">
                    <i class="fa-solid fa-globe"></i> Kembali ke Website Utama
                </a>
            </div>
        </div>

        <p class="text-center text-xs text-slate-400 mt-8 font-medium">
            &copy; <?= date('Y') ?> <?= htmlspecialchars($settings['nama_website'] ?? 'Rental Gedung') ?>. <br>Secure Admin Panel.
        </p>

    </div>

    <script>
    <?php if ($error): ?>
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        Toast.fire({
            icon: 'error',
            title: 'Login Gagal',
            text: '<?= addslashes($error) ?>'
        });
    <?php endif; ?>
    </script>

</body>
</html>