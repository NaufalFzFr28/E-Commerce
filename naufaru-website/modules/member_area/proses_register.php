<?php
session_start();
include '../../config.php';

// Matikan error reporting display agar tidak merusak respon JSON/Text AJAX
error_reporting(0);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password = $_POST['password']; 
    $nama_lengkap = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $no_hp = mysqli_real_escape_string($conn, preg_replace('/[^0-9]/', '', $_POST['no_hp']));

    // 1. Validasi WA
    if (strlen($no_hp) > 13) { echo "wa_too_long"; exit(); }

    // 2. Cek Username
    $cek_user = mysqli_query($conn, "SELECT id FROM users_member WHERE username = '$username'");
    if (mysqli_num_rows($cek_user) > 0) {
        echo "exists";
        exit();
    }

    // 3. Kelola Foto Profil
    $foto_name = "default-member.png";
    if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES['foto_profil']['name'], PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];
        
        if (in_array($ext, $allowed_ext)) {
            $foto_name = "member_" . time() . "." . $ext;
            $target_dir = "../../assets/imgs/profiles/";
            
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            if(!move_uploaded_file($_FILES['foto_profil']['tmp_name'], $target_dir . $foto_name)) {
                $foto_name = "default-member.png"; // Jika upload gagal, balik ke default
            }
        }
    }

    // 4. Proses Simpan (INSERT)
    // Gunakan password murni sesuai permintaan Anda (meskipun disarankan hash)
    $sql = "INSERT INTO users_member (username, password, nama_lengkap, no_hp, alamat, foto_profil) 
            VALUES ('$username', '$password', '$nama_lengkap', '$no_hp', '$alamat', '$foto_name')";

    if (mysqli_query($conn, $sql)) {
        echo "success";
    } else {
        // MENGIRIM PESAN ERROR DATABASE ASLI UNTUK DEBUGGING
        echo "error_db: " . mysqli_error($conn);
    }
}
?>