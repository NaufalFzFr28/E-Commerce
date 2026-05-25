<?php
session_start(); // Membaca sesi dari Splash Screen 

include '../../config.php';
include '../../functions.php';

// --- LOGIKA PENGATURAN BAHASA PERSISTEN ---
if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
    $_SESSION['lang'] = $lang; // Update session jika ganti bahasa di main site 
} elseif (isset($_SESSION['lang'])) {
    $lang = $_SESSION['lang']; // Ambil bahasa yang sudah terpilih sebelumnya 
} else {
    $lang = 'id'; // Fallback default 
}

// Ambil data pengaturan site
$settings = $conn->query("SELECT * FROM site_settings WHERE id = 1")->fetch_assoc();

// Tentukan path file JSON berdasarkan bahasa
$json_file = "../../languages/{$lang}.json";

if (file_exists($json_file)) {
    $json_data = file_get_contents($json_file);
    $text = json_decode($json_data, true);
} else {
    // Fallback ke bahasa Indonesia jika file spesifik tidak ditemukan
    $fallback_file = "../../languages/id.json";
    $json_data = file_exists($fallback_file) ? file_get_contents($fallback_file) : "{}";
    $text = json_decode($json_data, true);
}

//===Query Home Section===//

// Mengambil data teks hero dari tabel site_hero
$hero_query = mysqli_query($conn, "SELECT * FROM site_hero WHERE id = 1");
$hero = mysqli_fetch_assoc($hero_query);

// Mengambil data slideshow dari tabel site_hero_slides
$slides_query = mysqli_query($conn, "SELECT * FROM site_hero_slides ORDER BY id ASC LIMIT 3");

// Pastikan query ini ada di bagian atas file index.php
$stats_query = mysqli_query($conn, "SELECT * FROM site_stats WHERE id = 1");
$st = mysqli_fetch_assoc($stats_query);

// Jika database kosong, beri nilai default agar tidak nol
$subscribers = $st['subscribers'] ?? '0';
$followers   = $st['followers'] ?? '0';
$orders      = $st['orders'] ?? '0';

// --- PEMBARUAN: AMALGAMASI WALLPAPER KHUSUS DARI TABEL BARU site_bg_dark ---
$dark_wallpapers = [];
$q_wall = mysqli_query($conn, "SELECT image_path FROM site_bg_dark WHERE is_active = 1 ORDER BY id ASC");

if ($q_wall && mysqli_num_rows($q_wall) > 0) {
    while ($row = mysqli_fetch_assoc($q_wall)) {
        // Path disesuaikan keluar dari folder modules/main_site/ menuju assets
        $dark_wallpapers[] = "../../assets/imgs/" . $row['image_path'];
    }
} else {
    // FALLBACK MUTLAK: Jika tabel baru kosong, kunci murni ke bg-dark-profile.jpeg
    $dark_wallpapers[] = "../../assets/imgs/bg-dark-profile.jpeg";
}

//===Query About Section===//

// Ambil data gambar dari database
$query_img = mysqli_query($conn, "SELECT img_front, img_back FROM site_about WHERE id = 1");
$row_img = mysqli_fetch_assoc($query_img);

// PATH: Jangan pakai ../ jika file ini dipanggil dari root index.php
$path_front = "../../assets/imgs/" . (!empty($row_img['img_front']) ? $row_img['img_front'] : "avatar-naufaru-1.jpg");
$path_back  = "../../assets/imgs/" . (!empty($row_img['img_back']) ? $row_img['img_back'] : "avatar-naufaru-2.jpg");

//===Query Promo Section===//
$q_promo = $conn->query("SELECT * FROM site_promotion WHERE id = 1");
$pr = $q_promo->fetch_assoc();

// Override data JSON dengan data dari Database (site_promotion)
if ($pr) {
    // Tentukan suffix berdasarkan bahasa sesi (id, en, jp)
    $l = $lang; 
    
    // Jika data di DB tidak kosong, gunakan data DB. Jika kosong, tetap pakai JSON ($text)
    $text['promo_title']   = !empty($pr["title_$l"]) ? $pr["title_$l"] : $text['promo_title'];
    $text['promo_caption'] = !empty($pr["caption_$l"]) ? $pr["caption_$l"] : $text['promo_caption'];
    $text['promo_btn']     = !empty($pr["btn_text_$l"]) ? $pr["btn_text_$l"] : $text['promo_btn'];
};

$q_alerts = mysqli_query($conn, "SELECT * FROM site_portfolio_alerts WHERE is_active = 1 ORDER BY id DESC");
if (!$q_alerts) {
    die("Query Error: " . mysqli_error($conn)); // Cek apakah ada error penulisan tabel/kolom
}
echo "";

//===Query Video Section===//

