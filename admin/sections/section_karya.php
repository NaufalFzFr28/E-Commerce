<?php
/**
 * File: admin/sections/section_karya.php
 * Deskripsi: Komponen Modular Manajemen Galeri Karya (Foto) - REVISI GRID STYLE ORIGINAL & HOVER EFFECT
 * Pembaruan: Mengembalikan Proporsi Gambar Original (Kiri), Teks Justify (Kanan), Efek Hover Stroke Merah
 */

if (!isset($conn)) {
    include '../config.php';
}

$lang_karya = $_SESSION['lang'] ?? 'id';
$q_settings_karya = mysqli_query($conn, "SELECT portfolio_grid_desktop FROM site_settings WHERE id = 1");
$row_settings_karya = mysqli_fetch_assoc($q_settings_karya);

// Mengunci layout responsif murni berskala 2-Grid agar simetris di dashboard
$grid_num_karya = 2; 
?>

<style>
    /* Layout Utama Slider & Grid */
    .nfr-karya-slider-container {
        overflow: hidden; 
        width: 100%; 
        padding: 15px 0;
    }
    
    #nfrKaryaGalleryWrapper {
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: wrap !important; 
        gap: 30px 24px !important; /* Jarak vertikal 30px, horizontal 24px */
        width: 100% !important;
        align-items: stretch !important; /* Memaksa kolom flex kiri-kanan sejajar penuh */
        transition: transform 0.4s ease;
    }

    /* Struktur Grid Menjaga Layout Tepat 2-Grid Semetris */
    .nfr-karya-grid-item {
        display: flex !important;
        flex-direction: column !important;
        flex: 0 0 calc(50% - 12px) !important;
        width: calc(50% - 12px) !important;
        min-width: calc(50% - 12px) !important;
        max-width: calc(50% - 12px) !important;
        box-sizing: border-box !important;
        margin-bottom: 0px !important;
    }

    /* Pengunci Box Kartu Internal - Menggunakan Layout Horizontal Asli Dashboard */
    .admin-katalog-card-box {
        display: flex !important;
        flex-direction: row !important; /* Kembalikan ke samping agar gambar tidak raksasa */
        gap: 20px !important;
        flex: 1 1 auto !important;
        height: 100% !important;
        padding: 24px !important;
        border-radius: 20px !important;
        background: rgba(255, 255, 255, 0.02) !important;
        border: 1px solid rgba(255, 255, 255, 0.05) !important;
        box-sizing: border-box !important;
        transition: all 0.3s ease !important; /* Smooth transition untuk efek hover */
    }

    /* FIX: Efek Hover Stroke Merah Premium pada Setiap Card */
    .admin-katalog-card-box:hover {
        border-color: #ef4c4d !important;
        box-shadow: 0 10px 25px rgba(239, 76, 77, 0.15) !important;
        transform: translateY(-2px);
    }

    /* Area Media di Sebelah Kiri */
    .admin-katalog-media {
        flex: 0 0 160px !important; /* Kunci lebar pembungkus gambar agar tetap kecil proporsional */
        width: 160px !important;
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
    }

    /* Kustomisasi Frame Gambar Internal */
    .nfr-karya-img-frame {
        width: 100% !important;
        aspect-ratio: 1 / 1 !important;
        overflow: hidden;
        border-radius: 16px;
        background: #151515;
        position: relative;
    }

    .nfr-karya-img-frame img {
        width: 100%;
        height: 100%;
        object-fit: cover !important;
    }

    /* Jarak kode produk di bawah gambar */
    .admin-katalog-code {
        display: block !important;
        margin-top: 10px !important;
        font-size: 0.75rem !important;
        opacity: 0.6;
        text-align: center;
        width: 100%;
    }

    /* Detail Teks Penjelas Area Sebelah Kanan */
    .admin-katalog-details {
        display: flex !important;
        flex-direction: column !important;
        flex: 1 1 auto !important;
        justify-content: space-between !important;
    }

    .content-text-area {
        flex: 1 1 auto !important;
    }

    /* STYLING TEKS: Rata Kanan-Kiri & Line Clamping Otomatis 4 Baris */
    .admin-katalog-desc {
        text-align: justify !important; 
        text-justify: inter-word;
        font-size: 0.85rem;
        line-height: 1.5;
        opacity: 0.75;
        margin-top: 8px;
        
        display: -webkit-box !important;
        -webkit-line-clamp: 4 !important;
        -webkit-box-orient: vertical !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
    }

    @media (max-width: 768px) {
        .nfr-karya-grid-item {
            flex: 0 0 100% !important;
            width: 100% !important;
            min-width: 100% !important;
            max-width: 100% !important;
        }
        .admin-katalog-card-box {
            flex-direction: column !important;
        }
        .admin-katalog-media {
            flex: 0 0 100% !important;
            width: 100% !important;
        }
    }

    /* Premium Custom Scrollbar Modals */
    .nfr-karya-modal-scroll::-webkit-scrollbar { width: 6px; }
    .nfr-karya-modal-scroll::-webkit-scrollbar-track { background: rgba(255, 255, 255, 0.01); border-radius: 10px; }
    .nfr-karya-modal-scroll::-webkit-scrollbar-thumb { background: rgba(255, 193, 7, 0.25); border-radius: 10px; }
    .nfr-karya-modal-scroll::-webkit-scrollbar-thumb:hover { background: rgba(255, 193, 7, 0.5); }
    .nfr-karya-modal-scroll { max-height: 55vh; overflow-y: auto; padding-right: 8px; }

    /* File Input Premium Glass Style */
    .nfr-karya-input-file-glass[type="file"] { padding: 8px 12px; cursor: pointer; }
    .nfr-karya-input-file-glass[type="file"]::-webkit-file-upload-button {
        background: rgba(239, 76, 77, 0.15); border: 1px solid rgba(239, 76, 77, 0.3);
        color: #ef4c4d; padding: 6px 14px; border-radius: 8px; font-weight: bold; font-size: 0.75rem;
        cursor: pointer; margin-right: 12px; transition: all 0.2s ease; text-transform: uppercase;
    }
    .nfr-karya-input-file-glass[type="file"]::-webkit-file-upload-button:hover {
        background: #ef4c4d; color: #fff; box-shadow: 0 0 10px rgba(239, 76, 77, 0.4);
    }

    /* POPUP INTERACTION STYLING */
    .nfr-karya-overlay-mask {
        display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
        background: rgba(0, 0, 0, 0.65); z-index: 999999 !important; 
        justify-content: center; align-items: center; backdrop-filter: blur(15px); -webkit-backdrop-filter: blur(15px);
        opacity: 0; transition: opacity 0.3s ease;
    }

    .nfr-karya-card-modal {
        width: 480px; max-width: 92%; padding: 30px; background: #121212; 
        border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 24px; position: relative; 
        box-shadow: 0 25px 50px rgba(0,0,0,0.8); transform: scale(0.9) translateY(-20px); 
        transition: transform 0.32s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.32s ease; opacity: 0;
    }

    .nfr-karya-overlay-mask.active { display: flex; opacity: 1; }
    .nfr-karya-overlay-mask.active .nfr-karya-card-modal { transform: scale(1) translateY(0); opacity: 1; }
    .nfr-karya-overlay-mask.is-closing { opacity: 0; }
    .nfr-karya-overlay-mask.is-closing .nfr-karya-card-modal { transform: scale(0.92) translateY(12px); opacity: 0; }

    .nfr-karya-modal-scroll .form-group, .form-group.mb-4 { margin-bottom: 22px !important; }
    .karya-label-modal, .label-text {
        display: block; color: #ffc107; font-size: 0.75rem; font-weight: 800; 
        text-transform: uppercase; margin-bottom: 10px !important; letter-spacing: 0.8px;
    }

    .video-input-premium-glass, .input-glass {
        width: 100%; padding: 12px 16px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1); 
        border-radius: 12px; color: white; font-size: 0.9rem; transition: all 0.25s ease; box-sizing: border-box;
    }
    .video-input-premium-glass:focus, .input-glass:focus {
        border-color: #ffc107; background: rgba(255,255,255,0.08); outline: none; box-shadow: 0 0 12px rgba(255, 193, 7, 0.2);
    }

    .nfr-karya-modal-btn-option {
        width: 100%; padding: 14px 18px; text-align: left; background: rgba(255, 255, 255, 0.02); 
        border: 1px solid rgba(255, 255, 255, 0.06); border-radius: 14px; color: #fff; font-size: 0.88rem;
        cursor: pointer; transition: all 0.2s ease; display: flex; justify-content: space-between; align-items: center;
    }
    .nfr-karya-modal-btn-option:hover { background: rgba(255, 193, 7, 0.12) !important; border-color: #ffc107 !important; }

    /* Premium Custom Scrollbar Modals (Mendukung WebKit & Firefox) */
    .nfr-karya-modal-scroll {
        max-height: 55vh; 
        overflow-y: auto; 
        padding-right: 8px;
        scrollbar-width: thin; /* Untuk Firefox */
        scrollbar-color: rgba(255, 193, 7, 0.25) rgba(255, 255, 255, 0.01); /* Untuk Firefox */
    }

    /* Untuk Chrome, Edge, & Safari */
    .nfr-karya-modal-scroll::-webkit-scrollbar { 
        width: 6px; 
    }
    .nfr-karya-modal-scroll::-webkit-scrollbar-track { 
        background: rgba(255, 255, 255, 0.01); 
        border-radius: 10px; 
    }
    .nfr-karya-modal-scroll::-webkit-scrollbar-thumb { 
        background: rgba(255, 193, 7, 0.25); 
        border-radius: 10px; 
        transition: background 0.3s ease;
    }
    .nfr-karya-modal-scroll::-webkit-scrollbar-thumb:hover { 
        background: rgba(255, 193, 7, 0.5); 
    }
</style>

<div class="glass-card welcome-card" style="margin-top: 50px;">
    <h1>Manajemen Galeri Karya</h1>
    <p style="font-size: 0.85rem; opacity: 0.8;">Tambahkan desain terbaru atau kelola portofolio yang sudah ada tanpa konflik sistem.</p>
</div>

<div class="glass-card" style="overflow: visible;">
    <h4 style="font-size: 1rem; border-bottom: 1px solid var(--glass-border); padding-bottom: 10px; margin-bottom: 25px; margin-top: 0px;">
        <i class="fas fa-plus-circle me-2"></i> Tambah Karya Baru
    </h4>
    
    <form action="proses_update_portfolio.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add">
        
        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px;">
            <div>
                <div class="form-group mb-4">
                    <label class="label-text">GAMBAR KARYA (WAJIB)</label>
                    <input type="file" name="portfolio_image" class="input-glass" required>
                    <small style="color: var(--accent); font-size: 0.65rem; display: block; margin-top: 5px;">REKOMENDASI: 1:1 (800x800px)</small>
                </div>

                <div class="form-group mb-4">
                    <label class="label-text">KATEGORI PRODUK (RELASI)</label>
                    <input type="hidden" name="product_id" id="add_karya_product_id" required>
                    <button type="button" class="input-glass text-start d-flex justify-content-between align-items-center" onclick="window.nfr_karya_openModal('nfrModalAddCategory')" style="cursor: pointer; background: rgba(255,255,255,0.02); width:100%;">
                        <span id="add_karya_category_label" style="color: rgba(255,255,255,0.5); font-size: 0.9rem;">Pilih Kategori...</span>
                        <i class="fas fa-window-restore" style="color: #ffc107; font-size: 0.85rem;"></i>
                    </button>
                </div>

                <div class="form-group mb-4">
                    <label class="label-text">HARGA ASLI (RP)</label>
                    <input type="number" name="price_original" class="input-glass" placeholder="Contoh: 100000" required>
                </div>

                <div class="form-group">
                    <label class="label-text">LINK PROJECT / URL (OPSIONAL)</label>
                    <input type="url" name="link_url" class="input-glass" placeholder="https://...">
                </div>
            </div>

            <div>
                <div class="form-group mb-4">
                    <label class="label-text">JUDUL KARYA (ID / EN / JP)</label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px;">
                        <input type="text" name="title_id" class="input-glass" placeholder="ID" required>
                        <input type="text" name="title_en" class="input-glass" placeholder="EN">
                        <input type="text" name="title_jp" class="input-glass" placeholder="JP">
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label class="label-text">DESKRIPSI (BAHASA INDONESIA)</label>
                    <textarea name="desc_id" class="input-glass" rows="2" placeholder="Tulis deskripsi..." style="resize:none; font-size:0.85rem;"></textarea>
                </div>

                <div class="form-group mb-3">
                    <label class="label-text">DESCRIPTION (ENGLISH)</label>
                    <textarea name="desc_en" class="input-glass" rows="2" placeholder="Write description..." style="resize:none; font-size:0.85rem;"></textarea>
                </div>

                <div class="form-group">
                    <label class="label-text">説明 (JAPANESE)</label>
                    <textarea name="desc_jp" class="input-glass" rows="2" placeholder="説明を書いてください..." style="resize:none; font-size:0.85rem;"></textarea>
                </div>
            </div>
        </div>

        <button type="submit" class="btn-action" style="background: var(--accent); width: 100%; padding: 18px; font-weight: bold; margin-top: 25px;">
            <i class="fas fa-cloud-upload-alt me-2"></i> UNGGAH KARYA KE GALERI
        </button>
    </form>
</div>

<div id="nfrMainGalleryDashboardBox" class="glass-card" style="margin-top: 30px; overflow: visible;">
    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--glass-border); padding-bottom: 15px; margin-bottom: 25px;">
        <h4 style="margin: 0;"><i class="fas fa-images me-2"></i> Katalog Produk Terunggah</h4>
        
        <div style="display: flex; align-items: center; gap: 10px;">
            <small class="label-text-desc">FILTER:</small>
            <button type="button" class="input-glass d-flex justify-content-between align-items-center" onclick="window.nfr_karya_openModal('nfrModalFilterList')" style="width: 220px; cursor: pointer; text-align: left; padding: 10px 15px; background: rgba(255,255,255,0.03);">
                <span id="nfr_karya_filter_current_label" style="font-size: 0.85rem;">Semua Kategori</span>
                <i class="fas fa-filter" style="color: #ffc107; font-size: 0.8rem;"></i>
            </button>
        </div>
    </div>

    <div class="nfr-karya-slider-container">
        <div id="nfrKaryaGalleryWrapper"></div>
    </div>

    <div id="nfrKaryaRawStorage" style="display: none !important;">
        <?php 
        $q_list_karya = mysqli_query($conn, "SELECT p.*, s.product_name_id FROM site_portfolio p 
                                       JOIN site_products s ON p.product_id = s.id 
                                       ORDER BY p.id DESC");
        $total_items_karya = mysqli_num_rows($q_list_karya);
        $current_no_karya = $total_items_karya;

        while($row_karya = mysqli_fetch_assoc($q_list_karya)): 
            $product_code_karya = "#" . str_pad($current_no_karya, 2, '0', STR_PAD_LEFT) . "-" . date('Y-m-d', strtotime($row_karya['upload_date']));
            $current_no_karya--;
        ?>
            <div class="nfr-karya-grid-item gallery-item nfr-cat-<?= $row_karya['product_id']; ?>">
                <div class="admin-katalog-card-box">
                    <div class="admin-katalog-media">
                        <div class="nfr-karya-img-frame">
                            <img src="../assets/imgs/img-portfolio/<?= $row_karya['image_path']; ?>" onerror="this.src='../assets/imgs/placeholder.png'">
                        </div>
                        <span class="admin-katalog-code"><?= $product_code_karya; ?></span>
                    </div>
                    <div class="admin-katalog-details">
                        <div class="content-text-area">
                            <h5 class="admin-katalog-title"><?= htmlspecialchars($row_karya['title_id']); ?></h5>
                            <span class="admin-katalog-badge"><?= $row_karya['product_name_id']; ?></span>
                            <p class="admin-katalog-price">Rp <?= number_format($row_karya['price_original'], 0, ',', '.'); ?></p>
                            <p class="admin-katalog-desc"><?= htmlspecialchars($row_karya['desc_id']); ?></p>
                        </div>
                        <div class="admin-katalog-actions">
                            <button type="button" class="admin-btn-edit" onclick='window.nfr_karya_triggerEdit(<?= json_encode($row_karya, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>Edit</button>
                            <button type="button" class="admin-btn-delete" onclick="window.nfr_karya_triggerDelete(<?= $row_karya['id']; ?>)"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<div id="nfrModalAddCategory" class="nfr-karya-overlay-mask" onclick="window.nfr_karya_closeModal('nfrModalAddCategory')">
    <div class="nfr-karya-card-modal" onclick="event.stopPropagation()">
        <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:12px; margin-bottom:15px;">
            <span style="color:#ffc107; font-weight:900;"><i class="fas fa-boxes me-2"></i> PILIH KATEGORI PRODUK</span>
            <button type="button" style="background:none; border:none; color:#fff; font-size:1.5rem; cursor:pointer;" onclick="window.nfr_karya_closeModal('nfrModalAddCategory')">×</button>
        </div>
        <div style="display: flex; flex-direction: column; gap: 10px; max-height: 250px; overflow-y: auto;">
            <?php 
            $q_add_modal_select = mysqli_query($conn, "SELECT id, product_name_id FROM site_products ORDER BY product_name_id ASC");
            while($am_row = mysqli_fetch_assoc($q_add_modal_select)):
            ?>
                <button type="button" class="nfr-karya-modal-btn-option" onclick="window.nfr_karya_selectAddCategory(<?= $am_row['id']; ?>, '<?= htmlspecialchars($am_row['product_name_id'], ENT_QUOTES); ?>')">
                    <span><i class="fas fa-box me-2" style="color: #ffc107;"></i> <?= htmlspecialchars($am_row['product_name_id']); ?></span>
                    <i class="fas fa-chevron-right opacity-50"></i>
                </button>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<div id="nfrModalFilterList" class="nfr-karya-overlay-mask" onclick="window.nfr_karya_closeModal('nfrModalFilterList')">
    <div class="nfr-karya-card-modal" onclick="event.stopPropagation()">
        <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:12px; margin-bottom:15px;">
            <span style="color:#ffc107; font-weight:900;"><i class="fas fa-filter me-2"></i> FILTER KATALOG KARYA</span>
            <button type="button" style="background:none; border:none; color:#fff; font-size:1.5rem; cursor:pointer;" onclick="window.nfr_karya_closeModal('nfrModalFilterList')">×</button>
        </div>
        <div class="nfr-karya-modal-scroll" style="display: flex; flex-direction: column; gap: 10px; max-height: 280px; padding-right: 5px;">
            <button type="button" class="nfr-karya-modal-btn-option" onclick="window.nfr_karya_executeFilter('all', 'Semua Kategori')">
                <span><i class="fas fa-border-all me-2" style="color: #ffc107;"></i> Semua Kategori Karya</span>
                <i class="fas fa-chevron-right opacity-50"></i>
            </button>
            <?php 
            mysqli_data_seek($q_add_modal_select, 0);
            while($filt_row = mysqli_fetch_assoc($q_add_modal_select)):
            ?>
                <button type="button" class="nfr-karya-modal-btn-option" onclick="window.nfr_karya_executeFilter('nfr-cat-<?= $filt_row['id']; ?>', '<?= htmlspecialchars($filt_row['product_name_id'], ENT_QUOTES); ?>')">
                    <span><i class="fas fa-images me-2" style="color: #ffc107;"></i> <?= htmlspecialchars($filt_row['product_name_id']); ?></span>
                    <i class="fas fa-chevron-right opacity-50"></i>
                </button>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<div id="nfrModalEditKarya" class="nfr-karya-overlay-mask" onclick="window.nfr_karya_closeModal('nfrModalEditKarya')">
    <div class="nfr-karya-card-modal" style="width: 520px;" onclick="event.stopPropagation()">
        <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:12px; margin-bottom:15px;">
            <span style="color:#ffc107; font-weight:900;"><i class="fas fa-edit me-2"></i> EDIT KATALOG KARYA</span>
            <button type="button" style="background:none; border:none; color:#fff; font-size:1.5rem; cursor:pointer;" onclick="window.nfr_karya_closeModal('nfrModalEditKarya')">×</button>
        </div>

        <form action="proses_update_portfolio.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="portfolio_id" id="nfr_edit_id">
            
            <div class="nfr-karya-modal-scroll">
                <div class="form-group">
                    <label class="karya-label-modal">GANTI GAMBAR (OPSIONAL)</label>
                    <input type="file" name="portfolio_image" class="nfr-karya-input-file-glass">
                </div>

                <div class="form-group">
                    <label class="karya-label-modal">JUDUL KARYA (BAHASA INDONESIA)</label>
                    <input type="text" name="title_id" id="nfr_edit_title_id" class="video-input-premium-glass" required>
                </div>

                <div class="form-group">
                    <label class="karya-label-modal">KATEGORI PRODUK</label>
                    <select name="product_id" id="nfr_edit_product_id" class="video-input-premium-glass" style="background: #151515; color: #fff; border-radius:12px;">
                        <?php 
                        mysqli_data_seek($q_add_modal_select, 0);
                        while($rm_karya = mysqli_fetch_assoc($q_add_modal_select)): ?>
                            <option value="<?= $rm_karya['id']; ?>"><?= htmlspecialchars($rm_karya['product_name_id']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="karya-label-modal">HARGA PRODUK (RP)</label>
                    <input type="number" name="price_original" id="nfr_edit_price_original" class="video-input-premium-glass" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;" class="form-group">
                    <div>
                        <label class="karya-label-modal">JUDUL (ENGLISH)</label>
                        <input type="text" name="title_en" id="nfr_edit_title_en" class="video-input-premium-glass">
                    </div>
                    <div>
                        <label class="karya-label-modal">JUDUL (JAPANESE)</label>
                        <input type="text" name="title_jp" id="nfr_edit_title_jp" class="video-input-premium-glass">
                    </div>
                </div>

                <div class="form-group">
                    <label class="karya-label-modal">LINK PROJECT / URL (OPSIONAL)</label>
                    <input type="url" name="link_url" id="nfr_edit_link_url" class="video-input-premium-glass">
                </div>

                <div class="form-group">
                    <label class="karya-label-modal">DESKRIPSI (ID)</label>
                    <textarea name="desc_id" id="nfr_edit_desc_id" class="video-input-premium-glass" rows="2" style="resize:none;"></textarea>
                </div>
                <div class="form-group">
                    <label class="karya-label-modal">DESCRIPTION (EN)</label>
                    <textarea name="desc_en" id="nfr_edit_desc_en" class="video-input-premium-glass" rows="2" style="resize:none;"></textarea>
                </div>
                <div class="form-group">
                    <label class="karya-label-modal">説明 (JP)</label>
                    <textarea name="desc_jp" id="nfr_edit_desc_jp" class="video-input-premium-glass" rows="2" style="resize:none;"></textarea>
                </div>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 25px;">
                <button type="button" onclick="window.nfr_karya_closeModal('nfrModalEditKarya')" class="btn-action" style="flex:1; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); color: #fff;">BATAL</button>
                <button type="submit" class="btn-action" style="background: var(--accent); flex:2; font-weight: bold; color:#fff;">
                    <i class="fas fa-save me-2"></i> SIMPAN PERUBAHAN
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    (function() {
        "use strict";

        function domReady(fn) {
            if (document.readyState !== 'loading') {
                fn();
            } else {
                document.addEventListener('DOMContentLoaded', fn);
            }
        }

        domReady(function() {
            executeInternalRender('all');
        });

        // ANIMASI BUKA MODAL
        window.nfr_karya_openModal = function(id) {
            const m = document.getElementById(id);
            if (m) {
                m.classList.remove('is-closing');
                m.style.display = 'flex';
                void m.offsetWidth; 
                m.classList.add('active');
            }
        };

        // ANIMASI TUTUP MODAL
        window.nfr_karya_closeModal = function(id) {
            const m = document.getElementById(id);
            if (m) {
                m.classList.add('is-closing');
                m.classList.remove('active');
                
                setTimeout(() => {
                    m.style.display = 'none';
                    m.classList.remove('is-closing');
                }, 290);
            }
        };

        window.nfr_karya_selectAddCategory = function(id, name) {
            const hiddenInput = document.getElementById('add_karya_product_id');
            const visibleLabel = document.getElementById('add_karya_category_label');
            if (hiddenInput) hiddenInput.value = id;
            if (visibleLabel) {
                visibleLabel.innerText = name;
                visibleLabel.style.color = '#ffc107';
            }
            window.nfr_karya_closeModal('nfrModalAddCategory');
        };

        window.nfr_karya_executeFilter = function(val, textLabel) {
            const indicator = document.getElementById('nfr_karya_filter_current_label');
            if (indicator) indicator.innerText = textLabel;
            
            executeInternalRender(val);
            window.nfr_karya_closeModal('nfrModalFilterList');
        };

        function executeInternalRender(filterClass) {
            const storage = document.getElementById('nfrKaryaRawStorage');
            const displayWrapper = document.getElementById('nfrKaryaGalleryWrapper');
            
            if (storage && displayWrapper) {
                displayWrapper.innerHTML = ''; 
                const rawItems = storage.getElementsByClassName('gallery-item');
                
                Array.from(rawItems).forEach(function(item) {
                    if (filterClass === 'all' || item.classList.contains(filterClass)) {
                        const nodeKloning = item.cloneNode(true);
                        displayWrapper.appendChild(nodeKloning);
                    }
                });
            }
        }

        window.nfr_karya_triggerEdit = function(dataKarya) {
            if (!dataKarya) return;

            const mapFields = {
                'nfr_edit_id': dataKarya.id,
                'nfr_edit_title_id': dataKarya.title_id,
                'nfr_edit_product_id': dataKarya.product_id,
                'nfr_edit_price_original': dataKarya.price_original,
                'nfr_edit_title_en': dataKarya.title_en,
                'nfr_edit_title_jp': dataKarya.title_jp,
                'nfr_edit_link_url': dataKarya.link_url,
                'nfr_edit_desc_id': dataKarya.desc_id,
                'nfr_edit_desc_en': dataKarya.desc_en,
                'nfr_edit_desc_jp': dataKarya.desc_jp
            };

            for (const [idElemen, payloadValue] of Object.entries(mapFields)) {
                const inputTarget = document.getElementById(idElemen);
                if (inputTarget) {
                    inputTarget.value = payloadValue !== null ? payloadValue : '';
                }
            }

            window.nfr_karya_openModal('nfrModalEditKarya');
        };

        window.nfr_karya_triggerDelete = function(idHapus) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Hapus Karya?',
                    text: "File gambar dan data di database akan dihapus permanen dari galeri!",
                    icon: 'warning',
                    showCancelButton: true,
                    background: '#1a1a1a',
                    color: '#fff',
                    confirmButtonColor: '#ef4c4d',
                    cancelButtonColor: 'rgba(255,255,255,0.1)',
                    confirmButtonText: 'Ya, Hapus Karya!',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then((res) => {
                    if (res.isConfirmed) {
                        window.location.href = 'proses_update_portfolio.php?delete_id=' + idHapus;
                    }
                });
            } else {
                if (confirm("Apakah Anda yakin ingin menghapus karya ini secara permanen dari galeri?")) {
                    window.location.href = 'proses_update_portfolio.php?delete_id=' + idHapus;
                }
            }
        };

    })();
</script>