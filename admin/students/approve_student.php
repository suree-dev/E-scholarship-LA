<?php
session_start();
include '../../include/config.php';

$st_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$action = isset($_GET['action']) ? $_GET['action'] : 'approve'; // รับค่า action จาก URL

if ($st_id > 0) {
    if ($action === 'cancel') {
        $status_val = 0;
        $msg_success = 'ยกเลิกการอนุมัติเรียบร้อยแล้ว';
        $msg_error = 'เกิดข้อผิดพลาดในการยกเลิกการอนุมัติ';
    } else {
        $status_val = 1;
        $msg_success = 'อนุมัติคำขอรับทุนเรียบร้อยแล้ว';
        $msg_error = 'เกิดข้อผิดพลาดในการอนุมัติ';
    }

    $sql = "UPDATE tb_student SET st_confirm = '$status_val' WHERE st_id = '$st_id'";

    if (mysqli_query($connect1, $sql)) {
        $_SESSION['message'] = ['type' => 'success', 'text' => $msg_success];
    } else {
        $_SESSION['message'] = ['type' => 'error', 'text' => $msg_error];
    }
}

$res = mysqli_query($connect1, "SELECT st_type FROM tb_student WHERE st_id = '$st_id'");
$row = mysqli_fetch_assoc($res);
header("Location: ../students/student_data.php?type=" . $row['st_type']);
exit();
