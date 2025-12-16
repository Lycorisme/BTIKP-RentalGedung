<?php
// admin/api/check_notifications.php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/admin/auth.php';

// Cek sesi login (Hanya admin/superadmin yang boleh cek)
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_logged_in']) || !in_array($_SESSION['user_role'], ['admin', 'superadmin'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

try {
    $db = getDB();

    // 1. Hitung Booking Pending (Orderan Baru)
    $stmt = $db->query("SELECT COUNT(*) as total FROM booking WHERE status = 'pending' AND deleted_at IS NULL");
    $pendingCount = $stmt->fetch()['total'];

    // 2. Ambil 5 Notifikasi Terakhir untuk Dropdown
    $stmt = $db->query("
        SELECT b.id, b.booking_code, b.created_at, u.nama_lengkap 
        FROM booking b
        JOIN users u ON b.penyewa_id = u.id
        WHERE b.status = 'pending' AND b.deleted_at IS NULL
        ORDER BY b.created_at DESC 
        LIMIT 5
    ");
    $latestBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format waktu agar "Human Readable" (e.g., 5 menit yang lalu)
    foreach ($latestBookings as &$booking) {
        $time = strtotime($booking['created_at']);
        $booking['time_ago'] = time_elapsed_string($time);
    }

    echo json_encode([
        'status' => 'success',
        'count' => (int)$pendingCount,
        'data' => $latestBookings
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

// Helper Function: Time Ago
function time_elapsed_string($time) {
    $time_difference = time() - $time;
    if( $time_difference < 1 ) { return 'baru saja'; }
    $condition = array( 
        12 * 30 * 24 * 60 * 60 =>  'tahun',
        30 * 24 * 60 * 60       =>  'bulan',
        24 * 60 * 60            =>  'hari',
        60 * 60                 =>  'jam',
        60                      =>  'menit',
        1                       =>  'detik'
    );
    foreach( $condition as $secs => $str ) {
        $d = $time_difference / $secs;
        if( $d >= 1 ) {
            $t = round( $d );
            return $t . ' ' . $str . ' lalu';
        }
    }
}