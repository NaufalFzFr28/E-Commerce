<?php 
/**
 * File: admin/admin_fitur.php
 * Deskripsi: Modul POS dengan Fitur Dinamis Input Nama & Alamat Pelanggan Manual untuk Non-Member
 * Pembaruan: Penambahan Input Catatan Item, Catatan Petugas, & Sinkronisasi Form POS Manual Lengkap
 */

// 1. Proteksi Sesi dan Koneksi Database
include 'cek_login.php'; 
include '../config.php'; 

// Ambil jumlah pesanan dengan status 'Pending' untuk notifikasi di sidebar
$q_pending = mysqli_query($conn, "SELECT COUNT(*) as total FROM orders WHERE status = 'Pending'");
$pending_data = mysqli_fetch_assoc($q_pending);
$total_pending = $pending_data['total'] ?? 0;

// Otomatisasi komponen nomor invoice manual
$invoice_date_part = date('Ymd'); 
$invoice_rand_part = strtoupper(substr(md5(time()), 0, 4)); 
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NaufaRu Admin | Pembuatan Invoice Manual</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="admin_style.css">
    
    <style>
        /* CSS Utility Layout Form POS */
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

        /* Area Input Dinamis Non-Member (Nama & Alamat) */
        .guest-input-container {
            display: block;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s ease-in-out, margin-top 0.3s ease;
            opacity: 0;
        }
        .guest-input-container.show {
            max-height: 300px;
            margin-top: 15px;
            opacity: 1;
        }

        /* Styling Premium Custom Dropdown */
        .custom-select-wrapper { position: relative; width: 100%; user-select: none; }
        .custom-select-trigger { 
            display: flex; align-items: center; justify-content: space-between;
            padding: 12px 15px; background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--glass-border); border-radius: 12px;
            color: #fff; cursor: pointer; font-size: 0.9rem; transition: 0.3s;
        }
        .custom-select-trigger:hover, .custom-select-wrapper.open .custom-select-trigger { 
            border-color: var(--accent); background: rgba(255,255,255,0.08); 
        }
        .custom-select-arrow { font-size: 0.75rem; opacity: 0.6; transition: transform 0.3s ease; }
        .custom-select-wrapper.open .custom-select-arrow { transform: rotate(180deg); }
        
        .custom-options {
            position: absolute; top: calc(100% + 6px); left: 0; right: 0;
            background: rgba(20, 20, 20, 0.95); backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 14px; box-shadow: 0 15px 35px rgba(0,0,0,0.5);
            display: none; flex-direction: column; max-height: 220px; overflow-y: auto; z-index: 1000;
        }
        .custom-select-wrapper.open .custom-options { display: flex; }
        .custom-option {
            padding: 12px 15px; color: rgba(255,255,255,0.8); cursor: pointer;
            font-size: 0.88rem; transition: all 0.2s ease; border-bottom: 1px solid rgba(255,255,255,0.03);
        }
        .custom-option:last-child { border-bottom: none; }
        .custom-option:hover { background: rgba(239, 76, 77, 0.15); color: #fff; padding-left: 20px; }
        .custom-option.selected { background: var(--accent); color: white; font-weight: bold; }

        /* Styling Tabel POS & Riwayat */
        .table-pos-invoice { width: 100%; border-collapse: collapse; margin-top: 10px; table-layout: fixed; }
        .table-pos-invoice th { background: rgba(255, 255, 255, 0.02); padding: 12px; text-align: center; font-size: 0.7rem; color: var(--accent); text-transform: uppercase; letter-spacing: 1px; border-bottom: 1px solid var(--glass-border); }
        .table-pos-invoice td { padding: 12px; border-bottom: 1px solid rgba(255,255,255,0.05); vertical-align: middle; color: white; font-size: 0.88rem; }
        
        .btn-action-premium { padding: 15px 25px; font-weight: 700; border-radius: 12px; border: none; cursor: pointer; transition: 0.3s; display: inline-flex; align-items: center; justify-content: center; gap: 10px; font-size: 0.85rem; }
        .btn-action-premium:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(239,76,77,0.3); }

        .btn-remove-row { background: rgba(239,76,77,0.1); color: #ef4c4d; border: 1px solid rgba(239,76,77,0.2); width: 36px; height: 36px; border-radius: 8px; cursor: pointer; transition: 0.3s; display: flex; align-items: center; justify-content: center; margin: 0 auto; }
        .btn-remove-row:hover { background: #ef4c4d; color: white; }

        .btn-print-row { background: rgba(116, 185, 255, 0.1); color: #74b9ff; border: 1px solid rgba(116, 185, 255, 0.2); width: 36px; height: 36px; border-radius: 8px; cursor: pointer; transition: 0.3s; display: flex; align-items: center; justify-content: center; margin: 0 auto; text-decoration: none; }
        .btn-print-row:hover { background: #74b9ff; color: #111; transform: scale(1.05); }

        /* PERBAIKAN BADGE: Mengubah Teks Menjadi NON-MEMBER */
        .manual-badge {
            background: rgba(239, 76, 77, 0.15);
            color: #ef4c4d;
            border: 1px solid rgba(239, 76, 77, 0.3);
            padding: 2px 8px;
            border-radius: 6px;
            font-size: 0.65rem;
            font-weight: bold;
            display: inline-block;
            margin-left: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Custom SweetAlert Style */
        .swal2-popup { background: rgba(20, 20, 20, 0.95) !important; backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 25px !important; color: white !important; }
        .swal2-title, .swal2-html-container { color: white !important; }
        .swal2-confirm { background-color: var(--accent) !important; border-radius: 10px !important; }

        /* =========================================
        SCROLL HORIZONTAL KHUSUS RINCIAN ITEM
        ========================================= */

        .table-scroll-x {
            width: 100%;
            overflow-x: auto;
            overflow-y: visible;
            padding-bottom: 8px;
            
            /* Smooth scrolling */
            scroll-behavior: smooth;

            /* Firefox */
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,0.25) transparent;
        }

        /* Lebar minimum tabel agar bisa discroll */
        .table-scroll-x .table-pos-invoice {
            min-width: 980px;
            width: max-content;
        }

        /* Scrollbar Chrome/Edge */
        .table-scroll-x::-webkit-scrollbar {
            height: 8px;
        }

        .table-scroll-x::-webkit-scrollbar-track {
            background: rgba(255,255,255,0.03);
            border-radius: 20px;
        }

        .table-scroll-x::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.18);
            border-radius: 20px;
            transition: 0.3s;
        }

        .table-scroll-x::-webkit-scrollbar-thumb:hover {
            background: rgba(255,255,255,0.35);
        }

        /* Agar isi tabel tetap rapi */
        .table-pos-invoice th,
        .table-pos-invoice td {
            white-space: nowrap;
        }

        /* Kolom catatan tetap lebih fleksibel */
        .table-pos-invoice td:nth-child(5),
        .table-pos-invoice th:nth-child(5) {
            min-width: 220px;
        }

        /* =========================================
        WRAPPER SCROLL KHUSUS TABEL
        ========================================= */

        .table-scroll-x {
            width: 100%;
            overflow-x: auto;
            overflow-y: hidden;
            padding-bottom: 10px;
            position: relative;

            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,0.25) transparent;
        }

        /* Lebar minimum tabel */
        .table-scroll-x .table-pos-invoice {
            min-width: 1150px;
            width: max-content;
            border-collapse: collapse;
        }

        /* Scrollbar */
        .table-scroll-x::-webkit-scrollbar {
            height: 8px;
        }

        .table-scroll-x::-webkit-scrollbar-track {
            background: rgba(255,255,255,0.03);
            border-radius: 20px;
        }

        .table-scroll-x::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.18);
            border-radius: 20px;
        }

        .table-scroll-x::-webkit-scrollbar-thumb:hover {
            background: rgba(255,255,255,0.35);
        }

        /* Isi tabel */
        .table-pos-invoice th,
        .table-pos-invoice td {
            white-space: nowrap;
            position: relative;
        }

        /* Dropdown wrapper WAJIB relative */
        .row-product-dropdown {
            position: relative;
        }

        /* =========================================
        STYLING MODAL WINDOW NAUFARU GLASS THEME
        ========================================= */
        @keyframes popupZoomIn { from { opacity: 0; transform: scale(0.9); } to { opacity: 1; transform: scale(1); } }
        @keyframes popupZoomOut { from { opacity: 1; transform: scale(1); } to { opacity: 0; transform: scale(0.9); } }

        .modal-overlay-glass {
            display: none; position: fixed; top:0; left:0; width:100%; height:100%; 
            background: rgba(0,0,0,0.55); z-index: 99999; justify-content: center; 
            align-items: center; backdrop-filter: blur(15px); -webkit-backdrop-filter: blur(15px);
        }

        .modal-content-card {
            width: 460px; padding: 30px; 
            background: rgba(20, 20, 20, 0.85);
            border: 1px solid rgba(255, 255, 255, 0.1); 
            border-radius: 24px; position: relative; 
            box-shadow: 0 25px 50px rgba(0,0,0,0.6);
        }

        .modal-header-naufaru {
            display: flex; align-items: center; justify-content: space-between;
            font-size: 0.9rem; color: #EF4C4D; font-weight: 900;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1); padding-bottom: 10px; margin-bottom: 15px;
            letter-spacing: 0.5px;
        }

        .btn-close-modal {
            background: rgba(255, 255, 255, 0.05); border: none; color: #fff; width: 28px; height: 28px; 
            border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; transition: 0.2s;
        }
        .btn-close-modal:hover { background: #EF4C4D; }

        .info-box-modal-small {
            background: rgba(52, 152, 219, 0.08); border-left: 3px solid #3498db; padding: 8px 12px; border-radius: 10px; margin-bottom: 20px;
        }
        .info-box-modal-small p {
            margin: 0; font-size: 0.75rem; color: rgba(255,255,255,0.6); line-height: 1.4;
        }

        .label-modal { 
            display: block; color: #EF4C4D; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; margin-bottom: 12px; letter-spacing: 1px; 
        }

        /* Hover effect khusus daftar item di dalam modal pilihan */
        .btn-modal-select-item:hover {
            background: rgba(46, 204, 113, 0.15) !important;
            border-color: #2ecc71 !important;
            transform: scale(1.01);
        }

        /* Mematikan total system dropdown lama agar tidak bentrok */
        .row-product-dropdown .custom-options {
            display: none !important;
        }

        /* =========================================
        SCROLLBAR TABEL KASIR (DIPERTAHANKAN COCOK)
        ========================================= */
        .table-scroll-x {
            width: 100%; max-width: 100%; overflow-x: auto; overflow-y: visible; padding-bottom: 12px; box-sizing: border-box;
        }
        .table-scroll-x .table-pos-invoice {
            width: 1050px !important; table-layout: fixed; border-collapse: collapse;
        }
        .table-pos-invoice th:nth-child(1) { width: 320px; }
        .table-pos-invoice th:nth-child(2) { width: 130px; }
        .table-pos-invoice th:nth-child(3) { width: 90px;  }
        .table-pos-invoice th:nth-child(4) { width: 140px; }
        .table-pos-invoice th:nth-child(5) { width: 310px; }
        .table-pos-invoice th:nth-child(6) { width: 60px;  }

        .table-scroll-x::-webkit-scrollbar { height: 8px; }
        .table-scroll-x::-webkit-scrollbar-track { background: rgba(255,255,255,0.02); border-radius: 20px; }
        .table-scroll-x::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.18); border-radius: 20px; }
        .table-scroll-x::-webkit-scrollbar-thumb:hover { background: #EF4C4D; }

        /* =========================================
        CUSTOM PREMIUM SCROLLBAR UNTUK MODAL LIST
        ========================================= */

        /* Untuk Browser Firefox */
        .modal-product-list-scroll {
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.15) transparent;
        }

        /* Untuk Browser Engine Webkit (Chrome, Safari, Edge, Opera) */
        .modal-product-list-scroll::-webkit-scrollbar {
            width: 6px; /* Membuat scrollbar lebih tipis dan elegan */
        }

        /* Area Track / Jalur Scrollbar */
        .modal-product-list-scroll::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.01); /* Hampir transparan agar menyatu dengan background glass */
            border-radius: 10px;
        }

        /* Thumb / Batang Scrollbar yang Bergeser */
        .modal-product-list-scroll::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.12); /* Warna redup saat diam */
            border-radius: 10px;
            transition: background 0.3s ease;
        }

        /* Efek saat Batang Scrollbar di-Hover atau digeser Kasir */
        .modal-product-list-scroll::-webkit-scrollbar-thumb:hover {
            background: rgba(239, 76, 77, 0.6); /* Berubah menjadi warna merah aksen NaufaRu (--accent) dengan opacity */
        }

        /* Efek aktif saat scrollbar sedang ditekan/drag */
        .modal-product-list-scroll::-webkit-scrollbar-thumb:active {
            background: #EF4C4D; /* Merah solid murni saat kasir melakukan drag */
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
            <h1>Pembuatan Invoice Manual</h1>
            <p style="font-size: 0.85rem; opacity: 0.8;">Modul POS: Gabungkan item, kalkulasi diskon kustom, dan cetak lembar invoice fisik tanpa antrean sistem online.</p>
        </div>

        <form action="proses_invoice_manual.php" method="POST" id="formManualInvoice" onsubmit="return validasiSebelumKirim();">
            <div class="invoice-grid-split">
                
                <div class="glass-card flex-column" style="height: fit-content; overflow: visible;">
                    <h4 style="font-size: 1rem; border-bottom: 1px solid var(--glass-border); padding-bottom: 10px; margin-bottom: 20px; margin-top: 0px;">
                        <i class="fas fa-file-invoice me-2"></i> Metadata Invoice
                    </h4>

                    <div class="form-group">
                        <label class="label-text">Nomor Invoice (4 Segmen)</label>
                        <div class="invoice-segment-container">
                            <input type="text" name="invoice_prefix" class="input-glass segment-input-short" value="INV" placeholder="XXXX" required style="text-transform: uppercase;">
                            <span class="invoice-segment-dash">-</span>
                            <input type="text" name="invoice_brand" class="input-glass segment-input-short" value="NR" placeholder="NR" required style="text-transform: uppercase;">
                            <span class="invoice-segment-dash">-</span>
                            <input type="text" class="input-glass segment-input-medium" value="<?= $invoice_date_part; ?>" disabled>
                            <input type="hidden" name="invoice_date" value="<?= $invoice_date_part; ?>">
                            <span class="invoice-segment-dash">-</span>
                            <input type="text" class="input-glass segment-input-short" value="<?= $invoice_rand_part; ?>" disabled>
                            <input type="hidden" name="invoice_rand" value="<?= $invoice_rand_part; ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="label-text">Akun Pelanggan (Member)</label>
                        <div class="custom-select-wrapper" id="memberDropdown">
                            <input type="hidden" name="member_id" id="selected_member_id" value="0" required>
                            <div class="custom-select-trigger">
                                <span id="member_trigger_text">-- Non-Member (Umum / Guest) --</span>
                                <i class="fas fa-chevron-down custom-select-arrow"></i>
                            </div>
                            <div class="custom-options" style="min-width: 0%;">
                                <div class="custom-option selected" data-value="0">-- Non-Member (Umum / Guest) --</div>
                                <?php 
                                $members_query = mysqli_query($conn, "SELECT id, nama_lengkap FROM users_member ORDER BY nama_lengkap ASC");
                                while($mbr = mysqli_fetch_assoc($members_query)):
                                ?>
                                    <div class="custom-option" data-value="<?= $mbr['id']; ?>"><?= htmlspecialchars($mbr['nama_lengkap']); ?> (#<?= $mbr['id']; ?>)</div>
                                <?php endwhile; ?>
                            </div>
                        </div>

                        <div id="containerGuestManualName" class="guest-input-container show">
                            <div style="margin-bottom: 15px;">
                                <label class="label-text" style="color: #ff7675;">Nama Pelanggan Kustom</label>
                                <input type="text" name="guest_name_manual" id="inputGuestNameManual" class="input-glass" placeholder="Masukkan nama pembeli...">
                            </div>
                            <div>
                                <label class="label-text" style="color: #ff7675;">Alamat Pelanggan Kustom</label>
                                <textarea name="guest_address_manual" id="inputGuestAddressManual" class="input-glass" placeholder="Masukkan alamat lengkap pembeli..." style="resize: none; height: 70px; font-family: inherit;"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="label-text">Potongan Diskon Akhir (Rp)</label>
                        <input type="number" name="discount_nominal" id="inputDiscount" class="input-glass" value="0" min="0" oninput="hitungKalkulasiTotal()" onchange="hitungKalkulasiTotal()">
                    </div>

                    <div class="form-group">
                        <label class="label-text">Catatan Khusus Petugas</label>
                        <textarea name="invoice_notes" id="inputInvoiceNotes" class="input-glass" placeholder="Tulis instruksi khusus kasir/pembayaran disini..." style="resize: none; height: 65px; font-family: inherit; font-size: 0.85rem;"></textarea>
                    </div>
                    
                    <div class="form-group" style="margin-top: 10px;">
                        <label class="label-text" style="color: #4cd137;">Ringkasan Tagihan</label>
                        <div style="background: rgba(0,0,0,0.2); padding: 15px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.05);">
                            <div style="display:flex; justify-content:space-between; font-size:0.8rem; opacity:0.7; margin-bottom: 8px;">
                                <span>Subtotal:</span> <span id="labelSubtotal">Rp 0</span>
                            </div>
                            <div style="display:flex; justify-content:space-between; font-size:0.8rem; color:#ef4c4d; margin-bottom: 12px;">
                                <span>Diskon:</span> <span id="labelDiscount">- Rp 0</span>
                            </div>
                            <div style="display:flex; justify-content:space-between; font-size:1.1rem; font-weight:800; border-top:1px dashed rgba(255,255,255,0.1); padding-top:10px;">
                                <span>TOTAL:</span> <span id="labelTotalAkhir" style="color:#2ecc71;">Rp 0</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- <div class="glass-card" style="overflow: visible;"> -->
                <div class="glass-card" style="overflow: hidden;">
                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--glass-border); padding-bottom: 10px; margin-bottom: 20px;">
                        <h4 style="margin: 0; font-size: 1rem;"><i class="fas fa-shopping-basket me-2"></i> Rincian Item Transaksi</h4>
                        <button type="button" class="btn-action-premium" style="background: #ffc107; color: #111; padding: 6px 12px; border-radius: 8px; font-size: 0.7rem;" onclick="tambahBarisItem()">
                            <i class="fas fa-plus"></i> Tambah Item
                        </button>
                    </div>

                    <!-- <div class="table-responsive" style="overflow: visible;"> -->
                    <div class="table-scroll-x">
                        <table class="table-pos-invoice" id="tablePosItems">
                            <thead>
                                <tr>
                                    <th>Pilih Produk Katalog</th>
                                    <th width="110">Harga</th>
                                    <th width="70">Qty</th>
                                    <th width="110">Total</th>
                                    <th width="140">Keterangan / Catatan Item</th>
                                    <th width="45">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="invoiceItemRowsContainer">
                                <tr class="pos-item-row">
                                    <td style="overflow: visible;">
                                        <div class="custom-select-wrapper row-product-dropdown">
                                            <input type="hidden" name="product_ids[]" class="raw-product-id-value" value="" required>
                                            <div class="custom-select-trigger">
                                                <span>-- Pilih Item --</span>
                                                <i class="fas fa-chevron-down custom-select-arrow"></i>
                                            </div>
                                            <div class="custom-options">
                                                <div class="custom-option" data-value="" data-price="0">-- Pilih Item --</div>
                                                <?php 
                                                $catalog_query = mysqli_query($conn, "SELECT id, product_name, price FROM site_products_promo WHERE is_active = 1 ORDER BY product_name ASC");
                                                while($cat = mysqli_fetch_assoc($catalog_query)):
                                                ?>
                                                    <div class="custom-option" data-value="<?= $cat['id']; ?>" data-price="<?= $cat['price']; ?>"><?= htmlspecialchars($cat['product_name']); ?></div>
                                                <?php endwhile; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" class="input-glass input-row-price-label" value="Rp 0" disabled style="text-align: center; padding: 10px 5px;">
                                    </td>
                                    <td>
                                        <input type="number" name="qtys[]" class="input-glass input-row-qty" value="1" min="1" oninput="hitungHargaRow(this)" style="text-align: center; padding: 10px 5px;" required>
                                    </td>
                                    <td>
                                        <input type="text" class="input-glass input-row-subtotal-label" value="Rp 0" disabled style="text-align: center; font-weight: 700; color: #74b9ff; padding: 10px 5px;">
                                        <input type="hidden" class="raw-row-subtotal-value" value="0">
                                    </td>
                                    <td>
                                        <input type="text" name="item_notes[]" class="input-glass" placeholder="Contoh: Keterangan spesifikasi">
                                    </td>
                                    <td>
                                        <button type="button" class="btn-remove-row" onclick="hapusBarisItem(this)"><i class="fas fa-times"></i></button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div style="margin-top: 30px; display: flex; justify-content: flex-end; gap: 12px;">
                        <button type="button" class="btn-action-premium" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff;" onclick="window.location.href='admin_dashboard.php'">BATAL</button>
                        <button type="submit" class="btn-action-premium" style="background: var(--accent); color: white; flex: 1;">
                            <i class="fas fa-print"></i> SIMPAN & CETAK INVOICE MANUAL
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <div class="glass-card mt-4">
            <h4 style="font-size: 1rem; border-bottom: 1px solid var(--glass-border); padding-bottom: 15px; margin-bottom: 20px; margin-top: 0px;">
                <i class="fas fa-history me-2"></i> Riwayat Invoice Manual Terbit
            </h4>
            
            <div class="table-responsive">
                <table class="table-pos-invoice" style="table-layout: auto;">
                    <thead>
                        <tr>
                            <th style="text-align: left; padding-left: 15px;">Nomor Invoice</th>
                            <th style="text-align: left;">Nama Pelanggan</th>
                            <th style="text-align: center;">Tanggal Transaksi</th>
                            <th style="text-align: right;">Diskon</th>
                            <th style="text-align: right; padding-right: 15px;">Total Akhir</th>
                            <th style="text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $q_history = mysqli_query($conn, "SELECT o.*, m.nama_lengkap 
                                                          FROM orders o 
                                                          LEFT JOIN users_member m ON o.member_id = m.id 
                                                          WHERE o.invoice_number LIKE '%%-%%-%%-%%'
                                                          ORDER BY o.id DESC");
                        
                        if(mysqli_num_rows($q_history) > 0):
                            while($hist = mysqli_fetch_assoc($q_history)):
                                // REVISI VISUAL BADGE: Mengubah "MANUAL NON-SISTEM" menjadi "NON-MEMBER"
                                if(!empty($hist['nama_lengkap'])) {
                                    $display_customer = htmlspecialchars($hist['nama_lengkap']);
                                    $badge = '';
                                } else {
                                    $display_customer = !empty($hist['guest_name']) ? htmlspecialchars($hist['guest_name']) : 'Tanpa Nama';
                                    $badge = '<span class="manual-badge">NON-MEMBER</span>';
                                }
                        ?>
                            <tr>
                                <td style="font-weight: 700; color: var(--accent); padding-left: 15px;"><?= $hist['invoice_number']; ?></td>
                                <td><?= $display_customer . $badge; ?></td>
                                <td style="text-align: center; opacity: 0.8;"><?= date('d M Y, H:i', strtotime($hist['created_at'])); ?></td>
                                <td style="text-align: right; color: #ef4c4d;">Rp <?= number_format($hist['discount'], 0, ',', '.'); ?></td>
                                <td style="text-align: right; font-weight: bold; color: #2ecc71; padding-right: 15px;">Rp <?= number_format($hist['total_price'], 0, ',', '.'); ?></td>
                                <td style="text-align: center;">
                                    <a href="print_invoice_manual.php?id=<?= $hist['id']; ?>" target="_blank" class="btn-print-row" title="Cetak Ulang Invoice">
                                        <i class="fas fa-print"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php 
                            endwhile; 
                        else: 
                        ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 40px 0; opacity: 0.4;">
                                    <i class="fas fa-folder-open fa-2x mb-2 d-block"></i> Belum ada riwayat transaksi POS manual yang terekam.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div id="addProductModal" class="modal-overlay-glass">
        <div class="modal-content-card">
            <div class="modal-header-naufaru">
                <span><i class="fas fa-cart-plus me-2"></i> PILIHAN KATALOG PRODUK</span>
                <button type="button" class="btn-close-modal" onclick="closeAddProductModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="info-box-modal-small">
                <p><i class="fas fa-info-circle me-1" style="color:#3498db;"></i> Pilih produk aktif di bawah ini untuk dimasukkan ke dalam baris rincian transaksi Kasir.</p>
            </div>

            <div class="form-group-modal" style="margin-bottom: 15px;">
                <label class="label-modal">Daftar Produk Tersedia</label>
                <div class="modal-product-list-scroll" style="display: flex; flex-direction: column; gap: 10px; max-height: 260px; overflow-y: auto; padding-right: 10px;">
                    <?php 
                    // Mengambil ulang catalog murni untuk pilihan di dalam modal menu
                    $catalog_modal_query = mysqli_query($conn, "SELECT id, product_name, price FROM site_products_promo WHERE is_active = 1 ORDER BY product_name ASC");
                    while($c_mod = mysqli_fetch_assoc($catalog_modal_query)):
                    ?>
                        <button type="button" class="btn-modal-select-item" 
                                data-value="<?= $c_mod['id'] ?>" 
                                data-price="<?= $c_mod['price'] ?>"
                                style="text-align: left; background: rgba(46, 204, 113, 0.05); border: 1px solid rgba(46, 204, 113, 0.2); padding: 14px 15px; color: #2ecc71; border-radius: 12px; cursor: pointer; transition: 0.2s; font-size: 0.85rem; font-weight: 600; display: flex; justify-content: space-between; align-items: center;">
                            <span><i class="fas fa-box me-2" style="color: #ffc107;"></i> <?= htmlspecialchars($c_mod['product_name']) ?></span>
                            <b style="color: #fff;">Rp <?= number_format($c_mod['price'], 0, ',', '.') ?></b>
                        </button>
                    <?php endwhile; ?>
                </div>
            </div>

            <div style="margin-top: 25px;">
                <button type="button" onclick="closeAddProductModal()" class="btn-action" style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 12px; color: #fff; font-size: 0.75rem; font-weight: bold; cursor: pointer;">BATAL</button>
            </div>
        </div>
    </div>

    <script src="admin_script.js"></script>
    <script>
        // Global pointer untuk mencatat baris mana yang sedang melakukan pemilihan produk
        let currentActiveRowPointer = null;

        // Aksi ketika kasir menekan kotak pemicu pilih produk di tabel POS
        // --- FIX: Mengaktifkan kembali Trigger klik Dropdown Member Utama ---
        $(document).on('click', '#memberDropdown .custom-select-trigger', function(e) {
            e.stopPropagation();
            const wrapper = $(this).closest('.custom-select-wrapper');
            // Tutup select trigger lain jika ada yang terbuka
            $('.custom-select-wrapper').not(wrapper).removeClass('open');
            wrapper.toggleClass('open');
        });

        // Aksi ketika kasir menekan kotak pemicu pilih produk di tabel POS (Membuka Modal Premium)
        $(document).on('click', '.row-product-dropdown .custom-select-trigger', function(e) {
            e.stopPropagation();
            
            // Simpan target baris tr bersangkutan ke dalam pointer global
            currentActiveRowPointer = $(this).closest('tr');
            
            // Buka jendela modal dengan animasi zoom in bawaan style NaufaRu
            const modal = document.getElementById('addProductModal');
            const content = modal.querySelector('.modal-content-card');
            content.style.animation = "popupZoomIn 0.4s cubic-bezier(0.165, 0.84, 0.44, 1)";
            modal.style.display = 'flex';
        });

        // Aksi ketika salah satu produk di dalam modal di-klik oleh Kasir
        $(document).on('click', '.btn-modal-select-item', function(e) {
            e.stopPropagation();
            
            if (currentActiveRowPointer) {
                const productId = $(this).data('value');
                const productPrice = parseInt($(this).data('price')) || 0;
                
                // PERBAIKAN: Mengambil teks murni dari span tanpa ikut membawa elemen ikon (<i>) di dalamnya
                const productName = $(this).find('span').clone().children('i').remove().end().text().trim();
                
                // Salurkan nilai produk terpilih langsung ke baris tabel kasir yang aktif
                currentActiveRowPointer.find('.raw-product-id-value').val(productId);
                currentActiveRowPointer.find('.custom-select-trigger span').text(productName);
                
                // Simpan harga angka murni ke atribut data-price-raw agar bisa dihitung JavaScript
                const priceInput = currentActiveRowPointer.find('.input-row-price-label');
                priceInput.val("Rp " + productPrice.toLocaleString('id-ID'));
                priceInput.attr('data-price-raw', productPrice);
                
                // Ambil elemen input Qty dari baris aktif saat ini
                const qtyInput = currentActiveRowPointer.find('.input-row-qty')[0];
                
                // Jalankan kalkulasi total otomatis murni berdasarkan baris yang sedang aktif
                if (qtyInput) {
                    hitungHargaRow(qtyInput);
                } else {
                    // Fallback safety check jika elemen tidak terdeteksi langsung
                    hitungKalkulasiTotal();
                }
                
                // Berikan feedback visual border hijau terang pertanda sukses terhubung
                currentActiveRowPointer.find('.custom-select-trigger').css('border-color', '#2ecc71');
            }
            
            // Tutup modal
            closeAddProductModal();
        });

        // Fungsi penutup modal dengan transisi animasi Zoom Out halus
        function closeAddProductModal() {
            const modal = document.getElementById('addProductModal');
            if (!modal) return;
            const content = modal.querySelector('.modal-content-card');
            
            content.style.animation = "popupZoomOut 0.3s ease forwards";
            
            setTimeout(() => {
                modal.style.display = 'none';
                content.style.animation = ""; 
                currentActiveRowPointer = null; // bersihkan pointer cache memory
            }, 300);
        }

        // CSS Hover Effect dinamis untuk tombol pilihan di dalam Popup Modal
        $(document).on({
            mouseenter: function () { $(this).css({'background': 'rgba(239, 76, 77, 0.15)', 'border-color': '#ef4c4d', 'padding-left': '20px'}); },
            mouseleave: function () { $(this).css({'background': 'rgba(255,255,255,0.03)', 'border-color': 'rgba(255,255,255,0.1)', 'padding-left': '15px'}); }
        }, '.btn-popup-select-item');

        $(document).on('click', '.custom-option', function(e) {
            e.stopPropagation();
            const value = $(this).data('value');
            const text = $(this).text();
            const wrapper = $(this).closest('.custom-select-wrapper');
            
            wrapper.find('.custom-option').removeClass('selected');
            $(this).addClass('selected');
            wrapper.removeClass('open');
            wrapper.find('.custom-select-trigger span').text(text);
            wrapper.find('input[type="hidden"]').val(value);

            // LOGIKA DINAMIS: Cek apakah yang diklik adalah Dropdown Member utama
            if (wrapper.attr('id') === 'memberDropdown') {
                if (value == "0") {
                    $('#containerGuestManualName').addClass('show');
                    $('#inputGuestNameManual').focus();
                } else {
                    $('#containerGuestManualName').removeClass('show');
                    $('#inputGuestNameManual, #inputGuestAddressManual').val('');
                }
            }

            if(wrapper.hasClass('row-product-dropdown')) {
                const price = parseInt($(this).data('price')) || 0;
                const row = wrapper.closest('tr');
                row.find('.input-row-price-label').val("Rp " + price.toLocaleString('id-ID'));
                hitungHargaRow(row.find('.input-row-qty')[0]);
            }
        });

        $(document).on('click', function() {
            $('.custom-select-wrapper').removeClass('open');
        });

        // --- SISTEM VALIDASI KOTAK KOSONG FORM SEBELUM SUBMIT ---
        function validasiSebelumKirim() {
            let validProduk = true;
            $('.raw-product-id-value').each(function() {
                if ($(this).val() === "" || $(this).val() === null) {
                    validProduk = false;
                }
            });

            if (!validProduk) {
                tampilkanAlertStatus('failed_empty_items');
                return false;
            }

            // PERBAIKAN: Validasi ganda nama dan alamat manual kasir jika Non-Member
            const idMember = $('#selected_member_id').val();
            const namaKustom = $('#inputGuestNameManual').val().trim();
            const alamatKustom = $('#inputGuestAddressManual').val().trim();
            
            if (idMember == "0") {
                if (namaKustom === "") {
                    Swal.fire({ icon: 'warning', title: 'Nama Pembeli Kosong!', text: 'Harap isi nama pelanggan manual untuk transaksi Non-Member.', background: '#1a1a1a', color: '#fff', confirmButtonColor: '#ef4c4d' });
                    $('#inputGuestNameManual').focus();
                    return false;
                }
                if (alamatKustom === "") {
                    Swal.fire({ icon: 'warning', title: 'Alamat Pembeli Kosong!', text: 'Harap isi alamat lengkap pelanggan manual untuk transaksi Non-Member.', background: '#1a1a1a', color: '#fff', confirmButtonColor: '#ef4c4d' });
                    $('#inputGuestAddressManual').focus();
                    return false;
                }
            }

            return true;
        }

        // --- MANAJEMEN BARIS TABEL POS ---
        function tambahBarisItem() {
            const cleanRowTemplate = `
                <tr class="pos-item-row">
                    <td style="overflow: visible;">
                        <div class="custom-select-wrapper row-product-dropdown">
                            <input type="hidden" name="product_ids[]" class="raw-product-id-value" value="" required>
                            <div class="custom-select-trigger"><span>-- Pilih Item --</span> <i class="fas fa-chevron-down custom-select-arrow"></i></div>
                            <div class="custom-options">
                                <div class="custom-option" data-value="" data-price="0">-- Pilih Item --</div>
                                <?php 
                                mysqli_data_seek($catalog_query, 0);
                                while($cat = mysqli_fetch_assoc($catalog_query)):
                                ?>
                                    <div class="custom-option" data-value="<?= $cat['id']; ?>" data-price="<?= $cat['price']; ?>"><?= htmlspecialchars($cat['product_name']); ?></div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    </td>
                    <td><input type="text" class="input-glass input-row-price-label" value="Rp 0" disabled style="text-align: center; padding: 10px 5px;"></td>
                    <td><input type="number" name="qtys[]" class="input-glass input-row-qty" value="1" min="1" oninput="hitungHargaRow(this)" style="text-align: center; padding: 10px 5px;" required></td>
                    <td>
                        <input type="text" class="input-glass input-row-subtotal-label" value="Rp 0" disabled style="text-align: center; font-weight: 700; color: #74b9ff; padding: 10px 5px;">
                        <input type="hidden" class="raw-row-subtotal-value" value="0">
                    </td>
                    <td><input type="text" name="item_notes[]" class="input-glass input-row-note" placeholder="Contoh: Keterangan spesifikasi" style="padding: 10px 12px; font-size: 0.8rem;"></td>
                    <td><button type="button" class="btn-remove-row" onclick="hapusBarisItem(this)"><i class="fas fa-times"></i></button></td>
                </tr>`;

            $('#invoiceItemRowsContainer').append(cleanRowTemplate);
        }

        function hapusBarisItem(button) {
            if ($('.pos-item-row').length > 1) {
                $(button).closest('tr').remove();
                hitungKalkulasiTotal();
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: 'Minimal harus menyisakan 1 item transaksi.', background: '#1a1a1a', color: '#fff', confirmButtonColor: '#ef4c4d' });
            }
        }

        function hitungHargaRow(qtyInput) {
            const row = qtyInput.closest('tr');
            
            // Ambil harga asli angka dari atribut custom data-price-raw di element input harga
            const priceInput = row.querySelector('.input-row-price-label');
            const price = parseInt(priceInput.getAttribute('data-price-raw')) || 0;
            
            const qty = parseInt(qtyInput.value) || 1;
            
            const subtotal = price * qty;
            row.querySelector('.input-row-subtotal-label').value = "Rp " + subtotal.toLocaleString('id-ID');
            row.querySelector('.raw-row-subtotal-value').value = subtotal;
            
            hitungKalkulasiTotal();
        }

        function hitungKalkulasiTotal() {
            let subtotalInvoice = 0;
            document.querySelectorAll('.raw-row-subtotal-value').forEach(input => {
                subtotalInvoice += parseInt(input.value) || 0;
            });

            const discount = parseInt(document.getElementById('inputDiscount').value) || 0;
            let totalAkhir = subtotalInvoice - discount;
            if (totalAkhir < 0) totalAkhir = 0;

            document.getElementById('labelSubtotal').innerText = "Rp " + subtotalInvoice.toLocaleString('id-ID');
            document.getElementById('labelDiscount').innerText = "- Rp " + discount.toLocaleString('id-ID');
            document.getElementById('labelTotalAkhir').innerText = "Rp " + totalAkhir.toLocaleString('id-ID');
        }

        // --- SISTEM CENTRALIZED POPUP NOTIFIKASI ---
        function tampilkanAlertStatus(status) {
            let config = {
                timer: 2500,
                showConfirmButton: false,
                timerProgressBar: true,
                background: '#1a1a1a',
                color: '#fff',
                confirmButtonColor: '#ef4c4d'
            };

            if (status === 'success_invoice') {
                config.icon = 'success';
                config.title = 'Invoice Terbuat!';
                config.text = 'Data transaksi manual berhasil disimpan.';
                
                <?php if (isset($_SESSION['print_manual_invoice_id'])): ?>
                    const printId = "<?= $_SESSION['print_manual_invoice_id']; ?>";
                    window.open('print_invoice_manual.php?id=' + printId, '_blank');
                    <?php unset($_SESSION['print_manual_invoice_id']); ?>
                <?php endif; ?>
            } else if (status === 'failed_empty_items') {
                config.icon = 'warning';
                config.title = 'Kotak Belum Terisi!';
                config.text = 'Silakan pilih jenis produk katalog pada tabel rincian transaksi sebelum menyimpan.';
                config.showConfirmButton = true;
                config.timer = null;
            }

            if(status) {
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