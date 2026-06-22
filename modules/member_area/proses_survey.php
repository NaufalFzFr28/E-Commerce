<?php
session_start();
include '../../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['member_id'])) {
    $member_id = intval($_SESSION['member_id']);
    $source = mysqli_real_escape_string($conn, $_POST['source_answer']);
    $custom = isset($_POST['custom_answer']) ? mysqli_real_escape_string($conn, $_POST['custom_answer']) : '';

    // Validasi double-input perlindungan ganda
    $check = mysqli_query($conn, "SELECT id FROM member_surveys WHERE member_id = $member_id");
    if (mysqli_num_rows($check) > 0) {
        echo "already_filled";
        exit();
    }

    $query = "INSERT INTO member_surveys (member_id, source_answer, custom_answer) VALUES ($member_id, '$source', '$custom')";
    if (mysqli_query($conn, $query)) {
        echo "success";
    } else {
        echo "error";
    }
    exit();
}
echo "invalid_access";