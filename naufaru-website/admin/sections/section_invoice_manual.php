<?php
/**
 * File: admin/sections/section_invoice_manual.php
 * Deskripsi: Komponen Modul POS Pembuatan Invoice Manual (Include Section)
 */

// Otomatisasi komponen nomor invoice manual
$invoice_date_part = date('Ymd'); 
$invoice_rand_part = strtoupper(substr(md5(time()), 0, 4)); 
?>

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

        <div class="glass-card" style="overflow: hidden;">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--glass-border); padding-bottom: 10px; margin-bottom: 20px;">
                <h4 style="margin: 0; font-size: 1rem;"><i class="fas fa-shopping-basket me-2"></i> Rincian Item Transaksi</h4>
                <button type="button" class="btn-action-premium" style="background: #ffc107; color: #111; padding: 6px 12px; border-radius: 8px; font-size: 0.7rem;" onclick="tambahBarisItem()">
                    <i class="fas fa-plus"></i> Tambah Item
                </button>
            </div>

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
                            <td><input type="text" class="input-glass input-row-price-label" value="Rp 0" disabled style="text-align: center; padding: 10px 5px;"></td>
                            <td><input type="number" name="qtys[]" class="input-glass input-row-qty" value="1" min="1" oninput="hitungHargaRow(this)" style="text-align: center; padding: 10px 5px;" required></td>
                            <td>
                                <input type="text" class="input-glass input-row-subtotal-label" value="Rp 0" disabled style="text-align: center; font-weight: 700; color: #74b9ff; padding: 10px 5px;">
                                <input type="hidden" class="raw-row-subtotal-value" value="0">
                            </td>
                            <td><input type="text" name="item_notes[]" class="input-glass" placeholder="Contoh: Keterangan spesifikasi"></td>
                            <td><button type="button" class="btn-remove-row" onclick="hapusBarisItem(this)"><i class="fas fa-times"></i></button></td>
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
                $q_history = mysqli_query($conn, "SELECT o.*, m.nama_lengkap FROM orders o LEFT JOIN users_member m ON o.member_id = m.id WHERE o.invoice_number LIKE '%%-%%-%%-%%' ORDER BY o.id DESC");
                if(mysqli_num_rows($q_history) > 0):
                    while($hist = mysqli_fetch_assoc($q_history)):
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

<div id="addProductModal" class="modal-overlay-glass">
    <div class="modal-content-card">
        <div class="modal-header-naufaru">
            <span><i class="fas fa-cart-plus me-2"></i> PILIHAN KATALOG PRODUK</span>
            <button type="button" class="btn-close-modal" onclick="closeAddProductModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="info-box-modal-small">
            <p><i class="fas fa-info-circle me-1" style="color:#3498db;"></i> Pilih produk aktif di bawah ini untuk dimasukkan ke dalam baris rincian transaksi Kasir.</p>
        </div>
        <div class="form-group-modal" style="margin-bottom: 15px;">
            <label class="label-modal">Daftar Produk Tersedia</label>
            <div class="modal-product-list-scroll" style="display: flex; flex-direction: column; gap: 10px; max-height: 260px; overflow-y: auto; padding-right: 10px;">
                <?php 
                $catalog_modal_query = mysqli_query($conn, "SELECT id, product_name, price FROM site_products_promo WHERE is_active = 1 ORDER BY product_name ASC");
                while($c_mod = mysqli_fetch_assoc($catalog_modal_query)):
                ?>
                    <button type="button" class="btn-modal-select-item" data-value="<?= $c_mod['id'] ?>" data-price="<?= $c_mod['price'] ?>" style="text-align: left; background: rgba(46, 204, 113, 0.05); border: 1px solid rgba(46, 204, 113, 0.2); padding: 14px 15px; color: #2ecc71; border-radius: 12px; cursor: pointer; transition: 0.2s; font-size: 0.85rem; font-weight: 600; display: flex; justify-content: space-between; align-items: center;">
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

