<?php 
// Memastikan sesi admin aktif
include 'cek_login.php'; 
// Menghubungkan ke database
include '../config.php'; 

//Ambil data about
$q_hero = mysqli_query($conn, "SELECT * FROM site_hero WHERE id = 1");
$hero = mysqli_fetch_assoc($q_hero);

// Ambil semua data slide ke dalam array
$slides_query = mysqli_query($conn, "SELECT * FROM site_hero_slides ORDER BY id ASC LIMIT 3");
$slides_data = [];
if ($slides_query) {
    while($row = mysqli_fetch_assoc($slides_query)) {
        // Simpan berdasarkan ID sebagai key agar urutan pasti benar
        $slides_data[$row['id']] = $row['image_path'];
    }
}
// Ambil data stats
$q_stats = mysqli_query($conn, "SELECT * FROM site_stats WHERE id = 1");
$stats = mysqli_fetch_assoc($q_stats);

// Ambil data about
$q_about = mysqli_query($conn, "SELECT * FROM site_about WHERE id = 1");
$about = mysqli_fetch_assoc($q_about);

// Definisi nama file default
$default_front = 'avatar-naufaru-1.jpg';
$default_back = 'avatar-naufaru-2.jpg';

// Ambil data promo
$promo_query = mysqli_query($conn, "SELECT * FROM site_promotion WHERE id = 1"); 
$pr = mysqli_fetch_assoc($promo_query);

// Ambil data portfolio
// Ambil setting grid desktop
$q_settings = mysqli_query($conn, "SELECT portfolio_grid_desktop FROM site_settings WHERE id = 1");
$row_settings = mysqli_fetch_assoc($q_settings);
$grid_num = $row_settings['portfolio_grid_desktop'] ?? 3; // Default 3 jika kosong

// Konversi angka ke class Bootstrap
// 3 kolom = col-lg-4, 4 kolom = col-lg-3
$grid_class = ($grid_num == 4) ? "col-lg-3" : "col-lg-4";

// Ambil jumlah pesanan dengan status 'Pending' untuk notifikasi di sidebar
$q_pending = mysqli_query($conn, "SELECT COUNT(*) as total FROM orders WHERE status = 'Pending'");
$pending_data = mysqli_fetch_assoc($q_pending);
$total_pending = $pending_data['total'] ?? 0;


