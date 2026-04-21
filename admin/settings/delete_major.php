<?php
session_start();
include '../../include/config.php';

if (isset($_GET['id']) && !empty($_GET['id'])) {

    $major_id = mysqli_real_escape_string($connect1, $_GET['id']);

    $sql = "DELETE FROM tb_program WHERE g_id = '$major_id'";

    if (mysqli_query($connect1, $sql)) {
        if (mysqli_affected_rows($connect1) > 0) {
            $_SESSION['message'] = ['type' => 'success', 'text' => 'ลบสาขาวิชาสำเร็จ!'];
        } else {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'ไม่พบสาขาวิชาที่ต้องการลบ'];
        }
    } else {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'เกิดข้อผิดพลาดในการลบข้อมูล: ' . mysqli_error($connect1)];
    }
} else {
    $_SESSION['message'] = ['type' => 'error', 'text' => 'ไม่ได้ระบุ ID ของสาขาที่ต้องการลบ'];
}

header('Location: ../settings/majors.php');
exit();
