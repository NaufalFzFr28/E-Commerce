<?php
/**
 * File: admin/proses_invoice_manual.php
 * Deskripsi: Penanganan Komplit Masalah Integrity Constraint Database POS Manual
 */

session_start();
include 'cek_login.php';
include '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. Ambil & Gabungkan 4 Komponen Nomor Invoice (XXXX-NR-YYYYMMDD-NNNN)
    $prefix = strtoupper(mysqli_real_escape_string($conn, $_POST['invoice_prefix']));
    $brand  = strtoupper(mysqli_real_escape_string($conn, $_POST['invoice_brand']));
    $date   = mysqli_real_escape_string($conn, $_POST['invoice_date']);
    $rand   = mysqli_real_escape_string($conn, $_POST['invoice_rand']);
    
    $invoice_full = "{$prefix}-{$brand}-{$date}-{$rand}";
    
    // 2. Ambil Metadata Transaksi (SINKRONISASI FIELD CATATAN & ALAMAT)
    $member_id             = intval($_POST['member_id']);
    $guest_name_manual     = isset($_POST['guest_name_manual']) ? mysqli_real_escape_string($conn, $_POST['guest_name_manual']) : '';
    $guest_address_manual  = isset($_POST['guest_address_manual']) ? mysqli_real_escape_string($conn, $_POST['guest_address_manual']) : '';
    $invoice_notes         = isset($_POST['invoice_notes']) ? mysqli_real_escape_string($conn, $_POST['invoice_notes']) : '';
    $discount              = intval($_POST['discount_nominal']) ?? 0;

    // Array Item dari Form Dinamis POS (Termasuk Keterangan Per Baris)
    $product_ids = $_POST['product_ids'] ?? [];
    $qtys        = $_POST['qtys'] ?? [];
    $item_notes  = $_POST['item_notes'] ?? []; // Menangkap array catatan item jaminan karya/spesifikasi
    
    if (empty($product_ids) || count($product_ids) === 0 || $product_ids[0] == "") {
        header("Location: admin_fitur.php?status=failed_empty_items");
        exit();
    }
    
    // 3. Kalkulasi Subtotal & Total Akhir di Sisi Server (Safety Check)
        $subtotal_invoice = 0;
        $items_data_clean = [];

        foreach ($product_ids as $index => $p_id) {
            $product_id = intval($p_id);
            $qty        = intval($qtys[$index]) ?? 1;
            
            // PERBAIKAN: Ambil catatan secara presisi berdasarkan indeks baris tabel aktif
            $note       = isset($_POST['item_notes'][$index]) ? mysqli_real_escape_string($conn, $_POST['item_notes'][$index]) : '';
            
            if ($product_id > 0) {
                $q_price = mysqli_query($conn, "SELECT price FROM site_products_promo WHERE id = $product_id AND is_active = 1");
                $prod = mysqli_fetch_assoc($q_price);
                
                if ($prod) {
                    $price_original = intval($prod['price']);
                    $subtotal_item  = $price_original * $qty;
                    
                    $subtotal_invoice += $subtotal_item;
                    
                    $items_data_clean[] = [
                        'product_id' => $product_id,
                        'qty'        => $qty,
                        'price'      => $price_original,
                        'note'       => $note // Menyimpan catatan ke array pemrosesan
                    ];
                }
            }
        }
    
    // Hitung Grand Total Akhir
    $total_price_final = $subtotal_invoice - $discount;
    if ($total_price_final < 0) { $total_price_final = 0; }
    
    // 4. LOGIKA STRATEGIS FORMULASI QUERY VALUE
    // Jika Member ID <= 0 (Non-Member), kita bypass nilainya menjadi keyword SQL NULL asli tanpa kutip string
    if ($member_id > 0) {
        $val_member_id      = $member_id;
        $val_guest_name     = !empty($guest_name_manual) ? "'$guest_name_manual'" : "NULL"; 
        $val_guest_address  = !empty($guest_address_manual) ? "'$guest_address_manual'" : "NULL";
    } else {
        $val_member_id      = "NULL"; // Menghindari batasan Integrity Constraint DB
        $val_guest_name     = "'$guest_name_manual'";
        $val_guest_address  = "'$guest_address_manual'";
    }
    
    $order_number_fallback = "ORD-" . time(); 
    
    // Eksekusi Query Menggunakan Gabungan Variabel Kolom DB yang Lengkap & Akurat
    $query_order = "INSERT INTO orders (
                        order_number, 
                        invoice_number, 
                        member_id, 
                        guest_name,
                        guest_address,
                        total_price, 
                        discount,
                        status, 
                        catatan,
                        created_at
                    ) VALUES (
                        '$order_number_fallback', 
                        '$invoice_full', 
                        $val_member_id, 
                        $val_guest_name, 
                        $val_guest_address,
                        '$total_price_final', 
                        '$discount',
                        'Finished', 
                        '$invoice_notes',
                        NOW()
                    )";
                    
    if (mysqli_query($conn, $query_order)) {
        $new_order_id = mysqli_insert_id($conn);
        
        // 5. INSERT DATA RINCIAN KE TABEL `order_items`
        $success_items = true;
        foreach ($items_data_clean as $item) {
            $p_id  = $item['product_id'];
            $qty   = $item['qty'];
            $price = $item['price'];
            $note  = $item['note'];
            
            // SINKRONISASI KOLOM DB: Menggunakan 'catatan_item'
            $query_item = "INSERT INTO order_items (
                                order_id, 
                                product_id, 
                                qty, 
                                price_at_order,
                                catatan_item
                        ) VALUES (
                                '$new_order_id', 
                                '$p_id', 
                                '$qty', 
                                '$price',
                                '$note'
                        )";
                        
            if (!mysqli_query($conn, $query_item)) {
                $success_items = false;
                break;
            }
        }
                
        if ($success_items) {
            $_SESSION['print_manual_invoice_id'] = $new_order_id;
            header("Location: admin_fitur.php?status=success_invoice");
        } else {
            // Rollback jika rincian item gagal disisipkan
            mysqli_query($conn, "DELETE FROM orders WHERE id = $new_order_id");
            header("Location: admin_fitur.php?status=failed_items_insertion");
        }
        
    } else {
        header("Location: admin_fitur.php?status=failed_order_insertion");
    }
    exit();
    
} else {
    header("Location: admin_fitur.php");
    exit();
}
?>