<script>
    let currentActiveRowPointer = null;

    $(document).on('click', '#memberDropdown .custom-select-trigger', function(e) {
        e.stopPropagation();
        const wrapper = $(this).closest('.custom-select-wrapper');
        $('.custom-select-wrapper').not(wrapper).removeClass('open');
        wrapper.toggleClass('open');
    });

    $(document).on('click', '.row-product-dropdown .custom-select-trigger', function(e) {
        e.stopPropagation();
        currentActiveRowPointer = $(this).closest('tr');
        const modal = document.getElementById('addProductModal');
        const content = modal.querySelector('.modal-content-card');
        content.style.animation = "popupZoomIn 0.4s cubic-bezier(0.165, 0.84, 0.44, 1)";
        modal.style.display = 'flex';
    });

    $(document).on('click', '.btn-modal-select-item', function(e) {
        e.stopPropagation();
        if (currentActiveRowPointer) {
            const productId = $(this).data('value');
            const productPrice = parseInt($(this).data('price')) || 0;
            const productName = $(this).find('span').clone().children('i').remove().end().text().trim();
            
            currentActiveRowPointer.find('.raw-product-id-value').val(productId);
            currentActiveRowPointer.find('.custom-select-trigger span').text(productName);
            
            const priceInput = currentActiveRowPointer.find('.input-row-price-label');
            priceInput.val("Rp " + productPrice.toLocaleString('id-ID'));
            priceInput.attr('data-price-raw', productPrice);
            
            const qtyInput = currentActiveRowPointer.find('.input-row-qty')[0];
            if (qtyInput) { hitungHargaRow(qtyInput); } else { hitungKalkulasiTotal(); }
            currentActiveRowPointer.find('.custom-select-trigger').css('border-color', '#2ecc71');
        }
        closeAddProductModal();
    });

    function closeAddProductModal() {
        const modal = document.getElementById('addProductModal');
        if (!modal) return;
        const content = modal.querySelector('.modal-content-card');
        content.style.animation = "popupZoomOut 0.3s ease forwards";
        setTimeout(() => {
            modal.style.display = 'none';
            content.style.animation = ""; 
            currentActiveRowPointer = null;
        }, 300);
    }

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

        if (wrapper.attr('id') === 'memberDropdown') {
            if (value == "0") {
                $('#containerGuestManualName').addClass('show');
                $('#inputGuestNameManual').focus();
            } else {
                $('#containerGuestManualName').removeClass('show');
                $('#inputGuestNameManual, #inputGuestAddressManual').val('');
            }
        }
    });

    $(document).on('click', function() { $('.custom-select-wrapper').removeClass('open'); });

    function validasiSebelumKirim() {
        let validProduk = true;
        $('.raw-product-id-value').each(function() {
            if ($(this).val() === "" || $(this).val() === null) { validProduk = false; }
        });

        if (!validProduk) {
            tampilkanAlertStatus('failed_empty_items');
            return false;
        }

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
        document.querySelectorAll('.raw-row-subtotal-value').forEach(input => { subtotalInvoice += parseInt(input.value) || 0; });
        const discount = parseInt(document.getElementById('inputDiscount').value) || 0;
        let totalAkhir = subtotalInvoice - discount;
        if (totalAkhir < 0) totalAkhir = 0;

        document.getElementById('labelSubtotal').innerText = "Rp " + subtotalInvoice.toLocaleString('id-ID');
        document.getElementById('labelDiscount').innerText = "- Rp " + discount.toLocaleString('id-ID');
        document.getElementById('labelTotalAkhir').innerText = "Rp " + totalAkhir.toLocaleString('id-ID');
    }

    function tampilkanAlertStatus(status) {
        let config = { timer: 2500, showConfirmButton: false, timerProgressBar: true, background: '#1a1a1a', color: '#fff', confirmButtonColor: '#ef4c4d' };
        if (status === 'success_invoice') {
            config.icon = 'success'; config.title = 'Invoice Terbuat!'; config.text = 'Data transaksi manual berhasil disimpan.';
            <?php if (isset($_SESSION['print_manual_invoice_id'])): ?>
                const printId = "<?= $_SESSION['print_manual_invoice_id']; ?>";
                window.open('print_invoice_manual.php?id=' + printId, '_blank');
                <?php unset($_SESSION['print_manual_invoice_id']); ?>
            <?php endif; ?>
        } else if (status === 'failed_empty_items') {
            config.icon = 'warning'; config.title = 'Kotak Belum Terisi!'; config.text = 'Silakan pilih jenis produk katalog pada tabel rincian transaksi sebelum menyimpan.';
            config.showConfirmButton = true; config.timer = null;
        }
        if(status) { Swal.fire(config); }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const params = new URLSearchParams(window.location.search);
        if (params.has('status')) {
            tampilkanAlertStatus(params.get('status'));
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    });
</script>