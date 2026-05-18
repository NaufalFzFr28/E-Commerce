<?php
session_start();

// Menghapus semua variabel sesi khusus member
$_SESSION = array();

// Menghancurkan sesi secara total
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

// Alihkan kembali ke halaman login member area
header("Location: login_member.php");
exit();
?>