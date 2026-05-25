<?php
session_start();
include '../../config.php';
include '../../functions.php';

// Proteksi akses: Pastikan hanya member yang bisa mengakses file ini
if (!isset($_SESSION['member_id'])) {
    die(json_encode(['status' => 'error', 'message' => 'Unauthorized']));
}

$member_id = $_SESSION['member_id'];
$lang = $_SESSION['lang'] ?? 'id';

// Ambil data bahasa untuk label tabel dan pesan alert (Naik dua tingkat ke root folder)
$json_file = "languages/member_{$lang}.json";

if (file_exists($json_file)) {
    $text = json_decode(file_get_contents($json_file), true);
} else {
    // Cari ke tingkat fallback direktori languages jika dipanggil dari subfolder eksternal
    $fallback_root = "../../languages/member_{$lang}.json";
    if (file_exists($fallback_root)) {
        $text = json_decode(file_get_contents($fallback_root), true);
    } else {
        $text = json_decode(file_get_contents("languages/member_id.json"), true);
    }
}

// Penentuan kolom bahasa untuk nama produk
$col_name = ($lang == 'en') ? 'product_en' : (($lang == 'jp') ? 'product_jp' : 'product_name');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// --- LOGIKA 1: MENAMPILKAN KERANJANG (VIEW) ---
if ($action == 'view') {
    $query = "SELECT c.id as cart_id, c.qty, p.*, p.$col_name as display_name 
              FROM cart c 
              JOIN site_products_promo p ON c.product_id = p.id 
              WHERE c.member_id = '$member_id' ORDER BY c.created_at DESC";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        echo '<div class="cart-items-wrapper-luxury">';
        $total_all = 0;
        while ($row = mysqli_fetch_assoc($result)) {
            $subtotal = $row['price'] * $row['qty'];
            $total_all += $subtotal;
            $p_name = !empty($row['display_name']) ? $row['display_name'] : $row['product_name'];
            
            // Persiapan string translasi untuk dikirim ke SweetAlert2 via JavaScript parameter
            $alert_title   = $text['alert_delete_title'] ?? 'Hapus item?';
            $btn_confirm   = $text['alert_delete_confirm'] ?? 'Ya, Hapus';
            $btn_cancel    = $text['alert_delete_cancel'] ?? 'Batal';
            
            // MENJADI INI:
            echo '
            <div class="cart-item-card-luxury animate__animated animate__fadeIn">
                <div class="card-luxury-left">
                    <div class="cart-img-container">
                        <img src="../../../assets/imgs/img-catalog/' . $row['gambar_produk'] . '" alt="Product">
                    </div>
                    <div class="cart-info-container">
                        <h4 class="luxury-product-title">' . htmlspecialchars($p_name) . '</h4>
                        <span class="luxury-product-price">Rp ' . number_format($row['price'], 0, ',', '.') . '</span>
                    </div>
                </div>
                
                <div class="card-luxury-right">
                    <div class="action-group-luxury">
                        <button class="luxury-btn-delete" onclick="removeFromCart(' . $row['cart_id'] . ', \'' . addslashes($alert_title) . '\', \'' . addslashes($btn_confirm) . '\', \'' . addslashes($btn_cancel) . '\')" title="' . ($text['alert_delete_title'] ?? 'Hapus') . '">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                        <div class="luxury-qty-stepper">
                            <button onclick="updateQty(' . $row['cart_id'] . ', -1)"><i class="fas fa-minus"></i></button>
                            <input type="text" value="' . $row['qty'] . '" readonly>
                            <button onclick="updateQty(' . $row['cart_id'] . ', 1)"><i class="fas fa-plus"></i></button>
                        </div>
                    </div>
                    <div class="luxury-subtotal-box">
                        <small>' . ($text['table_total_item'] ?? 'Total Item') . '</small>
                        <p>Rp ' . number_format($subtotal, 0, ',', '.') . '</p>
                    </div>
                </div>
            </div>';
        }
        echo '</div>';

        // Footer Total & Checkout Terjemahan Penuh Berdasarkan JSON Aktif
        echo '
        <div class="luxury-cart-footer glass-card">
            <div class="luxury-total-payment">
                <span>' . ($text['total_payment'] ?? 'Total Pembayaran') . '</span>
                <h2>Rp ' . number_format($total_all, 0, ',', '.') . '</h2>
            </div>
            <button class="luxury-btn-checkout" onclick="checkoutToAdmin(\'' . addslashes($text['alert_send_order'] ?? 'Kirim ke Admin?') . '\', \'' . addslashes($text['btn_checkout'] ?? 'Kirim Sekarang') . '\', \'' . addslashes($text['back'] ?? 'Batal') . '\')">
                <i class="fas fa-paper-plane me-2"></i> ' . ($text['btn_checkout'] ?? 'Kirim Pesanan ke Admin') . '
            </button>
        </div>';
    } else {
        echo '<div class="text-center py-5 opacity-50"><i class="fas fa-shopping-basket mb-3 d-block" style="font-size: 3.5rem; color:#EF4C4D;"></i><p>' . ($text['cart_empty'] ?? 'Keranjang belanja Anda masih kosong.') . '</p></div>';
    }
}

// --- LOGIKA 2: TAMBAH KE KERANJANG (ADD) ---
if ($action == 'add') {
    $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
    
    $check = mysqli_query($conn, "SELECT * FROM cart WHERE member_id = '$member_id' AND product_id = '$product_id'");
    
    if (mysqli_num_rows($check) > 0) {
        mysqli_query($conn, "UPDATE cart SET qty = qty + 1 WHERE member_id = '$member_id' AND product_id = '$product_id'");
    } else {
        mysqli_query($conn, "INSERT INTO cart (member_id, product_id, qty) VALUES ('$member_id', '$product_id', 1)");
    }
    
    // PERBAIKAN: Ambil data judul dan deskripsi terjemahan resmi untuk dilempar ke SweetAlert2
    $alert_title = $text['alert_success_title'] ?? 'Berhasil Ditambahkan!';
    $alert_desc  = $text['alert_success_desc'] ?? 'Layanan berhasil dimasukkan ke dalam daftar Pesanan Saya.';
    
    echo json_encode([
        'status'  => 'success', 
        'title'   => $alert_title, 
        'message' => $alert_desc
    ]);
    exit();
}

// --- LOGIKA 3: UPDATE QUANTITY (UPDATE) ---
if ($action == 'update') {
    $cart_id = mysqli_real_escape_string($conn, $_POST['cart_id']);
    $change = (int)$_POST['change'];
    
    $curr = mysqli_query($conn, "SELECT qty FROM cart WHERE id = '$cart_id'");
    $data = mysqli_fetch_assoc($curr);
    $new_qty = $data['qty'] + $change;
    
    if ($new_qty > 0) {
        mysqli_query($conn, "UPDATE cart SET qty = '$new_qty' WHERE id = '$cart_id'");
    } else {
        mysqli_query($conn, "DELETE FROM cart WHERE id = '$cart_id'");
    }
    echo json_encode(['status' => 'success']);
    exit();
}

// --- LOGIKA 4: HAPUS ITEM (DELETE) ---
if ($action == 'delete') {
    $cart_id = mysqli_real_escape_string($conn, $_POST['cart_id']);
    mysqli_query($conn, "DELETE FROM cart WHERE id = '$cart_id'");
    echo json_encode(['status' => 'success']);
    exit();
}