<?php
session_start();
include '../../config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    // Mencari di tabel users_member
    $query = mysqli_query($conn, "SELECT * FROM users_member WHERE username = '$username'");
    $member = mysqli_fetch_assoc($query);

    if ($member) {
        // Verifikasi password (teks biasa sesuai data di db Anda)
        if ($password === $member['password']) {
            // Set session khusus member
            $_SESSION['member_logged_in'] = true;
            $_SESSION['member_id'] = $member['id'];
            $_SESSION['member_username'] = $member['username'];
            $_SESSION['member_foto'] = $member['foto_profil'];

            echo "success"; // Kirim respon teks ke AJAX
            exit();
        } else {
            echo "wrong_password";
            exit();
        }
    } else {
        echo "not_found";
        exit();
    }
}
?>