<?php
/**
 * File: admin/sections/section_team.php
 * Deskripsi: Panel Kendali Utama CRUD Komponen Tim Profesional & Info Video (Premium Glass Theme)
 * Perbaikan: Rekalkulasi Animasi Zoom In/Out Jendela Edit, Resolusi Warning Keys, & Penyelarasan Tombol Bulat
 */

// Ambil konfigurasi warna hover saat ini dari tabel site_settings
$q_settings = mysqli_query($conn, "SELECT team_hover_color_1, team_hover_color_2 FROM site_settings WHERE id = 1 LIMIT 1");
$set_data = mysqli_fetch_assoc($q_settings);
$team_grad1 = $set_data['team_hover_color_1'] ?? '#EF4C4D';
$team_grad2 = $set_data['team_hover_color_2'] ?? 'rgba(239, 76, 77, 0.15)';

// Ambil data alert/pesan info video aktif saat ini dari database
$q_video_alert = mysqli_query($conn, "SELECT * FROM site_video_alerts ORDER BY id DESC LIMIT 1");
$v_alert_data = mysqli_fetch_assoc($q_video_alert);

// PERBAIKAN SINKRONISASI VARIABEL: Mencegah Undefined Warning Error
$v_alert_text_id = $v_alert_data['text_id'] ?? '';
$v_alert_text_en = $v_alert_data['text_en'] ?? '';
$v_alert_text_jp = $v_alert_data['text_jp'] ?? '';
$v_alert_link_url = $v_alert_data['link_url'] ?? '';
$v_alert_link_text_id = $v_alert_data['link_text_id'] ?? '';
$v_alert_active = ($v_alert_data['is_active'] ?? 0) == 1;
?>

