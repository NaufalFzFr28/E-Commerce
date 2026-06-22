<?php
/**
 * File: admin/sections/section_testimonial_info.php
 * Deskripsi: Modul konfigurasi Alert Info pada section Testimonial (2 Card Layout).
 */

if (!isset($conn)) {
    include '../../config.php';
    include '../cek_login.php';
}

$q_testi_info = mysqli_query($conn, "SELECT * FROM site_testi_alerts ORDER BY id DESC");
?>

<div class="glass-card welcome-card" style="margin-top: 50px;">
    <h1>Konfigurasi Info Testimonial</h1>
    <p style="font-size: 0.85rem; opacity: 0.8;">Kelola pesan pengumuman (alert) yang melayang di atas slider testimoni pelanggan.</p>
</div>

<div class="row mt-4">
    <div class="col-lg-5"> <div class="glass-card mb-4 animate__animated animate__fadeInLeft">
            <h4 style="margin-bottom: 25px; font-size: 1.1rem; color: #2ecc71; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 15px;">
                <i class="fas fa-plus-circle me-2"></i> Buat Info Baru
            </h4>
            
            <form action="proses_update_testi_info.php" method="POST">
                <input type="hidden" name="action" value="add_testi_info">
                
                <div class="form-group-modal">
                    <label class="label-modal">Teks Utama (Indonesia) *</label>
                    <textarea name="text_id" class="input-premium-glass" rows="2" required></textarea>
                </div>
                <div class="form-group-modal">
                    <label class="label-modal">Teks Utama (English)</label>
                    <textarea name="text_en" class="input-premium-glass" rows="2"></textarea>
                </div>
                <div class="form-group-modal">
                    <label class="label-modal">Teks Utama (Japanese)</label>
                    <textarea name="text_jp" class="input-premium-glass" rows="2"></textarea>
                </div>

                <div class="menu-divider" style="margin: 30px 0; background: rgba(255,255,255,0.1); height: 1px;"></div>
                <p style="font-size: 0.85rem; opacity: 0.9; margin-bottom: 20px; color: #2ecc71; font-weight: 600;">
                    <i class="fas fa-link me-2"></i>Tambahan Tautan (Opsional)
                </p>
                
                <div class="form-group-modal">
                    <label class="label-modal">Teks Link (Indonesia)</label>
                    <input type="text" name="link_text_id" class="input-premium-glass" placeholder="Cek sekarang!">
                </div>
                <div class="form-group-modal">
                    <label class="label-modal">Teks Link (English)</label>
                    <input type="text" name="link_text_en" class="input-premium-glass" placeholder="Check now!">
                </div>
                <div class="form-group-modal">
                    <label class="label-modal">Teks Link (Japanese)</label>
                    <input type="text" name="link_text_jp" class="input-premium-glass" placeholder="今すぐチェック！">
                </div>
                <div class="form-group-modal">
                    <label class="label-modal">URL Tujuan Tautan</label>
                    <input type="text" name="link_url" class="input-premium-glass" placeholder="https://...">
                </div>
                
                <div style="text-align: right; margin-top: 25px;">
                    <button type="submit" class="btn-glass-primary" style="border-color: #2ecc71; color: #2ecc71;">
                        <i class="fas fa-save me-2"></i> TAMBAHKAN INFO
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="glass-card mb-4 animate__animated animate__fadeInRight">
            <h4 style="margin-bottom: 25px; font-size: 1.1rem; color: #3498db; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 15px;">
                <i class="fas fa-list me-2"></i> Daftar Info Tersedia
            </h4>

            <form action="proses_update_testi_info.php" method="POST">
                <input type="hidden" name="action" value="update_testi_status">
                
                <?php if (mysqli_num_rows($q_testi_info) > 0): ?>
                    <div class="table-scroll-x" style="max-height: 800px; overflow-y: auto;">
                        <?php while ($info = mysqli_fetch_assoc($q_testi_info)): ?>
                            <div class="glass-item-box">
                                
                                <div class="glass-item-header">
                                    <label class="glass-checkbox-label" style="color: <?= $info['is_active'] ? '#2ecc71' : '#aaa' ?>;">
                                        <input type="checkbox" name="is_active[<?= $info['id'] ?>]" class="glass-checkbox" <?= $info['is_active'] ? 'checked' : '' ?>>
                                        <?= $info['is_active'] ? 'Tampil di Website' : 'Disembunyikan' ?>
                                    </label>
                                    
                                    <button type="button" class="btn-action-glass delete-btn" onclick="konfirmasiHapusInfo(<?= $info['id'] ?>)" title="Hapus">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-md-12 mb-3">
                                        <span class="glass-data-label">Teks Indonesia (ID):</span>
                                        <p class="glass-data-text"><?= htmlspecialchars($info['text_id']) ?></p>
                                    </div>
                                    <?php if(!empty($info['text_en'])): ?>
                                    <div class="col-md-12 mb-3">
                                        <span class="glass-data-label">Teks Inggris (EN):</span>
                                        <p class="glass-data-text"><?= htmlspecialchars($info['text_en']) ?></p>
                                    </div>
                                    <?php endif; ?>
                                    <?php if(!empty($info['text_jp'])): ?>
                                    <div class="col-md-12 mb-3">
                                        <span class="glass-data-label">Teks Jepang (JP):</span>
                                        <p class="glass-data-text"><?= htmlspecialchars($info['text_jp']) ?></p>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if(!empty($info['link_url'])): ?>
                                    <div class="col-md-12 mt-3 pt-3" style="border-top: 1px dashed rgba(255,255,255,0.1);">
                                        <span class="glass-data-label" style="color: #3498db; margin-bottom: 8px;">Tautan Aktif (<?= htmlspecialchars($info['link_url']) ?>):</span>
                                        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                                            <?php if(!empty($info['link_text_id'])): ?>
                                                <span class="badge-glass-link"><strong>ID:</strong> <?= htmlspecialchars($info['link_text_id']) ?></span>
                                            <?php endif; ?>
                                            <?php if(!empty($info['link_text_en'])): ?>
                                                <span class="badge-glass-link"><strong>EN:</strong> <?= htmlspecialchars($info['link_text_en']) ?></span>
                                            <?php endif; ?>
                                            <?php if(!empty($info['link_text_jp'])): ?>
                                                <span class="badge-glass-link"><strong>JP:</strong> <?= htmlspecialchars($info['link_text_jp']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                    
                    <div style="text-align: right; margin-top: 25px;">
                        <button type="submit" class="btn-glass-primary" style="border-color: #3498db; color: #3498db;">
                            <i class="fas fa-check-double me-2"></i> SIMPAN PERUBAHAN STATUS AKTIF
                        </button>
                    </div>
                    
                <?php else: ?>
                    <div class="text-center p-5 opacity-50 border rounded-3" style="border-color: var(--glass-border) !important; background: rgba(255,255,255,0.02);">
                        <i class="fas fa-info-circle fa-2x mb-3" style="color: #3498db;"></i>
                        <p>Database kosong. Belum ada info alert yang terdata.</p>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>

<style>
/* ==============================================
   CSS GLASSMORPHISM & PADDING PENYESUAIAN
============================================== */

/* Wrapper Jarak untuk Label dan TextBox */
.form-group-modal {
    margin-bottom: 20px; /* Jarak antar kolom input */
}

/* Jarak Label dari Textbox (Supaya tidak mepet) */
.label-modal {
    display: block;
    margin-bottom: 10px; /* Jarak ruang nafas bawah teks */
    font-size: 0.9rem;
    color: #ffffff;
    font-weight: 500;
}

/* TextBox Glassmorphic */
.input-premium-glass {
    width: 100%;
    box-sizing: border-box; /* Memastikan padding tidak membuat textbox jebol ke kanan */
    background: rgba(0, 0, 0, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.15);
    color: #fff;
    padding: 12px 18px; /* Ruang dalam (kiri-kanan) diperlebar */
    border-radius: 10px;
    font-size: 0.95rem;
    outline: none;
    transition: all 0.3s ease;
}
.input-premium-glass:focus {
    border-color: #EF4C4D;
    background: rgba(0, 0, 0, 0.4);
    box-shadow: 0 0 10px rgba(239, 76, 77, 0.2);
}

/* Style Tombol Aksi */
.btn-glass-primary {
    display: inline-block;
    background: rgba(0, 0, 0, 0.2); 
    border: 1px solid; 
    border-radius: 10px; 
    font-weight: 600; 
    cursor: pointer; 
    padding: 12px 24px; /* Padding lebih proporsional */
    font-size: 0.95rem;
    transition: all 0.3s ease;
    backdrop-filter: blur(5px);
}
.btn-glass-primary:hover {
    background: rgba(255, 255, 255, 0.1);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

/* Desain Kotak Item "Daftar Info Tersedia" */
.glass-item-box {
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid rgba(255, 255, 255, 0.1);
    padding: 25px; /* Jarak dalam kotak list */
    border-radius: 14px;
    margin-bottom: 20px;
    transition: all 0.3s ease;
}
.glass-item-box:hover {
    background: rgba(255, 255, 255, 0.04);
}

/* Header & Checkbox di Kotak List */
.glass-item-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid rgba(255,255,255,0.05);
    padding-bottom: 15px;
}
.glass-checkbox-label {
    display: flex;
    align-items: center;
    cursor: pointer;
    font-weight: 600;
    font-size: 0.95rem;
}
.glass-checkbox {
    width: 20px; 
    height: 20px; 
    margin-right: 12px; 
    cursor: pointer;
}

/* Label & Text Read-Only */
.glass-data-label {
    display: block;
    font-size: 0.75rem;
    color: #aaaaaa;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 8px; /* Jarak antara judul info dan isinya */
}
.glass-data-text {
    margin: 0;
    font-size: 0.95rem;
    color: #ffffff;
    line-height: 1.6; /* Jarak spasi antar baris bacaan */
}

/* Tombol Hapus Kecil */
.btn-action-glass {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid rgba(255, 255, 255, 0.1);
    cursor: pointer;
    background: rgba(255, 255, 255, 0.05);
    transition: 0.3s;
}
.btn-action-glass.delete-btn { color: #e74c3c; }
.btn-action-glass.delete-btn:hover { background: #e74c3c; color: #fff; border-color: #e74c3c; }

/* Lencana/Badge untuk Status Tautan (Link) */
.badge-glass-link {
    background: rgba(52, 152, 219, 0.1);
    color: #3498db;
    border: 1px solid rgba(52, 152, 219, 0.3);
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    letter-spacing: 0.5px;
}
</style>

<script>
// ==========================================
// 1. FUNGSI KONFIRMASI HAPUS SWEETALERT
// ==========================================
function konfirmasiHapusInfo(id) {
    const isDark = document.body.classList.contains('dark-mode');
    Swal.fire({
        title: 'Hapus Info Alert?',
        text: "Info ini akan dihapus secara permanen dari database.",
        icon: 'warning',
        showCancelButton: true,
        background: isDark ? '#1a1a1a' : '#ffffff',
        color: isDark ? '#ffffff' : '#333333',
        confirmButtonColor: '#e74c3c',
        cancelButtonColor: isDark ? 'rgba(255,255,255,0.1)' : '#ddd',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
        customClass: {
            popup: isDark ? 'glass-card border border-secondary' : 'shadow-lg border-0 rounded-4'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'proses_update_testi_info.php?delete_testi_info_id=' + id;
        }
    });
}

// ==========================================
// 2. PENANGKAP STATUS URL (NOTIFIKASI SUKSES)
// ==========================================
document.addEventListener("DOMContentLoaded", function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('status')) {
        const status = urlParams.get('status');
        const isDark = document.body.classList.contains('dark-mode');
        
        let title = '';
        let text = '';
        let icon = 'success';

        // Tentukan teks berdasarkan status URL
        if (status === 'success_testi_info') {
            title = 'Info Ditambahkan!';
            text = 'Teks info testimonial baru berhasil disimpan.';
        } else if (status === 'success_update_testi_info') {
            title = 'Status Diperbarui!';
            text = 'Perubahan status visibilitas info berhasil disimpan.';
        } else if (status === 'success_delete_testi_info') {
            title = 'Berhasil Dihapus!';
            text = 'Data info testimonial telah dihapus permanen.';
        } else if (status === 'error_db') {
            title = 'Gagal Memproses!';
            text = 'Terjadi kesalahan pada sistem database.';
            icon = 'error';
        }

        // Tampilkan SweetAlert jika status valid dikenali
        if (title !== '') {
            Swal.fire({
                icon: icon,
                title: title,
                text: text,
                background: isDark ? '#1a1a1a' : '#ffffff',
                color: isDark ? '#ffffff' : '#333333',
                confirmButtonColor: '#2ecc71',
                timer: 3500, // Menutup otomatis dalam 3.5 detik
                timerProgressBar: true,
                customClass: {
                    popup: isDark ? 'glass-card border border-secondary' : 'shadow-lg border-0 rounded-4'
                }
            });
            
            // Bersihkan URL agar popup tidak terus muncul jika halaman di-refresh
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    }
});    
</script>