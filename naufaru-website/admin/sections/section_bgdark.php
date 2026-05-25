<?php
/**
 * File: admin/sections/section_bgdark.php
 * Perbaikan: Migrasi penuh ke tabel mandiri 'site_bg_dark' untuk mengisolasi background tema gelap
 */

// Sistem Deteksi Cerdas Konteks Panggilan File (Include vs Direct Request)
$is_direct_access = (basename($_SERVER['SCRIPT_FILENAME']) === 'section_bgdark.php');

if (!isset($conn)) {
    include '../../config.php';
    include '../cek_login.php';
    
    $base_upload_dir = "../../assets/imgs/";
    $redirect_target = "../admin_fitur.php";
    $gallery_img_prefix = "../assets/imgs/";
} else {
    $base_upload_dir = "../assets/imgs/";
    $redirect_target = "admin_fitur.php";
    $gallery_img_prefix = "../assets/imgs/";
}

// --- LOGIKA PROSES TAMBAH BG DARK BARU (TABEL: site_bg_dark) ---
if (isset($_POST['upload_bgdark_wallpaper'])) {
    if (isset($_FILES['wallpaper_dark_file']) && $_FILES['wallpaper_dark_file']['error'] === 0) {
        $file_name = $_FILES['wallpaper_dark_file']['name'];
        $file_size = $_FILES['wallpaper_dark_file']['size'];
        $file_tmp  = $_FILES['wallpaper_dark_file']['tmp_name'];
        
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png'];
        
        if (in_array($ext, $allowed_ext) && $file_size <= 5242880) {
            $new_name = "bg-dark-" . time() . "." . $ext;
            $upload_path = $base_upload_dir . $new_name;
            
            if (move_uploaded_file($file_tmp, $upload_path)) {
                // KORIDOR BARU: Mengarah murni ke tabel site_bg_dark
                $query_insert = "INSERT INTO site_bg_dark (image_path, is_active) VALUES ('$new_name', 1)";
                if (mysqli_query($conn, $query_insert)) {
                    header("Location: " . $redirect_target . "?status=success_bgdark");
                    exit();
                }
            }
        }
    }
    $_SESSION['bgdark_upload_errors'] = "Format berkas wajib JPG/JPEG/PNG dan ukuran maksimal adalah 5MB.";
    header("Location: " . $redirect_target . "?status=failed_bgdark");
    exit();
}

// --- LOGIKA PROSES MENGHAPUS BG DARK (TABEL: site_bg_dark) ---
if (isset($_GET['delete_dark_id'])) {
    $id_del = intval($_GET['delete_dark_id']);
    
    $check = mysqli_query($conn, "SELECT image_path FROM site_bg_dark WHERE id = $id_del");
    if ($row = mysqli_fetch_assoc($check)) {
        $file_path = $base_upload_dir . $row['image_path'];
        if (file_exists($file_path)) {
            @unlink($file_path);
        }
        mysqli_query($conn, "DELETE FROM site_bg_dark WHERE id = $id_del");
        header("Location: " . $redirect_target . "?status=success_bgdark");
        exit();
    }
}
?>

<!-- KONTEN FORMULIR ANTARMUKA PENGELOLAAN WALLPAPER DARK TEMA -->
<div class="glass-card mb-4 animate__animated animate__fadeIn">
    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--glass-border); padding-bottom: 15px; margin-bottom: 25px;">
        <h4 style="margin: 0; font-size: 1rem;"><i class="fas fa-moon me-2" style="color: #00d2d3;"></i> Unggah Gambar Latar Belakang Baru (Khusus Tema Gelap)</h4>
        <span class="manual-badge" style="background: rgba(0, 210, 211, 0.15); color: #00d2d3; border-color: rgba(0, 210, 211, 0.3);">Dark Mode Only</span>
    </div>

    <form action="sections/section_bgdark.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="upload_bgdark_wallpaper" value="1">
        
        <div class="w-100">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="label-text" style="color: #00d2d3;">Pilih Berkas Gambar Terkunci</label>
                <input type="file" name="wallpaper_dark_file" class="input-glass" accept="image/*" required style="padding: 9px 15px;">
            </div>
        </div>
        
        <div style="display: flex; justify-content: flex-end; width: 100%; margin-top: 15px;">
            <button type="submit" class="btn-action-premium" style="background: #00d2d3; color: #111; padding: 12px 25px; min-width: 200px;">
                <i class="fas fa-cloud-upload-alt me-2"></i> AKTIFKAN WALLPAPER
            </button>
        </div>
    </form>

    <div class="menu-divider" style="height: 1px; background: var(--glass-border); margin: 30px 0 20px 0;"></div>

    <h5 class="label-text" style="color: #00d2d3; margin-bottom: 15px;"><i class="fas fa-images me-1"></i> Daftar Wallpaper Tema Gelap Aktif Saat Ini</h5>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 15px; margin-top: 10px;">
        <?php
        // Kueri membaca murni dari tabel independen baru site_bg_dark
        $q_galeri = mysqli_query($conn, "SELECT * FROM site_bg_dark WHERE is_active = 1 ORDER BY id DESC");
        if (mysqli_num_rows($q_galeri) > 0):
            while ($g = mysqli_fetch_assoc($q_galeri)):
                $path_img = $gallery_img_prefix . $g['image_path'];
        ?>
                <div style="position: relative; border-radius: 12px; overflow: hidden; border: 1px solid var(--glass-border); background: rgba(0,0,0,0.3); aspect-ratio: 16/9;">
                    <img src="<?= $path_img ?>" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.src='../assets/imgs/bg-dark-profile.jpeg';">
                    
                    <a href="javascript:void(0)" onclick="konfirmasiHapusBgDark(<?= $g['id'] ?>)" 
                       style="position: absolute; top: 5px; right: 5px; background: rgba(239, 76, 77, 0.85); color: #fff; border: none; width: 26px; height: 26px; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; text-decoration: none; transition: 0.2s;">
                        <i class="fas fa-trash-alt"></i>
                    </a>
                </div>
        <?php 
            endwhile;
        else:
        ?>
            <div class="p-4 text-center border rounded-3" style="grid-column: 1 / -1; border-color: var(--glass-border) !important; background: rgba(255,255,255,0.01);">
                <span style="font-size: 0.8rem; opacity: 0.4; display: block; margin-bottom: 5px;"><i class="fas fa-info-circle me-1"></i> Galeri Tema Gelap Kosong</span>
                <span style="font-size: 0.75rem; opacity: 0.3;">Otomatis menggunakan fallback default: <b>bg-dark-profile.jpeg</b></span>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function konfirmasiHapusBgDark(id) {
    Swal.fire({
        title: 'Hapus Gambar?',
        text: 'Latar belakang terpilih akan dibersihkan permanen dari sistem.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4c4d',
        cancelButtonColor: '#333',
        confirmButtonText: 'YA, HAPUS',
        cancelButtonText: 'BATAL',
        background: '#1a1a1a',
        color: '#fff'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'sections/section_bgdark.php?delete_dark_id=' + id;
        }
    });
}
</script>