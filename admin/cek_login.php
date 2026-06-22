<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Tambahkan error_log atau print untuk cek status jika perlu
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Jika kedap-kedip berhenti saat baris di bawah dikomentar, 
    // berarti masalahnya ada di session yang tidak terbaca.
    header("Location: ../login.php?pesan=belum_login");
    exit;
}
?>