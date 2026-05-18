<?php
/**
 * File: admin/sections/section_karya.php
 * Deskripsi: Komponen Modular Manajemen Galeri Karya (Foto) dengan Premium Pop-up Modal Window
 * Pembaruan: Mengganti Sistem Dropdown Menjadi Pop-up Modal Window Mandiri (Anti-Bentrok)
 */

if (!isset($conn)) {
    include '../config.php';
}

// Mengunci pointer bahasa lokal dashboard
$lang_karya = $_SESSION['lang'] ?? 'id';

// 1. QUERY & CONFIGURATION DATA BASE GALERI KARYA (VARIABEL EKSKLUSIF)
$q_settings_karya = mysqli_query($conn, "SELECT portfolio_grid_desktop FROM site_settings WHERE id = 1");
$row_settings_karya = mysqli_fetch_assoc($q_settings_karya);
$grid_num_karya = $row_settings_karya['portfolio_grid_desktop'] ?? 3;

// Konversi angka ke class Bootstrap (3 kolom = col-lg-4, 4 kolom = col-lg-3)
$grid_class_karya = ($grid_num_karya == 4) ? "col-lg-3" : "col-lg-4";
?>

<style>
    .gallery-slider-container {
        overflow: hidden; 
        width: 100%; 
        padding: 10px 0;
    }
    
    #adminGalleryWrapper {
        display: flex; 
        transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1); 
        width: 100%; 
        align-items: flex-start;
    }
    
    .modal-body-scroll-karya {
        max-height: 65vh; 
        overflow-y: auto; 
        padding-right: 10px;
    }

    /* --- Animasi Pop-up Modal Windows Modul Karya --- */
    @keyframes karyaPopupZoomIn { from { opacity: 0; transform: scale(0.9); } to { opacity: 1; transform: scale(1); } }
    @keyframes karyaPopupZoomOut { from { opacity: 1; transform: scale(1); } to { opacity: 0; transform: scale(0.9); } }

    /* Overlay Kaca Kabur Pop-up Karya */
    .karya-modal-overlay-glass {
        display: none; 
        position: fixed; 
        top: 0; left: 0; width: 100%; height: 100%; 
        background: rgba(0, 0, 0, 0.45); 
        z-index: 99999 !important; 
        justify-content: center; 
        align-items: center; 
        backdrop-filter: blur(15px);
        -webkit-backdrop-filter: blur(15px);
    }

    /* Kartu Konten Modal Menyesuaikan Style Dashboard Premium */
    .karya-modal-content-card {
        width: 450px; 
        max-width: 90%;
        padding: 30px; 
        background: rgba(20, 20, 20, 0.85);
        border: 1px solid rgba(255, 255, 255, 0.1); 
        border-radius: 24px;
        position: relative; 
        box-shadow: 0 25px 50px rgba(0,0,0,0.6);
        transform: scale(0.9);
        opacity: 0;
    }

    .karya-modal-content-card.anim-in {
        animation: karyaPopupZoomIn 0.35s cubic-bezier(0.165, 0.84, 0.44, 1) forwards;
    }

    .karya-modal-content-card.anim-out {
        animation: karyaPopupZoomOut 0.3s cubic-bezier(0.165, 0.84, 0.44, 1) forwards;
    }

    .karya-modal-header-naufaru {
        display: flex; align-items: center; justify-content: space-between;
        font-size: 1rem; color: #ffc107; font-weight: 900;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        padding-bottom: 10px; margin-bottom: 15px;
    }

    .karya-info-box-modal-small {
        background: rgba(255, 193, 7, 0.05);
        border-left: 3px solid #ffc107;
        padding: 8px 12px; border-radius: 10px; margin-bottom: 20px;
    }

    .karya-info-box-modal-small p {
        margin: 0; font-size: 0.75rem; color: rgba(255,255,255,0.6); line-height: 1.4;
    }

    .karya-label-modal {
        display: block; color: #ffc107; font-size: 0.7rem; 
        font-weight: 800; text-transform: uppercase; 
        margin-bottom: 8px; letter-spacing: 1px;
    }

    .karya-input-premium-glass {
        width: 100%; padding: 12px 15px; background: rgba(255,255,255,0.03);
        border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; color: white;
        font-size: 0.9rem; transition: 0.3s; box-sizing: border-box;
    }

    .karya-input-premium-glass:focus {
        border-color: #ffc107; background: rgba(255,255,255,0.08); outline: none;
    }

    .btn-karya-modal-option {
        width: 100%; padding: 14px 18px; text-align: left;
        background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 14px; color: #fff; font-size: 0.88rem; font-weight: 700;
        cursor: pointer; transition: all 0.25s ease; display: flex; justify-content: space-between; align-items: center;
    }

    .btn-karya-modal-option:hover {
        background: rgba(255, 193, 7, 0.15) !important;
        border-color: #ffc107 !important;
        transform: translateY(-2px);
    }
