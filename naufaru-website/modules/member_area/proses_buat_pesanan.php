<?php
session_start();
include '../../config.php';
include '../../functions.php';

// Proteksi akses
if (!isset($_SESSION['member_id'])) {
    die(json_encode(['status' => 'error', 'message' => 'Unauthorized']));
}

$member_id = $_SESSION['member_id'];
$lang = $_SESSION['lang'] ?? 'id';

// Ambil data bahasa untuk pesan response
$json_file = "languages/member_{$lang}.json";
$text = file_exists($json_file) ? json_decode(file_get_contents($json_file), true) : [];

// 1. Cek apakah keranjang ada isinya
$check_cart = mysqli_query($conn, "SELECT c.*, p.price FROM cart c JOIN site_products_promo p ON c.product_id = p.id WHERE c.member_id = '$member_id'");

if (mysqli_num_rows($check_cart) == 0) {
    echo json_encode(['status' => 'error', 'message' => 'Keranjang kosong!']);
    exit;
}

// 2. Mulai Transaksi Database (Mencegah data parsial jika error)
mysqli_begin_transaction($conn);

try {
    // A. Hitung Total Harga dari Keranjang
    $total_price = 0;
    $cart_items = [];
    while ($item = mysqli_fetch_assoc($check_cart)) {
        $total_price += ($item['price'] * $item['qty']);
        $cart_items[] = $item;
    }

    // B. Generate Nomor Pesanan Unik (Format: NR-YYYYMMDD-RANDOM)
    $order_number = "NR-" . date('Ymd') . "-" . strtoupper(substr(md5(time()), 0, 5));

    // C. Insert ke Tabel orders (Header)
    $query_order = "INSERT INTO orders (order_number, member_id, total_price, status, is_invoice) 
                    VALUES ('$order_number', '$member_id', '$total_price', 'Pending', 0)";
    
    if (!mysqli_query($conn, $query_order)) {
        throw new Exception("Gagal membuat data pesanan.");
    }

    $order_id = mysqli_insert_id($conn);

    // D. Insert ke Tabel order_items (Detail)
    foreach ($cart_items as $ci) {
        $product_id = $ci['product_id'];
        $qty = $ci['qty'];
        $price = $ci['price'];

        $query_item = "INSERT INTO order_items (order_id, product_id, qty, price_at_order) 
                       VALUES ('$order_id', '$product_id', '$qty', '$price')";
        
        if (!mysqli_query($conn, $query_item)) {
            throw new Exception("Gagal menyimpan detail item pesanan.");
        }
    }

    // E. Kosongkan Keranjang Member setelah sukses pindah ke Orders
    mysqli_query($conn, "DELETE FROM cart WHERE member_id = '$member_id'");

    // Jika semua lancar, Commit transaksi
    mysqli_commit($conn);

    echo json_encode([
        'status' => 'success', 
        'message' => ($lang == 'id' ? "Pesanan $order_number berhasil dikirim ke Admin!" : 
                     ($lang == 'en' ? "Order $order_number has been sent to Admin!" : 
                     "注文 $order_number が管理者に送信されました！"))
    ]);

} catch (Exception $e) {
    // Jika ada error di tengah jalan, batalkan semua perubahan
    mysqli_rollback($conn);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}