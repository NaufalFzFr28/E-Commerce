<?php
/**
 * File: admin/sections/section_testimonial.php
 * Deskripsi: Panel Admin untuk Manajemen Testimoni (Approval, Edit, Delete)
 */

if (!isset($conn)) {
    include '../../config.php';
    include '../cek_login.php';
}

$q_testi = mysqli_query($conn, "SELECT t.*, m.nama_lengkap as member_name, m.foto_profil 
                                FROM site_testimonials t 
                                LEFT JOIN users_member m ON t.member_id = m.id 
                                ORDER BY t.created_at DESC");
?>

<div class="glass-card welcome-card" style="margin-top: 50px;">
    <h1>Konfigurasi Testimonial Universal</h1>
    <p style="font-size: 0.85rem; opacity: 0.8;">Atur ulasan member dan buat ulasan baru secara manual di sini.</p>
</div>

<div class="glass-card mb-4 animate__animated animate__fadeIn">
    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--glass-border); padding-bottom: 15px; margin-bottom: 25px;">
        <h4 style="margin: 0; font-size: 1rem;"><i class="fas fa-quote-left me-2" style="color: #EF4C4D;"></i> Manajemen Testimoni Pelanggan</h4>
        <button class="btn-glass-primary" onclick="openTestiManualModal()">
            <i class="fas fa-plus me-2"></i> Tambah Manual
        </button>
    </div>

    <div class="table-scroll-x">
        <table class="table-glass" style="width: 100%; border-collapse: collapse; text-align: center;">
            <thead>
                <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.1);">
                    <th style="text-align: left; padding: 0 0 15px 20px;">PELANGGAN</th>
                    <th style="text-align: center; padding: 0 0 15px 0;">PEKERJAAN</th>
                    <th style="text-align: left; padding: 0 0 15px 0;">ULASAN</th>
                    <th style="text-align: center; padding: 0 0 15px 0;">STATUS</th>
                    <th style="text-align: center; padding: 0 0 15px 0;">AKSI</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($t = mysqli_fetch_assoc($q_testi)): 
                    $name = !empty($t['member_name']) ? $t['member_name'] : $t['manual_name'];
                    $photo = !empty($t['foto_profil']) ? "../assets/imgs/profiles/".$t['foto_profil'] : (!empty($t['manual_photo']) ? "../assets/imgs/profiles/".$t['manual_photo'] : "../assets/imgs/profiles/default-member.png");
                ?>
                <tr class="glass-table-row">
                    <td style="text-align: left; padding: 18px 0 18px 20px;">
                        <div style="display:flex; align-items:center; gap:15px;">
                            <img src="<?= $photo ?>" style="width:45px; height:45px; border-radius:50%; border: 2px solid rgba(255,255,255,0.1); object-fit:cover;">
                            <span style="font-weight: 500; font-size: 0.95rem;"><?= htmlspecialchars($name) ?></span>
                        </div>
                    </td>
                    
                    <td style="text-align: center; font-size: 0.9rem; opacity: 0.8; padding: 18px 10px;">
                        <?= htmlspecialchars($t['pekerjaan']) ?>
                    </td>
                    
                    <td style="text-align: left; max-width: 250px; white-space: normal; font-size: 0.85rem; opacity: 0.7; line-height: 1.6; padding: 18px 10px;">
                        "<?= htmlspecialchars(substr($t['review_text'], 0, 75)) ?>..."
                    </td>
                    
                    <td style="text-align: center; padding: 18px 10px;">
                        <span class="manual-badge" style="background: <?= $t['is_active'] ? 'rgba(46, 204, 113, 0.15)' : 'rgba(231, 76, 60, 0.15)' ?>; color: <?= $t['is_active'] ? '#2ecc71' : '#e74c3c' ?>; padding: 6px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; border: 1px solid <?= $t['is_active'] ? 'rgba(46, 204, 113, 0.3)' : 'rgba(231, 76, 60, 0.3)' ?>;">
                            <?= $t['is_active'] ? 'AKTIF' : 'PENDING' ?>
                        </span>
                    </td>
                    
                    <td style="text-align: center; padding: 18px 10px;">
                        <div style="display: flex; justify-content: center; gap: 8px;">
                            <button class="btn-action-glass edit-btn" onclick='openEditTestiModal(<?= json_encode($t) ?>)' title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-action-glass delete-btn" onclick="konfirmasiHapusTesti(<?= $t['id'] ?>)" title="Hapus">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="modalEditTesti" class="modal-overlay-glass" style="display: none;">
    <div class="modal-content-card glass-modal-box">
        <div class="modal-header-naufaru" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <span style="font-weight: 700; letter-spacing: 1px;"><i class="fas fa-pen me-2"></i> EDIT TESTIMONI</span>
            <button type="button" class="btn-close-glass" onclick="closeEditTestiModal()"><i class="fas fa-times"></i></button>
        </div>
        
        <form action="proses_testimonial.php?action=update" method="POST">
            <input type="hidden" name="id" id="edit_testi_id">
            
            <div class="form-group-modal mt-3">
                <label class="label-modal text-white">Pekerjaan</label>
                <input type="text" name="pekerjaan" id="edit_pekerjaan" class="input-premium-glass" required>
            </div>
            
            <div class="form-group-modal mt-4">
                <label class="label-modal text-white">Ulasan Pelanggan</label>
                <textarea name="review_text" id="edit_review" class="input-premium-glass" rows="5" required style="resize: none;"></textarea>
            </div>
            
            <div class="form-group-modal mt-4" style="background: rgba(255,255,255,0.05); padding: 18px 20px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.1);">
                <label class="label-modal" style="display: flex; align-items: center; cursor: pointer; margin: 0;">
                    <input type="checkbox" name="is_active" id="edit_is_active" style="width: 20px; height: 20px; margin-right: 15px; cursor: pointer;"> 
                    <span style="font-size: 0.95rem; font-weight: 500;">Aktifkan Testimoni & Tampilkan di Homepage</span>
                </label>
            </div>
            
            <button type="submit" class="btn-glass-primary mt-4" style="width: 100%; padding: 14px; font-size: 1rem;">SIMPAN PERUBAHAN</button>
        </form>
    </div>
