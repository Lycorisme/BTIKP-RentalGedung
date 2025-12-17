<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

$error = '';
$settings = get_all_settings();

// --- 1. Routing Logic Fix ---
// Jika sudah login, lempar langsung ke Dashboard (bukan Data Gedung)
if (isset($_SESSION['user_id']) && in_array($_SESSION['role'], ['admin', 'superadmin'])) {
    header('Location: /situs-rental-gedung/admin/data/dashboard/');
    exit;
}

// --- 2. Login Logic & Validation ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitasi input dasar
    $username = trim(htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8'));
    $password = trim($_POST['password'] ?? '');
    
    // Validasi Input Kosong
    if (empty($username) || empty($password)) {
        $error = 'Silakan isi username dan password!';
    } else {
        try {
            $db = getDB();
            // Query cek user
            $stmt = $db->prepare("SELECT * FROM users WHERE username = ? AND password = ? AND role IN ('admin', 'superadmin') LIMIT 1");
            $stmt->execute([$username, $password]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Validasi Login
            if ($user) {
                // Cek status aktif
                if ($user['is_active'] == 0) {
                    $error = 'Akun Anda dinonaktifkan. Hubungi Superadmin.';
                } else {
                    // Set Session
                    session_regenerate_id(true); // Security: cegah session fixation
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                    $_SESSION['login_time'] = time();
                    
                    // Redirect ke Dashboard
                    header('Location: /situs-rental-gedung/admin/data/dashboard/');
                    exit;
                }
            } else {
                $error = 'Username atau password salah!';
            }
        } catch (PDOException $e) {
            error_log($e->getMessage()); // Log error server, jangan tampilkan ke user
            $error = 'Terjadi kesalahan sistem. Silakan coba lagi nanti.';
        }
    }
}

// --- Logic Helper Tema (Sama seperti header_admin.php) ---
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
        /* Pattern Background Halus */
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
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 ml-1">Username</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-slate-400 group-focus-within:text-primary transition-colors">
                                <i class="fa-regular fa-user text-lg"></i>
                            </div>
                            <input type="text" name="username" 
                                class="block w-full rounded-2xl border-0 bg-slate-50 py-3.5 pl-11 pr-4 text-slate-800 ring-1 ring-inset ring-slate-200 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-primary focus:bg-white transition-all sm:text-sm sm:leading-6 font-medium" 
                                placeholder="Masukkan username"
                                value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" 
                                required autocomplete="off">
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