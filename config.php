<?php
// Pengaturan Database
$host = "localhost";
$user = "root";
$pass = ""; // Kosongkan jika menggunakan XAMPP standar
$db   = "naufaru_db"; 

// Melakukan koneksi
$conn = mysqli_connect($host, $user, $pass, $db);

// Cek Koneksi
if (!$conn) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}

// Set timezone agar created_at di DB sesuai waktu lokal Indonesia
date_default_timezone_set('Asia/Jakarta');
?>