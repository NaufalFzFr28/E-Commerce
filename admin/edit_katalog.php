<?php
session_start();
include 'cek_login.php'; 
include '../config.php';

if (!isset($_GET['id'])) { header("Location: admin_katalog.php"); exit(); }
$id = mysqli_real_escape_string($conn, $_GET['id']);
$query = $conn->query("SELECT * FROM site_products_promo WHERE id = '$id'");
$data = $query->fetch_assoc();
if (!$data) { header("Location: admin_katalog.php"); exit(); }

// Menghilangkan ",00" atau desimal dari nilai harga untuk tampilan input
$clean_price = (int)$data['price']; 

// Ambil jumlah pesanan dengan status 'Pending' untuk notifikasi di sidebar
$q_pending = mysqli_query($conn, "SELECT COUNT(*) as total FROM orders WHERE status = 'Pending'");
$pending_data = mysqli_fetch_assoc($q_pending);
$total_pending = $pending_data['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>NaufaRu Admin | Edit Katalog</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="admin_style.css">
    <style>
        /* SINKRONISASI TOTAL: Menyamakan ukuran dengan halaman konfigurasi lain */
        .main-content {
            padding: 30px 40px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .edit-container { 
            width: 100%; 
            margin: 0; 
            padding: 0;
        }

        /* GRID LAYOUT: 1fr 2fr (320px vs Sisa Ruang) */
        .form-grid-edit { 
            display: grid; 
            grid-template-columns: 320px 1fr; 
            gap: 35px; 
            margin-top: 25px; 
        }

        /* TEXTBOX PREMIUM */
        .input-glass-premium { 
            box-sizing: border-box;
            width: 100%; 
            background: rgba(255, 255, 255, 0.05); 
            border: 1px solid var(--glass-border); 
            border-radius: 10px; 
            padding: 12px 15px; 
            color: white; 
            outline: none; 
            transition: 0.3s;
            margin-top: 10px; 
        }
        
        .input-glass-premium:focus { 
            border-color: var(--accent); 
            background: rgba(255, 255, 255, 0.1); 
        }

        .label-premium { 
            font-size: 0.75rem; 
            opacity: 0.6; 
            text-transform: uppercase; 
            display: block; 
            letter-spacing: 1.5px;
            font-weight: 600;
            padding-top: 10px;
        }

        .preview-wrapper {
            background: rgba(255,255,255,0.02);
            padding: 20px;
            border-radius: 15px;
            border: 1px solid var(--glass-border);
        }

        .img-edit-preview {
            width: 100%;
            aspect-ratio: 1/1;
            object-fit: cover;
            border-radius: 12px;
            margin: 15px 0;
            border: 1px solid rgba(255,255,255,0.1);
        }

        /* FOOTER ACTION */
        .action-footer {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 30px;
        }

        .btn-back-katalog {
            background: rgba(255, 255, 255, 0.05);
            color: white;
            padding: 15px 25px;
            border-radius: 12px;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            transition: 0.3s;
            border: 1px solid var(--glass-border);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-back-katalog:hover {
            background: rgba(255, 255, 255, 0.1);
            color: var(--accent);
        }

        /* Menu Dropdown Kustom */
        .custom-select-wrapper {
            position: relative;
            user-select: none;
            width: 100%;
            margin-top: 10px;
        }

        .custom-select-trigger {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 15px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--glass-border);
            backdrop-filter: blur(10px);
            border-radius: 10px;
            color: white;
            cursor: pointer;
            transition: 0.3s;
            margin-bottom: 20px;
        }

        .custom-options {
            position: absolute;
            top: calc(100% + 5px);
            left: 0;
            right: 0;
            background: rgba(20, 20, 20, 0.9) !important;
            backdrop-filter: blur(20px) !important;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 8px;
            display: none;
            z-index: 1000;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
            max-height: 250px;
            overflow-y: auto;
        }

        .custom-option {
            padding: 10px 15px;
            color: rgba(255, 255, 255, 0.7);
            cursor: pointer;
            border-radius: 8px;
            transition: 0.2s;
            font-size: 0.9rem;
        }

        .custom-option:hover { background: rgba(255, 255, 255, 0.05); color: white; }
        .custom-option.selected { background: var(--accent) !important; color: white !important; }

        .custom-select-arrow { font-size: 0.8rem; transition: 0.3s; opacity: 0.5; }
        .custom-select-wrapper.open .custom-select-arrow { transform: rotate(180deg); opacity: 1; }

        /* MULTILANGUAGE TABS STYLING */
        .lang-tabs-edit {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            padding: 5px;
            background: rgba(255,255,255,0.03);
            border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.05);
        }
        .lang-btn {
            flex: 1;
            padding: 10px;
            border: none;
            background: transparent;
            color: rgba(255,255,255,0.5);
            cursor: pointer;
            font-weight: 600;
            font-size: 0.75rem;
            border-radius: 8px;
            transition: 0.3s;
            letter-spacing: 1px;
        }
        .lang-btn.active {
            background: var(--accent);
            color: white;
            box-shadow: 0 4px 12px rgba(239, 76, 77, 0.3);
        }

        @media (max-width: 992px) { 
            .form-grid-edit { grid-template-columns: 1fr; } 
        }
    </style>
</head>
<body class="dark-mode">

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="../assets/imgs/logo-white.png" alt="Logo" class="sidebar-logo">
            <button class="toggle-btn" id="toggleBtn"><i class="fas fa-angle-left" id="toggleIcon"></i></button>
        </div>
        <nav>
            <a href="admin_dashboard.php" class="nav-link"><i class="fas fa-th-large"></i> <span class="scramble-text" data-value="Dashboard">Dashboard</span></a>
            <a href="main_website.php" class="nav-link"><i class="fas fa-globe"></i> <span class="scramble-text" data-value="Website Utama">Website Utama</span></a>
            <a href="#" class="nav-link"><i class="fas fa-file-alt"></i> <span class="scramble-text" data-value="Curriculum Vitae">Curriculum Vitae</span></a>
            <a href="#" class="nav-link"><i class="fas fa-calendar-check"></i> <span class="scramble-text" data-value="Event Site">Event Site</span></a>
            <a href="admin_katalog.php" class="nav-link active"><i class="fas fa-boxes"></i> <span class="scramble-text" data-value="Admin Katalog">Admin Katalog</span></a>
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
        <div class="glass-card welcome-card">
            <h1>Edit Detail Produk</h1>
            <p style="font-size: 0.85rem; opacity: 0.8;">Perbarui informasi katalog layanan Anda dengan dukungan multibahasa.</p>
        </div>

        <div class="glass-card">
            <form action="proses_update_katalog.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= $data['id'] ?>">
                
                <div class="form-grid-edit">
                    <!-- SISI KIRI: MEDIA -->
                    <div class="preview-wrapper">
                        <h4 style="font-size: 1rem; border-bottom: 1px solid var(--glass-border); padding-bottom: 15px; margin-bottom: 5px;">
                            <i class="fas fa-image me-2"></i> Media Produk
                        </h4>

                        <div class="text-center">
                            <span class="label-premium" style="margin-top: 20px;">GAMBAR SAAT INI</span>
                            <img src="../../assets/imgs/img-catalog/<?= $data['gambar_produk'] ?>" id="imgPrev" class="img-edit-preview" onerror="this.src='../../assets/imgs/placeholder.png'">
                            
                            <input type="file" name="image" id="fileInp" style="display:none;" onchange="previewFile()">
                            <button type="button" class="btn-action" style="width: 100%;" onclick="document.getElementById('fileInp').click()">
                                <i class="fas fa-sync-alt me-2"></i> Ubah Gambar
                            </button>
                        </div>
                    </div>

                    <!-- SISI KANAN: FORM -->
                    <div>
                        <h4 style="font-size: 1rem; border-bottom: 1px solid var(--glass-border); padding-bottom: 15px; margin-bottom: 20px;">
                            <i class="fas fa-edit me-2"></i> Detail Informasi
                        </h4>

                        <!-- Global Input: Harga & Kategori -->
                        <div class="form-group mb-4">
                            <label class="label-premium">HARGA JUAL (RP)</label>
                            <input type="number" name="price" class="input-glass-premium" value="<?= $clean_price ?>" required>
                        </div>

                        <div class="form-group mb-4">
                            <label class="label-premium">KATEGORI LAYANAN</label>
                            <div class="custom-select-wrapper" id="editCatSelect">
                                <input type="hidden" name="category" id="catInput" value="<?= $data['kategori'] ?>">
                                <div class="custom-select-trigger" id="catTrigger">
                                    <span id="catLabel"><?= $data['kategori'] ?></span>
                                    <i class="fas fa-chevron-down custom-select-arrow"></i>
                                </div>
                                <div class="custom-options" id="catList">
                                    <?php 
                                    $q_cat = mysqli_query($conn, "SELECT product_name_id FROM site_products");
                                    while($cat = mysqli_fetch_assoc($q_cat)):
                                        $isSelected = ($data['kategori'] == $cat['product_name_id']) ? 'selected' : '';
                                    ?>
                                        <div class="custom-option <?= $isSelected ?>" data-value="<?= $cat['product_name_id'] ?>">
                                            <?= $cat['product_name_id'] ?>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            </div>
                        </div>

                        <!-- MULTILANGUAGE SECTION -->
                        <div class="lang-tabs-edit">
                            <button type="button" onclick="switchEditLang('id')" class="lang-btn active" id="tab-id">INDONESIA</button>
                            <button type="button" onclick="switchEditLang('en')" class="lang-btn" id="tab-en">ENGLISH</button>
                            <button type="button" onclick="switchEditLang('jp')" class="lang-btn" id="tab-jp">JAPANESE</button>
                        </div>

                        <!-- ID CONTENT -->
                        <div id="edit-id" class="lang-content-edit">
                            <div class="form-group mb-4">
                                <label class="label-premium">NAMA PRODUK (ID)</label>
                                <input type="text" name="product_name" class="input-glass-premium" value="<?= htmlspecialchars($data['product_name']) ?>" required>
                            </div>
                            <div class="form-group mb-4">
                                <label class="label-premium">DESKRIPSI (ID)</label>
                                <textarea name="description" class="input-glass-premium" rows="5"><?= htmlspecialchars($data['deskripsi']) ?></textarea>
                            </div>
                        </div>

                        <!-- SECTION ENGLISH -->
<div id="edit-en" class="lang-content-edit" style="display:none;">
    <div class="form-group mb-4">
        <label class="label-premium">PRODUCT NAME (EN)</label>
        <input type="text" name="product_en" class="input-glass-premium" value="<?= htmlspecialchars($data['product_en'] ?? '') ?>" placeholder="English product name...">
    </div>
    <div class="form-group mb-4">
        <label class="label-premium">DESCRIPTION (EN)</label>
        <!-- Perhatikan: ganti description_en menjadi deskripsi_en -->
        <textarea name="description_en" class="input-glass-premium" rows="5" placeholder="English description..."><?= htmlspecialchars($data['deskripsi_en'] ?? '') ?></textarea>
    </div>
    <input type="hidden" name="kategori_en" value="<?= htmlspecialchars($data['kategori_en'] ?? '') ?>">
</div>

<!-- SECTION JAPANESE -->
<div id="edit-jp" class="lang-content-edit" style="display:none;">
    <div class="form-group mb-4">
        <label class="label-premium">製品名 (JP)</label>
        <input type="text" name="product_jp" class="input-glass-premium" value="<?= htmlspecialchars($data['product_jp'] ?? '') ?>" placeholder="Japanese product name...">
    </div>
    <div class="form-group mb-4">
        <label class="label-premium">説明 (JP)</label>
        <!-- Perhatikan: ganti description_jp menjadi deskripsi_jp -->
        <textarea name="description_jp" class="input-glass-premium" rows="5" placeholder="Japanese description..."><?= htmlspecialchars($data['deskripsi_jp'] ?? '') ?></textarea>
    </div>
    <input type="hidden" name="kategori_jp" value="<?= htmlspecialchars($data['kategori_jp'] ?? '') ?>">
</div>

                        <!-- FOOTER ACTION -->
                        <div class="action-footer">
                            <a href="admin_katalog.php" class="btn-back-katalog">
                                <i class="fas fa-arrow-left"></i> KEMBALI
                            </a>
                            <button type="submit" name="submit_update" class="btn-action" style="flex: 1; padding: 18px; font-weight: bold;">
                                <i class="fas fa-save me-2"></i> SIMPAN PERUBAHAN DATA
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </main>

    <script src="admin_script.js"></script>
    <script>
        function previewFile() {
            const preview = document.getElementById('imgPrev');
            const file = document.getElementById('fileInp').files[0];
            const reader = new FileReader();
            reader.onloadend = function() { preview.src = reader.result; }
            if (file) { reader.readAsDataURL(file); }
        }

        // Logic Switch Language Tabs
        function switchEditLang(lang) {
            // Sembunyikan semua section konten bahasa
            document.querySelectorAll('.lang-content-edit').forEach(el => el.style.display = 'none');
            // Reset state tombol tab
            document.querySelectorAll('.lang-btn').forEach(btn => btn.classList.remove('active'));
            
            // Tampilkan section yang dipilih
            document.getElementById('edit-' + lang).style.display = 'block';
            document.getElementById('tab-' + lang).classList.add('active');
        }

        // Logic Custom Dropdown
        document.addEventListener('DOMContentLoaded', function() {
            const wrapper = document.getElementById('editCatSelect');
            const trigger = document.getElementById('catTrigger');
            const options = document.querySelectorAll('.custom-option');
            const hiddenInput = document.getElementById('catInput');
            const label = document.getElementById('catLabel');
            const list = document.getElementById('catList');

            trigger.addEventListener('click', function(e) {
                e.stopPropagation();
                wrapper.classList.toggle('open');
                list.style.display = wrapper.classList.contains('open') ? 'block' : 'none';
            });

            options.forEach(opt => {
                opt.addEventListener('click', function() {
                    const val = this.getAttribute('data-value');
                    hiddenInput.value = val;
                    label.innerText = val;

                    options.forEach(o => o.classList.remove('selected'));
                    this.classList.add('selected');

                    wrapper.classList.remove('open');
                    list.style.display = 'none';
                });
            });

            window.addEventListener('click', function() {
                wrapper.classList.remove('open');
                list.style.display = 'none';
            });
        });
    </script>
</body>
</html>