<?php
include 'cek_login.php';
include '../config.php';

if (isset($_GET['id'])) {
    // Sanitasi ID
    $id = intval($_GET['id']);
    $target_dir = "../assets/imgs/";

    // 1. Cari nama file di database sebelum dihapus
    $query = mysqli_query($conn, "SELECT image_path FROM site_hero_slides WHERE id = $id");
    $row = mysqli_fetch_assoc($query);

    if ($row) {
        $file_name = $row['image_path'];
        $file_path = $target_dir . $file_name;

        // 2. Hapus file fisik dari server (jika bukan file default)
        if (file_exists($file_path) && strpos($file_name, 'man-') === false) {
            unlink($file_path);
        }

        // 3. Hapus data dari database
        // Dengan menghapus baris ini, logic di main_website.php akan otomatis 
        // menampilkan man-i.png karena isset($slides_data[$i]) menjadi false.
        $delete = mysqli_query($conn, "DELETE FROM site_hero_slides WHERE id = $id");

        if ($delete) {
            header("Location: main_website.php?status=success");
        } else {
            header("Location: main_website.php?status=error");
        }
    } else {
        // Jika data tidak ditemukan di DB, tetap balik ke main website
        header("Location: main_website.php");
    }
} else {
    header("Location: main_website.php");
}
exit();