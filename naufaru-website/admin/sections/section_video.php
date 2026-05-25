<?php
/**
 * File: admin/sections/section_video.php
 * Deskripsi: Komponen Modular Manajemen Galeri Video dengan Sistem Pop-up Modal Premium Glass
 * Pembaruan: Mengunci Jarak Spacing Antar Card (Silang Kuning Fix) Menggunakan Sistem Grid Col-md-4 mb-4
 */

if (!isset($conn)) {
    include '../config.php';
}

$lang = $_SESSION['lang'] ?? 'id';
?>

<style>
    /* Grid Layout Form */
    .video-form-grid {
        display: grid;
        grid-template-columns: 1fr 2fr;
        gap: 30px;
    }

    .video-slider-container {
        width: 100%;
        overflow: hidden;
        padding: 10px 0;
    }

    #adminVideoWrapper {
        display: flex;
        transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        width: 100%;
        align-items: flex-start;
    }

    .video-media-preview-frame {
        position: relative;
        width: calc(100% + 16px);
        margin-left: -8px;
        margin-top: -8px;

        /* LANDSCAPE RESPONSIVE */
        aspect-ratio: 16 / 9;
        height: auto;

        background: #000;
        border-radius: 16px 16px 12px 12px;
        overflow: hidden;

        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Preview image full - FIX FULL COVER CONTENT */
    .video-media-preview-frame img {
        position: absolute;
        top: 0; 
        left: 0; 
        width: 100%; 
        height: 100%;
        object-fit: cover;
        object-position: center;
        transition: transform 0.5s ease;
    }

    /* Hover zoom lembut */
    .admin-katalog-card-box:hover .video-media-preview-frame img {
        transform: scale(1.04);
    }

    /* Pembungkus Kartu Video Internal Mengunci Jarak Silang Kuning */
    .nfr-video-item-card-inner {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
        padding: 5px;
    }

    /* Pembatasan Teks Sinopsis 2 Baris Ketat Otomatis Titik-Titik (...) */
    .nfr-video-clamped-desc {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: normal;
        height: 36px;
        line-height: 1.5;
        margin-top: 10px;
        margin-bottom: 0px;
    }

    /* --- Keyframe Animasi Pop-up In & Out --- */
    @keyframes videoPopupZoomIn { 
        from { opacity: 0; transform: scale(0.9); } 
        to { opacity: 1; transform: scale(1); } 
    }
    @keyframes videoPopupZoomOut { 
        from { opacity: 1; transform: scale(1); } 
        to { opacity: 0; transform: scale(0.9); } 
    }

    /* Overlay Backdrop Blur */
    .video-modal-overlay-glass {
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

    /* Kartu Utama Modal */
    .video-modal-content-card {
        width: 440px; 
        padding: 30px; 
        background: rgba(20, 20, 20, 0.85);
        border: 1px solid rgba(255, 255, 255, 0.1); 
        border-radius: 24px;
        position: relative; 
        box-shadow: 0 25px 50px rgba(0,0,0,0.7);
        transform: scale(0.9);
        opacity: 0;
    }

    /* Trigger Kelas Animasi via JS */
    .video-modal-content-card.anim-in {
        animation: videoPopupZoomIn 0.35s cubic-bezier(0.165, 0.84, 0.44, 1) forwards;
    }

    .video-modal-content-card.anim-out {
        animation: videoPopupZoomOut 0.3s cubic-bezier(0.165, 0.84, 0.44, 1) forwards;
    }

    /* Header Modal & Pembatas */
    .video-modal-header-naufaru {
        display: flex; align-items: center; justify-content: space-between;
        font-size: 0.95rem; font-weight: 800; letter-spacing: 0.5px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        padding-bottom: 15px; margin-bottom: 15px;
    }

    /* Style Premium Bulat Tombol Close */
    .btn-close-modal-circle {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.15);
        color: rgba(255, 255, 255, 0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.25s ease;
        padding: 0;
    }

    .btn-close-modal-circle:hover {
        background: rgba(239, 76, 77, 0.2);
        border-color: #ef4c4d;
        color: #ef4c4d;
        transform: rotate(90deg);
    }

    .video-info-box-modal-small {
        background: rgba(255, 255, 255, 0.02);
        border-left: 3px solid rgba(255, 255, 255, 0.2);
        padding: 10px 14px; border-radius: 10px; margin-bottom: 20px;
    }

    .video-info-box-modal-small p {
        margin: 0; font-size: 0.75rem; color: rgba(255,255,255,0.5); line-height: 1.4;
    }

    .video-label-modal {
        display: block; color: #ffc107; 
        font-size: 0.7rem; 
        font-weight: 800; 
        text-transform: uppercase; 
        margin-bottom: 8px; 
        margin-top: 10px;
        letter-spacing: 1px;
    }

    .video-input-premium-glass {
        width: 100%; padding: 12px 15px; background: rgba(255,255,255,0.03);
        border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; color: white;
        font-size: 0.9rem; transition: 0.3s; box-sizing: border-box;
    }

    .video-input-premium-glass:focus {
        border-color: #ffc107; background: rgba(255,255,255,0.08); outline: none;
    }

    /* Pilihan List Menu di Dalam Modal Windows */
    .btn-video-modal-option {
        width: 100%; padding: 14px 18px; text-align: left;
        background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 14px; color: #fff; font-size: 0.88rem; font-weight: 700;
        cursor: pointer; transition: all 0.25s ease; display: flex; justify-content: space-between; align-items: center;
    }

    .btn-video-modal-option:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }

    /* Kelas State Warna untuk Tombol Pemicu Luar */
    .trigger-theme-yellow {
        border-color: rgba(255, 193, 7, 0.3) !important;
        box-shadow: inset 0 0 10px rgba(255, 193, 7, 0.05);
    }
    .trigger-theme-green {
        border-color: rgba(46, 204, 113, 0.3) !important;
        box-shadow: inset 0 0 10px rgba(46, 204, 113, 0.05);
    }

    @media (max-width: 992px) {
        .video-form-grid { grid-template-columns: 1fr; gap: 20px; }
    }

    /* =========================
    GRID CARD VIDEO PREMIUM
     ========================= */
    .video-slider-container {
        width: 100%;
        overflow: hidden;
        padding: 10px 4px;
    }

    /* Wrapper utama katalog video */
    #adminVideoWrapper {
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 28px; /* JARAK ANTAR CARD */
        width: 100%;
        align-items: stretch;
    }

    /* Pastikan card full dan tidak mepet */
    #adminVideoWrapper .video-item {
        width: 100%;
        margin: 0 !important;
        padding: 0 !important;
        display: flex;
    }

    /* Card utama */
    .admin-katalog-card-box {
        width: 100%;
        height: 100%;
        transition: 
            transform 0.35s ease,
            box-shadow 0.35s ease,
            border-color 0.35s ease;
    }

    /* Hover card lembut */
    .admin-katalog-card-box:hover {
        transform: translateY(-4px);
        border-color: rgba(255,255,255,0.12) !important;
        box-shadow: 0 12px 30px rgba(0,0,0,0.28);
    }

    /* Inner spacing card */
    .nfr-video-item-card-inner {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
        padding: 8px;
    }

    /* Area teks */
    .content-text-area {
        padding-bottom: 14px; /* JARAK TEKS KE TOMBOL */
    }

    /* Area tombol */
    .admin-katalog-actions {
        margin-top: 18px !important;
        padding-top: 16px !important;
        border-top: 1px solid rgba(255,255,255,0.06);
        display: flex;
        gap: 12px;
        align-items: center;
    }

    /* Tombol edit premium */
    .admin-btn-edit {
        flex: 1;
        height: 42px;
        border: 1px solid rgba(255,255,255,0.08);
        background: rgba(255,255,255,0.04);
        color: #fff;
        border-radius: 12px;
        font-size: 0.84rem;
        font-weight: 600;
        transition:
            all 0.32s cubic-bezier(0.4, 0, 0.2, 1),
            transform 0.22s ease;
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
    }

    /* Hover tombol edit */
    .admin-btn-edit:hover {
        background: rgba(255,255,255,0.09);
        border-color: rgba(255,255,255,0.18);
        transform: translateY(-2px);
        box-shadow: 0 8px 18px rgba(0,0,0,0.25);
    }

    /* Efek klik */
    .admin-btn-edit:active {
        transform: scale(0.97);
    }

    /* Tombol delete */
    .admin-btn-delete {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        flex-shrink: 0;
    }

    /* Responsive tablet */
    @media (max-width: 1100px) {
        #adminVideoWrapper {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    /* Responsive mobile */
    @media (max-width: 768px) {
        #adminVideoWrapper {
            grid-template-columns: 1fr;
            gap: 20px;
        }
    }

    .admin-katalog-title {
        font-size: 1rem;
        font-weight: 700;
        color: #fff;
        line-height: 1.45;
        margin-top: 14px;
        margin-bottom: 12px;
        word-break: break-word;

        /* TAMPILKAN SEMUA JUDUL */
        display: block;
        overflow: visible;
        min-height: auto;
    }

    .admin-katalog-desc {
        color: rgba(255,255,255,0.62);
        font-size: 0.84rem;
        line-height: 1.7;
    }

    /* Clamp caption */
    .nfr-video-clamped-desc {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: normal;
        min-height: 70px;
        margin-top: 12px;
        margin-bottom: 6px;
    }

    /* Tombol baca selengkapnya */
    .btn-read-more-video {
        padding: 0;
        background: transparent;
        border: none;
        color: #74b9ff;
        font-size: 0.78rem;
        font-weight: 600;
        cursor: pointer;
        transition: 0.25s ease;
    }

    .btn-read-more-video:hover {
        color: #ffffff;
        transform: translateX(2px);
    }

    /* --- ANIMASI DESKRIPSI BACA SELENGKAPNYA (IN/OUT) --- */
    .video-full-desc {
        margin-top: 10px;
        color: rgba(255,255,255,0.72);
        font-size: 0.82rem;
        line-height: 1.7;
        overflow: hidden;
        display: none;
    }

    .video-full-desc.anim-show {
        display: block;
        animation: fadeVideoDescIn 0.3s cubic-bezier(0.4, 0, 0.2, 1) forwards;
    }

    .video-full-desc.anim-hide {
        animation: fadeVideoDescOut 0.25s cubic-bezier(0.4, 0, 0.2, 1) forwards;
    }

    @keyframes fadeVideoDescIn {
        from { opacity: 0; transform: translateY(-6px); max-height: 0; }
        to { opacity: 1; transform: translateY(0); max-height: 500px; }
    }
    @keyframes fadeVideoDescOut {
        from { opacity: 1; transform: translateY(0); max-height: 500px; }
        to { opacity: 0; transform: translateY(-6px); max-height: 0; }
    }

    /* ==========================================
   PREMIUM CUSTOM RADIO GROUP STATUS DISPLAY
   ========================================== */
