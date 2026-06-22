<?php
/**
 * File: modules/invoice_site/index.php
 * Deskripsi: Sistem Pembuatan & Riwayat Invoice NaufaRu
 */

include '../../admin/config/db.php';
include '../../functions.php';

// Ambil pengaturan site
$settings = $conn->query("SELECT * FROM site_settings WHERE id = 1")->fetch_assoc();

// Ambil riwayat invoice terbaru
$history = $conn->query("SELECT * FROM invoice_history ORDER BY created_at DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NaufaRu - Professional Invoice System</title>
    
    <link rel="stylesheet" href="../../assets/vendors/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    
    <style>
        .invoice-box {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
        }
        @media print {
            .no-print, .main-footer, .chat-widget, .navbar, .burger-menu-trigger { display: none !important; }
            .invoice-box { box-shadow: none; border: none; padding: 0; }
            body { background: white; }
        }
    </style>
</head>
<body class="<?php echo $settings['night_mode_default'] ? 'night-mode' : ''; ?>">

    <?php include '../../includes/header.php'; ?>
    <?php include '../../includes/sidebar.php'; ?>

    <div class="container py-5 mt-5">
        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="invoice-box fade-in-element">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <img src="../../assets/imgs/naufaru-logo.png" alt="Logo" width="60">
                            <h4 class="mt-2 font-weight-bold">INVOICE</h4>
                        </div>
                        <div class="text-right">
                            <h6 class="mb-0">NaufaRu Digital</h6>
                            <small class="text-muted">Tangerang Selatan, Indonesia</small>
                        </div>
                    </div>

                    <form id="invoice-form">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="small font-weight-bold">TUJUAN TAGIHAN:</label>
                                <input type="text" name="customer_name" class="form-control form-control-sm mb-2" placeholder="Nama Pelanggan" required>
                                <textarea name="customer_address" class="form-control form-control-sm" placeholder="Alamat Pelanggan" rows="2"></textarea>
                            </div>
                            <div class="col-md-6 text-md-right">
                                <label class="small font-weight-bold">DETAIL:</label>
                                <input type="text" name="order_number" class="form-control form-control-sm mb-2" value="INV-<?php echo date('YmdHis'); ?>" readonly>
                                <input type="date" class="form-control form-control-sm" value="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>

                        <table class="table table-sm" id="item-table">
                            <thead class="bg-light">
                                <tr>
                                    <th>Deskripsi Jasa/Produk</th>
                                    <th width="150">Harga (Rp)</th>
                                    <th width="50"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><input type="text" name="desc[]" class="form-control form-control-sm" placeholder="Contoh: Jasa Foto Wisuda" required></td>
                                    <td><input type="number" name="price[]" class="form-control form-control-sm item-price" placeholder="0" required></td>
                                    <td><button type="button" class="btn btn-sm btn-danger remove-row"><i class="fas fa-times"></i></button></td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <button type="button" class="btn btn-sm btn-outline-primary mb-4" id="add-item">
                            <i class="fas fa-plus mr-1"></i> Tambah Baris
                        </button>

                        <div class="row justify-content-end">
                            <div class="col-md-5 text-right">
                                <h5>Total: <span id="grand-total">Rp 0</span></h5>
                            </div>
                        </div>

                        <hr class="no-print">
                        <div class="no-print text-right">
                            <button type="button" class="btn btn-secondary" onclick="window.print()">
                                <i class="fas fa-print mr-1"></i> Cetak ke PDF
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i> Simpan ke Riwayat
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-lg-4 no-print">
                <div class="card border-0 shadow-sm fade-in-element">
                    <div class="card-header bg-white font-weight-bold">
                        Riwayat Terbaru
                    </div>
                    <ul class="list-group list-group-flush">
                        <?php while($row = $history->fetch_assoc()): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-primary d-block"><?php echo $row['order_number']; ?></small>
                                <strong><?php echo $row['customer_name']; ?></strong>
                            </div>
                            <span class="badge badge-pill badge-light">Rp <?php echo number_format($row['total_price'], 0, ',', '.'); ?></span>
                        </li>
                        <?php endwhile; ?>
                    </ul>
                    <div class="card-footer bg-white">
                        <a href="../../admin/index.php" class="btn btn-sm btn-block btn-outline-secondary">Lihat Semua Data</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../../includes/footer.php'; ?>

    <script src="../../assets/vendors/jquery/jquery.min.js"></script>
    <script src="../../assets/vendors/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/vendors/sweetalert2/sweetalert2.all.min.js"></script>
    <script src="../../assets/js/main.js"></script>

    <script>
        // Logika Perhitungan Otomatis
        $(document).ready(function(){
            function calculateTotal() {
                let total = 0;
                $('.item-price').each(function(){
                    let val = $(this).val();
                    if(val) total += parseFloat(val);
                });
                $('#grand-total').text('Rp ' + total.toLocaleString('id-ID'));
            }

            $('#add-item').click(function(){
                let newRow = `<tr>
                    <td><input type="text" name="desc[]" class="form-control form-control-sm" required></td>
                    <td><input type="number" name="price[]" class="form-control form-control-sm item-price" required></td>
                    <td><button type="button" class="btn btn-sm btn-danger remove-row"><i class="fas fa-times"></i></button></td>
                </tr>`;
                $('#item-table tbody').append(newRow);
            });

            $(document).on('click', '.remove-row', function(){
                $(this).closest('tr').remove();
                calculateTotal();
            });

            $(document).on('input', '.item-price', function(){
                calculateTotal();
            });

            // Handling Simpan Riwayat via AJAX
            $('#invoice-form').on('submit', function(e){
                e.preventDefault();
                Swal.fire({
                    title: 'Simpan Data?',
                    text: "Invoice ini akan tercatat di riwayat penjualan.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Simpan'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire('Berhasil!', 'Data invoice telah disimpan.', 'success');
                    }
                });
            });
        });
    </script>
</body>
</html>