<?php
session_start(); 

// Proteksi halaman
include 'cek_login_member.php'; 
include '../../config.php';
include '../../functions.php';

$member_id = $_SESSION['member_id'];
$username  = $_SESSION['member_username'];
$foto      = $_SESSION['member_foto'];

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
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>NaufaRu - Member Dashboard</title>
    
    <!-- Link Assets dengan Path Koreksi -->
    <link rel="stylesheet" href="../../assets/vendors/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="style_member.css">
</head>
<body>

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
                                <i class="fas fa-history"></i> <?php echo $text['section_history']; ?>
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
                        
                        // 1. Ambil data mentah atau fallback ke Indonesia
                        $finalName = !empty($p['display_name']) ? $p['display_name'] : $p['product_name'];
                        $finalDesc = !empty($p['display_desc']) ? $p['display_desc'] : $p['deskripsi'];
                        
                        // 2. LOGIKA MAPPING KATEGORI (Tambahkan di sini)
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
                        // Ambil terjemahan dari JSON
                        $translated_cat = $text[$cat_key] ?? $raw_cat;

                        // 3. Bersihkan teks untuk keamanan JavaScript
                        $safeDesc = str_replace(["\r", "\n"], ' ', addslashes($finalDesc));
                        $safeName = addslashes($finalName);
                        $safeCat  = addslashes($translated_cat); // Gunakan hasil terjemahan
                    ?>
                        <div class="member-katalog-card">
                            <img src="<?= $displayImg ?>" class="member-katalog-img">
                            <div class="member-card-overlay">
                                <p class="member-card-title"><?= htmlspecialchars($finalName); ?></p>
                                
                                <button class="btn-detail-glass" onclick='openNaufaruModal({
                                    id: "<?= $p['id'] ?>", // TAMBAHKAN BARIS INI
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

            <script>
            function loadPendingOrders() {
                $.ajax({
                    url: 'get_pending_orders.php', // Pastikan file ini satu folder dengan dashboard.php
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
                // Refresh otomatis setiap 30 detik jika admin mengubah status
                setInterval(loadPendingOrders, 30000); 
            });
            </script>

            <section id="riwayat" class="mt-5 mb-5">
                <div class="section-header-card glass-card mb-4">
                    <h4 class="mb-0 fw-bold"><i class="fas fa-history me-2"></i> <?php echo $text['section_history']; ?></h4>
                </div>
                
                <div class="glass-card-content p-0 overflow-hidden animate__animated animate__fadeIn">
                    <?php
                    // Ambil data riwayat yang sudah selesai milik member
                    $q_history = mysqli_query($conn, "SELECT * FROM orders WHERE member_id = '$member_id' AND status = 'Finished' ORDER BY created_at DESC");
                    
                    if (mysqli_num_rows($q_history) > 0): 
                    ?>
                        <div class="table-responsive">
                            <table class="table table-borderless align-middle member-table-full">
                                <thead>
                                    <tr>
                                        <th class="text-center"><?php echo $text['table_id']; ?></th>
                                        <th class="text-center"><?php echo $text['table_date']; ?></th>
                                        <th class="text-center"><?php echo $text['table_total']; ?></th>
                                        <th class="text-center"><?php echo $text['table_status']; ?></th>
                                        <th class="text-center"><?php echo $text['table_invoice']; ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($h = mysqli_fetch_assoc($q_history)): 
                                        $order_id_raw = $h['id'];
                                        $display_no = !empty($h['invoice_number']) ? $h['invoice_number'] : $h['order_number'];
                                        
                                        // Ambil nama produk dari item order secara terperinci berdasarkan bahasa yang aktif
                                        $q_item_names = mysqli_query($conn, "
                                            SELECT GROUP_CONCAT(p.$col_name SEPARATOR ', ') as multi_product_names 
                                            FROM order_items oi
                                            INNER JOIN site_products_promo p ON oi.product_id = p.id
                                            WHERE oi.order_id = '$order_id_raw'
                                        ");
                                        $item_names_data = mysqli_fetch_assoc($q_item_names);
                                        $display_product_name = !empty($item_names_data['multi_product_names']) ? $item_names_data['multi_product_names'] : 'Pesanan Selesai';
                                    ?>
                                        <tr class="order-row-static">
                                            <td class="text-center fw-bold text-accent">
                                                #<?php echo $display_no; ?><br>
                                                <small class="text-muted fw-normal" style="font-size: 10px; display: block; margin-top: 2px;">
                                                    <?php echo htmlspecialchars($display_product_name); ?>
                                                </small>
                                            </td>
                                            <td class="text-center opacity-75"><?php echo date('d M Y', strtotime($h['created_at'])); ?></td>
                                            <td class="text-center fw-bold text-white-adaptive">Rp <?php echo number_format($h['total_price'], 0, ',', '.'); ?></td>
                                            <td class="text-center">
                                                <span class="status-badge-finished"><?php echo $text['status_finished']; ?></span>
                                            </td>
                                            <td class="text-center">
                                                <a href="print_invoice_member.php?id=<?php echo $h['id']; ?>" 
                                                target="_blank" 
                                                class="btn-download-invoice">
                                                    <i class="fas fa-file-pdf"></i>
                                                </a>
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
                    <i class="fas fa-edit me-2"></i> <?php echo $text['btn_edit_profile']; ?>
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
            // Animasi transisi section
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
                // Sertakan parameter lang ke dalam request AJAX
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
            // 1. Deteksi apakah body memiliki class 'dark-mode'
            const isDarkMode = document.body.classList.contains('dark-mode');

            // 2. Tentukan skema warna box popup berdasarkan mode aktif
            const swalBg        = isDarkMode ? 'rgba(30, 30, 30, 0.95)' : '#ffffff';
            const swalTextColor = isDarkMode ? '#ffffff' : '#222222';
            const swalSubColor  = isDarkMode ? '#bbbbbb' : '#555555';

            // 3. ATUR BACKDROP BLUR ADAPTIF (Opacity rendah agar tidak terlalu pekat)
            // Mode Gelap: Hitam transparan tipis + blur tipis
            // Mode Terang: Putih transparan tipis + blur tipis
            const swalBackdrop = isDarkMode 
                ? 'rgba(0, 0, 0, 0.25) backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px);' 
                : 'rgba(255, 255, 255, 0.35) backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px);';

            // 4. Ambil data terjemahan dinamis dari PHP
            const swalTitle = "<?php echo $text['lock_title'] ?? 'Fitur Terkunci'; ?>";
            const swalDesc  = "<?php echo $text['lock_desc'] ?? 'Perubahan profil hanya dapat dilakukan melalui Admin.'; ?>";
            const swalBtn   = "<?php echo $text['btn_close'] ?? 'Tutup'; ?>";

            // 5. Eksekusi SweetAlert2
            Swal.fire({
                title: `<span style="color: ${swalTextColor}; font-weight: 700;">${swalTitle}</span>`,
                html: `<span style="color: ${swalSubColor}; font-size: 0.95rem;">${swalDesc}</span>`,
                icon: 'warning',
                confirmButtonColor: '#EF4C4D',
                confirmButtonText: swalBtn,
                background: swalBg,
                backdrop: swalBackdrop, // Menerapkan backdrop adaptif transparan rendah
                customClass: {
                    popup: isDarkMode ? 'glass-card' : 'light-swal-card'
                },
                didOpen: () => {
                    // Memastikan tetap berada di lapisan paling depan di atas modal profil
                    const swalContainer = document.querySelector('.swal2-container');
                    if (swalContainer) {
                        swalContainer.style.zIndex = '99999';
                    }
                }
            });
        }
    </script>
</body>
</html>