</style>

<div class="glass-card welcome-card" style="margin-top: 50px;">
    <h1>Manajemen Galeri Karya</h1>
    <p style="font-size: 0.85rem; opacity: 0.8;">Tambahkan desain terbaru atau kelola portofolio yang sudah ada.</p>
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
                    <button type="button" id="btn_add_karya_trigger" class="input-glass text-start d-flex justify-content-between align-items-center" onclick="openKaryaModalWindow('modalAddKaryaCategory')" style="cursor: pointer; background: rgba(255,255,255,0.02);">
                        <span id="add_karya_category_label" style="color: #fff; font-size: 0.9rem;">Pilih Kategori...</span>
                        <i class="fas fa-window-restore" style="color: #ffc107; font-size: 0.85rem;"></i>
                    </button>
                </div>

                <div class="form-group mb-4">
                    <label class="label-text">HARGA ASLI (RP)</label>
                    <input type="number" name="price_original" class="input-glass" placeholder="Contoh: 100000" required>
                </div>

                <div class="form-group">
                    <label class="label-text">LINK PROJECT / URL (OPSIONAL)</label>
                    <input type="url" name="link_url" class="input-glass" placeholder="https://google.com/portfolio-anda">
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
                    <textarea name="desc_id" class="input-glass" rows="2" placeholder="Tulis deskripsi dalam bahasa Indonesia..." style="resize:none; font-family:inherit; font-size:0.85rem;"></textarea>
                </div>

                <div class="form-group mb-3">
                    <label class="label-text">DESCRIPTION (ENGLISH)</label>
                    <textarea name="desc_en" class="input-glass" rows="2" placeholder="Write description in English..." style="resize:none; font-family:inherit; font-size:0.85rem;"></textarea>
                </div>

                <div class="form-group">
                    <label class="label-text">説明 (JAPANESE)</label>
                    <textarea name="desc_jp" class="input-glass" rows="2" placeholder="日本語で説明を書いてください..." style="resize:none; font-family:inherit; font-size:0.85rem;"></textarea>
                </div>
            </div>
        </div>

        <button type="submit" class="btn-action" style="background: var(--accent); width: 100%; padding: 18px; font-weight: bold; margin-top: 25px;">
            <i class="fas fa-cloud-upload-alt me-2"></i> UNGGAH KARYA KE GALERI
        </button>
    </form>
</div>