$q_info = mysqli_query($conn, "SELECT * FROM site_video_alerts WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
$ai = mysqli_fetch_assoc($q_info);

//===Query My Team Section===//

// 1. SETTING UTAMA ENCODING: Paksa MySQLi membaca UTF8 agar tulisan Jepang tidak hancur menjadi simbol
if (isset($conn) && $conn) {
    mysqli_set_charset($conn, "utf8");
}

// 2. Ambil preferensi warna hover dinamis dari tabel site_settings
$team_grad1 = '#EF4C4D';
$team_grad2 = 'rgba(239, 76, 77, 0.15)';
if (isset($conn) && $conn) {
    $q_team_settings = mysqli_query($conn, "SELECT team_hover_color_1, team_hover_color_2 FROM site_settings WHERE id = 1 LIMIT 1");
    if ($q_team_settings && mysqli_num_rows($q_team_settings) > 0) {
        $team_set = mysqli_fetch_assoc($q_team_settings);
        $team_grad1 = $team_set['team_hover_color_1'] ?? '#EF4C4D';
        $team_grad2 = $team_set['team_hover_color_2'] ?? 'rgba(239, 76, 77, 0.15)';
    }
}

// 3. Eksekusi Kueri penarik seluruh baris data kolaborator tim yang aktif
$q_team_members = false;
if (isset($conn) && $conn) {
    $q_team_members = mysqli_query($conn, "SELECT * FROM site_team WHERE is_active = 1 ORDER BY sort_order ASC, id DESC");
}

// Sinkronisasi Suffix Database (Sesi 'jp' dikonversi murni membaca kolom '_ja')
$team_lang_suffix = ($lang === 'jp') ? 'ja' : $lang;

?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>NaufaRu - Main Site</title>
    
    <!-- Menggunakan Jalur CDN Online Resmi Bootstrap v5 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Style -->
    <link rel="stylesheet" href="../../assets/css/style.css">
    
    <style>

    </style>
</head>
<body class="">

    <script>
        if (localStorage.getItem('theme') === 'dark') {
            document.body.classList.add('dark-mode');
        }
    </script>

    <!-- PEMBARUAN CSS BACKDROP SLIDESHOW TEMA GELAP NAUFARU -->
    <style>
        .mainsite-dark-bg-container { 
            position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1; overflow: hidden; display: none; 
        }
        /* Sistem Isolasi: Hanya muncul dan merender gambar saat website beralih ke Mode Malam */
        body.dark-mode .mainsite-dark-bg-container { 
            display: block; 
        }
        .mainsite-bg-layer {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background-size: cover; background-position: center;
            opacity: 0; transform: scale(1);
            transition: opacity 2s ease-in-out, transform 8s ease-in-out;
        }
        .mainsite-bg-layer.active { 
            opacity: 1; transform: scale(1.05); 
        }
        .mainsite-overlay-dark { 
            position: absolute; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.72); z-index: 0; 
        }
    </style>

    <!-- PEMBARUAN HTML CONTAINER: Mengeluarkan hasil upload Konfigurasi Wallpaper Tema Gelap -->
    <div class="mainsite-dark-bg-container">
        <?php foreach ($dark_wallpapers as $index => $imageUrl): ?>
            <div class="mainsite-bg-layer <?php echo $index === 0 ? 'active' : ''; ?>" 
                 style="background-image: url('<?php echo htmlspecialchars($imageUrl); ?>');">
            </div>
        <?php endforeach; ?>
        <div class="mainsite-overlay-dark"></div>
    </div>

    <!-- Menu Dropdown -->
    <header class="header-nav">
        <div class="nav-logo">
            <a href="../../index.php" class="logo-container">
                <img src="../../assets/imgs/logo-white.png" alt="NaufaRu" class="top-logo logo-white">
                <img src="../../assets/imgs/logo-dark.png" alt="NaufaRu" class="top-logo logo-dark">
            </a>
        </div>

        <div class="dropdown-wrapper">
            <button class="burger-btn" id="burgerToggle">
                <i class="fas fa-bars"></i>
            </button>
            
            <div class="main-menu" id="menuDrop">
                <div class="menu-slider">
                    <div class="menu-content">
                        <a href="#home" class="menu-item"><i class="fas fa-home"></i> <?php echo $text['menu_home']; ?></a>
                        <a href="#about" class="menu-item"><i class="fas fa-user"></i> <?php echo $text['menu_about']; ?></a>
                        <a href="#my-team-section" class="menu-item"><i class="fas fa-users"></i> <?php echo $text['menu_team']; ?></a>
                        <a href="#services" class="menu-item"><i class="fas fa-th-list"></i> <?php echo $text['menu_service']; ?></a>
                        <a href="#photo-portfolio" class="menu-item"><i class="fas fa-paint-brush"></i> <?php echo $text['menu_gallery_work']; ?></a>
                        <a href="#video-portfolio" class="menu-item"><i class="fas fa-video"></i> <?php echo $text['menu_gallery_video']; ?></a>
                        <a href="#testmonial" class="menu-item"><i class="fas fa-comment-dots"></i> <?php echo $text['menu_testimonial']; ?></a>
                        <a href="#documentation" class="menu-item"><i class="fas fa-camera-retro"></i> <?php echo $text['menu_documentation']; ?></a>
                        <a href="#gallery-hunting" class="menu-item"><i class="fas fa-train"></i> <?php echo $text['menu_gallery_hunting']; ?></a>
                        <a href="#calligraphy" class="menu-item"><i class="fas fa-pen-nib"></i> <?php echo $text['menu_calligraphy']; ?></a>
                        
                        <div class="divider"></div>
                        
                        <div class="menu-item" onclick="toggleMainMode()" 
                            id="modeToggleBtn" 
                            data-dark="<?php echo $text['mode_dark']; ?>" 
                            data-light="<?php echo $text['mode_light']; ?>">
                            <i class="fas fa-circle-half-stroke" id="modeIcon"></i> 
                            <span id="modeText"><?php echo $text['mode_dark']; ?></span>
                        </div>

                        <div class="menu-item" id="openLang">
                            <i class="fas fa-language"></i> <?php echo $text['lang_select']; ?> 
                            <i class="fas fa-chevron-right ms-auto" style="font-size: 0.7rem;"></i>
                        </div>
                        
                        <div class="divider"></div>
                        
                        <a href="../../index.php" class="menu-item btn-exit">
                            <i class="fas fa-sign-out-alt"></i> <?php echo $text['menu_exit']; ?>
                        </a>

                        <div class="menu-footer-info">
                            <strong>NaufaRu Visuals</strong><br>
                            Creative Photography & Design
                            <div class="info-socials">
                                <a href="#"><i class="fab fa-instagram"></i></a>
                                <a href="#"><i class="fab fa-behance"></i></a>
                                <a href="#"><i class="fab fa-youtube"></i></a>
                            </div>
                        </div>
                    </div>

                    <div class="lang-submenu">
                        <div class="menu-item" id="backToMain" style="background: rgba(0,0,0,0.03);">
                            <i class="fas fa-chevron-left"></i> <?php echo $text['back']; ?>
                        </div>
                        <div class="divider"></div>
                        <div class="menu-item" onclick="changeMainLang('id')">
                            <img src="https://flagcdn.com/w20/id.png" class="me-2" style="width:20px"> Indonesia
                        </div>
                        <div class="menu-item" onclick="changeMainLang('en')">
                            <img src="https://flagcdn.com/w20/us.png" class="me-2" style="width:20px"> English
                        </div>
                        <div class="menu-item" onclick="changeMainLang('jp')">
                            <img src="https://flagcdn.com/w20/jp.png" class="me-2" style="width:20px"> Japanese
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- <div class="hero-section">
        <h1 class="animate__animated animate__fadeInUp">NaufaRu Main Site</h1>
        <p class="animate__animated animate__fadeInUp animate__delay-1s">Eksplorasi karya melalui menu navigasi di atas.</p>
    </div> -->

    <!-- Home Section -->
    <section id="home" class="home-section">
        <div class="container">
            <div class="row align-items-center justify-content-center">

                <div class="col-lg-6 col-md-10 home-text text-lg-start text-center">
                    
                    <p class="intro scramble-text"><?php echo $text['home_intro'] ?? 'Halo, Nama saya'; ?></p>
                    
                    <h1 class="name scramble-text"><?php echo $text['home_name'] ?? 'Naufal FzFr'; ?></h1>
                    
                    <p class="desc scramble-text"><?php echo $text['home_desc'] ?? 'Seorang Editor, Fotografer, & Kaligrafer'; ?></p>

                    <div class="btn-group-custom mt-4">
                        <a href="#" class="btn btn-primary custom-btn shadow-sm"><?php echo $text['btn_event_history'] ?? 'Event History'; ?></a>
                        <a href="#" class="btn btn-danger custom-btn shadow-sm"><?php echo $text['btn_watch_story'] ?? 'Watch Story'; ?></a>
                    </div>

                    <div class="social-icons mt-4 justify-content-center justify-content-lg-start d-flex">
                        <a href="#"><i class="bi bi-facebook"></i></a>
                        <a href="#"><i class="bi bi-instagram"></i></a>
                        <a href="#"><i class="bi bi-youtube"></i></a>
                        <a href="#"><i class="bi bi-camera"></i></a>
                    </div>
                </div>

                <div class="col-lg-6 col-md-8 text-center home-image d-none d-lg-block">
                    <div class="swiper master-wpap-slider">
                        <div class="swiper-wrapper">
                            <?php 
                            if (mysqli_num_rows($slides_query) > 0):
                                while($s = mysqli_fetch_assoc($slides_query)): 
                            ?>
                                <div class="swiper-slide">
                                    <img src="../../assets/imgs/<?php echo $s['image_path']; ?>" alt="WPAP Slide">
                                </div>
                            <?php 
                                endwhile; 
                            else: 
                                // Fallback jika database belum ada isinya (Default NaufaRu)
                                for($i=1; $i<=3; $i++):
                            ?>
                                <div class="swiper-slide">
                                    <img src="../../assets/imgs/man-<?php echo $i; ?>.png" alt="WPAP Default">
                                </div>
                            <?php 
                                endfor;
                            endif; 
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="stats-box shadow-lg border-0">
                <div id="triggerStats" class="text-center py-2">
                    <button class="btn btn-outline-danger rounded-pill px-4 fw-bold" onclick="startStatsAnimation()">
                        <i class="fas fa-chart-bar me-2"></i> <?php echo $text['stats_btn'] ?? 'Lihat Statistik'; ?>
                    </button>
                </div>

                <div id="statsData" class="row text-center g-3 d-none reveal">
                    <div class="col-4">
                        <h3 class="text-danger fw-bold stat-number" 
                            data-target="<?php echo preg_replace('/[^0-9]/', '', $subscribers); ?>">0</h3>
                        <p class="mb-0 small stat-label"><?php echo $text['stats_subscribers']; ?></p>
                    </div>
                    
                    <div class="col-4 border-start border-end">
                        <h3 class="text-warning fw-bold stat-number" 
                            data-target="<?php echo preg_replace('/[^0-9]/', '', $followers); ?>">0</h3>
                        <p class="mb-0 small stat-label"><?php echo $text['stats_followers']; ?></p>
                    </div>
                    
                    <div class="col-4">
                        <h3 class="text-primary fw-bold stat-number" 
                            data-target="<?php echo preg_replace('/[^0-9]/', '', $orders); ?>">0</h3>
                        <p class="mb-0 small stat-label"><?php echo $text['stats_orders']; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="section">
        <div class="container-fluid" style="padding: 100px 10%;"> 
            <div class="row">
                <div class="col-md-3">
                    <div class="flip-box reveal">
                        <div class="flip-box-inner">
                            <div class="flip-box-front">
                                <img src="<?php echo $path_front; ?>?v=<?php echo time(); ?>" alt="Naufal Front" class="img-thumbnail">
                            </div>
                            <div class="flip-box-back">
                                <img src="<?php echo $path_back; ?>?v=<?php echo time(); ?>" alt="Naufal Back" class="img-thumbnail">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-9 pl-md-4">
                    <div class="about-header mb-4 reveal">
                        <h2 class="fw-bold mb-0 scramble-text" style="color: var(--text-main);"><?php echo $text['about_title']; ?></h2>
                        <p class="text-danger fw-bold scramble-text"><?php echo $text['about_subtitle']; ?></p>
                    </div>
                    
                    <div class="about-text" style="color: var(--text-main); text-align: justify;">
                        <p class="about-p reveal"><?php echo $text['about_p1']; ?></p>
                        <p class="about-p reveal"><?php echo $text['about_p2']; ?></p>
                        <p class="about-p p-desktop-only reveal"><?php echo $text['about_p3']; ?></p>

                        <div id="extraContent" class="more-content">
                            <p class="about-p reveal"><?php echo $text['about_p4']; ?></p>
                            <p class="about-p reveal"><?php echo $text['about_p5']; ?></p>
                        </div>

                        <button id="readMoreBtn" class="btn-read-more-minimal" 
                                onclick="toggleReadMore()" 
                                data-more="<?php echo $text['btn_read_more']; ?>" 
                                data-less="<?php echo $text['btn_read_less']; ?>">
                            <span id="btnText"><?php echo $text['btn_read_more']; ?></span> 
                            <i id="btnIcon" class="fas fa-chevron-down ms-1"></i>
                        </button>
                    </div>

                    <a href="https://naufaru-curriculumvitae.netlify.app/" target="_blank" class="btn btn-danger rounded-pill px-4 mt-4 shadow-sm">
                        <?php echo $text['btn_view_cv']; ?>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Promo Section -->
    <section class="reveal">
        <!-- Pastikan ID #promo-section terikat kuat sebagai jangkar CSS -->
        <div id="promo-section" class="promo-section shadow-sm">
            <span class="tutup-btn">&times;</span>
            <div class="promo-content">
                
                <div class="images-group">
                    <div class="image-container">
                        <img src="<?php echo !empty($pr['img_primary']) ? '../../assets/imgs/' . $pr['img_primary'] : '../../assets/imgs/promo-1.jpg'; ?>" alt="Promo 1">
                    </div>
                    <div class="image-container">
                        <img src="<?php echo !empty($pr['img_secondary']) ? '../../assets/imgs/' . $pr['img_secondary'] : '../../assets/imgs/promo-2.jpg'; ?>" alt="Promo 2">
                    </div>
                </div>

                <div class="text-container">
                    <!-- Judul Utama Promo dengan Efek Scramble Kontras Tinggi -->
                    <h2 class="title fw-bold scramble-text"><?php echo $text['promo_title']; ?></h2>
                    <p class="caption">
                        <?php echo $text['promo_caption']; ?>
                    </p>
                    
                    <a href="<?php echo $pr['btn_url'] ?? 'https://www.instagram.com/naufaru/'; ?>" 
                       class="see-more-btn" 
                       target="_blank" 
                       rel="noopener noreferrer">
                        <?php echo $text['promo_btn']; ?>
                    </a>
                </div>

            </div>
        </div>
    </section>
    
    <!-- Service Section -->
    <section id="services" class="section reveal">
        <div class="container-fluid" style="padding: 100px 10%;"> 
            <div class="text-center mb-5">
                <h6 class="subtitle reveal"><?php echo $text['service_subtitle']; ?></h6>
                <h2 class="section-title mb-4 reveal scramble-text scramble-main-title">
                    <?php echo $text['service_title']; ?>
                </h2>
                <p class="reveal mx-auto" style="max-width: 600px;"><?php echo $text['service_desc']; ?></p>
            </div>

                <div class="row g-4"> <div class="col-sm-6 col-md-3 reveal">
                    <div class="custom-card card border-0 shadow-sm">
                        <div class="card-body">
                            <i class="bi bi-camera icon-service"></i>
                            <h5 class="scramble-text scramble-service"><?php echo $text['service_photo']; ?></h5>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3 reveal">
                    <div class="custom-card card border-0 shadow-sm">
                        <div class="card-body">
                            <i class="bi bi-camera-reels icon-service"></i>
                            <h5 class="scramble-text scramble-service"><?php echo $text['service_video']; ?></h5>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3 reveal">
                    <div class="custom-card card border-0 shadow-sm">
                        <div class="card-body">
                            <i class="bi bi-film icon-service"></i>
                            <h5 class="scramble-text scramble-service"><?php echo $text['service_editor']; ?></h5>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3 reveal">
                    <div class="custom-card card border-0 shadow-sm">
                        <div class="card-body">
                            <i class="bi bi-palette icon-service"></i>
                            <h5 class="scramble-text scramble-service"><?php echo $text['service_calligraphy']; ?></h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- My Team Section -->
    <section id="my-team-section" class="section reveal" style="padding: 100px 10%; background: transparent;">
        <div class="container-fluid">
            
            <!-- Judul Section Komponen -->
            <div class="text-center mb-5">
                <h6 class="subtitle reveal"><?php echo $text['team_subtitle'] ?? 'Kolaborator Kreatif'; ?></h6>
                <h2 class="section-title mb-4 reveal scramble-text scramble-main-title">
                    <?php echo $text['team_title'] ?? 'Tim Profesional Saya'; ?>
                </h2>
                <p class="reveal mx-auto" style="max-width: 600px;"><?php echo $text['team_desc'] ?? ''; ?></p>
            </div>

            <!-- Flex Container: Kunci Komposisi Rata Tengah 5 Atas / 4 Bawah + 1 Tombol -->
            <div class="team-flex-container centered-dynamic-grid">
                <?php 
                if ($q_team_members && mysqli_num_rows($q_team_members) > 0):
                    $index_team = 0;
                    mysqli_data_seek($q_team_members, 0); 
                    
                    while($member = mysqli_fetch_assoc($q_team_members)):
                        $display_name = !empty($member["name_$team_lang_suffix"]) ? $member["name_$team_lang_suffix"] : ($member["name_id"] ?? 'No Name');
                        $display_role = !empty($member["role_$team_lang_suffix"]) ? $member["role_$team_lang_suffix"] : ($member["role_id"] ?? 'Staff');
                        $anim_delay = $index_team * 0.12;
                        
                        $photo_filename = $member['photo_path'];
                        $path_root = '../../../assets/imgs/img-team/' . $photo_filename;
                        $path_admin = '../../../admin/assets/imgs/img-team/' . $photo_filename;

                        if (!empty($photo_filename) && file_exists($path_root)) {
                            $photo_src = $path_root;
                        } elseif (!empty($photo_filename) && file_exists($path_admin)) {
                            $photo_src = $path_admin;
                        } else {
                            $photo_src = '../../assets/imgs/default-portfolio.jpg'; 
                        }
                ?>
                    <!-- Card Profile Item Individual -->
                    <div class="team-premium-card structural-5-col" 
                         style="animation-delay: <?= $anim_delay; ?>s; --hover-grad-1: <?= $team_grad1; ?>; --hover-grad-2: <?= $team_grad2; ?>;">
                        <div class="team-card-inner-box">
                            <div class="team-avatar-frame image-stroke-active">
                                <img src="<?= $photo_src; ?>" alt="<?= htmlspecialchars($display_name); ?>" class="team-img-png">
                            </div>
                            <div class="team-profile-info-overlay">
                                <h4 class="team-member-name"><?= htmlspecialchars($display_name); ?></h4>
                                <p class="team-member-role"><?= htmlspecialchars($display_role); ?></p>
                            </div>
                        </div>
                    </div>
                <?php 
                    $index_team++;
                    endwhile;
                ?>
                
                    <!-- TOMBOL SEJARAH TIM KECIL (Wadah transparan pengisi kolom ke-5) -->
                    <div class="structural-5-col reveal d-flex align-items-center justify-content-center" 
                         style="animation-delay: <?= $index_team * 0.12; ?>s; background: transparent; border: none; box-shadow: none;">
                        
                        <!-- Tombol Lingkaran Kecil Tanpa Teks -->
                        <button class="btn-history-circle" onclick="openHistoryModal()" title="<?php echo $text['team_history_title'] ?? 'Sejarah Tim'; ?>">
                            <i class="fas fa-arrow-right history-arrow"></i>
                        </button>
                        
                    </div>

                <?php else: ?>
                    <div style="width: 100%; text-align: center; opacity: 0.4; padding: 40px 0; color: #fff;">
                        <i class="fas fa-users-slash fa-2x mb-3" style="color: var(--accent);"></i>
                        <p style="font-size: 0.9rem;">No active team profiles available.</p>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </section>

    <!-- CUSTOM MODAL SEJARAH TIM -->
    <div id="customHistoryModal" class="custom-modal-overlay">
        <div class="custom-modal-card glass-modal-history">
            
            <!-- Tombol Close Modal -->
            <button class="btn-close-custom" onclick="closeHistoryModal()">
                <i class="fas fa-times"></i>
            </button>
            
            <div class="modal-body-content text-center">
                
                <!-- Wrapper Logo Kolaborasi (Sejajar Tengah) -->
                <div class="modal-logo-collab-wrapper mb-4">
                    <!-- Logo NaufaRu -->
                    <div class="logo-item">
                        <img src="../../assets/imgs/logo-dark.png" alt="NaufaRu" class="img-fluid logo-light-mode" style="max-height: 45px;">
                        <img src="../../assets/imgs/logo-white.png" alt="NaufaRu" class="img-fluid logo-dark-mode" style="max-height: 45px; display: none;">
                    </div>
                    
                    <!-- Tanda Silang Kolaborasi (x) -->
                    <div class="collab-cross-icon">
                        <i class="fas fa-times"></i>
                    </div>
                    
                    <!-- Logo Hello Multimedia -->
                    <div class="logo-item">
                        <img src="../../assets/imgs/HM-Light.png" alt="Hello Multimedia" class="img-fluid logo-light-mode" style="max-height: 60px;">
                        <img src="../../assets/imgs/HM-Dark.png" alt="Hello Multimedia" class="img-fluid logo-dark-mode" style="max-height: 60px; display: none;">
                    </div>
                </div>
                
                <!-- Judul Modal -->
                <h4 class="fw-bold mb-4 modal-title-history" style="color: var(--accent); letter-spacing: 1px;">
                    <?php echo $text['team_history_title'] ?? 'Sejarah Tim Kami'; ?>
                </h4>
                
                <!-- Wrapper Scrollable Khusus Mobile/Android -->
                <div class="modal-scroll-content">
                    <p class="modal-text-history">
                        <?php echo $text['team_history_text'] ?? 'Deskripsi sejarah tidak tersedia.'; ?>
                    </p>
                </div>
                
                <!-- Tombol Tautan Eksternal -->
                <div class="mt-4 pt-4 border-top-custom">
                    <a href="https://www.instagram.com/hellomultimedia/" target="_blank" class="btn-glass-link" id="teamHistoryLink">
                        <?php echo $text['team_history_btn'] ?? 'Kunjungi Tautan'; ?> <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
            
        </div>
    </div>

    <!-- Skills Section -->
    <section id="skills" class="section reveal">
        <div class="container-fluid" style="padding: 60px 10%;">
            <div class="text-center mb-5">
                <h6 class="subtitle reveal"><?php echo $text['skills_subtitle']; ?></h6>
                <h2 class="section-title mb-4 reveal scramble-text scramble-main-title"><?php echo $text['skills_title']; ?></h2>
                <p class="reveal mx-auto" style="max-width: 700px;"><?php echo $text['skills_desc']; ?></p>
            </div>

            <div class="row skills-wrapper text-left">
                <?php 
                $q_skills = mysqli_query($conn, "SELECT * FROM site_skills ORDER BY order_index ASC");
                while ($s = mysqli_fetch_assoc($q_skills)) : 
                    $skill_display_name = !empty($s["skill_name_$lang"]) ? $s["skill_name_$lang"] : $s["skill_name_id"];
                ?>
                <div class="col-md-6 skill mb-4 reveal">
                    <p class="mb-2 fw-bold skill-label">
                        <?php echo htmlspecialchars($skill_display_name); ?>
                    </p>
                    <div class="skill-bar">
                        <div class="skill-percentage" data-percentage="<?php echo $s['percentage']; ?>%"></div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <!-- Portfolio Section -->
    <section id="photo-portfolio" class="section reveal">
        <div class="container-fluid" style="padding: 100px 10%;">
            
            <!-- Judul Section -->
            <div class="text-center mb-5">
                <h6 class="subtitle reveal"><?php echo $text['portfolio_subtitle'] ?? 'Galeri Karya'; ?></h6>
                <h2 class="section-title mb-4 reveal scramble-text scramble-main-title"><?php echo $text['portfolio_title'] ?? 'Projek Terbaik Saya'; ?></h2>
                <p class="reveal mx-auto" style="max-width: 700px;"><?php echo $text['portfolio_desc'] ?? 'Menampilkan Karya Terbaik Saya.'; ?></p>
            </div>

            <!-- Alert Info Dinamis (Precision Aligned) -->
            <div class="portfolio-alerts-container mb-5">
                <?php 
                $q_alerts_final = mysqli_query($conn, "SELECT * FROM site_portfolio_alerts WHERE is_active = 1 ORDER BY id DESC");
                
                while($alert = mysqli_fetch_assoc($q_alerts_final)):
                    $msg_text = !empty($alert["text_$lang"]) ? $alert["text_$lang"] : $alert["text_id"];
                    $lnk_text = !empty($alert["link_text_$lang"]) ? $alert["link_text_$lang"] : $alert["link_text_id"];
                    $lnk_url  = $alert["link_url"];
                ?>
                    <div class="alert alert-naufaru alert-dismissible fade show animate__animated animate__fadeInUp" role="alert">
                        <div class="alert-content">
                            <span><?php echo htmlspecialchars($msg_text); ?></span>
                            <?php if(!empty($lnk_text) && !empty($lnk_url) && $lnk_url !== 'NULL'): ?>
                                <a href="<?php echo $lnk_url; ?>" target="_blank" class="alert-link-custom">
                                    <?php echo htmlspecialchars($lnk_text); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                        <!-- Tombol Close Standard Bootstrap -->
                        <button type="button" class="btn-close-naufaru" data-bs-dismiss="alert" aria-label="Close">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                <?php endwhile; ?>
            </div>

            <!-- Controls: Sortir, Filter, Grid -->
            <div class="d-flex justify-content-center flex-wrap gap-3 mb-5 controls-container" style="position: relative; z-index: 1050;">

                <!-- Tombol Sortir -->
                <button class="btn btn-naufaru rounded-pill px-4 sort-portfolio" 
                        data-sort="newest"
                        data-lang-newest="<?php echo $text['sort_newest'] ?? 'Terbaru'; ?>"
                        data-lang-oldest="<?php echo $text['sort_oldest'] ?? 'Terlama'; ?>">
                    <i class="fas fa-sort-amount-down me-2"></i><?php echo $text['sort_newest'] ?? 'Terbaru'; ?>
                </button>
                
                <!-- Dropdown Filter Kategori -->
                <div class="dropdown custom-dropdown">
                    <button class="btn btn-naufaru rounded-pill px-4 dropdown-toggle" type="button" id="filterDropdown">
                        <i class="fas fa-filter me-2"></i><?php echo $text['filter_title'] ?? 'Filter Karya'; ?>
                    </button>
                    <ul class="dropdown-menu glass-dropdown shadow-lg border-0" id="filterMenu">
                        <li>
                            <a class="dropdown-item filter-portfolio-item" href="javascript:void(0)" data-filter="all" data-label="<?php echo $text['filter_all'] ?? 'Semua Karya'; ?>">
                                <?php echo $text['filter_all'] ?? 'Semua Karya'; ?>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <?php 
                        $q_cat = mysqli_query($conn, "SELECT product_slug, product_name_id FROM site_products");
                        while($c = mysqli_fetch_assoc($q_cat)): 
                            $raw_slug = $c['product_slug'];
                            $clean_slug = strtolower(preg_replace('/[^a-zA-Z0-9]/', '-', $raw_slug));
                            $key = "filter_" . $clean_slug;
                            $display_name = isset($text[$key]) ? $text[$key] : $c['product_name_id'];
                        ?>
                        <li>
                            <a class="dropdown-item filter-portfolio-item" href="javascript:void(0)" 
                            data-filter="<?php echo $raw_slug; ?>" 
                            data-label="<?php echo $display_name; ?>">
                                <?php echo $display_name; ?>
                            </a>
                        </li>
                        <?php endwhile; ?>
                    </ul>
                </div>

                <!-- Dropdown Tampilan Grid (Foto) -->
                <div class="dropdown custom-dropdown">
                    <button class="btn btn-naufaru rounded-pill px-4 dropdown-toggle" type="button" id="gridDropdown">
                        <i class="fas fa-th-large me-2"></i><?php echo $text['grid_title'] ?? 'Tampilan Grid'; ?>
                    </button>
                    <ul class="dropdown-menu glass-dropdown shadow-lg border-0" id="gridMenu">
                        <li><a class="dropdown-item grid-switcher" href="javascript:void(0)" data-grid="6"><?php echo $text['grid_2_col'] ?? '2 Kolom'; ?></a></li>
                        <li><a class="dropdown-item grid-switcher" href="javascript:void(0)" data-grid="4"><?php echo $text['grid_3_col'] ?? '3 Kolom'; ?></a></li>
                        <li><a class="dropdown-item grid-switcher" href="javascript:void(0)" data-grid="3"><?php echo $text['grid_4_col'] ?? '4 Kolom'; ?></a></li>
                    </ul>
                </div>
            </div>

            <div class="row" id="portfolio-container-parent">
                <?php 
                $q_porto = mysqli_query($conn, "SELECT p.*, s.product_slug, s.product_name_id 
                                                FROM site_portfolio p 
                                                JOIN site_products s ON p.product_id = s.id 
                                                ORDER BY p.id DESC");
                
                while($row = mysqli_fetch_assoc($q_porto)):
                    // Terjemahan Judul & Deskripsi
                    $display_title = !empty($row["title_$lang"]) ? $row["title_$lang"] : $row["title_id"];
                    $display_desc = !empty($row["desc_$lang"]) ? $row["desc_$lang"] : $row["desc_id"];

                    // Terjemahan Kategori (Label Merah)
                    $raw_slug = $row['product_slug'];
                    $clean_slug = strtolower(preg_replace('/[^a-zA-Z0-9]/', '-', $raw_slug));
                    $key_cat = "filter_" . $clean_slug;
                    $display_category = isset($text[$key_cat]) ? $text[$key_cat] : $row["product_name_id"];

                    // Teks Tombol Modal dari JSON
                    $btn_modal_text = $text['promo_btn'] ?? 'Lihat Selengkapnya';

                    $imagePath = '../../assets/imgs/img-portfolio/' . $row['image_path'];
                    $displayImg = (!empty($row['image_path']) && file_exists($imagePath)) ? $imagePath : '../../assets/imgs/default-portfolio.jpg';
                ?>
                    <div class="col-sm-6 col-lg-4 portfolio-item mb-4" data-filter-type="<?php echo $row['product_slug']; ?>" data-id="<?php echo $row['id']; ?>">
                        <div class="portfolio-card shadow-sm">
                            <img src="<?php echo $displayImg; ?>" alt="<?php echo htmlspecialchars($display_title); ?>" class="portfolio-img">
                            
                            <div class="portfolio-overlay">
                                <h5 class="overlay-title"><?php echo htmlspecialchars($display_title); ?></h5>
                                
                                <!-- Trigger Modal dengan data yang sudah diterjemahkan PHP -->
                                <button class="btn-info-modal" onclick='showPortfolioDetail(<?php echo json_encode([
                                    "title"    => htmlspecialchars($display_title),
                                    "category" => htmlspecialchars($display_category),
                                    "desc"     => htmlspecialchars($display_desc),
                                    "link"     => $row["link_url"],
                                    "price"    => "Rp " . number_format($row["price_original"], 0, ",", "."),
                                    "img"      => $displayImg,
                                    "btnText"  => htmlspecialchars($btn_modal_text)
                                ]); ?>)'>
                                    <i class="fas fa-info-circle"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            <!-- Tombol Lihat Selengkapnya -->
            <div class="text-center mt-5" id="video-load-more-btn-container">
                <button id="btn-load-more" class="btn btn-video-load-more">
                    <i class="fas fa-plus-circle me-2"></i>
                    <span><?php echo $text['promo_btn'] ?? 'Lihat Selengkapnya'; ?></span>
                </button>
            </div>
        </div>
    </section>

    <!-- Portfolio Modal -->
    <div id="portfolioModal" class="portfolio-modal-overlay">
        <div class="portfolio-modal-content glass-card">
            <button type="button" class="close-modal-naufaru" onclick="closePortfolioDetail()">
                <i class="fas fa-times"></i>
            </button>
            
            <div class="row align-items-center">
                <div class="col-md-6">
                    <img id="modalImg" src="" class="img-fluid rounded-lg shadow-md" alt="Preview">
                </div>
                <div class="col-md-6 text-left mt-4 mt-md-0">
                    <h3 id="modalTitle" class="font-weight-bold mb-1"></h3>
                    <div id="modalCat" class="portfolio-category-label mb-3"></div>
                    <p id="modalDesc" class="modal-description mb-4"></p>
                    <h4 id="modalPrice" class="text-success font-weight-bold mb-4"></h4>
                    
                    <a id="modalLink" href="#" target="_blank" class="btn btn-naufaru rounded-pill px-5 py-2 shadow-sm"></a>
                </div>
            </div>
        </div>
    </div>

    <!-- Portfolio Video YouTube Section -->
    <section id="video-portfolio" class="section reveal">
        <div class="container-fluid" style="padding: 100px 10%;">
            
            <div class="text-center mb-5">
                <h6 class="subtitle reveal"><?php echo $text['video_portfolio_subtitle']; ?></h6>
                <h2 class="section-title mb-4 reveal scramble-text scramble-main-title"><?php echo $text['video_portfolio_title']; ?></h2>
                <p class="reveal mx-auto" style="max-width: 700px;"><?php echo $text['video_portfolio_desc']; ?></p>
            </div>

            <div class="video-alerts-container mb-5">
                <?php 
                // Mengambil alert dinamis aktif khusus untuk modul video portfolio
                $q_video_alerts = mysqli_query($conn, "SELECT * FROM site_video_alerts WHERE is_active = 1 ORDER BY id DESC");
                
                while($v_alert = mysqli_fetch_assoc($q_video_alerts)):
                    // Mengatur translasi bahasa dinamis teks alert
                    $v_msg_text = !empty($v_alert["text_".$lang]) ? $v_alert["text_".$lang] : $v_alert["text_id"];
                    $v_lnk_text = !empty($v_alert["link_text_".$lang]) ? $v_alert["link_text_".$lang] : $v_alert["link_text_id"];
                    $v_lnk_url  = $v_alert["link_url"];
                ?>
                    <div class="alert alert-naufaru alert-dismissible fade show animate__animated animate__fadeInUp" role="alert">
                        <div class="alert-content">
                            <span><?php echo htmlspecialchars($v_msg_text); ?></span>
                            <?php if(!empty($v_lnk_text) && !empty($v_lnk_url) && $v_lnk_url !== 'NULL'): ?>
                                <a href="<?php echo $v_lnk_url; ?>" target="_blank" class="alert-link-custom">
                                    <?php echo htmlspecialchars($v_lnk_text); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                        <button type="button" class="btn-close-naufaru" data-bs-dismiss="alert" aria-label="Close">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                <?php endwhile; ?>
            </div>

            <div class="d-flex justify-content-center flex-wrap gap-3 mb-5 video-controls-container" style="position: relative; z-index: 1040;">
            <button class="btn btn-naufaru rounded-pill px-4" 
                    id="sort-video-portfolio-btn" 
                    data-sort="newest"
                    data-lang-newest="<?php echo $text['sort_newest'] ?? 'Terbaru'; ?>"
                    data-lang-oldest="<?php echo $text['sort_oldest'] ?? 'Terlama'; ?>">
                <i class="fas fa-sort-amount-down me-2" id="sort-video-icon"></i>
                <span id="sort-video-text"><?php echo $text['sort_newest'] ?? 'Terbaru'; ?></span>
            </button>
            
            <!-- Dropdown Tampilan Grid (Video) -->
            <div class="dropdown custom-dropdown">
                <button class="btn btn-naufaru rounded-pill px-4 dropdown-toggle" type="button" id="videoGridDropdown">
                    <i class="fas fa-th-large me-2"></i><span id="video-grid-label"><?php echo $text['grid_title'] ?? 'Tampilan Grid'; ?></span>
                </button>
                <ul class="dropdown-menu glass-dropdown shadow-lg border-0">
                    <li><a class="dropdown-item video-grid-switcher" href="javascript:void(0)" data-grid="6"><?php echo $text['grid_2_col'] ?? '2 Kolom'; ?></a></li>
                    <li><a class="dropdown-item video-grid-switcher" href="javascript:void(0)" data-grid="4"><?php echo $text['grid_3_col'] ?? '3 Kolom'; ?></a></li>
                </ul>
            </div>
        </div>

        <div class="row" id="video-portfolio-parent-container">
            <?php 
            $q_video_porto = mysqli_query($conn, "SELECT * FROM site_video_portfolio WHERE is_active = 1 ORDER BY id DESC");
            $v_index = 0;
            
            if (!function_exists('parseYoutubeEmbedUrl')) {
                function parseYoutubeEmbedUrl($url) {
                    $id = '';
                    if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^\"&?/ ]{11})%i', $url, $match)) {
                        $id = $match[1];
                    }
                    return !empty($id) ? "https://www.youtube.com/embed/" . $id : $url;
                }
            }

            while($v_row = mysqli_fetch_assoc($q_video_porto)):
                $display_v_title = !empty($v_row["title_$lang"]) ? $v_row["title_$lang"] : $v_row["title_id"];
                $display_v_desc  = !empty($v_row["desc_$lang"]) ? $v_row["desc_$lang"] : $v_row["desc_id"];
                $clean_embed_url = parseYoutubeEmbedUrl($v_row['video_url']);
                
                // Batasi pemuatan awal maksimal 4 item video
                $load_more_hiding_class = ($v_index >= 4) ? 'd-none video-item-hidden-state' : '';
            ?>
                <div class="col-sm-6 col-lg-4 video-portfolio-card-item mb-4 <?php echo $load_more_hiding_class; ?>" data-id="<?php echo $v_row['id']; ?>">
                    <div class="box-video-card h-100 d-flex flex-column">
                        <div class="video-media-container-wrapper">
                            <iframe class="youtube-video-frame" src="<?php echo $clean_embed_url; ?>" allowfullscreen loading="lazy"></iframe>
                        </div>
                        <div class="caption-video-content p-4 text-center d-flex flex-column flex-grow-1">
                            <h4><?php echo htmlspecialchars($display_v_title); ?></h4>
                            <p class="mt-2 text-muted-kustom mb-0"><?php echo htmlspecialchars($display_v_desc); ?></p>
                        </div>
                    </div>
                </div>
            <?php 
                $v_index++;
            endwhile; 
            ?>
        </div>

        <?php if ($v_index > 4): ?>
            <div class="text-center mt-5" id="video-load-more-btn-container">
                <button id="btn-video-load-more" class="btn btn-video-load-more">
                    <i class="fas fa-plus-circle me-2"></i> 
                    <span><?php echo $text['promo_btn'] ?? 'Lihat Selengkapnya'; ?></span>
                </button>
            </div>
        <?php endif; ?>

        </div>
    </section>    
    
    <!-- Testimonial Section -->
    <section id="testmonial" class="section reveal" style="padding: 100px 0; background: transparent;">
        <div class="container-fluid" style="padding: 0 10%;">
            
            <!-- Header Section -->
            <div class="text-center mb-5">
                <h6 class="subtitle reveal"><?php echo $text['testi_subtitle'] ?? 'Testimonial'; ?></h6>
                <h2 class="section-title mb-4 reveal scramble-text scramble-main-title"><?php echo $text['testi_title'] ?? 'Apa Kata Mereka Tentang Karya Saya'; ?></h2>
                <p class="reveal mx-auto" style="max-width: 700px;"><?php echo $text['testi_desc'] ?? 'Temukan keindahan tak terbatas dalam setiap detail! Segera miliki karya unik kami yang menyatu dengan keindahan dan inovasi. Jelajahi koleksi kami sekarang dan hadirkan sentuhan istimewa ke dalam hidup Anda.'; ?></p>
            </div>

            <!-- Alert Hijau Penanda Review (Dinamis & Diperlebar) -->
            <div class="row justify-content-center mb-5 reveal">
                <div class="col-12">
                    <?php 
                    // Mengambil data alert testimoni aktif dari database
                    $q_testi_alerts = mysqli_query($conn, "SELECT * FROM site_testi_alerts WHERE is_active = 1 ORDER BY id DESC");
                    
                    if ($q_testi_alerts && mysqli_num_rows($q_testi_alerts) > 0):
                        while ($alert = mysqli_fetch_assoc($q_testi_alerts)):
                            $alert_text = !empty($alert["text_$lang"]) ? $alert["text_$lang"] : $alert["text_id"];
                            $alert_link_text = !empty($alert["link_text_$lang"]) ? $alert["link_text_$lang"] : ($alert["link_text_id"] ?? '');
                    ?>
                        <div class="alert alert-custom-green fade show d-flex align-items-center justify-content-between mb-3" role="alert">
                            <span class="mx-auto text-center w-100" style="font-size: 0.85rem; font-weight: 600;">
                                <?= htmlspecialchars($alert_text); ?> 
                                <?php if(!empty($alert['link_url'])): ?>
                                    <a href="<?= htmlspecialchars($alert['link_url']) ?>" class="alert-link ms-2" style="color: #ef4c4d; text-decoration: none;" target="_blank"><?= htmlspecialchars($alert_link_text) ?></a>
                                <?php endif; ?>
                            </span>
                            
                            <button type="button" class="btn-close-custom-alert" onclick="this.closest('.alert').remove();" aria-label="Close" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; opacity: 0.5; padding: 0;">
                                <span aria-hidden="true"><i class="fas fa-times"></i></span>
                            </button>
                        </div>
                    <?php 
                        endwhile;
                    endif; 
                    ?>
                </div>
            </div>

            <!-- Wrapper Slider Swiper -->
            <div class="row justify-content-center reveal">
                <div class="col-md-10 col-lg-8 position-relative">
                    
                    <!-- Swiper Container -->
                    <div class="swiper testimonialSwiper">
                        <div class="swiper-wrapper">
                            
                            <?php 
                            // Mengambil data testimoni yang berstatus Aktif (1)
                            $q_testi = mysqli_query($conn, "SELECT t.*, m.nama_lengkap as member_name, m.foto_profil 
                                                            FROM site_testimonials t 
                                                            LEFT JOIN users_member m ON t.member_id = m.id 
                                                            WHERE t.is_active = 1 
                                                            ORDER BY t.created_at DESC");

                            if (mysqli_num_rows($q_testi) > 0):
                                while($testi = mysqli_fetch_assoc($q_testi)): 
                                    // Logika Penentuan Nama
                                    $display_name = !empty($testi['member_name']) ? $testi['member_name'] : $testi['manual_name'];
                                    
                                    // Logika Penentuan Foto Profil
                                    if (!empty($testi['foto_profil']) && file_exists("../../assets/imgs/profiles/" . $testi['foto_profil'])) {
                                        $photo_src = "../../assets/imgs/profiles/" . $testi['foto_profil'];
                                    } elseif (!empty($testi['manual_photo']) && file_exists("../../assets/imgs/profiles/" . $testi['manual_photo'])) {
                                        $photo_src = "../../assets/imgs/profiles/" . $testi['manual_photo'];
                                    } else {
                                        $photo_src = "../../assets/imgs/profiles/default-member.png"; // Fallback foto
                                    }
                            ?>
                            <div class="swiper-slide">
                                <div class="testimonial-card">
                                    <div class="testi-avatar-wrapper">
                                        <img src="<?= $photo_src; ?>" alt="<?= htmlspecialchars($display_name); ?>" class="testi-avatar">
                                    </div>
                                    <p class="testi-text">
                                        "<?= nl2br(htmlspecialchars($testi['review_text'])); ?>"
                                    </p>
                                    <h5 class="testi-name"><?= htmlspecialchars($display_name); ?></h5>
                                    <span class="testi-job"><?= htmlspecialchars($testi['pekerjaan']); ?></span>
                                </div>
                            </div>
                            <?php 
                                endwhile; 
                            else: 
                            ?>
                            <!-- Fallback jika belum ada testimoni aktif -->
                            <div class="swiper-slide">
                                <div class="testimonial-card" style="opacity: 0.5;">
                                    <i class="fas fa-comment-slash fa-3x mb-3" style="color: var(--accent);"></i>
                                    <p class="testi-text">Belum ada ulasan pelanggan saat ini.</p>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                        </div>
                        
                        <!-- Titik Navigasi (Pagination) -->
                        <div class="swiper-pagination mt-4"></div>
                    </div>

                    <!-- Panah Kanan & Kiri Custom -->
                    <div class="swiper-button-prev custom-swiper-nav"><i class="fas fa-chevron-left"></i></div>
                    <div class="swiper-button-next custom-swiper-nav"><i class="fas fa-chevron-right"></i></div>

                </div>
            </div>

        </div>
    </section>
    <!-- End of Testimonial Section -->


    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Script -->
    <script src="../../assets/js/script.js"></script>

    <!-- PEMBARUAN JAVASCRIPT: Mengontrol pergantian cross-fade background secara otomatis -->
    <script>
        $(document).ready(function() {
            const m_layers = document.querySelectorAll('.mainsite-bg-layer');
            let currentMLayer = 0;
            const m_interval = 6000; // Gambar berganti otomatis setiap 6 detik

            function nextMainSiteBg() {
                if (m_layers.length <= 1) return;
                m_layers[currentMLayer].classList.remove('active');
                currentMLayer = (currentMLayer + 1) % m_layers.length;
                m_layers[currentMLayer].classList.add('active');
            }

            // Loop slideshow hanya berjalan murni jika gambar lebih dari satu dan mode gelap aktif
            if (m_layers.length > 1 && document.body.classList.contains('dark-mode')) {
                setInterval(nextMainSiteBg, m_interval);
            }
        });
    </script>
</body>
</html>