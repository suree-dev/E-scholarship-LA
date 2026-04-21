<?php
session_start();
include '../../include/config.php';

if (isset($_GET['id']) && !empty($_GET['id'])) {

    $ban_id = mysqli_real_escape_string($connect1, $_GET['id']);

    $sql = "DELETE FROM tb_ban WHERE id_ban = '$ban_id'";

    if (mysqli_query($connect1, $sql)) {
        if (mysqli_affected_rows($connect1) > 0) {
            $_SESSION['message'] = ['type' => 'success', 'text' => 'ยกเลิกการระงับนักศึกษาสำเร็จ!'];
        } else {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'ไม่พบข้อมูลที่ต้องการยกเลิก'];
        }
    } else {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'เกิดข้อผิดพลาด: ' . mysqli_error($connect1)];
    }
} else {
    $_SESSION['message'] = ['type' => 'error', 'text' => 'ไม่ได้ระบุ ID ของรายการ'];
}

header('Location: ../../student/susp_std.php');
exit();
