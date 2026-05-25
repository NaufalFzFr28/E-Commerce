<?php
/**
 * File: admin/proses_hapus_member.php
 * Deskripsi: Eksekutor pembersih database khusus member lewat jalur AJAX aman (Tanpa Redirect)
 */

include 'cek_login.php';
include '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id_del = intval($_POST['id']);
    
    // 1. Amankan Lokasi Gambar Fisik & Hapus dari Penyimpanan Harddisk Server
    $check_foto = mysqli_query($conn, "SELECT foto_profil FROM users_member WHERE id = $id_del");
    if ($check_foto && mysqli_num_rows($check_foto) > 0) {
        $data_foto = mysqli_fetch_assoc($check_foto);
        $file_lama = $data_foto['foto_profil'];
        
        if (!empty($file_lama) && $file_lama !== 'default-member.png') {
            $full_path = "../assets/imgs/profiles/" . $file_lama;
            if (file_exists($full_path)) { 
                @unlink($full_path); 
            }
        }
    }
    
    // 2. Eksekusi Pembersihan Baris Data Akun dari Database
    $query_delete = "DELETE FROM users_member WHERE id = $id_del";
    if (mysqli_query($conn, $query_delete)) {
        echo "success"; // Kirim sinyal sukses murni kembali ke jQuery AJAX
    } else {
        echo "error";
    }
    exit();
} else {
    echo "invalid_request";
    exit();
}
?>