<div id="adminGalleryMainContainer" class="glass-card" style="margin-top: 30px; overflow: visible; display: flex; flex-direction: column; transition: height 0.4s ease; min-height: unset !important;">
    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--glass-border); padding-bottom: 15px; margin-bottom: 25px; position: relative; z-index: 999;">
        <h4 style="margin: 0;"><i class="fas fa-images me-2"></i> Katalog Produk Terunggah</h4>
        
        <div style="display: flex; align-items: center; gap: 10px;">
            <small class="label-text-desc">FILTER:</small>
            <button type="button" class="input-glass d-flex justify-content-between align-items-center" onclick="openKaryaModalWindow('modalFilterKaryaList')" style="width: 220px; cursor: pointer; text-align: left; padding: 10px 15px; background: rgba(255,255,255,0.03);">
                <span id="admin_filter_label" style="font-size: 0.85rem;">Semua Kategori</span>
                <i class="fas fa-filter" style="color: #ffc107; font-size: 0.8rem;"></i>
            </button>
        </div>
    </div>

    <div class="gallery-slider-container">
        <div id="adminGalleryWrapper">
            </div>
    </div>

    <div id="rawGalleryData" style="display: none;">
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
            <div class="admin-katalog-item-column gallery-item cat-<?= $row_karya['product_id']; ?>">
                <div class="admin-katalog-card-box">
                    <div class="admin-katalog-media">
                        <div class="admin-katalog-img-frame">
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
                            <button type="button" class="admin-btn-edit" onclick='openEditKaryaModal(<?= json_encode($row_karya); ?>)'>Edit</button>
                            <button type="button" class="admin-btn-delete" onclick="deletePortfolio(<?= $row_karya['id']; ?>)"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <div class="lang-nav-centered mt-4" id="adminGalleryNav" style="display: none;">
        <button type="button" class="btn-nav-lang" onclick="moveAdminGallery(-1)"><i class="fas fa-chevron-left"></i></button>
        <div class="lang-indicator-wrapper">
            <div id="adminGalleryPageLabel" class="lang-text-dynamic">HALAMAN 1</div>
            <div id="adminGalleryDots" class="lang-dots-container"></div>
        </div>
        <button type="button" class="btn-nav-lang" onclick="moveAdminGallery(1)"><i class="fas fa-chevron-right"></i></button>
    </div>
</div>

