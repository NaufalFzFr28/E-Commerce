<?php
/**
 * File: admin/config/db.php
 * Deskripsi: Koneksi database utama dengan proteksi SQL Injection
 */

// Konfigurasi database untuk server lokal (XAMPP)
$host     = "localhost";
$username = "root";
$password = "";
$database = "naufaru_db";

// Mengaktifkan pelaporan error mysqli untuk debugging
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Membuat koneksi ke database
    $conn = new mysqli($host, $username, $password, $database);

    // Set charset ke utf8mb4 agar mendukung karakter khusus (seperti simbol atau teks Jepang)
    $conn->set_charset("utf8mb4");

} catch (Exception $e) {
    // Jika koneksi gagal, tampilkan pesan error yang aman (tidak membocorkan struktur path server)
    die("Koneksi database gagal: Silakan cek konfigurasi database Anda.");
}

/**
 * FUNGSI GLOBAL KEAMANAN
 * Digunakan untuk membersihkan input user sebelum diproses
 */
function sanitize_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $conn->real_escape_string($data);
}

/**
 * FUNGSI PREPARED STATEMENT (Proteksi SQL Injection)
 * Contoh penggunaan untuk query aman
 */
function execute_query($sql, $params = [], $types = "") {
    global $conn;
    $stmt = $conn->prepare($sql);
    
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    return $stmt->get_result();
}
?>