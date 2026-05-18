<?php
// admin/proses_update_portfolio_info.php
include 'cek_login.php'; 
include '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // --- 1. LOGIKA UPDATE PREFERENSI GRID ---
    if ($action === 'update_grid') {
        $grid_val = mysqli_real_escape_string($conn, $_POST['portfolio_grid_desktop']);
        
        // Update nilai ke tabel site_settings
        $sql_grid = "UPDATE site_settings SET portfolio_grid_desktop = '$grid_val' WHERE id = 1";
        
        if (mysqli_query($conn, $sql_grid)) {
            header("Location: main_website.php?status=success_grid");
        } else {
            header("Location: main_website.php?status=error");
        }
        exit();
    }

    // --- 2. LOGIKA TAMBAH ALERT/INFO BARU ---
    elseif ($action === 'add_info') {
        $text_id      = mysqli_real_escape_string($conn, $_POST['text_id']);
        $text_en      = mysqli_real_escape_string($conn, $_POST['text_en']);
        $text_jp      = mysqli_real_escape_string($conn, $_POST['text_jp']);
        $link_text_id = mysqli_real_escape_string($conn, $_POST['link_text_id']);
        $link_text_en = mysqli_real_escape_string($conn, $_POST['link_text_en']);
        $link_text_jp = mysqli_real_escape_string($conn, $_POST['link_text_jp']);
        $link_url     = mysqli_real_escape_string($conn, $_POST['link_url']);
        
        $sql_info = "INSERT INTO site_portfolio_alerts 
                     (text_id, text_en, text_jp, link_text_id, link_text_en, link_text_jp, link_url, is_active) 
                     VALUES 
                     ('$text_id', '$text_en', '$text_jp', '$link_text_id', '$link_text_en', '$link_text_jp', '$link_url', 1)";
        
        if (mysqli_query($conn, $sql_info)) {
            header("Location: main_website.php?status=success_info");
        } else {
            header("Location: main_website.php?status=error");
        }
        exit();
    }

    // --- 3. LOGIKA UPDATE ALERT (SINKRONISASI & MASS UPDATE) ---
    elseif ($action === 'update_info') {
        if (isset($_POST['info_ids'])) {
            foreach ($_POST['info_ids'] as $index => $id) {
                $id  = mysqli_real_escape_string($conn, $id);
                $tid = mysqli_real_escape_string($conn, $_POST['text_id'][$index]);
                $ten = mysqli_real_escape_string($conn, $_POST['text_en'][$index]);
                $tjp = mysqli_real_escape_string($conn, $_POST['text_jp'][$index]);
                $ltid = mysqli_real_escape_string($conn, $_POST['link_text_id'][$index]);
                $lten = mysqli_real_escape_string($conn, $_POST['link_text_en'][$index]);
                $ltjp = mysqli_real_escape_string($conn, $_POST['link_text_jp'][$index]);
                $url = mysqli_real_escape_string($conn, $_POST['link_url'][$index]);
                $act = isset($_POST['is_active'][$index]) ? 1 : 0;
                
                mysqli_query($conn, "UPDATE site_portfolio_alerts SET 
                    text_id = '$tid', 
                    text_en = '$ten', 
                    text_jp = '$tjp', 
                    link_text_id = '$ltid',
                    link_text_en = '$lten',
                    link_text_jp = '$ltjp',
                    link_url = '$url', 
                    is_active = '$act' 
                    WHERE id = '$id'");
            }
            header("Location: main_website.php?status=success_info");
        } else {
            header("Location: main_website.php");
        }
        exit();
    }
}

// --- 4. LOGIKA HAPUS INFO (VIA GET) ---
if (isset($_GET['delete_info_id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete_info_id']);
    
    // Opsional: Cek keberadaan data sebelum hapus
    $check = mysqli_query($conn, "SELECT id FROM site_portfolio_alerts WHERE id = '$id'");
    if (mysqli_num_rows($check) > 0) {
        if (mysqli_query($conn, "DELETE FROM site_portfolio_alerts WHERE id = '$id'")) {
            header("Location: main_website.php?status=success_info_delete");
        } else {
            header("Location: main_website.php?status=error");
        }
    } else {
        header("Location: main_website.php?status=error_not_found");
    }
    exit();
}

// Jika akses langsung tanpa metode yang benar
header("Location: main_website.php");
exit();
?>