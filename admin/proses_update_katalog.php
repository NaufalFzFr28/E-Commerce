<?php
session_start();
include 'cek_login.php'; 
include '../config.php';

if (isset($_POST['submit_update'])) {
    // 1. Ambil data dari form dan sanitasi (Termasuk EN dan JP)
    $id             = mysqli_real_escape_string($conn, $_POST['id']);
    $price          = mysqli_real_escape_string($conn, $_POST['price']);
    
    // Data Bahasa Indonesia (ID)
    $name           = mysqli_real_escape_string($conn, $_POST['product_name']);
    $category       = mysqli_real_escape_string($conn, $_POST['category']);
    $deskripsi      = mysqli_real_escape_string($conn, $_POST['description']);

    // Data Bahasa Inggris (EN)
    $name_en        = mysqli_real_escape_string($conn, $_POST['product_en']);
    $category_en    = mysqli_real_escape_string($conn, $_POST['kategori_en']);
    $deskripsi_en   = mysqli_real_escape_string($conn, $_POST['description_en']);

    // Data Bahasa Jepang (JP)
    $name_jp        = mysqli_real_escape_string($conn, $_POST['product_jp']);
    $category_jp    = mysqli_real_escape_string($conn, $_POST['kategori_jp']);
    $deskripsi_jp   = mysqli_real_escape_string($conn, $_POST['description_jp']);

    // 2. Ambil data lama untuk pengecekan gambar
    $old_data_query = $conn->query("SELECT gambar_produk FROM site_products_promo WHERE id = '$id'");
    $old_data       = $old_data_query->fetch_assoc();
    $new_filename   = $old_data['gambar_produk']; 

    // 3. Logika Upload Gambar Baru (Jika ada)
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../../assets/imgs/img-catalog/";
        
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_name = $_FILES['image']['name'];
        $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        $new_filename = "catalog_upd_" . time() . "_" . uniqid() . "." . $file_ext;
        $target_file  = $target_dir . $new_filename;

        $allowed = array("jpg", "jpeg", "png", "webp");
        if (in_array($file_ext, $allowed)) {
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $old_file_path = $target_dir . $old_data['gambar_produk'];
                if ($old_data['gambar_produk'] != 'placeholder.png' && file_exists($old_file_path)) {
                    unlink($old_file_path);
                }
            } else {
                header("Location: admin_katalog.php?status=error_upload");
                exit();
            }
        } else {
            header("Location: admin_katalog.php?status=invalid_format");
            exit();
        }
    }

    // 4. Update Database (Menyertakan semua kolom multibahasa)
    $query_update = "UPDATE site_products_promo SET 
                        product_name = '$name', 
                        product_en   = '$name_en', 
                        product_jp   = '$name_jp', 
                        price        = '$price', 
                        kategori     = '$category', 
                        kategori_en  = '$category_en', 
                        kategori_jp  = '$category_jp', 
                        deskripsi    = '$deskripsi', 
                        deskripsi_en = '$deskripsi_en', 
                        deskripsi_jp = '$deskripsi_jp', 
                        gambar_produk = '$new_filename' 
                     WHERE id = '$id'";

    if ($conn->query($query_update)) {
        header("Location: admin_katalog.php?status=success");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
} else {
    header("Location: admin_katalog.php");
    exit();
} 