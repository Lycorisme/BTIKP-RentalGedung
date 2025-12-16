<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['admin_login'])) {
    header("Location: ../../login/");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Data Gedung - Admin</title>
</head>
<body>
    <h1>Halaman Data Gedung</h1>
    <p>Selamat datang, Admin!</p>
</body>
</html>