.video-status-radio-group {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    width: 100%;
    margin-top: 5px;
}

/* Sembunyikan Input Radio Asli Browser */
.status-radio-card-label input[type="radio"] {
    display: none;
}

/* Card Container Label */
.status-radio-card-label {
    width: 100%;
    cursor: pointer;
    display: block;
}

.status-card-inner {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 14px;
    position: relative;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Tipografi Teks di Dalam Card */
.status-info-text {
    display: flex;
    flex-direction: column;
    flex-grow: 1;
}

.status-info-text .status-title {
    font-size: 0.85rem;
    font-weight: 700;
    color: rgba(255, 255, 255, 0.9);
    transition: color 0.25s ease;
}

.status-info-text .status-desc {
    font-size: 0.7rem;
    color: rgba(255, 255, 255, 0.4);
    margin-top: 2px;
}

/* Styling Icon Samping */
.status-card-inner .status-icon {
    font-size: 1.1rem;
    color: rgba(255, 255, 255, 0.3);
    transition: color 0.25s ease;
}

/* Centang Indikator (Awalnya Sembunyi) */
.status-card-inner .check-indicator {
    font-size: 1rem;
    opacity: 0;
    transform: scale(0.6);
    transition: all 0.25s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

/* Hover State Card Soft Efek */
.status-radio-card-label:hover .status-card-inner {
    background: rgba(255, 255, 255, 0.04);
    border-color: rgba(255, 255, 255, 0.15);
}

/* ==========================================
   LOGIK REKAYASA STATE :CHECKED VIA CSS
   ========================================== */

/* 1. STATE JIKA PILIHAN AKTIF (GREEN) DICENTANG */
.status-radio-card-label.theme-green input[type="radio"]:checked + .status-card-inner {
    background: rgba(46, 204, 113, 0.06);
    border-color: #2ecc71;
    box-shadow: inset 0 0 12px rgba(46, 204, 113, 0.1);
}
.status-radio-card-label.theme-green input[type="radio"]:checked + .status-card-inner .status-title {
    color: #2ecc71;
}
.status-radio-card-label.theme-green input[type="radio"]:checked + .status-card-inner .status-icon,
.status-radio-card-label.theme-green input[type="radio"]:checked + .status-card-inner .check-indicator {
    color: #2ecc71;
    opacity: 1;
    transform: scale(1);
}

/* 2. STATE JIKA PILIHAN DRAFT (YELLOW) DICENTANG */
.status-radio-card-label.theme-yellow input[type="radio"]:checked + .status-card-inner {
    background: rgba(255, 193, 7, 0.05);
    border-color: #ffc107;
    box-shadow: inset 0 0 12px rgba(255, 193, 7, 0.08);
}
.status-radio-card-label.theme-yellow input[type="radio"]:checked + .status-card-inner .status-title {
    color: #ffc107;
}
.status-radio-card-label.theme-yellow input[type="radio"]:checked + .status-card-inner .status-icon,
.status-radio-card-label.theme-yellow input[type="radio"]:checked + .status-card-inner .check-indicator {
    color: #ffc107;
    opacity: 1;
    transform: scale(1);
}

/* Responsive Mobile Switch Group */
@media (max-width: 576px) {
    .video-status-radio-group {
        grid-template-columns: 1fr;
        gap: 10px;
    }
}
</style>

<div class="glass-card welcome-card" style="margin-top: 50px;">
    <h1>Konfigurasi Video</h1>
    <p style="font-size: 0.85rem; opacity: 0.8;">Atur galeri video anda yang ingin ditampilkan.</p>
</div>

<div class="glass-card" style="overflow: visible;">
    <h4 style="font-size: 1rem; border-bottom: 1px solid var(--glass-border); padding-bottom: 10px; margin-bottom: 25px; margin-top: 0px;">
        <i class="fas fa-plus-circle me-2"></i> Tambah Video Baru
    </h4>
    
    <form action="proses_update_video.php" method="POST">
        <input type="hidden" name="action" value="add">
        
        <div class="video-form-grid">
            <div>
                <div class="form-group mb-4">
                    <label class="label-text">TAUTAN VIDEO YOUTUBE (WAJIB)</label>
                    <input type="url" name="video_url" class="input-glass" placeholder="https://www.youtube.com/watch?v=..." required>
                </div>

                <div class="form-group mb-4">
                    <label class="label-text">STATUS PUBLIKASI</label>
                    <input type="hidden" name="is_active" id="add_video_is_active" value="1">
                    <button type="button" id="btn_add_video_trigger" class="input-glass text-start d-flex justify-content-between align-items-center trigger-theme-green" onclick="openVideoModalWindow('modalAddVideoStatus')" style="cursor: pointer; background: rgba(255,255,255,0.02);">
                        <span id="add_video_status_label" style="color: #2ecc71; font-size: 0.9rem; font-weight: bold;"><i class="fas fa-circle me-2 small"></i>Langsung Aktif (Public)</span>
                        <i class="fas fa-window-restore" style="color: #2ecc71; font-size: 0.85rem;" id="icon_add_trigger"></i>
                    </button>
                </div>
                
                <div style="background: rgba(255, 255, 255, 0.01); border: 1px dashed rgba(255, 255, 255, 0.1); padding: 15px; border-radius: 14px;">
                    <small style="font-size: 0.72rem; line-height: 1.4; display: block; opacity: 0.7; color: #fff;">
                        <i class="fas fa-info-circle me-1" style="color: #ffc107;"></i> <b>Info Hub Bisnis:</b> Konfigurasi metadata video streaming terlokalisasi multi-bahasa akan tersimpan ke database secara real-time.
                    </small>
                </div>
            </div>

            <div>
                <div class="form-group mb-4">
                    <label class="label-text">JUDUL VIDEO PORTFOLIO (ID / EN / JP)</label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px;">
                        <input type="text" name="title_id" class="input-glass" placeholder="ID" required>
                        <input type="text" name="title_en" class="input-glass" placeholder="EN">
                        <input type="text" name="title_jp" class="input-glass" placeholder="JP">
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label class="label-text">DESKRIPSI VIDEO (BAHASA INDONESIA)</label>
                    <textarea name="desc_id" class="input-glass" rows="2" placeholder="Tulis deskripsi..." required style="font-family: inherit; font-size: 0.85rem; resize: none;"></textarea>
                </div>

                <div class="form-group mb-3">
                    <label class="label-text">VIDEO DESCRIPTION (ENGLISH)</label>
                    <textarea name="desc_en" class="input-glass" rows="2" placeholder="Write description..." style="font-family: inherit; font-size: 0.85rem; resize: none;"></textarea>
                </div>

                <div class="form-group">
                    <label class="label-text">ビデオの説明 (JAPANESE)</label>
                    <textarea name="desc_jp" class="input-glass" rows="2" placeholder="説明を入力してください..." style="font-family: inherit; font-size: 0.85rem; resize: none;"></textarea>
                </div>
            </div>
        </div>

        <button type="submit" class="btn-action" style="background: var(--accent); width: 100%; padding: 18px; font-weight: bold; margin-top: 25px;">
            <i class="fas fa-cloud-upload-alt me-2"></i> PUBLIKASIKAN VIDEO KE GALERI UTAMA
        </button>
    </form>
</div>

<div id="adminVideoMainContainer" class="glass-card" style="margin-top: 30px; overflow: visible; display: flex; flex-direction: column;">
    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--glass-border); padding-bottom: 15px; margin-bottom: 25px; position: relative; z-index: 99;">
        <h4 style="margin: 0;"><i class="fas fa-video me-2"></i> Katalog Portofolio Video Terunggah</h4>
        
        <div style="display: flex; align-items: center; gap: 10px;">
            <small class="label-text-desc">STATUS FILTER:</small>
            <button type="button" id="btn_filter_video_trigger" class="input-glass d-flex justify-content-between align-items-center trigger-theme-yellow" onclick="openVideoModalWindow('modalFilterVideoList')" style="width: 200px; cursor: pointer; text-align: left; padding: 10px 15px; background: rgba(255,255,255,0.02);">
                <span id="admin_video_filter_label" style="font-size: 0.85rem; color: #ffc107; font-weight: bold;">Semua Status</span>
                <i class="fas fa-filter" id="icon_filter_trigger" style="color: #ffc107; font-size: 0.8rem;"></i>
            </button>
        </div>
    </div>

    <div class="video-slider-container">
        <div id="adminVideoWrapper"></div>
    </div>

    <div id="rawVideoData" style="display: none;">
        <?php 
        $q_video_list = mysqli_query($conn, "SELECT * FROM site_video_portfolio ORDER BY id DESC");
        $total_videos = mysqli_num_rows($q_video_list);
        $v_current_no = $total_videos;

        if (!function_exists('getYouTubeThumbnail')) {
            function getYouTubeThumbnail($url) {
                $video_id = '';
                if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^\"&?/ ]{11})%i', $url, $match)) {
                    $video_id = $match[1];
                }
                return !empty($video_id) ? "https://img.youtube.com/vi/$video_id/mqdefault.jpg" : "../assets/imgs/placeholder.png";
            }
        }

        while($v_row = mysqli_fetch_assoc($q_video_list)): 
            $video_code = "#VID-" . str_pad($v_current_no, 2, '0', STR_PAD_LEFT) . "-" . date('Ymd', strtotime($v_row['created_at']));
            $v_current_no--;
            $filter_status_class = ($v_row['is_active'] == 1) ? 'status-active' : 'status-draft';
            $status_label_text = ($v_row['is_active'] == 1) ? 'Aktif / Public' : 'Draft / Hidden';
            $thumb_preview_url = getYouTubeThumbnail($v_row['video_url']);
            
            // Mengamankan string data agar tidak memecah element HTML saat dibaca JavaScript
            $secure_attributes = htmlspecialchars(json_encode($v_row, JSON_HEX_APOS | JSON_HEX_QUOT), ENT_QUOTES, 'UTF-8');
        ?>
            <div class="video-item <?= $filter_status_class; ?>">
                <div class="admin-katalog-card-box h-100 p-3" style="background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 16px;">
                    <div class="nfr-video-item-card-inner">
                        
                        <div class="admin-katalog-media mb-3">
                            <div class="video-media-preview-frame">
                                <img src="<?= $thumb_preview_url; ?>" onerror="this.src='../assets/imgs/placeholder.png'">
                                <div style="position: absolute; top:50%; left:50%; transform:translate(-50%, -50%); background: rgba(239,76,77,0.85); color:white; width:36px; height:36px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:0.8rem;">
                                    <i class="fas fa-play"></i>
                                </div>
                            </div>
                            <span class="admin-katalog-code"><?= $video_code; ?></span>
                        </div>
                        
                        <div class="admin-katalog-details flex-grow-1 d-flex flex-column">
                            <div class="content-text-area flex-grow-1">
                                <h5 class="admin-katalog-title mb-2">
                                
                                <div class="d-flex align-items-center flex-wrap gap-2 my-2">
                                    <span class="admin-katalog-badge" style="background: <?= ($v_row['is_active'] == 1) ? 'rgba(46,204,113,0.12)' : 'rgba(241,196,15,0.12)'; ?>; color: <?= ($v_row['is_active'] == 1) ? '#2ecc71' : '#f1c40f'; ?>; border: 1px solid <?= ($v_row['is_active'] == 1) ? 'rgba(46,204,113,0.2)' : 'rgba(241,196,15,0.2)'; ?>; padding: 2px 8px; border-radius: 6px; font-size: 0.65rem; font-weight: bold;">
                                        <?= $status_label_text; ?>
                                    </span>
                                    <p class="admin-katalog-price m-0" style="font-size: 0.72rem; color: #74b9ff; font-weight: bold;"><i class="fab fa-youtube me-1" style="color:#ef4c4d;"></i> YouTube Hub Link</p>
                                </div>
                                
                                <p class="admin-katalog-desc nfr-video-clamped-desc" title="<?= htmlspecialchars($v_row['desc_id']); ?>">
                                    <?= htmlspecialchars($v_row['desc_id']); ?>
                                </p>

                                <?php if(strlen($v_row['desc_id']) > 90): ?>
                                    <button type="button" class="btn-read-more-video" onclick="toggleVideoDesc(this)">...</button>
                                    <div class="video-full-desc">
                                        <?= nl2br(htmlspecialchars($v_row['desc_id'])); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="admin-katalog-actions mt-3 pt-2" style="border-top: 1px solid rgba(255,255,255,0.05);">
                                <button type="button" class="admin-btn-edit btn-trigger-edit-portfolio" data-video-info="<?= $secure_attributes; ?>">Edit</button>
                                <button type="button" class="admin-btn-delete" onclick="confirmDeleteVideo(<?= $v_row['id']; ?>)"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <div class="lang-nav-centered mt-4" id="adminVideoNav" style="display: none;">
        <button type="button" class="btn-nav-lang" onclick="moveAdminVideoGallery(-1)"><i class="fas fa-chevron-left"></i></button>
        <div class="lang-indicator-wrapper">
            <div id="adminVideoPageLabel" class="lang-text-dynamic">HALAMAN 1</div>
            <div id="adminVideoDots" class="lang-dots-container"></div>
        </div>
        <button type="button" class="btn-nav-lang" onclick="moveAdminVideoGallery(1)"><i class="fas fa-chevron-right"></i></button>
    </div>
