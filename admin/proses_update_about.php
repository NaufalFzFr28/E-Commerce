<?php
include 'cek_login.php';
include '../config.php';

/**
 * LOGIKA 1: RESET FOTO KE DEFAULT (Via link/GET)
 */
if (isset($_GET['reset'])) {
    $type = $_GET['reset'];
    $target_dir = "../assets/imgs/";
    
    // Ambil data lama untuk proses penghapusan file fisik
    $q = mysqli_query($conn, "SELECT img_front, img_back FROM site_about WHERE id = 1");
    $data = mysqli_fetch_assoc($q);

    if ($type == 'front') {
        $old_file = $data['img_front'];
        $default = 'avatar-naufaru-1.jpg';
        $update_col = 'img_front';
    } elseif ($type == 'back') {
        $old_file = $data['img_back'];
        $default = 'avatar-naufaru-2.jpg';
        $update_col = 'img_back';
    }

    // Hapus file fisik lama jika bukan file default bawaan
    if (isset($old_file) && $old_file != $default && file_exists($target_dir . $old_file)) {
        unlink($target_dir . $old_file);
    }

    // Update database ke nama file default
    mysqli_query($conn, "UPDATE site_about SET $update_col = '$default' WHERE id = 1");
    
    header("Location: main_website.php?status=success_about");
    exit();
}

/**
 * LOGIKA 2: UPDATE DATA (Via Form/POST)
 */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. AMBIL DATA TEKS DARI POST
    $title_id = mysqli_real_escape_string($conn, $_POST['about_title_id'] ?? '');
    $title_en = mysqli_real_escape_string($conn, $_POST['about_title_en'] ?? '');
    $title_jp = mysqli_real_escape_string($conn, $_POST['about_title_jp'] ?? '');

    $sub_id   = mysqli_real_escape_string($conn, $_POST['about_subtitle_id'] ?? '');
    $sub_en   = mysqli_real_escape_string($conn, $_POST['about_subtitle_en'] ?? '');
    $sub_jp   = mysqli_real_escape_string($conn, $_POST['about_subtitle_jp'] ?? '');

    $paragraphs = [];
    $langs = ['id', 'en', 'jp'];
    foreach ($langs as $l) {
        for ($i = 1; $i <= 5; $i++) {
            $key = "p{$i}_{$l}";
            $paragraphs[$key] = mysqli_real_escape_string($conn, $_POST[$key] ?? '');
        }
    }

    // 2. LOGIKA UPLOAD GAMBAR
    $target_dir = "../assets/imgs/";
    $img_fields = ['about_img_front' => 'img_front', 'about_img_back' => 'img_back'];
    $img_updates = "";

    foreach ($img_fields as $post_name => $db_column) {
        if (isset($_FILES[$post_name]) && $_FILES[$post_name]['error'] == 0) {
            $ext = pathinfo($_FILES[$post_name]['name'], PATHINFO_EXTENSION);
            $new_filename = "about_" . $db_column . "_" . time() . "." . $ext;

            if (move_uploaded_file($_FILES[$post_name]['tmp_name'], $target_dir . $new_filename)) {
                // Hapus foto lama jika ada
                $old_query = mysqli_query($conn, "SELECT $db_column FROM site_about WHERE id = 1");
                $old_data = mysqli_fetch_assoc($old_query);
                if ($old_data && !empty($old_data[$db_column])) {
                    $old_path = $target_dir . $old_data[$db_column];
                    // Jangan hapus jika itu file default
                    // Ganti bagian if (!str_contains(...)) menjadi:
                    if (file_exists($old_path) && strpos($old_data[$db_column], 'avatar-naufaru') === false) {
                        unlink($old_path);
                    }
                }
                $img_updates .= ", $db_column = '$new_filename'";
            }
        }
    }

    // 3. UPDATE DATABASE (Hanya Satu Kali Jalankan Query)
    $sql_update = "UPDATE site_about SET 
                    about_title_id = '$title_id', about_title_en = '$title_en', about_title_jp = '$title_jp',
                    about_subtitle_id = '$sub_id', about_subtitle_en = '$sub_en', about_subtitle_jp = '$sub_jp',
                    p1_id = '{$paragraphs['p1_id']}', p2_id = '{$paragraphs['p2_id']}', p3_id = '{$paragraphs['p3_id']}', p4_id = '{$paragraphs['p4_id']}', p5_id = '{$paragraphs['p5_id']}',
                    p1_en = '{$paragraphs['p1_en']}', p2_en = '{$paragraphs['p2_en']}', p3_en = '{$paragraphs['p3_en']}', p4_en = '{$paragraphs['p4_en']}', p5_en = '{$paragraphs['p5_en']}',
                    p1_jp = '{$paragraphs['p1_jp']}', p2_jp = '{$paragraphs['p2_jp']}', p3_jp = '{$paragraphs['p3_jp']}', p4_jp = '{$paragraphs['p4_jp']}', p5_jp = '{$paragraphs['p5_jp']}'
                    $img_updates
                   WHERE id = 1";

    if (!mysqli_query($conn, $sql_update)) {
        die("Error Database: " . mysqli_error($conn));
    }

    // 4. SINKRONISASI JSON
    $json_dir = "../languages/";
    foreach ($langs as $lang) {
        $json_file = $json_dir . $lang . ".json";
        $json_data = file_exists($json_file) ? json_decode(file_get_contents($json_file), true) : [];

        // Mapping Data untuk JSON
        if($lang == 'id') {
            $json_data['about_title'] = $title_id;
            $json_data['about_subtitle'] = $sub_id;
        } elseif($lang == 'en') {
            $json_data['about_title'] = $title_en;
            $json_data['about_subtitle'] = $sub_en;
        } else {
            $json_data['about_title'] = $title_jp;
            $json_data['about_subtitle'] = $sub_jp;
        }
        
        for ($i = 1; $i <= 5; $i++) {
            $json_data["about_p$i"] = $paragraphs["p{$i}_{$lang}"];
        }

        file_put_contents($json_file, json_encode($json_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    header("Location: main_website.php?status=success_about");
    exit();
}