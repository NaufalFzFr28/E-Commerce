<?php
session_start();

// Menghapus semua data sesi untuk keamanan NaufaRu Admin
$_SESSION = array();

// Menghancurkan sesi
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

// Mengarahkan kembali ke index.php atau splash screen di luar folder admin
header("Location: ../index.php");
exit();
?>