<?php
include 'cek_login.php';
include '../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Ambil data POST (Pastikan name_jp ikut diambil)
    $main_name   = mysqli_real_escape_string($conn, $_POST['main_name']);
    $name_jp     = mysqli_real_escape_string($conn, $_POST['name_jp']); // <-- Tambahkan ini
    
    $greeting_id = mysqli_real_escape_string($conn, $_POST['greeting_id']);
    $greeting_en = mysqli_real_escape_string($conn, $_POST['greeting_en']);
    $greeting_jp = mysqli_real_escape_string($conn, $_POST['greeting_jp']);
    
    $desc_id     = mysqli_real_escape_string($conn, $_POST['desc_id']);
    $desc_en     = mysqli_real_escape_string($conn, $_POST['desc_en']);
    $desc_jp     = mysqli_real_escape_string($conn, $_POST['desc_jp']);

    // 2. Update Database (Pastikan kolom name_jp sudah ada di tabel site_hero)
    $sql_update = "UPDATE site_hero SET 
                    main_name = '$main_name',
                    name_jp = '$name_jp', 
                    greeting_id = '$greeting_id',
                    greeting_en = '$greeting_en',
                    greeting_jp = '$greeting_jp',
                    desc_id = '$desc_id',
                    desc_en = '$desc_en',
                    desc_jp = '$desc_jp'
                   WHERE id = 1";
    
    mysqli_query($conn, $sql_update);

    // 3. Sinkronisasi JSON
    $lang_dir = "../languages/";
    $languages = ['id', 'en', 'jp'];

    foreach ($languages as $lang) {
        $json_file = $lang_dir . $lang . ".json";
        $data = file_exists($json_file) ? json_decode(file_get_contents($json_file), true) : [];

        // --- LOGIKA PEMISAH NAMA ---
        if ($lang == 'jp') {
            // Jika ada input Nama Jepang, buat format: Nama Jepang (Nama Standar)
            // Anda bisa menambahkan tag HTML <small> atau <br> di sini jika CSS mendukung
            if (!empty($name_jp)) {
                // Format: 能法留 Naufal FzFr
                $data['home_name'] = $name_jp . " <small style='font-size: 0.5em; display: block; opacity: 0.8;'>" . $main_name . "</small>";
            } else {
                $data['home_name'] = $main_name;
            }
            
            $data['home_intro'] = $greeting_jp;
            $data['home_desc']  = $desc_jp;
        } else {
            // Untuk mode Indo & Inggris, tetap murni Nama Standar
            $data['home_name'] = $main_name;
            
            if ($lang == 'id') {
                $data['home_intro'] = $greeting_id;
                $data['home_desc']  = $desc_id;
            } else {
                $data['home_intro'] = $greeting_en;
                $data['home_desc']  = $desc_en;
            }
        }

        file_put_contents($json_file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    // 4. Logika Upload Gambar (Slide 1-3)
    $target_dir = "../assets/imgs/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    for ($i = 1; $i <= 3; $i++) {
        $field = "slide" . $i;
        if (isset($_FILES[$field]) && $_FILES[$field]['error'] == 0) {
            
            // Ambil ekstensi asli file
            $ext = pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION);
            $new_name = time() . "_slide" . $i . "." . $ext;
            
            if (move_uploaded_file($_FILES[$field]['tmp_name'], $target_dir . $new_name)) {
                
                // Hapus file lama di server agar storage tidak penuh
                $old_data_query = mysqli_query($conn, "SELECT image_path FROM site_hero_slides WHERE id = $i");
                $old_data = mysqli_fetch_assoc($old_data_query);
                
                if ($old_data && !empty($old_data['image_path'])) {
                    $old_file = $target_dir . $old_data['image_path'];
                    // Jangan hapus jika itu file default (man-x.png)
                    if (file_exists($old_file) && !preg_match('/man-\d\.png$/', $old_data['image_path'])) {
                        unlink($old_file);
                    }
                }

                // Update database path gambar
                $check = mysqli_query($conn, "SELECT id FROM site_hero_slides WHERE id = $i");
                if (mysqli_num_rows($check) > 0) {
                    mysqli_query($conn, "UPDATE site_hero_slides SET image_path = '$new_name' WHERE id = $i");
                } else {
                    mysqli_query($conn, "INSERT INTO site_hero_slides (id, image_path) VALUES ($i, '$new_name')");
                }
            }
        }
    }

    $stats_sub    = mysqli_real_escape_string($conn, $_POST['stats_sub']);
    $stats_follow = mysqli_real_escape_string($conn, $_POST['stats_follow']);
    $stats_order  = mysqli_real_escape_string($conn, $_POST['stats_order']);

    // Update tabel site_stats
    mysqli_query($conn, "UPDATE site_stats SET 
        subscribers = '$stats_sub', 
        followers = '$stats_follow', 
        orders = '$stats_order' 
        WHERE id = 1");


    // Kembali ke halaman utama admin dengan notifikasi sukses
    header("Location: main_website.php?status=success");
    exit();
}