</div>

<div id="modalAddVideoStatus" class="video-modal-overlay-glass" onclick="closeVideoModalWindow('modalAddVideoStatus')">
    <div class="video-modal-content-card" onclick="event.stopPropagation()">
        <div class="video-modal-header-naufaru" style="color: #fff;">
            <span><i class="fas fa-toggle-on me-2" style="color: #ffc107;"></i> STATUS PUBLIKASI</span>
            <button type="button" class="btn-close-modal-circle" onclick="closeVideoModalWindow('modalAddVideoStatus')">×</button>
        </div>
        <div class="video-info-box-modal-small">
            <p><i class="fas fa-info-circle me-1"></i> Tentukan apakah video langsung di-publish umum (Hijau) atau disembunyikan (Kuning).</p>
        </div>
        <div style="display: flex; flex-direction: column; gap: 12px;">
            <button type="button" class="btn-video-modal-option" onclick="selectAddVideoStatus(1, 'Langsung Aktif (Public)')" style="border-left: 4px solid #2ecc71; background: rgba(46,204,113,0.03);">
                <span style="color: #2ecc71; font-weight: 800;"><i class="fas fa-eye me-2"></i> Langsung Aktif (Public)</span>
                <i class="fas fa-check-circle" style="color: #2ecc71;"></i>
            </button>
            <button type="button" class="btn-video-modal-option" onclick="selectAddVideoStatus(0, 'Sembunyikan Dahulu (Draft)')" style="border-left: 4px solid #ffc107; background: rgba(255,193,7,0.03);">
                <span style="color: #ffc107; font-weight: 800;"><i class="fas fa-eye-slash me-2"></i> Sembunyikan Dahulu (Draft)</span>
                <i class="fas fa-pause-circle" style="color: #ffc107;"></i>
            </button>
        </div>
    </div>
