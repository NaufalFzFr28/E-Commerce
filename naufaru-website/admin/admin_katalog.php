<?php
session_start();
include 'cek_login.php'; 
include '../config.php';
include '../functions.php';

// Logika Hapus Produk
if (isset($_GET['delete'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete']);
    
    // 1. Ambil nama file gambar menggunakan nama kolom yang benar: 'gambar_produk'
    $res = $conn->query("SELECT gambar_produk FROM site_products_promo WHERE id = $id");
    
    if ($res && $res->num_rows > 0) {
        $data = $res->fetch_assoc();
        $nama_file = $data['gambar_produk'];
        
        // 2. Hapus file fisik dari folder assets jika ada
        if (!empty($nama_file)) {
            $file_path = "../../assets/imgs/img-catalog/" . $nama_file;
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
    }

    // 3. Hapus data dari database
    $conn->query("DELETE FROM site_products_promo WHERE id = $id");
    
    header("Location: admin_katalog.php?status=success_delete");
    exit();
}

// Ambil jumlah pesanan dengan status 'Pending' untuk notifikasi di sidebar
$q_pending = mysqli_query($conn, "SELECT COUNT(*) as total FROM orders WHERE status = 'Pending'");
$pending_data = mysqli_fetch_assoc($q_pending);
$total_pending = $pending_data['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>NaufaRu Admin | Admin Katalog</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="admin_style.css">
    <style>
        /* Buat scrollbar kalau hilang otomatis saat membuka konten */
        html {
            overflow-y: scroll; /* Memastikan scrollbar selalu muncul sebagai track statis */
        }

        .img-preview { width: 60px; height: 60px; object-fit: cover; border-radius: 12px; border: 1px solid var(--glass-border); }
        .table-glass { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .table-glass th { background: rgba(239, 76, 77, 0.1); padding: 15px; text-align: left; font-size: 0.75rem; letter-spacing: 1px; color: var(--accent); }
        .table-glass td { padding: 15px; border-bottom: 1px solid var(--glass-border); font-size: 0.9rem; color: white; }

        /* --- Animasi & Style Popup Baru --- */
        @keyframes zoomInBounce {
            0% { transform: scale(0.8); opacity: 0; }
            70% { transform: scale(1.05); opacity: 1; }
            100% { transform: scale(1); opacity: 1; }
        }

        @keyframes fadeOutZoom {
            from { transform: scale(1); opacity: 1; }
            to { transform: scale(0.9); opacity: 0; }
        }

        /* --- Perbaikan Style Popup NaufaRu --- */
        .modal-overlay .glass-card {
            width: 500px;
            padding: 40px;
            border-radius: 25px;
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
        }

        /* Header Modal */
        .modal-header-naufaru {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 30px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            padding-bottom: 15px;
        }

        /* Pengaturan Baris Form */
        .form-group-row {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            gap: 15px;
        }

        .form-group-row label {
            flex: 0 0 140px; /* Lebar label tetap agar textbox sejajar vertikal */
            font-size: 0.75rem;
            font-weight: 700;
            color: rgba(255,255,255,0.6);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Style Khusus Input File */
        .input-file-wrapper {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        input[type="file"]::file-selector-button {
            background: var(--accent);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.75rem;
            font-weight: 600;
            transition: 0.3s;
        }

        input[type="file"]::file-selector-button:hover {
            background: #d43d3e;
        }

        /* --- Penambahan Style Dropdown Glassmorphic --- */
        .custom-select-wrapper {
            position: relative;
            flex: 1;
            user-select: none;
        }

        .custom-select-trigger {
            background: rgba(255, 255, 255, 0.03) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            border-radius: 10px;
            padding: 12px 15px;
            color: white;
            font-size: 0.9rem;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: 0.3s;
        }

        .custom-select-wrapper.open .custom-select-trigger {
            border-color: var(--accent) !important;
            background: rgba(239, 76, 77, 0.05) !important;
        }

        .custom-options {
            position: absolute;
            top: calc(100% + 8px);
            left: 0;
            right: 0;
            background: rgba(20, 20, 20, 0.9) !important;
            backdrop-filter: blur(15px) !important;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 10px;
            display: none; /* Dikontrol JS */
            z-index: 11000;
            box-shadow: 0 15px 35px rgba(0,0,0,0.5);
            max-height: 220px;
            overflow-y: auto;
        }

        .custom-option {
            padding: 10px 15px;
            color: rgba(255,255,255,0.7);
            font-size: 0.85rem;
            cursor: pointer;
            border-radius: 8px;
            transition: 0.2s;
        }

        .custom-option:hover { background: rgba(255, 255, 255, 0.05); color: white; }
        .custom-option.selected { background: var(--accent) !important; color: white !important; }

        .custom-select-arrow { font-size: 0.7rem; transition: 0.3s; opacity: 0.5; }
        .custom-select-wrapper.open .custom-select-arrow { transform: rotate(180deg); opacity: 1; }

        /* Scrollbar Dropdown */
        .custom-options::-webkit-scrollbar { width: 5px; }
        .custom-options::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }

        /* Overlay Modal Fix */
        .modal-overlay.active { display: flex; opacity: 1; }
        .modal-overlay.closing .glass-card { animation: fadeOutZoom 0.3s ease forwards; }
        .modal-overlay .glass-card { position: relative; animation: zoomInBounce 0.4s cubic-bezier(0.34, 1.56, 0.64, 1) forwards; }
   

        /* Perbaikan Tombol Aksi NaufaRu */
        .btn-edit-naufaru {
            background: rgba(13, 110, 253, 0.1);
            color: #0d6efd;
            border: 1px solid rgba(13, 110, 253, 0.2);
            padding: 8px 12px;
            border-radius: 8px;
            transition: 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-edit-naufaru:hover {
            background: #0d6efd;
            color: white;
            transform: translateY(-2px);
        }

        .btn-delete-naufaru {
            background: rgba(239, 76, 77, 0.1);
            color: #ef4c4d;
            border: 1px solid rgba(239, 76, 77, 0.2);
            padding: 8px 12px;
            border-radius: 8px;
            transition: 0.3s;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-delete-naufaru:hover {
            background: #ef4c4d;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(239, 76, 77, 0.3);
        }

        /* Memperbaiki alignment tabel aksi */
        .action-cell {
            display: flex;
            gap: 8px;
            justify-content: flex-start;
        }

        /* Tab Bahasa Styling */
        .lang-tabs-container {
            display: flex;
            gap: 8px;
            margin-bottom: 20px;
            background: rgba(255, 255, 255, 0.03);
            padding: 5px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .lang-btn {
            flex: 1;
            background: transparent;
            border: none;
            color: rgba(255, 255, 255, 0.5);
            padding: 10px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.75rem;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }

        .lang-btn.active {
            background: var(--accent);
            color: white;
            box-shadow: 0 4px 12px rgba(230, 57, 70, 0.3);
        }

        /* Tes */

        /* Container Utama */
        .custom-select-wrapper {
            position: relative;
            flex: 1;
            user-select: none;
        }

        /* Tampilan Trigger (Meniru Input) */
        .custom-select-trigger {
            background: rgba(255, 255, 255, 0.05) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            backdrop-filter: blur(10px);
            border-radius: 12px;
            padding: 12px 15px;
            color: white;
            font-size: 0.9rem;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: 0.3s;
        }

        /* List Pilihan (Floating) */
        .custom-options {
            position: absolute;
            top: calc(100% + 5px);
            left: 0;
            right: 0;
            background: rgba(20, 20, 20, 0.95) !important;
            backdrop-filter: blur(20px) !important;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 8px;
            display: none; 
            z-index: 12000; /* Pastikan di atas modal */
            box-shadow: 0 15px 35px rgba(0,0,0,0.5);
            max-height: 200px;
            overflow-y: auto;
        }

        .custom-option {
            padding: 10px 15px;
            color: rgba(255,255,255,0.7);
            cursor: pointer;
            border-radius: 8px;
            transition: 0.2s;
            font-size: 0.85rem;
        }

        .custom-option:hover {
            background: rgba(255, 255, 255, 0.05);
            color: white;
        }

        .custom-option.selected {
            background: var(--accent) !important;
            color: white !important;
        }

        /* Animasi Panah */
        .custom-select-arrow {
            font-size: 0.75rem;
            transition: 0.3s;
            opacity: 0.5;
        }
        .custom-select-wrapper.open .custom-select-arrow {
            transform: rotate(180deg);
            opacity: 1;
        }

        /* --- 1. STYLING TEXTBOX PREMIUM (Harga, Nama, Deskripsi) --- */
        .input-glass-premium {
            width: 100%;
            background: rgba(255, 255, 255, 0.03) !important;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            border-radius: 12px;
            padding: 12px 15px;
            color: white !important;
            font-size: 0.9rem;
            outline: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .input-glass-premium:focus {
            background: rgba(239, 76, 77, 0.05) !important;
            border-color: var(--accent) !important;
            box-shadow: 0 0 20px rgba(239, 76, 77, 0.15);
        }

        /* Placeholder Styling */
        .input-glass-premium::placeholder {
            color: rgba(255, 255, 255, 0.3);
            font-size: 0.85rem;
        }

        /* --- 2. HILANGKAN SCROLLBAR DEFAULT & FIX POPUP --- */

        /* Sembunyikan scrollbar default pada modal tapi tetap bisa di-scroll jika konten luber */
        #addModal .glass-card::-webkit-scrollbar {
            width: 0px; /* Sembunyikan bar */
            background: transparent;
        }

        /* Jika dropdown aktif, pastikan container utama tidak scroll berlebih */
        .modal-overlay .glass-card {
            overflow-y: visible !important; /* Agar menu dropdown tidak terpotong */
            max-height: none !important;    /* Biarkan tinggi menyesuaikan konten */
        }

        /* Scrollbar hanya muncul di dalam list dropdown jika opsi terlalu banyak */
        .custom-options::-webkit-scrollbar {
            width: 5px;
        }
        .custom-options::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }
    </style>
</head>
<body class="dark-mode">

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="../assets/imgs/logo-white.png" alt="Logo" class="sidebar-logo">
            <button class="toggle-btn" id="toggleBtn"><i class="fas fa-angle-left"></i></button>
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
            <h1>Manajemen Katalog Member</h1>
            <p style="font-size: 0.85rem; opacity: 0.8;">Kelola produk yang akan tampil pada katalog belanja member area.</p>
        </div>

        <div class="glass-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; margin-top: -10px; border-bottom: 1px solid var(--glass-border);">
                <h4 style="font-size: 1rem;"><i class="fas fa-list me-2"></i> Daftar Produk Aktif</h4>
                <button class="btn-action" style="width: auto; padding: 10px 25px;" onclick="openAddModal()">
                    <i class="fas fa-plus me-2"></i> Tambah Produk
                </button>
            </div>

            <table class="table-glass">
                <thead>
                    <tr>
                        <th>GAMBAR</th>
                        <th>NAMA PRODUK</th>
                        <th>KATEGORI</th>
                        <th>HARGA</th>
                        <th>AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $products = $conn->query("SELECT * FROM site_products_promo ORDER BY id DESC");
                    while($row = $products->fetch_assoc()):
                        // SINKRONISASI DATABASE:
                        // Menggunakan kolom 'gambar_produk' dan 'kategori' sesuai screenshot DB Anda
                        $gambar = !empty($row['gambar_produk']) ? $row['gambar_produk'] : 'placeholder.png';
                        $kategori = !empty($row['kategori']) ? $row['kategori'] : 'Tanpa Kategori';
                    ?>
                    <tr class="align-middle">
                        <td>
                            <img src="../../assets/imgs/img-catalog/<?= $gambar ?>" 
                                class="img-preview" 
                                onerror="this.src='../../assets/imgs/placeholder.png'">
                        </td>
                        <td>
                            <span class="fw-bold" style="color: rgba(255,255,255,0.9);"><?= htmlspecialchars($row['product_name']) ?></span>
                        </td>
                        <td>
                            <span class="badge" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: var(--accent); padding: 6px 12px; font-weight: 500;">
                                <i class="fas fa-tag me-1" style="font-size: 0.7rem;"></i> 
                                <!-- Pastikan memanggil $row['kategori'] untuk menampilkan teksnya -->
                                <?= !empty($row['kategori']) ? htmlspecialchars($row['kategori']) : 'Lainnya' ?>
                            </span>
                        </td>
                        <td>
                            <span style="color: #4cd137; font-weight: 600;">Rp <?= number_format($row['price'], 0, ',', '.') ?></span>
                        </td>
                        <td>
                            <div class="action-cell">
                                <!-- Tombol Edit Premium -->
                                <a href="edit_katalog.php?id=<?= $row['id'] ?>" class="btn-edit-naufaru" title="Edit Produk">
                                    <i class="fas fa-edit"></i>
                                </a>
                                
                                <!-- Tombol Hapus Premium -->
                                <button class="btn-delete-naufaru" onclick="confirmDelete(<?= $row['id'] ?>)" title="Hapus Produk">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Modal Tambah Produk (Style & Animasi Terintegrasi) -->
    <div id="addModal" class="modal-overlay">
        <div class="glass-card" style="max-width: 600px; width: 90%; max-height: 90vh; overflow-y: auto;">
            <button onclick="closeAddModal()" style="position: absolute; top: 25px; right: 25px; background: none; border: none; color: rgba(255,255,255,0.3); font-size: 1.5rem; cursor: pointer;">&times;</button>
            
            <div class="modal-header-naufaru">
                <i class="fas fa-layer-group" style="color: var(--accent);"></i>
                <h4 style="margin: 0;">Tambah Katalog Produk</h4>
            </div>
            
            <form id="formTambahKatalog" action="proses_katalog.php" method="POST" enctype="multipart/form-data">
                
                <div class="form-group-row">
                    <label>Harga (Rp)</label>
                    <input type="number" name="price" class="input-glass-premium" placeholder="Contoh: 150000" required>
                </div>

                <div class="form-group-row">
                    <label>Gambar Produk</label>
                    <div class="input-file-wrapper">
                        <input type="file" name="image" required>
                    </div>
                </div>

                <div class="lang-tabs-container">
                    <button type="button" onclick="switchLang('id')" class="lang-btn active" id="btn-id">INDONESIA</button>
                    <button type="button" onclick="switchLang('en')" class="lang-btn" id="btn-en">ENGLISH</button>
                    <button type="button" onclick="switchLang('jp')" class="lang-btn" id="btn-jp">JAPANESE</button>
                </div>

                <div id="lang-id" class="lang-section">
                    <div class="form-group-row">
                        <label>Nama Produk (ID)</label>
                        <input type="text" name="product_name" class="input-glass-premium" placeholder="Nama dalam Bahasa Indonesia..." required>
                    </div>
                    <div class="form-group-row">
                        <label>Kategori (ID)</label>
                        <div class="custom-select-wrapper lang-dropdown" data-lang="id">
                            <input type="hidden" name="category" class="lang-input" required>
                            <div class="custom-select-trigger lang-trigger">
                                <span class="lang-label">Pilih Kategori...</span>
                                <i class="fas fa-chevron-down custom-select-arrow"></i>
                            </div>
                            <div class="custom-options lang-list">
                                <div class="custom-option" data-value="Banner/X-Banner">Banner/X-Banner</div>
                                <div class="custom-option" data-value="Stiker">Stiker</div>
                                <div class="custom-option" data-value="Cetak Buku">Cetak Buku</div>
                                <div class="custom-option" data-value="Cetak Foto">Cetak Foto</div>
                                <div class="custom-option" data-value="Flyer & Poster">Flyer & Poster</div>
                                <div class="custom-option" data-value="Lainnya">Lainnya</div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group-row" style="align-items: flex-start;">
                        <label style="margin-top: 12px;">Deskripsi (ID)</label>
                        <textarea name="description" class="input-glass-premium" rows="3" placeholder="Tulis deskripsi Indonesia..."></textarea>
                    </div>
                </div>

                <div id="lang-en" class="lang-section" style="display:none;">
                    <div class="form-group-row">
                        <label>Product Name (EN)</label>
                        <input type="text" name="product_en" class="input-glass-premium" placeholder="Product name in English...">
                    </div>
                    <div class="form-group-row">
                        <label>Category (EN)</label>
                        <div class="custom-select-wrapper lang-dropdown" data-lang="en">
                            <input type="hidden" name="kategori_en" class="lang-input">
                            <div class="custom-select-trigger lang-trigger">
                                <span class="lang-label">Select Category...</span>
                                <i class="fas fa-chevron-down custom-select-arrow"></i>
                            </div>
                            <div class="custom-options lang-list">
                                <div class="custom-option" data-value="Banner/X-Banner">Banner/X-Banner</div>
                                <div class="custom-option" data-value="Stickers">Stickers</div>
                                <div class="custom-option" data-value="Book Printing">Book Printing</div>
                                <div class="custom-option" data-value="Photo Printing">Photo Printing</div>
                                <div class="custom-option" data-value="Flyer & Poster">Flyer & Poster</div>
                                <div class="custom-option" data-value="Others">Others</div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group-row" style="align-items: flex-start;">
                        <label style="margin-top: 12px;">Description (EN)</label>
                        <textarea name="description_en" class="input-glass-premium" rows="3" placeholder="English description..."></textarea>
                    </div>
                </div>

                <div id="lang-jp" class="lang-section" style="display:none;">
                    <div class="form-group-row">
                        <label>製品名 (JP)</label>
                        <input type="text" name="product_jp" class="input-glass-premium" placeholder="日本語の製品名...">
                    </div>
                    <div class="form-group-row">
                        <label>カテゴリー (JP)</label>
                        <div class="custom-select-wrapper lang-dropdown" data-lang="jp">
                            <input type="hidden" name="kategori_jp" class="lang-input">
                            <div class="custom-select-trigger lang-trigger">
                                <span class="lang-label">カテゴリーを選択...</span>
                                <i class="fas fa-chevron-down custom-select-arrow"></i>
                            </div>
                            <div class="custom-options lang-list">
                                <div class="custom-option" data-value="バナー/Xバナー">バナー/Xバナー</div>
                                <div class="custom-option" data-value="ステッカー">ステッカー</div>
                                <div class="custom-option" data-value="本印刷">本印刷</div>
                                <div class="custom-option" data-value="写真プリント">写真プリント</div>
                                <div class="custom-option" data-value="チラシ＆ポスター">チラシ＆ポスター</div>
                                <div class="custom-option" data-value="その他">その他</div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group-row" style="align-items: flex-start;">
                        <label style="margin-top: 12px;">説明 (JP)</label>
                        <textarea name="description_jp" class="input-glass-premium" rows="3" placeholder="日本語の説明..."></textarea>
                    </div>
                </div>

                <div style="margin-top: 30px;">
                    <button type="submit" name="submit_add" class="btn-action" style="background: var(--accent); width: 100%; padding: 15px; border-radius: 12px; font-weight: 700;">
                        <i class="fas fa-save"></i> SIMPAN KE DATABASE
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="admin_script.js"></script>
    <script>
    // --- 1. LOGIKA MODAL (OPEN & CLOSE) ---
    function openAddModal() {
        const modal = document.getElementById('addModal');
        modal.classList.remove('closing');
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeAddModal() {
        const modal = document.getElementById('addModal');
        modal.classList.add('closing');
        
        setTimeout(() => {
            modal.classList.remove('active');
            modal.classList.remove('closing');
            document.body.style.overflow = 'auto';
        }, 300);
    }

    // Tutup Modal saat klik Overlay
    window.addEventListener('click', function(event) {
        const modal = document.getElementById('addModal');
        if (event.target == modal) {
            closeAddModal();
        }
    });

    // --- 2. LOGIKA CUSTOM DROPDOWN MULTIBAHASA ---
    document.addEventListener('DOMContentLoaded', function() {
        // Seleksi semua wrapper dropdown yang memiliki class .lang-dropdown
        const dropdowns = document.querySelectorAll('.lang-dropdown');

        dropdowns.forEach(dropdown => {
            const trigger = dropdown.querySelector('.lang-trigger');
            const list = dropdown.querySelector('.lang-list');
            const options = dropdown.querySelectorAll('.custom-option');
            const hiddenInput = dropdown.querySelector('.lang-input');
            const label = dropdown.querySelector('.lang-label');

            // Toggle Buka/Tutup
            trigger.addEventListener('click', function(e) {
                e.stopPropagation();
                
                // Tutup dropdown lain yang mungkin terbuka
                dropdowns.forEach(other => {
                    if (other !== dropdown) {
                        other.classList.remove('open');
                        const otherList = other.querySelector('.lang-list');
                        if (otherList) otherList.style.display = 'none';
                    }
                });

                dropdown.classList.toggle('open');
                list.style.display = dropdown.classList.contains('open') ? 'block' : 'none';
            });

            // Pilih Opsi
            options.forEach(opt => {
                opt.addEventListener('click', function() {
                    const val = this.getAttribute('data-value');
                    
                    hiddenInput.value = val;
                    label.innerText = val;

                    // Update UI State (Selected Class)
                    options.forEach(o => o.classList.remove('selected'));
                    this.classList.add('selected');

                    // Tutup setelah pilih
                    dropdown.classList.remove('open');
                    list.style.display = 'none';
                });
            });
        });

        // Klik di luar untuk menutup semua dropdown
        window.addEventListener('click', function() {
            dropdowns.forEach(dropdown => {
                dropdown.classList.remove('open');
                const list = dropdown.querySelector('.lang-list');
                if (list) list.style.display = 'none';
            });
        });

        // --- 3. VALIDASI FORM SEBELUM KIRIM ---
        const form = document.getElementById('formTambahKatalog');
        if (form) {
            form.addEventListener('submit', function(e) {
                const mainCategory = form.querySelector('input[name="category"]').value;
                
                if (!mainCategory || mainCategory === "") {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Kategori Belum Dipilih',
                        text: 'Silakan pilih kategori produk (ID) terlebih dahulu!',
                        background: '#1a1a1a',
                        color: '#ffffff',
                        confirmButtonColor: '#ef4c4d'
                    });
                }
            });
        }
    });

    // --- 4. LOGIKA SWITCH BAHASA (TABS) ---
    function switchLang(lang) {
        // Sembunyikan semua section
        document.querySelectorAll('.lang-section').forEach(el => el.style.display = 'none');
        // Reset state tombol
        document.querySelectorAll('.lang-btn').forEach(btn => btn.classList.remove('active'));
        
        // Tampilkan yang dipilih
        const targetSection = document.getElementById('lang-' + lang);
        const targetBtn = document.getElementById('btn-' + lang);
        
        if (targetSection) targetSection.style.display = 'block';
        if (targetBtn) targetBtn.classList.add('active');
    }

    // --- 5. SWEETALERT HAPUS DATA ---
    function confirmDelete(id) {
        Swal.fire({
            title: 'Hapus Produk?',
            text: "Data produk akan dihapus secara permanen dari database NaufaRu.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4c4d',
            cancelButtonColor: 'rgba(255,255,255,0.1)',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            background: '#1a1a1a',
            color: '#ffffff',
            scrollbarPadding: false 
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'admin_katalog.php?delete=' + id;
            }
        });
    }

    // --- 6. NOTIFIKASI STATUS DARI URL ---
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('status')) {
            const status = urlParams.get('status');
            const swalConfig = {
                icon: 'success',
                timer: 2000,
                showConfirmButton: false,
                background: '#1a1a1a',
                color: '#ffffff'
            };

            if (status === 'success_delete') {
                Swal.fire({ ...swalConfig, title: 'Berhasil!', text: 'Produk telah dihapus.' });
            } else if (status === 'success') {
                Swal.fire({ ...swalConfig, title: 'Berhasil!', text: 'Produk baru ditambahkan.' });
            }
            
            // Bersihkan URL tanpa refresh
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    });
</script>
</body>
</html> 