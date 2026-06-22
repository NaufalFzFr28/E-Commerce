<?php
/**
 * File: admin/proses_testimonial.php
 * Deskripsi: Controller Backend Operasi CRUD Testimoni (Member & Admin)
 */

session_start();
include '../config.php'; 

$action = $_POST['action'] ?? ($_GET['action'] ?? '');

// --- 1. PROSES TAMBAH TESTIMONI (Member & Admin) ---
if ($action === 'add') {
    $member_id   = isset($_POST['member_id']) ? intval($_POST['member_id']) : null;
    $manual_name = mysqli_real_escape_string($conn, $_POST['manual_name'] ?? '');
    $pekerjaan   = mysqli_real_escape_string($conn, $_POST['pekerjaan']);
    $review_text = mysqli_real_escape_string($conn, $_POST['review_text']);
    $order_id    = isset($_POST['order_id']) ? intval($_POST['order_id']) : null;

    // =========================================================================
    // FIX: CEK DUPLIKASI ORDER ID AGAR TIDAK FATAL ERROR
    // =========================================================================
    if ($order_id) {
        $cek_duplikat = mysqli_query($conn, "SELECT id FROM site_testimonials WHERE order_id = '$order_id'");
        if (mysqli_num_rows($cek_duplikat) > 0) {
            // Jika pesanan ini sudah pernah diulas, lemparkan status error duplikat
            if ($member_id) {
                header("Location: ../modules/member_area/dashboard.php?status=error_duplicate_testi#riwayat");
            } else {
                header("Location: main_website.php?status=error_duplicate_testi");
            }
            exit();
        }
    }
    // =========================================================================

    $photo_filename = null;

    // Jika input manual (Admin), proses unggah foto manual
    if (!$member_id && isset($_FILES['manual_photo']) && $_FILES['manual_photo']['error'] == 0) {
        $target_dir = "../assets/imgs/profiles/";
        $ext = pathinfo($_FILES['manual_photo']['name'], PATHINFO_EXTENSION);
        $photo_filename = "testi_" . time() . "." . $ext;
        move_uploaded_file($_FILES['manual_photo']['tmp_name'], $target_dir . $photo_filename);
    }

    $sql = "INSERT INTO site_testimonials (order_id, member_id, manual_name, manual_photo, pekerjaan, review_text, is_active) 
            VALUES (?, ?, ?, ?, ?, ?, 0)";
    
    try {
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "iissss", $order_id, $member_id, $manual_name, $photo_filename, $pekerjaan, $review_text);

        if (mysqli_stmt_execute($stmt)) {
            // Jika berhasil, redirect dengan status sukses
            if ($member_id) {
                header("Location: ../modules/member_area/dashboard.php?status=success_testi#riwayat");
            } else {
                header("Location: main_website.php?status=success_testi");
            }
        } else {
            throw new Exception("Eksekusi query gagal.");
        }
    } catch (Exception $e) {
        // Tangkap error lainnya agar tidak muncul fatal error putih
        if ($member_id) {
            header("Location: ../modules/member_area/dashboard.php?status=error#riwayat");
        } else {
            header("Location: main_website.php?status=error");
        }
    }
    exit();
}

// --- 2. PROSES UPDATE (Admin Edit) ---
elseif ($action === 'update') {
    $id = intval($_POST['id']);
    $pekerjaan = mysqli_real_escape_string($conn, $_POST['pekerjaan']);
    $review_text = mysqli_real_escape_string($conn, $_POST['review_text']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    $sql = "UPDATE site_testimonials SET pekerjaan = ?, review_text = ?, is_active = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssii", $pekerjaan, $review_text, $is_active, $id);
    mysqli_stmt_execute($stmt);

    header("Location: main_website.php?status=success_testi_update");
    exit();
}

// --- 3. PROSES HAPUS (Admin) ---
elseif ($action === 'delete') {
    $id = intval($_GET['id']);
    
    // Hapus file fisik jika ada (manual photo)
    $q = mysqli_query($conn, "SELECT manual_photo FROM site_testimonials WHERE id = $id");
    $data = mysqli_fetch_assoc($q);
    if (!empty($data['manual_photo']) && file_exists("../assets/imgs/profiles/" . $data['manual_photo'])) {
        @unlink("../assets/imgs/profiles/" . $data['manual_photo']);
    }

    mysqli_query($conn, "DELETE FROM site_testimonials WHERE id = $id");
    header("Location: main_website.php?status=success_testi_delete");
    exit();
}
?>