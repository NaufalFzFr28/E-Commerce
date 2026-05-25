<?php
/**
 * File: admin/sections/section_karya_info.php
 * Deskripsi: Komponen Modular Manajemen Pesan Info/Alert Portfolio (Multibahasa)
 * Pembaruan: Isolasi Mandiri, Bebas Konflik JS/CSS, Integrasi Fallback SweetAlert2
 */

if (!isset($conn)) {
    include '../config.php';
}

// Mengunci pointer bahasa lokal dashboard
$lang_karya_info = $_SESSION['lang'] ?? 'id';
?>

<style>
    .nfr-info-manage-wrapper {
        display: flex;
        flex-direction: column;
        gap: 25px;
        margin-top: 20px;
    }

    .info-manage-item {
        position: relative;
        background: rgba(255, 255, 255, 0.01) !important;
        border: 1px solid rgba(255, 255, 255, 0.06) !important;
        border-radius: 16px !important;
        padding: 25px !important;
        box-sizing: border-box;
        transition: border-color 0.3s ease;
        padding: 35px 25px 25px 25px !important; /
    }

    .info-manage-item:hover {
        border-color: rgba(255, 193, 7, 0.3) !important;
    }

    .info-item-controls {
        position: absolute;
        top: 20px;
        right: 20px;
        display: flex;
        align-items: center;
        gap: 12px;
        z-index: 10;
    }

    /* Kustomisasi Kontrol Penutup Glassmorphic */
    .glass-control {
        background: rgba(255, 255, 255, 0.03) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        color: rgba(255, 255, 255, 0.6) !important;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .glass-control:hover {
        background: #ef4c4d !important;
        border-color: #ef4c4d !important;
        color: #fff !important;
        transform: scale(1.05);
    }

    /* Penyesuaian Spacing Label Input Info */
    .nfr-info-label {
        display: block;
        color: #ffc107;
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
        margin-top: 15px !important;   
        margin-bottom: 10px !important; 
        letter-spacing: 0.8px;
    }

    /* Memastikan baris pertama form tambah tidak memiliki margin-top berlebih */
    .row:first-child .nfr-info-label {
        margin-top: 15px !important;
    }

    .btn-delete-info-alt.glass-control {
        background: rgba(239, 76, 77, 0.1);
        border: 1px solid rgba(239, 76, 77, 0.25);
        color: #ef4c4d;
        width: 34px;
        height: 34px;
        border-radius: 8px !important; /* Diubah dari 50% menjadi 8px agar berbentuk kotak */
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .custom-check-container {
        display: block;
        position: relative;
        width: 34px;
        height: 34px;
        cursor: pointer;
    }

    .checkmark {
        position: absolute;
        top: 0; left: 0;
        width: 34px; height: 34px;
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 8px !important; /* Diubah dari 50% menjadi 8px agar berbentuk kotak */
        display: flex;
        align-items: center;
        justify-content: center;
        color: transparent;
        transition: all 0.2s ease;
    }

    .custom-check-input:checked ~ .checkmark {
        background: rgba(40, 167, 69, 0.15);
        border-color: #28a745;
        color: #28a745;
    }

    .custom-check-container:hover .checkmark {
        border-color: #ffc107;
    }

    
</style>

<div class="glass-card welcome-card" style="margin-top: 50px;">
    <h1>Konfigurasi Pesan Info Portfolio</h1>
    <p style="font-size: 0.85rem; opacity: 0.8;">Atur pesan promosi atau pemberitahuan penting yang akan tampil di atas galeri.</p>
</div>

<div class="glass-card mb-5" style="overflow: visible;">
    <h4 style="font-size: 1rem; border-bottom: 1px solid var(--glass-border); padding-bottom: 15px; margin-bottom: 0px; margin-top: 0px;">
        <i class="fas fa-plus-circle me-2"></i> Tambah Info Baru
    </h4>
    <form action="proses_update_portfolio_info.php" method="POST">
        <input type="hidden" name="action" value="add_info">
        
        <div class="input-stack-box">
            <div class="row g-4">
                <div class="col-md-4">
                    <label class="nfr-info-label">PESAN UTAMA (ID)</label>
                    <input type="text" name="text_id" class="input-glass" placeholder="Contoh: Punya rencana desain?" required>
                </div>
                <div class="col-md-4">
                    <label class="nfr-info-label">MAIN MESSAGE (EN)</label>
                    <input type="text" name="text_en" class="input-glass" placeholder="Example: Have a design plan?">
                </div>
                <div class="col-md-4">
                    <label class="nfr-info-label">メインメッセージ (JP)</label>
                    <input type="text" name="text_jp" class="input-glass" placeholder="例：デザイン案はありますか？">
                </div>
            </div>

            <div class="row g-4 py-4"> 
                <div class="col-md-4">
                    <label class="nfr-info-label">TEKS LINK (ID)</label>
                    <input type="text" name="link_text_id" class="input-glass" placeholder="Contoh: Chat sekarang!">
                </div>
                <div class="col-md-4">
                    <label class="nfr-info-label">LINK TEXT (EN)</label>
                    <input type="text" name="link_text_en" class="input-glass" placeholder="Example: Chat now!">
                </div>
                <div class="col-md-4">
                    <label class="nfr-info-label">リンクテキスト (JP)</label>
                    <input type="text" name="link_text_jp" class="input-glass" placeholder="例：今すぐチャット！">
                </div>
            </div>

            <div class="row g-4">
                <div class="col-12">
                    <label class="nfr-info-label">URL TUJUAN (WHATSAPP / EXTERNAL LINK)</label>
                    <input type="url" name="link_url" class="input-glass" placeholder="https://wa.me/62895...">
                </div>
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn-action" style="background: var(--accent); width: 100%; padding: 18px; font-weight: bold; margin-top: 20px;">
                <i class="fas fa-upload me-2"></i> UNGGAH INFO BARU
            </button>
        </div>
    </form>
</div>

<div class="glass-card">
    <h4 style="font-size: 1rem; border-bottom: 1px solid var(--glass-border); padding-bottom: 15px; margin-bottom: 25px; margin-top: 0px;">
        <i class="fas fa-tasks me-2"></i> Kelola Info Info Aktif
    </h4>
    
    <form action="proses_update_portfolio_info.php" method="POST">
        <input type="hidden" name="action" value="update_info">
        
        <div id="info-list-container" class="nfr-info-manage-wrapper">
            <?php 
            $q_list_info = mysqli_query($conn, "SELECT * FROM site_portfolio_alerts ORDER BY id DESC");
            while($ai = mysqli_fetch_assoc($q_list_info)):
            ?>
            <div class="info-manage-item">
                <input type="hidden" name="info_ids[]" value="<?php echo $ai['id']; ?>">
                
                <div class="info-item-controls">
                    <label class="custom-check-container">
                        <input type="checkbox" name="is_active[]" class="custom-check-input" <?php echo $ai['is_active'] ? 'checked' : ''; ?>>
                        <span class="checkmark">
                            <i class="fas fa-check"></i>
                        </span>
                    </label>

                    <button type="button" class="btn-delete-info-alt glass-control" onclick="window.nfr_info_triggerDelete(<?php echo $ai['id']; ?>)">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="row g-4">
                    <div class="col-md-4">
                        <label class="nfr-info-label">PESAN (ID)</label>
                        <input type="text" name="text_id[]" class="input-glass" value="<?php echo htmlspecialchars($ai['text_id'], ENT_QUOTES); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="nfr-info-label">MESSAGE (EN)</label>
                        <input type="text" name="text_en[]" class="input-glass" value="<?php echo htmlspecialchars($ai['text_en'], ENT_QUOTES); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="nfr-info-label">メッセージ (JP)</label>
                        <input type="text" name="text_jp[]" class="input-glass" value="<?php echo htmlspecialchars($ai['text_jp'], ENT_QUOTES); ?>">
                    </div>
                </div>

                <div class="row g-4 py-3"> 
                    <div class="col-md-4">
                        <label class="nfr-info-label">LINK (ID)</label>
                        <input type="text" name="link_text_id[]" class="input-glass" value="<?php echo htmlspecialchars($ai['link_text_id'], ENT_QUOTES); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="nfr-info-label">LINK (EN)</label>
                        <input type="text" name="link_text_en[]" class="input-glass" value="<?php echo htmlspecialchars($ai['link_text_en'], ENT_QUOTES); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="nfr-info-label">LINK (JP)</label>
                        <input type="text" name="link_text_jp[]" class="input-glass" value="<?php echo htmlspecialchars($ai['link_text_jp'], ENT_QUOTES); ?>">
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-12">
                        <label class="nfr-info-label">URL LINK</label>
                        <input type="url" name="link_url[]" class="input-glass" value="<?php echo htmlspecialchars($ai['link_url'], ENT_QUOTES); ?>">
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn-action" style="background: var(--accent); width: 100%; padding: 18px; font-weight: bold;">
                <i class="fas fa-save me-2"></i> SIMPAN PERUBAHAN INFO
            </button>
        </div>
    </form>
</div>

<script>
    (function() {
        "use strict";

        // Blok Fungsi Eksklusif Trigger Delete Data Alert Info Portfolio
        window.nfr_info_triggerDelete = function(idAlert) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Hapus Pesan Info?',
                    text: "Riwayat alert pemberitahuan ini akan dihapus permanen dari sistem!",
                    icon: 'warning',
                    showCancelButton: true,
                    background: '#1a1a1a',
                    color: '#fff',
                    confirmButtonColor: '#ef4c4d',
                    cancelButtonColor: 'rgba(255,255,255,0.1)',
                    confirmButtonText: 'Ya, Hapus Info!',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then((res) => {
                    if (res.isConfirmed) {
                        window.location.href = 'proses_update_portfolio_info.php?delete_info_id=' + idAlert;
                    }
                });
            } else {
                // Fallback aman jika CDN SweetAlert diblokir tracking prevention browser
                if (confirm("Apakah Anda yakin ingin menghapus alert info ini secara permanen?")) {
                    window.location.href = 'proses_update_portfolio_info.php?delete_info_id=' + idAlert;
                }
            }
        };

    })();
</script>