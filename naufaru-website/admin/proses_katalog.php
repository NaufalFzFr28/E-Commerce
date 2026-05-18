<?php
session_start();
include 'cek_login.php'; 
include '../config.php';

if (isset($_POST['submit_add'])) {
    // 1. Ambil data teks dan bersihkan (Sanitasi) - ID (Default)
    $name         = mysqli_real_escape_string($conn, $_POST['product_name']);
    $price        = mysqli_real_escape_string($conn, $_POST['price']);
    $deskripsi    = mysqli_real_escape_string($conn, $_POST['description']);
    $kategori_val = (isset($_POST['category']) && trim($_POST['category']) !== "") 
                    ? mysqli_real_escape_string($conn, $_POST['category']) 
                    : 'Lainnya';

    // 2. Ambil data Multibahasa (EN & JP)
    // Menangkap input dari section lang-en dan lang-jp di form modal
    $name_en      = mysqli_real_escape_string($conn, $_POST['product_en']);
    $kategori_en  = mysqli_real_escape_string($conn, $_POST['kategori_en']);
    $deskripsi_en = mysqli_real_escape_string($conn, $_POST['description_en']);

    $name_jp      = mysqli_real_escape_string($conn, $_POST['product_jp']);
    $kategori_jp  = mysqli_real_escape_string($conn, $_POST['kategori_jp']);
    $deskripsi_jp = mysqli_real_escape_string($conn, $_POST['description_jp']);

    // 3. Pengelolaan File Gambar
    $new_filename = "placeholder.png"; 
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../../assets/imgs/img-catalog/";
        if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }

        $filename = $_FILES['image']['name'];
        $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        $new_filename = "cat_" . time() . "_" . uniqid() . "." . $file_ext;
        $target_file = $target_dir . $new_filename;

        move_uploaded_file($_FILES['image']['tmp_name'], $target_file);
    }

    // 4. Query ke TABEL site_products_promo dengan kolom lengkap
    // Menyertakan kolom yang sebelumnya NULL di database Anda
    $query = "INSERT INTO site_products_promo 
              (product_name, product_en, product_jp, 
               deskripsi, deskripsi_en, deskripsi_jp, 
               price, 
               kategori, kategori_en, kategori_jp, 
               gambar_produk, stok, is_active) 
              VALUES 
              ('$name', '$name_en', '$name_jp', 
               '$deskripsi', '$deskripsi_en', '$deskripsi_jp', 
               '$price', 
               '$kategori_val', '$kategori_en', '$kategori_jp', 
               '$new_filename', 0, 1)";
    
    if ($conn->query($query)) {
        header("Location: admin_katalog.php?status=success");
        exit();
    } else {
        // Membantu debugging jika ada struktur tabel yang tidak cocok
        die("Fatal Error MySQL: " . $conn->error);
    }
} else {
    header("Location: admin_katalog.php");
    exit();
}
?>