</div>

<div id="modalAddManualTesti" class="modal-overlay-glass" style="display: none;">
    <div class="modal-content-card glass-modal-box">
        <div class="modal-header-naufaru" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <span style="font-weight: 700; letter-spacing: 1px;"><i class="fas fa-user-plus me-2"></i> TAMBAH TESTIMONI MANUAL</span>
            <button type="button" class="btn-close-glass" onclick="closeTestiManualModal()"><i class="fas fa-times"></i></button>
        </div>
        
        <form action="proses_testimonial.php?action=add" method="POST" enctype="multipart/form-data">
            <div class="form-group-modal mt-3">
                <label class="label-modal text-white">Nama Klien</label>
                <input type="text" name="manual_name" class="input-premium-glass" placeholder="Contoh: Budi Santoso" required>
            </div>
            
            <div class="form-group-modal mt-4">
                <label class="label-modal text-white">Pekerjaan / Jabatan</label>
                <input type="text" name="pekerjaan" class="input-premium-glass" placeholder="Contoh: CEO PT. XYZ" required>
            </div>
            
            <div class="form-group-modal mt-4">
                <label class="label-modal text-white">Ulasan Klien</label>
                <textarea name="review_text" class="input-premium-glass" rows="4" placeholder="Tuliskan ulasan klien di sini..." required style="resize: none;"></textarea>
            </div>
            
            <div class="form-group-modal mt-4">
                <label class="label-modal text-white">Foto Klien (Opsional)</label>
                <input type="file" name="manual_photo" class="input-premium-glass file-input-custom" accept="image/*">
            </div>
            
            <button type="submit" class="btn-glass-primary mt-4" style="width: 100%; padding: 14px; font-size: 1rem;">TAMBAHKAN TESTIMONI</button>
        </form>
    </div>
</div>

<style>
/* Styling Baris Tabel Testimoni */
.glass-table-row {
    border-bottom: 1px solid rgba(255, 255, 255, 0.05); /* Garis pembatas tipis antar item */
    transition: all 0.3s ease;
}

.glass-table-row:last-child {
    border-bottom: none; /* Hilangkan garis di item paling bawah */
}

