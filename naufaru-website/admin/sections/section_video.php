<?php
/**
 * File: admin/sections/section_video.php
 * Deskripsi: Komponen Modular Manajemen Galeri Video dengan Sistem Pop-up Modal Premium Glass
 * Pembaruan: Fix Sistem Penutupan Otomatis Modal Saat Opsi Menu Diklik & Delegasi Animasi Keluar
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
        width: 100%;
        padding-top: 56.25%; /* Aspek rasio sinematik 16:9 */
        background: #000;
        border-radius: 10px;
        overflow: hidden;
    }

    .video-media-preview-frame img {
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
        object-fit: cover;
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
        display: block; color: #ffc107; font-size: 0.7rem; 
        font-weight: 800; text-transform: uppercase; 
        margin-bottom: 8px; letter-spacing: 1px;
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
</style>

<div class="glass-card welcome-card" style="margin-top: 50px;">
    <h1>Manajemen Galeri Video</h1>
    <p style="font-size: 0.85rem; opacity: 0.8;">Tambahkan tautan video sinematik terbaru atau kelola portofolio video yang sudah terbit di website utama.</p>
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
                        <i class="fas fa-info-circle me-1" style="color: #ffc107;"></i> <b>Info Manajemen:</b> Konfigurasi metadata multi-bahasa akan tersimpan langsung ke dalam database cluster portofolio.
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
        <div id="adminVideoWrapper">
            </div>
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
        ?>
            <div class="admin-katalog-item-column video-item <?= $filter_status_class; ?>">
                <div class="admin-katalog-card-box">
                    <div class="admin-katalog-media">
                        <div class="video-media-preview-frame">
                            <img src="<?= $thumb_preview_url; ?>" onerror="this.src='../assets/imgs/placeholder.png'">
                            <div style="position: absolute; top:50%; left:50%; transform:translate(-50%, -50%); background: rgba(239,76,77,0.85); color:white; width:36px; height:36px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:0.8rem;">
                                <i class="fas fa-play"></i>
                            </div>
                        </div>
                        <span class="admin-katalog-code"><?= $video_code; ?></span>
                    </div>
                    <div class="admin-katalog-details">
                        <div class="content-text-area">
                            <h5 class="admin-katalog-title"><?= htmlspecialchars($v_row['title_id']); ?></h5>
                            <span class="admin-katalog-badge" style="background: <?= ($v_row['is_active'] == 1) ? 'rgba(46,204,113,0.12)' : 'rgba(241,196,15,0.12)'; ?>; color: <?= ($v_row['is_active'] == 1) ? '#2ecc71' : '#f1c40f'; ?>; border: 1px solid <?= ($v_row['is_active'] == 1) ? 'rgba(46,204,113,0.2)' : 'rgba(241,196,15,0.2)'; ?>; padding: 2px 8px; border-radius: 6px; font-size: 0.65rem;">
                                <?= $status_label_text; ?>
                            </span>
                            <p class="admin-katalog-price" style="font-size: 0.72rem; color: #74b9ff; margin-top: 6px;"><i class="fab fa-youtube me-1" style="color:#ef4c4d;"></i> YouTube Link</p>
                            <p class="admin-katalog-desc" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; white-space: normal; height: 34px;"><?= htmlspecialchars($v_row['desc_id']); ?></p>
                        </div>
                        <div class="admin-katalog-actions">
                            <button type="button" class="admin-btn-edit" onclick='openEditVideoModal(<?= json_encode($v_row); ?>)'>Edit</button>
                            <button type="button" class="admin-btn-delete" onclick="confirmDeleteVideo(<?= $v_row['id']; ?>)"><i class="fas fa-trash"></i></button>
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
                <select name="is_active" id="edit_video_is_active" class="video-input-premium-glass" style="background: #151515; color: #fff;">
                    <option value="1" style="color: #2ecc71;">Aktif / Publikasikan (Public)</option>
                    <option value="0" style="color: #ffc107;">Sembunyikan / Simpan (Draft)</option>
                </select>
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
            card.classList.remove('anim-out');
            card.classList.add('anim-in');
        }
    }

    // --- CLOSE MODAL DENGAN ANIMASI ---
    function closeVideoModalWindow(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;
        const card = modal.querySelector('.video-modal-content-card');
        if (!card) {
            modal.style.display = 'none';
            return;
        }

        // Hindari double animation
        if (card.classList.contains('anim-out')) return;
        card.classList.remove('anim-in');
        card.classList.add('anim-out');
        setTimeout(() => {
            modal.style.display = 'none';
            card.classList.remove('anim-out');
        }, 300);
    }

    // --- SELEKSI MENU TAMBAH VIDEO LENGKAP ---
    function selectAddVideoStatus(value, label) {
        document.getElementById('add_video_is_active').value = value;   
        const triggerBtn = document.getElementById('btn_add_video_trigger');
        const triggerLabel = document.getElementById('add_video_status_label');
        const triggerIcon = document.getElementById('icon_add_trigger');

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
        
        // FIX: Panggil fungsi penutup modal berbasis transisi animasi keluar
        closeVideoModalWindow('modalAddVideoStatus');
    }

    // --- SELEKSI MENU FILTER LIST KATALOG (FIXED AUTOCLOSE FINAL) ---
    function selectFilterVideoStatus(value, label) {

        const triggerBtn = document.getElementById('btn_filter_video_trigger');
        const triggerLabel = document.getElementById('admin_video_filter_label');
        const triggerIcon = document.getElementById('icon_filter_trigger');

        // Update label filter
        triggerLabel.innerText = label;

        // Update warna trigger
        if (value === 'status-active') {

            triggerLabel.style.color = '#2ecc71';
            triggerIcon.style.color = '#2ecc71';

            triggerBtn.className =
                "input-glass d-flex justify-content-between align-items-center trigger-theme-green";

        } else {

            triggerLabel.style.color = '#ffc107';
            triggerIcon.style.color = '#ffc107';

            triggerBtn.className =
                "input-glass d-flex justify-content-between align-items-center trigger-theme-yellow";
        }

        // TUTUP MODAL TERLEBIH DAHULU
        closeVideoModalWindow('modalFilterVideoList');

        // BERI DELAY AGAR ANIMASI CLOSE SELESAI
        setTimeout(() => {
            prosesFilterDanRenderVideo(value);
        }, 320);
    }

    function openEditVideoModal(data) {
        document.getElementById('edit_video_id').value = data.id;
        document.getElementById('edit_video_url').value = data.video_url;
        document.getElementById('edit_video_is_active').value = data.is_active;
        document.getElementById('edit_video_title_id').value = data.title_id;
        document.getElementById('edit_video_title_en').value = data.title_en;
        document.getElementById('edit_video_title_jp').value = data.title_jp;
        document.getElementById('edit_video_desc_id').value = data.desc_id;
        document.getElementById('edit_video_desc_en').value = data.desc_en;
        document.getElementById('edit_video_desc_jp').value = data.desc_jp;

        openVideoModalWindow('modalEditVideoData');
    }

    // --- CONTROL SLIDER CATALOGUE ENGINE ---
    function prosesFilterDanRenderVideo(filterValue) {
        const rawContainer = $('#rawVideoData');
        filteredVideoItems = [];
        currentVideoGalleryPage = 0;

        rawContainer.find('.video-item').each(function() {
            if (filterValue === 'all' || $(this).hasClass(filterValue)) {
                filteredVideoItems.push($(this).clone()[0].outerHTML);
            }
        });

        totalVideoPages = Math.ceil(filteredVideoItems.length / itemsPerVideoPage);
        updateVideoSliderInterface();
    }

    function updateVideoSliderInterface() {
        const targetWrapper = $('#adminVideoWrapper');
        targetWrapper.empty();

        if (filteredVideoItems.length === 0) {
            targetWrapper.append('<div style="width: 100%; text-align: center; padding: 40px 0; opacity: 0.4; color: white; font-size:0.85rem;"><i class="fas fa-video-slash fa-2x mb-2 d-block" style="color:#ffc107;"></i> Belum ada portofolio video yang terekam pada status ini.</div>');
            $('#adminVideoNav').hide();
            return;
        }

        const startIdx = currentVideoGalleryPage * itemsPerVideoPage;
        const endIdx = startIdx + itemsPerVideoPage;
        const itemsToRender = filteredVideoItems.slice(startIdx, endIdx);

        itemsToRender.forEach(htmlString => {
            targetWrapper.append(htmlString);
        });

        $('#adminVideoNav').toggle(totalVideoPages > 1);
        $('#adminVideoPageLabel').text(`HALAMAN ${currentVideoGalleryPage + 1} DARI ${totalVideoPages || 1}`);
        
        const dotsContainer = $('#adminVideoDots').empty();
        for (let i = 0; i < totalVideoPages; i++) {
            dotsContainer.append(`<div class="lang-dot ${i === currentVideoGalleryPage ? 'active' : ''}" onclick="lompatKeHalamanVideo(${i})"></div>`);
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
    }

    $(document).ready(function() {
        prosesFilterDanRenderVideo('all');
    });
</script>