<?php
/**
 * File: admin/proses_splashscreen.php
 * Deskripsi: Skrip Eksekusi Upload Media & Penanganan Fitur Reset Default Wallpaper
 */
session_start();
include 'cek_login.php';
include '../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $target_dir = "../assets/imgs/";
    $success_count = 0;
    $errors = [];

    // Loop melewati 3 slot wallpaper
    for ($slot = 1; $slot <= 3; $slot++) {
        $field_name = "wallpaper_slot_" . $slot;
        $reset_field_name = "reset_slot_" . $slot;
        
        $is_reset_requested = isset($_POST[$reset_field_name]) && $_POST[$reset_field_name] == "1";

        // KONDISI A: ADMIN MEMINTA RESET KEMBALI KE DEFAULT IMGS
        if ($is_reset_requested) {
            $default_img_name = "bg-" . $slot . ".jpg";

            // Ambil nama file lama dari DB untuk pembersihan penyimpanan fisik disk
            $q_old = mysqli_query($conn, "SELECT image_path FROM site_wallpaper WHERE sort_order = $slot LIMIT 1");
            if ($old_data = mysqli_fetch_assoc($q_old)) {
                $old_file_name = $old_data['image_path'];
                // Pastikan file lama yang dihapus bukan merupakan file default sistem itu sendiri
                if ($old_file_name !== "bg-1.jpg" && $old_file_name !== "bg-2.jpg" && $old_file_name !== "bg-3.jpg") {
                    $old_file_path = $target_dir . $old_file_name;
                    if (file_exists($old_file_path) && !empty($old_file_name)) {
                        @unlink($old_file_path);
                    }
                }
            }

            // Kembalikan isian database ke text bg-X.jpg bawaan
            $stmt = $conn->prepare("UPDATE site_wallpaper SET image_path = ?, is_active = 1 WHERE sort_order = ?");
            $stmt->bind_param("si", $default_img_name, $slot);
            $stmt->execute();
            $stmt->close();
            
            $success_count++;
            continue; // Lompati proses kueri upload file karena slot ini sudah berhasil di-reset
        }

        // KONDISI B: PROSES OPERASI STANDARD UPLOAD JURUSAN GAMBAR BARU KUSTOM
        if (isset($_FILES[$field_name]) && $_FILES[$field_name]['error'] == UPLOAD_ERR_OK) {
            $file_tmp = $_FILES[$field_name]['tmp_name'];
            $file_original_name = $_FILES[$field_name]['name'];
            $file_ext = strtolower(pathinfo($file_original_name, PATHINFO_EXTENSION));
            
            $allowed_ext = ['jpg', 'jpeg', 'png'];
            if (!in_array($file_ext, $allowed_ext)) {
                $errors[] = "Slot $slot gagal: Format file harus JPG, JPEG, atau PNG.";
                continue;
            }

            // Generate nama enkripsi acak premium anti redundansi
            $new_file_name = "splash_bg_" . $slot . "_" . substr(md5(time() . rand()), 0, 8) . "." . $file_ext;
            $target_file = $target_dir . $new_file_name;

            if (move_uploaded_file($file_tmp, $target_file)) {
                
                // Disk clean up file custom lama
                $q_old = mysqli_query($conn, "SELECT image_path FROM site_wallpaper WHERE sort_order = $slot LIMIT 1");
                if ($old_data = mysqli_fetch_assoc($q_old)) {
                    $old_file_name = $old_data['image_path'];
                    if ($old_file_name !== "bg-1.jpg" && $old_file_name !== "bg-2.jpg" && $old_file_name !== "bg-3.jpg") {
                        $old_file_path = $target_dir . $old_file_name;
                        if (file_exists($old_file_path) && !empty($old_file_name)) {
                            @unlink($old_file_path);
                        }
                    }
                }

                // Update row di DB
                $stmt = $conn->prepare("UPDATE site_wallpaper SET image_path = ?, is_active = 1 WHERE sort_order = ?");
                $stmt->bind_param("si", $new_file_name, $slot);
                $stmt->execute();
                $stmt->close();

                $success_count++;
            } else {
                $errors[] = "Slot $slot gagal: Tidak dapat memindahkan berkas gambar kustom ke direktori aset.";
            }
        }
    }

    // Redirect status feedback beralih menuju SweetAlert admin_fitur.php
    if (empty($errors)) {
        header("Location: admin_fitur.php?status=success_splash");
    } else {
        $_SESSION['splash_upload_errors'] = implode("<br>", $errors);
        header("Location: admin_fitur.php?status=failed_splash");
    }
    exit();
} else {
    header("Location: admin_fitur.php");
    exit();
}
?>