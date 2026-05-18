<?php 
/**
 * File: admin/admin_member.php
 * Pembaruan: Rata Tengah Tanggal & Style Tombol Hapus Premium
 */

// 1. Proteksi Sesi dan Koneksi
include 'cek_login.php'; 
include '../config.php'; 

// --- LOGIKA HAPUS MEMBER ---
if (isset($_GET['delete_member'])) {
    $id_del = intval($_GET['delete_member']); 
    
    $check_foto = mysqli_query($conn, "SELECT foto_profil FROM users_member WHERE id = $id_del");
    $data_foto = mysqli_fetch_assoc($check_foto);
    
    if ($data_foto) {
        $file_lama = $data_foto['foto_profil'];
        if (!empty($file_lama) && $file_lama != 'default-member.png') {
            $full_path = "../assets/imgs/profiles/" . $file_lama;
            if (file_exists($full_path)) { @unlink($full_path); }
        }
    }
    
    mysqli_query($conn, "DELETE FROM users_member WHERE id = $id_del");
    header("Location: admin_member.php?status=success_delete");
    exit(); 
}

// --- LOGIKA UPDATE DATA MEMBER ---
if (isset($_POST['update_member'])) {
    $id_edit = intval($_POST['member_id']);
    $nama_baru = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $whatsapp_baru = mysqli_real_escape_string($conn, $_POST['no_hp']);
    $alamat_baru = mysqli_real_escape_string($conn, $_POST['alamat']);
    
    $query_update = "UPDATE users_member SET 
                        nama_lengkap = '$nama_baru', 
                        no_hp = '$whatsapp_baru', 
                        alamat = '$alamat_baru' 
                     WHERE id = $id_edit";
                     
    if (mysqli_query($conn, $query_update)) {
        header("Location: admin_member.php?status=success_update");
    } else {
        header("Location: admin_member.php?status=failed_update");
    }
    exit();
}

