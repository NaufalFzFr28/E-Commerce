<?php
session_start(); 

// Proteksi halaman
include 'cek_login_member.php'; 
include '../../config.php';
include '../../functions.php';

$member_id = $_SESSION['member_id'];
$username  = $_SESSION['member_username'];
$foto      = $_SESSION['member_foto'];

// --- PETUNJUK GANTI KODE: SINKRONISASI TABEL INDEPENDEN UNTUK MEMBER DASHBOARD ---
$dark_wallpapers = [];
$q_wall = mysqli_query($conn, "SELECT image_path FROM site_bg_dark WHERE is_active = 1 ORDER BY id ASC");

if ($q_wall && mysqli_num_rows($q_wall) > 0) {
    while ($row = mysqli_fetch_assoc($q_wall)) {
        $dark_wallpapers[] = "../../assets/imgs/" . $row['image_path'];
    }
} else {
    // FALLBACK INSTAN: Mengamankan pemandangan dashboard transparan member
    $dark_wallpapers[] = "../../assets/imgs/bg-dark-profile.jpeg";
}

// Mengambil data lengkap member untuk fitur "Lihat Profil"
$q_member = mysqli_query($conn, "SELECT * FROM users_member WHERE id = '$member_id'");
$data_m = mysqli_fetch_assoc($q_member);

// --- LOGIKA PENGATURAN BAHASA ---
if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
    $_SESSION['lang'] = $lang; 
} elseif (isset($_SESSION['lang'])) {
    $lang = $_SESSION['lang']; 
} else {
    $lang = 'id'; 
}

$settings = $conn->query("SELECT * FROM site_settings WHERE id = 1")->fetch_assoc();

// Path JSON dikoreksi sesuai struktur folder Anda
$json_file = "languages/member_{$lang}.json";

if (file_exists($json_file)) {
    $json_data = file_get_contents($json_file);
    $text = json_decode($json_data, true);
} else {
    $fallback_file = "languages/member_id.json";
    $json_data = file_exists($fallback_file) ? file_get_contents($fallback_file) : "{}";
    $text = json_decode($json_data, true);
}

$text['menu_lang'] = $text['menu_lang'] ?? ($lang == 'id' ? 'Pilih Bahasa' : 'Select Language');
$text['menu_exit_member'] = $text['menu_logout'] ?? ($lang == 'id' ? 'Logout' : 'Logout');

// Pemilihan kolom database berdasarkan bahasa
$col_name = ($lang == 'en') ? 'product_en' : (($lang == 'jp') ? 'product_jp' : 'product_name');
$col_desc = ($lang == 'en') ? 'deskripsi_en' : (($lang == 'jp') ? 'deskripsi_jp' : 'deskripsi');
$col_cat  = ($lang == 'en') ? 'kategori_en' : (($lang == 'jp') ? 'kategori_jp' : 'kategori');

// Hitung Total Pesanan (Pending + Finished)
$q_total_order = mysqli_query($conn, "SELECT COUNT(*) as total FROM orders WHERE member_id = '$member_id'");
$row_total = mysqli_fetch_assoc($q_total_order);
$total_pesanan = $row_total['total'] ?? 0;

// CRITICAL CHECK: LOGIKA EVALUASI STATUS SURVEI ONBOARDING
$show_survey = false;
$check_survey = mysqli_query($conn, "SELECT id FROM member_surveys WHERE member_id = '$member_id'");
if (mysqli_num_rows($check_survey) === 0) {
    $show_survey = true;
}

