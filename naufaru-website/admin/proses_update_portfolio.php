<?php
include '../config.php'; // Mengambil koneksi database 

// Ambil aksi dari POST atau GET [cite: 108, 109]
$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['delete_id']) ? 'delete' : '');

/**
 * 1. LOGIKA TAMBAH DATA (ADD)
 */
if ($action === 'add') {
    $product_id     = mysqli_real_escape_string($conn, $_POST['product_id']); 
    $price_original = mysqli_real_escape_string($conn, $_POST['price_original']); 
    $link_url       = mysqli_real_escape_string($conn, $_POST['link_url']); 
    
    // Judul Multibahasa
    $title_id       = mysqli_real_escape_string($conn, $_POST['title_id']);
    $title_en       = mysqli_real_escape_string($conn, $_POST['title_en']);
    $title_jp       = mysqli_real_escape_string($conn, $_POST['title_jp']);
    
    // Deskripsi Multibahasa 
    $desc_id        = mysqli_real_escape_string($conn, $_POST['desc_id']);
    $desc_en        = mysqli_real_escape_string($conn, $_POST['desc_en']);
    $desc_jp        = mysqli_real_escape_string($conn, $_POST['desc_jp']);

    // Validasi Wajib Isi
    if (empty($title_id) || empty($product_id) || empty($price_original) || empty($_FILES['portfolio_image']['name'])) {
        header("Location: main_website.php?status=error_empty"); 
        exit(); // [cite: 114]
    }

    // Penanganan Upload Gambar 
    $image_name = $_FILES['portfolio_image']['name'];
    $image_tmp  = $_FILES['portfolio_image']['tmp_name'];
    $target_dir = "../assets/imgs/img-portfolio/";
    
    $ext = pathinfo($image_name, PATHINFO_EXTENSION); //
    $new_image_name = time() . "_" . preg_replace("/[^a-zA-Z0-9]/", "_", $title_id) . "." . $ext; // Penamaan unik 
    $target_file = $target_dir . $new_image_name; 

    if (move_uploaded_file($image_tmp, $target_file)) { 
        $sql = "INSERT INTO site_portfolio (product_id, image_path, link_url, title_id, title_en, title_jp, desc_id, desc_en, desc_jp, price_original) 
                VALUES ('$product_id', '$new_image_name', '$link_url', '$title_id', '$title_en', '$title_jp', '$desc_id', '$desc_en', '$desc_jp', '$price_original')"; 
        
        if (mysqli_query($conn, $sql)) { 
            header("Location: main_website.php?status=success_portfolio"); 
        } else {
            header("Location: main_website.php?status=error"); 
        }
    } else {
        header("Location: main_website.php?status=error_upload"); 
    }
}

/**
 * 2. LOGIKA UPDATE DATA (EDIT)
 */
elseif ($action === 'update') {
    $id             = mysqli_real_escape_string($conn, $_POST['portfolio_id']);  
    $product_id     = mysqli_real_escape_string($conn, $_POST['product_id']);  
    $price_original = mysqli_real_escape_string($conn, $_POST['price_original']); 
    $link_url       = mysqli_real_escape_string($conn, $_POST['link_url']);  
    
    $title_id       = mysqli_real_escape_string($conn, $_POST['title_id']); 
    $title_en       = mysqli_real_escape_string($conn, $_POST['title_en']);  
    $title_jp       = mysqli_real_escape_string($conn, $_POST['title_jp']);  
    
    $desc_id        = mysqli_real_escape_string($conn, $_POST['desc_id']); 
    $desc_en        = mysqli_real_escape_string($conn, $_POST['desc_en']);  
    $desc_jp        = mysqli_real_escape_string($conn, $_POST['desc_jp']);  

    // Validasi Wajib Isi (Kecuali Gambar karena opsional saat update) 
    if (empty($title_id) || empty($product_id) || empty($price_original)) {
        header("Location: main_website.php?status=error_empty");  
        exit(); 
    }

    // Jika ada unggahan gambar baru  
    if (!empty($_FILES['portfolio_image']['name'])) {
        // Ambil data lama untuk hapus file fisik lama 
        $res = mysqli_query($conn, "SELECT image_path FROM site_portfolio WHERE id = '$id'");
        $old = mysqli_fetch_assoc($res);
        if ($old && file_exists("../assets/imgs/img-portfolio/" . $old['image_path'])) {
            unlink("../assets/imgs/img-portfolio/" . $old['image_path']); // Hapus file lama  
        }

        // Upload file baru  
        $image_name = $_FILES['portfolio_image']['name'];
        $ext = pathinfo($image_name, PATHINFO_EXTENSION);
        $new_image_name = time() . "_" . preg_replace("/[^a-zA-Z0-9]/", "_", $title_id) . "." . $ext;
        move_uploaded_file($_FILES['portfolio_image']['tmp_name'], "../assets/imgs/img-portfolio/" . $new_image_name); 

        $sql = "UPDATE site_portfolio SET 
                image_path = '$new_image_name',
                product_id = '$product_id', price_original = '$price_original', link_url = '$link_url',
                title_id = '$title_id', title_en = '$title_en', title_jp = '$title_jp',
                desc_id = '$desc_id', desc_en = '$desc_en', desc_jp = '$desc_jp'
                WHERE id = '$id'";  
    } else {
        // Update tanpa ganti gambar  
        $sql = "UPDATE site_portfolio SET 
                product_id = '$product_id', price_original = '$price_original', link_url = '$link_url',
                title_id = '$title_id', title_en = '$title_en', title_jp = '$title_jp',
                desc_id = '$desc_id', desc_en = '$desc_en', desc_jp = '$desc_jp'
                WHERE id = '$id'";  
    }

    if (mysqli_query($conn, $sql)) {  
        header("Location: main_website.php?status=success_portfolio"); 
    } else {
        header("Location: main_website.php?status=error");  
    }
}

/**
 * 3. LOGIKA HAPUS DATA (DELETE)
 */
elseif ($action === 'delete') {
    $delete_id = mysqli_real_escape_string($conn, $_GET['delete_id']);  
    
    // Hapus file fisik dari server  
    $res = mysqli_query($conn, "SELECT image_path FROM site_portfolio WHERE id = '$delete_id'");
    $row = mysqli_fetch_assoc($res);  
    if ($row && file_exists("../assets/imgs/img-portfolio/" . $row['image_path'])) {
        unlink("../assets/imgs/img-portfolio/" . $row['image_path']); // Hapus file fisik  
    }

    // Hapus record database 
    if (mysqli_query($conn, "DELETE FROM site_portfolio WHERE id = '$delete_id'")) {
        header("Location: main_website.php?status=success_delete_portfolio");  
    } else {
        header("Location: main_website.php?status=error");  
    }
}

else {
    header("Location: main_website.php");  
}
?>