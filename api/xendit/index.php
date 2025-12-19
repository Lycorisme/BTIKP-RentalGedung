<?php
// File: C:\laragon\www\situs-rental-gedung\api\xendit\index.php

header('Content-Type: application/json');
require_once '../../config/database.php';
require_once '../../config/xendit.php';

// 1. Verifikasi Token Callback (Keamanan)
// Token ini kita set di Admin Panel > Settings > Payment Gateway
$headers = getallheaders();
$incomingToken = isset($headers['x-callback-token']) ? $headers['x-callback-token'] : "";
$myToken = getXenditCallbackToken();

// Jika admin sudah mengisi token di setting, maka validasi wajib dilakukan
if (!empty($myToken) && $incomingToken !== $myToken) {
    http_response_code(403);
    echo json_encode(["status" => "error", "message" => "Unauthorized callback token"]);
    exit;
}

// 2. Ambil Data JSON dari Webhook
$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Invalid JSON data"]);
    exit;
}

// 3. Proses Status Pembayaran
// Format external_id dari payment processor: "BOOKINGCODE-TIMESTAMP"
$external_id_raw = $data['external_id'] ?? '';
$parts = explode('-', $external_id_raw);
$booking_code = $parts[0]; // Kita hanya butuh kode depannya saja

$status = $data['status'] ?? null;

if ($booking_code && $status) {
    $conn = getDB();
    
    if ($status === 'PAID') {
        // Pembayaran BERHASIL
        // Update status jadi 'paid' HANYA JIKA status sekarang bukan 'paid' atau 'selesai'
        // Ini mencegah double update atau menimpa status 'selesai'
        $stmt = $conn->prepare("UPDATE booking SET status = 'paid', updated_at = NOW() WHERE booking_code = ? AND status NOT IN ('paid', 'selesai')");
        $stmt->execute([$booking_code]);
        
    } elseif ($status === 'EXPIRED') {
        // Invoice KADALUARSA
        // Update status jadi 'batal' HANYA JIKA status sekarang masih 'pending'
        // Agar user bisa mengulang booking baru
        $stmt = $conn->prepare("UPDATE booking SET status = 'batal', updated_at = NOW() WHERE booking_code = ? AND status = 'pending'");
        $stmt->execute([$booking_code]);
    }
}

// Selalu response 200 OK ke Xendit agar webhook tidak dikirim ulang
http_response_code(200);
echo json_encode(["status" => "success"]);
?>