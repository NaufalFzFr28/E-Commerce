<?php
/**
 * File: admin/proses_update_testi_info.php
 * Deskripsi: Handler CRUD untuk Alert Info Testimonial
 */
session_start();
include '../config.php';

$action = $_POST['action'] ?? ($_GET['action'] ?? '');

// 1. TAMBAH INFO BARU
if ($action === 'add_testi_info') {
    $text_id      = mysqli_real_escape_string($conn, $_POST['text_id']);
    $text_en      = mysqli_real_escape_string($conn, $_POST['text_en']);
    $text_jp      = mysqli_real_escape_string($conn, $_POST['text_jp']);
    $link_text_id = mysqli_real_escape_string($conn, $_POST['link_text_id']);
    $link_url     = mysqli_real_escape_string($conn, $_POST['link_url']);

    // Set default menjadi aktif (1) saat ditambahkan
    $sql = "INSERT INTO site_testi_alerts (text_id, text_en, text_jp, link_text_id, link_url, is_active) 
            VALUES ('$text_id', '$text_en', '$text_jp', '$link_text_id', '$link_url', 1)";
    
    if (mysqli_query($conn, $sql)) {
        header("Location: main_website.php?status=success_testi_info");
    } else {
        header("Location: main_website.php?status=error_db");
    }
    exit();
}

// 2. SIMPAN PERUBAHAN STATUS (Hanya Update Toggle Active)
elseif ($action === 'update_testi_status') {
    // Reset semua status menjadi non-aktif (0) terlebih dahulu
    mysqli_query($conn, "UPDATE site_testi_alerts SET is_active = 0");
    
    // Jika ada checkbox yang dicentang, update menjadi aktif (1)
    if (!empty($_POST['is_active'])) {
        foreach ($_POST['is_active'] as $id => $value) {
            $id = (int)$id;
            mysqli_query($conn, "UPDATE site_testi_alerts SET is_active = 1 WHERE id = $id");
        }
    }
    
    header("Location: main_website.php?status=success_update_testi_info");
    exit();
}

// 3. HAPUS INFO
elseif (isset($_GET['delete_testi_info_id'])) {
    $id = (int)$_GET['delete_testi_info_id'];
    mysqli_query($conn, "DELETE FROM site_testi_alerts WHERE id = $id");
    header("Location: main_website.php?status=success_delete_testi_info");
    exit();
}
?>