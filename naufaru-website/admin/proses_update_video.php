<?php
/**
 * File: admin/proses_update_video.php
 * Deskripsi: Handler Backend Utama CRUD untuk Objek Galeri Video Portfolio Hub (Tiga Bahasa)
 * Fitur: Kebal SQL Injection (Prepared Statements) & Fungsi Ekstraktor URL Core Helper
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!file_exists('../config.php')) {
    die("Fatal Error: Berkas config.php tidak ditemukan.");
}
include '../config.php';

// Menangkap jenis operasi tindakan request
$action = $_POST['action'] ?? ($_GET['action'] ?? '');

/**
 * ===================================================================================
 * 1. PROSES TAMBAH VIDEO BARU (INSERT DATA)
 * ===================================================================================
 */
if ($action === 'add') {
    $video_url = trim($_POST['video_url']);
    $is_active = (int)$_POST['is_active'];
    $title_id  = trim($_POST['title_id']);
    $title_en  = !empty($_POST['title_en']) ? trim($_POST['title_en']) : null;
    $title_jp  = !empty($_POST['title_jp']) ? trim($_POST['title_jp']) : null;
    $desc_id   = trim($_POST['desc_id']);
    $desc_en   = !empty($_POST['desc_en']) ? trim($_POST['desc_en']) : null;
    $desc_jp   = !empty($_POST['desc_jp']) ? trim($_POST['desc_jp']) : null;

    if (empty($video_url) || empty($title_id) || empty($desc_id)) {
        header("Location: main_website.php?status=error_empty");
        exit();
    }

    $sql = "INSERT INTO site_video_portfolio (video_url, title_id, title_en, title_jp, desc_id, desc_en, desc_jp, is_active) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "sssssssi", $video_url, $title_id, $title_en, $title_jp, $desc_id, $desc_en, $desc_jp, $is_active);
        
        if (mysqli_stmt_execute($stmt)) {
            header("Location: main_website.php?status=success_portfolio"); // Memicu sweetalert sukses bawaan
        } else {
            header("Location: main_website.php?status=error_db");
        }
        mysqli_stmt_close($stmt);
    }
    exit();
}

/**
 * ===================================================================================
 * 2. PROSES UPDATE PERUBAHAN DATA VIDEO (MODAL EDIT HANDLER)
 * ===================================================================================
 */
elseif ($action === 'update') {
    $video_id  = (int)$_POST['video_id'];
    $video_url = trim($_POST['video_url']);
    $is_active = (int)$_POST['is_active'];
    $title_id  = trim($_POST['title_id']);
    $title_en  = !empty($_POST['title_en']) ? trim($_POST['title_en']) : null;
    $title_jp  = !empty($_POST['title_jp']) ? trim($_POST['title_jp']) : null;
    $desc_id   = trim($_POST['desc_id']);
    $desc_en   = !empty($_POST['desc_en']) ? trim($_POST['desc_en']) : null;
    $desc_jp   = !empty($_POST['desc_jp']) ? trim($_POST['desc_jp']) : null;

    $sql = "UPDATE site_video_portfolio SET 
            video_url = ?, is_active = ?, title_id = ?, title_en = ?, title_jp = ?, desc_id = ?, desc_en = ?, desc_jp = ? 
            WHERE id = ?";
            
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "sissssssi", $video_url, $is_active, $title_id, $title_en, $title_jp, $desc_id, $desc_en, $desc_jp, $video_id);
        
        if (mysqli_stmt_execute($stmt)) {
            header("Location: main_website.php?status=success_grid"); // Memicu pesan berhasil simpan perubahan
        } else {
            header("Location: main_website.php?status=error_db");
        }
        mysqli_stmt_close($stmt);
    }
    exit();
}

/**
 * ===================================================================================
 * 3. PROSES HAPUS VIDEO PERMANEN (DELETE DATA)
 * ===================================================================================
 */
elseif ($action === 'delete') {
    $video_id = (int)$_GET['id'];

    $sql = "DELETE FROM site_video_portfolio WHERE id = ?";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $video_id);
        
        if (mysqli_stmt_execute($stmt)) {
            header("Location: main_website.php?status=success_delete_portfolio"); // Memicu pesan berhasil hapus katalog
        } else {
            header("Location: main_website.php?status=error_db");
        }
        mysqli_stmt_close($stmt);
    }
    exit();
}

// Redirect Pengaman jika diakses secara ilegal
else {
    header("Location: main_website.php");
    exit();
}