<?php 
/**
 * File: admin/admin_member.php
 * Pembaruan: Full Sinkronisasi AJAX Premium Dark Mode, Deteksi Fitur Survei Onboarding & Jendela Info Komparatif
 */

// 1. Proteksi Sesi dan Koneksi
include 'cek_login.php'; 
include '../config.php'; 

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
    
    <!-- LOAD CORE SCRIPT & JQUERY DI ATAS UNTUK MENCEGAH RETAKNYA INTERAKSI EVENT LISTENER -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
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
            width: 38px;
            height: 38px;
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
            width: 38px;
            height: 38px;
            margin-right: 4px;
        }
        .btn-edit-member:hover {
            background: #ffc107;
            color: #111;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(255, 193, 7, 0.4);
        }

        /* STYLE BARU: Tombol Info Kuesioner Survey Pemasaran (Cyan Premium) */
        .btn-survey-info {
            background: rgba(0, 210, 211, 0.1);
            color: #00d2d3;
            border: 1px solid rgba(0, 210, 211, 0.2);
            padding: 10px;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            margin-right: 4px;
        }
        .btn-survey-info:hover {
            background: #00d2d3;
            color: #111;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 210, 211, 0.4);
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
            color: #ffc107; 
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
            background: rgba(255, 193, 7, 0.05); 
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

        /* HARD-FIX INLINE SELECTION OVERRIDE FOR SWEETALERT2 DOCKING CONTAINER */
        .swal-custom-premium-popup {
            width: 450px !important;
            background: #1a1a1a !important;
            border: 1px solid rgba(255,255,255,0.08) !important;
            border-radius: 25px !important;
            padding: 30px !important;
            box-shadow: 0 20px 45px rgba(0,0,0,0.6) !important;
        }
        .swal-custom-title-text {
            color: #ffffff !important;
            font-weight: 800 !important;
            font-size: 1.35rem !important;
            margin-top: 15px !important;
        }
        .swal-custom-html-content {
            color: rgba(255,255,255,0.75) !important;
            font-size: 0.92rem !important;
            line-height: 1.5 !important;
            margin-top: 8px !important;
        }

        /* ==============================================================
        NAUFARU SURVEY MODAL: TEXTBOX TERPISAH & RATA TENGAH
        ============================================================== */

        /* Kontainer boks teks terpisah untuk masing-masing info */
        .swal-info-box-split {
            background: rgba(255, 255, 255, 0.03) !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            border-radius: 14px !important;
            padding: 14px 20px !important;
            margin-bottom: 12px !important;
            text-align: center !important; /* Memaksa isi teks rata tengah */
            width: 100% !important;
            box-sizing: border-box !important;
        }

        .swal-info-box-label {
            font-size: 0.7rem !important;
            opacity: 0.4 !important;
            letter-spacing: 0.8px !important;
            text-transform: uppercase !important;
            font-weight: 800 !important;
            margin-bottom: 5px !important;
        }

        .swal-info-box-value-name {
            color: #ffffff !important;
            font-weight: 700 !important;
            font-size: 1rem !important;
            margin: 0 !important;
        }

        .swal-info-box-value-source {
            color: #ef4c4d !important; /* Gunakan warna merah NaufaRu sebagai aksen */
            font-weight: 800 !important;
            font-size: 1.05rem !important;
            margin: 0 !important;
        }

        /* FIX UTAMA: Tombol tutup kustom warna merah dengan efek hover gelap */
        .swal-btn-close-red {
            background-color: #ef4c4d !important; /* Merah NaufaRu */
            color: #ffffff !important;
            font-weight: 700 !important;
            letter-spacing: 0.5px !important;
            border-radius: 12px !important;
            padding: 12px 30px !important;
            font-size: 0.85rem !important;
            border: none !important;
            transition: all 0.25s ease-in-out !important;
            cursor: pointer !important;
            box-shadow: 0 4px 15px rgba(239, 76, 77, 0.2) !important;
        }

        /* Efek saat kursor berada di atas tombol tutup */
        .swal-btn-close-red:hover {
            background-color: #bd3a3b !important; /* Merah maroon lebih gelap */
            transform: translateY(-1px) !important;
            box-shadow: 0 6px 20px rgba(239, 76, 77, 0.35) !important;
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
                        <th class="text-center-cell" width="140">Opsi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // PENYESUAIAN SQL JOIN: Menghubungkan users_member dengan hasil data kuesioner onboarding
                    $sql_query = "SELECT m.*, s.source_answer, s.custom_answer 
                                  FROM users_member m 
                                  LEFT JOIN member_surveys s ON m.id = s.member_id 
                                  ORDER BY m.created_at DESC";
                    $exec_get = mysqli_query($conn, $sql_query);
                    
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
                            <!-- TOMBOL BARU: Informasi Data Kuesioner Saluran Komunikasi (Cyan Premium) -->
                            <button class="btn-survey-info" title="Lihat Sumber Informasi" 
                                    onclick='bukaInfoSurvey(<?= json_encode($m['source_answer']); ?>, <?= json_encode($m['custom_answer']); ?>, <?= json_encode($m['nama_lengkap']); ?>)'>
                                <i class="fas fa-question-circle"></i>
                            </button>
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

    <!-- CONTAINER MODAL BOX: EDIT DATA INDIVIDUAL -->
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
            if (e === null || e.target === modal) {
                const content = modal.querySelector('.modal-content-card');
                content.style.animation = "popupZoomOut 0.3s ease forwards";
                setTimeout(() => {
                    modal.style.display = 'none';
                    content.style.animation = ""; 
                }, 300);
            }
        }

        // BASE CONFIG OBJECT: Penentu ukuran solid anti-menciut di framework manapun
        const baseSwalPremium = {
            width: '450px',
            background: '#1a1a1a',
            color: '#ffffff',
            timerProgressBar: true,
            confirmButtonColor: '#ef4c4d',
            cancelButtonColor: 'rgba(255, 255, 255, 0.08)',
            customClass: {
                popup: 'swal-custom-premium-popup',
                title: 'swal-custom-title-text',
                htmlContainer: 'swal-custom-html-content'
            }
        };

        // NEW INTERFACE LOGIC: Render Jendela Popup Info Sumber Kuesioner Member Terpisah & Rata Tengah
        function bukaInfoSurvey(source, custom, namaMember) {
            let infoHtml = "";
            
            if (source === null || source === "") {
                // PERBAIKAN: Membungkus pemberitahuan belum mengisi ke dalam boks teks terpisah rata tengah
                infoHtml = `
                    <div style="width: 100%;">
                        <!-- Box 1: Informasi Nama Lengkap Member -->
                        <div class="swal-info-box-split">
                            <div class="swal-info-box-label">Nama Lengkap Member</div>
                            <div class="swal-info-box-value-name">${namaMember}</div>
                        </div>
                        
                        <!-- Box 2: Status Kuesioner (Belum Mengisi dengan Ikon dan Textbox Terpisah) -->
                        <div class="swal-info-box-split" style="margin-bottom: 5px !important; background: rgba(255, 193, 7, 0.02) !important; border-color: rgba(255, 193, 7, 0.15) !important;">
                            <div class="swal-info-box-label" style="color: #ffc107 !important;">Status Kuesioner Onboarding</div>
                            <div style="color: #ffc107; font-weight: 700; font-size: 0.9rem; margin-top: 5px; line-height: 1.5;">
                                <i class="fas fa-hourglass-half me-2 animate__animated animate__pulse animate__infinite" style="font-size: 1rem;"></i> 
                                Member terdaftar belum mengisi data kuesioner onboarding.
                            </div>
                        </div>
                    </div>
                `;
            } else {
                let detailJawaban = source;
                if (source === 'Lainnya' && custom !== '') {
                    detailJawaban = `Lainnya: "${custom}"`;
                }
                
                infoHtml = `
                    <div style="width: 100%;">
                        <!-- Box 1: Informasi Nama Lengkap Member -->
                        <div class="swal-info-box-split">
                            <div class="swal-info-box-label">Nama Lengkap Member</div>
                            <div class="swal-info-box-value-name">${namaMember}</div>
                        </div>
                        
                        <!-- Box 2: Informasi Saluran Pemasaran -->
                        <div class="swal-info-box-split" style="margin-bottom: 5px !important;">
                            <div class="swal-info-box-label">Darimana Mengetahui NaufaRu</div>
                            <div class="swal-info-box-value-source">
                                <i class="fas fa-bullhorn me-2"></i>${detailJawaban}
                            </div>
                        </div>
                    </div>
                `;
            }

            Swal.fire({
                ...baseSwalPremium,
                title: 'Info Asal Informasi Member',
                html: infoHtml,
                confirmButtonText: 'TUTUP JENDELA',
                customClass: {
                    popup: 'swal-custom-premium-popup',
                    title: 'swal-custom-title-text',
                    htmlContainer: 'swal-custom-html-content',
                    confirmButton: 'swal-btn-close-red'
                },
                buttonsStyling: false
            });
        }

        // ==========================================
        // CORE CONTROLLER: KONFIRMASI HAPUS (AJAX)
        // ==========================================
        function hapusMember(id) {
            Swal.fire({
                ...baseSwalPremium,
                title: 'Hapus Member?',
                text: "Data akun keanggotaan ini akan dihapus secara permanen dari database.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'YA, HAPUS PERMANEN',
                cancelButtonText: 'BATAL',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'proses_hapus_member.php',
                        type: 'POST',
                        data: { id: id },
                        success: function(response) {
                            if (response.trim() === "success") {
                                Swal.fire({
                                    ...baseSwalPremium,
                                    icon: 'success',
                                    title: 'Berhasil Dihapus!',
                                    text: 'Data member beserta berkas fotonya telah dibersihkan.',
                                    timer: 2000,
                                    showConfirmButton: false
                                }).then(() => {
                                    location.reload(); 
                                });
                            } else {
                                Swal.fire({
                                    ...baseSwalPremium,
                                    icon: 'error',
                                    title: 'Gagal Menghapus',
                                    text: 'Terjadi kegagalan komunikasi kueri pada server internal.',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                ...baseSwalPremium,
                                icon: 'error',
                                title: 'Network Error',
                                text: 'Gagal menghubungkan ke berkas eksekutor PHP.',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        }
                    });
                }
            });
        }

        // SINKRONISASI EVALUASI: Detektor Tunggal Notifikasi Operasi UPDATE
        document.addEventListener('DOMContentLoaded', function() {
            const params = new URLSearchParams(window.location.search);
            const status = params.get('status');

            if (status === 'success_update') {
                Swal.fire({
                    ...baseSwalPremium,
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Profil member berhasil diperbarui.',
                    timer: 2000,
                    showConfirmButton: false
                });
                window.history.replaceState({}, document.title, window.location.pathname);
            } else if (status === 'failed_update') {
                Swal.fire({
                    ...baseSwalPremium,
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Terjadi kesalahan internal sistem data.',
                    timer: 2000,
                    showConfirmButton: false
                });
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        });
    </script>
</body>
</html>