</div>

<div id="modalFilterVideoList" class="video-modal-overlay-glass" onclick="closeVideoModalWindow('modalFilterVideoList')">
    <div class="video-modal-content-card" onclick="event.stopPropagation()">
        <div class="video-modal-header-naufaru" style="color: #fff;">
            <span><i class="fas fa-filter me-2" style="color: #ffc107;"></i> FILTER STATUS KATALOG</span>
            <button type="button" class="btn-close-modal-circle" onclick="closeVideoModalWindow('modalFilterVideoList')">×</button>
        </div>
        <div class="video-info-box-modal-small">
            <p><i class="fas fa-info-circle me-1"></i> Saring data produk video berdasarkan kategori status rilis.</p>
        </div>
        <div style="display: flex; flex-direction: column; gap: 12px;">
            <button type="button" class="btn-video-modal-option" onclick="selectFilterVideoStatus('all', 'Semua Status')" style="border-left: 4px solid #ffc107; background: rgba(255,193,7,0.02);">
                <span style="color: #ffc107;"><i class="fas fa-border-all me-2"></i> Semua Status Terunggah</span>
                <i class="fas fa-chevron-right opacity-50" style="color: #ffc107;"></i>
            </button>
            <button type="button" class="btn-video-modal-option" onclick="selectFilterVideoStatus('status-active', 'Aktif (Public)')" style="border-left: 4px solid #2ecc71; background: rgba(46,204,113,0.02);">
                <span style="color: #2ecc71;"><i class="fas fa-check-circle me-2"></i> Hanya Status Aktif (Public)</span>
                <i class="fas fa-chevron-right opacity-50" style="color: #2ecc71;"></i>
            </button>
            <button type="button" class="btn-video-modal-option" onclick="selectFilterVideoStatus('status-draft', 'Draft (Hidden)')" style="border-left: 4px solid #ffc107; background: rgba(255,193,7,0.02);">
                <span style="color: #ffc107;"><i class="fas fa-pause-circle me-2"></i> Hanya Status Draft (Hidden)</span>
                <i class="fas fa-chevron-right opacity-50" style="color: #ffc107;"></i>
            </button>
        </div>
    </div>
