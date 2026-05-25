<?php 
/**
 * File: admin/admin_fitur.php
 * Deskripsi: Master Panel Admin Fitur (Modular Include Shell)
 * Pembaruan: Penambahan modul pengelolaan gambar latar belakang khusus tema gelap (section_bgdark.php)
 */

// 1. Proteksi Sesi dan Koneksi Database
include 'cek_login.php'; 
include '../config.php'; 

// --- CENTRALIZED ACTION: LOGIKA PROSES TAMBAH WALLPAPER TEMA GELAP BARU ---
if (isset($_POST['upload_bgdark_wallpaper'])) {
    if (isset($_FILES['wallpaper_dark_file']) && $_FILES['wallpaper_dark_file']['error'] === 0) {
        $file_name = $_FILES['wallpaper_dark_file']['name'];
        $file_size = $_FILES['wallpaper_dark_file']['size'];
        $file_tmp  = $_FILES['wallpaper_dark_file']['tmp_name'];
        
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png'];
        
        if (in_array($ext, $allowed_ext) && $file_size <= 5242880) {
            $new_name = "bg-dark-" . time() . "." . $ext;
            // Jalur penyimpanan langsung ke folder assets dari posisi admin_fitur.php
            $upload_path = "../assets/imgs/" . $new_name;
            
            if (move_uploaded_file($file_tmp, $upload_path)) {
                $query_insert = "INSERT INTO site_wallpaper (image_path, theme_mode, is_active) VALUES ('$new_name', 'dark_wallpaper', 1)";
                if (mysqli_query($conn, $query_insert)) {
                    header("Location: admin_fitur.php?status=success_bgdark");
                    exit();
                }
            }
        }
    }
    $_SESSION['bgdark_upload_errors'] = "Format berkas wajib JPG/JPEG/PNG dan ukuran maksimal adalah 5MB.";
    header("Location: admin_fitur.php?status=failed_bgdark");
    exit();
}