?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NaufaRu Admin | Konfigurasi Home</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="admin_style.css">
    <style>
        .grid-container { display: grid; grid-template-columns: 1fr 2fr; gap: 35px; margin-top: 20px; }
        .preview-wrapper { display: flex; flex-direction: column; gap: 15px; }
        .mini-preview { 
            background: rgba(255,255,255,0.05); 
            border: 1px solid var(--glass-border); 
            border-radius: 12px; 
            padding: 15px; 
            text-align: center;
        }
        .mini-preview img { 
            width: 100%; 
            height: 120px; 
            object-fit: contain; 
            border-radius: 8px; 
            margin-bottom: 15px; 
            filter: drop-shadow(0 5px 15px rgba(0,0,0,0.4));
        }

        /* Form Styling yang lebih lega */
        .form-group { 
            margin-bottom: 30px; /* Jarak antar kelompok input utama */
            display: flex;
            flex-direction: column;
        }

        .label-text { 
            font-size: 0.75rem; 
            opacity: 0.6; 
            text-transform: uppercase; 
            margin-bottom: 10px; /* Jarak antara label dan input */
            display: block; 
            letter-spacing: 1.5px;
            font-weight: 600;
        }

        .input-glass { 
            box-sizing: border-box; /* WAJIB: Agar padding tidak membuat textbox melebar keluar */
            width: 100%; 
            background: rgba(255,255,255,0.05); 
            border: 1px solid var(--glass-border); 
            border-radius: 10px; 
            padding: 12px 15px; 
            color: white; 
            outline: none; 
            transition: 0.3s;
        }

        .input-glass:focus { border-color: var(--accent); background: rgba(255,255,255,0.1); }

        /* Container untuk input grid (Greeting & Description) */
        .input-stack-box {
            display: flex;
            flex-direction: column;
            gap: 15px; /* Memberi jarak antar baris textbox */
        }

        .input-grid-box {
            display: grid;
            /* Menggunakan 1fr memastikan setiap kolom membagi ruang secara adil */
            grid-template-columns: repeat(3, 1fr); 
            gap: 15px; /* Jarak antar kolom */
            width: 100%;
            align-items: center;
        }

        @media (max-width: 768px) {
            .input-grid-box {
                grid-template-columns: 1fr;
                gap: 10px;
            }
        }
                
        /* Penyesuaian Tombol agar seragam dan proporsional */
        .btn-action-small {
            padding: 10px !important;
            font-size: 0.75rem !important;
            font-weight: 600 !important;
            border-radius: 8px !important;
            width: 100%;
            cursor: pointer;
            transition: 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-delete-img {
            background: rgba(239, 76, 77, 0.1) !important;
            color: #ff4d4d !important;
            border: 1px solid rgba(239, 76, 77, 0.2) !important;
            margin-top: 8px;
        }
        .btn-delete-img:hover { 
            background: rgba(239, 76, 77, 0.2) !important; 
            color: white !important;
            border-color: var(--accent) !important;
        }

        /* Custom SweetAlert NaufaRu Style */
        .swal2-popup {
            background: rgba(20, 20, 20, 0.95) !important;
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 25px !important;
            color: white !important;
        }
        .swal2-title, .swal2-html-container { color: white !important; }
        .swal2-timer-progress-bar { background: var(--accent) !important; }
        .swal2-confirm { background-color: var(--accent) !important; border-radius: 10px !important; }
        .swal2-cancel { background-color: rgba(255,255,255,0.1) !important; border-radius: 10px !important; color: white !important; }

        @media (max-width: 992px) { 
            .grid-container { grid-template-columns: 1fr; } 
            .input-grid-box { grid-template-columns: 1fr; }
        }

        .info-label-red {
            background: rgba(239, 76, 77, 0.1);
            border: 1px solid rgba(239, 76, 77, 0.2);
            color: #ffcccc;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: -5px;
            margin-bottom: 20px;
            backdrop-filter: blur(5px);
            line-height: 1.4;
        }
        .info-label-red i {
            color: var(--accent);
            font-size: 1.1rem;
        }
        
    </style>
</head>
<body>

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="../assets/imgs/logo-white.png" alt="Logo" class="sidebar-logo">
            <button class="toggle-btn" id="toggleBtn"><i class="fas fa-angle-left" id="toggleIcon"></i></button>
        </div>
        <nav>
            <a href="admin_dashboard.php" class="nav-link"><i class="fas fa-th-large"></i> <span class="scramble-text" data-value="Dashboard">Dashboard</span></a>
            <a href="main_website.php" class="nav-link active"><i class="fas fa-globe"></i> <span class="scramble-text" data-value="Website Utama">Website Utama</span></a>
            <a href="#" class="nav-link"><i class="fas fa-file-alt"></i> <span class="scramble-text" data-value="Curriculum Vitae">Curriculum Vitae</span></a>
            <a href="#" class="nav-link"><i class="fas fa-calendar-check"></i> <span class="scramble-text" data-value="Event Site">Event Site</span></a>
            <a href="admin_katalog.php" class="nav-link"><i class="fas fa-boxes"></i> <span class="scramble-text" data-value="Admin Katalog">Admin Katalog</span></a>
            <a href="admin_fitur.php" class="nav-link"><i class="fas fa-user-cog"></i> <span class="scramble-text" data-value="Admin Fitur">Admin Fitur</span></a>
            
            <a href="kelola_pesanan.php" class="nav-link">
                <i class="fas fa-shopping-cart"></i> 
                <span class="scramble-text" data-value="Kelola Pesanan">Kelola Pesanan</span>
                <?php if($total_pending > 0): ?>
                    <span class="pending-badge"><?= $total_pending ?></span>
                <?php endif; ?>
            </a>

            <a href="admin_member.php" class="nav-link"><i class="fas fa-users"></i> <span class="scramble-text" data-value="Daftar Member">Daftar Member</span></a>
            <a href="logout.php" class="nav-link logout-link"><i class="fas fa-sign-out-alt"></i> <span class="scramble-text" data-value="Logout">Logout</span></a>
        </nav>
    </aside>

    <main class="main-content">

        <!-- Home Section Configuration -->
        <div class="glass-card welcome-card">
            <h1>Konfigurasi Section Home</h1>
            <p style="font-size: 0.85rem; opacity: 0.8;">Atur konten visual slideshow WPAP dan informasi teks utama.</p>
        </div>

        <div class="glass-card">
            <form action="proses_update_home.php" method="POST" enctype="multipart/form-data">
                <div class="grid-container">
                    
                    <div class="preview-wrapper">
                        <h4 style="font-size: 1rem; border-bottom: 1px solid var(--glass-border); padding-bottom: 10px; margin-bottom: 15px; margin-top: 0px;">
                            <i class="fas fa-images me-2"></i> Slideshow WPAP
                        </h4>

                        <div class="info-label-red">
                            <i class="fas fa-info-circle"></i>
                            <span>Optimalkan tampilan: Gunakan gambar berasio 1:1 (Disarankan 1280x1280 px) dan berformat .png tanpa background.</span>
                        </div>
                        
                        <?php for($i=1; $i<=3; $i++): 
                            // Ambil dari slides_data berdasarkan ID $i
                            $img_db = isset($slides_data[$i]) ? $slides_data[$i] : "man-$i.png";
                            $img_path = "../assets/imgs/" . $img_db;
                            $is_default = (strpos($img_db, "man-") === 0);
                        ?>
                        <div class="mini-preview">
                            <span class="label-text">Slide <?php echo $i; ?></span>
                            <img src="<?php echo $img_path; ?>" id="preview<?php echo $i; ?>" onerror="this.src='../assets/imgs/placeholder.png'">
                            
                            <input type="file" name="slide<?php echo $i; ?>" id="input<?php echo $i; ?>" style="display:none;" onchange="previewFile(this, 'preview<?php echo $i; ?>')">
                            
                            <div style="display: flex; flex-direction: column; gap: 8px; margin-top: 5px;">
                                <button type="button" class="btn-action btn-action-small" onclick="document.getElementById('input<?php echo $i; ?>').click()">
                                    <i class="fas fa-edit"></i> Ubah
                                </button>

                                <?php if(!$is_default): ?>
                                    <button type="button" class="btn-action-small btn-delete-img" onclick="confirmReset(<?php echo $i; ?>)">
                                        <i class="fas fa-undo"></i> Reset Ke Default
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endfor; ?>
                    </div>

                    <div>
                        <h4 style="font-size: 1rem; border-bottom: 1px solid var(--glass-border); padding-bottom: 10px; margin-bottom: 25px; margin-top: 0px;">
                            <i class="fas fa-edit me-2"></i> Konten Teks Hero (Multi-Language)
                        </h4>

                        <div class="form-group">
                            <label class="label-text">NAMA UTAMA (ID & EN)</label>
                            <input type="text" name="main_name" class="input-glass" 
                                value="<?php echo htmlspecialchars($hero['main_name'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label class="label-text">NAMA UTAMA (KHUSUS JP)</label>
                            <input type="text" name="name_jp" class="input-glass" 
                                value="<?php echo htmlspecialchars($hero['name_jp'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label class="label-text">Greeting Text (ID / EN / JP)</label>
                            <div class="input-grid-box">
                                <input type="text" name="greeting_id" class="input-glass" value="<?php echo htmlspecialchars($hero['greeting_id'] ?? ''); ?>" placeholder="Indonesia">
                                <input type="text" name="greeting_en" class="input-glass" value="<?php echo htmlspecialchars($hero['greeting_en'] ?? ''); ?>" placeholder="English">
                                <input type="text" name="greeting_jp" class="input-glass" value="<?php echo htmlspecialchars($hero['greeting_jp'] ?? ''); ?>" placeholder="Japanese">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="label-text">Sub-Teks / Profesi (ID / EN / JP)</label>
                            <div class="input-stack-box">
                                <textarea name="desc_id" class="input-glass" rows="3"><?php echo htmlspecialchars($hero['desc_id'] ?? ''); ?></textarea>
                                <textarea name="desc_en" class="input-glass" rows="3"><?php echo htmlspecialchars($hero['desc_en'] ?? ''); ?></textarea>
                                <textarea name="desc_jp" class="input-glass" rows="3"><?php echo htmlspecialchars($hero['desc_jp'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <div class="form-group" style="margin-top: 30px;">
                            <label class="label-text">Konfigurasi Statistik (Stats Box)</label>
                            <div class="stats-grid-admin">
                                
                                <div class="stat-card-admin card-red">
                                    <div class="stat-header">
                                        <i class="fab fa-youtube"></i>
                                        <span>Subscribers</span>
                                    </div>
                                    <input type="text" name="stats_sub" class="input-stat-glass" value="<?php echo htmlspecialchars($stats['subscribers'] ?? '0'); ?>">
                                </div>

                                <div class="stat-card-admin card-yellow">
                                    <div class="stat-header">
                                        <i class="fab fa-instagram"></i>
                                        <span>Followers</span>
                                    </div>
                                    <input type="text" name="stats_follow" class="input-stat-glass" value="<?php echo htmlspecialchars($stats['followers'] ?? '0'); ?>">
                                </div>

                                <div class="stat-card-admin card-blue">
                                    <div class="stat-header">
                                        <i class="fas fa-shopping-bag"></i>
                                        <span>Orders</span>
                                    </div>
                                    <input type="text" name="stats_order" class="input-stat-glass" value="<?php echo htmlspecialchars($stats['orders'] ?? '0'); ?>">
                                </div>
                                
                            </div>
                        </div>

                        <button type="submit" class="btn-action" style="background: var(--accent); width: 100%; padding: 18px; font-weight: bold; margin-top: 25px;">
                            <i class="fas fa-save me-2"></i> SIMPAN SEMUA BAHASA & JSON
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- About Section Configuration -->
        <div class="glass-card welcome-card" style="margin-top: 50px;">
            <h1>Konfigurasi Section About</h1>
            <p style="font-size: 0.85rem; opacity: 0.8;">Atur informasi profil, foto flip box, dan narasi cerita multi-bahasa.</p>
        </div>

        <div class="glass-card">
            <form action="proses_update_about.php" method="POST" enctype="multipart/form-data">
                <div class="grid-container">
                    
                    <div class="preview-wrapper">
                        <h4 style="font-size: 1rem; border-bottom: 1px solid var(--glass-border); padding-bottom: 10px; margin-bottom: 15px; margin-top: 0px;">
                            <i class="fas fa-sync-alt me-2"></i> Foto Flip Box
                        </h4>

                        <div class="info-label-red">
                            <i class="fas fa-info-circle"></i>
                            <span>Gunakan foto potret dengan rasio yang sama untuk efek flip yang sempurna.</span>
                        </div>
                        
                        <div class="mini-preview">
                            <span class="label-text">Foto Depan (Front)</span>
                            <img src="../assets/imgs/<?php echo $about['img_front']; ?>?v=<?php echo time(); ?>" id="previewFront" onerror="this.src='../assets/imgs/placeholder.png'">
                            
                            <input type="file" name="about_img_front" id="inputFront" style="display:none;" onchange="previewFile(this, 'previewFront')">
                            
                            <div style="display: flex; flex-direction: column; gap: 8px; margin-top: 10px;">
                                <button type="button" class="btn-action btn-action-small" onclick="document.getElementById('inputFront').click()">
                                    <i class="fas fa-edit"></i> Ubah Foto Depan
                                </button>

                                <?php if($about['img_front'] !== $default_front): ?>
                                    <button type="button" class="btn-action-small btn-delete-img" onclick="confirmResetAbout('front')">
                                        <i class="fas fa-undo"></i> Reset Default
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mini-preview">
                            <span class="label-text">Foto Belakang (Back)</span>
                            <img src="../assets/imgs/<?php echo $about['img_back']; ?>?v=<?php echo time(); ?>" id="previewBack" onerror="this.src='../assets/imgs/placeholder.png'">
                            
                            <input type="file" name="about_img_back" id="inputBack" style="display:none;" onchange="previewFile(this, 'previewBack')">
                            
                            <div style="display: flex; flex-direction: column; gap: 8px; margin-top: 10px;">
                                <button type="button" class="btn-action btn-action-small" onclick="document.getElementById('inputBack').click()">
                                    <i class="fas fa-edit"></i> Ubah Foto Belakang
                                </button>

                                <?php if($about['img_back'] !== $default_back): ?>
                                    <button type="button" class="btn-action-small btn-delete-img" onclick="confirmResetAbout('back')">
                                        <i class="fas fa-undo"></i> Reset Default
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h4 style="font-size: 1rem; border-bottom: 1px solid var(--glass-border); padding-bottom: 10px; margin-bottom: 25px; margin-top: 0px;">
                            <i class="fas fa-user-edit me-2"></i> Konten Identitas & Narasi
                        </h4>

                        <div class="form-group">
                            <label class="label-text">NAMA LENGKAP (ID / EN / JP)</label>
                            <div class="input-grid-box">
                                <input type="text" name="about_title_id" class="input-glass" value="<?php echo htmlspecialchars($about['about_title_id'] ?? ''); ?>" placeholder="Indonesia">
                                <input type="text" name="about_title_en" class="input-glass" value="<?php echo htmlspecialchars($about['about_title_en'] ?? ''); ?>" placeholder="English">
                                <input type="text" name="about_title_jp" class="input-glass" value="<?php echo htmlspecialchars($about['about_title_jp'] ?? ''); ?>" placeholder="Japanese">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="label-text">JABATAN / PROFESI (ID / EN / JP)</label>
                            <div class="input-grid-box">
                                <input type="text" name="about_subtitle_id" class="input-glass" value="<?php echo htmlspecialchars($about['about_subtitle_id'] ?? ''); ?>" placeholder="Indonesia">
                                <input type="text" name="about_subtitle_en" class="input-glass" value="<?php echo htmlspecialchars($about['about_subtitle_en'] ?? ''); ?>" placeholder="English">
                                <input type="text" name="about_subtitle_jp" class="input-glass" value="<?php echo htmlspecialchars($about['about_subtitle_jp'] ?? ''); ?>" placeholder="Japanese">
                            </div>
                        </div>

                        <div class="form-group">
                            <h4 style="font-size: 1rem; border-bottom: 1px solid var(--glass-border); padding-bottom: 10px; margin: 35px 0 20px 0; margin-top: 0px;">
                                <i class="fas fa-book-open me-2"></i> Narasi Cerita (Slider Language)
                            </h4>

                            <div class="lang-nav-centered mb-4">
                                <button type="button" class="btn-nav-lang" onclick="moveLangAbout(-1)">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                
                                <div class="lang-indicator-wrapper">
                                    <div id="currentLangLabelAbout" class="lang-text-dynamic">BAHASA INDONESIA</div>
                                    <div class="lang-dots-container">
                                        <div class="dot-lang-about active"></div>
                                        <div class="dot-lang-about"></div>
                                        <div class="dot-lang-about"></div>
                                    </div>
                                </div>

                                <button type="button" class="btn-nav-lang" onclick="moveLangAbout(1)">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                            
                            <div class="lang-slider-container">
                                <div class="lang-wrapper" id="langWrapperAbout">
                                    
                                    <div class="lang-content">
                                        <?php for($i=1; $i<=5; $i++): ?>
                                            <div class="paragraph-group">
                                                <small class="label-text-desc">PARAGRAPH <?php echo $i; ?> (ID)</small>
                                                <textarea name="p<?php echo $i; ?>_id" class="input-glass" rows="2"><?php echo htmlspecialchars($about['p'.$i.'_id'] ?? ''); ?></textarea>
                                            </div>
                                        <?php endfor; ?>
                                    </div>

                                    <div class="lang-content">
                                        <?php for($i=1; $i<=5; $i++): ?>
                                            <div class="paragraph-group">
                                                <small class="label-text-desc">PARAGRAPH <?php echo $i; ?> (EN)</small>
                                                <textarea name="p<?php echo $i; ?>_en" class="input-glass" rows="2"><?php echo htmlspecialchars($about['p'.$i.'_en'] ?? ''); ?></textarea>
                                            </div>
                                        <?php endfor; ?>
                                    </div>

                                    <div class="lang-content">
                                        <?php for($i=1; $i<=5; $i++): ?>
                                            <div class="paragraph-group">
                                                <small class="label-text-desc">PARAGRAPH <?php echo $i; ?> (JP)</small>
                                                <textarea name="p<?php echo $i; ?>_jp" class="input-glass" rows="2"><?php echo htmlspecialchars($about['p'.$i.'_jp'] ?? ''); ?></textarea>
                                            </div>
                                        <?php endfor; ?>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn-action" style="background: var(--accent); width: 100%; padding: 18px; font-weight: bold; margin-top: 25px;">
                            <i class="fas fa-save me-2"></i> SIMPAN PERUBAHAN ABOUT
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Promo Section -->
        <div class="glass-card welcome-card" style="margin-top: 50px;">
            <h1>Konfigurasi Section Promo</h1>
            <p style="font-size: 0.85rem; opacity: 0.8;">Atur konten visual promo, teks penawaran, dan link tujuan eksternal secara dinamis.</p>
        </div>

        <div class="glass-card">
            <form action="proses_update_promo.php" method="POST" enctype="multipart/form-data">
                <div class="grid-container">
                    
                    <div class="preview-wrapper">
                        <h4 style="font-size: 1rem; border-bottom: 1px solid var(--glass-border); padding-bottom: 10px; margin-bottom: 15px; margin-top: 0px;">
                            <i class="fas fa-ad me-2"></i> Media Promo
                        </h4>

                        <div class="info-label-red">
                            <i class="fas fa-info-circle"></i>
                            <span>Disarankan menggunakan gambar potret. Jika direset, akan menggunakan file default sistem.</span>
                        </div>
                        
                        <div class="mini-preview">
                            <span class="label-text">Gambar Utama (Primary)</span>
                            <img src="../assets/imgs/<?php echo !empty($pr['img_primary']) ? $pr['img_primary'] : 'promo-1.jpg'; ?>?v=<?php echo time(); ?>" id="previewPromo1" onerror="this.src='../assets/imgs/placeholder.png'">
                            
                            <input type="file" name="img_primary" id="inputPromo1" style="display:none;" onchange="previewFile(this, 'previewPromo1')">
                            
                            <div style="display: flex; flex-direction: column; gap: 8px; margin-top: 10px;">
                                <button type="button" class="btn-action btn-action-small" onclick="document.getElementById('inputPromo1').click()">
                                    <i class="fas fa-edit"></i> Ubah Gambar 1
                                </button>

                                <?php if(!empty($pr['img_primary'])): ?>
                                    <button type="button" class="btn-action-small btn-delete-img" onclick="confirmResetPromo('primary')">
                                        <i class="fas fa-undo"></i> Reset Default
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mini-preview">
                            <span class="label-text">Gambar Sekunder (Secondary)</span>
                            <img src="../assets/imgs/<?php echo !empty($pr['img_secondary']) ? $pr['img_secondary'] : 'promo-2.jpg'; ?>?v=<?php echo time(); ?>" id="previewPromo2" onerror="this.src='../assets/imgs/placeholder.png'">
                            
                            <input type="file" name="img_secondary" id="inputPromo2" style="display:none;" onchange="previewFile(this, 'previewPromo2')">
                            
                            <div style="display: flex; flex-direction: column; gap: 8px; margin-top: 10px;">
                                <button type="button" class="btn-action btn-action-small" onclick="document.getElementById('inputPromo2').click()">
                                    <i class="fas fa-edit"></i> Ubah Gambar 2
                                </button>

                                <?php if(!empty($pr['img_secondary'])): ?>
                                    <button type="button" class="btn-action-small btn-delete-img" onclick="confirmResetPromo('secondary')">
                                        <i class="fas fa-undo"></i> Reset Default
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h4 style="font-size: 1rem; border-bottom: 1px solid var(--glass-border); padding-bottom: 10px; margin-bottom: 25px; margin-top: 0px;">
                            <i class="fas fa-i-cursor me-2"></i> Detail Konten & Link
                        </h4>

                        <div class="form-group">
                            <label class="label-text">URL TUJUAN (INSTAGRAM / REELS LINK)</label>
                            <input type="text" name="btn_url" class="input-glass" value="<?php echo htmlspecialchars($pr['btn_url'] ?? ''); ?>" placeholder="https://instagram.com/reel/...">
                        </div>

                        <div class="form-group">
                            <label class="label-text">JUDUL PROMO (ID / EN / JP)</label>
                            <div class="input-grid-box">
                                <input type="text" name="title_id" class="input-glass" value="<?php echo htmlspecialchars($pr['title_id'] ?? ''); ?>" placeholder="Indonesia">
                                <input type="text" name="title_en" class="input-glass" value="<?php echo htmlspecialchars($pr['title_en'] ?? ''); ?>" placeholder="English">
                                <input type="text" name="title_jp" class="input-glass" value="<?php echo htmlspecialchars($pr['title_jp'] ?? ''); ?>" placeholder="Japanese">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="label-text">CAPTION PROMO (ID / EN / JP)</label>
                            <div class="input-stack-box">
                                <textarea name="caption_id" class="input-glass" rows="2" placeholder="Caption Indonesia"><?php echo htmlspecialchars($pr['caption_id'] ?? ''); ?></textarea>
                                <textarea name="caption_en" class="input-glass" rows="2" placeholder="Caption English"><?php echo htmlspecialchars($pr['caption_en'] ?? ''); ?></textarea>
                                <textarea name="caption_jp" class="input-glass" rows="2" placeholder="Caption Japanese"><?php echo htmlspecialchars($pr['caption_jp'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="label-text">TEKS TOMBOL (ID / EN / JP)</label>
                            <div class="input-grid-box">
                                <input type="text" name="btn_text_id" class="input-glass" value="<?php echo htmlspecialchars($pr['btn_text_id'] ?? ''); ?>" placeholder="Indonesia">
                                <input type="text" name="btn_text_en" class="input-glass" value="<?php echo htmlspecialchars($pr['btn_text_en'] ?? ''); ?>" placeholder="English">
                                <input type="text" name="btn_text_jp" class="input-glass" value="<?php echo htmlspecialchars($pr['btn_text_jp'] ?? ''); ?>" placeholder="Japanese">
                            </div>
                        </div>

                        <button type="submit" class="btn-action" style="background: var(--accent); width: 100%; padding: 18px; font-weight: bold; margin-top: 25px;">
                            <i class="fas fa-save me-2"></i> SIMPAN & SINKRONISASI PROMO
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Skills Section -->
        <div class="glass-card welcome-card" style="margin-top: 50px;">
            <h1>Konfigurasi Section Skills</h1>
            <p style="font-size: 0.85rem; opacity: 0.8;">Kelola daftar keahlian, tingkat persentase, dan narasi pengantar keahlian.</p>
        </div>

        <div class="glass-card">
            <form action="proses_update_skills.php" method="POST">
                <div class="full-width-container" style="width: 100%;">
                    
                    <h4 style="font-size: 1rem; border-bottom: 1px solid var(--glass-border); padding-bottom: 10px; margin-bottom: 25px; margin-top: 0px;">
                        <i class="fas fa-tasks me-2"></i> Daftar Keahlian & Progres
                    </h4>

                    <div id="skills-list-container">
                        <?php 
                        $q_skills = mysqli_query($conn, "SELECT * FROM site_skills ORDER BY order_index ASC");
                        while($s = mysqli_fetch_assoc($q_skills)): 
                        ?>
                        <div class="skill-row-item" style="background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); padding: 20px; border-radius: 12px; margin-bottom: 20px; position: relative; width: 100%; box-sizing: border-box;">
                            <input type="hidden" name="skill_ids[]" value="<?php echo $s['id']; ?>">
                            
                            <div style="display: grid; grid-template-columns: 2fr 2fr 2fr 100px; gap: 20px; width: 100%;">
                                <div>
                                    <small class="label-text-desc" style="font-size: 0.65rem; margin-bottom: 8px;">BAHASA INDONESIA</small>
                                    <input type="text" name="skill_name_id[]" class="input-glass" value="<?php echo htmlspecialchars($s['skill_name_id']); ?>" style="width: 100%;">
                                </div>
                                <div>
                                    <small class="label-text-desc" style="font-size: 0.65rem; margin-bottom: 8px;">ENGLISH VERSION</small>
                                    <input type="text" name="skill_name_en[]" class="input-glass" value="<?php echo htmlspecialchars($s['skill_name_en']); ?>" style="width: 100%;">
                                </div>
                                <div>
                                    <small class="label-text-desc" style="font-size: 0.65rem; margin-bottom: 8px;">JAPANESE VERSION</small>
                                    <input type="text" name="skill_name_jp[]" class="input-glass" value="<?php echo htmlspecialchars($s['skill_name_jp']); ?>" style="width: 100%;">
                                </div>
                                <div>
                                    <small class="label-text-desc" style="font-size: 0.65rem; margin-bottom: 8px;">PROG.</small>
                                    <input type="number" name="percentage[]" class="input-glass" value="<?php echo $s['percentage']; ?>" min="0" max="100" style="text-align: center; font-weight: bold;">
                                </div>
                            </div>

                            <button type="button" class="btn-delete-img" style="position: absolute; top: -10px; right: -10px; width: 28px; height: 28px; padding: 0 !important; border-radius: 50%; display: flex; align-items: center; justify-content: center;" onclick="confirmDeleteSkill(<?php echo $s['id']; ?>)">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <?php endwhile; ?>
                    </div>

                    <div style="border: 2px dashed var(--glass-border); padding: 25px; border-radius: 12px; margin-top: 10px; width: 100%; box-sizing: border-box;">
                        <label class="label-text" style="color: var(--accent); margin-bottom: 20px; display: block; font-weight: 800;">+ TAMBAH SKILL BARU</label>
                        <div style="display: grid; grid-template-columns: 2fr 2fr 2fr 100px; gap: 20px; width: 100%;">
                            <input type="text" name="new_skill_id" class="input-glass" placeholder="Nama (ID)">
                            <input type="text" name="new_skill_en" class="input-glass" placeholder="Name (EN)">
                            <input type="text" name="new_skill_jp" class="input-glass" placeholder="名前 (JP)">
                            <input type="number" name="new_percentage" class="input-glass" placeholder="%" style="text-align: center;">
                        </div>
                    </div>

                    <button type="submit" class="btn-action" style="background: var(--accent); width: 100%; padding: 18px; font-weight: bold; margin-top: 30px; letter-spacing: 1px;">
                        <i class="fas fa-save me-2"></i> SIMPAN SEMUA PERUBAHAN SKILL
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Konfigurasi Info -->
        <div class="glass-card welcome-card">
            <h1>Konfigurasi Pesan Info Portfolio</h1>
            <p style="font-size: 0.85rem; opacity: 0.8;">Atur pesan promosi atau pemberitahuan penting yang akan tampil di atas galeri.</p>
        </div>

        <div class="glass-card mb-5">
            <h4 style="font-size: 1rem; border-bottom: 1px solid var(--glass-border); padding-bottom: 15px; margin-bottom: 10px; margin-top: 0px;">
                <i class="fas fa-plus-circle me-2"></i> Tambah Info Baru
            </h4>
            <form action="proses_update_portfolio_info.php" method="POST">
                <input type="hidden" name="action" value="add_info">
                
                <div class="input-stack-box">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <label class="label-text-desc mb-3">PESAN UTAMA (ID)</label>
                            <input type="text" name="text_id" class="input-glass" placeholder="Contoh: Punya rencana desain?" required>
                        </div>
                        <div class="col-md-4">
                            <label class="label-text-desc mb-3">MAIN MESSAGE (EN)</label>
                            <input type="text" name="text_en" class="input-glass" placeholder="Example: Have a design plan?">
                        </div>
                        <div class="col-md-4">
                            <label class="label-text-desc mb-3">メインメッセージ (JP)</label>
                            <input type="text" name="text_jp" class="input-glass" placeholder="例：デザイン案はありますか？">
                        </div>
                    </div>

                    <div class="row g-4 py-4"> <div class="col-md-4">
                            <label class="label-text-desc mb-3">TEKS LINK (ID)</label>
                            <input type="text" name="link_text_id" class="input-glass" placeholder="Contoh: Chat sekarang!">
                        </div>
                        <div class="col-md-4">
                            <label class="label-text-desc mb-3">LINK TEXT (EN)</label>
                            <input type="text" name="link_text_en" class="input-glass" placeholder="Example: Chat now!">
                        </div>
                        <div class="col-md-4">
                            <label class="label-text-desc mb-3">リンクテキスト (JP)</label>
                            <input type="text" name="link_text_jp" class="input-glass" placeholder="例：今すぐチャット！">
                        </div>
                    </div>

                    <div class="row g-4">
                        <div class="col-12">
                            <label class="label-text-desc mb-3">URL TUJUAN (WHATSAPP / EXTERNAL LINK)</label>
                            <input type="url" name="link_url" class="input-glass" placeholder="https://wa.me/62895...">
                        </div>
                    </div>
                </div>

                <div class="mt-5">
                    <button type="submit" class="btn-action" style="background: var(--accent); width: 100%; padding: 18px; margin-top: 20px;">
                        <i class="fas fa-upload me-2"></i> UNGGAH INFO BARU
                    </button>
                </div>
            </form>
        </div>

        <div class="glass-card">
            <h4 style="font-size: 1rem; border-bottom: 1px solid var(--glass-border); padding-bottom: 15px; margin-bottom: 30px; margin-top: 0px;">
                <i class="fas fa-tasks me-2"></i> Kelola Info Aktif
            </h4>
            
            <form action="proses_update_portfolio_info.php" method="POST">
                <input type="hidden" name="action" value="update_info">
                
                <div id="info-list-container">
                    <?php 
                    $q_list_info = mysqli_query($conn, "SELECT * FROM site_portfolio_alerts ORDER BY id DESC");
                    while($ai = mysqli_fetch_assoc($q_list_info)):
                    ?>
                    <div class="info-manage-item">
                        <input type="hidden" name="info_ids[]" value="<?php echo $ai['id']; ?>">
                        
                        <!-- Grup Kontrol Kanan Atas Glassmorphic -->
                        <div class="info-item-controls">
                            <!-- Checkbox Kustom (Status Aktif) -->
                            <label class="custom-check-container">
                                <input type="checkbox" name="is_active[]" class="custom-check-input" <?php echo $ai['is_active'] ? 'checked' : ''; ?>>
                                <span class="checkmark">
                                    <i class="fas fa-check"></i>
                                </span>
                            </label>

                            <!-- Tombol Hapus -->
                            <button type="button" class="btn-delete-info-alt glass-control" onclick="confirmDeleteInfo(<?php echo $ai['id']; ?>)">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <!-- Baris 1: Pesan -->
                        <div class="row g-4">
                            <div class="col-md-4">
                                <label class="label-text-desc mb-2">PESAN (ID)</label>
                                <input type="text" name="text_id[]" class="input-glass" value="<?php echo htmlspecialchars($ai['text_id']); ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="label-text-desc mb-2">MESSAGE (EN)</label>
                                <input type="text" name="text_en[]" class="input-glass" value="<?php echo htmlspecialchars($ai['text_en']); ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="label-text-desc mb-2">メッセージ (JP)</label>
                                <input type="text" name="text_jp[]" class="input-glass" value="<?php echo htmlspecialchars($ai['text_jp']); ?>">
                            </div>
                        </div>

                        <!-- Baris 2: Link -->
                        <div class="row g-4 py-3"> <!-- Dipersempit dari py-4 ke py-3 -->
                            <div class="col-md-4">
                                <label class="label-text-desc mb-2">LINK (ID)</label>
                                <input type="text" name="link_text_id[]" class="input-glass" value="<?php echo htmlspecialchars($ai['link_text_id']); ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="label-text-desc mb-2">LINK (EN)</label>
                                <input type="text" name="link_text_en[]" class="input-glass" value="<?php echo htmlspecialchars($ai['link_text_en']); ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="label-text-desc mb-2">LINK (JP)</label>
                                <input type="text" name="link_text_jp[]" class="input-glass" value="<?php echo htmlspecialchars($ai['link_text_jp']); ?>">
                            </div>
                        </div>

                        <!-- Baris 3: URL -->
                        <div class="row g-4">
                            <div class="col-12">
                                <label class="label-text-desc mb-2">URL LINK</label>
                                <input type="url" name="link_url[]" class="input-glass" value="<?php echo htmlspecialchars($ai['link_url']); ?>">
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn-action" style="background: var(--accent); width: 100%; padding: 18px;">
                        <i class="fas fa-save me-2"></i> SIMPAN PERUBAHAN INFO
                    </button>
                </div>
            </form>
        </div>

        <!-- Portfolio Section -->
        <?php include 'sections/section_karya.php'; ?>

        <!-- Portfolio Video Section --> 

        <?php include 'sections/section_video.php'; ?>     
    </div>

    </main>

    <script src="admin_script.js"></script>
    <script>
        // Fungsi untuk preview gambar secara realtime saat upload
        function previewFile(input, previewId) {
            const file = input.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = e => document.getElementById(previewId).src = e.target.result;
                reader.readAsDataURL(file);
            }
        }

        // Fungsi Konfirmasi Reset untuk Gambar Slideshow (Home)
        function confirmReset(id) {
            Swal.fire({
                title: 'Reset Gambar?',
                text: "Kembalikan ke gambar default NaufaRu?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4c4d',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Reset!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'proses_hapus_gambar.php?id=' + id;
                }
            })
        }

        // Fungsi Konfirmasi Reset untuk Gambar Flip Box (About)
        function confirmResetAbout(type) {
            Swal.fire({
                title: 'Reset Foto About?',
                text: `Kembalikan foto ${type === 'front' ? 'depan' : 'belakang'} ke default?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4c4d',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Reset!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'proses_update_about.php?reset=' + type;
                }
            })
        }

        // Logika Notifikasi Saat Halaman Dimuat
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            
            if (urlParams.has('status')) {
                const status = urlParams.get('status');
                
                let config = {
                    timer: 3000,
                    showConfirmButton: false,
                    timerProgressBar: true,
                    confirmButtonColor: '#ef4c4d' 
                };

                // Tambahkan status baru ke dalam array successStatuses
                const successStatuses = [
                    'success', 'success_about', 'success_promo', 'success_skill', 
                    'success_portfolio', 'success_delete_portfolio', 
                    'success_info', 'success_info_delete', 'success_grid'
                ]; 

                if (successStatuses.includes(status)) {
                    config.icon = 'success'; 
                    config.title = 'Berhasil!';
                    
                    switch (status) {
                        case 'success': config.text = 'Konfigurasi Home diperbarui.'; break;
                        case 'success_about': config.text = 'Konfigurasi About diperbarui.'; break;
                        case 'success_promo': config.text = 'Konfigurasi Promo disinkronkan.'; break;
                        case 'success_skill': config.text = 'Daftar keahlian diperbarui.'; break;
                        case 'success_portfolio': 
                            config.text = 'Katalog karya berhasil diunggah ke galeri.'; 
                            break;
                        case 'success_delete_portfolio': 
                            config.text = 'Katalog karya telah berhasil dihapus.'; 
                            break;
                        // Pesan Khusus Alert Info & Grid
                        case 'success_info': 
                            config.text = 'Pengaturan pesan info berhasil disimpan.'; 
                            break;
                        case 'success_info_delete': 
                            config.text = 'Pesan info telah dihapus dari sistem.'; 
                            break;
                        case 'success_grid': 
                            config.text = 'Tampilan grid portfolio berhasil diubah.'; 
                            break;
                    }
                } else {
                    config.icon = 'error'; 
                    config.title = 'Gagal!'; 
                    config.text = 'Terjadi kesalahan sistem atau parameter tidak dikenali.'; 
                    config.showConfirmButton = true; 
                    config.timer = null; 
                }

                Swal.fire(config).then(() => {
                    // Bersihkan URL dari parameter status agar notifikasi tidak muncul lagi saat refresh
                    window.history.replaceState({}, document.title, window.location.pathname);
                });
            }
        });

        // Promo Section
        function confirmResetPromo(type) {
            Swal.fire({
                title: 'Reset Gambar Promo?',
                text: `Kembalikan gambar ${type === 'primary' ? 'utama' : 'sekunder'} ke default?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4c4d',
                confirmButtonText: 'Ya, Reset!',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'proses_update_promo.php?reset=' + type;
                }
            })
        }

        // Skills Section
        function confirmDeleteSkill(id) {
            Swal.fire({
                title: 'Hapus Keahlian?',
                text: "Data keahlian ini akan dihapus permanen dari database.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4c4d',
                confirmButtonText: 'Ya, Hapus!',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'proses_update_skills.php?delete_id=' + id;
                }
            })
        }
    </script> 
</body>
</html>