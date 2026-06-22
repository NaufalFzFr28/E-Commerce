<?php
// proses_update_skills.php
include 'cek_login.php'; 
include '../config.php';

// --- LOGIKA HAPUS SKILL (AJAX/GET) ---
if (isset($_GET['delete_id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete_id']);
    $sql_delete = "DELETE FROM site_skills WHERE id = '$id'";
    
    if (mysqli_query($conn, $sql_delete)) {
        header("Location: main_website.php?status=success_skill");
    } else {
        header("Location: main_website.php?status=error");
    }
    exit();
}

// --- LOGIKA UPDATE & TAMBAH SKILL ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Update Existing Skills (Multi-row)
    if (isset($_POST['skill_ids'])) {
        foreach ($_POST['skill_ids'] as $index => $id) {
            $name_id = mysqli_real_escape_string($conn, $_POST['skill_name_id'][$index]);
            $name_en = mysqli_real_escape_string($conn, $_POST['skill_name_en'][$index]);
            $name_jp = mysqli_real_escape_string($conn, $_POST['skill_name_jp'][$index]);
            $pct     = mysqli_real_escape_string($conn, $_POST['percentage'][$index]);
            
            $sql_update = "UPDATE site_skills SET 
                skill_name_id = '$name_id', 
                skill_name_en = '$name_en', 
                skill_name_jp = '$name_jp', 
                percentage = '$pct' 
                WHERE id = '$id'";
            mysqli_query($conn, $sql_update);
        }
    }

    // Tambah Skill Baru (Jika ada input baru)
    if (!empty($_POST['new_skill_id'])) {
        $new_id = mysqli_real_escape_string($conn, $_POST['new_skill_id']);
        $new_en = mysqli_real_escape_string($conn, $_POST['new_skill_en']);
        $new_jp = mysqli_real_escape_string($conn, $_POST['new_skill_jp']);
        $new_pct = mysqli_real_escape_string($conn, $_POST['new_percentage']);
        
        $sql_add = "INSERT INTO site_skills (skill_name_id, skill_name_en, skill_name_jp, percentage) 
                    VALUES ('$new_id', '$new_en', '$new_jp', '$new_pct')";
        mysqli_query($conn, $sql_add);
    }

    header("Location: main_website.php?status=success_skill");
}
?>