// --- CENTRALIZED ACTION: LOGIKA PROSES MENGHAPUS WALLPAPER TEMA GELAP ---
if (isset($_GET['delete_dark_id'])) {
    $id_del = intval($_GET['delete_dark_id']);
    
    $check = mysqli_query($conn, "SELECT image_path FROM site_wallpaper WHERE id = $id_del AND theme_mode = 'dark_wallpaper'");
    if ($row = mysqli_fetch_assoc($check)) {
        $file_path = "../assets/imgs/" . $row['image_path'];
        if (file_exists($file_path)) {
            @unlink($file_path);
        }
        mysqli_query($conn, "DELETE FROM site_wallpaper WHERE id = $id_del");
        header("Location: admin_fitur.php?status=success_bgdark");
        exit();
    }
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NaufaRu Admin | Admin Fitur Konfigurasi</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="admin_style.css">
    
    <style>
        /* CSS Utility Layout Form POS & Background Uploader */
        .invoice-grid-split { display: grid; grid-template-columns: 1fr 2fr; gap: 30px; margin-top: 20px; margin-bottom: 30px; }
        .form-group { margin-bottom: 25px; display: flex; flex-direction: column; }
        .label-text { font-size: 0.75rem; opacity: 0.6; text-transform: uppercase; margin-bottom: 10px; display: block; letter-spacing: 1.5px; font-weight: 600; color: #ffc107; }
        
        .input-glass { 
            box-sizing: border-box; width: 100%; background: rgba(255,255,255,0.03); 
            border: 1px solid var(--glass-border); border-radius: 12px; padding: 12px 15px; 
            color: white; outline: none; transition: 0.3s; font-size: 0.9rem;
        }
        .input-glass:focus { border-color: var(--accent); background: rgba(255,255,255,0.08); }
        .input-glass:disabled { opacity: 0.6; cursor: not-allowed; background: rgba(0,0,0,0.2); }

        .invoice-segment-container { display: flex; align-items: center; gap: 6px; width: 100%; }
        .invoice-segment-dash { color: rgba(255,255,255,0.4); font-weight: bold; font-size: 1rem; }
        .segment-input-short { flex: 1; text-align: center; font-weight: 700; letter-spacing: 1px; }
        .segment-input-medium { flex: 1.4; text-align: center; }

        .guest-input-container { display: block; max-height: 0; overflow: hidden; transition: max-height 0.4s ease-in-out, margin-top 0.3s ease; opacity: 0; }
        .guest-input-container.show { max-height: 300px; margin-top: 15px; opacity: 1; }

        .custom-select-wrapper { position: relative; width: 100%; user-select: none; }
        .custom-select-trigger { display: flex; align-items: center; justify-content: space-between; padding: 12px 15px; background: rgba(255, 255, 255, 0.03); border: 1px solid var(--glass-border); border-radius: 12px; color: #fff; cursor: pointer; font-size: 0.9rem; transition: 0.3s; }
        .custom-select-trigger:hover, .custom-select-wrapper.open .custom-select-trigger { border-color: var(--accent); background: rgba(255,255,255,0.08); }
        .custom-select-arrow { font-size: 0.75rem; opacity: 0.6; transition: transform 0.3s ease; }
        .custom-select-wrapper.open .custom-select-arrow { transform: rotate(180deg); }
        
        .custom-options { position: absolute; top: calc(100% + 6px); left: 0; right: 0; background: rgba(20, 20, 20, 0.95); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 14px; box-shadow: 0 15px 35px rgba(0,0,0,0.5); display: none; flex-direction: column; max-height: 220px; overflow-y: auto; z-index: 1000; }
        .custom-select-wrapper.open .custom-options { display: flex; }
        .custom-option { padding: 12px 15px; color: rgba(255,255,255,0.8); cursor: pointer; font-size: 0.88rem; transition: all 0.2s ease; border-bottom: 1px solid rgba(255,255,255,0.03); }
        .custom-option:last-child { border-bottom: none; }
        .custom-option:hover { background: rgba(239, 76, 77, 0.15); color: #fff; padding-left: 20px; }
        .custom-option.selected { background: var(--accent); color: white; font-weight: bold; }

        .table-pos-invoice { width: 100%; border-collapse: collapse; margin-top: 10px; table-layout: fixed; }
        .table-pos-invoice th { background: rgba(255, 255, 255, 0.02); padding: 12px; text-align: center; font-size: 0.7rem; color: var(--accent); text-transform: uppercase; letter-spacing: 1px; border-bottom: 1px solid var(--glass-border); }
        .table-pos-invoice td { padding: 12px; border-bottom: 1px solid rgba(255,255,255,0.05); vertical-align: middle; color: white; font-size: 0.88rem; }
        
        .btn-action-premium { padding: 15px 25px; font-weight: 700; border-radius: 12px; border: none; cursor: pointer; transition: 0.3s; display: inline-flex; align-items: center; justify-content: center; gap: 10px; font-size: 0.85rem; }
        .btn-action-premium:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(239,76,77,0.3); }
        .btn-remove-row { background: rgba(239,76,77,0.1); color: #ef4c4d; border: 1px solid rgba(239,76,77,0.2); width: 36px; height: 36px; border-radius: 8px; cursor: pointer; transition: 0.3s; display: flex; align-items: center; justify-content: center; margin: 0 auto; }
        .btn-remove-row:hover { background: #ef4c4d; color: white; }
        .btn-print-row { background: rgba(116, 185, 255, 0.1); color: #74b9ff; border: 1px solid rgba(116, 185, 255, 0.2); width: 36px; height: 36px; border-radius: 8px; cursor: pointer; transition: 0.3s; display: flex; align-items: center; justify-content: center; margin: 0 auto; text-decoration: none; }
        .btn-print-row:hover { background: #74b9ff; color: #111; transform: scale(1.05); }

        .manual-badge { background: rgba(239, 76, 77, 0.15); color: #ef4c4d; border: 1px solid rgba(239, 76, 77, 0.3); padding: 2px 8px; border-radius: 6px; font-size: 0.65rem; font-weight: bold; display: inline-block; margin-left: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
        .swal2-popup { background: rgba(20, 20, 20, 0.95) !important; backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 25px !important; color: white !important; }
        .swal2-title, .swal2-html-container { color: white !important; }
        .swal2-confirm { background-color: var(--accent) !important; border-radius: 10px !important; }

        .table-scroll-x { width: 100%; overflow-x: auto; overflow-y: visible; padding-bottom: 8px; scroll-behavior: smooth; scrollbar-width: thin; scrollbar-color: rgba(255,255,255,0.25) transparent; }
        .table-scroll-x .table-pos-invoice { min-width: 980px; width: max-content; }
        .table-scroll-x::-webkit-scrollbar { height: 8px; }
        .table-scroll-x::-webkit-scrollbar-track { background: rgba(255,255,255,0.03); border-radius: 20px; }
        .table-scroll-x::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.18); border-radius: 20px; }
        .table-scroll-x::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.35); }
        .table-pos-invoice th, .table-pos-invoice td { white-space: nowrap; }
        .table-pos-invoice td:nth-child(5), .table-pos-invoice th:nth-child(5) { min-width: 220px; }

        .modal-overlay-glass { display: none; position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.55); z-index: 99999; justify-content: center; align-items: center; backdrop-filter: blur(15px); -webkit-backdrop-filter: blur(15px); }
        .modal-content-card { width: 460px; padding: 30px; background: rgba(20, 20, 20, 0.85); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 24px; position: relative; box-shadow: 0 25px 50px rgba(0,0,0,0.6); }
        .modal-header-naufaru { display: flex; align-items: center; justify-content: space-between; font-size: 0.9rem; color: #EF4C4D; font-weight: 900; border-bottom: 1px solid rgba(255, 255, 255, 0.1); padding-bottom: 10px; margin-bottom: 15px; letter-spacing: 0.5px; }
        .btn-close-modal { background: rgba(255, 255, 255, 0.05); border: none; color: #fff; width: 28px; height: 28px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; transition: 0.2s; }
        .btn-close-modal:hover { background: #EF4C4D; }
        .info-box-modal-small { background: rgba(52, 152, 219, 0.08); border-left: 3px solid #3498db; padding: 8px 12px; border-radius: 10px; margin-bottom: 20px; }
        .info-box-modal-small p { margin: 0; font-size: 0.75rem; color: rgba(255,255,255,0.6); line-height: 1.4; }
        .label-modal { display: block; color: #EF4C4D; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; margin-bottom: 12px; letter-spacing: 1px; }
        .btn-modal-select-item:hover { background: rgba(46, 204, 113, 0.15) !important; border-color: #2ecc71 !important; transform: scale(1.01); }
        .row-product-dropdown .custom-options { display: none !important; }

        .table-scroll-x .table-pos-invoice { width: 1050px !important; table-layout: fixed; border-collapse: collapse; }
        .table-pos-invoice th:nth-child(1) { width: 320px; }
        .table-pos-invoice th:nth-child(2) { width: 130px; }
        .table-pos-invoice th:nth-child(3) { width: 90px;  }
        .table-pos-invoice th:nth-child(4) { width: 140px; }
        .table-pos-invoice th:nth-child(5) { width: 310px; }
        .table-pos-invoice th:nth-child(6) { width: 60px;  }
        .table-scroll-x::-webkit-scrollbar-thumb:hover { background: #EF4C4D; }

        .modal-product-list-scroll { scrollbar-width: thin; scrollbar-color: rgba(255, 255, 255, 0.15) transparent; }
        .modal-product-list-scroll::-webkit-scrollbar { width: 6px; }
        .modal-product-list-scroll::-webkit-scrollbar-track { background: rgba(255, 255, 255, 0.01); border-radius: 10px; }
        .modal-product-list-scroll::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.12); border-radius: 10px; transition: background 0.3s ease; }
        .modal-product-list-scroll::-webkit-scrollbar-thumb:hover { background: rgba(239, 76, 77, 0.6); }
        .modal-product-list-scroll::-webkit-scrollbar-thumb:active { background: #EF4C4D; }
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
            <a href="admin_katalog.php" class="nav-link"><i class="fas fa-boxes"></i> <span class="scramble-text" data-value="Admin Katalog">Admin Katalog</span></a>
            <a href="admin_fitur.php" class="nav-link active"><i class="fas fa-user-cog"></i> <span class="scramble-text" data-value="Admin Fitur">Admin Fitur</span></a>
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
            <h1>Konfigurasi Splash Screen</h1>
            <p style="font-size: 0.85rem; opacity: 0.8;">Perbaiki background splash screen disini.</p>
        </div>

        <?php include 'sections/section_splashscreen.php'; ?>

        <!-- MODUL BARU: MODULAR INCLUDE UNTUK WALLPAPER KHUSUS TEMA GELAP -->
        <div class="glass-card welcome-card">
            <h1>Konfigurasi Wallpaper Tema Gelap</h1>
            <p style="font-size: 0.85rem; opacity: 0.8;">Kelola gambar latar belakang dinamis yang dikhususkan murni untuk mendongkrak visualisasi halaman member saat mengaktifkan Dark Mode.</p>
        </div>

        <?php include 'sections/section_bgdark.php'; ?>

        <div class="glass-card welcome-card">
            <h1>Pembuatan Invoice Manual</h1>
            <p style="font-size: 0.85rem; opacity: 0.8;">Modul POS: Gabungkan item, kalkulasi diskon kustom, dan cetak lembar invoice fisik tanpa antrean sistem online.</p>
        </div>

        <?php include 'sections/section_invoice_manual.php'; ?>
        
    </main>

    <script src="admin_script.js"></script>
    <script>
    // --- SISTEM CENTRALIZED POPUP NOTIFIKASI (UTUH & LENGKAP) ---
    function tampilkanAlertStatus(status) {
        let config = {
            timer: 3000,
            showConfirmButton: false,
            timerProgressBar: true,
            background: '#1a1a1a',
            color: '#fff',
            confirmButtonColor: '#EF4C4D'
        };

        // 1. DETEKSI STATUS: BERHASIL MEMBUAT INVOICE MANUAL
        if (status === 'success_invoice') {
            config.icon = 'success';
            config.title = 'Invoice Terbuat!';
            config.text = 'Data transaksi kasir manual berhasil disimpan ke dalam sistem.';
            
            <?php if (isset($_SESSION['print_manual_invoice_id'])): ?>
                const printId = "<?= $_SESSION['print_manual_invoice_id']; ?>";
                window.open('print_invoice_manual.php?id=' + printId, '_blank');
                <?php unset($_SESSION['print_manual_invoice_id']); ?>
            <?php endif; ?>

        // 2. DETEKSI STATUS: GAGAL KARENA STRUKTUR ITEM KASIR KOSONG
        } else if (status === 'failed_empty_items') {
            config.icon = 'warning';
            config.title = 'Kotak Belum Terisi!';
            config.text = 'Silakan pilih jenis produk katalog pada tabel rincian transaksi sebelum menyimpan.';
            config.showConfirmButton = true;
            config.timer = null; 

        // 3. DETEKSI STATUS: BERHASIL MEMPERBARUI WALLPAPER SPLASH SCREEN
        } else if (status === 'success_splash') {
            config.icon = 'success';
            config.title = 'Wallpaper Diperbarui!';
            config.text = 'Perubahan gambar latar belakang splash screen berhasil diterapkan ke database utama.';

        // 4. DETEKSI STATUS: GAGAL SAAT PROSES UPLOAD WALLPAPER SPLASH SCREEN
        } else if (status === 'failed_splash') {
            config.icon = 'error';
            config.title = 'Upload Bermasalah!';
            config.html = `<div style="text-align: left; font-size: 0.85rem; opacity: 0.8;">
                <?= isset($_SESSION['splash_upload_errors']) ? $_SESSION['splash_upload_errors'] : 'Terjadi kendala pemrosesan ukuran atau ekstensi berkas gambar.'; ?>
            </div>`;
            config.showConfirmButton = true;
            config.timer = null;
            <?php unset($_SESSION['splash_upload_errors']); ?>

        // 5. DETEKSI STATUS: BERHASIL MENGUPLOAD/MENGUBAH WALLPAPER KHUSUS TEMA GELAP
        } else if (status === 'success_bgdark') {
            config.icon = 'success';
            config.title = 'Wallpaper Dark Mode Diperbarui!';
            config.text = 'Gambar latar belakang khusus tema malam berhasil disimpan dan diselaraskan ke database keanggotaan.';

        // 6. DETEKSI STATUS: GAGAL SAAT PROSES UPLOAD WALLPAPER KHUSUS TEMA GELAP
        } else if (status === 'failed_bgdark') {
            config.icon = 'error';
            config.title = 'Gagal Mengunggah!';
            config.html = `<div style="text-align: left; font-size: 0.85rem; opacity: 0.8;">
                <?= isset($_SESSION['bgdark_upload_errors']) ? $_SESSION['bgdark_upload_errors'] : 'Pastikan ukuran file di bawah 5MB dan berekstensi JPG/JPEG/PNG.'; ?>
            </div>`;
            config.showConfirmButton = true;
            config.timer = null;
            <?php unset($_SESSION['bgdark_upload_errors']); ?>
        }

        if (status) {
            Swal.fire(config);
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const params = new URLSearchParams(window.location.search);
        if (params.has('status')) {
            tampilkanAlertStatus(params.get('status'));
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    });
    </script>
</body>
</html>