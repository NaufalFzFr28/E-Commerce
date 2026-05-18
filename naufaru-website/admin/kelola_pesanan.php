<?php 
// Memastikan sesi admin aktif sebelum mengakses halaman ini
include 'cek_login.php'; 
// Menghubungkan ke database naufaru_db
include '../config.php'; 

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
    <title>NaufaRu Admin | Kelola Pesanan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="admin_style.css">
    <style>
        /* Styling tambahan untuk tabel agar serasi dengan glass card */
        .order-table {
            width: 100%;
            border-collapse: collapse;
            color: white;
            margin-top: 20px;
        }
        .order-table th {
            text-align: left;
            padding: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 0.9rem;
            opacity: 0.7;
            text-transform: uppercase;
        }
        .order-table td {
            padding: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            font-size: 0.95rem;
        }
        .status-badge {
            padding: 5px 12px;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .status-pending { 
            background: rgba(255, 193, 7, 0.2); 
            color: #ffc107; 
            border: 1px solid #ffc107; 
        }
        .status-finished { 
            background: rgba(25, 135, 84, 0.2); 
            color: #2ecc71; 
            border: 1px solid #2ecc71; 
        }
        .btn-action {
            padding: 6px 15px;
            border-radius: 5px;
            text-decoration: none;
            color: white;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            font-size: 0.85rem;
            transition: 0.3s;
        }
        .btn-action:hover {
            background: #EF4C4D;
            border-color: #EF4C4D;
        }
        .btn-print {
            background: rgba(239, 76, 77, 0.2);
            border-color: #EF4C4D;
            color: #EF4C4D;
            margin-left: 5px;
        }
        /* Hover khusus tombol print */
        .btn-print:hover {
            background: #EF4C4D;
            color: white !important; /* Mengubah icon jadi putih saat hover */
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(239, 76, 77, 0.3);
        }
    </style>
</head>
<body>

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="../assets/imgs/logo-white.png" alt="Logo" class="sidebar-logo">
            <button class="toggle-btn" id="toggleBtn">
                <i class="fas fa-angle-left" id="toggleIcon"></i>
            </button>
        </div>
        <nav>
            <a href="admin_dashboard.php" class="nav-link"><i class="fas fa-th-large"></i> <span class="scramble-text" data-value="Dashboard">Dashboard</span></a>
            <a href="main_website.php" class="nav-link"><i class="fas fa-globe"></i> <span class="scramble-text" data-value="Website Utama">Website Utama</span></a>
            <a href="#" class="nav-link"><i class="fas fa-file-alt"></i> <span class="scramble-text" data-value="Curriculum Vitae">Curriculum Vitae</span></a>
            <a href="#" class="nav-link"><i class="fas fa-calendar-check"></i> <span class="scramble-text" data-value="Event Site">Event Site</span></a>
            <a href="admin_katalog.php" class="nav-link"><i class="fas fa-boxes"></i> <span class="scramble-text" data-value="Admin Katalog">Admin Katalog</span></a>
            <a href="admin_fitur.php" class="nav-link"><i class="fas fa-user-cog"></i> <span class="scramble-text" data-value="Admin Fitur">Admin Fitur</span></a>
            
            <a href="kelola_pesanan.php" class="nav-link active">
                <i class="fas fa-shopping-cart"></i> 
                <span class="scramble-text" data-value="Kelola Pesanan">Kelola Pesanan</span>
                <?php if($total_pending > 0): ?>
                    <span class="pending-badge"><?= $total_pending ?></span>
                <?php endif; ?>
            </a>

            <a href="admin_member.php" class="nav-link"><i class="fas fa-users"></i> <span class="scramble-text" data-value="Daftar Member">Daftar Member</span></a>
            <a href="logout.php" class="nav-link logout-link"><i class="fas fa-sign-out-alt"></i> <span class="scramble-text" data-value="Logout">Logout</span></a>
        </nav>
    </aside>

    <main class="main-content">
        <div class="glass-card welcome-card">
            <h1>Kelola <b>Pesanan</b></h1>
            <p>Manajemen pesanan masuk dan penerbitan invoice member NaufaRu.</p>
        </div>

        <div class="glass-card" style="margin-top: 20px; padding: 25px;">
            <h4 style="font-size: 1rem; border-bottom: 1px solid var(--glass-border); padding-bottom: 15px; margin-bottom: 0px; margin-top: 0px;">
                <i class="fas fa-history me-2"></i> Riwayat Pesanan Masuk
            </h4>
            <div style="overflow-x: auto;">
                <table class="order-table">
                    <thead>
                        <tr>
                            <th>Invoice No.</th>
                            <th>Member</th>
                            <th>Tanggal Masuk</th>
                            <th>Total Tagihan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT o.*, m.nama_lengkap 
                                  FROM orders o 
                                  JOIN users_member m ON o.member_id = m.id 
                                  ORDER BY o.created_at DESC";
                        $result = mysqli_query($conn, $query);

                        if (mysqli_num_rows($result) > 0):
                            while ($row = mysqli_fetch_assoc($result)):
                                $status_label = ($row['status'] == 'Pending') ? 'Pending' : 'Finished';
                                $status_class = ($row['status'] == 'Pending') ? 'status-pending' : 'status-finished';
                        ?>
                            <tr>
                                <td style="color: #EF4C4D; font-weight: bold;"><?= $row['order_number'] ?></td>
                                <td>
                                    <b><?= htmlspecialchars($row['nama_lengkap']) ?></b><br>
                                    <small style="opacity: 0.5;">ID: #MBR-<?= $row['member_id'] ?></small>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?> WIB</td>
                                <td style="color: #2ecc71; font-weight: bold;">Rp <?= number_format($row['total_price'], 0, ',', '.') ?></td>
                                <td><span class="status-badge <?= $status_class ?>"><?= $status_label ?></span></td>
                                <td>
                                    <a href="detail_pesanan.php?id=<?= $row['id'] ?>" class="btn-action">
                                        <i class="fas fa-edit"></i> Proses
                                    </a>
                                    <?php if($row['status'] == 'Finished'): ?>
                                        <a href="invoice_print.php?id=<?= $row['id'] ?>" target="_blank" class="btn-action btn-print">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php 
                            endwhile; 
                        else: 
                        ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 50px; opacity: 0.5;">
                                    <i class="fas fa-folder-open" style="font-size: 2rem; display: block; margin-bottom: 10px;"></i>
                                    Belum ada data pesanan yang masuk.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script src="admin_script.js"></script>
</body>
</html>