</div>

<div id="modalEditVideoData" class="video-modal-overlay-glass" onclick="closeVideoModalWindow('modalEditVideoData')">
    <div class="video-modal-content-card" style="width: 500px;" onclick="event.stopPropagation()">
        <div class="video-modal-header-naufaru" style="color: #ffc107;">
            <span><i class="fas fa-edit me-2"></i> EDIT PORTFOLIO VIDEO</span>
            <button type="button" class="btn-close-modal-circle" onclick="closeVideoModalWindow('modalEditVideoData')">×</button>
        </div>

        <form action="proses_update_video.php" method="POST">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="video_id" id="edit_video_id" required>

            <div class="form-group-modal mb-3">
                <label class="video-label-modal">Tautan Video YouTube</label>
                <input type="url" name="video_url" id="edit_video_url" class="video-input-premium-glass" required>
            </div>

            <div class="form-group-modal mb-3">
                <label class="video-label-modal">Status Tampilan Video</label>
                
                <div class="video-status-radio-group">
                    
                    <label class="status-radio-card-label theme-green">
                        <input type="radio" name="is_active" id="edit_video_active_true" value="1">
                        <div class="status-card-inner">
                            <i class="fas fa-eye status-icon"></i>
                            <div class="status-info-text">
                                <span class="status-title">Aktif / Public</span>
                                <span class="status-desc">Dapat dilihat semua</span>
                            </div>
                            <i class="fas fa-check-circle check-indicator"></i>
                        </div>
                    </label>

                    <label class="status-radio-card-label theme-yellow">
                        <input type="radio" name="is_active" id="edit_video_active_false" value="0">
                        <div class="status-card-inner">
                            <i class="fas fa-eye-slash status-icon"></i>
                            <div class="status-info-text">
                                <span class="status-title">Sembunyikan / Draft</span>
                                <span class="status-desc">Hanya tampil di panel admin</span>
                            </div>
                            <i class="fas fa-check-circle check-indicator"></i>
                        </div>
                    </label>

                </div>
            </div>

            <div class="form-group-modal mb-3">
                <label class="video-label-modal">Judul Video (ID / EN / JP)</label>
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px;">
                    <input type="text" name="title_id" id="edit_video_title_id" class="video-input-premium-glass" placeholder="ID" required>
                    <input type="text" name="title_en" id="edit_video_title_en" class="video-input-premium-glass" placeholder="EN">
                    <input type="text" name="title_jp" id="edit_video_title_jp" class="video-input-premium-glass" placeholder="JP">
                </div>
            </div>

            <div class="form-group-modal mb-2">
                <label class="video-label-modal">Deskripsi (Indonesia)</label>
                <textarea name="desc_id" id="edit_video_desc_id" class="video-input-premium-glass" rows="2" style="resize:none; font-family: inherit;" required></textarea>
            </div>

            <div class="form-group-modal mb-2">
                <label class="video-label-modal">Description (English)</label>
                <textarea name="desc_en" id="edit_video_desc_en" class="video-input-premium-glass" rows="2" style="resize:none; font-family: inherit;"></textarea>
            </div>

            <div class="form-group-modal mb-3">
                <label class="video-label-modal">説明 (Japanese)</label>
                <textarea name="desc_jp" id="edit_video_desc_jp" class="video-input-premium-glass" rows="2" style="resize:none; font-family: inherit;"></textarea>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 25px;">
                <button type="button" onclick="closeVideoModalWindow('modalEditVideoData')" class="btn-action" style="flex:1; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); color: #fff;">
                    BATAL
                </button>
                <button type="submit" class="btn-action" style="flex:2; background:#ffc107; border:1px solid #ffc107; color:#111; font-weight: bold;">
                    <i class="fas fa-save"></i> SIMPAN PERUBAHAN
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    let currentVideoGalleryPage = 0;
    let itemsPerVideoPage = 3; 
    let totalVideoPages = 0;
    let filteredVideoItems = [];

    // --- ANIMASI MASUK MODAL WINDOWS ---
    function openVideoModalWindow(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'flex';
            const card = modal.querySelector('.video-modal-content-card');
            if (card) {
                card.classList.remove('anim-out');
                card.classList.add('anim-in');
            }
        }
    }

    // --- ANIMASI KELUAR (CLOSE) MODAL WINDOWS ---
    function closeVideoModalWindow(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            const card = modal.querySelector('.video-modal-content-card');
            if (card) {
                card.classList.remove('anim-in');
                card.classList.add('anim-out');
                
                setTimeout(() => {
                    modal.style.display = 'none';
                    card.classList.remove('anim-out');
                }, 290);
            }
        }
    }

    // --- SELEKSI MENU TAMBAH VIDEO LENGKAP ---
    function selectAddVideoStatus(value, label) {
        const inputHidden = document.getElementById('add_video_is_active');
        if (inputHidden) inputHidden.value = value;
        
        const triggerBtn = document.getElementById('btn_add_video_trigger');
        const triggerLabel = document.getElementById('add_video_status_label');
        const triggerIcon = document.getElementById('icon_add_trigger');

        if (triggerLabel && triggerIcon && triggerBtn) {
            if (value === 1) {
                triggerLabel.innerHTML = `<i class="fas fa-circle me-2 small"></i>${label}`;
                triggerLabel.style.color = '#2ecc71';
                triggerIcon.style.color = '#2ecc71';
                triggerBtn.className = "input-glass text-start d-flex justify-content-between align-items-center trigger-theme-green";
            } else {
                triggerLabel.innerHTML = `<i class="fas fa-pause-circle me-2 small"></i>${label}`;
                triggerLabel.style.color = '#ffc107';
                triggerIcon.style.color = '#ffc107';
                triggerBtn.className = "input-glass text-start d-flex justify-content-between align-items-center trigger-theme-yellow";
            }
        }
        closeVideoModalWindow('modalAddVideoStatus');
    }

    // --- SELEKSI MENU FILTER LIST KATALOG ---
    function selectFilterVideoStatus(value, label) {
        const triggerBtn = document.getElementById('btn_filter_video_trigger');
        const triggerLabel = document.getElementById('admin_video_filter_label');
        const triggerIcon = document.getElementById('icon_filter_trigger');

        if (triggerLabel && triggerIcon && triggerBtn) {
            triggerLabel.innerText = label;
            if (value === 'status-active') {
                triggerLabel.style.color = '#2ecc71';
                triggerIcon.style.color = '#2ecc71';
                triggerBtn.className = "input-glass d-flex justify-content-between align-items-center trigger-theme-green";
            } else {
                triggerLabel.style.color = '#ffc107';
                triggerIcon.style.color = '#ffc107';
                triggerBtn.className = "input-glass d-flex justify-content-between align-items-center trigger-theme-yellow";
            }
        }

        closeVideoModalWindow('modalFilterVideoList');
        setTimeout(() => {
            prosesFilterDanRenderVideo(value);
        }, 320);
    }

    // --- AMBIL DATA EDIT DARI ATRIBUT DOM SECARA AMAN (VANILLA JS DELEGATION) ---
    function initEditButtonListeners() {
        document.querySelectorAll('.btn-trigger-edit-portfolio').forEach(button => {
            button.removeEventListener('click', handleEditButtonClick);
            button.addEventListener('click', handleEditButtonClick);
        });
    }

    // GANTI & SESUAIKAN MENJADI LOGIKA BARU BERIKUT:
    function handleEditButtonClick(e) {
        try {
            const dataRaw = e.currentTarget.getAttribute('data-video-info');
            const data = JSON.parse(dataRaw);
            
            // 1. Petakan input teks standar
            const fields = {
                'edit_video_id': data.id,
                'edit_video_url': data.video_url,
                'edit_video_title_id': data.title_id,
                'edit_video_title_en': data.title_en,
                'edit_video_title_jp': data.title_jp,
                'edit_video_desc_id': data.desc_id,
                'edit_video_desc_en': data.desc_en,
                'edit_video_desc_jp': data.desc_jp
            };

            for (let id in fields) {
                const el = document.getElementById(id);
                if (el) el.value = fields[id] ?? '';
            }

            // 2. LOGIKA FIX TRIGGER RADIO BUTTON STATUS
            if (parseInt(data.is_active) === 1) {
                const radActive = document.getElementById('edit_video_active_true');
                if (radActive) radActive.checked = true;
            } else {
                const radDraft = document.getElementById('edit_video_active_false');
                if (radDraft) radDraft.checked = true;
            }

            openVideoModalWindow('modalEditVideoData');
        } catch (error) {
            console.error("Gagal memproses data JSON card video:", error);
        }
    }

    // --- VANILLA JS SLIDER RE-RENDER ENGINE ---
    function prosesFilterDanRenderVideo(filterValue) {
        const rawContainer = document.getElementById('rawVideoData');
        if (!rawContainer) return;

        filteredVideoItems = [];
        currentVideoGalleryPage = 0;

        const videoItems = rawContainer.getElementsByClassName('video-item');
        for (let item of videoItems) {
            if (filterValue === 'all' || item.classList.contains(filterValue)) {
                filteredVideoItems.push(item.outerHTML);
            }
        }

        totalVideoPages = Math.ceil(filteredVideoItems.length / itemsPerVideoPage);
        updateVideoSliderInterface();
    }

    function updateVideoSliderInterface() {
        const targetWrapper = document.getElementById('adminVideoWrapper');
        const navContainer = document.getElementById('adminVideoNav');
        const pageLabel = document.getElementById('adminVideoPageLabel');
        const dotsContainer = document.getElementById('adminVideoDots');

        if (!targetWrapper) return;
        targetWrapper.innerHTML = "";

        if (filteredVideoItems.length === 0) {
            targetWrapper.innerHTML = '<div style="width: 100%; text-align: center; padding: 40px 0; opacity: 0.4; color: white; font-size:0.85rem;"><i class="fas fa-video-slash fa-2x mb-2 d-block" style="color:#ffc107;"></i> Belum ada portofolio video yang terekam pada status ini.</div>';
            if (navContainer) navContainer.style.display = 'none';
            return;
        }

        const startIdx = currentVideoGalleryPage * itemsPerVideoPage;
        const endIdx = startIdx + itemsPerVideoPage;
        const itemsToRender = filteredVideoItems.slice(startIdx, endIdx);

        itemsToRender.forEach(htmlString => {
            targetWrapper.insertAdjacentHTML('beforeend', htmlString);
        });

        // Re-inisialisasi listener tombol edit setelah render ulang grid
        initEditButtonListeners();

        if (navContainer) {
            navContainer.style.display = totalVideoPages > 1 ? 'flex' : 'none';
        }
        if (pageLabel) {
            pageLabel.innerText = `HALAMAN ${currentVideoGalleryPage + 1} DARI ${totalVideoPages || 1}`;
        }
        
        if (dotsContainer) {
            dotsContainer.innerHTML = "";
            for (let i = 0; i < totalVideoPages; i++) {
                const activeClass = i === currentVideoGalleryPage ? 'active' : '';
                dotsContainer.insertAdjacentHTML('beforeend', `<div class="lang-dot ${activeClass}" onclick="lompatKeHalamanVideo(${i})"></div>`);
            }
        }
    }

    function moveAdminVideoGallery(direction) {
        let targetPage = currentVideoGalleryPage + direction;
        if (targetPage >= 0 && targetPage < totalVideoPages) {
            currentVideoGalleryPage = targetPage;
            updateVideoSliderInterface();
        }
    }

    function lompatKeHalamanVideo(pageIndex) {
        currentVideoGalleryPage = pageIndex;
        updateVideoSliderInterface();
    }

    function confirmDeleteVideo(videoId) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Hapus Portofolio Video?',
                text: "Tautan galeri video akan dicabut permanen dari sistem!",
                icon: 'warning',
                showCancelButton: true,
                background: '#1a1a1a',
                color: '#fff',
                confirmButtonColor: '#ef4c4d',
                cancelButtonColor: 'rgba(255,255,255,0.1)',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `proses_update_video.php?action=delete&id=${videoId}`;
                }
            });
        } else {
            if (confirm("Apakah Anda yakin ingin menghapus video ini?")) {
                window.location.href = `proses_update_video.php?action=delete&id=${videoId}`;
            }
        }
    }

    // --- FIX ANIMASI TUTUP DESKRIPSI (SMOOTH SLIDE OUT) ---
    function toggleVideoDesc(button) {
        const descBox = button.nextElementSibling;
        if (!descBox) return;

        const isOpen = descBox.classList.contains('anim-show');

        if (isOpen) {
            descBox.classList.remove('anim-show');
            descBox.classList.add('anim-hide');
            button.innerText = '...';
            
            // Tunggu animasi CSS fadeout selesai, baru ubah display jadi none secara penuh
            const onAnimationEnd = function() {
                descBox.classList.remove('anim-hide');
                descBox.removeEventListener('animationend', onAnimationEnd);
            };
            descBox.addEventListener('animationend', onAnimationEnd);
        } else {
            descBox.classList.remove('anim-hide');
            descBox.classList.add('anim-show');
            button.innerText = 'Tutup';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        prosesFilterDanRenderVideo('all');
    });
</script>