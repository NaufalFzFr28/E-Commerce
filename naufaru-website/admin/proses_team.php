<?php
/**
 * File: admin/proses_team.php
 * Deskripsi: Controller Backend Operasi CRUD (Insert, Update, Toggle, Delete) Komponen Team
 * Perbaikan: Resolusi Sinkronisasi Kolom 'name_id', Fitur Update Data, & Validasi Pembersihan Disk Physical
 */
session_start();
include 'cek_login.php';
include '../config.php';

$action = $_GET['action'] ?? '';

// --- ROUTE ACTION 1: UPDATE WARNA GLOBAL HOVER ---
if ($action === 'update_color') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $color1 = $_POST['team_hover_color_1'] ?? '#EF4C4D';
        $color2 = $_POST['team_hover_color_2'] ?? 'rgba(239, 76, 77, 0.15)';

        $stmt = $conn->prepare("UPDATE site_settings SET team_hover_color_1 = ?, team_hover_color_2 = ? WHERE id = 1");
        $stmt->bind_param("ss", $color1, $color2);
        
        if ($stmt->execute()) {
            header("Location: main_website.php?status=success_team_color");
        } else {
            header("Location: main_website.php?status=failed_team");
        }
        $stmt->close();
        exit();
    }
}

// --- ROUTE ACTION 2: INSERT ANGGOTA BARU ---
if ($action === 'insert') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name_id = $_POST['name_id'] ?? '';
        $name_en = $_POST['name_en'] ?? '';
        $name_ja = $_POST['name_ja'] ?? '';
        $role_id = $_POST['role_id'] ?? '';
        $role_en = $_POST['role_en'] ?? '';
        $role_ja = $_POST['role_ja'] ?? '';
        $sort_order = intval($_POST['sort_order'] ?? 1);

        if (isset($_FILES['team_photo']) && $_FILES['team_photo']['error'] == UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['team_photo']['tmp_name'];
            $file_name = $_FILES['team_photo']['name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            if ($file_ext !== 'png') {
                $_SESSION['team_errors'] = "Gagal Tambah: Anggota tim wajib menggunakan foto berformat .PNG transparan.";
                header("Location: main_website.php?status=failed_team_msg");
                exit();
            }

            $new_photo_name = "team_" . substr(md5(time() . rand()), 0, 10) . ".png";
            $target_path = "../../assets/imgs/img-team/" . $new_photo_name;

            if (!is_dir('../../assets/imgs/img-team/')) {
                @mkdir('../../assets/imgs/img-team/', 0777, true);
            }

            if (move_uploaded_file($file_tmp, $target_path)) {
                // FIX: Kueri eksekusi SQL diselaraskan dengan kolom tabel site_team yang sudah diperbarui
                $stmt = $conn->prepare("INSERT INTO site_team (photo_path, name_id, name_en, name_ja, role_id, role_en, role_ja, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)");
                $stmt->bind_param("sssssssi", $new_photo_name, $name_id, $name_en, $name_ja, $role_id, $role_en, $role_ja, $sort_order);
                $stmt->execute();
                $stmt->close();

                header("Location: main_website.php?status=success_team");
            } else {
                $_SESSION['team_errors'] = "Sistem gagal memindahkan file foto ke direktori tujuan.";
                header("Location: main_website.php?status=failed_team_msg");
            }
        } else {
            header("Location: main_website.php?status=failed_team");
        }
        exit();
    }
}