<!-- STYLE OVERRIDE FOR PREMIUM SINKRONISASI GLASS THEME -->
<style>
    /* ==============================================================
       ANIMASI TIMED KEYFRAMES POPUP WINDOW MODAL EDIT TEAM
       ============================================================== */
    @keyframes popupZoomInTeam { 
        from { opacity: 0; transform: scale(0.9); } 
        to { opacity: 1; transform: scale(1); } 
    }
    @keyframes popupZoomOutTeam { 
        from { opacity: 1; transform: scale(1); } 
        to { opacity: 0; transform: scale(0.9); } 
    }

    /* Custom Scrollbar untuk Container Tabel Team & Input Textarea */
    .premium-scroll-target::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }
    .premium-scroll-target::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.01);
        border-radius: 10px;
    }
    .premium-scroll-target::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.12);
        border-radius: 10px;
        transition: background 0.3s;
    }
    .premium-scroll-target::-webkit-scrollbar-thumb:hover {
        background: rgba(239, 76, 77, 0.6);
    }

    /* Penyelarasan Tombol Input Type File Custom Redondant Melengkung */
    .input-file-glass-wrapper {
        padding: 9px 12px !important;
        cursor: pointer;
        border-radius: 12px !important;
    }
    .input-file-glass-wrapper::file-selector-button {
        background: var(--accent) !important;
        color: white !important;
        border: none !important;
        padding: 4px 14px !important;
        border-radius: 8px !important;
        margin-right: 12px !important;
        cursor: pointer !important;
        font-size: 0.75rem !important;
        font-weight: 700 !important;
        transition: 0.3s ease !important;
    }
    .input-file-glass-wrapper::file-selector-button:hover {
        background: #d43f40 !important;
        box-shadow: 0 0 10px rgba(239, 76, 77, 0.3);
    }

    /* Penyelarasan Seluruh Tombol Aksi Menjadi Melengkung (Rounded Layout) */
    .btn-action-premium-rounded {
        padding: 12px 25px;
        font-weight: 700;
        border-radius: 12px !important; /* Membuang struktur kotak kaku */
        border: none;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        font-size: 0.82rem;
        letter-spacing: 0.5px;
    }
    .btn-action-premium-rounded:hover {
        transform: translateY(-2px);
    }

    /* Warna Tombol Modifikasi Kasir/Admin Theme */
    .btn-premium-save-green {
        background: #2ecc71 !important;
        color: white !important;
        border: 1px solid rgba(46, 204, 113, 0.15) !important;
    }
    .btn-premium-save-green:hover {
        background: #27ae60 !important;
        box-shadow: 0 6px 15px rgba(46, 204, 113, 0.3) !important;
    }

    .btn-premium-action-red {
        background: var(--accent) !important;
        color: white !important;
    }
    .btn-premium-action-red:hover {
        box-shadow: 0 6px 15px rgba(239, 76, 77, 0.4);
    }

    /* PREMIUM ROUNDED BUTTONS: Memperbaiki Tombol Aksi Ikon Agar Berbentuk Bulat Lengkung Mulus */
    .btn-premium-action-icon-rounded {
        width: 36px;
        height: 36px;
        border-radius: 10px !important; /* Melengkung presisi serasi glass theme */
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        font-size: 0.85rem;
        border: none;
    }
    .btn-premium-action-icon-rounded:hover {
        transform: scale(1.05);
    }

    /* FIX TOMBOL EDIT: Re-theme warna emas transparan melengkung */
    .btn-edit-row-premium {
        background: rgba(255, 193, 7, 0.08) !important;
        color: #ffc107 !important;
        border: 1px solid rgba(255, 193, 7, 0.2) !important;
    }
    .btn-edit-row-premium:hover {
        background: #ffc107 !important;
        color: #111 !important;
        box-shadow: 0 4px 10px rgba(255, 193, 7, 0.3);
    }

    /* FIX TOMBOL HAPUS: Re-theme warna merah transparan melengkung */
    .btn-remove-row-premium {
        background: rgba(239, 76, 77, 0.08) !important;
        color: #ef4c4d !important;
        border: 1px solid rgba(239, 76, 77, 0.2) !important;
    }
    .btn-remove-row-premium:hover {
        background: #ef4c4d !important;
        color: white !important;
        box-shadow: 0 4px 10px rgba(239, 76, 77, 0.3);
    }

    /* Switch Toggle Animasi Premium untuk Visibilitas Alert */
    .switch-premium-label {
        display: inline-flex;
        align-items: center;
        cursor: pointer;
        user-select: none;
        gap: 10px;
    }
    .switch-premium-input { display: none; }
    .switch-premium-slider {
        width: 42px; height: 22px;
        background: rgba(255,255,255,0.1);
        border: 1px solid rgba(255,255,255,0.15);
        border-radius: 50px; position: relative;
        transition: 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
    }
    .switch-premium-slider::before {
        content: ""; position: absolute;
        width: 14px; height: 14px; left: 3px; bottom: 3px;
        background: #fff; border-radius: 50%;
        transition: 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
    }
    .switch-premium-input:checked + .switch-premium-slider {
        background: #2ecc71; border-color: rgba(46, 204, 113, 0.4);
    }
    .switch-premium-input:checked + .switch-premium-slider::before {
        transform: translateX(20px);
    }

    /* DIV INFO DIMENSI PIKSEL DESAIN */
    .info-box-pixel-guide {
        background: rgba(116, 185, 255, 0.06);
        border-left: 4px solid #74b9ff;
        padding: 12px 16px;
        border-radius: 12px;
        margin-bottom: 20px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        text-align: left;
    }
    .info-box-pixel-guide i {
        color: #74b9ff;
        font-size: 1.1rem;
        margin-top: 2px;
    }
    .info-box-pixel-text h6 {
        font-size: 0.82rem;
        font-weight: 700;
        margin-bottom: 3px;
        color: #ffffff;
        margin-top: 0px;
    }
    .info-box-pixel-text p {
        margin: 0;
        font-size: 0.75rem;
        color: rgba(255, 255, 255, 0.55);
        line-height: 1.4;
    }

    /* STRUCTURAL FIX: Mengunci Kedalaman Posisi Overlay Modal di Tengah Layar */
    .modal-overlay-glass {
        display: none; 
        position: fixed; 
        top: 0; left: 0; 
        width: 100vw; height: 100vh; 
        background: rgba(0, 0, 0, 0.65); 
        z-index: 999999 !important; 
        justify-content: center; 
        align-items: center; 
        backdrop-filter: blur(20px); 
        -webkit-backdrop-filter: blur(20px);
    }
    .modal-content-card {
        width: 580px; 
        padding: 30px; 
        background: rgba(20, 20, 20, 0.92);
        border: 1px solid rgba(255, 255, 255, 0.1); 
        border-radius: 24px; 
        position: relative; 
        box-shadow: 0 30px 60px rgba(0, 0, 0, 0.7);
    }
    .modal-header-naufaru {
        display: flex; align-items: center; justify-content: space-between;
        font-size: 0.9rem; color: #EF4C4D; font-weight: 900;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1); padding-bottom: 12px; margin-bottom: 20px;
        letter-spacing: 0.5px;
    }
    .btn-close-modal {
        background: rgba(255, 255, 255, 0.05); border: none; color: #fff; width: 28px; height: 28px; 
        border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; transition: 0.2s;
    }
    .btn-close-modal:hover { background: #EF4C4D; }
    .label-modal { display: block; color: #ffc107; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; margin-bottom: 8px; letter-spacing: 1px; }
</style>

<div class="glass-card welcome-card" style="margin-top: 50px;">
    <h1>Konfigurasi Pesan Info Video</h1>
    <p style="font-size: 0.85rem; opacity: 0.8;">Atur pesan promosi atau pemberitahuan penting yang akan tampil di atas galeri video seksi frontend.</p>
</div>

<div class="glass-card mt-4">
    <h4 style="font-size: 1rem; border-bottom: 1px solid var(--glass-border); padding-bottom: 15px; margin-bottom: 20px; color: #74b9ff;">
        <i class="fas fa-bullhorn me-2"></i> Pengaturan Konten Alert Galeri Video
    </h4>
    <form action="proses_video_alert.php" method="POST">
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 25px; margin-bottom: 15px;">
            
            <!-- Input Pesan Multi Bahasa -->
            <div style="display: flex; flex-direction: column; gap: 15px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="label-text" style="color: #74b9ff;">Pesan Banner (Indonesia)</label>
                    <input type="text" name="text_id" class="input-glass" value="<?= htmlspecialchars($v_alert_text_id); ?>" placeholder="Tulis pengumuman bahasa indonesia..." required>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="label-text">Pesan Banner (English)</label>
                    <input type="text" name="text_en" class="input-glass" value="<?= htmlspecialchars($v_alert_text_en); ?>" placeholder="Tulis pengumuman bahasa inggris..." required>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="label-text" style="color: #ffeaa7;">Pesan Banner (Japanese)</label>
                    <input type="text" name="text_jp" class="input-glass" value="<?= htmlspecialchars($v_alert_text_jp); ?>" placeholder="Tulis pengumuman bahasa jepang..." required>
                </div>
            </div>

            <!-- Input Elemen Tautan Aset -->
            <div style="display: flex; flex-direction: column; gap: 15px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="label-text">Tautan Aksi Target (Link URL)</label>
                    <input type="text" name="link_url" class="input-glass" value="<?= htmlspecialchars($v_alert_link_url); ?>" placeholder="https://youtube.com/... / NULL">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="label-text">Teks Tautan (ID/Global)</label>
                    <input type="text" name="link_text_id" class="input-glass" value="<?= htmlspecialchars($v_alert_link_text_id); ?>" placeholder="Contoh: Tonton Sekarang">
                </div>
                <div class="form-group" style="margin-bottom: 0; margin-top: 10px;">
                    <label class="label-text" style="color: #2ecc71;">Status Publikasi</label>
                    <label class="switch-premium-label">
                        <input type="checkbox" name="is_active" value="1" class="switch-premium-input" <?= $v_alert_active ? 'checked' : ''; ?>>
                        <div class="switch-premium-slider"></div>
                        <span style="font-size: 0.85rem; opacity: 0.8;">Aktifkan Banner</span>
                    </label>
                </div>
            </div>

        </div>
        <div style="display: flex; justify-content: flex-end; border-top: 1px solid var(--glass-border); padding-top: 15px;">
            <button type="submit" class="btn-action-premium-rounded btn-premium-save-green">
                <i class="fas fa-save"></i> SIMPAN PEMBERITAHUAN VIDEO
            </button>
        </div>
    </form>
</div>

<div class="glass-card mt-4">
    <h4 style="font-size: 1rem; border-bottom: 1px solid var(--glass-border); padding-bottom: 15px; margin-bottom: 20px; color: #ffc107;">
        <i class="fas fa-palette me-2"></i> Pengaturan Efek Warna Hover Tim Global
    </h4>
    <form action="proses_team.php?action=update_color" method="POST">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 20px;">
            <div class="form-group" style="margin-bottom:0;">
                <label class="label-text">Warna Border & Teks (Aksen Utama)</label>
                <input type="color" name="team_hover_color_1" class="input-glass" value="<?= $team_grad1; ?>" style="height: 45px; padding: 5px; cursor: pointer; border-radius: 10px;">
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label class="label-text">Warna Latar Belakang Gradasi</label>
                <input type="color" name="team_hover_color_2" class="input-glass" value="<?= $team_grad2; ?>" style="height: 45px; padding: 5px; cursor: pointer; border-radius: 10px;">
            </div>
        </div>
        <div style="display: flex; justify-content: flex-end; border-top: 1px solid var(--glass-border); padding-top: 15px;">
            <button type="submit" class="btn-action-premium-rounded btn-premium-save-green">
                <i class="fas fa-save"></i> SIMPAN TEMA HOVER
            </button>
        </div>
    </form>
</div>

<div class="glass-card mt-4">
    <h4 style="font-size: 1rem; border-bottom: 1px solid var(--glass-border); padding-bottom: 15px; margin-bottom: 20px; color: var(--accent);">
        <i class="fas fa-user-plus me-2"></i> Tambah Anggota Tim Baru
    </h4>

    <div class="info-box-pixel-guide">
        <i class="fas fa-info-circle"></i>
        <div class="info-box-pixel-text">
            <h6>Panduan Dimensi Berkas Foto Tim:</h6>
            <p>Rekomendasi ukuran foto adalah <b>500 x 700 piksel</b> (Aspek Rasio Vertikal 5:7) dengan latar belakang transparan berformat <b>.PNG</b> murni, agar kalkulasi visual gradasi hover di halaman Android berposisi simetris.</p>
        </div>
    </div>

    <form action="proses_team.php?action=insert" method="POST" enctype="multipart/form-data">
        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 25px; margin-bottom: 20px;">
            
            <div>
                <div class="form-group">
                    <label class="label-text">Foto Profil (.PNG Transparan)</label>
                    <input type="file" name="team_photo" class="input-glass input-file-glass-wrapper" accept="image/png" required>
                    <span style="font-size: 0.65rem; opacity: 0.4; margin-top: 7px; display:block; line-height:1.3;">*Wajib berformat berkas .PNG murni transparan</span>
                </div>
                <div class="form-group" style="margin-top: 18px; margin-bottom:0;">
                    <label class="label-text">No Urut Tampilan Grid</label>
                    <input type="number" name="sort_order" class="input-glass" value="1" min="1" required style="text-align: center; border-radius: 12px;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group" style="margin-bottom:0;">
                    <label class="label-text" style="color:#74b9ff;">Nama (Indonesia)</label>
                    <input type="text" name="name_id" class="input-glass" placeholder="Nama Lengkap" required style="border-radius: 12px;">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="label-text" style="color:#74b9ff;">Keahlian (Indonesia)</label>
                    <input type="text" name="role_id" class="input-glass" placeholder="Contoh: Videographer" required style="border-radius: 12px;">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="label-text">Nama (English)</label>
                    <input type="text" name="name_en" class="input-glass" placeholder="Full Name" required style="border-radius: 12px;">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="label-text">Keahlian (English)</label>
                    <input type="text" name="role_en" class="input-glass" placeholder="e.g. Lead Editor" required style="border-radius: 12px;">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="label-text" style="color:#ffeaa7;">Nama (Japanese)</label>
                    <input type="text" name="name_ja" class="input-glass" placeholder="フルネーム" required style="border-radius: 12px;">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="label-text" style="color:#ffeaa7;">Keahlian (Japanese)</label>
                    <input type="text" name="role_ja" class="input-glass" placeholder="例: カメラマン" required style="border-radius: 12px;">
                </div>
            </div>
        </div>

        <div style="display: flex; justify-content: flex-end; border-top: 1px solid var(--glass-border); padding-top: 15px;">
            <button type="submit" class="btn-action-premium-rounded btn-premium-action-red">
                <i class="fas fa-plus-circle"></i> TAMBAHKAN ANGGOTA TIM
            </button>
        </div>
    </form>
</div>

<div class="glass-card mt-4" style="overflow: hidden;">
    <h4 style="font-size: 1rem; border-bottom: 1px solid var(--glass-border); padding-bottom: 15px; margin-bottom: 20px;">
        <i class="fas fa-users me-2"></i> Daftar Anggota Tim Publikasi Saat Ini
    </h4>
    
    <div class="table-responsive premium-scroll-target" style="max-height: 400px; overflow-y: auto; padding-bottom:10px;">
        <table class="table-pos-invoice" style="table-layout: auto; width: 100%;">
            <thead>
                <tr>
                    <th width="60">Urutan</th>
                    <th width="80">Foto</th>
                    <th style="text-align: left;">Nama Anggota (ID / EN / JP)</th>
                    <th style="text-align: left;">Keahlian / Bidang (ID / EN / JP)</th>
                    <th width="110">Status</th>
                    <th width="110" style="text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $q_list = mysqli_query($conn, "SELECT * FROM site_team ORDER BY sort_order ASC, id DESC");
                if (mysqli_num_rows($q_list) > 0):
                    while ($row = mysqli_fetch_assoc($q_list)):
                        $img_src = "../../assets/imgs/img-team/" . $row['photo_path'];
                        $is_active = $row['is_active'] == 1;
                        
                        // FIX WARNINGS RESOLUTION: Null Coalescing Handler untuk meredam teks error kasar PHP
                        $team_name_id = $row['name_id'] ?? $row['name_en'] ?? 'No Name';
                        $team_role_id = $row['role_id'] ?? $row['role_en'] ?? 'General Staff';
                ?>
                    <tr>
                        <td style="text-align: center; font-weight: bold; color: #ffc107; vertical-align: middle;"><?= $row['sort_order']; ?></td>
                        <td style="text-align: center; vertical-align: middle;">
                            <div style="width: 45px; height: 45px; background: rgba(0,0,0,0.4); border-radius: 8px; border: 1px solid rgba(255,255,255,0.05); overflow: hidden; display:inline-flex; align-items:center; justify-content:center;">
                                <img src="<?= $img_src; ?>?v=<?= time(); ?>" style="width: 100%; height: 100%; object-fit: contain;">
                            </div>
                        </td>
                        <td style="text-align: left; line-height: 1.4; vertical-align: middle;">
                            <b style="color: #fff; font-size: 0.9rem;"><?= htmlspecialchars($team_name_id); ?></b><br>
                            <span style="font-size: 0.75rem; opacity: 0.5;">EN: <?= htmlspecialchars($row['name_en'] ?? ''); ?> | JP: <?= htmlspecialchars($row['name_ja'] ?? ''); ?></span>
                        </td>
                        <td style="text-align: left; line-height: 1.4; vertical-align: middle;">
                            <span style="color: var(--accent); font-weight: 500; font-size: 0.85rem;"><?= htmlspecialchars($team_role_id); ?></span><br>
                            <span style="font-size: 0.75rem; opacity: 0.5;">EN: <?= htmlspecialchars($row['role_en'] ?? ''); ?> | JP: <?= htmlspecialchars($row['role_ja'] ?? ''); ?></span>
                        </td>
                        <td style="text-align: center; vertical-align: middle;">
                            <a href="proses_team.php?action=toggle&id=<?= $row['id']; ?>&state=<?= $row['is_active']; ?>" 
                               class="btn-print-row" style="width: auto; padding: 5px 12px; border-radius: 8px; font-size: 0.7rem; font-weight: bold; background: <?= $is_active ? 'rgba(46, 204, 113, 0.12)' : 'rgba(239, 76, 77, 0.12)'; ?>; color: <?= $is_active ? '#2ecc71' : '#ef4c4d'; ?>; border-color: <?= $is_active ? 'rgba(46, 204, 113, 0.25)' : 'rgba(239, 76, 77, 0.25)'; ?>; text-decoration: none;" title="Klik untuk merubah visibilitas grid">
                                <?= $is_active ? '<i class="fas fa-eye me-1"></i> AKTIF' : '<i class="fas fa-eye-slash me-1"></i> SEMBUNYI'; ?>
                            </a>
                        </td>
                        <td style="text-align: center; vertical-align: middle;">
                            <div style="display: flex; gap: 8px; justify-content: center; align-items: center;">
                                <!-- RE-THEME BUTTON EDIT: Menggunakan sistem melengkung bulat premium -->
                                <button type="button" class="btn-premium-action-icon-rounded btn-edit-row-premium" 
                                        onclick='bukaJendelaModalEditTeam(<?= json_encode([
                                            "id" => $row["id"],
                                            "sort_order" => $row["sort_order"],
                                            "name_id" => $team_name_id,
                                            "name_en" => $row["name_en"] ?? "",
                                            "name_ja" => $row["name_ja"] ?? "",
                                            "role_id" => $team_role_id,
                                            "role_en" => $row["role_en"] ?? "",
                                            "role_ja" => $row["role_ja"] ?? ""
                                        ]); ?>)' title="Ubah urutan & rincian data keahlian">
                                    <i class="fas fa-edit"></i>
                                </button>
                                
                                <!-- RE-THEME BUTTON HAPUS: Menggunakan sistem melengkung bulat premium -->
                                <button type="button" class="btn-premium-action-icon-rounded btn-remove-row-premium" 
                                        onclick="konfirmasiHapusMember(<?= $row['id']; ?>)" title="Hapus Permanen">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php 
                    endwhile;
                else: 
                ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px 0; opacity: 0.4;">
                            <i class="fas fa-users-slash fa-2x mb-2 d-block" style="color:var(--accent);"></i> Belum ada profil anggota tim yang didaftarkan.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ==============================================================
     NEW MODULE COMPONENT: JENDELA OVERLAY MODAL WINDOW EDIT TEAM 
     ============================================================== -->
