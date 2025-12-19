<?php
// File: C:\laragon\www\situs-rental-gedung\config\xendit.php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/database.php';

use Xendit\Xendit;

/**
 * Menginisialisasi Xendit dengan API Key dari database.
 */
function initXendit() {
    $conn = getDB();
    
    // Ambil API Key dari tabel settings
    $stmt = $conn->prepare("SELECT value FROM settings WHERE `key` = 'xendit_api_key' LIMIT 1");
    $stmt->execute();
    $apiKey = $stmt->fetchColumn();

    if ($apiKey) {
        Xendit::setApiKey($apiKey);
        return true;
    }
    return false;
}

/**
 * Mengambil Token Callback untuk verifikasi webhook.
 */
function getXenditCallbackToken() {
    $conn = getDB();
    $stmt = $conn->prepare("SELECT value FROM settings WHERE `key` = 'xendit_callback_token' LIMIT 1");
    $stmt->execute();
    return $stmt->fetchColumn();
}
?>