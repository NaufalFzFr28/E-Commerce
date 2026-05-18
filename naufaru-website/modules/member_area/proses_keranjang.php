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

// Ambil data bahasa untuk label tabel dan pesan alert
$json_file = "languages/member_{$lang}.json";
$text = file_exists($json_file) ? json_decode(file_get_contents($json_file), true) : [];

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
                        <button class="luxury-btn-delete" onclick="removeFromCart(' . $row['cart_id'] . ')" title="Hapus">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                        <div class="luxury-qty-stepper">
                            <button onclick="updateQty(' . $row['cart_id'] . ', -1)"><i class="fas fa-minus"></i></button>
                            <input type="text" value="' . $row['qty'] . '" readonly>
                            <button onclick="updateQty(' . $row['cart_id'] . ', 1)"><i class="fas fa-plus"></i></button>
                        </div>
                    </div>
                    <div class="luxury-subtotal-box">
                        <small>Total Item</small>
                        <p>Rp ' . number_format($subtotal, 0, ',', '.') . '</p>
                    </div>
                </div>
            </div>';
        }
        echo '</div>';

        // Footer Total & Checkout
        echo '
        <div class="luxury-cart-footer glass-card">
            <div class="luxury-total-payment">
                <span>' . ($text['total_payment'] ?? 'Total Pembayaran') . '</span>
                <h2>Rp ' . number_format($total_all, 0, ',', '.') . '</h2>
            </div>
            <button class="luxury-btn-checkout" onclick="checkoutToAdmin()">
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
    
    // Cek apakah produk sudah ada di keranjang
    $check = mysqli_query($conn, "SELECT * FROM cart WHERE member_id = '$member_id' AND product_id = '$product_id'");
    
    if (mysqli_num_rows($check) > 0) {
        $update = mysqli_query($conn, "UPDATE cart SET qty = qty + 1 WHERE member_id = '$member_id' AND product_id = '$product_id'");
    } else {
        $insert = mysqli_query($conn, "INSERT INTO cart (member_id, product_id, qty) VALUES ('$member_id', '$product_id', 1)");
    }
    
    echo json_encode(['status' => 'success', 'message' => ($text['alert_added'] ?? 'Berhasil ditambahkan')]);
}

// --- LOGIKA 3: UPDATE QUANTITY (UPDATE) ---
if ($action == 'update') {
    $cart_id = mysqli_real_escape_string($conn, $_POST['cart_id']);
    $change = (int)$_POST['change'];
    
    // Ambil qty saat ini
    $curr = mysqli_query($conn, "SELECT qty FROM cart WHERE id = '$cart_id'");
    $data = mysqli_fetch_assoc($curr);
    $new_qty = $data['qty'] + $change;
    
    if ($new_qty > 0) {
        mysqli_query($conn, "UPDATE cart SET qty = '$new_qty' WHERE id = '$cart_id'");
    } else {
        mysqli_query($conn, "DELETE FROM cart WHERE id = '$cart_id'");
    }
}

// --- LOGIKA 4: HAPUS ITEM (DELETE) ---
if ($action == 'delete') {
    $cart_id = mysqli_real_escape_string($conn, $_POST['cart_id']);
    mysqli_query($conn, "DELETE FROM cart WHERE id = '$cart_id'");
}