.glass-table-row:hover {
    background: rgba(255, 255, 255, 0.02); /* Efek terang sedikit saat di-hover */
}
/* Perbaikan Spasi pada Kontainer Modal */
.modal-content-card.glass-modal-box {
    padding: 35px; /* Memberikan ruang napas di dalam popup */
    width: 90%;
    max-width: 600px;
    box-sizing: border-box; /* Memastikan padding tidak memperlebar box */
}

/* Jarak Teks (Label) ke Textbox */
.label-modal {
    display: block; /* Memaksa label pindah ke baris sendiri (di atas textbox) */
    margin-bottom: 10px; /* Jarak antara teks dan textbox */
    font-size: 0.9rem;
    font-weight: 500;
}

/* Style Kotak Input (TextBox) Modal & Perbaikan Margin Kanan */
.input-premium-glass {
    width: 100%;
    box-sizing: border-box; /* KUNCI: Mencegah textbox menempel atau tembus ke kanan */
    background: rgba(0, 0, 0, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.15);
    color: #fff;
    padding: 14px 18px; /* Padding dalam textbox dipertebal */
    border-radius: 12px;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    outline: none;
}
.input-premium-glass:focus {
    border-color: #EF4C4D;
    background: rgba(0, 0, 0, 0.4);
    box-shadow: 0 0 10px rgba(239, 76, 77, 0.2);
}

/* Style Khusus Tombol "Choose File" (Glassmorphism) */
.file-input-custom {
    padding: 8px 12px; /* Dikecilkan sedikit agar tombol choose file pas */
}
.file-input-custom::file-selector-button {
    background: rgba(239, 76, 77, 0.15);
    color: #EF4C4D;
    border: 1px solid rgba(239, 76, 77, 0.3);
    padding: 8px 16px;
    border-radius: 8px;
    margin-right: 15px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 600;
    backdrop-filter: blur(5px);
}
.file-input-custom::file-selector-button:hover {
    background: #EF4C4D;
    color: #fff;
    box-shadow: 0 4px 10px rgba(239, 76, 77, 0.3);
}

/* Margin Tambahan (Utility Class) */
.mt-3 { margin-top: 15px !important; }
.mt-4 { margin-top: 25px !important; }
.mb-4 { margin-bottom: 25px !important; }

/* Style Tombol Utama Glassmorphism */
.btn-glass-primary {
    background: rgba(239, 76, 77, 0.15);
    color: #EF4C4D;
    border: 1px solid rgba(239, 76, 77, 0.3);
    border-radius: 12px;
    padding: 10px 24px; /* Tambahan ruang atas-bawah 10px, kiri-kanan 24px */
    font-size: 0.95rem; /* Ukuran teks diperbesar sedikit agar seimbang */
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    backdrop-filter: blur(5px);
}

.btn-glass-primary:hover {
    background: #EF4C4D;
    color: #fff;
    box-shadow: 0 5px 15px rgba(239, 76, 77, 0.3);
    transform: translateY(-2px);
}

