<?php
session_start();
include '../../include/config.php';

if (isset($_GET['id']) && !empty($_GET['id'])) {

    $id = mysqli_real_escape_string($connect1, $_GET['id']);

    $sql = "DELETE FROM tb_teacher WHERE tc_id = '$id' AND tc_type = 5";

    if (mysqli_query($connect1, $sql)) {
        if (mysqli_affected_rows($connect1) > 0) {
            $_SESSION['message'] = ['type' => 'success', 'text' => 'ลบข้อมูลคณะกรรมการสำเร็จ!'];
        } else {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'ไม่พบข้อมูลที่ต้องการลบ'];
        }
    } else {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'เกิดข้อผิดพลาดในการลบข้อมูล: ' . mysqli_error($connect1)];
    }
} else {
    $_SESSION['message'] = ['type' => 'error', 'text' => 'ไม่ได้ระบุ ID ของข้อมูลที่ต้องการลบ'];
}

header('Location: ../committees/committees.php');
exit();
