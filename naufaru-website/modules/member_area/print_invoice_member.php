<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
include '../../config.php';
include '../../functions.php';

// 1. Ambil ID Pesanan
$order_id = mysqli_real_escape_string($conn, $_GET['id']);

// 2. Ambil data lengkap (Prioritaskan kolom invoice_number jika sudah diterbitkan)
$query_order = mysqli_query($conn, "SELECT o.*, m.nama_lengkap, m.alamat 
                                    FROM orders o 
                                    JOIN users_member m ON o.member_id = m.id 
                                    WHERE o.id = '$order_id'");
$order = mysqli_fetch_assoc($query_order);

if (!$order) { die("Invoice tidak ditemukan."); }

/* |--------------------------------------------------------------------------
| LOGIKA PENAMAAN & NOMOR INVOICE
|--------------------------------------------------------------------------
| 1. display_order_number: Dipakai untuk tampilan di dalam dokumen.
| 2. filename_slug: Dipakai untuk nama file export agar rapi (Tanpa Spasi).
*/

// Cek apakah ada nomor invoice manual yang baru diinput atau sudah ada di DB
$display_order_number = !empty($order['invoice_number']) ? $order['invoice_number'] : $order['order_number'];

// Bersihkan nama untuk file (Contoh: "Naufal Fauzi" -> "Naufal_Fauzi")
$safe_name = str_replace(' ', '_', $order['nama_lengkap']);
$file_name_export = "Invoice_" . $safe_name . "_" . $display_order_number;

// 3. Ambil Item Produk
$q_items = mysqli_query($conn, "SELECT oi.*, p.product_name 
                                FROM order_items oi 
                                JOIN site_products_promo p ON oi.product_id = p.id 
                                WHERE oi.order_id = '$order_id'");

$all_items = [];
$subtotal = 0;
while ($item = mysqli_fetch_assoc($q_items)) {
    $subtotal += ($item['qty'] * $item['price_at_order']);
    $all_items[] = $item;
}

// 4. Pecah Halaman (Maksimal 5 item per lembar)
$chunked_items = array_chunk($all_items, 5);
$total_pages = count($chunked_items);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice_<?= str_replace(' ', '_', $order['nama_lengkap']) ?>_<?= $display_order_number ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        body { margin: 0; padding: 0; background: #525659; font-family: Helvetica, Arial, sans-serif; }
        
        /* Toolbar Preview */
        .no-print-zone { 
            position: fixed; top: 20px; right: 20px; z-index: 9999; 
            background: #EF4C4D; color: white; padding: 15px; border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3); text-align: center; width: 200px;
        }
        .btn-print { 
            background: white; color: #EF4C4D; border: none; padding: 10px 20px; 
            border-radius: 8px; font-weight: bold; cursor: pointer; transition: 0.3s; width: 100%;
        }
        .btn-print:hover { background: #333; color: white; }

        /* Kertas A4 */
        .page-container {
            position: relative; width: 210mm; height: 297mm;
            background: #fff; margin: 20px auto; overflow: hidden;
            box-shadow: 0 0 20px rgba(0,0,0,0.5);
        }

        .header-img { position: absolute; top: 0; left: 0; width: 100%; z-index: 1; }
        .footer-img { position: absolute; bottom: 0; left: 0; width: 100%; z-index: 1; }

        .content-wrapper { position: relative; z-index: 5; padding: 0 45px; }

        /* Info Pelanggan Area */
        .info-table { 
            width: 100%; border-collapse: separate; border-spacing: 10px 0; 
            margin-top: 185px; /* Menyesuaikan posisi di bawah header */
        }
        .label-text { 
            font-size: 8.5pt; font-weight: 900; color: #EF4C4D; 
            text-transform: uppercase; padding-bottom: 5px; padding-left: 5px;
        }
        .info-box { 
            width: 33.33%; border: 2px solid #EF4C4D; border-radius: 15px;
            background: #fff; text-align: center; font-size: 10pt; font-weight: bold;
            padding: 12px 5px; min-height: 40px; vertical-align: middle;
        }

        /* Tabel Produk */
        .product-table { width: 100%; border-collapse: collapse; margin-top: 30px; }
        .product-table th { background: #333; color: #fff; padding: 12px; font-size: 9pt; text-transform: uppercase; }
        .product-table td { padding: 12px 10px; border-bottom: 1px solid #eee; font-size: 9pt; vertical-align: top; }

        /* Summary Area */
        .summary-section { margin-top: 30px; display: flex; justify-content: space-between; }
        .left-notes { width: 55%; }
        .right-totals { width: 40%; text-align: center; }
        .red-box { border: 1.5px solid #EF4C4D; border-radius: 12px; padding: 10px; margin-bottom: 10px; font-size: 8pt; line-height: 1.4; color: #444; }
        
        .stat-label { background: #333; color: #fff; padding: 8px; border-radius: 6px; font-size: 9pt; font-weight: bold; margin-top: 5px; }
        .stat-val { padding: 10px; font-size: 12pt; font-weight: bold; color: #000; margin-bottom: 5px; }

        /* Signature */
        .signature-box { text-align: center; margin-top: 20px; }
        .ttd-image { width: 120px; margin: 10px auto; display: block; }
        .owner-text { font-size: 10pt; font-weight: bold; }

        /* Editable Feedback */
        [contenteditable="true"] { cursor: text; outline: none; transition: 0.2s; }
        [contenteditable="true"]:hover { background: rgba(239, 76, 77, 0.05); border-radius: 4px; }
        [contenteditable="true"]:focus { background: rgba(239, 76, 77, 0.1); border-bottom: 1px dashed #EF4C4D; }

        @media print {
            body { background: white; margin: 0; }
            .no-print-zone { display: none; }
            .page-container { margin: 0; box-shadow: none; page-break-after: always; width: 210mm; height: 297mm; }
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        }
    </style>
</head>
<body>

    <div class="no-print-zone">
        <p style="margin:0 0 12px 0; font-size: 13px; font-weight:900; letter-spacing:1px;">MODE EDIT AKTIF</p>
        <button class="btn-print" onclick="triggerPrint()">
            <i class="fas fa-print me-2"></i> SIMPAN PDF
        </button>
        <p style="margin:12px 0 0 0; font-size: 10px; opacity: 0.9; line-height:1.4;">Silakan edit teks secara langsung sebelum mencetak.</p>
    </div>

    <?php foreach ($chunked_items as $index => $page_items) : 
        $is_last_page = ($index + 1 === $total_pages);
    ?>
    <div class="page-container">
        <img src="../../assets/imgs/header.png" class="header-img">
        <img src="../../assets/imgs/footer.png" class="footer-img">

        <div class="content-wrapper">
            <?php if ($index === 0) : ?>
                <table class="info-table">
                    <thead>
                        <tr>
                            <td class="label-text">Nama Pelanggan</td>
                            <td class="label-text">Alamat</td>
                            <td class="label-text">Nomor Pesanan</td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="info-box" contenteditable="true"><?= htmlspecialchars($order['nama_lengkap']) ?></td>
                            <td class="info-box" contenteditable="true"><?= htmlspecialchars($order['alamat']) ?></td>
                            <td class="info-box" contenteditable="true"><?= htmlspecialchars($display_order_number) ?></td>
                        </tr>
                    </tbody>
                </table>
            <?php else: ?>
                <div style="height: 250px;"></div>
            <?php endif; ?>

            <table class="product-table">
                <thead>
                    <tr>
                        <th align="left">PRODUK / JASA</th>
                        <th align="center">HARGA</th>
                        <th align="center">QTY</th>
                        <th align="right">TOTAL</th>
                        <th align="left">KETERANGAN</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($page_items as $it) : ?>
                    <tr>
                        <td contenteditable="true"><b><?= htmlspecialchars($it['product_name']) ?></b></td>
                        <td align="center" contenteditable="true">Rp <?= number_format($it['price_at_order'], 0, ',', '.') ?></td>
                        <td align="center" contenteditable="true"><?= $it['qty'] ?></td>
                        <td align="right" contenteditable="true"><b>Rp <?= number_format(($it['qty'] * $it['price_at_order']), 0, ',', '.') ?></b></td>
                        <td style="color:#666; font-style:italic;" contenteditable="true"><?= htmlspecialchars($it['catatan_item'] ?? '-') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if ($is_last_page) : ?>
            <div class="summary-section">
                <div class="left-notes">
                    <div class="red-box" contenteditable="true">
                        TERIMA KASIH ATAS PESANAN ANDA. JANGAN LUPA MEMBERIKAN TESTIMONI DI GOOGLE MAPS KAMI.
                    </div>
                    <div class="red-box" contenteditable="true">
                        BARANG YANG SUDAH DIBELI TIDAK DAPAT DIKEMBALIKAN / DITUKAR. HARAP MAKLUM.
                    </div>
                    <div style="font-size:9pt; margin-top:15px; border-left: 3px solid #EF4C4D; padding-left: 10px;">
                        <b>Catatan Khusus:</b><br>
                        <span contenteditable="true"><?= (!empty($order['catatan']) ? nl2br(htmlspecialchars($order['catatan'])) : 'Tidak ada catatan tambahan.') ?></span>
                    </div>
                </div>

                <div class="right-totals">
                    <div class="stat-label">SUBTOTAL</div>
                    <div class="stat-val" contenteditable="true">Rp <?= number_format($subtotal, 0, ',', '.') ?></div>
                    
                    <div class="stat-label">DISKON</div>
                    <div class="stat-val" contenteditable="true">Rp <?= number_format($order['discount'], 0, ',', '.') ?></div>
                    
                    <div class="stat-label" style="background:#EF4C4D">TOTAL AKHIR</div>
                    <div class="stat-val" style="color:#EF4C4D; font-size:16pt" contenteditable="true">Rp <?= number_format($order['total_price'], 0, ',', '.') ?></div>

                    <div class="signature-box">
                        <p style="font-size:12pt; margin-bottom:5px;">Hormat Kami,</p>
                        <img src="../../assets/imgs/ttd.png" class="ttd-image">
                        <span class="owner-text">OWNER NAUFARU</span>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>

</body>

<script>
    // Fungsi cetak yang lebih stabil
    function triggerPrint() {
        // Opsional: Hilangkan focus dari elemen contenteditable sebelum print
        if (document.activeElement) {
            document.activeElement.blur();
        }
        
        // Picu perintah print browser
        window.print();
    }

    // Menutup dropdown atau modal jika ada yang terbuka (opsional)
    window.onafterprint = function() {
        console.log("Proses cetak selesai atau dibatalkan.");
    };
</script>
</html>