<div id="modalAddKaryaCategory" class="karya-modal-overlay-glass" onclick="closeKaryaModalWindow('modalAddKaryaCategory')">
    <div class="karya-modal-content-card" onclick="event.stopPropagation()">
        <div class="karya-modal-header-naufaru" style="color: #fff; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 15px;">
            <span><i class="fas fa-boxes me-2" style="color: #ffc107;"></i> PILIH KATEGORI PRODUK</span>
            <button type="button" class="btn-close-modal-circle" onclick="closeKaryaModalWindow('modalAddKaryaCategory')">×</button>
        </div>
        <div class="karya-info-box-modal-small">
            <p><i class="fas fa-info-circle me-1"></i> Hubungkan hasil unggahan karya ke sub-kategori layanan katalog utama.</p>
        </div>
        <div style="display: flex; flex-direction: column; gap: 10px; max-height: 250px; overflow-y: auto; padding-right: 5px;">
            <?php 
            $q_add_modal_select = mysqli_query($conn, "SELECT id, product_name_id FROM site_products ORDER BY product_name_id ASC");
            while($am_row = mysqli_fetch_assoc($q_add_modal_select)):
            ?>
                <button type="button" class="btn-karya-modal-option" onclick="selectAddKaryaCategory(<?= $am_row['id']; ?>, '<?= htmlspecialchars($am_row['product_name_id']); ?>')">
                    <span><i class="fas fa-box me-2" style="color: #ffc107;"></i> <?= htmlspecialchars($am_row['product_name_id']); ?></span>
                    <i class="fas fa-chevron-right opacity-50" style="font-size: 0.75rem;"></i>
                </button>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<div id="modalFilterKaryaList" class="karya-modal-overlay-glass" onclick="closeKaryaModalWindow('modalFilterKaryaList')">
    <div class="karya-modal-content-card" onclick="event.stopPropagation()">
        <div class="karya-modal-header-naufaru" style="color: #fff; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 15px;">
            <span><i class="fas fa-filter me-2" style="color: #ffc107;"></i> FILTER KATALOG KARYA</span>
            <button type="button" class="btn-close-modal-circle" onclick="closeKaryaModalWindow('modalFilterKaryaList')">×</button>
        </div>
        <div style="display: flex; flex-direction: column; gap: 10px; max-height: 280px; overflow-y: auto; padding-right: 5px; margin-top: 15px;">
            <button type="button" class="btn-karya-modal-option" onclick="selectFilterKaryaCategory('all', 'Semua Kategori')" style="background: rgba(255,193,7,0.02);">
                <span><i class="fas fa-border-all me-2" style="color: #ffc107;"></i> Semua Kategori Karya</span>
                <i class="fas fa-chevron-right opacity-50"></i>
            </button>
            <?php 
            mysqli_data_seek($q_add_modal_select, 0);
            while($filt_row = mysqli_fetch_assoc($q_add_modal_select)):
            ?>
                <button type="button" class="btn-karya-modal-option" onclick="selectFilterKaryaCategory('cat-<?= $filt_row['id']; ?>', '<?= htmlspecialchars($filt_row['product_name_id']); ?>')">
                    <span><i class="fas fa-images me-2" style="color: #ffc107;"></i> <?= htmlspecialchars($filt_row['product_name_id']); ?></span>
                    <i class="fas fa-chevron-right opacity-50"></i>
                </button>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<div id="editModal" class="karya-modal-overlay-glass" onclick="closeKaryaModalWindow('editModal')">
    <div class="video-modal-content-card" style="width: 500px; background: rgba(20, 20, 20, 0.85); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 24px; padding: 30px;" onclick="event.stopPropagation()">
        <div class="video-modal-header-naufaru" style="color: #ffc107; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 12px; margin-bottom: 15px;">
            <span><i class="fas fa-edit me-2"></i> EDIT KATALOG KARYA</span>
            <button type="button" class="btn-close-modal-circle" onclick="closeKaryaModalWindow('editModal')">×</button>
        </div>

        <form action="proses_update_portfolio.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="portfolio_id" id="edit_id">
            
            <div class="modal-body-scroll-karya">
                <div class="form-group mb-4">
                    <label class="karya-label-modal">GANTI GAMBAR (OPSIONAL)</label>
                    <input type="file" name="portfolio_image" class="video-input-premium-glass">
                </div>

                <div class="form-group mb-3">
                    <label class="karya-label-modal">JUDUL KARYA (BAHASA INDONESIA)</label>
                    <input type="text" name="title_id" id="edit_title_id" class="video-input-premium-glass" required>
                </div>

                <div class="form-group mb-3">
                    <label class="karya-label-modal">KATEGORI PRODUK</label>
                    <select name="product_id" id="edit_product_id" class="video-input-premium-glass" style="background: #151515; color: #fff;">
                        <?php 
                        mysqli_data_seek($q_add_modal_select, 0);
                        while($rm_karya = mysqli_fetch_assoc($q_add_modal_select)): ?>
                            <option value="<?= $rm_karya['id']; ?>"><?= htmlspecialchars($rm_karya['product_name_id']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group mb-4">
                    <label class="karya-label-modal">HARGA PRODUK (RP)</label>
                    <input type="number" name="price_original" id="edit_price_original" class="video-input-premium-glass" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;" class="mb-4">
                    <div class="form-group">
                        <label class="karya-label-modal">JUDUL (ENGLISH)</label>
                        <input type="text" name="title_en" id="edit_title_en" class="video-input-premium-glass">
                    </div>
                    <div class="form-group">
                        <label class="karya-label-modal">JUDUL (JAPANESE)</label>
                        <input type="text" name="title_jp" id="edit_title_jp" class="video-input-premium-glass">
                    </div>
                </div>

                <div class="form-group mb-4">
                    <label class="karya-label-modal">LINK PROJECT / URL (OPSIONAL)</label>
                    <input type="url" name="link_url" id="edit_link_url" class="video-input-premium-glass" placeholder="https://...">
                </div>

                <div class="form-group mb-3">
                    <label class="karya-label-modal">DESKRIPSI (ID)</label>
                    <textarea name="desc_id" id="edit_desc_id" class="video-input-premium-glass" rows="2" style="resize:none; font-family:inherit;"></textarea>
                </div>
                <div class="form-group mb-3">
                    <label class="karya-label-modal">DESCRIPTION (EN)</label>
                    <textarea name="desc_en" id="edit_desc_en" class="video-input-premium-glass" rows="2" style="resize:none; font-family:inherit;"></textarea>
                </div>
                <div class="form-group mb-3">
                    <label class="karya-label-modal">説明 (JP)</label>
                    <textarea name="desc_jp" id="edit_desc_jp" class="video-input-premium-glass" rows="2" style="resize:none; font-family:inherit;"></textarea>
                </div>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="button" onclick="closeKaryaModalWindow('editModal')" class="btn-action" style="flex:1; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); color: #fff;">BATAL</button>
                <button type="submit" class="btn-action" style="background: var(--accent); flex:2; font-weight: bold; color:#fff;">
                    <i class="fas fa-save me-2"></i> SIMPAN PERUBAHAN
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // --- ANIMASI MASUK MODAL WINDOWS KARYA ---
    function openKaryaModalWindow(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'flex';
            const card = modal.querySelector('.karya-modal-content-card');
            if(card) {
                card.classList.remove('anim-out');
                card.classList.add('anim-in');
            }
        }
    }

    // --- ANIMASI KELUAR MODAL WINDOWS KARYA ---
    function closeKaryaModalWindow(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            const card = modal.querySelector('.karya-modal-content-card') || modal.querySelector('.video-modal-content-card');
            if(card) {
                card.classList.remove('anim-in');
                card.classList.add('anim-out');
                setTimeout(() => {
                    modal.style.display = 'none';
                    card.classList.remove('anim-out');
                }, 290);
            } else {
                modal.style.display = 'none';
            }
        }
    }

    // --- SELEKSI INTERAKSI POP-UP SELEKSI KATEGORI BARU ---
    function selectAddKaryaCategory(id, label) {
        document.getElementById('add_karya_product_id').value = id;
        document.getElementById('add_karya_category_label').innerText = label;
        document.getElementById('add_karya_category_label').style.color = '#ffc107';
        closeKaryaModalWindow('modalAddKaryaCategory');
    }

    // --- SELEKSI INTERAKSI POP-UP FILTER KATALOG BAWAH ---
    function selectFilterKaryaCategory(value, label) {
        document.getElementById('admin_filter_label').innerText = label;
        
        // Pemicu fungsi render bawaan file global script admin_script.js Anda
        if (typeof updateGalleryFilter === 'function') {
            updateGalleryFilter(value);
        } else if (typeof prosesFilterData === 'function') {
            prosesFilterData(value);
        } else if (typeof window.prosesFilterData === 'function') {
            window.prosesFilterData(value);
        } else {
            // Fallback manual kustom jika script eksternal dimuat terlambat
            const rawGal = $('#rawGalleryData');
            const targetWrap = $('#adminGalleryWrapper');
            if(targetWrap.length) {
                targetWrap.empty();
                rawGal.find('.gallery-item').each(function() {
                    if (value === 'all' || $(this).hasClass(value)) {
                        targetWrap.append($(this).clone());
                    }
                });
            }
        }
        closeKaryaModalWindow('modalFilterKaryaList');
    }

    // --- OPEN WINDOW FORM EDIT KARYA DATA ---
    function openEditKaryaModal(data) {
        document.getElementById('edit_id').value = data.id;
        document.getElementById('edit_title_id').value = data.title_id;
        document.getElementById('edit_product_id').value = data.product_id;
        document.getElementById('edit_price_original').value = data.price_original;
        document.getElementById('edit_title_en').value = data.title_en;
        document.getElementById('edit_title_jp').value = data.title_jp;
        document.getElementById('edit_link_url').value = data.link_url;
        document.getElementById('edit_desc_id').value = data.desc_id;
        document.getElementById('edit_desc_en').value = data.desc_en;
        document.getElementById('edit_desc_jp').value = data.desc_jp;

        const modal = document.getElementById('editModal');
        if(modal) {
            modal.style.display = 'flex';
            const card = modal.querySelector('.video-modal-content-card');
            if(card) {
                card.classList.remove('anim-out');
                card.classList.add('anim-in');
            }
        }
    }
</script>