<?php
session_start();
include '../../include/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['major_name'])) {

    $major_name = mysqli_real_escape_string($connect1, $_POST['major_name']);

    $sql = "INSERT INTO tb_program (g_program) VALUES ('$major_name')";

    if (mysqli_query($connect1, $sql)) {
        $_SESSION['message'] = ['type' => 'success', 'text' => 'เพิ่มสาขาวิชา "' . htmlspecialchars($major_name) . '" สำเร็จ!'];
    } else {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'เกิดข้อผิดพลาดในการเพิ่มข้อมูล: ' . mysqli_error($connect1)];
    }
} else {
    $_SESSION['message'] = ['type' => 'error', 'text' => 'กรุณากรอกชื่อสาขาวิชา'];
}

header('Location: ../settings/majors.php');
exit();
