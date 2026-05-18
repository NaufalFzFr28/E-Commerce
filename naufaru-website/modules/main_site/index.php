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

//===Query Portfolio Section===//

// Menggunakan $row sesuai variabel loop di baris 358 dan menambahkan ../../ untuk path folder
// $imagePath = '../../assets/imgs/img-portfolio/' . $row['image_path'];

// Cek apakah file ada secara fisik dan kolom image_path tidak kosong
// if (!empty($row['image_path']) && file_exists($imagePath)) {
//     $displayImg = $imagePath;
// } else {
//     $displayImg = '../../assets/imgs/default-portfolio.jpg';
// }

?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>NaufaRu - Main Site</title>
    
    <link rel="stylesheet" href="../../assets/vendors/bootstrap/css/bootstrap.min.css">
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
                        <a href="#services" class="menu-item"><i class="fas fa-th-list"></i> <?php echo $text['menu_service']; ?></a>
                        <a href="#gallery-work" class="menu-item"><i class="fas fa-paint-brush"></i> <?php echo $text['menu_gallery_work']; ?></a>
                        <a href="#gallery-video" class="menu-item"><i class="fas fa-video"></i> <?php echo $text['menu_gallery_video']; ?></a>
                        <a href="#testimonial" class="menu-item"><i class="fas fa-comment-dots"></i> <?php echo $text['menu_testimonial']; ?></a>
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
        <div class="container-fluid" style="padding: 50px 10%;"> 
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

    <!-- Skills Section -->
    <section id="skills" class="section reveal">
        <div class="container-fluid" style="padding: 60px 10%;">
            <div class="text-center mb-5">
                <h6 class="subtitle reveal"><?php echo $text['skills_subtitle']; ?></h6>
                <h2 class="section-title mb-4 reveal"><?php echo $text['skills_title']; ?></h2>
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
        <div class="container-fluid" style="padding: 60px 10%;">
            
            <!-- Judul Section -->
            <div class="text-center mb-5">
                <h6 class="subtitle"><?php echo $text['portfolio_subtitle'] ?? 'Galeri Karya'; ?></h6>
                <h2 class="section-title mb-4"><?php echo $text['portfolio_title'] ?? 'Projek Terbaik Saya'; ?></h2>
                <p><?php echo $text['portfolio_desc'] ?? 'Menampilkan Karya Terbaik Saya.'; ?></p>
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

                <!-- Dropdown Tampilan Grid -->
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
            <div class="text-center mt-5 mb-5" id="load-more-container">
                <button id="btn-load-more" class="btn btn-naufaru rounded-pill shadow-sm animate__animated animate__fadeIn">
                    <i class="fas fa-plus-circle"></i> 
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
        <div class="container-fluid" style="padding: 60px 10%;">
            
            <div class="text-center mb-5">
                <h6 class="subtitle"><?php echo $text['video_portfolio_subtitle'] ?? 'Galeri Video'; ?></h6>
                <h2 class="section-title mb-4"><?php echo $text['video_portfolio_title'] ?? 'Visual yang Memukau Anda'; ?></h2>
                <p><?php echo $text['video_portfolio_desc'] ?? 'Menelusuri Portofolio Video Saya yang Mengagumkan.'; ?></p>
            </div>

            <div class="video-alerts-container mb-5">
                <?php 
                // Mengambil alert dinamis aktif khusus untuk modul video portfolio
                $q_video_alerts = mysqli_query($conn, "SELECT * FROM site_portfolio_alerts WHERE is_active = 1 ORDER BY id DESC");
                
                while($v_alert = mysqli_fetch_assoc($q_video_alerts)):
                    // Mengatur translasi bahasa dinamis teks alert
                    $v_msg_text = !empty($v_alert["text_$lang"]) ? $v_alert["text_$lang"] : $v_alert["text_id"];
                    $v_lnk_text = !empty($v_alert["link_text_$lang"]) ? $v_alert["link_text_$lang"] : $v_alert["link_text_id"];
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

                <button class="btn btn-naufaru rounded-pill px-4" id="sort-video-portfolio-btn" data-sort="newest">
                    <i class="fas fa-sort-amount-down me-2" id="sort-video-icon"></i>
                    <span id="sort-video-text"><?php echo $text['sort_newest'] ?? 'Terbaru'; ?></span>
                </button>
                
                <div class="dropdown video-custom-dropdown">
                    <button class="btn btn-naufaru rounded-pill px-4 dropdown-toggle" type="button" id="videoGridDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-th-large me-2"></i><?php echo $text['grid_title'] ?? 'Tampilan Grid'; ?>
                    </button>
                    <ul class="dropdown-menu glass-dropdown shadow-lg border-0" aria-labelledby="videoGridDropdown">
                        <li><a class="dropdown-item video-grid-switcher" href="javascript:void(0)" data-grid="6"><?php echo $text['grid_2_col'] ?? '2 Kolom'; ?></a></li>
                        <li><a class="dropdown-item video-grid-switcher" href="javascript:void(0)" data-grid="4"><?php echo $text['grid_3_col'] ?? '3 Kolom'; ?></a></li>
                    </ul>
                </div>
            </div>

            <div class="row" id="video-portfolio-parent-container">
                <?php 
                // Mengambil portofolio video aktif dari tabel database baru site_video_portfolio
                $q_video_porto = mysqli_query($conn, "SELECT * FROM site_video_portfolio WHERE is_active = 1 ORDER BY id DESC");
                $v_index = 0;
                
                // Helper function internal untuk konversi URL YouTube biasa ke format embed safety
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
                    
                    // Aturan Sembunyikan Awal (Load More): Video urutan ke-4 dan seterusnya disembunyikan dahulu
                    $load_more_hiding_class = ($v_index >= 4) ? 'd-none video-item-hidden-state' : '';
                ?>
                    <div class="col-sm-6 col-lg-4 video-portfolio-card-item mb-4 <?php echo $load_more_hiding_class; ?>" data-id="<?php echo $v_row['id']; ?>">
                        <div class="box-video-card h-100 d-flex flex-column">
                            <div class="video-media-container-wrapper">
                                <iframe class="youtube-video-frame" src="<?php echo $clean_embed_url; ?>" allowfullscreen loading="lazy"></iframe>
                            </div>
                            <div class="caption-video-content p-4 text-center d-flex flex-column flex-grow-1">
                                <h4><?php echo htmlspecialchars($display_v_title); ?></h4>
                                <p class="mt-2 text-muted mb-0"><?php echo htmlspecialchars($display_v_desc); ?></p>
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
                    <button id="btn-video-load-more" class="btn btn-naufaru rounded-pill shadow-sm">
                        <i class="fas fa-plus-circle me-2"></i> 
                        <span><?php echo $text['promo_btn'] ?? 'Lihat Selengkapnya'; ?></span>
                    </button>
                </div>
            <?php endif; ?>

        </div>
    </section>                                


    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Script -->
    <script src="../../assets/js/script.js"></script>
</body>
</html>