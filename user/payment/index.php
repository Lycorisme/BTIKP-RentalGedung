<?php
// File: C:\laragon\www\situs-rental-gedung\user\payment\index.php

session_start();
require_once '../../config/database.php';
require_once '../../config/xendit.php';
require_once '../../includes/admin/auth.php'; // Menggunakan auth check yang sudah ada

// Cek Login User
requireLogin();

// Inisialisasi Xendit dari Database
if (!initXendit()) {
    die('<div style="padding:20px; font-family:sans-serif;"><h3>Sistem pembayaran belum dikonfigurasi.</h3><p>Silakan hubungi admin untuk mengaktifkan pembayaran online.</p></div>');
}

use Xendit\Invoice;

$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$conn = getDB();

// 1. Ambil Data Booking & User
// Pastikan booking milik user yang sedang login
$query = "SELECT b.*, u.email, u.nama_lengkap, u.no_telepon 
          FROM booking b 
          JOIN users u ON b.penyewa_id = u.id 
          WHERE b.id = ? AND b.penyewa_id = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$booking_id, $_SESSION['user_id']]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    die("Data booking tidak ditemukan atau Anda tidak memiliki akses.");
}

// Cek status booking
if (in_array($booking['status'], ['paid', 'selesai', 'batal', 'ditolak'])) {
    // Jika sudah lunas atau batal, kembalikan ke halaman detail
    header("Location: ../booking/detail.php?id=" . $booking_id);
    exit;
}

// Cek jika invoice URL sudah ada di database
// Kita redirect langsung (Asumsi link belum expired)
if (!empty($booking['payment_url'])) {
    header("Location: " . $booking['payment_url']);
    exit;
}

try {
    // 2. Siapkan Parameter Invoice Xendit
    // External ID kita buat unik dengan timestamp agar bisa regenerate jika yang lama expired
    $external_id = $booking['booking_code'] . '-' . time();
    
    // Deteksi domain otomatis
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $domain = $protocol . "://" . $_SERVER['HTTP_HOST'] . "/situs-rental-gedung";

    $params = [
        'external_id' => $external_id,
        'amount' => (int)$booking['total_bayar'],
        'description' => "Sewa Gedung Kode #" . $booking['booking_code'],
        'invoice_duration' => 86400, // Link berlaku 24 Jam
        'payer_email' => $booking['email'],
        'customer' => [
            'given_names' => $booking['nama_lengkap'],
            'mobile_number' => $booking['no_telepon'] ?? null
        ],
        // Redirect setelah bayar sukses
        'success_redirect_url' => $domain . '/user/booking/detail.php?id=' . $booking_id . '&status=success',
        // Redirect jika gagal/close
        'failure_redirect_url' => $domain . '/user/booking/detail.php?id=' . $booking_id . '&status=failed'
    ];

    // 3. Request ke API Xendit
    $createInvoice = Invoice::create($params);
    
    $paymentUrl = $createInvoice['invoice_url'];
    $paymentId = $createInvoice['id'];

    // 4. Simpan Link & ID ke Database
    $update = $conn->prepare("UPDATE booking SET payment_id = ?, payment_url = ? WHERE id = ?");
    $update->execute([$paymentId, $paymentUrl, $booking_id]);

    // 5. Redirect User ke Halaman Pembayaran Xendit
    header("Location: " . $paymentUrl);
    exit;

} catch (Exception $e) {
    // Tampilkan error jika terjadi masalah koneksi ke Xendit
    echo '<div style="font-family:sans-serif; padding:40px; text-align:center;">';
    echo '<h2 style="color:#e11d48;">Gagal Memproses Pembayaran</h2>';
    echo '<p style="color:#64748b;">' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<a href="../booking/detail.php?id='.$booking_id.'" style="display:inline-block; margin-top:20px; text-decoration:none; color:#4f46e5; font-weight:bold;">&larr; Kembali ke Detail Booking</a>';
    echo '</div>';
}
?>