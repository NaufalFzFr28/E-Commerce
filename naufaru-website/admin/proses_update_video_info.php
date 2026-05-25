<?php
/**
 * File: admin/proses_update_video_info.php
 * Deskripsi: Handler Backend CRUD (Create, Update, Delete) untuk Alert Info Video
 * Pembaruan: Menggunakan Prepared Statements untuk Mengamankan Query dari SQL Injection
 */

session_start();

// Validasi session admin jika diperlukan (sesuaikan dengan sistem login dashboard Anda)
// if (!isset($_SESSION['admin'])) { header("Location: login.php"); exit(); }

if (!file_exists('../config.php')) {
    die("File config.php tidak ditemukan. Pastikan jalur database benar.");
}
include '../config.php';

// Menangkap metode request tindakan
$action = $_POST['action'] ?? ($_GET['delete_video_info_id'] ?? '');

/**
 * 1. LOGIKA TAMBAH INFO VIDEO BARU (ADD)
 */
if ($action === 'add_video_info') {
    $text_id      = trim($_POST['text_id']);
    $text_en      = trim($_POST['text_en']);
    $text_jp      = trim($_POST['text_jp']);
    $link_text_id = trim($_POST['link_text_id']);
    $link_text_en = trim($_POST['link_text_en']);
    $link_text_jp = trim($_POST['link_text_jp']);
    $link_url     = trim($_POST['link_url']);

    if (empty($text_id)) {
        header("Location: main_website.php?status=error_empty");
        exit();
    }

    // Menggunakan Prepared Statements agar kebal dari serangan SQL Injection
    $sql = "INSERT INTO site_video_alerts (text_id, text_en, text_jp, link_text_id, link_text_en, link_text_jp, link_url, is_active) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 1)";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sssssss", $text_id, $text_en, $text_jp, $link_text_id, $link_text_en, $link_text_jp, $link_url);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: main_website.php?status=success_video_info");
    } else {
        header("Location: main_website.php?status=error_db");
    }
    mysqli_stmt_close($stmt);
    exit();
}

/**
 * 2. LOGIKA SIMPAN PERUBAHAN & STATUS AKTIF (UPDATE)
 */
elseif ($action === 'update_video_info') {
    $info_ids     = $_POST['info_ids'] ?? [];
    $text_ids     = $_POST['text_id'] ?? [];
    $text_ens     = $_POST['text_en'] ?? [];
    $text_jps     = $_POST['text_jp'] ?? [];
    $link_text_ids = $_POST['link_text_id'] ?? [];
    $link_text_ens = $_POST['link_text_en'] ?? [];
    $link_text_jps = $_POST['link_text_jp'] ?? [];
    $link_urls    = $_POST['link_url'] ?? [];
    $is_active_posts = $_POST['is_active'] ?? []; // Hanya menangkap indeks yang dicentang

    // Loop semua ID info video yang dikirim dari form
    foreach ($info_ids as $index => $id) {
        $id           = (int)$id;
        $text_id      = trim($text_ids[$index]);
        $text_en      = trim($text_ens[$index]);
        $text_jp      = trim($text_jps[$index]);
        $link_text_id = trim($link_text_ids[$index]);
        $link_text_en = trim($link_text_ens[$index]);
        $link_text_jp = trim($link_text_jps[$index]);
        $link_url     = trim($link_urls[$index]);
        
        // Logika Checkbox: Jika array is_active dikirim dalam format [] bawaan form,
        // dia hanya mengirim item yang dicentang. Kita lakukan rekalkulasi status aktif.
        $is_active = isset($is_active_posts[$index]) ? 1 : 0;

        $sql = "UPDATE site_video_alerts SET 
                text_id = ?, text_en = ?, text_jp = ?, 
                link_text_id = ?, link_text_en = ?, link_text_jp = ?, 
                link_url = ?, is_active = ? 
                WHERE id = ?";
                
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssssssiii", $text_id, $text_en, $text_jp, $link_text_id, $link_text_en, $link_text_jp, $link_url, $is_active, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    header("Location: main_website.php?status=success_update_video_info");
    exit();
}

/**
 * 3. LOGIKA HAPUS DATA INFO VIDEO (DELETE)
 */
elseif (isset($_GET['delete_video_info_id'])) {
    $delete_id = (int)$_GET['delete_video_info_id'];

    $sql = "DELETE FROM site_video_alerts WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $delete_id);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: main_website.php?status=success_delete_video_info");
    } else {
        header("Location: main_website.php?status=error_db");
    }
    mysqli_stmt_close($stmt);
    exit();
}

// Terowongan pengaman redirect jika diakses ilegal tanpa muatan aksi
else {
    header("Location: main_website.php");
    exit();
}
?>