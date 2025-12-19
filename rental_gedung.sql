-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 19, 2025 at 07:45 AM
-- Server version: 5.7.39
-- PHP Version: 8.5.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `rental_gedung`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `module` varchar(50) NOT NULL,
  `record_id` varchar(50) DEFAULT NULL,
  `description` text NOT NULL,
  `old_value` json DEFAULT NULL,
  `new_value` json DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `module`, `record_id`, `description`, `old_value`, `new_value`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'Soft Delete', 'Gedung', '4', 'Memindahkan gedung ke sampah: test', '{\"id\": 4, \"nama\": \"test\", \"status\": \"tersedia\", \"luas_m2\": 79, \"deskripsi\": \"vgrevercdc\", \"created_at\": \"2025-12-17 11:07:08\", \"created_by\": 1, \"deleted_at\": null, \"foto_utama\": \"uploads/gedung/gedung_69422c6c90505.jpg\", \"kategori_id\": null, \"alamat_lengkap\": \"vevfrvev\", \"harga_per_hari\": \"10000000.00\", \"kapasitas_orang\": 100}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 22:44:28'),
(2, 1, 'Soft Delete', 'Gedung', '3', 'Memindahkan gedung ke sampah: test', '{\"id\": 3, \"nama\": \"test\", \"status\": \"tersedia\", \"luas_m2\": 60, \"deskripsi\": \"csdcsdccc\", \"created_at\": \"2025-12-17 10:56:43\", \"created_by\": 1, \"deleted_at\": null, \"foto_utama\": \"uploads/gedung/gedung_69422a0b7c754.jpeg\", \"kategori_id\": null, \"alamat_lengkap\": \"dcsddsc\", \"harga_per_hari\": \"100000.00\", \"kapasitas_orang\": 100}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 22:45:55'),
(3, 1, 'Restore', 'Gedung', '3', 'Memulihkan gedung ID: 3', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 22:46:12'),
(4, 1, 'Soft Delete', 'Gedung', '3', 'Memindahkan gedung ke sampah: test', '{\"id\": 3, \"nama\": \"test\", \"status\": \"tersedia\", \"luas_m2\": 60, \"deskripsi\": \"csdcsdccc\", \"created_at\": \"2025-12-17 10:56:43\", \"created_by\": 1, \"deleted_at\": null, \"foto_utama\": \"uploads/gedung/gedung_69422a0b7c754.jpeg\", \"kategori_id\": null, \"alamat_lengkap\": \"dcsddsc\", \"harga_per_hari\": \"100000.00\", \"kapasitas_orang\": 100}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 22:46:26'),
(5, 1, 'UPDATE', 'Gedung', '14', 'UPDATE data pada modul Gedung', NULL, '[14]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 23:01:25'),
(6, 1, 'UPDATE', 'Fasilitas', '2', 'UPDATE data pada modul Fasilitas', NULL, '[2]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 23:02:26'),
(7, 1, 'UPDATE', 'Fasilitas', '2', 'UPDATE data pada modul Fasilitas', NULL, '[2]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 23:02:50'),
(8, 1, 'UPDATE', 'Users', '1', 'UPDATE data pada modul Users', NULL, '[\"***PASSWORD_HASH***\", 1]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 23:13:24'),
(9, 1, 'UPDATE', 'Users', '4', 'UPDATE data pada modul Users', NULL, '[4]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 23:14:07'),
(10, 1, 'UPDATE', 'Users', '2', 'UPDATE data pada modul Users', NULL, '[2]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 23:16:44'),
(11, 1, 'UPDATE', 'Users', '3', 'UPDATE data pada modul Users', NULL, '[3]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 23:17:30'),
(12, 1, 'DELETE', 'Users', '3', 'DELETE data pada modul Users', NULL, '[\"3\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 23:17:45'),
(13, 1, 'DELETE', 'Users', '4', 'DELETE data pada modul Users', NULL, '[\"4\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 23:17:45'),
(14, 1, 'DELETE', 'Gedung', '14', 'DELETE data pada modul Gedung', NULL, '[\"14\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 23:17:45'),
(15, 1, 'DELETE', 'Gedung', '3', 'DELETE data pada modul Gedung', NULL, '[\"3\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 23:17:45'),
(16, 1, 'UPDATE', 'Users', '1', 'UPDATE data pada modul Users', NULL, '[\"***PASSWORD_HASH***\", 1]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 23:18:38'),
(17, 1, 'UPDATE', 'Users', '1', 'UPDATE data pada modul Users', NULL, '[\"***PASSWORD_HASH***\", 1]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 23:20:08'),
(18, 1, 'UPDATE', 'Users', '1', 'UPDATE data pada modul Users', NULL, '[\"Super Admin\", \"superadmin\", \"admin@gmail.com\", \"0511-123456\", 1]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 23:20:57'),
(19, 1, 'UPDATE', 'Users', '1', 'UPDATE data pada modul Users', NULL, '[\"***PASSWORD_HASH***\", 1]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 23:26:39'),
(20, 1, 'UPDATE', 'Users', '1', 'UPDATE data pada modul Users', NULL, '[\"***PASSWORD_HASH***\", 1]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 23:28:22'),
(21, 1, 'UPDATE', 'Users', '1', 'UPDATE data pada modul Users', NULL, '[\"***PASSWORD_HASH***\", 1]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 23:28:33'),
(22, 1, 'UPDATE', 'Settings', 'nama_website', 'UPDATE data pada modul Settings', NULL, '[\"GedungKita\", \"nama_website\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 23:35:07'),
(23, 1, 'UPDATE', 'Settings', 'nama_panjang', 'UPDATE data pada modul Settings', NULL, '[\"Sistem Manajemen Rental Gedung Terpadu\", \"nama_panjang\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 23:35:07'),
(24, 1, 'UPDATE', 'Settings', 'app_theme', 'UPDATE data pada modul Settings', NULL, '[\"sunset\", \"app_theme\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 23:35:07'),
(25, 1, 'UPDATE', 'Settings', 'instansi_nama', 'UPDATE data pada modul Settings', NULL, '[\"PT. Rental Gedung Maju\", \"instansi_nama\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 23:35:07'),
(26, 1, 'UPDATE', 'Settings', 'instansi_alamat', 'UPDATE data pada modul Settings', NULL, '[\"Jl. Profesional No.123, Banjarmasin\", \"instansi_alamat\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 23:35:07'),
(27, 1, 'UPDATE', 'Settings', 'instansi_telepon', 'UPDATE data pada modul Settings', NULL, '[\"0511-1234567\", \"instansi_telepon\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 23:35:07'),
(28, 1, 'UPDATE', 'Settings', 'instansi_email', 'UPDATE data pada modul Settings', NULL, '[\"info@rentalgedung.co.id\", \"instansi_email\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 23:35:07'),
(29, 1, 'UPDATE', 'Settings', 'company_address', 'UPDATE data pada modul Settings', NULL, '[\"Jl. Jenderal Sudirman No. Kav 50, Jakarta Selatan, DKI Jakarta 12930\", \"company_address\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 23:35:07'),
(30, 1, 'UPDATE', 'Settings', 'company_email', 'UPDATE data pada modul Settings', NULL, '[\"support@gedungkita.com\", \"company_email\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 23:35:07'),
(31, 1, 'UPDATE', 'Settings', 'company_phone', 'UPDATE data pada modul Settings', NULL, '[\"+62 812-3456-7890\", \"company_phone\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 23:35:07'),
(32, 1, 'UPDATE', 'Settings', 'footer_copyright', 'UPDATE data pada modul Settings', NULL, '[\"© 2025 GedungKita. All rights reserved.\", \"footer_copyright\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 23:35:07'),
(33, 1, 'UPDATE', 'Settings', 'social_instagram', 'UPDATE data pada modul Settings', NULL, '[\"https://instagram.com/gedungkita\", \"social_instagram\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 23:35:07'),
(34, 1, 'UPDATE', 'Settings', 'social_facebook', 'UPDATE data pada modul Settings', NULL, '[\"https://facebook.com/gedungkita\", \"social_facebook\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 23:35:08'),
(35, 1, 'UPDATE', 'Settings', 'ttd_nama', 'UPDATE data pada modul Settings', NULL, '[\"Budi Santoso\", \"ttd_nama\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 23:35:08'),
(36, 1, 'UPDATE', 'Settings', 'ttd_nip', 'UPDATE data pada modul Settings', NULL, '[\"198001012015041001\", \"ttd_nip\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 23:35:08'),
(37, 1, 'UPDATE', 'Settings', 'ttd_jabatan', 'UPDATE data pada modul Settings', NULL, '[\"Direktur Utama\", \"ttd_jabatan\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-17 23:35:08'),
(38, 1, 'UPDATE', 'Users', '1', 'UPDATE data pada modul Users', NULL, '[\"Super Admind\", \"superadmin\", \"admin@gmail.com\", \"0511-123456\", 1]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-18 00:02:08'),
(39, 1, 'UPDATE', 'Users', '1', 'UPDATE data pada modul Users', NULL, '[\"***PASSWORD_HASH***\", 1]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-18 00:02:08'),
(40, 1, 'UPDATE', 'Users', '1', 'UPDATE data pada modul Users', NULL, '[\"Super Admin\", \"superadmin\", \"admin@gmail.com\", \"0511-123456\", 1]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-18 00:02:38'),
(41, 1, 'UPDATE', 'Users', '1', 'UPDATE data pada modul Users', NULL, '[\"***PASSWORD_HASH***\", 1]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-18 00:02:39'),
(42, NULL, 'UPDATE', 'Users', '1', 'UPDATE data pada modul Users', NULL, '[\"***PASSWORD_HASH***\", 1]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-18 20:36:12'),
(43, 1, 'UPDATE', 'Settings', 'nama_website', 'UPDATE data pada modul Settings', NULL, '[\"GedungKita\", \"nama_website\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-18 20:42:31'),
(44, 1, 'UPDATE', 'Settings', 'nama_panjang', 'UPDATE data pada modul Settings', NULL, '[\"Sistem Manajemen Rental Gedung Terpadu\", \"nama_panjang\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-18 20:42:31'),
(45, 1, 'UPDATE', 'Settings', 'app_theme', 'UPDATE data pada modul Settings', NULL, '[\"rose\", \"app_theme\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-18 20:42:31'),
(46, 1, 'UPDATE', 'Settings', 'instansi_nama', 'UPDATE data pada modul Settings', NULL, '[\"PT. Rental Gedung Maju\", \"instansi_nama\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-18 20:42:31'),
(47, 1, 'UPDATE', 'Settings', 'instansi_alamat', 'UPDATE data pada modul Settings', NULL, '[\"Jl. Profesional No.123, Banjarmasin\", \"instansi_alamat\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-18 20:42:31'),
(48, 1, 'UPDATE', 'Settings', 'instansi_telepon', 'UPDATE data pada modul Settings', NULL, '[\"0511-1234567\", \"instansi_telepon\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-18 20:42:31'),
(49, 1, 'UPDATE', 'Settings', 'instansi_email', 'UPDATE data pada modul Settings', NULL, '[\"info@rentalgedung.co.id\", \"instansi_email\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-18 20:42:31'),
(50, 1, 'UPDATE', 'Settings', 'company_address', 'UPDATE data pada modul Settings', NULL, '[\"Jl. Jenderal Sudirman No. Kav 50, Jakarta Selatan, DKI Jakarta 12930\", \"company_address\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-18 20:42:31'),
(51, 1, 'UPDATE', 'Settings', 'company_email', 'UPDATE data pada modul Settings', NULL, '[\"support@gedungkita.com\", \"company_email\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-18 20:42:31'),
(52, 1, 'UPDATE', 'Settings', 'company_phone', 'UPDATE data pada modul Settings', NULL, '[\"+62 812-3456-7890\", \"company_phone\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-18 20:42:31'),
(53, 1, 'UPDATE', 'Settings', 'footer_copyright', 'UPDATE data pada modul Settings', NULL, '[\"© 2025 GedungKita. All rights reserved.\", \"footer_copyright\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-18 20:42:31'),
(54, 1, 'UPDATE', 'Settings', 'social_instagram', 'UPDATE data pada modul Settings', NULL, '[\"https://instagram.com/gedungkita\", \"social_instagram\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-18 20:42:31'),
(55, 1, 'UPDATE', 'Settings', 'social_facebook', 'UPDATE data pada modul Settings', NULL, '[\"https://facebook.com/gedungkita\", \"social_facebook\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-18 20:42:31'),
(56, 1, 'UPDATE', 'Settings', 'ttd_nama', 'UPDATE data pada modul Settings', NULL, '[\"Budi Santoso\", \"ttd_nama\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-18 20:42:31'),
(57, 1, 'UPDATE', 'Settings', 'ttd_nip', 'UPDATE data pada modul Settings', NULL, '[\"198001012015041001\", \"ttd_nip\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-18 20:42:31'),
(58, 1, 'UPDATE', 'Settings', 'ttd_jabatan', 'UPDATE data pada modul Settings', NULL, '[\"Direktur Utama\", \"ttd_jabatan\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-18 20:42:31'),
(59, 1, 'UPDATE', 'Settings', 'nama_website', 'UPDATE data pada modul Settings', NULL, '[\"GedungKita\", \"nama_website\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-18 21:30:11'),
(60, 1, 'UPDATE', 'Settings', 'nama_panjang', 'UPDATE data pada modul Settings', NULL, '[\"Sistem Manajemen Rental Gedung Terpadu\", \"nama_panjang\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-18 21:30:12'),
(61, 1, 'UPDATE', 'Settings', 'app_theme', 'UPDATE data pada modul Settings', NULL, '[\"teal\", \"app_theme\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-18 21:30:12'),
(62, 1, 'UPDATE', 'Settings', 'instansi_nama', 'UPDATE data pada modul Settings', NULL, '[\"PT. Rental Gedung Maju\", \"instansi_nama\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-18 21:30:12'),
(63, 1, 'UPDATE', 'Settings', 'instansi_alamat', 'UPDATE data pada modul Settings', NULL, '[\"Jl. Profesional No.123, Banjarmasin\", \"instansi_alamat\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-18 21:30:12'),
(64, 1, 'UPDATE', 'Settings', 'instansi_telepon', 'UPDATE data pada modul Settings', NULL, '[\"0511-1234567\", \"instansi_telepon\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-18 21:30:12'),
(65, 1, 'UPDATE', 'Settings', 'instansi_email', 'UPDATE data pada modul Settings', NULL, '[\"info@rentalgedung.co.id\", \"instansi_email\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-18 21:30:12'),
(66, 1, 'UPDATE', 'Settings', 'company_address', 'UPDATE data pada modul Settings', NULL, '[\"Jl. Jenderal Sudirman No. Kav 50, Jakarta Selatan, DKI Jakarta 12930\", \"company_address\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-18 21:30:12'),
(67, 1, 'UPDATE', 'Settings', 'company_email', 'UPDATE data pada modul Settings', NULL, '[\"support@gedungkita.com\", \"company_email\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-18 21:30:13'),
(68, 1, 'UPDATE', 'Settings', 'company_phone', 'UPDATE data pada modul Settings', NULL, '[\"+62 812-3456-7890\", \"company_phone\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-18 21:30:13'),
(69, 1, 'UPDATE', 'Settings', 'footer_copyright', 'UPDATE data pada modul Settings', NULL, '[\"© 2025 GedungKita. All rights reserved.\", \"footer_copyright\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-18 21:30:13'),
(70, 1, 'UPDATE', 'Settings', 'social_instagram', 'UPDATE data pada modul Settings', NULL, '[\"https://instagram.com/gedungkita\", \"social_instagram\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-18 21:30:13'),
(71, 1, 'UPDATE', 'Settings', 'social_facebook', 'UPDATE data pada modul Settings', NULL, '[\"https://facebook.com/gedungkita\", \"social_facebook\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-18 21:30:13'),
(72, 1, 'UPDATE', 'Settings', 'ttd_nama', 'UPDATE data pada modul Settings', NULL, '[\"Budi Santoso\", \"ttd_nama\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-18 21:30:13'),
(73, 1, 'UPDATE', 'Settings', 'ttd_nip', 'UPDATE data pada modul Settings', NULL, '[\"198001012015041001\", \"ttd_nip\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-18 21:30:13'),
(74, 1, 'UPDATE', 'Settings', 'ttd_jabatan', 'UPDATE data pada modul Settings', NULL, '[\"Direktur Utama\", \"ttd_jabatan\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-18 21:30:13'),
(75, 1, 'UPDATE', 'Promos', '2', 'UPDATE data pada modul Promos', NULL, '[2]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 00:30:32'),
(76, 1, 'UPDATE', 'Settings', 'nama_website', 'UPDATE data pada modul Settings', NULL, '[\"GedungKita\", \"nama_website\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 00:57:44'),
(77, 1, 'UPDATE', 'Settings', 'nama_panjang', 'UPDATE data pada modul Settings', NULL, '[\"Sistem Manajemen Rental Gedung Terpadu\", \"nama_panjang\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 00:57:44'),
(78, 1, 'UPDATE', 'Settings', 'app_theme', 'UPDATE data pada modul Settings', NULL, '[\"sunset\", \"app_theme\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 00:57:44'),
(79, 1, 'UPDATE', 'Settings', 'instansi_nama', 'UPDATE data pada modul Settings', NULL, '[\"PT. Rental Gedung Maju\", \"instansi_nama\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 00:57:44'),
(80, 1, 'UPDATE', 'Settings', 'instansi_alamat', 'UPDATE data pada modul Settings', NULL, '[\"Jl. Profesional No.123, Banjarmasin\", \"instansi_alamat\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 00:57:44'),
(81, 1, 'UPDATE', 'Settings', 'instansi_telepon', 'UPDATE data pada modul Settings', NULL, '[\"0511-1234567\", \"instansi_telepon\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 00:57:44'),
(82, 1, 'UPDATE', 'Settings', 'instansi_email', 'UPDATE data pada modul Settings', NULL, '[\"info@rentalgedung.co.id\", \"instansi_email\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 00:57:44'),
(83, 1, 'UPDATE', 'Settings', 'company_address', 'UPDATE data pada modul Settings', NULL, '[\"Jl. Jenderal Sudirman No. Kav 50, Jakarta Selatan, DKI Jakarta 12930\", \"company_address\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 00:57:44'),
(84, 1, 'UPDATE', 'Settings', 'company_email', 'UPDATE data pada modul Settings', NULL, '[\"support@gedungkita.com\", \"company_email\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 00:57:44'),
(85, 1, 'UPDATE', 'Settings', 'company_phone', 'UPDATE data pada modul Settings', NULL, '[\"+62 812-3456-7890\", \"company_phone\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 00:57:44'),
(86, 1, 'UPDATE', 'Settings', 'footer_copyright', 'UPDATE data pada modul Settings', NULL, '[\"© 2025 GedungKita. All rights reserved.\", \"footer_copyright\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 00:57:44'),
(87, 1, 'UPDATE', 'Settings', 'social_instagram', 'UPDATE data pada modul Settings', NULL, '[\"https://instagram.com/gedungkita\", \"social_instagram\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 00:57:44'),
(88, 1, 'UPDATE', 'Settings', 'social_facebook', 'UPDATE data pada modul Settings', NULL, '[\"https://facebook.com/gedungkita\", \"social_facebook\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 00:57:44'),
(89, 1, 'UPDATE', 'Settings', 'ttd_nama', 'UPDATE data pada modul Settings', NULL, '[\"Budi Santoso\", \"ttd_nama\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 00:57:44'),
(90, 1, 'UPDATE', 'Settings', 'ttd_nip', 'UPDATE data pada modul Settings', NULL, '[\"198001012015041001\", \"ttd_nip\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 00:57:44'),
(91, 1, 'UPDATE', 'Settings', 'ttd_jabatan', 'UPDATE data pada modul Settings', NULL, '[\"Direktur Utama\", \"ttd_jabatan\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 00:57:44'),
(92, 1, 'UPDATE', 'Settings', 'xendit_api_key', 'UPDATE data pada modul Settings', NULL, '[\"\", \"xendit_api_key\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 00:57:44'),
(93, 1, 'UPDATE', 'Settings', 'xendit_callback_token', 'UPDATE data pada modul Settings', NULL, '[\"\", \"xendit_callback_token\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 00:57:44'),
(94, 1, 'UPDATE', 'Settings', 'nama_website', 'UPDATE data pada modul Settings', NULL, '[\"GedungKita\", \"nama_website\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:00:54'),
(95, 1, 'UPDATE', 'Settings', 'nama_panjang', 'UPDATE data pada modul Settings', NULL, '[\"Sistem Manajemen Rental Gedung Terpadu\", \"nama_panjang\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:00:54'),
(96, 1, 'UPDATE', 'Settings', 'app_theme', 'UPDATE data pada modul Settings', NULL, '[\"sunset\", \"app_theme\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:00:54'),
(97, 1, 'UPDATE', 'Settings', 'public_theme', 'UPDATE data pada modul Settings', NULL, '[\"dark\", \"public_theme\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:00:55'),
(98, 1, 'UPDATE', 'Settings', 'instansi_nama', 'UPDATE data pada modul Settings', NULL, '[\"PT. Rental Gedung Maju\", \"instansi_nama\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:00:55'),
(99, 1, 'UPDATE', 'Settings', 'instansi_alamat', 'UPDATE data pada modul Settings', NULL, '[\"Jl. Profesional No.123, Banjarmasin\", \"instansi_alamat\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:00:55'),
(100, 1, 'UPDATE', 'Settings', 'instansi_telepon', 'UPDATE data pada modul Settings', NULL, '[\"0511-1234567\", \"instansi_telepon\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:00:55'),
(101, 1, 'UPDATE', 'Settings', 'instansi_email', 'UPDATE data pada modul Settings', NULL, '[\"info@rentalgedung.co.id\", \"instansi_email\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:00:55'),
(102, 1, 'UPDATE', 'Settings', 'company_address', 'UPDATE data pada modul Settings', NULL, '[\"Jl. Jenderal Sudirman No. Kav 50, Jakarta Selatan, DKI Jakarta 12930\", \"company_address\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:00:55'),
(103, 1, 'UPDATE', 'Settings', 'company_email', 'UPDATE data pada modul Settings', NULL, '[\"support@gedungkita.com\", \"company_email\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:00:55'),
(104, 1, 'UPDATE', 'Settings', 'company_phone', 'UPDATE data pada modul Settings', NULL, '[\"+62 812-3456-7890\", \"company_phone\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:00:55'),
(105, 1, 'UPDATE', 'Settings', 'footer_copyright', 'UPDATE data pada modul Settings', NULL, '[\"© 2025 GedungKita. All rights reserved.\", \"footer_copyright\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:00:55'),
(106, 1, 'UPDATE', 'Settings', 'social_instagram', 'UPDATE data pada modul Settings', NULL, '[\"https://instagram.com/gedungkita\", \"social_instagram\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:00:55'),
(107, 1, 'UPDATE', 'Settings', 'social_facebook', 'UPDATE data pada modul Settings', NULL, '[\"https://facebook.com/gedungkita\", \"social_facebook\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:00:55'),
(108, 1, 'UPDATE', 'Settings', 'ttd_nama', 'UPDATE data pada modul Settings', NULL, '[\"Budi Santoso\", \"ttd_nama\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:00:55'),
(109, 1, 'UPDATE', 'Settings', 'ttd_nip', 'UPDATE data pada modul Settings', NULL, '[\"198001012015041001\", \"ttd_nip\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:00:55'),
(110, 1, 'UPDATE', 'Settings', 'ttd_jabatan', 'UPDATE data pada modul Settings', NULL, '[\"Direktur Utama\", \"ttd_jabatan\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:00:55'),
(111, 1, 'UPDATE', 'Settings', 'xendit_api_key', 'UPDATE data pada modul Settings', NULL, '[\"\", \"xendit_api_key\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:00:55'),
(112, 1, 'UPDATE', 'Settings', 'xendit_callback_token', 'UPDATE data pada modul Settings', NULL, '[\"\", \"xendit_callback_token\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:00:55'),
(113, 1, 'UPDATE', 'Settings', 'maintenance_mode', 'UPDATE data pada modul Settings', NULL, '[\"0\", \"maintenance_mode\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:00:56'),
(114, 1, 'UPDATE', 'Settings', 'maintenance_message', 'UPDATE data pada modul Settings', NULL, '[\"Website sedang dalam perbaikan. Silakan kembali nanti.\", \"maintenance_message\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:00:56'),
(115, 1, 'UPDATE', 'Settings', 'nama_website', 'UPDATE data pada modul Settings', NULL, '[\"GedungKita\", \"nama_website\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:01:01'),
(116, 1, 'UPDATE', 'Settings', 'nama_panjang', 'UPDATE data pada modul Settings', NULL, '[\"Sistem Manajemen Rental Gedung Terpadu\", \"nama_panjang\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:01:01'),
(117, 1, 'UPDATE', 'Settings', 'app_theme', 'UPDATE data pada modul Settings', NULL, '[\"teal\", \"app_theme\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:01:01'),
(118, 1, 'UPDATE', 'Settings', 'public_theme', 'UPDATE data pada modul Settings', NULL, '[\"dark\", \"public_theme\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:01:01'),
(119, 1, 'UPDATE', 'Settings', 'instansi_nama', 'UPDATE data pada modul Settings', NULL, '[\"PT. Rental Gedung Maju\", \"instansi_nama\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:01:01'),
(120, 1, 'UPDATE', 'Settings', 'instansi_alamat', 'UPDATE data pada modul Settings', NULL, '[\"Jl. Profesional No.123, Banjarmasin\", \"instansi_alamat\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:01:01'),
(121, 1, 'UPDATE', 'Settings', 'instansi_telepon', 'UPDATE data pada modul Settings', NULL, '[\"0511-1234567\", \"instansi_telepon\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:01:01'),
(122, 1, 'UPDATE', 'Settings', 'instansi_email', 'UPDATE data pada modul Settings', NULL, '[\"info@rentalgedung.co.id\", \"instansi_email\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:01:01'),
(123, 1, 'UPDATE', 'Settings', 'company_address', 'UPDATE data pada modul Settings', NULL, '[\"Jl. Jenderal Sudirman No. Kav 50, Jakarta Selatan, DKI Jakarta 12930\", \"company_address\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:01:01'),
(124, 1, 'UPDATE', 'Settings', 'company_email', 'UPDATE data pada modul Settings', NULL, '[\"support@gedungkita.com\", \"company_email\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:01:01'),
(125, 1, 'UPDATE', 'Settings', 'company_phone', 'UPDATE data pada modul Settings', NULL, '[\"+62 812-3456-7890\", \"company_phone\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:01:01'),
(126, 1, 'UPDATE', 'Settings', 'footer_copyright', 'UPDATE data pada modul Settings', NULL, '[\"© 2025 GedungKita. All rights reserved.\", \"footer_copyright\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:01:01'),
(127, 1, 'UPDATE', 'Settings', 'social_instagram', 'UPDATE data pada modul Settings', NULL, '[\"https://instagram.com/gedungkita\", \"social_instagram\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:01:01'),
(128, 1, 'UPDATE', 'Settings', 'social_facebook', 'UPDATE data pada modul Settings', NULL, '[\"https://facebook.com/gedungkita\", \"social_facebook\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:01:01'),
(129, 1, 'UPDATE', 'Settings', 'ttd_nama', 'UPDATE data pada modul Settings', NULL, '[\"Budi Santoso\", \"ttd_nama\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:01:01'),
(130, 1, 'UPDATE', 'Settings', 'ttd_nip', 'UPDATE data pada modul Settings', NULL, '[\"198001012015041001\", \"ttd_nip\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:01:01'),
(131, 1, 'UPDATE', 'Settings', 'ttd_jabatan', 'UPDATE data pada modul Settings', NULL, '[\"Direktur Utama\", \"ttd_jabatan\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:01:01'),
(132, 1, 'UPDATE', 'Settings', 'xendit_api_key', 'UPDATE data pada modul Settings', NULL, '[\"\", \"xendit_api_key\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:01:01'),
(133, 1, 'UPDATE', 'Settings', 'xendit_callback_token', 'UPDATE data pada modul Settings', NULL, '[\"\", \"xendit_callback_token\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:01:01'),
(134, 1, 'UPDATE', 'Settings', 'maintenance_mode', 'UPDATE data pada modul Settings', NULL, '[\"0\", \"maintenance_mode\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:01:01'),
(135, 1, 'UPDATE', 'Settings', 'maintenance_message', 'UPDATE data pada modul Settings', NULL, '[\"Website sedang dalam perbaikan. Silakan kembali nanti.\", \"maintenance_message\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:01:02'),
(136, 1, 'UPDATE', 'Settings', 'nama_website', 'UPDATE data pada modul Settings', NULL, '[\"GedungKita\", \"nama_website\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:29:07'),
(137, 1, 'UPDATE', 'Settings', 'nama_panjang', 'UPDATE data pada modul Settings', NULL, '[\"Sistem Manajemen Rental Gedung Terpadu\", \"nama_panjang\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:29:07'),
(138, 1, 'UPDATE', 'Settings', 'app_theme', 'UPDATE data pada modul Settings', NULL, '[\"sunset\", \"app_theme\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:29:07'),
(139, 1, 'UPDATE', 'Settings', 'maintenance_mode', 'UPDATE data pada modul Settings', NULL, '[\"0\", \"maintenance_mode\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:29:07'),
(140, 1, 'UPDATE', 'Settings', 'maintenance_message', 'UPDATE data pada modul Settings', NULL, '[\"Website sedang dalam perbaikan. Silakan kembali nanti.\", \"maintenance_message\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:29:07'),
(141, 1, 'UPDATE', 'Settings', 'public_theme', 'UPDATE data pada modul Settings', NULL, '[\"teal\", \"public_theme\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:29:07'),
(142, 1, 'UPDATE', 'Settings', 'instansi_nama', 'UPDATE data pada modul Settings', NULL, '[\"PT. Rental Gedung Maju\", \"instansi_nama\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:29:07'),
(143, 1, 'UPDATE', 'Settings', 'instansi_alamat', 'UPDATE data pada modul Settings', NULL, '[\"Jl. Profesional No.123, Banjarmasin\", \"instansi_alamat\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:29:07'),
(144, 1, 'UPDATE', 'Settings', 'instansi_telepon', 'UPDATE data pada modul Settings', NULL, '[\"0511-1234567\", \"instansi_telepon\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:29:07'),
(145, 1, 'UPDATE', 'Settings', 'instansi_email', 'UPDATE data pada modul Settings', NULL, '[\"info@rentalgedung.co.id\", \"instansi_email\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:29:07'),
(146, 1, 'UPDATE', 'Settings', 'company_address', 'UPDATE data pada modul Settings', NULL, '[\"Jl. Jenderal Sudirman No. Kav 50, Jakarta Selatan, DKI Jakarta 12930\", \"company_address\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:29:07'),
(147, 1, 'UPDATE', 'Settings', 'company_email', 'UPDATE data pada modul Settings', NULL, '[\"support@gedungkita.com\", \"company_email\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:29:08'),
(148, 1, 'UPDATE', 'Settings', 'company_phone', 'UPDATE data pada modul Settings', NULL, '[\"+62 812-3456-7890\", \"company_phone\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:29:08'),
(149, 1, 'UPDATE', 'Settings', 'footer_copyright', 'UPDATE data pada modul Settings', NULL, '[\"© 2025 GedungKita. All rights reserved.\", \"footer_copyright\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:29:08'),
(150, 1, 'UPDATE', 'Settings', 'social_instagram', 'UPDATE data pada modul Settings', NULL, '[\"https://instagram.com/gedungkita\", \"social_instagram\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:29:08'),
(151, 1, 'UPDATE', 'Settings', 'social_facebook', 'UPDATE data pada modul Settings', NULL, '[\"https://facebook.com/gedungkita\", \"social_facebook\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:29:08'),
(152, 1, 'UPDATE', 'Settings', 'ttd_nama', 'UPDATE data pada modul Settings', NULL, '[\"Budi Santoso\", \"ttd_nama\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:29:08'),
(153, 1, 'UPDATE', 'Settings', 'ttd_nip', 'UPDATE data pada modul Settings', NULL, '[\"198001012015041001\", \"ttd_nip\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:29:08'),
(154, 1, 'UPDATE', 'Settings', 'ttd_jabatan', 'UPDATE data pada modul Settings', NULL, '[\"Direktur Utama\", \"ttd_jabatan\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:29:08'),
(155, 1, 'UPDATE', 'Settings', 'xendit_api_key', 'UPDATE data pada modul Settings', NULL, '[\"\", \"xendit_api_key\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:29:08'),
(156, 1, 'UPDATE', 'Settings', 'xendit_callback_token', 'UPDATE data pada modul Settings', NULL, '[\"\", \"xendit_callback_token\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:29:08'),
(157, 1, 'UPDATE', 'Settings', 'nama_website', 'UPDATE data pada modul Settings', NULL, '[\"GedungKita\", \"nama_website\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:29:26'),
(158, 1, 'UPDATE', 'Settings', 'nama_panjang', 'UPDATE data pada modul Settings', NULL, '[\"Sistem Manajemen Rental Gedung Terpadu\", \"nama_panjang\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:29:26'),
(159, 1, 'UPDATE', 'Settings', 'app_theme', 'UPDATE data pada modul Settings', NULL, '[\"nature\", \"app_theme\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:29:26'),
(160, 1, 'UPDATE', 'Settings', 'maintenance_mode', 'UPDATE data pada modul Settings', NULL, '[\"0\", \"maintenance_mode\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:29:26'),
(161, 1, 'UPDATE', 'Settings', 'maintenance_message', 'UPDATE data pada modul Settings', NULL, '[\"Website sedang dalam perbaikan. Silakan kembali nanti.\", \"maintenance_message\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:29:26'),
(162, 1, 'UPDATE', 'Settings', 'public_theme', 'UPDATE data pada modul Settings', NULL, '[\"nature\", \"public_theme\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:29:26'),
(163, 1, 'UPDATE', 'Settings', 'instansi_nama', 'UPDATE data pada modul Settings', NULL, '[\"PT. Rental Gedung Maju\", \"instansi_nama\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:29:26'),
(164, 1, 'UPDATE', 'Settings', 'instansi_alamat', 'UPDATE data pada modul Settings', NULL, '[\"Jl. Profesional No.123, Banjarmasin\", \"instansi_alamat\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:29:26'),
(165, 1, 'UPDATE', 'Settings', 'instansi_telepon', 'UPDATE data pada modul Settings', NULL, '[\"0511-1234567\", \"instansi_telepon\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:29:26'),
(166, 1, 'UPDATE', 'Settings', 'instansi_email', 'UPDATE data pada modul Settings', NULL, '[\"info@rentalgedung.co.id\", \"instansi_email\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:29:26'),
(167, 1, 'UPDATE', 'Settings', 'company_address', 'UPDATE data pada modul Settings', NULL, '[\"Jl. Jenderal Sudirman No. Kav 50, Jakarta Selatan, DKI Jakarta 12930\", \"company_address\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:29:26'),
(168, 1, 'UPDATE', 'Settings', 'company_email', 'UPDATE data pada modul Settings', NULL, '[\"support@gedungkita.com\", \"company_email\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:29:26'),
(169, 1, 'UPDATE', 'Settings', 'company_phone', 'UPDATE data pada modul Settings', NULL, '[\"+62 812-3456-7890\", \"company_phone\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:29:26'),
(170, 1, 'UPDATE', 'Settings', 'footer_copyright', 'UPDATE data pada modul Settings', NULL, '[\"© 2025 GedungKita. All rights reserved.\", \"footer_copyright\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:29:26'),
(171, 1, 'UPDATE', 'Settings', 'social_instagram', 'UPDATE data pada modul Settings', NULL, '[\"https://instagram.com/gedungkita\", \"social_instagram\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:29:26'),
(172, 1, 'UPDATE', 'Settings', 'social_facebook', 'UPDATE data pada modul Settings', NULL, '[\"https://facebook.com/gedungkita\", \"social_facebook\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:29:26'),
(173, 1, 'UPDATE', 'Settings', 'ttd_nama', 'UPDATE data pada modul Settings', NULL, '[\"Budi Santoso\", \"ttd_nama\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:29:26'),
(174, 1, 'UPDATE', 'Settings', 'ttd_nip', 'UPDATE data pada modul Settings', NULL, '[\"198001012015041001\", \"ttd_nip\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:29:26'),
(175, 1, 'UPDATE', 'Settings', 'ttd_jabatan', 'UPDATE data pada modul Settings', NULL, '[\"Direktur Utama\", \"ttd_jabatan\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:29:26');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `module`, `record_id`, `description`, `old_value`, `new_value`, `ip_address`, `user_agent`, `created_at`) VALUES
(176, 1, 'UPDATE', 'Settings', 'xendit_api_key', 'UPDATE data pada modul Settings', NULL, '[\"\", \"xendit_api_key\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:29:26'),
(177, 1, 'UPDATE', 'Settings', 'xendit_callback_token', 'UPDATE data pada modul Settings', NULL, '[\"\", \"xendit_callback_token\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:29:26'),
(178, 1, 'UPDATE', 'Settings', 'nama_website', 'UPDATE data pada modul Settings', NULL, '[\"GedungKita\", \"nama_website\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:30:26'),
(179, 1, 'UPDATE', 'Settings', 'nama_panjang', 'UPDATE data pada modul Settings', NULL, '[\"Sistem Manajemen Rental Gedung Terpadu\", \"nama_panjang\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:30:26'),
(180, 1, 'UPDATE', 'Settings', 'app_theme', 'UPDATE data pada modul Settings', NULL, '[\"rose\", \"app_theme\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:30:26'),
(181, 1, 'UPDATE', 'Settings', 'maintenance_mode', 'UPDATE data pada modul Settings', NULL, '[\"0\", \"maintenance_mode\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:30:26'),
(182, 1, 'UPDATE', 'Settings', 'maintenance_message', 'UPDATE data pada modul Settings', NULL, '[\"Website sedang dalam perbaikan. Silakan kembali nanti.\", \"maintenance_message\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:30:26'),
(183, 1, 'UPDATE', 'Settings', 'public_theme', 'UPDATE data pada modul Settings', NULL, '[\"nature\", \"public_theme\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:30:26'),
(184, 1, 'UPDATE', 'Settings', 'instansi_nama', 'UPDATE data pada modul Settings', NULL, '[\"PT. Rental Gedung Maju\", \"instansi_nama\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:30:26'),
(185, 1, 'UPDATE', 'Settings', 'instansi_alamat', 'UPDATE data pada modul Settings', NULL, '[\"Jl. Profesional No.123, Banjarmasin\", \"instansi_alamat\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:30:26'),
(186, 1, 'UPDATE', 'Settings', 'instansi_telepon', 'UPDATE data pada modul Settings', NULL, '[\"0511-1234567\", \"instansi_telepon\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:30:26'),
(187, 1, 'UPDATE', 'Settings', 'instansi_email', 'UPDATE data pada modul Settings', NULL, '[\"info@rentalgedung.co.id\", \"instansi_email\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:30:26'),
(188, 1, 'UPDATE', 'Settings', 'company_address', 'UPDATE data pada modul Settings', NULL, '[\"Jl. Jenderal Sudirman No. Kav 50, Jakarta Selatan, DKI Jakarta 12930\", \"company_address\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:30:26'),
(189, 1, 'UPDATE', 'Settings', 'company_email', 'UPDATE data pada modul Settings', NULL, '[\"support@gedungkita.com\", \"company_email\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:30:26'),
(190, 1, 'UPDATE', 'Settings', 'company_phone', 'UPDATE data pada modul Settings', NULL, '[\"+62 812-3456-7890\", \"company_phone\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:30:26'),
(191, 1, 'UPDATE', 'Settings', 'footer_copyright', 'UPDATE data pada modul Settings', NULL, '[\"© 2025 GedungKita. All rights reserved.\", \"footer_copyright\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:30:26'),
(192, 1, 'UPDATE', 'Settings', 'social_instagram', 'UPDATE data pada modul Settings', NULL, '[\"https://instagram.com/gedungkita\", \"social_instagram\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:30:26'),
(193, 1, 'UPDATE', 'Settings', 'social_facebook', 'UPDATE data pada modul Settings', NULL, '[\"https://facebook.com/gedungkita\", \"social_facebook\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:30:26'),
(194, 1, 'UPDATE', 'Settings', 'ttd_nama', 'UPDATE data pada modul Settings', NULL, '[\"Budi Santoso\", \"ttd_nama\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:30:26'),
(195, 1, 'UPDATE', 'Settings', 'ttd_nip', 'UPDATE data pada modul Settings', NULL, '[\"198001012015041001\", \"ttd_nip\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:30:26'),
(196, 1, 'UPDATE', 'Settings', 'ttd_jabatan', 'UPDATE data pada modul Settings', NULL, '[\"Direktur Utama\", \"ttd_jabatan\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:30:26'),
(197, 1, 'UPDATE', 'Settings', 'xendit_api_key', 'UPDATE data pada modul Settings', NULL, '[\"\", \"xendit_api_key\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:30:26'),
(198, 1, 'UPDATE', 'Settings', 'xendit_callback_token', 'UPDATE data pada modul Settings', NULL, '[\"\", \"xendit_callback_token\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:30:26'),
(199, 1, 'UPDATE', 'Settings', 'ttd_image', 'UPDATE data pada modul Settings', NULL, '[\"uploads/logos/img_6944c72435d1e.png\", \"ttd_image\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:31:48'),
(200, 1, 'UPDATE', 'Settings', 'nama_website', 'UPDATE data pada modul Settings', NULL, '[\"GedungKita\", \"nama_website\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:31:48'),
(201, 1, 'UPDATE', 'Settings', 'nama_panjang', 'UPDATE data pada modul Settings', NULL, '[\"Sistem Manajemen Rental Gedung Terpadu\", \"nama_panjang\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:31:48'),
(202, 1, 'UPDATE', 'Settings', 'app_theme', 'UPDATE data pada modul Settings', NULL, '[\"rose\", \"app_theme\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:31:48'),
(203, 1, 'UPDATE', 'Settings', 'maintenance_mode', 'UPDATE data pada modul Settings', NULL, '[\"0\", \"maintenance_mode\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:31:48'),
(204, 1, 'UPDATE', 'Settings', 'maintenance_message', 'UPDATE data pada modul Settings', NULL, '[\"Website sedang dalam perbaikan. Silakan kembali nanti.\", \"maintenance_message\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:31:48'),
(205, 1, 'UPDATE', 'Settings', 'public_theme', 'UPDATE data pada modul Settings', NULL, '[\"nature\", \"public_theme\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:31:48'),
(206, 1, 'UPDATE', 'Settings', 'instansi_nama', 'UPDATE data pada modul Settings', NULL, '[\"PT. Rental Gedung Maju\", \"instansi_nama\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:31:48'),
(207, 1, 'UPDATE', 'Settings', 'instansi_alamat', 'UPDATE data pada modul Settings', NULL, '[\"Jl. Profesional No.123, Banjarmasin\", \"instansi_alamat\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:31:48'),
(208, 1, 'UPDATE', 'Settings', 'instansi_telepon', 'UPDATE data pada modul Settings', NULL, '[\"0511-1234567\", \"instansi_telepon\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:31:48'),
(209, 1, 'UPDATE', 'Settings', 'instansi_email', 'UPDATE data pada modul Settings', NULL, '[\"info@rentalgedung.co.id\", \"instansi_email\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:31:48'),
(210, 1, 'UPDATE', 'Settings', 'company_address', 'UPDATE data pada modul Settings', NULL, '[\"Jl. Jenderal Sudirman No. Kav 50, Jakarta Selatan, DKI Jakarta 12930\", \"company_address\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:31:48'),
(211, 1, 'UPDATE', 'Settings', 'company_email', 'UPDATE data pada modul Settings', NULL, '[\"support@gedungkita.com\", \"company_email\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:31:48'),
(212, 1, 'UPDATE', 'Settings', 'company_phone', 'UPDATE data pada modul Settings', NULL, '[\"+62 812-3456-7890\", \"company_phone\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:31:48'),
(213, 1, 'UPDATE', 'Settings', 'footer_copyright', 'UPDATE data pada modul Settings', NULL, '[\"© 2025 GedungKita. All rights reserved.\", \"footer_copyright\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:31:48'),
(214, 1, 'UPDATE', 'Settings', 'social_instagram', 'UPDATE data pada modul Settings', NULL, '[\"https://instagram.com/gedungkita\", \"social_instagram\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:31:48'),
(215, 1, 'UPDATE', 'Settings', 'social_facebook', 'UPDATE data pada modul Settings', NULL, '[\"https://facebook.com/gedungkita\", \"social_facebook\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:31:48'),
(216, 1, 'UPDATE', 'Settings', 'ttd_nama', 'UPDATE data pada modul Settings', NULL, '[\"Budi Santoso\", \"ttd_nama\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:31:48'),
(217, 1, 'UPDATE', 'Settings', 'ttd_nip', 'UPDATE data pada modul Settings', NULL, '[\"198001012015041001\", \"ttd_nip\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:31:49'),
(218, 1, 'UPDATE', 'Settings', 'ttd_jabatan', 'UPDATE data pada modul Settings', NULL, '[\"Direktur Utama\", \"ttd_jabatan\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:31:49'),
(219, 1, 'UPDATE', 'Settings', 'xendit_api_key', 'UPDATE data pada modul Settings', NULL, '[\"\", \"xendit_api_key\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:31:49'),
(220, 1, 'UPDATE', 'Settings', 'xendit_callback_token', 'UPDATE data pada modul Settings', NULL, '[\"\", \"xendit_callback_token\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:31:49'),
(221, 1, 'UPDATE', 'Settings', 'nama_website', 'UPDATE data pada modul Settings', NULL, '[\"GedungKita\", \"nama_website\"]', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2025-12-19 03:37:45'),
(222, 1, 'UPDATE', 'Settings', 'nama_panjang', 'UPDATE data pada modul Settings', NULL, '[\"Sistem Manajemen Rental Gedung Terpadu\", \"nama_panjang\"]', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2025-12-19 03:37:45'),
(223, 1, 'UPDATE', 'Settings', 'app_theme', 'UPDATE data pada modul Settings', NULL, '[\"indigo\", \"app_theme\"]', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2025-12-19 03:37:45'),
(224, 1, 'UPDATE', 'Settings', 'maintenance_mode', 'UPDATE data pada modul Settings', NULL, '[\"0\", \"maintenance_mode\"]', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2025-12-19 03:37:45'),
(225, 1, 'UPDATE', 'Settings', 'maintenance_message', 'UPDATE data pada modul Settings', NULL, '[\"Website sedang dalam perbaikan. Silakan kembali nanti.\", \"maintenance_message\"]', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2025-12-19 03:37:45'),
(226, 1, 'UPDATE', 'Settings', 'public_theme', 'UPDATE data pada modul Settings', NULL, '[\"sunset\", \"public_theme\"]', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2025-12-19 03:37:45'),
(227, 1, 'UPDATE', 'Settings', 'instansi_nama', 'UPDATE data pada modul Settings', NULL, '[\"PT. Rental Gedung Maju\", \"instansi_nama\"]', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2025-12-19 03:37:45'),
(228, 1, 'UPDATE', 'Settings', 'instansi_alamat', 'UPDATE data pada modul Settings', NULL, '[\"Jl. Profesional No.123, Banjarmasin\", \"instansi_alamat\"]', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2025-12-19 03:37:45'),
(229, 1, 'UPDATE', 'Settings', 'instansi_telepon', 'UPDATE data pada modul Settings', NULL, '[\"0511-1234567\", \"instansi_telepon\"]', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2025-12-19 03:37:45'),
(230, 1, 'UPDATE', 'Settings', 'instansi_email', 'UPDATE data pada modul Settings', NULL, '[\"info@rentalgedung.co.id\", \"instansi_email\"]', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2025-12-19 03:37:45'),
(231, 1, 'UPDATE', 'Settings', 'company_address', 'UPDATE data pada modul Settings', NULL, '[\"Jl. Jenderal Sudirman No. Kav 50, Jakarta Selatan, DKI Jakarta 12930\", \"company_address\"]', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2025-12-19 03:37:45'),
(232, 1, 'UPDATE', 'Settings', 'company_email', 'UPDATE data pada modul Settings', NULL, '[\"support@gedungkita.com\", \"company_email\"]', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2025-12-19 03:37:45'),
(233, 1, 'UPDATE', 'Settings', 'company_phone', 'UPDATE data pada modul Settings', NULL, '[\"+62 812-3456-7890\", \"company_phone\"]', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2025-12-19 03:37:45'),
(234, 1, 'UPDATE', 'Settings', 'footer_copyright', 'UPDATE data pada modul Settings', NULL, '[\"© 2025 GedungKita. All rights reserved.\", \"footer_copyright\"]', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2025-12-19 03:37:45'),
(235, 1, 'UPDATE', 'Settings', 'social_instagram', 'UPDATE data pada modul Settings', NULL, '[\"https://instagram.com/gedungkita\", \"social_instagram\"]', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2025-12-19 03:37:46'),
(236, 1, 'UPDATE', 'Settings', 'social_facebook', 'UPDATE data pada modul Settings', NULL, '[\"https://facebook.com/gedungkita\", \"social_facebook\"]', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2025-12-19 03:37:46'),
(237, 1, 'UPDATE', 'Settings', 'ttd_nama', 'UPDATE data pada modul Settings', NULL, '[\"Budi Santoso\", \"ttd_nama\"]', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2025-12-19 03:37:46'),
(238, 1, 'UPDATE', 'Settings', 'ttd_nip', 'UPDATE data pada modul Settings', NULL, '[\"198001012015041001\", \"ttd_nip\"]', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2025-12-19 03:37:46'),
(239, 1, 'UPDATE', 'Settings', 'ttd_jabatan', 'UPDATE data pada modul Settings', NULL, '[\"Direktur Utama\", \"ttd_jabatan\"]', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2025-12-19 03:37:46'),
(240, 1, 'UPDATE', 'Settings', 'xendit_api_key', 'UPDATE data pada modul Settings', NULL, '[\"\", \"xendit_api_key\"]', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2025-12-19 03:37:46'),
(241, 1, 'UPDATE', 'Settings', 'xendit_callback_token', 'UPDATE data pada modul Settings', NULL, '[\"\", \"xendit_callback_token\"]', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2025-12-19 03:37:46'),
(242, 1, 'UPDATE', 'Settings', 'public_hero_image', 'UPDATE data pada modul Settings', NULL, '[\"uploads/hero/img_6944c91008605.jpg\", \"public_hero_image\"]', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2025-12-19 03:40:00'),
(243, 1, 'UPDATE', 'Settings', 'nama_website', 'UPDATE data pada modul Settings', NULL, '[\"GedungKita\", \"nama_website\"]', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2025-12-19 03:40:00'),
(244, 1, 'UPDATE', 'Settings', 'nama_panjang', 'UPDATE data pada modul Settings', NULL, '[\"Sistem Manajemen Rental Gedung Terpadu\", \"nama_panjang\"]', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2025-12-19 03:40:00'),
(245, 1, 'UPDATE', 'Settings', 'app_theme', 'UPDATE data pada modul Settings', NULL, '[\"indigo\", \"app_theme\"]', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2025-12-19 03:40:00'),
(246, 1, 'UPDATE', 'Settings', 'maintenance_mode', 'UPDATE data pada modul Settings', NULL, '[\"0\", \"maintenance_mode\"]', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2025-12-19 03:40:00'),
(247, 1, 'UPDATE', 'Settings', 'maintenance_message', 'UPDATE data pada modul Settings', NULL, '[\"Website sedang dalam perbaikan. Silakan kembali nanti.\", \"maintenance_message\"]', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2025-12-19 03:40:00'),
(248, 1, 'UPDATE', 'Settings', 'public_theme', 'UPDATE data pada modul Settings', NULL, '[\"sunset\", \"public_theme\"]', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2025-12-19 03:40:00'),
(249, 1, 'UPDATE', 'Settings', 'instansi_nama', 'UPDATE data pada modul Settings', NULL, '[\"PT. Rental Gedung Maju\", \"instansi_nama\"]', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2025-12-19 03:40:00'),
(250, 1, 'UPDATE', 'Settings', 'instansi_alamat', 'UPDATE data pada modul Settings', NULL, '[\"Jl. Profesional No.123, Banjarmasin\", \"instansi_alamat\"]', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2025-12-19 03:40:00'),
(251, 1, 'UPDATE', 'Settings', 'instansi_telepon', 'UPDATE data pada modul Settings', NULL, '[\"0511-1234567\", \"instansi_telepon\"]', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2025-12-19 03:40:00'),
(252, 1, 'UPDATE', 'Settings', 'instansi_email', 'UPDATE data pada modul Settings', NULL, '[\"info@rentalgedung.co.id\", \"instansi_email\"]', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2025-12-19 03:40:00'),
(253, 1, 'UPDATE', 'Settings', 'company_address', 'UPDATE data pada modul Settings', NULL, '[\"Jl. Jenderal Sudirman No. Kav 50, Jakarta Selatan, DKI Jakarta 12930\", \"company_address\"]', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2025-12-19 03:40:00'),
(254, 1, 'UPDATE', 'Settings', 'company_email', 'UPDATE data pada modul Settings', NULL, '[\"support@gedungkita.com\", \"company_email\"]', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2025-12-19 03:40:00'),
(255, 1, 'UPDATE', 'Settings', 'company_phone', 'UPDATE data pada modul Settings', NULL, '[\"+62 812-3456-7890\", \"company_phone\"]', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2025-12-19 03:40:00'),
(256, 1, 'UPDATE', 'Settings', 'footer_copyright', 'UPDATE data pada modul Settings', NULL, '[\"© 2025 GedungKita. All rights reserved.\", \"footer_copyright\"]', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2025-12-19 03:40:00'),
(257, 1, 'UPDATE', 'Settings', 'social_instagram', 'UPDATE data pada modul Settings', NULL, '[\"https://instagram.com/gedungkita\", \"social_instagram\"]', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2025-12-19 03:40:00'),
(258, 1, 'UPDATE', 'Settings', 'social_facebook', 'UPDATE data pada modul Settings', NULL, '[\"https://facebook.com/gedungkita\", \"social_facebook\"]', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2025-12-19 03:40:00'),
(259, 1, 'UPDATE', 'Settings', 'ttd_nama', 'UPDATE data pada modul Settings', NULL, '[\"Budi Santoso\", \"ttd_nama\"]', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2025-12-19 03:40:00'),
(260, 1, 'UPDATE', 'Settings', 'ttd_nip', 'UPDATE data pada modul Settings', NULL, '[\"198001012015041001\", \"ttd_nip\"]', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2025-12-19 03:40:00'),
(261, 1, 'UPDATE', 'Settings', 'ttd_jabatan', 'UPDATE data pada modul Settings', NULL, '[\"Direktur Utama\", \"ttd_jabatan\"]', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2025-12-19 03:40:00'),
(262, 1, 'UPDATE', 'Settings', 'xendit_api_key', 'UPDATE data pada modul Settings', NULL, '[\"\", \"xendit_api_key\"]', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2025-12-19 03:40:00'),
(263, 1, 'UPDATE', 'Settings', 'xendit_callback_token', 'UPDATE data pada modul Settings', NULL, '[\"\", \"xendit_callback_token\"]', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2025-12-19 03:40:00'),
(264, 1, 'UPDATE', 'Settings', 'public_hero_image', 'UPDATE data pada modul Settings', NULL, '[\"uploads/hero/img_6944c93207aae.jpg\", \"public_hero_image\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:40:34'),
(265, 1, 'UPDATE', 'Settings', 'nama_website', 'UPDATE data pada modul Settings', NULL, '[\"GedungKita\", \"nama_website\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:40:34'),
(266, 1, 'UPDATE', 'Settings', 'nama_panjang', 'UPDATE data pada modul Settings', NULL, '[\"Sistem Manajemen Rental Gedung Terpadu\", \"nama_panjang\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:40:34'),
(267, 1, 'UPDATE', 'Settings', 'app_theme', 'UPDATE data pada modul Settings', NULL, '[\"indigo\", \"app_theme\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:40:34'),
(268, 1, 'UPDATE', 'Settings', 'maintenance_mode', 'UPDATE data pada modul Settings', NULL, '[\"0\", \"maintenance_mode\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:40:34'),
(269, 1, 'UPDATE', 'Settings', 'maintenance_message', 'UPDATE data pada modul Settings', NULL, '[\"Website sedang dalam perbaikan. Silakan kembali nanti.\", \"maintenance_message\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:40:34'),
(270, 1, 'UPDATE', 'Settings', 'public_theme', 'UPDATE data pada modul Settings', NULL, '[\"sunset\", \"public_theme\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:40:34'),
(271, 1, 'UPDATE', 'Settings', 'instansi_nama', 'UPDATE data pada modul Settings', NULL, '[\"PT. Rental Gedung Maju\", \"instansi_nama\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:40:34'),
(272, 1, 'UPDATE', 'Settings', 'instansi_alamat', 'UPDATE data pada modul Settings', NULL, '[\"Jl. Profesional No.123, Banjarmasin\", \"instansi_alamat\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:40:34'),
(273, 1, 'UPDATE', 'Settings', 'instansi_telepon', 'UPDATE data pada modul Settings', NULL, '[\"0511-1234567\", \"instansi_telepon\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:40:34'),
(274, 1, 'UPDATE', 'Settings', 'instansi_email', 'UPDATE data pada modul Settings', NULL, '[\"info@rentalgedung.co.id\", \"instansi_email\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:40:34'),
(275, 1, 'UPDATE', 'Settings', 'company_address', 'UPDATE data pada modul Settings', NULL, '[\"Jl. Jenderal Sudirman No. Kav 50, Jakarta Selatan, DKI Jakarta 12930\", \"company_address\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:40:34'),
(276, 1, 'UPDATE', 'Settings', 'company_email', 'UPDATE data pada modul Settings', NULL, '[\"support@gedungkita.com\", \"company_email\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:40:34'),
(277, 1, 'UPDATE', 'Settings', 'company_phone', 'UPDATE data pada modul Settings', NULL, '[\"+62 812-3456-7890\", \"company_phone\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:40:34'),
(278, 1, 'UPDATE', 'Settings', 'footer_copyright', 'UPDATE data pada modul Settings', NULL, '[\"© 2025 GedungKita. All rights reserved.\", \"footer_copyright\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:40:34'),
(279, 1, 'UPDATE', 'Settings', 'social_instagram', 'UPDATE data pada modul Settings', NULL, '[\"https://instagram.com/gedungkita\", \"social_instagram\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:40:34'),
(280, 1, 'UPDATE', 'Settings', 'social_facebook', 'UPDATE data pada modul Settings', NULL, '[\"https://facebook.com/gedungkita\", \"social_facebook\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:40:34'),
(281, 1, 'UPDATE', 'Settings', 'ttd_nama', 'UPDATE data pada modul Settings', NULL, '[\"Budi Santoso\", \"ttd_nama\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:40:34'),
(282, 1, 'UPDATE', 'Settings', 'ttd_nip', 'UPDATE data pada modul Settings', NULL, '[\"198001012015041001\", \"ttd_nip\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:40:34'),
(283, 1, 'UPDATE', 'Settings', 'ttd_jabatan', 'UPDATE data pada modul Settings', NULL, '[\"Direktur Utama\", \"ttd_jabatan\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:40:34'),
(284, 1, 'UPDATE', 'Settings', 'xendit_api_key', 'UPDATE data pada modul Settings', NULL, '[\"\", \"xendit_api_key\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:40:34'),
(285, 1, 'UPDATE', 'Settings', 'xendit_callback_token', 'UPDATE data pada modul Settings', NULL, '[\"\", \"xendit_callback_token\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:40:34'),
(286, 1, 'UPDATE', 'Settings', 'public_hero_image', 'UPDATE data pada modul Settings', NULL, '[\"uploads/hero/img_6944cb3aee39f.jpg\", \"public_hero_image\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:49:15'),
(287, 1, 'UPDATE', 'Settings', 'nama_website', 'UPDATE data pada modul Settings', NULL, '[\"GedungKita\", \"nama_website\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:49:15'),
(288, 1, 'UPDATE', 'Settings', 'nama_panjang', 'UPDATE data pada modul Settings', NULL, '[\"Sistem Manajemen Rental Gedung Terpadu\", \"nama_panjang\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:49:15'),
(289, 1, 'UPDATE', 'Settings', 'app_theme', 'UPDATE data pada modul Settings', NULL, '[\"indigo\", \"app_theme\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:49:15'),
(290, 1, 'UPDATE', 'Settings', 'maintenance_mode', 'UPDATE data pada modul Settings', NULL, '[\"0\", \"maintenance_mode\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:49:15'),
(291, 1, 'UPDATE', 'Settings', 'maintenance_message', 'UPDATE data pada modul Settings', NULL, '[\"Website sedang dalam perbaikan. Silakan kembali nanti.\", \"maintenance_message\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:49:15'),
(292, 1, 'UPDATE', 'Settings', 'public_theme', 'UPDATE data pada modul Settings', NULL, '[\"sunset\", \"public_theme\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:49:15'),
(293, 1, 'UPDATE', 'Settings', 'instansi_nama', 'UPDATE data pada modul Settings', NULL, '[\"PT. Rental Gedung Maju\", \"instansi_nama\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:49:15'),
(294, 1, 'UPDATE', 'Settings', 'instansi_alamat', 'UPDATE data pada modul Settings', NULL, '[\"Jl. Profesional No.123, Banjarmasin\", \"instansi_alamat\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:49:15'),
(295, 1, 'UPDATE', 'Settings', 'instansi_telepon', 'UPDATE data pada modul Settings', NULL, '[\"0511-1234567\", \"instansi_telepon\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:49:15'),
(296, 1, 'UPDATE', 'Settings', 'instansi_email', 'UPDATE data pada modul Settings', NULL, '[\"info@rentalgedung.co.id\", \"instansi_email\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:49:15'),
(297, 1, 'UPDATE', 'Settings', 'company_address', 'UPDATE data pada modul Settings', NULL, '[\"Jl. Jenderal Sudirman No. Kav 50, Jakarta Selatan, DKI Jakarta 12930\", \"company_address\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:49:15'),
(298, 1, 'UPDATE', 'Settings', 'company_email', 'UPDATE data pada modul Settings', NULL, '[\"support@gedungkita.com\", \"company_email\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:49:15'),
(299, 1, 'UPDATE', 'Settings', 'company_phone', 'UPDATE data pada modul Settings', NULL, '[\"+62 812-3456-7890\", \"company_phone\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:49:15'),
(300, 1, 'UPDATE', 'Settings', 'footer_copyright', 'UPDATE data pada modul Settings', NULL, '[\"© 2025 GedungKita. All rights reserved.\", \"footer_copyright\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:49:15'),
(301, 1, 'UPDATE', 'Settings', 'social_instagram', 'UPDATE data pada modul Settings', NULL, '[\"https://instagram.com/gedungkita\", \"social_instagram\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:49:15'),
(302, 1, 'UPDATE', 'Settings', 'social_facebook', 'UPDATE data pada modul Settings', NULL, '[\"https://facebook.com/gedungkita\", \"social_facebook\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:49:15'),
(303, 1, 'UPDATE', 'Settings', 'ttd_nama', 'UPDATE data pada modul Settings', NULL, '[\"Budi Santoso\", \"ttd_nama\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:49:15'),
(304, 1, 'UPDATE', 'Settings', 'ttd_nip', 'UPDATE data pada modul Settings', NULL, '[\"198001012015041001\", \"ttd_nip\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:49:16'),
(305, 1, 'UPDATE', 'Settings', 'ttd_jabatan', 'UPDATE data pada modul Settings', NULL, '[\"Direktur Utama\", \"ttd_jabatan\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:49:16'),
(306, 1, 'UPDATE', 'Settings', 'xendit_api_key', 'UPDATE data pada modul Settings', NULL, '[\"\", \"xendit_api_key\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:49:16'),
(307, 1, 'UPDATE', 'Settings', 'xendit_callback_token', 'UPDATE data pada modul Settings', NULL, '[\"\", \"xendit_callback_token\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:49:16'),
(308, 1, 'UPDATE', 'Settings', 'logo_url', 'UPDATE data pada modul Settings', NULL, '[\"uploads/logos/img_6944cb69e0a4b.png\", \"logo_url\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:50:02'),
(309, 1, 'UPDATE', 'Settings', 'nama_website', 'UPDATE data pada modul Settings', NULL, '[\"GedungKita\", \"nama_website\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:50:02'),
(310, 1, 'UPDATE', 'Settings', 'nama_panjang', 'UPDATE data pada modul Settings', NULL, '[\"Sistem Manajemen Rental Gedung Terpadu\", \"nama_panjang\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:50:02'),
(311, 1, 'UPDATE', 'Settings', 'app_theme', 'UPDATE data pada modul Settings', NULL, '[\"indigo\", \"app_theme\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:50:02'),
(312, 1, 'UPDATE', 'Settings', 'maintenance_mode', 'UPDATE data pada modul Settings', NULL, '[\"0\", \"maintenance_mode\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:50:02'),
(313, 1, 'UPDATE', 'Settings', 'maintenance_message', 'UPDATE data pada modul Settings', NULL, '[\"Website sedang dalam perbaikan. Silakan kembali nanti.\", \"maintenance_message\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:50:02'),
(314, 1, 'UPDATE', 'Settings', 'public_theme', 'UPDATE data pada modul Settings', NULL, '[\"sunset\", \"public_theme\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:50:02'),
(315, 1, 'UPDATE', 'Settings', 'instansi_nama', 'UPDATE data pada modul Settings', NULL, '[\"PT. Rental Gedung Maju\", \"instansi_nama\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:50:02'),
(316, 1, 'UPDATE', 'Settings', 'instansi_alamat', 'UPDATE data pada modul Settings', NULL, '[\"Jl. Profesional No.123, Banjarmasin\", \"instansi_alamat\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:50:02'),
(317, 1, 'UPDATE', 'Settings', 'instansi_telepon', 'UPDATE data pada modul Settings', NULL, '[\"0511-1234567\", \"instansi_telepon\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:50:02'),
(318, 1, 'UPDATE', 'Settings', 'instansi_email', 'UPDATE data pada modul Settings', NULL, '[\"info@rentalgedung.co.id\", \"instansi_email\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:50:02'),
(319, 1, 'UPDATE', 'Settings', 'company_address', 'UPDATE data pada modul Settings', NULL, '[\"Jl. Jenderal Sudirman No. Kav 50, Jakarta Selatan, DKI Jakarta 12930\", \"company_address\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:50:02'),
(320, 1, 'UPDATE', 'Settings', 'company_email', 'UPDATE data pada modul Settings', NULL, '[\"support@gedungkita.com\", \"company_email\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:50:02'),
(321, 1, 'UPDATE', 'Settings', 'company_phone', 'UPDATE data pada modul Settings', NULL, '[\"+62 812-3456-7890\", \"company_phone\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:50:02'),
(322, 1, 'UPDATE', 'Settings', 'footer_copyright', 'UPDATE data pada modul Settings', NULL, '[\"© 2025 GedungKita. All rights reserved.\", \"footer_copyright\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:50:02'),
(323, 1, 'UPDATE', 'Settings', 'social_instagram', 'UPDATE data pada modul Settings', NULL, '[\"https://instagram.com/gedungkita\", \"social_instagram\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:50:02'),
(324, 1, 'UPDATE', 'Settings', 'social_facebook', 'UPDATE data pada modul Settings', NULL, '[\"https://facebook.com/gedungkita\", \"social_facebook\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:50:02'),
(325, 1, 'UPDATE', 'Settings', 'ttd_nama', 'UPDATE data pada modul Settings', NULL, '[\"Budi Santoso\", \"ttd_nama\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:50:02'),
(326, 1, 'UPDATE', 'Settings', 'ttd_nip', 'UPDATE data pada modul Settings', NULL, '[\"198001012015041001\", \"ttd_nip\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:50:02'),
(327, 1, 'UPDATE', 'Settings', 'ttd_jabatan', 'UPDATE data pada modul Settings', NULL, '[\"Direktur Utama\", \"ttd_jabatan\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:50:02'),
(328, 1, 'UPDATE', 'Settings', 'xendit_api_key', 'UPDATE data pada modul Settings', NULL, '[\"\", \"xendit_api_key\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:50:02'),
(329, 1, 'UPDATE', 'Settings', 'xendit_callback_token', 'UPDATE data pada modul Settings', NULL, '[\"\", \"xendit_callback_token\"]', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 03:50:02');

-- --------------------------------------------------------

--
-- Table structure for table `booking`
--

CREATE TABLE `booking` (
  `id` int(11) NOT NULL,
  `booking_code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `penyewa_id` int(11) NOT NULL,
  `gedung_id` int(11) NOT NULL,
  `promo_id` int(11) DEFAULT NULL,
  `tanggal_mulai` date NOT NULL,
  `durasi_hari` int(11) NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `total_harga` decimal(12,2) NOT NULL,
  `potongan_harga` decimal(12,2) DEFAULT '0.00',
  `total_bayar` decimal(12,2) DEFAULT '0.00',
  `status` enum('pending','disetujui','ditolak','selesai','batal') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `catatan_admin` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fasilitas`
--

CREATE TABLE `fasilitas` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `grup` enum('basic','pro','premium') COLLATE utf8mb4_unicode_ci DEFAULT 'basic',
  `harga_tambahan` decimal(10,2) DEFAULT '0.00',
  `is_active` tinyint(1) DEFAULT '1',
  `urutan` int(11) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fasilitas`
--

INSERT INTO `fasilitas` (`id`, `nama`, `icon`, `grup`, `harga_tambahan`, `is_active`, `urutan`, `created_at`, `created_by`, `updated_at`, `deleted_at`) VALUES
(1, 'test', '', 'basic', 10000.00, 1, 0, '2025-12-17 21:20:47', 1, '2025-12-17 21:21:12', '2025-12-17 21:21:12'),
(2, 'test', 'fa-solid fa-wifi', 'pro', 1000000.00, 1, 0, '2025-12-17 21:20:57', 1, '2025-12-17 23:02:50', '2025-12-17 23:02:50'),
(3, 'test', 'fa-solid fa-wifi', 'premium', 1000000.00, 1, 0, '2025-12-17 21:21:02', 1, '2025-12-17 21:22:06', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `gedung`
--

CREATE TABLE `gedung` (
  `id` int(11) NOT NULL,
  `nama` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `harga_per_hari` decimal(12,2) NOT NULL,
  `luas_m2` int(11) DEFAULT NULL,
  `kapasitas_orang` int(11) DEFAULT NULL,
  `alamat_lengkap` text COLLATE utf8mb4_unicode_ci,
  `status` enum('tersedia','maintenance','full_booked') COLLATE utf8mb4_unicode_ci DEFAULT 'tersedia',
  `foto_utama` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `kategori_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `gedung`
--

INSERT INTO `gedung` (`id`, `nama`, `deskripsi`, `harga_per_hari`, `luas_m2`, `kapasitas_orang`, `alamat_lengkap`, `status`, `foto_utama`, `created_by`, `created_at`, `deleted_at`, `kategori_id`) VALUES
(5, 'test2', 'scddsc', 100000.00, 23, 11222, 'scsdc', 'tersedia', 'uploads/gedung/gedung_69422ee70e06c.jpg', 1, '2025-12-17 04:17:43', NULL, NULL),
(6, 'cdcsdcdc', 'vfvdfv', 11313100.00, 32323, 32, 'dvdvfv', 'tersedia', 'uploads/gedung/gedung_694231270ee12.jpeg', 1, '2025-12-17 04:27:19', NULL, NULL),
(7, 'vfvdv', 'cdscdcd', 32323.00, 232, 332, 'cdscsdc', 'maintenance', 'uploads/gedung/gedung_694231369a401.jpg', 1, '2025-12-17 04:27:34', NULL, NULL),
(8, 'csdcsdc', 'cdscsdc', 212100.00, 32, 13, 'dcscdsc', 'full_booked', 'uploads/gedung/gedung_69423150ea5e6.jpg', 1, '2025-12-17 04:27:48', NULL, NULL),
(9, 'wcds', 'cdcdcc', 200.00, 31, 212, 'cdcdsc', 'full_booked', 'uploads/gedung/gedung_6942317d755b5.png', 1, '2025-12-17 04:28:36', NULL, NULL),
(10, 'cdcsdc', 'csddsc', 21.00, 22, 21, 'cdscdscsdcs', 'maintenance', 'uploads/gedung/gedung_6942318e3f778.png', 1, '2025-12-17 04:29:02', NULL, NULL),
(12, 'dcsdc', 'dscsdcs', 212.00, 211, 2121, 'cdcsdcs', 'tersedia', 'uploads/gedung/gedung_694231abdec6f.jpg', 1, '2025-12-17 04:29:31', NULL, NULL),
(13, 'cdscsdc', 'cddscd', 121.00, 211, 21, '2dwcds', 'tersedia', 'uploads/gedung/gedung_694231b8d7d3f.png', 1, '2025-12-17 04:29:44', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `gedung_fasilitas`
--

CREATE TABLE `gedung_fasilitas` (
  `gedung_id` int(11) NOT NULL,
  `fasilitas_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gedung_foto`
--

CREATE TABLE `gedung_foto` (
  `id` int(11) NOT NULL,
  `gedung_id` int(11) NOT NULL,
  `filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_primary` tinyint(1) DEFAULT '0',
  `urutan` int(11) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jadwal`
--

CREATE TABLE `jadwal` (
  `id` int(11) NOT NULL,
  `gedung_id` int(11) NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `status` enum('available','booked','maintenance') COLLATE utf8mb4_unicode_ci DEFAULT 'available',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `booking_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kategori_gedung`
--

CREATE TABLE `kategori_gedung` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `icon` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `warna` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT '#3b82f6',
  `is_active` tinyint(1) DEFAULT '1',
  `urutan` int(11) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `laporan`
--

CREATE TABLE `laporan` (
  `id` int(11) NOT NULL,
  `jenis` enum('harian','mingguan','bulanan','booking_status','revenue') COLLATE utf8mb4_unicode_ci NOT NULL,
  `periode` date NOT NULL,
  `data_json` json DEFAULT NULL,
  `filename_pdf` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pelanggan`
--

CREATE TABLE `pelanggan` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `no_ktp` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alamat` text COLLATE utf8mb4_unicode_ci,
  `pekerjaan` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `perusahaan` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `promos`
--

CREATE TABLE `promos` (
  `id` int(11) NOT NULL,
  `kode_promo` varchar(50) NOT NULL,
  `nama_promo` varchar(100) NOT NULL,
  `tipe` enum('persen','nominal') NOT NULL DEFAULT 'persen',
  `nilai` decimal(12,2) NOT NULL,
  `min_transaksi` decimal(12,2) DEFAULT '0.00',
  `maks_potongan` decimal(12,2) DEFAULT NULL,
  `kuota` int(11) DEFAULT NULL,
  `kuota_terpakai` int(11) DEFAULT '0',
  `tanggal_mulai` date NOT NULL,
  `tanggal_berakhir` date NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `promos`
--

INSERT INTO `promos` (`id`, `kode_promo`, `nama_promo`, `tipe`, `nilai`, `min_transaksi`, `maks_potongan`, `kuota`, `kuota_terpakai`, `tanggal_mulai`, `tanggal_berakhir`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'test', 'diskon tes', 'persen', 10000.00, 0.00, 10000000.00, 20, 0, '2025-12-14', '2025-12-31', 1, '2025-12-17 21:27:14', '2025-12-17 21:27:14', NULL),
(2, 'test2', 'diskon tes lagi', 'nominal', 100000.00, 0.00, NULL, 90, 0, '2025-12-07', '2025-12-18', 0, '2025-12-17 21:30:25', '2025-12-19 00:30:32', '2025-12-19 00:30:32');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `gedung_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(1) NOT NULL,
  `komentar` text,
  `tampilkan` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('text','textarea','number','email','password','image','color','select') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'text',
  `group` enum('institusi','kop_surat','branding','sistem','payment') COLLATE utf8mb4_unicode_ci NOT NULL,
  `order` int(11) DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `key`, `value`, `type`, `group`, `order`, `updated_at`) VALUES
(1, 'nama_website', 'GedungKita', 'text', 'branding', 1, '2025-12-19 03:50:02'),
(2, 'nama_panjang', 'Sistem Manajemen Rental Gedung Terpadu', 'text', 'branding', 2, '2025-12-19 03:50:02'),
(3, 'logo_url', 'uploads/logos/img_6944cb69e0a4b.png', 'image', 'branding', 3, '2025-12-19 03:50:01'),
(4, 'favicon_url', 'uploads/favicon/img_6941a21ba2126.png', 'image', 'branding', 4, '2025-12-16 18:16:59'),
(5, 'theme_color', '#3b82f6', 'text', 'branding', 5, '2025-12-16 16:52:18'),
(6, 'instansi_nama', 'PT. Rental Gedung Maju', 'text', 'institusi', 10, '2025-12-19 03:50:02'),
(7, 'instansi_alamat', 'Jl. Profesional No.123, Banjarmasin', 'textarea', 'institusi', 11, '2025-12-19 03:50:02'),
(8, 'instansi_telepon', '0511-1234567', 'text', 'institusi', 12, '2025-12-19 03:50:02'),
(9, 'instansi_email', 'info@rentalgedung.co.id', 'text', 'institusi', 13, '2025-12-19 03:50:02'),
(10, 'kop_surat_logo', 'uploads/laporan/img_6941a228ee3b2.png', 'image', 'kop_surat', 20, '2025-12-16 18:17:12'),
(11, 'ttd_nama', 'Budi Santoso', 'text', 'kop_surat', 21, '2025-12-19 03:50:02'),
(12, 'ttd_nip', '198001012015041001', 'text', 'kop_surat', 22, '2025-12-19 03:50:02'),
(13, 'ttd_jabatan', 'Direktur Utama', 'text', 'kop_surat', 23, '2025-12-19 03:50:02'),
(14, 'ttd_image', 'uploads/logos/img_6944c72435d1e.png', 'image', 'kop_surat', 24, '2025-12-19 03:31:48'),
(15, 'maintenance_mode', '0', 'number', 'sistem', 1, '2025-12-19 03:50:02'),
(22, 'footer_copyright', '© 2025 GedungKita. All rights reserved.', 'text', 'branding', 10, '2025-12-19 03:50:02'),
(23, 'company_address', 'Jl. Jenderal Sudirman No. Kav 50, Jakarta Selatan, DKI Jakarta 12930', 'textarea', 'institusi', 20, '2025-12-19 03:50:02'),
(24, 'company_email', 'support@gedungkita.com', 'text', 'institusi', 21, '2025-12-19 03:50:02'),
(25, 'company_phone', '+62 812-3456-7890', 'text', 'institusi', 22, '2025-12-19 03:50:02'),
(26, 'social_instagram', 'https://instagram.com/gedungkita', 'text', 'branding', 30, '2025-12-19 03:50:02'),
(27, 'social_facebook', 'https://facebook.com/gedungkita', 'text', 'branding', 31, '2025-12-19 03:50:02'),
(28, 'app_theme', 'indigo', 'text', 'branding', 5, '2025-12-19 03:50:02'),
(29, 'xendit_api_key', '', 'text', 'payment', 1, '2025-12-19 03:50:02'),
(30, 'xendit_callback_token', '', 'text', 'payment', 2, '2025-12-19 03:50:02'),
(33, 'public_hero_image', 'uploads/hero/img_6944cb3aee39f.jpg', 'image', 'branding', 7, '2025-12-19 03:49:15'),
(34, 'site_status', 'live', 'select', 'sistem', 10, '2025-12-19 01:42:30'),
(35, 'maintenance_message', 'Website sedang dalam perbaikan. Silakan kembali nanti.', 'textarea', 'sistem', 11, '2025-12-19 03:50:02'),
(36, 'public_theme', 'sunset', 'select', 'branding', 5, '2025-12-19 03:50:02');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('superadmin','admin','penyewa') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'penyewa',
  `nama_lengkap` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_telepon` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `nama_lengkap`, `no_telepon`, `is_active`, `created_at`, `deleted_at`) VALUES
(1, 'superadmin', 'admin@gmail.com', '$2y$12$36Qt4uduBCeZbyQFD9Ow2e28O5v.vLQHLdHDPHs8IKSXVbMJCg.g2', 'superadmin', 'Super Admin', '0511-123456', 1, '2025-12-16 09:02:43', NULL),
(2, 'admin', 'admin@rental.co.id', 'admin123', 'admin', 'Admin Utama', NULL, 1, '2025-12-16 09:02:43', '2025-12-17 23:16:44');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_module` (`module`),
  ADD KEY `idx_action` (`action`);

--
-- Indexes for table `booking`
--
ALTER TABLE `booking`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `booking_code` (`booking_code`),
  ADD KEY `idx_penyewa` (`penyewa_id`),
  ADD KEY `idx_gedung` (`gedung_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_tanggal` (`tanggal_mulai`),
  ADD KEY `booking_promo_fk` (`promo_id`);

--
-- Indexes for table `fasilitas`
--
ALTER TABLE `fasilitas`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `gedung`
--
ALTER TABLE `gedung`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_harga` (`harga_per_hari`),
  ADD KEY `fk_gedung_kategori` (`kategori_id`);

--
-- Indexes for table `gedung_fasilitas`
--
ALTER TABLE `gedung_fasilitas`
  ADD PRIMARY KEY (`gedung_id`,`fasilitas_id`),
  ADD KEY `fasilitas_id` (`fasilitas_id`);

--
-- Indexes for table `gedung_foto`
--
ALTER TABLE `gedung_foto`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_gedung` (`gedung_id`),
  ADD KEY `idx_primary` (`is_primary`);

--
-- Indexes for table `jadwal`
--
ALTER TABLE `jadwal`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `idx_gedung` (`gedung_id`),
  ADD KEY `idx_tanggal` (`tanggal_mulai`);

--
-- Indexes for table `kategori_gedung`
--
ALTER TABLE `kategori_gedung`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `laporan`
--
ALTER TABLE `laporan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_jenis` (`jenis`),
  ADD KEY `idx_periode` (`periode`);

--
-- Indexes for table `pelanggan`
--
ALTER TABLE `pelanggan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user` (`user_id`),
  ADD KEY `idx_ktp` (`no_ktp`);

--
-- Indexes for table `promos`
--
ALTER TABLE `promos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_promo` (`kode_promo`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `booking_id` (`booking_id`),
  ADD KEY `gedung_id` (`gedung_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key` (`key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_active` (`is_active`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=330;

--
-- AUTO_INCREMENT for table `booking`
--
ALTER TABLE `booking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fasilitas`
--
ALTER TABLE `fasilitas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `gedung`
--
ALTER TABLE `gedung`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `gedung_foto`
--
ALTER TABLE `gedung_foto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jadwal`
--
ALTER TABLE `jadwal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kategori_gedung`
--
ALTER TABLE `kategori_gedung`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `laporan`
--
ALTER TABLE `laporan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pelanggan`
--
ALTER TABLE `pelanggan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `promos`
--
ALTER TABLE `promos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `log_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `booking`
--
ALTER TABLE `booking`
  ADD CONSTRAINT `booking_ibfk_1` FOREIGN KEY (`penyewa_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `booking_ibfk_2` FOREIGN KEY (`gedung_id`) REFERENCES `gedung` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `booking_promo_fk` FOREIGN KEY (`promo_id`) REFERENCES `promos` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `gedung`
--
ALTER TABLE `gedung`
  ADD CONSTRAINT `fk_gedung_kategori` FOREIGN KEY (`kategori_id`) REFERENCES `kategori_gedung` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `gedung_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `gedung_fasilitas`
--
ALTER TABLE `gedung_fasilitas`
  ADD CONSTRAINT `gedung_fasilitas_ibfk_1` FOREIGN KEY (`gedung_id`) REFERENCES `gedung` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `gedung_fasilitas_ibfk_2` FOREIGN KEY (`fasilitas_id`) REFERENCES `fasilitas` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `gedung_foto`
--
ALTER TABLE `gedung_foto`
  ADD CONSTRAINT `gedung_foto_ibfk_1` FOREIGN KEY (`gedung_id`) REFERENCES `gedung` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `jadwal`
--
ALTER TABLE `jadwal`
  ADD CONSTRAINT `jadwal_ibfk_1` FOREIGN KEY (`gedung_id`) REFERENCES `gedung` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `jadwal_ibfk_2` FOREIGN KEY (`booking_id`) REFERENCES `booking` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `laporan`
--
ALTER TABLE `laporan`
  ADD CONSTRAINT `laporan_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `pelanggan`
--
ALTER TABLE `pelanggan`
  ADD CONSTRAINT `pelanggan_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `review_booking_fk` FOREIGN KEY (`booking_id`) REFERENCES `booking` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `review_gedung_fk` FOREIGN KEY (`gedung_id`) REFERENCES `gedung` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `review_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
