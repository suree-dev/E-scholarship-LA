<?php
session_start();
include '../../include/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['major_id']) && !empty($_POST['major_name'])) {

    $major_id = mysqli_real_escape_string($connect1, $_POST['major_id']);
    $major_name = mysqli_real_escape_string($connect1, $_POST['major_name']);

    $sql = "UPDATE tb_program SET g_program = '$major_name' WHERE g_id = '$major_id'";

    if (mysqli_query($connect1, $sql)) {
        $_SESSION['message'] = ['type' => 'success', 'text' => 'อัปเดตสาขาวิชาสำเร็จ!'];
    } else {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'เกิดข้อผิดพลาดในการอัปเดต: ' . mysqli_error($connect1)];
    }
} else {
    $_SESSION['message'] = ['type' => 'error', 'text' => 'ข้อมูลไม่ครบถ้วน'];
}

header('Location: ../settings/majors.php');
exit();
