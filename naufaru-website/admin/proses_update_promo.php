<?php
// proses_update_promo.php
include 'cek_login.php'; 
include '../config.php';

// Cek jika ada permintaan Reset Gambar
if (isset($_GET['reset'])) {
    $reset_type = $_GET['reset'];
    $column = ($reset_type === 'primary') ? 'img_primary' : 'img_secondary';
    
    // Ambil nama file lama untuk dihapus
    $q = mysqli_query($conn, "SELECT $column FROM site_promotion WHERE id = 1");
    $data = mysqli_fetch_assoc($q);
    
    if ($data[$column]) {
        @unlink("../assets/imgs/" . $data[$column]); // Hapus file fisiknya
    }
    
    // Set kolom di database menjadi NULL agar kembali ke default sistem
    mysqli_query($conn, "UPDATE site_promotion SET $column = NULL WHERE id = 1");
    header("Location: main_website.php?status=success_promo");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Ambil data teks dari form
    $btn_url     = mysqli_real_escape_string($conn, $_POST['btn_url']);
    
    // Teks Multi-bahasa
    $title_id    = mysqli_real_escape_string($conn, $_POST['title_id']);
    $title_en    = mysqli_real_escape_string($conn, $_POST['title_en']);
    $title_jp    = mysqli_real_escape_string($conn, $_POST['title_jp']);
    
    $caption_id  = mysqli_real_escape_string($conn, $_POST['caption_id']);
    $caption_en  = mysqli_real_escape_string($conn, $_POST['caption_en']);
    $caption_jp  = mysqli_real_escape_string($conn, $_POST['caption_jp']);
    
    $btn_text_id = mysqli_real_escape_string($conn, $_POST['btn_text_id']);
    $btn_text_en = mysqli_real_escape_string($conn, $_POST['btn_text_en']);
    $btn_text_jp = mysqli_real_escape_string($conn, $_POST['btn_text_jp']);

    // 2. Logika Pemrosesan Gambar (Primary & Secondary)
    $q_old = mysqli_query($conn, "SELECT img_primary, img_secondary FROM site_promotion WHERE id = 1");
    $old_data = mysqli_fetch_assoc($q_old);

    function uploadPromoImage($file_key, $old_name) {
        if (!empty($_FILES[$file_key]['name'])) {
            $ext = pathinfo($_FILES[$file_key]['name'], PATHINFO_EXTENSION);
            $new_name = "promo-" . ($file_key == 'img_primary' ? '1' : '2') . "-" . time() . "." . $ext;
            $target = "../assets/imgs/" . $new_name;
            
            if (move_uploaded_file($_FILES[$file_key]['tmp_name'], $target)) {
                // Hapus file lama jika bukan default
                if ($old_name && strpos($old_name, 'promo-') === false) {
                    @unlink("../assets/imgs/" . $old_name);
                }
                return $new_name;
            }
        }
        return $old_name;
    }

    $img_primary   = uploadPromoImage('img_primary', $old_data['img_primary']);
    $img_secondary = uploadPromoImage('img_secondary', $old_data['img_secondary']);

    // 3. Update Database MySQL
    $sql_update = "UPDATE site_promotion SET 
        img_primary = '$img_primary', 
        img_secondary = '$img_secondary',
        btn_url = '$btn_url',
        title_id = '$title_id', title_en = '$title_en', title_jp = '$title_jp',
        caption_id = '$caption_id', caption_en = '$caption_en', caption_jp = '$caption_jp',
        btn_text_id = '$btn_text_id', btn_text_en = '$btn_text_en', btn_text_jp = '$btn_text_jp'
        WHERE id = 1";

    if (mysqli_query($conn, $sql_update)) {
        
        // 4. SINKRONISASI KE JSON (PENTING)
        $languages = ['id', 'en', 'jp'];
        foreach ($languages as $l) {
            $json_path = "../../languages/{$l}.json";
            if (file_exists($json_path)) {
                $current_content = json_decode(file_get_contents($json_path), true);
                
                // Update bagian promo saja tanpa merusak key lainnya
                $current_content['promo_title']   = $_POST["title_$l"];
                $current_content['promo_caption'] = $_POST["caption_$l"];
                $current_content['promo_btn']     = $_POST["btn_text_$l"];
                
                file_put_contents($json_path, json_encode($current_content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }
        }

        header("Location: main_website.php?status=success_promo");
    } else {
        header("Location: main_website.php?status=error");
    }
}
?>