<?php
// Selalu mulai session di baris paling atas 
session_start();

// Panggil koneksi database dari root 
require_once 'config.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dan proteksi dari SQL Injection 
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    // Query mencari user berdasarkan username 
    $sql = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $sql);

    // Cek apakah permintaan datang dari AJAX (untuk SweetAlert) 
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

    if ($result && mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        
        // Verifikasi password dengan hash yang ada di database 
        if ($password === $row['password']) {
            // LOGIN BERHASIL: Set data sesi 
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $row['username'];
            $_SESSION['admin_id'] = $row['id'];
            
            if ($isAjax) {
                echo "success";
                exit;
            }
            
            // Redirect manual jika bukan AJAX 
            header("Location: admin/admin_dashboard.php");
            exit;
        } else {
            // Password salah 
            if ($isAjax) {
                http_response_code(401);
                echo "password_salah";
                exit;
            }
            header("Location: login.php?pesan=gagal&err=pass");
            exit;
        }
    } else {
        // Username tidak ditemukan 
        if ($isAjax) {
            http_response_code(404);
            echo "user_tidak_ada";
            exit;
        }
        header("Location: login.php?pesan=gagal&err=user");
        exit;
    }
} else {
    // Jika akses file langsung tanpa POST 
    header("Location: login.php");
    exit;
}
?>