// --- ROUTE ACTION 3: UPDATE / SIMPAN PERUBAHAN DATA (EDIT HANDLER) ---
if ($action === 'update') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $edit_id    = intval($_POST['edit_id'] ?? 0);
        $name_id    = $_POST['name_id'] ?? '';
        $name_en    = $_POST['name_en'] ?? '';
        $name_ja    = $_POST['name_ja'] ?? '';
        $role_id    = $_POST['role_id'] ?? '';
        $role_en    = $_POST['role_en'] ?? '';
        $role_ja    = $_POST['role_ja'] ?? '';
        $sort_order = intval($_POST['sort_order'] ?? 1);
        
        $target_dir = "../../assets/imgs/img-team/";

        // Cek apakah admin mengunggah file foto PNG baru untuk mengganti foto lama
        if (isset($_FILES['team_photo']) && $_FILES['team_photo']['error'] == UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['team_photo']['tmp_name'];
            $file_name = $_FILES['team_photo']['name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            if ($file_ext !== 'png') {
                $_SESSION['team_errors'] = "Gagal Update: Berkas foto pengganti harus berformat .PNG transparan.";
                header("Location: main_website.php?status=failed_team_msg");
                exit();
            }

            // Hapus foto lama fisik dari disk storage biar hemat kapasitas disk server
            $q_old = mysqli_query($conn, "SELECT photo_path FROM site_team WHERE id = $edit_id LIMIT 1");
            if ($old_data = mysqli_fetch_assoc($q_old)) {
                $old_file = $target_dir . $old_data['photo_path'];
                if (file_exists($old_file) && !empty($old_data['photo_path'])) {
                    @unlink($old_file);
                }
            }

            // Upload foto baru
            $new_photo_name = "team_upd_" . substr(md5(time() . rand()), 0, 10) . ".png";
            move_uploaded_file($file_tmp, $target_dir . $new_photo_name);

            // Jalankan query update data termasuk path gambar baru
            $stmt = $conn->prepare("UPDATE site_team SET photo_path = ?, name_id = ?, name_en = ?, name_ja = ?, role_id = ?, role_en = ?, role_ja = ?, sort_order = ? WHERE id = ?");
            $stmt->bind_param("sssssssii", $new_photo_name, $name_id, $name_en, $name_ja, $role_id, $role_en, $role_ja, $sort_order, $edit_id);
        } else {
            // Jalankan query update rincian teks saja tanpa mengganti gambar lama
            $stmt = $conn->prepare("UPDATE site_team SET name_id = ?, name_en = ?, name_ja = ?, role_id = ?, role_en = ?, role_ja = ?, sort_order = ? WHERE id = ?");
            $stmt->bind_param("ssssssii", $name_id, $name_en, $name_ja, $role_id, $role_en, $role_ja, $sort_order, $edit_id);
        }

        if ($stmt->execute()) {
            header("Location: main_website.php?status=success_team_update"); // Memicu alert sukses edit
        } else {
            header("Location: main_website.php?status=failed_team");
        }
        $stmt->close();
        exit();
    }
}

// --- ROUTE ACTION 4: TOGGLE VISIBILITAS (AKTIF / SEMBUNYI) ---
if ($action === 'toggle') {
    $id = intval($_GET['id'] ?? 0);
    $current_state = intval($_GET['state'] ?? 1);
    $new_state = ($current_state == 1) ? 0 : 1;

    $stmt = $conn->prepare("UPDATE site_team SET is_active = ? WHERE id = ?");
    $stmt->bind_param("ii", $new_state, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: main_website.php?status=success_team_toggle");
    exit();
}

// --- ROUTE ACTION 5: HAPUS ANGGOTA PERMANEN ---
if ($action === 'delete') {
    $id = intval($_GET['id'] ?? 0);
    $target_dir = "../../assets/imgs/img-team/";

    $q_photo = mysqli_query($conn, "SELECT photo_path FROM site_team WHERE id = $id LIMIT 1");
    if ($data = mysqli_fetch_assoc($q_photo)) {
        $file_path = $target_dir . $data['photo_path'];
        if (!empty($data['photo_path']) && file_exists($file_path)) {
            @unlink($file_path);
        }
    }

    $stmt = $conn->prepare("DELETE FROM site_team WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: main_website.php?status=success_team_delete");
    exit();
}

header("Location: main_website.php");
exit();
?>