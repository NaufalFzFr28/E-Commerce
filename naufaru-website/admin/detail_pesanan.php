<?php
session_start();
include 'cek_login.php'; 
include '../config.php';
include '../functions.php';

$order_id = mysqli_real_escape_string($conn, $_GET['id']);

// 1. Ambil Data Header Pesanan & Member
$query_order = mysqli_query($conn, "SELECT o.*, m.nama_lengkap, m.no_hp, m.alamat 
                                    FROM orders o 
                                    JOIN users_member m ON o.member_id = m.id 
                                    WHERE o.id = '$order_id'");
$order = mysqli_fetch_assoc($query_order);

if (!$order) {
    die("Pesanan tidak ditemukan.");
}

$is_locked = ($order['status'] == 'Finished');

// --- LOGIKA PEMROSESAN DATA ---

if (!$is_locked) {
    
    // A. LOGIKA UPDATE QTY & CATATAN & DISKON (Satu Pintu)
    if (isset($_POST['update_order'])) {
        foreach ($_POST['qty'] as $item_id => $new_qty) {
            $item_id = mysqli_real_escape_string($conn, $item_id);
            $new_qty = (int)$new_qty;
            $c_item = mysqli_real_escape_string($conn, $_POST['catatan_item'][$item_id] ?? '');

            if ($new_qty > 0) {
                mysqli_query($conn, "UPDATE order_items SET qty = '$new_qty', catatan_item = '$c_item' WHERE id = '$item_id'");
            }
        }

        $discount = mysqli_real_escape_string($conn, $_POST['discount'] ?? 0);
        $catatan_umum = mysqli_real_escape_string($conn, $_POST['catatan'] ?? '');

        // Hitung ulang total belanja
        $q_calc = mysqli_query($conn, "SELECT SUM(qty * price_at_order) as grand_total FROM order_items WHERE order_id = '$order_id'");
        $item_total = mysqli_fetch_assoc($q_calc)['grand_total'] ?? 0;
        $final_total = $item_total - $discount;

        mysqli_query($conn, "UPDATE orders SET total_price = '$final_total', discount = '$discount', catatan = '$catatan_umum' WHERE id = '$order_id'");
        
        header("Location: detail_pesanan.php?id=$order_id&msg=updated");
        exit;
    }

    // B. LOGIKA HAPUS ITEM (Proteksi Minimal 1 Produk)
    if (isset($_POST['delete_item'])) {
        $item_id = mysqli_real_escape_string($conn, $_POST['delete_item']);
        
        // Cek jumlah item saat ini
        $q_check = mysqli_query($conn, "SELECT COUNT(*) as total FROM order_items WHERE order_id = '$order_id'");
        $count = mysqli_fetch_assoc($q_check)['total'];

        if ($count > 1) {
            mysqli_query($conn, "DELETE FROM order_items WHERE id = '$item_id'");
            
            // Recalculate Total
            $q_calc = mysqli_query($conn, "SELECT SUM(qty * price_at_order) as grand_total FROM order_items WHERE order_id = '$order_id'");
            $item_total = mysqli_fetch_assoc($q_calc)['grand_total'] ?? 0;
            $final_total = $item_total - $order['discount'];
            mysqli_query($conn, "UPDATE orders SET total_price = '$final_total' WHERE id = '$order_id'");

            header("Location: detail_pesanan.php?id=$order_id&msg=item_deleted");
        } else {
            header("Location: detail_pesanan.php?id=$order_id&msg=error_min_item");
        }
        exit;
    }

    // C. LOGIKA TAMBAH PRODUK BARU
    if (isset($_POST['add_new_item'])) {
        $new_product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
        $new_qty = (int)$_POST['new_qty'];
        
        $q_p = mysqli_query($conn, "SELECT price FROM site_products_promo WHERE id = '$new_product_id'");
        $p_data = mysqli_fetch_assoc($q_p);
        $current_price = $p_data['price'];

        mysqli_query($conn, "INSERT INTO order_items (order_id, product_id, qty, price_at_order) 
                            VALUES ('$order_id', '$new_product_id', '$new_qty', '$current_price')");

        // Recalculate Total
        $q_calc = mysqli_query($conn, "SELECT SUM(qty * price_at_order) as grand_total FROM order_items WHERE order_id = '$order_id'");
        $item_total = mysqli_fetch_assoc($q_calc)['grand_total'] ?? 0;
        $final_total = $item_total - $order['discount'];
        mysqli_query($conn, "UPDATE orders SET total_price = '$final_total' WHERE id = '$order_id'");

        header("Location: detail_pesanan.php?id=$order_id&msg=item_added");
        exit;
    }
}

// 3. Logika Generate Invoice (Kunci Pesanan & Simpan No. Invoice Manual)
if (isset($_POST['generate_invoice']) && !$is_locked) {
    // Tangkap nomor manual dari modal SweetAlert
    $manual_no = mysqli_real_escape_string($conn, $_POST['manual_order_number']);
    $system_date = date('Y-m-d'); 

    // Update status dan simpan nomor invoice manual
    mysqli_query($conn, "UPDATE orders SET 
        status = 'Finished', 
        is_invoice = 1, 
        invoice_number = '$manual_no', 
        invoice_date = '$system_date' 
        WHERE id = '$order_id'");

    header("Location: detail_pesanan.php?id=$order_id&msg=finalized");
    exit;
}

$q_pending = mysqli_query($conn, "SELECT COUNT(*) as total FROM orders WHERE status = 'Pending'");
$total_pending = mysqli_fetch_assoc($q_pending)['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NaufaRu Admin | Detail Pesanan #<?= $order['order_number'] ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="admin_style.css">
    <style>
        /* --- Style Tombol Kembali (Absolute Position) --- */
        .welcome-card {
            position: relative; /* Penting untuk koordinat tombol */
        }
        
        .header-info {
            padding-right: 200px; /* Mencegah teks bertumpuk dengan tombol */
        }

        .btn-back-naufaru {
            position: absolute;
            right: 25px; /* Jarak dari sisi kanan kartu */
            bottom: 22px; /* Posisi di area kuning yang Anda tandai */
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #fff;
            padding: 8px 18px;
            border-radius: 10px;
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 600;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            white-space: nowrap;
            z-index: 10;
        }
        
        .btn-back-naufaru:hover {
            background: #EF4C4D;
            border-color: #EF4C4D;
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(239, 76, 77, 0.3);
        }

        /* --- Style Info Shape --- */
        .info-shape-card {
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            padding: 15px;
            border-radius: 18px;
            margin-bottom: 15px;
            gap: 15px;
        }
        .shape-icon-box {
            width: 45px; height: 45px;
            background: rgba(239, 76, 77, 0.15);
            color: #EF4C4D;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        /* --- Table & Form Styles --- */
        .table-detail { width: 100%; border-collapse: collapse; color: #fff; }
        .table-detail th { padding: 15px; border-bottom: 1px solid rgba(255,255,255,0.1); text-align: left; opacity: 0.5; font-size: 0.8rem; text-transform: uppercase; }
        .table-detail td { padding: 15px; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .input-qty { width: 65px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.2); color: #fff; text-align: center; border-radius: 8px; padding: 5px; }
        .input-discount { background: rgba(46, 204, 113, 0.05); border: 1px solid rgba(46, 204, 113, 0.3); color: #2ecc71; font-weight: 800; width: 90%; text-align: right; padding: 8px; border-radius: 8px; }
        
        /* --- Area Kalkulasi (Peningkatan Jarak) --- */
        .summary-container {
            border-left: 3px solid #EF4C4D;
            padding-left: 25px;
            max-width: 450px;
            margin-left: auto;
        }
        .summary-row { 
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            margin-bottom: 18px; /* Jarak antar konten diperlebar */
        }
        .final-total { 
            font-size: 1.6rem; 
            color: #EF4C4D; 
            font-weight: 900; 
            border-top: 1px solid rgba(255,255,255,0.1); 
            margin-top: 20px; 
            padding-top: 20px; 
        }
        .calculation-area {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-top: 40px;
            gap: 30px;
        }
        
        .admin-note-section {
            flex: 1;
            background: rgba(255, 255, 255, 0.02);
            border: 1px dashed rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 15px;
        }

        .admin-note-section label {
            display: block;
            color: #EF4C4D;
            font-weight: 700;
            font-size: 0.75rem;
            text-transform: uppercase;
            margin-bottom: 10px;
            letter-spacing: 1px;
        }

        .note-textarea {
            width: 100%;
            background: transparent;
            border: none;
            color: rgba(255,255,255,0.8);
            font-size: 0.85rem;
            resize: none;
            outline: none;
            min-height: 100px;
            line-height: 1.6;
        }

        /* Penyesuaian lebar container total agar tetap di kanan */
        .summary-container {
            width: 380px;
            border-left: 3px solid #EF4C4D;
            padding-left: 25px;
        }

        .input-discount option {
            background: #1a1a1a; /* Latar belakang dropdown agar teks putih terbaca */
            color: #fff;
        }

        .modal-overlay-glass {
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* --- Animasi Popup --- */
        @keyframes popupZoomIn { from { opacity: 0; transform: scale(0.9); } to { opacity: 1; transform: scale(1); } }
        @keyframes popupZoomOut { from { opacity: 1; transform: scale(1); } to { opacity: 0; transform: scale(0.9); } }

        .modal-overlay-glass {
            display: none; position: fixed; top:0; left:0; width:100%; height:100%; 
            background: rgba(0,0,0,0.35); z-index: 9999; justify-content: center; 
            align-items: center; backdrop-filter: blur(15px);
        }

        .modal-content-card {
            width: 450px; /* Ukuran lebar modal lebih compact */
            padding: 30px; 
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1); 
            border-radius: 24px;
            position: relative; 
            animation: popupZoomIn 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        }

        /* Header Modal dengan Border Bottom sesuai permintaan */
        .modal-header-naufaru {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 1rem;
            color: #EF4C4D;
            font-weight: 900;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1); /* style: var(--glass-border) */
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .btn-close-modal {
            background: rgba(255, 255, 255, 0.05);
            border: none; color: #fff; width: 28px; height: 28px; 
            border-radius: 50%; cursor: pointer;
            display: flex; align-items: center; justify-content: center; font-size: 0.8rem;
        }

        /* Div Info Lebih Kecil */
        .info-box-modal-small {
            background: rgba(52, 152, 219, 0.08);
            border-left: 3px solid #3498db;
            padding: 8px 12px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .info-box-modal-small p {
            margin: 0; font-size: 0.75rem; /* Ukuran teks lebih kecil */
            color: rgba(255,255,255,0.6); line-height: 1.4;
        }

        .label-modal { 
            display: block; color: #EF4C4D; font-size: 0.7rem; 
            font-weight: 800; text-transform: uppercase; 
            margin-bottom: 8px; letter-spacing: 1px; 
        }

        /* --- Custom Select Fix (Lebar Presisi) --- */
        .custom-select-wrapper { 
            position: relative; 
            width: 100%; 
            box-sizing: border-box; /* Mencegah pelebaran */
        }

        .custom-select-trigger {
            height: 48px; background: rgba(46, 204, 113, 0.05); 
            border: 1px solid rgba(46, 204, 113, 0.3);
            color: #2ecc71; border-radius: 12px; padding: 0 15px; 
            font-weight: 600; display: flex; align-items: center; 
            justify-content: space-between; cursor: pointer; font-size: 0.85rem;
        }

        .custom-options {
            position: absolute; top: calc(100% + 5px); left: 0; right: 0;
            background: rgba(20, 20, 20, 0.95) !important; backdrop-filter: blur(20px) !important;
            border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 12px;
            padding: 5px; display: none; z-index: 12000;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5); max-height: 180px; overflow-y: auto;
            scrollbar-width: thin; scrollbar-color: rgba(255, 255, 255, 0.1) rgba(255, 255, 255, 0.02);
        }

        .custom-select-wrapper.open .custom-options { display: block; }

        .custom-option {
            padding: 10px 12px; color: #2ecc71; cursor: pointer; 
            border-radius: 8px; font-size: 0.8rem; transition: 0.2s;
        }
        .custom-option:hover { background: rgba(46, 204, 113, 0.1); }

        /* Input Qty Sejajar */
        .input-premium-glass {
            width: 100%; height: 48px; background: rgba(46, 204, 113, 0.05);
            border: 1px solid rgba(46, 204, 113, 0.3); color: #2ecc71;
            border-radius: 12px; padding: 0 15px; font-weight: 600; 
            outline: none; box-sizing: border-box; font-size: 0.9rem;
        }

        /* Menghapus garis bawah pada semua tombol aksi yang menggunakan tag <a> */
        .btn-action {
            text-decoration: none !important;
        }

        /* Memastikan warna teks tetap putih saat di-hover agar underline tidak muncul kembali */
        .btn-action:hover {
            text-decoration: none !important;
            color: white !important;
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
            <a href="main_website.php" class="nav-link"><i class="fas fa-globe"></i> <span class="scramble-text" data-value="Website Utama">Website Utama</span></a>
            <a href="#" class="nav-link"><i class="fas fa-file-alt"></i> <span class="scramble-text" data-value="Curriculum Vitae">Curriculum Vitae</span></a>
            <a href="#" class="nav-link"><i class="fas fa-calendar-check"></i> <span class="scramble-text" data-value="Event Site">Event Site</span></a>
            <a href="admin_katalog.php" class="nav-link"><i class="fas fa-boxes"></i> <span class="scramble-text" data-value="Admin Katalog">Admin Katalog</span></a>
            <a href="admin_fitur.php" class="nav-link"><i class="fas fa-user-cog"></i> <span class="scramble-text" data-value="Admin Fitur">Admin Fitur</span></a>
            
            <a href="kelola_pesanan.php" class="nav-link active">
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
            <div class="header-info">
                <h1 class="m-0">Detail <b>Pesanan</b></h1>
                <p class="m-0 opacity-75">No. Pesanan: <span style="color:#EF4C4D; font-weight:bold; letter-spacing:1px;"><?= $order['order_number'] ?></span></p>
            </div>
            <a href="kelola_pesanan.php" class="btn-back-naufaru">
                <i class="fas fa-arrow-left me-2"></i> Kembali ke Daftar
            </a>
        </div>

        <div class="row" style="margin-top: 25px;">
            <div class="col-md-4">
                <div class="glass-card" style="padding: 25px; height: 100%;">
                    <h4 style="color:#EF4C4D; font-weight:800; margin-bottom: 25px;"><i class="fas fa-user-shield me-2"></i> Data Pelanggan</h4>
                    
                    <div class="info-shape-card">
                        <div class="shape-icon-box"><i class="fas fa-user"></i></div>
                        <div><small style="opacity:0.5; text-transform:uppercase; font-size:0.65rem; display:block;">Nama Lengkap</small><strong><?= htmlspecialchars($order['nama_lengkap']) ?></strong></div>
                    </div>

                    <div class="info-shape-card">
                        <div class="shape-icon-box"><i class="fab fa-whatsapp"></i></div>
                        <div><small style="opacity:0.5; text-transform:uppercase; font-size:0.65rem; display:block;">WhatsApp</small><strong style="color:#2ecc71;"><?= $order['no_hp'] ?></strong></div>
                    </div>

                    <div class="info-shape-card" style="align-items: flex-start;">
                        <div class="shape-icon-box"><i class="fas fa-map-marker-alt"></i></div>
                        <div><small style="opacity:0.5; text-transform:uppercase; font-size:0.65rem; display:block;">Alamat Pengiriman</small><strong style="font-weight:normal; font-size:0.85rem; opacity:0.8;"><?= nl2br(htmlspecialchars($order['alamat'])) ?></strong></div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <form action="" method="POST" id="form-main-update">
                    <div class="glass-card" style="padding: 25px;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 style="color:#EF4C4D; font-weight:800; margin: 0;"><i class="fas fa-box-open me-2"></i> Rincian Item</h4>
                        </div>

                        <table class="table-detail">
                            <thead>
                                <tr>
                                    <th style="width: 25%;">Produk</th>
                                    <th style="text-align:center; width: 10%;">Qty</th>
                                    <th style="text-align:right; width: 15%;">Harga</th>
                                    <th style="text-align:right; width: 15%;">Total</th>
                                    <th style="width: 25%;">Catatan Produk</th>
                                    <?php if(!$is_locked): ?><th style="text-align:center; width: 10%;">Aksi</th><?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $subtotal_items = 0;
                                $items_query = mysqli_query($conn, "SELECT oi.*, p.product_name FROM order_items oi JOIN site_products_promo p ON oi.product_id = p.id WHERE oi.order_id = '$order_id'");
                                $total_rows = mysqli_num_rows($items_query);
                                
                                while($it = mysqli_fetch_assoc($items_query)):
                                    $row_total = $it['qty'] * $it['price_at_order'];
                                    $subtotal_items += $row_total;
                                ?>
                                <tr>
                                    <td class="fw-bold"><?= $it['product_name'] ?></td>
                                    <td style="text-align:center;">
                                        <?php if(!$is_locked): ?>
                                            <input type="number" name="qty[<?= $it['id'] ?>]" value="<?= $it['qty'] ?>" class="input-qty" min="1">
                                        <?php else: ?>
                                            <b><?= $it['qty'] ?></b>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align:right;">Rp <?= number_format($it['price_at_order'], 0, ',', '.') ?></td>
                                    <td style="text-align:right; font-weight:700;">Rp <?= number_format($row_total, 0, ',', '.') ?></td>
                                    <td>
                                        <?php if(!$is_locked): ?>
                                            <input type="text" name="catatan_item[<?= $it['id'] ?>]" value="<?= htmlspecialchars($it['catatan_item'] ?? '') ?>" class="input-discount" style="text-align:left; font-weight:normal; font-size:0.75rem;" placeholder="Ket...">
                                        <?php else: ?>
                                            <small class="opacity-75"><?= htmlspecialchars($it['catatan_item'] ?? '-') ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <?php if(!$is_locked): ?>
                                    <td style="text-align:center;">
                                        <?php if($total_rows > 1): ?>
                                            <button type="button" class="btn-action" 
                                                style="background: rgba(239, 76, 77, 0.1); border-color: #EF4C4D; color: #EF4C4D; padding: 5px 10px;" 
                                                onclick="confirmDelete('<?= $it['id'] ?>', '<?= addslashes($it['product_name']) ?>')">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn-action opacity-25" style="cursor: not-allowed; padding: 5px 10px;" title="Minimal harus ada 1 produk">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                    <?php endif; ?>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>

                        <?php if(!$is_locked): ?>
                        <div class="mt-3">
                            <button type="button" class="btn-action" style="background: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.1); padding: 8px 15px; font-size: 0.8rem; margin-top: 25px;" onclick="openAddProductModal()">
                                <i class="fas fa-plus-circle me-2" style="color: #EF4C4D;"></i> Tambah Produk Lainnya
                            </button>
                        </div>
                        <?php endif; ?>

                        <div class="calculation-area">
                            <div class="admin-note-section">
                                <label><i class="fas fa-sticky-note me-1"></i> Catatan Keseluruhan Pesanan</label>
                                <?php if(!$is_locked): ?>
                                    <textarea name="catatan" class="note-textarea" placeholder="Tulis instruksi umum..."><?= htmlspecialchars($order['catatan'] ?? '') ?></textarea>
                                <?php else: ?>
                                    <div class="note-textarea" style="opacity: 0.7;">
                                        <?= !empty($order['catatan']) ? nl2br(htmlspecialchars($order['catatan'])) : '<i>Tidak ada catatan tambahan.</i>' ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="summary-container">
                                <div class="summary-row">
                                    <span class="opacity-50">Subtotal Belanja</span>
                                    <span class="fw-bold">Rp <?= number_format($subtotal_items, 0, ',', '.') ?></span>
                                </div>
                                <div class="summary-row">
                                    <span class="opacity-50">Diskon (Opsi)</span>
                                    <div style="width: 140px;">
                                        <?php if(!$is_locked): ?>
                                            <input type="number" name="discount" value="<?= (int)$order['discount'] ?>" class="input-discount">
                                        <?php else: ?>
                                            <span class="text-success fw-bold">- Rp <?= number_format($order['discount'], 0, ',', '.') ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="final-total summary-row">
                                    <span>TOTAL AKHIR</span>
                                    <span>Rp <?= number_format($order['total_price'], 0, ',', '.') ?></span>
                                </div>
                            </div>
                        </div>

                        <div style="margin-top: 40px; display: flex; justify-content: flex-end; gap: 15px;">
                            <?php if(!$is_locked): ?>
                                <button type="submit" name="update_order" class="btn-action" style="background: #3498db; border-color: #3498db;">
                                    <i class="fas fa-sync-alt me-2"></i> Update Kalkulasi & Catatan
                                </button>
                                <button type="button" name="generate_invoice_btn" class="btn-action" 
                                    style="background: #2ecc71; border-color: #2ecc71;" 
                                    onclick="confirmGenerateInvoice()">
                                    <i class="fas fa-file-invoice me-2"></i> Terbitkan Invoice
                                </button>

                                <input type="hidden" name="generate_invoice" id="input-trigger-invoice" value="0">
                            <?php else: ?>
                                <a href="invoice_print.php?id=<?= $order_id ?>" target="_blank" class="btn-action text-decoration-none" style="background: #EF4C4D; border-color: #EF4C4D; color: white;">
                                    <i class="fas fa-print me-2"></i> Cetak Invoice (PDF)
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>

                <form id="form-delete-helper" method="POST" style="display:none;">
                    <input type="hidden" name="delete_item" id="input-delete-id">
                </form>
            </div>
        </div>
    </main>

    <div id="addProductModal" class="modal-overlay-glass">
        <div class="modal-content-card">
            <div class="modal-header-naufaru">
                <span><i class="fas fa-cart-plus me-2"></i> TAMBAH ITEM</span>
                <button type="button" class="btn-close-modal" onclick="closeAddProductModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="info-box-modal-small">
                <p><i class="fas fa-info-circle me-1" style="color:#3498db;"></i> Pilih produk aktif untuk rincian pesanan.</p>
            </div>

            <form action="" method="POST" id="formAddItem">
                <input type="hidden" name="product_id" id="selected_product_id" required>
                <input type="hidden" name="add_new_item" value="1">

                <div class="form-group-modal" style="margin-bottom: 15px;">
                    <label class="label-modal">Pilih Produk Katalog</label>
                    <div class="custom-select-wrapper" id="customSelect">
                        <div class="custom-select-trigger">
                            <span id="trigger-text">-- Pilih Produk --</span>
                            <i class="fas fa-chevron-down" style="font-size: 0.7rem;"></i>
                        </div>
                        <div class="custom-options">
                            <?php 
                            $catalog = mysqli_query($conn, "SELECT id, product_name, price FROM site_products_promo WHERE is_active = 1 ORDER BY product_name ASC");
                            while($cat = mysqli_fetch_assoc($catalog)):
                            ?>
                            <div class="custom-option" data-value="<?= $cat['id'] ?>">
                                <?= $cat['product_name'] ?> - <b>Rp <?= number_format($cat['price'], 0, ',', '.') ?></b>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>

                <div class="form-group-modal" style="margin-bottom: 20px;">
                    <label class="label-modal">Jumlah Pembelian (Qty)</label>
                    <input type="number" name="new_qty" class="input-premium-glass" value="1" min="1" required>
                </div>

                <div class="d-flex gap-2" style="margin-top: 30px;">
                    <button type="button" onclick="closeAddProductModal()" class="btn-action" style="flex:1; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); border-radius:10px; font-size: 0.75rem;">BATAL</button>
                    <button type="submit" class="btn-action" style="flex:2; background:#EF4C4D; border-color:#EF4C4D; border-radius:10px; font-size: 0.75rem;">
                        TAMBAHKAN KE PESANAN
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="admin_script.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    /* 1. LOGIKA CUSTOM SELECT (MODAL) */
    // Toggle buka-tutup dropdown
    const selectTrigger = document.querySelector('.custom-select-trigger');
    if (selectTrigger) {
        selectTrigger.addEventListener('click', function(e) {
            e.stopPropagation();
            this.parentElement.classList.toggle('open');
        });
    }

    // Memilih opsi produk
    document.querySelectorAll('.custom-option').forEach(option => {
        option.addEventListener('click', function() {
            const value = this.getAttribute('data-value');
            const text = this.innerText;
            
            document.getElementById('selected_product_id').value = value;
            document.getElementById('trigger-text').innerText = text;
            
            this.closest('.custom-select-wrapper').classList.remove('open');
            // Feedback visual warna hijau saat produk terpilih
            document.querySelector('.custom-select-trigger').style.borderColor = '#2ecc71';
        });
    });

    // Menutup dropdown saat klik di luar area dropdown
    window.addEventListener('click', function() {
        const select = document.getElementById('customSelect');
        if (select) select.classList.remove('open');
    });


    /* 2. LOGIKA MODAL TAMBAH PRODUK */
    function openAddProductModal() {
        const modal = document.getElementById('addProductModal');
        modal.style.display = 'flex';
    }

    function closeAddProductModal() {
        const modal = document.getElementById('addProductModal');
        const content = modal.querySelector('.modal-content-card');
        
        // Animasi zoom out sebelum menutup
        content.style.animation = "popupZoomOut 0.3s ease forwards";
        
        setTimeout(() => {
            modal.style.display = 'none';
            content.style.animation = ""; 
        }, 300);
    }


    /* 3. LOGIKA SWEETALERT: KONFIRMASI HAPUS */
    function confirmDelete(itemId, productName) {
        Swal.fire({
            title: 'Hapus Item?',
            text: `Produk '${productName}' akan dihapus dari rincian pesanan.`,
            icon: 'warning',
            showCancelButton: true,
            background: '#1a1a1a',
            color: '#ffffff',
            confirmButtonColor: '#EF4C4D',
            cancelButtonColor: 'rgba(255,255,255,0.1)',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            backdrop: `rgba(0,0,0,0.6)`
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('input-delete-id').value = itemId;
                document.getElementById('form-delete-helper').submit();
            }
        });
    }


    /* 4. LOGIKA SWEETALERT: KONFIRMASI TERBITKAN INVOICE */
    function confirmGenerateInvoice() {
        // Generate format otomatis sebagai saran (Bisa diubah manual oleh admin)
        const now = new Date();
        const datePart = now.toISOString().split('T')[0].replace(/-/g, ''); // 20260514
        const randomPart = Math.floor(1000 + Math.random() * 9000); // 4 digit random
        const defaultNo = "NR-" + datePart + "-" + randomPart;

        Swal.fire({
            title: 'Terbitkan Invoice',
            html: `
                <p style="font-size:0.85rem; color:#aaa; margin-bottom:15px;">Masukan Nomor Pesanan/Invoice Manual</p>
                <input type="text" id="manual_no" class="swal2-input" 
                    style="background:#111; color:#2ecc71; border:1px solid #333; font-family:monospace;" 
                    value="${defaultNo}">
            `,
            icon: 'question',
            showCancelButton: true,
            background: '#1a1a1a',
            color: '#ffffff',
            confirmButtonColor: '#2ecc71',
            confirmButtonText: 'Ya, Terbitkan!',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            preConfirm: () => {
                const manualNo = Swal.getPopup().querySelector('#manual_no').value;
                if (!manualNo) {
                    Swal.showValidationMessage(`Nomor pesanan wajib diisi!`)
                }
                return { manualNo: manualNo }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Memproses...',
                    text: 'Mengunci data pesanan...',
                    allowOutsideClick: false,
                    background: '#1a1a1a',
                    color: '#fff',
                    didOpen: () => { Swal.showLoading(); }
                });

                // Buat form dinamis untuk submit ke PHP
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '';

                const inputGen = document.createElement('input');
                inputGen.type = 'hidden';
                inputGen.name = 'generate_invoice';
                inputGen.value = '1';

                const inputNo = document.createElement('input');
                inputNo.type = 'hidden';
                inputNo.name = 'manual_order_number';
                inputNo.value = result.value.manualNo;

                form.appendChild(inputGen);
                form.appendChild(inputNo);
                document.body.appendChild(form);
                form.submit();
            }
        });
    }


    /* 5. SISTEM NOTIFIKASI OTOMATIS (URL Params) */
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('msg')) {
        const msg = urlParams.get('msg');
        let config = {
            background: '#1a1a1a',
            color: '#fff',
            timer: 2500,
            showConfirmButton: false,
            backdrop: `rgba(0,0,0,0.4)`
        };

        if (msg === 'item_deleted') {
            Swal.fire({ ...config, icon: 'success', title: 'Terhapus!', text: 'Produk berhasil dihapus.' });
        } else if (msg === 'item_added') {
            Swal.fire({ ...config, icon: 'success', title: 'Berhasil!', text: 'Produk ditambahkan ke rincian.' });
        } else if (msg === 'updated') {
            Swal.fire({ ...config, icon: 'success', title: 'Tersimpan!', text: 'Kalkulasi dan catatan telah diperbarui.' });
        } else if (msg === 'finalized') {
            Swal.fire({ ...config, icon: 'success', title: 'Invoice Terbit!', text: 'Pesanan telah berhasil diselesaikan.', confirmButtonColor: '#EF4C4D', showConfirmButton: true, timer: null });
        } else if (msg === 'error_min_item') {
            Swal.fire({ ...config, icon: 'error', title: 'Gagal!', text: 'Minimal harus ada 1 produk dalam pesanan.', timer: 3500 });
        }
    }
</script>
</body>
</html>