?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>NaufaRu - Member Dashboard</title>
    
    <!-- Link Assets dengan Path Koreksi -->
    <!-- <link rel="stylesheet" href="../../../assets/vendors/bootstrap/css/bootstrap.min.css"> -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="style_member.css">
    
    <style>
        /* CSS ISOLASI: Background dinamis slideshow hanya boleh muncul dan merender saat tema gelap aktif */
        .dashboard-dark-bg-container {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1; overflow: hidden; display: none;
        }
        body.dark-mode .dashboard-dark-bg-container {
            display: block; /* Aktif murni di tema malam */
        }
        .dashboard-bg-layer {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background-size: cover; background-position: center;
            opacity: 0; transform: scale(1);
            transition: opacity 2s ease-in-out, transform 7s ease-in-out;
        }
        .dashboard-bg-layer.active {
            opacity: 1; transform: scale(1.04);
        }
        .dashboard-overlay-dark {
            position: absolute; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.65); z-index: 0;
        }
        /* Memastikan pembungkus konten utama berlatar transparan kaca agar slideshow tembus pandang */
        body.dark-mode .member-content-wrapper {
            background: transparent !important;
            position: relative;
            z-index: 2;
        }
        
        /* Terapkan setelan css box radio button agar adaptif di dalam SweetAlert */
        .survey-radio-wrapper {
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }
        .survey-radio-wrapper input {
            cursor: pointer;
            width: 16px;
            height: 16px;
        }
        .survey-radio-wrapper label {
            cursor: pointer;
            margin-left: 10px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .survey-custom-input {
            width: 100% !important; 
            box-sizing: border-box !important;
            border: 1px solid #EF4C4D !important; 
            border-radius: 12px !important;
            font-size: 0.88rem !important;
            padding: 12px 16px !important;
            outline: none !important;
            transition: all 0.3s ease-in-out !important;
            box-shadow: none !important;
        }

        .survey-custom-input:focus {
            border-color: #d43f40 !important;
            box-shadow: 0 0 10px rgba(239, 76, 77, 0.25) !important;
        }
    </style>
</head>
<body>

    <div class="dashboard-dark-bg-container">
        <?php foreach ($dark_wallpapers as $index => $imageUrl): ?>
            <div class="dashboard-bg-layer <?php echo $index === 0 ? 'active' : ''; ?>" 
                style="background-image: url('<?php echo htmlspecialchars($imageUrl); ?>');">
            </div>
        <?php endforeach; ?>
        <div class="dashboard-overlay-dark"></div>
    </div>

    <script>
        if (localStorage.getItem('theme') === 'dark') {
            document.body.classList.add('dark-mode');
        }
    </script>

    <header class="header-nav">
        <div class="nav-logo">
            <a href="../../index.php" class="logo-container">
                <img src="../../assets/imgs/logo-white.png" alt="NaufaRu" class="top-logo logo-white">
                <img src="../../assets/imgs/logo-dark.png" alt="NaufaRu" class="top-logo logo-dark">
            </a>
        </div>

        <div class="header-controls">
            <div class="dropdown-wrapper" id="userProfileContainer">
                <button class="profile-trigger" id="profileToggleBtn">
                    <img src="../../assets/imgs/profiles/<?php echo $foto; ?>" alt="Profile" onerror="this.src='../../assets/imgs/profiles/default-member.png';">
                </button>
                <div class="main-menu" id="profileDropdownContent">
                    <div class="menu-content">
                        <div class="account-preview-box">
                            <img src="../../assets/imgs/profiles/<?php echo $foto; ?>" class="profile-pic-large" onerror="this.src='../../assets/imgs/profiles/default-member.png';">
                            <div class="user-name-title"><?php echo htmlspecialchars($username); ?></div>
                            <div class="user-role-subtitle">Member NaufaRu</div>
                        </div>
                        <div class="menu-divider"></div>
                        
                        <a href="javascript:void(0)" class="menu-item" onclick="openProfileModal()">
                            <i class="fas fa-user-circle"></i> <?php echo $text['menu_profile']; ?>
                        </a>
                        
                        <a href="logout.php" class="menu-item btn-exit">
                            <i class="fas fa-sign-out-alt"></i> <?php echo $text['menu_logout']; ?>
                        </a>
                    </div>
                </div>
            </div>

            <div class="dropdown-wrapper" id="navMenuContainer">
                <button class="burger-btn" id="burgerToggleBtn">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="main-menu" id="navDropdownContent">
                    <div class="menu-slider">
                        <div class="menu-content">
                            <a href="#dashboard" class="menu-item nav-section-link">
                                <i class="fas fa-th-large"></i> <?php echo $text['menu_dashboard']; ?>
                            </a>
                            <a href="#katalog" class="menu-item nav-section-link">
                                <i class="fas fa-shopping-bag"></i> <?php echo $text['section_catalog']; ?>
                            </a>
                            <a href="#pesanan-saya" class="menu-item nav-section-link">
                                <i class="fas fa-shopping-cart"></i> <?php echo $text['section_my_order']; ?>
                            </a>
                            <a href="#pending-orders" class="menu-item nav-section-link">
                                <i class="fas fa-spinner"></i> <?php echo $text['section_pending']; ?>
                            </a>
                            <a href="#riwayat" class="menu-item nav-section-link">
                                <i class="fas fa-history"></i> <?php echo $text['menu_riwayat']; ?>
                            </a>

                            <div class="divider"></div>
                            
                            <div class="menu-item" onclick="toggleMainMode()" id="modeToggleBtn" 
                                data-dark="<?php echo $text['menu_mode_dark']; ?>" 
                                data-light="<?php echo $text['menu_mode_light']; ?>">
                                <i class="fas fa-circle-half-stroke" id="modeIcon"></i> 
                                <span id="modeText"><?php echo ($lang == 'id' ? 'Mode Terang' : ($lang == 'en' ? 'Light Mode' : 'ライトモード')); ?></span>
                            </div>
                            <div class="menu-item" id="openLangBtn">
                                <i class="fas fa-language"></i> <?php echo $text['menu_lang']; ?> 
                                <i class="fas fa-chevron-right ms-auto" style="font-size: 0.7rem;"></i>
                            </div>
                        </div>
                        <div class="lang-submenu">
                            <div class="menu-item" id="backToMenuBtn" style="background: rgba(255,255,255,0.03);">
                                <i class="fas fa-chevron-left"></i> <?php echo $text['back']; ?>
                            </div>
                            <div class="divider"></div>
                            <div class="menu-item" onclick="location.href='?lang=id'"> Indonesia</div>
                            <div class="menu-item" onclick="location.href='?lang=en'"> English</div>
                            <div class="menu-item" onclick="location.href='?lang=jp'"> Japanese</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="member-content-wrapper">
        <div class="container-fluid px-md-5">
            
            <section id="dashboard">
                <div class="dashboard-main-card animate__animated animate__fadeIn">
                    <div class="row">
                        <div class="col-lg-12">
                            <h1 class="fw-bold welcome-text">
                                <?php echo $text['welcome_title']; ?>, 
                                <span class="scramble-text" data-value="<?php echo htmlspecialchars($username); ?>">
                                    <?php echo htmlspecialchars($username); ?>
                                </span>!
                            </h1>
                            <p class="lead welcome-subtext"><?php echo $text['welcome_subtitle']; ?></p>
                            
                            <div class="stats-grid-member">
                                <div class="stats-mini-card">
                                    <small class="stats-label"><?php echo $text['stat_total_order']; ?></small>
                                    <div class="stats-justify-row">
                                        <h3 class="fw-bold stats-value" id="dashboard-total-orders">
                                            <?php echo $total_pesanan; ?>
                                        </h3>
                                        <i class="fas fa-shopping-cart stats-icon"></i>
                                    </div>
                                </div>
                                
                                <div class="stats-mini-card">
                                    <small class="stats-label"><?php echo $text['stat_membership']; ?></small>
                                    <div class="stats-justify-row">
                                        <h3 class="fw-bold stats-value">
                                            <?php echo date('d M Y', strtotime($data_m['created_at'])); ?>
                                        </h3>
                                        <i class="fas fa-calendar-alt stats-icon"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="katalog" class="member-katalog-container">
                <div class="section-header-card glass-card mb-4">
                    <h4 class="mb-0 fw-bold"><i class="fas fa-shopping-bag me-2"></i> <?php echo $text['section_catalog']; ?></h4>
                </div>
                
                <div class="katalog-row"> 
                    <?php 
                    $products = mysqli_query($conn, "SELECT *, $col_name AS display_name, $col_desc AS display_desc, $col_cat AS display_cat FROM site_products_promo WHERE is_active = 1 ORDER BY id DESC");
                    
                    while($p = mysqli_fetch_assoc($products)):
                        $displayImg = "../../../assets/imgs/img-catalog/" . $p['gambar_produk'];
                        
                        $finalName = !empty($p['display_name']) ? $p['display_name'] : $p['product_name'];
                        $finalDesc = !empty($p['display_desc']) ? $p['display_desc'] : $p['deskripsi'];
                        
                        $cat_map = [
                            'Banner/X-Banner'           => 'cat_banner_x_banner',
                            'Stiker'                    => 'cat_stiker',
                            'Cetak Buku'                => 'cat_cetak_buku',
                            'Cetak Foto'                => 'cat_cetak_foto',
                            'Flyer & Poster'            => 'cat_flyer_poster',
                            'Kalender & Lembar Balik'   => 'cat_kalender',   
                            'Sertifikat'                => 'cat_sertifikat',  
                            'Lainnya'                   => 'cat_lainnya'
                        ];

                        $raw_cat = $p['kategori'];
                        $cat_key = $cat_map[$raw_cat] ?? 'cat_lainnya';
                        $translated_cat = $text[$cat_key] ?? $raw_cat;

                        $safeDesc = str_replace(["\r", "\n"], ' ', addslashes($finalDesc));
                        $safeName = addslashes($finalName);
                        $safeCat  = addslashes($translated_cat); 
                    ?>
                        <div class="member-katalog-card">
                            <img src="<?= $displayImg ?>" class="member-katalog-img">
                            <div class="member-card-overlay">
                                <p class="member-card-title"><?= htmlspecialchars($finalName); ?></p>
                                
                                <button class="btn-detail-glass" onclick='openNaufaruModal({
                                    id: "<?= $p['id'] ?>", 
                                    gambar_produk: "<?= $p['gambar_produk'] ?>",
                                    product_name: "<?= $safeName ?>",
                                    kategori: "<?= $safeCat ?>", 
                                    deskripsi: "<?= $safeDesc ?>",
                                    price: "<?= $p['price'] ?>"
                                })'>
                                    <i>i</i> <?= $text['btn_detail'] ?? 'Detail' ?>
                                </button>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </section>

            <div id="naufaruModal" class="portfolio-modal-overlay" onclick="closeNaufaruModal(event)">
                <div class="portfolio-modal-content" onclick="event.stopPropagation()">
                    <button class="close-modal-naufaru" onclick="closeNaufaruModal()">&times;</button>
                    
                    <div class="modal-left">
                        <img id="m-img" src="" alt="Preview">
                    </div>
                    
                    <div class="modal-right">
                        <h3 id="m-title"></h3>
                        <div id="m-cat" class="modal-category-badge">
                            <i class="fas fa-tag"></i> <span id="m-cat-text"></span>
                        </div>
                        <div id="m-desc"></div>
                        <div id="m-price" style="color: #EF4C4D; font-size: 1.3rem; font-weight: 800; margin-bottom: 20px;"></div>
                        <button class="btn-naufaru-modal" id="btn-add-to-cart" onclick="">
                            <i class="fas fa-cart-plus me-2"></i> <?php echo $text['btn_add_cart']; ?>
                        </button>
                    </div>
                </div>
            </div>

            <section id="pesanan-saya" class="mt-5">
                <div class="section-header-card glass-card mb-4">
                    <h4 class="mb-0 fw-bold"><i class="fas fa-shopping-cart me-2"></i> <?php echo $text['section_my_order']; ?></h4>
                </div>

                <div class="glass-card-content p-0 overflow-hidden animate__animated animate__fadeIn">
                    <div id="cart-content">
                        <div class="text-center py-5 opacity-75">
                            <div class="spinner-border text-danger mb-3" role="status"></div>
                            <p><?php echo $text['cart_empty']; ?></p>
                        </div>
                    </div>
                </div>
            </section>

            <section id="pending-orders" class="mt-5">
                <div class="section-header-card glass-card mb-4">
                    <h4 class="mb-0 fw-bold">
                        <i class="fas fa-spinner fa-spin me-2" style="color: #ffc107;"></i> 
                        <?php echo $text['section_pending'] ?? 'Pesanan di Proses'; ?>
                    </h4>
                </div>

                <div class="glass-card-content p-0 overflow-hidden animate__animated animate__fadeIn">
                    <div id="pending-list-content">
                        <div class="text-center py-5 opacity-75">
                            <div class="spinner-border text-danger mb-3" role="status"></div>
                            <p class="mb-0 fw-light">Memuat detail pesanan...</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ===================================================================
                 REVISI SINKRONISASI SEKTOR: RIWAYAT PESANAN (MANUAL INVOICE DETECTOR)
                 =================================================================== -->
            <section id="riwayat" class="mt-5 mb-5">
                <div class="section-header-card glass-card mb-4">
                    <h4 class="mb-0 fw-bold"><i class="fas fa-history me-2"></i> <?php echo $text['section_history']; ?></h4>
                </div>
                
                <div class="glass-card-content p-0 overflow-hidden animate__animated animate__fadeIn">
                    <?php
                    $q_history = mysqli_query($conn, "SELECT * FROM orders WHERE member_id = '$member_id' AND status = 'Finished' ORDER BY updated_at DESC");
                    if (mysqli_num_rows($q_history) > 0): 
                    ?>
                        <div class="table-responsive">
                            <table class="table table-borderless align-middle member-table-full">
                                <thead>
                                    <tr>
                                        <th class="text-center"><?php echo $text['table_id']; ?></th>
                                        <th class="text-center"><?php echo $text['table_order_date'] ?? 'Tanggal Order'; ?></th>
                                        <th class="text-center"><?php echo $text['table_received_admin'] ?? 'Diterima Admin'; ?></th>
                                        <th class="text-center"><?php echo $text['table_total']; ?></th>
                                        <th class="text-center"><?php echo $text['table_status']; ?></th>
                                        <th class="text-center"><?php echo $text['table_invoice']; ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($h = mysqli_fetch_assoc($q_history)): 
    $order_id_raw = $h['id'];
    
    // Detektor Invoice Manual POS Admin NaufaRu
    $is_manual_invoice = !empty($h['invoice_number']);
    $display_no = $is_manual_invoice ? $h['invoice_number'] : $h['order_number'];
    
    $q_item_names = mysqli_query($conn, "
        SELECT GROUP_CONCAT(p.$col_name SEPARATOR ', ') as multi_product_names 
        FROM order_items oi
        INNER JOIN site_products_promo p ON oi.product_id = p.id
        WHERE oi.order_id = '$order_id_raw'
    ");
    $item_names_data = mysqli_fetch_assoc($q_item_names);
    $display_product_name = !empty($item_names_data['multi_product_names']) ? $item_names_data['multi_product_names'] : 'Pesanan Selesai';

    // ==========================================================
    // LOGIKA PENGECEKAN ULASAN (Wajib berada di dalam loop)
    // ==========================================================
    $check_testi = mysqli_query($conn, "SELECT id FROM site_testimonials WHERE order_id = '$order_id_raw'");
    $is_reviewed = (mysqli_num_rows($check_testi) > 0);
?>
    <tr class="order-row-static">
        <td class="text-center fw-bold text-accent">
            #<?php echo $display_no; ?><br>
            <small class="text-muted fw-normal" style="font-size: 10px; display: block; margin-top: 2px;">
                <?php echo htmlspecialchars($display_product_name); ?>
            </small>
        </td>
        
        <td class="text-center opacity-75">
            <?= date('d M Y', strtotime($h['created_at'])) ?><br>
            <small class="text-muted" style="font-size: 11px;">
                <i class="far fa-clock me-1"></i><?= date('H:i', strtotime($h['created_at'])) ?>
            </small>
        </td>
        
        <td class="text-center text-white-adaptive fw-medium">
            <?= date('d M Y', strtotime($h['updated_at'])) ?><br>
            <small class="text-accent" style="font-size: 11px;"><i class="far fa-clock me-1"></i><?= date('H:i', strtotime($h['updated_at'])) ?></small>
        </td>

        <td class="text-center fw-bold text-white-adaptive">Rp <?php echo number_format($h['total_price'], 0, ',', '.'); ?></td>
        <td class="text-center">
            <span class="status-badge-finished"><?php echo $text['status_finished']; ?></span>
        </td>
        
        <!-- KOLOM AKSI (CETAK & ULASAN) -->
        <td class="text-center">
            <div style="display: flex; justify-content: center; align-items: center; gap: 8px;">
                <!-- Tombol PDF -->
                <a href="print_invoice_member.php?id=<?php echo $order_id_raw; ?>" target="_blank" class="btn-download-invoice" title="Cetak Invoice">
                    <i class="fas fa-file-pdf"></i>
                </a>
                
                <!-- Logika Render Tombol Ulasan -->
                <?php if ($is_reviewed): ?>
                    <!-- STATE: SUDAH DIULAS (Disabled & Ikon Centang) -->
                    <button type="button" class="btn-download-invoice" title="<?= ($lang === 'id' ? 'Sudah Diulas' : ($lang === 'en' ? 'Already Reviewed' : 'レビュー済み')) ?>" disabled>
                        <i class="fas fa-check-circle"></i>
                    </button>
                <?php else: ?>
                    <!-- STATE: BELUM DIULAS (Aktif & Ikon Pen) -->
                    <button type="button" class="btn-download-invoice" style="border: none; cursor: pointer;" onclick="openReviewPopup('<?= $order_id_raw ?>', '<?= htmlspecialchars($data_m['pekerjaan'] ?? '') ?>')" title="<?= ($lang === 'id' ? 'Tulis Ulasan' : ($lang === 'en' ? 'Write Review' : 'レビューを書く')) ?>">
                        <i class="fas fa-pen"></i>
                    </button>
                <?php endif; ?>
            </div>
        </td>
    </tr>
<?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5 opacity-50">
                            <i class="fas fa-folder-open mb-3 d-block" style="font-size: 3rem;"></i>
                            <p><?php echo $text['history_empty']; ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </main>

    <div id="profileModal" class="modal-overlay-glass">
        <div class="glass-card modal-profile-card">
            <button class="close-btn-glass" onclick="closeProfileModal()">&times;</button>
            
            <div class="modal-header-profile">
                <img src="../../assets/imgs/profiles/<?php echo $foto; ?>" class="img-profile-modal" onerror="this.src='../../assets/imgs/profiles/default-member.png';">
                <h3 class="fw-bold"><?php echo $text['profile_title']; ?></h3>
                <span class="member-id-tag">ID Member: #MBR-<?php echo $data_m['id']; ?></span>
            </div>

            <div class="modal-body-profile">
                <div class="profile-info-row">
                    <label><?php echo $text['profile_name']; ?></label>
                    <div class="info-value"><?php echo htmlspecialchars($data_m['nama_lengkap']); ?></div>
                </div>
                <div class="profile-info-row">
                    <label><?php echo $text['profile_phone']; ?></label>
                    <div class="info-value">
                        <i class="fab fa-whatsapp text-success me-2"></i> 
                        <?php echo htmlspecialchars($data_m['no_hp']); ?>
                    </div>
                </div>
                <div class="profile-info-row">
                    <label><?php echo $text['profile_address']; ?></label>
                    <div class="info-value address-value"><?php echo htmlspecialchars($data_m['alamat']); ?></div>
                </div>
                <div class="profile-info-row">
                    <label><?php echo $text['profile_username']; ?></label>
                    <div class="info-value opacity-50"><?php echo htmlspecialchars($data_m['username']); ?></div>
                </div>
            </div>
            
            <div class="mt-4 text-center">
                <button class="btn btn-naufaru rounded-pill w-100 py-3" onclick="triggerFeatureLocked()">
                    <i class="fas fa-edit"></i> <?php echo $text['btn_edit_profile']; ?>
                </button>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="../../assets/js/script.js"></script>
    <script src="script_member.js"></script>
    
    <script>
        $(document).ready(function() {
            $('.nav-section-link').on('click', function(e) {
                e.preventDefault();
                const target = $(this).attr('href');
                $('html, body').animate({
                    scrollTop: $(target).offset().top - 120
                }, 800);
                
                if(typeof closeAllDropdowns === "function") {
                    closeAllDropdowns();
                }
            });
        });

        function loadPendingOrders() {
            const currentLang = '<?php echo $lang; ?>'; 
            $.ajax({
                url: 'get_pending_orders.php?lang=' + currentLang,
                type: 'GET',
                success: function(response) {
                    $('#pending-list-content').html(response);
                },
                error: function() {
                    $('#pending-list-content').html('<div class="p-5 text-center opacity-50">Gagal memuat data pesanan.</div>');
                }
            });
        }

        $(document).ready(function() {
            loadPendingOrders();
            setInterval(loadPendingOrders, 60000); 
        });

        function triggerFeatureLocked() {
            const isDarkMode = document.body.classList.contains('dark-mode');
            const swalBg        = isDarkMode ? 'rgba(30, 30, 30, 0.95)' : '#ffffff';
            const swalTextColor = isDarkMode ? '#ffffff' : '#222222';
            const swalSubColor  = isDarkMode ? '#bbbbbb' : '#555555';

            const swalBackdrop = isDarkMode 
                ? 'rgba(0, 0, 0, 0.25) backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px);' 
                : 'rgba(255, 255, 255, 0.35) backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px);';

            const swalTitle = "<?php echo $text['lock_title'] ?? 'Fitur Terkunci'; ?>";
            const swalDesc  = "<?php echo $text['lock_desc'] ?? 'Perubahan profil hanya dapat dilakukan melalui Admin.'; ?>";
            const swalBtn   = "<?php echo $text['btn_close'] ?? 'Tutup'; ?>";

            Swal.fire({
                title: `<span style="color: ${swalTextColor}; font-weight: 700;">${swalTitle}</span>`,
                html: `<span style="color: ${swalSubColor}; font-size: 0.95rem;">${swalDesc}</span>`,
                icon: 'warning',
                confirmButtonColor: '#EF4C4D',
                confirmButtonText: swalBtn,
                background: swalBg,
                backdrop: swalBackdrop,
                customClass: {
                    popup: isDarkMode ? 'glass-card' : 'light-swal-card'
                },
                didOpen: () => {
                    const swalContainer = document.querySelector('.swal2-container');
                    if (swalContainer) {
                        swalContainer.style.zIndex = '99999';
                    }
                }
            });
        }

        <?php if ($show_survey): ?>
        $(document).ready(function() {
            const isDark = document.body.classList.contains('dark-mode');
            
            const sTitle = "<?php echo ($lang === 'id' ? 'Darimana Anda Mengetahui Kami?' : ($lang === 'en' ? 'How Did You Hear About Us?' : 'どこで私たちを知りましたか？')); ?>";
            const sDesc  = "<?php echo ($lang === 'id' ? 'Bantu tim multimedia NaufaRu meningkatkan layanan dengan memilih salah satu saluran informasi di bawah ini:' : ($lang === 'en' ? 'Help NaufaRu multimedia team improve our service by selecting one of the channels below:' : '以下の情報チャネルから1つ選択して、NaufaRuチーム의 서비스向上にご協力ください。')); ?>";
            const sOptA  = "<?php echo ($lang === 'id' ? 'Teman / Sahabat' : ($lang === 'en' ? 'Friend / Companion' : '友人・知人')); ?>";
            const sOptB  = "<?php echo ($lang === 'id' ? 'Keluarga' : ($lang === 'en' ? 'Family' : '家族')); ?>";
            const sOptC  = "<?php echo ($lang === 'id' ? 'Media Sosial (Instagram/YouTube)' : ($lang === 'en' ? 'Social Media (Instagram/YouTube)' : 'SNS (Instagram/YouTube)')); ?>";
            const sOptD  = "<?php echo ($lang === 'id' ? 'Google Maps' : ($lang === 'en' ? 'Google Maps' : 'Googleマップ')); ?>";
            const sOptE  = "<?php echo ($lang === 'id' ? 'Lainnya' : ($lang === 'en' ? 'Others' : 'その他')); ?>";
            const sPlace = "<?php echo ($lang === 'id' ? 'Tuliskan jawaban Anda di sini...' : ($lang === 'en' ? 'Write your answer here...' : 'ここに回答を入力してください...')); ?>";
            const sAlert = "<?php echo ($lang === 'id' ? 'Harap isi kolom jawaban kustom Anda!' : ($lang === 'en' ? 'Please fill out your custom answer!' : 'カスタム回答を入力してください！')); ?>";
            const sBtn   = "<?php echo ($lang === 'id' ? 'KIRIM JAWABAN' : ($lang === 'en' ? 'SUBMIT ANSWER' : '回答を送信')); ?>";
            
            Swal.fire({
                title: `<span style="color: ${isDark ? '#fff' : '#222'}; font-weight: 700; font-size:1.3rem;">${sTitle}</span>`,
                html: `
                    <div style="text-align: left; padding: 5px;" id="surveyFormContainer">
                        <p style="font-size: 0.82rem; color: ${isDark ? 'rgba(255,255,255,0.6)' : '#555'}; margin-bottom: 18px; line-height: 1.45;">
                            ${sDesc}
                        </p>
                        <div class="survey-radio-wrapper">
                            <input type="radio" name="survey_opt" id="opt_a" value="Teman / Sahabat" checked>
                            <label style="color: ${isDark ? '#fff' : '#333'}" for="opt_a">${sOptA}</label>
                        </div>
                        <div class="survey-radio-wrapper">
                            <input type="radio" name="survey_opt" id="opt_b" value="Keluarga">
                            <label style="color: ${isDark ? '#fff' : '#333'}" for="opt_b">${sOptB}</label>
                        </div>
                        <div class="survey-radio-wrapper">
                            <input type="radio" name="survey_opt" id="opt_c" value="Media Sosial (Instagram/YouTube)">
                            <label style="color: ${isDark ? '#fff' : '#333'}" for="opt_c">${sOptC}</label>
                        </div>
                        <div class="survey-radio-wrapper">
                            <input type="radio" name="survey_opt" id="opt_d" value="Google Maps">
                            <label style="color: ${isDark ? '#fff' : '#333'}" for="opt_d">${sOptD}</label>
                        </div>
                        <div class="survey-radio-wrapper">
                            <input type="radio" name="survey_opt" id="opt_e" value="Lainnya">
                            <label style="color: ${isDark ? '#fff' : '#333'}" for="opt_e">${sOptE}</label>
                        </div>
                        
                        <div id="customAnswerWrapper" style="display: none; margin-top: 15px; width: 100%;">
                            <input type="text" id="custom_textbox" class="form-control survey-custom-input" 
                                placeholder="${sPlace}"
                                style="background: ${isDark ? 'rgba(255,255,255,0.05)' : '#ffffff'}; 
                                        color: ${isDark ? '#ffffff' : '#000000'};">
                        </div>
                    </div>
                `,
                width: '460px',
                background: isDark ? 'rgba(25, 25, 25, 0.98)' : '#ffffff',
                backdrop: isDark ? 'rgba(0,0,0,0.4) backdrop-filter: blur(5px);' : 'rgba(0,0,0,0.2) backdrop-filter: blur(5px);',
                confirmButtonText: sBtn,
                confirmButtonColor: '#ef4c4d',
                allowOutsideClick: false,
                allowEscapeKey: false,
                customClass: {
                    popup: isDark ? 'glass-card border border-secondary' : 'shadow-lg border-0 rounded-4'
                },
                didOpen: () => {
                    const wrapper = document.getElementById('customAnswerWrapper');
                    $('input[name="survey_opt"]').on('change', function() {
                        if (this.value === 'Lainnya') {
                            $(wrapper).slideDown(250);
                            document.getElementById('custom_textbox').focus();
                        } else {
                            $(wrapper).slideUp(180);
                        }
                    });
                },
                preConfirm: () => {
                    const selectedOpt = document.querySelector('input[name="survey_opt"]:checked').value;
                    const customText = document.getElementById('custom_textbox').value.trim();
                    
                    if (selectedOpt === 'Lainnya' && customText === '') {
                        Swal.showValidationMessage(sAlert);
                        return false;
                    }
                    return { source: selectedOpt, custom: customText };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'proses_survey.php',
                        type: 'POST',
                        data: {
                            source_answer: result.value.source,
                            custom_answer: result.value.custom
                        },
                        success: function(response) {
                            const res = response.trim();
                            if (res === "success") {
                                Swal.fire({
                                    icon: 'success',
                                    title: "<?php echo ($lang === 'id' ? 'Terima Kasih!' : ($lang === 'en' ? 'Thank You!' : 'ありがとうございました！')); ?>",
                                    background: isDark ? '#1a1a1a' : '#fff',
                                    color: isDark ? '#fff' : '#222',
                                    timer: 1800,
                                    showConfirmButton: false
                                });
                            }
                        }
                    });
                }
            });
        });
        <?php endif; ?>

        $(document).ready(function() {
            const dLayers = document.querySelectorAll('.dashboard-bg-layer');
            let currentDLayer = 0;
            
            function nextDashboardBg() {
                if (dLayers.length <= 1) return;
                dLayers[currentDLayer].classList.remove('active');
                currentDLayer = (currentDLayer + 1) % dLayers.length;
                dLayers[currentDLayer].classList.add('active');
            }
            
            if (dLayers.length > 1 && document.body.classList.contains('dark-mode')) {
                setInterval(nextDashboardBg, 6000);
            }
        });

        // 1. Deklarasi teks multibahasa HANYA di dalam file PHP
        const rTitle = "<?= ($lang === 'id' ? 'Tulis Ulasan Anda' : ($lang === 'en' ? 'Write Your Review' : 'レビューを書く')) ?>";
        const rDesc  = "<?= ($lang === 'id' ? 'Bagikan pengalaman jujur Anda menggunakan layanan dan produk karya NaufaRu.' : ($lang === 'en' ? 'Share your honest experience using NaufaRu services and products.' : 'NaufaRuのサービスと製品を使用した率直な経験を共有してください。')) ?>";
        const rPlace = "<?= ($lang === 'id' ? 'Tuliskan ulasan Anda di sini...' : ($lang === 'en' ? 'Write your review here...' : 'ここにレビューを書いてください...')) ?>";
        const rBtn   = "<?= ($lang === 'id' ? 'KIRIM TESTIMONI' : ($lang === 'en' ? 'SUBMIT TESTIMONIAL' : '証言を送信する')) ?>";
        const rAlert = "<?= ($lang === 'id' ? 'Ulasan tidak boleh kosong!' : ($lang === 'en' ? 'Review cannot be empty!' : 'レビューは空にできません！')) ?>";
        const rCancel = "<?= ($lang === 'id' ? 'Batal' : 'Cancel') ?>";

        // 2. Fungsi Eksekusi Popup
        function openReviewPopup(orderId, pekerjaan) {
            const isDark = document.body.classList.contains('dark-mode');
            const memberId = '<?= $member_id ?>'; // Ambil session PHP
            
            Swal.fire({
                title: `<span style="color: ${isDark ? '#fff' : '#222'}; font-weight: 800; font-size: 1.3rem;">${rTitle}</span>`,
                html: `
                    <div style="text-align: left; padding: 5px;">
                        <p style="font-size: 0.85rem; color: ${isDark ? 'rgba(255,255,255,0.6)' : '#555'}; margin-bottom: 20px; line-height: 1.5;">
                            ${rDesc}
                        </p>
                        <textarea id="review_custom_text" class="form-control survey-custom-input" rows="4" 
                            placeholder="${rPlace}"
                            style="background: ${isDark ? 'rgba(255,255,255,0.05)' : '#ffffff'}; 
                                color: ${isDark ? '#ffffff' : '#000000'}; border-radius: 12px; resize: none;"></textarea>
                    </div>
                `,
                width: '460px',
                background: isDark ? 'rgba(25, 25, 25, 0.98)' : '#ffffff',
                backdrop: isDark ? 'rgba(0,0,0,0.5) backdrop-filter: blur(5px);' : 'rgba(0,0,0,0.25) backdrop-filter: blur(5px);',
                confirmButtonText: rBtn,
                confirmButtonColor: '#ef4c4d',
                showCancelButton: true,
                cancelButtonText: rCancel,
                cancelButtonColor: isDark ? 'rgba(255,255,255,0.1)' : '#dddddd',
                allowOutsideClick: false,
                customClass: {
                    popup: isDark ? 'glass-card border border-secondary' : 'shadow-lg border-0 rounded-4'
                },
                preConfirm: () => {
                    const reviewText = document.getElementById('review_custom_text').value.trim();
                    if (reviewText === '') {
                        Swal.showValidationMessage(rAlert);
                        return false;
                    }
                    return reviewText;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const reviewText = result.value;

                    // Form virtual untuk POST ke backend Admin
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '../../admin/proses_testimonial.php';

                    const fields = {
                        'action': 'add',
                        'order_id': orderId,
                        'member_id': memberId,
                        'pekerjaan': pekerjaan,
                        'review_text': reviewText
                    };

                    for (const key in fields) {
                        const hiddenField = document.createElement('input');
                        hiddenField.type = 'hidden';
                        hiddenField.name = key;
                        hiddenField.value = fields[key];
                        form.appendChild(hiddenField);
                    }

                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        document.addEventListener("DOMContentLoaded", function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('status')) {
                const status = urlParams.get('status');
                const isDark = document.body.classList.contains('dark-mode');
                
                let swalConfig = {
                    background: isDark ? '#1a1a1a' : '#ffffff',
                    color: isDark ? '#ffffff' : '#333333',
                    customClass: { popup: isDark ? 'glass-card border border-secondary' : 'shadow-lg border-0 rounded-4' }
                };

                if (status === 'success_testi') {
                    Swal.fire({
                        ...swalConfig,
                        icon: 'success',
                        title: "<?= ($lang === 'id' ? 'Terima Kasih!' : ($lang === 'en' ? 'Thank You!' : 'ありがとうございます！')) ?>",
                        text: "<?= ($lang === 'id' ? 'Ulasan Anda berhasil dikirim dan sedang menunggu peninjauan Admin.' : ($lang === 'en' ? 'Your review has been successfully submitted and is pending Admin review.' : 'レビューが正常に送信され、管理者の審査を待っています。')) ?>",
                        confirmButtonColor: '#ef4c4d'
                    });
                } else if (status === 'error_duplicate_testi') {
                    Swal.fire({
                        ...swalConfig,
                        icon: 'warning',
                        title: "<?= ($lang === 'id' ? 'Sudah Diulas' : ($lang === 'en' ? 'Already Reviewed' : 'レビュー済み')) ?>",
                        text: "<?= ($lang === 'id' ? 'Anda sudah pernah memberikan ulasan untuk ID pesanan ini.' : ($lang === 'en' ? 'You have already submitted a review for this order ID.' : 'この注文IDのレビューはすでに送信されています。')) ?>",
                        confirmButtonColor: '#ef4c4d'
                    });
                } else if (status === 'error') {
                    Swal.fire({
                        ...swalConfig,
                        icon: 'error',
                        title: "<?= ($lang === 'id' ? 'Gagal Memproses' : ($lang === 'en' ? 'Processing Failed' : '処理に失敗しました')) ?>",
                        text: "<?= ($lang === 'id' ? 'Terjadi kesalahan pada sistem. Silakan coba lagi nanti.' : ($lang === 'en' ? 'A system error occurred. Please try again later.' : 'システムエラーが発生しました。後でもう一度お試しください。')) ?>",
                        confirmButtonColor: '#ef4c4d'
                    });
                }
                
                // Membersihkan URL agar popup tidak terus-terusan muncul jika halaman direfresh
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
</body>
</html>