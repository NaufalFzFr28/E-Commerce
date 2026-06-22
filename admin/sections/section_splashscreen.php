<?php
/**
 * File: admin/sections/section_splashscreen.php
 * Deskripsi: Komponen Manajemen Wallpaper Splash Screen dengan Perbaikan Jalur Preview Default
 */

// Ambil data wallpaper yang ada saat ini di database untuk pratinjau kasir/admin
$current_wallpapers = [];
$q_wall = mysqli_query($conn, "SELECT * FROM site_wallpaper ORDER BY sort_order ASC LIMIT 3");
while ($w = mysqli_fetch_assoc($q_wall)) {
    $current_wallpapers[$w['sort_order']] = $w['image_path'];
}

// Fallback jika database kosong/belum di-seed
for ($i = 1; $i <= 3; $i++) {
    if (!isset($current_wallpapers[$i])) {
        $current_wallpapers[$i] = "bg-" . $i . ".jpg";
    }
}
?>

<div class="glass-card mt-4">
    <h4 style="font-size: 1rem; border-bottom: 1px solid var(--glass-border); padding-bottom: 15px; margin-bottom: 25px; margin-top: 0px; color: #ffc107;">
        <i class="fas fa-images me-2"></i> Konfigurasi Wallpaper Splash Screen
    </h4>

    <form action="proses_splashscreen.php" method="POST" enctype="multipart/form-data" id="formSplashScreen">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 25px; margin-bottom: 30px;">
            
            <?php for ($slot = 1; $slot <= 3; $slot++): 
                $img_name = $current_wallpapers[$slot];
                // Path dihitung dari admin_fitur.php (naik satu folder ke root baru ke assets)
                $preview_path = "../assets/imgs/" . $img_name;
                
                // Jika file kustom tidak ditemukan secara fisik, beralih ke file default internal
                if (!file_exists($preview_path) || empty($img_name)) {
                    $preview_path = "../assets/imgs/bg-" . $slot . ".jpg"; 
                }
            ?>
                <div class="slot-card-wrapper" data-slot="<?= $slot; ?>" style="background: rgba(255,255,255,0.02); border: 1px solid var(--glass-border); border-radius: 16px; padding: 20px; text-align: center; display: flex; flex-direction: column; align-items: center; position: relative;">
                    <span class="label-text" style="color: var(--accent); margin-bottom: 15px;">SLOT BACKGROUND <?= $slot; ?></span>
                    
                    <input type="hidden" name="reset_slot_<?= $slot; ?>" id="resetInputSlot<?= $slot; ?>" value="0">

                    <div style="width: 100%; height: 130px; border-radius: 10px; overflow: hidden; margin-bottom: 15px; border: 1px solid rgba(255,255,255,0.05); background: #000; position: relative;">
                        <img id="imgPreviewSlot<?= $slot; ?>" src="<?= $preview_path; ?>?v=<?= time(); ?>" alt="Preview Slot <?= $slot; ?>" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>

                    <span id="labelFileNameSlot<?= $slot; ?>" style="font-size: 0.75rem; opacity: 0.5; display: block; margin-bottom: 15px; word-break: break-all;">
                        <i class="fas fa-link me-1"></i> <?= $img_name; ?>
                    </span>

                    <div class="form-group" style="width: 100%; margin-bottom: 15px;">
                        <input type="file" name="wallpaper_slot_<?= $slot; ?>" class="input-glass file-input-splash" accept="image/jpeg, image/png, image/jpg" style="padding: 8px 12px; font-size: 0.8rem;">
                    </div>

                    <button type="button" class="btn-reset-default-splash" onclick="triggerResetDefaultSlot(<?= $slot; ?>)" style="width: 100%; padding: 10px; border-radius: 10px; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); color: rgba(255,255,255,0.7); font-size: 0.75rem; font-weight: 600; cursor: pointer; transition: all 0.2s ease; display: inline-flex; align-items: center; justify-content: center; gap: 8px;">
                        <i class="fas fa-undo-alt"></i> Kembalikan ke Default
                    </button>
                </div>
            <?php endfor; ?>

        </div>

        <div style="display: flex; justify-content: flex-end; gap: 15px;">
            <button type="submit" class="btn-action-premium" style="background: var(--accent); color: white; min-width: 240px; padding: 14px;">
                <i class="fas fa-save"></i> PERBARUI WALLPAPER SPLASH
            </button>
        </div>
    </form>
</div>

<style>
    .btn-reset-default-splash:hover {
        background: rgba(239, 76, 77, 0.1) !important;
        border-color: rgba(239, 76, 77, 0.3) !important;
        color: #ef4c4d !important;
    }
</style>

<script>
    // FIX: Perbaikan Jalur Relatif di JavaScript dari sisi Halaman Utama Admin (admin_fitur.php)
    function triggerResetDefaultSlot(slotId) {
        // Set flag reset menjadi 1 agar dibaca oleh skrip proses PHP
        document.getElementById('resetInputSlot' + slotId).value = "1";
        
        // Ubah text preview link secara real-time ke default string
        document.getElementById('labelFileNameSlot' + slotId).innerHTML = '<i class="fas fa-link me-1"></i> bg-' + slotId + '.jpg <span style="color:#ffc107;">(Reset Request)</span>';
        
        // PERBAIKAN UTAMA: Menggunakan jalur relasi yang valid dari admin_fitur.php (naik satu tingkat folder)
        document.getElementById('imgPreviewSlot' + slotId).src = '../assets/imgs/bg-' + slotId + '.jpg';
        
        // Bersihkan data file input jika sebelumnya admin sempat memilih berkas lokal
        const currentSlotWrapper = document.querySelector('.slot-card-wrapper[data-slot="' + slotId + '"]');
        if (currentSlotWrapper) {
            const fileInput = currentSlotWrapper.querySelector('.file-input-splash');
            if (fileInput) { fileInput.value = ""; }
        }
    }

    // Reset flag otomatis kembali ke 0 jika admin mendadak memilih file baru lewat tombol 'Choose File'
    document.querySelectorAll('.file-input-splash').forEach(function(inputElement) {
        inputElement.addEventListener('change', function() {
            if (this.files.length > 0) {
                const parentSlotId = this.closest('.slot-card-wrapper').getAttribute('data-slot');
                document.getElementById('resetInputSlot' + parentSlotId).value = "0";
                document.getElementById('labelFileNameSlot' + parentSlotId).innerHTML = '<i class="fas fa-link me-1"></i> ' + this.files[0].name;
                
                // File reader preview lokal browser
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('imgPreviewSlot' + parentSlotId).src = e.target.result;
                }
                reader.readAsDataURL(this.files[0]);
            }
        });
    });

    // Validasi ukuran berkas maksimal 5MB sebelum dikirim
    document.getElementById('formSplashScreen').addEventListener('submit', function(e) {
        let files = this.querySelectorAll('input[type="file"]');
        let valid = true;
        
        files.forEach(function(input) {
            if (input.files.length > 0) {
                let file = input.files[0];
                let fileSize = file.size / 1024 / 1024;
                if (fileSize > 5) {
                    Swal.fire({ icon: 'error', title: 'File Terlalu Besar!', text: 'Maksimal ukuran file gambar adalah 5MB.', background: '#1a1a1a', color: '#fff', confirmButtonColor: '#ef4c4d' });
                    valid = false;
                }
            }
        });

        if (!valid) { e.preventDefault(); }
    });
</script>