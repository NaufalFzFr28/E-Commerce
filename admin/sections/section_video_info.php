<?php
/**
 * File: admin/sections/section_video_info.php
 * Deskripsi: Komponen Modular Manajemen Pesan Info/Alert Galeri Video (Multibahasa)
 * Pembaruan: Isolasi Mandiri Sandboxing, Jarak Spacing Sempurna, Kotak Rounded Controls
 */

if (!isset($conn)) {
    include '../config.php';
}

// Mengunci pointer bahasa lokal dashboard
$lang_video_info = $_SESSION['lang'] ?? 'id';
?>

<style>
    .nfr-video-info-manage-wrapper {
        display: flex;
        flex-direction: column;
        gap: 25px;
        margin-top: 20px;
    }

    /* Container Utama Baris Info - Mengunci Ruang Atas Agar Tombol Tidak Menimpa Textbox */
    .nfr-video-info-manage-item {
        position: relative;
        background: rgba(255, 255, 255, 0.01) !important;
        border: 1px solid rgba(255, 255, 255, 0.06) !important;
        border-radius: 16px !important;
        box-sizing: border-box;
        transition: border-color 0.3s ease;
        padding: 40px 25px 25px 25px !important; /* Ruang atas dinaikkan jadi 40px */
    }

    .nfr-video-info-manage-item:hover {
        border-color: rgba(255, 193, 7, 0.3) !important;
    }

    .nfr-video-info-item-controls {
        position: absolute;
        top: 20px;
        right: 20px;
        display: flex;
        align-items: center;
        gap: 12px;
        z-index: 10;
    }

    /* Penyesuaian Spacing Bernapas Label Input Info Video */
    .nfr-vid-info-label {
        display: block;
        color: #ffc107;
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
        margin-top: 16px !important;    /* Jarak renggang dari batas bawah textbox di atasnya */
        margin-bottom: 10px !important; /* Jarak renggang ke textbox pasangannya */
        letter-spacing: 0.8px;
    }

    /* Grup Tombol Kontrol Kotak Rounded Premium */
    .btn-delete-video-info-alt.glass-control-box {
        background: rgba(239, 76, 77, 0.1);
        border: 1px solid rgba(239, 76, 77, 0.25);
        color: #ef4c4d;
        width: 34px;
        height: 34px;
        border-radius: 8px !important; /* Bentuk kotak rounded minimalis */
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-delete-video-info-alt.glass-control-box:hover {
        background: #ef4c4d;
        color: #fff;
        box-shadow: 0 0 10px rgba(239, 76, 77, 0.4);
        transform: scale(1.05);
    }

    .nfr-vid-info-check-container {
        display: block;
        position: relative;
        width: 34px;
        height: 34px;
        cursor: pointer;
    }

    .nfr-vid-info-checkmark {
        position: absolute;
        top: 0; left: 0;
        width: 34px; height: 34px;
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 8px !important; /* Bentuk kotak rounded minimalis */
        display: flex;
        align-items: center;
        justify-content: center;
        color: transparent;
        transition: all 0.2s ease;
    }

    .custom-check-input:checked ~ .nfr-vid-info-checkmark {
        background: rgba(40, 167,  green, 0.15);
        background: rgba(40, 167, 69, 0.15);
        border-color: #28a745;
        color: #28a745;
    }

    .nfr-vid-info-check-container:hover .nfr-vid-info-checkmark {
        border-color: #ffc107;
    }
</style>

<div class="glass-card welcome-card" style="margin-top: 50px;">
    <h1>Konfigurasi Pesan Info Video</h1>
    <p style="font-size: 0.85rem; opacity: 0.8;">Atur pesan promosi atau pemberitahuan penting yang akan tampil di atas galeri video.</p>
</div>

<div class="glass-card mb-5" style="overflow: visible;">
    <h4 style="font-size: 1rem; border-bottom: 1px solid var(--glass-border); padding-bottom: 15px; margin-bottom: 0px; margin-top: 0px;">
        <i class="fas fa-plus-circle me-2"></i> Tambah Info Video Baru
    </h4>
    <form action="proses_update_video_info.php" method="POST">
        <input type="hidden" name="action" value="add_video_info">
        
        <div class="input-stack-box">
            <div class="row g-4">
                <div class="col-md-4">
                    <label class="nfr-vid-info-label">PESAN UTAMA (ID)</label>
                    <input type="text" name="text_id" class="input-glass" placeholder="Contoh: Punya konsep video menarik?" required>
                </div>
                <div class="col-md-4">
                    <label class="nfr-vid-info-label">MAIN MESSAGE (EN)</label>
                    <input type="text" name="text_en" class="input-glass" placeholder="Example: Have an interesting video concept?">
                </div>
                <div class="col-md-4">
                    <label class="nfr-vid-info-label">メインメッセージ (JP)</label>
                    <input type="text" name="text_jp" class="input-glass" placeholder="例：面白い動画の企画はありますか？">
                </div>
            </div>

            <div class="row g-4"> 
                <div class="col-md-4">
                    <label class="nfr-vid-info-label">TEKS LINK (ID)</label>
                    <input type="text" name="link_text_id" class="input-glass" placeholder="Contoh: Konsultasi sekarang!">
                </div>
                <div class="col-md-4">
                    <label class="nfr-vid-info-label">LINK TEXT (EN)</label>
                    <input type="text" name="link_text_en" class="input-glass" placeholder="Example: Consult now!">
                </div>
                <div class="col-md-4">
                    <label class="nfr-vid-info-label">リンクテキスト (JP)</label>
                    <input type="text" name="link_text_jp" class="input-glass" placeholder="例：今すぐ相談する！">
                </div>
            </div>

            <div class="row g-4">
                <div class="col-12">
                    <label class="nfr-vid-info-label">URL TUJUAN (WHATSAPP / EXTERNAL LINK)</label>
                    <input type="url" name="link_url" class="input-glass" placeholder="https://wa.me/62895...">
                </div>
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn-action" style="background: var(--accent); width: 100%; padding: 18px; font-weight: bold; margin-top: 20px;">
                <i class="fas fa-upload me-2"></i> UNGGAH INFO VIDEO BARU
            </button>
        </div>
    </form>
</div>

<div class="glass-card">
    <h4 style="font-size: 1rem; border-bottom: 1px solid var(--glass-border); padding-bottom: 15px; margin-bottom: 25px; margin-top: 0px;">
        <i class="fas fa-tasks me-2"></i> Kelola Info Video Aktif
    </h4>
    
    <form action="proses_update_video_info.php" method="POST">
        <input type="hidden" name="action" value="update_video_info">
        
        <div id="video-info-list-container" class="nfr-video-info-manage-wrapper">
            <?php 
            // Query diarahkan ke database tabel alerts video terpisah
            $q_list_video_info = mysqli_query($conn, "SELECT * FROM site_video_alerts ORDER BY id DESC");
            while($vi = mysqli_fetch_assoc($q_list_video_info)):
            ?>
            <div class="nfr-video-info-manage-item">
                <input type="hidden" name="info_ids[]" value="<?php echo $vi['id']; ?>">
                
                <div class="nfr-video-info-item-controls">
                    <label class="nfr-vid-info-check-container">
                        <input type="checkbox" name="is_active[]" class="custom-check-input" <?php echo $vi['is_active'] ? 'checked' : ''; ?>>
                        <span class="nfr-vid-info-checkmark">
                            <i class="fas fa-check"></i>
                        </span>
                    </label>

                    <button type="button" class="btn-delete-video-info-alt glass-control-box" onclick="window.nfr_video_info_triggerDelete(<?php echo $vi['id']; ?>)">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="row g-4">
                    <div class="col-md-4">
                        <label class="nfr-vid-info-label">PESAN (ID)</label>
                        <input type="text" name="text_id[]" class="input-glass" value="<?php echo htmlspecialchars($vi['text_id'], ENT_QUOTES); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="nfr-vid-info-label">MESSAGE (EN)</label>
                        <input type="text" name="text_en[]" class="input-glass" value="<?php echo htmlspecialchars($vi['text_en'], ENT_QUOTES); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="nfr-vid-info-label">メッセージ (JP)</label>
                        <input type="text" name="text_jp[]" class="input-glass" value="<?php echo htmlspecialchars($vi['text_jp'], ENT_QUOTES); ?>">
                    </div>
                </div>

                <div class="row g-4"> 
                    <div class="col-md-4">
                        <label class="nfr-vid-info-label">LINK (ID)</label>
                        <input type="text" name="link_text_id[]" class="input-glass" value="<?php echo htmlspecialchars($vi['link_text_id'], ENT_QUOTES); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="nfr-vid-info-label">LINK (EN)</label>
                        <input type="text" name="link_text_en[]" class="input-glass" value="<?php echo htmlspecialchars($vi['link_text_en'], ENT_QUOTES); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="nfr-vid-info-label">LINK (JP)</label>
                        <input type="text" name="link_text_jp[]" class="input-glass" value="<?php echo htmlspecialchars($vi['link_text_jp'], ENT_QUOTES); ?>">
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-12">
                        <label class="nfr-vid-info-label">URL LINK</label>
                        <input type="url" name="link_url[]" class="input-glass" value="<?php echo htmlspecialchars($vi['link_url'], ENT_QUOTES); ?>">
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn-action" style="background: var(--accent); width: 100%; padding: 18px; font-weight: bold;">
                <i class="fas fa-save me-2"></i> SIMPAN PERUBAHAN INFO VIDEO
            </button>
        </div>
    </form>
</div>

<script>
    (function() {
        "use strict";

        // Global function dengan prefix eksklusif video agar aman dari intercept script portfolio foto
        window.nfr_video_info_triggerDelete = function(idVidAlert) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Hapus Info Video?',
                    text: "Pesan pemberitahuan alert video ini akan dihapus permanen dari database!",
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
                        window.location.href = 'proses_update_video_info.php?delete_video_info_id=' + idVidAlert;
                    }
                });
            } else {
                if (confirm("Apakah Anda yakin ingin menghapus alert info video ini secara permanen?")) {
                    window.location.href = 'proses_update_video_info.php?delete_video_info_id=' + idVidAlert;
                }
            }
        };

    })();
</script>