/* Style Tombol Aksi Tabel (Edit/Hapus) */
.btn-action-glass {
    width: 35px;
    height: 35px;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
}
.btn-action-glass.edit-btn { color: #3498db; }
.btn-action-glass.edit-btn:hover { background: #3498db; color: #fff; border-color: #3498db; }

.btn-action-glass.delete-btn { color: #e74c3c; }
.btn-action-glass.delete-btn:hover { background: #e74c3c; color: #fff; border-color: #e74c3c; }

/* Style Tombol Close Modal (X) */
.btn-close-glass {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: #fff;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    cursor: pointer;
    transition: 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
}
.btn-close-glass:hover {
    background: #e74c3c;
    border-color: #e74c3c;
    transform: rotate(90deg);
}

/* Animasi Buka/Tutup Modal */
.glass-modal-box {
    animation: modalZoomIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
@keyframes modalZoomIn {
    0% { transform: scale(0.8); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
}
.modal-zoom-out {
    animation: modalZoomOut 0.25s ease forwards;
}
@keyframes modalZoomOut {
    0% { transform: scale(1); opacity: 1; }
    100% { transform: scale(0.9); opacity: 0; }
}
</style>

<script>
// Fungsi Konfirmasi Hapus SweetAlert
function konfirmasiHapusTesti(id) {
    Swal.fire({
        title: 'Hapus Testimoni?',
        text: "Ulasan ini akan dihapus secara permanen dari database.",
        icon: 'warning',
        showCancelButton: true,
        background: '#1a1a1a',
        color: '#fff',
        confirmButtonColor: '#ef4c4d',
        cancelButtonColor: '#555',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'proses_testimonial.php?action=delete&id=' + id;
        }
    });
}

// LOGIKA MODAL EDIT TESTIMONI
function openEditTestiModal(data) {
    document.getElementById('edit_testi_id').value = data.id;
    document.getElementById('edit_pekerjaan').value = data.pekerjaan;
    document.getElementById('edit_review').value = data.review_text;
    document.getElementById('edit_is_active').checked = (data.is_active == 1);
    
    const modal = document.getElementById('modalEditTesti');
    const modalBox = modal.querySelector('.glass-modal-box');
    modalBox.classList.remove('modal-zoom-out');
    modal.style.display = 'flex';
}

function closeEditTestiModal() {
    const modal = document.getElementById('modalEditTesti');
    const modalBox = modal.querySelector('.glass-modal-box');
    modalBox.classList.add('modal-zoom-out');
    setTimeout(() => { modal.style.display = 'none'; }, 250);
}

// LOGIKA MODAL TAMBAH MANUAL
function openTestiManualModal() {
    const modal = document.getElementById('modalAddManualTesti');
    const modalBox = modal.querySelector('.glass-modal-box');
    modalBox.classList.remove('modal-zoom-out');
    modal.style.display = 'flex';
}

function closeTestiManualModal() {
    const modal = document.getElementById('modalAddManualTesti');
    const modalBox = modal.querySelector('.glass-modal-box');
    modalBox.classList.add('modal-zoom-out');
    setTimeout(() => { modal.style.display = 'none'; }, 250);
}

// Tutup modal jika klik di luar box (Overlay Click)
window.addEventListener('click', function(e) {
    const editModal = document.getElementById('modalEditTesti');
    const addModal = document.getElementById('modalAddManualTesti');
    
    if (e.target === editModal) closeEditTestiModal();
    if (e.target === addModal) closeTestiManualModal();
});

// ==========================================
// PENANGKAP STATUS URL (NOTIFIKASI SUKSES CRUD TESTIMONI)
// ==========================================
document.addEventListener("DOMContentLoaded", function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('status')) {
        const status = urlParams.get('status');
        const isDark = document.body.classList.contains('dark-mode');
        
        let title = '';
        let text = '';
        let icon = 'success';

        // Tentukan teks berdasarkan status URL dari proses_testimonial.php
        if (status === 'success_testi_delete') {
            title = 'Berhasil Dihapus!';
            text = 'Data testimoni pelanggan telah dihapus permanen.';
        } else if (status === 'success_testi_update') {
            title = 'Berhasil Diperbarui!';
            text = 'Perubahan data testimoni berhasil disimpan.';
        } else if (status === 'success_testi') {
            title = 'Berhasil Ditambahkan!';
            text = 'Testimoni manual berhasil disimpan ke dalam database.';
        } else if (status === 'error') {
            title = 'Gagal Memproses!';
            text = 'Terjadi kesalahan pada sistem. Silakan coba lagi.';
            icon = 'error';
        }

        // Tampilkan SweetAlert jika status dikenali (title tidak kosong)
        if (title !== '') {
            Swal.fire({
                icon: icon,
                title: title,
                text: text,
                background: isDark ? '#1a1a1a' : '#ffffff',
                color: isDark ? '#ffffff' : '#333333',
                confirmButtonColor: '#EF4C4D',
                timer: 3500, // Menutup otomatis dalam 3.5 detik
                timerProgressBar: true,
                customClass: {
                    popup: isDark ? 'glass-card border border-secondary' : 'shadow-lg border-0 rounded-4'
                }
            });
            
            // Bersihkan URL agar popup tidak terus-terusan muncul saat halaman direfresh
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    }
});
</script>