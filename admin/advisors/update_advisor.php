<?php
session_start();
include '../../include/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['advisor_id']) && !empty($_POST['advisor_name'])) {

    $advisor_id = mysqli_real_escape_string($connect1, $_POST['advisor_id']);
    $advisor_name = mysqli_real_escape_string($connect1, $_POST['advisor_name']);

    $sql = "UPDATE tb_teacher SET tc_name = '$advisor_name' WHERE tc_id = '$advisor_id'";

    if (mysqli_query($connect1, $sql)) {
        $_SESSION['message'] = ['type' => 'success', 'text' => 'อัปเดตข้อมูลอาจารย์สำเร็จ!'];
    } else {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'เกิดข้อผิดพลาดในการอัปเดต: ' . mysqli_error($connect1)];
    }
} else {
    $_SESSION['message'] = ['type' => 'error', 'text' => 'ข้อมูลไม่ครบถ้วน'];
}

header('Location: ../../admin/advisors/advisors.php');
exit();