// Ambil jumlah pesanan dengan status 'Pending' untuk notifikasi di sidebar
$q_pending = mysqli_query($conn, "SELECT COUNT(*) as total FROM orders WHERE status = 'Pending'");
$pending_data = mysqli_fetch_assoc($q_pending);
$total_pending = $pending_data['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NaufaRu Admin | Daftar Member</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="admin_style.css">
    <style>
        .member-avatar-wrapper {
            width: 50px; height: 50px;
            border-radius: 50%;
            overflow: hidden;
            border: 2px solid var(--glass-border);
            background: rgba(255,255,255,0.05);
            display: flex; align-items: center; justify-content: center;
        }
        .img-member-db { width: 100%; height: 100%; object-fit: cover; }
        
        .table-glass { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .table-glass th { background: rgba(239, 76, 77, 0.1); padding: 15px; text-align: left; font-size: 0.7rem; color: var(--accent); text-transform: uppercase; }
        .table-glass td { padding: 15px; border-bottom: 1px solid var(--glass-border); font-size: 0.85rem; color: white; vertical-align: middle; }
        
        /* Pengaturan Rata Tengah Kolom Tertentu */
        .text-center-cell { text-align: center !important; }

        .wa-link { color: #4cd137; text-decoration: none; font-weight: 700; transition: 0.3s; }
        .wa-link:hover { opacity: 0.7; }

        /* Style Tombol Hapus Premium Glass */
        .btn-delete-member {
            background: rgba(239, 76, 77, 0.1);
            color: #ef4c4d;
            border: 1px solid rgba(239, 76, 77, 0.2);
            padding: 10px;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
        }
        .btn-delete-member:hover {
            background: #ef4c4d;
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(239, 76, 77, 0.4);
        }

        /* Style Tombol Edit Premium Glass */
        .btn-edit-member {
            background: rgba(255, 193, 7, 0.1);
            color: #ffc107;
            border: 1px solid rgba(255, 193, 7, 0.2);
            padding: 10px;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            margin-right: 5px;
        }
        .btn-edit-member:hover {
            background: #ffc107;
            color: #111;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(255, 193, 7, 0.4);
        }

        /* --- Animasi Popup NaufaRu Premium --- */
        @keyframes popupZoomIn { from { opacity: 0; transform: scale(0.9); } to { opacity: 1; transform: scale(1); } }
        @keyframes popupZoomOut { from { opacity: 1; transform: scale(1); } to { opacity: 0; transform: scale(0.9); } }

        .modal-overlay-glass {
            display: none; position: fixed; top:0; left:0; width:100%; height:100%; 
            background: rgba(0,0,0,0.35); z-index: 9999; justify-content: center; 
            align-items: center; backdrop-filter: blur(15px);
        }

        .modal-content-card {
            width: 450px; 
            padding: 30px; 
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1); 
            border-radius: 24px;
            position: relative; 
            animation: popupZoomIn 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        }

        .modal-header-naufaru {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 1rem;
            color: #ffc107; /* Diubah menjadi Emas untuk aksi Edit/Modifikasi data */
            font-weight: 900;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .btn-close-modal {
            background: rgba(255, 255, 255, 0.05);
            border: none; color: #fff; width: 28px; height: 28px; 
            border-radius: 50%; cursor: pointer;
            display: flex; align-items: center; justify-content: center; font-size: 0.8rem;
            transition: 0.3s;
        }
        .btn-close-modal:hover {
            background: rgba(239, 76, 77, 0.2);
            color: #EF4C4D;
        }

        .info-box-modal-small {
            background: rgba(255, 193, 7, 0.05); /* Menggunakan aksen emas */
            border-left: 3px solid #ffc107;
            padding: 8px 12px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .info-box-modal-small p {
            margin: 0; font-size: 0.75rem;
            color: rgba(255,255,255,0.6); line-height: 1.4;
        }

        .form-group-modal {
            margin-bottom: 15px;
        }

        .label-modal { 
            display: block; color: #ffc107; font-size: 0.7rem; 
            font-weight: 800; text-transform: uppercase; 
            margin-bottom: 8px; letter-spacing: 1px; 
        }

        .input-premium-glass {
            width: 100%; padding: 12px 15px; background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; color: white;
            font-size: 0.9rem; transition: 0.3s; box-sizing: border-box;
        }
        .input-premium-glass:focus {
            border-color: #ffc107; background: rgba(255,255,255,0.08); outline: none;
            box-shadow: 0 0 15px rgba(255, 193, 7, 0.1);
        }

        .btn-action {
            padding: 12px 20px; font-weight: 800; letter-spacing: 1px;
            text-transform: uppercase; border-radius: 10px; font-size: 0.75rem;
            cursor: pointer; transition: 0.3s ease; display: inline-flex;
            align-items: center; justify-content: center; gap: 8px;
        }
        .btn-action:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body class="dark-mode">

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="../assets/imgs/logo-white.png" alt="Logo" class="sidebar-logo">
            <button class="toggle-btn" id="toggleBtn"><i class="fas fa-angle-left"></i></button>
        </div>
        <nav>
            <a href="admin_dashboard.php" class="nav-link"><i class="fas fa-th-large"></i> <span class="scramble-text" data-value="Dashboard">Dashboard</span></a>
            <a href="main_website.php" class="nav-link"><i class="fas fa-globe"></i> <span class="scramble-text" data-value="Website Utama">Website Utama</span></a>
            <a href="#" class="nav-link"><i class="fas fa-file-alt"></i> <span class="scramble-text" data-value="Curriculum Vitae">Curriculum Vitae</span></a>
            <a href="#" class="nav-link"><i class="fas fa-calendar-check"></i> <span class="scramble-text" data-value="Event Site">Event Site</span></a>
            <a href="admin_katalog.php" class="nav-link"><i class="fas fa-boxes"></i> <span class="scramble-text" data-value="Admin Katalog">Admin Katalog</span></a>
            <a href="admin_fitur.php" class="nav-link"><i class="fas fa-user-cog"></i> <span class="scramble-text" data-value="Admin Fitur">Admin Fitur</span></a>
            
            <a href="kelola_pesanan.php" class="nav-link">
                <i class="fas fa-shopping-cart"></i> 
                <span class="scramble-text" data-value="Kelola Pesanan">Kelola Pesanan</span>
                <?php if($total_pending > 0): ?>
                    <span class="pending-badge"><?= $total_pending ?></span>
                <?php endif; ?>
            </a>

            <a href="admin_member.php" class="nav-link active"><i class="fas fa-users"></i> <span class="scramble-text" data-value="Daftar Member">Daftar Member</span></a>
            <a href="logout.php" class="nav-link logout-link"><i class="fas fa-sign-out-alt"></i> <span class="scramble-text" data-value="Logout">Logout</span></a>
        </nav>
    </aside>

    <main class="main-content">
        <div class="glass-card welcome-card">
            <h1>Data Keanggotaan</h1>
            <p>Kelola profil member terdaftar langsung dari database NaufaRu.</p>
        </div>

        <div class="glass-card">
            <h4 style="font-size: 1rem; border-bottom: 1px solid var(--glass-border); padding-bottom: 15px; margin-bottom: 20px; margin-top: 0px;">
                <i class="fas fa-users"></i> Data Member-Member NaufaRu
            </h4>
            <table class="table-glass">
                <thead>
                    <tr>
                        <th width="80">Profil</th>
                        <th>Nama Lengkap</th>
                        <th>WhatsApp</th>
                        <th>Alamat</th>
                        <th class="text-center-cell">Tanggal Daftar</th>
                        <th class="text-center-cell" width="100">Opsi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $exec_get = mysqli_query($conn, "SELECT * FROM users_member ORDER BY created_at DESC");
                    
                    while($m = mysqli_fetch_assoc($exec_get)):
                        $img_name = !empty($m['foto_profil']) ? $m['foto_profil'] : 'default-member.png';
                        $img_path = "../assets/imgs/profiles/" . $img_name;
                    ?>
                    <tr>
                        <td>
                            <div class="member-avatar-wrapper">
                                <img src="<?= $img_path ?>" 
                                     class="img-member-db" 
                                     onerror="this.onerror=null; this.src='../assets/imgs/profiles/default-member.png';">
                            </div>
                        </td>
                        <td>
                            <div style="font-weight: 700;"><?= htmlspecialchars($m['nama_lengkap']) ?></div>
                            <span style="font-size: 0.7rem; opacity: 0.4;">ID: #<?= $m['id'] ?></span>
                        </td>
                        <td>
                            <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $m['no_hp']) ?>" target="_blank" class="wa-link">
                                <i class="fab fa-whatsapp me-1"></i> <?= htmlspecialchars($m['no_hp']) ?>
                            </a>
                        </td>
                        <td><?= htmlspecialchars($m['alamat']) ?></td>
                        <td class="text-center-cell"><?= date('d M Y', strtotime($m['created_at'])) ?></td>
                        <td class="text-center-cell">
                            <button class="btn-edit-member" title="Edit Member" onclick='bukaModalEdit(<?= json_encode($m); ?>)'>
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-delete-member" onclick="hapusMember(<?= $m['id'] ?>)" title="Hapus Member">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>

    <div id="modalEditMember" class="modal-overlay-glass" onclick="closeEditMemberModal(event)">
        <div class="modal-content-card" onclick="event.stopPropagation()">
            <div class="modal-header-naufaru">
                <span><i class="fas fa-user-edit me-2"></i> EDIT PROFIL MEMBER</span>
                <button type="button" class="btn-close-modal" onclick="closeEditMemberModal(null)">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="info-box-modal-small">
                <p><i class="fas fa-info-circle me-1" style="color:#ffc107;"></i> Pastikan data nomor WhatsApp aktif untuk koordinasi pengiriman berkas.</p>
            </div>

            <form action="admin_member.php" method="POST" id="formEditMember">
                <input type="hidden" name="member_id" id="edit_id" required>
                <input type="hidden" name="update_member" value="1">

                <div class="form-group-modal">
                    <label class="label-modal">Nama Lengkap Member</label>
                    <input type="text" name="nama_lengkap" id="edit_nama" class="input-premium-glass" required>
                </div>

                <div class="form-group-modal">
                    <label class="label-modal">Nomor WhatsApp (Aktif)</label>
                    <input type="text" name="no_hp" id="edit_hp" class="input-premium-glass" required>
                </div>

                <div class="form-group-modal" style="margin-bottom: 25px;">
                    <label class="label-modal">Alamat Domisili Pengiriman</label>
                    <textarea name="alamat" id="edit_alamat" class="input-premium-glass" rows="3" style="resize: none;" required></textarea>
                </div>

                <div class="d-flex gap-2" style="display: flex; gap: 10px; margin-top: 30px;">
                    <button type="button" onclick="closeEditMemberModal(null)" class="btn-action" style="flex:1; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); color: #fff;">
                        BATAL
                    </button>
                    <button type="submit" class="btn-action" style="flex:2; background:#ffc107; border:1px solid #ffc107; color:#111;">
                        <i class="fas fa-save"></i> SIMPAN PERUBAHAN
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="admin_script.js"></script>
    <script>

        // Fungsi Membuka Modal Edit Member & Menyuntikkan Data Form
        function bukaModalEdit(data) {
            document.getElementById('edit_id').value = data.id;
            document.getElementById('edit_nama').value = data.nama_lengkap;
            document.getElementById('edit_hp').value = data.no_hp;
            document.getElementById('edit_alamat').value = data.alamat;
            
            const modal = document.getElementById('modalEditMember');
            modal.style.display = 'flex';
        }

        // Fungsi Menutup Modal Edit Member dengan Animasi Zoom-Out Progresif
        function closeEditMemberModal(e) {
            const modal = document.getElementById('modalEditMember');
            
            // Validasi apakah penutupan dipicu tombol silang/batal (null) atau klik luar area overlay
            if (e === null || e.target === modal) {
                const content = modal.querySelector('.modal-content-card');
                
                // Menyuntikkan animasi keluar dari CSS
                content.style.animation = "popupZoomOut 0.3s ease forwards";
                
                // Menunggu durasi animasi selesai (300ms) sebelum menyembunyikan kontainer
                setTimeout(() => {
                    modal.style.display = 'none';
                    content.style.animation = ""; // Reset animasi agar dapat digunakan kembali
                }, 300);
            }
        }

        // Integrasi Alert Status Operasi CRUD Database
        document.addEventListener('DOMContentLoaded', function() {
            const params = new URLSearchParams(window.location.search);
            if (params.get('status') === 'success_update') {
                Swal.fire({ icon: 'success', title: 'Berhasil', text: 'Profil member berhasil diperbarui.', timer: 2000, showConfirmButton: false, background: '#1a1a1a', color: '#fff' });
                window.history.replaceState({}, document.title, window.location.pathname);
            } else if (params.get('status') === 'failed_update') {
                Swal.fire({ icon: 'error', title: 'Gagal', text: 'Terjadi kesalahan internal sistem.', timer: 2000, showConfirmButton: false, background: '#1a1a1a', color: '#fff' });
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        });

        function hapusMember(id) {
            Swal.fire({
                title: 'Hapus Member?',
                text: "Data akun ini akan dihapus secara permanen.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4c4d',
                cancelButtonColor: 'rgba(255,255,255,0.1)',
                confirmButtonText: 'Ya, Hapus',
                reverseButtons: true,
                background: '#1a1a1a', color: '#fff'
            }).then((result) => {
                if (result.isConfirmed) { window.location.href = 'admin_member.php?delete_member=' + id; }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const params = new URLSearchParams(window.location.search);
            if (params.get('status') === 'success_delete') {
                Swal.fire({ icon: 'success', title: 'Berhasil', text: 'Data member telah dihapus.', timer: 2000, showConfirmButton: false });
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        });
    </script>
</body>
</html>