<div id="modalEditTeamContainer" class="modal-overlay-glass">
    <div class="modal-content-card">
        <div class="modal-header-naufaru">
            <span><i class="fas fa-user-edit me-2"></i> PERBARUI PROFIL ANGGOTA TIM</span>
            <button type="button" class="btn-close-modal" onclick="tabelaModalCloseTeamAnimation()">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form action="proses_team.php?action=update" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="edit_id" id="field_edit_id">

            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px; margin-bottom: 15px;">
                <div>
                    <div class="form-group">
                        <label class="label-modal">Foto Profil (Ganti)</label>
                        <input type="file" name="team_photo" class="input-glass input-file-glass-wrapper" accept="image/png" style="font-size: 0.75rem;">
                        <span style="font-size: 0.6rem; opacity: 0.4; margin-top: 4px; display:block; line-height:1.2;">*Kosongkan jika foto tidak diganti</span>
                    </div>
                    <div class="form-group" style="margin-top: 12px;">
                        <label class="label-modal">No Urut Grid</label>
                        <input type="number" name="sort_order" id="field_edit_sort" class="input-glass" min="1" required style="text-align:center; border-radius: 12px;">
                    </div>
                </div>

                <div class="premium-scroll-target" style="display: flex; flex-direction: column; gap: 12px; max-height: 250px; overflow-y: auto; padding-right: 8px;">
                    <div>
                        <label class="label-text" style="color:#74b9ff; margin-bottom: 4px; display:block;">Nama & Jabatan (Indonesia)</label>
                        <input type="text" name="name_id" id="field_edit_name_id" class="input-glass mb-2" required style="border-radius: 12px;">
                        <input type="text" name="role_id" id="field_edit_role_id" class="input-glass" required style="border-radius: 12px;">
                    </div>
                    <div>
                        <label class="label-text" style="margin-bottom: 4px; display:block;">Nama & Jabatan (English)</label>
                        <input type="text" name="name_en" id="field_edit_name_en" class="input-glass mb-2" required style="border-radius: 12px;">
                        <input type="text" name="role_en" id="field_edit_role_en" class="input-glass" required style="border-radius: 12px;">
                    </div>
                    <div>
                        <label class="label-text" style="color:#ffeaa7; margin-bottom: 4px; display:block;">Nama & Jabatan (Japanese)</label>
                        <input type="text" name="name_ja" id="field_edit_name_ja" class="input-glass mb-2" required style="border-radius: 12px;">
                        <input type="text" name="role_ja" id="field_edit_role_ja" class="input-glass" required style="border-radius: 12px;">
                    </div>
                </div>
            </div>

            <div style="margin-top: 25px; display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 15px;">
                <!-- FIX: Merubah tombol batal melengkung rounded -->
                <button type="button" onclick="tabelaModalCloseTeamAnimation()" class="btn-action-premium-rounded" style="background: rgba(255,255,255,0.05); color:#fff; border: 1px solid rgba(255,255,255,0.1);">BATAL</button>
                <button type="submit" class="btn-action-premium-rounded btn-premium-save-green">
                    <i class="fas fa-check-circle"></i> SIMPAN PERUBAHAN DATA
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // FIX ANIMASI BUKA: Jalankan Timed Keyframes Zoom In dengan CSS murni
    function bukaJendelaModalEditTeam(data) {
        document.getElementById('field_edit_id').value = data.id;
        document.getElementById('field_edit_sort').value = data.sort_order;
        document.getElementById('field_edit_name_id').value = data.name_id;
        document.getElementById('field_edit_name_en').value = data.name_en;
        document.getElementById('field_edit_name_ja').value = data.name_ja;
        document.getElementById('field_edit_role_id').value = data.role_id;
        document.getElementById('field_edit_role_en').value = data.role_en;
        document.getElementById('field_edit_role_ja').value = data.role_ja;

        const container = document.getElementById('modalEditTeamContainer');
        const contentCard = container.querySelector('.modal-content-card');
        
        // Memasang inline style pemicu animasi entrance zoom-in halus
        contentCard.style.animation = "popupZoomInTeam 0.38s cubic-bezier(0.165, 0.84, 0.44, 1) forwards";
        container.style.display = 'flex';
    }

    // FIX ANIMASI TUTUP: Tunggu siklus delay selesai baru hilangkan kontainer agar transisi penutupan terlihat halus
    function tabelaModalCloseTeamAnimation() {
        const container = document.getElementById('modalEditTeamContainer');
        if (!container) return;
        const contentCard = container.querySelector('.modal-content-card');
        
        contentCard.style.animation = "popupZoomOutTeam 0.28s cubic-bezier(0.165, 0.84, 0.44, 1) forwards";
        
        // Tunda manipulasi display agar animasi keluar selesai dieksekusi browser terlebih dahulu
        setTimeout(() => {
            container.style.display = 'none';
            contentCard.style.animation = ""; 
        }, 260);
    }

    function konfirmasiHapusMember(memberId) {
        Swal.fire({
            title: 'Hapus Anggota?',
            text: "Profil anggota tim beserta berkas foto PNG-nya akan dihapus permanen dari sistem database.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#EF4C4D',
            cancelButtonColor: '#555',
            confirmButtonText: 'YA, HAPUS PERMANEN',
            cancelButtonText: 'BATAL',
            background: '#1a1a1a',
            color: '#fff'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "proses_team.php?action=delete&id=" + memberId;
            }
        });
    }
</script>